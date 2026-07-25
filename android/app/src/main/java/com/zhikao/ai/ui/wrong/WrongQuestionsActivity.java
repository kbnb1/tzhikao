package com.zhikao.ai.ui.wrong;

import android.content.Intent;
import android.os.Bundle;
import android.view.View;
import android.widget.LinearLayout;
import android.widget.TextView;

import androidx.appcompat.app.AlertDialog;
import androidx.appcompat.app.AppCompatActivity;
import androidx.recyclerview.widget.LinearLayoutManager;
import androidx.recyclerview.widget.RecyclerView;

import com.google.android.material.button.MaterialButton;
import com.zhikao.ai.R;
import com.zhikao.ai.app.ApiConfig;
import com.zhikao.ai.model.Question;
import com.zhikao.ai.ui.quiz.QuizActivity;
import com.zhikao.ai.utils.ToastUtils;

import java.util.ArrayList;
import java.util.List;

/**
 * 错题本页
 */
public class WrongQuestionsActivity extends AppCompatActivity implements View.OnClickListener {

    private TextView tvWrongCount;
    private RecyclerView rvWrong;
    private LinearLayout llEmpty;
    private MaterialButton btnPractice;
    private TextView tvClear;

    private List<Question> wrongList;

    @Override
    protected void onCreate(Bundle savedInstanceState) {
        super.onCreate(savedInstanceState);
        setContentView(R.layout.activity_wrong_questions);

        initViews();
        initListeners();
        loadWrongQuestions();
    }

    /**
     * 初始化视图
     */
    private void initViews() {
        tvWrongCount = findViewById(R.id.tv_wrong_count);
        rvWrong = findViewById(R.id.rv_wrong);
        llEmpty = findViewById(R.id.ll_empty);
        btnPractice = findViewById(R.id.btn_practice);
        tvClear = findViewById(R.id.tv_clear);

        // 返回按钮
        findViewById(R.id.toolbar).setOnClickListener(v -> finish());
    }

    /**
     * 初始化监听器
     */
    private void initListeners() {
        btnPractice.setOnClickListener(this);
        tvClear.setOnClickListener(this);
    }

    /**
     * 加载错题列表
     */
    private void loadWrongQuestions() {
        // 模拟数据
        wrongList = new ArrayList<>();
        for (int i = 0; i < 10; i++) {
            Question question = new Question();
            question.setId("wrong_" + i);
            question.setType(1);
            question.setContent("错题第" + (i + 1) + "题：以下哪个选项是正确的？");
            List<String> options = new ArrayList<>();
            options.add("选项A");
            options.add("选项B");
            options.add("选项C");
            options.add("选项D");
            question.setOptions(options);
            question.setAnswer("B");
            question.setAnalysis("答案解析内容");
            question.setWrong(true);
            wrongList.add(question);
        }

        updateUI();
    }

    /**
     * 更新UI
     */
    private void updateUI() {
        tvWrongCount.setText(String.format(getString(R.string.wrong_count), wrongList.size()));

        if (wrongList.isEmpty()) {
            rvWrong.setVisibility(View.GONE);
            llEmpty.setVisibility(View.VISIBLE);
        } else {
            rvWrong.setVisibility(View.VISIBLE);
            llEmpty.setVisibility(View.GONE);

            // 设置适配器（这里简化处理，实际项目中需要创建错题适配器）
            rvWrong.setLayoutManager(new LinearLayoutManager(this));
            // rvWrong.setAdapter(new WrongQuestionAdapter(wrongList));
        }
    }

    @Override
    public void onClick(View v) {
        int id = v.getId();
        if (id == R.id.btn_practice) {
            // 错题练习
            if (wrongList.isEmpty()) {
                ToastUtils.showShort("暂无错题");
                return;
            }
            Intent intent = new Intent(this, QuizActivity.class);
            intent.putExtra(ApiConfig.INTENT_TYPE, "wrong");
            startActivity(intent);
        } else if (id == R.id.tv_clear) {
            // 清空错题
            if (wrongList.isEmpty()) {
                ToastUtils.showShort("暂无错题");
                return;
            }
            showClearDialog();
        }
    }

    /**
     * 显示清空确认对话框
     */
    private void showClearDialog() {
        new AlertDialog.Builder(this)
                .setTitle("提示")
                .setMessage(R.string.confirm_clear_wrong)
                .setPositiveButton("确定", (dialog, which) -> {
                    wrongList.clear();
                    updateUI();
                    ToastUtils.showShort("已清空错题本");
                })
                .setNegativeButton("取消", null)
                .show();
    }
}
