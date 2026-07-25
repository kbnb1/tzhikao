package com.zhikao.ai.ui.main;

import android.os.Bundle;

import androidx.appcompat.app.AppCompatActivity;
import androidx.fragment.app.Fragment;
import androidx.fragment.app.FragmentManager;
import androidx.fragment.app.FragmentTransaction;

import com.google.android.material.bottomnavigation.BottomNavigationView;
import com.zhikao.ai.R;
import com.zhikao.ai.ui.main.community.CommunityFragment;
import com.zhikao.ai.ui.main.exam.ExamFragment;
import com.zhikao.ai.ui.main.home.HomeFragment;
import com.zhikao.ai.ui.main.profile.ProfileFragment;

import java.util.ArrayList;
import java.util.List;

/**
 * 主页
 * 底部导航 + Fragment切换
 */
public class MainActivity extends AppCompatActivity {

    private BottomNavigationView bottomNavView;
    private List<Fragment> fragmentList;
    private int currentFragmentIndex = -1;

    @Override
    protected void onCreate(Bundle savedInstanceState) {
        super.onCreate(savedInstanceState);
        setContentView(R.layout.activity_main);

        initViews();
        initFragments();
        initListeners();

        // 默认显示首页
        switchFragment(0);
    }

    /**
     * 初始化视图
     */
    private void initViews() {
        bottomNavView = findViewById(R.id.bottom_nav_view);
    }

    /**
     * 初始化Fragment
     */
    private void initFragments() {
        fragmentList = new ArrayList<>();
        fragmentList.add(new HomeFragment());
        fragmentList.add(new ExamFragment());
        fragmentList.add(new CommunityFragment());
        fragmentList.add(new ProfileFragment());
    }

    /**
     * 初始化监听器
     */
    private void initListeners() {
        bottomNavView.setOnItemSelectedListener(item -> {
            int itemId = item.getItemId();
            if (itemId == R.id.navigation_home) {
                switchFragment(0);
                return true;
            } else if (itemId == R.id.navigation_exam) {
                switchFragment(1);
                return true;
            } else if (itemId == R.id.navigation_community) {
                switchFragment(2);
                return true;
            } else if (itemId == R.id.navigation_profile) {
                switchFragment(3);
                return true;
            }
            return false;
        });
    }

    /**
     * 切换Fragment
     * @param index Fragment索引
     */
    private void switchFragment(int index) {
        if (index == currentFragmentIndex) {
            return;
        }

        FragmentManager fragmentManager = getSupportFragmentManager();
        FragmentTransaction transaction = fragmentManager.beginTransaction();

        // 隐藏当前Fragment
        if (currentFragmentIndex != -1 && fragmentList.get(currentFragmentIndex) != null) {
            transaction.hide(fragmentList.get(currentFragmentIndex));
        }

        // 显示目标Fragment
        Fragment targetFragment = fragmentList.get(index);
        if (targetFragment.isAdded()) {
            transaction.show(targetFragment);
        } else {
            transaction.add(R.id.fl_container, targetFragment);
        }

        transaction.commitAllowingStateLoss();
        currentFragmentIndex = index;
    }

    @Override
    public void onBackPressed() {
        // 首页时按返回键退出应用
        if (currentFragmentIndex == 0) {
            super.onBackPressed();
        } else {
            // 其他页面返回首页
            bottomNavView.setSelectedItemId(R.id.navigation_home);
        }
    }
}
