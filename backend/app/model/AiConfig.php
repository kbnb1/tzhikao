<?php

declare(strict_types=1);

namespace app\model;

use think\Model;

/**
 * AI配置模型
 * @property int $id 配置ID
 * @property string $provider AI服务商名称
 * @property string $name 配置名称
 * @property string $api_key API密钥
 * @property string $api_url API地址
 * @property string $model 模型名称
 * @property int $status 状态：0禁用 1启用
 * @property int $is_default 是否默认：0否 1是
 * @property string $create_time 创建时间
 * @property string $update_time 更新时间
 */
class AiConfig extends Model
{
    protected $name = 'ai_configs';

    protected $pk = 'id';

    protected $autoWriteTimestamp = true;

    protected $createTime = 'create_time';
    protected $updateTime = 'update_time';

    protected $json = [];

    protected $hidden = ['api_key'];

    /**
     * 获取启用的配置列表
     * @return \think\Collection
     */
    public static function getActiveList()
    {
        return self::where('status', 1)
            ->order('is_default desc, id asc')
            ->select();
    }

    /**
     * 获取默认配置
     * @return AiConfig|null
     */
    public static function getDefaultConfig()
    {
        return self::where('status', 1)
            ->where('is_default', 1)
            ->find();
    }

    /**
     * 根据服务商获取配置
     * @param string $provider 服务商标识
     * @return AiConfig|null
     */
    public static function getConfigByProvider(string $provider)
    {
        return self::where('status', 1)
            ->where('provider', $provider)
            ->find();
    }
}
