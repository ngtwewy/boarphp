<?php
// +----------------------------------------------------------------------
// | Copyright (c) 2018 http://www.restfulapi.cn All rights reserved.
// +----------------------------------------------------------------------
// | Author: ngtwewy <62006464@qq.com> 
// +----------------------------------------------------------------------
// | DateTime: 2018-10-30
// +----------------------------------------------------------------------

namespace app\article\controller;

use think\Db;
use think\Loader;
use think\Validate;
use think\Config;
use think\Controller;

use app\common\controller\API;
use app\article\model\ArticleComment AS ArticleCommentModel;

class ArticleComment extends API
{
    public function __construct()
    {
        parent::__construct();
    }

    /**
    * @api {get} /api/articles/:id/comments 获取文章评论列表
    * @apiVersion 3.1.0
    * @apiName 获取文章评论列表
    * @apiGroup 文章接口-评论
    * @apiParam {String} :id 	必填，文章ID
    */
    public function index()
    {
        //参数
        $params     = $this->request->get();
        
        $article_id = input("?param.id") ? input("param.id") : 0;

        //页码
        $page       = isset($params['page']) ? intval($params['page']) : 1;
        //每页条数
        $per_page   = input('?get.per_page') ? input('get.per_page/d') : 0;
        $per_page == 0 && $per_page = Config::get("per_page");
        $start      = ($page - 1) * $per_page; 

        $sql = "
                SELECT
                    ac.*, 
                    u.id AS 'user.id', 
                    u.nickname AS 'user.nickname',
                    u.avatar AS 'user.avatar'
                FROM
                    tb_article_comment AS ac
                LEFT JOIN tb_user AS u
                ON ac.user_id = u.id
                WHERE ac.article_id = :article_id
                ORDER BY ac.create_time DESC
            ";

        $comments = Db::query($sql, ['article_id'=>$article_id]);
        if($comments == 0){
            $this->json('',404);
        }

        
        foreach ($comments as $k => $v) {
            $comments[$k]['user.avatar'] = \get_image_url($v['user.avatar']);
            $comments[$k] = field_filter($comments[$k], 'user');
        }
        
        $this->json($comments);
    }


    /**
    * @api {post} /api/articles/:id/comments 添加文章评论
    * @apiVersion 3.1.0
    * @apiName 添加文章评论
    * @apiGroup 文章接口-评论
    * @apiParam {String} :id 	必填，文章ID
    * @apiParam {String} :content 	必填，评论内容
    */
    public function add(){
        $data = json_decode(file_get_contents("php://input"), true);

        // 获取数据
        $data['user_id']        = $this->getUserId();
        $data['article_id']     = input('param.id');
        $data['content']        = trim($data['content'] );
        $data['create_time']    = time();
        $data['is_show']        = 1;

        // 验证数据
        $validate = Loader::validate('ArticleComment');
        if (!$validate->scene('add')->check($data)) {
            $response['error']['message'] =$validate->getError();
            return $this->json($response, 400);
        }
        // 验证文章ID是否存在
        $res = Db::name('article')->where('id', $data['article_id'])->find();
        if(!$res){
            return $this->json(['error'=>'文章不存在'], 404);
        }
        // 验证评论ID是否存在
        if(isset($data['parent_id']) ){
            $parent_comment = Db::name("article_comment")->where(['id'=>$data['parent_id']])->find();
            if(!$parent_comment){
                return $this->json(['error'=>'评论ID不存在'], 404);
            }
        }
        
        // 处理数据
        $res = Db::name("article_comment")->insert($data);
        if($res){
            $comment_id         = Db::name("article_comment")->getLastInsID();
            $comments           = Db::name("article_comment")->where(['id'=>$comment_id])->find();
            $comments['user']   = get_user_info($data['user_id']);
            isset($data['parent_id']) && $comments['to_user']= get_user_by_id($parent_comment['user_id']);
            return $this->json($comments, 200);
        }else{
            return $this->json(['error'=>'评论ID不存在'], 500);
        }
    }



    /**
     * 删除评论
     * @return void
     * @author ngtwewy < 62006464@qq.com >
     * @since  2019-08-02
     */
    /**
    * @api {delete} /api/articles/:article_id/comments/:comment_id 删除评论
    * @apiVersion 3.1.0
    * @apiName 删除评论
    * @apiGroup 文章接口-评论
    * @apiHeader {String} Authorization 用户授权token
    * @apiParam {String} :article_id 	必填，文章ID
    * @apiParam {String} :comment_id 	必填，评论ID
    */
    public function delete()
    {
        $article_id = input("param.article_id");
        $comment_id = input("param.id");

        $ArticleCommentModel = new ArticleCommentModel();

        $res = $ArticleCommentModel
                ->where('article_id',$article_id)
                ->where('id',$comment_id)
                ->find();
        if(!$res){
            return $this->json(['error'=>"该评论不存在"], 404);
        }

        $res = $ArticleCommentModel::destroy($comment_id);
        if($res){
            return $this->json([], 204);
        }
    }


    


    

}