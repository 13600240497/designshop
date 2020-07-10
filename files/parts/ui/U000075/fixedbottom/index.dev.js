$(function() {

  $('.geshop-U000075-fixedbottom').each(function(){
    var $component = $(this);
    var staticDomain = $('[data-static-domain]:eq(0)').attr('data-static-domain') ? $('[data-static-domain]:eq(0)').attr('data-static-domain') : "";
    loadCss(staticDomain + '/resources/javascripts/library/swiper/swiper.min.css');
    $LAB.script(staticDomain + '/resources/javascripts/library/swiper/swiper.3.4.spec.min.js').wait(function () {

      var editEnv = $component.find('input[name=editEnv]').val();
      var slideLen = $component.find('input[name=slideLen]').val();
      var foldSrc = $('.main-container').find('.img-fold').attr('data-fold-src');
      var unfoldSrc = $('.main-container').find('.img-fold').attr('data-unfold-src');
      $('.main-container').find('.img-fold').attr('src',foldSrc);
      if(editEnv == 0){
        $component.find('.main-container').css({position: 'fixed'});
        //$component.find('.main-container .img-fold').attr('src','https://geshopimg.logsss.com/uploads/14XwdPGioNhlWe5zKm8bLBctp9I6fVgU.png');
      }else{
        $('.main-container').find('.img-fold').attr('src',unfoldSrc);
        $component.find('.main-container').css({transform: 'translate3d(0, 48px, 0)'});
        //$component.find('.main-container .img-fold').attr('src','https://geshopimg.logsss.com/uploads/P9eSmVFLJGpXEt53R2wOirvQNhCnB7WT.png');
      }
      if(slideLen > 1){
        var mySwiper = new Swiper3($component.find('.swiper-container'), {
          slidesPerView: 'auto',
          pagination: '.swiper-pagination',
          loop : true,
          prevButton:'.swiper-button-prev',
          nextButton:'.swiper-button-next',
          lazyLoading : true,
          watchSlidesVisibility: true
        })
      }else{ 
        $component.find('.swiper-container').css('width',slideLen*250 + 'px');
        if(editEnv == 1 && slideLen == 0){
          $component.find('.swiper-container').css('width','1120px');
        }
        $component.find('.swiper-button').hide();
      }
       
    })


    $(document).on('click','.center-align',function(e){
      var foldSrc = $('.main-container').find('.img-fold').attr('data-fold-src');
      var unfoldSrc = $('.main-container').find('.img-fold').attr('data-unfold-src');
      if($('.main-container').hasClass('open')){
        $('.main-container').removeClass('open');
        $('.main-container').find('.ico-fold').addClass('unfold');
        $('.overlay').removeClass('open');
        $('.main-container').find('.img-fold').attr('src',foldSrc);
      }else{
        $('.main-container').addClass('open');
        $('.main-container').find('.ico-fold').removeClass('unfold');
        $('.overlay').addClass('open');
        $('.main-container').find('.img-fold').attr('src',unfoldSrc);
      }
    });

    $('body').on("click",function(e){
      var editEnv = $component.find('input[name=editEnv]').val();
      if(editEnv == 0){
        var ele = window.event || e;
        var element = ele.target || ele.srcElement;
        if(element && !$(element).hasClass("swiper-pagination") && !$(element).hasClass('swiper-pagination-bullet') && !$(element).hasClass("deals-main-container") && !$(element).hasClass('swiper-container')  && !$(element).hasClass("swiper-img") && !$(element).hasClass("center-align") && !$(element).hasClass("deal-button") && !$(element).hasClass("img-fold")){
          var foldSrc = $('.main-container').find('.img-fold').attr('data-fold-src');
          $('.main-container').find('.img-fold').attr('src',foldSrc);
          $('.main-container').removeClass('open');
          $('.overlay').removeClass('open');
        }
      }
    });
  });
  
  
});

