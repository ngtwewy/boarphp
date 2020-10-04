<?php
// +----------------------------------------------------------------------
// | Copyright (c) 2018 http://www.restfulapi.cn All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: ngtwewy <62006464@qq.com> 
// +----------------------------------------------------------------------
// | DateTime: 2018-08-20 00:49:08
// +----------------------------------------------------------------------
namespace app\moment\model;

use think\Model;
use think\db\Query;

use app\user\model\User;

class Moment extends Model
{  
    // public function editMoment($data)
    // {
    //     if(!empty($data['more']) ){
    //         $data['more'] = json_encode($data['more']);
    //     }
        
    //     $fields = ['comment','content','more','status'];
    //     $res = $this->save($data, ['id' => $data["id"]]);
    //     if($res){
    //         return true;
    //     }else{
    //         return false;
    //     }
    // }

    public function comments()
    {
        return $this->hasMany('moment_comment');
    }

    public function likes()
    {
        return $this->hasMany('moment_like');
    }

}