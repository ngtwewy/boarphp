<?php
// +----------------------------------------------------------------------
// | Copyright (c) 2018 http://www.restfulapi.cn All rights reserved.
// +----------------------------------------------------------------------
// | Author: ngtwewy <62006464@qq.com> 
// +----------------------------------------------------------------------
// | DateTime: 2018-10-31
// +----------------------------------------------------------------------

namespace app\item\controller;

use think\Db;
use think\Loader;
use think\Validate;
use think\Config;
use think\Controller;

use app\common\controller\API;
use app\item\model\ItemLike AS ItemLikeModel;

class ItemLike extends API
{
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * 喜欢
     *
     * @return void
     * @author: ngtwewy < 62006464@qq.com >
     * @link:   http://www.restfulapi.cn
     * @date:   2018-11-08 02:40
     */
    public function add(){
        //获取数据
        $data['user_id']        = $this->getUserId();
        $data['item_id']        = input('param.id');
        $data['create_time']    = time();
        //验证数据
        $validate = Loader::validate('ItemLike');
        if (!$validate->scene('add')->check($data)) {
            $response['message'] = $validate->getError();
            $this->json($response, 401);
        }
        //验证资源ID是否存在
        $res = Db::name('item')->where(['id'=>$data['item_id']])->find();
        if(!$res){
            $this->error("资源ID不存在");
        }
        //点赞数
        $like_count = Db::name("item_like")->where(['item_id'=>$data['item_id']])->count();
        //验证是否已点赞
        $like = Db::name("item_like")->where("item_id",$data['item_id'])->where("user_id", $data['user_id'])->find();
        if($like){
            $this->success("喜欢成功", $like_count);
        }

        $res = Db::name("item_like")->insert($data);
        if($res){
            $this->success("喜欢成功", ++$like_count);
        }else{
            $this->error("喜欢失败", $like_count);
        }
    }

    /**
     * 取消喜欢
     *
     * @return void
     * @author: ngtwewy < 62006464@qq.com >
     * @link:   http://www.restfulapi.cn
     * @date:   2018-11-08 02:40
     */
    public function delete()
    {
        //获取数据
        $data['user_id']        = $this->getUserId();
        $data['item_id']        = input('param.id');
  
        //验证数据
        $validate = Loader::validate('ItemLike');
        if (!$validate->scene('delete')->check($data)) {
            $this->error($validate->getError());
        }
        //验证资源ID是否存在
        $res = Db::name('item')->where(['id'=>$data['item_id']])->find();
        if(!$res){
            $this->error("资源ID不存在");
        }

        //点赞数
        $like_count = Db::name("item_like")->where(['item_id'=>$data['item_id']])->count();

        //删除点赞
        $res = Db::name("item_like")->where($data)->delete();
        if($res){
            $this->success("取消喜欢成功", --$like_count);
        }else{
            $this->error("取消喜欢失败", $like_count);
        }
    }


    


    

}