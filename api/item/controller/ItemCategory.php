<?php
// +----------------------------------------------------------------------
// | Copyright (c) 2018 http://www.restfulapi.cn All rights reserved.
// +----------------------------------------------------------------------
// | Author: ngtwewy <62006464@qq.com> 
// +----------------------------------------------------------------------
// | DateTime: 2018-10-22
// +----------------------------------------------------------------------

namespace app\item\controller;

use think\Db;
use think\Validate;
use think\Config;
use think\Controller;

use app\common\controller\API;
use app\item\model\Item AS ItemModel;

class ItemCategory extends API
{
    public function __construct()
    {
        parent::__construct();
    }

    /**
    * @api {get} /api/items/categories 获取资源分类
    * @apiVersion 3.1.0
    * @apiName 获取资源分类
    * @apiGroup 资源管理-分类
    */
    public function index()
    {
        $sql = '
                SELECT 
                    ic.*,
                    (SELECT COUNT(*) FROM tb_item WHERE category_id=ic.id) AS item_counter
                FROM 
                    tb_item_category AS ic
                ORDER BY list_order DESC, create_time DESC
            ';
        $res = Db::query($sql);

        // 处理数据
        foreach ($res as $k => $v) {
            $res[$k]['thumbnail'] = get_image_url($res[$k]['thumbnail']);
        }

        return $this->json($res, 200);
    }

     /**
    * @api {get} /api/items/categories/:id 获取一个分类详情
    * @apiVersion 3.1.0
    * @apiName 获取一个分类详情
    * @apiGroup 资源管理-分类
    */
    public function read()
    {
        $id = input("?id") ? input('id') : 0;

        $sql = '
                SELECT 
                    ic.*,
                    (SELECT COUNT(*) FROM tb_item WHERE category_id=ic.id) AS item_counter
                FROM 
                    tb_item_category AS ic
                WHERE ic.id=?
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