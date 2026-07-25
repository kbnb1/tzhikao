#!/bin/bash
# ============================================================
# 智考AI - 项目一键部署脚本
# 功能：部署后端代码、配置数据库、设置Nginx、配置环境
# ============================================================

set -e

# ===================== 可配置变量 =====================
# 项目路径配置
PROJECT_DIR="/var/www/zhikao"          # 项目部署目录
BACKEND_SOURCE="/workspace/backend"     # 后端代码源路径
INTRO_SOURCE="/workspace/introduction"  # 介绍页源码路径
SQL_FILE="/workspace/database/install.sql" # 数据库安装SQL

# 数据库配置
DB_HOST="127.0.0.1"
DB_PORT="3306"
DB_NAME="zhikao"
DB_USER="zhikao"
DB_PASS="Zhikao@2024!"
DB_ROOT_PASS="Zhikao@2024Root!"

# 域名配置
DOMAIN="zhikao.example.com"
SERVER_IP=$(hostname -I | awk '{print $1}')

# PHP配置
PHP_VERSION="8.1"
PHP_FPM_SOCK="/run/php/php${PHP_VERSION}-fpm.sock"

# Nginx配置
NGINX_CONF_DIR="/etc/nginx/sites-available"
NGINX_ENABLED_DIR="/etc/nginx/sites-enabled"
NGINX_CONF_FILE="zhikao.conf"

# 日志文件
LOG_FILE="/var/log/zhikao_deploy.log"
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
智考AI - 项目一键部署脚本

用法: sudo bash $0 [选项]

选项:
    --help              显示此帮助信息
    --project-dir       项目部署目录 (默认: ${PROJECT_DIR})
    --domain            网站域名 (默认: ${DOMAIN})
    --db-name           数据库名 (默认: ${DB_NAME})
    --db-user           数据库用户名 (默认: ${DB_USER})
    --db-pass           数据库密码 (默认: ${DB_PASS})
    --db-root-pass      MySQL root 密码 (默认: ${DB_ROOT_PASS})
    --php-version       PHP 版本 (默认: ${PHP_VERSION})
    --skip-db           跳过数据库创建
    --skip-nginx        跳过 Nginx 配置

示例:
    sudo bash $0
    sudo bash $0 --domain www.zhikao.com
    sudo bash $0 --db-pass "YourPass123!" --skip-nginx
    sudo bash $0 --project-dir /data/www/zhikao

注意:
    - 必须使用 root 或 sudo 权限运行
    - 请先运行 install_env.sh 安装基础环境
    - 数据库 root 密码需要与安装时一致
EOF
}

# 解析命令行参数
parse_args() {
    SKIP_DB=false
    SKIP_NGINX=false
    
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
            --domain)
                DOMAIN="$2"
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
            --db-root-pass)
                DB_ROOT_PASS="$2"
                shift 2
                ;;
            --php-version)
                PHP_VERSION="$2"
                PHP_FPM_SOCK="/run/php/php${PHP_VERSION}-fpm.sock"
                shift 2
                ;;
            --skip-db)
                SKIP_DB=true
                shift
                ;;
            --skip-nginx)
                SKIP_NGINX=true
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

# 检查环境
check_env() {
    log_info "检查运行环境..."
    
    # 检查 PHP
    if ! command -v php &> /dev/null; then
        log_error "PHP 未安装，请先运行 install_env.sh"
        exit 1
    fi
    
    # 检查 MySQL
    if ! command -v mysql &> /dev/null; then
        log_error "MySQL 未安装，请先运行 install_env.sh"
        exit 1
    fi
    
    # 检查 Nginx
    if ! command -v nginx &> /dev/null; then
        log_error "Nginx 未安装，请先运行 install_env.sh"
        exit 1
    fi
    
    # 检查 Composer
    if ! command -v composer &> /dev/null; then
        log_error "Composer 未安装，请先运行 install_env.sh"
        exit 1
    fi
    
    # 检查源目录
    if [[ ! -d "$BACKEND_SOURCE" ]]; then
        log_error "后端源码目录不存在: $BACKEND_SOURCE"
        exit 1
    fi
    
    if [[ ! -f "$SQL_FILE" ]]; then
        log_error "数据库SQL文件不存在: $SQL_FILE"
        exit 1
    fi
    
    log_info "环境检查通过"
}

# 创建项目目录
create_project_dir() {
    log_info "创建项目目录..."
    
    if [[ -d "$PROJECT_DIR" ]]; then
        log_warn "项目目录已存在: $PROJECT_DIR"
        read -p "是否覆盖？(y/N): " confirm
        if [[ "$confirm" != "y" && "$confirm" != "Y" ]]; then
            log_info "取消部署"
            exit 0
        fi
        log_warn "将覆盖现有项目"
    fi
    
    mkdir -p "$PROJECT_DIR"
    log_info "项目目录创建完成: $PROJECT_DIR"
}

# 复制后端代码
copy_backend_code() {
    log_info "复制后端代码..."
    
    # 复制代码（排除不需要的目录）
    rsync -av --exclude='.git' \
              --exclude='runtime/*' \
              --exclude='.env' \
              "$BACKEND_SOURCE/" "$PROJECT_DIR/" >> "$LOG_FILE" 2>&1
    
    log_info "后端代码复制完成"
}

# 安装 Composer 依赖
install_composer_deps() {
    log_info "安装 Composer 依赖..."
    
    cd "$PROJECT_DIR"
    
    # 检查 composer.json 是否存在
    if [[ ! -f "composer.json" ]]; then
        log_error "composer.json 不存在"
        exit 1
    fi
    
    # 安装依赖
    composer install --no-dev --optimize-autoloader >> "$LOG_FILE" 2>&1
    
    log_info "Composer 依赖安装完成"
}

# 设置目录权限
set_permissions() {
    log_info "设置目录权限..."
    
    # 创建需要的目录
    mkdir -p "$PROJECT_DIR/runtime"
    mkdir -p "$PROJECT_DIR/public/uploads"
    
    # 设置目录权限
    chown -R www-data:www-data "$PROJECT_DIR"
    chmod -R 755 "$PROJECT_DIR"
    
    # 设置可写目录权限
    chmod -R 775 "$PROJECT_DIR/runtime"
    chmod -R 775 "$PROJECT_DIR/public/uploads"
    
    log_info "目录权限设置完成"
}

# 创建数据库
create_database() {
    if [[ "$SKIP_DB" == true ]]; then
        log_info "跳过数据库创建"
        return
    fi
    
    log_info "创建数据库..."
    
    # 检查数据库是否已存在
    DB_EXISTS=$(mysql -u root -p"${DB_ROOT_PASS}" -e "SHOW DATABASES LIKE '${DB_NAME}';" 2>/dev/null | grep -q "$DB_NAME" && echo "yes" || echo "no")
    
    if [[ "$DB_EXISTS" == "yes" ]]; then
        log_warn "数据库已存在: $DB_NAME"
        read -p "是否删除并重建？(y/N): " confirm
        if [[ "$confirm" != "y" && "$confirm" != "Y" ]]; then
            log_info "保留现有数据库"
            return
        fi
        mysql -u root -p"${DB_ROOT_PASS}" -e "DROP DATABASE ${DB_NAME};" 2>/dev/null
        log_info "已删除旧数据库"
    fi
    
    # 创建数据库
    mysql -u root -p"${DB_ROOT_PASS}" <<EOF
CREATE DATABASE ${DB_NAME} DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
CREATE USER IF NOT EXISTS '${DB_USER}'@'localhost' IDENTIFIED BY '${DB_PASS}';
GRANT ALL PRIVILEGES ON ${DB_NAME}.* TO '${DB_USER}'@'localhost';
FLUSH PRIVILEGES;
EOF
    
    log_info "数据库创建成功: $DB_NAME"
    log_info "数据库用户: $DB_USER"
}

# 导入数据库
import_database() {
    if [[ "$SKIP_DB" == true ]]; then
        log_info "跳过数据库导入"
        return
    fi
    
    log_info "导入数据库..."
    
    mysql -u "${DB_USER}" -p"${DB_PASS}" "${DB_NAME}" < "$SQL_FILE" 2>/dev/null
    
    # 检查导入是否成功
    if [[ $? -eq 0 ]]; then
        log_info "数据库导入成功"
    else
        log_error "数据库导入失败"
        exit 1
    fi
}

# 配置 .env 文件
configure_env() {
    log_info "配置 .env 文件..."
    
    ENV_FILE="$PROJECT_DIR/.env"
    
    # 复制示例文件
    if [[ -f "$PROJECT_DIR/.example.env" ]]; then
        cp "$PROJECT_DIR/.example.env" "$ENV_FILE"
    else
        # 创建新的 .env 文件
        cat > "$ENV_FILE" << 'EOF'
APP_DEBUG = false

[DATABASE]
TYPE = mysql
HOSTNAME = 127.0.0.1
DATABASE = test
USERNAME = username
PASSWORD = password
HOSTPORT = 3306
CHARSET = utf8mb4
PREFIX = 

[LANG]
DEFAULT_LANG = zh-cn
EOF
    fi
    
    # 替换数据库配置
    sed -i "s/DB_HOST = .*/DB_HOST = ${DB_HOST}/" "$ENV_FILE"
    sed -i "s/DB_NAME = .*/DB_NAME = ${DB_NAME}/" "$ENV_FILE"
    sed -i "s/DB_USER = .*/DB_USER = ${DB_USER}/" "$ENV_FILE"
    sed -i "s/DB_PASS = .*/DB_PASS = ${DB_PASS}/" "$ENV_FILE"
    sed -i "s/DB_PORT = .*/DB_PORT = ${DB_PORT}/" "$ENV_FILE"
    
    # 关闭调试模式
    sed -i 's/APP_DEBUG = true/APP_DEBUG = false/' "$ENV_FILE"
    
    # 设置文件权限
    chown www-data:www-data "$ENV_FILE"
    chmod 644 "$ENV_FILE"
    
    log_info ".env 文件配置完成"
}

# 复制介绍页
copy_introduction_page() {
    log_info "复制介绍页面..."
    
    if [[ ! -d "$INTRO_SOURCE" ]]; then
        log_warn "介绍页源码目录不存在: $INTRO_SOURCE，跳过"
        return
    fi
    
    # 创建介绍页目录
    INTRO_DEST="$PROJECT_DIR/public/intro"
    mkdir -p "$INTRO_DEST"
    
    # 复制介绍页
    cp -r "$INTRO_SOURCE/"* "$INTRO_DEST/"
    
    chown -R www-data:www-data "$INTRO_DEST"
    
    log_info "介绍页面复制完成: $INTRO_DEST"
}

# 配置 Nginx
configure_nginx() {
    if [[ "$SKIP_NGINX" == true ]]; then
        log_info "跳过 Nginx 配置"
        return
    fi
    
    log_info "配置 Nginx..."
    
    # 获取脚本所在目录
    SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
    TEMPLATE_CONF="$SCRIPT_DIR/nginx/zhikao.conf"
    
    if [[ ! -f "$TEMPLATE_CONF" ]]; then
        log_error "Nginx 配置模板不存在: $TEMPLATE_CONF"
        exit 1
    fi
    
    # 复制配置模板
    cp "$TEMPLATE_CONF" "${NGINX_CONF_DIR}/${NGINX_CONF_FILE}"
    
    # 替换变量
    sed -i "s|{{DOMAIN}}|${DOMAIN}|g" "${NGINX_CONF_DIR}/${NGINX_CONF_FILE}"
    sed -i "s|{{SERVER_IP}}|${SERVER_IP}|g" "${NGINX_CONF_DIR}/${NGINX_CONF_FILE}"
    sed -i "s|{{PROJECT_DIR}}|${PROJECT_DIR}|g" "${NGINX_CONF_DIR}/${NGINX_CONF_FILE}"
    sed -i "s|{{PHP_FPM_SOCK}}|${PHP_FPM_SOCK}|g" "${NGINX_CONF_DIR}/${NGINX_CONF_FILE}"
    
    # 启用站点
    ln -sf "${NGINX_CONF_DIR}/${NGINX_CONF_FILE}" "${NGINX_ENABLED_DIR}/${NGINX_CONF_FILE}"
    
    # 删除默认站点
    if [[ -f "${NGINX_ENABLED_DIR}/default" ]]; then
        rm -f "${NGINX_ENABLED_DIR}/default"
        log_info "已移除默认站点"
    fi
    
    # 测试 Nginx 配置
    if nginx -t >> "$LOG_FILE" 2>&1; then
        systemctl reload nginx
        log_info "Nginx 配置完成并已重载"
    else
        log_error "Nginx 配置测试失败"
        nginx -t
        exit 1
    fi
}

# 生成密钥
generate_app_key() {
    log_info "生成应用密钥..."
    
    cd "$PROJECT_DIR"
    
    # 生成随机密钥
    APP_KEY=$(head -c 32 /dev/urandom | base64 | tr -d '\n')
    
    # 添加到 .env
    if ! grep -q "APP_KEY" "$PROJECT_DIR/.env"; then
        echo "" >> "$PROJECT_DIR/.env"
        echo "APP_KEY = ${APP_KEY}" >> "$PROJECT_DIR/.env"
        log_info "应用密钥已生成"
    else
        log_warn "APP_KEY 已存在，跳过"
    fi
}

# 显示部署摘要
show_summary() {
    echo ""
    echo -e "${CYAN}============================================================${NC}"
    echo -e "${GREEN}              智考AI 项目部署完成！${NC}"
    echo -e "${CYAN}============================================================${NC}"
    echo ""
    echo -e "${YELLOW}项目信息：${NC}"
    echo "  项目目录: $PROJECT_DIR"
    echo "  访问域名: http://${DOMAIN}"
    echo "  服务器IP: ${SERVER_IP}"
    echo ""
    echo -e "${YELLOW}数据库信息：${NC}"
    echo "  数据库名: $DB_NAME"
    echo "  用户名:   $DB_USER"
    echo "  密码:     $DB_PASS"
    echo "  主机:     $DB_HOST:$DB_PORT"
    echo ""
    echo -e "${YELLOW}访问地址：${NC}"
    echo "  首页:      http://${DOMAIN}/"
    echo "  介绍页:    http://${DOMAIN}/intro/"
    echo "  API地址:   http://${DOMAIN}/api/v1/"
    echo "  管理后台:  http://${DOMAIN}/admin/"
    echo ""
    echo -e "${YELLOW}服务状态：${NC}"
    echo "  Nginx:    $(systemctl is-active nginx)"
    echo "  PHP-FPM:  $(systemctl is-active php${PHP_VERSION}-fpm)"
    echo "  MySQL:    $(systemctl is-active mysql)"
    echo ""
    echo -e "${YELLOW}日志文件：${NC} ${LOG_FILE}"
    echo ""
    echo -e "${RED}安全提示：${NC}"
    echo "  1. 请及时修改数据库密码"
    echo "  2. 建议配置 SSL 证书启用 HTTPS"
    echo "  3. 请修改 .env 中的 APP_KEY"
    echo "  4. 请配置管理后台管理员账号"
    echo ""
    echo -e "${CYAN}============================================================${NC}"
}

# 主函数
main() {
    # 初始化日志文件
    mkdir -p "$(dirname "$LOG_FILE")"
    echo "=== 智考AI 项目部署日志 - $(date) ===" > "$LOG_FILE"
    
    # 解析参数
    parse_args "$@"
    
    # 前置检查
    check_root
    check_env
    
    echo ""
    echo -e "${CYAN}============================================================${NC}"
    echo -e "${GREEN}            欢迎使用智考AI 项目部署脚本${NC}"
    echo -e "${CYAN}============================================================${NC}"
    echo ""
    echo -e "项目目录:       ${YELLOW}${PROJECT_DIR}${NC}"
    echo -e "域名:           ${YELLOW}${DOMAIN}${NC}"
    echo -e "数据库:         ${YELLOW}${DB_NAME}${NC}"
    echo -e "PHP版本:        ${YELLOW}${PHP_VERSION}${NC}"
    echo ""
    echo -e "${YELLOW}部署过程约 3-5 分钟，请耐心等待...${NC}"
    echo ""
    
    # 执行部署步骤
    create_project_dir
    copy_backend_code
    install_composer_deps
    set_permissions
    create_database
    import_database
    configure_env
    generate_app_key
    copy_introduction_page
    configure_nginx
    
    # 显示摘要
    show_summary
}

# 执行主函数
main "$@"
