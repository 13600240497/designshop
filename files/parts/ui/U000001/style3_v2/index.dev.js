; $(function () {
    var staticDomain = GESHOP_STATIC;
	$LAB.script(staticDomain + '/resources/javascripts/library/jquery.SuperSlide.2.1.3.js').wait(function () {
		if ($.fn.slide) {
			$(".geshop-U000001-style3-v2-wrapper").each(function () {
				var $this = $(this);
				$this.find(".geshop-U000001-style3-v2-wrapper-swiper").slide({
					mainCell: "ul",
					prevCell: $this.find(".button-swiper-prev"),
					nextCell: $this.find(".button-swiper-next"),
					interTime: 5000,
					autoPlay: true,
					vis: 3,
					scroll: 3,
					autoPage: true,
					effect: "leftLoop",
					endFun: function () {
                        if ($.fn.lazyload) {
                            $this.find('img.js-geshopImg-lazyload').lazyload({
                                threshold: 100,
                                failure_limit: 40,
                                skip_invisible: false
                            });
                        } else {
                            window.GS_GOODS_LAZY_FN('.js-geshopImg-lazyload');
                        }
					}
				});
			})
		} else {
			console.error('slide not exit!')
		}
	})

});
