package com.zhikao.ai.ui.guide;

import android.content.Intent;
import android.os.Bundle;
import android.view.LayoutInflater;
import android.view.View;
import android.view.ViewGroup;
import android.widget.ImageView;
import android.widget.TextView;

import androidx.annotation.NonNull;
import androidx.appcompat.app.AppCompatActivity;
import androidx.recyclerview.widget.RecyclerView;
import androidx.viewpager2.widget.ViewPager2;

import com.google.android.material.button.MaterialButton;
import com.zhikao.ai.R;
import com.zhikao.ai.ui.login.LoginActivity;

import java.util.ArrayList;
import java.util.List;

/**
 * 引导页
 */
public class GuideActivity extends AppCompatActivity {

    private ViewPager2 viewPager;
    private MaterialButton btnStart;
    private TextView tvSkip;
    private GuideAdapter adapter;
    private List<GuideItem> guideItems;

    @Override
    protected void onCreate(Bundle savedInstanceState) {
        super.onCreate(savedInstanceState);
        setContentView(R.layout.activity_guide);

        initViews();
        initData();
        initListeners();
    }

    /**
     * 初始化视图
     */
    private void initViews() {
        viewPager = findViewById(R.id.view_pager);
        btnStart = findViewById(R.id.btn_start);
        tvSkip = findViewById(R.id.tv_skip);
    }

    /**
     * 初始化数据
     */
    private void initData() {
        // 初始化引导页数据
        guideItems = new ArrayList<>();
        guideItems.add(new GuideItem(
                getString(R.string.guide_title_1),
                getString(R.string.guide_desc_1),
                0
        ));
        guideItems.add(new GuideItem(
                getString(R.string.guide_title_2),
                getString(R.string.guide_desc_2),
                0
        ));
        guideItems.add(new GuideItem(
                getString(R.string.guide_title_3),
                getString(R.string.guide_desc_3),
                0
        ));

        // 设置适配器
        adapter = new GuideAdapter(guideItems);
        viewPager.setAdapter(adapter);
    }

    /**
     * 初始化监听器
     */
    private void initListeners() {
        // ViewPager2页面切换监听
        viewPager.registerOnPageChangeCallback(new ViewPager2.OnPageChangeCallback() {
            @Override
            public void onPageSelected(int position) {
                super.onPageSelected(position);
                // 最后一页显示开始按钮
                if (position == guideItems.size() - 1) {
                    btnStart.setVisibility(View.VISIBLE);
                    tvSkip.setVisibility(View.GONE);
                } else {
                    btnStart.setVisibility(View.GONE);
                    tvSkip.setVisibility(View.VISIBLE);
                }
                // 更新指示器
                updateIndicator(position);
            }
        });

        // 开始使用按钮
        btnStart.setOnClickListener(v -> goToLogin());

        // 跳过按钮
        tvSkip.setOnClickListener(v -> goToLogin());
    }

    /**
     * 更新指示器
     */
    private void updateIndicator(int position) {
        // 这里可以根据需要更新指示器的状态
        // 简单实现可以通过修改ll_indicator中的子View背景
    }

    /**
     * 跳转到登录页
     */
    private void goToLogin() {
        Intent intent = new Intent(this, LoginActivity.class);
        startActivity(intent);
        finish();
    }

    /**
     * 引导页数据模型
     */
    private static class GuideItem {
        String title;
        String description;
        int imageRes;

        GuideItem(String title, String description, int imageRes) {
            this.title = title;
            this.description = description;
            this.imageRes = imageRes;
        }
    }

    /**
     * 引导页适配器
     */
    private static class GuideAdapter extends RecyclerView.Adapter<GuideAdapter.GuideViewHolder> {

        private List<GuideItem> items;

        GuideAdapter(List<GuideItem> items) {
            this.items = items;
        }

        @NonNull
        @Override
        public GuideViewHolder onCreateViewHolder(@NonNull ViewGroup parent, int viewType) {
            View view = LayoutInflater.from(parent.getContext())
                    .inflate(R.layout.item_guide, parent, false);
            return new GuideViewHolder(view);
        }

        @Override
        public void onBindViewHolder(@NonNull GuideViewHolder holder, int position) {
            GuideItem item = items.get(position);
            holder.tvTitle.setText(item.title);
            holder.tvDescription.setText(item.description);
            // 如果有图片资源，可以在这里设置
        }

        @Override
        public int getItemCount() {
            return items.size();
        }

        static class GuideViewHolder extends RecyclerView.ViewHolder {
            ImageView ivImage;
            TextView tvTitle;
            TextView tvDescription;

            GuideViewHolder(@NonNull View itemView) {
                super(itemView);
                ivImage = itemView.findViewById(R.id.iv_image);
                tvTitle = itemView.findViewById(R.id.tv_title);
                tvDescription = itemView.findViewById(R.id.tv_description);
            }
        }
    }
}
