<?php

declare(strict_types=1);

namespace app\service;

use app\model\Reminder;

/**
 * 提醒服务类
 */
class ReminderService
{
    private Reminder $reminderModel;

    public function __construct()
    {
        $this->reminderModel = new Reminder();
    }

    /**
     * 获取提醒列表
     * @param int $userId 用户ID
     * @param int $page 页码
     * @param int $pageSize 每页数量
     * @param int $status 状态筛选
     * @return array
     */
    public function getList(int $userId, int $page = 1, int $pageSize = 10, int $status = -1): array
    {
        $where = [['user_id', '=', $userId]];
        if ($status >= 0) {
            $where[] = ['status', '=', $status];
        }

        $query = $this->reminderModel->where($where);
        $total = $query->count();
        $list = $query->order('reminder_time asc, id desc')
            ->page($page, $pageSize)
            ->select()
            ->toArray();

        return [
            'code' => 0,
            'msg'  => '获取成功',
            'data' => [
                'list'       => $list,
                'total'      => $total,
                'page'       => $page,
                'page_size'  => $pageSize,
                'total_page' => ceil($total / $pageSize),
            ],
        ];
    }

    /**
     * 添加提醒
     * @param int $userId 用户ID
     * @param array $data 提醒数据
     * @return array
     */
    public function add(int $userId, array $data): array
    {
        if (empty($data['title'])) {
            return ['code' => 1, 'msg' => '提醒标题不能为空', 'data' => []];
        }
        if (empty($data['reminder_time'])) {
            return ['code' => 1, 'msg' => '提醒时间不能为空', 'data' => []];
        }

        $repeatType = $data['repeat_type'] ?? 'none';
        if (!in_array($repeatType, ['none', 'daily', 'weekly', 'monthly'])) {
            return ['code' => 1, 'msg' => '重复类型不正确', 'data' => []];
        }

        $reminder = $this->reminderModel->create([
            'user_id'       => $userId,
            'title'         => $data['title'],
            'content'       => $data['content'] ?? '',
            'reminder_time' => $data['reminder_time'],
            'repeat_type'   => $repeatType,
            'status'        => $data['status'] ?? 1,
        ]);

        if ($reminder) {
            return ['code' => 0, 'msg' => '添加成功', 'data' => $reminder->toArray()];
        }

        return ['code' => 1, 'msg' => '添加失败', 'data' => []];
    }

    /**
     * 编辑提醒
     * @param int $userId 用户ID
     * @param int $id 提醒ID
     * @param array $data 更新数据
     * @return array
     */
    public function edit(int $userId, int $id, array $data): array
    {
        $reminder = $this->reminderModel->where('id', $id)->where('user_id', $userId)->find();
        if (!$reminder) {
            return ['code' => 1, 'msg' => '提醒不存在', 'data' => []];
        }

        $updateData = [];
        if (isset($data['title'])) {
            if (empty($data['title'])) {
                return ['code' => 1, 'msg' => '提醒标题不能为空', 'data' => []];
            }
            $updateData['title'] = $data['title'];
        }
        if (isset($data['content'])) {
            $updateData['content'] = $data['content'];
        }
        if (isset($data['reminder_time'])) {
            if (empty($data['reminder_time'])) {
                return ['code' => 1, 'msg' => '提醒时间不能为空', 'data' => []];
            }
            $updateData['reminder_time'] = $data['reminder_time'];
        }
        if (isset($data['repeat_type'])) {
            if (!in_array($data['repeat_type'], ['none', 'daily', 'weekly', 'monthly'])) {
                return ['code' => 1, 'msg' => '重复类型不正确', 'data' => []];
            }
            $updateData['repeat_type'] = $data['repeat_type'];
        }

        if (!empty($updateData)) {
            $reminder->save($updateData);
        }

        return ['code' => 0, 'msg' => '更新成功', 'data' => $reminder->toArray()];
    }

    /**
     * 删除提醒
     * @param int $userId 用户ID
     * @param int $id 提醒ID
     * @return array
     */
    public function delete(int $userId, int $id): array
    {
        $reminder = $this->reminderModel->where('id', $id)->where('user_id', $userId)->find();
        if (!$reminder) {
            return ['code' => 1, 'msg' => '提醒不存在', 'data' => []];
        }

        $result = $reminder->delete();
        if ($result) {
            return ['code' => 0, 'msg' => '删除成功', 'data' => []];
        }

        return ['code' => 1, 'msg' => '删除失败', 'data' => []];
    }

    /**
     * 开关提醒
     * @param int $userId 用户ID
     * @param int $id 提醒ID
     * @param int $status 状态（0关闭，1开启）
     * @return array
     */
    public function toggle(int $userId, int $id, int $status): array
    {
        if (!in_array($status, [0, 1])) {
            return ['code' => 1, 'msg' => '状态值不正确', 'data' => []];
        }

        $reminder = $this->reminderModel->where('id', $id)->where('user_id', $userId)->find();
        if (!$reminder) {
            return ['code' => 1, 'msg' => '提醒不存在', 'data' => []];
        }

        $reminder->status = $status;
        $reminder->save();

        $msg = $status == 1 ? '提醒已开启' : '提醒已关闭';
        return ['code' => 0, 'msg' => $msg, 'data' => $reminder->toArray()];
    }
}
