<?php
// +----------------------------------------------------------------------
// | 题目模型
// +----------------------------------------------------------------------

declare(strict_types=1);

namespace app\model;

use think\Model;

/**
 * 题目模型类
 * 对应数据表：zk_questions
 */
class Question extends Model
{
    // 表名
    protected $name = 'questions';

    // 主键
    protected $pk = 'id';

    // 自动写入时间戳
    protected $autoWriteTimestamp = true;

    // 时间字段取出后的默认时间格式
    protected $dateFormat = 'Y-m-d H:i:s';

    // JSON字段
    protected $json = ['options'];

    // JSON字段返回数组
    protected $jsonAssoc = true;

    // 题目类型常量
    const TYPE_SINGLE = 1;   // 单选题
    const TYPE_MULTIPLE = 2; // 多选题
    const TYPE_JUDGE = 3;    // 判断题
    const TYPE_FILL = 4;     // 填空题
    const TYPE_SHORT = 5;    // 简答题

    // 难度常量
    const DIFFICULTY_EASY = 1;   // 简单
    const DIFFICULTY_MEDIUM = 2; // 中等
    const DIFFICULTY_HARD = 3;   // 困难

    // 状态常量
    const STATUS_DISABLED = 0; // 禁用
    const STATUS_ENABLED = 1;  // 启用

    /**
     * 关联科目
     * 一道题目属于一个科目
     */
    public function subject()
    {
        return $this->belongsTo(Subject::class, 'subject_id', 'id');
    }

    /**
     * 获取题目类型文本
     * @param int $type
     * @return string
     */
    public static function getTypeText(int $type): string
    {
        $map = [
            self::TYPE_SINGLE   => '单选题',
            self::TYPE_MULTIPLE => '多选题',
            self::TYPE_JUDGE    => '判断题',
            self::TYPE_FILL     => '填空题',
            self::TYPE_SHORT    => '简答题',
        ];
        return $map[$type] ?? '未知';
    }

    /**
     * 获取难度文本
     * @param int $difficulty
     * @return string
     */
    public static function getDifficultyText(int $difficulty): string
    {
        $map = [
            self::DIFFICULTY_EASY   => '简单',
            self::DIFFICULTY_MEDIUM => '中等',
            self::DIFFICULTY_HARD   => '困难',
        ];
        return $map[$difficulty] ?? '未知';
    }

    /**
     * 检查答案是否正确
     * @param string $userAnswer 用户答案
     * @return bool
     */
    public function checkAnswer(string $userAnswer): bool
    {
        $correctAnswer = $this->answer;

        // 多选题需要排序后比较
        if ($this->type == self::TYPE_MULTIPLE) {
            $userArr = str_split($userAnswer);
            $correctArr = str_split($correctAnswer);
            sort($userArr);
            sort($correctArr);
            return implode('', $userArr) === implode('', $correctArr);
        }

        // 其他题型直接比较（不区分大小写）
        return strcasecmp(trim($userAnswer), trim($correctAnswer)) === 0;
    }
}
