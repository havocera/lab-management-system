<?php
declare (strict_types = 1);

namespace app\middleware;

use think\facade\Db;
use think\facade\Request;

class SystemLog
{
    public function handle($request, \Closure $next)
    {
        $response = $next($request);
        
        // 获取当前用户ID
        $userId = $request->user_id ?? null;
        if (!$userId) {
            return $response;
        }
        
        // 获取请求路径和方法
        $path = $request->path();
        $method = $request->method();
        
        // 解析操作类型和目标
        $action = $this->parseAction($method, $path);
        $target = $this->parseTarget($path);
        
        if ($action && $target) {
            // 记录系统日志
            Db::table('system_log')->insert([
                'user_id' => $userId,
                'action' => $action,
                'target' => $target,
                'created_at' => date('Y-m-d H:i:s')
            ]);
        }
        
        return $response;
    }
    
    /**
     * 解析操作类型
     */
    private function parseAction($method, $path)
    {
        $path = strtolower($path);
        
        switch ($method) {
            case 'POST':
                if (strpos($path, 'create') !== false || strpos($path, 'add') !== false) {
                    return '添加了';
                }
                break;
            case 'PUT':
            case 'PATCH':
                if (strpos($path, 'update') !== false || strpos($path, 'edit') !== false) {
                    return '更新了';
                }
                break;
            case 'DELETE':
                if (strpos($path, 'delete') !== false || strpos($path, 'remove') !== false) {
                    return '删除了';
                }
                break;
        }
        
        return null;
    }
    
    /**
     * 解析操作目标
     */
    private function parseTarget($path)
    {
        $path = strtolower($path);
        $segments = explode('/', $path);
        
        // 移除空值和数字
        $segments = array_filter($segments, function($segment) {
            return !empty($segment) && !is_numeric($segment);
        });
        
        // 获取最后一个非空段
        $target = end($segments);
        
        // 移除常见的操作词
        $target = str_replace(['create', 'update', 'delete', 'edit', 'add', 'remove'], '', $target);
        
        return ucfirst($target);
    }
} 