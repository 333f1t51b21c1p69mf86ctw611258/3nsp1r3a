tinymce.init({
  selector: "textarea.mceEditor",
  //readonly: true,
  toolbar: false,
  menubar: false,
  statusbar: false,
  relative_urls: false
});
tinymce.init({
  selector: "textarea.mceImageEditor",
  readonly: true,
  toolbar: false,
  menubar: false,
  statusbar: false,
  relative_urls: false,
  width: 210,
  height: 210,
});
//$(document).ready(function(){
//    $('div').height($('td').outerHeight());
//});
