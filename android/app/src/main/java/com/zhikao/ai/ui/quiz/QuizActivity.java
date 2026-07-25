package com.zhikao.ai.ui.quiz;

import android.os.Bundle;
import android.os.CountDownTimer;
import android.view.View;
import android.widget.ProgressBar;
import android.widget.TextView;

import androidx.appcompat.app.AlertDialog;
import androidx.appcompat.app.AppCompatActivity;
import androidx.cardview.widget.CardView;

import com.google.android.material.button.MaterialButton;
import com.zhikao.ai.R;
import com.zhikao.ai.app.ApiConfig;
import com.zhikao.ai.model.ExamPaper;
import com.zhikao.ai.model.Question;
import com.zhikao.ai.utils.TimeUtils;
import com.zhikao.ai.utils.ToastUtils;

import java.util.ArrayList;
import java.util.List;

/**
 * 答题页
 */
public class QuizActivity extends AppCompatActivity implements View.OnClickListener {

    private TextView tvTimer;
    private ProgressBar progressBar;
    private TextView tvQuestionNum;
    private TextView tvQuestionType;
    private TextView tvQuestionContent;
    private CardView optionA, optionB, optionC, optionD;
    private TextView tvOptionA, tvOptionB, tvOptionC, tvOptionD;
    private CardView cardAnalysis;
    private TextView tvAnalysis;
    private MaterialButton btnPrevious;
    private MaterialButton btnCollect;
    private MaterialButton btnNext;

    private String paperId;
    private ExamPaper examPaper;
    private List<Question> questionList;
    private int currentIndex = 0;
    private CountDownTimer countDownTimer;
    private long remainingSeconds;

    @Override
    protected void onCreate(Bundle savedInstanceState) {
        super.onCreate(savedInstanceState);
        setContentView(R.layout.activity_quiz);

        // 获取传递的参数
        paperId = getIntent().getStringExtra(ApiConfig.INTENT_PAPER_ID);

        initViews();
        initListeners();
        initData();
    }

    /**
     * 初始化视图
     */
    private void initViews() {
        tvTimer = findViewById(R.id.tv_timer);
        progressBar = findViewById(R.id.progress_bar);
        tvQuestionNum = findViewById(R.id.tv_question_num);
        tvQuestionType = findViewById(R.id.tv_question_type);
        tvQuestionContent = findViewById(R.id.tv_question_content);
        optionA = findViewById(R.id.option_a);
        optionB = findViewById(R.id.option_b);
        optionC = findViewById(R.id.option_c);
        optionD = findViewById(R.id.option_d);
        tvOptionA = findViewById(R.id.tv_option_a);
        tvOptionB = findViewById(R.id.tv_option_b);
        tvOptionC = findViewById(R.id.tv_option_c);
        tvOptionD = findViewById(R.id.tv_option_d);
        cardAnalysis = findViewById(R.id.card_analysis);
        tvAnalysis = findViewById(R.id.tv_analysis);
        btnPrevious = findViewById(R.id.btn_previous);
        btnCollect = findViewById(R.id.btn_collect);
        btnNext = findViewById(R.id.btn_next);

        // 返回按钮
        findViewById(R.id.toolbar).setOnClickListener(v -> showExitDialog());
    }

    /**
     * 初始化监听器
     */
    private void initListeners() {
        optionA.setOnClickListener(this);
        optionB.setOnClickListener(this);
        optionC.setOnClickListener(this);
        optionD.setOnClickListener(this);
        btnPrevious.setOnClickListener(this);
        btnCollect.setOnClickListener(this);
        btnNext.setOnClickListener(this);
    }

    /**
     * 初始化数据
     */
    private void initData() {
        // 模拟数据
        questionList = new ArrayList<>();
        for (int i = 0; i < 10; i++) {
            Question question = new Question();
            question.setId("q_" + i);
            question.setType(1);
            question.setContent("第" + (i + 1) + "题：以下哪个选项是正确的？");
            List<String> options = new ArrayList<>();
            options.add("选项A内容");
            options.add("选项B内容");
            options.add("选项C内容");
            options.add("选项D内容");
            question.setOptions(options);
            question.setAnswer("A");
            question.setAnalysis("这是答案解析内容，详细说明为什么选A。");
            question.setScore(10);
            questionList.add(question);
        }

        // 初始化考试时长（120分钟）
        remainingSeconds = 120 * 60;

        // 显示第一题
        showQuestion(0);

        // 开始倒计时
        startCountDown();
    }

    /**
     * 显示指定位置的题目
     */
    private void showQuestion(int index) {
        if (questionList == null || index < 0 || index >= questionList.size()) {
            return;
        }

        currentIndex = index;
        Question question = questionList.get(index);

        // 更新进度
        int progress = (int) ((index + 1) * 100.0 / questionList.size());
        progressBar.setProgress(progress);

        // 题号和题型
        tvQuestionNum.setText("第 " + (index + 1) + " 题");
        tvQuestionType.setText(question.getTypeText());

        // 题目内容
        tvQuestionContent.setText(question.getContent());

        // 选项
        if (question.getOptions() != null && question.getOptions().size() >= 4) {
            tvOptionA.setText(question.getOptions().get(0));
            tvOptionB.setText(question.getOptions().get(1));
            tvOptionC.setText(question.getOptions().get(2));
            tvOptionD.setText(question.getOptions().get(3));
        }

        // 重置选项状态
        resetOptionState();

        // 如果已作答，显示答案状态
        if (question.isAnswered()) {
            showAnswerResult(question);
        } else {
            cardAnalysis.setVisibility(View.GONE);
        }

        // 收藏状态
        btnCollect.setIconResource(question.isCollected()
                ? android.R.drawable.star_big_on
                : android.R.drawable.star_big_off);

        // 上一题/下一题按钮状态
        btnPrevious.setEnabled(index > 0);
        if (index == questionList.size() - 1) {
            btnNext.setText(R.string.submit_paper);
        } else {
            btnNext.setText(R.string.next_question);
        }
    }

    /**
     * 重置选项状态
     */
    private void resetOptionState() {
        optionA.setCardBackgroundColor(getResources().getColor(R.color.white));
        optionB.setCardBackgroundColor(getResources().getColor(R.color.white));
        optionC.setCardBackgroundColor(getResources().getColor(R.color.white));
        optionD.setCardBackgroundColor(getResources().getColor(R.color.white));
    }

    /**
     * 显示答题结果
     */
    private void showAnswerResult(Question question) {
        String userAnswer = question.getUserAnswer();
        String correctAnswer = question.getAnswer();

        // 正确答案标绿
        if ("A".equals(correctAnswer)) {
            optionA.setCardBackgroundColor(getResources().getColor(R.color.success));
        } else if ("B".equals(correctAnswer)) {
            optionB.setCardBackgroundColor(getResources().getColor(R.color.success));
        } else if ("C".equals(correctAnswer)) {
            optionC.setCardBackgroundColor(getResources().getColor(R.color.success));
        } else if ("D".equals(correctAnswer)) {
            optionD.setCardBackgroundColor(getResources().getColor(R.color.success));
        }

        // 如果答错，错误答案标红
        if (!question.isCorrect() && userAnswer != null) {
            if ("A".equals(userAnswer)) {
                optionA.setCardBackgroundColor(getResources().getColor(R.color.error));
            } else if ("B".equals(userAnswer)) {
                optionB.setCardBackgroundColor(getResources().getColor(R.color.error));
            } else if ("C".equals(userAnswer)) {
                optionC.setCardBackgroundColor(getResources().getColor(R.color.error));
            } else if ("D".equals(userAnswer)) {
                optionD.setCardBackgroundColor(getResources().getColor(R.color.error));
            }
        }

        // 显示解析
        cardAnalysis.setVisibility(View.VISIBLE);
        tvAnalysis.setText(question.getAnalysis());
    }

    /**
     * 选择选项
     */
    private void selectOption(String option) {
        Question question = questionList.get(currentIndex);
        if (question.isAnswered()) {
            // 已作答的不能修改
            return;
        }

        // 设置用户答案
        question.setUserAnswer(option);
        question.setAnswered(true);
        question.setCorrect(option.equals(question.getAnswer()));

        // 如果答错，加入错题本
        if (!question.isCorrect()) {
            question.setWrong(true);
        }

        // 显示结果
        showAnswerResult(question);
    }

    /**
     * 开始倒计时
     */
    private void startCountDown() {
        countDownTimer = new CountDownTimer(remainingSeconds * 1000, 1000) {
            @Override
            public void onTick(long millisUntilFinished) {
                remainingSeconds = millisUntilFinished / 1000;
                tvTimer.setText(TimeUtils.formatCountdown(remainingSeconds));
            }

            @Override
            public void onFinish() {
                // 时间到，自动交卷
                submitPaper();
            }
        };
        countDownTimer.start();
    }

    /**
     * 显示退出对话框
     */
    private void showExitDialog() {
        new AlertDialog.Builder(this)
                .setTitle("提示")
                .setMessage("确定要退出答题吗？答题进度将不会保存。")
                .setPositiveButton("确定", (dialog, which) -> finish())
                .setNegativeButton("取消", null)
                .show();
    }

    /**
     * 交卷
     */
    private void submitPaper() {
        if (countDownTimer != null) {
            countDownTimer.cancel();
        }

        // 计算得分
        int totalScore = 0;
        int correctCount = 0;
        int wrongCount = 0;
        int notAnsweredCount = 0;

        for (Question q : questionList) {
            if (!q.isAnswered()) {
                notAnsweredCount++;
            } else if (q.isCorrect()) {
                correctCount++;
                totalScore += q.getScore();
            } else {
                wrongCount++;
            }
        }

        String message = String.format("得分：%d分\n正确：%d题\n错误：%d题\n未答：%d题",
                totalScore, correctCount, wrongCount, notAnsweredCount);

        new AlertDialog.Builder(this)
                .setTitle("交卷成功")
                .setMessage(message)
                .setCancelable(false)
                .setPositiveButton("确定", (dialog, which) -> finish())
                .show();
    }

    @Override
    public void onClick(View v) {
        int id = v.getId();
        Question question = questionList.get(currentIndex);

        if (id == R.id.option_a) {
            selectOption("A");
        } else if (id == R.id.option_b) {
            selectOption("B");
        } else if (id == R.id.option_c) {
            selectOption("C");
        } else if (id == R.id.option_d) {
            selectOption("D");
        } else if (id == R.id.btn_previous) {
            // 上一题
            if (currentIndex > 0) {
                showQuestion(currentIndex - 1);
            }
        } else if (id == R.id.btn_collect) {
            // 收藏
            question.setCollected(!question.isCollected());
            btnCollect.setIconResource(question.isCollected()
                    ? android.R.drawable.star_big_on
                    : android.R.drawable.star_big_off);
            ToastUtils.showShort(question.isCollected() ? "已收藏" : "已取消收藏");
        } else if (id == R.id.btn_next) {
            // 下一题 或 交卷
            if (currentIndex == questionList.size() - 1) {
                // 最后一题，交卷
                new AlertDialog.Builder(this)
                        .setTitle("提示")
                        .setMessage(R.string.confirm_submit)
                        .setPositiveButton("确定", (dialog, which) -> submitPaper())
                        .setNegativeButton("取消", null)
                        .show();
            } else {
                showQuestion(currentIndex + 1);
            }
        }
    }

    @Override
    public void onBackPressed() {
        showExitDialog();
    }

    @Override
    protected void onDestroy() {
        super.onDestroy();
        if (countDownTimer != null) {
            countDownTimer.cancel();
        }
    }
}
