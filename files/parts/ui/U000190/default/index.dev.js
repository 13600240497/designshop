
(function(){
  var isEidtenv = $('.geshop-U000190-default input[name=editEnv]').val();

  var platform;

  $('.geshop-U000190-default .goods-list .goods-item').each(function(){
    if($(this).find('.geshop-goods-img').hasClass('soldout-bg')){
        $(this).find('.shop-now-container').show();
        $(this).find('.shop-now-text').text('');
      }
  });

  if(isEidtenv == 1){
    $("img.js-lazy").each(function(){
      $(this).attr('src',$(this).attr('data-original'));
    })
  }else{
    platform = GLOBAL.util.getPlatform();
    $('.geshop-U000190-default input[name=platform]').val(platform);

      if ($.fn.lazyload) {
          $(".geshop-U000190-default img.js-lazy").lazyload();
      } else {
          window.GS_GOODS_LAZY_FN('.js-lazy');
      }

    $('.geshop-U000190-default .goods-list .goods-item').each(function(){
        $(this).find('.geshop-goods-promotions .promotions-text').each(function(){
          var promotionsLength = $(this).data('promotions-length');
          if(promotionsLength > 1){
              if(platform === 2){
                  $(this).append(' ···');
              }else{
                  $(this).append('<img src="https://geshoptest.s3.amazonaws.com/uploads/WaiETHlZ8ShDeFRXMB97Yjc3uQ4InAJr.png">');
              }

            }
        });
        if($(this).find('.geshop-goods-img').hasClass('soldout-bg')){
            $(this).find('.shop-now-container').show();
            $(this).find('.shop-now-text').text('');
        }
      })
  }



  $(document)
  .on('mouseenter', '.geshop-U000190-default .geshop-goods-img', function() {
    if (platform === 2 && !$(this).hasClass('soldout-bg')){
        $(this).find('.shop-now-container').show();
    }


  }).on('mouseleave', '.geshop-U000190-default .geshop-goods-img', function() {
    if (platform === 2 && !$(this).hasClass('soldout-bg')){
        $(this).find('.shop-now-container').hide();
    }

  }).on('mouseenter', '.geshop-U000190-default .geshop-goods-promotions', function() {
      var platform = GLOBAL.util.getPlatform();
      // pc
      var sonLength = $(this).has('.promotions-text').length;
      var promotionsLength = $(this).find('.promotions-text').data('promotions-length');
      if (platform === 2 && sonLength > 0 && promotionsLength > 1) {
          $(this).next('.geshop-goods-promotions-more').removeClass('none');
      }
  })
  .on('mouseleave', '.geshop-U000190-default .geshop-goods-promotions', function() {
      var platform = GLOBAL.util.getPlatform();
      // pc
      if (platform === 2) {
          $(this).next('.geshop-goods-promotions-more').addClass('none');
      }
  })
  .on('click', '.geshop-U000190-default .geshop-goods-promotions', function() {
    var platform = GLOBAL.util.getPlatform();
    // mobile, pad
    if (platform !== 2 && $(this).data('flag') == 1) {
        var $parent = $(this).parent();
        var $more =$(this).next('.geshop-goods-promotions-more');

        if($(this).find('img').hasClass('up')){
            $(this).find('img').removeClass('up');
        }else{
            $(this).find('img').addClass('up');
        }
        if ($more.is('.none')) {
            $more.removeClass('none');
        }
        else{
            $more.addClass('none');
        }
    }
})
})();
