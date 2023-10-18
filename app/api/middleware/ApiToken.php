<?php

namespace app\api\middleware;

// 中间件
use app\BaseController;
use Firebase\JWT\ExpiredException;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use Firebase\JWT\SignatureInvalidException;
use think\Exception;
use think\facade\Db;

class ApiToken extends BaseController
{
    //解析Token
    public function handle($request, \Closure $next)
    {
        try {
            //接收token
            $token = $request->header('token');
            if (empty($token)) {
                $this->error('请先登录！');
            }
            // 记录 API 请求日志
            Db::name('api_request_log')->insert([
                'api_url' => $request->domain() . $request->url(),
                'request_method' => $request->method(),
                'request_ip' => $request->ip(),
                'request_time' => date('Y-m-d H:i:s'),
            ]);
            //加载配置项JWT Key
            $test = JWT::decode($token, new Key(config('app.jwtKey'), 'HS256'));
            $request->user_id = $test->data->user_id['0']->id;
            $request->mobile = $test->data->mobile;
        } catch (SignatureInvalidException $signatureInvalidException) {
            // 获取验证失败时抛出的错误信息
            $this->error('身份信息验证失败,请重新登录!');
        } catch (ExpiredException $expiredException) {
            // 获取 token 过期时抛出的错误信息
            $this->error('登录信息过期，请重新登录！');
        } catch (Exception $exception) {
            // 获取抛出的其它错误信息
            return $exception->getMessage();
        }
        return $next($request);
    }

}