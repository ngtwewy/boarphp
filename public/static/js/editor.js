window.onload = function () {
  //初始化编辑器
  var toolbarOptions = [
    [{ 'header': [1, 2, 3, 4, 5, 6, false] }],
    ['bold', 'italic', 'underline', 'strike'],
    ['blockquote', 'code-block'],
    [{ 'color': [] }, { 'background': [] }],
    [{ 'align': [] }],
    ['image'],
    ['clean']
  ];

  // var toolbarOptions = [
  //   ['bold', 'italic', 'underline', 'strike'],        // toggled buttons
  //   ['blockquote', 'code-block'],
  
  //   [{ 'header': 1 }, { 'header': 2 }],               // custom button values
  //   [{ 'list': 'ordered'}, { 'list': 'bullet' }],
  //   [{ 'script': 'sub'}, { 'script': 'super' }],      // superscript/subscript
  //   [{ 'indent': '-1'}, { 'indent': '+1' }],          // outdent/indent
  //   [{ 'direction': 'rtl' }],                         // text direction
  
  //   [{ 'size': ['small', false, 'large', 'huge'] }],  // custom dropdown
  //   [{ 'header': [1, 2, 3, 4, 5, 6, false] }],
  
  //   [{ 'color': [] }, { 'background': [] }],          // dropdown with defaults from theme
  //   [{ 'font': [] }],
  //   [{ 'align': [] }],
  
  //   ['clean']                                         // remove formatting button
  // ];


  if (!document.querySelector('#editor')) {
    return false;
  }

  var editor = new Quill('#editor', {
    theme: 'snow',
    modules: {
      'toolbar': toolbarOptions
    }
  });


  //同步编辑器内容到textarea
  var html = editor.container.firstChild.innerHTML;
  var content = document.querySelector("textarea[name='content']");
  content.innerHTML = html;

  editor.on('text-change', function (delta, oldDelta, source) {
    content.innerHTML = editor.container.firstChild.innerHTML;
  });

  let toolbar = editor.getModule('toolbar');
  toolbar.addHandler('image', function () {
    var fileInput = this.container.querySelector('input.ql-image[type=file]');
    if (fileInput == null) {
      fileInput = document.createElement('input');
      fileInput.setAttribute('type', 'file');
      fileInput.setAttribute('accept', 'image/png, image/gif, image/jpeg, image/bmp, image/x-icon');
      fileInput.classList.add('ql-image');
      fileInput.addEventListener('change', function () {
        if (fileInput.files != null && fileInput.files[0] != null) {
          var formData = new FormData();
          formData.append('file', fileInput.files[0]);
          axios({
            url: '/asset/image/uploadthumbnail?resize=false',
            method: 'POST',
            data: formData,
            headers: { 'content-type': 'multipart/form-data' },
          }).then(function (res) {
            //你的图片上传成功后的返回值...所以格式由你来定!
            // console.log(res);
				    if(res.data.error != 0){ myAlert.show(res.data.msg, 'danger'); }
            var range = editor.getSelection(true);
            editor.insertEmbed(range.index, 'image', res.data.data.url);
            editor.setSelection(range.index + 1);
          }).then(function (res) {
          });
        }
      });
      this.container.appendChild(fileInput);
    }
    fileInput.click();
  });
}
