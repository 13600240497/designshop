; $( function() {
    var staticDomain = typeof GESHOP_STATIC == "undefined" ? "" : GESHOP_STATIC;

    $LAB.script(staticDomain + '/resources/javascripts/library/swiper/swiper.3.4.spec.min.js').wait(function () {
        $('[data-gid=U000198_default] .swiper-container').each( function(index, element) {
			var swiperTimeGoods = new Swiper3(this, {
			    freeMode: true,
                freeModeMomentumRatio: 0.5,
			    slidesPerView: 'auto',
			});
		})
    })
})
