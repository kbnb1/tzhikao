<?php
// +----------------------------------------------------------------------
// | 科目模型
// +----------------------------------------------------------------------

declare(strict_types=1);

namespace app\model;

use think\Model;

/**
 * 科目模型类
 * 对应数据表：zk_subjects
 */
class Subject extends Model
{
    // 表名
    protected $name = 'subjects';

    // 主键
    protected $pk = 'id';

    // 自动写入时间戳
    protected $autoWriteTimestamp = true;

    // 时间字段取出后的默认时间格式
    protected $dateFormat = 'Y-m-d H:i:s';

    // 状态常量
    const STATUS_DISABLED = 0; // 禁用
    const STATUS_ENABLED = 1;  // 启用

    /**
     * 关联试卷
     * 一个科目下有多套试卷
     */
    public function examPapers()
    {
        return $this->hasMany(ExamPaper::class, 'subject_id', 'id');
    }

    /**
     * 关联题目
     * 一个科目下有多道题目
     */
    public function questions()
    {
        return $this->hasMany(Question::class, 'subject_id', 'id');
    }

    /**
     * 获取启用状态的科目列表
     * @return \think\Collection
     */
    public static function getEnabledList()
    {
        return self::where('status', self::STATUS_ENABLED)
            ->order('sort', 'asc')
            ->order('id', 'desc')
            ->select();
    }
}
