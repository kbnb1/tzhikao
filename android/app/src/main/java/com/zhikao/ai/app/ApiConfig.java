package com.zhikao.ai.app;

/**
 * API配置类
 * 包含所有接口地址和常量配置
 */
public class ApiConfig {

    // 服务器基础地址 - 开发环境
    public static final String BASE_URL = "http://api.zhikao.com/";

    // 服务器基础地址 - 生产环境
    public static final String BASE_URL_RELEASE = "https://api.zhikaoai.com/";

    // 是否为调试模式
    public static final boolean DEBUG = true;

    // 接口超时时间（秒）
    public static final int TIMEOUT = 30;

    // ==========================================
    // 用户相关接口
    // ==========================================
    // 发送验证码
    public static final String SEND_CODE = "api/user/sendCode";
    // 登录
    public static final String LOGIN = "api/user/login";
    // 注册
    public static final String REGISTER = "api/user/register";
    // 获取用户信息
    public static final String GET_USER_INFO = "api/user/info";
    // 更新用户信息
    public static final String UPDATE_USER_INFO = "api/user/update";
    // 修改密码
    public static final String CHANGE_PASSWORD = "api/user/changePassword";
    // 退出登录
    public static final String LOGOUT = "api/user/logout";

    // ==========================================
    // 考试相关接口
    // ==========================================
    // 获取科目列表
    public static final String GET_SUBJECT_LIST = "api/exam/subjectList";
    // 获取试卷列表
    public static final String GET_PAPER_LIST = "api/exam/paperList";
    // 获取试卷详情
    public static final String GET_PAPER_DETAIL = "api/exam/paperDetail";
    // 提交试卷
    public static final String SUBMIT_PAPER = "api/exam/submit";
    // 获取考试记录
    public static final String GET_EXAM_HISTORY = "api/exam/history";

    // ==========================================
    // 题目相关接口
    // ==========================================
    // 获取题目列表
    public static final String GET_QUESTION_LIST = "api/question/list";
    // 获取题目详情
    public static final String GET_QUESTION_DETAIL = "api/question/detail";

    // ==========================================
    // 错题本相关接口
    // ==========================================
    // 获取错题列表
    public static final String GET_WRONG_LIST = "api/wrong/list";
    // 添加错题
    public static final String ADD_WRONG = "api/wrong/add";
    // 移除错题
    public static final String REMOVE_WRONG = "api/wrong/remove";
    // 清空错题
    public static final String CLEAR_WRONG = "api/wrong/clear";

    // ==========================================
    // 成绩预测相关接口
    // ==========================================
    // 获取预测结果
    public static final String GET_PREDICTION = "api/prediction/get";
    // 获取成绩趋势
    public static final String GET_SCORE_TREND = "api/prediction/trend";

    // ==========================================
    // 成就相关接口
    // ==========================================
    // 获取成就列表
    public static final String GET_ACHIEVEMENT_LIST = "api/achievement/list";

    // ==========================================
    // 学习提醒相关接口
    // ==========================================
    // 获取提醒列表
    public static final String GET_REMINDER_LIST = "api/reminder/list";
    // 添加提醒
    public static final String ADD_REMINDER = "api/reminder/add";
    // 更新提醒
    public static final String UPDATE_REMINDER = "api/reminder/update";
    // 删除提醒
    public static final String DELETE_REMINDER = "api/reminder/delete";

    // ==========================================
    // 社区相关接口
    // ==========================================
    // 获取帖子列表
    public static final String GET_POST_LIST = "api/community/postList";
    // 获取帖子详情
    public static final String GET_POST_DETAIL = "api/community/postDetail";
    // 发布帖子
    public static final String CREATE_POST = "api/community/createPost";
    // 点赞
    public static final String LIKE_POST = "api/community/like";
    // 评论
    public static final String COMMENT_POST = "api/community/comment";

    // ==========================================
    // SharedPreferences Key
    // ==========================================
    public static final String SP_TOKEN = "token";
    public static final String SP_USER_ID = "user_id";
    public static final String SP_USER_INFO = "user_info";
    public static final String SP_IS_FIRST_LAUNCH = "is_first_launch";
    public static final String SP_IS_LOGIN = "is_login";
    public static final String SP_REMEMBER_PHONE = "remember_phone";
    public static final String SP_REMEMBER_PASSWORD = "remember_password";

    // ==========================================
    // Intent Key
    // ==========================================
    public static final String INTENT_TITLE = "title";
    public static final String INTENT_URL = "url";
    public static final String INTENT_PAPER_ID = "paper_id";
    public static final String INTENT_SUBJECT_ID = "subject_id";
    public static final String INTENT_QUESTION_ID = "question_id";
    public static final String INTENT_TYPE = "type";
    public static final String INTENT_DATA = "data";
    public static final String INTENT_POSITION = "position";

    // ==========================================
    // 请求码
    // ==========================================
    public static final int REQUEST_CODE_LOGIN = 1001;
    public static final int REQUEST_CODE_REGISTER = 1002;
    public static final int REQUEST_CODE_QUIZ = 1003;

    // ==========================================
    // 其他常量
    // ==========================================
    // 分页大小
    public static final int PAGE_SIZE = 20;
    // 验证码倒计时（秒）
    public static final int CODE_COUNT_DOWN = 60;
}
