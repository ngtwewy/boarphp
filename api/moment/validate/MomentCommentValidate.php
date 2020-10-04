<?php
// +----------------------------------------------------------------------
// | Copyright (c) 2018 http://www.restfulapi.cn All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: ngtwewy <62006464@qq.com> 
// +----------------------------------------------------------------------
// | DateTime: 2018-08-21 15:24:03
// +----------------------------------------------------------------------

namespace api\moment\validate;

use think\Validate;

class MomentCommentValidate extends Validate
{
    protected $rule = [
        'user_id'       =>  'require',
	    'to_user_id'    =>  'require',
        'moment_id'     =>  'require',
        'content'       =>  'require',
        'status'        =>  'require'
    ];
    
    protected $message = [
        'content.require'    =>  '请添加评论内容',
    ];


}
