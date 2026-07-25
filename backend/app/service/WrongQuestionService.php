<?php

declare(strict_types=1);

namespace app\service;

use app\model\WrongQuestion;

/**
 * 错题本服务类
 */
class WrongQuestionService
{
    /**
     * 获取错题列表
     * @param int $userId 用户ID
     * @param int $page 页码
     * @param int $limit 每页数量
     * @param string|null $subject 科目筛选
     * @param int|null $status 状态筛选：0未掌握 1已掌握
     * @return array
     */
    public function getList(int $userId, int $page = 1, int $limit = 10, ?string $subject = null, ?int $status = null): array
    {
        return WrongQuestion::getUserList($userId, $page, $limit, $subject, $status);
    }

    /**
     * 添加错题
     * @param int $userId 用户ID
     * @param array $data 错题数据
     * @return WrongQuestion
     */
    public function addWrong(int $userId, array $data): WrongQuestion
    {
        return WrongQuestion::addWrongQuestion($userId, $data);
    }

    /**
     * 批量添加错题（考试提交时自动添加）
     * @param int $userId 用户ID
     * @param array $questions 错题数组
     * @return int 添加数量
     */
    public function batchAddWrong(int $userId, array $questions): int
    {
        $count = 0;
        foreach ($questions as $question) {
            if (!empty($question['is_wrong']) && !empty($question['question_id'])) {
                $this->addWrong($userId, $question);
                $count++;
            }
        }
        return $count;
    }

    /**
     * 移除错题
     * @param int $id 错题ID
     * @param int $userId 用户ID
     * @return bool
     */
    public function remove(int $id, int $userId): bool
    {
        return WrongQuestion::removeQuestion($id, $userId);
    }

    /**
     * 标记已掌握
     * @param int $id 错题ID
     * @param int $userId 用户ID
     * @return bool
     */
    public function markMastered(int $id, int $userId): bool
    {
        return WrongQuestion::markMastered($id, $userId);
    }

    /**
     * 获取错题详情
     * @param int $id 错题ID
     * @param int $userId 用户ID
     * @return WrongQuestion|null
     */
    public function getDetail(int $id, int $userId): ?WrongQuestion
    {
        return WrongQuestion::where('id', $id)
            ->where('user_id', $userId)
            ->find();
    }

    /**
     * 薄弱点分析（统计各科目错题数量）
     * @param int $userId 用户ID
     * @return array
     */
    public function getWeakPoints(int $userId): array
    {
        $weakPoints = WrongQuestion::getWeakPoints($userId);
        $totalWrong = 0;
        $totalQuestions = 0;

        foreach ($weakPoints as &$item) {
            $totalWrong += $item['total_wrong'];
            $totalQuestions += $item['wrong_count'];
        }

        return [
            'total_wrong' => $totalWrong,
            'total_questions' => $totalQuestions,
            'subject_count' => count($weakPoints),
            'weak_points' => $weakPoints,
        ];
    }

    /**
     * 生成强化练习试卷（从错题中随机抽题）
     * @param int $userId 用户ID
     * @param string|null $subject 科目筛选
     * @param int $count 题目数量
     * @return array
     */
    public function generatePracticePaper(int $userId, ?string $subject = null, int $count = 10): array
    {
        $questions = WrongQuestion::getRandomQuestions($userId, $subject, $count);
        $totalScore = 0;
        $formattedQuestions = [];

        foreach ($questions as $item) {
            $score = $this->getScoreByType($item['question_type'] ?? 'single_choice');
            $totalScore += $score;
            $formattedQuestions[] = [
                'id' => $item['id'],
                'question_id' => $item['question_id'],
                'type' => $item['question_type'],
                'subject' => $item['subject'],
                'content' => $item['question_content'],
                'score' => $score,
                'wrong_count' => $item['wrong_count'],
                'correct_answer' => $item['correct_answer'],
                'analysis' => $item['analysis'],
            ];
        }

        return [
            'subject' => $subject ?: '全部',
            'question_count' => count($formattedQuestions),
            'total_score' => $totalScore,
            'duration' => count($formattedQuestions) * 3,
            'source' => 'wrong_book',
            'questions' => $formattedQuestions,
        ];
    }

    /**
     * 根据题型获取分值
     * @param string $type 题型
     * @return int
     */
    protected function getScoreByType(string $type): int
    {
        $scores = [
            'single_choice' => 5,
            'multiple_choice' => 6,
            'true_false' => 3,
            'fill_blank' => 5,
            'short_answer' => 10,
            '简答题' => 10,
            '单选题' => 5,
            '多选题' => 6,
            '判断题' => 3,
            '填空题' => 5,
        ];
        return $scores[$type] ?? 5;
    }
}
