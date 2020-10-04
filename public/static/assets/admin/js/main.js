var navs = document.querySelectorAll(".nav-list li a");

navs.forEach(function(nav){
    if(!nav.parentNode.children[1]){
        return;
    }
    nav.addEventListener("click",function(){
        if(this.parentNode.children[1].style.display=="none"){
            this.parentNode.children[1].style.display="block";
            this.children[0].className="arr-down";
        }else{
            this.parentNode.children[1].style.display="none";
            this.children[0].className="arr-right";
        }
    });
});


var links = document.querySelectorAll(".nav-list a");
if(links.length){
    links.forEach(function(link){
        link.onclick=function(){
            if(this.href=="javascript:;") return;
            document.querySelector("#page-iframe").src = link.href;
            return false;
        }
    });
}