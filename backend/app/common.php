<?php

declare(strict_types=1);

use think\facade\Config;

if (!function_exists('success')) {
    function success($data = [], string $msg = '操作成功', int $code = 0): \think\response\Json
    {
        return json([
            'code' => $code,
            'msg'  => $msg,
            'data' => $data,
        ]);
    }
}

if (!function_exists('error')) {
    function error(string $msg = '操作失败', int $code = 1, $data = []): \think\response\Json
    {
        return json([
            'code' => $code,
            'msg'  => $msg,
            'data' => $data,
        ]);
    }
}

if (!function_exists('get_jwt_config')) {
    function get_jwt_config(): array
    {
        return [
            'secret'          => env('JWT_SECRET', 'zhikao_ai_jwt_secret'),
            'expire'          => (int)env('JWT_EXPIRE', 7200),
            'refresh_expire'  => (int)env('JWT_REFRESH_EXPIRE', 86400),
            'issuer'          => env('JWT_ISSUER', 'zhikao_ai'),
            'audience'        => env('JWT_AUDIENCE', 'zhikao_ai_api'),
        ];
    }
}

