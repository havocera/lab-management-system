<?php
// 中间件配置
return [
    // 别名或分组
    'alias' => [
        'jwt' => \app\middleware\JwtAuth::class,
        'admin' => \app\middleware\AdminAuth::class,
        'cross' => \think\middleware\AllowCrossDomain::class,
        'system_log' => \app\middleware\SystemLog::class,
    ],
    // 优先级设置，此数组中的中间件会按照数组中的顺序优先执行
    'priority' => [],
    // 全局中间件
    'global' => [
        \app\middleware\SystemLog::class,
    ],
];
