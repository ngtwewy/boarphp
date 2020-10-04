<?php
// +----------------------------------------------------------------------
// | Copyright (c) 2018 http://www.restfulapi.cn All rights reserved.
// +----------------------------------------------------------------------
// | Author: ngtwewy <62006464@qq.com> 
// +----------------------------------------------------------------------
// | DateTime: 2018-09-25
// +----------------------------------------------------------------------

use think\Db;
use think\Config;

/**
 * helper
 */
function p($arg)
{
    header("Content-type: text/html; charset=utf-8");
    echo "<pre>";
    print_r($arg);
}


/**
 * 返回PDO对象
 * @return PDO
 * @author ngtwewy < 62006464@qq.com >
 * @since  2019-06-23
 */
function getPDO(){
    $dsn        = 'mysql:dbname='.config('database')['database'].';host='.config('database')['hostname'];
    $username   = config('database')['username'];
    $password   = config('database')['password'];

    try {
        $dbh = new PDO($dsn, $username, $password);
        $dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    } catch (PDOException $e) {
        // echo 'Connection failed: ' . $e->getMessage();
        throw new \think\exception\HttpException(500, $e->getMessage() );
        return false;
    }
    return $dbh;
}


/**
 * 通过用户 id 获取用户信息
 *
 * @param [type] $user_id
 * @return void
 * @author: ngtwewy < 62006464@qq.com >
 * @link:   http://www.restfulapi.cn
 * @date:   2018-09-25 22:27
 */
function get_user_by_id($user_id, $field=['id','nickname','avatar'])
{
    $user = Db::name('user')->field($field)->where("id",$user_id)->find();
    return $user;
}

/**
 * 获取图片完整地址
 *
 * @param [type] $url
 * @return string
 * @author: ngtwewy < 62006464@qq.com >
 * @link:   http://www.restfulapi.cn
 * @date:   2018-09-25 22:17
 */
function get_image_url($url){
    if(empty($url) ){
        return "";
        // return config("static_url")."/static/images/no-thumbnail.svg";
    }
    return config("static_url")."/uploads/images/".$url;
}

/**
 * 获取 OSS url
 * @param [type] $url
 * @return void
 * @author ngtwewy < 62006464@qq.com >
 * @since  2020-04-21
 */
function get_static_url($url)
{
    if(empty($url) ){
        return "";
    }
    return config("static_url")."/uploads/audio/".$url;
}


/**
 * 修改富文本编辑器内容的图片地址，把相对地址改成绝对地址
 * @param [type] $content
 * @return void
 * @author ngtwewy < 62006464@qq.com >
 * @since  2019-03-14
 */
function replace_content_url($content)
{
    $content = str_replace('src="/uploads','src="'.config("static_url").'/uploads',$content);
    return $content;
}




/**
 * 检查手机号是否合法
 *
 * @param [type] $num
 * @return boolean
 * @author: ngtwewy < 62006464@qq.com >
 * @link:   http://www.restfulapi.cn
 * @date:   2018-09-25 22:27
 */
function check_mobile($num){
    if( preg_match('/(^(13\d|15[^4\D]|17[013678]|18\d)\d{8})$/', $num) ){
        return true;
    }else{
        return false;
    }
}

/**
 * 获取加密后的密码
 *
 * @param string $password
 * @return void
 * @author: ngtwewy < 62006464@qq.com >
 * @link:   http://www.restfulapi.cn
 * @date:   2018-09-25 23:34
 */
function get_password_md5($password){
    return md5(Config::get('salt').$password);
}


/**
 * 判断验证码是否正确
 *
 * @param string $account
 * @param string $code
 * @return void
 */
function verification_code($account, $code)
{
    //验证码是否存在
    $res = Db::name("verification_code")
                ->where('account', $account)
                ->where('code', $code)
                ->order("id desc")
                ->find();
    if(!$res){ return false; }

    //检查是否超时
    if( time()-$res['expire_time'] > 0 ){
        return false;
    }
    return true;
}



/**
 * 返回处理过的用户信息
 * 这个函数用来处理返回给前端的用户信息，处理了图片链接，删除了敏感数据
 * @param [type] $user_id
 * @return void
 * @author ngtwewy < 62006464@qq.com >
 * @since  2019-01-19
 */
function get_user_info($user_id){
    $user = Db::name("user")
        ->field('id,nickname,avatar,signature,gender,birthday,address')
        ->where("id", $user_id)->find();
    if(!$user){
        return [];
    }
    //处理返回的用户信息
    $user['avatar'] = get_image_url($user['avatar']);
    unset($user['password']);

    return $user;
}


/**
 * 字段过滤
 * {
 *      "list_order": "1",
 *      "is_show": "1",
 *      "user#id": "1",
 *      "user#nickname": "林深雾122223",
 * }
 * 生成
 * {
 *      "list_order": "1",
 *      "is_show": "1",
 *      "user": {
 *          "id": "1",
 *          "nickname": "林深雾122223",
 *      }
 * }
 * @param [type] $arr
 * @param [type] $flag
 * @return void
 * @author ngtwewy < 62006464@qq.com >
 * @since  2020-02-22
 */
function field_filter($arr, $flag){
    foreach ($arr as $k => $v) {
        $field = explode($flag,$k);
        if(sizeof($field) > 1){
            $arr[$field[0]][$field[1]] = $v;
            unset($arr[$k]);
        }
    }
    return $arr;
}

