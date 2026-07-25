#!/bin/bash
# ============================================================
# 智考AI - 服务器环境一键安装脚本
# 支持系统：Ubuntu 20.04 / 22.04
# 功能：安装 Nginx、PHP 8.1+、MySQL 8.0、Composer、Redis、配置防火墙
# ============================================================

set -e

# ===================== 可配置变量 =====================
PHP_VERSION="8.1"                      # PHP 版本
MYSQL_ROOT_PASSWORD="Zhikao@2024!"     # MySQL root 密码（安装后请及时修改）
INSTALL_REDIS=true                     # 是否安装 Redis
TIMEZONE="Asia/Shanghai"               # 时区
LOG_FILE="/var/log/zhikao_install.log" # 日志文件路径
# =======================================================

# 颜色定义
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
CYAN='\033[0;36m'
NC='\033[0m' # No Color

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
智考AI - 服务器环境一键安装脚本

用法: sudo bash $0 [选项]

选项:
    --help              显示此帮助信息
    --php-version       指定 PHP 版本 (默认: ${PHP_VERSION})
    --mysql-password    指定 MySQL root 密码 (默认: 随机生成)
    --no-redis          不安装 Redis
    --timezone          指定时区 (默认: ${TIMEZONE})

示例:
    sudo bash $0
    sudo bash $0 --php-version 8.2
    sudo bash $0 --no-redis
    sudo bash $0 --mysql-password "YourPassword123!"

注意:
    - 必须使用 root 或 sudo 权限运行
    - 仅支持 Ubuntu 20.04 / 22.04
    - 安装过程约 10-20 分钟，请耐心等待
EOF
}

# 解析命令行参数
parse_args() {
    while [[ $# -gt 0 ]]; do
        case $1 in
            --help)
                show_help
                exit 0
                ;;
            --php-version)
                PHP_VERSION="$2"
                shift 2
                ;;
            --mysql-password)
                MYSQL_ROOT_PASSWORD="$2"
                shift 2
                ;;
            --no-redis)
                INSTALL_REDIS=false
                shift
                ;;
            --timezone)
                TIMEZONE="$2"
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

# 检查是否为 root 用户
check_root() {
    if [[ $EUID -ne 0 ]]; then
        log_error "此脚本必须使用 root 或 sudo 权限运行"
        exit 1
    fi
}

# 检查系统版本
check_os() {
    if [[ -f /etc/os-release ]]; then
        . /etc/os-release
        OS_NAME="$ID"
        OS_VERSION="$VERSION_ID"
        
        if [[ "$OS_NAME" != "ubuntu" ]]; then
            log_error "此脚本仅支持 Ubuntu 系统，当前系统: $OS_NAME"
            exit 1
        fi
        
        if [[ "$OS_VERSION" != "20.04" && "$OS_VERSION" != "22.04" && "$OS_VERSION" != "24.04" ]]; then
            log_warn "当前系统版本 Ubuntu $OS_VERSION 未经完全测试，可能存在兼容性问题"
        fi
        
        log_info "系统版本: Ubuntu $OS_VERSION"
    else
        log_error "无法检测系统版本"
        exit 1
    fi
}

# 设置时区
set_timezone() {
    log_info "设置时区为 $TIMEZONE"
    timedatectl set-timezone "$TIMEZONE"
    log_info "时区设置完成"
}

# 更新系统包
update_system() {
    log_info "更新系统软件包..."
    apt-get update -y >> "$LOG_FILE" 2>&1
    apt-get upgrade -y >> "$LOG_FILE" 2>&1
    log_info "系统软件包更新完成"
}

# 安装基础工具
install_basic_tools() {
    log_info "安装基础工具..."
    apt-get install -y \
        curl \
        wget \
        vim \
        git \
        unzip \
        zip \
        htop \
        net-tools \
        software-properties-common \
        apt-transport-https \
        ca-certificates \
        gnupg \
        lsb-release >> "$LOG_FILE" 2>&1
    log_info "基础工具安装完成"
}

# 安装 Nginx
install_nginx() {
    log_info "安装 Nginx..."
    
    if command -v nginx &> /dev/null; then
        log_warn "Nginx 已安装，跳过"
        return
    fi
    
    apt-get install -y nginx >> "$LOG_FILE" 2>&1
    
    # 启动 Nginx 并设置开机自启
    systemctl enable nginx
    systemctl start nginx
    
    # 验证安装
    if systemctl is-active --quiet nginx; then
        log_info "Nginx 安装成功并已启动"
    else
        log_error "Nginx 启动失败"
        exit 1
    fi
}

# 安装 PHP
install_php() {
    log_info "安装 PHP ${PHP_VERSION}..."
    
    if command -v php &> /dev/null && php -v | head -n 1 | grep -q "$PHP_VERSION"; then
        log_warn "PHP ${PHP_VERSION} 已安装，跳过"
        return
    fi
    
    # 添加 PHP PPA 源
    add-apt-repository -y ppa:ondrej/php >> "$LOG_FILE" 2>&1
    apt-get update -y >> "$LOG_FILE" 2>&1
    
    # 安装 PHP 及扩展
    apt-get install -y \
        php${PHP_VERSION}-fpm \
        php${PHP_VERSION}-cli \
        php${PHP_VERSION}-mysql \
        php${PHP_VERSION}-mbstring \
        php${PHP_VERSION}-gd \
        php${PHP_VERSION}-curl \
        php${PHP_VERSION}-bcmath \
        php${PHP_VERSION}-xml \
        php${PHP_VERSION}-zip \
        php${PHP_VERSION}-intl \
        php${PHP_VERSION}-opcache \
        php${PHP_VERSION}-redis \
        php${PHP_VERSION}-imagick \
        php${PHP_VERSION}-soap \
        php${PHP_VERSION}-dev >> "$LOG_FILE" 2>&1
    
    # 启动 PHP-FPM 并设置开机自启
    systemctl enable php${PHP_VERSION}-fpm
    systemctl start php${PHP_VERSION}-fpm
    
    # 验证安装
    if systemctl is-active --quiet php${PHP_VERSION}-fpm; then
        log_info "PHP ${PHP_VERSION} 安装成功并已启动"
        php -v
    else
        log_error "PHP-FPM 启动失败"
        exit 1
    fi
}

# 配置 PHP
configure_php() {
    log_info "配置 PHP..."
    
    PHP_INI="/etc/php/${PHP_VERSION}/fpm/php.ini"
    
    # 备份原配置
    cp "$PHP_INI" "${PHP_INI}.bak"
    
    # 修改 PHP 配置
    sed -i 's/^upload_max_filesize = .*/upload_max_filesize = 50M/' "$PHP_INI"
    sed -i 's/^post_max_size = .*/post_max_size = 50M/' "$PHP_INI"
    sed -i 's/^max_execution_time = .*/max_execution_time = 300/' "$PHP_INI"
    sed -i 's/^max_input_time = .*/max_input_time = 300/' "$PHP_INI"
    sed -i 's/^memory_limit = .*/memory_limit = 256M/' "$PHP_INI"
    sed -i 's/^date.timezone = .*/date.timezone = '${TIMEZONE}'/' "$PHP_INI"
    
    # 配置 OPcache
    cat >> "$PHP_INI" << EOF

; [OPcache 配置]
opcache.enable=1
opcache.memory_consumption=128
opcache.interned_strings_buffer=8
opcache.max_accelerated_files=10000
opcache.revalidate_freq=60
opcache.fast_shutdown=1
opcache.save_comments=1
EOF
    
    # 重启 PHP-FPM
    systemctl restart php${PHP_VERSION}-fpm
    
    log_info "PHP 配置完成"
}

# 安装 MySQL 8.0
install_mysql() {
    log_info "安装 MySQL 8.0..."
    
    if command -v mysql &> /dev/null; then
        log_warn "MySQL 已安装，跳过"
        return
    fi
    
    # 安装 MySQL Server
    apt-get install -y mysql-server mysql-client >> "$LOG_FILE" 2>&1
    
    # 启动 MySQL 并设置开机自启
    systemctl enable mysql
    systemctl start mysql
    
    # 验证安装
    if systemctl is-active --quiet mysql; then
        log_info "MySQL 安装成功并已启动"
    else
        log_error "MySQL 启动失败"
        exit 1
    fi
    
    # 配置 MySQL root 密码
    log_info "配置 MySQL root 密码..."
    mysql -u root <<EOF
ALTER USER 'root'@'localhost' IDENTIFIED WITH mysql_native_password BY '${MYSQL_ROOT_PASSWORD}';
FLUSH PRIVILEGES;
EOF
    
    log_info "MySQL root 密码设置完成"
    log_warn "MySQL root 密码: ${MYSQL_ROOT_PASSWORD} (请妥善保管并及时修改)"
}

# 安全配置 MySQL
secure_mysql() {
    log_info "MySQL 安全配置..."
    
    mysql -u root -p"${MYSQL_ROOT_PASSWORD}" <<EOF
DELETE FROM mysql.user WHERE User='';
DELETE FROM mysql.user WHERE User='root' AND Host NOT IN ('localhost', '127.0.0.1', '::1');
DROP DATABASE IF EXISTS test;
DELETE FROM mysql.db WHERE Db='test' OR Db='test\\_%';
FLUSH PRIVILEGES;
EOF
    
    log_info "MySQL 安全配置完成"
}

# 安装 Composer
install_composer() {
    log_info "安装 Composer..."
    
    if command -v composer &> /dev/null; then
        log_warn "Composer 已安装，跳过"
        return
    fi
    
    # 下载 Composer 安装器
    php -r "copy('https://getcomposer.org/installer', 'composer-setup.php');"
    
    # 验证安装器
    HASH="$(wget -q -O - https://composer.github.io/installer.sig)"
    php -r "if (hash_file('sha384', 'composer-setup.php') === '$HASH') { echo 'Installer verified'; } else { echo 'Installer corrupt'; unlink('composer-setup.php'); } echo PHP_EOL;"
    
    # 安装 Composer
    php composer-setup.php --install-dir=/usr/local/bin --filename=composer >> "$LOG_FILE" 2>&1
    
    # 清理安装文件
    rm -f composer-setup.php
    
    # 配置国内镜像
    composer config -g repo.packagist composer https://mirrors.aliyun.com/composer/
    
    # 验证安装
    if command -v composer &> /dev/null; then
        log_info "Composer 安装成功"
        composer --version
    else
        log_error "Composer 安装失败"
        exit 1
    fi
}

# 安装 Redis
install_redis() {
    if [[ "$INSTALL_REDIS" != true ]]; then
        log_info "跳过 Redis 安装"
        return
    fi
    
    log_info "安装 Redis..."
    
    if command -v redis-server &> /dev/null; then
        log_warn "Redis 已安装，跳过"
        return
    fi
    
    apt-get install -y redis-server redis-tools >> "$LOG_FILE" 2>&1
    
    # 配置 Redis
    REDIS_CONF="/etc/redis/redis.conf"
    cp "$REDIS_CONF" "${REDIS_CONF}.bak"
    
    # 修改配置：设置密码、绑定地址
    sed -i 's/^# requirepass .*/requirepass ZhikaoRedis@2024/' "$REDIS_CONF"
    sed -i 's/^bind .*/bind 127.0.0.1/' "$REDIS_CONF"
    sed -i 's/^# maxmemory .*/maxmemory 256mb/' "$REDIS_CONF"
    sed -i 's/^# maxmemory-policy .*/maxmemory-policy allkeys-lru/' "$REDIS_CONF"
    
    # 启动 Redis 并设置开机自启
    systemctl enable redis-server
    systemctl restart redis-server
    
    # 验证安装
    if systemctl is-active --quiet redis-server; then
        log_info "Redis 安装成功并已启动"
        redis-cli ping
    else
        log_error "Redis 启动失败"
        exit 1
    fi
}

# 配置防火墙
configure_firewall() {
    log_info "配置防火墙..."
    
    # 检查 UFW 是否安装
    if ! command -v ufw &> /dev/null; then
        log_warn "UFW 防火墙未安装，正在安装..."
        apt-get install -y ufw >> "$LOG_FILE" 2>&1
    fi
    
    # 重置防火墙规则
    ufw --force reset
    
    # 默认策略
    ufw default deny incoming
    ufw default allow outgoing
    
    # 允许 SSH
    ufw allow 22/tcp
    log_info "已允许 SSH (22 端口)"
    
    # 允许 HTTP/HTTPS
    ufw allow 80/tcp
    ufw allow 443/tcp
    log_info "已允许 HTTP (80) / HTTPS (443)"
    
    # 启用防火墙
    ufw --force enable
    
    log_info "防火墙配置完成"
    ufw status numbered
}

# 显示安装摘要
show_summary() {
    echo ""
    echo -e "${CYAN}============================================================${NC}"
    echo -e "${GREEN}              智考AI 环境安装完成！${NC}"
    echo -e "${CYAN}============================================================${NC}"
    echo ""
    echo -e "${YELLOW}安装组件：${NC}"
    echo "  - Nginx: $(nginx -v 2>&1 | awk -F/ '{print $2}')"
    echo "  - PHP: $(php -v | head -n 1 | awk '{print $2}')"
    echo "  - PHP-FPM: php${PHP_VERSION}-fpm"
    echo "  - MySQL: $(mysql --version | awk '{print $5}' | cut -d'-' -f1)"
    echo "  - Composer: $(composer --version | awk '{print $3}')"
    
    if [[ "$INSTALL_REDIS" == true ]]; then
        echo "  - Redis: $(redis-server --version | awk '{print $3}' | cut -d'=' -f2)"
    fi
    
    echo ""
    echo -e "${YELLOW}重要信息：${NC}"
    echo "  MySQL root 密码: ${MYSQL_ROOT_PASSWORD}"
    
    if [[ "$INSTALL_REDIS" == true ]]; then
        echo "  Redis 密码: ZhikaoRedis@2024"
    fi
    
    echo ""
    echo -e "${YELLOW}服务状态：${NC}"
    echo "  Nginx: $(systemctl is-active nginx)"
    echo "  PHP-FPM: $(systemctl is-active php${PHP_VERSION}-fpm)"
    echo "  MySQL: $(systemctl is-active mysql)"
    
    if [[ "$INSTALL_REDIS" == true ]]; then
        echo "  Redis: $(systemctl is-active redis-server)"
    fi
    
    echo ""
    echo -e "${YELLOW}日志文件：${NC} ${LOG_FILE}"
    echo ""
    echo -e "${RED}安全提示：${NC}"
    echo "  1. 请立即修改 MySQL root 密码"
    echo "  2. 请立即修改 Redis 密码"
    echo "  3. 建议配置 SSL 证书启用 HTTPS"
    echo "  4. 定期更新系统和软件包"
    echo ""
    echo -e "${CYAN}============================================================${NC}"
}

# 主函数
main() {
    # 初始化日志文件
    mkdir -p "$(dirname "$LOG_FILE")"
    echo "=== 智考AI 环境安装日志 - $(date) ===" > "$LOG_FILE"
    
    # 解析参数
    parse_args "$@"
    
    # 前置检查
    check_root
    check_os
    
    echo ""
    echo -e "${CYAN}============================================================${NC}"
    echo -e "${GREEN}            欢迎使用智考AI 环境安装脚本${NC}"
    echo -e "${CYAN}============================================================${NC}"
    echo ""
    echo -e "PHP 版本:       ${YELLOW}${PHP_VERSION}${NC}"
    echo -e "安装 Redis:     ${YELLOW}${INSTALL_REDIS}${NC}"
    echo -e "时区:           ${YELLOW}${TIMEZONE}${NC}"
    echo ""
    echo -e "${YELLOW}安装过程约 10-20 分钟，请耐心等待...${NC}"
    echo ""
    
    # 执行安装步骤
    set_timezone
    update_system
    install_basic_tools
    install_nginx
    install_php
    configure_php
    install_mysql
    secure_mysql
    install_composer
    install_redis
    configure_firewall
    
    # 显示摘要
    show_summary
}

# 执行主函数
main "$@"
