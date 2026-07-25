#!/bin/bash
# ============================================================
# 智考AI - 一键更新脚本
# 功能：从Git拉取代码、更新依赖、执行迁移、清理缓存、平滑重启
# ============================================================

set -e

# ===================== 可配置变量 =====================
# 项目路径
PROJECT_DIR="/var/www/zhikao"

# PHP配置
PHP_VERSION="8.1"
PHP_FPM_SERVICE="php${PHP_VERSION}-fpm"

# Git配置
GIT_REMOTE="origin"
GIT_BRANCH="main"

# 数据库配置
DB_HOST="127.0.0.1"
DB_PORT="3306"
DB_NAME="zhikao"
DB_USER="zhikao"
DB_PASS="Zhikao@2024!"

# 维护模式
MAINTENANCE_MODE=true

# 备份配置
BACKUP_BEFORE_UPDATE=true
BACKUP_DIR="/data/backup/zhikao/updates"

# 日志文件
LOG_FILE="/var/log/zhikao_update.log"
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
智考AI - 一键更新脚本

用法: sudo bash $0 [选项]

选项:
    --help              显示此帮助信息
    --project-dir       项目目录 (默认: ${PROJECT_DIR})
    --branch            Git分支 (默认: ${GIT_BRANCH})
    --remote            Git远程仓库 (默认: ${GIT_REMOTE})
    --no-backup         更新前不备份
    --no-migration      不执行数据库迁移
    --no-cache-clear    不清理缓存
    --no-composer       不更新 Composer 依赖
    --no-git            不从 Git 拉取代码（只执行后续步骤）
    --dry-run           演练模式，只检查不实际更新
    --rollback          回滚到上一个版本

示例:
    sudo bash $0
    sudo bash $0 --branch dev
    sudo bash $0 --no-composer --no-migration
    sudo bash $0 --dry-run
    sudo bash $0 --rollback

更新步骤:
    1. 检查环境
    2. 开启维护模式
    3. 更新前备份（可选）
    4. 从 Git 拉取最新代码
    5. 更新 Composer 依赖
    6. 执行数据库迁移
    7. 清理运行时缓存
    8. 重启 PHP-FPM
    9. 关闭维护模式
    10. 验证更新
EOF
}

# 解析命令行参数
parse_args() {
    ACTION="update"
    DRY_RUN=false
    SKIP_GIT=false
    SKIP_COMPOSER=false
    SKIP_MIGRATION=false
    SKIP_CACHE_CLEAR=false
    
    while [[ $# -gt 0 ]]; do
        case $1 in
            --help)
                show_help
                exit 0
                ;;
            --project-dir)
                PROJECT_DIR="$2"
                shift 2
                ;;
            --branch)
                GIT_BRANCH="$2"
                shift 2
                ;;
            --remote)
                GIT_REMOTE="$2"
                shift 2
                ;;
            --no-backup)
                BACKUP_BEFORE_UPDATE=false
                shift
                ;;
            --no-migration)
                SKIP_MIGRATION=true
                shift
                ;;
            --no-cache-clear)
                SKIP_CACHE_CLEAR=true
                shift
                ;;
            --no-composer)
                SKIP_COMPOSER=true
                shift
                ;;
            --no-git)
                SKIP_GIT=true
                shift
                ;;
            --dry-run)
                DRY_RUN=true
                shift
                ;;
            --rollback)
                ACTION="rollback"
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

# 检查是否为 root 用户
check_root() {
    if [[ $EUID -ne 0 ]]; then
        log_error "此脚本必须使用 root 或 sudo 权限运行"
        exit 1
    fi
}

# 环境检查
check_env() {
    log_info "检查更新环境..."
    
    # 检查项目目录
    if [[ ! -d "$PROJECT_DIR" ]]; then
        log_error "项目目录不存在: $PROJECT_DIR"
        exit 1
    fi
    
    cd "$PROJECT_DIR"
    
    # 检查 Git（除非跳过）
    if [[ "$SKIP_GIT" != true ]]; then
        if ! command -v git &> /dev/null; then
            log_error "Git 未安装"
            exit 1
        fi
        
        if [[ ! -d ".git" ]]; then
            log_error "项目不是 Git 仓库"
            exit 1
        fi
        
        # 检查远程仓库
        if ! git remote get-url "$GIT_REMOTE" &> /dev/null; then
            log_error "Git 远程仓库不存在: $GIT_REMOTE"
            exit 1
        fi
        
        log_info "Git 环境正常"
    fi
    
    # 检查 Composer
    if [[ "$SKIP_COMPOSER" != true ]]; then
        if ! command -v composer &> /dev/null; then
            log_error "Composer 未安装"
            exit 1
        fi
        log_info "Composer 环境正常"
    fi
    
    # 检查 PHP
    if ! command -v php &> /dev/null; then
        log_error "PHP 未安装"
        exit 1
    fi
    
    # 检查项目文件
    if [[ ! -f "composer.json" ]]; then
        log_error "composer.json 不存在"
        exit 1
    fi
    
    log_info "环境检查通过"
}

# 开启维护模式
enable_maintenance() {
    if [[ "$MAINTENANCE_MODE" != true ]]; then
        return
    fi
    
    if [[ "$DRY_RUN" == true ]]; then
        log_info "[演练] 将开启维护模式"
        return
    fi
    
    log_info "开启维护模式..."
    
    # 创建维护模式标识文件
    touch "$PROJECT_DIR/public/maintenance.html"
    
    # 写入维护页面
    cat > "$PROJECT_DIR/public/maintenance.html" << 'EOF'
<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>系统维护中 - 智考AI</title>
    <style>
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0;
            color: #fff;
        }
        .container {
            text-align: center;
            padding: 40px;
        }
        h1 {
            font-size: 48px;
            margin-bottom: 20px;
        }
        p {
            font-size: 18px;
            opacity: 0.9;
            margin-bottom: 10px;
        }
        .icon {
            font-size: 80px;
            margin-bottom: 20px;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="icon">⚙️</div>
        <h1>系统维护中</h1>
        <p>系统正在进行升级维护</p>
        <p>请稍后再试，感谢您的理解！</p>
    </div>
</body>
</html>
EOF
    
    log_info "维护模式已开启"
}

# 关闭维护模式
disable_maintenance() {
    if [[ "$MAINTENANCE_MODE" != true ]]; then
        return
    fi
    
    if [[ "$DRY_RUN" == true ]]; then
        log_info "[演练] 将关闭维护模式"
        return
    fi
    
    log_info "关闭维护模式..."
    
    rm -f "$PROJECT_DIR/public/maintenance.html"
    
    log_info "维护模式已关闭"
}

# 更新前备份
backup_before_update() {
    if [[ "$BACKUP_BEFORE_UPDATE" != true ]]; then
        log_info "跳过更新前备份"
        return
    fi
    
    if [[ "$DRY_RUN" == true ]]; then
        log_info "[演练] 将创建更新前备份"
        return
    fi
    
    log_info "创建更新前备份..."
    
    mkdir -p "$BACKUP_DIR"
    
    BACKUP_DATE=$(date '+%Y%m%d_%H%M%S')
    BACKUP_FILE="${BACKUP_DIR}/pre_update_${BACKUP_DATE}.tar.gz"
    
    # 备份代码和配置（排除 runtime 和 vendor）
    cd "$PROJECT_DIR"
    tar -czf "$BACKUP_FILE" \
        --exclude='runtime/*' \
        --exclude='vendor' \
        --exclude='public/uploads' \
        . 2>>"$LOG_FILE"
    
    FILE_SIZE=$(du -h "$BACKUP_FILE" | awk '{print $1}')
    log_info "备份完成: $BACKUP_FILE ($FILE_SIZE)"
    
    # 同时备份数据库
    DB_BACKUP="${BACKUP_DIR}/pre_update_${BACKUP_DATE}_db.sql"
    mysqldump -h "$DB_HOST" -P "$DB_PORT" -u "$DB_USER" -p"$DB_PASS" \
        --single-transaction "$DB_NAME" > "$DB_BACKUP" 2>>"$LOG_FILE"
    
    log_info "数据库备份完成: $DB_BACKUP"
}

# 从 Git 拉取代码
git_pull() {
    if [[ "$SKIP_GIT" == true ]]; then
        log_info "跳过 Git 代码拉取"
        return
    fi
    
    if [[ "$DRY_RUN" == true ]]; then
        log_info "[演练] 将从 ${GIT_REMOTE}/${GIT_BRANCH} 拉取代码"
        cd "$PROJECT_DIR"
        git fetch "$GIT_REMOTE" 2>>"$LOG_FILE" || true
        LOCAL_COMMIT=$(git rev-parse --short HEAD 2>/dev/null || echo "unknown")
        REMOTE_COMMIT=$(git rev-parse --short ${GIT_REMOTE}/${GIT_BRANCH} 2>/dev/null || echo "unknown")
        log_info "[演练] 当前版本: $LOCAL_COMMIT, 远程版本: $REMOTE_COMMIT"
        return
    fi
    
    log_info "从 Git 拉取最新代码..."
    
    cd "$PROJECT_DIR"
    
    # 保存当前版本
    OLD_COMMIT=$(git rev-parse --short HEAD)
    log_info "当前版本: $OLD_COMMIT"
    
    # 拉取最新代码
    git fetch "$GIT_REMOTE" 2>>"$LOG_FILE"
    git checkout "$GIT_BRANCH" 2>>"$LOG_FILE"
    git pull "$GIT_REMOTE" "$GIT_BRANCH" 2>>"$LOG_FILE"
    
    # 获取新版本
    NEW_COMMIT=$(git rev-parse --short HEAD)
    log_info "新版本: $NEW_COMMIT"
    
    if [[ "$OLD_COMMIT" == "$NEW_COMMIT" ]]; then
        log_info "代码已是最新版本，无需更新"
    else
        log_info "代码已更新: $OLD_COMMIT -> $NEW_COMMIT"
        
        # 显示提交记录
        echo ""
        log_info "更新内容:"
        git log --oneline "${OLD_COMMIT}..${NEW_COMMIT}" 2>/dev/null || true
        echo ""
    fi
}

# 更新 Composer 依赖
update_composer() {
    if [[ "$SKIP_COMPOSER" == true ]]; then
        log_info "跳过 Composer 依赖更新"
        return
    fi
    
    if [[ "$DRY_RUN" == true ]]; then
        log_info "[演练] 将更新 Composer 依赖"
        return
    fi
    
    log_info "更新 Composer 依赖..."
    
    cd "$PROJECT_DIR"
    
    # 安装/更新依赖
    composer install --no-dev --optimize-autoloader >> "$LOG_FILE" 2>&1
    
    log_info "Composer 依赖更新完成"
}

# 执行数据库迁移
run_migration() {
    if [[ "$SKIP_MIGRATION" == true ]]; then
        log_info "跳过数据库迁移"
        return
    fi
    
    if [[ "$DRY_RUN" == true ]]; then
        log_info "[演练] 将执行数据库迁移"
        return
    fi
    
    log_info "检查数据库迁移..."
    
    cd "$PROJECT_DIR"
    
    # 检查 ThinkPHP 迁移工具
    if [[ -f "think" ]]; then
        # 检查是否有 migrate 命令
        if php think list | grep -q "migrate" 2>/dev/null; then
            log_info "执行数据库迁移..."
            php think migrate:run >> "$LOG_FILE" 2>&1
            log_info "数据库迁移完成"
        else
            log_info "未找到迁移命令，跳过"
        fi
    else
        log_info "ThinkPHP 命令行工具不存在，跳过迁移"
    fi
}

# 清理缓存
clear_cache() {
    if [[ "$SKIP_CACHE_CLEAR" == true ]]; then
        log_info "跳过缓存清理"
        return
    fi
    
    if [[ "$DRY_RUN" == true ]]; then
        log_info "[演练] 将清理运行时缓存"
        return
    fi
    
    log_info "清理运行时缓存..."
    
    cd "$PROJECT_DIR"
    
    # 清理 ThinkPHP runtime 目录
    if [[ -d "runtime" ]]; then
        # 保留目录结构，只清理内容
        find runtime -type f -delete 2>/dev/null || true
        find runtime -type d -empty -delete 2>/dev/null || true
        log_info "Runtime 缓存已清理"
    fi
    
    # 使用 ThinkPHP 命令清理（如果可用）
    if [[ -f "think" ]]; then
        php think clear >> "$LOG_FILE" 2>&1 || true
        log_info "ThinkPHP 缓存已清理"
    fi
    
    # 清理 OPcache
    if command -v systemctl &> /dev/null; then
        systemctl reload "$PHP_FPM_SERVICE" 2>>"$LOG_FILE" || true
        log_info "OPcache 已重置"
    fi
    
    log_info "缓存清理完成"
}

# 重启服务
restart_services() {
    if [[ "$DRY_RUN" == true ]]; then
        log_info "[演练] 将重启服务"
        return
    fi
    
    log_info "重启相关服务..."
    
    # 平滑重启 PHP-FPM
    if systemctl is-active --quiet "$PHP_FPM_SERVICE"; then
        systemctl reload "$PHP_FPM_SERVICE" 2>>"$LOG_FILE"
        log_info "PHP-FPM 已平滑重启"
    fi
    
    # 重载 Nginx
    if systemctl is-active --quiet nginx; then
        nginx -t >> "$LOG_FILE" 2>&1 && systemctl reload nginx 2>>"$LOG_FILE"
        log_info "Nginx 已重载"
    fi
    
    log_info "服务重启完成"
}

# 验证更新
verify_update() {
    log_info "验证更新结果..."
    
    cd "$PROJECT_DIR"
    
    # 检查项目文件
    if [[ -f "composer.json" ]]; then
        log_info "项目文件: 正常"
    else
        log_error "项目文件: 异常"
        return 1
    fi
    
    # 检查 vendor 目录
    if [[ -d "vendor" ]]; then
        log_info "依赖目录: 正常"
    else
        log_warn "依赖目录: 不存在（可能未安装依赖）"
    fi
    
    # 检查 PHP-FPM
    if systemctl is-active --quiet "$PHP_FPM_SERVICE"; then
        log_info "PHP-FPM: 运行中"
    else
        log_error "PHP-FPM: 未运行"
        return 1
    fi
    
    # 检查 Nginx
    if systemctl is-active --quiet nginx; then
        log_info "Nginx: 运行中"
    else
        log_warn "Nginx: 未运行"
    fi
    
    # 检查数据库连接
    if mysql -h "$DB_HOST" -P "$DB_PORT" -u "$DB_USER" -p"$DB_PASS" -e "SELECT 1;" &>/dev/null; then
        log_info "数据库: 连接正常"
    else
        log_warn "数据库: 连接失败"
    fi
    
    log_info "更新验证完成"
    return 0
}

# 回滚
do_rollback() {
    log_info "准备回滚..."
    
    cd "$PROJECT_DIR"
    
    # 检查 Git
    if [[ ! -d ".git" ]]; then
        log_error "不是 Git 仓库，无法回滚"
        exit 1
    fi
    
    # 查找上一个版本
    PREV_COMMIT=$(git log --oneline -2 | tail -n1 | awk '{print $1}')
    
    if [[ -z "$PREV_COMMIT" ]]; then
        log_error "无法找到上一个版本"
        exit 1
    fi
    
    log_warn "将回滚到版本: $PREV_COMMIT"
    read -p "确认回滚？(y/N): " confirm
    if [[ "$confirm" != "y" && "$confirm" != "Y" ]]; then
        log_info "取消回滚"
        exit 0
    fi
    
    # 开启维护模式
    enable_maintenance
    
    # Git 回滚
    log_info "回滚代码..."
    git reset --hard "$PREV_COMMIT" 2>>"$LOG_FILE"
    log_info "代码已回滚到: $PREV_COMMIT"
    
    # 更新依赖
    if [[ "$SKIP_COMPOSER" != true ]]; then
        log_info "更新依赖..."
        composer install --no-dev --optimize-autoloader >> "$LOG_FILE" 2>&1
        log_info "依赖更新完成"
    fi
    
    # 清理缓存
    clear_cache
    
    # 重启服务
    restart_services
    
    # 关闭维护模式
    disable_maintenance
    
    log_info "回滚完成"
}

# 主更新流程
do_update() {
    log_info "========== 开始更新 =========="
    log_info "更新时间: $(date)"
    log_info "项目目录: $PROJECT_DIR"
    log_info "Git 分支: $GIT_REMOTE/$GIT_BRANCH"
    
    if [[ "$DRY_RUN" == true ]]; then
        log_warn "演练模式：不会实际执行更新"
    fi
    
    echo ""
    START_TIME=$(date +%s)
    
    # 1. 开启维护模式
    enable_maintenance
    
    # 2. 更新前备份
    backup_before_update
    
    # 3. 拉取代码
    git_pull
    
    # 4. 更新依赖
    update_composer
    
    # 5. 数据库迁移
    run_migration
    
    # 6. 清理缓存
    clear_cache
    
    # 7. 重启服务
    restart_services
    
    # 8. 关闭维护模式
    disable_maintenance
    
    # 9. 验证更新
    verify_update
    
    END_TIME=$(date +%s)
    DURATION=$((END_TIME - START_TIME))
    
    echo ""
    log_info "========== 更新完成 =========="
    log_info "耗时: ${DURATION} 秒"
    
    if [[ "$DRY_RUN" == true ]]; then
        log_info "演练模式完成，未实际修改任何内容"
    fi
}

# 错误处理
on_error() {
    log_error "更新过程中发生错误！"
    
    # 尝试关闭维护模式
    disable_maintenance
    
    log_error "请检查日志: $LOG_FILE"
    exit 1
}

# 主函数
main() {
    # 初始化日志
    mkdir -p "$(dirname "$LOG_FILE")"
    echo "=== 智考AI 更新日志 - $(date) ===" > "$LOG_FILE"
    
    # 错误陷阱
    trap 'on_error' ERR
    
    # 解析参数
    parse_args "$@"
    
    # 前置检查
    check_root
    check_env
    
    echo ""
    echo -e "${CYAN}============================================================${NC}"
    echo -e "${GREEN}            欢迎使用智考AI 一键更新脚本${NC}"
    echo -e "${CYAN}============================================================${NC}"
    echo ""
    
    # 根据动作执行
    case "$ACTION" in
        update)
            do_update
            ;;
        rollback)
            do_rollback
            ;;
        *)
            log_error "未知动作: $ACTION"
            exit 1
            ;;
    esac
}

# 执行主函数
main "$@"
