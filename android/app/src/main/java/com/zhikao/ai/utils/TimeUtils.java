package com.zhikao.ai.utils;

import java.text.ParseException;
import java.text.SimpleDateFormat;
import java.util.Calendar;
import java.util.Date;
import java.util.Locale;

/**
 * 时间工具类
 * 提供时间格式化、时间差计算等功能
 */
public class TimeUtils {

    // 常用时间格式
    public static final String FORMAT_YMD = "yyyy-MM-dd";
    public static final String FORMAT_YMD_HMS = "yyyy-MM-dd HH:mm:ss";
    public static final String FORMAT_YMD_HM = "yyyy-MM-dd HH:mm";
    public static final String FORMAT_HMS = "HH:mm:ss";
    public static final String FORMAT_HM = "HH:mm";
    public static final String FORMAT_MD = "MM-dd";
    public static final String FORMAT_MD_HM = "MM-dd HH:mm";

    /**
     * 获取当前时间戳（毫秒）
     */
    public static long getCurrentTimeMillis() {
        return System.currentTimeMillis();
    }

    /**
     * 获取当前时间戳（秒）
     */
    public static long getCurrentTimeSeconds() {
        return System.currentTimeMillis() / 1000;
    }

    /**
     * 获取当前日期字符串
     * @param format 格式化模式
     */
    public static String getCurrentDate(String format) {
        return formatTime(getCurrentTimeMillis(), format);
    }

    /**
     * 格式化时间戳为日期字符串
     * @param timeMillis 时间戳（毫秒）
     * @param format 格式化模式
     */
    public static String formatTime(long timeMillis, String format) {
        SimpleDateFormat sdf = new SimpleDateFormat(format, Locale.getDefault());
        return sdf.format(new Date(timeMillis));
    }

    /**
     * 解析日期字符串为时间戳
     * @param dateStr 日期字符串
     * @param format 格式化模式
     * @return 时间戳（毫秒），解析失败返回0
     */
    public static long parseTime(String dateStr, String format) {
        try {
            SimpleDateFormat sdf = new SimpleDateFormat(format, Locale.getDefault());
            Date date = sdf.parse(dateStr);
            return date != null ? date.getTime() : 0;
        } catch (ParseException e) {
            e.printStackTrace();
            return 0;
        }
    }

    /**
     * 获取友好的时间显示（刚刚、x分钟前、x小时前、昨天、日期等）
     * @param timeMillis 时间戳（毫秒）
     */
    public static String getFriendlyTime(long timeMillis) {
        long currentTime = getCurrentTimeMillis();
        long diff = currentTime - timeMillis;

        // 1分钟内
        if (diff < 60 * 1000) {
            return "刚刚";
        }
        // 1小时内
        else if (diff < 60 * 60 * 1000) {
            return (diff / (60 * 1000)) + "分钟前";
        }
        // 今天内
        else if (isToday(timeMillis)) {
            return (diff / (60 * 60 * 1000)) + "小时前";
        }
        // 昨天
        else if (isYesterday(timeMillis)) {
            return "昨天 " + formatTime(timeMillis, FORMAT_HM);
        }
        // 今年内
        else if (isSameYear(timeMillis, currentTime)) {
            return formatTime(timeMillis, FORMAT_MD_HM);
        }
        // 其他
        else {
            return formatTime(timeMillis, FORMAT_YMD_HM);
        }
    }

    /**
     * 判断是否是今天
     */
    public static boolean isToday(long timeMillis) {
        Calendar today = Calendar.getInstance();
        Calendar time = Calendar.getInstance();
        time.setTimeInMillis(timeMillis);
        return today.get(Calendar.YEAR) == time.get(Calendar.YEAR)
                && today.get(Calendar.MONTH) == time.get(Calendar.MONTH)
                && today.get(Calendar.DAY_OF_MONTH) == time.get(Calendar.DAY_OF_MONTH);
    }

    /**
     * 判断是否是昨天
     */
    public static boolean isYesterday(long timeMillis) {
        Calendar yesterday = Calendar.getInstance();
        yesterday.add(Calendar.DAY_OF_MONTH, -1);
        Calendar time = Calendar.getInstance();
        time.setTimeInMillis(timeMillis);
        return yesterday.get(Calendar.YEAR) == time.get(Calendar.YEAR)
                && yesterday.get(Calendar.MONTH) == time.get(Calendar.MONTH)
                && yesterday.get(Calendar.DAY_OF_MONTH) == time.get(Calendar.DAY_OF_MONTH);
    }

    /**
     * 判断是否同一年
     */
    public static boolean isSameYear(long time1, long time2) {
        Calendar cal1 = Calendar.getInstance();
        cal1.setTimeInMillis(time1);
        Calendar cal2 = Calendar.getInstance();
        cal2.setTimeInMillis(time2);
        return cal1.get(Calendar.YEAR) == cal2.get(Calendar.YEAR);
    }

    /**
     * 计算两个时间之间的天数差
     * @param time1 时间戳1（毫秒）
     * @param time2 时间戳2（毫秒）
     * @return 天数差
     */
    public static int getDaysBetween(long time1, long time2) {
        Calendar cal1 = Calendar.getInstance();
        cal1.setTimeInMillis(time1);
        cal1.set(Calendar.HOUR_OF_DAY, 0);
        cal1.set(Calendar.MINUTE, 0);
        cal1.set(Calendar.SECOND, 0);
        cal1.set(Calendar.MILLISECOND, 0);

        Calendar cal2 = Calendar.getInstance();
        cal2.setTimeInMillis(time2);
        cal2.set(Calendar.HOUR_OF_DAY, 0);
        cal2.set(Calendar.MINUTE, 0);
        cal2.set(Calendar.SECOND, 0);
        cal2.set(Calendar.MILLISECOND, 0);

        long diff = Math.abs(cal1.getTimeInMillis() - cal2.getTimeInMillis());
        return (int) (diff / (24 * 60 * 60 * 1000));
    }

    /**
     * 格式化倒计时时间
     * @param seconds 剩余秒数
     * @return 格式化后的时间（HH:mm:ss）
     */
    public static String formatCountdown(long seconds) {
        long hours = seconds / 3600;
        long minutes = (seconds % 3600) / 60;
        long secs = seconds % 60;
        return String.format(Locale.getDefault(), "%02d:%02d:%02d", hours, minutes, secs);
    }

    /**
     * 获取星期几
     * @param timeMillis 时间戳（毫秒）
     * @return 星期几字符串
     */
    public static String getWeekDay(long timeMillis) {
        Calendar calendar = Calendar.getInstance();
        calendar.setTimeInMillis(timeMillis);
        int dayOfWeek = calendar.get(Calendar.DAY_OF_WEEK);
        String[] weekDays = {"周日", "周一", "周二", "周三", "周四", "周五", "周六"};
        return weekDays[dayOfWeek - 1];
    }

    /**
     * 获取年龄
     * @param birthdayMillis 生日时间戳（毫秒）
     * @return 年龄
     */
    public static int getAge(long birthdayMillis) {
        Calendar birthday = Calendar.getInstance();
        birthday.setTimeInMillis(birthdayMillis);
        Calendar now = Calendar.getInstance();

        int age = now.get(Calendar.YEAR) - birthday.get(Calendar.YEAR);
        if (now.get(Calendar.DAY_OF_YEAR) < birthday.get(Calendar.DAY_OF_YEAR)) {
            age--;
        }
        return age;
    }
}
