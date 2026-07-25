package com.zhikao.ai.ui.main.home;

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
import androidx.recyclerview.widget.LinearLayoutManager;
import androidx.recyclerview.widget.RecyclerView;

import com.zhikao.ai.R;
import com.zhikao.ai.adapter.ExamPaperAdapter;
import com.zhikao.ai.app.ApiConfig;
import com.zhikao.ai.model.ExamPaper;
import com.zhikao.ai.ui.achievement.AchievementActivity;
import com.zhikao.ai.ui.prediction.PredictionActivity;
import com.zhikao.ai.ui.reminder.ReminderActivity;
import com.zhikao.ai.ui.wrong.WrongQuestionsActivity;

import java.util.ArrayList;
import java.util.List;

/**
 * 首页Fragment
 */
public class HomeFragment extends Fragment implements View.OnClickListener {

    private View rootView;
    private TextView tvStudyDays;
    private TextView tvQuestionCount;
    private TextView tvAccuracy;
    private RecyclerView rvExam;
    private ExamPaperAdapter paperAdapter;
    private List<ExamPaper> paperList;

    // 功能入口
    private LinearLayout llPrediction;
    private LinearLayout llWrong;
    private LinearLayout llReminder;
    private LinearLayout llAchievement;

    @Nullable
    @Override
    public View onCreateView(@NonNull LayoutInflater inflater, @Nullable ViewGroup container, @Nullable Bundle savedInstanceState) {
        if (rootView == null) {
            rootView = inflater.inflate(R.layout.fragment_home, container, false);
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
        tvStudyDays = rootView.findViewById(R.id.tv_study_days);
        tvQuestionCount = rootView.findViewById(R.id.tv_question_count);
        tvAccuracy = rootView.findViewById(R.id.tv_accuracy);
        rvExam = rootView.findViewById(R.id.rv_exam);

        llPrediction = rootView.findViewById(R.id.ll_prediction);
        llWrong = rootView.findViewById(R.id.ll_wrong);
        llReminder = rootView.findViewById(R.id.ll_reminder);
        llAchievement = rootView.findViewById(R.id.ll_achievement);
    }

    /**
     * 初始化监听器
     */
    private void initListeners() {
        llPrediction.setOnClickListener(this);
        llWrong.setOnClickListener(this);
        llReminder.setOnClickListener(this);
        llAchievement.setOnClickListener(this);
    }

    /**
     * 初始化数据
     */
    private void initData() {
        // 初始化热门考试列表
        paperList = new ArrayList<>();
        paperAdapter = new ExamPaperAdapter(paperList);
        rvExam.setLayoutManager(new LinearLayoutManager(getContext()));
        rvExam.setAdapter(paperAdapter);

        // 设置点击事件
        paperAdapter.setOnStartClickListener((position, paper) -> {
            // 跳转到答题页
            Intent intent = new Intent(getActivity(), com.zhikao.ai.ui.quiz.QuizActivity.class);
            intent.putExtra(ApiConfig.INTENT_PAPER_ID, paper.getId());
            startActivity(intent);
        });

        // 加载数据
        loadHomeData();
        loadHotExam();
    }

    /**
     * 加载首页数据
     */
    private void loadHomeData() {
        // 这里可以调用接口获取用户学习数据
        // 暂时使用模拟数据
        tvStudyDays.setText("15");
        tvQuestionCount.setText("520");
        tvAccuracy.setText("78%");
    }

    /**
     * 加载热门考试
     */
    private void loadHotExam() {
        // 模拟数据
        List<ExamPaper> list = new ArrayList<>();
        for (int i = 0; i < 5; i++) {
            ExamPaper paper = new ExamPaper();
            paper.setId("paper_" + i);
            paper.setTitle("热门考试模拟题 " + (i + 1));
            paper.setQuestionCount(100);
            paper.setDuration(120);
            paper.setTotalScore(100);
            paper.setStatus(0);
            list.add(paper);
        }
        paperAdapter.setData(list);
    }

    @Override
    public void onClick(View v) {
        int id = v.getId();
        Intent intent;
        if (id == R.id.ll_prediction) {
            intent = new Intent(getActivity(), PredictionActivity.class);
            startActivity(intent);
        } else if (id == R.id.ll_wrong) {
            intent = new Intent(getActivity(), WrongQuestionsActivity.class);
            startActivity(intent);
        } else if (id == R.id.ll_reminder) {
            intent = new Intent(getActivity(), ReminderActivity.class);
            startActivity(intent);
        } else if (id == R.id.ll_achievement) {
            intent = new Intent(getActivity(), AchievementActivity.class);
            startActivity(intent);
        }
    }

    @Override
    public void onResume() {
        super.onResume();
        // 页面可见时刷新数据
        loadHomeData();
    }
}
