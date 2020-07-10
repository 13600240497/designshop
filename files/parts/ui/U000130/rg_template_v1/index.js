$(function () {
	var staticDomain = $('[data-static-domain]:eq(0)').attr('data-static-domain') ? $('[data-static-domain]:eq(0)').attr('data-static-domain') : "";
	loadCss(staticDomain + '/resources/javascripts/library/swiper/swiper.min.css');

	function getDataList() {
		var currentTime = new Date().getTime();

		$('.geshop-component-U000130-rg_template_v1[data-key=U000130]').each(function (index,element) {
			var target = $(this);
			var skus = target.data('skus') || [];
			var pageId = target.data('pageId');
			var pageInstanceId = target.data('pageInstanceId');
			var layoutIndex = target.data('layoutIndex');
			var compKey = target.data('compKey');
			var uiIndex = target.data('uiIndex');
			var count = skus.length;
			var index;
			var isRemoved = true;

			for (index = 0; index < count; index++) {
				if (currentTime > skus[index].dataStartTime &&currentTime < skus[index].dataEndTime) {
					isRemoved = false;

					var sku = skus[index];
					var hasNext = false;

					for (var nextIndex = 0; nextIndex < count; nextIndex++) {
						if (index != nextIndex && skus[index].dataEndTime < skus[nextIndex].dataStartTime) {
							hasNext = true;
						}
					}
                    var url = GESHOP_INTERFACE.timeseckilldetail.url;
					var params = {
                        lang: GESHOP_LANG ? GESHOP_LANG : 'en',
                        goodsSn: sku.lists,
                        pipeline: (typeof GESHOP_PIPELINE != 'undefined' ? GESHOP_PIPELINE : ''),
                        v:'2'
                    }
                    window.GEShopCommonFn_Vue.$jsonp(url,params,{target:element}).done(function (res) {
                        target.find('.geshop-loading, .geshop-flash-sale-default-img').remove();
                        target.find('.geshop-flash-sale-block').show();

                        if (res.code == 0) {
                            target.find('.js_geshopFlashSaleTitle').text(sku.tabTitle);

                            var totalSeconds;
                            var days, hours, minutes, seconds;

                            totalSeconds = parseInt((sku.dataEndTime - currentTime) / 1000);

                            var clock = setInterval(function () {
                                if (totalSeconds > 1) {
                                    totalSeconds = totalSeconds - 1;
                                } else {
                                    totalSeconds = 0;
                                }

                                if (totalSeconds == 0) {
                                    if (target.closest('#design_view').length <= 0) {
                                        target.remove();
                                    }

                                    clearInterval(clock);
                                }

                                days = parseInt(totalSeconds / 86400);
                                hours = parseInt((totalSeconds - days * 86400) / 3600);
                                minutes = parseInt((totalSeconds - days * 86400 - hours * 3600) / 60);
                                seconds = totalSeconds - days * 86400 - hours * 3600 - minutes * 60;

                                target.find('.js_geshopFlashSaleDay').text((days < 10 ? '0' : '') + String(days));
                                target.find('.js_geshopFlashSaleHour').text((hours < 10 ? '0' : '') + String(hours));
                                target.find('.js_geshopFlashSaleMinute').text((minutes < 10 ? '0' : '') + String(minutes));
                                target.find('.js_geshopFlashSaleSecond').text((seconds < 10 ? '0' : '') + String(seconds));
                            }, 1000);
                            var btnText = target.find('input[name=btnText]').val();
                            var isShowBtn = target.find('input[name=isShowBtn]').val();
                            var goods = res.data.goodsInfo;
                            var keys = Object.keys(goods);
                            var length = keys.length;
                            var html = '';
                            var lazyGoodsImg = target.data('goods-lazyimg');
                            var lang_sold_out = GESHOP_LANGUAGES["btn_sold_out"];
                            for (var index = 0; index < length; index = index + 1) {
                                var goodsItem = goods[keys[index]];
                                var sold_out_html = '';
                                if(Number(goodsItem.goods_number) === 0 || Math.abs(goodsItem.activity_number - goodsItem.activity_volume_number) === 0){
                                    sold_out_html =  '<div class="geshop-components-soldout-bottom"> <div class="geshop-soldout-wrapper">\n' +
                                        '<span class="geshop-soldout-table">\n' +
                                        '<label>'+lang_sold_out+'</label>\n' +
                                        '</span>\n' +
                                        '</div></div>'
                                }

                                html += '' +
                                    '<div class="geshop-flash-sale-item swiper-slide">' +
                                    '<div class="geshop-image-box">' +
                                    '<a class="geshop-flash-sale-picture logsss_event" href="' + goods[keys[index]].url_title + '" ' +
                                    'data-logsss-event-value="{ \'pm\':\'mr\',\'p\':\'p-' + pageId + '\',\'ubcta\':{\'cpID\':\'' + pageInstanceId + '\',\'cpnum\':\'' + compKey + '\',\'cplocation\':\'' + uiIndex + '\',\'sku\':\'' + goods[keys[index]].goods_sn + '\',\'cporder\':\'' + layoutIndex + '\',\'rank\':\'' + index + '\'},\'skuinfo\':{\'sku\':\'' + goods[keys[index]].goods_sn + '\',\'pam\':\'0\',\'pc\':\'' + goods[keys[index]].cateid + '\',\'k\':\'' + goods[keys[index]].warehousecode + '\'} }"' +
                                    '>' +
                                    '<img class="swiper-lazy" alt="' + goods[keys[index]].goods_title + '" src="'+lazyGoodsImg+'"  data-src="' + goods[keys[index]].goods_img + '" ' +
                                    'data-logsss-browser-value="{ \'pm\':\'mr\',\'p\':\'p-' + pageId + '\',\'bv\':{\'cpID\':\'' + pageInstanceId + '\',\'cpnum\':\'' + compKey + '\',\'cplocation\':\'' + uiIndex + '\',\'sku\':\'' + goods[keys[index]].goods_sn + '\',\'cporder\':\'' + layoutIndex + '\',\'rank\':\'' + index + '\'} }"' +
                                    '>' +
                                    // ((hasNext && index == length - 1) ? '<span class="geshop-flash-sale-upcoming"><span class="geshop-flash-sale-upcoming-time">23:00</span><span class="geshop-flash-sale-upcoming-text">Upcoming</span></span>' : '') +
                                    '</a>' + sold_out_html +
                                    '</div>' +
                                    '<p class="geshop-flash-sale-price">' +
                                    '<span class="geshop-flash-sale-price-sale site-bold-strict">' +
                                    '<strong class="bizhong">USD</strong><span class="bz_icon">$</span>' +
                                    '<span class="my_shop_price" data-orgp="' + goods[keys[index]].shop_price + '">' + goods[keys[index]].shop_price + '</span>' +
                                    '</span>' +
                                    '<span class="geshop-flash-sale-price-market js_market_wrap">' +
                                    '<strong class="bizhong">USD</strong><span class="bz_icon">$</span>' +
                                    '<span class="my_shop_price" data-orgp="' + goods[keys[index]].market_price + '">' + goods[keys[index]].market_price + '</span>' +
                                    '</span>' +
                                    '</p>';
                                if(isShowBtn === '1'){
                                    html += '<a class="buyBtn" href="' + goods[keys[index]].url_title + '" ' +
                                        'data-logsss-event-value="{ \'pm\':\'mr\',\'p\':\'p-' + pageId + '\',\'ubcta\':{\'cpID\':\'' + pageInstanceId + '\',\'cpnum\':\'' + compKey + '\',\'cplocation\':\'' + uiIndex + '\',\'sku\':\'' + goods[keys[index]].goods_sn + '\',\'cporder\':\'' + layoutIndex + '\',\'rank\':\'' + index + '\'},\'skuinfo\':{\'sku\':\'' + goods[keys[index]].goods_sn + '\',\'pam\':\'0\',\'pc\':\'' + goods[keys[index]].cateid + '\',\'k\':\'' + goods[keys[index]].warehousecode + '\'} }"' +
                                        '>'+ btnText +'</a>';
                                }
                                html +='</div>';
                            }

                            target.find('.js_geshopFlashSaleBody').prepend(html);

                            // target.find('.js_geshopFlashSaleBody img').each(function () {
                            // 	if (typeof gbLogsss != 'undefined') {
                            // 		var $target = $(this);

                            // 		gbLogsss.getsku($target);
                            // 		gbLogsss.sendsku();
                            // 	}
                            // });

                            if (window.GLOBAL && window.GLOBAL.currency) {
                                window.GLOBAL.currency.change_html();
                            }

                            if (window.FUN && window.FUN.currency) {
                                window.FUN.currency.change_html();
                            }

                            $LAB.script(staticDomain + '/resources/javascripts/library/swiper/swiper.3.4.spec.min.js').wait(function() {
                                function sendLoggssData($target){

                                    gbLogsss.getsku($target);
                                    gbLogsss.sendsku();
                                }
                                new Swiper3('.geshop-flash-sale-body.swiper-container', {

                                    slidesPerView: 'auto',
                                    scrollbar: '.swiper-scrollbar',

                                    watchSlidesProgress: true,
                                    watchSlidesVisibility: true,
                                    lazyLoading : true,
                                    onLazyImageLoad:function(swiper, slide, image) {
                                        if (typeof gbLogsss != 'undefined'&&swiper.realIndex>0) {
                                            sendLoggssData($(image))
                                        }
                                    }
                                });
                            });
                        }
                    })
				}
			}

			if (isRemoved && target.closest('#design_view').length <= 0) {
				target.remove();
			}
		});
	}

	getDataList();
});
