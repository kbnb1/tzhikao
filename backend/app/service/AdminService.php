<?php

declare(strict_types=1);

namespace app\service;

use app\model\AdminUser;
use app\utils\Jwt;
use think\facade\Db;

/**
 * 后台服务类
 */
class AdminService
{
    /**
     * 管理员登录
     * @param string $username 用户名
     * @param string $password 密码
     * @return array
     */
    public function login(string $username, string $password): array
    {
        $admin = AdminUser::where('username', $username)->find();

        if (!$admin) {
            return ['code' => 1, 'msg' => '用户名或密码错误', 'data' => []];
        }

        if ($admin['status'] != 1) {
            return ['code' => 1, 'msg' => '账号已被禁用', 'data' => []];
        }

        if (!password_verify($password, $admin['password'])) {
            return ['code' => 1, 'msg' => '用户名或密码错误', 'data' => []];
        }

        $jwt = new Jwt();
        $userInfo = [
            'id' => $admin['id'],
            'username' => $admin['username'],
            'nickname' => $admin['nickname'],
            'is_admin' => 1,
        ];
        $tokens = $jwt->generateTokens($userInfo);

        return [
            'code' => 0,
            'msg' => '登录成功',
            'data' => [
                'admin_info' => [
                    'id' => $admin['id'],
                    'username' => $admin['username'],
                    'nickname' => $admin['nickname'],
                    'avatar' => $admin['avatar'] ?? '',
                ],
                'token' => $tokens['token'],
                'refresh_token' => $tokens['refresh_token'],
                'expires_in' => $tokens['expires_in'],
            ],
        ];
    }

    /**
     * 获取仪表盘统计数据
     * @return array
     */
    public function getDashboardStats(): array
    {
        $today = date('Y-m-d');
        $startOfToday = strtotime($today . ' 00:00:00');
        $startOfMonth = strtotime(date('Y-m-01') . ' 00:00:00');

        $userTotal = Db::name('users')->count();
        $userToday = Db::name('users')->whereTime('create_time', '>=', $startOfToday)->count();
        $userMonth = Db::name('users')->whereTime('create_time', '>=', $startOfMonth)->count();

        $examTotal = Db::name('exam_papers')->count();
        $subjectTotal = Db::name('subjects')->count();
        $questionTotal = Db::name('questions')->count();

        $incomeTotal = 0;
        $incomeToday = 0;
        $incomeMonth = 0;

        $postTotal = Db::name('community_posts')->count();
        $postPending = Db::name('community_posts')->where('status', 0)->count();

        return [
            'code' => 0,
            'msg' => '获取成功',
            'data' => [
                'user_stats' => [
                    'total' => $userTotal,
                    'today' => $userToday,
                    'month' => $userMonth,
                ],
                'exam_stats' => [
                    'total' => $examTotal,
                    'subject_total' => $subjectTotal,
                    'question_total' => $questionTotal,
                ],
                'income_stats' => [
                    'total' => $incomeTotal,
                    'today' => $incomeToday,
                    'month' => $incomeMonth,
                ],
                'community_stats' => [
                    'post_total' => $postTotal,
                    'post_pending' => $postPending,
                ],
            ],
        ];
    }

    /**
     * 分页查询通用方法
     * @param string $table 表名
     * @param array $where 查询条件
     * @param string $order 排序
     * @param int $page 页码
     * @param int $pageSize 每页数量
     * @param array $field 字段
     * @return array
     */
    public function paginate(string $table, array $where = [], string $order = 'id desc', int $page = 1, int $pageSize = 10, array $field = ['*']): array
    {
        $query = Db::name($table)->where($where)->field($field)->order($order);
        $total = $query->count();
        $list = $query->page($page, $pageSize)->select()->toArray();

        return [
            'list' => $list,
            'total' => $total,
            'page' => $page,
            'page_size' => $pageSize,
            'total_page' => ceil($total / $pageSize),
        ];
    }
}
