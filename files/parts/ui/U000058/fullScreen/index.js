;$(function () {
	var staticDomain = $('[data-static-domain]:eq(0)').attr('data-static-domain') ? $('[data-static-domain]:eq(0)').attr('data-static-domain') : '';

	loadCss(staticDomain + '/resources/javascripts/library/swiper/swiper.min.css');
	$LAB.script(staticDomain + '/resources/javascripts/library/swiper/swiper.3.4.spec.min.js').wait(function () {

		$('.wrap-U000058_fullScreen .geshop-index-banner').each(function(i, element) {
			var id = $(element).data('id');
			var length = $(element).find('.swiper-slide').length;
			var swiperContainer = $('.geshop-swiper-container-' + id);
			var paginationElement = $(element).find('.swiper-pagination');
			
			if (length == 1) {
				new Swiper3(swiperContainer, {
					autoplayDisableOnInteraction: true,
					onInit:function(swiper) {
						gbLogsss.getsku($(swiper.slides).find('.ge-banner-img'));
						gbLogsss.sendsku();
					}
				});
			} else {
				new Swiper3(swiperContainer, {
					autoplay: 5000,
					autoplayDisableOnInteraction: false,
					pagination: paginationElement,
					paginationClickable: true,
					loop: true,
					onSlideChangeEnd: function(swiper) {
						var $slides = swiperContainer.find('.geshop-swiper-slide');
						var $slide = $slides.eq(swiper.activeIndex);
						var src = $slide.find('.ge-banner-img').data('src'); 
						if(src){
							$slide.find('.ge-banner-img').css('background-image','url('+src+')')
						}
						var $slideRealIndex = $slides.eq(swiper.realIndex);
						
						if (swiper.realIndex != 0 && $slideRealIndex.data('isLoad')!=1 && typeof gbLogsss != 'undefined') {
							$slideRealIndex.data('isLoad',1);
							gbLogsss.getsku($slideRealIndex.find('.ge-banner-img'));
							gbLogsss.sendsku();
						}
					},
					onInit: function (swiper) {
						var $slide = swiperContainer.find('.swiper-slide-duplicate-active');
						$slide.data('isLoad',1);
						
						if (swiper.realIndex == 0 && typeof gbLogsss != 'undefined') {
							gbLogsss.getsku($slide.find('.ge-banner-img'));
							gbLogsss.sendsku();
						}
					}
				});
			}
		});
	});
});
