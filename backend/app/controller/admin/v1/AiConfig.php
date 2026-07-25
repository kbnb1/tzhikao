<?php

declare(strict_types=1);

namespace app\controller\admin\v1;

use app\BaseController;
use think\facade\Db;
use think\response\Json;

/**
 * AI配置控制器
 */
class AiConfig extends BaseController
{
    /**
     * AI配置列表
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
            $where[] = ['name', 'like', '%' . $keyword . '%'];
        }
        if ($status !== '') {
            $where[] = ['status', '=', (int)$status];
        }
        if ($type !== '') {
            $where[] = ['type', '=', $type];
        }

        $query = Db::name('ai_configs')->where($where);
        $total = $query->count();
        $list = $query->field('id,name,type,model,api_base,api_key,is_default,status,sort,create_time')
            ->order('sort asc, id desc')
            ->page($page, $pageSize)
            ->select()
            ->toArray();

        foreach ($list as &$item) {
            if (!empty($item['api_key'])) {
                $item['api_key'] = substr($item['api_key'], 0, 8) . '****' . substr($item['api_key'], -4);
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
     * 新增AI配置
     * @return Json
     */
    public function create(): Json
    {
        $name = $this->request->param('name', '');
        $type = $this->request->param('type', '');
        $model = $this->request->param('model', '');
        $apiBase = $this->request->param('api_base', '');
        $apiKey = $this->request->param('api_key', '');
        $sort = $this->request->param('sort', 0);
        $status = $this->request->param('status', 1);
        $isDefault = $this->request->param('is_default', 0);

        if (empty($name)) {
            return $this->error('配置名称不能为空');
        }
        if (empty($type)) {
            return $this->error('AI类型不能为空');
        }
        if (empty($model)) {
            return $this->error('模型名称不能为空');
        }
        if (empty($apiKey)) {
            return $this->error('API密钥不能为空');
        }

        Db::startTrans();
        try {
            if ($isDefault == 1) {
                Db::name('ai_configs')->where('type', $type)->update(['is_default' => 0]);
            }

            $data = [
                'name' => $name,
                'type' => $type,
                'model' => $model,
                'api_base' => $apiBase,
                'api_key' => $apiKey,
                'sort' => (int)$sort,
                'status' => (int)$status,
                'is_default' => (int)$isDefault,
                'create_time' => time(),
                'update_time' => time(),
            ];

            $id = Db::name('ai_configs')->insertGetId($data);

            Db::commit();
            return $this->success(['id' => $id], '新增成功');
        } catch (\Exception $e) {
            Db::rollback();
            return $this->error('新增失败: ' . $e->getMessage());
        }
    }

    /**
     * 编辑AI配置
     * @param int $id
     * @return Json
     */
    public function update(int $id): Json
    {
        $config = Db::name('ai_configs')->where('id', $id)->find();
        if (!$config) {
            return $this->error('配置不存在');
        }

        $name = $this->request->param('name', '');
        $type = $this->request->param('type', '');
        $model = $this->request->param('model', '');
        $apiBase = $this->request->param('api_base', '');
        $apiKey = $this->request->param('api_key', '');
        $sort = $this->request->param('sort', '');
        $status = $this->request->param('status', '');
        $isDefault = $this->request->param('is_default', '');

        if (empty($name)) {
            return $this->error('配置名称不能为空');
        }

        Db::startTrans();
        try {
            if ($isDefault !== '' && $isDefault == 1) {
                $updateType = $type ?: $config['type'];
                Db::name('ai_configs')->where('type', $updateType)->where('id', '<>', $id)->update(['is_default' => 0]);
            }

            $data = [
                'name' => $name,
                'update_time' => time(),
            ];

            if ($type !== '') {
                $data['type'] = $type;
            }
            if ($model !== '') {
                $data['model'] = $model;
            }
            if ($apiBase !== '') {
                $data['api_base'] = $apiBase;
            }
            if ($apiKey !== '') {
                $data['api_key'] = $apiKey;
            }
            if ($sort !== '') {
                $data['sort'] = (int)$sort;
            }
            if ($status !== '') {
                $data['status'] = (int)$status;
            }
            if ($isDefault !== '') {
                $data['is_default'] = (int)$isDefault;
            }

            Db::name('ai_configs')->where('id', $id)->update($data);

            Db::commit();
            return $this->success([], '编辑成功');
        } catch (\Exception $e) {
            Db::rollback();
            return $this->error('编辑失败: ' . $e->getMessage());
        }
    }

    /**
     * 删除AI配置
     * @param int $id
     * @return Json
     */
    public function delete(int $id): Json
    {
        $config = Db::name('ai_configs')->where('id', $id)->find();
        if (!$config) {
            return $this->error('配置不存在');
        }

        if ($config['is_default'] == 1) {
            return $this->error('默认配置不能删除');
        }

        $result = Db::name('ai_configs')->where('id', $id)->delete();

        if ($result) {
            return $this->success([], '删除成功');
        }

        return $this->error('删除失败');
    }

    /**
     * 切换默认配置
     * @param int $id
     * @return Json
     */
    public function setDefault(int $id): Json
    {
        $config = Db::name('ai_configs')->where('id', $id)->find();
        if (!$config) {
            return $this->error('配置不存在');
        }

        if ($config['status'] != 1) {
            return $this->error('请先启用该配置');
        }

        Db::startTrans();
        try {
            Db::name('ai_configs')->where('type', $config['type'])->update(['is_default' => 0]);
            Db::name('ai_configs')->where('id', $id)->update(['is_default' => 1, 'update_time' => time()]);

            Db::commit();
            return $this->success([], '设置成功');
        } catch (\Exception $e) {
            Db::rollback();
            return $this->error('设置失败: ' . $e->getMessage());
        }
    }

    /**
     * AI配置详情
     * @param int $id
     * @return Json
     */
    public function detail(int $id): Json
    {
        $config = Db::name('ai_configs')->where('id', $id)->find();

        if (!$config) {
            return $this->error('配置不存在');
        }

        return $this->success($config, '获取成功');
    }

    /**
     * 全部AI配置（下拉选择用）
     * @return Json
     */
    public function all(): Json
    {
        $list = Db::name('ai_configs')
            ->where('status', 1)
            ->field('id,name,type,model,is_default')
            ->order('sort asc, id desc')
            ->select()
            ->toArray();

        return $this->success($list, '获取成功');
    }
}
