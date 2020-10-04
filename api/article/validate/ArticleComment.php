<?php
// +----------------------------------------------------------------------
// | Copyright (c) 2018 http://www.restfulapi.cn All rights reserved.
// +----------------------------------------------------------------------
// | Author: ngtwewy <62006464@qq.com> 
// +----------------------------------------------------------------------
// | DateTime: 2018-10-30
// +----------------------------------------------------------------------

namespace app\article\validate;

use think\Validate;

class ArticleComment extends Validate
{
    protected $rule = [
        'id'            => 'require|integer',
        'article_id'    => 'require|integer',
        'user_id'       => 'require|integer',
        'parent_id'     => 'require|integer',
        'content'       => 'require|length:1,255',
        'is_show'       => 'require|in:0,1'
    ];

    protected $scene = [
        'add'   => ['article_id','to_user_id','content','is_show'],
        'edit'  => ['id','article_id','to_user_id','content','is_show']
    ];

    // protected $message = [
    //     'item_id.require' => '资源ID不能为空',
    //     'item_id.integer' => '请输入数字资源ID'
    // ];

}