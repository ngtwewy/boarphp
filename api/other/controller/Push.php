<?php
// +----------------------------------------------------------------------
// | Copyright (c) 2019 http://restfulapi.cn All rights reserved.
// +----------------------------------------------------------------------
// | Author: ngtwewy <62006464@qq.com> 
// +----------------------------------------------------------------------
// | DateTime: 2019-08-02
// +----------------------------------------------------------------------
// | 推送基类 - unipush 个推
// +----------------------------------------------------------------------

namespace app\common\controller;

require_once(EXTEND_PATH . 'getui/' . 'IGt.Push.php');
require_once(EXTEND_PATH . 'getui/' . 'igetui/IGt.AppMessage.php');
require_once(EXTEND_PATH . 'getui/' . 'igetui/IGt.TagMessage.php');
require_once(EXTEND_PATH . 'getui/' . 'igetui/IGt.APNPayload.php');
require_once(EXTEND_PATH . 'getui/' . 'igetui/template/IGt.BaseTemplate.php');
require_once(EXTEND_PATH . 'getui/' . 'IGt.Batch.php');
require_once(EXTEND_PATH . 'getui/' . 'igetui/utils/AppConditions.php');
require_once(EXTEND_PATH . 'getui/' . 'igetui/template/notify/IGt.Notify.php');
require_once(EXTEND_PATH . 'getui/' . 'igetui/IGt.MultiMedia.php');
require_once(EXTEND_PATH . 'getui/' . 'payload/VOIPPayload.php');
require_once(EXTEND_PATH . 'getui/' . 'igetui/template/IGt.RevokeTemplate.php');
require_once(EXTEND_PATH . 'getui/' . 'igetui/template/IGt.StartActivityTemplate.php');

use think\Controller;
use think\Config;
use think\Cache;

class Push
{
    private $template;

    public function __construct()
    {
        define('APPKEY',        config('push')['getui']['APPKEY']);
        define('APPID',         config('push')['getui']['APPID']);
        define('MASTERSECRET',  config('push')['getui']['MASTERSECRET']);
        define('CID',           config('push')['getui']['CID']);
        define('HOST',          config('push')['getui']['HOST']);
    }


    //所有推送接口均支持四个消息模板，依次为通知弹框下载模板，通知链接模板，通知透传模板，透传模板
    //注：IOS离线推送需通过APN进行转发，需填写pushInfo字段，目前仅不支持通知弹框下载功能
  
    /**
     * 通知透传模板
     * @return void
     * @author ngtwewy < 62006464@qq.com >
     * @since  2019-12-10
     */
    public function IGtNotificationTemplateDemo($title, $content, $logo_url){
        $this->template =  new \IGtNotificationTemplate();
        $this->template->set_appId(APPID);//应用appid
        $this->template->set_appkey(APPKEY);//应用appkey
        $this->template->set_transmissionType(1);//透传消息类型
        $this->template->set_transmissionContent("测试离线");//透传内容
        $this->template->set_title($title);//通知栏标题
        $this->template->set_text($content);//通知栏内容
        // $this->template->set_logo("");//通知栏logo
        $this->template->set_logoURL($logo_url); 
        // $this->template->set_isRing(true);//是否响铃
        // $this->template->set_isVibrate(true);//是否震动
        $this->template->set_isClearable(true);//通知栏是否可清除
        // $this->template->set_notifyId(123456789);
        // $this->template->set_channel("set_channel");
        // $this->template->set_channelName("set_channelName");
        // $this->template->set_channelLevel(3);
        //$this->template->set_duration(BEGINTIME,ENDTIME); //设置ANDROID客户端在此时间区间内展示消息
        return $this->template;
    }


    /**
     * 执行单推
     * @return void
     * @author ngtwewy < 62006464@qq.com >
     * @since  2019-12-10
     */
    public function toSingle($cid, $title, $content, $logo_url)
    {
        $this->template =  $this->IGtNotificationTemplateDemo($title, $content, $logo_url);
        try {
            try {
                $igt = new \IGeTui(null, APPKEY, MASTERSECRET);
            } catch (\Exception $e) {
                echo $e->getMessage();
            }
            $this->pushMessageToSingleForTemplate($igt, $cid);
        } catch (\Exception $e) {
            echo $e->getMessage();
        }
    }

    

    //setSmsInfo接口
    public function pushMessageToSingleForTemplate($igt, $cid)
    {
        $sound_d = new \Sound();
        $sound_d->set_name("set_name");
        $sound_d->set_critical(1);

        $message = new \IGtSingleMessage();
        $message->set_isOffline(true);
        $message->set_offlineExpireTime(60 * 60 * 1000);
        $message->set_data($this->template);
        $message->set_pushNetWorkType(0);
        $target = new \IGtTarget();
        $target->set_appId(APPID);
        $target->set_clientId($cid);

        try {
            $ret = $igt->pushMessageToSingle($message, $target);
            var_dump($ret);
        }catch (\Exception $e){
            echo $e->getMessage();
            //        $requstId = $e->getRequestId();
            //        $ret = $igt->pushMessageToSingle($message,$target,$requstId);
            //        var_dump($ret);
        }
    }


    






















}
