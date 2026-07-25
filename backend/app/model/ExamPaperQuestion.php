<?php
// +----------------------------------------------------------------------
// | 试卷题目关联模型
// +----------------------------------------------------------------------

declare(strict_types=1);

namespace app\model;

use think\Model;

/**
 * 试卷题目关联模型类
 * 对应数据表：zk_exam_paper_questions
 */
class ExamPaperQuestion extends Model
{
    // 表名
    protected $name = 'exam_paper_questions';

    // 主键
    protected $pk = 'id';

    // 自动写入时间戳
    protected $autoWriteTimestamp = true;

    // 时间字段取出后的默认时间格式
    protected $dateFormat = 'Y-m-d H:i:s';

    /**
     * 关联试卷
     */
    public function examPaper()
    {
        return $this->belongsTo(ExamPaper::class, 'exam_paper_id', 'id');
    }

    /**
     * 关联题目
     */
    public function question()
    {
        return $this->belongsTo(Question::class, 'question_id', 'id');
    }

    /**
     * 根据试卷ID获取题目列表（含题目详情）
     * @param int $examPaperId 试卷ID
     * @return \think\Collection
     */
    public static function getQuestionsByPaperId(int $examPaperId)
    {
        return self::where('exam_paper_id', $examPaperId)
            ->with('question')
            ->order('sort', 'asc')
            ->order('id', 'asc')
            ->select();
    }

    /**
     * 批量添加试卷题目
     * @param int $examPaperId 试卷ID
     * @param array $questions 题目数组 [['question_id' => xx, 'score' => xx, 'sort' => xx], ...]
     * @return bool
     */
    public static function addQuestions(int $examPaperId, array $questions): bool
    {
        $data = [];
        foreach ($questions as $index => $q) {
            $data[] = [
                'exam_paper_id' => $examPaperId,
                'question_id'   => $q['question_id'],
                'score'         => $q['score'] ?? 0,
                'sort'          => $q['sort'] ?? ($index + 1),
                'create_time'   => time(),
                'update_time'   => time(),
            ];
        }

        return !empty($data) && (new self())->saveAll($data);
    }
}
