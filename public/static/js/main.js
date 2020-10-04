/**
* @author   ngtwewy <https://www.restfulapi.cn>
* @license  Apache 2
* @time     2018-10-12
*/


// Nav 导航点击弹出弹入
(function(){
  var navbar = document.querySelectorAll(".navbar");
  navbar.forEach(function(item){
    item.querySelector(".navbar-toggle").onclick = function(){
      console.log(this.parentNode.parentNode);
      // this.parentNode.parentNode.querySelector(".collapse").style.display = 'block';
      this.parentNode.parentNode.querySelector(".collapse").classList.toggle("in");
    }
  });
})();
