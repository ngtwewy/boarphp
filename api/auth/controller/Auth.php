<?php
// +----------------------------------------------------------------------
// | Copyright (c) 2020 http://restfulapi.cn All rights reserved.
// +----------------------------------------------------------------------
// | Author: ngtwewy <62006464@qq.com> 
// +----------------------------------------------------------------------
// | DateTime: 2020-02-20
// +----------------------------------------------------------------------

namespace app\auth\controller;

use think\Db;
use think\Validate;

use app\common\controller\API;
use Exception;
use \Firebase\JWT\JWT;

class Auth extends API
{

    /**
     * 用户登录
     * @return void
     * @author ngtwewy < 62006464@qq.com >
     * @since  2020-02-20
     */
    /**
    * @api {post} /api/auth/token 用户登录
    * @apiVersion 3.1.0
    * @apiName 用户登录
    * @apiGroup 用户相关
    * @apiErrorExample {json} Error-Response:
    *     HTTP/1.1 404 Not Found
    *     {
    *       "error": "UserNotFound"
    *     }
    * @apiParam {String} account 必填，账户 手机或邮箱
    * @apiParam {String} password 必填，密码
    *
    */
    public function signIn()
    {
        $data = $this->getJson();
        
        // 验证参数
        $validate = new Validate([
            'account'       => 'require|length:6,50',
            'password'      => 'require|length:6,50'
        ]);
        if(!$validate->check($data)) {
            $res['error'] = $validate->getError();
            $this->json($res, 401);
        }

        // 检查用户是否存在
        $sql = "SELECT  * FROM tb_user WHERE password= ? AND mobile = ? OR email = ?";
        $res = db()->query($sql, [get_password_md5($data['password']), $data['account'], $data['account']]);
        if (!$res) {
            $this->json(['error' => "用户名或密码错误"], 401);
        }
        $user = $res[0];

        // 权限检查
        $this->checkUser($user);

        // 返回数据
        unset($user['password']);
        $user['avatar'] = $user['avatar'] ? get_image_url($user['avatar']) : '';
        $response['token']  = $this->createToken($user['id']);
        $response['user']   = $user;
        return $this->json($response, 200);
    }



    /**
     * 注册用户
     * 注册时，可以是空密码，这样只能用验证码登录
     * @return void
     * @author ngtwewy < 62006464@qq.com >
     * @since  2018-11-16
     */
    /**
    * @api {post} /api/user 注册用户
    * @apiVersion 3.1.0
    * @apiName 注册用户
    * @apiGroup 用户相关
    * @apiDescription 用户注册接口，注册成功后会返回 token，已注册用户也会返回token。也就是说，已注册用户，可以使用此接口获取token登录。
    * 这个接口可以直接使用账户和密码登录，不需要短信或邮件验证码。
    *
    * 如果不使用密码，想使用短信验证码直接登录，可以使用注册接口，短信验证成功以后会返回 Token。
    * @apiParam {String} account    必填，账户
    * @apiParam {String} nickname   选填，昵称，后端会生成昵称
    * @apiParam {String} password   选填，密码，用户只能使用短信验证码登录
    * @apiParam {String} verification_code 必填，短信验证码
    */
    public function signUp()
    {
        $data = $this->getJson();

        // 验证数据
        $validate = new Validate([
            'account'           => 'require|length:6,50',
            'nickname'          => 'length:1,20',
            'password'          => 'length:6,50',
            'verification_code' => 'require|length:2,25'
        ]);
        if(!$validate->check($data)) {
            return $this->json(['error'=>$validate->getError()], 400);
        }
        // account 必须是手机号或者邮箱号
        if (!Validate::is($data['account'], 'email') && !check_mobile($data['account'])) {
            return $this->json(['error'=>"account 必须是手机号或者邮箱号"], 400);
        }

        // 检查短信或邮件验证码是否正确
        if( !verification_code($data['account'], $data['verification_code'])){
            return $this->json(['error'=>'验证码错误'], 401);
        }

        // 检查用户
        $sql = "SELECT  * FROM tb_user WHERE mobile = ? OR email = ?";
        $res = db()->query($sql, [$data['account'], $data['account']]);
        if (!$res) {    // 1，账户不存在，创建账户；
            if (Validate::is($data['account'], 'email')) {
                $data['email'] = $data['account'];
            } else {
                $data['mobile'] = $data['account'];
            }
            $data['password']    = isset($data['password']) ? get_password_md5($data['password']) : '';
            $data['create_time'] = time();
            $data['login_time']  = time();
            $user_id    = Db::name('user')->strict(false)->insertGetId($data);
            $user       = Db::name('user')->where('id', $user_id)->find();
        } else {        // 2，账户存在，登录
            $user_id = $res[0]['id'];
            $user    = $res[0];
        }

        // 检查用户权限
        $this->checkUser($user);

        // 返回信息
        unset($user['password']);
        $user['avatar'] = $user['avatar'] ? get_image_url($user['avatar']) : '';
        $response['token'] = $this->createToken($user_id);
        $response['user']  = $user;
        return $this->json($response, 200);
    }


    
    /**
     * 生成 JWT Token
     * @param [type] $user_id
     * @return string token
     * @author ngtwewy < 62006464@qq.com >
     * @since  2020-02-20
     */
    public function createToken($user_id)
    {
        $time                    = time();
        $payload['iat']          = $time;        // 签发时间
        $payload['exp']          = $time + 60*1000;   // 过期时间
        $payload['nbf']          = $time;        // 生效时间
        $payload['user_id']      = $user_id;  // 用户 ID
        $key    = config('?jwt_key') ? config('jwt_key') : 'default_key';
        $token  = JWT::encode($payload, $key);
        return $token;
    }



    /**
     * 检查用户相关权限
     * @return void
     * @author ngtwewy < 62006464@qq.com >
     * @since  2019-08-20
     */
    private function checkUser($user)
    {
        if($user['is_black'] == 1) {
            $res['message'] = "该账户被拉入了黑名单";
            return $this->json($res, 403);
        }
        if($user['is_active'] == 0){
            $res['message'] = "账户还没有通过验证";
            return $this->json($res, 403);
        }
    }




    
    
}
