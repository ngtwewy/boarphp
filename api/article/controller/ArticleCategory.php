<?php
// +----------------------------------------------------------------------
// | Copyright (c) 2019 http://restfulapi.cn All rights reserved.
// +----------------------------------------------------------------------
// | Author: ngtwewy <62006464@qq.com> 
// +----------------------------------------------------------------------
// | DateTime: 2019-08-02
// +----------------------------------------------------------------------


namespace app\article\controller;

use think\Db;
use think\Validate;
use think\Config;
use think\Controller;

use app\common\controller\API;

class ArticleCategory extends API
{
    public function __construct()
    {
        parent::__construct();
    }


    /**
    * @api {get} /api/articles/categories 获取文章分类列表
    * @apiVersion 3.1.0
    * @apiName 获取文章分类列表
    * @apiGroup 文章接口-分类
    */
    public function index()
    {
        $sql = '
                SELECT 
                    ac.*,
                    (SELECT COUNT(*) FROM tb_article WHERE category_id=ac.id) AS article_counter
                FROM 
                    tb_article_category AS ac
                ORDER BY list_order DESC, create_time DESC
            ';
        $res = Db::query($sql);

        // 处理数据
        foreach ($res as $k => $v) {
            $res[$k]['thumbnail'] = get_image_url($res[$k]['thumbnail']);
        }
        // if(sizeof($articles) == 0 ){
        //     return $this->json($articles, 404);
        // }
        return $this->json($res, 200);
    }


    /**
    * @api {get} /api/articles/categories/:id 获取一个分类详情
    * @apiVersion 3.1.0
    * @apiName 获取一个分类详情
    * @apiGroup 文章接口-分类
    */
    public function read()
    {
        $id = input("?id") ? input('id') : 0;

        $sql = '
                SELECT 
                    ac.*,
                    (SELECT COUNT(*) FROM tb_article WHERE category_id=ac.id) AS article_counter
                FROM 
                    tb_article_category AS ac
                WHERE ac.id=?
            ';
        $res = Db::query($sql,[$id]);
        if(sizeof($res) == 0 ){
            return $this->json($res, 404);
        }

        $category = $res[0];
        $category['thumbnail'] = get_image_url($category['thumbnail']);
        
        return $this->json($category, 200);
    }
    

    

}