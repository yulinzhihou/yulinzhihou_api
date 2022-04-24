<?php
declare(strict_types=1);
namespace app\admin\middleware;

use app\admin\library\JwtUtil;
use think\facade\Config;
use think\facade\Env;

class checkSign
{
    /**
     * 处理请求
     * @return mixed|void
     */
    public function handle(\think\Request $request, \Closure $next)
    {
        //过滤OPTIONS请求
        if ( $_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
            header("Access-Control-Allow-Origin: *");
            header("Access-Control-Allow-Headers:Authorization,Content-Type,If-Match,If-Modified-Since,If-None-Match,If-Unmodified-Since,X-Requested-With,x_requested_with,X-token,ignoreCancelToken");
            header('Access-Control-Allow-Methods: GET, POST, PUT,DELETE,OPTIONS,PATCH,OPTIONS');
            exit;
        }
        header('Access-Control-Allow-Headers: Authorization, Content-Type, If-Match, If-Modified-Since, If-None-Match, If-Unmodified-Since, X-CSRF-TOKEN, X-Requested-With,X-token,ignoreCancelToken');
        header('Access-Control-Allow-Methods: GET, POST, PATCH, PUT, DELETE');
        header('Access-Control-Allow-Origin: *');
        // 路由白名单
        $whitelist = Config::get('whitelist');
        $route = request()->pathinfo();
        if (!in_array($route, $whitelist)) { // 对登录控制器放行
            $token = request()->header('authorization');  // 前端请求携带的Token信息
            $isRas = Env::get('jwt.is_rsa',false);
            $key = $isRas ? root_path().Env::get('jwt.path').DIRECTORY_SEPARATOR.Env::get('jwt.name').'.key' : Env::get('jwt.app_key');
            $jwt = JwtUtil::verification($key, $token,$isRas ? 'RS256' : 'HS256'); // 与签发的key一致
            if ($jwt['status'] == 200) {
                $request->uid       = $jwt['data']->data->uid; // 传入登录用户ID
                $request->role_key  = $jwt['data']->data->role; // 传入登录用户角色组key
                $request->user_info = $jwt['data']->data->user_info;
            } else {
                $data = [
                    'status'        => 200,
                    'code'          => 0,
                    'result'        => [],
                    'message'       => '身份信息异常，或已退出系统',
                    'type'          => 'success'
                ];
                return json($data);
            }
        } else {
            // 无需要鉴权的接口请求，也需要标识哪个用户来源
            $token = $request->param('token');  // 前端请求携带的Token信息
            $uid = $request->param('uid');  // uid
            // 排队登录接口
            if ($route != 'v1/login/login') {
                if ($token == '' || $uid == '') {
                    $data = [
                        'status'        => 504,
                        'code'          => 0,
                        'result'        => [],
                        'message'       => '请仔细检查请求的接口参数，请求被拒绝。',
                        'type'          => 'error'
                    ];
                    return json($data);
                } else {
                    $jwt = JwtUtil::verification(Env::get('app_key','test'), $token); // 与签发的key一致
                    if ($jwt['status'] && $jwt['data']->data->uid == $uid) {
                        $request->uid       = $jwt['data']->data->uid; // 传入登录用户ID
                        $request->role_key  = $jwt['data']->data->role; // 传入登录用户角色组key
                        $request->user_info = $jwt['data']->data->user_info;
                    } else {
                        $data = [
                            'status'        => 504,
                            'code'          => 0,
                            'result'        => [],
                            'message'       => 'token被恶意修改，请求被拒绝。',
                            'type'          => 'error'
                        ];
                        return json($data);
                    }
                }
            }
        }
        return $next($request);
    }
}