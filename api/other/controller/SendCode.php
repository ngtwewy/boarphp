<?php 
// +----------------------------------------------------------------------
// | Copyright (c) 2018 http://www.restfulapi.cn All rights reserved.
// +----------------------------------------------------------------------
// | Author: ngtwewy <62006464@qq.com> 
// +----------------------------------------------------------------------
// | DateTime: 2018-09-27
// +----------------------------------------------------------------------
// | Description: 发送短信验证码
// +----------------------------------------------------------------------

namespace app\other\controller;

use think\Validate;
use think\Config;
use think\Db;
use think\Cache;

use app\common\controller\API;

class SendCode extends API
{
    /**
     * 发送验证码
     *
     * @return void
     * @author: ngtwewy < 62006464@qq.com >
     * @link:   http://www.restfulapi.cn
     * @date:   2018-09-27 03:47
     */
    /**
    * @api {post} /api/sendcode 发送短信验证码
    * @apiVersion 3.1.0
    * @apiName 发送短信验证码
    * @apiGroup 验证码
    * @apiDescription 发送短信验证码
    * @apiParam {String} mobile 手机号
    * @apiParam {String} length 数字验证码长度，范围1-10的数字，默认是6
    */
    public function send()
    {
        $data = request()->param();
        $validate = new Validate([
            'mobile'  => 'require|length:1,50',
            'length'  => 'length:1,50',
        ]);
        if (!$validate->check($data)) {
            $this->json(['message'=>$validate->getError()], 400);
        }

        // 生成数字验证码
        $code = $this->code(input('length') );

        // 检测发送次数
        if($this->isAllowed($data['mobile']) == false){
            $response['message'] = "已经发了 ".config("code")["limit_per_day"]." 个验证码，请明天再试";
            $this->json($response, 429);
        }

        // 不发送验证码，api返回的json包含验证码
        if(Config::get('code')['is_send'] == false){
            usleep(200000);
            $this->record($data['mobile'], $code);
            $this->json(['message'=>'验证码未真实发送，验证码：'.$code, 'data'=>$code], 200);
        }

        // 判断账户类型，发送邮件或者手机验证码
        if(strlen($data['mobile']) == 11 ){ // 手机
            $this->sendSMS($data['mobile'], $code);
        } else {
            $this->json(['message'=>'请填写正确的账户'], 400);
        }
    }


    /**
     * 检查验证码是否合法
     * @return void
     * @author ngtwewy < 62006464@qq.com >
     * @since  2019-12-14
     */
    /**
    * @api {post} /api/checkcode 检查验证码
    * @apiVersion 3.1.0
    * @apiName 检查验证码是否合法
    * @apiGroup 验证码
    * @apiParam {String} account 接收验证码的账户，邮箱地址或者手机号
    * @apiParam {String} code 要检查的验证码
    */
    public function checkCode()
    {
        $data = request()->param();
        $validate = new Validate([
            'account' => 'require|length:1,100',
            'code'  => 'require|length:1,10'
        ]);
        if (!$validate->check($data)) {
            $this->json(['message'=>$validate->getError()], 400);
        }

        $res = Db::name("verification_code")
                    ->where("account", $data['account'])
                    ->where("code", $data['code'])
                    ->find();
        if($res && $res['expire_time'] > time() && $res['is_used']!=1){
            $this->json(['message'=>'验证码正常'], 200);
        }else{
            $this->json(['message'=>'验证码不存或已过期'], 404);
        }
        return;
    }



    /**
     * 发送短信
     *
     * @return void
     * @author: ngtwewy < 62006464@qq.com >
     * @link:   http://restfulapi.cn
     * @date:   2018-09-27 02:40
     */
    private function sendSMS($mobile, $code)
    {
        if( !check_mobile($mobile) ){
            $this->json(['message'=>'请输入正确的手机号'], 400);
        }
        $yunpian = new \sms\Yunpian();
        $res     = $yunpian->sendSMS($mobile, $code, Config::get('sms'));
        if($res['message']==0){
            $this->record($mobile, $code);
            $this->json(['message'=>'短信验证码发送成功'], 200);
        }else{
            $this->json(['message'=>$res['message']], 500);
        }
    }

    

    /**
     * 生成验证码
     *
     * @param int $length 1-10
     * @return int
     * @author: ngtwewy < 62006464@qq.com >
     * @link:   http://www.restfulapi.cn
     * @date:   2018-09-27 01:29
     */
    private function code($length)
    {
        if($length < 1 || $length > 10){
            $length = 6;
        }
        $start  = pow(10, $length-1);
        $end    = pow(10, $length)-1;
        $code   = rand($start, $end);
        return $code;        
    }



    /**
     * 记录验证码信息
     *
     * @param [type] $account
     * @param [type] $code
     * @return void
     * @author: ngtwewy < 62006464@qq.com >
     * @link:   http://restfulapi.cn
     * @date:   2018-09-27 03:54
     */
    private function record($account, $code)
    {
        $data['account']     = $account;
        $data['code']        = $code;
        $data['create_time'] = time();
        $data['expire_time'] = time() + Config::get('code')['expire_minute'] * 60;
        Db::name('verification_code')->insert($data);
    }



    /**
     * 检查是否超过限制
     * @return boolean
     * @author ngtwewy < 62006464@qq.com >
     * @since  2019-08-03
     */
    private function isAllowed($account)
    {
        $times = Cache::get($account);
        if(!$times){
            Cache::set($account, 1, 60*60*24);
        }
        // 如果真实发送， 并且超过数量， 则限制发送
        if($times >= config("code")['limit_per_day'] && config("code")['is_send']==true){
            return false;
        } else {
            Cache::inc($account);
            return true;
        }
    }













}


