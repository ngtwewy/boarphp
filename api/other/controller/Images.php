<?php
// +----------------------------------------------------------------------
// | Copyright (c) 2019 http://restfulapi.cn All rights reserved.
// +----------------------------------------------------------------------
// | Author: ngtwewy <62006464@qq.com> 
// +----------------------------------------------------------------------
// | DateTime: 2019-01-13
// +----------------------------------------------------------------------


namespace app\other\controller;

use think\Controller;
use think\Validate;
use think\Config;
use think\Db;
use think\Image;

use app\common\controller\API;


class Images extends API
{

    public function upload()
    {
        $this->getUserId();

        //1 检查文件上传
        $file_error_msg['1'] = "文件大小超过了系统限制";
        $file_error_msg['2'] = "文件大小超过了 HTML 表单中 MAX_FILE_SIZE 选项指定的值";
        $file_error_msg['3'] = "文件只有部分被上传";
        $file_error_msg['4'] = "没有文件被上传";
        $file_error_msg['6'] = "找不到临时文件夹";
        $file_error_msg['7'] = "文件写入失败";
        
        if($_FILES['file']['error'] != 0){
            $this->json(['error'=>"上传失败，".$file_error_msg[ $_FILES['file']['error'] ]], 400);
        }
        $name = \pathinfo($_FILES['file']['name'],PATHINFO_EXTENSION);
        if(!$name){
            $_FILES['file']['name'] = $_FILES['file']['name'] . ".png";
        }
        // p($_FILES);die();

        //2 处理上传的文件
        $file = request()->file('file');
        $info = $file->validate(['size'=>3*1024*1024,'ext'=>'jpeg,jpg,png,gif'])->move(ROOT_PATH . 'public' . DS . 'uploads' . DS . 'images');
        if($info){
            $respones['thumbnail'] = $info->getSaveName();
            $respones['url']       = get_image_url($info->getSaveName() );
        }else{
            $this->json(['error'=>"上传失败:".$file->getError()], 500);
        }

        //3 压缩文件
        //3.1. 获取图片信息
        $file   = ROOT_PATH."public". DS ."uploads". DS ."images". DS .$info->getSaveName();
        $image  = \think\Image::open($file);

        //3.2 判断是否缩小图片
        if( input("?post.resize") && input("post.resize")=="true" ){
            $width  = input("?post.width") ? input("post.width") : 500;
            $height = input("?post.height") ? input("post.height") : 500;
            $image->thumb($width, $height)->save($file);
        }

        //3.3 压缩过大图片
        $max = 1500;
        if($image->width() > $max || $image->height() > $max){
            $width  = $image->width() > $max ? $max : $image->width();
            $height = $image->height() > $max ? $max : $image->height();
            $image->thumb($width, $height)->save($file);
        }

        //4 文件信息存储到asset中
        clearstatcache(); //清除文件状态缓存
        $data['user_id']        = $this->userId;
        $data['suffix']         = $info->getExtension();
        $data['file_name']      = $info->getFileName();
        $data['file_path']      = $info->getSaveName();
        $data['file_size']      = filesize($file); //$info->getSize();
        $data['create_time']    = time();
        $res = Db::name("asset")->insert($data);

        $this->json($respones, 201);
    }
    





}

