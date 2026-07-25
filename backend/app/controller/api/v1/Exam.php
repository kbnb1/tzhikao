<?php
// +----------------------------------------------------------------------
// | 考试控制器
// +----------------------------------------------------------------------

declare(strict_types=1);

namespace app\controller\api\v1;

use app\BaseController;
use app\service\ExamService;
use think\response\Json;

/**
 * 考试模块控制器
 * 提供考试测评相关的API接口
 * 所有接口需要jwt_auth中间件认证
 */
class Exam extends BaseController
{
    // 中间件配置
    protected $middleware = ['jwt_auth'];

    // 考试服务类实例
    private ExamService $examService;

    /**
     * 构造函数
     * @param \think\App $app
     */
    public function __construct(\think\App $app)
    {
        parent::__construct($app);
        $this->examService = new ExamService();
    }

    /**
     * 获取科目列表
     * @return Json
     */
    public function subjectList(): Json
    {
        $result = $this->examService->getSubjectList();

        if ($result['code'] !== 0) {
            return $this->error($result['msg'], $result['code'], $result['data']);
        }

        return $this->success($result['data'], $result['msg']);
    }

    /**
     * 获取试卷列表
     * 支持按科目筛选、分页
     * @return Json
     */
    public function examPaperList(): Json
    {
        $subjectId = (int)$this->request->param('subject_id', 0);
        $page = (int)$this->request->param('page', 1);
        $pageSize = (int)$this->request->param('page_size', 10);

        // 参数校验
        if ($page < 1) {
            $page = 1;
        }
        if ($pageSize < 1 || $pageSize > 100) {
            $pageSize = 10;
        }

        $result = $this->examService->getExamPaperList($subjectId, $page, $pageSize);

        if ($result['code'] !== 0) {
            return $this->error($result['msg'], $result['code'], $result['data']);
        }

        return $this->success($result['data'], $result['msg']);
    }

    /**
     * 获取试卷详情（含题目列表）
     * @return Json
     */
    public function examPaperDetail(): Json
    {
        $examPaperId = (int)$this->request->param('exam_paper_id', 0);

        if ($examPaperId <= 0) {
            return $this->error('试卷ID不能为空');
        }

        $result = $this->examService->getExamPaperDetail($examPaperId);

        if ($result['code'] !== 0) {
            return $this->error($result['msg'], $result['code'], $result['data']);
        }

        return $this->success($result['data'], $result['msg']);
    }

    /**
     * 开始考试
     * 创建考试记录并返回试卷题目
     * @return Json
     */
    public function startExam(): Json
    {
        $examPaperId = (int)$this->request->param('exam_paper_id', 0);

        if ($examPaperId <= 0) {
            return $this->error('试卷ID不能为空');
        }

        $result = $this->examService->startExam($this->userId, $examPaperId);

        if ($result['code'] !== 0) {
            return $this->error($result['msg'], $result['code'], $result['data']);
        }

        return $this->success($result['data'], $result['msg']);
    }

    /**
     * 提交单题答案
     * @return Json
     */
    public function submitSingleAnswer(): Json
    {
        $examRecordId = (int)$this->request->param('exam_record_id', 0);
        $questionId = (int)$this->request->param('question_id', 0);
        $userAnswer = (string)$this->request->param('user_answer', '');

        if ($examRecordId <= 0) {
            return $this->error('考试记录ID不能为空');
        }
        if ($questionId <= 0) {
            return $this->error('题目ID不能为空');
        }

        $result = $this->examService->submitSingleAnswer($this->userId, $examRecordId, $questionId, $userAnswer);

        if ($result['code'] !== 0) {
            return $this->error($result['msg'], $result['code'], $result['data']);
        }

        return $this->success($result['data'], $result['msg']);
    }

    /**
     * 提交整卷答案并自动评分
     * @return Json
     */
    public function submitExam(): Json
    {
        $examRecordId = (int)$this->request->param('exam_record_id', 0);
        $answers = $this->request->param('answers/a', []);

        if ($examRecordId <= 0) {
            return $this->error('考试记录ID不能为空');
        }
        if (!is_array($answers) || empty($answers)) {
            return $this->error('请提交答案');
        }

        $result = $this->examService->submitExam($this->userId, $examRecordId, $answers);

        if ($result['code'] !== 0) {
            return $this->error($result['msg'], $result['code'], $result['data']);
        }

        return $this->success($result['data'], $result['msg']);
    }

    /**
     * 获取考试记录列表（分页）
     * @return Json
     */
    public function examRecordList(): Json
    {
        $page = (int)$this->request->param('page', 1);
        $pageSize = (int)$this->request->param('page_size', 10);

        // 参数校验
        if ($page < 1) {
            $page = 1;
        }
        if ($pageSize < 1 || $pageSize > 100) {
            $pageSize = 10;
        }

        $result = $this->examService->getExamRecordList($this->userId, $page, $pageSize);

        if ($result['code'] !== 0) {
            return $this->error($result['msg'], $result['code'], $result['data']);
        }

        return $this->success($result['data'], $result['msg']);
    }

    /**
     * 获取考试记录详情（含答题明细、答案解析）
     * @return Json
     */
    public function examRecordDetail(): Json
    {
        $examRecordId = (int)$this->request->param('exam_record_id', 0);

        if ($examRecordId <= 0) {
            return $this->error('考试记录ID不能为空');
        }

        $result = $this->examService->getExamRecordDetail($this->userId, $examRecordId);

        if ($result['code'] !== 0) {
            return $this->error($result['msg'], $result['code'], $result['data']);
        }

        return $this->success($result['data'], $result['msg']);
    }
}
