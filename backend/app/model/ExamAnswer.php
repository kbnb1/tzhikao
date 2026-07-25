<?php
// +----------------------------------------------------------------------
// | 答题明细模型
// +----------------------------------------------------------------------

declare(strict_types=1);

namespace app\model;

use think\Model;

/**
 * 答题明细模型类
 * 对应数据表：zk_exam_answers
 */
class ExamAnswer extends Model
{
    // 表名
    protected $name = 'exam_answers';

    // 主键
    protected $pk = 'id';

    // 自动写入时间戳
    protected $autoWriteTimestamp = true;

    // 时间字段取出后的默认时间格式
    protected $dateFormat = 'Y-m-d H:i:s';

    // 是否正确常量
    const IS_WRONG = 0;   // 错误
    const IS_CORRECT = 1; // 正确

    /**
     * 关联考试记录
     * 一条答题明细属于一条考试记录
     */
    public function examRecord()
    {
        return $this->belongsTo(ExamRecord::class, 'exam_record_id', 'id');
    }

    /**
     * 关联题目
     * 一条答题明细对应一道题目
     */
    public function question()
    {
        return $this->belongsTo(Question::class, 'question_id', 'id');
    }

    /**
     * 根据考试记录ID获取答题明细列表
     * @param int $examRecordId 考试记录ID
     * @return \think\Collection
     */
    public static function getAnswersByRecordId(int $examRecordId)
    {
        return self::where('exam_record_id', $examRecordId)
            ->with('question')
            ->order('id', 'asc')
            ->select();
    }

    /**
     * 保存或更新答题记录
     * @param int $examRecordId 考试记录ID
     * @param int $questionId 题目ID
     * @param string $userAnswer 用户答案
     * @param float $score 得分
     * @param int $isCorrect 是否正确
     * @return bool
     */
    public static function saveAnswer(int $examRecordId, int $questionId, string $userAnswer, float $score = 0, int $isCorrect = 0): bool
    {
        // 检查是否已有答题记录
        $answer = self::where('exam_record_id', $examRecordId)
            ->where('question_id', $questionId)
            ->find();

        if ($answer) {
            // 更新
            $answer->user_answer = $userAnswer;
            $answer->score = $score;
            $answer->is_correct = $isCorrect;
            $answer->update_time = time();
            return $answer->save();
        } else {
            // 新增
            $answer = new self();
            $answer->exam_record_id = $examRecordId;
            $answer->question_id = $questionId;
            $answer->user_answer = $userAnswer;
            $answer->score = $score;
            $answer->is_correct = $isCorrect;
            return $answer->save();
        }
    }

    /**
     * 批量保存答题记录
     * @param int $examRecordId 考试记录ID
     * @param array $answers 答题数组 [['question_id' => xx, 'user_answer' => xx, 'score' => xx, 'is_correct' => xx], ...]
     * @return bool
     */
    public static function saveAnswers(int $examRecordId, array $answers): bool
    {
        foreach ($answers as $answer) {
            $result = self::saveAnswer(
                $examRecordId,
                (int)$answer['question_id'],
                (string)($answer['user_answer'] ?? ''),
                (float)($answer['score'] ?? 0),
                (int)($answer['is_correct'] ?? 0)
            );
            if (!$result) {
                return false;
            }
        }
        return true;
    }
}
