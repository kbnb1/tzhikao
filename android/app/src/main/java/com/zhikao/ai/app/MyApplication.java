package com.zhikao.ai.app;

import android.app.Application;
import android.content.Context;

import com.zhikao.ai.utils.SharedPrefs;

/**
 * Application类
 * 应用全局初始化配置
 */
public class MyApplication extends Application {

    private static MyApplication instance;
    private static Context appContext;

    @Override
    public void onCreate() {
        super.onCreate();
        instance = this;
        appContext = getApplicationContext();

        // 初始化SharedPreferences工具类
        SharedPrefs.init(this);

        // 初始化其他第三方SDK（如需要）
        // initSDK();
    }

    /**
     * 获取Application实例
     */
    public static MyApplication getInstance() {
        return instance;
    }

    /**
     * 获取全局Context
     */
    public static Context getAppContext() {
        return appContext;
    }

    /**
     * 获取登录Token
     */
    public static String getToken() {
        return SharedPrefs.getString(ApiConfig.SP_TOKEN, "");
    }

    /**
     * 设置登录Token
     */
    public static void setToken(String token) {
        SharedPrefs.putString(ApiConfig.SP_TOKEN, token);
    }

    /**
     * 判断是否已登录
     */
    public static boolean isLogin() {
        return SharedPrefs.getBoolean(ApiConfig.SP_IS_LOGIN, false);
    }

    /**
     * 设置登录状态
     */
    public static void setLogin(boolean isLogin) {
        SharedPrefs.putBoolean(ApiConfig.SP_IS_LOGIN, isLogin);
    }

    /**
     * 退出登录，清除用户数据
     */
    public static void logout() {
        SharedPrefs.remove(ApiConfig.SP_TOKEN);
        SharedPrefs.remove(ApiConfig.SP_USER_ID);
        SharedPrefs.remove(ApiConfig.SP_USER_INFO);
        SharedPrefs.putBoolean(ApiConfig.SP_IS_LOGIN, false);
    }

    /**
     * 初始化第三方SDK
     */
    private void initSDK() {
        // 在这里初始化各种第三方SDK
        // 例如：Bugly、友盟、极光推送等
    }
}
