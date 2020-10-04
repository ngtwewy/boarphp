<?php
// +----------------------------------------------------------------------
// | Copyright (c) 2018 http://www.restfulapi.cn All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: ngtwewy <62006464@qq.com> 
// +----------------------------------------------------------------------
// | DateTime: 2018-09-20 01:21:42
// +----------------------------------------------------------------------

// [ 应用入口文件 ]

// 如果是 OPTIONS请求，立即返回相关头信息


header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Headers: Authorization, Content-Type, If-Match, If-Modified-Since, If-None-Match, If-Unmodified-Since, X-Requested-With');
header('Access-Control-Allow-Methods: GET, POST, PATCH, PUT, DELETE, OPTIONS');
header('Access-Control-Max-Age: 1728000');

if($_SERVER['REQUEST_METHOD'] == 'OPTIONS'){
    exit;
}

// 定义应用目录
define('APP_PATH', __DIR__ . '/../../api/');
// 加载框架引导文件
require __DIR__ . '/../../thinkphp/start.php';