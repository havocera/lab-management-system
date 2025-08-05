<?php

return [
    // JWT密钥
    'key' => env('JWT_KEY', 'your-secret-key'),
    
    // token过期时间（秒）
    'expire' => 604800,
    
    // 刷新token过期时间（秒）
    'refresh_expire' => env('JWT_REFRESH_EXPIRE', 604800),
    
    // 签发者
    'issuer' => env('JWT_ISSUER', 'labmanage'),
    
    // 接收者
    'audience' => env('JWT_AUDIENCE', 'labmanage'),
]; 