# 智考AI - 部署运维文档

## 目录

- [1. 环境要求](#1-环境要求)
- [2. 快速部署](#2-快速部署)
- [3. 目录结构说明](#3-目录结构说明)
- [4. 脚本使用说明](#4-脚本使用说明)
- [5. 常见问题排查](#5-常见问题排查)
- [6. 安全建议](#6-安全建议)
- [7. 性能优化建议](#7-性能优化建议)
- [8. 附录](#8-附录)

---

## 1. 环境要求

### 1.1 系统要求

| 项目 | 最低要求 | 推荐配置 |
|------|----------|----------|
| 操作系统 | Ubuntu 20.04 LTS | Ubuntu 22.04 LTS |
| CPU | 2 核 | 4 核及以上 |
| 内存 | 2 GB | 4 GB 及以上 |
| 磁盘 | 20 GB | 50 GB SSD 及以上 |
| 网络 | 1 Mbps | 5 Mbps 及以上 |

### 1.2 软件版本

| 软件 | 版本要求 | 说明 |
|------|----------|------|
| Nginx | 1.18+ | Web 服务器 |
| PHP | 8.1+ | 后端运行环境 |
| MySQL | 8.0+ | 数据库 |
| Redis | 6.0+ | 缓存（可选） |
| Composer | 2.0+ | PHP 依赖管理 |

### 1.3 PHP 扩展要求

必需扩展：
- `pdo_mysql` - MySQL 数据库驱动
- `mbstring` - 多字节字符串处理
- `gd` - 图像处理
- `curl` - HTTP 请求
- `bcmath` - 高精度数学运算
- `opcache` - PHP  opcode 缓存
- `xml` - XML 解析
- `zip` - ZIP 压缩

可选扩展：
- `redis` - Redis 缓存支持
- `imagick` - 高级图像处理
- `intl` - 国际化支持

---

## 2. 快速部署

### 2.1 一键环境安装

```bash
# 1. 进入 deploy 目录
cd /workspace/deploy

# 2. 给脚本添加执行权限
chmod +x install_env.sh

# 3. 运行环境安装脚本
sudo bash install_env.sh

# 或者指定参数
sudo bash install_env.sh --php-version 8.2 --mysql-password "YourPassword123!"
```

安装完成后，请：
- 记录 MySQL root 密码
- 记录 Redis 密码（如安装）
- 立即修改默认密码

### 2.2 项目部署

```bash
# 1. 给部署脚本添加执行权限
chmod +x deploy_project.sh

# 2. 运行部署脚本
sudo bash deploy_project.sh --domain yourdomain.com

# 或者使用默认配置
sudo bash deploy_project.sh
```

部署完成后访问：
- 首页：`http://yourdomain.com/`
- 介绍页：`http://yourdomain.com/intro/`
- API 地址：`http://yourdomain.com/api/v1/`

### 2.3 SSL 配置（推荐）

使用 Let's Encrypt 免费证书：

```bash
# 安装 Certbot
sudo apt install certbot python3-certbot-nginx -y

# 申请证书
sudo certbot --nginx -d yourdomain.com -d www.yourdomain.com

# 自动续期（已默认配置）
sudo certbot renew --dry-run
```

---

## 3. 目录结构说明

### 3.1 项目目录结构

```
/var/www/zhikao/
├── app/                    # 应用目录
│   ├── controller/         # 控制器
│   │   ├── admin/          # 管理后台控制器
│   │   └── api/            # API 控制器
│   ├── model/              # 模型
│   ├── service/            # 服务层
│   ├── middleware/         # 中间件
│   ├── validate/           # 验证器
│   ├── utils/              # 工具类
│   └── exception/          # 异常处理
├── config/                 # 配置文件目录
├── public/                 # Web 可访问目录
│   ├── index.php           # 入口文件
│   ├── intro/              # 介绍页面（静态）
│   ├── uploads/            # 上传文件目录
│   └── static/             # 静态资源
├── route/                  # 路由配置
├── runtime/                # 运行时目录（缓存、日志）
├── vendor/                 # Composer 依赖
├── .env                    # 环境配置文件
├── composer.json           # Composer 配置
└── think                   # ThinkPHP 命令行工具
```

### 3.2 部署脚本目录

```
/workspace/deploy/
├── install_env.sh          # 环境安装脚本
├── deploy_project.sh       # 项目部署脚本
├── backup.sh               # 数据备份脚本
├── monitor.sh              # 服务监控脚本
├── update.sh               # 一键更新脚本
├── nginx/
│   └── zhikao.conf         # Nginx 配置模板
└── README.md               # 本文档
```

### 3.3 日志目录

```
/var/log/
├── nginx/
│   ├── zhikao_access.log   # Nginx 访问日志
│   └── zhikao_error.log    # Nginx 错误日志
├── zhikao_install.log      # 环境安装日志
├── zhikao_deploy.log       # 项目部署日志
├── zhikao_backup.log       # 备份日志
├── zhikao_monitor.log      # 监控日志
└── zhikao_update.log       # 更新日志
```

---

## 4. 脚本使用说明

### 4.1 install_env.sh - 环境安装脚本

**功能**：一键安装 Nginx、PHP、MySQL、Redis、Composer 等基础环境。

**用法**：
```bash
sudo bash install_env.sh [选项]
```

**选项**：

| 选项 | 说明 | 默认值 |
|------|------|--------|
| `--help` | 显示帮助信息 | - |
| `--php-version` | 指定 PHP 版本 | 8.1 |
| `--mysql-password` | MySQL root 密码 | 随机生成 |
| `--no-redis` | 不安装 Redis | 安装 |
| `--timezone` | 设置时区 | Asia/Shanghai |

**示例**：
```bash
# 默认安装
sudo bash install_env.sh

# 指定 PHP 8.2
sudo bash install_env.sh --php-version 8.2

# 不安装 Redis
sudo bash install_env.sh --no-redis

# 指定 MySQL 密码
sudo bash install_env.sh --mysql-password "MySecurePass123!"
```

### 4.2 deploy_project.sh - 项目部署脚本

**功能**：部署后端代码、配置数据库、设置 Nginx、配置环境变量。

**用法**：
```bash
sudo bash deploy_project.sh [选项]
```

**选项**：

| 选项 | 说明 | 默认值 |
|------|------|--------|
| `--help` | 显示帮助信息 | - |
| `--project-dir` | 项目部署目录 | /var/www/zhikao |
| `--domain` | 网站域名 | zhikao.example.com |
| `--db-name` | 数据库名 | zhikao |
| `--db-user` | 数据库用户名 | zhikao |
| `--db-pass` | 数据库密码 | Zhikao@2024! |
| `--db-root-pass` | MySQL root 密码 | Zhikao@2024Root! |
| `--php-version` | PHP 版本 | 8.1 |
| `--skip-db` | 跳过数据库创建 | 否 |
| `--skip-nginx` | 跳过 Nginx 配置 | 否 |

### 4.3 backup.sh - 数据备份脚本

**功能**：备份数据库、上传文件、配置文件。

**用法**：
```bash
bash backup.sh [选项]
```

**选项**：

| 选项 | 说明 | 默认值 |
|------|------|--------|
| `--help` | 显示帮助信息 | - |
| `--backup-dir` | 备份存储目录 | /data/backup/zhikao |
| `--retention-days` | 保留天数 | 30 |
| `--no-db` | 不备份数据库 | 否 |
| `--no-uploads` | 不备份上传文件 | 否 |
| `--no-config` | 不备份配置文件 | 否 |
| `--with-code` | 同时备份代码 | 否 |
| `--no-compress` | 不压缩备份 | 否 |
| `--list` | 列出所有备份 | - |
| `--restore <文件>` | 恢复指定备份 | - |
| `--clean` | 清理过期备份 | - |

**示例**：
```bash
# 执行备份
bash backup.sh

# 列出备份
bash backup.sh --list

# 恢复备份
bash backup.sh --restore /data/backup/zhikao/zhikao_backup_20240101_120000.tar.gz

# 清理过期备份
bash backup.sh --clean
```

**定时备份配置**：
```bash
# 编辑 crontab
crontab -e

# 每天凌晨 2 点执行备份
0 2 * * * /path/to/backup.sh >> /var/log/zhikao_cron_backup.log 2>&1
```

### 4.4 monitor.sh - 服务监控脚本

**功能**：监控 Nginx、PHP-FPM、MySQL、Redis、磁盘、内存、CPU 等。

**用法**：
```bash
bash monitor.sh [选项]
```

**选项**：

| 选项 | 说明 | 默认值 |
|------|------|--------|
| `--help` | 显示帮助信息 | - |
| `--once` | 只运行一次检查 | 是 |
| `--daemon` | 守护进程模式 | 否 |
| `--interval` | 检查间隔（秒） | 60 |
| `--disk-warn` | 磁盘告警阈值(%) | 80 |
| `--memory-warn` | 内存告警阈值(%) | 80 |
| `--cpu-warn` | CPU 告警阈值(%) | 80 |

**监控项**：
- Nginx 服务状态（自动重启）
- PHP-FPM 服务状态（自动重启）
- MySQL 服务状态及连接数（自动重启）
- Redis 服务状态及内存使用
- 磁盘空间使用率
- 内存使用率及 Swap
- CPU 负载
- 项目目录权限检查

**示例**：
```bash
# 单次检查
bash monitor.sh

# 守护进程模式，30秒检查一次
bash monitor.sh --daemon --interval 30

# 自定义告警阈值
bash monitor.sh --disk-warn 90 --memory-warn 85
```

### 4.5 update.sh - 一键更新脚本

**功能**：从 Git 拉取代码、更新依赖、执行迁移、清理缓存、平滑重启。

**用法**：
```bash
sudo bash update.sh [选项]
```

**选项**：

| 选项 | 说明 | 默认值 |
|------|------|--------|
| `--help` | 显示帮助信息 | - |
| `--project-dir` | 项目目录 | /var/www/zhikao |
| `--branch` | Git 分支 | main |
| `--remote` | Git 远程仓库 | origin |
| `--no-backup` | 更新前不备份 | 备份 |
| `--no-migration` | 不执行数据库迁移 | 执行 |
| `--no-cache-clear` | 不清理缓存 | 清理 |
| `--no-composer` | 不更新依赖 | 更新 |
| `--no-git` | 不从 Git 拉取 | 拉取 |
| `--dry-run` | 演练模式 | 否 |
| `--rollback` | 回滚到上一版本 | - |

**更新流程**：
1. 检查环境
2. 开启维护模式
3. 更新前备份（可选）
4. 从 Git 拉取最新代码
5. 更新 Composer 依赖
6. 执行数据库迁移
7. 清理运行时缓存
8. 平滑重启 PHP-FPM
9. 关闭维护模式
10. 验证更新

**示例**：
```bash
# 正常更新
sudo bash update.sh

# 演练模式（不实际修改）
sudo bash update.sh --dry-run

# 指定分支
sudo bash update.sh --branch dev

# 快速更新（不备份、不迁移）
sudo bash update.sh --no-backup --no-migration

# 回滚到上一版本
sudo bash update.sh --rollback
```

---

## 5. 常见问题排查

### 5.1 Nginx 相关

**问题：Nginx 启动失败**

排查步骤：
```bash
# 1. 检查配置语法
nginx -t

# 2. 查看错误日志
tail -f /var/log/nginx/error.log

# 3. 检查端口占用
netstat -tlnp | grep :80
```

**问题：404 Not Found**

可能原因：
- ThinkPHP 重写规则未配置
- 文件路径不正确
- PHP-FPM 未运行

解决方法：
```bash
# 检查 Nginx 配置中的 root 路径
# 确保 .htaccess 或 Nginx 重写规则正确
# 检查 PHP-FPM 状态
systemctl status php8.1-fpm
```

### 5.2 PHP 相关

**问题：502 Bad Gateway**

排查步骤：
```bash
# 1. 检查 PHP-FPM 状态
systemctl status php8.1-fpm

# 2. 查看 PHP-FPM 日志
tail -f /var/log/php8.1-fpm.log

# 3. 检查 Nginx 配置中的 fastcgi_pass
```

**问题：文件上传失败**

可能原因：
- 上传大小限制
- 目录权限不足
- 磁盘空间不足

解决方法：
```bash
# 检查 PHP 配置
php -i | grep upload_max_filesize
php -i | grep post_max_size

# 修改 php.ini
# upload_max_filesize = 50M
# post_max_size = 50M

# 检查目录权限
ls -la /var/www/zhikao/public/uploads/

# 修改权限
chown -R www-data:www-data /var/www/zhikao/public/uploads
chmod -R 775 /var/www/zhikao/public/uploads
```

### 5.3 数据库相关

**问题：数据库连接失败**

排查步骤：
```bash
# 1. 检查 MySQL 状态
systemctl status mysql

# 2. 测试连接
mysql -u zhikao -p -h 127.0.0.1 zhikao

# 3. 检查 .env 配置
cat /var/www/zhikao/.env
```

**问题：数据库导入失败**

可能原因：
- SQL 文件路径错误
- 数据库不存在
- 权限不足

解决方法：
```bash
# 手动导入
mysql -u root -p
CREATE DATABASE zhikao DEFAULT CHARACTER SET utf8mb4;
USE zhikao;
SOURCE /workspace/database/install.sql;
```

### 5.4 权限相关

**问题：runtime 目录不可写**

```bash
# 设置正确的所有者
chown -R www-data:www-data /var/www/zhikao/runtime
chmod -R 775 /var/www/zhikao/runtime

# 检查 SELinux（如启用）
getenforce
```

### 5.5 缓存相关

**问题：代码更新后不生效**

```bash
# 清理 ThinkPHP 缓存
cd /var/www/zhikao
php think clear

# 清理 OPcache
systemctl reload php8.1-fpm

# 清理文件缓存
rm -rf runtime/cache/*
rm -rf runtime/temp/*
```

---

## 6. 安全建议

### 6.1 系统安全

1. **修改默认密码**
   - MySQL root 密码
   - Redis 密码
   - 系统用户密码

2. **配置防火墙**
   - 只开放必要端口（22, 80, 443）
   - 限制 SSH 访问 IP

   ```bash
   # 只允许特定 IP 访问 SSH
   ufw allow from 192.168.1.0/24 to any port 22
   ```

3. **禁用 root 远程登录**
   ```bash
   # 编辑 /etc/ssh/sshd_config
   PermitRootLogin no
   
   # 重启 SSH
   systemctl restart sshd
   ```

4. **使用密钥登录**
   ```bash
   # 生成密钥对
   ssh-keygen -t ed25519
   
   # 上传公钥到服务器
   ssh-copy-id user@yourserver
   ```

5. **定期更新系统**
   ```bash
   apt update && apt upgrade -y
   ```

### 6.2 应用安全

1. **关闭调试模式**
   ```env
   APP_DEBUG = false
   ```

2. **配置 HTTPS**
   - 启用 SSL 证书
   - 强制 HTTP 跳转到 HTTPS
   - 配置 HSTS

3. **保护敏感文件**
   - `.env` 文件权限设为 644
   - 禁止 Web 访问 `.git` 目录
   - 禁止访问配置文件

4. **输入验证**
   - 所有用户输入都要验证
   - 使用参数化查询防止 SQL 注入
   - 对输出进行 HTML 转义

5. **文件上传安全**
   - 限制上传文件类型
   - 限制上传文件大小
   - 上传目录禁止执行 PHP

### 6.3 数据库安全

1. **使用独立数据库用户**
   - 不要使用 root 用户连接应用
   - 只授予必要权限

2. **定期备份**
   - 配置自动备份
   - 备份文件加密存储
   - 定期测试恢复

3. **限制远程访问**
   - MySQL 只监听本地
   - 如需远程访问，限制 IP

   ```bash
   # 编辑 /etc/mysql/mysql.conf.d/mysqld.cnf
   bind-address = 127.0.0.1
   ```

### 6.4 日志与监控

1. **启用访问日志和错误日志**
2. **定期检查日志**
3. **配置告警机制**
4. **监控异常访问**

---

## 7. 性能优化建议

### 7.1 Nginx 优化

1. **启用 Gzip 压缩**
   - 已在配置中启用

2. **静态资源缓存**
   - 图片：30 天
   - CSS/JS：7 天
   - 字体：30 天

3. **开启长连接**
   ```nginx
   keepalive_timeout 65;
   ```

4. **worker 进程优化**
   ```nginx
   worker_processes auto;
   worker_connections 65535;
   ```

### 7.2 PHP 优化

1. **启用 OPcache**
   ```ini
   opcache.enable=1
   opcache.memory_consumption=128
   opcache.max_accelerated_files=10000
   opcache.revalidate_freq=60
   ```

2. **调整 PHP-FPM 进程数**
   ```ini
   pm = dynamic
   pm.max_children = 50
   pm.start_servers = 10
   pm.min_spare_servers = 5
   pm.max_spare_servers = 20
   pm.max_requests = 500
   ```

3. **调整内存限制**
   ```ini
   memory_limit = 256M
   ```

### 7.3 数据库优化

1. **启用查询缓存**
   ```ini
   query_cache_type = 1
   query_cache_size = 64M
   ```

2. **调整连接数**
   ```ini
   max_connections = 200
   ```

3. **InnoDB 优化**
   ```ini
   innodb_buffer_pool_size = 1G
   innodb_log_file_size = 256M
   innodb_flush_log_at_trx_commit = 2
   ```

4. **慢查询日志**
   ```ini
   slow_query_log = 1
   slow_query_log_file = /var/log/mysql/slow.log
   long_query_time = 2
   ```

5. **定期优化表**
   ```bash
   mysqlcheck -o zhikao
   ```

### 7.4 缓存策略

1. **使用 Redis 缓存**
   - 缓存热点数据
   - 缓存数据库查询结果
   - 缓存 Session

2. **页面缓存**
   - 静态页面缓存
   - API 响应缓存

3. **浏览器缓存**
   - 设置合适的 Cache-Control
   - 使用版本号刷新静态资源

### 7.5 代码优化

1. **使用 Composer 自动加载优化**
   ```bash
   composer install --optimize-autoloader --no-dev
   ```

2. **数据库查询优化**
   - 添加合适的索引
   - 避免 N+1 查询
   - 使用批量操作

3. **减少文件 I/O**
   - 合理使用缓存
   - 避免频繁读写文件

---

## 8. 附录

### 8.1 常用命令速查

**服务管理**：
```bash
# Nginx
systemctl start nginx      # 启动
systemctl stop nginx       # 停止
systemctl restart nginx    # 重启
systemctl reload nginx     # 重载配置
systemctl status nginx     # 查看状态
nginx -t                   # 测试配置

# PHP-FPM
systemctl start php8.1-fpm
systemctl stop php8.1-fpm
systemctl restart php8.1-fpm
systemctl reload php8.1-fpm
systemctl status php8.1-fpm

# MySQL
systemctl start mysql
systemctl stop mysql
systemctl restart mysql
systemctl status mysql

# Redis
systemctl start redis-server
systemctl stop redis-server
systemctl restart redis-server
systemctl status redis-server
```

**日志查看**：
```bash
# Nginx
tail -f /var/log/nginx/zhikao_access.log
tail -f /var/log/nginx/zhikao_error.log

# PHP-FPM
tail -f /var/log/php8.1-fpm.log

# MySQL
tail -f /var/log/mysql/error.log

# 项目日志
tail -f /var/www/zhikao/runtime/log/*.log
```

**数据库操作**：
```bash
# 登录
mysql -u root -p

# 备份
mysqldump -u root -p zhikao > backup.sql

# 恢复
mysql -u root -p zhikao < backup.sql
```

### 8.2 Cron 配置示例

编辑 crontab：
```bash
crontab -e
```

常用定时任务：
```bash
# 每天凌晨 2 点备份
0 2 * * * /path/to/deploy/backup.sh >> /var/log/zhikao_cron_backup.log 2>&1

# 每 5 分钟监控一次
*/5 * * * * /path/to/deploy/monitor.sh >> /var/log/zhikao_cron_monitor.log 2>&1

# 每周日凌晨 3 点清理过期备份
0 3 * * 0 /path/to/deploy/backup.sh --clean >> /var/log/zhikao_cron_clean.log 2>&1

# 每天凌晨 4 点优化数据库
0 4 * * * mysqlcheck -o zhikao >> /var/log/zhikao_cron_optimize.log 2>&1
```

### 8.3 联系与支持

如遇到问题，请：
1. 先查阅本文档的「常见问题排查」部分
2. 查看相关日志文件
3. 检查服务状态
4. 确认配置文件是否正确

---

**文档版本**：v1.0.0  
**最后更新**：2024-01-01
