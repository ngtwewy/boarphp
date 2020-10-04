<?php
// +----------------------------------------------------------------------
// | Copyright (c) 2018 http://restfulapi.cn All rights reserved.
// +----------------------------------------------------------------------
// | Author: ngtwewy <62006464@qq.com> 
// +----------------------------------------------------------------------
// | DateTime: 2018-12-17
// +----------------------------------------------------------------------


namespace app\index\controller;

use think\Config;
use think\Db;
use app\common\controller\API;

class Index extends API
{
    public function index()
    {
        $data['system_title'] = Config::get('system_title');
        $data['documentation_url']  = Config::get('documentation_url');
        $this->json($data, 200);
    }
    
}
