<?php

declare(strict_types=1);

namespace app\controller\api\v1;

use app\BaseController;
use app\service\CommunityService;
use think\response\Json;

/**
 * 社区控制器
 */
class Community extends BaseController
{
    private CommunityService $communityService;

    protected $middleware = ['jwt_auth'];

    public function __construct(\think\App $app)
    {
        parent::__construct($app);
        $this->communityService = new CommunityService();
    }

    /**
     * 帖子列表
     * @return Json
     */
    public function postList(): Json
    {
        $page = (int)$this->request->param('page', 1);
        $pageSize = (int)$this->request->param('page_size', 10);
        $category = $this->request->param('category', '');
        $keyword = $this->request->param('keyword', '');
        $sort = $this->request->param('sort', 'latest');

        $result = $this->communityService->getPostList($this->userId, $page, $pageSize, $category, $keyword, $sort);

        if ($result['code'] !== 0) {
            return $this->error($result['msg'], $result['code'], $result['data']);
        }

        return $this->success($result['data'], $result['msg']);
    }

    /**
     * 帖子详情
     * @param int $id
     * @return Json
     */
    public function postDetail(int $id): Json
    {
        $result = $this->communityService->getPostDetail($this->userId, $id);

        if ($result['code'] !== 0) {
            return $this->error($result['msg'], $result['code'], $result['data']);
        }

        return $this->success($result['data'], $result['msg']);
    }

    /**
     * 发布帖子
     * @return Json
     */
    public function postCreate(): Json
    {
        $data = $this->request->post();

        $result = $this->communityService->createPost($this->userId, $data);

        if ($result['code'] !== 0) {
            return $this->error($result['msg'], $result['code'], $result['data']);
        }

        return $this->success($result['data'], $result['msg']);
    }

    /**
     * 删除帖子
     * @param int $id
     * @return Json
     */
    public function postDelete(int $id): Json
    {
        $result = $this->communityService->deletePost($this->userId, $id);

        if ($result['code'] !== 0) {
            return $this->error($result['msg'], $result['code'], $result['data']);
        }

        return $this->success($result['data'], $result['msg']);
    }

    /**
     * 点赞帖子
     * @param int $id
     * @return Json
     */
    public function postLike(int $id): Json
    {
        $result = $this->communityService->likePost($this->userId, $id);

        if ($result['code'] !== 0) {
            return $this->error($result['msg'], $result['code'], $result['data']);
        }

        return $this->success($result['data'], $result['msg']);
    }

    /**
     * 收藏帖子
     * @param int $id
     * @return Json
     */
    public function postFavorite(int $id): Json
    {
        $result = $this->communityService->favoritePost($this->userId, $id);

        if ($result['code'] !== 0) {
            return $this->error($result['msg'], $result['code'], $result['data']);
        }

        return $this->success($result['data'], $result['msg']);
    }

    /**
     * 评论列表
     * @param int $postId
     * @return Json
     */
    public function commentList(int $postId): Json
    {
        $page = (int)$this->request->param('page', 1);
        $pageSize = (int)$this->request->param('page_size', 10);

        $result = $this->communityService->getCommentList($this->userId, $postId, $page, $pageSize);

        if ($result['code'] !== 0) {
            return $this->error($result['msg'], $result['code'], $result['data']);
        }

        return $this->success($result['data'], $result['msg']);
    }

    /**
     * 发表评论
     * @param int $postId
     * @return Json
     */
    public function commentCreate(int $postId): Json
    {
        $content = $this->request->param('content', '');

        $result = $this->communityService->createComment($this->userId, $postId, $content);

        if ($result['code'] !== 0) {
            return $this->error($result['msg'], $result['code'], $result['data']);
        }

        return $this->success($result['data'], $result['msg']);
    }

    /**
     * 删除评论
     * @param int $id
     * @return Json
     */
    public function commentDelete(int $id): Json
    {
        $result = $this->communityService->deleteComment($this->userId, $id);

        if ($result['code'] !== 0) {
            return $this->error($result['msg'], $result['code'], $result['data']);
        }

        return $this->success($result['data'], $result['msg']);
    }

    /**
     * 点赞评论
     * @param int $id
     * @return Json
     */
    public function commentLike(int $id): Json
    {
        $result = $this->communityService->likeComment($this->userId, $id);

        if ($result['code'] !== 0) {
            return $this->error($result['msg'], $result['code'], $result['data']);
        }

        return $this->success($result['data'], $result['msg']);
    }
}
