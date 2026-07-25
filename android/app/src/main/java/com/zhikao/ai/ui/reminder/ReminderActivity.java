package com.zhikao.ai.ui.reminder;

import android.os.Bundle;
import android.view.View;
import android.widget.LinearLayout;

import androidx.appcompat.app.AppCompatActivity;
import androidx.recyclerview.widget.LinearLayoutManager;
import androidx.recyclerview.widget.RecyclerView;

import com.google.android.material.button.MaterialButton;
import com.zhikao.ai.R;
import com.zhikao.ai.utils.ToastUtils;

/**
 * 学习提醒页
 */
public class ReminderActivity extends AppCompatActivity implements View.OnClickListener {

    private RecyclerView rvReminder;
    private LinearLayout llEmpty;
    private MaterialButton btnAddReminder;
    private View ivAdd;

    @Override
    protected void onCreate(Bundle savedInstanceState) {
        super.onCreate(savedInstanceState);
        setContentView(R.layout.activity_reminder);

        initViews();
        initListeners();
        loadReminderList();
    }

    /**
     * 初始化视图
     */
    private void initViews() {
        rvReminder = findViewById(R.id.rv_reminder);
        llEmpty = findViewById(R.id.ll_empty);
        btnAddReminder = findViewById(R.id.btn_add_reminder);
        ivAdd = findViewById(R.id.iv_add);

        // 返回按钮
        findViewById(R.id.toolbar).setOnClickListener(v -> finish());
    }

    /**
     * 初始化监听器
     */
    private void initListeners() {
        btnAddReminder.setOnClickListener(this);
        if (ivAdd != null) {
            ivAdd.setOnClickListener(this);
        }
    }

    /**
     * 加载提醒列表
     */
    private void loadReminderList() {
        // 模拟数据 - 显示空状态
        boolean hasData = false;

        if (hasData) {
            rvReminder.setVisibility(View.VISIBLE);
            llEmpty.setVisibility(View.GONE);
            rvReminder.setLayoutManager(new LinearLayoutManager(this));
            // rvReminder.setAdapter(new ReminderAdapter(reminderList));
        } else {
            rvReminder.setVisibility(View.GONE);
            llEmpty.setVisibility(View.VISIBLE);
        }
    }

    @Override
    public void onClick(View v) {
        int id = v.getId();
        if (id == R.id.btn_add_reminder || id == R.id.iv_add) {
            addReminder();
        }
    }

    /**
     * 添加提醒
     */
    private void addReminder() {
        ToastUtils.showShort("添加提醒功能开发中...");
    }
}
