package com.zhikao.ai.ui.main.exam;

import android.content.Intent;
import android.os.Bundle;
import android.view.LayoutInflater;
import android.view.View;
import android.view.ViewGroup;

import androidx.annotation.NonNull;
import androidx.annotation.Nullable;
import androidx.fragment.app.Fragment;
import androidx.recyclerview.widget.LinearLayoutManager;
import androidx.recyclerview.widget.RecyclerView;

import com.google.android.material.chip.Chip;
import com.google.android.material.chip.ChipGroup;
import com.zhikao.ai.R;
import com.zhikao.ai.adapter.ExamPaperAdapter;
import com.zhikao.ai.app.ApiConfig;
import com.zhikao.ai.model.ExamPaper;
import com.zhikao.ai.ui.quiz.QuizActivity;

import java.util.ArrayList;
import java.util.List;

/**
 * 考试Fragment
 */
public class ExamFragment extends Fragment {

    private View rootView;
    private ChipGroup chipGroupSubject;
    private RecyclerView rvPaper;
    private ExamPaperAdapter paperAdapter;
    private List<ExamPaper> paperList;

    @Nullable
    @Override
    public View onCreateView(@NonNull LayoutInflater inflater, @Nullable ViewGroup container, @Nullable Bundle savedInstanceState) {
        if (rootView == null) {
            rootView = inflater.inflate(R.layout.fragment_exam, container, false);
            initViews();
            initListeners();
            initData();
        }
        return rootView;
    }

    /**
     * 初始化视图
     */
    private void initViews() {
        chipGroupSubject = rootView.findViewById(R.id.chip_group_subject);
        rvPaper = rootView.findViewById(R.id.rv_paper);
    }

    /**
     * 初始化监听器
     */
    private void initListeners() {
        chipGroupSubject.setOnCheckedStateChangeListener((group, checkedIds) -> {
            Chip checkedChip = rootView.findViewById(group.getCheckedChipId());
            if (checkedChip != null) {
                String subject = checkedChip.getText().toString();
                loadPaperList(subject);
            }
        });
    }

    /**
     * 初始化数据
     */
    private void initData() {
        // 初始化试卷列表
        paperList = new ArrayList<>();
        paperAdapter = new ExamPaperAdapter(paperList);
        rvPaper.setLayoutManager(new LinearLayoutManager(getContext()));
        rvPaper.setAdapter(paperAdapter);

        // 设置点击事件
        paperAdapter.setOnStartClickListener((position, paper) -> {
            // 跳转到答题页
            Intent intent = new Intent(getActivity(), QuizActivity.class);
            intent.putExtra(ApiConfig.INTENT_PAPER_ID, paper.getId());
            startActivity(intent);
        });

        // 默认加载全部
        loadPaperList("全部");
    }

    /**
     * 加载试卷列表
     * @param subject 科目
     */
    private void loadPaperList(String subject) {
        // 模拟数据
        List<ExamPaper> list = new ArrayList<>();
        for (int i = 0; i < 10; i++) {
            ExamPaper paper = new ExamPaper();
            paper.setId("paper_" + subject + "_" + i);
            paper.setTitle(subject + "模拟试卷 " + (i + 1));
            paper.setQuestionCount(50 + i * 10);
            paper.setDuration(90 + i * 10);
            paper.setTotalScore(100);
            paper.setStatus(i % 3);
            if (i % 3 == 2) {
                paper.setUserScore(65 + i % 20);
            }
            list.add(paper);
        }
        paperAdapter.setData(list);
    }
}
