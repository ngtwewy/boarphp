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

class ArticlePage extends API
{
    public function __construct()
    {
        parent::__construct();
    }

    /**
    * @api {get} /api/articles/pages 获取页面列表
    * @apiVersion 3.1.0
    * @apiName 获取页面列表
    * @apiGroup 文章接口-页面
    */
    public function index()
    {
        $sql = '
                SELECT 
                    *
                FROM 
                    tb_article_page
                WHERE is_show=1
                ORDER BY list_order DESC, create_time DESC
            ';
        $res = Db::query($sql);

        // 处理数据
        foreach ($res as $k => $v) {
            $res[$k]['thumbnail'] = get_image_url($res[$k]['thumbnail']);
        }
        if(sizeof($res) == 0 ){
            return $this->json($res, 404);
        }
        return $this->json($res, 200);
    }
    

    /**
    * @api {get} /api/articles/pages/:id 获取一个页面
    * @apiVersion 3.1.0
    * @apiName 获取一个页面
    * @apiGroup 文章接口-页面
    * @apiParam {String} :id 页面ID
    */
    public function read()
    {
        $page_id = input("param.id");
        
        $page = Db::name('article_page')
            ->where('id', $page_id)
            ->where('is_show', 1)
            ->find();
        if($page){
            $page['thumbnail'] = get_image_url($page['thumbnail']);
            return $this->json($page, 200);
        }else{
            return $this->json('', 404);
        }
    }
    

}