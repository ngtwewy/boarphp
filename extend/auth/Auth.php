<?php
// +----------------------------------------------------------------------
// | Copyright (c) 2018 http://restfulapi.cn All rights reserved.
// +----------------------------------------------------------------------
// | Author: ngtwewy <62006464@qq.com> 
// +----------------------------------------------------------------------
// | DateTime: 2018-12-02
// +----------------------------------------------------------------------



/**
 * Auth 权限认证类
 * 
 * 功能介绍：
 * 1. 对规则名称进行认证。
 *    $auth=new Auth();  $auth->check('规则名称','用户id')
 *    $auth=new Auth();  $auth->check('规则1,规则2','用户id'）
 * 2. 一个用户只能属于一个角色。
 * 3. 需要三个表，用户表，用户角色表，权限规则表。


CREATE TABLE `tp_user_role` (
  `id` mediumint(8) unsigned NOT NULL AUTO_INCREMENT,
  `name` char(100) NOT NULL DEFAULT '' COMMENT '角色名称',
  `status` tinyint(1) NOT NULL DEFAULT '1' COMMENT '0，禁用；1，正常；',
  `rules` char(80) NOT NULL DEFAULT '' COMMENT '规则ID：2,3,4',
  `description` varchar(255) DEFAULT NULL COMMENT '规则描述',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=9 DEFAULT CHARSET=utf8;

CREATE TABLE `tp_user_rule` (
  `id` mediumint(8) unsigned NOT NULL AUTO_INCREMENT,
  `name` char(80) NOT NULL DEFAULT '' COMMENT '规则名',
  `title` char(20) NOT NULL DEFAULT '' COMMENT '规则标题',
  `type` tinyint(1) NOT NULL DEFAULT '1',
  `status` tinyint(1) NOT NULL DEFAULT '1' COMMENT '状态：0，禁用， 1，正常',
  `condition` char(100) NOT NULL DEFAULT '',
  PRIMARY KEY (`id`),
  UNIQUE KEY `name` (`name`)
) ENGINE=MyISAM AUTO_INCREMENT=5 DEFAULT CHARSET=utf8;
 */


namespace auth;

use think\Db;
use think\Config;
use think\Request;

class Auth
{
    //默认配置
    protected $config = [
        'auth_on'           => 1, // 权限开关
        'auth_role'         => 'user_role', // 用户组数据表名
        'auth_rule'         => 'user_rule', // 权限规则表
        'auth_user'         => 'user', // 用户信息表
    ];


    /**
     * 类架构函数
     * Auth constructor.
     */
    public function __construct()
    {
        //可设置配置项 auth, 此配置项为数组。
        if ($auth = Config::get('auth')) {
            $this->config = array_merge($this->config, $auth);
        }
    }


    /**
     * 检查权限
     * @param [type] $name
     * @param [type] $user_id
     * @return void
     * @author ngtwewy < 62006464@qq.com >
     * @since  2018-12-02
     */
    public function check($name, $user_id)
    {
        if (!$this->config['auth_on']) {
            return true;
        }
        $user = Db::name("user")
                ->alias('u')
                ->join("user_role r","u.type=r.id")
                ->where('u.id',$user_id)
                ->find();
        //如果角色被禁用
        if($user['status']==0){ return false; }

        //用户拥有的规则
        $role_rules = Db::name("user_rule")->whereIn('id', $user['rules'])->column("name");
        $test_rules = explode(",", $name);
        if(array_diff($test_rules, $role_rules) ){
            return false;
        }else{
            return true;
        }
    }


    /**
     * 获取用户的用户组
     * @param int $user_id
     * @return void
     * @author ngtwewy < 62006464@qq.com >
     * @since  2018-12-02
     */
    public function getRole($user_id)
    {
        $user = Db::name("user")
                ->alias('u')
                ->join("user_role r","u.type=r.id")
                ->field("r.name")
                ->where('u.id',$user_id)
                ->find();
        return array_values($user);
    }


    /**
     * 获取权限列表
     * @param [type] $user_id
     * @return void
     * @author ngtwewy < 62006464@qq.com >
     * @since  2018-12-02
     */
    public function getAuthList($user_id)
    {
        $user = Db::name("user")
                ->alias('u')
                ->join("user_role r","u.type=r.id")
                ->where('u.id',$user_id)
                ->find();
        //用户拥有的规则
        $user_rules = Db::name("user_rule")->whereIn('id', $user['rules'])->column("name");
        return $user_rules;
    }



}