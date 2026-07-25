<?php
// +----------------------------------------------------------------------
// | 考试记录模型
// +----------------------------------------------------------------------

declare(strict_types=1);

namespace app\model;

use think\Model;

/**
 * 考试记录模型类
 * 对应数据表：zk_exam_records
 */
class ExamRecord extends Model
{
    // 表名
    protected $name = 'exam_records';

    // 主键
    protected $pk = 'id';

    // 自动写入时间戳
    protected $autoWriteTimestamp = true;

    // 时间字段取出后的默认时间格式
    protected $dateFormat = 'Y-m-d H:i:s';

    // 考试状态常量
    const STATUS_NOT_STARTED = 0; // 未开始
    const STATUS_IN_PROGRESS = 1; // 进行中
    const STATUS_SUBMITTED = 2;   // 已提交
    const STATUS_GRADED = 3;      // 已评分

    /**
     * 关联用户
     * 一条考试记录属于一个用户
     */
    public function user()
    {
        // 这里假设用户模型为 app\model\User，如实际路径不同请调整
        return $this->belongsTo(\app\model\User::class, 'user_id', 'id');
    }

    /**
     * 关联试卷
     * 一条考试记录对应一套试卷
     */
    public function examPaper()
    {
        return $this->belongsTo(ExamPaper::class, 'exam_paper_id', 'id');
    }

    /**
     * 关联答题明细
     * 一条考试记录包含多条答题明细
     */
    public function answers()
    {
        return $this->hasMany(ExamAnswer::class, 'exam_record_id', 'id');
    }

    /**
     * 获取考试状态文本
     * @param int $status
     * @return string
     */
    public static function getStatusText(int $status): string
    {
        $map = [
            self::STATUS_NOT_STARTED => '未开始',
            self::STATUS_IN_PROGRESS => '进行中',
            self::STATUS_SUBMITTED   => '已提交',
            self::STATUS_GRADED      => '已评分',
        ];
        return $map[$status] ?? '未知';
    }

    /**
     * 获取用户的考试记录列表（分页）
     * @param int $userId 用户ID
     * @param int $page 页码
     * @param int $pageSize 每页条数
     * @return \think\Paginator
     */
    public static function getUserRecords(int $userId, int $page = 1, int $pageSize = 10)
    {
        return self::where('user_id', $userId)
            ->with('examPaper')
            ->order('create_time', 'desc')
            ->paginate([
                'page'     => $page,
                'list_rows' => $pageSize,
            ]);
    }

    /**
     * 开始考试
     * @param int $userId 用户ID
     * @param int $examPaperId 试卷ID
     * @param int $duration 考试时长（秒）
     * @return ExamRecord|false
     */
    public static function startExam(int $userId, int $examPaperId, int $duration = 0)
    {
        // 检查是否有未完成的考试
        $exist = self::where('user_id', $userId)
            ->where('exam_paper_id', $examPaperId)
            ->whereIn('status', [self::STATUS_NOT_STARTED, self::STATUS_IN_PROGRESS])
            ->find();

        if ($exist) {
            return $exist;
        }

        // 创建新的考试记录
        $record = new self();
        $record->user_id = $userId;
        $record->exam_paper_id = $examPaperId;
        $record->status = self::STATUS_IN_PROGRESS;
        $record->score = 0;
        $record->total_score = 0;
        $record->correct_count = 0;
        $record->total_count = 0;
        $record->duration = $duration;
        $record->start_time = time();

        if ($record->save()) {
            return $record;
        }

        return false;
    }

    /**
     * 计算正确率
     * @return float
     */
    public function getAccuracy(): float
    {
        if ($this->total_count == 0) {
            return 0.00;
        }
        return round(($this->correct_count / $this->total_count) * 100, 2);
    }
}
