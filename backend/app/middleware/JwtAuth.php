<?php

declare(strict_types=1);

namespace app\middleware;

use Closure;
use think\Request;
use think\Response;
use app\utils\Jwt;

class JwtAuth
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

        $request->userInfo = $result['data'];
        $request->userId = $result['data']['id'] ?? null;
        $request->username = $result['data']['username'] ?? null;

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
