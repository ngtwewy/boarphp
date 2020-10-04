<?php
// +----------------------------------------------------------------------
// | Copyright (c) 2018 http://www.restfulapi.cn All rights reserved.
// +----------------------------------------------------------------------
// | Author: ngtwewy <62006464@qq.com> 
// +----------------------------------------------------------------------
// | DateTime: 2018-09-27
// +----------------------------------------------------------------------

use think\Route;

Route::post("/sendcode", "other/SendCode/send"); // 发送验证码
Route::post("/sendemailcode", "other/SendEmailCode/send"); // 发送验证码
Route::post("/checkcode", "other/SendCode/checkCode"); // 检查验证码
Route::post("/images", "other/Images/upload"); // 上传图片
Route::get("/sliders", "other/Sliders/index"); //获取幻灯片
