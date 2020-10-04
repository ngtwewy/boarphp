<?php
// +----------------------------------------------------------------------
// | Copyright (c) 2018 http://www.restfulapi.cn All rights reserved.
// +----------------------------------------------------------------------
// | Author: ngtwewy <62006464@qq.com> 
// +----------------------------------------------------------------------
// | DateTime: 2018-10-30
// +----------------------------------------------------------------------

namespace app\item\validate;

use think\Validate;

class ItemComment extends Validate
{
    protected $rule = [
        'id'            => 'require|integer',
        'item_id'       => 'require|integer',
        'user_id'       => 'require|integer',
        'parent_id'     => 'require|integer',
        'content'       => 'require|length:1,255',
        'is_show'       => 'require|in:0,1'
    ];

    protected $scene = [
        'add'   => ['item_id','to_user_id','content','is_show'],
        'edit'  => ['id','item_id','to_user_id','content','is_show']
    ];

    // protected $message = [
    //     'item_id.require' => '资源ID不能为空',
    //     'item_id.integer' => '请输入数字资源ID'
    // ];

}