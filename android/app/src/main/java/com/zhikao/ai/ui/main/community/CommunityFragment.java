package com.zhikao.ai.ui.main.community;

import android.os.Bundle;
import android.view.LayoutInflater;
import android.view.View;
import android.view.ViewGroup;

import androidx.annotation.NonNull;
import androidx.annotation.Nullable;
import androidx.fragment.app.Fragment;
import androidx.viewpager2.adapter.FragmentStateAdapter;
import androidx.viewpager2.widget.ViewPager2;

import com.google.android.material.tabs.TabLayout;
import com.google.android.material.tabs.TabLayoutMediator;
import com.zhikao.ai.R;

import java.util.ArrayList;
import java.util.List;

/**
 * 社区Fragment
 */
public class CommunityFragment extends Fragment {

    private View rootView;
    private TabLayout tabLayout;
    private ViewPager2 viewPager;
    private List<Fragment> fragmentList;
    private String[] tabTitles = {"热门", "最新"};

    @Nullable
    @Override
    public View onCreateView(@NonNull LayoutInflater inflater, @Nullable ViewGroup container, @Nullable Bundle savedInstanceState) {
        if (rootView == null) {
            rootView = inflater.inflate(R.layout.fragment_community, container, false);
            initViews();
            initViewPager();
        }
        return rootView;
    }

    /**
     * 初始化视图
     */
    private void initViews() {
        tabLayout = rootView.findViewById(R.id.tab_layout);
        viewPager = rootView.findViewById(R.id.view_pager);
    }

    /**
     * 初始化ViewPager
     */
    private void initViewPager() {
        fragmentList = new ArrayList<>();
        fragmentList.add(new HotPostFragment());
        fragmentList.add(new LatestPostFragment());

        viewPager.setAdapter(new FragmentStateAdapter(this) {
            @NonNull
            @Override
            public Fragment createFragment(int position) {
                return fragmentList.get(position);
            }

            @Override
            public int getItemCount() {
                return fragmentList.size();
            }
        });

        // 关联TabLayout和ViewPager2
        new TabLayoutMediator(tabLayout, viewPager,
                (tab, position) -> tab.setText(tabTitles[position])
        ).attach();
    }

    /**
     * 热门帖子Fragment
     */
    public static class HotPostFragment extends Fragment {
        @Nullable
        @Override
        public View onCreateView(@NonNull LayoutInflater inflater, @Nullable ViewGroup container, @Nullable Bundle savedInstanceState) {
            View view = inflater.inflate(R.layout.fragment_post_list, container, false);
            // 初始化热门帖子列表
            return view;
        }
    }

    /**
     * 最新帖子Fragment
     */
    public static class LatestPostFragment extends Fragment {
        @Nullable
        @Override
        public View onCreateView(@NonNull LayoutInflater inflater, @Nullable ViewGroup container, @Nullable Bundle savedInstanceState) {
            View view = inflater.inflate(R.layout.fragment_post_list, container, false);
            // 初始化最新帖子列表
            return view;
        }
    }
}
