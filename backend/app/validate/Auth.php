<?php

declare(strict_types=1);

namespace app\validate;

use think\Validate;

class Auth extends Validate
{
    protected $rule = [
        'phone'         => 'require|mobile',
        'username'      => 'require|length:3,20',
        'password'      => 'require|length:6,20',
        'code'          => 'require|length:4,6',
        'refresh_token' => 'require',
    ];

    protected $message = [
        'phone.require'         => '手机号不能为空',
        'phone.mobile'          => '手机号格式不正确',
        'username.require'      => '用户名不能为空',
        'username.length'       => '用户名长度必须在3-20个字符之间',
        'password.require'      => '密码不能为空',
        'password.length'       => '密码长度必须在6-20个字符之间',
        'code.require'          => '验证码不能为空',
        'code.length'           => '验证码长度不正确',
        'refresh_token.require' => '刷新令牌不能为空',
    ];

    public function sceneLogin()
    {
        return $this->only(['password'])
            ->remove('password', 'length')
            ->append('account', 'require');
    }

    public function sceneRegister()
    {
        return $this->only(['phone', 'password', 'code']);
    }

    public function sceneSendCode()
    {
        return $this->only(['phone']);
    }

    public function sceneRefresh()
    {
        return $this->only(['refresh_token']);
    }

    public function sceneForgotPassword()
    {
        return $this->only(['phone', 'password', 'code']);
    }
}
