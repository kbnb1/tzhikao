package com.zhikao.ai.adapter;

import android.view.LayoutInflater;
import android.view.View;
import android.view.ViewGroup;
import android.widget.TextView;

import androidx.annotation.NonNull;
import androidx.recyclerview.widget.RecyclerView;

import com.google.android.material.button.MaterialButton;
import com.zhikao.ai.R;
import com.zhikao.ai.model.ExamPaper;

import java.util.List;

/**
 * 试卷列表适配器
 */
public class ExamPaperAdapter extends RecyclerView.Adapter<ExamPaperAdapter.ViewHolder> {

    private List<ExamPaper> paperList;
    private OnItemClickListener onItemClickListener;
    private OnStartClickListener onStartClickListener;

    public ExamPaperAdapter(List<ExamPaper> paperList) {
        this.paperList = paperList;
    }

    /**
     * 设置数据
     */
    public void setData(List<ExamPaper> list) {
        this.paperList = list;
        notifyDataSetChanged();
    }

    /**
     * 添加数据
     */
    public void addData(List<ExamPaper> list) {
        if (this.paperList != null && list != null) {
            this.paperList.addAll(list);
            notifyDataSetChanged();
        }
    }

    /**
     * 设置条目点击事件监听
     */
    public void setOnItemClickListener(OnItemClickListener listener) {
        this.onItemClickListener = listener;
    }

    /**
     * 设置开始考试按钮点击事件监听
     */
    public void setOnStartClickListener(OnStartClickListener listener) {
        this.onStartClickListener = listener;
    }

    @NonNull
    @Override
    public ViewHolder onCreateViewHolder(@NonNull ViewGroup parent, int viewType) {
        View view = LayoutInflater.from(parent.getContext())
                .inflate(R.layout.item_exam_paper, parent, false);
        return new ViewHolder(view);
    }

    @Override
    public void onBindViewHolder(@NonNull ViewHolder holder, int position) {
        ExamPaper paper = paperList.get(position);
        holder.bind(paper);

        final int pos = position;

        // 条目点击事件
        holder.itemView.setOnClickListener(v -> {
            if (onItemClickListener != null) {
                onItemClickListener.onItemClick(pos, paper);
            }
        });

        // 开始考试按钮点击事件
        holder.btnStart.setOnClickListener(v -> {
            if (onStartClickListener != null) {
                onStartClickListener.onStartClick(pos, paper);
            }
        });
    }

    @Override
    public int getItemCount() {
        return paperList != null ? paperList.size() : 0;
    }

    static class ViewHolder extends RecyclerView.ViewHolder {
        TextView tvPaperTitle;
        TextView tvQuestionCount;
        TextView tvDuration;
        TextView tvTotalScore;
        TextView tvStatus;
        TextView tvScore;
        MaterialButton btnStart;

        public ViewHolder(@NonNull View itemView) {
            super(itemView);
            tvPaperTitle = itemView.findViewById(R.id.tv_paper_title);
            tvQuestionCount = itemView.findViewById(R.id.tv_question_count);
            tvDuration = itemView.findViewById(R.id.tv_duration);
            tvTotalScore = itemView.findViewById(R.id.tv_total_score);
            tvStatus = itemView.findViewById(R.id.tv_status);
            tvScore = itemView.findViewById(R.id.tv_score);
            btnStart = itemView.findViewById(R.id.btn_start);
        }

        public void bind(ExamPaper paper) {
            // 试卷标题
            tvPaperTitle.setText(paper.getTitle());
            // 题目数量
            tvQuestionCount.setText(paper.getQuestionCount() + "题");
            // 考试时长
            tvDuration.setText(paper.getDuration() + "分钟");
            // 总分
            tvTotalScore.setText(paper.getTotalScore() + "分");
            // 状态
            tvStatus.setText(paper.getStatusText());

            // 根据状态显示不同内容
            if (paper.getStatus() == 2) {
                // 已完成，显示分数
                tvScore.setVisibility(View.VISIBLE);
                tvScore.setText(paper.getUserScore() + "分");
                btnStart.setText("查看解析");
            } else if (paper.getStatus() == 1) {
                // 进行中
                tvScore.setVisibility(View.GONE);
                btnStart.setText("继续考试");
            } else {
                // 未开始
                tvScore.setVisibility(View.GONE);
                btnStart.setText("开始考试");
            }
        }
    }

    /**
     * 条目点击事件接口
     */
    public interface OnItemClickListener {
        void onItemClick(int position, ExamPaper paper);
    }

    /**
     * 开始考试按钮点击事件接口
     */
    public interface OnStartClickListener {
        void onStartClick(int position, ExamPaper paper);
    }
}
