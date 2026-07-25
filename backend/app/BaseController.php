<?php

declare(strict_types=1);

namespace app;

use think\App;
use think\exception\ValidateException;
use think\Validate;
use think\response\Json;

abstract class BaseController
{
    protected $request;
    protected $app;
    protected $batchValidate = false;
    protected $middleware = [];

    protected $userId;
    protected $username;
    protected $userInfo;

    public function __construct(App $app)
    {
        $this->app     = $app;
        $this->request = $this->app->request;

        $this->initialize();
    }

    protected function initialize()
    {
        $userInfo = $this->request->userInfo;
        if ($userInfo) {
            $this->userInfo = $userInfo;
            $this->userId = $userInfo['id'] ?? null;
            $this->username = $userInfo['username'] ?? null;
        }
    }

    protected function success($data = [], string $msg = '操作成功', int $code = 0): Json
    {
        return json([
            'code' => $code,
            'msg'  => $msg,
            'data' => $data,
        ]);
    }

    protected function error(string $msg = '操作失败', int $code = 1, $data = []): Json
    {
        return json([
            'code' => $code,
            'msg'  => $msg,
            'data' => $data,
        ]);
    }

    protected function validate(array $data, string|array $validate, array $message = [], bool $batch = false)
    {
        if (is_array($validate)) {
            $v = new Validate();
            $v->rule($validate);
        } else {
            if (strpos($validate, '.')) {
                [$validate, $scene] = explode('.', $validate);
            }
            $class = false !== strpos($validate, '\\') ? $validate : $this->app->parseClass('validate', $validate);
            $v     = new $class();
            if (!empty($scene)) {
                $v->scene($scene);
            }
        }

        $v->message($message);

        if ($batch || $this->batchValidate) {
            $v->batch(true);
        }

        return $v->failException(true)->check($data);
    }
}
