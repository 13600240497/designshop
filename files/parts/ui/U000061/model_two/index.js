;(function(){
	var staticDomain = $('[data-static-domain]:eq(0)').attr('data-static-domain') ? $('[data-static-domain]:eq(0)').attr('data-static-domain') : "";
	loadCss(staticDomain + '/resources/javascripts/library/swiper/swiper.min.css');

	$(function () {
		$('body').on('click', '.js_quickShop', function() {
			var url = $(this).attr('data-href');
			var glb_p = $(this).parents('.geshop-component-box').eq(0).attr('data-p');
            window.sessionStorage.setItem('logsss-categoryid', glb_p);
			GEShopSiteCommon.dialog.iframe(url, 1080, 597, true);
		});

		$LAB.script(staticDomain + '/resources/javascripts/library/swiper/swiper.3.4.spec.min.js').wait(function() {
			$.each($('.js-geshop-swiper-container'),function (i,v) {
				var length = $(this).find('.swiper-slide').length;

				var config = {
					autoplay: false,
					slidesPerView: length<4?length:4,
					slidesPerGroup: 4,
					nextButton: '.geshop-next-btn',
					prevButton: '.geshop-pre-btn',
					loop: length<4?false:true,
					lazyLoading : true,
					onLazyImageLoad:function(swiper, slide, image) {

						if( typeof gbLogsss != 'undefined'  && !$(slide).hasClass('swiper-slide-duplicate')){
							gbLogsss.getsku($(image));
							gbLogsss.sendsku();
						}
					}
				};
				new Swiper3($(this), config);
			});
		});
	});
})();


