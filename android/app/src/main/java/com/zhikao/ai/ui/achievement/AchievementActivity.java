package com.zhikao.ai.ui.achievement;

import android.os.Bundle;
import android.widget.TextView;

import androidx.appcompat.app.AppCompatActivity;
import androidx.recyclerview.widget.GridLayoutManager;
import androidx.recyclerview.widget.RecyclerView;

import com.zhikao.ai.R;

/**
 * 成就页
 */
public class AchievementActivity extends AppCompatActivity {

    private TextView tvUnlockedCount;
    private TextView tvTotalCount;
    private TextView tvProgress;
    private RecyclerView rvAchievement;

    @Override
    protected void onCreate(Bundle savedInstanceState) {
        super.onCreate(savedInstanceState);
        setContentView(R.layout.activity_achievement);

        initViews();
        initData();
    }

    /**
     * 初始化视图
     */
    private void initViews() {
        tvUnlockedCount = findViewById(R.id.tv_unlocked_count);
        tvTotalCount = findViewById(R.id.tv_total_count);
        tvProgress = findViewById(R.id.tv_progress);
        rvAchievement = findViewById(R.id.rv_achievement);

        // 返回按钮
        findViewById(R.id.toolbar).setOnClickListener(v -> finish());
    }

    /**
     * 初始化数据
     */
    private void initData() {
        // 模拟数据
        int unlockedCount = 8;
        int totalCount = 20;
        int progress = (int) (unlockedCount * 100.0 / totalCount);

        tvUnlockedCount.setText(String.valueOf(unlockedCount));
        tvTotalCount.setText(String.valueOf(totalCount));
        tvProgress.setText(progress + "%");

        // 设置成就列表（网格布局）
        rvAchievement.setLayoutManager(new GridLayoutManager(this, 3));
        // rvAchievement.setAdapter(new AchievementAdapter(achievementList));
    }
}
