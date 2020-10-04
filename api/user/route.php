<?php
// +----------------------------------------------------------------------
// | Copyright (c) 2018 http://www.restfulapi.cn All rights reserved.
// +----------------------------------------------------------------------
// | Author: ngtwewy <62006464@qq.com> 
// +----------------------------------------------------------------------
// | DateTime: 2018-11-16
// +----------------------------------------------------------------------

use think\Route;


// 修改密码
Route::patch("user/password$", "user/User/updatePassword");

// 修改手机号
Route::put("user/:id/mobile","user/User/updateMobile");
Route::put("user/mobile","user/User/updateMobile");

// 修改邮箱
Route::put("user/:id/email","user/User/updateEmail");
Route::put("user/email","user/User/updateEmail");

// 获取用户信息 
Route::get("user/:id","user/User/read");

// 修改用户信息 
Route::put("user/:id","user/User/edit");
