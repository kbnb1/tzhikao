<?php

declare(strict_types=1);

namespace app\controller\admin\v1;

use app\BaseController;
use think\facade\Db;
use think\response\Json;

/**
 * 用户管理控制器
 */
class User extends BaseController
{
    /**
     * 用户列表
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
            $where[] = ['username|nickname|mobile', 'like', '%' . $keyword . '%'];
        }
        if ($status !== '') {
            $where[] = ['status', '=', (int)$status];
        }

        $query = Db::name('users')->where($where);
        $total = $query->count();
        $list = $query->field('id,username,nickname,avatar,mobile,email,status,create_time')
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
     * 新增用户
     * @return Json
     */
    public function create(): Json
    {
        $username = $this->request->param('username', '');
        $nickname = $this->request->param('nickname', '');
        $password = $this->request->param('password', '');
        $mobile = $this->request->param('mobile', '');
        $email = $this->request->param('email', '');
        $status = $this->request->param('status', 1);

        if (empty($username)) {
            return $this->error('用户名不能为空');
        }
        if (empty($password)) {
            return $this->error('密码不能为空');
        }

        $exists = Db::name('users')->where('username', $username)->find();
        if ($exists) {
            return $this->error('用户名已存在');
        }

        $data = [
            'username' => $username,
            'nickname' => $nickname,
            'password' => password_hash($password, PASSWORD_DEFAULT),
            'mobile' => $mobile,
            'email' => $email,
            'status' => (int)$status,
            'create_time' => time(),
            'update_time' => time(),
        ];

        $result = Db::name('users')->insert($data);

        if ($result) {
            return $this->success([], '新增成功');
        }

        return $this->error('新增失败');
    }

    /**
     * 编辑用户
     * @param int $id
     * @return Json
     */
    public function update(int $id): Json
    {
        $user = Db::name('users')->where('id', $id)->find();
        if (!$user) {
            return $this->error('用户不存在');
        }

        $nickname = $this->request->param('nickname', '');
        $mobile = $this->request->param('mobile', '');
        $email = $this->request->param('email', '');
        $status = $this->request->param('status', '');
        $password = $this->request->param('password', '');

        $data = [
            'nickname' => $nickname,
            'mobile' => $mobile,
            'email' => $email,
            'update_time' => time(),
        ];

        if ($status !== '') {
            $data['status'] = (int)$status;
        }

        if ($password) {
            $data['password'] = password_hash($password, PASSWORD_DEFAULT);
        }

        $result = Db::name('users')->where('id', $id)->update($data);

        if ($result !== false) {
            return $this->success([], '编辑成功');
        }

        return $this->error('编辑失败');
    }

    /**
     * 删除用户
     * @param int $id
     * @return Json
     */
    public function delete(int $id): Json
    {
        $user = Db::name('users')->where('id', $id)->find();
        if (!$user) {
            return $this->error('用户不存在');
        }

        $result = Db::name('users')->where('id', $id)->delete();

        if ($result) {
            return $this->success([], '删除成功');
        }

        return $this->error('删除失败');
    }

    /**
     * 禁用/启用用户
     * @param int $id
     * @return Json
     */
    public function toggleStatus(int $id): Json
    {
        $user = Db::name('users')->where('id', $id)->find();
        if (!$user) {
            return $this->error('用户不存在');
        }

        $newStatus = $user['status'] == 1 ? 0 : 1;
        $result = Db::name('users')->where('id', $id)->update([
            'status' => $newStatus,
            'update_time' => time(),
        ]);

        if ($result !== false) {
            $msg = $newStatus == 1 ? '启用成功' : '禁用成功';
            return $this->success(['status' => $newStatus], $msg);
        }

        return $this->error('操作失败');
    }

    /**
     * 用户详情
     * @param int $id
     * @return Json
     */
    public function detail(int $id): Json
    {
        $user = Db::name('users')->where('id', $id)->field('id,username,nickname,avatar,mobile,email,status,create_time,update_time')->find();

        if (!$user) {
            return $this->error('用户不存在');
        }

        return $this->success($user, '获取成功');
    }
}
