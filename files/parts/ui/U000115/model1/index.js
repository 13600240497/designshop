;$(function () {
	var staticDomain = $('[data-static-domain]:eq(0)').attr('data-static-domain') ? $('[data-static-domain]:eq(0)').attr('data-static-domain') : "";

	loadCss(staticDomain + '/resources/javascripts/library/swiper/swiper.min.css');
	$LAB.script(staticDomain + '/resources/javascripts/library/swiper/swiper.3.4.spec.min.js').wait(function() {
		$('.wrap-U000115_mobile .geshop-index-banner').each(function () {
			var that = $(this);
			var id = that.data('id');
			var length = that.find('.swiper-slide').length;
			var config = {};

			if (length > 1) {
				config = {
					autoplay: 5000,
					pagination: that.find('.swiper-pagination'),
					lazyLoading : true,
					onLazyImageLoad:function(swiper, slide, image) {
						if (typeof gbLogsss != 'undefined') {
							gbLogsss.getsku($(image));
							gbLogsss.sendsku();
						}
					}
				}
				
				new Swiper3('.swiper-container.geshop-index-banner-' + id, config);
			}
		});
  })
});
