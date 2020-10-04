<?php
// +----------------------------------------------------------------------
// | Copyright (c) 2019 http://restfulapi.cn All rights reserved.
// +----------------------------------------------------------------------
// | Author: ngtwewy <62006464@qq.com> 
// +----------------------------------------------------------------------
// | DateTime: 2019-08-02
// +----------------------------------------------------------------------
// | 接口基类
// +----------------------------------------------------------------------

namespace app\common\controller;

use DateTime;
use think\Db;
use think\Log;
use think\Cache;
use think\Response;
use think\exception\HttpResponseException;

use \Firebase\JWT\JWT;

class API
{
    protected $token    = '';   // token
    protected $userId   = 0;    // 用户 id
    protected $user;            // 用户详情
    protected $userType;        // 用户类型
    protected $request;         // Request 类
    protected $header   = [];   // 默认 HTTP Header


    public function __construct()
    {
        $this->request = request();
        $this->limit();     // 限速
        $this->userInit();  // 初始化
        $this->record();
    }


    /**
     * 用户信息初始化
     * @return void
     * @author ngtwewy < 62006464@qq.com >
     * @since  2019-07-31
     */
    private function userInit()
    {
        // 请求不包含 token，退出
        $token = request()->header('Authorization');
        if (empty($token)) { return; }
        
        // 请求包含 token，如果 token 错误退出
        $key = config('?jwt_key') ? config('jwt_key') : 'default_key';
        try {
            $data   = JWT::decode($token, $key, array('HS256'));
        } catch (\Throwable $th) {
            return;
        }
        $data = (array)$data;

        // 检查 token 中 user_id 是否有效
        $user = db()->query("SELECT * FROM tb_user WHERE id=?", [$data['user_id']]);
        if(!$user){ return; }

        // 初始化信息
        $this->token    = $token;
        $this->user     = $user[0];
        $this->userId   = $user[0]['id'];
        $this->userType = $user[0]['type'];
    }


    /**
     * 获取当前登录用户的 user_id，self::$userId == 0 是未登录
     * @return int
     */
    public function getUserId()
    {
        if ( $this->userId == 0 ) {
            $this->json(['message'=>'用户没有登录'], 401);
        }
        return $this->userId;
    }


    /**
     * 输出JSON
     * @return void
     * @author ngtwewy < 62006464@qq.com >
     * @since  2019-07-31
     */
    public function json($data = [], $code = 200, $header = [], $options = [])
    {
        $header = array_merge($this->header, $header);
        if(config('is_pack') == true ){
            $res['code'] = $code;
            $res['message'] = isset($data['message']) ? $data['message'] : '';
            $res['data'] = $data;
            $response = Response::create($res, 'json', 200, $header, $options);
            throw new HttpResponseException($response);
            return;
        }
        $response = Response::create($data, 'json', $code, $header, $options);
        throw new HttpResponseException($response);
    }


    /**
     * 获取前端发来的请求，两部分组成
     * @return void
     * @author ngtwewy < 62006464@qq.com >
     * @since  2020-02-21
     */
    public function getJson()
    {
        $request = $_REQUEST;
        $json = json_decode(file_get_contents("php://input"), true);
        if($json){
            $request = array_merge($request, $json);
        }
        return $request;
    }


    /**
     * 记录访问信息
     *
     * @return void
     * @author: ngtwewy < 62006464@qq.com >
     * @link:   http://restfulapi.cn
     * @date:   2018-11-12 20:02
     */
    private function record()
    {
        // $user_mark   user_id 或 IP地址
        $user_mark  = $this->userId ? $this->userId : $_SERVER["REMOTE_ADDR"];
        $api_mark   = $this->request->method().'-'.$this->request->module().'-'.$this->request->controller().'-'.$this->request->action();
        $api_mark   = strtolower($api_mark);
        // 获取缓存标记  例如：127.0.0.1-get-article-article-index
        $cache_mark = $user_mark."-".$api_mark;
        Log::record($cache_mark,'notice');
    }


    /**
     * API 限速
     * 参考： https://javascript.net.cn/article?id=623
     * @return void
     * @author ngtwewy < 62006464@qq.com >
     * @since  2019-11-04
     */
    private function limit()
    {
        if(config('is_rate_limit') == false){
            return;
        }

        // $user_mark   user_id 或 IP地址
        $user_mark  = $this->userId ? $this->userId : $_SERVER["REMOTE_ADDR"];
        $api_mark   = $this->request->method().'-'.$this->request->module().'-'.$this->request->controller().'-'.$this->request->action();
        $api_mark   = strtolower($api_mark);
        // echo $api_mark."\n";
        // 获取缓存标记  例如：127.0.0.1-get-article-article-index
        $cache_mark = $user_mark."-".$api_mark;

        // 最大的访问量
        $max_tokens = config($api_mark) ? config($api_mark) : config('rate_limit'); 
        $interval   = config("rate_limit_time"); // 间隔时间，单位秒

        $store_tokens   = 0; // 令牌数
        $cache          = Cache::get($cache_mark);
        if($cache === false){
            $store_tokens   = $max_tokens - 1;
            $reset_time     = time() + $interval; 
            $DateTime       = new Datetime();
            $DateTime->setTimestamp($reset_time);
            Cache::set( $cache_mark, $store_tokens."-".$reset_time, $DateTime );
        } else {
            $cache_arr      = explode('-', $cache);
            $store_tokens   = $cache_arr[0];
            $reset_time     = $cache_arr[1];
            $DateTime       = new Datetime();
            $DateTime->setTimestamp($reset_time);
            // 如果 Token Bucket > 0，自减
            if($store_tokens > 0){
                Cache::set( $cache_mark, --$store_tokens.'-'.$reset_time, $DateTime );
            }
        }

        $this->header["X-RateLimit-Limit"]      = $max_tokens;
        $this->header["X-RateLimit-Remaining"]  = $store_tokens;
        $this->header["X-RateLimit-Reset"]      = $reset_time - time();
        
        if($store_tokens <= 0){
            $this->json([], 403, $this->header);
        }
        return;
    }




}
