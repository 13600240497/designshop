$(function(){
    var staticDomain = $('[data-static-domain]:eq(0)').attr('data-static-domain') ? $('[data-static-domain]:eq(0)').attr('data-static-domain') : "";
	loadCss(staticDomain + '/resources/javascripts/library/swiper/swiper.min.css');
	$LAB.script(staticDomain + '/resources/javascripts/library/swiper/swiper.3.4.spec.min.js').wait(function () {
       function sendLoggssData($target){
			
			gbLogsss.getsku($target);
			gbLogsss.sendsku();
        }
        $('.wrap-U000064_mobile').each(function(){

        
            new Swiper3($(this).find('.swiper-container'), {
                    slidesPerView: 2.4,
                spaceBetween: 5,
               lazyLoading : true,
               scrollbar: $(this).find('.swiper-scrollbar'),
               onLazyImageLoad:function(swiper, slide, image) {
                    if (typeof gbLogsss != 'undefined'&&swiper.realIndex>0) {
                        sendLoggssData($(image))
                    }
                }
            });
        })
    }) 
});

