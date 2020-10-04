<?php 
// +----------------------------------------------------------------------
// | Copyright (c) 2018 http://www.restfulapi.cn All rights reserved.
// +----------------------------------------------------------------------
// | Author: ngtwewy <62006464@qq.com> 
// +----------------------------------------------------------------------
// | DateTime: 2020-10-05
// +----------------------------------------------------------------------
// | Description: 发送邮件验证码
// +----------------------------------------------------------------------

namespace app\other\controller;

use think\Validate;
use think\Config;
use think\Db;
use think\Cache;

use app\common\controller\API;

class SendEmailCode extends API
{
    /**
     * 发送邮件验证码
     *
     * @return void
     * @author: ngtwewy < 62006464@qq.com >
     * @link:   http://www.restfulapi.cn
     * @date:   2018-09-27 03:47
     */
    /**
    * @api {post} /api/sendemailcode 发送邮件验证码
    * @apiVersion 3.1.0
    * @apiName 发送邮件验证码
    * @apiGroup 验证码
    * @apiDescription 发送邮件验证码
    * @apiParam {String} email 账户 邮箱地址
    * @apiParam {String} length 数字验证码长度，范围1-10的数字，默认是6
    */
    public function send()
    {
        $data = request()->param();
        $validate = new Validate([
            'email'  => 'require|email|length:1,100'
        ]);
        if (!$validate->check($data)) {
            $this->json(['message'=>$validate->getError()], 400);
        }

        // 生成数字验证码
        $code = $this->code(input('length') );

        // 检测发送次数
        if($this->isAllowed($data['email']) == false){
            $response['message'] = "已经发了 ".config("code")["limit_per_day"]." 个验证码，请明天再试";
            $this->json($response, 429);
        }

        // 不发送验证码，api返回的json包含验证码
        if(Config::get('code')['is_send'] == false){
            usleep(200000);
            $this->record($data['email'], $code);
            $this->json(['message'=>'验证码未真实发送，验证码：'.$code, 'data'=>$code], 200);
        }

        // 判断账户类型，发送邮件或者手机验证码
        if ( Validate::is($data['email'], 'email') ) { // 邮箱
            $this->sendEmail($data['email'], $code);
        } else {
            $this->json(['message'=>'请填写正确的账户'], 400);
        }
    }



    /**
     * 发送邮件
     *
     * @return void
     * @author: ngtwewy < 62006464@qq.com >
     * @link:   http://restfulapi.cn
     * @date:   2018-09-27 02:40
     */
    private function sendEmail($email, $code)
    {
        //获取配置
        $data = Config::get("email");
        Config::set('email', $data);

        //Load Composer's autoloader
        require '../../vendor/autoload.php';

        $mail = new \PHPMailer\PHPMailer\PHPMailer(true);
        //Server settings
        $mail->SMTPDebug    = 0;
        $mail->isSMTP();
        $mail->Host         = Config::get('email')['host'];
        $mail->SMTPAuth     = true;
        $mail->Username     = Config::get('email')['username'];
        $mail->Password     = Config::get('email')['password'];
        $mail->SMTPSecure   = 'tls'; 
        $mail->Port         = Config::get('email')['port'];
        $mail->CharSet      = "utf-8";
        //Recipients
        $mail->setFrom(Config::get('email')['username'], Config::get('email')['from'] );
        $mail->addAddress($email, '您好');
        //Content
        $mail->isHTML(true);
        $mail->Subject = str_replace('#code#', $code, Config::get('email')['subject']);
        $mail->Body    = str_replace('#code#', $code, Config::get('email')['body']);

        if($mail->send()){
            $this->record($email, $code);
            $this->json(['message'=>'邮件验证码发送成功'], 200);
        }else{
            $this->json(['message'=>$mail->ErrorInfo], 500);
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


