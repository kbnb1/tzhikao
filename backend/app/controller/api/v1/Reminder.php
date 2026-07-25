<?php

declare(strict_types=1);

namespace app\controller\api\v1;

use app\BaseController;
use app\service\ReminderService;
use think\response\Json;

/**
 * 学习提醒控制器
 */
class Reminder extends BaseController
{
    private ReminderService $reminderService;

    protected $middleware = ['jwt_auth'];

    public function __construct(\think\App $app)
    {
        parent::__construct($app);
        $this->reminderService = new ReminderService();
    }

    /**
     * 提醒列表
     * @return Json
     */
    public function list(): Json
    {
        $page = (int)$this->request->param('page', 1);
        $pageSize = (int)$this->request->param('page_size', 10);
        $status = (int)$this->request->param('status', -1);

        $result = $this->reminderService->getList($this->userId, $page, $pageSize, $status);

        if ($result['code'] !== 0) {
            return $this->error($result['msg'], $result['code'], $result['data']);
        }

        return $this->success($result['data'], $result['msg']);
    }

    /**
     * 添加提醒
     * @return Json
     */
    public function add(): Json
    {
        $data = $this->request->post();

        $result = $this->reminderService->add($this->userId, $data);

        if ($result['code'] !== 0) {
            return $this->error($result['msg'], $result['code'], $result['data']);
        }

        return $this->success($result['data'], $result['msg']);
    }

    /**
     * 编辑提醒
     * @param int $id
     * @return Json
     */
    public function edit(int $id): Json
    {
        $data = $this->request->put();

        $result = $this->reminderService->edit($this->userId, $id, $data);

        if ($result['code'] !== 0) {
            return $this->error($result['msg'], $result['code'], $result['data']);
        }

        return $this->success($result['data'], $result['msg']);
    }

    /**
     * 删除提醒
     * @param int $id
     * @return Json
     */
    public function delete(int $id): Json
    {
        $result = $this->reminderService->delete($this->userId, $id);

        if ($result['code'] !== 0) {
            return $this->error($result['msg'], $result['code'], $result['data']);
        }

        return $this->success($result['data'], $result['msg']);
    }

    /**
     * 开关提醒
     * @param int $id
     * @return Json
     */
    public function toggle(int $id): Json
    {
        $status = (int)$this->request->param('status', 1);

        $result = $this->reminderService->toggle($this->userId, $id, $status);

        if ($result['code'] !== 0) {
            return $this->error($result['msg'], $result['code'], $result['data']);
        }

        return $this->success($result['data'], $result['msg']);
    }
}
