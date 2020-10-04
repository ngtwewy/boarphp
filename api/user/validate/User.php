<?php
// +----------------------------------------------------------------------
// | Copyright (c) 2018 http://www.restfulapi.cn All rights reserved.
// +----------------------------------------------------------------------
// | Author: ngtwewy <62006464@qq.com> 
// +----------------------------------------------------------------------
// | DateTime: 2018-11-16
// +----------------------------------------------------------------------

namespace app\user\validate;

use think\Validate;

class User extends Validate
{
    protected $rule = [
        'account'   => 'require',

        'id'        => 'require|integer',
        'username'  => 'length:2,50',
        'nickname'  => 'require|length:2,30',
        'email'     => 'require|email',
        'mobile'    => 'require|integer',
        'password'  => 'length:6,30',
        'verification_code' => 'require|length:1,20',
        'device_type' => 'require|length:1,20',
        
        'gender'    => '',
        'birthday'  => '',
        'score'     => '',  //积分
        'coin'      => '',  //金币
        'balance'   => '',  //余额

        'avatar'    => '',
        'signature' => '',
        'address'   => '',
        'type'      => '',  //用户类型; 1:admin; 2:会员
    ];

    protected $scene = [
        'add'       => ['account', 'nickname', 'password', 'verification_code', 'device_type'],
        'password'  => ['account', 'password']
    ];
    
    protected $message = [
        'account.require'           => '请填写手机号或邮箱账号',
        
        'password.require'          => '请填写密码',
        'password.length'           => '密码长度必须是6-30位的字符',

        'verification_code.require' => '缺少验证码',
        'verification_code.length'  => '验证码长度必须为1-20位的字符'
    ];

}