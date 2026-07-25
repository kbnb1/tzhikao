<?php

declare(strict_types=1);

namespace app\controller\admin\v1;

use app\BaseController;
use think\facade\Db;
use think\response\Json;

/**
 * 题目管理控制器
 */
class Question extends BaseController
{
    /**
     * 题目列表
     * @return Json
     */
    public function list(): Json
    {
        $page = $this->request->param('page', 1);
        $pageSize = $this->request->param('page_size', 10);
        $keyword = $this->request->param('keyword', '');
        $subjectId = $this->request->param('subject_id', '');
        $type = $this->request->param('type', '');
        $difficulty = $this->request->param('difficulty', '');
        $status = $this->request->param('status', '');

        $where = [];
        if ($keyword) {
            $where[] = ['title', 'like', '%' . $keyword . '%'];
        }
        if ($subjectId !== '') {
            $where[] = ['subject_id', '=', (int)$subjectId];
        }
        if ($type !== '') {
            $where[] = ['type', '=', (int)$type];
        }
        if ($difficulty !== '') {
            $where[] = ['difficulty', '=', (int)$difficulty];
        }
        if ($status !== '') {
            $where[] = ['status', '=', (int)$status];
        }

        $query = Db::name('questions')->where($where);
        $total = $query->count();
        $list = $query->field('id,subject_id,title,type,difficulty,score,status,create_time')
            ->withAttr('type_text', function ($value, $data) {
                $typeMap = [1 => '单选题', 2 => '多选题', 3 => '判断题', 4 => '填空题', 5 => '简答题'];
                return $typeMap[$data['type']] ?? '未知';
            })
            ->withAttr('difficulty_text', function ($value, $data) {
                $diffMap = [1 => '简单', 2 => '中等', 3 => '困难'];
                return $diffMap[$data['difficulty']] ?? '未知';
            })
            ->order('id desc')
            ->page($page, $pageSize)
            ->select()
            ->toArray();

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
     * 新增题目
     * @return Json
     */
    public function create(): Json
    {
        $subjectId = $this->request->param('subject_id', 0);
        $title = $this->request->param('title', '');
        $type = $this->request->param('type', 1);
        $difficulty = $this->request->param('difficulty', 1);
        $score = $this->request->param('score', 0);
        $options = $this->request->param('options', '');
        $answer = $this->request->param('answer', '');
        $analysis = $this->request->param('analysis', '');
        $status = $this->request->param('status', 1);

        if (empty($subjectId)) {
            return $this->error('请选择科目');
        }
        if (empty($title)) {
            return $this->error('题目标题不能为空');
        }

        $subject = Db::name('subjects')->where('id', $subjectId)->find();
        if (!$subject) {
            return $this->error('科目不存在');
        }

        $data = [
            'subject_id' => (int)$subjectId,
            'title' => $title,
            'type' => (int)$type,
            'difficulty' => (int)$difficulty,
            'score' => (float)$score,
            'options' => is_array($options) ? json_encode($options, JSON_UNESCAPED_UNICODE) : $options,
            'answer' => $answer,
            'analysis' => $analysis,
            'status' => (int)$status,
            'create_time' => time(),
            'update_time' => time(),
        ];

        $result = Db::name('questions')->insert($data);

        if ($result) {
            return $this->success([], '新增成功');
        }

        return $this->error('新增失败');
    }

    /**
     * 编辑题目
     * @param int $id
     * @return Json
     */
    public function update(int $id): Json
    {
        $question = Db::name('questions')->where('id', $id)->find();
        if (!$question) {
            return $this->error('题目不存在');
        }

        $subjectId = $this->request->param('subject_id', 0);
        $title = $this->request->param('title', '');
        $type = $this->request->param('type', '');
        $difficulty = $this->request->param('difficulty', '');
        $score = $this->request->param('score', '');
        $options = $this->request->param('options', '');
        $answer = $this->request->param('answer', '');
        $analysis = $this->request->param('analysis', '');
        $status = $this->request->param('status', '');

        if (empty($subjectId)) {
            return $this->error('请选择科目');
        }
        if (empty($title)) {
            return $this->error('题目标题不能为空');
        }

        $data = [
            'subject_id' => (int)$subjectId,
            'title' => $title,
            'update_time' => time(),
        ];

        if ($type !== '') {
            $data['type'] = (int)$type;
        }
        if ($difficulty !== '') {
            $data['difficulty'] = (int)$difficulty;
        }
        if ($score !== '') {
            $data['score'] = (float)$score;
        }
        if ($options !== '') {
            $data['options'] = is_array($options) ? json_encode($options, JSON_UNESCAPED_UNICODE) : $options;
        }
        if ($answer !== '') {
            $data['answer'] = $answer;
        }
        if ($analysis !== '') {
            $data['analysis'] = $analysis;
        }
        if ($status !== '') {
            $data['status'] = (int)$status;
        }

        $result = Db::name('questions')->where('id', $id)->update($data);

        if ($result !== false) {
            return $this->success([], '编辑成功');
        }

        return $this->error('编辑失败');
    }

    /**
     * 删除题目
     * @param int $id
     * @return Json
     */
    public function delete(int $id): Json
    {
        $question = Db::name('questions')->where('id', $id)->find();
        if (!$question) {
            return $this->error('题目不存在');
        }

        $result = Db::name('questions')->where('id', $id)->delete();

        if ($result) {
            return $this->success([], '删除成功');
        }

        return $this->error('删除失败');
    }

    /**
     * 批量删除题目
     * @return Json
     */
    public function batchDelete(): Json
    {
        $ids = $this->request->param('ids', '');
        if (empty($ids)) {
            return $this->error('请选择要删除的题目');
        }

        $idArray = is_array($ids) ? $ids : explode(',', $ids);
        $idArray = array_filter(array_map('intval', $idArray));

        if (empty($idArray)) {
            return $this->error('ID格式错误');
        }

        $result = Db::name('questions')->whereIn('id', $idArray)->delete();

        if ($result) {
            return $this->success(['count' => $result], '批量删除成功');
        }

        return $this->error('批量删除失败');
    }

    /**
     * 题目详情
     * @param int $id
     * @return Json
     */
    public function detail(int $id): Json
    {
        $question = Db::name('questions')->where('id', $id)->find();

        if (!$question) {
            return $this->error('题目不存在');
        }

        if (!empty($question['options'])) {
            $question['options'] = json_decode($question['options'], true);
        }

        return $this->success($question, '获取成功');
    }

    /**
     * 批量导入题目
     * @return Json
     */
    public function batchImport(): Json
    {
        $subjectId = $this->request->param('subject_id', 0);
        $questions = $this->request->param('questions', []);

        if (empty($subjectId)) {
            return $this->error('请选择科目');
        }
        if (empty($questions) || !is_array($questions)) {
            return $this->error('请提供题目数据');
        }

        $subject = Db::name('subjects')->where('id', $subjectId)->find();
        if (!$subject) {
            return $this->error('科目不存在');
        }

        $successCount = 0;
        $failCount = 0;
        $insertData = [];
        $now = time();

        foreach ($questions as $q) {
            $title = $q['title'] ?? '';
            if (empty($title)) {
                $failCount++;
                continue;
            }

            $insertData[] = [
                'subject_id' => (int)$subjectId,
                'title' => $title,
                'type' => isset($q['type']) ? (int)$q['type'] : 1,
                'difficulty' => isset($q['difficulty']) ? (int)$q['difficulty'] : 1,
                'score' => isset($q['score']) ? (float)$q['score'] : 0,
                'options' => isset($q['options']) && is_array($q['options']) ? json_encode($q['options'], JSON_UNESCAPED_UNICODE) : '',
                'answer' => $q['answer'] ?? '',
                'analysis' => $q['analysis'] ?? '',
                'status' => isset($q['status']) ? (int)$q['status'] : 1,
                'create_time' => $now,
                'update_time' => $now,
            ];

            $successCount++;
        }

        if (!empty($insertData)) {
            Db::name('questions')->insertAll($insertData);
        }

        return $this->success([
            'success_count' => $successCount,
            'fail_count' => $failCount,
        ], '导入完成');
    }
}
