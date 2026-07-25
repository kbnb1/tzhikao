<?php

declare(strict_types=1);

namespace app\controller\admin\v1;

use app\BaseController;
use think\facade\Db;
use think\response\Json;

/**
 * 社区管理控制器
 */
class Community extends BaseController
{
    /**
     * 帖子列表
     * @return Json
     */
    public function postList(): Json
    {
        $page = $this->request->param('page', 1);
        $pageSize = $this->request->param('page_size', 10);
        $keyword = $this->request->param('keyword', '');
        $status = $this->request->param('status', '');
        $userId = $this->request->param('user_id', '');

        $where = [];
        if ($keyword) {
            $where[] = ['title|content', 'like', '%' . $keyword . '%'];
        }
        if ($status !== '') {
            $where[] = ['status', '=', (int)$status];
        }
        if ($userId !== '') {
            $where[] = ['user_id', '=', (int)$userId];
        }

        $query = Db::name('community_posts')->where($where);
        $total = $query->count();
        $list = $query->field('id,user_id,title,content,images,view_count,like_count,comment_count,status,create_time')
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
                if (!empty($item['images'])) {
                    $item['images'] = json_decode($item['images'], true);
                }
            }
        }

        $statusMap = [0 => '待审核', 1 => '已通过', 2 => '已拒绝'];
        foreach ($list as &$item) {
            $item['status_text'] = $statusMap[$item['status']] ?? '未知';
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
     * 帖子详情
     * @param int $id
     * @return Json
     */
    public function postDetail(int $id): Json
    {
        $post = Db::name('community_posts')->where('id', $id)->find();

        if (!$post) {
            return $this->error('帖子不存在');
        }

        if (!empty($post['images'])) {
            $post['images'] = json_decode($post['images'], true);
        }

        $user = Db::name('users')->where('id', $post['user_id'])->field('id,nickname,avatar')->find();
        $post['user'] = $user;

        return $this->success($post, '获取成功');
    }

    /**
     * 审核帖子
     * @param int $id
     * @return Json
     */
    public function postReview(int $id): Json
    {
        $post = Db::name('community_posts')->where('id', $id)->find();
        if (!$post) {
            return $this->error('帖子不存在');
        }

        $status = $this->request->param('status', 1);
        $remark = $this->request->param('remark', '');

        if (!in_array($status, [1, 2])) {
            return $this->error('状态值错误');
        }

        $data = [
            'status' => (int)$status,
            'review_remark' => $remark,
            'review_time' => time(),
            'update_time' => time(),
        ];

        $result = Db::name('community_posts')->where('id', $id)->update($data);

        if ($result !== false) {
            $msg = $status == 1 ? '审核通过' : '审核拒绝';
            return $this->success([], $msg);
        }

        return $this->error('操作失败');
    }

    /**
     * 删除帖子
     * @param int $id
     * @return Json
     */
    public function postDelete(int $id): Json
    {
        $post = Db::name('community_posts')->where('id', $id)->find();
        if (!$post) {
            return $this->error('帖子不存在');
        }

        Db::startTrans();
        try {
            Db::name('community_comments')->where('post_id', $id)->delete();
            Db::name('community_posts')->where('id', $id)->delete();

            Db::commit();
            return $this->success([], '删除成功');
        } catch (\Exception $e) {
            Db::rollback();
            return $this->error('删除失败: ' . $e->getMessage());
        }
    }

    /**
     * 批量删除帖子
     * @return Json
     */
    public function postBatchDelete(): Json
    {
        $ids = $this->request->param('ids', '');
        if (empty($ids)) {
            return $this->error('请选择要删除的帖子');
        }

        $idArray = is_array($ids) ? $ids : explode(',', $ids);
        $idArray = array_filter(array_map('intval', $idArray));

        if (empty($idArray)) {
            return $this->error('ID格式错误');
        }

        Db::startTrans();
        try {
            Db::name('community_comments')->whereIn('post_id', $idArray)->delete();
            $result = Db::name('community_posts')->whereIn('id', $idArray)->delete();

            Db::commit();
            return $this->success(['count' => $result], '批量删除成功');
        } catch (\Exception $e) {
            Db::rollback();
            return $this->error('批量删除失败: ' . $e->getMessage());
        }
    }

    /**
     * 评论列表
     * @return Json
     */
    public function commentList(): Json
    {
        $page = $this->request->param('page', 1);
        $pageSize = $this->request->param('page_size', 10);
        $keyword = $this->request->param('keyword', '');
        $postId = $this->request->param('post_id', '');
        $status = $this->request->param('status', '');

        $where = [];
        if ($keyword) {
            $where[] = ['content', 'like', '%' . $keyword . '%'];
        }
        if ($postId !== '') {
            $where[] = ['post_id', '=', (int)$postId];
        }
        if ($status !== '') {
            $where[] = ['status', '=', (int)$status];
        }

        $query = Db::name('community_comments')->where($where);
        $total = $query->count();
        $list = $query->field('id,post_id,user_id,content,like_count,status,create_time')
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
     * 删除评论
     * @param int $id
     * @return Json
     */
    public function commentDelete(int $id): Json
    {
        $comment = Db::name('community_comments')->where('id', $id)->find();
        if (!$comment) {
            return $this->error('评论不存在');
        }

        Db::startTrans();
        try {
            Db::name('community_comments')->where('id', $id)->delete();
            Db::name('community_posts')->where('id', $comment['post_id'])->dec('comment_count')->update();

            Db::commit();
            return $this->success([], '删除成功');
        } catch (\Exception $e) {
            Db::rollback();
            return $this->error('删除失败: ' . $e->getMessage());
        }
    }
}
