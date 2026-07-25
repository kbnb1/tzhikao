<?php

declare(strict_types=1);

namespace app\model;

use think\Model;

/**
 * 管理员模型
 */
class AdminUser extends Model
{
    /**
     * 表名
     * @var string
     */
    protected $name = 'admin_users';

    /**
     * 主键
     * @var string
     */
    protected $pk = 'id';

    /**
     * 自动写入时间戳
     * @var bool
     */
    protected $autoWriteTimestamp = true;

    /**
     * 创建时间字段
     * @var string
     */
    protected $createTime = 'create_time';

    /**
     * 更新时间字段
     * @var string
     */
    protected $updateTime = 'update_time';

    /**
     * 状态字段获取器
     * @param int $value
     * @return string
     */
    public function getStatusTextAttr($value, $data): string
    {
        $status = $data['status'] ?? 0;
        return $status == 1 ? '启用' : '禁用';
    }

    /**
     * 密码加密设置器
     * @param string $value
     * @return string
     */
    public function setPasswordAttr(string $value): string
    {
        return password_hash($value, PASSWORD_DEFAULT);
    }
}
