<?php
// +----------------------------------------------------------------------
// | Copyright (c) 2018 http://www.restfulapi.cn All rights reserved.
// +----------------------------------------------------------------------
// | Author: ngtwewy <62006464@qq.com> 
// +----------------------------------------------------------------------
// | DateTime: 2018-09-27
// +----------------------------------------------------------------------

namespace sms;

class Yunpian
{
    /**
     * 发送短信验证码
     *
     * @param string $mobile 手机号
     * @param string $code 验证码
     * @param string $config
     * 'sms'       => [
     *     'yunpian' => [  //yunpian.com
     *         'api_key'          => "da9a370e74f0024cddb192df5ce49123", //apikey官网后获取
     *         'template_content' => "【欢宇网络】您的验证码是#code#。如非本人操作，请忽略本短信",
     *         'template_id'      => "2196482",
     *     ]
     * ]
     * @return void
     * @author: ngtwewy < 62006464@qq.com >
     * @link:   http://www.restfulapi.cn
     * @date:   2018-09-27
     */
    public function sendSMS($mobile, $code, $config)
    {
        header("Content-Type:text/html;charset=utf-8");
        $apikey = $config['yunpian']['api_key'];
        $text   = $config['yunpian']['template_content'];;
        $tpl_id = $config['yunpian']['template_id'];
        
        $ch = curl_init();

        /* 设置验证方式 */
        curl_setopt($ch, CURLOPT_HTTPHEADER, array('Accept:text/plain;charset=utf-8',
            'Content-Type:application/x-www-form-urlencoded', 'charset=utf-8'));
        /* 设置返回结果为流 */
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        /* 设置超时时间*/
        curl_setopt($ch, CURLOPT_TIMEOUT, 10);
        /* 设置通信方式 */
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        // 取得用户信息
        $json_data = $this->get_user($ch,$apikey);
        $array = json_decode($json_data,true);

        // 发送模板短信
        // 需要对value进行编码
        $data = array(
            'tpl_id' => $tpl_id, 
            'tpl_value' => ('#code#').'='.urlencode($code), 
            'apikey' => $apikey, 
            'mobile' => $mobile
        );

        $json_data  = $this->tpl_send($ch,$data);
        $array      = json_decode($json_data,true);
        if($array['code']==0){
            $result = ['error'   => 0,'message' => '验证码发送成功'];
        }else{
            $result = ['error'   => 1,'message' => $array['msg']];
        }

        return $result;
    }

    //获得账户
    public function get_user($ch,$apikey){
        curl_setopt ($ch, CURLOPT_URL, 'https://sms.yunpian.com/v2/user/get.json');
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query(array('apikey' => $apikey)));
        $result = curl_exec($ch);
        $error = curl_error($ch);
        $this->checkErr($result,$error);
        return $result;
    }

    public function tpl_send($ch,$data){
        curl_setopt ($ch, CURLOPT_URL,
            'https://sms.yunpian.com/v2/sms/tpl_single_send.json');
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));
        $result = curl_exec($ch);
        $error = curl_error($ch);
        $this->checkErr($result,$error);
        return $result;
    }


    public function checkErr($result,$error) {
        if($result === false)
        {
            // echo 'Curl error: ' . $error;
            $result = [
                'error'   => 0,
                'message' => 'Curl error: ' . $error,
            ];
            return $result;
        }
        else
        {
            //echo '操作完成没有任何错误';
        }
    }








}











//     public $apikey  = "da9a370e74f0024cddb192df5ce49c5a"; //修改为您的apikey(https://www.yunpian.com)登录官网后获取
//     public $mobile  = "13683760971"; //请用自己的手机号代替
//     public $text    = "【欢宇网络】您的验证码是#code#。如非本人操作，请忽略本短信";
//     public $ch;

//     public function __construct()
//     {
//         $this->ch = curl_init();
//         /* 设置验证方式 */
//         curl_setopt($this->ch, CURLOPT_HTTPHEADER, array('Accept:text/plain;charset=utf-8',
//             'Content-Type:application/x-www-form-urlencoded', 'charset=utf-8'));
//         /* 设置返回结果为流 */
//         curl_setopt($this->ch, CURLOPT_RETURNTRANSFER, true);

//         /* 设置超时时间*/
//         curl_setopt($this->ch, CURLOPT_TIMEOUT, 10);

//         /* 设置通信方式 */
//         curl_setopt($this->ch, CURLOPT_POST, 1);
//         curl_setopt($this->ch, CURLOPT_SSL_VERIFYPEER, false);
//     }

//     public function sendSMS()
//     {
//         // 取得用户信息
//         $json_data  = $this->get_user($this->ch,$apikey);
//         $array      = json_decode($json_data,true);
//         echo '<pre>';print_r($array);

//         // 发送短信
//         $data=array('text'=>$this->text,'apikey'=>$this->apikey,'mobile'=>$this->mobile);
//         $json_data = send($this->ch,$data);
//         $array = json_decode($json_data,true);
//         echo '<pre>';print_r($array);

//         // 发送模板短信
//         // 需要对value进行编码
//         $data = array('tpl_id' => '1', 'tpl_value' => ('#code#').
//             '='.urlencode('1234').
//             '&'.urlencode('#company#').
//             '='.urlencode('欢乐行'), 'apikey' => $apikey, 'mobile' => $mobile);
//         print_r ($data);
//         $json_data = tpl_send($this->ch,$data);
//         $array = json_decode($json_data,true);
//         echo '<pre>';print_r($array);

//         // 发送语音验证码
//         $data=array('code'=>'9876','apikey'=>$this->apikey,'mobile'=>$this->mobile);
//         $json_data =voice_send($this->ch,$data);
//         $array = json_decode($json_data,true);
//         echo '<pre>';print_r($array);

//         // 发送语音通知，务必要报备好模板
//         /*
//         模板： 课程#name#在#time#开始。 最终发送结果： 课程深度学习在14:00开始
//         */

//         $tpl_id = '123456';//你自己后台报备的模板id
//         $tpl_value = urlencode('name=深度学习&time=14:00');
//         $data = array('tpl_id'=>$tpl_id,'tpl_value'=>$tpl_value,'apikey'=>$apikey,'mobile'=>$mobile);
//         $json_data = notify_send($this->ch,$data);
//         $array = json_decode($json_data,true);
//         print_r($array);

//         curl_close($this->ch); 
//     }

//     //获得账户
//     function get_user($ch,$apikey){
//         curl_setopt ($ch, CURLOPT_URL, 'https://sms.yunpian.com/v2/user/get.json');
//         curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query(array('apikey' => $apikey)));
//         $result = curl_exec($ch);
//         $error = curl_error($ch);
//         checkErr($result,$error);
//         return $result;
//     }
//     function send($ch,$data){
//         curl_setopt ($ch, CURLOPT_URL, 'https://sms.yunpian.com/v2/sms/single_send.json');
//         curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));
//         $result = curl_exec($ch);
//         $error = curl_error($ch);
//         checkErr($result,$error);
//         return $result;
//     }
//     function tpl_send($ch,$data){
//         curl_setopt ($ch, CURLOPT_URL,
//             'https://sms.yunpian.com/v2/sms/tpl_single_send.json');
//         curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));
//         $result = curl_exec($ch);
//         $error = curl_error($ch);
//         checkErr($result,$error);
//         return $result;
//     }
//     function voice_send($ch,$data){
//         curl_setopt ($ch, CURLOPT_URL, 'http://voice.yunpian.com/v2/voice/send.json');
//         curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));
//         $result = curl_exec($ch);
//         $error = curl_error($ch);
//         checkErr($result,$error);
//         return $result;
//     }
//     function notify_send($ch,$data){
//         curl_setopt ($ch, CURLOPT_URL, 'https://voice.yunpian.com/v2/voice/tpl_notify.json');
//         curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));
//         $result = curl_exec($ch);
//         $error = curl_error($ch);
//         checkErr($result,$error);
//         return $result;
//     }

//     function checkErr($result,$error) {
//         if($result === false)
//         {
//             echo 'Curl error: ' . $error;
//         }
//         else
//         {
//             //echo '操作完成没有任何错误';
//         }
//     }





// }