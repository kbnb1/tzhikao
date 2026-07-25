<?php

declare(strict_types=1);

namespace app\controller\admin\v1;

use app\BaseController;
use think\facade\Db;
use think\response\Json;

/**
 * 试卷管理控制器
 */
class ExamPaper extends BaseController
{
    /**
     * 试卷列表
     * @return Json
     */
    public function list(): Json
    {
        $page = $this->request->param('page', 1);
        $pageSize = $this->request->param('page_size', 10);
        $keyword = $this->request->param('keyword', '');
        $subjectId = $this->request->param('subject_id', '');
        $status = $this->request->param('status', '');

        $where = [];
        if ($keyword) {
            $where[] = ['name', 'like', '%' . $keyword . '%'];
        }
        if ($subjectId !== '') {
            $where[] = ['subject_id', '=', (int)$subjectId];
        }
        if ($status !== '') {
            $where[] = ['status', '=', (int)$status];
        }

        $query = Db::name('exam_papers')->where($where);
        $total = $query->count();
        $list = $query->field('id,subject_id,name,description,total_score,question_count,duration,status,create_time')
            ->order('id desc')
            ->page($page, $pageSize)
            ->select()
            ->toArray();

        $subjectIds = array_column($list, 'subject_id');
        if (!empty($subjectIds)) {
            $subjects = Db::name('subjects')->whereIn('id', array_unique($subjectIds))->column('name', 'id');
            foreach ($list as &$item) {
                $item['subject_name'] = $subjects[$item['subject_id']] ?? '';
            }
        }

        $data = [
            'list' => $list,
            'total' => $total,
            'page' => (int)$page,
            'page_size' => (int)$pageSize,
            'total_page' => ceil($total / $pageSize),
        ];

        return $this->success($data, '获取成功');
    }

    /**
     * 新增试卷
     * @return Json
     */
    public function create(): Json
    {
        $subjectId = $this->request->param('subject_id', 0);
        $name = $this->request->param('name', '');
        $description = $this->request->param('description', '');
        $totalScore = $this->request->param('total_score', 0);
        $duration = $this->request->param('duration', 0);
        $passScore = $this->request->param('pass_score', 0);
        $status = $this->request->param('status', 1);
        $questionIds = $this->request->param('question_ids', []);

        if (empty($subjectId)) {
            return $this->error('请选择科目');
        }
        if (empty($name)) {
            return $this->error('试卷名称不能为空');
        }

        $subject = Db::name('subjects')->where('id', $subjectId)->find();
        if (!$subject) {
            return $this->error('科目不存在');
        }

        $exists = Db::name('exam_papers')->where('name', $name)->find();
        if ($exists) {
            return $this->error('试卷名称已存在');
        }

        Db::startTrans();
        try {
            $paperData = [
                'subject_id' => (int)$subjectId,
                'name' => $name,
                'description' => $description,
                'total_score' => (float)$totalScore,
                'duration' => (int)$duration,
                'pass_score' => (float)$passScore,
                'question_count' => is_array($questionIds) ? count($questionIds) : 0,
                'status' => (int)$status,
                'create_time' => time(),
                'update_time' => time(),
            ];

            $paperId = Db::name('exam_papers')->insertGetId($paperData);

            if ($paperId && !empty($questionIds) && is_array($questionIds)) {
                $questionList = Db::name('questions')
                    ->whereIn('id', array_map('intval', $questionIds))
                    ->field('id,score')
                    ->select()
                    ->toArray();

                $paperQuestions = [];
                $sort = 1;
                foreach ($questionIds as $qid) {
                    $qid = (int)$qid;
                    $question = null;
                    foreach ($questionList as $q) {
                        if ($q['id'] == $qid) {
                            $question = $q;
                            break;
                        }
                    }
                    if ($question) {
                        $paperQuestions[] = [
                            'paper_id' => $paperId,
                            'question_id' => $qid,
                            'score' => $question['score'] ?? 0,
                            'sort' => $sort++,
                            'create_time' => time(),
                        ];
                    }
                }

                if (!empty($paperQuestions)) {
                    Db::name('exam_paper_questions')->insertAll($paperQuestions);
                }
            }

            Db::commit();
            return $this->success(['id' => $paperId], '新增成功');
        } catch (\Exception $e) {
            Db::rollback();
            return $this->error('新增失败: ' . $e->getMessage());
        }
    }

    /**
     * 编辑试卷
     * @param int $id
     * @return Json
     */
    public function update(int $id): Json
    {
        $paper = Db::name('exam_papers')->where('id', $id)->find();
        if (!$paper) {
            return $this->error('试卷不存在');
        }

        $subjectId = $this->request->param('subject_id', 0);
        $name = $this->request->param('name', '');
        $description = $this->request->param('description', '');
        $totalScore = $this->request->param('total_score', '');
        $duration = $this->request->param('duration', '');
        $passScore = $this->request->param('pass_score', '');
        $status = $this->request->param('status', '');
        $questionIds = $this->request->param('question_ids', null);

        if (empty($subjectId)) {
            return $this->error('请选择科目');
        }
        if (empty($name)) {
            return $this->error('试卷名称不能为空');
        }

        $exists = Db::name('exam_papers')->where('name', $name)->where('id', '<>', $id)->find();
        if ($exists) {
            return $this->error('试卷名称已存在');
        }

        Db::startTrans();
        try {
            $data = [
                'subject_id' => (int)$subjectId,
                'name' => $name,
                'description' => $description,
                'update_time' => time(),
            ];

            if ($totalScore !== '') {
                $data['total_score'] = (float)$totalScore;
            }
            if ($duration !== '') {
                $data['duration'] = (int)$duration;
            }
            if ($passScore !== '') {
                $data['pass_score'] = (float)$passScore;
            }
            if ($status !== '') {
                $data['status'] = (int)$status;
            }

            Db::name('exam_papers')->where('id', $id)->update($data);

            if ($questionIds !== null && is_array($questionIds)) {
                Db::name('exam_paper_questions')->where('paper_id', $id)->delete();

                $questionList = Db::name('questions')
                    ->whereIn('id', array_map('intval', $questionIds))
                    ->field('id,score')
                    ->select()
                    ->toArray();

                $paperQuestions = [];
                $sort = 1;
                foreach ($questionIds as $qid) {
                    $qid = (int)$qid;
                    $question = null;
                    foreach ($questionList as $q) {
                        if ($q['id'] == $qid) {
                            $question = $q;
                            break;
                        }
                    }
                    if ($question) {
                        $paperQuestions[] = [
                            'paper_id' => $id,
                            'question_id' => $qid,
                            'score' => $question['score'] ?? 0,
                            'sort' => $sort++,
                            'create_time' => time(),
                        ];
                    }
                }

                if (!empty($paperQuestions)) {
                    Db::name('exam_paper_questions')->insertAll($paperQuestions);
                }

                Db::name('exam_papers')->where('id', $id)->update([
                    'question_count' => count($paperQuestions),
                ]);
            }

            Db::commit();
            return $this->success([], '编辑成功');
        } catch (\Exception $e) {
            Db::rollback();
            return $this->error('编辑失败: ' . $e->getMessage());
        }
    }

    /**
     * 删除试卷
     * @param int $id
     * @return Json
     */
    public function delete(int $id): Json
    {
        $paper = Db::name('exam_papers')->where('id', $id)->find();
        if (!$paper) {
            return $this->error('试卷不存在');
        }

        Db::startTrans();
        try {
            Db::name('exam_paper_questions')->where('paper_id', $id)->delete();
            Db::name('exam_papers')->where('id', $id)->delete();

            Db::commit();
            return $this->success([], '删除成功');
        } catch (\Exception $e) {
            Db::rollback();
            return $this->error('删除失败: ' . $e->getMessage());
        }
    }

    /**
     * 试卷详情
     * @param int $id
     * @return Json
     */
    public function detail(int $id): Json
    {
        $paper = Db::name('exam_papers')->where('id', $id)->find();

        if (!$paper) {
            return $this->error('试卷不存在');
        }

        $paperQuestions = Db::name('exam_paper_questions')
            ->where('paper_id', $id)
            ->order('sort asc')
            ->select()
            ->toArray();

        $questionIds = array_column($paperQuestions, 'question_id');
        $questions = [];
        if (!empty($questionIds)) {
            $questionList = Db::name('questions')
                ->whereIn('id', $questionIds)
                ->field('id,subject_id,title,type,difficulty,score,options,answer,analysis')
                ->select()
                ->toArray();

            $questionMap = [];
            foreach ($questionList as $q) {
                if (!empty($q['options'])) {
                    $q['options'] = json_decode($q['options'], true);
                }
                $questionMap[$q['id']] = $q;
            }

            foreach ($paperQuestions as $pq) {
                if (isset($questionMap[$pq['question_id']])) {
                    $q = $questionMap[$pq['question_id']];
                    $q['paper_question_id'] = $pq['id'];
                    $q['sort'] = $pq['sort'];
                    $q['paper_score'] = $pq['score'];
                    $questions[] = $q;
                }
            }
        }

        $paper['questions'] = $questions;
        $paper['question_ids'] = array_column($questions, 'id');

        return $this->success($paper, '获取成功');
    }

    /**
     * 获取试卷题目列表
     * @param int $id
     * @return Json
     */
    public function questions(int $id): Json
    {
        $paper = Db::name('exam_papers')->where('id', $id)->find();
        if (!$paper) {
            return $this->error('试卷不存在');
        }

        $paperQuestions = Db::name('exam_paper_questions')
            ->where('paper_id', $id)
            ->order('sort asc')
            ->select()
            ->toArray();

        $questionIds = array_column($paperQuestions, 'question_id');
        $questions = [];
        if (!empty($questionIds)) {
            $questionList = Db::name('questions')
                ->whereIn('id', $questionIds)
                ->field('id,subject_id,title,type,difficulty,score')
                ->select()
                ->toArray();

            $questionMap = [];
            foreach ($questionList as $q) {
                $typeMap = [1 => '单选题', 2 => '多选题', 3 => '判断题', 4 => '填空题', 5 => '简答题'];
                $diffMap = [1 => '简单', 2 => '中等', 3 => '困难'];
                $q['type_text'] = $typeMap[$q['type']] ?? '未知';
                $q['difficulty_text'] = $diffMap[$q['difficulty']] ?? '未知';
                $questionMap[$q['id']] = $q;
            }

            foreach ($paperQuestions as $pq) {
                if (isset($questionMap[$pq['question_id']])) {
                    $q = $questionMap[$pq['question_id']];
                    $q['sort'] = $pq['sort'];
                    $q['paper_score'] = $pq['score'];
                    $questions[] = $q;
                }
            }
        }

        return $this->success($questions, '获取成功');
    }
}
