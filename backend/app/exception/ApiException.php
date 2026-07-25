<?php

declare(strict_types=1);

namespace app\exception;

use think\exception\Handle;
use Throwable;

class ApiException extends \RuntimeException
{
    protected $code;
    protected $message;
    protected $data;

    public function __construct(string $message = '', int $code = 1, $data = [], Throwable $previous = null)
    {
        $this->code = $code;
        $this->message = $message;
        $this->data = $data;

        parent::__construct($message, $code, $previous);
    }

    public function getData()
    {
        return $this->data;
    }

    public function getResponseData(): array
    {
        return [
            'code' => $this->code,
            'msg'  => $this->message,
            'data' => $this->data,
        ];
    }
}
