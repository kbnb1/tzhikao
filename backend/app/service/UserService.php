<?php

declare(strict_types=1);

namespace app\service;

use app\model\User;
use app\utils\Jwt;
use think\facade\Cache;
use think\facade\Filesystem;

class UserService
{
    private User $userModel;
    private Jwt $jwt;

    public function __construct()
    {
        $this->userModel = new User();
        $this->jwt = new Jwt();
    }

    public function login(string $account, string $password): array
    {
        $user = $this->userModel->where('phone', $account)
            ->whereOr('username', $account)
            ->find();

        if (!$user) {
            return ['code' => 1, 'msg' => '账号或密码错误', 'data' => []];
        }

        if ($user['status'] != 1) {
            return ['code' => 1, 'msg' => '账号已被禁用', 'data' => []];
        }

        if (!$user->checkPassword($password)) {
            return ['code' => 1, 'msg' => '账号或密码错误', 'data' => []];
        }

        $userInfo = $user->toArray();
        $tokens = $this->jwt->generateTokens([
            'id'       => $userInfo['id'],
            'username' => $userInfo['username'],
            'phone'    => $userInfo['phone'],
        ]);

        return [
            'code' => 0,
            'msg'  => '登录成功',
            'data' => [
                'user_info' => $userInfo,
                'tokens'    => $tokens,
            ],
        ];
    }

    public function register(string $phone, string $password, string $code): array
    {
        $cacheKey = 'sms_code:' . $phone;
        $cachedCode = Cache::get($cacheKey);

        if (!$cachedCode || $cachedCode != $code) {
            return ['code' => 1, 'msg' => '验证码错误或已过期', 'data' => []];
        }

        $exists = $this->userModel->where('phone', $phone)->find();
        if ($exists) {
            return ['code' => 1, 'msg' => '该手机号已注册', 'data' => []];
        }

        $username = 'user_' . substr($phone, -6) . mt_rand(100, 999);

        $user = $this->userModel->create([
            'phone'    => $phone,
            'username' => $username,
            'password' => $password,
            'nickname' => '用户' . substr($phone, -4),
            'status'   => 1,
        ]);

        Cache::delete($cacheKey);

        $userInfo = $user->toArray();
        $tokens = $this->jwt->generateTokens([
            'id'       => $userInfo['id'],
            'username' => $userInfo['username'],
            'phone'    => $userInfo['phone'],
        ]);

        return [
            'code' => 0,
            'msg'  => '注册成功',
            'data' => [
                'user_info' => $userInfo,
                'tokens'    => $tokens,
            ],
        ];
    }

    public function sendSmsCode(string $phone): array
    {
        $code = str_pad((string)mt_rand(0, 999999), 6, '0', STR_PAD_LEFT);

        Cache::set('sms_code:' . $phone, $code, 300);

        return [
            'code' => 0,
            'msg'  => '验证码发送成功',
            'data' => [
                'phone' => $phone,
                'code'  => $code,
            ],
        ];
    }

    public function refreshToken(string $refreshToken): array
    {
        $result = $this->jwt->refresh($refreshToken);
        return $result;
    }

    public function getUserInfo(int $userId): array
    {
        $user = $this->userModel->find($userId);
        if (!$user) {
            return ['code' => 1, 'msg' => '用户不存在', 'data' => []];
        }

        return [
            'code' => 0,
            'msg'  => '获取成功',
            'data' => $user->toArray(),
        ];
    }

    public function updateProfile(int $userId, array $data): array
    {
        $user = $this->userModel->find($userId);
        if (!$user) {
            return ['code' => 1, 'msg' => '用户不存在', 'data' => []];
        }

        $updateData = [];
        if (isset($data['nickname'])) {
            $updateData['nickname'] = $data['nickname'];
        }
        if (isset($data['avatar'])) {
            $updateData['avatar'] = $data['avatar'];
        }

        if (!empty($updateData)) {
            $user->save($updateData);
        }

        return [
            'code' => 0,
            'msg'  => '更新成功',
            'data' => $user->toArray(),
        ];
    }

    public function changePassword(int $userId, string $oldPassword, string $newPassword): array
    {
        $user = $this->userModel->find($userId);
        if (!$user) {
            return ['code' => 1, 'msg' => '用户不存在', 'data' => []];
        }

        if (!$user->checkPassword($oldPassword)) {
            return ['code' => 1, 'msg' => '原密码错误', 'data' => []];
        }

        $user->password = $newPassword;
        $user->save();

        return [
            'code' => 0,
            'msg'  => '密码修改成功',
            'data' => [],
        ];
    }

    public function uploadAvatar(int $userId, $file): array
    {
        $user = $this->userModel->find($userId);
        if (!$user) {
            return ['code' => 1, 'msg' => '用户不存在', 'data' => []];
        }

        $saveName = Filesystem::disk('public')->putFile('avatar', $file, 'md5');
        $avatarPath = 'uploads/' . $saveName;

        $user->avatar = $avatarPath;
        $user->save();

        return [
            'code' => 0,
            'msg'  => '上传成功',
            'data' => [
                'avatar' => $user->avatar,
            ],
        ];
    }

    public function forgotPassword(string $phone, string $password, string $code): array
    {
        $cacheKey = 'sms_code:' . $phone;
        $cachedCode = Cache::get($cacheKey);

        if (!$cachedCode || $cachedCode != $code) {
            return ['code' => 1, 'msg' => '验证码错误或已过期', 'data' => []];
        }

        $user = $this->userModel->where('phone', $phone)->find();
        if (!$user) {
            return ['code' => 1, 'msg' => '该手机号未注册', 'data' => []];
        }

        $user->password = $password;
        $user->save();

        Cache::delete($cacheKey);

        return [
            'code' => 0,
            'msg'  => '密码重置成功',
            'data' => [],
        ];
    }
}
