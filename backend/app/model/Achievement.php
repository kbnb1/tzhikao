<?php

declare(strict_types=1);

namespace app\model;

use think\Model;

/**
 * 成就模型
 */
class Achievement extends Model
{
    protected $name = 'achievements';
    protected $pk = 'id';
    protected $prefix = 'zk_';

    protected $autoWriteTimestamp = true;
    protected $createTime = 'created_at';
    protected $updateTime = 'updated_at';

    protected $type = [
        'id'              => 'integer',
        'condition_value' => 'integer',
        'reward_value'    => 'integer',
        'sort'            => 'integer',
        'status'          => 'integer',
        'created_at'      => 'datetime',
        'updated_at'      => 'datetime',
    ];

    /**
     * 类型获取器
     */
    public function getTypeTextAttr($value, $data): string
    {
        $map = [
            'study'   => '学习类',
            'exam'    => '考试类',
            'social'  => '社交类',
            'special' => '特殊类',
        ];
        return $map[$data['type']] ?? '未知';
    }

    /**
     * 图标获取器
     */
    public function getIconAttr($value): string
    {
        if (empty($value)) {
            return '';
        }
        if (str_starts_with($value, 'http://') || str_starts_with($value, 'https://')) {
            return $value;
        }
        return request()->domain() . '/' . ltrim($value, '/');
    }

    /**
     * 用户成就关联
     */
    public function userAchievements()
    {
        return $this->hasMany(UserAchievement::class, 'achievement_id', 'id');
    }
}
