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

class Item extends Model
{
    public function comments()
    {
        return $this->hasMany('item_comment');
    }

    public function user(){
        return $this->hasOne('user', 'id', 'user_id')->field('id,nickname,avatar');;
    }

    public function like(){
        return $this->hasOne('user', 'id', 'user_id')->field('id,nickname,avatar');;
    }

}