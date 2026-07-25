<?php

declare(strict_types=1);

namespace app\controller\admin\v1;

use app\BaseController;
use app\service\AdminService;
use think\response\Json;

/**
 * 后台登录控制器
 */
class Auth extends BaseController
{
    /**
     * 管理员登录
     * @return Json
     */
    public function login(): Json
    {
        $username = $this->request->param('username', '');
        $password = $this->request->param('password', '');

        if (empty($username) || empty($password)) {
            return $this->error('用户名和密码不能为空');
        }

        $adminService = new AdminService();
        $result = $adminService->login($username, $password);

        if ($result['code'] !== 0) {
            return $this->error($result['msg']);
        }

        return $this->success($result['data'], '登录成功');
    }

    /**
     * 退出登录
     * @return Json
     */
    public function logout(): Json
    {
        return $this->success([], '退出成功');
    }

    /**
     * 获取当前管理员信息
     * @return Json
     */
    public function info(): Json
    {
        $data = [
            'id' => $this->request->adminId,
            'username' => $this->request->adminName,
            'nickname' => $this->userInfo['nickname'] ?? '',
            'avatar' => $this->userInfo['avatar'] ?? '',
        ];

        return $this->success($data, '获取成功');
    }
}
