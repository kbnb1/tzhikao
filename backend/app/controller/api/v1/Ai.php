<?php

declare(strict_types=1);

namespace app\controller\api\v1;

use app\BaseController;
use app\service\AiService;
use think\response\Json;

/**
 * AI模块控制器
 */
class Ai extends BaseController
{
    protected $middleware = ['jwt_auth'];

    protected $aiService;

    public function __construct(\think\App $app)
    {
        parent::__construct($app);
        $this->aiService = new AiService();
    }

    /**
     * 获取AI配置列表（用户端只读，不返回密钥）
     * @return Json
     */
    public function configList(): Json
    {
        try {
            $list = $this->aiService->getConfigList();
            return $this->success($list, '获取成功');
        } catch (\Exception $e) {
            return $this->error($e->getMessage());
        }
    }

    /**
     * AI生成试卷
     * @return Json
     */
    public function generatePaper(): Json
    {
        try {
            $subject = $this->request->param('subject', '');
            $difficulty = $this->request->param('difficulty', 'medium');
            $questionCount = (int)$this->request->param('question_count', 10);
            $provider = $this->request->param('provider', '');

            if (empty($subject)) {
                return $this->error('科目不能为空');
            }

            if (!in_array($difficulty, ['easy', 'medium', 'hard'])) {
                return $this->error('难度参数错误');
            }

            if ($questionCount < 1 || $questionCount > 100) {
                return $this->error('题目数量应在1-100之间');
            }

            $service = !empty($provider) ? new AiService($provider) : $this->aiService;
            $result = $service->generatePaper($subject, $difficulty, $questionCount);

            return $this->success($result, '生成成功');
        } catch (\Exception $e) {
            return $this->error('生成试卷失败: ' . $e->getMessage());
        }
    }

    /**
     * AI成绩预测
     * @return Json
     */
    public function predictScore(): Json
    {
        try {
            $subject = $this->request->param('subject', '');
            $historyScores = $this->request->param('history_scores', []);
            $provider = $this->request->param('provider', '');

            if (empty($subject)) {
                return $this->error('科目不能为空');
            }

            if (!is_array($historyScores)) {
                $historyScores = json_decode($historyScores, true) ?: [];
            }

            $service = !empty($provider) ? new AiService($provider) : $this->aiService;
            $result = $service->predictScore($this->userId, $subject, $historyScores);

            return $this->success($result, '预测成功');
        } catch (\Exception $e) {
            return $this->error('成绩预测失败: ' . $e->getMessage());
        }
    }

    /**
     * 预测记录列表
     * @return Json
     */
    public function predictionList(): Json
    {
        try {
            $page = (int)$this->request->param('page', 1);
            $limit = (int)$this->request->param('limit', 10);
            $subject = $this->request->param('subject', '');

            $result = $this->aiService->getPredictionList(
                $this->userId,
                $page,
                $limit,
                !empty($subject) ? $subject : null
            );

            return $this->success($result, '获取成功');
        } catch (\Exception $e) {
            return $this->error($e->getMessage());
        }
    }
}
