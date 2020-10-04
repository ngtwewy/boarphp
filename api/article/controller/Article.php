<?php
// +----------------------------------------------------------------------
// | Copyright (c) 2018 http://www.restfulapi.cn All rights reserved.
// +----------------------------------------------------------------------
// | Author: ngtwewy <62006464@qq.com> 
// +----------------------------------------------------------------------
// | DateTime: 2018-10-22
// +----------------------------------------------------------------------

namespace app\article\controller;

use think\Db;
use think\Validate;
use think\Config;
use think\Loader;

use app\common\controller\API;
use Exception;

class Article extends API
{
    public function __construct()
    {
        parent::__construct();
    }

    /**
    * @api {get} /api/articles 获取文章列表
    * @apiVersion 3.1.0
    * @apiName 文章列表
    * @apiGroup 文章接口
    * @apiParam {String} page	        选填，页码
    * @apiParam {String} per_page	    选填，每页条数，默认20
    * @apiParam {String} category_id	选填，分类ID，留空的话，返回所有文章
    * @apiParam {String} order	        选填，desc或者asc，默认desc
    * @apiParam {String} sortby	        选填，排序字段，默认为id, 允许字段 id,list_order,create_time
    */
    public function index()
    {
        // 页码
        $page        = input('page') ? input('page/d') : 1;
        // 每页条数
        $per_page    = input('per_page') ? input('get.per_page/d') : Config::get("per_page");
        // 开始条数
        $start       = ($page - 1) * $per_page; 
        // 分类
        $category_id = input('?category_id') ? input('category_id/d') : "";
        // 排序 只能使用自己定义的值
        $order       = input('order') == 'asc' ? 'asc' : 'desc';
        $sortby      = input('sortby') ? input('sortby') : 'id';
        $allow_field = ['id','list_order','create_time']; // 允许排序的字段
        if(in_array($sortby, $allow_field)){
            $key = array_search($sortby,$allow_field);
            $sortby = $allow_field[$key];
        }

        // 绑定参数
        $para_arr   = [];
        $para_arr[] = [':start', $start, \PDO::PARAM_INT];
        $para_arr[] = [':per_page', $per_page, \PDO::PARAM_INT];

        $sql = "
            SELECT 
                a.*,
                (SELECT COUNT(*) FROM tb_article_comment AS ac WHERE a.id=ac.article_id) AS comment_counter,
                (SELECT COUNT(*) FROM tb_article_like AS al WHERE a.id=al.article_id) AS like_counter,
                u.id AS 'user#id',
                u.nickname AS 'user#nickname',
                u.avatar AS 'user#avatar',
                u.signature AS 'user#signature'
            FROM tb_article AS a 
                LEFT JOIN tb_user AS u ON a.user_id = u.id
                LEFT JOIN tb_article_category AS ac ON ac.id = a.category_id
        ";
        $sql .= " WHERE a.is_show=1";

        if($category_id){
            $sql .= " AND category_id=:category_id";
            $para_arr[] = [':category_id', $category_id, \PDO::PARAM_INT];
        }

        $sql .= " ORDER BY $sortby $order";
        $sql .= " LIMIT :start,:per_page";

        try{
            $dbh = getPDO();
            $sth = $dbh->prepare($sql);
            foreach ($para_arr as $v) {
                $sth->bindValue($v[0],$v[1],$v[2]);
            }
            $sth->execute();
            $articles = $sth->fetchAll(\PDO::FETCH_ASSOC);
        } catch (\PDOException $e){
            return $this->json(['error'=>$e->getMessage()], 500);
        }
        
        //处理数据
        foreach ($articles as $k => $v) {
            $articles[$k]['thumbnail']      = get_image_url($articles[$k]['thumbnail']);
            $articles[$k]['user#avatar']    = get_image_url($articles[$k]['user#avatar']);
            $articles[$k]['description']    = $v['description'] ? $v['description'] : mb_substr(strip_tags($v['content']),0,100 );
            $articles[$k]                   = field_filter($articles[$k],'#');
        }

        // 获取记录条数
        $sql = "SELECT COUNT(*) AS count FROM tb_article WHERE is_show=1";
        $category_id && $sql .= " AND category_id=:category_id";
        $sth = $dbh->prepare($sql);
        $category_id && $sth->bindValue(':category_id', $category_id, \PDO::PARAM_INT);
        $sth->execute();
        $res = $sth->fetchAll(\PDO::FETCH_ASSOC);
        $count = $res[0]['count'];

        // 返回数据
        $response['count']  = $count;
        $response['list']   = $articles;
        if(sizeof($articles) == 0 ){
            return $this->json($response, 404);
        }
        return $this->json($response, 200);
    }
    

    /**
    * @api {get} /api/articles/:id 获取一篇文章
    * @apiVersion 3.1.0
    * @apiName 获取文章
    * @apiGroup 文章接口
    * @apiParam {String} id 文章ID
    */
    /**
     * 获取文章详情
     * @return void
     * @author ngtwewy < 62006464@qq.com >
     * @since  2019-01-23
     */
    public function read(){
        $map['id'] = request()->route("id");

        // 获取文章
        $sql = "
                SELECT 
                    a.*,
                    (SELECT COUNT(*) FROM tb_article_comment AS ac WHERE a.id=ac.article_id) AS comment_counter,
                    (SELECT COUNT(*) FROM tb_article_like AS al WHERE a.id=al.article_id) AS like_counter
                FROM tb_article AS a
                WHERE id=:id
                AND is_show=1
            ";
        $res = Db::query($sql, $map);
        if($res){
            Db::name('article')->where('id', $map['id'])->update(['hit_counter'=>++$res[0]['hit_counter'] ]);

            $res[0]["thumbnail"]    = str_replace("\\", "\/", $res[0]["thumbnail"]);
            $res[0]["content"]      = str_replace("\\", "\/", $res[0]["content"]);
            $res[0]["thumbnail"]    = get_image_url($res[0]["thumbnail"]);
            $res[0]["content"]      = replace_content_url($res[0]["content"]);
            $res[0]["user"]         = get_user_info($res[0]['user_id']);
            $this->json($res[0], 200);
        }else{
            $this->json('', 404);
        }
    }



    /**
     * 添加文章
     * @return void
     * @author ngtwewy < 62006464@qq.com >
     * @since  2019-08-02
     * @last 
     */
    /**
    * @api {post} /api/articles 添加一篇文章
    * @apiVersion 3.1.0
    * @apiName 添加一篇文章
    * @apiGroup 文章接口
    * @apiHeader {String} Authorization 用户授权token
    * @apiParam {String} category_id 必填，分类ID
    * @apiParam {String} name        必填，文章标题
    * @apiParam {String} content	 必填，文章内容
    * @apiParam {String} description 必填，文章描述
    * @apiParam {String} thumbnail	 选填，缩略图地址 类似20190822/1624...21588013.jpg
    */
    public function add()
    {
        $user_id = $this->getUserId();
        $arr     = $this->getJson();

        // 数据组装
        $data['category_id'] = isset($arr['category_id']) ? trim($arr['category_id']) : '';
        $data['title']        = isset($arr['name']) ? trim($arr['name']) : '';
        $data['content']     = isset($arr['content']) ? trim($arr['content']) : '';
        $data['description'] = isset($arr['description']) ? trim($arr['description']) : '';
        $data['thumbnail']   = isset($arr['thumbnail']) ? trim($arr['thumbnail']) : '';
        
        $data['create_time']    = time();
        $data['list_order']     = 99;
        $data['user_id']        = $user_id;
        $data['is_show']        = config("post_is_show") ? 1 : 0;

        // 数据验证
        $validate = Loader::validate('Article');
        if (!$validate->scene('add')->check($data)) {
            return $this->json(['error'=>$validate->getError()], 400);
        }
        // 检测分类是否存在
        $res = Db::name("article_category")->where('id', $data['category_id'])->where('is_show', 1)->find();
        if(!$res){
            $this->json(['error'=>'文章分类不存在或隐藏'], 403);
        }

        // 添加数据
        $article_id = Db::name("article")->insertGetId($data);
        if($article_id){
            $article = Db::name("article")->where('id', $article_id)->find();
            $article['thumbnail'] = get_image_url($article['thumbnail']);
            
            $user['id']         = $this->user['id'];
            $user['nickname']   = $this->user['nickname'];
            $user['avatar']     = get_image_url($this->user['avatar']);
            $article['user']    = $user;

            $this->json($article, 201);
        }else{
            $this->json(['error'=>'文章添加失败'], 500);
        }

        return $this->json($data, 200);
    }


    
    /**
     * 编辑文章
     * @return void
     * @author ngtwewy < 62006464@qq.com >
     * @since  2019-08-02
     */
    /**
    * @api {put} /api/articles/:id 更新一篇文章
    * @apiVersion 3.1.0
    * @apiName 更新一篇文章
    * @apiGroup 文章接口
    * @apiHeader {String} Authorization 用户授权token
    * @apiParam {String} category_id 必填，分类ID
    * @apiParam {String} name        必填，文章标题
    * @apiParam {String} content	 必填，文章内容
    * @apiParam {String} description 必填，文章描述
    * @apiParam {String} thumbnail	 选填，缩略图地址 类似20190822/1624...21588013.jpg
    */
    public function edit()
    {
        $user_id    = $this->getUserId();
        $arr        = $this->getJson();

        // 检测，1、文章是否存在 2、是否是自己的文章
        $article_id = input('?param.id') ? input('param.id') : 0;
        $res = Db::name("article")->where('id', $article_id)->where('user_id', $user_id)->find();
        if(!$res){
            return $this->json(['error'=>'文章不存在或者没有权限'], 404);
        }

        // 数据组装
        $data['category_id'] = isset($arr['category_id']) ? trim($arr['category_id']) : '';
        $data['title']        = isset($arr['name']) ? trim($arr['name']) : '';
        $data['content']     = isset($arr['content']) ? trim($arr['content']) : '';
        $data['description'] = isset($arr['description']) ? trim($arr['description']) : '';
        $data['thumbnail']   = isset($arr['thumbnail']) ? trim($arr['thumbnail']) : '';
        
        $data['create_time']    = time();
        $data['list_order']     = 99;
        $data['user_id']        = $user_id;
        $data['is_show']        = config("post_is_show") ? 1 : 0;

        // 数据验证
        $validate = Loader::validate('Article');
        if (!$validate->scene('add')->check($data)) {
            return $this->json(['error'=>$validate->getError()], 400);
        }
        // 检测分类是否存在
        $res = Db::name("article_category")->where('id', $data['category_id'])->where('is_show', 1)->find();
        if(!$res){
            $this->json(['error'=>'文章分类不存在或隐藏'], 403);
        }

        // 修改文章
        $res = Db::name("article")->where('id',$article_id)->update($data);
        if($res){
            $article = Db::name("article")->where('id', $article_id)->find();
            $article['thumbnail'] = get_image_url($article['thumbnail']);
            
            $user['id']         = $this->user['id'];
            $user['nickname']   = $this->user['nickname'];
            $user['avatar']     = get_image_url($this->user['avatar']);
            $article['user']    = $user;

            $this->json($article, 200);
        }else{
            $this->json(['error'=>'文章添加失败'], 500);
        }

        return $this->json($data, 200);
    }
    


    /**
     * 删除文章
     * @return void
     * @author ngtwewy < 62006464@qq.com >
     * @since  2019-08-02
     */
    /**
    * @api {delete} /api/articles/:article_id 删除一篇文章
    * @apiVersion 3.1.0
    * @apiName 删除一篇文章
    * @apiGroup 文章接口
    * @apiHeader {String} Authorization 用户授权token
    * @apiParam {String} article_id 必填，文章ID
    */
    public function delete()
    {
        $user_id    = $this->getUserId();
        $article_id = \input('?param.id') ? \input('param.id') : 0;

        $res = Db::name("article")
                ->where('id', $article_id)
                ->where('user_id', $user_id)
                ->find();
        if(!$res){
            return $this->json(['error'=>'文章不存在或没有权限'], 403);
        }

        $res = Db::name("article")->where('id', $article_id)->delete();
        if($res >= 0){
            $this->json("", 204);
        }
    }


}