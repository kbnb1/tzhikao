<?php

declare(strict_types=1);

namespace app\controller\api\v1;

use app\BaseController;
use app\service\WrongQuestionService;
use think\response\Json;

/**
 * 错题本模块控制器
 */
class WrongQuestion extends BaseController
{
    protected $middleware = ['jwt_auth'];

    protected $wrongQuestionService;

    public function __construct(\think\App $app)
    {
        parent::__construct($app);
        $this->wrongQuestionService = new WrongQuestionService();
    }

    /**
     * 错题列表（按科目筛选、分页）
     * @return Json
     */
    public function index(): Json
    {
        try {
            $page = (int)$this->request->param('page', 1);
            $limit = (int)$this->request->param('limit', 10);
            $subject = $this->request->param('subject', '');
            $status = $this->request->param('status', '');

            $statusVal = $status !== '' ? (int)$status : null;

            $result = $this->wrongQuestionService->getList(
                $this->userId,
                $page,
                $limit,
                !empty($subject) ? $subject : null,
                $statusVal
            );

            return $this->success($result, '获取成功');
        } catch (\Exception $e) {
            return $this->error($e->getMessage());
        }
    }

    /**
     * 添加错题（考试提交时自动添加）
     * @return Json
     */
    public function add(): Json
    {
        try {
            $questionId = (int)$this->request->param('question_id', 0);
            $subject = $this->request->param('subject', '');
            $questionType = $this->request->param('question_type', '');
            $questionContent = $this->request->param('question_content', '');
            $userAnswer = $this->request->param('user_answer', '');
            $correctAnswer = $this->request->param('correct_answer', '');
            $analysis = $this->request->param('analysis', '');
            $examId = $this->request->param('exam_id', '');

            if (empty($questionId)) {
                return $this->error('题目ID不能为空');
            }

            $data = [
                'question_id' => $questionId,
                'subject' => $subject,
                'question_type' => $questionType,
                'question_content' => $questionContent,
                'user_answer' => $userAnswer,
                'correct_answer' => $correctAnswer,
                'analysis' => $analysis,
                'exam_id' => $examId,
            ];

            $result = $this->wrongQuestionService->addWrong($this->userId, $data);

            return $this->success($result, '添加成功');
        } catch (\Exception $e) {
            return $this->error('添加错题失败: ' . $e->getMessage());
        }
    }

    /**
     * 批量添加错题（考试提交时自动添加）
     * @return Json
     */
    public function batchAdd(): Json
    {
        try {
            $questions = $this->request->param('questions', []);

            if (!is_array($questions)) {
                $questions = json_decode($questions, true) ?: [];
            }

            if (empty($questions)) {
                return $this->error('错题列表不能为空');
            }

            $count = $this->wrongQuestionService->batchAddWrong($this->userId, $questions);

            return $this->success(['count' => $count], "成功添加{$count}道错题");
        } catch (\Exception $e) {
            return $this->error('批量添加错题失败: ' . $e->getMessage());
        }
    }

    /**
     * 移除错题
     * @return Json
     */
    public function remove(): Json
    {
        try {
            $id = (int)$this->request->param('id', 0);

            if (empty($id)) {
                return $this->error('错题ID不能为空');
            }

            $result = $this->wrongQuestionService->remove($id, $this->userId);

            if (!$result) {
                return $this->error('错题不存在或删除失败');
            }

            return $this->success([], '移除成功');
        } catch (\Exception $e) {
            return $this->error($e->getMessage());
        }
    }

    /**
     * 标记已掌握
     * @return Json
     */
    public function markMastered(): Json
    {
        try {
            $id = (int)$this->request->param('id', 0);

            if (empty($id)) {
                return $this->error('错题ID不能为空');
            }

            $result = $this->wrongQuestionService->markMastered($id, $this->userId);

            if (!$result) {
                return $this->error('错题不存在或操作失败');
            }

            return $this->success([], '标记成功');
        } catch (\Exception $e) {
            return $this->error($e->getMessage());
        }
    }

    /**
     * 错题详情（含解析）
     * @return Json
     */
    public function detail(): Json
    {
        try {
            $id = (int)$this->request->param('id', 0);

            if (empty($id)) {
                return $this->error('错题ID不能为空');
            }

            $detail = $this->wrongQuestionService->getDetail($id, $this->userId);

            if (!$detail) {
                return $this->error('错题不存在');
            }

            return $this->success($detail, '获取成功');
        } catch (\Exception $e) {
            return $this->error($e->getMessage());
        }
    }

    /**
     * 薄弱点分析（统计各科目错题数量）
     * @return Json
     */
    public function weakPoints(): Json
    {
        try {
            $result = $this->wrongQuestionService->getWeakPoints($this->userId);
            return $this->success($result, '获取成功');
        } catch (\Exception $e) {
            return $this->error($e->getMessage());
        }
    }

    /**
     * 生成强化练习试卷（从错题中随机抽题）
     * @return Json
     */
    public function generatePractice(): Json
    {
        try {
            $subject = $this->request->param('subject', '');
            $count = (int)$this->request->param('count', 10);

            if ($count < 1 || $count > 100) {
                return $this->error('题目数量应在1-100之间');
            }

            $result = $this->wrongQuestionService->generatePracticePaper(
                $this->userId,
                !empty($subject) ? $subject : null,
                $count
            );

            if (empty($result['questions'])) {
                return $this->error('暂无错题，无法生成练习试卷');
            }

            return $this->success($result, '生成成功');
        } catch (\Exception $e) {
            return $this->error('生成练习试卷失败: ' . $e->getMessage());
        }
    }
}
