;$(function () {
 var staticDomain = $('[data-static-domain]:eq(0)').attr('data-static-domain') ? $('[data-static-domain]:eq(0)').attr('data-static-domain') : "";
  loadCss(staticDomain+'/resources/javascripts/library/slick/slick.css');
	$LAB.script(staticDomain+'/resources/javascripts/library/slick/slick.min.js').wait(function () {
		$('.geshop_index_deals_zone_item_1').show();
		
		if (Number($('[data-key="U000084"][data-is-edit]').get(0).getAttribute('data-is-edit')) != 1) {
			$('.geshop_index_deals_zone').slick({
				slidesToShow: 4,
				autoplay: true,
				prevArrow: '<a href="javascript:;" class="index-deals-zone-prev"></a>',
				nextArrow: '<a href="javascript:;" class="index-deals-zone-next"></a>'
			})
			.on('afterChange', function (event, slick, currentSlide) {
				var index,
					length = $('.index-deals-zone-item').length;

				$('.index-deals-zone-item').hide();

				index = ((currentSlide + 1) == length ? 0 : currentSlide + 1);
				$('.geshop-index-deals-zone-item-' + index).show();

				var hiddenSlick = $('.geshop_index_deals_zone .slick-current').next().next().next();
				
				if (hiddenSlick.length === 0) {
					return false;
				} else {
					var imgTag = hiddenSlick.find('img'),
						imgSrc = imgTag.attr('src');
					if (imgSrc.indexOf('data:image') != -1) {
						var originalSrc = imgTag.attr('data-original');

						imgTag.attr('src', originalSrc);
					}
				}
			});
		}

		$('.index-deals-zone-item').removeClass('custom-area');
		$('.index-deals-deal-screen-right').removeClass('custom-right');
	})
});
