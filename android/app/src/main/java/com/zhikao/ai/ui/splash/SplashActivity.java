package com.zhikao.ai.ui.splash;

import android.content.Intent;
import android.os.Bundle;
import android.os.Handler;
import android.os.Looper;

import androidx.appcompat.app.AppCompatActivity;

import com.zhikao.ai.R;
import com.zhikao.ai.app.ApiConfig;
import com.zhikao.ai.app.MyApplication;
import com.zhikao.ai.ui.guide.GuideActivity;
import com.zhikao.ai.ui.login.LoginActivity;
import com.zhikao.ai.ui.main.MainActivity;
import com.zhikao.ai.utils.SharedPrefs;

/**
 * 启动页
 */
public class SplashActivity extends AppCompatActivity {

    private static final int SPLASH_DELAY = 2000; // 启动页显示时间（毫秒）

    @Override
    protected void onCreate(Bundle savedInstanceState) {
        super.onCreate(savedInstanceState);
        setContentView(R.layout.activity_splash);

        // 延迟跳转
        new Handler(Looper.getMainLooper()).postDelayed(this::navigateToNext, SPLASH_DELAY);
    }

    /**
     * 跳转到下一个页面
     */
    private void navigateToNext() {
        Intent intent;

        // 判断是否是第一次启动
        boolean isFirstLaunch = SharedPrefs.getBoolean(ApiConfig.SP_IS_FIRST_LAUNCH, true);

        if (isFirstLaunch) {
            // 第一次启动，跳转到引导页
            intent = new Intent(this, GuideActivity.class);
            // 标记已启动过
            SharedPrefs.putBoolean(ApiConfig.SP_IS_FIRST_LAUNCH, false);
        } else if (MyApplication.isLogin()) {
            // 已登录，跳转到主页
            intent = new Intent(this, MainActivity.class);
        } else {
            // 未登录，跳转到登录页
            intent = new Intent(this, LoginActivity.class);
        }

        startActivity(intent);
        finish();
    }
}
