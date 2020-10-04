<?php
// +----------------------------------------------------------------------
// | Copyright (c) 2018 http://www.restfulapi.cn All rights reserved.
// +----------------------------------------------------------------------
// | Author: ngtwewy <62006464@qq.com> 
// +----------------------------------------------------------------------
// | DateTime: 2018-09-25
// +----------------------------------------------------------------------

namespace app\moment\controller;

use think\Db;
use think\Validate;
use think\Config;
use think\Controller;

use app\common\controller\API;
use app\moment\model\Moment as MomentModel;

class Moment extends API
{
    protected $momentModel;

    public function __construct(MomentModel $momentModel)
    {
        parent::__construct();
        $this->momentModel = $momentModel;
    }

    /**
     * 朋友圈列表
     *
     * @return void
     * @author: ngtwewy < 62006464@qq.com >
     * @link:   http://restfulapi.cn
     * @date:   2018-08-20 01:33
     */
    public function index()
    {
        $params             = $this->request->get();
        $page               = isset($params['page']) ? intval($params['page']) : 1;

        $arg['per_page']    = Config::get("moments_per_page");
        if( input("?get.per_page") ){
            $arg['per_page'] = input("get.per_page");
        }
        
        $arg['start']       = ($page - 1) * $arg['per_page'];

        //判断是否有 user_id 是登录用户的ID，如果如果包含，列表包含点赞等状态
        if(!isset($params['user_id'])){
            $sql = "
                SELECT 
                    m.id, m.user_id, m.content, m.create_time, m.more, m.like_counter, m.hit_counter, m.comment_counter,
                    u.nickname, u.avatar
                FROM tb_moment AS m
                    LEFT JOIN tb_moment_like AS l ON l.moment_id = m.id
                    LEFT JOIN tb_user AS u ON u.id=m.user_id
                WHERE m.is_show = 1
                ORDER BY m.id DESC
                LIMIT :start,:per_page
            ";
            $list = Db::query($sql,$arg);
        }else{
            $sql = "
                SELECT 
                    m.id, m.content, m.create_time, m.more, m.like_counter, m.hit_counter, m.comment_counter,
                    u.nickname, u.avatar,
                    l.create_time AS like_time
                FROM tb_moment AS m 
                    LEFT JOIN tb_moment_like AS l ON l.moment_id = m.id AND l.user_id = :user_id
                    LEFT JOIN tb_user AS u ON u.id=m.user_id
                WHERE m.is_show = 1
                ORDER BY m.id DESC
                LIMIT :start,:per_page
            ";
            $arg['user_id'] = intval($params['user_id']);
            $list = Db::query($sql,$arg);
        }
        
        //修改图片地址
        foreach ($list as $k => $v) {
            if($v['avatar']){
                $list[$k]['avatar'] = \get_image_url($v['avatar']);
            }else{
                $list[$k]['avatar'] = \get_image_url("avatar/avatar.jpg");
            }

            $list[$k]['more'] = json_decode($v['more'], true);
            if( !is_array($list[$k]['more']['images']) ) continue;
            foreach ($list[$k]['more']['images'] as $key => $value) {
                // p($value);
                $list[$k]['more']['images'][$key]['url'] = \get_image_url($value['url']);
                // $list[$k]['more']['images'][$key]['name'] = \get_image_url($value);
            }
        }

        $this->json(['list'=>$list]);
    }
    




    /**
     * 发布朋友圈
     *
     * @return void
     * @author: ngtwewy < 62006464@qq.com >
     * @link:   http://www.restfulapi.cn
     * @date:   2018-08-20 01:33
     */
    public function add()
    {
        $params = $this->request->post();
        $userId = $this->getUserId();

        // p($params); die();
        $validate = new Validate([
            'content'  => 'require|max:5000',
        ]);
        if (!$validate->check($params)) {
            $this->json($validate->getError(), 400);
            return;
        }

        // 处理图片
        $more = [];
        if(!empty($params['images']) && is_array($params['images']) ){
            foreach ($params['images'] as $k => $v) {
                $temp['url']        = $v;
                $temp['name']       = "";
                $more['images'][]   = $temp;
            }
            $data['more'] = json_encode($more, true);
        }

        $data['content']     = $params["content"];
        $data['create_time'] = time();
        $data['user_id']     = $userId;
        $data['status']      = 1;
        
        $res = Db::name("moment")->insert($data);
        if($res){
            $this->json('发布朋友圈成功', 201);
        }else{
            $this->json('发布朋友圈失败', 500);
        }
    }


    /**
     * 查看发布
     *
     * @return void
     * @author: ngtwewy < 62006464@qq.com >
     * @link:   http://www.restfulapi.cn
     * @date:   2018-08-20 01:33
     */
    public function read()
    {
        $id = request()->route('id');
        $map['id']          = $id;
        $map['delete_time'] = 0;

        $sql = "
        SELECT
            m.*,
            u.nickname, u.avatar
        FROM 
            tb_moment AS m,
            tb_user AS u
        WHERE
            m.id=:id
            AND m.delete_time = :delete_time
            AND m.user_id = u.id
        ";
        $res = Db::query($sql, $map);
        if($res){
            $moment = $res[0];
            $moment['avatar'] = \get_image_url($moment['avatar']);
            $this->success("success", $moment); 
        }else{
            $this->error("error", $res); 
        }
    }


    /**
     * 删除朋友圈 （软删除）
     *
     * @return void
     * @author: ngtwewy < 62006464@qq.com >
     * @link:   http://www.restfulapi.cn
     * @date:   2018-08-20 00:41
     */
    public function delete()
    {
        $id     = request()->route('id');
        $userId = $this->getUserId();

        $data['delete_time'] = time();
        $data['status']      = 1;

        $map['id']      = $id;
        $map['user_id'] = $userId;
        $res = $this->momentModel->save($data, $map);
        $this->json("删除成功", 200);
    }


    /**
     * 朋友圈点赞
     *
     * @param Type $var
     * @return void
     * @author: ngtwewy < 62006464@qq.com >
     * @link:   http://www.restfulapi.cn
     * @date:   2018-09-10 18:37
     */
    public function like()
    {
        $userId = $this->getUserId();

        $moment_id  = request()->route('id');
        $moment     = Db::name("moment")->where('id',$moment_id)->find();
        if(!$moment){ 
            return $this->error("id不存在"); 
        }

        $response = [];

        Db::startTrans();
        try{
            //1.检查是否已经点赞
            $res = Db::name("moment_like")->where('user_id',$userId)->where('moment_id',$moment_id)->find();
            if($res==null){
                //2.更新点赞记录表
                $data['user_id']     = $userId;
                $data['moment_id']   = $moment_id;
                $data['create_time'] = time();
                Db::name("moment_like")->insert($data);

                //3.更新朋友圈点赞信息
                $counter     = Db::name('moment_like')->where('moment_id', $moment_id)->count();
                Db::name('moment')->where('id',$moment_id)->update(['like_counter'=>$counter]);
                $response['like_counter'] = $counter; 
            }else{
                $response['like_counter'] = $moment['like_counter']; 
            }
            Db::commit();
        } catch (\Exception $e) {
            Db::rollback();
            return $this->json("点赞失败".$e->getMessage(), 500);
        }
        return $this->json($response, 200);
    }

    /**
     * 取消点赞
     *
     * @return boolean
     * @author: ngtwewy < 62006464@qq.com >
     * @link:   http://www.restfulapi.cn
     * @date:   2018-09-16 20:17
     */
    public function cancelLike()
    {
        $userId = $this->getUserId();

        $moment_id  = request()->route('id');
        $moment     = Db::name("moment")->where('id',$moment_id)->find();
        if(!$moment){ 
            return $this->error("id不存在"); 
        }

        $response = [];

        Db::startTrans(); 
        try{
            $map['user_id']     = $userId;
            $map['moment_id']   = $moment_id;
            Db::name("moment_like")->where($map)->delete();

            $counter = Db::name('moment_like')->where('moment_id', $moment_id)->count();
            if($counter==0){
                $counter==0;
            }else{
                $counter--;
            }
            Db::name('moment')->where('id',$moment_id)->update(['like_counter'=>$counter]);
            $response['like_counter'] = $counter;
            Db::commit();
        } catch (\Exception $e) {
            Db::rollback();
            return $this->json("取消点赞失败".$e->getMessage(), 500);
        }
        return $this->json($response, 200);
    }

}