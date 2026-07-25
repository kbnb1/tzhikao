<?php
// +----------------------------------------------------------------------
// | 考试服务类
// +----------------------------------------------------------------------

declare(strict_types=1);

namespace app\service;

use app\model\Subject;
use app\model\Question;
use app\model\ExamPaper;
use app\model\ExamPaperQuestion;
use app\model\ExamRecord;
use app\model\ExamAnswer;

/**
 * 考试服务类
 * 负责考试相关的核心业务逻辑
 */
class ExamService
{
    /**
     * 获取科目列表
     * @return array
     */
    public function getSubjectList(): array
    {
        $list = Subject::getEnabledList();

        $data = [];
        foreach ($list as $subject) {
            $data[] = [
                'id'          => $subject->id,
                'name'        => $subject->name,
                'icon'        => $subject->icon ?? '',
                'description' => $subject->description ?? '',
                'sort'        => $subject->sort,
                'status'      => $subject->status,
                'create_time' => $subject->create_time,
            ];
        }

        return ['code' => 0, 'msg' => '获取成功', 'data' => $data];
    }

    /**
     * 获取试卷列表（支持按科目筛选、分页）
     * @param int $subjectId 科目ID（0表示全部）
     * @param int $page 页码
     * @param int $pageSize 每页条数
     * @return array
     */
    public function getExamPaperList(int $subjectId = 0, int $page = 1, int $pageSize = 10): array
    {
        $paginate = ExamPaper::getEnabledList($subjectId, $page, $pageSize);

        $items = [];
        foreach ($paginate->items() as $paper) {
            $items[] = [
                'id'             => $paper->id,
                'subject_id'     => $paper->subject_id,
                'subject_name'   => $paper->subject ? $paper->subject->name : '',
                'title'          => $paper->title,
                'description'    => $paper->description ?? '',
                'type'           => $paper->type,
                'duration'       => $paper->duration,
                'total_score'    => $paper->total_score,
                'pass_score'     => $paper->pass_score,
                'question_count' => $paper->getQuestionCount(),
                'sort'           => $paper->sort,
                'status'         => $paper->status,
                'create_time'    => $paper->create_time,
            ];
        }

        $data = [
            'total'       => $paginate->total(),
            'page'        => $paginate->currentPage(),
            'page_size'   => $paginate->listRows(),
            'total_pages' => $paginate->lastPage(),
            'items'       => $items,
        ];

        return ['code' => 0, 'msg' => '获取成功', 'data' => $data];
    }

    /**
     * 获取试卷详情（含题目列表）
     * @param int $examPaperId 试卷ID
     * @return array
     */
    public function getExamPaperDetail(int $examPaperId): array
    {
        $paper = ExamPaper::where('id', $examPaperId)
            ->where('status', ExamPaper::STATUS_ENABLED)
            ->with('subject')
            ->find();

        if (!$paper) {
            return ['code' => 1, 'msg' => '试卷不存在或已下架', 'data' => []];
        }

        // 获取试卷题目
        $paperQuestions = ExamPaperQuestion::where('exam_paper_id', $examPaperId)
            ->with('question')
            ->order('sort', 'asc')
            ->order('id', 'asc')
            ->select();

        $questions = [];
        $totalScore = 0;
        foreach ($paperQuestions as $pq) {
            $question = $pq->question;
            if (!$question) {
                continue;
            }

            $totalScore += $pq->score;

            // 返回题目信息时不含正确答案（防止作弊）
            $questions[] = [
                'paper_question_id' => $pq->id,
                'question_id'       => $question->id,
                'type'              => $question->type,
                'type_text'         => Question::getTypeText($question->type),
                'difficulty'        => $question->difficulty,
                'difficulty_text'   => Question::getDifficultyText($question->difficulty),
                'title'             => $question->title,
                'options'           => $question->options,
                'score'             => $pq->score,
                'sort'              => $pq->sort,
            ];
        }

        $data = [
            'id'             => $paper->id,
            'subject_id'     => $paper->subject_id,
            'subject_name'   => $paper->subject ? $paper->subject->name : '',
            'title'          => $paper->title,
            'description'    => $paper->description ?? '',
            'type'           => $paper->type,
            'duration'       => $paper->duration,
            'total_score'    => $totalScore,
            'pass_score'     => $paper->pass_score,
            'question_count' => count($questions),
            'questions'      => $questions,
            'sort'           => $paper->sort,
            'status'         => $paper->status,
            'create_time'    => $paper->create_time,
        ];

        return ['code' => 0, 'msg' => '获取成功', 'data' => $data];
    }

    /**
     * 开始考试（创建考试记录）
     * @param int $userId 用户ID
     * @param int $examPaperId 试卷ID
     * @return array
     */
    public function startExam(int $userId, int $examPaperId): array
    {
        // 校验试卷是否存在且启用
        $paper = ExamPaper::where('id', $examPaperId)
            ->where('status', ExamPaper::STATUS_ENABLED)
            ->find();

        if (!$paper) {
            return ['code' => 1, 'msg' => '试卷不存在或已下架', 'data' => []];
        }

        // 开始考试
        $record = ExamRecord::startExam($userId, $examPaperId, $paper->duration);

        if (!$record) {
            return ['code' => 1, 'msg' => '开始考试失败', 'data' => []];
        }

        // 获取试卷题目
        $paperQuestions = ExamPaperQuestion::where('exam_paper_id', $examPaperId)
            ->with('question')
            ->order('sort', 'asc')
            ->order('id', 'asc')
            ->select();

        $questions = [];
        $totalScore = 0;
        $totalCount = 0;
        foreach ($paperQuestions as $pq) {
            $question = $pq->question;
            if (!$question) {
                continue;
            }

            $totalScore += $pq->score;
            $totalCount++;

            $questions[] = [
                'paper_question_id' => $pq->id,
                'question_id'       => $question->id,
                'type'              => $question->type,
                'type_text'         => Question::getTypeText($question->type),
                'difficulty'        => $question->difficulty,
                'difficulty_text'   => Question::getDifficultyText($question->difficulty),
                'title'             => $question->title,
                'options'           => $question->options,
                'score'             => $pq->score,
                'sort'              => $pq->sort,
            ];
        }

        // 更新考试记录的总分和题目数
        $record->total_score = $totalScore;
        $record->total_count = $totalCount;
        $record->save();

        $data = [
            'exam_record_id' => $record->id,
            'exam_paper_id'  => $paper->id,
            'paper_title'    => $paper->title,
            'status'         => $record->status,
            'status_text'    => ExamRecord::getStatusText($record->status),
            'duration'       => $record->duration,
            'start_time'     => $record->start_time ? date('Y-m-d H:i:s', $record->start_time) : '',
            'total_score'    => $totalScore,
            'question_count' => $totalCount,
            'questions'      => $questions,
        ];

        return ['code' => 0, 'msg' => '开始考试成功', 'data' => $data];
    }

    /**
     * 提交单题答案
     * @param int $userId 用户ID
     * @param int $examRecordId 考试记录ID
     * @param int $questionId 题目ID
     * @param string $userAnswer 用户答案
     * @return array
     */
    public function submitSingleAnswer(int $userId, int $examRecordId, int $questionId, string $userAnswer): array
    {
        // 校验考试记录是否存在且属于该用户
        $record = ExamRecord::where('id', $examRecordId)
            ->where('user_id', $userId)
            ->find();

        if (!$record) {
            return ['code' => 1, 'msg' => '考试记录不存在', 'data' => []];
        }

        // 校验考试状态
        if ($record->status != ExamRecord::STATUS_IN_PROGRESS) {
            return ['code' => 1, 'msg' => '考试已提交或已结束', 'data' => []];
        }

        // 查找题目信息（通过试卷题目关联表）
        $paperQuestion = ExamPaperQuestion::where('exam_paper_id', $record->exam_paper_id)
            ->where('question_id', $questionId)
            ->find();

        if (!$paperQuestion) {
            return ['code' => 1, 'msg' => '题目不存在', 'data' => []];
        }

        $question = Question::find($questionId);
        if (!$question) {
            return ['code' => 1, 'msg' => '题目不存在', 'data' => []];
        }

        // 判断答案是否正确并计算得分
        $isCorrect = $question->checkAnswer($userAnswer) ? 1 : 0;
        $score = $isCorrect ? $paperQuestion->score : 0;

        // 保存答题记录
        ExamAnswer::saveAnswer($examRecordId, $questionId, $userAnswer, $score, $isCorrect);

        $data = [
            'question_id' => $questionId,
            'is_correct'  => $isCorrect,
            'score'       => $score,
        ];

        return ['code' => 0, 'msg' => '提交成功', 'data' => $data];
    }

    /**
     * 提交整卷答案并自动评分
     * @param int $userId 用户ID
     * @param int $examRecordId 考试记录ID
     * @param array $answers 答题数组 [['question_id' => xx, 'user_answer' => xx], ...]
     * @return array
     */
    public function submitExam(int $userId, int $examRecordId, array $answers): array
    {
        // 校验考试记录是否存在且属于该用户
        $record = ExamRecord::where('id', $examRecordId)
            ->where('user_id', $userId)
            ->find();

        if (!$record) {
            return ['code' => 1, 'msg' => '考试记录不存在', 'data' => []];
        }

        // 校验考试状态
        if ($record->status == ExamRecord::STATUS_GRADED) {
            return ['code' => 1, 'msg' => '考试已评分，请勿重复提交', 'data' => []];
        }

        // 获取试卷所有题目及分值
        $paperQuestions = ExamPaperQuestion::where('exam_paper_id', $record->exam_paper_id)
            ->with('question')
            ->select();

        // 建立 question_id => 信息 的映射
        $questionMap = [];
        foreach ($paperQuestions as $pq) {
            if ($pq->question) {
                $questionMap[$pq->question_id] = [
                    'paper_question' => $pq,
                    'question'       => $pq->question,
                ];
            }
        }

        // 计算每道题的得分
        $totalScore = 0;
        $correctCount = 0;
        $totalCount = count($questionMap);
        $answerResults = [];

        // 处理所有题目
        foreach ($questionMap as $questionId => $info) {
            $question = $info['question'];
            $paperQuestion = $info['paper_question'];

            // 查找用户答案
            $userAnswer = '';
            foreach ($answers as $ans) {
                if ($ans['question_id'] == $questionId) {
                    $userAnswer = (string)($ans['user_answer'] ?? '');
                    break;
                }
            }

            // 判分
            $isCorrect = !empty($userAnswer) && $question->checkAnswer($userAnswer) ? 1 : 0;
            $score = $isCorrect ? $paperQuestion->score : 0;

            $totalScore += $score;
            if ($isCorrect) {
                $correctCount++;
            }

            // 保存答题记录
            ExamAnswer::saveAnswer($examRecordId, $questionId, $userAnswer, $score, $isCorrect);

            $answerResults[] = [
                'question_id' => $questionId,
                'user_answer' => $userAnswer,
                'is_correct'  => $isCorrect,
                'score'       => $score,
                'full_score'  => $paperQuestion->score,
            ];
        }

        // 计算正确率
        $accuracy = $totalCount > 0 ? round(($correctCount / $totalCount) * 100, 2) : 0;

        // 更新考试记录
        $record->status = ExamRecord::STATUS_GRADED;
        $record->score = $totalScore;
        $record->correct_count = $correctCount;
        $record->total_count = $totalCount;
        $record->end_time = time();
        $record->save();

        $data = [
            'exam_record_id' => $record->id,
            'status'         => $record->status,
            'status_text'    => ExamRecord::getStatusText($record->status),
            'score'          => $totalScore,
            'total_score'    => $record->total_score,
            'correct_count'  => $correctCount,
            'total_count'    => $totalCount,
            'accuracy'       => $accuracy,
            'answers'        => $answerResults,
        ];

        return ['code' => 0, 'msg' => '提交成功', 'data' => $data];
    }

    /**
     * 获取考试记录列表（分页）
     * @param int $userId 用户ID
     * @param int $page 页码
     * @param int $pageSize 每页条数
     * @return array
     */
    public function getExamRecordList(int $userId, int $page = 1, int $pageSize = 10): array
    {
        $paginate = ExamRecord::getUserRecords($userId, $page, $pageSize);

        $items = [];
        foreach ($paginate->items() as $record) {
            $paper = $record->examPaper;
            $items[] = [
                'id'             => $record->id,
                'exam_paper_id'  => $record->exam_paper_id,
                'paper_title'    => $paper ? $paper->title : '',
                'subject_id'     => $paper ? $paper->subject_id : 0,
                'subject_name'   => ($paper && $paper->subject) ? $paper->subject->name : '',
                'status'         => $record->status,
                'status_text'    => ExamRecord::getStatusText($record->status),
                'score'          => $record->score,
                'total_score'    => $record->total_score,
                'correct_count'  => $record->correct_count,
                'total_count'    => $record->total_count,
                'accuracy'       => $record->getAccuracy(),
                'start_time'     => $record->start_time ? date('Y-m-d H:i:s', $record->start_time) : '',
                'end_time'       => $record->end_time ? date('Y-m-d H:i:s', $record->end_time) : '',
                'create_time'    => $record->create_time,
            ];
        }

        $data = [
            'total'       => $paginate->total(),
            'page'        => $paginate->currentPage(),
            'page_size'   => $paginate->listRows(),
            'total_pages' => $paginate->lastPage(),
            'items'       => $items,
        ];

        return ['code' => 0, 'msg' => '获取成功', 'data' => $data];
    }

    /**
     * 获取考试记录详情（含答题明细、答案解析）
     * @param int $userId 用户ID
     * @param int $examRecordId 考试记录ID
     * @return array
     */
    public function getExamRecordDetail(int $userId, int $examRecordId): array
    {
        // 校验考试记录是否存在且属于该用户
        $record = ExamRecord::where('id', $examRecordId)
            ->where('user_id', $userId)
            ->with('examPaper.subject')
            ->find();

        if (!$record) {
            return ['code' => 1, 'msg' => '考试记录不存在', 'data' => []];
        }

        $paper = $record->examPaper;

        // 获取答题明细（含题目信息）
        $answers = ExamAnswer::where('exam_record_id', $examRecordId)
            ->with('question')
            ->order('id', 'asc')
            ->select();

        // 获取试卷题目关联信息（用于获取分值和排序）
        $paperQuestionMap = [];
        if ($paper) {
            $paperQuestions = ExamPaperQuestion::where('exam_paper_id', $record->exam_paper_id)
                ->select();
            foreach ($paperQuestions as $pq) {
                $paperQuestionMap[$pq->question_id] = $pq;
            }
        }

        $answerDetails = [];
        foreach ($answers as $answer) {
            $question = $answer->question;
            if (!$question) {
                continue;
            }

            $pq = $paperQuestionMap[$question->id] ?? null;

            $answerDetails[] = [
                'question_id'     => $question->id,
                'type'            => $question->type,
                'type_text'       => Question::getTypeText($question->type),
                'difficulty'      => $question->difficulty,
                'difficulty_text' => Question::getDifficultyText($question->difficulty),
                'title'           => $question->title,
                'options'         => $question->options,
                'score'           => $pq ? $pq->score : 0,
                'user_answer'     => $answer->user_answer,
                'correct_answer'  => $question->answer,
                'is_correct'      => $answer->is_correct,
                'user_score'      => $answer->score,
                'analysis'        => $question->analysis ?? '',
            ];
        }

        $data = [
            'id'             => $record->id,
            'exam_paper_id'  => $record->exam_paper_id,
            'paper_title'    => $paper ? $paper->title : '',
            'subject_id'     => $paper ? $paper->subject_id : 0,
            'subject_name'   => ($paper && $paper->subject) ? $paper->subject->name : '',
            'status'         => $record->status,
            'status_text'    => ExamRecord::getStatusText($record->status),
            'score'          => $record->score,
            'total_score'    => $record->total_score,
            'correct_count'  => $record->correct_count,
            'total_count'    => $record->total_count,
            'accuracy'       => $record->getAccuracy(),
            'duration'       => $record->duration,
            'start_time'     => $record->start_time ? date('Y-m-d H:i:s', $record->start_time) : '',
            'end_time'       => $record->end_time ? date('Y-m-d H:i:s', $record->end_time) : '',
            'create_time'    => $record->create_time,
            'answers'        => $answerDetails,
        ];

        return ['code' => 0, 'msg' => '获取成功', 'data' => $data];
    }
}
