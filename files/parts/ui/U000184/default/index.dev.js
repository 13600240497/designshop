(function(){
  if(isIE()){
    $('.geshop-U000184-default').each(function(){
      var imgObj = $(this).find('.ie-img-wrap');
      $(this).find('.banner-img').hide();
      if(GLOBAL.util.getPlatform() == 2){
        //pc
        $(imgObj).show().attr('src', $(imgObj).data('pc-src'));
      }else if(GLOBAL.util.getPlatform() == 3){
        //pad
        $(imgObj).show().attr('src', $(imgObj).data('pad-src'));
      }else if(GLOBAL.util.getPlatform() == 1){
        //m
        $(imgObj).show().attr('src', $(imgObj).data('m-src'));
      }

    });

  }


})();

function isIE() {
  if (!!window.ActiveXObject || "ActiveXObject" in window)
        { return true; }
  else
        { return false; }
}

