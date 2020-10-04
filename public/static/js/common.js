/**
* @author   ngtwewy <https://restfulapi.cn>
* @license  Apache 2
* @time     2018-10-12
* @version  1.190120
*/


/************************************************************************/
/******************************** 分割线 *********************************/
/************************************************************************/
//Nav 导航点击弹出弹入 移动端导航按钮
(function(){
    var navbar = document.querySelectorAll(".navbar");
    navbar.forEach(function(item){
        if(!item.querySelector(".navbar-toggle")){return;}
        item.querySelector(".navbar-toggle").onclick = function(){
          var collapse = this.parentNode.parentNode.querySelector(".collapse");
          collapse && collapse.classList.toggle("in");
        }
    });
})();

//导航下拉菜单
(function(){
    var dropdown = document.querySelectorAll(".dropdown");
    dropdown.forEach(function(item){
      if(!item.querySelector(".dropdown-toggle")){return;}
      item.querySelector(".dropdown-toggle").addEventListener("click",function(event){
        this.parentNode.classList.toggle("open");
        event.stopPropagation();    
      },false);
    });

    //点击任意地方关闭下拉菜单
    document.querySelector("*").onclick = function(){
      if(document.querySelector(".dropdown-toggle") ){
        document.querySelector(".dropdown-toggle").parentNode.classList.remove("open");
      }
      if(parent.document.querySelector(".dropdown-toggle") ){
        parent.document.querySelector(".dropdown-toggle").parentNode.classList.remove("open");
      }
    };
})();



/************************************************************************/
/******************************** 分割线 *********************************/
/************************************************************************/
/*!
* Modal.js
*
* @author   ngtwewy <https://www.restfulapi.cn>
* @license  Apache 2
* @time     2018-09-21
*/

/* 生成如下Html，然后自动删除
<div class="modal">
  <div class="modal-backdrop fade in"></div>
  <div class="modal-dialog modal-sm">
    <div class="modal-content">
      <div class="modal-header">
        <button type="button" class="close">
          <span aria-hidden="true">&times;</span>
        </button>
        <h4 class="modal-title">对话框标题</h4>
      </div>
      <div class="modal-body">
        <p>对话框内容&hellip;</p>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-default" data-dismiss="modal">取消</button>
        <button type="button" class="btn btn-primary">确定</button>
      </div>
    </div><!-- /.modal-content -->
  </div><!-- /.modal-dialog -->
</div><!-- /.modal -->
*/

(function () {
  var options = {
    title:'',
    message:'',
    button: [
      {
        value: '同意',
        callback: function () {
          console.log(123);
          return false;
        },
        autofocus: true,
        type:'default'
      },
      {
        value: '不同意',
        callback: function () {
          alert('你不同意')
        },
        type:'primary'
      },
      {
        id: 'button-disabled',
        value: '无效按钮',
        disabled: true,
        type:'success'
      }
    ]
	}
  
  //helper
	var getType = function(arg){
    return arg;
  }
  
  var dialogShow = function(msg, type, seconds, url){
      var div       = document.createElement("div");
          div.classList.add("modal");
          div.style.display = "block";
      
      var modalBackdrop = document.createElement("div");
          modalBackdrop.className = "modal-backdrop fade in";
          div.appendChild(modalBackdrop);

      var modalDialog = document.createElement("div");
          modalDialog.className = "modal-dialog modal-sm";
          modalDialog.style['z-index'] = "1050";
          div.appendChild(modalDialog);

      var modalContent = document.createElement('div');
          modalContent.className = "modal-content";
          modalDialog.appendChild(modalContent);
      
      var modalHeader = document.createElement('div');
          modalHeader.className = 'modal-header';
          modalContent.appendChild(modalHeader);
      var closeButton = document.createElement('button');
          closeButton.setAttribute('type', 'button');
          closeButton.className = 'close';
          closeButton.addEventListener('click', closeModal);
          modalHeader.appendChild(closeButton);
      var span = document.createElement("span");
          span.appendChild(document.createTextNode('x'));
          span.setAttribute('aria-hidden', 'true');
          closeButton.appendChild(span);
      var h4 = document.createElement('h4');
          h4.className = 'modal-title';
          h4.appendChild(document.createTextNode('信息提示'));
          modalHeader.appendChild(h4);
      

      var modalBody = document.createElement("div");
          modalBody.classList.add("modal-body");
          // modalBody.innerHTML("<p>对话框内容&hellip;</p>");
          modalBody.appendChild(document.createTextNode(msg));
          modalContent.appendChild(modalBody);


      var modalFooter = document.createElement('div');
          modalFooter.classList.add("modal-footer");

      options.button.forEach(function(elem){
        var button = document.createElement("button");
        button.setAttribute("type", "button");
        button.className = "btn";
        if(elem.type){//添加模态对话框按钮样式
          button.classList.add("btn-"+elem.type);
        }else{
          button.classList.add("btn-default");
        }
        if(elem.disabled==true){//禁用
          button.setAttribute("disabled",'disabled');
        }
        if(elem.id){
          button.setAttribute('id','button-disabled');
        }
        //绑定按钮事件
        button.appendChild(document.createTextNode(elem.value) );
        if(elem.callback){
          button.addEventListener("click", elem.callback);
        }else{
          button.addEventListener("click", closeModal);
        }
        
        modalFooter.appendChild(button);
      });
      modalContent.appendChild(modalFooter);

      var fragment = document.createDocumentFragment();
      fragment.appendChild(div);
      document.querySelector("body").appendChild(fragment);
  }
  //关闭所有
  var closeModal = function(){
    var modals = document.querySelectorAll('.modal');
    modals.forEach(function(elem){
      document.querySelector("body").removeChild(elem);
    });
  }

  //API
	var api = {
		config: function (opts) {
			if(!opts) return options;
			for(var key in opts) {
				options[key] = opts[key];
			}
			return this;
		},

		listen: function listen(elem) {
			if (typeof elem === 'string') {
				var elems = document.querySelectorAll(elem),
					      i = elems.length;
        while (i--) {
          listen(elems[i]);
        }
        return
			}
			return this;
		},

    show: function(message, type, seconds, url){
      dialogShow(message, getType(type), seconds, url);
    },

    closeModal: closeModal
	}

	this.myDialog = api;
})();


















/************************************************************************/
/******************************** 分割线 *********************************/
/************************************************************************/
/*!
* Alert.jsf
*
* @author   ngtwewy <https://www.restfulapi.cn>
* @license  Apache 2
* @time     2018-09-21
*/

/* 生成如下Html，然后自动删除
<div class="alert alert-danger my-alert" role="alert">
  <a href="#" class="alert-link">
    依赖警告框 JavaScript 插件
  </a>
</div>
*/

(function () {
  var options = {
    message:'默认 message',
    type: 'alert-success',
    seconds: 3,
    url: null
	}
  
  //helper
	var getType = function(arg){
    var type;
	  switch(arg){
      case "success": type = "alert-success"; 
        break;
      case "info":    type = "alert-info"; 
        break;
      case "warning": type = "alert-warning"; 
        break;
      case "danger":  type = "alert-danger"; 
        break;
      default:        type = "alert-success"; 
    }
    return type;
  }
  
  var alertShow = function (msg, type, url, seconds) {
    //创建警告框
    var fragment = document.createDocumentFragment();
    var div = document.createElement("div");
    div.classList.add("alert");
    div.classList.add(getType(type));
    div.classList.add("my-alert");

    var text = document.createTextNode(msg);
    div.appendChild(text);
    fragment.appendChild(div);
    document.querySelector("body").appendChild(fragment);

    seconds = seconds ? seconds * 1000 : 2 * 1000;
    //隐藏警告框
    setTimeout(function () {
      div.classList.add("my-alert");
      div.classList.add("my-alert-hide");
    }, seconds);

    //删除警告框
    setTimeout(function () {
      document.querySelector("body").removeChild(div);
      // document.querySelectorAll(".my-alert").forEach(function (item) {
      //   document.querySelector("body").removeChild(item);
      // });
    }, seconds + 300);

    //跳转
    setTimeout(function () {
      if (url) {
        location.href = url;
      }
    }, seconds + 300);
  }

  //API
	var api = {
		config: function (opts) {
			if(!opts) return options;
			for(var key in opts) {
				options[key] = opts[key];
      }
			return this;
		},

		listen: function listen(elem) {
			if (typeof elem === 'string') {
				var elems = document.querySelectorAll(elem),
					      i = elems.length;
        while (i--) {
          listen(elems[i]);
        }
        return
			}
			return this;
		},

    show: function(msg, type, url, seconds){
      alertShow(msg, type, url, seconds);
    }
	}

	this.myAlert = api;
})();














/************************************************************************/
/******************************** 分割线 *********************************/
/************************************************************************/

/** 表单自动提交
* @author   ngtwewy <https://www.restfulapi.cn>
* @license  Apache 2
* @time     2018-09-21
*/


/**
 * 表单按钮操作
 */
(function(){
  //所有Ajax操作都依赖Axios, 对Axios初始化
  axios.defaults.headers['X-Requested-With']  = "XMLHttpRequest";
  axios.defaults.headers['Content-Type']      = 'application/x-www-form-urlencoded';
  //获取所有表单
  var forms    = document.querySelectorAll(".ajax-form");
  if(!forms){
    return false;
  }
	//ajax提交函数
	var ajaxAction = function(formData, url){
		axios({
			method: 'post',
			url: url,
			responseType: 'json',
			data: formData
		})
		.then(function (response) {
			console.log(response.data);
			if(response.data.code==1){
				myAlert.show(response.data.msg, 'success', response.data.url, response.data.seconds);
			}else{
				// myAlert.show(response.data.msg, 'danger', response.data.url, response.data.seconds);
				myAlert.show(response.data.msg, 'danger');
			}
		})
		.catch(function (error) {
			console.log(error);
		});
	}

	//获取表单中的所有按钮，绑定事件
  forms.forEach(function(form){
    var buttons = form.querySelectorAll(".ajax-submit");
    buttons.forEach(function(button){
      button.onclick = function(){
				//按钮事件处理函数
				var formData  		= new FormData(form);
				var formAction		= form.getAttribute("action");
				var buttonAction  = button.getAttribute("data-action");
				var url = null;
				if(formAction){
					url = formAction;
				}else if(buttonAction){
					url = buttonAction;
				}else{
					myAlert.show("没有找到表单地址", 'danger');
					return false;
				}
				//有没有弹出信息
				if(this.getAttribute("data-msg")){
					console.log("data-msg: ", this.getAttribute("data-msg"));
					var options = {
						title:'提示',
						message: this.getAttribute("data-action"),
						button: [
							{
								value:'取消'
							},
							{
								value: '确定',
								callback: function () {
									myDialog.closeModal();
									ajaxAction(formData, url);
								},
								type:'primary'
							}
						]};
					myDialog.config(options).show( this.getAttribute("data-msg") );
				}else{
					ajaxAction(formData, url);
				}
				return false;
			}
    });
  });
})();



/**
 * Ajax按钮
 */
(function(){
	var buttons = document.querySelectorAll(".ajax-url");
	if(buttons.length){
		buttons.forEach(function(btn){
			btn.onclick=function(){
				var msg = this.getAttribute("data-msg");
				var url = this.href;

				var options = {
					title:'',
					message:msg,
					button: [
						{
							value:'取消'
						},
						{
							value: '确定',
							callback: function () {
								myDialog.closeModal();
								axios({
									method: 'get',
									url: url,
									responseType: 'json',
								})
								.then(function (response) {
									// console.log(response.data);
									if(response.data.code==1){
										myAlert.show(response.data.msg, 'success', response.data.url, response.data.seconds);
									}else{
										myAlert.show(response.data.msg, 'danger', response.data.url, response.data.seconds);
									}
								})
								.catch(function (error) {
									myAlert.show(error);
								});
							},
							type:'primary'
						}
					]
				}
				
				myDialog.config(options).show(msg);
				return false;
			}
		});
	}
})();



/**
 * checkbox 全选或全不选
 */
(function(){
	//获取所有表单
	var forms = document.querySelectorAll(".ajax-form");
	if(!forms){return false;}

	forms.forEach(function(form){
		var checkAll = form.querySelector(".check-all");
		if(!checkAll){return false;}

		checkAll.onclick = function(){
			if(this.checked==true){
				form.querySelectorAll("input[name='ids[]']").forEach(function(item){
					item.checked = true;
				});
			}else{
				console.log('false', checkAll.checked);
				form.querySelectorAll("input[name='ids[]']").forEach(function(item){
					item.checked = false;
				});
			}
		}
	});

})();





/************************************************************************/
/******************************** 分割线 *********************************/
/************************************************************************/

/** 缩略图插件
* @author   ngtwewy <https://www.restfulapi.cn>
* @license  Apache 2
* @time     2018-10-14
*/

(function(){
  //上传按钮事件
  var thumbnailAction = function () {
    let fileInput = this.parentNode.querySelector("input[name='file']");
    let url = fileInput.getAttribute("data-url");

    fileInput.click();
    fileInput.onchange = function () {
      if (!this.files[0] || this.files[0] == undefined) return;

      var fd = new FormData();
      fd.append("file", this.files[0]);
      // fd.append("project", 123);

      axios({
        method: 'post',
        url: url,
        data: fd,
        headers: { 'content-type': 'multipart/form-data' },
      }).then(function (response) {
        if (response.data.error == 0) {
          fileInput.parentNode.parentNode.querySelector('.input-hidden').value = response.data.data.thumbnail;
          fileInput.parentNode.parentNode.querySelector('.project-thumbnail img').src = response.data.data.url;
          fileInput.parentNode.parentNode.querySelector(".delete-button").style.display = 'block';
        }else{
          myAlert.show(response.data.msg, 'danger');
          fileInput.value="";
        }
      });
    }
  }

  //删除按钮事件
  var thumbnailDelete = function () {
    var thumbnail = this.parentNode.querySelector(".project-thumbnail img");
    thumbnail.src = thumbnail.getAttribute("data-nothumbnail");
    this.parentNode.querySelector('.input-hidden').value = "";
    this.style.display = 'none';
  }

  //所有缩略图绑定
  var thumbnailContainers = document.querySelectorAll(".thumbnail-container");
  if (thumbnailContainers) {
    thumbnailContainers.forEach(function (item) {
      item.querySelector('.upload-button').addEventListener('click', thumbnailAction);
      item.querySelector('.delete-button').addEventListener('click', thumbnailDelete);
      //检测是否有缩略图，有的话, 显示缩略图，同时显示删除按钮
      if (item.querySelector('.input-hidden').value) {
        var imgUrl = item.querySelector('.project-thumbnail img').getAttribute("data-thumbnail") + item.querySelector('.input-hidden').value;
        item.querySelector('.project-thumbnail img').src = imgUrl;
        item.querySelector('.delete-button').style.display = 'block';
      }
    });
  }
})();








/************************************************************************/
/******************************** 分割线 *********************************/
/************************************************************************/
/**
 * 上传图片列表
 */

 /**
  <div class="image-item">
      <input type="text" name="image_name[]" value="{$vo.name}" class="form-control">
      <input type="hidden" name="image_url[]" value="{$vo.url}" class="form-control">
      <img src="{$vo.url}">
      <a href="javascript:;" class="btn btn-warning image-btn-update">替换</a>
      <a href="javascript:;" class="btn btn-danger image-btn-delete">删除</a>
  </div>

  <a class="btn btn-primary btn-sm image-btn-add" href="javascript:;">添加图片</a>
  <input type="file" name="image_input" style="display:none;" data-url='{:url("asset/Image/uploadThumbnail")}'>
  */


(function(){
  //上传按钮事件
  var imagesAction = function () {
    var that = this;
    let fileInput = this.parentNode.querySelector("input[name='image_input']");
    let url = fileInput.getAttribute("data-url");

    fileInput.click();
    fileInput.onchange = function () {
      if (!this.files[0] || this.files[0] == undefined) return;

      var fd = new FormData();
      fd.append("file", this.files[0]);

      axios({
        method: 'post',
        // url: url + '?resize=true',
        url: url,
        data: fd,
        headers: { 'content-type': 'multipart/form-data' },
      }).then(function (response) {
        if (response.data.error == 0) {
          var data = response.data.data;
          console.log(data);
          var image_item = '\
                <div class="image-item">\
                    <input type="text" name="image_name[]" var="" class="form-control">\
                    <input type="hidden" name="image_url[]" value="'+ data.thumbnail + '" class="form-control">\
                    <img src="'+ data.url + '">\
                    <a href="javascript:;" class="btn btn-warning image-btn-update">替换</a>\
                    <a href="javascript:;" class="btn btn-danger image-btn-delete">删除</a>\
                </div>\
                ';

          var ss = document.createElement("div");
          ss.innerHTML = image_item;
          var add_btn = that.parentNode.querySelector(".image-btn-add");
          add_btn.parentNode.insertBefore(ss, add_btn);
        } else {
          console.log("图片上传错误");
        }
      });
    }
  }
  var image_btn_add = document.querySelectorAll(".image-btn-add");
  if(image_btn_add){
    image_btn_add.forEach(function(btn){
      btn.addEventListener('click', imagesAction);
    });
  }
  

  // 删除
  var image_btn_delete = document.querySelectorAll(".image-btn-delete");
  image_btn_delete.forEach(function (btn) {
    btn.addEventListener('click', function () {
      console.log("del");
      this.parentNode.parentNode.removeChild(this.parentNode);
    });
  });

  // 修改
  //上传按钮事件
  var updateAction = function () {
    let fileInput = this.parentNode.parentNode.querySelector("input[name='image_input']");
    let url = fileInput.getAttribute("data-url");

    let image_name = this.parentNode.querySelector('input[type="text"]');
    let image_url = this.parentNode.querySelector('input[type="hidden"]');
    let img = this.parentNode.querySelector('img');

    fileInput.click();
    fileInput.onchange = function () {
      if (!this.files[0] || this.files[0] == undefined) return;

      var fd = new FormData();
      fd.append("file", this.files[0]);

      axios({
        method: 'post',
        url: url,
        data: fd,
        headers: { 'content-type': 'multipart/form-data' },
      }).then(function (response) {
        if (response.data.error == 0) {
          var data = response.data.data;
          console.log(data);
          img.src = data.url;
          image_url.value = data.thumbnail;
        } else {
          console.log("图片上传错误");
        }
      });
    }
  }
  // 删除
  var image_btn_update = document.querySelectorAll(".image-btn-update");
  if(image_btn_update){
    image_btn_update.forEach(function (btn) {
      btn.addEventListener('click', updateAction);
    });
  }
})();



/** tooltip
* @author   ngtwewy <https://www.restfulapi.cn>
* @license  Apache 2
* @time     2020-03-04
* data-toggle="tooltip" data-placement="bottom" title=""
*/
(function () {
  
  //获取元素的纵坐标
  function getTop(e) {
    var offset = e.offsetTop;
    if (e.offsetParent != null) offset += getTop(e.offsetParent);
    return offset;
  }
  //获取元素的横坐标
  function getLeft(e) {
    var offset = e.offsetLeft;
    if (e.offsetParent != null) offset += getLeft(e.offsetParent);
    return offset;
  }
  document.querySelectorAll("[data-toggle='tooltip']").forEach(function (item) {
    item.setAttribute('original-title', item.getAttribute('title'));
    item.setAttribute('title', '');
    item.addEventListener("mouseenter", function (e) {
      var placement = item.getAttribute('data-placement')
        ? item.getAttribute('data-placement') : 'top';

      var tooltipHtml = '\
                <div class="tooltip '+ placement + '" role="tooltip" style="top:0;left:0;opacity:1;">\
                    <div class="tooltip-arrow"></div>\
                    <div class="tooltip-inner">\
                    '+ item.getAttribute('original-title') + '\
                    </div>\
                </div>\
            ';
      var tooltipDom = document.createElement("div");
      item.appendChild(tooltipDom);
      tooltipDom.outerHTML = tooltipHtml;

      var top = getTop(item);
      var left = getLeft(item);

      var tooltip = item.querySelector(".tooltip");

      if (placement == 'bottom') {
        tooltip.style.left = left - (tooltip.clientWidth - item.clientWidth) / 2 + "px";
        tooltip.style.top = top + item.clientHeight + "px";
      } else if (placement == 'left') {
        tooltip.style.left = left - tooltip.clientWidth + "px";
        tooltip.style.top = top - (tooltip.clientHeight - item.clientHeight) / 2 + "px";
      } else if (placement == 'right') {
        tooltip.style.left = left + item.clientWidth + "px";
        tooltip.style.top = top - (tooltip.clientHeight - item.clientHeight) / 2 + "px";
      } else { // top
        tooltip.style.left = left - (tooltip.clientWidth - item.clientWidth) / 2 + "px";
        tooltip.style.top = top - tooltip.clientHeight + "px";
      }
      event.preventDefault();
    });
    item.addEventListener("mouseout", function (e) {
      item.removeChild(item.querySelector('.tooltip'));
    });
  });
})();




















