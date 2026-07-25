<?php

declare(strict_types=1);

namespace app\validate;

use think\Validate;

class User extends Validate
{
    protected $rule = [
        'nickname'      => 'length:2,20',
        'avatar'        => 'max:255',
        'old_password'  => 'require',
        'new_password'  => 'require|length:6,20',
        'confirm_password' => 'require|confirm:new_password',
    ];

    protected $message = [
        'nickname.length'         => '昵称长度必须在2-20个字符之间',
        'avatar.max'              => '头像地址长度不能超过255个字符',
        'old_password.require'    => '原密码不能为空',
        'new_password.require'    => '新密码不能为空',
        'new_password.length'     => '新密码长度必须在6-20个字符之间',
        'confirm_password.require' => '确认密码不能为空',
        'confirm_password.confirm' => '两次输入的密码不一致',
    ];

    public function sceneUpdateProfile()
    {
        return $this->only(['nickname', 'avatar']);
    }

    public function sceneChangePassword()
    {
        return $this->only(['old_password', 'new_password', 'confirm_password']);
    }
}
