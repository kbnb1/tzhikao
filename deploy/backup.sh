#!/bin/bash
# ============================================================
# 智考AI - 数据备份脚本
# 功能：备份数据库、上传文件、代码配置
# ============================================================

set -e

# ===================== 可配置变量 =====================
# 项目路径
PROJECT_DIR="/var/www/zhikao"

# 数据库配置
DB_HOST="127.0.0.1"
DB_PORT="3306"
DB_NAME="zhikao"
DB_USER="zhikao"
DB_PASS="Zhikao@2024!"

# 备份配置
BACKUP_DIR="/data/backup/zhikao"      # 备份存储目录
RETENTION_DAYS=30                      # 保留天数
BACKUP_PREFIX="zhikao_backup"          # 备份文件前缀

# 备份内容开关
BACKUP_DATABASE=true                   # 是否备份数据库
BACKUP_UPLOADS=true                    # 是否备份上传文件
BACKUP_CONFIG=true                     # 是否备份配置文件
BACKUP_CODE=false                      # 是否备份代码（建议用Git管理）

# 压缩配置
COMPRESS=true                          # 是否压缩
COMPRESS_LEVEL=6                       # 压缩级别 1-9

# 日志文件
LOG_FILE="/var/log/zhikao_backup.log"
# =======================================================

# 颜色定义
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
CYAN='\033[0;36m'
NC='\033[0m'

# 日志函数
log_info() {
    echo -e "${GREEN}[INFO]${NC} $(date '+%Y-%m-%d %H:%M:%S') - $1"
    echo "[INFO] $(date '+%Y-%m-%d %H:%M:%S') - $1" >> "$LOG_FILE"
}

log_warn() {
    echo -e "${YELLOW}[WARN]${NC} $(date '+%Y-%m-%d %H:%M:%S') - $1"
    echo "[WARN] $(date '+%Y-%m-%d %H:%M:%S') - $1" >> "$LOG_FILE"
}

log_error() {
    echo -e "${RED}[ERROR]${NC} $(date '+%Y-%m-%d %H:%M:%S') - $1"
    echo "[ERROR] $(date '+%Y-%m-%d %H:%M:%S') - $1" >> "$LOG_FILE"
}

# 帮助信息
show_help() {
    cat << EOF
智考AI - 数据备份脚本

用法: bash $0 [选项]

选项:
    --help              显示此帮助信息
    --backup-dir        备份存储目录 (默认: ${BACKUP_DIR})
    --retention-days    保留天数 (默认: ${RETENTION_DAYS})
    --db-name           数据库名 (默认: ${DB_NAME})
    --db-user           数据库用户名 (默认: ${DB_USER})
    --db-pass           数据库密码 (默认: ${DB_PASS})
    --no-db             不备份数据库
    --no-uploads        不备份上传文件
    --no-config         不备份配置文件
    --with-code         同时备份代码
    --no-compress       不压缩备份文件
    --list              列出所有备份
    --restore           恢复备份（需指定备份文件）
    --clean             清理过期备份

示例:
    bash $0
    bash $0 --backup-dir /data/backup
    bash $0 --retention-days 15
    bash $0 --no-uploads --no-config
    bash $0 --list
    bash $0 --clean

注意:
    - 数据库备份需要 mysqldump 工具
    - 建议设置 crontab 定时执行
    - 重要数据请同时备份到远程服务器
EOF
}

# 解析命令行参数
parse_args() {
    ACTION="backup"
    
    while [[ $# -gt 0 ]]; do
        case $1 in
            --help)
                show_help
                exit 0
                ;;
            --backup-dir)
                BACKUP_DIR="$2"
                shift 2
                ;;
            --retention-days)
                RETENTION_DAYS="$2"
                shift 2
                ;;
            --db-name)
                DB_NAME="$2"
                shift 2
                ;;
            --db-user)
                DB_USER="$2"
                shift 2
                ;;
            --db-pass)
                DB_PASS="$2"
                shift 2
                ;;
            --no-db)
                BACKUP_DATABASE=false
                shift
                ;;
            --no-uploads)
                BACKUP_UPLOADS=false
                shift
                ;;
            --no-config)
                BACKUP_CONFIG=false
                shift
                ;;
            --with-code)
                BACKUP_CODE=true
                shift
                ;;
            --no-compress)
                COMPRESS=false
                shift
                ;;
            --list)
                ACTION="list"
                shift
                ;;
            --restore)
                ACTION="restore"
                RESTORE_FILE="$2"
                shift 2
                ;;
            --clean)
                ACTION="clean"
                shift
                ;;
            *)
                log_error "未知选项: $1"
                show_help
                exit 1
                ;;
        esac
    done
}

# 初始化备份目录
init_backup_dir() {
    if [[ ! -d "$BACKUP_DIR" ]]; then
        mkdir -p "$BACKUP_DIR"
        log_info "创建备份目录: $BACKUP_DIR"
    fi
    
    # 检查目录可写
    if [[ ! -w "$BACKUP_DIR" ]]; then
        log_error "备份目录不可写: $BACKUP_DIR"
        exit 1
    fi
}

# 备份数据库
backup_database() {
    if [[ "$BACKUP_DATABASE" != true ]]; then
        log_info "跳过数据库备份"
        return
    fi
    
    log_info "开始备份数据库..."
    
    BACKUP_FILE="${BACKUP_TMP_DIR}/database_${DB_NAME}.sql"
    
    # 使用 mysqldump 备份
    mysqldump -h "$DB_HOST" \
              -P "$DB_PORT" \
              -u "$DB_USER" \
              -p"$DB_PASS" \
              --single-transaction \
              --routines \
              --triggers \
              --databases "$DB_NAME" \
              --default-character-set=utf8mb4 > "$BACKUP_FILE" 2>>"$LOG_FILE"
    
    if [[ $? -eq 0 ]]; then
        FILE_SIZE=$(du -h "$BACKUP_FILE" | awk '{print $1}')
        log_info "数据库备份完成: $BACKUP_FILE ($FILE_SIZE)"
    else
        log_error "数据库备份失败"
        exit 1
    fi
}

# 备份上传文件
backup_uploads() {
    if [[ "$BACKUP_UPLOADS" != true ]]; then
        log_info "跳过上传文件备份"
        return
    fi
    
    log_info "开始备份上传文件..."
    
    UPLOAD_DIR="${PROJECT_DIR}/public/uploads"
    
    if [[ ! -d "$UPLOAD_DIR" ]]; then
        log_warn "上传目录不存在: $UPLOAD_DIR"
        return
    fi
    
    BACKUP_FILE="${BACKUP_TMP_DIR}/uploads"
    cp -r "$UPLOAD_DIR" "$BACKUP_FILE"
    
    FILE_COUNT=$(find "$BACKUP_FILE" -type f | wc -l)
    DIR_SIZE=$(du -sh "$BACKUP_FILE" | awk '{print $1}')
    log_info "上传文件备份完成: $FILE_COUNT 个文件 ($DIR_SIZE)"
}

# 备份配置文件
backup_config() {
    if [[ "$BACKUP_CONFIG" != true ]]; then
        log_info "跳过配置文件备份"
        return
    fi
    
    log_info "开始备份配置文件..."
    
    BACKUP_FILE="${BACKUP_TMP_DIR}/config"
    mkdir -p "$BACKUP_FILE"
    
    # 备份 .env 文件
    if [[ -f "${PROJECT_DIR}/.env" ]]; then
        cp "${PROJECT_DIR}/.env" "$BACKUP_FILE/"
        log_info "已备份: .env"
    fi
    
    # 备份配置目录
    if [[ -d "${PROJECT_DIR}/config" ]]; then
        cp -r "${PROJECT_DIR}/config" "$BACKUP_FILE/"
        log_info "已备份: config/"
    fi
    
    # 备份 Nginx 配置
    if [[ -f "/etc/nginx/sites-available/zhikao.conf" ]]; then
        mkdir -p "$BACKUP_FILE/nginx"
        cp "/etc/nginx/sites-available/zhikao.conf" "$BACKUP_FILE/nginx/"
        log_info "已备份: nginx/zhikao.conf"
    fi
    
    # 备份 PHP-FPM 配置
    PHP_FPM_CONF="/etc/php/8.1/fpm/pool.d/www.conf"
    if [[ -f "$PHP_FPM_CONF" ]]; then
        mkdir -p "$BACKUP_FILE/php"
        cp "$PHP_FPM_CONF" "$BACKUP_FILE/php/"
        log_info "已备份: php/www.conf"
    fi
    
    log_info "配置文件备份完成"
}

# 备份代码
backup_code() {
    if [[ "$BACKUP_CODE" != true ]]; then
        log_info "跳过代码备份"
        return
    fi
    
    log_info "开始备份代码..."
    
    BACKUP_FILE="${BACKUP_TMP_DIR}/code"
    
    # 复制代码（排除运行时和缓存文件）
    rsync -av --exclude='runtime/*' \
              --exclude='.git' \
              --exclude='vendor' \
              --exclude='public/uploads' \
              "$PROJECT_DIR/" "$BACKUP_FILE/" >> "$LOG_FILE" 2>&1
    
    FILE_COUNT=$(find "$BACKUP_FILE" -type f | wc -l)
    DIR_SIZE=$(du -sh "$BACKUP_FILE" | awk '{print $1}')
    log_info "代码备份完成: $FILE_COUNT 个文件 ($DIR_SIZE)"
}

# 打包压缩
compress_backup() {
    if [[ "$COMPRESS" != true ]]; then
        # 直接移动到备份目录
        FINAL_BACKUP="${BACKUP_DIR}/${BACKUP_PREFIX}_${BACKUP_DATE}"
        mv "$BACKUP_TMP_DIR" "$FINAL_BACKUP"
        log_info "备份已保存: $FINAL_BACKUP"
        return
    fi
    
    log_info "正在压缩备份..."
    
    BACKUP_ARCHIVE="${BACKUP_DIR}/${BACKUP_PREFIX}_${BACKUP_DATE}.tar.gz"
    
    cd "$(dirname "$BACKUP_TMP_DIR")"
    tar -czf "$BACKUP_ARCHIVE" "$(basename "$BACKUP_TMP_DIR")"
    
    FILE_SIZE=$(du -h "$BACKUP_ARCHIVE" | awk '{print $1}')
    log_info "备份压缩完成: $BACKUP_ARCHIVE ($FILE_SIZE)"
    
    # 清理临时目录
    rm -rf "$BACKUP_TMP_DIR"
}

# 清理过期备份
clean_old_backups() {
    log_info "清理 ${RETENTION_DAYS} 天前的备份..."
    
    # 查找并删除过期文件
    find "$BACKUP_DIR" -type f -name "${BACKUP_PREFIX}_*.tar.gz" -mtime +"$RETENTION_DAYS" -delete 2>/dev/null || true
    find "$BACKUP_DIR" -type d -name "${BACKUP_PREFIX}_*" -mtime +"$RETENTION_DAYS" -exec rm -rf {} + 2>/dev/null || true
    
    # 统计剩余备份
    BACKUP_COUNT=$(find "$BACKUP_DIR" -name "${BACKUP_PREFIX}_*" | wc -l)
    log_info "清理完成，剩余 $BACKUP_COUNT 个备份"
}

# 列出所有备份
list_backups() {
    echo ""
    echo -e "${CYAN}============================================================${NC}"
    echo -e "${GREEN}                   备份列表${NC}"
    echo -e "${CYAN}============================================================${NC}"
    echo ""
    
    if [[ ! -d "$BACKUP_DIR" ]]; then
        echo -e "${YELLOW}备份目录不存在: $BACKUP_DIR${NC}"
        return
    fi
    
    echo -e "备份目录: ${YELLOW}$BACKUP_DIR${NC}"
    echo ""
    
    # 列出压缩包
    if ls "$BACKUP_DIR"/*.tar.gz 1> /dev/null 2>&1; then
        echo -e "${GREEN}压缩备份:${NC}"
        ls -lh "$BACKUP_DIR"/*.tar.gz | awk '{printf "  %s  %s  %s\n", $6, $5, $9}'
        echo ""
    fi
    
    # 列出目录备份
    for dir in "$BACKUP_DIR"/${BACKUP_PREFIX}_*/; do
        if [[ -d "$dir" ]]; then
            echo -e "${GREEN}目录备份:${NC}"
            du -sh "$dir" | awk '{printf "  %s  %s\n", $1, $2}'
        fi
    done
    
    # 总大小
    TOTAL_SIZE=$(du -sh "$BACKUP_DIR" | awk '{print $1}')
    echo ""
    echo -e "总大小: ${YELLOW}$TOTAL_SIZE${NC}"
    echo ""
    echo -e "${CYAN}============================================================${NC}"
}

# 恢复备份
restore_backup() {
    if [[ -z "$RESTORE_FILE" ]]; then
        log_error "请指定要恢复的备份文件"
        exit 1
    fi
    
    if [[ ! -f "$RESTORE_FILE" && ! -d "$RESTORE_FILE" ]]; then
        log_error "备份文件不存在: $RESTORE_FILE"
        exit 1
    fi
    
    log_warn "即将恢复备份: $RESTORE_FILE"
    log_warn "这将覆盖当前数据，请确认已做好当前数据备份！"
    read -p "确认恢复？(y/N): " confirm
    if [[ "$confirm" != "y" && "$confirm" != "Y" ]]; then
        log_info "取消恢复"
        exit 0
    fi
    
    # 创建临时目录用于解压
    RESTORE_TMP_DIR=$(mktemp -d)
    
    if [[ -f "$RESTORE_FILE" ]]; then
        log_info "正在解压备份文件..."
        tar -xzf "$RESTORE_FILE" -C "$RESTORE_TMP_DIR"
        BACKUP_CONTENT="$RESTORE_TMP_DIR/$(ls "$RESTORE_TMP_DIR")"
    else
        BACKUP_CONTENT="$RESTORE_FILE"
    fi
    
    # 恢复数据库
    if [[ -f "${BACKUP_CONTENT}/database_${DB_NAME}.sql" ]]; then
        log_info "恢复数据库..."
        mysql -h "$DB_HOST" -P "$DB_PORT" -u "$DB_USER" -p"$DB_PASS" "$DB_NAME" < "${BACKUP_CONTENT}/database_${DB_NAME}.sql"
        log_info "数据库恢复完成"
    else
        log_warn "未找到数据库备份，跳过数据库恢复"
    fi
    
    # 恢复上传文件
    if [[ -d "${BACKUP_CONTENT}/uploads" ]]; then
        log_info "恢复上传文件..."
        rm -rf "${PROJECT_DIR}/public/uploads/*"
        cp -r "${BACKUP_CONTENT}/uploads/"* "${PROJECT_DIR}/public/uploads/"
        chown -R www-data:www-data "${PROJECT_DIR}/public/uploads"
        log_info "上传文件恢复完成"
    else
        log_warn "未找到上传文件备份，跳过"
    fi
    
    # 恢复配置文件
    if [[ -d "${BACKUP_CONTENT}/config" ]]; then
        log_info "恢复配置文件..."
        if [[ -f "${BACKUP_CONTENT}/config/.env" ]]; then
            cp "${BACKUP_CONTENT}/config/.env" "${PROJECT_DIR}/.env"
            chown www-data:www-data "${PROJECT_DIR}/.env"
        fi
        log_info "配置文件恢复完成"
    else
        log_warn "未找到配置文件备份，跳过"
    fi
    
    # 清理临时目录
    rm -rf "$RESTORE_TMP_DIR"
    
    log_info "备份恢复完成"
}

# 执行备份
do_backup() {
    # 初始化
    init_backup_dir
    
    # 生成备份日期时间戳
    BACKUP_DATE=$(date '+%Y%m%d_%H%M%S')
    BACKUP_TMP_DIR=$(mktemp -d)
    
    log_info "========== 开始备份 =========="
    log_info "备份时间: $(date)"
    log_info "备份目录: $BACKUP_DIR"
    log_info "保留天数: $RETENTION_DAYS 天"
    echo ""
    
    START_TIME=$(date +%s)
    
    # 执行各项备份
    backup_database
    backup_uploads
    backup_config
    backup_code
    
    # 打包压缩
    compress_backup
    
    # 清理过期备份
    clean_old_backups
    
    END_TIME=$(date +%s)
    DURATION=$((END_TIME - START_TIME))
    
    echo ""
    log_info "========== 备份完成 =========="
    log_info "耗时: ${DURATION} 秒"
}

# 主函数
main() {
    # 初始化日志
    mkdir -p "$(dirname "$LOG_FILE")"
    echo "=== 智考AI 备份日志 - $(date) ===" >> "$LOG_FILE"
    
    # 解析参数
    parse_args "$@"
    
    # 根据动作执行
    case "$ACTION" in
        backup)
            do_backup
            ;;
        list)
            list_backups
            ;;
        restore)
            restore_backup
            ;;
        clean)
            init_backup_dir
            clean_old_backups
            ;;
        *)
            log_error "未知动作: $ACTION"
            exit 1
            ;;
    esac
}

# 执行主函数
main "$@"
