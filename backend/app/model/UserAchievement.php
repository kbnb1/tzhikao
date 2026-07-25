<?php

declare(strict_types=1);

namespace app\model;

use think\Model;

/**
 * 用户成就模型
 */
class UserAchievement extends Model
{
    protected $name = 'user_achievements';
    protected $pk = 'id';
    protected $prefix = 'zk_';

    protected $autoWriteTimestamp = false;

    protected $type = [
        'id'              => 'integer',
        'user_id'         => 'integer',
        'achievement_id'  => 'integer',
        'obtain_time'     => 'datetime',
    ];

    /**
     * 成就关联
     */
    public function achievement()
    {
        return $this->belongsTo(Achievement::class, 'achievement_id', 'id');
    }

    /**
     * 用户关联
     */
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }
}
