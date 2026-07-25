<?php

namespace app;

use think\db\exception\DataNotFoundException;
use think\db\exception\ModelNotFoundException;
use think\exception\Handle;
use think\exception\HttpException;
use think\exception\HttpResponseException;
use think\exception\ValidateException;
use think\Response;
use Throwable;
use app\exception\ApiException;

class ExceptionHandle extends Handle
{
    protected $ignoreReport = [
        HttpException::class,
        HttpResponseException::class,
        ModelNotFoundException::class,
        DataNotFoundException::class,
        ValidateException::class,
        ApiException::class,
    ];

    public function report(Throwable $exception): void
    {
        parent::report($exception);
    }

    public function render($request, Throwable $e): Response
    {
        if ($e instanceof ApiException) {
            return json($e->getResponseData())->code(200);
        }

        if ($e instanceof ValidateException) {
            return json([
                'code' => 422,
                'msg'  => $e->getMessage(),
                'data' => [],
            ])->code(422);
        }

        if ($e instanceof HttpException) {
            $statusCode = $e->getStatusCode();
            $message = $e->getMessage() ?: '请求异常';
            return json([
                'code' => $statusCode,
                'msg'  => $message,
                'data' => [],
            ])->code($statusCode);
        }

        if ($e instanceof ModelNotFoundException || $e instanceof DataNotFoundException) {
            return json([
                'code' => 404,
                'msg'  => '数据不存在',
                'data' => [],
            ])->code(404);
        }

        if (env('APP_DEBUG', false)) {
            return json([
                'code' => 500,
                'msg'  => $e->getMessage(),
                'data' => [
                    'file'  => $e->getFile(),
                    'line'  => $e->getLine(),
                    'trace' => $e->getTraceAsString(),
                ],
            ])->code(500);
        }

        return json([
            'code' => 500,
            'msg'  => '服务器内部错误',
            'data' => [],
        ])->code(500);
    }
}
