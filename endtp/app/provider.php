<?php
use app\ExceptionHandle;
use app\Request;
use app\common\exception\JsonException;
// 容器Provider定义文件
return [
    'think\Request'          => Request::class,
    'think\exception\Handle' => JsonException::class,
    
];
