
;$(function () {
	var staticDomain = $('[data-static-domain]:eq(0)').attr('data-static-domain') ? $('[data-static-domain]:eq(0)').attr('data-static-domain') : "";

	loadCss(staticDomain + '/resources/javascripts/library/swiper/swiper.min.css');
	$LAB.script(staticDomain + '/resources/javascripts/library/swiper/swiper.3.4.spec.min.js').wait(function() {
		var length = $('.geshop-index-goods .swiper-slide').length;

		if (length < 4) {
			new Swiper3('.geshop-index-goods.swiper-container', {
				autoplay: false,
				slidesPerView: length,
				slidesPerGroup: 4,
				nextButton: '.swiper-button-next',
        prevButton: '.swiper-button-prev'
			});
		} else {
			new Swiper3('.geshop-index-goods.swiper-container', {
				autoplay: false,
				slidesPerView: 4,
				slidesPerGroup: 4,
				nextButton: '.swiper-button-next',
        prevButton: '.swiper-button-prev'
			});
		}

		$('.geshop-index-goods .swiper-button-prev').css("display", "block");
		$('.geshop-index-goods .swiper-button-next').css("display", "block");
 	})
});
