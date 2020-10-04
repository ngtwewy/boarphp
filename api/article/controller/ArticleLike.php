<?php
// +----------------------------------------------------------------------
// | Copyright (c) 2020 http://restfulapi.cn All rights reserved.
// +----------------------------------------------------------------------
// | Author: ngtwewy <62006464@qq.com> 
// +----------------------------------------------------------------------
// | DateTime: 2020-03-07
// +----------------------------------------------------------------------

namespace app\article\controller;

use think\Db;
use think\Loader;
use think\Validate;
use think\Config;

use app\common\controller\API;

class ArticleLike extends API
{
    /**
    * @api {get} /api/articles/like 我喜欢过的文章
    * @apiVersion 3.1.0
    * @apiName 我喜欢过的文章
    * @apiGroup 文章接口-喜欢
    * @apiHeader {String} Authorization 用户授权token
    * @apiParam {String} article_id 必填，文章ID
    */
    public function index()
    {
        $user_id = $this->getUserId();
        $sql = "SELECT 
                    al.article_id,a.title,a.category_id,ac.title AS category_name,al.create_time
                FROM tb_article_like AS al
                    LEFT JOIN tb_article AS a ON al.article_id=a.id 
                    LEFT JOIN tb_article_category AS ac ON a.category_id=ac.id
                WHERE al.user_id=:user_id
                ORDER BY create_time DESC
            ";
        $res = db()->query($sql, ['user_id'=>$user_id]);
        $this->json($res, 200);
    }


    /**
    * @api {post} /api/articles/:id/like 喜欢一篇文章
    * @apiVersion 3.1.0
    * @apiName 喜欢一篇文章
    * @apiGroup 文章接口-喜欢
    * @apiHeader {String} Authorization 用户授权token
    * @apiParam {String} id 必填，文章ID
    */
    public function add(){
        // 检查文章是否存在
        $id = input("?id") ? input("id") : 0;
        $res = Db::name('article')->where('id', $id)->find();
        if(!$res){
            return $this->json([], 404);
        }
        // 检查是否喜欢过
        $res = Db::name('article_like')
                ->where('user_id', $this->getUserId())
                ->where('article_id', $id)
                ->find();
        if($res){
            return $this->json('', 201);
        }
        // 如果没有喜欢过，添加喜欢记录
        $data['article_id']     = $id;
        $data['user_id']        = $this->getUserId();
        $data['create_time']    = time();
        $res = Db::name('article_like')->insert($data);
        if($res){
            $this->json('', 201);
        } else {
            $this->json(['message'=>'喜欢失败，请稍后再试'], 500);
        }
    }


    /**
    * @api {delete} /api/articles/:id/like 取消喜欢一篇文章
    * @apiVersion 3.1.0
    * @apiName 取消喜欢一篇文章
    * @apiGroup 文章接口-喜欢
    * @apiHeader {String} Authorization 用户授权token
    * @apiParam {String} id 必填，文章ID
    */
    public function delete()
    {
        $user_id = $this->getUserId();

        $id = input("?id") ? input("id") : 0;
        $res = Db::name('article')->where('id', $id)->find();
        if(!$res){
            return $this->json(['message'=>'需要取消的文章不存在'], 404);
        }

        Db::name('article_like')
                ->where('user_id', $user_id)
                ->where('article_id', $id)
                ->delete();
        return $this->json(['message'=>'成功取消喜欢'], 200);
    }


    

}