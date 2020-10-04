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

class Item extends API
{
    public function __construct()
    {
        parent::__construct();
    }

    /**
    * @api {get} /api/items 获取资源列表
    * @apiVersion 3.1.0
    * @apiName 获取资源列表
    * @apiGroup 资源管理
    * @apiParam {String} page	        选填，页码
    * @apiParam {String} user_id        选填，用户ID，不填写的话返回所有资源
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
        // 用户 ID
        $user_id = input('user_id') ? input('user_id/d') : 0;

        // 绑定参数
        $para_arr   = [];
        $para_arr[] = [':start', $start, \PDO::PARAM_INT];
        $para_arr[] = [':per_page', $per_page, \PDO::PARAM_INT];

        $sql = "
            SELECT 
                a.*,
                (SELECT COUNT(*) FROM tb_item_comment AS ac WHERE a.id=ac.item_id) AS comment_counter,
                (SELECT COUNT(*) FROM tb_item_like AS al WHERE a.id=al.item_id) AS like_counter,
                u.id AS 'user#id',
                u.nickname AS 'user#nickname',
                u.avatar AS 'user#avatar',
                u.signature AS 'user#signature'
            FROM tb_item AS a LEFT JOIN tb_user AS u
            ON a.user_id = u.id
        ";
        $sql .= " WHERE is_show=1";

        if($category_id){
            $sql .= " AND category_id=:category_id";
            $para_arr[] = [':category_id', $category_id, \PDO::PARAM_INT];
        }
        if($user_id){
            $sql .= " AND a.user_id=:user_id";
            $para_arr[] = [':user_id', $user_id, \PDO::PARAM_INT];
        }

        $sql .= " ORDER BY $sortby $order";
        $sql .= " LIMIT :start,:per_page";

        $dbh = getPDO();
        $sth = $dbh->prepare($sql);
        foreach ($para_arr as $v) {
            $sth->bindValue($v[0],$v[1],$v[2]);
        }
        $sth->execute();
        $articles = $sth->fetchAll(\PDO::FETCH_ASSOC);
        
        //处理数据
        foreach ($articles as $k => $v) {
            $articles[$k]['thumbnail'] = get_image_url($articles[$k]['thumbnail']);
            $articles[$k]['url'] = get_static_url($articles[$k]['url']);
            $articles[$k]['user#avatar'] = get_image_url($articles[$k]['user#avatar']);
            $articles[$k] = field_filter($articles[$k],'#');
        }

        // 获取记录条数
        $sql = "SELECT COUNT(*) AS count FROM tb_item WHERE is_show=1";
        $category_id && $sql .= " AND category_id=:category_id";
        $user_id && $sql .= " AND user_id=:user_id";
        $sth = $dbh->prepare($sql);
        $category_id && $sth->bindValue(':category_id', $category_id, \PDO::PARAM_INT);
        $user_id && $sth->bindValue(':user_id', $user_id, \PDO::PARAM_INT);
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
     * 获取资源详情
     * @return void
     * @author ngtwewy < 62006464@qq.com >
     * @since  2019-01-23
     */
    // public function read(){
    //     $map['id'] = input("param.id");
    //     $sql = "
    //             SELECT 
    //                 *,
    //                 (SELECT count(*)
    //                 FROM tb_item_comment
    //                 WHERE tb_item_comment.item_id=tb_item.id) AS comments 
    //             FROM tb_item 
    //             WHERE id=:id
    //         ";
    //     $res = Db::query($sql, $map);
    //     if($res){
    //         return $this->json($res[0], 200);
    //     }else{
    //         return $this->json([],404);
    //     }
    // }
   


    /**
    * @api {get} /api/items/:id 获取一条资源
    * @apiVersion 3.1.0
    * @apiName 获取一条资源
    * @apiGroup 资源管理
    * @apiParam {String} id 资源ID
    */
    // /**
    //  * 获取文章详情
    //  * @return void
    //  * @author ngtwewy < 62006464@qq.com >
    //  * @since  2019-01-23
    //  */
    public function read(){
        $map['id'] = request()->route("id");

        // 获取文章
        $sql = "
                SELECT 
                    a.*,
                    (SELECT COUNT(*) FROM tb_item_comment AS ac WHERE a.id=ac.item_id) AS comment_counter,
                    (SELECT COUNT(*) FROM tb_item_like AS al WHERE a.id=al.item_id) AS like_counter
                FROM tb_item AS a
                WHERE id=:id
                AND is_show=1
            ";
        $res = Db::query($sql, $map);
        if($res){
            Db::name('item')->where('id', $map['id'])->update(['hit_counter'=>++$res[0]['hit_counter'] ]);

            $res[0]["thumbnail"]    = str_replace("\\", "\/", $res[0]["thumbnail"]);
            $res[0]["content"]      = str_replace("\\", "\/", $res[0]["content"]);
            $res[0]["thumbnail"]    = get_image_url($res[0]["thumbnail"]);
            $res[0]["url"]          = get_static_url($res[0]["url"]);
            $res[0]["content"]      = replace_content_url($res[0]["content"]);
            $res[0]["user"]         = get_user_info($res[0]['user_id']);
            $this->json($res[0], 200);
        }else{
            $this->json('', 404);
        }
    }


    

}