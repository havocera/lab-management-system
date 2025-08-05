<?php
declare (strict_types = 1);

namespace app\middleware;

use Closure;
use think\Request;
use think\Response;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;

class AdminAuth
{
    public function handle(Request $request, Closure $next)
    {
        $token = $request->header('Authorization');
        if (!$token) {
            return json(['code' => 401, 'msg' => '未登录']);
        }
        $token = str_replace('Bearer ', '', $token);
        try {
            // 验证token
            $key = config('jwt.key');
            $decoded = JWT::decode($token, new Key($key, 'HS256'));
            
            // 检查用户角色是否包含admin
            // if (!in_array('admin', $decoded->roles)) {
            //     return json(['code' => 403, 'msg' => '无权限访问']);
            // }

            // 将用户信息注入到请求中
            $request->user = $decoded;
            
            return $next($request);
        } catch (\Exception $e) {
            return json(['code' => 401, 'msg' => '登录已过期，请重新登录']);
        }
    }
} 