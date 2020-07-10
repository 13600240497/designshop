; $(function () {
	var staticDomain = $('[data-static-domain]:eq(0)').attr('data-static-domain') ? $('[data-static-domain]:eq(0)').attr('data-static-domain') : "";
	$LAB.script(staticDomain + '/resources/javascripts/library/jquery.SuperSlide.2.1.1.js').wait(function () {
		if ($.fn.slide && !$(".gs-goods-adslider").data('slide')) {
			$(".gs-goods-adslider").slide({
				mainCell: "ul",
				prevCell: ".prev-btn",
				nextCell: ".next-btn",
				interTime: 5000,
				autoPlay: true,
				vis: 3,
				scroll: 3,
				autoPage: true,
				effect: "leftLoop",
				endFun: function () {
					if ($.fn.lazyload) {
						$('.gs-goods-adslider').find('img.lazyload').lazyload({
							threshold: 100,
							failure_limit: 40,
							skip_invisible: false
						}).attr('style', "max-width: 100%; max-height: 100%; display: inline;")
					} else {
						window.GS_GOODS_LAZY_FN('.lazyload');
					}
				}
			}).data('slide', true);
		} else {
			console.error('slide not exit!')
		}
	})

});
