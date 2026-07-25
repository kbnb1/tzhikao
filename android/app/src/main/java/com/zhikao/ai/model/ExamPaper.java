package com.zhikao.ai.model;

import java.io.Serializable;
import java.util.List;

/**
 * 试卷模型
 */
public class ExamPaper implements Serializable {

    private String id;
    private String title;
    private String subjectId;
    private String subjectName;
    private int questionCount;
    private int totalScore;
    private int duration; // 考试时长（分钟）
    private int difficulty; // 难度 1:简单 2:中等 3:困难
    private String description;
    private String coverImage;
    private int status; // 0:未开始 1:进行中 2:已完成
    private int userScore;
    private int correctCount;
    private int wrongCount;
    private int notAnsweredCount;
    private long createTime;
    private long startTime;
    private long endTime;
    private List<Question> questions;

    public ExamPaper() {
    }

    public String getId() {
        return id;
    }

    public void setId(String id) {
        this.id = id;
    }

    public String getTitle() {
        return title;
    }

    public void setTitle(String title) {
        this.title = title;
    }

    public String getSubjectId() {
        return subjectId;
    }

    public void setSubjectId(String subjectId) {
        this.subjectId = subjectId;
    }

    public String getSubjectName() {
        return subjectName;
    }

    public void setSubjectName(String subjectName) {
        this.subjectName = subjectName;
    }

    public int getQuestionCount() {
        return questionCount;
    }

    public void setQuestionCount(int questionCount) {
        this.questionCount = questionCount;
    }

    public int getTotalScore() {
        return totalScore;
    }

    public void setTotalScore(int totalScore) {
        this.totalScore = totalScore;
    }

    public int getDuration() {
        return duration;
    }

    public void setDuration(int duration) {
        this.duration = duration;
    }

    public int getDifficulty() {
        return difficulty;
    }

    public void setDifficulty(int difficulty) {
        this.difficulty = difficulty;
    }

    public String getDescription() {
        return description;
    }

    public void setDescription(String description) {
        this.description = description;
    }

    public String getCoverImage() {
        return coverImage;
    }

    public void setCoverImage(String coverImage) {
        this.coverImage = coverImage;
    }

    public int getStatus() {
        return status;
    }

    public void setStatus(int status) {
        this.status = status;
    }

    public int getUserScore() {
        return userScore;
    }

    public void setUserScore(int userScore) {
        this.userScore = userScore;
    }

    public int getCorrectCount() {
        return correctCount;
    }

    public void setCorrectCount(int correctCount) {
        this.correctCount = correctCount;
    }

    public int getWrongCount() {
        return wrongCount;
    }

    public void setWrongCount(int wrongCount) {
        this.wrongCount = wrongCount;
    }

    public int getNotAnsweredCount() {
        return notAnsweredCount;
    }

    public void setNotAnsweredCount(int notAnsweredCount) {
        this.notAnsweredCount = notAnsweredCount;
    }

    public long getCreateTime() {
        return createTime;
    }

    public void setCreateTime(long createTime) {
        this.createTime = createTime;
    }

    public long getStartTime() {
        return startTime;
    }

    public void setStartTime(long startTime) {
        this.startTime = startTime;
    }

    public long getEndTime() {
        return endTime;
    }

    public void setEndTime(long endTime) {
        this.endTime = endTime;
    }

    public List<Question> getQuestions() {
        return questions;
    }

    public void setQuestions(List<Question> questions) {
        this.questions = questions;
    }

    /**
     * 获取难度文字
     */
    public String getDifficultyText() {
        switch (difficulty) {
            case 1:
                return "简单";
            case 2:
                return "中等";
            case 3:
                return "困难";
            default:
                return "未知";
        }
    }

    /**
     * 获取状态文字
     */
    public String getStatusText() {
        switch (status) {
            case 0:
                return "未开始";
            case 1:
                return "进行中";
            case 2:
                return "已完成";
            default:
                return "未知";
        }
    }

    @Override
    public String toString() {
        return "ExamPaper{" +
                "id='" + id + '\'' +
                ", title='" + title + '\'' +
                ", questionCount=" + questionCount +
                '}';
    }
}
