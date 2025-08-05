<?php
declare (strict_types = 1);

namespace app\middleware;

use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use think\facade\Config;

class JwtAuth
{
    public function handle($request, \Closure $next)
    {
        // 获取token
        $token = $request->header('Authorization');
        
        if (!$token) {
            return json(['code' => 401, 'msg' => '未登录'], 401);
        }
        $token = str_replace('Bearer ', '', $token);
        // var_dump($token);
        try {
            // 验证token
            $key = config('jwt.key');
            $decoded = JWT::decode($token, new Key($key, 'HS256'));
            // var_dump($decoded);
            // 将用户信息注入到请求中
            $request->uid = $decoded->uid;
            $request->username = $decoded->username;
            $request->uid = $decoded->uid;
            $request->user = $decoded;
            // $request->role = $decoded->role;
            // var_dump($request);
            return $next($request);
        } catch (\Exception $e) {
            // var_dump($e);
            return json(['code' => 401, 'msg' => '登录已过期，请重新登录'], 401);
        }
    }
} 