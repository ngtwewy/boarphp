<?php
// +----------------------------------------------------------------------
// | Copyright (c) 2018 http://www.restfulapi.cn All rights reserved.
// +----------------------------------------------------------------------
// | Author: ngtwewy <62006464@qq.com> 
// +----------------------------------------------------------------------
// | DateTime: 2018-10-28
// +----------------------------------------------------------------------

namespace app\item\validate;

use think\Validate;

class Item extends Validate
{
    protected $rule = [
        'id'            => 'require|integer',
        'user_id'       => 'require|integer',
        'category_id'   => 'require|integer',
        'name'          => 'require|length:1,255',
        'description'   => 'max:255',
        'content'       => 'require|max:6000',
        'list_order'    => 'require|integer',
        'create_time'   => 'require',
        'is_show'       => 'require|in:0,1'
    ];

    protected $scene = [
        'add'   => ['user_id','category_id','name','description','content','list_order','is_show'],
        'edit'  => ['id','user_id','category_id','name','description','content','list_order','is_show'],
    ];

}