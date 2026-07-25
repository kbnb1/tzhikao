<?php

declare(strict_types=1);

namespace app\controller\admin\v1;

use app\BaseController;
use think\facade\Db;
use think\response\Json;

/**
 * 成就管理控制器
 */
class Achievement extends BaseController
{
    /**
     * 成就列表
     * @return Json
     */
    public function list(): Json
    {
        $page = $this->request->param('page', 1);
        $pageSize = $this->request->param('page_size', 10);
        $keyword = $this->request->param('keyword', '');
        $status = $this->request->param('status', '');
        $type = $this->request->param('type', '');

        $where = [];
        if ($keyword) {
            $where[] = ['name|description', 'like', '%' . $keyword . '%'];
        }
        if ($status !== '') {
            $where[] = ['status', '=', (int)$status];
        }
        if ($type !== '') {
            $where[] = ['type', '=', $type];
        }

        $query = Db::name('achievements')->where($where);
        $total = $query->count();
        $list = $query->field('id,name,description,icon,type,condition_type,condition_value,reward_type,reward_value,sort,status,create_time')
            ->order('sort asc, id desc')
            ->page($page, $pageSize)
            ->select()
            ->toArray();

        $typeMap = [
            'study' => '学习类',
            'exam' => '考试类',
            'social' => '社交类',
            'special' => '特殊类',
        ];
        foreach ($list as &$item) {
            $item['type_text'] = $typeMap[$item['type']] ?? '未知';
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
     * 新增成就
     * @return Json
     */
    public function create(): Json
    {
        $name = $this->request->param('name', '');
        $description = $this->request->param('description', '');
        $icon = $this->request->param('icon', '');
        $type = $this->request->param('type', 'study');
        $conditionType = $this->request->param('condition_type', '');
        $conditionValue = $this->request->param('condition_value', 0);
        $rewardType = $this->request->param('reward_type', '');
        $rewardValue = $this->request->param('reward_value', 0);
        $sort = $this->request->param('sort', 0);
        $status = $this->request->param('status', 1);

        if (empty($name)) {
            return $this->error('成就名称不能为空');
        }
        if (empty($description)) {
            return $this->error('成就描述不能为空');
        }

        $exists = Db::name('achievements')->where('name', $name)->find();
        if ($exists) {
            return $this->error('成就名称已存在');
        }

        $data = [
            'name' => $name,
            'description' => $description,
            'icon' => $icon,
            'type' => $type,
            'condition_type' => $conditionType,
            'condition_value' => (int)$conditionValue,
            'reward_type' => $rewardType,
            'reward_value' => (int)$rewardValue,
            'sort' => (int)$sort,
            'status' => (int)$status,
            'create_time' => time(),
            'update_time' => time(),
        ];

        $result = Db::name('achievements')->insert($data);

        if ($result) {
            return $this->success([], '新增成功');
        }

        return $this->error('新增失败');
    }

    /**
     * 编辑成就
     * @param int $id
     * @return Json
     */
    public function update(int $id): Json
    {
        $achievement = Db::name('achievements')->where('id', $id)->find();
        if (!$achievement) {
            return $this->error('成就不存在');
        }

        $name = $this->request->param('name', '');
        $description = $this->request->param('description', '');
        $icon = $this->request->param('icon', '');
        $type = $this->request->param('type', '');
        $conditionType = $this->request->param('condition_type', '');
        $conditionValue = $this->request->param('condition_value', '');
        $rewardType = $this->request->param('reward_type', '');
        $rewardValue = $this->request->param('reward_value', '');
        $sort = $this->request->param('sort', '');
        $status = $this->request->param('status', '');

        if (empty($name)) {
            return $this->error('成就名称不能为空');
        }
        if (empty($description)) {
            return $this->error('成就描述不能为空');
        }

        $exists = Db::name('achievements')->where('name', $name)->where('id', '<>', $id)->find();
        if ($exists) {
            return $this->error('成就名称已存在');
        }

        $data = [
            'name' => $name,
            'description' => $description,
            'icon' => $icon,
            'update_time' => time(),
        ];

        if ($type !== '') {
            $data['type'] = $type;
        }
        if ($conditionType !== '') {
            $data['condition_type'] = $conditionType;
        }
        if ($conditionValue !== '') {
            $data['condition_value'] = (int)$conditionValue;
        }
        if ($rewardType !== '') {
            $data['reward_type'] = $rewardType;
        }
        if ($rewardValue !== '') {
            $data['reward_value'] = (int)$rewardValue;
        }
        if ($sort !== '') {
            $data['sort'] = (int)$sort;
        }
        if ($status !== '') {
            $data['status'] = (int)$status;
        }

        $result = Db::name('achievements')->where('id', $id)->update($data);

        if ($result !== false) {
            return $this->success([], '编辑成功');
        }

        return $this->error('编辑失败');
    }

    /**
     * 删除成就
     * @param int $id
     * @return Json
     */
    public function delete(int $id): Json
    {
        $achievement = Db::name('achievements')->where('id', $id)->find();
        if (!$achievement) {
            return $this->error('成就不存在');
        }

        $hasUser = Db::name('user_achievements')->where('achievement_id', $id)->count();
        if ($hasUser > 0) {
            return $this->error('已有用户获得该成就，无法删除');
        }

        $result = Db::name('achievements')->where('id', $id)->delete();

        if ($result) {
            return $this->success([], '删除成功');
        }

        return $this->error('删除失败');
    }

    /**
     * 成就详情
     * @param int $id
     * @return Json
     */
    public function detail(int $id): Json
    {
        $achievement = Db::name('achievements')->where('id', $id)->find();

        if (!$achievement) {
            return $this->error('成就不存在');
        }

        return $this->success($achievement, '获取成功');
    }

    /**
     * 用户获得成就记录
     * @param int $id
     * @return Json
     */
    public function userList(int $id): Json
    {
        $page = $this->request->param('page', 1);
        $pageSize = $this->request->param('page_size', 10);

        $achievement = Db::name('achievements')->where('id', $id)->find();
        if (!$achievement) {
            return $this->error('成就不存在');
        }

        $query = Db::name('user_achievements')->where('achievement_id', $id);
        $total = $query->count();
        $list = $query->field('id,user_id,obtain_time')
            ->order('id desc')
            ->page($page, $pageSize)
            ->select()
            ->toArray();

        $userIds = array_column($list, 'user_id');
        if (!empty($userIds)) {
            $users = Db::name('users')->whereIn('id', array_unique($userIds))->column('nickname,avatar', 'id');
            foreach ($list as &$item) {
                $item['user_nickname'] = $users[$item['user_id']]['nickname'] ?? '';
                $item['user_avatar'] = $users[$item['user_id']]['avatar'] ?? '';
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
     * 全部成就（下拉选择用）
     * @return Json
     */
    public function all(): Json
    {
        $list = Db::name('achievements')
            ->where('status', 1)
            ->field('id,name,icon,type')
            ->order('sort asc, id desc')
            ->select()
            ->toArray();

        return $this->success($list, '获取成功');
    }
}
