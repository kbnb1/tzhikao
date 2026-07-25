<?php

declare(strict_types=1);

namespace app\service;

use app\model\CommunityPost;
use app\model\CommunityComment;
use think\facade\Db;

/**
 * 社区服务类
 */
class CommunityService
{
    private CommunityPost $postModel;
    private CommunityComment $commentModel;

    public function __construct()
    {
        $this->postModel = new CommunityPost();
        $this->commentModel = new CommunityComment();
    }

    /**
     * 帖子列表
     * @param int $userId 当前用户ID
     * @param int $page 页码
     * @param int $pageSize 每页数量
     * @param string $category 分类
     * @param string $keyword 搜索关键词
     * @param string $sort 排序方式
     * @return array
     */
    public function getPostList(int $userId, int $page = 1, int $pageSize = 10, string $category = '', string $keyword = '', string $sort = 'latest'): array
    {
        $where = [['status', '=', 1]];
        if (!empty($category)) {
            $where[] = ['category', '=', $category];
        }

        $query = $this->postModel->where($where);

        if (!empty($keyword)) {
            $query->whereLike('title|content', '%' . $keyword . '%');
        }

        switch ($sort) {
            case 'hot':
                $query->order('like_count desc, view_count desc, id desc');
                break;
            case 'most_comment':
                $query->order('comment_count desc, id desc');
                break;
            case 'latest':
            default:
                $query->order('id desc');
                break;
        }

        $total = $query->count();
        $list = $query->with(['user' => function ($query) {
            $query->field('id,nickname,avatar');
        }])->page($page, $pageSize)->select()->toArray();

        if (!empty($list) && $userId > 0) {
            $postIds = array_column($list, 'id');
            $likedPostIds = Db::name('community_post_likes')
                ->where('user_id', $userId)
                ->whereIn('post_id', $postIds)
                ->column('post_id');
            $favoritedPostIds = Db::name('community_post_favorites')
                ->where('user_id', $userId)
                ->whereIn('post_id', $postIds)
                ->column('post_id');

            foreach ($list as &$item) {
                $item['is_liked'] = in_array($item['id'], $likedPostIds) ? 1 : 0;
                $item['is_favorited'] = in_array($item['id'], $favoritedPostIds) ? 1 : 0;
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
     * 帖子详情
     * @param int $userId 当前用户ID
     * @param int $id 帖子ID
     * @return array
     */
    public function getPostDetail(int $userId, int $id): array
    {
        $post = $this->postModel->with(['user' => function ($query) {
            $query->field('id,nickname,avatar');
        }])->where('id', $id)->where('status', 1)->find();

        if (!$post) {
            return ['code' => 1, 'msg' => '帖子不存在', 'data' => []];
        }

        $post->view_count = ['inc', 1];
        $post->save();

        $data = $post->toArray();

        if ($userId > 0) {
            $isLiked = Db::name('community_post_likes')
                ->where('user_id', $userId)
                ->where('post_id', $id)
                ->find();
            $isFavorited = Db::name('community_post_favorites')
                ->where('user_id', $userId)
                ->where('post_id', $id)
                ->find();
            $data['is_liked'] = $isLiked ? 1 : 0;
            $data['is_favorited'] = $isFavorited ? 1 : 0;
        } else {
            $data['is_liked'] = 0;
            $data['is_favorited'] = 0;
        }

        return ['code' => 0, 'msg' => '获取成功', 'data' => $data];
    }

    /**
     * 发布帖子
     * @param int $userId 用户ID
     * @param array $data 帖子数据
     * @return array
     */
    public function createPost(int $userId, array $data): array
    {
        if (empty($data['title'])) {
            return ['code' => 1, 'msg' => '帖子标题不能为空', 'data' => []];
        }
        if (empty($data['content'])) {
            return ['code' => 1, 'msg' => '帖子内容不能为空', 'data' => []];
        }

        $images = isset($data['images']) ? (is_array($data['images']) ? $data['images'] : json_decode($data['images'], true)) : [];

        $post = $this->postModel->create([
            'user_id'  => $userId,
            'title'    => $data['title'],
            'content'  => $data['content'],
            'images'   => $images,
            'category' => $data['category'] ?? '',
            'status'   => 1,
        ]);

        if ($post) {
            return ['code' => 0, 'msg' => '发布成功', 'data' => $post->toArray()];
        }

        return ['code' => 1, 'msg' => '发布失败', 'data' => []];
    }

    /**
     * 删除帖子
     * @param int $userId 用户ID
     * @param int $id 帖子ID
     * @return array
     */
    public function deletePost(int $userId, int $id): array
    {
        $post = $this->postModel->where('id', $id)->find();
        if (!$post) {
            return ['code' => 1, 'msg' => '帖子不存在', 'data' => []];
        }

        if ($post['user_id'] != $userId) {
            return ['code' => 1, 'msg' => '只能删除自己的帖子', 'data' => []];
        }

        Db::startTrans();
        try {
            $this->commentModel->where('post_id', $id)->delete();
            $post->delete();

            Db::commit();
            return ['code' => 0, 'msg' => '删除成功', 'data' => []];
        } catch (\Exception $e) {
            Db::rollback();
            return ['code' => 1, 'msg' => '删除失败: ' . $e->getMessage(), 'data' => []];
        }
    }

    /**
     * 点赞帖子
     * @param int $userId 用户ID
     * @param int $id 帖子ID
     * @return array
     */
    public function likePost(int $userId, int $id): array
    {
        $post = $this->postModel->where('id', $id)->where('status', 1)->find();
        if (!$post) {
            return ['code' => 1, 'msg' => '帖子不存在', 'data' => []];
        }

        $likeTable = Db::name('community_post_likes');
        $liked = $likeTable->where('user_id', $userId)->where('post_id', $id)->find();

        Db::startTrans();
        try {
            if ($liked) {
                $likeTable->where('id', $liked['id'])->delete();
                $post->like_count = ['dec', 1];
                $post->save();
                Db::commit();
                return ['code' => 0, 'msg' => '取消点赞成功', 'data' => ['is_liked' => 0, 'like_count' => $post['like_count'] - 1]];
            } else {
                $likeTable->insert([
                    'user_id'    => $userId,
                    'post_id'    => $id,
                    'created_at' => time(),
                ]);
                $post->like_count = ['inc', 1];
                $post->save();
                Db::commit();
                return ['code' => 0, 'msg' => '点赞成功', 'data' => ['is_liked' => 1, 'like_count' => $post['like_count'] + 1]];
            }
        } catch (\Exception $e) {
            Db::rollback();
            return ['code' => 1, 'msg' => '操作失败: ' . $e->getMessage(), 'data' => []];
        }
    }

    /**
     * 收藏帖子
     * @param int $userId 用户ID
     * @param int $id 帖子ID
     * @return array
     */
    public function favoritePost(int $userId, int $id): array
    {
        $post = $this->postModel->where('id', $id)->where('status', 1)->find();
        if (!$post) {
            return ['code' => 1, 'msg' => '帖子不存在', 'data' => []];
        }

        $favoriteTable = Db::name('community_post_favorites');
        $favorited = $favoriteTable->where('user_id', $userId)->where('post_id', $id)->find();

        Db::startTrans();
        try {
            if ($favorited) {
                $favoriteTable->where('id', $favorited['id'])->delete();
                $post->favorite_count = ['dec', 1];
                $post->save();
                Db::commit();
                return ['code' => 0, 'msg' => '取消收藏成功', 'data' => ['is_favorited' => 0, 'favorite_count' => $post['favorite_count'] - 1]];
            } else {
                $favoriteTable->insert([
                    'user_id'    => $userId,
                    'post_id'    => $id,
                    'created_at' => time(),
                ]);
                $post->favorite_count = ['inc', 1];
                $post->save();
                Db::commit();
                return ['code' => 0, 'msg' => '收藏成功', 'data' => ['is_favorited' => 1, 'favorite_count' => $post['favorite_count'] + 1]];
            }
        } catch (\Exception $e) {
            Db::rollback();
            return ['code' => 1, 'msg' => '操作失败: ' . $e->getMessage(), 'data' => []];
        }
    }

    /**
     * 评论列表
     * @param int $userId 当前用户ID
     * @param int $postId 帖子ID
     * @param int $page 页码
     * @param int $pageSize 每页数量
     * @return array
     */
    public function getCommentList(int $userId, int $postId, int $page = 1, int $pageSize = 10): array
    {
        $post = $this->postModel->where('id', $postId)->where('status', 1)->find();
        if (!$post) {
            return ['code' => 1, 'msg' => '帖子不存在', 'data' => []];
        }

        $query = $this->commentModel->where('post_id', $postId);
        $total = $query->count();
        $list = $query->with(['user' => function ($query) {
            $query->field('id,nickname,avatar');
        }])->order('id desc')->page($page, $pageSize)->select()->toArray();

        if (!empty($list) && $userId > 0) {
            $commentIds = array_column($list, 'id');
            $likedCommentIds = Db::name('community_comment_likes')
                ->where('user_id', $userId)
                ->whereIn('comment_id', $commentIds)
                ->column('comment_id');

            foreach ($list as &$item) {
                $item['is_liked'] = in_array($item['id'], $likedCommentIds) ? 1 : 0;
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
     * 发表评论
     * @param int $userId 用户ID
     * @param int $postId 帖子ID
     * @param string $content 评论内容
     * @return array
     */
    public function createComment(int $userId, int $postId, string $content): array
    {
        if (empty(trim($content))) {
            return ['code' => 1, 'msg' => '评论内容不能为空', 'data' => []];
        }

        $post = $this->postModel->where('id', $postId)->where('status', 1)->find();
        if (!$post) {
            return ['code' => 1, 'msg' => '帖子不存在', 'data' => []];
        }

        Db::startTrans();
        try {
            $comment = $this->commentModel->create([
                'post_id' => $postId,
                'user_id' => $userId,
                'content' => $content,
                'status'  => 1,
            ]);

            $post->comment_count = ['inc', 1];
            $post->save();

            Db::commit();

            $commentData = $comment->toArray();
            $commentData['user'] = [
                'id'       => $userId,
                'nickname' => $this->getUserNickname($userId),
                'avatar'   => $this->getUserAvatar($userId),
            ];
            $commentData['is_liked'] = 0;

            return ['code' => 0, 'msg' => '评论成功', 'data' => $commentData];
        } catch (\Exception $e) {
            Db::rollback();
            return ['code' => 1, 'msg' => '评论失败: ' . $e->getMessage(), 'data' => []];
        }
    }

    /**
     * 删除评论
     * @param int $userId 用户ID
     * @param int $id 评论ID
     * @return array
     */
    public function deleteComment(int $userId, int $id): array
    {
        $comment = $this->commentModel->where('id', $id)->find();
        if (!$comment) {
            return ['code' => 1, 'msg' => '评论不存在', 'data' => []];
        }

        if ($comment['user_id'] != $userId) {
            return ['code' => 1, 'msg' => '只能删除自己的评论', 'data' => []];
        }

        Db::startTrans();
        try {
            $comment->delete();

            $post = $this->postModel->where('id', $comment['post_id'])->find();
            if ($post && $post['comment_count'] > 0) {
                $post->comment_count = ['dec', 1];
                $post->save();
            }

            Db::commit();
            return ['code' => 0, 'msg' => '删除成功', 'data' => []];
        } catch (\Exception $e) {
            Db::rollback();
            return ['code' => 1, 'msg' => '删除失败: ' . $e->getMessage(), 'data' => []];
        }
    }

    /**
     * 点赞评论
     * @param int $userId 用户ID
     * @param int $id 评论ID
     * @return array
     */
    public function likeComment(int $userId, int $id): array
    {
        $comment = $this->commentModel->where('id', $id)->find();
        if (!$comment) {
            return ['code' => 1, 'msg' => '评论不存在', 'data' => []];
        }

        $likeTable = Db::name('community_comment_likes');
        $liked = $likeTable->where('user_id', $userId)->where('comment_id', $id)->find();

        Db::startTrans();
        try {
            if ($liked) {
                $likeTable->where('id', $liked['id'])->delete();
                $comment->like_count = ['dec', 1];
                $comment->save();
                Db::commit();
                return ['code' => 0, 'msg' => '取消点赞成功', 'data' => ['is_liked' => 0, 'like_count' => $comment['like_count'] - 1]];
            } else {
                $likeTable->insert([
                    'user_id'    => $userId,
                    'comment_id' => $id,
                    'created_at' => time(),
                ]);
                $comment->like_count = ['inc', 1];
                $comment->save();
                Db::commit();
                return ['code' => 0, 'msg' => '点赞成功', 'data' => ['is_liked' => 1, 'like_count' => $comment['like_count'] + 1]];
            }
        } catch (\Exception $e) {
            Db::rollback();
            return ['code' => 1, 'msg' => '操作失败: ' . $e->getMessage(), 'data' => []];
        }
    }

    /**
     * 获取用户昵称
     */
    private function getUserNickname(int $userId): string
    {
        $user = Db::name('users')->where('id', $userId)->value('nickname');
        return $user ?? '';
    }

    /**
     * 获取用户头像
     */
    private function getUserAvatar(int $userId): string
    {
        $avatar = Db::name('users')->where('id', $userId)->value('avatar');
        if (empty($avatar)) {
            return '';
        }
        if (str_starts_with($avatar, 'http://') || str_starts_with($avatar, 'https://')) {
            return $avatar;
        }
        return request()->domain() . '/' . ltrim($avatar, '/');
    }
}
