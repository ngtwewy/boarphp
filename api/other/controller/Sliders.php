<?php
// +----------------------------------------------------------------------
// | Copyright (c) 2020 http://restfulapi.cn All rights reserved.
// +----------------------------------------------------------------------
// | Author: ngtwewy <62006464@qq.com> 
// +----------------------------------------------------------------------
// | DateTime: 2020-05-09
// +----------------------------------------------------------------------
// | Description: 幻灯片列表
// +----------------------------------------------------------------------

namespace app\other\controller;

use think\Controller;
use think\Validate;
use think\Config;
use think\Db;
use think\Image;

use app\common\controller\API;


class Sliders extends API
{
    /**
     * 获取幻灯片
     *
     * @return void
     * @author: ngtwewy < 62006464@qq.com >
     * @link:   http://restfulapi.cn
     * @date:   2020-05-14 00:01
     */
    /**
    * @api {post} /api/slider 获取幻灯片
    * @apiVersion 3.1.0
    * @apiName 获取幻灯片
    * @apiGroup 通用接口
    * @apiDescription 通过幻灯 ID 获取幻灯片
    * @apiParam {Integer} id 通过幻灯 ID
    */
    public function index()
    {
        $data = input();
        $validate = new Validate([
            'id'  => 'require|max:25',
        ]);
        if (!$validate->check($data)) {
            $this->json(['error'=>$validate->getError()], 400);
        }

        $res = Db::name('slider')->where('id', $data['id'])->find();
        if(!$res){
            return $this->json('没有找到幻灯片或没有权限', 404);
        }

        $res['more'] = json_decode($res['more'], JSON_UNESCAPED_UNICODE);
        if(isset($res['more']['images'])){
            foreach ($res['more']['images'] as $key => $value) {
                $res['more']['images'][$key]['url'] = get_image_url($value['url']);
            }
        }
        $this->json($res, 200);
    }
    





}

