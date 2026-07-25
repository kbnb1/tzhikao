# 智考AI - 智能高考预测助手

> 面向高中生的AI驱动高考备考平台，包含Android App、PHP后台管理系统、App介绍页面。

## 项目简介

智考AI是一款基于人工智能的高考备考学习平台，通过AI算法精准预测考试成绩，智能分析学习薄弱点，为高中生提供个性化的学习方案。

## 技术架构

### 后端
- **框架**: ThinkPHP 8.x
- **语言**: PHP 8.1+
- **数据库**: MySQL 8.0+
- **缓存**: Redis (可选)
- **认证**: JWT Token
- **AI服务**: 支持多服务商（OpenAI、DeepSeek、百度等）

### Android App
- **语言**: Java
- **最低SDK**: API 21 (Android 5.0)
- **目标SDK**: API 34
- **UI**: Material Design 3 + AndroidX
- **网络**: OkHttp + Retrofit
- **图片加载**: Glide
- **图表**: MPAndroidChart

### 前端页面
- 管理后台: 纯HTML + CSS + JavaScript (SPA)
- App介绍页: 响应式设计，Tailwind CSS

## 目录结构

```
/workspace
├── backend/                # 后端PHP项目 (ThinkPHP 8)
│   ├── app/
│   │   ├── controller/
│   │   │   ├── api/v1/    # 用户端API控制器
│   │   │   └── admin/v1/  # 管理端API控制器
│   │   ├── model/          # 数据模型
│   │   ├── service/        # 业务服务层
│   │   ├── middleware/     # 中间件
│   │   ├── utils/          # 工具类
│   │   └── validate/       # 验证器
│   ├── config/             # 配置文件
│   ├── route/              # 路由配置
│   ├── public/
│   │   └── admin/          # 管理后台前端
│   └── ...
├── android/                # Android App项目
│   ├── app/
│   │   └── src/main/
│   │       ├── java/com/zhikao/ai/
│   │       └── res/
│   └── ...
├── introduction/           # App介绍页面
│   ├── index.html
│   ├── css/
│   └── js/
├── database/               # 数据库脚本
│   └── install.sql
├── deploy/                 # 部署脚本
│   ├── install_env.sh      # 环境安装脚本
│   ├── deploy_project.sh   # 项目部署脚本
│   ├── backup.sh           # 备份脚本
│   ├── monitor.sh          # 监控脚本
│   ├── update.sh           # 更新脚本
│   ├── nginx/              # Nginx配置
│   └── README.md           # 部署文档
└── docs/                   # 文档目录
```

## 核心功能

### 用户系统
- ✅ 手机号/用户名密码登录注册
- ✅ JWT Token认证
- ✅ 个人资料管理
- ✅ 头像上传
- ✅ VIP会员系统

### 考试测评
- ✅ 多科目题库（语文、数学、英语、物理、化学、生物）
- ✅ 历年真题试卷
- ✅ 在线答题计时
- ✅ 自动评分
- ✅ 答案解析
- ✅ 蒙题标记

### AI预测
- ✅ AI智能生成试卷
- ✅ 成绩预测（置信度 + 学习建议）
- ✅ 成绩趋势图分析
- ✅ 多AI服务商支持
- ✅ 薄弱点分析

### 错题本
- ✅ 自动收集错题
- ✅ 错题分类管理
- ✅ 薄弱点分析
- ✅ 强化练习生成
- ✅ 掌握程度标记

### 学习提醒
- ✅ 自定义提醒时间
- ✅ 重复周期设置
- ✅ 提醒开关控制

### 成就系统
- ✅ 多种成就徽章
- ✅ 成就解锁条件
- ✅ 积分奖励
- ✅ 分享成就

### 学习社区
- ✅ 帖子发布/浏览
- ✅ 评论互动
- ✅ 点赞收藏
- ✅ 分类筛选
- ✅ 搜索功能

### 学习计划
- ✅ 制定学习计划
- ✅ 目标设定
- ✅ 进度跟踪
- ✅ 学习记录

### 管理后台
- ✅ 仪表盘统计
- ✅ 用户管理
- ✅ 科目管理
- ✅ 题目管理
- ✅ 试卷管理
- ✅ AI配置管理
- ✅ 社区内容审核
- ✅ 成就管理
- ✅ 页面配置
- ✅ 系统配置

## 数据库表结构

共23张表：
1. `users` - 用户表
2. `admin_users` - 管理员表
3. `subjects` - 科目表
4. `questions` - 题目表
5. `exam_papers` - 试卷表
6. `exam_paper_questions` - 试卷题目关联表
7. `exam_records` - 考试记录表
8. `exam_answers` - 答题明细表
9. `wrong_questions` - 错题本表
10. `ai_configs` - AI配置表
11. `predictions` - 预测记录表
12. `reminders` - 学习提醒表
13. `achievements` - 成就表
14. `user_achievements` - 用户成就表
15. `community_posts` - 社区帖子表
16. `community_comments` - 社区评论表
17. `page_config` - 页面配置表
18. `community_post_likes` - 帖子点赞表
19. `community_post_favorites` - 帖子收藏表
20. `community_comment_likes` - 评论点赞表
21. `study_plans` - 学习计划表
22. `study_records` - 学习记录表
23. `sys_config` - 系统配置表

## API接口

### 用户端API (前缀: /api/v1)

| 方法 | 路径 | 说明 | 认证 |
|------|------|------|------|
| POST | /login | 登录 | - |
| POST | /register | 注册 | - |
| POST | /send-code | 发送验证码 | - |
| POST | /refresh | 刷新token | - |
| POST | /forgot-password | 找回密码 | - |
| GET | /user/info | 获取用户信息 | ✅ |
| PUT | /user/profile | 更新资料 | ✅ |
| PUT | /user/password | 修改密码 | ✅ |
| POST | /user/avatar | 上传头像 | ✅ |
| GET | /exam/subjects | 科目列表 | ✅ |
| GET | /exam/papers | 试卷列表 | ✅ |
| GET | /exam/paper/detail | 试卷详情 | ✅ |
| POST | /exam/start | 开始考试 | ✅ |
| POST | /exam/submit-answer | 提交单题 | ✅ |
| POST | /exam/submit | 提交整卷 | ✅ |
| GET | /exam/records | 考试记录 | ✅ |
| GET | /exam/record/detail | 记录详情 | ✅ |
| GET | /wrong-questions | 错题列表 | ✅ |
| ... | ... | ... | ... |

### 管理端API (前缀: /admin/v1)

| 方法 | 路径 | 说明 | 认证 |
|------|------|------|------|
| POST | /login | 登录 | - |
| GET | /dashboard | 仪表盘 | ✅ |
| GET | /users | 用户列表 | ✅ |
| POST | /users | 新增用户 | ✅ |
| PUT | /users/:id | 编辑用户 | ✅ |
| DELETE | /users/:id | 删除用户 | ✅ |
| ... | ... | ... | ... |

## 快速开始

### 环境要求

- PHP 8.1+
- MySQL 8.0+
- Nginx / Apache
- Composer 2.0+
- Android Studio (开发App)

### 后端部署

```bash
# 1. 安装环境
cd deploy
sudo bash install_env.sh

# 2. 部署项目
sudo bash deploy_project.sh --domain yourdomain.com

# 3. 导入数据库
mysql -u root -p zhikao_ai < ../database/install.sql

# 4. 修改配置
cd /var/www/zhikao/backend
vim .env
```

### 本地开发

```bash
# 进入后端目录
cd backend

# 安装依赖
composer install

# 启动开发服务器
php think run -H 0.0.0.0 -p 8000
```

### Android开发

1. 使用Android Studio打开 `/workspace/android` 目录
2. 修改 `ApiConfig.BASE_URL` 为你的服务器地址
3. 同步Gradle依赖
4. 连接设备或启动模拟器
5. 点击运行

## 管理后台

部署后访问：`https://你的域名/admin/index.html`

默认账号：`admin` / `admin123`

> ⚠️ 部署后请立即修改默认密码！

## App介绍页

部署后访问：`https://你的域名/introduction/index.html`

内容可通过管理后台配置。

## 默认配置

- **管理员账号**: admin / admin123
- **数据库名**: zhikao_ai
- **表前缀**: zk_
- **JWT密钥**: zhikao_ai_jwt_secret_key_2024
- **Token有效期**: 2小时

## 安全建议

1. 部署后立即修改管理员默认密码
2. 修改JWT密钥为随机字符串
3. 启用HTTPS
4. 配置数据库备份
5. 启用防火墙，只开放必要端口
6. 定期更新系统和依赖包
7. 配置日志监控

## 商业运营

### 支付接入
预留VIP会员系统，可接入支付宝、微信支付等支付渠道。

### 增值服务
- AI预测次数包
- 高级题库解锁
- 一对一辅导
- 视频课程

### 数据统计
后台提供完整的数据统计和报表功能。

## 技术支持

如有问题，请参考 `deploy/README.md` 中的常见问题排查部分。

## 许可证

MIT License
