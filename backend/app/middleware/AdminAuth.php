<?php

declare(strict_types=1);

namespace app\middleware;

use Closure;
use think\Request;
use think\Response;
use app\utils\Jwt;

class AdminAuth
{
    public function handle(Request $request, Closure $next): Response
    {
        $token = $this->getTokenFromHeader($request);

        if (empty($token)) {
            return json([
                'code' => 401,
                'msg'  => '请先登录',
                'data' => [],
            ])->code(401);
        }

        $jwt = new Jwt();
        $result = $jwt->verify($token);

        if ($result['code'] !== 0) {
            return json([
                'code' => 401,
                'msg'  => $result['msg'],
                'data' => [],
            ])->code(401);
        }

        $userInfo = $result['data'];

        if (empty($userInfo['is_admin']) || $userInfo['is_admin'] != 1) {
            return json([
                'code' => 403,
                'msg'  => '权限不足，需要管理员权限',
                'data' => [],
            ])->code(403);
        }

        $request->userInfo = $userInfo;
        $request->adminId = $userInfo['id'] ?? null;
        $request->adminName = $userInfo['username'] ?? null;

        return $next($request);
    }

    private function getTokenFromHeader(Request $request): string
    {
        $authorization = $request->header('Authorization');
        if (empty($authorization)) {
            return '';
        }

        if (preg_match('/Bearer\s+(.*)$/i', $authorization, $matches)) {
            return $matches[1];
        }

        return '';
    }
}
