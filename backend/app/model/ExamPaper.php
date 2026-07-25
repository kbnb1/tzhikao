<?php
// +----------------------------------------------------------------------
// | 试卷模型
// +----------------------------------------------------------------------

declare(strict_types=1);

namespace app\model;

use think\Model;

/**
 * 试卷模型类
 * 对应数据表：zk_exam_papers
 */
class ExamPaper extends Model
{
    // 表名
    protected $name = 'exam_papers';

    // 主键
    protected $pk = 'id';

    // 自动写入时间戳
    protected $autoWriteTimestamp = true;

    // 时间字段取出后的默认时间格式
    protected $dateFormat = 'Y-m-d H:i:s';

    // 状态常量
    const STATUS_DISABLED = 0; // 禁用
    const STATUS_ENABLED = 1;  // 启用

    // 试卷类型常量
    const TYPE_RANDOM = 1;  // 随机组卷
    const TYPE_FIXED = 2;   // 固定试卷

    /**
     * 关联科目
     * 一套试卷属于一个科目
     */
    public function subject()
    {
        return $this->belongsTo(Subject::class, 'subject_id', 'id');
    }

    /**
     * 关联试卷题目
     * 一套试卷包含多道题目（通过中间表）
     */
    public function questions()
    {
        return $this->belongsToMany(
            Question::class,
            ExamPaperQuestion::class,
            'question_id',
            'exam_paper_id'
        )->withPivot(['sort', 'score']);
    }

    /**
     * 关联试卷题目关联表
     */
    public function paperQuestions()
    {
        return $this->hasMany(ExamPaperQuestion::class, 'exam_paper_id', 'id')
            ->order('sort', 'asc');
    }

    /**
     * 关联考试记录
     * 一套试卷对应多条考试记录
     */
    public function examRecords()
    {
        return $this->hasMany(ExamRecord::class, 'exam_paper_id', 'id');
    }

    /**
     * 获取启用状态的试卷列表
     * @param int $subjectId 科目ID（可选）
     * @param int $page 页码
     * @param int $pageSize 每页条数
     * @return \think\Paginator
     */
    public static function getEnabledList(int $subjectId = 0, int $page = 1, int $pageSize = 10)
    {
        $query = self::where('status', self::STATUS_ENABLED);

        if ($subjectId > 0) {
            $query->where('subject_id', $subjectId);
        }

        return $query->order('sort', 'asc')
            ->order('id', 'desc')
            ->paginate([
                'page'     => $page,
                'list_rows' => $pageSize,
            ]);
    }

    /**
     * 获取试卷总分
     * @return float
     */
    public function getTotalScore(): float
    {
        return (float)$this->paperQuestions()->sum('score');
    }

    /**
     * 获取题目总数
     * @return int
     */
    public function getQuestionCount(): int
    {
        return $this->paperQuestions()->count();
    }
}
