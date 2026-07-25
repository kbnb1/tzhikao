package com.zhikao.ai.utils;

import android.content.Context;
import android.widget.Toast;

import com.zhikao.ai.app.MyApplication;

/**
 * Toast工具类
 * 统一管理Toast显示，避免重复弹出
 */
public class ToastUtils {

    private static Toast toast;

    /**
     * 显示短时间Toast
     * @param message 消息内容
     */
    public static void showShort(String message) {
        show(MyApplication.getAppContext(), message, Toast.LENGTH_SHORT);
    }

    /**
     * 显示短时间Toast
     * @param context 上下文
     * @param message 消息内容
     */
    public static void showShort(Context context, String message) {
        show(context, message, Toast.LENGTH_SHORT);
    }

    /**
     * 显示长时间Toast
     * @param message 消息内容
     */
    public static void showLong(String message) {
        show(MyApplication.getAppContext(), message, Toast.LENGTH_LONG);
    }

    /**
     * 显示长时间Toast
     * @param context 上下文
     * @param message 消息内容
     */
    public static void showLong(Context context, String message) {
        show(context, message, Toast.LENGTH_LONG);
    }

    /**
     * 显示Toast
     * @param context 上下文
     * @param message 消息内容
     * @param duration 持续时间
     */
    private static void show(Context context, String message, int duration) {
        if (context == null || message == null || message.isEmpty()) {
            return;
        }

        // 取消上一个Toast，避免重复显示
        if (toast != null) {
            toast.cancel();
        }

        toast = Toast.makeText(context.getApplicationContext(), message, duration);
        toast.show();
    }

    /**
     * 取消当前显示的Toast
     */
    public static void cancel() {
        if (toast != null) {
            toast.cancel();
            toast = null;
        }
    }
}
