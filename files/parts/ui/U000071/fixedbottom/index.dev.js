$(function() {

  $('.geshop-U000071-fixedbottom').each(function(){
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
      }else{
        $('.main-container').find('.img-fold').attr('src',unfoldSrc);
        $component.find('.main-container').css({transform: 'translate3d(0, 48px, 0)'});
        $('.main-container .deals-main-container').show();
      }

      if(slideLen >= 4){
        $('.main-container .deals-main-container').show();
        var mySwiper = new Swiper3($component.find('.swiper-container'), {
          slidesPerView: '4',
          loop : true,
          slidesPerGroup: 1,
          prevButton:'.btn-prev',
          nextButton:'.btn-next',
          lazyLoading : true,
          watchSlidesVisibility: true
        })


      }else{
        $component.find('.swiper-container').css('width',slideLen*280 + 'px');
        if(editEnv == 1 && slideLen == 0){
          $component.find('.swiper-container').css('width','1120px');

        }
        $component.find('.swiper-button').hide();
        // var mySwiper = new Swiper3 ('.swiper-container', {
        //   slidesPerView: 'auto'
        // })
      }

    })


    $(document).on('click','.center-align',function(e){


      var foldSrc = $('.main-container').find('.img-fold').attr('data-fold-src');
      var unfoldSrc = $('.main-container').find('.img-fold').attr('data-unfold-src');
      if($('.main-container').hasClass('open')){
        $('.main-container').removeClass('open');
        $('.main-container').find('.img-fold').attr('src',foldSrc);
        $('.overlay').removeClass('open');
      }else{
        $('.main-container').addClass('open');
        $('.main-container').find('.img-fold').attr('src',unfoldSrc);
        $('.overlay').addClass('open');
        $('.main-container .deals-main-container').show();
      }
    });

    $('body').on("click",function(e){
      var editEnv = $component.find('input[name=editEnv]').val();
      if(editEnv == 0){
        var ele = window.event || e;
        var element = ele.target || ele.srcElement;
        if(element && !$(element).hasClass("deals-main-container") && !$(element).hasClass('slider') && !$(element).hasClass('.swiper-container') && !$(element).hasClass("swiper-button") && !$(element).hasClass("swiper-img") && !$(element).hasClass("swiper-button") && !$(element).hasClass("deal-button") && !$(element).hasClass("pagation-img") && !$(element).hasClass("img-fold")){

          $('.main-container').removeClass('open');
          $('.overlay').removeClass('open');
          var foldSrc = $('.main-container').find('.img-fold').attr('data-fold-src');
          $('.main-container').find('.img-fold').attr('src',foldSrc);
        }
      }

    });
  });


});

