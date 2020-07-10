
$(function () {
  $('.geshop-U000089-model2').each(function(){
    var $elem = $(this);
    var isEditEnv = $elem.find('input[name=isEditEnv]').val();
    var nav_height = $elem.find('input[name=nav_height]').val();


    if(isEditEnv == 1){
      $elem.find('.component-m-nav').removeClass('fixed');
      $elem.find('.swiper-button-prev,.swiper-button-next').hide();
    }else{
      if(GESHOP_PLATFORM === 'app'){
        $elem.css('padding-top', nav_height/75+'rem');
      }
    }
    setTimeout(function(){
      $elem.find('.component-m-nav').show();
    },300);

    var staticDomain = GESHOP_STATIC;
    loadCss(staticDomain + '/resources/javascripts/library/swiper/swiper.min.css')
    $LAB.script(staticDomain + '/resources/javascripts/library/swiper/swiper.3.4.spec.min.js').wait(function () {
      var swiperContainer =  $elem.find('.component-m-nav');

      var lastSlide = $elem.find('.component-m-nav-ul').find('.swiper-slide').last();
      if(isEditEnv == 0){
        // if (lastSlide.context.offsetWidth + lastSlide.width() < document.body.clientWidth) {
        //   swiperContainer.find('.swiper-wrapper').addClass('wrap-center');
        // } else {
        //   swiperContainer.find('.swiper-wrapper').removeClass('wrap-center');
        // }
      }else{

        if (lastSlide.context.offsetWidth + lastSlide.width() < 400) {
          swiperContainer.find('.swiper-wrapper').addClass('wrap-center');
        } else {
          swiperContainer.find('.swiper-wrapper').removeClass('wrap-center');
        }
      }


      var mySwiper = new Swiper3(swiperContainer, {
        freeMode: true,
        slidesPerView: 'auto',
        spaceBetween : 0,
        observeParents:true,
        observer:true,
        nextButton: swiperContainer.find('.swiper-button-next'),
        prevButton: swiperContainer.find('.swiper-button-prev')
      });
      mySwiper.setWrapperTransition(100);

    })

  });
});
