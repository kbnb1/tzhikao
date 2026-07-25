package com.zhikao.ai.ui.main.profile;

import android.content.Intent;
import android.os.Bundle;
import android.view.LayoutInflater;
import android.view.View;
import android.view.ViewGroup;
import android.widget.LinearLayout;
import android.widget.TextView;

import androidx.annotation.NonNull;
import androidx.annotation.Nullable;
import androidx.fragment.app.Fragment;

import com.google.android.material.button.MaterialButton;
import com.zhikao.ai.R;
import com.zhikao.ai.app.MyApplication;
import com.zhikao.ai.model.User;
import com.zhikao.ai.ui.login.LoginActivity;
import com.zhikao.ai.utils.ToastUtils;

/**
 * 我的Fragment
 */
public class ProfileFragment extends Fragment implements View.OnClickListener {

    private View rootView;
    private TextView tvUsername;
    private TextView tvId;
    private TextView tvStudyDays;
    private TextView tvExamCount;
    private TextView tvAchievementCount;
    private LinearLayout llPersonalInfo;
    private LinearLayout llMyCollection;
    private LinearLayout llMyNotes;
    private LinearLayout llSettings;
    private LinearLayout llFeedback;
    private LinearLayout llAbout;
    private MaterialButton btnLogout;

    @Nullable
    @Override
    public View onCreateView(@NonNull LayoutInflater inflater, @Nullable ViewGroup container, @Nullable Bundle savedInstanceState) {
        if (rootView == null) {
            rootView = inflater.inflate(R.layout.fragment_profile, container, false);
            initViews();
            initListeners();
            loadUserInfo();
        }
        return rootView;
    }

    /**
     * 初始化视图
     */
    private void initViews() {
        tvUsername = rootView.findViewById(R.id.tv_username);
        tvId = rootView.findViewById(R.id.tv_id);
        tvStudyDays = rootView.findViewById(R.id.tv_study_days);
        tvExamCount = rootView.findViewById(R.id.tv_exam_count);
        tvAchievementCount = rootView.findViewById(R.id.tv_achievement_count);
        llPersonalInfo = rootView.findViewById(R.id.ll_personal_info);
        llMyCollection = rootView.findViewById(R.id.ll_my_collection);
        llMyNotes = rootView.findViewById(R.id.ll_my_notes);
        llSettings = rootView.findViewById(R.id.ll_settings);
        llFeedback = rootView.findViewById(R.id.ll_feedback);
        llAbout = rootView.findViewById(R.id.ll_about);
        btnLogout = rootView.findViewById(R.id.btn_logout);
    }

    /**
     * 初始化监听器
     */
    private void initListeners() {
        llPersonalInfo.setOnClickListener(this);
        llMyCollection.setOnClickListener(this);
        llMyNotes.setOnClickListener(this);
        llSettings.setOnClickListener(this);
        llFeedback.setOnClickListener(this);
        llAbout.setOnClickListener(this);
        btnLogout.setOnClickListener(this);
    }

    /**
     * 加载用户信息
     */
    private void loadUserInfo() {
        User user = getCurrentUser();
        if (user != null) {
            tvUsername.setText(user.getNickname() != null ? user.getNickname() : "用户昵称");
            tvId.setText("ID: " + (user.getId() != null ? user.getId() : "100001"));
            tvStudyDays.setText(String.valueOf(user.getStudyDays()));
            tvExamCount.setText(String.valueOf(user.getExamCount()));
            tvAchievementCount.setText(String.valueOf(user.getAchievementCount()));
        }
    }

    /**
     * 获取当前用户信息
     */
    private User getCurrentUser() {
        // 从SharedPreferences中获取用户信息
        // 这里返回null表示暂无数据，实际项目中需要解析JSON
        return null;
    }

    @Override
    public void onClick(View v) {
        int id = v.getId();
        if (id == R.id.ll_personal_info) {
            ToastUtils.showShort("个人信息");
        } else if (id == R.id.ll_my_collection) {
            ToastUtils.showShort("我的收藏");
        } else if (id == R.id.ll_my_notes) {
            ToastUtils.showShort("我的笔记");
        } else if (id == R.id.ll_settings) {
            ToastUtils.showShort("设置");
        } else if (id == R.id.ll_feedback) {
            ToastUtils.showShort("意见反馈");
        } else if (id == R.id.ll_about) {
            ToastUtils.showShort("关于我们");
        } else if (id == R.id.btn_logout) {
            logout();
        }
    }

    /**
     * 退出登录
     */
    private void logout() {
        MyApplication.logout();
        ToastUtils.showShort("已退出登录");
        // 跳转到登录页
        Intent intent = new Intent(getActivity(), LoginActivity.class);
        intent.setFlags(Intent.FLAG_ACTIVITY_NEW_TASK | Intent.FLAG_ACTIVITY_CLEAR_TASK);
        startActivity(intent);
        if (getActivity() != null) {
            getActivity().finish();
        }
    }

    @Override
    public void onResume() {
        super.onResume();
        // 页面可见时刷新用户信息
        loadUserInfo();
    }
}
