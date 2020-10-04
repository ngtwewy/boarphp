<?php
// +----------------------------------------------------------------------
// | Copyright (c) 2018 http://restfulapi.cn All rights reserved.
// +----------------------------------------------------------------------
// | Author: ngtwewy <62006464@qq.com> 
// +----------------------------------------------------------------------
// | DateTime: 2018-11-16
// +----------------------------------------------------------------------

namespace app\user\controller;

use think\Db;
use think\Validate;
use think\Config;
use think\Controller;
use think\Loader;

use app\common\controller\API;
use app\user\model\User AS UserModel;

class User extends API
{
    /**
     * 修改密码
     * @return void
     * @author ngtwewy < 62006464@qq.com >
     * @since  2020-04-08
     */
    /**
    * @api {patch} /user/password 修改密码
    * @apiVersion 3.1.0
    * @apiName 修改密码
    * @apiGroup 用户相关
    * @apiDescription 修改密码
    * @apiParam {String} account    必填，账户
    * @apiParam {String} password   选填，密码，用户只能使用短信验证码登录
    * @apiParam {String} verification_code 必填，短信验证码
    */
    public function updatePassword()
    {
        $data = $this->getJson();
        // 验证数据
        $validate = new Validate([
            'account'           => 'require|length:6,50',
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

        // 修改用户密码
        try {
            $password = get_password_md5($data['password']);
            $res = Db::name('user');
            if( Validate::is($data['account'], 'email') ){
                $res = $res->where('email', $data['account']);
            }else{
                $res = $res->where('mobile', $data['account']);
            }
            $res = $res->setField('password', $password);
        } catch (\Exception $e){
            return $this->json(['error'=>$e->getMessage()], 500);
        }
        return $this->json(['message'=>'密码修改成功'], 200);
    }



    /**
     * 修改手机号
     * @return void
     * @author ngtwewy < 62006464@qq.com >
     * @since  2018-11-16
     */
    public function updateMobile()
    {
        $user_id = $this->getUserId();
        
        $old_code   = input("old_code");
        $new_mobile = input("new_mobile");
        $new_code   = input("new_code");

        //如果存在手机号，需要验证旧手机验证码是否正确
        if($this->user['mobile']){
            if(verification_code($this->user['mobile'], $old_code)==false){
                $this->json(['message'=>'原手机验证码不正确'], 401);
            }
        }else{
            $this->json(['message'=>'手机号不存在'], 404);
        }
        //验证新手机验证码
        if( verification_code($new_mobile, $new_code)==false ){
            p(2);
            $this->json(['message'=>'新手机验证码不正确'], 401);

        }
        //验证新手机号是否已经注册
        if(Db::name('user')->where(['mobile'=>$new_mobile])->find()){
            p(3);
            $this->json(['message'=>'该手机号已注册过'], 401);         
        }
        $user = Db::name("user")->where('id', $user_id)->update(['mobile'=>$new_mobile]);
        if($user){
            $this->json(['message'=>'手机号修改成功'], 200); 
        }else{
            $this->json(['message'=>'手机号修改失败'], 500); 
        }
    }



    /**
     * 修改用户邮箱
     * @return void
     * @author ngtwewy < 62006464@qq.com >
     * @since  2018-11-16
     */
    /**
    * @api {get} /user/:id 获取用户信息
    * @apiVersion 3.1.0
    * @apiName 获取用户信息
    * @apiGroup 用户相关
    * @apiDescription 获取用户信息
    * @apiParam {String} id 用户 ID
    */
    public function updateEmail()
    {
        $user_id = $this->getUserId();
        
        $old_code   = input("old_code");
        $new_email  = input("new_email");
        $new_code   = input("new_code");

        //如果存在手机号，需要验证旧手机验证码是否正确
        if($this->user['email']){
            if(verification_code($this->user['email'], $old_code)==false){
                $this->json(['message'=>'原邮箱验证码不正确'], 401); 
            }
        }
        //验证新手机验证码
        if( verification_code($new_email, $new_code)==false ){
            $this->json(['message'=>'新邮箱验证码不正确'], 401); 
        }
        //验证新手机号是否已经注册
        if(Db::name('user')->where(['email'=>$new_email])->find()){
            $this->json(['message'=>'该邮箱已注册过'], 401); 
        }
        $user = Db::name("user")->where('id', $user_id)->update(['email'=>$new_email]);
        if($user){
            $this->json(['message'=>'邮箱修改成功'], 200); 
        }else{
            $this->json(['message'=>'邮箱修改失败'], 200); 
        }
    }



    /**
     * 获取用户信息
     * 判断获取的是否为自己的用户信息，如果是返回所有，不是的话返回公开信息
     * @return void
     * @author ngtwewy < 62006464@qq.com >
     * @since  2018-11-16
     */
    /**
    * @api {get} /user/:id 获取用户信息
    * @apiVersion 3.1.0
    * @apiName 获取用户信息
    * @apiGroup 用户相关
    * @apiDescription 获取用户信息
    * @apiParam {String} id 用户 ID
    */
    public function read()
    {
        $route_id   = request()->route('id');
        $user       = $this->user;

        //判断获取的是否为自己的用户信息
        if($user && $user['id']==$route_id){
            $this->user['avatar'] = \get_image_url($this->user['avatar']);
            unset($this->user['password']);
            $this->json($this->user, 200);
        }

        $user = Db::name("user")->where(['id'=>$route_id])->find();
        if(!$user){
            $this->json(['message'=>'指定用户不存在'], 404);
        }
        $user_public_field = config("user_public_field");
        $data = [];
        foreach ($user_public_field as $k) {
            $data[$k] = $user[$k];
        }
        $data['avatar'] = get_image_url($data['avatar']);
        $this->json($data, 200);
    }



    /**
     * 修改用户信息
     * @return void
     * @author ngtwewy < 62006464@qq.com >
     * @since  2018-11-16
     */

    /**
    * @api {put} /user/:id 修改用户信息
    * @apiVersion 3.1.0
    * @apiName 修改用户信息
    * @apiGroup 用户相关
    * @apiDescription 修改用户信息
    * @apiParam {String} nickname 选填，昵称
    * @apiParam {String} avatar 选填，头像地址 20200407/.....jpg
    * @apiParam {String} signature 选填，签名
    * @apiParam {String} gender 选填，0 保密，1，男，2 女。
    * @apiParam {String} birthday 选填，生日。格式：2001-08-08
    * @apiParam {String} address 选填，地址
    */
    public function edit()
    {
        $arr = input();

        $user_id    = $this->getUserId();
        $route_id   = request()->route('id');
        if($user_id != $route_id){
            $this->json(['message'=>'无权限修改他人信息'], 403);
        }

        //允许该接口修改的字段
        isset($arr['nickname'])     && $data['nickname']    = $arr['nickname'];
        isset($arr['avatar'])       && $data['avatar']      = $arr['avatar'];
        isset($arr['signature'])    && $data['signature']   = $arr['signature'];
        isset($arr['gender'])       && $data['gender']      = $arr['gender'];
        isset($arr['birthday'])     && $data['birthday']    = $arr['birthday'];
        isset($arr['address'])      && $data['address']     = $arr['address'];

        $rule = [
            'nickname'  => 'length:2,30',
            'avatar'    => 'length:0,255',
            'signature' => 'length:2,255',
            'gender'    => 'in:0,1,2',
            'birthday'  => 'length:0,50|date',
            'address'   => 'length:0,255',
        ];

        $validate = new Validate($rule);
        if(!$validate->check($data) ){
            $this->json(['message'=>$validate->getError()], 400);
        }

        //修改
        $data['update_time'] = time();

        $res = Db::name("user")->where('id', $user_id)->update($data);
        if($res){
            $this->json(get_user_info($user_id), 200);
        }else{
            $this->json(['message'=>'修改失败'], 500);
        }
    }


    



}