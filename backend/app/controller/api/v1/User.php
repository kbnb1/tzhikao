<?php

declare(strict_types=1);

namespace app\controller\api\v1;

use app\BaseController;
use app\service\UserService;
use think\response\Json;

class User extends BaseController
{
    private UserService $userService;

    protected $middleware = ['jwt_auth'];

    public function __construct(\think\App $app)
    {
        parent::__construct($app);
        $this->userService = new UserService();
    }

    public function info(): Json
    {
        $result = $this->userService->getUserInfo($this->userId);

        if ($result['code'] !== 0) {
            return $this->error($result['msg'], $result['code'], $result['data']);
        }

        return $this->success($result['data'], $result['msg']);
    }

    public function updateProfile(): Json
    {
        $data = $this->request->put();
        $this->validate($data, 'User.updateProfile');

        $result = $this->userService->updateProfile($this->userId, $data);

        if ($result['code'] !== 0) {
            return $this->error($result['msg'], $result['code'], $result['data']);
        }

        return $this->success($result['data'], $result['msg']);
    }

    public function changePassword(): Json
    {
        $data = $this->request->put();
        $this->validate($data, 'User.changePassword');

        $oldPassword = $data['old_password'];
        $newPassword = $data['new_password'];

        $result = $this->userService->changePassword($this->userId, $oldPassword, $newPassword);

        if ($result['code'] !== 0) {
            return $this->error($result['msg'], $result['code'], $result['data']);
        }

        return $this->success($result['data'], $result['msg']);
    }

    public function uploadAvatar(): Json
    {
        $file = $this->request->file('avatar');
        if (!$file) {
            return $this->error('请上传头像文件');
        }

        $result = $this->userService->uploadAvatar($this->userId, $file);

        if ($result['code'] !== 0) {
            return $this->error($result['msg'], $result['code'], $result['data']);
        }

        return $this->success($result['data'], $result['msg']);
    }
}
