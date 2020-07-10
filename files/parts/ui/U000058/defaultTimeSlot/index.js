;$(function () {
	var staticDomain = $('[data-static-domain]:eq(0)').attr('data-static-domain') ? $('[data-static-domain]:eq(0)').attr('data-static-domain') : "";

	loadCss(staticDomain + '/resources/javascripts/library/swiper/swiper.min.css');

	$LAB.script(staticDomain + '/resources/javascripts/library/swiper/swiper.3.4.spec.min.js').wait(function () {

		$('[data-gid="U000058_default_timeslot"] .geshop-index-banner').each(function(i, element) {

			var id = $(element).data('id');
			var length = $(element).find('.swiper-slide').length;
			var swiperContainer = $('.geshop-swiper-container-' + id);
			var prevButtonElement = $(element).find('.swiper-button-prev');
			var nextButtonElement = $(element).find('.swiper-button-next');
			var paginationElement = $(element).find('.swiper-pagination');
	
			if (length == 1) {
				new Swiper3(swiperContainer, {
					autoplayDisableOnInteraction: true,
					lazyLoading: true,
					onLazyImageLoad:function(swiper, slide, image) {
						gbLogsss.getsku($(image));
						gbLogsss.sendsku();
					}
				});
			} else {
				new Swiper3(swiperContainer, {
					autoplay: 5000,
					autoplayDisableOnInteraction: false,
					prevButton: prevButtonElement,
					nextButton: nextButtonElement,
					pagination: paginationElement,
					lazyLoading: true,
					loop: true,
					onLazyImageLoad: function(swiper, slide, image) {
						if (swiper.realIndex != 0 && !$(slide).hasClass('swiper-slide-duplicate') && typeof gbLogsss != 'undefined') {
							gbLogsss.getsku($(image));
							gbLogsss.sendsku();
						}
					},
					onInit: function (swiper) {
						if (swiper.realIndex == 0 && typeof gbLogsss != 'undefined') {
							gbLogsss.getsku($(swiper.slides[1]).find('.swiper-lazy'));
							gbLogsss.sendsku();
						}
					}
				});
		
				$(element).hover(function() {
					prevButtonElement.css('display', 'block');
					nextButtonElement.css('display', 'block');
				}, function() {
					prevButtonElement.css('display', 'none');
					nextButtonElement.css('display', 'none');
				});
			}
		});
	});

	});
