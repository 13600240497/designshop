
function beforeSubmit(progress) {
  var text = $('textarea[name=content]').val();
  $('textarea[name=content]').val(text.replace(/\\n/g,'<br>'));
  progress.next()     
}

$('textarea[name=content]').val($('textarea[name=content]').val().replace(/<br>/g,'\\n'));