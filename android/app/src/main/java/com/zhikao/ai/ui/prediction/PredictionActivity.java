package com.zhikao.ai.ui.prediction;

import android.graphics.Color;
import android.os.Bundle;

import androidx.appcompat.app.AppCompatActivity;

import com.github.mikephil.charting.charts.BarChart;
import com.github.mikephil.charting.charts.LineChart;
import com.github.mikephil.charting.components.Legend;
import com.github.mikephil.charting.components.XAxis;
import com.github.mikephil.charting.components.YAxis;
import com.github.mikephil.charting.data.BarData;
import com.github.mikephil.charting.data.BarDataSet;
import com.github.mikephil.charting.data.BarEntry;
import com.github.mikephil.charting.data.Entry;
import com.github.mikephil.charting.data.LineData;
import com.github.mikephil.charting.data.LineDataSet;
import com.github.mikephil.charting.formatter.IndexAxisValueFormatter;
import com.zhikao.ai.R;

import java.util.ArrayList;
import java.util.List;

/**
 * 成绩预测页
 */
public class PredictionActivity extends AppCompatActivity {

    private BarChart barChart;
    private LineChart lineChart;

    @Override
    protected void onCreate(Bundle savedInstanceState) {
        super.onCreate(savedInstanceState);
        setContentView(R.layout.activity_prediction);

        initViews();
        initBarChart();
        initLineChart();
    }

    /**
     * 初始化视图
     */
    private void initViews() {
        barChart = findViewById(R.id.bar_chart);
        lineChart = findViewById(R.id.line_chart);

        // 返回按钮
        findViewById(R.id.toolbar).setOnClickListener(v -> finish());
    }

    /**
     * 初始化柱状图（各科成绩）
     */
    private void initBarChart() {
        // 基本设置
        barChart.getDescription().setEnabled(false);
        barChart.setDrawGridBackground(false);
        barChart.setTouchEnabled(false);
        barChart.setDragEnabled(false);
        barChart.setScaleEnabled(false);
        barChart.setPinchZoom(false);
        barChart.setDoubleTapToZoomEnabled(false);

        // X轴
        XAxis xAxis = barChart.getXAxis();
        xAxis.setPosition(XAxis.XAxisPosition.BOTTOM);
        xAxis.setDrawGridLines(false);
        xAxis.setTextColor(getResources().getColor(R.color.text_secondary));
        xAxis.setTextSize(12f);
        xAxis.setGranularity(1f);
        final String[] subjects = {"语文", "数学", "英语", "物理", "化学", "生物"};
        xAxis.setValueFormatter(new IndexAxisValueFormatter(subjects));

        // Y轴（左侧）
        YAxis leftAxis = barChart.getAxisLeft();
        leftAxis.setDrawGridLines(true);
        leftAxis.setGridColor(getResources().getColor(R.color.divider));
        leftAxis.setTextColor(getResources().getColor(R.color.text_secondary));
        leftAxis.setTextSize(12f);
        leftAxis.setAxisMinimum(0f);
        leftAxis.setAxisMaximum(100f);

        // Y轴（右侧）
        YAxis rightAxis = barChart.getAxisRight();
        rightAxis.setEnabled(false);

        // 图例
        Legend legend = barChart.getLegend();
        legend.setEnabled(false);

        // 设置数据
        List<BarEntry> entries = new ArrayList<>();
        entries.add(new BarEntry(0f, 85f));
        entries.add(new BarEntry(1f, 65f));
        entries.add(new BarEntry(2f, 78f));
        entries.add(new BarEntry(3f, 70f));
        entries.add(new BarEntry(4f, 82f));
        entries.add(new BarEntry(5f, 88f));

        BarDataSet dataSet = new BarDataSet(entries, "成绩");
        dataSet.setColor(getResources().getColor(R.color.primary));
        dataSet.setHighLightAlpha(255);
        dataSet.setDrawValues(false);

        BarData barData = new BarData(dataSet);
        barData.setBarWidth(0.5f);

        barChart.setData(barData);
        barChart.invalidate();
    }

    /**
     * 初始化折线图（成绩趋势）
     */
    private void initLineChart() {
        // 基本设置
        lineChart.getDescription().setEnabled(false);
        lineChart.setDrawGridBackground(false);
        lineChart.setTouchEnabled(false);
        lineChart.setDragEnabled(false);
        lineChart.setScaleEnabled(false);
        lineChart.setPinchZoom(false);
        lineChart.setDoubleTapToZoomEnabled(false);

        // X轴
        XAxis xAxis = lineChart.getXAxis();
        xAxis.setPosition(XAxis.XAxisPosition.BOTTOM);
        xAxis.setDrawGridLines(false);
        xAxis.setTextColor(getResources().getColor(R.color.text_secondary));
        xAxis.setTextSize(12f);
        xAxis.setGranularity(1f);
        final String[] exams = {"月考1", "月考2", "月考3", "期中", "月考4", "月考5", "期末"};
        xAxis.setValueFormatter(new IndexAxisValueFormatter(exams));

        // Y轴（左侧）
        YAxis leftAxis = lineChart.getAxisLeft();
        leftAxis.setDrawGridLines(true);
        leftAxis.setGridColor(getResources().getColor(R.color.divider));
        leftAxis.setTextColor(getResources().getColor(R.color.text_secondary));
        leftAxis.setTextSize(12f);
        leftAxis.setAxisMinimum(0f);
        leftAxis.setAxisMaximum(100f);

        // Y轴（右侧）
        YAxis rightAxis = lineChart.getAxisRight();
        rightAxis.setEnabled(false);

        // 图例
        Legend legend = lineChart.getLegend();
        legend.setEnabled(false);

        // 设置数据
        List<Entry> entries = new ArrayList<>();
        entries.add(new Entry(0f, 70f));
        entries.add(new Entry(1f, 72f));
        entries.add(new Entry(2f, 68f));
        entries.add(new Entry(3f, 75f));
        entries.add(new Entry(4f, 78f));
        entries.add(new Entry(5f, 76f));
        entries.add(new Entry(6f, 78f));

        LineDataSet dataSet = new LineDataSet(entries, "成绩趋势");
        dataSet.setColor(getResources().getColor(R.color.primary));
        dataSet.setValueTextColor(getResources().getColor(R.color.primary));
        dataSet.setLineWidth(2f);
        dataSet.setCircleColor(getResources().getColor(R.color.primary));
        dataSet.setCircleRadius(4f);
        dataSet.setDrawValues(false);
        dataSet.setDrawFilled(true);
        dataSet.setFillColor(getResources().getColor(R.color.primary_light));
        dataSet.setFillAlpha(30);
        dataSet.setMode(LineDataSet.Mode.CUBIC_BEZIER);

        LineData lineData = new LineData(dataSet);
        lineChart.setData(lineData);
        lineChart.invalidate();
    }
}
