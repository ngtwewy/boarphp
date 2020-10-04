// +----------------------------------------------------------------------
// | WebSocket IMSDK.js
// +----------------------------------------------------------------------
// | Copyright (c) 2020 http://restfulapi.cn All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: ngtwewy <62006464@qq.com> http://javascript.net.cn
// +----------------------------------------------------------------------

/**
 * JSSDK 对象
 */
var PigIM = {
  config: {},       // 配置
  fromId: {},       // 发送者 ID
  ws: {},           // Websocket 对象
  connection: {},
  listen: {},
  heartbeatTimer: {},
  utils: {},
  actions: {}
}

/**
 * 配置
 */
PigIM.config = {
  apiURL: "wss://restfulapi.cn:9000",
  token: "",                // 服务端权鉴 token
  isAutoLogin: true,        // 开启自动登录
  heartBeatWait: 10,        // 心跳间隔
  delivery: false,          // 开启消息回执
  autoReconnectInterval: 10, // 重新连接的间隔秒数 
  autoReconnectNumMax:   60, // 重新连接的最大次数
  autoReconnectCounter:  0  // 重新连接计数器
};

/**
 * 助手函数
 */
PigIM.utils.uuid = function () {
  function S4() {
    return (((1 + Math.random()) * 0x10000) | 0).toString(16).substring(1);
  }
  return (S4() + S4() + "-" + S4() + "-" + S4() + "-" + S4() + "-" + S4() + S4() + S4());
}
PigIM.utils.timestampToTime = function (timestamp) {
  var date = new Date(timestamp);
  var Y = date.getFullYear() + '-';
  var M = (date.getMonth() + 1 < 10 ? '0' + (date.getMonth() + 1) : date.getMonth() + 1) + '-';
  var D = date.getDate() < 10 ? '0' + date.getDate() + ' ' : date.getDate() + ' ';
  var h = date.getHours() < 10 ? '0' + date.getHours() + ':' : date.getHours() + ':';
  var m = date.getMinutes() < 10 ? '0' + date.getMinutes() + ':' : date.getMinutes() + ':';
  var s = date.getSeconds() < 10 ? '0' + date.getSeconds() : date.getSeconds();
  return Y + M + D + h + m + s;
}

/**
 * 初始化 JSSDK 对象
 */
PigIM.connection = function (config) {
  this.config = config;
  this.ws     = new WebSocket(this.config.apiURL);
}

/**
 * 监听
 */
PigIM.listen = function (actions) {
  this.ws.onopen    = actions.onOpen;     // 连接成功时触发
  this.ws.onerror   = actions.onError;    // 通信发生错误时触发
  this.ws.onclose   = actions.onClose;    // 连接关闭时触发
  this.ws.onmessage = actions.onMessage;  // 收到消息时触发
};

// Websocket回调：连接成功回调
PigIM.actions.onOpen = function (event) {
  console.log("连接成功回调：",event, this);
  var that = this;
  // 发送心跳包
  var json = {messageType: "heartbeat", messageId: PigIM.utils.uuid(), token:PigIM.config.token};
  PigIM.ws.send(JSON.stringify(json));
  PigIM.heartbeatTimer = setInterval(function () {
    if(that.readyState != 1){return;}
    PigIM.ws.send(JSON.stringify(json));
    console.log("开始发送心跳包，"+ PigIM.config.heartBeatWait + "秒发送一个");
  }, PigIM.config.heartBeatWait * 1000);
}

// Websocket回调：通信发生错误时
PigIM.actions.onError = function (event) {
  console.log("onError 通信发生错误时: ", event);
  PigIM.actions.reconnect();
}

// Websocket回调：连接关闭回调
PigIM.actions.onClose = function (event) {
  console.log("onClose 连接关闭回调: ", event);
  // PigIM.actions.reconnect();
}

// Websocket回调：收到信息回调
PigIM.actions.onMessage = function (event) {
  var response = JSON.parse(event.data);
  response.messageType = response.data ? response.data.messageType : '';
  switch (response.messageType) {
    case "heartbeat":         PigIM.actions.onHeartbeat(response); break;
    case "privateText":       PigIM.actions.onPrivateMessage(response); break;
    case "recallMessage":     PigIM.actions.onRecallMessage(response); break;
    case "addToBlackList":    PigIM.actions.onAddToBlackList(response); break;
    case "deliveredMessage":  PigIM.actions.deliveredMessage(response); break;
    default: PigIM.actions.onDefaultAction(response);
  }
}

// 接收文本消息
PigIM.actions.onPrivateMessage = function (message) {
  if (message.code == 400) { // 消息格式不正确
    console.log("错误 400: ", message);
  } else if (message.code == 201) { // 消息成功到达服务器
    console.log("消息成功到达服务器:", message);
  } else if (message.code == 200) { // 收到用户发来的信息
    console.log("收到用户发来的信息:", message);
  }
}

// 收到服务端发来的心跳包时执行
PigIM.actions.onHeartbeat = function () {
  console.log("Server: 返回心跳 ", PigIM.utils.timestampToTime(new Date()));
}

// 默认函数，当服务器不明白客户端的请求时执行
PigIM.actions.onDefaultAction = function (message) {
  console.log("默认函数，当服务器不明白客户端的请求时执行", message);
}

PigIM.actions.reconnect = function(){
  console.log("重新连接...");
  setTimeout(function(){
    if(PigIM.config.autoReconnectCounter > PigIM.config.autoReconnectNumMax){
      return;
    }
    PigIM.config.token = document.querySelector('input[name="token"]').value;
    PigIM.connection(PigIM.config);
    PigIM.listen(PigIM.actions);
    PigIM.config.autoReconnectCounter++;
  },PigIM.config.autoReconnectInterval*1000);
}


