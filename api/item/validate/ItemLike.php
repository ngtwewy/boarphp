<?php
// +----------------------------------------------------------------------
// | Copyright (c) 2018 http://www.restfulapi.cn All rights reserved.
// +----------------------------------------------------------------------
// | Author: ngtwewy <62006464@qq.com> 
// +----------------------------------------------------------------------
// | DateTime: 2018-11-08
// +----------------------------------------------------------------------

namespace app\item\validate;

use think\Validate;

class ItemLike extends Validate
{
    protected $rule = [
        'id'            => 'require|integer',
        'item_id'       => 'require|integer',
        'user_id'       => 'require|integer',
        'create_time'   => 'require'
    ];

    protected $scene = [
        'add'       => ['item_id','user_id','create_time'],
        'delete'    => ['item_id','user_id']
    ];

    // protected $message = [
    //     'item_id.require' => '资源ID不能为空',
    //     'item_id.integer' => '请输入数字资源ID'
    // ];

}