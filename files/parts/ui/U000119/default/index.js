
(function(){

 var staticDomain = $('[data-static-domain]:eq(0)').attr('data-static-domain') ? $('[data-static-domain]:eq(0)').attr('data-static-domain') : "";
  
  loadCss(staticDomain+'/resources/javascripts/library/swiper/swiper.min.css');

  var o = {
    init: function () {
      // 兼容一个页面多个时间轴组件
      $('.geshop-time-line-container').each(function (i, element) {
        var $ele = $(element),
          swiperContainer = $ele.find('.swiper-container'),
          isDefaultView = $ele.find('[data-is-default-view]').attr('data-is-default-view')
        
        // 组件默认状态
        if(isDefaultView == 1) {
          new Swiper3(swiperContainer, {
            freeMode : true,
            slidesPerView :'auto',
            onInit: function (swiper) {
              swiper.slideTo(0, 0, false);
            }
          })
        } else {
          new Swiper3(swiperContainer, {
            freeMode : true,
            slidesPerView :'auto',
            onInit: function (swiper) {
              swiperContainer.find('li').each(function (i, element) {
                var startTime = $(element).attr('data-starttime'),
                  endTime = $(element).attr('data-endtime'),
                  currentTime = Date.parse(new Date())/1000;
                
                if (currentTime > startTime && currentTime < endTime) {
                  $(element).addClass('active');
                } else if (currentTime > endTime) {
                  $(element).addClass('hasEndedClass');
                } else {
                  $(element).addClass('hasNotStartClass');
                }

                var s_time = new Date(startTime * 1000);
                var e_time = new Date(endTime * 1000);
                var s_month = s_time.getMonth() + 1;
                var s_date = s_time.getDate() < 10 ? '0' + s_time.getDate() : s_time.getDate();
                var e_month = e_time.getMonth() + 1;
                var e_date = e_time.getDate() < 10 ? '0' + e_time.getDate() : e_time.getDate();
                var result = s_month + '.' + s_date + ' - ' + e_month + '.' + e_date;
                $(element).find('.header-timeline-date').text(result);

              });
              var index = swiperContainer.find('li.active:eq(0)').attr('data-index');
              index = index == 0 ? index : index - 1;
              swiper.slideTo(index, 100, false);
            }
          })
        }
      })
    }
  }

  $LAB
    .script(staticDomain+'/resources/javascripts/library/swiper/swiper.3.4.spec.min.js')
    .wait(function () {
      o.init();
    });

})();
