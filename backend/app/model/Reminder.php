<?php

declare(strict_types=1);

namespace app\model;

use think\Model;

/**
 * 学习提醒模型
 */
class Reminder extends Model
{
    protected $name = 'reminders';
    protected $pk = 'id';
    protected $prefix = 'zk_';

    protected $autoWriteTimestamp = true;
    protected $createTime = 'created_at';
    protected $updateTime = 'updated_at';

    protected $type = [
        'id'         => 'integer',
        'user_id'    => 'integer',
        'status'     => 'integer',
        'reminder_time' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * 重复类型获取器
     */
    public function getRepeatTypeTextAttr($value, $data): string
    {
        $map = [
            'none'    => '不重复',
            'daily'   => '每天',
            'weekly'  => '每周',
            'monthly' => '每月',
        ];
        return $map[$data['repeat_type']] ?? '未知';
    }

    /**
     * 状态获取器
     */
    public function getStatusTextAttr($value, $data): string
    {
        return $data['status'] == 1 ? '已开启' : '已关闭';
    }
}
