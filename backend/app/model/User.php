<?php

declare(strict_types=1);

namespace app\model;

use think\Model;

class User extends Model
{
    protected $name = 'users';
    protected $pk = 'id';
    protected $prefix = 'zk_';

    protected $autoWriteTimestamp = true;
    protected $createTime = 'created_at';
    protected $updateTime = 'updated_at';

    protected $hidden = ['password', 'deleted_at'];

    protected $type = [
        'id'         => 'integer',
        'status'     => 'integer',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function setPasswordAttr($value): string
    {
        return password_hash($value, PASSWORD_DEFAULT);
    }

    public function checkPassword(string $password): bool
    {
        return password_verify($password, $this->getData('password'));
    }

    public function getAvatarAttr($value): string
    {
        if (empty($value)) {
            return '';
        }
        if (str_starts_with($value, 'http://') || str_starts_with($value, 'https://')) {
            return $value;
        }
        return request()->domain() . '/' . ltrim($value, '/');
    }
}
