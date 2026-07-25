#!/bin/bash
# ============================================================
# 智考AI - 服务监控脚本
# 功能：监控 PHP-FPM、MySQL、Nginx、磁盘空间、系统资源
# ============================================================

# ===================== 可配置变量 =====================
# 项目路径
PROJECT_DIR="/var/www/zhikao"

# PHP配置
PHP_VERSION="8.1"
PHP_FPM_SERVICE="php${PHP_VERSION}-fpm"

# 数据库配置
DB_HOST="127.0.0.1"
DB_PORT="3306"
DB_USER="zhikao"
DB_PASS="Zhikao@2024!"
DB_NAME="zhikao"

# 告警阈值
DISK_USAGE_WARN=80       # 磁盘使用率告警阈值 (%)
MEMORY_USAGE_WARN=80     # 内存使用率告警阈值 (%)
CPU_USAGE_WARN=80        # CPU使用率告警阈值 (%)
MYSQL_CONN_WARN=80       # MySQL连接数告警阈值 (%)

# 日志文件
LOG_FILE="/var/log/zhikao_monitor.log"

# 告警方式：log(日志) / email(邮件)
ALERT_METHOD="log"
# =======================================================

# 颜色定义
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
CYAN='\033[0;36m'
NC='\033[0m'

# 告警计数器
WARN_COUNT=0
ERROR_COUNT=0

# 日志函数
log_info() {
    echo -e "${GREEN}[INFO]${NC} $(date '+%Y-%m-%d %H:%M:%S') - $1"
    echo "[INFO] $(date '+%Y-%m-%d %H:%M:%S') - $1" >> "$LOG_FILE"
}

log_warn() {
    echo -e "${YELLOW}[WARN]${NC} $(date '+%Y-%m-%d %H:%M:%S') - $1"
    echo "[WARN] $(date '+%Y-%m-%d %H:%M:%S') - $1" >> "$LOG_FILE"
    ((WARN_COUNT++))
}

log_error() {
    echo -e "${RED}[ERROR]${NC} $(date '+%Y-%m-%d %H:%M:%S') - $1"
    echo "[ERROR] $(date '+%Y-%m-%d %H:%M:%S') - $1" >> "$LOG_FILE"
    ((ERROR_COUNT++))
}

# 帮助信息
show_help() {
    cat << EOF
智考AI - 服务监控脚本

用法: bash $0 [选项]

选项:
    --help              显示此帮助信息
    --once              只运行一次检查
    --daemon            以守护进程模式运行
    --interval          检查间隔（秒，默认: 60）
    --disk-warn         磁盘告警阈值 (%)
    --memory-warn       内存告警阈值 (%)
    --cpu-warn          CPU告警阈值 (%)

示例:
    bash $0                          # 单次检查
    bash $0 --daemon --interval 30   # 守护进程模式，30秒检查一次
    bash $0 --disk-warn 90           # 磁盘告警阈值设为90%

检查项:
    - Nginx 服务状态
    - PHP-FPM 服务状态
    - MySQL 服务状态
    - Redis 服务状态（可选）
    - 磁盘空间使用率
    - 内存使用率
    - CPU 负载
    - 数据库连接数
    - 项目目录权限
EOF
}

# 解析命令行参数
parse_args() {
    MODE="once"
    INTERVAL=60
    
    while [[ $# -gt 0 ]]; do
        case $1 in
            --help)
                show_help
                exit 0
                ;;
            --once)
                MODE="once"
                shift
                ;;
            --daemon)
                MODE="daemon"
                shift
                ;;
            --interval)
                INTERVAL="$2"
                shift 2
                ;;
            --disk-warn)
                DISK_USAGE_WARN="$2"
                shift 2
                ;;
            --memory-warn)
                MEMORY_USAGE_WARN="$2"
                shift 2
                ;;
            --cpu-warn)
                CPU_USAGE_WARN="$2"
                shift 2
                ;;
            *)
                log_error "未知选项: $1"
                show_help
                exit 1
                ;;
        esac
    done
}

# 检查 Nginx 状态
check_nginx() {
    echo -e "${CYAN}【Nginx 状态】${NC}"
    
    if systemctl is-active --quiet nginx; then
        # 获取 Nginx 版本
        NGINX_VERSION=$(nginx -v 2>&1 | awk -F/ '{print $2}')
        echo -e "  状态: ${GREEN}运行中${NC}"
        echo -e "  版本: $NGINX_VERSION"
        
        # 获取连接数
        if command -v curl &> /dev/null; then
            ACTIVE_CONNS=$(curl -s http://127.0.0.1/health 2>/dev/null || echo "N/A")
            echo -e "  健康检查: ${GREEN}正常${NC}"
        fi
        
        log_info "Nginx 运行正常"
    else
        log_error "Nginx 未运行！"
        echo -e "  状态: ${RED}未运行${NC}"
        
        # 尝试重启
        echo -e "  ${YELLOW}尝试重启 Nginx...${NC}"
        systemctl start nginx 2>>"$LOG_FILE"
        if systemctl is-active --quiet nginx; then
            log_info "Nginx 已自动重启"
            echo -e "  ${GREEN}Nginx 已自动重启${NC}"
        else
            log_error "Nginx 重启失败"
            echo -e "  ${RED}重启失败${NC}"
        fi
    fi
    echo ""
}

# 检查 PHP-FPM 状态
check_php_fpm() {
    echo -e "${CYAN}【PHP-FPM 状态】${NC}"
    
    if systemctl is-active --quiet "$PHP_FPM_SERVICE"; then
        PHP_VERSION=$(php -v | head -n 1 | awk '{print $2}')
        echo -e "  状态: ${GREEN}运行中${NC}"
        echo -e "  版本: PHP $PHP_VERSION"
        
        # 检查 PHP-FPM 进程数
        FPM_PROCS=$(ps aux | grep "php-fpm" | grep -v grep | wc -l)
        echo -e "  进程数: $FPM_PROCS"
        
        log_info "PHP-FPM 运行正常，进程数: $FPM_PROCS"
    else
        log_error "PHP-FPM 未运行！"
        echo -e "  状态: ${RED}未运行${NC}"
        
        # 尝试重启
        echo -e "  ${YELLOW}尝试重启 PHP-FPM...${NC}"
        systemctl start "$PHP_FPM_SERVICE" 2>>"$LOG_FILE"
        if systemctl is-active --quiet "$PHP_FPM_SERVICE"; then
            log_info "PHP-FPM 已自动重启"
            echo -e "  ${GREEN}PHP-FPM 已自动重启${NC}"
        else
            log_error "PHP-FPM 重启失败"
            echo -e "  ${RED}重启失败${NC}"
        fi
    fi
    echo ""
}

# 检查 MySQL 状态
check_mysql() {
    echo -e "${CYAN}【MySQL 状态】${NC}"
    
    if systemctl is-active --quiet mysql; then
        MYSQL_VERSION=$(mysql --version | awk '{print $5}' | cut -d'-' -f1)
        echo -e "  状态: ${GREEN}运行中${NC}"
        echo -e "  版本: MySQL $MYSQL_VERSION"
        
        # 尝试连接数据库
        if mysql -h "$DB_HOST" -P "$DB_PORT" -u "$DB_USER" -p"$DB_PASS" -e "SELECT 1;" &>/dev/null; then
            echo -e "  数据库连接: ${GREEN}正常${NC}"
            
            # 获取连接数
            MAX_CONN=$(mysql -h "$DB_HOST" -P "$DB_PORT" -u "$DB_USER" -p"$DB_PASS" -e "SHOW VARIABLES LIKE 'max_connections';" 2>/dev/null | awk 'NR==2 {print $2}')
            CURRENT_CONN=$(mysql -h "$DB_HOST" -P "$DB_PORT" -u "$DB_USER" -p"$DB_PASS" -e "SHOW STATUS LIKE 'Threads_connected';" 2>/dev/null | awk 'NR==2 {print $2}')
            
            if [[ -n "$MAX_CONN" && -n "$CURRENT_CONN" ]]; then
                CONN_PERCENT=$((CURRENT_CONN * 100 / MAX_CONN))
                echo -e "  连接数: $CURRENT_CONN / $MAX_CONN (${CONN_PERCENT}%)"
                
                if [[ $CONN_PERCENT -ge $MYSQL_CONN_WARN ]]; then
                    log_warn "MySQL 连接数过高: ${CONN_PERCENT}%"
                fi
            fi
            
            log_info "MySQL 运行正常"
        else
            log_error "数据库连接失败！"
            echo -e "  数据库连接: ${RED}失败${NC}"
        fi
    else
        log_error "MySQL 未运行！"
        echo -e "  状态: ${RED}未运行${NC}"
        
        # 尝试重启
        echo -e "  ${YELLOW}尝试重启 MySQL...${NC}"
        systemctl start mysql 2>>"$LOG_FILE"
        if systemctl is-active --quiet mysql; then
            log_info "MySQL 已自动重启"
            echo -e "  ${GREEN}MySQL 已自动重启${NC}"
        else
            log_error "MySQL 重启失败"
            echo -e "  ${RED}重启失败${NC}"
        fi
    fi
    echo ""
}

# 检查 Redis 状态
check_redis() {
    if ! command -v redis-server &> /dev/null; then
        return
    fi
    
    echo -e "${CYAN}【Redis 状态】${NC}"
    
    if systemctl is-active --quiet redis-server; then
        REDIS_VERSION=$(redis-server --version | awk '{print $3}' | cut -d'=' -f2)
        echo -e "  状态: ${GREEN}运行中${NC}"
        echo -e "  版本: Redis $REDIS_VERSION"
        
        # 检查连接
        if redis-cli ping &>/dev/null; then
            echo -e "  连接: ${GREEN}正常${NC}"
            
            # 内存使用
            MEM_USED=$(redis-cli info memory 2>/dev/null | grep "used_memory_human" | cut -d: -f2 | tr -d '[:space:]')
            echo -e "  已用内存: $MEM_USED"
            
            log_info "Redis 运行正常"
        else
            log_warn "Redis 连接失败"
            echo -e "  连接: ${RED}失败${NC}"
        fi
    else
        log_warn "Redis 未运行"
        echo -e "  状态: ${YELLOW}未运行${NC}"
    fi
    echo ""
}

# 检查磁盘空间
check_disk() {
    echo -e "${CYAN}【磁盘空间】${NC}"
    
    # 检查根分区
    ROOT_USAGE=$(df -h / | awk 'NR==2 {print $5}' | tr -d '%')
    ROOT_USED=$(df -h / | awk 'NR==2 {print $3}')
    ROOT_TOTAL=$(df -h / | awk 'NR==2 {print $2}')
    ROOT_AVAIL=$(df -h / | awk 'NR==2 {print $4}')
    
    echo -e "  根分区 (/):"
    echo -e "    已用: $ROOT_USED / $ROOT_TOTAL"
    echo -e "    可用: $ROOT_AVAIL"
    echo -e "    使用率: ${ROOT_USAGE}%"
    
    if [[ $ROOT_USAGE -ge $DISK_USAGE_WARN ]]; then
        log_warn "根分区磁盘使用率过高: ${ROOT_USAGE}%"
        echo -e "    状态: ${YELLOW}告警${NC}"
    else
        echo -e "    状态: ${GREEN}正常${NC}"
    fi
    
    # 检查项目目录所在分区
    if [[ -d "$PROJECT_DIR" ]]; then
        PROJECT_USAGE=$(df -h "$PROJECT_DIR" | awk 'NR==2 {print $5}' | tr -d '%')
        PROJECT_PARTITION=$(df -h "$PROJECT_DIR" | awk 'NR==2 {print $1}')
        echo ""
        echo -e "  项目分区 ($PROJECT_PARTITION):"
        echo -e "    使用率: ${PROJECT_USAGE}%"
        
        if [[ $PROJECT_USAGE -ge $DISK_USAGE_WARN ]]; then
            log_warn "项目分区磁盘使用率过高: ${PROJECT_USAGE}%"
        fi
    fi
    
    # 大文件提示
    echo ""
    echo -e "  ${YELLOW}建议清理:${NC}"
    echo -e "    - /tmp 临时文件"
    echo -e "    - 日志文件: /var/log/"
    echo -e "    - 过期备份"
    
    log_info "磁盘检查完成，根分区使用率: ${ROOT_USAGE}%"
    echo ""
}

# 检查内存使用
check_memory() {
    echo -e "${CYAN}【内存使用】${NC}"
    
    # 获取内存信息
    MEM_TOTAL=$(free -m | awk 'NR==2 {print $2}')
    MEM_USED=$(free -m | awk 'NR==2 {print $3}')
    MEM_FREE=$(free -m | awk 'NR==2 {print $4}')
    MEM_AVAILABLE=$(free -m | awk 'NR==2 {print $7}')
    MEM_PERCENT=$((MEM_USED * 100 / MEM_TOTAL))
    
    echo -e "  总内存: ${MEM_TOTAL}MB"
    echo -e "  已使用: ${MEM_USED}MB"
    echo -e "  可用: ${MEM_AVAILABLE}MB"
    echo -e "  使用率: ${MEM_PERCENT}%"
    
    if [[ $MEM_PERCENT -ge $MEMORY_USAGE_WARN ]]; then
        log_warn "内存使用率过高: ${MEM_PERCENT}%"
        echo -e "  状态: ${YELLOW}告警${NC}"
    else
        echo -e "  状态: ${GREEN}正常${NC}"
    fi
    
    # Swap 使用情况
    SWAP_TOTAL=$(free -m | awk 'NR==3 {print $2}')
    if [[ $SWAP_TOTAL -gt 0 ]]; then
        SWAP_USED=$(free -m | awk 'NR==3 {print $3}')
        SWAP_PERCENT=$((SWAP_USED * 100 / SWAP_TOTAL))
        echo -e "  Swap: ${SWAP_USED}MB / ${SWAP_TOTAL}MB (${SWAP_PERCENT}%)"
        
        if [[ $SWAP_PERCENT -ge 50 ]]; then
            log_warn "Swap 使用过高: ${SWAP_PERCENT}%"
        fi
    fi
    
    log_info "内存检查完成，使用率: ${MEM_PERCENT}%"
    echo ""
}

# 检查 CPU
check_cpu() {
    echo -e "${CYAN}【CPU 负载】${NC}"
    
    # 获取 CPU 核数
    CPU_CORES=$(nproc)
    
    # 获取负载
    LOAD_1=$(uptime | awk -F'load average:' '{print $2}' | cut -d',' -f1 | tr -d ' ')
    LOAD_5=$(uptime | awk -F'load average:' '{print $2}' | cut -d',' -f2 | tr -d ' ')
    LOAD_15=$(uptime | awk -F'load average:' '{print $2}' | cut -d',' -f3 | tr -d ' ')
    
    echo -e "  CPU 核心数: $CPU_CORES"
    echo -e "  1分钟负载: $LOAD_1"
    echo -e "  5分钟负载: $LOAD_5"
    echo -e "  15分钟负载: $LOAD_15"
    
    # 计算负载百分比（以1分钟负载为基准）
    LOAD_PERCENT=$(echo "$LOAD_1 * 100 / $CPU_CORES" | bc 2>/dev/null || echo "0")
    
    if [[ ${LOAD_PERCENT%.*} -ge $CPU_USAGE_WARN ]]; then
        log_warn "CPU负载过高: ${LOAD_PERCENT}%"
        echo -e "  状态: ${YELLOW}告警${NC}"
    else
        echo -e "  状态: ${GREEN}正常${NC}"
    fi
    
    log_info "CPU检查完成，负载: $LOAD_1 (${LOAD_PERCENT}%)"
    echo ""
}

# 检查项目目录权限
check_permissions() {
    echo -e "${CYAN}【项目权限检查】${NC}"
    
    if [[ ! -d "$PROJECT_DIR" ]]; then
        log_warn "项目目录不存在: $PROJECT_DIR"
        echo -e "  项目目录: ${RED}不存在${NC}"
        return
    fi
    
    # 检查 runtime 目录
    RUNTIME_DIR="${PROJECT_DIR}/runtime"
    if [[ -d "$RUNTIME_DIR" ]]; then
        RUNTIME_OWNER=$(stat -c '%U' "$RUNTIME_DIR")
        RUNTIME_PERMS=$(stat -c '%a' "$RUNTIME_DIR")
        echo -e "  runtime 目录:"
        echo -e "    所有者: $RUNTIME_OWNER"
        echo -e "    权限: $RUNTIME_PERMS"
        
        if [[ "$RUNTIME_OWNER" != "www-data" ]]; then
            log_warn "runtime 目录所有者不是 www-data"
            echo -e "    状态: ${YELLOW}警告${NC}"
        else
            echo -e "    状态: ${GREEN}正常${NC}"
        fi
    fi
    
    # 检查 uploads 目录
    UPLOADS_DIR="${PROJECT_DIR}/public/uploads"
    if [[ -d "$UPLOADS_DIR" ]]; then
        UPLOADS_OWNER=$(stat -c '%U' "$UPLOADS_DIR")
        echo -e "  uploads 目录:"
        echo -e "    所有者: $UPLOADS_OWNER"
        
        if [[ "$UPLOADS_OWNER" != "www-data" ]]; then
            log_warn "uploads 目录所有者不是 www-data"
            echo -e "    状态: ${YELLOW}警告${NC}"
        else
            echo -e "    状态: ${GREEN}正常${NC}"
        fi
    fi
    
    # 检查 .env 文件
    ENV_FILE="${PROJECT_DIR}/.env"
    if [[ -f "$ENV_FILE" ]]; then
        ENV_PERMS=$(stat -c '%a' "$ENV_FILE")
        echo -e "  .env 文件:"
        echo -e "    权限: $ENV_PERMS"
        
        if [[ $ENV_PERMS -gt 644 ]]; then
            log_warn ".env 文件权限过高，建议设置为 644"
            echo -e "    状态: ${YELLOW}警告${NC}"
        else
            echo -e "    状态: ${GREEN}正常${NC}"
        fi
    fi
    
    log_info "权限检查完成"
    echo ""
}

# 执行一次完整检查
do_check() {
    echo ""
    echo -e "${CYAN}============================================================${NC}"
    echo -e "${GREEN}           智考AI 服务监控检查 - $(date '+%Y-%m-%d %H:%M:%S')${NC}"
    echo -e "${CYAN}============================================================${NC}"
    echo ""
    
    # 重置计数器
    WARN_COUNT=0
    ERROR_COUNT=0
    
    # 执行各项检查
    check_nginx
    check_php_fpm
    check_mysql
    check_redis
    check_disk
    check_memory
    check_cpu
    check_permissions
    
    # 汇总结果
    echo -e "${CYAN}============================================================${NC}"
    echo -e "检查结果: "
    
    if [[ $ERROR_COUNT -gt 0 ]]; then
        echo -e "  ${RED}错误: $ERROR_COUNT 个${NC}"
    else
        echo -e "  ${GREEN}错误: 0 个${NC}"
    fi
    
    if [[ $WARN_COUNT -gt 0 ]]; then
        echo -e "  ${YELLOW}警告: $WARN_COUNT 个${NC}"
    else
        echo -e "  ${GREEN}警告: 0 个${NC}"
    fi
    
    echo -e "${CYAN}============================================================${NC}"
    echo ""
}

# 守护进程模式
run_daemon() {
    log_info "监控脚本已守护进程模式启动，检查间隔: ${INTERVAL}秒"
    
    while true; do
        do_check
        sleep "$INTERVAL"
    done
}

# 主函数
main() {
    # 初始化日志
    mkdir -p "$(dirname "$LOG_FILE")"
    echo "=== 智考AI 监控日志 - $(date) ===" >> "$LOG_FILE"
    
    # 解析参数
    parse_args "$@"
    
    # 执行检查
    case "$MODE" in
        once)
            do_check
            ;;
        daemon)
            run_daemon
            ;;
        *)
            log_error "未知模式: $MODE"
            exit 1
            ;;
    esac
    
    # 根据检查结果设置退出码
    if [[ $ERROR_COUNT -gt 0 ]]; then
        exit 2
    elif [[ $WARN_COUNT -gt 0 ]]; then
        exit 1
    else
        exit 0
    fi
}

# 执行主函数
main "$@"
