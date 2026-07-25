package com.zhikao.ai.adapter;

import android.view.LayoutInflater;
import android.view.View;
import android.view.ViewGroup;
import android.widget.ImageView;
import android.widget.ProgressBar;
import android.widget.TextView;

import androidx.annotation.NonNull;
import androidx.recyclerview.widget.RecyclerView;

import com.bumptech.glide.Glide;
import com.zhikao.ai.R;
import com.zhikao.ai.model.Subject;

import java.util.List;

/**
 * 科目列表适配器
 */
public class SubjectAdapter extends RecyclerView.Adapter<SubjectAdapter.ViewHolder> {

    private List<Subject> subjectList;
    private OnItemClickListener onItemClickListener;

    public SubjectAdapter(List<Subject> subjectList) {
        this.subjectList = subjectList;
    }

    /**
     * 设置数据
     */
    public void setData(List<Subject> list) {
        this.subjectList = list;
        notifyDataSetChanged();
    }

    /**
     * 设置点击事件监听
     */
    public void setOnItemClickListener(OnItemClickListener listener) {
        this.onItemClickListener = listener;
    }

    @NonNull
    @Override
    public ViewHolder onCreateViewHolder(@NonNull ViewGroup parent, int viewType) {
        View view = LayoutInflater.from(parent.getContext())
                .inflate(R.layout.item_subject, parent, false);
        return new ViewHolder(view);
    }

    @Override
    public void onBindViewHolder(@NonNull ViewHolder holder, int position) {
        Subject subject = subjectList.get(position);
        holder.bind(subject);

        // 点击事件
        holder.itemView.setOnClickListener(v -> {
            if (onItemClickListener != null) {
                onItemClickListener.onItemClick(position, subject);
            }
        });
    }

    @Override
    public int getItemCount() {
        return subjectList != null ? subjectList.size() : 0;
    }

    static class ViewHolder extends RecyclerView.ViewHolder {
        ImageView ivSubject;
        TextView tvSubjectName;
        TextView tvPaperCount;
        ProgressBar progressBar;
        TextView tvProgress;

        public ViewHolder(@NonNull View itemView) {
            super(itemView);
            ivSubject = itemView.findViewById(R.id.iv_subject);
            tvSubjectName = itemView.findViewById(R.id.tv_subject_name);
            tvPaperCount = itemView.findViewById(R.id.tv_paper_count);
            progressBar = itemView.findViewById(R.id.progress_bar);
            tvProgress = itemView.findViewById(R.id.tv_progress);
        }

        public void bind(Subject subject) {
            // 科目名称
            tvSubjectName.setText(subject.getName());
            // 试卷数量
            tvPaperCount.setText("共" + subject.getPaperCount() + "套试卷");
            // 学习进度
            int progress = subject.getProgress();
            progressBar.setProgress(progress);
            tvProgress.setText(progress + "%");

            // 科目图片
            if (subject.getIcon() != null && !subject.getIcon().isEmpty()) {
                Glide.with(itemView.getContext())
                        .load(subject.getIcon())
                        .placeholder(R.mipmap.ic_launcher)
                        .error(R.mipmap.ic_launcher)
                        .into(ivSubject);
            }
        }
    }

    /**
     * 点击事件接口
     */
    public interface OnItemClickListener {
        void onItemClick(int position, Subject subject);
    }
}
