package com.zhikao.ai.utils;

import android.content.Context;
import android.content.SharedPreferences;

import com.zhikao.ai.app.MyApplication;

/**
 * SharedPreferences工具类
 * 轻量级数据存储
 */
public class SharedPrefs {

    private static final String PREFS_NAME = "zhikao_ai_prefs";
    private static SharedPreferences sharedPreferences;
    private static SharedPreferences.Editor editor;

    /**
     * 初始化
     */
    public static void init(Context context) {
        sharedPreferences = context.getSharedPreferences(PREFS_NAME, Context.MODE_PRIVATE);
        editor = sharedPreferences.edit();
    }

    /**
     * 检查是否已初始化
     */
    private static void checkInit() {
        if (sharedPreferences == null) {
            init(MyApplication.getAppContext());
        }
    }

    /**
     * 保存String类型数据
     */
    public static void putString(String key, String value) {
        checkInit();
        editor.putString(key, value);
        editor.apply();
    }

    /**
     * 获取String类型数据
     */
    public static String getString(String key, String defaultValue) {
        checkInit();
        return sharedPreferences.getString(key, defaultValue);
    }

    /**
     * 保存int类型数据
     */
    public static void putInt(String key, int value) {
        checkInit();
        editor.putInt(key, value);
        editor.apply();
    }

    /**
     * 获取int类型数据
     */
    public static int getInt(String key, int defaultValue) {
        checkInit();
        return sharedPreferences.getInt(key, defaultValue);
    }

    /**
     * 保存boolean类型数据
     */
    public static void putBoolean(String key, boolean value) {
        checkInit();
        editor.putBoolean(key, value);
        editor.apply();
    }

    /**
     * 获取boolean类型数据
     */
    public static boolean getBoolean(String key, boolean defaultValue) {
        checkInit();
        return sharedPreferences.getBoolean(key, defaultValue);
    }

    /**
     * 保存float类型数据
     */
    public static void putFloat(String key, float value) {
        checkInit();
        editor.putFloat(key, value);
        editor.apply();
    }

    /**
     * 获取float类型数据
     */
    public static float getFloat(String key, float defaultValue) {
        checkInit();
        return sharedPreferences.getFloat(key, defaultValue);
    }

    /**
     * 保存long类型数据
     */
    public static void putLong(String key, long value) {
        checkInit();
        editor.putLong(key, value);
        editor.apply();
    }

    /**
     * 获取long类型数据
     */
    public static long getLong(String key, long defaultValue) {
        checkInit();
        return sharedPreferences.getLong(key, defaultValue);
    }

    /**
     * 移除指定key的数据
     */
    public static void remove(String key) {
        checkInit();
        editor.remove(key);
        editor.apply();
    }

    /**
     * 清空所有数据
     */
    public static void clear() {
        checkInit();
        editor.clear();
        editor.apply();
    }

    /**
     * 检查是否包含指定key
     */
    public static boolean contains(String key) {
        checkInit();
        return sharedPreferences.contains(key);
    }
}
