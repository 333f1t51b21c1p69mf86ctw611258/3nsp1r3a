tinymce.init({
  selector : 'textarea.mceEditor',
  plugins: ["advlist autolink lists link image charmap print preview anchor",
    "searchreplace visualblocks code fullscreen",
    "insertdatetime media table contextmenu paste jbimages"],
  menubar: false,
  statusbar: false,
  toolbar: "styleselect | bold italic | alignleft aligncenter alignright alignjustify | bullist numlist outdent indent | link jbimages",
  relative_urls: false,
  resize: true,
});

tinymce.init({
  selector : 'textarea.mceImageEditor',
  plugins: ["advlist autolink lists link image charmap print preview anchor",
    "searchreplace visualblocks code fullscreen",
    "insertdatetime media table contextmenu paste jbimages"],
  menubar: false,
  statusbar: false,
  toolbar: "jbimages",
  relative_urls: false,
  height: 210,
  width: 210,
});

$(document).ready(function(){
    //$('textarea.tinymce').tinymce({
    //    theme_advanced_resizing: true,
    //    theme_advanced_resizing_use_cookie : false
    //});
    resizeHeight = 500;
    resizeWidth = 500;
    //editor = tinymce.get('DESCRIPTION_OF_NONCONFORMITY');
    //editor.theme.resizeTo(resizeWidth, resizeHeight);
});

