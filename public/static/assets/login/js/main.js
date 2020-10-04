//验证码刷新
if(document.querySelector('img[alt="captcha"]')){
  document.querySelector('img[alt="captcha"]').onclick=function(){
    var url = this.getAttribute("src");
    url = url.split("?")[0] + '?t=' + new Date().getTime();;        
    this.setAttribute("src", url);
  }
}
