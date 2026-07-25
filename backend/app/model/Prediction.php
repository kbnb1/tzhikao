<?php

declare(strict_types=1);

namespace app\model;

use think\Model;

/**
 * 预测记录模型
 * @property int $id 记录ID
 * @property int $user_id 用户ID
 * @property string $subject 科目
 * @property float $predicted_score 预测分数
 * @property float $confidence 置信度 0-1
 * @property string $suggestion 学习建议
 * @property string $prediction_data 预测原始数据(JSON)
 * @property string $provider AI服务商
 * @property string $create_time 创建时间
 * @property string $update_time 更新时间
 */
class Prediction extends Model
{
    protected $name = 'predictions';

    protected $pk = 'id';

    protected $autoWriteTimestamp = true;

    protected $createTime = 'create_time';
    protected $updateTime = 'update_time';

    protected $json = ['prediction_data'];

    /**
     * 获取用户预测记录列表
     * @param int $userId 用户ID
     * @param int $page 页码
     * @param int $limit 每页数量
     * @param string|null $subject 科目筛选
     * @return array
     */
    public static function getUserList(int $userId, int $page = 1, int $limit = 10, ?string $subject = null): array
    {
        $query = self::where('user_id', $userId);

        if (!empty($subject)) {
            $query->where('subject', $subject);
        }

        $total = $query->count();
        $list = $query->order('id desc')
            ->page($page, $limit)
            ->select();

        return [
            'total' => $total,
            'page' => $page,
            'limit' => $limit,
            'list' => $list,
        ];
    }

    /**
     * 创建预测记录
     * @param int $userId 用户ID
     * @param array $data 预测数据
     * @return Prediction
     */
    public static function createRecord(int $userId, array $data): self
    {
        $prediction = new self();
        $prediction->user_id = $userId;
        $prediction->subject = $data['subject'] ?? '';
        $prediction->predicted_score = $data['predicted_score'] ?? 0;
        $prediction->confidence = $data['confidence'] ?? 0;
        $prediction->suggestion = $data['suggestion'] ?? '';
        $prediction->prediction_data = $data['prediction_data'] ?? [];
        $prediction->provider = $data['provider'] ?? 'mock';
        $prediction->save();

        return $prediction;
    }
}
