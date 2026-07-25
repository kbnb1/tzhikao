<?php

return [
    'alias' => [
        'cors'       => \app\middleware\Cors::class,
        'jwt_auth'   => \app\middleware\JwtAuth::class,
        'admin_auth' => \app\middleware\AdminAuth::class,
    ],

    'priority' => [
        \app\middleware\Cors::class,
        \app\middleware\JwtAuth::class,
        \app\middleware\AdminAuth::class,
    ],
];
