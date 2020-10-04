<?php
// +----------------------------------------------------------------------
// | Copyright (c) 2018 http://www.restfulapi.cn All rights reserved.
// +----------------------------------------------------------------------
// | Author: ngtwewy <62006464@qq.com> 
// +----------------------------------------------------------------------
// | DateTime: 2018-10-30
// +----------------------------------------------------------------------

namespace app\item\controller;

use think\Db;
use think\Loader;
use think\Validate;
use think\Config;
use think\Controller;

use app\common\controller\API;
use app\item\model\ItemComment AS ItemCommentModel;

class ItemComment extends API
{
    public function __construct()
    {
        parent::__construct();
    }

    //资源评论列表
    /**
    * @api {get} /api/items/:id/comments 获取资源评论列表
    * @apiVersion 3.1.0
    * @apiName 获取资源评论列表
    * @apiGroup 资源管理-评论
    * @apiParam {String} page	        选填，页码
    * @apiParam {String} per_page	    选填，每页条数，默认20
    */
    public function index()
    {
        //参数
        $params     = $this->request->get();
        //查询条件
        $where      = [];
        $order      = [];

        //页码
        $page       = isset($params['page']) ? intval($params['page']) : 1;
        //每页条数
        $per_page   = Config::get("per_page");
        isset($params['per_page']) && $per_page = intval($params['per_page']);
        $start      = ($page - 1) * $per_page; 
        //排序
        if( isset($params['sortby']) && isset($params['order']) ){
            $order[$params['sortby']] = $params['order'];
        }

        $where['is_show'] = 1;

        $ItemCommentModel = new ItemCommentModel();
        $comments = $ItemCommentModel
                    ->alias("i")
                    ->join('user u','i.user_id = u.id')
                    ->field('i.*, u.nickname, u.avatar')
                    ->where($where)
                    ->order($order)
                    ->limit($start, $per_page)
                    ->select();
        foreach ($comments as $k => $v) {
            $comments[$k]['avatar'] = \get_image_url($v['avatar']);
        }
        
        $count = db('item_comment')->where($where)->count();
        
        $response['count']  = $count;
        $response['list']   = $comments;
        if(sizeof($comments) == 0 ){
            return $this->json($response, 404);
        }

        return $this->json($response, 200);
    }


    /**
    * @api {post} /api/items/:id/comments 添加资源评论
    * @apiVersion 3.1.0
    * @apiName 添加资源评论
    * @apiGroup 资源管理-评论
    * @apiParam {String} :id 	必填，文章ID
    * @apiParam {String} :content 	必填，评论内容
    */
    public function add(){
        //获取数据
        $data['user_id']        = $this->getUserId();
        $data['item_id']        = input('param.id');
        $data['content']        = trim(input('content') );
        $data['create_time']    = time();
        $data['is_show']        = 1;
        input('parent_id') && $data['parent_id'] = input('parent_id');

        //验证数据
        $validate = Loader::validate('ItemComment');
        if (!$validate->scene('add')->check($data)) {
            return $this->json(["message"=>$validate->getError()], 401);
        }
        //验证资源ID是否存在
        $res = Db::name('item')->where(['id'=>$data['item_id']])->find();
        if(!$res){
            return $this->json(["message"=>"资源ID不存在"], 404);
        }
        //验证评论ID是否存在
        if(isset($data['parent_id']) ){
            $parent_comment = Db::name("item_comment")->where(['id'=>$data['parent_id']])->find();
            if(!$parent_comment){
                return $this->json(["message"=>"评论失败"], 500);
            }
        }
        
        //处理数据
        $res = Db::name("item_comment")->insert($data);
        if($res){
            $comment_id         = Db::name("item_comment")->getLastInsID();
            $comments           = Db::name("item_comment")->where(['id'=>$comment_id])->find();
            $comments['user']   = $this->user;
            isset($data['parent_id']) && $comments['to_user']= get_user_by_id($parent_comment['user_id']);
            return $this->json($comments, 200);
        }else{
            return $this->json(["message"=>"评论失败"], 500);
        }
    }

    /**
     * 删除评论
     *
     * @return void
     * @author: ngtwewy < 62006464@qq.com >
     * @link:   http://www.restfulapi.cn
     * @date:   2018-10-30 21:28
     */
    /**
    * @api {delete} /api/items/:item_id/comments/:comment_id 删除资源评论
    * @apiVersion 3.1.0
    * @apiName 删除资源评论
    * @apiGroup 资源管理-评论
    * @apiHeader {String} Authorization 用户授权token
    * @apiParam {String} :item_id 	    必填，文章ID
    * @apiParam {String} :comment_id 	必填，评论ID
    */
    public function delete()
    {
        $comment_id = input("param.id");
        $ItemCommentModel = new ItemCommentModel();

        $res = $ItemCommentModel->where(['id'=>$comment_id])->find();
        if(!$res){
            return $this->json(["message"=>"没有id"], 404);
        }

        $res = $ItemCommentModel::destroy($comment_id);
        if($res){
            return $this->json(["message"=>"删除成功"], 204);
        }
        die();


    }


    


    

}