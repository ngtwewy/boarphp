<?php
// +----------------------------------------------------------------------
// | Copyright (c) 2012-2018 http://javascript.net.cn All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: Lee <62006464@qq.com> 
// +----------------------------------------------------------------------
// | DateTime: 2018-07-28 01:43:56
// +----------------------------------------------------------------------

namespace app\moment\validate;

use think\Validate;

class MomentValidate extends Validate
{
    protected $rule = [
        'post_title'        =>  'require',
	    'post_content'      =>  'require',
	    'categories'        =>  'require'
    ];
    
    protected $message = [
        'post_title.require'    =>  '文章标题不能为空',
	    'post_content.require'  =>  '内容不能为空',
	    'categories.require'    =>  '文章分类不能为空'
    ];

    protected $scene = [
        'article'  => [ 'post_title' , 'post_content' , 'categories' ],
        'page' => ['post_title']
    ];
}
