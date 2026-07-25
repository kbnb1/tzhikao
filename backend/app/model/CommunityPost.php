<?php

declare(strict_types=1);

namespace app\model;

use think\Model;

/**
 * 社区帖子模型
 */
class CommunityPost extends Model
{
    protected $name = 'community_posts';
    protected $pk = 'id';
    protected $prefix = 'zk_';

    protected $autoWriteTimestamp = true;
    protected $createTime = 'created_at';
    protected $updateTime = 'updated_at';

    protected $type = [
        'id'            => 'integer',
        'user_id'       => 'integer',
        'view_count'    => 'integer',
        'like_count'    => 'integer',
        'favorite_count' => 'integer',
        'comment_count' => 'integer',
        'status'        => 'integer',
        'created_at'    => 'datetime',
        'updated_at'    => 'datetime',
    ];

    protected $json = ['images'];
    protected $jsonAssoc = true;

    /**
     * 状态获取器
     */
    public function getStatusTextAttr($value, $data): string
    {
        $map = [
            0 => '待审核',
            1 => '已通过',
            2 => '已拒绝',
        ];
        return $map[$data['status']] ?? '未知';
    }

    /**
     * 用户关联
     */
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }

    /**
     * 评论关联
     */
    public function comments()
    {
        return $this->hasMany(CommunityComment::class, 'post_id', 'id');
    }
}
