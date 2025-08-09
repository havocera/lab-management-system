<?php

namespace app\common\exception;

use think\db\exception\DataNotFoundException;
use think\db\exception\ModelNotFoundException;
use think\exception\Handle;
use think\exception\HttpException;
use think\exception\HttpResponseException;
use think\exception\ValidateException;
use think\Response;
use Throwable;

/**
 * JSON异常处理类 - 符合ThinkPHP 8规范
 */
class JsonException extends Handle
{
    /**
     * 不需要记录信息（日志）的异常类列表
     * @var array
     */
    protected $ignoreReport = [
        HttpException::class,
        HttpResponseException::class,
        ModelNotFoundException::class,
        DataNotFoundException::class,
        ValidateException::class,
    ];

    /**
     * 记录异常信息（包括日志或者其它方式记录）
     *
     * @access public
     * @param  Throwable $exception
     * @return void
     */
    public function report(Throwable $exception): void
    {
        // 使用内置的方式记录异常日志
        parent::report($exception);
    }

    /**
     * Render an exception into an HTTP response.
     *
     * @access public
     * @param \think\Request $request
     * @param Throwable $e
     * @return Response
     */
    public function render($request, Throwable $e): Response
    {
        // 统一返回JSON格式
        if ($this->app->isDebug()) {
            // 调试模式返回详细信息
            $data = [
                'code' => $this->getCode($e),
                'msg' => $e->getMessage(),
                'data' => [
                    'file' => $e->getFile(),
                    'line' => $e->getLine(),
                    'trace' => explode("\n", $e->getTraceAsString())
                ]
            ];
        } else {
            // 生产模式返回简化信息
            $message = $e->getMessage();
            
            // 根据异常类型设置友好的错误信息
            if ($e instanceof ValidateException) {
                $message = $e->getError();
            } elseif ($e instanceof DataNotFoundException || $e instanceof ModelNotFoundException) {
                $message = '数据不存在';
            } elseif ($e instanceof HttpException) {
                // HTTP异常使用其状态码对应的信息
                $statusCode = $e->getStatusCode();
                $message = $this->getHttpStatusMessage($statusCode);
            } else {
                // 其他异常显示通用错误信息
                $message = $this->app->config->get('app.error_message', '系统错误，请稍后重试');
            }
            
            $data = [
                'code' => $this->getCode($e),
                'msg' => $message,
                'data' => []
            ];
        }

        $response = Response::create($data, 'json');
        
        // 设置HTTP状态码
        if ($e instanceof HttpException) {
            $response->code($e->getStatusCode());
            $response->header($e->getHeaders());
        } else {
            $response->code(500);
        }

        return $response;
    }

    /**
     * 获取异常错误码
     * 
     * @param Throwable $exception
     * @return int
     */
    protected function getCode(Throwable $exception): int
    {
        $code = $exception->getCode();
        
        if ($exception instanceof HttpException) {
            return $exception->getStatusCode();
        }
        
        if ($exception instanceof ValidateException) {
            return 422; // 表单验证错误
        }
        
        if ($exception instanceof DataNotFoundException || $exception instanceof ModelNotFoundException) {
            return 404; // 数据不存在
        }
        
        return $code ?: 500;
    }

    /**
     * 获取HTTP状态码对应的友好信息
     * 
     * @param int $statusCode
     * @return string
     */
    protected function getHttpStatusMessage(int $statusCode): string
    {
        $messages = [
            400 => '请求参数错误',
            401 => '未授权访问',
            403 => '访问被禁止',
            404 => '请求的资源不存在',
            405 => '请求方法不被允许',
            422 => '请求参数验证失败',
            429 => '请求过于频繁',
            500 => '服务器内部错误',
            502 => '网关错误',
            503 => '服务暂时不可用',
        ];

        return $messages[$statusCode] ?? '未知错误';
    }
}