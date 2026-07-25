<?php

declare(strict_types=1);

namespace app\controller\admin\v1;

use app\BaseController;
use think\facade\Db;
use think\response\Json;

/**
 * 科目管理控制器
 */
class Subject extends BaseController
{
    /**
     * 科目列表
     * @return Json
     */
    public function list(): Json
    {
        $page = $this->request->param('page', 1);
        $pageSize = $this->request->param('page_size', 10);
        $keyword = $this->request->param('keyword', '');
        $status = $this->request->param('status', '');

        $where = [];
        if ($keyword) {
            $where[] = ['name', 'like', '%' . $keyword . '%'];
        }
        if ($status !== '') {
            $where[] = ['status', '=', (int)$status];
        }

        $query = Db::name('subjects')->where($where);
        $total = $query->count();
        $list = $query->field('id,name,description,icon,sort,status,create_time')
            ->order('sort asc, id desc')
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
     * 新增科目
     * @return Json
     */
    public function create(): Json
    {
        $name = $this->request->param('name', '');
        $description = $this->request->param('description', '');
        $icon = $this->request->param('icon', '');
        $sort = $this->request->param('sort', 0);
        $status = $this->request->param('status', 1);

        if (empty($name)) {
            return $this->error('科目名称不能为空');
        }

        $exists = Db::name('subjects')->where('name', $name)->find();
        if ($exists) {
            return $this->error('科目名称已存在');
        }

        $data = [
            'name' => $name,
            'description' => $description,
            'icon' => $icon,
            'sort' => (int)$sort,
            'status' => (int)$status,
            'create_time' => time(),
            'update_time' => time(),
        ];

        $result = Db::name('subjects')->insert($data);

        if ($result) {
            return $this->success([], '新增成功');
        }

        return $this->error('新增失败');
    }

    /**
     * 编辑科目
     * @param int $id
     * @return Json
     */
    public function update(int $id): Json
    {
        $subject = Db::name('subjects')->where('id', $id)->find();
        if (!$subject) {
            return $this->error('科目不存在');
        }

        $name = $this->request->param('name', '');
        $description = $this->request->param('description', '');
        $icon = $this->request->param('icon', '');
        $sort = $this->request->param('sort', 0);
        $status = $this->request->param('status', '');

        if (empty($name)) {
            return $this->error('科目名称不能为空');
        }

        $exists = Db::name('subjects')->where('name', $name)->where('id', '<>', $id)->find();
        if ($exists) {
            return $this->error('科目名称已存在');
        }

        $data = [
            'name' => $name,
            'description' => $description,
            'icon' => $icon,
            'sort' => (int)$sort,
            'update_time' => time(),
        ];

        if ($status !== '') {
            $data['status'] = (int)$status;
        }

        $result = Db::name('subjects')->where('id', $id)->update($data);

        if ($result !== false) {
            return $this->success([], '编辑成功');
        }

        return $this->error('编辑失败');
    }

    /**
     * 删除科目
     * @param int $id
     * @return Json
     */
    public function delete(int $id): Json
    {
        $subject = Db::name('subjects')->where('id', $id)->find();
        if (!$subject) {
            return $this->error('科目不存在');
        }

        $questionCount = Db::name('questions')->where('subject_id', $id)->count();
        if ($questionCount > 0) {
            return $this->error('该科目下存在题目，无法删除');
        }

        $result = Db::name('subjects')->where('id', $id)->delete();

        if ($result) {
            return $this->success([], '删除成功');
        }

        return $this->error('删除失败');
    }

    /**
     * 科目详情
     * @param int $id
     * @return Json
     */
    public function detail(int $id): Json
    {
        $subject = Db::name('subjects')->where('id', $id)->find();

        if (!$subject) {
            return $this->error('科目不存在');
        }

        return $this->success($subject, '获取成功');
    }

    /**
     * 全部科目（下拉选择用）
     * @return Json
     */
    public function all(): Json
    {
        $list = Db::name('subjects')
            ->where('status', 1)
            ->field('id,name')
            ->order('sort asc, id desc')
            ->select()
            ->toArray();

        return $this->success($list, '获取成功');
    }
}
