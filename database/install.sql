-- =============================================
-- 智考AI - 数据库安装脚本
-- MySQL Version: 8.0+
-- Engine: InnoDB
-- Charset: utf8mb4
-- =============================================

SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

-- ---------------------------------------------
-- 1. 用户表
-- ---------------------------------------------
DROP TABLE IF EXISTS `users`;
CREATE TABLE `users` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT COMMENT '用户ID',
  `username` varchar(50) NOT NULL DEFAULT '' COMMENT '用户名',
  `password` varchar(255) NOT NULL DEFAULT '' COMMENT '密码',
  `phone` varchar(20) NOT NULL DEFAULT '' COMMENT '手机号',
  `email` varchar(100) NOT NULL DEFAULT '' COMMENT '邮箱',
  `nickname` varchar(50) NOT NULL DEFAULT '' COMMENT '昵称',
  `avatar` varchar(255) NOT NULL DEFAULT '' COMMENT '头像',
  `gender` tinyint unsigned NOT NULL DEFAULT 0 COMMENT '性别 0未知 1男 2女',
  `grade` varchar(20) NOT NULL DEFAULT '' COMMENT '年级',
  `target_university` varchar(100) NOT NULL DEFAULT '' COMMENT '目标大学',
  `target_major` varchar(100) NOT NULL DEFAULT '' COMMENT '目标专业',
  `province` varchar(50) NOT NULL DEFAULT '' COMMENT '省份',
  `city` varchar(50) NOT NULL DEFAULT '' COMMENT '城市',
  `school` varchar(100) NOT NULL DEFAULT '' COMMENT '学校',
  `total_study_days` int unsigned NOT NULL DEFAULT 0 COMMENT '总学习天数',
  `continuous_days` int unsigned NOT NULL DEFAULT 0 COMMENT '连续学习天数',
  `points` int unsigned NOT NULL DEFAULT 0 COMMENT '积分',
  `vip_level` tinyint unsigned NOT NULL DEFAULT 0 COMMENT 'VIP等级',
  `vip_expire_time` datetime DEFAULT NULL COMMENT 'VIP过期时间',
  `status` tinyint unsigned NOT NULL DEFAULT 1 COMMENT '状态 0禁用 1正常',
  `last_login_time` datetime DEFAULT NULL COMMENT '最后登录时间',
  `last_login_ip` varchar(50) NOT NULL DEFAULT '' COMMENT '最后登录IP',
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT '创建时间',
  `updated_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '更新时间',
  `deleted_at` datetime DEFAULT NULL COMMENT '删除时间',
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_username` (`username`),
  UNIQUE KEY `uk_phone` (`phone`),
  UNIQUE KEY `uk_email` (`email`),
  KEY `idx_status` (`status`),
  KEY `idx_created_at` (`created_at`),
  KEY `idx_deleted_at` (`deleted_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='用户表';

-- ---------------------------------------------
-- 2. 管理员表
-- ---------------------------------------------
DROP TABLE IF EXISTS `admin_users`;
CREATE TABLE `admin_users` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT COMMENT '管理员ID',
  `username` varchar(50) NOT NULL DEFAULT '' COMMENT '用户名',
  `password` varchar(255) NOT NULL DEFAULT '' COMMENT '密码',
  `nickname` varchar(50) NOT NULL DEFAULT '' COMMENT '昵称',
  `avatar` varchar(255) NOT NULL DEFAULT '' COMMENT '头像',
  `role` varchar(50) NOT NULL DEFAULT 'admin' COMMENT '角色',
  `status` tinyint unsigned NOT NULL DEFAULT 1 COMMENT '状态 0禁用 1正常',
  `last_login_time` datetime DEFAULT NULL COMMENT '最后登录时间',
  `last_login_ip` varchar(50) NOT NULL DEFAULT '' COMMENT '最后登录IP',
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT '创建时间',
  `updated_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '更新时间',
  `deleted_at` datetime DEFAULT NULL COMMENT '删除时间',
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_username` (`username`),
  KEY `idx_status` (`status`),
  KEY `idx_deleted_at` (`deleted_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='管理员表';

-- ---------------------------------------------
-- 3. 科目表
-- ---------------------------------------------
DROP TABLE IF EXISTS `subjects`;
CREATE TABLE `subjects` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT COMMENT '科目ID',
  `name` varchar(50) NOT NULL DEFAULT '' COMMENT '科目名称',
  `icon` varchar(255) NOT NULL DEFAULT '' COMMENT '图标',
  `sort` int unsigned NOT NULL DEFAULT 0 COMMENT '排序',
  `status` tinyint unsigned NOT NULL DEFAULT 1 COMMENT '状态 0禁用 1启用',
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT '创建时间',
  `updated_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '更新时间',
  PRIMARY KEY (`id`),
  KEY `idx_status` (`status`),
  KEY `idx_sort` (`sort`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='科目表';

-- ---------------------------------------------
-- 4. 题目表
-- ---------------------------------------------
DROP TABLE IF EXISTS `questions`;
CREATE TABLE `questions` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT COMMENT '题目ID',
  `subject_id` bigint unsigned NOT NULL DEFAULT 0 COMMENT '科目ID',
  `type` tinyint unsigned NOT NULL DEFAULT 1 COMMENT '题目类型 1单选 2多选 3判断 4填空 5解答',
  `difficulty` tinyint unsigned NOT NULL DEFAULT 2 COMMENT '难度 1简单 2中等 3困难',
  `content` text NOT NULL COMMENT '题目内容',
  `options` json DEFAULT NULL COMMENT '选项(JSON)',
  `answer` text NOT NULL COMMENT '答案',
  `analysis` text COMMENT '解析',
  `score` int unsigned NOT NULL DEFAULT 5 COMMENT '分值',
  `year` varchar(10) NOT NULL DEFAULT '' COMMENT '年份',
  `province` varchar(50) NOT NULL DEFAULT '' COMMENT '省份',
  `source` varchar(100) NOT NULL DEFAULT '' COMMENT '来源',
  `status` tinyint unsigned NOT NULL DEFAULT 1 COMMENT '状态 0禁用 1启用',
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT '创建时间',
  `updated_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '更新时间',
  `deleted_at` datetime DEFAULT NULL COMMENT '删除时间',
  PRIMARY KEY (`id`),
  KEY `idx_subject_id` (`subject_id`),
  KEY `idx_type` (`type`),
  KEY `idx_difficulty` (`difficulty`),
  KEY `idx_status` (`status`),
  KEY `idx_created_at` (`created_at`),
  KEY `idx_deleted_at` (`deleted_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='题目表';

-- ---------------------------------------------
-- 5. 试卷表
-- ---------------------------------------------
DROP TABLE IF EXISTS `exam_papers`;
CREATE TABLE `exam_papers` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT COMMENT '试卷ID',
  `title` varchar(200) NOT NULL DEFAULT '' COMMENT '试卷标题',
  `subject_id` bigint unsigned NOT NULL DEFAULT 0 COMMENT '科目ID',
  `type` tinyint unsigned NOT NULL DEFAULT 1 COMMENT '试卷类型 1模拟卷 2真题卷 3专项卷',
  `total_score` int unsigned NOT NULL DEFAULT 100 COMMENT '总分',
  `total_time` int unsigned NOT NULL DEFAULT 120 COMMENT '考试时长(分钟)',
  `question_count` int unsigned NOT NULL DEFAULT 0 COMMENT '题目数量',
  `difficulty` tinyint unsigned NOT NULL DEFAULT 2 COMMENT '难度 1简单 2中等 3困难',
  `description` text COMMENT '试卷描述',
  `cover_image` varchar(255) NOT NULL DEFAULT '' COMMENT '封面图片',
  `status` tinyint unsigned NOT NULL DEFAULT 1 COMMENT '状态 0禁用 1启用',
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT '创建时间',
  `updated_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '更新时间',
  `deleted_at` datetime DEFAULT NULL COMMENT '删除时间',
  PRIMARY KEY (`id`),
  KEY `idx_subject_id` (`subject_id`),
  KEY `idx_type` (`type`),
  KEY `idx_status` (`status`),
  KEY `idx_created_at` (`created_at`),
  KEY `idx_deleted_at` (`deleted_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='试卷表';

-- ---------------------------------------------
-- 6. 试卷题目关联表
-- ---------------------------------------------
DROP TABLE IF EXISTS `exam_paper_questions`;
CREATE TABLE `exam_paper_questions` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT COMMENT 'ID',
  `exam_paper_id` bigint unsigned NOT NULL DEFAULT 0 COMMENT '试卷ID',
  `question_id` bigint unsigned NOT NULL DEFAULT 0 COMMENT '题目ID',
  `sort` int unsigned NOT NULL DEFAULT 0 COMMENT '排序',
  `score` int unsigned NOT NULL DEFAULT 5 COMMENT '分值',
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT '创建时间',
  `updated_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '更新时间',
  PRIMARY KEY (`id`),
  KEY `idx_exam_paper_id` (`exam_paper_id`),
  KEY `idx_question_id` (`question_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='试卷题目关联表';

-- ---------------------------------------------
-- 7. 考试记录表
-- ---------------------------------------------
DROP TABLE IF EXISTS `exam_records`;
CREATE TABLE `exam_records` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT COMMENT '记录ID',
  `user_id` bigint unsigned NOT NULL DEFAULT 0 COMMENT '用户ID',
  `exam_paper_id` bigint unsigned NOT NULL DEFAULT 0 COMMENT '试卷ID',
  `subject_id` bigint unsigned NOT NULL DEFAULT 0 COMMENT '科目ID',
  `score` int unsigned NOT NULL DEFAULT 0 COMMENT '得分',
  `total_score` int unsigned NOT NULL DEFAULT 0 COMMENT '总分',
  `correct_count` int unsigned NOT NULL DEFAULT 0 COMMENT '正确题数',
  `wrong_count` int unsigned NOT NULL DEFAULT 0 COMMENT '错误题数',
  `guess_count` int unsigned NOT NULL DEFAULT 0 COMMENT '蒙对题数',
  `use_time` int unsigned NOT NULL DEFAULT 0 COMMENT '用时(秒)',
  `status` tinyint unsigned NOT NULL DEFAULT 1 COMMENT '状态 1进行中 2已提交 3已交卷',
  `submitted_at` datetime DEFAULT NULL COMMENT '提交时间',
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT '创建时间',
  `updated_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '更新时间',
  `deleted_at` datetime DEFAULT NULL COMMENT '删除时间',
  PRIMARY KEY (`id`),
  KEY `idx_user_id` (`user_id`),
  KEY `idx_exam_paper_id` (`exam_paper_id`),
  KEY `idx_subject_id` (`subject_id`),
  KEY `idx_status` (`status`),
  KEY `idx_created_at` (`created_at`),
  KEY `idx_deleted_at` (`deleted_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='考试记录表';

-- ---------------------------------------------
-- 8. 答题明细表
-- ---------------------------------------------
DROP TABLE IF EXISTS `exam_answers`;
CREATE TABLE `exam_answers` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT COMMENT 'ID',
  `exam_record_id` bigint unsigned NOT NULL DEFAULT 0 COMMENT '考试记录ID',
  `question_id` bigint unsigned NOT NULL DEFAULT 0 COMMENT '题目ID',
  `user_answer` text COMMENT '用户答案',
  `is_correct` tinyint unsigned NOT NULL DEFAULT 0 COMMENT '是否正确 0否 1是',
  `is_guess` tinyint unsigned NOT NULL DEFAULT 0 COMMENT '是否蒙对 0否 1是',
  `score` int unsigned NOT NULL DEFAULT 0 COMMENT '得分',
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT '创建时间',
  `updated_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '更新时间',
  PRIMARY KEY (`id`),
  KEY `idx_exam_record_id` (`exam_record_id`),
  KEY `idx_question_id` (`question_id`),
  KEY `idx_is_correct` (`is_correct`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='答题明细表';

-- ---------------------------------------------
-- 9. 错题本表
-- ---------------------------------------------
DROP TABLE IF EXISTS `wrong_questions`;
CREATE TABLE `wrong_questions` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT COMMENT 'ID',
  `user_id` bigint unsigned NOT NULL DEFAULT 0 COMMENT '用户ID',
  `question_id` bigint unsigned NOT NULL DEFAULT 0 COMMENT '题目ID',
  `subject_id` bigint unsigned NOT NULL DEFAULT 0 COMMENT '科目ID',
  `wrong_count` int unsigned NOT NULL DEFAULT 1 COMMENT '错误次数',
  `last_wrong_time` datetime DEFAULT NULL COMMENT '最后错误时间',
  `master_level` tinyint unsigned NOT NULL DEFAULT 0 COMMENT '掌握程度 0未掌握 1一般 2较好 3完全掌握',
  `note` text COMMENT '笔记',
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT '创建时间',
  `updated_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '更新时间',
  `deleted_at` datetime DEFAULT NULL COMMENT '删除时间',
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_user_question` (`user_id`, `question_id`),
  KEY `idx_user_id` (`user_id`),
  KEY `idx_subject_id` (`subject_id`),
  KEY `idx_question_id` (`question_id`),
  KEY `idx_master_level` (`master_level`),
  KEY `idx_deleted_at` (`deleted_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='错题本表';

-- ---------------------------------------------
-- 10. AI配置表
-- ---------------------------------------------
DROP TABLE IF EXISTS `ai_configs`;
CREATE TABLE `ai_configs` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT COMMENT '配置ID',
  `provider` varchar(50) NOT NULL DEFAULT '' COMMENT 'AI服务商',
  `name` varchar(100) NOT NULL DEFAULT '' COMMENT '配置名称',
  `api_key` varchar(255) NOT NULL DEFAULT '' COMMENT 'API密钥',
  `api_url` varchar(255) NOT NULL DEFAULT '' COMMENT 'API地址',
  `model` varchar(100) NOT NULL DEFAULT '' COMMENT '模型名称',
  `is_default` tinyint unsigned NOT NULL DEFAULT 0 COMMENT '是否默认 0否 1是',
  `status` tinyint unsigned NOT NULL DEFAULT 1 COMMENT '状态 0禁用 1启用',
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT '创建时间',
  `updated_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '更新时间',
  PRIMARY KEY (`id`),
  KEY `idx_provider` (`provider`),
  KEY `idx_is_default` (`is_default`),
  KEY `idx_status` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='AI配置表';

-- ---------------------------------------------
-- 11. 预测记录表
-- ---------------------------------------------
DROP TABLE IF EXISTS `predictions`;
CREATE TABLE `predictions` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT COMMENT '预测ID',
  `user_id` bigint unsigned NOT NULL DEFAULT 0 COMMENT '用户ID',
  `subject_id` bigint unsigned NOT NULL DEFAULT 0 COMMENT '科目ID',
  `exam_record_id` bigint unsigned NOT NULL DEFAULT 0 COMMENT '考试记录ID',
  `predicted_score` decimal(5,2) NOT NULL DEFAULT 0.00 COMMENT '预测分数',
  `confidence` decimal(5,2) NOT NULL DEFAULT 0.00 COMMENT '置信度',
  `suggestion` text COMMENT '建议',
  `trend_data` json DEFAULT NULL COMMENT '趋势数据(JSON)',
  `ai_provider` varchar(50) NOT NULL DEFAULT '' COMMENT 'AI服务商',
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT '创建时间',
  `updated_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '更新时间',
  `deleted_at` datetime DEFAULT NULL COMMENT '删除时间',
  PRIMARY KEY (`id`),
  KEY `idx_user_id` (`user_id`),
  KEY `idx_subject_id` (`subject_id`),
  KEY `idx_exam_record_id` (`exam_record_id`),
  KEY `idx_created_at` (`created_at`),
  KEY `idx_deleted_at` (`deleted_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='预测记录表';

-- ---------------------------------------------
-- 12. 学习提醒表
-- ---------------------------------------------
DROP TABLE IF EXISTS `reminders`;
CREATE TABLE `reminders` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT COMMENT '提醒ID',
  `user_id` bigint unsigned NOT NULL DEFAULT 0 COMMENT '用户ID',
  `title` varchar(100) NOT NULL DEFAULT '' COMMENT '标题',
  `content` text COMMENT '内容',
  `reminder_time` time NOT NULL COMMENT '提醒时间',
  `repeat_type` tinyint unsigned NOT NULL DEFAULT 1 COMMENT '重复类型 1不重复 2每天 3每周 4每月',
  `repeat_days` varchar(50) NOT NULL DEFAULT '' COMMENT '重复日期(1-7表示周一到周日)',
  `is_enabled` tinyint unsigned NOT NULL DEFAULT 1 COMMENT '是否启用 0否 1是',
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT '创建时间',
  `updated_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '更新时间',
  `deleted_at` datetime DEFAULT NULL COMMENT '删除时间',
  PRIMARY KEY (`id`),
  KEY `idx_user_id` (`user_id`),
  KEY `idx_is_enabled` (`is_enabled`),
  KEY `idx_deleted_at` (`deleted_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='学习提醒表';

-- ---------------------------------------------
-- 13. 成就表
-- ---------------------------------------------
DROP TABLE IF EXISTS `achievements`;
CREATE TABLE `achievements` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT COMMENT '成就ID',
  `name` varchar(50) NOT NULL DEFAULT '' COMMENT '成就名称',
  `description` varchar(255) NOT NULL DEFAULT '' COMMENT '成就描述',
  `icon` varchar(255) NOT NULL DEFAULT '' COMMENT '图标',
  `type` tinyint unsigned NOT NULL DEFAULT 1 COMMENT '类型 1学习类 2考试类 3社交类 4特殊类',
  `condition_value` int unsigned NOT NULL DEFAULT 0 COMMENT '条件值',
  `points_reward` int unsigned NOT NULL DEFAULT 0 COMMENT '奖励积分',
  `sort` int unsigned NOT NULL DEFAULT 0 COMMENT '排序',
  `status` tinyint unsigned NOT NULL DEFAULT 1 COMMENT '状态 0禁用 1启用',
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT '创建时间',
  `updated_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '更新时间',
  PRIMARY KEY (`id`),
  KEY `idx_type` (`type`),
  KEY `idx_status` (`status`),
  KEY `idx_sort` (`sort`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='成就表';

-- ---------------------------------------------
-- 14. 用户成就表
-- ---------------------------------------------
DROP TABLE IF EXISTS `user_achievements`;
CREATE TABLE `user_achievements` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT COMMENT 'ID',
  `user_id` bigint unsigned NOT NULL DEFAULT 0 COMMENT '用户ID',
  `achievement_id` bigint unsigned NOT NULL DEFAULT 0 COMMENT '成就ID',
  `unlocked_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT '解锁时间',
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT '创建时间',
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_user_achievement` (`user_id`, `achievement_id`),
  KEY `idx_user_id` (`user_id`),
  KEY `idx_achievement_id` (`achievement_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='用户成就表';

-- ---------------------------------------------
-- 15. 社区帖子表
-- ---------------------------------------------
DROP TABLE IF EXISTS `community_posts`;
CREATE TABLE `community_posts` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT COMMENT '帖子ID',
  `user_id` bigint unsigned NOT NULL DEFAULT 0 COMMENT '用户ID',
  `title` varchar(200) NOT NULL DEFAULT '' COMMENT '标题',
  `content` text NOT NULL COMMENT '内容',
  `images` json DEFAULT NULL COMMENT '图片(JSON)',
  `category` varchar(50) NOT NULL DEFAULT '' COMMENT '分类',
  `view_count` int unsigned NOT NULL DEFAULT 0 COMMENT '浏览数',
  `like_count` int unsigned NOT NULL DEFAULT 0 COMMENT '点赞数',
  `comment_count` int unsigned NOT NULL DEFAULT 0 COMMENT '评论数',
  `is_top` tinyint unsigned NOT NULL DEFAULT 0 COMMENT '是否置顶 0否 1是',
  `is_essence` tinyint unsigned NOT NULL DEFAULT 0 COMMENT '是否精华 0否 1是',
  `status` tinyint unsigned NOT NULL DEFAULT 1 COMMENT '状态 0禁用 1正常',
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT '创建时间',
  `updated_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '更新时间',
  `deleted_at` datetime DEFAULT NULL COMMENT '删除时间',
  PRIMARY KEY (`id`),
  KEY `idx_user_id` (`user_id`),
  KEY `idx_category` (`category`),
  KEY `idx_is_top` (`is_top`),
  KEY `idx_is_essence` (`is_essence`),
  KEY `idx_status` (`status`),
  KEY `idx_created_at` (`created_at`),
  KEY `idx_deleted_at` (`deleted_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='社区帖子表';

-- ---------------------------------------------
-- 16. 社区评论表
-- ---------------------------------------------
DROP TABLE IF EXISTS `community_comments`;
CREATE TABLE `community_comments` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT COMMENT '评论ID',
  `post_id` bigint unsigned NOT NULL DEFAULT 0 COMMENT '帖子ID',
  `user_id` bigint unsigned NOT NULL DEFAULT 0 COMMENT '用户ID',
  `content` text NOT NULL COMMENT '内容',
  `parent_id` bigint unsigned NOT NULL DEFAULT 0 COMMENT '父评论ID',
  `like_count` int unsigned NOT NULL DEFAULT 0 COMMENT '点赞数',
  `status` tinyint unsigned NOT NULL DEFAULT 1 COMMENT '状态 0禁用 1正常',
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT '创建时间',
  `updated_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '更新时间',
  `deleted_at` datetime DEFAULT NULL COMMENT '删除时间',
  PRIMARY KEY (`id`),
  KEY `idx_post_id` (`post_id`),
  KEY `idx_user_id` (`user_id`),
  KEY `idx_parent_id` (`parent_id`),
  KEY `idx_status` (`status`),
  KEY `idx_created_at` (`created_at`),
  KEY `idx_deleted_at` (`deleted_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='社区评论表';

-- ---------------------------------------------
-- 17. 页面配置表
-- ---------------------------------------------
DROP TABLE IF EXISTS `page_config`;
CREATE TABLE `page_config` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT COMMENT '配置ID',
  `page_key` varchar(100) NOT NULL DEFAULT '' COMMENT '页面标识',
  `config_key` varchar(100) NOT NULL DEFAULT '' COMMENT '配置键',
  `config_value` text COMMENT '配置值',
  `config_type` varchar(20) NOT NULL DEFAULT 'string' COMMENT '配置类型 string text json number boolean',
  `description` varchar(255) NOT NULL DEFAULT '' COMMENT '描述',
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT '创建时间',
  `updated_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '更新时间',
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_page_config` (`page_key`, `config_key`),
  KEY `idx_page_key` (`page_key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='页面配置表';

-- =============================================
-- 初始数据
-- =============================================

-- ---------------------------------------------
-- 管理员数据
-- ---------------------------------------------
INSERT INTO `admin_users` (`username`, `password`, `nickname`, `avatar`, `role`, `status`) VALUES
('admin', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '超级管理员', '', 'super_admin', 1);

-- ---------------------------------------------
-- 科目数据
-- ---------------------------------------------
INSERT INTO `subjects` (`name`, `icon`, `sort`, `status`) VALUES
('语文', '', 1, 1),
('数学', '', 2, 1),
('英语', '', 3, 1),
('物理', '', 4, 1),
('化学', '', 5, 1),
('生物', '', 6, 1);

-- ---------------------------------------------
-- 成就数据
-- ---------------------------------------------
INSERT INTO `achievements` (`name`, `description`, `icon`, `type`, `condition_value`, `points_reward`, `sort`, `status`) VALUES
('初出茅庐', '完成第一次答题', '', 1, 1, 10, 1, 1),
('小试牛刀', '累计答题10道', '', 1, 10, 20, 2, 1),
('学霸之路', '累计答题100道', '', 1, 100, 50, 3, 1),
('题神降临', '累计答题1000道', '', 1, 1000, 200, 4, 1),
('坚持不懈', '连续学习7天', '', 1, 7, 30, 5, 1),
('持之以恒', '连续学习30天', '', 1, 30, 100, 6, 1),
('首战告捷', '完成第一次考试', '', 2, 1, 15, 7, 1),
('满分达人', '获得一次满分', '', 2, 100, 100, 8, 1),
('进步神速', '成绩提升20分', '', 2, 20, 50, 9, 1),
('社区新星', '发布第一篇帖子', '', 3, 1, 10, 10, 1),
('人气之王', '获得100个赞', '', 3, 100, 80, 11, 1),
('VIP会员', '成为VIP会员', '', 4, 1, 500, 12, 1);

-- ---------------------------------------------
-- AI配置数据
-- ---------------------------------------------
INSERT INTO `ai_configs` (`provider`, `name`, `api_key`, `api_url`, `model`, `is_default`, `status`) VALUES
('OpenAI', 'OpenAI GPT-4', '', 'https://api.openai.com/v1/chat/completions', 'gpt-4', 0, 0),
('DeepSeek', 'DeepSeek V3', '', 'https://api.deepseek.com/v1/chat/completions', 'deepseek-chat', 1, 0);

-- ---------------------------------------------
-- 页面配置数据
-- ---------------------------------------------
INSERT INTO `page_config` (`page_key`, `config_key`, `config_value`, `config_type`, `description`) VALUES
('home', 'banner_title', '智考AI - 智能备考助手', 'string', '首页banner标题'),
('home', 'banner_subtitle', 'AI智能预测，助你金榜题名', 'string', '首页banner副标题'),
('home', 'feature_1_title', '海量题库', 'string', '首页特色功能1标题'),
('home', 'feature_1_desc', '覆盖各学科各年级的精品题目', 'string', '首页特色功能1描述'),
('home', 'feature_2_title', 'AI智能预测', 'string', '首页特色功能2标题'),
('home', 'feature_2_desc', '基于大数据分析，精准预测成绩', 'string', '首页特色功能2描述'),
('home', 'feature_3_title', '错题本', 'string', '首页特色功能3标题'),
('home', 'feature_3_desc', '智能记录错题，针对性复习', 'string', '首页特色功能3描述'),
('home', 'feature_4_title', '学习社区', 'string', '首页特色功能4标题'),
('home', 'feature_4_desc', '与同学交流学习经验', 'string', '首页特色功能4描述'),
('about', 'about_title', '关于智考AI', 'string', '关于页标题'),
('about', 'about_content', '智考AI是一款专为学生打造的智能备考平台，运用先进的人工智能技术，提供个性化学习方案和精准的成绩预测，帮助学生高效备考、快速提分。', 'text', '关于页内容'),
('contact', 'contact_title', '联系我们', 'string', '联系页标题'),
('contact', 'contact_email', 'support@zhikaoai.com', 'string', '联系邮箱'),
('contact', 'contact_wechat', 'zhikao_ai', 'string', '联系微信');

-- ---------------------------------------------
-- 示例题目数据
-- ---------------------------------------------

-- 语文题目
INSERT INTO `questions` (`subject_id`, `type`, `difficulty`, `content`, `options`, `answer`, `analysis`, `score`, `status`) VALUES
(1, 1, 1, '下列词语中，加点字读音全都正确的一组是', '{"A": "模样(mó) 疲惫(bèi) 濒临绝境(bīn) 并行不悖(bèi)", "B": "麻痹(bì) 包庇(bì) 心潮澎湃(bài) 步履蹒跚(pán)", "C": "贮藏(zhù) 鞭笞(chī) 瞠目结舌(chēng) 解甲归田(jiě)", "D": "畸形(jī) 机械(jiè) 破绽百出(zhàn) 伺机报复(sì)"}', 'C', 'A项中\"模样\"应读mú；B项中\"心潮澎湃\"应读pài；D项中\"机械\"应读xiè。', 5, 1),
(1, 1, 2, '下列各句中，没有语病的一句是', '{"A": "随着社会的不断进步，科技知识的价值日益显现，人类已进入知识产权的归属和利益的分成，并已开始向科技工作者身上倾斜。", "B": "本栏目将各地电视台选送的歌舞曲艺、风情民俗、文化娱乐和体育活动等方面的节目，加以重新编排、组合和润色，进行的再创作。", "C": "俄罗斯也进行了一些改革，如禁止政府官员使用进口汽车，推行住房商品化，以及精简包括电力公司、铁路公司等大型国有企业等。", "D": "终身教育制度的建立，不仅为那些因这样那样的原因未能完成学业的人打开了一扇门，也为那些对知识有着更高需求的人提供了机会。"}', 'D', 'A项成分残缺，\"进入\"后缺少宾语中心语；B项句式杂糅；C项不合逻辑，\"推行住房商品化\"不是精简的内容。', 5, 1),
(1, 1, 2, '依次填入下列各句横线处的词语，最恰当的一组是：①岗位培训改变了只在学校接受教育的状况，一个人离开学校并不意味着学习的______。②由于环境污染和一些人为的原因，著名的阿尔巴斯白山羊绒的品质正在逐步地______。③终于回到了魂牵梦萦的故乡，再次走上熟悉的大街小巷，______想起许多童年的往事。', '{"A": "终止 蜕化 难免", "B": "中止 退化 难免", "C": "中止 蜕化 不免", "D": "终止 退化 不免"}', 'D', '\"终止\"指结束、停止；\"中止\"指中途停止。\"退化\"泛指事物由优变劣、由好变坏；\"蜕化\"比喻腐化堕落。\"不免\"指免不了；\"难免\"指不容易避免。', 5, 1),
(1, 1, 3, '下列有关文学常识的表述，错误的一项是', '{"A": "《左传》《史记》等历史散文作品，以\"实录\"的笔法将人物写得真实丰满，有血有肉。", "B": "《项脊轩志》以清淡朴素的笔法写身边琐事，亲切动人。它的作者归有光被认为是\"桐城派\"的代表人物。", "C": "茅盾的《子夜》、巴金的《家》、老舍的《骆驼祥子》以及叶圣陶的《倪焕之》，是我国20世纪二三十年代著名的长篇小说。", "D": "马克·吐温和欧·亨利都擅长写讽刺小说。马克·吐温的《竞选州长》《百万英镑》和欧·亨利的《警察与赞美诗》等都深受读者的喜爱。"}', 'B', '归有光是明代唐宋派的代表人物，不是桐城派。桐城派是清代的散文流派，代表人物有方苞、姚鼐等。', 5, 1),
(1, 1, 2, '对下列古诗句中加点的词解说错误的一项是', '{"A": "故人具鸡黍，邀我至田家。绿树村边合，青山郭外斜。\"合\"\"斜\"是拟人写法，把绿树、青山写得有人的感情。", "B": "好雨知时节，当春乃发生。随风潜入夜，润物细无声。用\"知\"\"潜\"把春雨人格化，写成有知觉、有灵性的东西。", "C": "八月湖水平，涵虚混太清。气蒸云梦泽，波撼岳阳城。\"气蒸\"\"波撼\"是夸张的写法，突出了洞庭湖的雄伟气势。", "D": "黄河远上白云间，一片孤城万仞山。羌笛何须怨杨柳，春风不度玉门关。\"羌笛何须怨杨柳\"用了拟人的手法。"}', 'A', '\"合\"和\"斜\"只是景物描写，并没有把绿树、青山拟人化。', 5, 1);

-- 数学题目
INSERT INTO `questions` (`subject_id`, `type`, `difficulty`, `content`, `options`, `answer`, `analysis`, `score`, `status`) VALUES
(2, 1, 1, '设集合A={x|x²-4≤0}，B={x|2x+a≤0}，且A∩B={x|-2≤x≤1}，则a=', '{"A": "-4", "B": "-2", "C": "2", "D": "4"}', 'B', 'A={x|-2≤x≤2}，B={x|x≤-a/2}。因为A∩B={x|-2≤x≤1}，所以-a/2=1，解得a=-2。', 5, 1),
(2, 1, 2, '已知函数f(x)=x³-3x，则f(x)的极大值为', '{"A": "2", "B": "0", "C": "-2", "D": "4"}', 'A', "f'(x)=3x²-3=3(x+1)(x-1)。令f'(x)=0，得x=-1或x=1。当x<-1时，f'(x)>0；当-1<x<1时，f'(x)<0；当x>1时，f'(x)>0。所以x=-1时，f(x)取得极大值f(-1)=-1+3=2。", 5, 1),
(2, 1, 2, '在等差数列{aₙ}中，a₁=1，a₃+a₅=14，则公差d=', '{"A": "1", "B": "2", "C": "3", "D": "4"}', 'C', 'a₃=a₁+2d，a₅=a₁+4d，所以a₃+a₅=2a₁+6d=2+6d=14，解得d=2。', 5, 1),
(2, 1, 3, '设F₁，F₂是双曲线C：x²-y²/3=1的两个焦点，O为坐标原点，点P在C上且|OP|=2，则△PF₁F₂的面积为', '{"A": "7/2", "B": "3", "C": "5/2", "D": "2"}', 'B', '双曲线中a²=1，b²=3，所以c²=a²+b²=4，c=2，F₁(-2,0)，F₂(2,0)。由|OP|=2知，点P在以F₁F₂为直径的圆上，故PF₁⊥PF₂。所以|PF₁|²+|PF₂|²=16，又||PF₁|-|PF₂||=2，所以(|PF₁|-|PF₂|)²=4，即|PF₁|²+|PF₂|²-2|PF₁||PF₂|=4，得|PF₁||PF₂|=6。面积S=1/2|PF₁||PF₂|=3。', 5, 1),
(2, 1, 2, '已知向量a=(1,2)，b=(2,-2)，c=(1,λ)，若c∥(2a+b)，则λ=', '{"A": "1/2", "B": "-1/2", "C": "2", "D": "-2"}', 'A', '2a+b=2(1,2)+(2,-2)=(4,2)。因为c∥(2a+b)，所以4λ-1×2=0，解得λ=1/2。', 5, 1);

-- 英语题目
INSERT INTO `questions` (`subject_id`, `type`, `difficulty`, `content`, `options`, `answer`, `analysis`, `score`, `status`) VALUES
(3, 1, 1, 'The teacher told us that the earth ______ around the sun.', '{"A": "moved", "B": "moves", "C": "was moving", "D": "has moved"}', 'B', '宾语从句表示客观真理时，用一般现在时，不受主句时态影响。', 5, 1),
(3, 1, 2, 'It is generally considered unwise to give a child ______ he or she wants.', '{"A": "however", "B": "whatever", "C": "whichever", "D": "whenever"}', 'B', 'whatever引导宾语从句，在从句中作wants的宾语，意为\"无论什么\"。', 5, 1),
(3, 1, 2, 'We ______ here at nine, but we ______ up till now.', '{"A": "were supposed to arrive; don\\'t turn", "B": "were supposed to arrive; haven\\'t turned", "C": "supposed to arrive; haven\\'t turned", "D": "supposed arriving; don\\'t turn"}', 'B', 'be supposed to do sth. 表示\"应该做某事\"；till now常与现在完成时连用。', 5, 1),
(3, 1, 3, 'The reason ______ he was late was ______ he missed the early bus.', '{"A": "why; that", "B": "why; because", "C": "that; why", "D": "that; because"}', 'A', 'the reason why...is that...是固定句型，why引导定语从句，that引导表语从句。', 5, 1),
(3, 1, 2, 'Only when your identity has been checked, ______.', '{"A": "you are allowed in", "B": "you will be allowed in", "C": "will you allow in", "D": "will you be allowed in"}', 'D', 'only+状语从句置于句首时，主句要用部分倒装。根据句意应用被动语态。', 5, 1);

-- 物理题目
INSERT INTO `questions` (`subject_id`, `type`, `difficulty`, `content`, `options`, `answer`, `analysis`, `score`, `status`) VALUES
(4, 1, 1, '下列关于力的说法中，正确的是', '{"A": "只有相互接触的物体间才会产生力的作用", "B": "力是物体对物体的作用", "C": "物体受力的同时也一定在施力", "D": "力可以离开物体而单独存在"}', 'B', '力是物体对物体的作用，力不能脱离物体而存在。不接触的物体间也能产生力的作用，如磁力。物体受力的同时不一定施力。', 5, 1),
(4, 1, 2, '一个物体从静止开始做匀加速直线运动，第3秒内的位移是10m，则其加速度大小为', '{"A": "2m/s²", "B": "4m/s²", "C": "6m/s²", "D": "8m/s²"}', 'B', '第3秒内的位移等于前3秒位移减去前2秒位移：x = 1/2a·3² - 1/2a·2² = 1/2a(9-4) = 5a/2 = 10m，解得a=4m/s²。', 5, 1),
(4, 1, 2, '质量为m的物体放在水平面上，在水平恒力F作用下由静止开始运动，经时间t，速度达到v。若此时撤去F，物体又经时间2t静止。则物体与水平面间的动摩擦因数为', '{"A": "v/(gt)", "B": "v/(2gt)", "C": "v/(3gt)", "D": "2v/(3gt)"}', 'C', '加速阶段：a₁=(F-μmg)/m=v/t；减速阶段：a₂=μg=v/(2t)。所以μg=v/(2t)，μ=v/(2gt)。再代入加速阶段方程可验证，但题目只问μ，直接由减速阶段即可得出。', 5, 1),
(4, 1, 3, '如图所示，理想变压器原副线圈匝数比n₁:n₂=10:1，原线圈接入u=220√2sin100πt(V)的交变电流，副线圈接有一电动机，电动机的内阻为1Ω，电流表的示数为2A。则', '{"A": "电压表的示数为22√2V", "B": "电动机消耗的电功率为44W", "C": "电动机的输出功率为40W", "D": "电动机的输出功率为44W"}', 'C', '原线圈电压有效值U₁=220V，由U₁/U₂=n₁/n₂得U₂=22V。电动机消耗的电功率P=U₂I=22×2=44W。电动机内阻消耗的功率P热=I²R=4W。输出功率P出=P-P热=40W。', 5, 1),
(4, 1, 2, '关于布朗运动，下列说法正确的是', '{"A": "布朗运动就是分子的无规则运动", "B": "布朗运动是液体分子无规则运动的反映", "C": "悬浮颗粒越大，布朗运动越明显", "D": "温度越低，布朗运动越明显"}', 'B', '布朗运动是悬浮微粒的运动，不是分子的运动，它是液体分子无规则运动的反映。颗粒越小、温度越高，布朗运动越明显。', 5, 1);

-- 化学题目
INSERT INTO `questions` (`subject_id`, `type`, `difficulty`, `content`, `options`, `answer`, `analysis`, `score`, `status`) VALUES
(5, 1, 1, '下列物质中，属于电解质的是', '{"A": "铜", "B": "氯化钠溶液", "C": "蔗糖", "D": "氢氧化钠"}', 'D', '电解质是在水溶液中或熔融状态下能导电的化合物。铜是单质，氯化钠溶液是混合物，蔗糖是非电解质，氢氧化钠是电解质。', 5, 1),
(5, 1, 2, '设Nₐ为阿伏加德罗常数的值，下列说法正确的是', '{"A": "1mol Fe与足量盐酸反应，转移的电子数为3Nₐ", "B": "常温常压下，18g H₂O中含有的原子总数为3Nₐ", "C": "1L 1mol/L的NaCl溶液中含有Nₐ个NaCl分子", "D": "标准状况下，22.4L CHCl₃中含有氯原子数目为3Nₐ"}', 'B', 'A项Fe与盐酸反应生成Fe²⁺，转移2Nₐ电子；B项18g水为1mol，含3mol原子；C项NaCl在溶液中完全电离，不存在NaCl分子；D项标准状况下CHCl₃为液体。', 5, 1),
(5, 1, 2, '下列离子方程式书写正确的是', '{"A": "铁与稀硫酸反应：2Fe + 6H⁺ = 2Fe³⁺ + 3H₂↑", "B": "碳酸钙与稀盐酸反应：CO₃²⁻ + 2H⁺ = CO₂↑ + H₂O", "C": "钠与水反应：2Na + 2H₂O = 2Na⁺ + 2OH⁻ + H₂↑", "D": "硫酸铜溶液与氢氧化钡溶液反应：Ba²⁺ + SO₄²⁻ = BaSO₄↓"}', 'C', 'A项应生成Fe²⁺；B项碳酸钙难溶，应写化学式；D项漏掉了Cu²⁺与OH⁻的反应。', 5, 1),
(5, 1, 3, '常温下，下列各组离子在指定溶液中一定能大量共存的是', '{"A": "0.1mol·L⁻¹NaOH溶液：K⁺、Na⁺、SO₄²⁻、CO₃²⁻", "B": "0.1mol·L⁻¹Na₂CO₃溶液：K⁺、Ba²⁺、NO₃⁻、Cl⁻", "C": "0.1mol·L⁻¹FeCl₃溶液：K⁺、NH₄⁺、I⁻、SCN⁻", "D": "c(H⁺)/c(OH⁻)=1×10¹⁴的溶液：Ca²⁺、Na⁺、ClO⁻、NO₃⁻"}', 'A', 'B项Ba²⁺与CO₃²⁻生成沉淀；C项Fe³⁺与I⁻发生氧化还原反应，与SCN⁻生成络合物；D项溶液呈酸性，ClO⁻不能大量存在。', 5, 1),
(5, 1, 2, '下列关于有机物的说法正确的是', '{"A": "乙醇、乙酸和乙酸乙酯能用饱和Na₂CO₃溶液鉴别", "B": "淀粉、油脂、蛋白质都属于高分子化合物", "C": "甲烷、乙烯和苯在工业上都可通过石油分馏得到", "D": "棉、麻、丝、毛及合成纤维完全燃烧都只生成CO₂和H₂O"}', 'A', '乙醇与Na₂CO₃溶液互溶，乙酸与之反应产生气泡，乙酸乙酯与之分层，故A正确。油脂不是高分子化合物；乙烯通过石油裂解得到；丝、毛含氮元素，燃烧还有氮的氧化物生成。', 5, 1);

-- 生物题目
INSERT INTO `questions` (`subject_id`, `type`, `difficulty`, `content`, `options`, `answer`, `analysis`, `score`, `status`) VALUES
(6, 1, 1, '下列关于细胞学说的叙述，错误的是', '{"A": "细胞学说的建立者主要是施莱登和施旺", "B": "细胞学说认为一切动植物都是由细胞发育而来", "C": "细胞学说认为细胞是一个相对独立的单位", "D": "细胞学说揭示了细胞的多样性和生物体结构的多样性"}', 'D', '细胞学说揭示了细胞的统一性和生物体结构的统一性，而不是多样性。', 5, 1),
(6, 1, 2, '下列关于酶的叙述，正确的是', '{"A": "酶是活细胞产生的具有催化作用的蛋白质", "B": "酶的活性随着温度升高而不断提高", "C": "酶既可以作为催化剂，也可以作为另一个反应的底物", "D": "淀粉酶溶液中加入蛋白酶不会导致淀粉酶活性发生变化"}', 'C', '酶绝大多数是蛋白质，少数是RNA；在一定温度范围内酶活性随温度升高而升高，超过最适温度后酶活性下降；淀粉酶的化学本质是蛋白质，加入蛋白酶会将其分解。酶可以作为催化剂，也可以被其他酶分解，因此可作为底物。', 5, 1),
(6, 1, 2, '下列关于光合作用的叙述，正确的是', '{"A": "光合作用产生的O₂来自CO₂", "B": "光反应和暗反应都需要光合色素参与", "C": "暗反应只能在黑暗条件下进行", "D": "叶绿体中的色素具有吸收、传递和转化光能的作用"}', 'D', '光合作用产生的O₂来自H₂O；暗反应不需要光合色素参与；暗反应在有光无光条件下都能进行，只要有ATP和[H]。', 5, 1),
(6, 1, 3, '某双链DNA分子共有含氮碱基1400个，其中一条单链上(A+T):(G+C)=2:5。则该DNA分子连续复制两次共需游离的胸腺嘧啶脱氧核苷酸的数目是', '{"A": "300个", "B": "400个", "C": "600个", "D": "1200个"}', 'C', '一条链上(A+T):(G+C)=2:5，则整个DNA分子中(A+T):(G+C)=2:5。总碱基1400个，所以A+T=400个，又A=T，故T=200个。复制两次需要游离的T=200×(2²-1)=600个。', 5, 1),
(6, 1, 2, '下列关于生物进化的叙述，正确的是', '{"A": "自然选择决定了生物变异和进化的方向", "B": "生物进化的实质是种群基因型频率的改变", "C": "种群内基因频率的改变在世代间具有连续性", "D": "种群内基因频率改变的偶然性随种群数量下降而减小"}', 'C', '自然选择决定进化方向，但变异是不定向的；生物进化的实质是种群基因频率的改变；种群越小，基因频率改变的偶然性越大。', 5, 1);

-- ---------------------------------------------
-- 18. 帖子点赞表
-- ---------------------------------------------
DROP TABLE IF EXISTS `community_post_likes`;
CREATE TABLE `community_post_likes` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT COMMENT 'ID',
  `post_id` bigint unsigned NOT NULL DEFAULT 0 COMMENT '帖子ID',
  `user_id` bigint unsigned NOT NULL DEFAULT 0 COMMENT '用户ID',
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT '创建时间',
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_post_user` (`post_id`, `user_id`),
  KEY `idx_post_id` (`post_id`),
  KEY `idx_user_id` (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='帖子点赞表';

-- ---------------------------------------------
-- 19. 帖子收藏表
-- ---------------------------------------------
DROP TABLE IF EXISTS `community_post_favorites`;
CREATE TABLE `community_post_favorites` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT COMMENT 'ID',
  `post_id` bigint unsigned NOT NULL DEFAULT 0 COMMENT '帖子ID',
  `user_id` bigint unsigned NOT NULL DEFAULT 0 COMMENT '用户ID',
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT '创建时间',
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_post_user` (`post_id`, `user_id`),
  KEY `idx_post_id` (`post_id`),
  KEY `idx_user_id` (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='帖子收藏表';

-- ---------------------------------------------
-- 20. 评论点赞表
-- ---------------------------------------------
DROP TABLE IF EXISTS `community_comment_likes`;
CREATE TABLE `community_comment_likes` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT COMMENT 'ID',
  `comment_id` bigint unsigned NOT NULL DEFAULT 0 COMMENT '评论ID',
  `user_id` bigint unsigned NOT NULL DEFAULT 0 COMMENT '用户ID',
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT '创建时间',
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_comment_user` (`comment_id`, `user_id`),
  KEY `idx_comment_id` (`comment_id`),
  KEY `idx_user_id` (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='评论点赞表';

-- ---------------------------------------------
-- 21. 学习计划表
-- ---------------------------------------------
DROP TABLE IF EXISTS `study_plans`;
CREATE TABLE `study_plans` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT COMMENT '计划ID',
  `user_id` bigint unsigned NOT NULL DEFAULT 0 COMMENT '用户ID',
  `title` varchar(200) NOT NULL DEFAULT '' COMMENT '计划标题',
  `description` text COMMENT '计划描述',
  `subject_id` bigint unsigned NOT NULL DEFAULT 0 COMMENT '科目ID',
  `start_date` date DEFAULT NULL COMMENT '开始日期',
  `end_date` date DEFAULT NULL COMMENT '结束日期',
  `target_score` int unsigned NOT NULL DEFAULT 0 COMMENT '目标分数',
  `daily_minutes` int unsigned NOT NULL DEFAULT 0 COMMENT '每日学习分钟数',
  `progress` tinyint unsigned NOT NULL DEFAULT 0 COMMENT '进度百分比',
  `status` tinyint unsigned NOT NULL DEFAULT 1 COMMENT '状态 0未开始 1进行中 2已完成 3已取消',
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT '创建时间',
  `updated_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '更新时间',
  `deleted_at` datetime DEFAULT NULL COMMENT '删除时间',
  PRIMARY KEY (`id`),
  KEY `idx_user_id` (`user_id`),
  KEY `idx_status` (`status`),
  KEY `idx_deleted_at` (`deleted_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='学习计划表';

-- ---------------------------------------------
-- 22. 学习记录表
-- ---------------------------------------------
DROP TABLE IF EXISTS `study_records`;
CREATE TABLE `study_records` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT COMMENT '记录ID',
  `user_id` bigint unsigned NOT NULL DEFAULT 0 COMMENT '用户ID',
  `subject_id` bigint unsigned NOT NULL DEFAULT 0 COMMENT '科目ID',
  `study_type` tinyint unsigned NOT NULL DEFAULT 1 COMMENT '学习类型 1做题 2看书 3听课 4复习',
  `content` varchar(500) NOT NULL DEFAULT '' COMMENT '学习内容',
  `duration` int unsigned NOT NULL DEFAULT 0 COMMENT '学习时长(分钟)',
  `questions_count` int unsigned NOT NULL DEFAULT 0 COMMENT '做题数量',
  `correct_count` int unsigned NOT NULL DEFAULT 0 COMMENT '正确数量',
  `study_date` date NOT NULL COMMENT '学习日期',
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT '创建时间',
  `updated_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '更新时间',
  PRIMARY KEY (`id`),
  KEY `idx_user_date` (`user_id`, `study_date`),
  KEY `idx_subject_id` (`subject_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='学习记录表';

-- ---------------------------------------------
-- 23. 系统配置表
-- ---------------------------------------------
DROP TABLE IF EXISTS `sys_config`;
CREATE TABLE `sys_config` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT COMMENT '配置ID',
  `config_key` varchar(100) NOT NULL DEFAULT '' COMMENT '配置键',
  `config_value` text COMMENT '配置值',
  `config_type` varchar(20) NOT NULL DEFAULT 'string' COMMENT '配置类型 string int json',
  `description` varchar(255) NOT NULL DEFAULT '' COMMENT '配置描述',
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT '创建时间',
  `updated_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '更新时间',
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_config_key` (`config_key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='系统配置表';

-- 初始系统配置数据
INSERT INTO `sys_config` (`config_key`, `config_value`, `config_type`, `description`) VALUES
('site_name', '智考AI', 'string', '网站名称'),
('site_logo', '', 'string', '网站Logo'),
('app_download_url', '', 'string', 'App下载地址'),
('android_version', '1.0.0', 'string', 'Android最新版本'),
('ios_version', '1.0.0', 'string', 'iOS最新版本'),
('android_update_url', '', 'string', 'Android更新包地址'),
('ios_update_url', '', 'string', 'iOS更新地址'),
('update_content', '', 'string', '更新内容'),
('force_update', '0', 'int', '是否强制更新 0否 1是'),
('register_enabled', '1', 'int', '是否开放注册 0关闭 1开启'),
('daily_questions_limit', '100', 'int', '每日做题上限'),
('ai_daily_limit', '5', 'int', '每日AI预测次数'),
('vip_price_month', '29.9', 'string', 'VIP月卡价格'),
('vip_price_quarter', '79.9', 'string', 'VIP季卡价格'),
('vip_price_year', '199.9', 'string', 'VIP年卡价格'),
('agreement_user', '', 'text', '用户协议'),
('agreement_privacy', '', 'text', '隐私政策'),
('about_us', '', 'text', '关于我们'),
('contact_us', '', 'text', '联系我们'),
('customer_service', '', 'string', '客服联系方式');

SET FOREIGN_KEY_CHECKS = 1;
