<?php

declare(strict_types=1);

namespace app\utils;

use Firebase\JWT\JWT as FirebaseJWT;
use Firebase\JWT\Key;
use Firebase\JWT\ExpiredException;
use Firebase\JWT\SignatureInvalidException;
use Firebase\JWT\BeforeValidException;
use UnexpectedValueException;

class Jwt
{
    private string $secret;
    private int $expire;
    private int $refreshExpire;
    private string $issuer;
    private string $audience;
    private string $alg = 'HS256';

    public function __construct()
    {
        $config = get_jwt_config();
        $this->secret = $config['secret'];
        $this->expire = $config['expire'];
        $this->refreshExpire = $config['refresh_expire'];
        $this->issuer = $config['issuer'];
        $this->audience = $config['audience'];
    }

    public function encode(array $payload, bool $isRefresh = false): string
    {
        $now = time();
        $exp = $isRefresh ? $this->refreshExpire : $this->expire;

        $basePayload = [
            'iss' => $this->issuer,
            'aud' => $this->audience,
            'iat' => $now,
            'nbf' => $now,
            'exp' => $now + $exp,
        ];

        $payload = array_merge($basePayload, $payload);

        return FirebaseJWT::encode($payload, $this->secret, $this->alg);
    }

    public function decode(string $token): object
    {
        return FirebaseJWT::decode($token, new Key($this->secret, $this->alg));
    }

    public function verify(string $token): array
    {
        try {
            $decoded = $this->decode($token);
            return [
                'code' => 0,
                'msg'  => '验证成功',
                'data' => (array)$decoded,
            ];
        } catch (ExpiredException $e) {
            return [
                'code' => 4011,
                'msg'  => 'Token已过期',
                'data' => [],
            ];
        } catch (SignatureInvalidException $e) {
            return [
                'code' => 4012,
                'msg'  => 'Token签名无效',
                'data' => [],
            ];
        } catch (BeforeValidException $e) {
            return [
                'code' => 4013,
                'msg'  => 'Token尚未生效',
                'data' => [],
            ];
        } catch (UnexpectedValueException $e) {
            return [
                'code' => 4014,
                'msg'  => 'Token格式错误',
                'data' => [],
            ];
        } catch (\Exception $e) {
            return [
                'code' => 4015,
                'msg'  => 'Token验证失败: ' . $e->getMessage(),
                'data' => [],
            ];
        }
    }

    public function refresh(string $token): array
    {
        $result = $this->verify($token);
        if ($result['code'] !== 0) {
            return $result;
        }

        $payload = $result['data'];
        unset($payload['iss'], $payload['aud'], $payload['iat'], $payload['nbf'], $payload['exp']);

        $newToken = $this->encode($payload);
        $newRefreshToken = $this->encode($payload, true);

        return [
            'code' => 0,
            'msg'  => '刷新成功',
            'data' => [
                'token'         => $newToken,
                'refresh_token' => $newRefreshToken,
            ],
        ];
    }

    public function generateTokens(array $userInfo): array
    {
        $token = $this->encode($userInfo);
        $refreshToken = $this->encode($userInfo, true);

        return [
            'token'         => $token,
            'refresh_token' => $refreshToken,
            'expires_in'    => $this->expire,
        ];
    }
}
