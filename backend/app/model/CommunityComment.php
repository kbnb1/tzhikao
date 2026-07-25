<?php

declare(strict_types=1);

namespace app\model;

use think\Model;

/**
 * 社区评论模型
 */
class CommunityComment extends Model
{
    protected $name = 'community_comments';
    protected $pk = 'id';
    protected $prefix = 'zk_';

    protected $autoWriteTimestamp = true;
    protected $createTime = 'created_at';
    protected $updateTime = 'updated_at';

    protected $type = [
        'id'         => 'integer',
        'post_id'    => 'integer',
        'user_id'    => 'integer',
        'like_count' => 'integer',
        'status'     => 'integer',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * 用户关联
     */
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }

    /**
     * 帖子关联
     */
    public function post()
    {
        return $this->belongsTo(CommunityPost::class, 'post_id', 'id');
    }
}
