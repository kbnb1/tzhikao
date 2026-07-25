<?php

declare(strict_types=1);

namespace app\controller\admin\v1;

use app\BaseController;
use app\service\AdminService;
use think\response\Json;

/**
 * 仪表盘控制器
 */
class Dashboard extends BaseController
{
    /**
     * 获取仪表盘统计数据
     * @return Json
     */
    public function index(): Json
    {
        $adminService = new AdminService();
        $result = $adminService->getDashboardStats();

        return $this->success($result['data'], '获取成功');
    }
}
