<?php 
// +----------------------------------------------------------------------
// | Copyright (c) 2018 http://www.restfulapi.cn All rights reserved.
// +----------------------------------------------------------------------
// | Author: ngtwewy <62006464@qq.com> 
// +----------------------------------------------------------------------
// | DateTime: 2018-09-25
// +----------------------------------------------------------------------

use think\Route;

Route::post("/user$", "auth/Auth/signUp");      // 注册用户
Route::post('/auth/token', 'auth/Auth/signIn'); // 用户登录



