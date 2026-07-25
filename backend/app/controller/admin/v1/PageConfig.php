<?php

declare(strict_types=1);

namespace app\controller\admin\v1;

use app\BaseController;
use think\facade\Db;
use think\response\Json;

/**
 * 页面配置控制器
 */
class PageConfig extends BaseController
{
    /**
     * 页面配置列表
     * @return Json
     */
    public function list(): Json
    {
        $page = $this->request->param('page', 1);
        $pageSize = $this->request->param('page_size', 10);
        $keyword = $this->request->param('keyword', '');
        $status = $this->request->param('status', '');
        $pageKey = $this->request->param('page_key', '');

        $where = [];
        if ($keyword) {
            $where[] = ['title|page_key', 'like', '%' . $keyword . '%'];
        }
        if ($status !== '') {
            $where[] = ['status', '=', (int)$status];
        }
        if ($pageKey) {
            $where[] = ['page_key', '=', $pageKey];
        }

        $query = Db::name('page_config')->where($where);
        $total = $query->count();
        $list = $query->field('id,page_key,title,description,status,sort,create_time')
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
     * 新增页面配置
     * @return Json
     */
    public function create(): Json
    {
        $pageKey = $this->request->param('page_key', '');
        $title = $this->request->param('title', '');
        $description = $this->request->param('description', '');
        $content = $this->request->param('content', '');
        $sort = $this->request->param('sort', 0);
        $status = $this->request->param('status', 1);

        if (empty($pageKey)) {
            return $this->error('页面标识不能为空');
        }
        if (empty($title)) {
            return $this->error('标题不能为空');
        }

        $exists = Db::name('page_config')->where('page_key', $pageKey)->find();
        if ($exists) {
            return $this->error('页面标识已存在');
        }

        $data = [
            'page_key' => $pageKey,
            'title' => $title,
            'description' => $description,
            'content' => $content,
            'sort' => (int)$sort,
            'status' => (int)$status,
            'create_time' => time(),
            'update_time' => time(),
        ];

        $result = Db::name('page_config')->insert($data);

        if ($result) {
            return $this->success([], '新增成功');
        }

        return $this->error('新增失败');
    }

    /**
     * 编辑页面配置
     * @param int $id
     * @return Json
     */
    public function update(int $id): Json
    {
        $config = Db::name('page_config')->where('id', $id)->find();
        if (!$config) {
            return $this->error('配置不存在');
        }

        $pageKey = $this->request->param('page_key', '');
        $title = $this->request->param('title', '');
        $description = $this->request->param('description', '');
        $content = $this->request->param('content', '');
        $sort = $this->request->param('sort', '');
        $status = $this->request->param('status', '');

        if (empty($pageKey)) {
            return $this->error('页面标识不能为空');
        }
        if (empty($title)) {
            return $this->error('标题不能为空');
        }

        $exists = Db::name('page_config')->where('page_key', $pageKey)->where('id', '<>', $id)->find();
        if ($exists) {
            return $this->error('页面标识已存在');
        }

        $data = [
            'page_key' => $pageKey,
            'title' => $title,
            'description' => $description,
            'content' => $content,
            'update_time' => time(),
        ];

        if ($sort !== '') {
            $data['sort'] = (int)$sort;
        }
        if ($status !== '') {
            $data['status'] = (int)$status;
        }

        $result = Db::name('page_config')->where('id', $id)->update($data);

        if ($result !== false) {
            return $this->success([], '编辑成功');
        }

        return $this->error('编辑失败');
    }

    /**
     * 删除页面配置
     * @param int $id
     * @return Json
     */
    public function delete(int $id): Json
    {
        $config = Db::name('page_config')->where('id', $id)->find();
        if (!$config) {
            return $this->error('配置不存在');
        }

        $result = Db::name('page_config')->where('id', $id)->delete();

        if ($result) {
            return $this->success([], '删除成功');
        }

        return $this->error('删除失败');
    }

    /**
     * 页面配置详情
     * @param int $id
     * @return Json
     */
    public function detail(int $id): Json
    {
        $config = Db::name('page_config')->where('id', $id)->find();

        if (!$config) {
            return $this->error('配置不存在');
        }

        return $this->success($config, '获取成功');
    }

    /**
     * 根据page_key获取配置
     * @param string $key
     * @return Json
     */
    public function getByKey(string $key): Json
    {
        $config = Db::name('page_config')->where('page_key', $key)->find();

        if (!$config) {
            return $this->error('配置不存在');
        }

        return $this->success($config, '获取成功');
    }
}
