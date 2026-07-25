package com.zhikao.ai.model;

import java.io.Serializable;

/**
 * 科目模型
 */
public class Subject implements Serializable {

    private String id;
    private String name;
    private String icon;
    private int paperCount;
    private int questionCount;
    private int progress;
    private String description;
    private int sort;

    public Subject() {
    }

    public Subject(String id, String name, String icon, int paperCount, int progress) {
        this.id = id;
        this.name = name;
        this.icon = icon;
        this.paperCount = paperCount;
        this.progress = progress;
    }

    public String getId() {
        return id;
    }

    public void setId(String id) {
        this.id = id;
    }

    public String getName() {
        return name;
    }

    public void setName(String name) {
        this.name = name;
    }

    public String getIcon() {
        return icon;
    }

    public void setIcon(String icon) {
        this.icon = icon;
    }

    public int getPaperCount() {
        return paperCount;
    }

    public void setPaperCount(int paperCount) {
        this.paperCount = paperCount;
    }

    public int getQuestionCount() {
        return questionCount;
    }

    public void setQuestionCount(int questionCount) {
        this.questionCount = questionCount;
    }

    public int getProgress() {
        return progress;
    }

    public void setProgress(int progress) {
        this.progress = progress;
    }

    public String getDescription() {
        return description;
    }

    public void setDescription(String description) {
        this.description = description;
    }

    public int getSort() {
        return sort;
    }

    public void setSort(int sort) {
        this.sort = sort;
    }

    @Override
    public String toString() {
        return "Subject{" +
                "id='" + id + '\'' +
                ", name='" + name + '\'' +
                ", paperCount=" + paperCount +
                '}';
    }
}
