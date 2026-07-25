<?php

declare(strict_types=1);

namespace app\controller\api\v1;

use app\BaseController;
use app\service\UserService;
use think\response\Json;

class Auth extends BaseController
{
    private UserService $userService;

    public function __construct(\think\App $app)
    {
        parent::__construct($app);
        $this->userService = new UserService();
    }

    public function login(): Json
    {
        $data = $this->request->post();
        $this->validate($data, 'Auth.login');

        $account = $data['account'] ?? '';
        $password = $data['password'] ?? '';

        $result = $this->userService->login($account, $password);

        if ($result['code'] !== 0) {
            return $this->error($result['msg'], $result['code'], $result['data']);
        }

        return $this->success($result['data'], $result['msg']);
    }

    public function register(): Json
    {
        $data = $this->request->post();
        $this->validate($data, 'Auth.register');

        $phone = $data['phone'];
        $password = $data['password'];
        $code = $data['code'];

        $result = $this->userService->register($phone, $password, $code);

        if ($result['code'] !== 0) {
            return $this->error($result['msg'], $result['code'], $result['data']);
        }

        return $this->success($result['data'], $result['msg']);
    }

    public function sendCode(): Json
    {
        $data = $this->request->post();
        $this->validate($data, 'Auth.sendCode');

        $phone = $data['phone'];
        $result = $this->userService->sendSmsCode($phone);

        if ($result['code'] !== 0) {
            return $this->error($result['msg'], $result['code'], $result['data']);
        }

        return $this->success($result['data'], $result['msg']);
    }

    public function refresh(): Json
    {
        $data = $this->request->post();
        $this->validate($data, 'Auth.refresh');

        $refreshToken = $data['refresh_token'];
        $result = $this->userService->refreshToken($refreshToken);

        if ($result['code'] !== 0) {
            return $this->error($result['msg'], $result['code'], $result['data']);
        }

        return $this->success($result['data'], $result['msg']);
    }

    public function forgotPassword(): Json
    {
        $data = $this->request->post();
        $this->validate($data, 'Auth.forgotPassword');

        $phone = $data['phone'];
        $password = $data['password'];
        $code = $data['code'];

        $result = $this->userService->forgotPassword($phone, $password, $code);

        if ($result['code'] !== 0) {
            return $this->error($result['msg'], $result['code'], $result['data']);
        }

        return $this->success($result['data'], $result['msg']);
    }
}
