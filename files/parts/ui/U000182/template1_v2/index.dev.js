$(function(){
  var isEidtenv = $('.geshop-U000182-model1 input[name=editEnv]').val();
  var platform;

  $('.geshop-U000182-model1 .goods-list .goods-item').each(function(){
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
    $('.geshop-U000182-model1 input[name=platform]').val(platform); 
    $(".geshop-U000182-model1 img.js-lazy").lazyload();

    $('.geshop-U000182-model1 .goods-list .goods-item').each(function(){
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
  .on('mouseenter', '.geshop-U000182-model1 .geshop-goods-img', function() {
    if (platform === 2 && !$(this).hasClass('soldout-bg')){
        $(this).find('.shop-now-container').show();
    }
      
      
  }).on('mouseleave', '.geshop-U000182-model1 .geshop-goods-img', function() {
    if (platform === 2 && !$(this).hasClass('soldout-bg')){
        $(this).find('.shop-now-container').hide();
    }
    
  }).on('mouseenter', '.geshop-U000182-model1 .geshop-goods-promotions', function() {
      var platform = GLOBAL.util.getPlatform();
      // pc
      var sonLength = $(this).has('.promotions-text').length;
      var promotionsLength = $(this).find('.promotions-text').data('promotions-length');
      if (platform === 2 && sonLength > 0 && promotionsLength > 1) {
          $(this).next('.geshop-goods-promotions-more').removeClass('none');
      }
  })
  .on('mouseleave', '.geshop-U000182-model1 .geshop-goods-promotions', function() {
      var platform = GLOBAL.util.getPlatform();
      // pc
      if (platform === 2) {
          $(this).next('.geshop-goods-promotions-more').addClass('none');
      }
  })
  .on('click', '.geshop-U000182-model1 .geshop-goods-promotions', function() {
    var platform = GLOBAL.util.getPlatform();
    var $more =$(this).next('.geshop-goods-promotions-more');
    // mobile, pad
    if (platform !== 2 && $(this).data('flag') == 1) {
        var $parent = $(this).parent();
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
});
