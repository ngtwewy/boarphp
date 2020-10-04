<?php
// +----------------------------------------------------------------------
// | Copyright (c) 2018 http://restfulapi.cn All rights reserved.
// +----------------------------------------------------------------------
// | Author: ngtwewy <62006464@qq.com> 
// +----------------------------------------------------------------------
// | DateTime: 2018-12-20
// +----------------------------------------------------------------------

namespace app\moment\controller;

use think\Db;
use think\Validate;
use think\Loader;
use think\Config;

use app\common\controller\Base;
use app\moment\model\Moment as MomentModel;
use app\moment\model\MomentComment as MomentCommentModel;

class MomentComment extends API
{
    public function __construct(MomentModel $momentModel, MomentCommentModel $momentCommentModel)
    {
        parent::__construct();
        $this->momentModel = $momentModel;
        $this->momentCommentModel = $momentCommentModel;
    }


    /**
     * 评论列表
     *
     * @return void
     * @author: ngtwewy < 62006464@qq.com >
     * @link:   http://restfulapi.cn
     * @date:   2018-08-21 18:48
     */
    public function index()
    {
        $id                 = request()->route('id');
        $params             = $this->request->get();
        $page               = isset($params['page']) ? intval($params['page']) : 1;

        $per_page  = Config::get("comments_per_page");
        input("?get.per_page") && $per_page = input("get.per_page");
        $start              = ($page - 1) * $per_page;

        $list = Db::name("moment_comment")
                    ->where('is_show', 1)
                    ->where('moment_id', $id)
                    ->limit($start, $per_page)
                    ->order("create_time", "desc")
                    ->order("id", "desc")
                    ->select();

        $this->success('朋友圈留言请求成功!', ['list'=>$list]);
    }


    /**
     * 发布评论
     *
     * @return void
     * @author: ngtwewy < 62006464@qq.com >
     * @link:   http://www.restfulapi.cn
     * @date:   2018-08-21 15:21
     */
    public function add()
    {
        $data   = $this->request->post();
        $userId = $this->getUserId();

        if(isset($data['moment_id'])){
            $res = DB::name("moment")->where(['id'=>$data['moment_id']])->find();
            if(!$res){
                $this->error("无效的朋友圈id");
            }
        }

        if(isset($data['parent_id']) ){
            $res = DB::name("moment_comment")->where(['id'=>$data['parent_id']])->find();
            if(!$res){
                $this->error("无效评论id");
            }
        }

        $data['create_time']    = time();
        $data['user_id']        = $userId;
        $data['to_user_id']     = $res['user_id'];
        $data['status']         = 1;

        //验证数据
        $validate = Loader::validate('MomentComment');
        if (!$validate->scene('add')->check($data)) {
            $this->error($validate->getError());
        }

        $this->momentCommentModel->data($data);
        $res = $this->momentCommentModel->allowField(true)->save();
        if($res){
            $map['id'] = $userId = $this->getUserId();
            $user = Db::name("user")->where($map)->field("id,nickname,avatar")->find();
            $comments = array_merge($user, $data);
            $comments['avatar'] = \get_image_url($comments['avatar']);
            $this->success('评论成功', $comments);
        }else{
            $this->error('评论失败');
        }
    }


    /**
     * 删除评论
     *
     * @return void
     * @author: ngtwewy < 62006464@qq.com >
     * @link:   http://www.restfulapi.cn
     * @date:   2018-08-21 17:52
     */
    public function delete()
    {
        $id     = request()->route('id');
        $userId = $this->getUserId();

        $data['delete_time'] = time();

        $map['id']      = $id;
        $map['user_id'] = $userId;
        $res = $this->momentCommentModel->save($data, $map);
        if($res){
            $this->success("评论删除成功"); 
        }else{
            $this->error("评论删除失败"); 
        }
    }






}