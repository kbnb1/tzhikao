<?php

declare(strict_types=1);

namespace app\service;

use app\model\Achievement;
use app\model\UserAchievement;
use think\facade\Db;

/**
 * 成就服务类
 */
class AchievementService
{
    private Achievement $achievementModel;
    private UserAchievement $userAchievementModel;

    public function __construct()
    {
        $this->achievementModel = new Achievement();
        $this->userAchievementModel = new UserAchievement();
    }

    /**
     * 获取全部成就列表（含是否已解锁状态）
     * @param int $userId 用户ID
     * @param int $page 页码
     * @param int $pageSize 每页数量
     * @param string $type 类型筛选
     * @return array
     */
    public function getList(int $userId, int $page = 1, int $pageSize = 10, string $type = ''): array
    {
        $where = [['status', '=', 1]];
        if (!empty($type)) {
            $where[] = ['type', '=', $type];
        }

        $query = $this->achievementModel->where($where);
        $total = $query->count();
        $list = $query->order('sort asc, id desc')
            ->page($page, $pageSize)
            ->select()
            ->toArray();

        if (!empty($list)) {
            $achievementIds = array_column($list, 'id');
            $unlockedIds = $this->userAchievementModel
                ->where('user_id', $userId)
                ->whereIn('achievement_id', $achievementIds)
                ->column('achievement_id');

            foreach ($list as &$item) {
                $item['is_unlocked'] = in_array($item['id'], $unlockedIds) ? 1 : 0;
                unset($item['condition_type'], $item['condition_value'], $item['reward_type'], $item['reward_value']);
            }
        }

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
     * 获取我的成就（已解锁的）
     * @param int $userId 用户ID
     * @param int $page 页码
     * @param int $pageSize 每页数量
     * @return array
     */
    public function getMyAchievements(int $userId, int $page = 1, int $pageSize = 10): array
    {
        $query = $this->userAchievementModel
            ->alias('ua')
            ->join('achievements a', 'ua.achievement_id = a.id')
            ->where('ua.user_id', $userId)
            ->where('a.status', 1);

        $total = $query->count();
        $list = $query->field('a.id,a.name,a.description,a.icon,a.type,ua.obtain_time')
            ->order('ua.obtain_time desc, ua.id desc')
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
     * 成就详情
     * @param int $userId 用户ID
     * @param int $id 成就ID
     * @return array
     */
    public function getDetail(int $userId, int $id): array
    {
        $achievement = $this->achievementModel->where('id', $id)->where('status', 1)->find();
        if (!$achievement) {
            return ['code' => 1, 'msg' => '成就不存在', 'data' => []];
        }

        $data = $achievement->toArray();

        $userAchievement = $this->userAchievementModel
            ->where('user_id', $userId)
            ->where('achievement_id', $id)
            ->find();

        $data['is_unlocked'] = $userAchievement ? 1 : 0;
        $data['obtain_time'] = $userAchievement ? $userAchievement['obtain_time'] : null;

        return ['code' => 0, 'msg' => '获取成功', 'data' => $data];
    }

    /**
     * 解锁成就（根据条件自动判断）
     * @param int $userId 用户ID
     * @param string $conditionType 条件类型
     * @param int $currentValue 当前值
     * @return array 解锁的成就列表
     */
    public function unlockAchievement(int $userId, string $conditionType, int $currentValue): array
    {
        $unlockedIds = $this->userAchievementModel
            ->where('user_id', $userId)
            ->column('achievement_id');

        $achievements = $this->achievementModel
            ->where('status', 1)
            ->where('condition_type', $conditionType)
            ->whereNotIn('id', $unlockedIds)
            ->select()
            ->toArray();

        $newlyUnlocked = [];
        foreach ($achievements as $achievement) {
            if ($currentValue >= $achievement['condition_value']) {
                Db::startTrans();
                try {
                    $this->userAchievementModel->create([
                        'user_id'        => $userId,
                        'achievement_id' => $achievement['id'],
                        'obtain_time'    => time(),
                    ]);

                    Db::commit();
                    $newlyUnlocked[] = $achievement;
                } catch (\Exception $e) {
                    Db::rollback();
                }
            }
        }

        return [
            'code' => 0,
            'msg'  => '检查完成',
            'data' => [
                'newly_unlocked' => $newlyUnlocked,
                'count'          => count($newlyUnlocked),
            ],
        ];
    }
}
