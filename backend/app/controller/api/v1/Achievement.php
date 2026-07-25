<?php

declare(strict_types=1);

namespace app\controller\api\v1;

use app\BaseController;
use app\service\AchievementService;
use think\response\Json;

/**
 * 成就控制器
 */
class Achievement extends BaseController
{
    private AchievementService $achievementService;

    protected $middleware = ['jwt_auth'];

    public function __construct(\think\App $app)
    {
        parent::__construct($app);
        $this->achievementService = new AchievementService();
    }

    /**
     * 成就列表（全部成就，含是否已解锁状态）
     * @return Json
     */
    public function list(): Json
    {
        $page = (int)$this->request->param('page', 1);
        $pageSize = (int)$this->request->param('page_size', 10);
        $type = $this->request->param('type', '');

        $result = $this->achievementService->getList($this->userId, $page, $pageSize, $type);

        if ($result['code'] !== 0) {
            return $this->error($result['msg'], $result['code'], $result['data']);
        }

        return $this->success($result['data'], $result['msg']);
    }

    /**
     * 我的成就（已解锁的）
     * @return Json
     */
    public function mine(): Json
    {
        $page = (int)$this->request->param('page', 1);
        $pageSize = (int)$this->request->param('page_size', 10);

        $result = $this->achievementService->getMyAchievements($this->userId, $page, $pageSize);

        if ($result['code'] !== 0) {
            return $this->error($result['msg'], $result['code'], $result['data']);
        }

        return $this->success($result['data'], $result['msg']);
    }

    /**
     * 成就详情
     * @param int $id
     * @return Json
     */
    public function detail(int $id): Json
    {
        $result = $this->achievementService->getDetail($this->userId, $id);

        if ($result['code'] !== 0) {
            return $this->error($result['msg'], $result['code'], $result['data']);
        }

        return $this->success($result['data'], $result['msg']);
    }
}
