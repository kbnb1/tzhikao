<?php

declare(strict_types=1);

namespace app\model;

use think\Model;

/**
 * 错题本模型
 * @property int $id 错题ID
 * @property int $user_id 用户ID
 * @property int $question_id 题目ID
 * @property string $subject 科目
 * @property string $question_type 题型
 * @property string $question_content 题目内容
 * @property string $user_answer 用户答案
 * @property string $correct_answer 正确答案
 * @property string $analysis 解析
 * @property int $wrong_count 错误次数
 * @property int $status 状态：0未掌握 1已掌握
 * @property string $mastered_time 掌握时间
 * @property string $exam_id 来源考试ID
 * @property string $create_time 创建时间
 * @property string $update_time 更新时间
 */
class WrongQuestion extends Model
{
    protected $name = 'wrong_questions';

    protected $pk = 'id';

    protected $autoWriteTimestamp = true;

    protected $createTime = 'create_time';
    protected $updateTime = 'update_time';

    protected $json = [];

    /**
     * 获取用户错题列表
     * @param int $userId 用户ID
     * @param int $page 页码
     * @param int $limit 每页数量
     * @param string|null $subject 科目筛选
     * @param int|null $status 状态筛选
     * @return array
     */
    public static function getUserList(int $userId, int $page = 1, int $limit = 10, ?string $subject = null, ?int $status = null): array
    {
        $query = self::where('user_id', $userId);

        if (!empty($subject)) {
            $query->where('subject', $subject);
        }

        if ($status !== null) {
            $query->where('status', $status);
        }

        $total = $query->count();
        $list = $query->order('id desc')
            ->page($page, $limit)
            ->select();

        return [
            'total' => $total,
            'page' => $page,
            'limit' => $limit,
            'list' => $list,
        ];
    }

    /**
     * 添加错题（已存在则增加错误次数）
     * @param int $userId 用户ID
     * @param array $data 错题数据
     * @return WrongQuestion
     */
    public static function addWrongQuestion(int $userId, array $data): self
    {
        $wrongQuestion = self::where('user_id', $userId)
            ->where('question_id', $data['question_id'])
            ->find();

        if ($wrongQuestion) {
            $wrongQuestion->wrong_count += 1;
            $wrongQuestion->status = 0;
            $wrongQuestion->user_answer = $data['user_answer'] ?? $wrongQuestion->user_answer;
            $wrongQuestion->save();
        } else {
            $wrongQuestion = new self();
            $wrongQuestion->user_id = $userId;
            $wrongQuestion->question_id = $data['question_id'];
            $wrongQuestion->subject = $data['subject'] ?? '';
            $wrongQuestion->question_type = $data['question_type'] ?? '';
            $wrongQuestion->question_content = $data['question_content'] ?? '';
            $wrongQuestion->user_answer = $data['user_answer'] ?? '';
            $wrongQuestion->correct_answer = $data['correct_answer'] ?? '';
            $wrongQuestion->analysis = $data['analysis'] ?? '';
            $wrongQuestion->wrong_count = 1;
            $wrongQuestion->status = 0;
            $wrongQuestion->exam_id = $data['exam_id'] ?? '';
            $wrongQuestion->save();
        }

        return $wrongQuestion;
    }

    /**
     * 标记已掌握
     * @param int $id 错题ID
     * @param int $userId 用户ID
     * @return bool
     */
    public static function markMastered(int $id, int $userId): bool
    {
        $wrongQuestion = self::where('id', $id)
            ->where('user_id', $userId)
            ->find();

        if (!$wrongQuestion) {
            return false;
        }

        $wrongQuestion->status = 1;
        $wrongQuestion->mastered_time = date('Y-m-d H:i:s');
        return $wrongQuestion->save();
    }

    /**
     * 移除错题
     * @param int $id 错题ID
     * @param int $userId 用户ID
     * @return bool
     */
    public static function removeQuestion(int $id, int $userId): bool
    {
        $wrongQuestion = self::where('id', $id)
            ->where('user_id', $userId)
            ->find();

        if (!$wrongQuestion) {
            return false;
        }

        return $wrongQuestion->delete();
    }

    /**
     * 薄弱点分析（按科目统计错题数量）
     * @param int $userId 用户ID
     * @return array
     */
    public static function getWeakPoints(int $userId): array
    {
        return self::where('user_id', $userId)
            ->where('status', 0)
            ->field('subject, count(*) as wrong_count, sum(wrong_count) as total_wrong')
            ->group('subject')
            ->order('wrong_count desc')
            ->select()
            ->toArray();
    }

    /**
     * 从错题中随机抽取题目生成练习试卷
     * @param int $userId 用户ID
     * @param string|null $subject 科目筛选
     * @param int $count 题目数量
     * @return array
     */
    public static function getRandomQuestions(int $userId, ?string $subject = null, int $count = 10): array
    {
        $query = self::where('user_id', $userId)
            ->where('status', 0);

        if (!empty($subject)) {
            $query->where('subject', $subject);
        }

        $total = $query->count();
        if ($total == 0) {
            return [];
        }

        $limit = min($count, $total);

        return $query->orderRaw('rand()')
            ->limit($limit)
            ->select()
            ->toArray();
    }
}
