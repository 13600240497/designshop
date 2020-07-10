(function () {
	var staticDomain = $('[data-static-domain]:eq(0)').attr('data-static-domain') ? $('[data-static-domain]:eq(0)').attr('data-static-domain') : "";
	loadCss(staticDomain + '/resources/javascripts/library/swiper/swiper.min.css');

	var spikeGoodsApi = GESHOP_INTERFACE ? GESHOP_INTERFACE.spikegoods.url : '';
	
	function getDataList(url){
	    var ext = new Date().getTime();
		$.ajax({
			url: spikeGoodsApi + '?' + parseInt(new Date().getTime() / 1000),
			jsonp: 'callback',
			dataType: 'jsonp',
            jsonpCallback: `geshop_callback_${ext}`,
			success: function (res) {
				$('[data-key="U000076"] .geshop-loading').remove();
				$('[data-key="U000076"] .geshop-flash-sale-block').show();

				if (res.code == 0) {
					var target;

					if (res.data.is_show) {
						var totalSeconds = res.data.seconds;
						var hours, minutes, seconds;

						setInterval(function () {
							if (totalSeconds > 1) {
								totalSeconds = totalSeconds - 1;
							} else {
								totalSeconds = 0;
							}

							if (totalSeconds == 0) {
								$('.geshop-component-box[data-key=U000076]').remove();
							}

							hours = parseInt(totalSeconds / 3600);
							minutes = parseInt((totalSeconds - hours * 3600) / 60);
							seconds = totalSeconds - hours * 3600 - minutes * 60;

							$('.js_geshopFlashSaleHour').text((hours < 10 ? '0' : '') + String(hours));
							$('.js_geshopFlashSaleMinute').text((minutes < 10 ? '0' : '') + String(minutes));
							$('.js_geshopFlashSaleSecond').text((seconds < 10 ? '0' : '') + String(seconds));
						}, 1000);

						$('.js_geshopFlashSaleMore').attr('href', res.data.view_more);

						var length = res.data.goods_list.length;
						var html = '';

						target = $('.geshop-component-box[data-key=U000076]').get(0);

						for (var index = 0; index < length; index = index + 1) {
							html += '' +
								'<div class="geshop-flash-sale-item swiper-slide">' +
								(res.data.goods_list[index].discount > 0 ? '<span class="geshop-flash-sale-discount" style="color: ' + target.getAttribute('data-fontColorDiscount') + '; background-color: ' + target.getAttribute('data-bgColorDiscount') + ';">-' + res.data.goods_list[index].discount + '%</span>' : '') +
								'<a class="geshop-flash-sale-picture" href="' + res.data.goods_list[index].link + '">' +
								'<img alt="' + res.data.goods_list[index].title + '" src="' + res.data.goods_list[index].picture + '">' +
								(res.data.goods_list[index].lefts == 0 ? '<span class="geshop-flash-sale-sold-out">SOLD OUT</span>' : '') +
								'</a>' +
								'<p class="geshop-flash-sale-left">' +
								'<span class="geshop-flash-sale-left-total">' +
								'<span class="geshop-flash-sale-left-still" style="width: ' + (res.data.goods_list[index].lefts / res.data.goods_list[index].total).toFixed(2) * 100 + '%; background-color: ' + target.getAttribute('data-bgColorFlashed') + ';"></span>' +
								'</span>' +
								'<span class="geshop-flash-sale-left-number">' + res.data.goods_list[index].lefts + ' left</span>' +
								'</p>' +
								'<p class="geshop-flash-sale-price">' +
								'<span class="geshop-flash-sale-price-sale site-bold-strict" style="color: ' + target.getAttribute('data-fontColorSalePrice') + ';">' +
								'<strong class="bizhong">USD</strong><span class="bz_icon"></span>' +
								'<span class="my_shop_price" data-orgp="' + res.data.goods_list[index].shop_price + '">' + '$' + res.data.goods_list[index].shop_price + '</span>' +
								'</span>' +
								'<span class="geshop-flash-sale-price-market" style="color: ' + target.getAttribute('data-fontColorMarketPrice') + ';">' +
								'<strong class="bizhong">USD</strong><span class="bz_icon"></span>' +
								'<span class="my_shop_price" data-orgp="' + res.data.goods_list[index].market_price + '">' + '$' + res.data.goods_list[index].market_price + '</span>' +
								'</span>' +
								'</p>' +
								'<a class="geshop-flash-sale-link" href="' + res.data.goods_list[index].link + '" style="' +
								'color: ' + target.getAttribute('data-fontColorBuyNow') + ';' +
								'background-color: ' + target.getAttribute('data-bgColorBuyNow') + ';' +
								'border: 1px solid ' + target.getAttribute('data-borderColorBuyNow') + ';' +
								'">BUY NOW</a>' +
								'</div>';
						}
					
						$('.js_geshopFlashSaleBody').prepend(html);
						
						$LAB.script(staticDomain + '/resources/javascripts/library/swiper/swiper.3.4.spec.min.js').wait(function() {
							new Swiper3('.geshop-flash-sale-body.swiper-container', {
								freeMode: true,
								slidesPerView: 'auto'
							});
						});
						
					} else {
						target = $('.geshop-component-box[data-key=U000076]')

						if (target.closest('#design_view').length <= 0) {
							target.remove();
						}

						if (target.closest('#design_view').length > 0) {
							layui.layer.msg('当前没有可用的秒杀活动，请去站点后台创建')
						}
					}
				}
			}
		})
	}
	
	getDataList();
})();
