<?php
// +----------------------------------------------------------------------
// | Copyright (c) 2018 http://www.restfulapi.cn All rights reserved.
// +----------------------------------------------------------------------
// | Author: ngtwewy <62006464@qq.com> 
// +----------------------------------------------------------------------
// | DateTime: 2018-10-29
// +----------------------------------------------------------------------

namespace app\item\model;

use think\Model;
use traits\model\SoftDelete;

class ItemComment extends Model
{
    use SoftDelete;
    protected $deleteTime = 'delete_time';

}