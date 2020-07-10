
$.fn.extend({
	flashSaleInit: function(params,pid) {

		// 初始化倒计时
		function countdownInit(startDate, endDate, target) {
			var totalSeconds, day, hours, minutes, seconds;
			var startTime = Number(startDate);
			var endTime = Number(endDate);
			var nowTime = new Date().getTime();
			if (nowTime>startTime && nowTime<endTime) {
				totalSeconds = parseInt((endTime - nowTime)/1000);
			} else {
				totalSeconds = 0;
			}
			if (totalSeconds>0) {
				that.find('.geshop-loading').remove();
				that.find('.geshop-flash-sale-block').show();
				setInterval(function () {
					totalSeconds = (totalSeconds>1) ? totalSeconds-1 : 0;
					var leftSecond = totalSeconds;
					totalSeconds == 0 && target.remove();
					day = parseInt(leftSecond / (3600*24));
					leftSecond = leftSecond - day * 24 * 3600;
					hours = parseInt(leftSecond / 3600);
					leftSecond = leftSecond - hours * 3600;
					minutes = parseInt(leftSecond / 60);
					leftSecond = leftSecond - minutes * 60;
					seconds = leftSecond;
					target.find('.js_geshopFlashSaleDay').text((day < 10 ? '0' : '') + String(day));
					target.find('.js_geshopFlashSaleHour').text((hours < 10 ? '0' : '') + String(hours));
					target.find('.js_geshopFlashSaleMinute').text((minutes < 10 ? '0' : '') + String(minutes));
					target.find('.js_geshopFlashSaleSecond').text((seconds < 10 ? '0' : '') + String(seconds));
				}, 1000);
			}
		}

		/*
			创建产品Html Node 标签
			@params {
				...商品列表接口返回的字段
				leftPercent: 剩余百分比
			}
			return String
		*/
		function createGoodNode(params, langCode, languages) {
			var template = '<div class="geshop-flash-sale-item swiper-slide"><span class="geshop-flash-sale-discount"><table border="0"><tr><td>{discountLabel}</td></tr></table></span><a class="geshop-flash-sale-picture" href="{link}">' +
				'<div class="swiper-lazy-preloader"></div><img class="swiper-lazy" alt="{title}" src="data:image/gif;base64,R0lGODlhAQABAIAAAPHx8QAAACwAAAAAAQABAAACAkQBADs=" data-src="{picture}"><div class="geshop-flash-sale-sold-out"><div class="geshop-flash-sale-sold-out-inner"><span>{soldoutText}</span></div></div>' +
				'</a><p class="geshop-flash-sale-price"><span class="geshop-flash-sale-price-sale"><strong class="bizhong">USD</strong><span class="bz_icon"></span><span class="my_shop_price" data-orgp="{shop_price}">${shop_price}</span>' +
				'</span><span class="geshop-flash-sale-price-market js_market_wrap"><strong class="bizhong">USD</strong><span class="bz_icon"></span><span class="my_shop_price" data-orgp="{market_price}">${market_price}</span>' +
				'</span></p><p class="geshop-flash-sale-left"><span class="geshop-flash-sale-left-total"><span class="geshop-flash-sale-left-still" style="width: {leftPercent}%;"></span>' +
				'</span><span class="geshop-flash-sale-left-number">{lefts}</span></p><a class="geshop-flash-sale-link" href="{link}">{goodsBtnText}</a></div>';
			template = template.replace(/{discountLabel}/g, params.discountLabel);
			template = template.replace(/{title}/g, params.goods_title);
			template = template.replace(/{picture}/g, params.goods_img);
			template = template.replace(/{link}/g, geshopUrlToApp(params.url_title,params.goods_id));
			template = template.replace(/{shop_price}/g, params.shop_price);
			template = template.replace(/{market_price}/g, params.market_price);
			template = template.replace(/{goodsBtnText}/g, params.goodsBtnText);
			template = template.replace(/{leftPercent}/g, params.leftPercent||100);
			template = template.replace(/{soldoutText}/g, params.soldoutText);

			// 根据销售状态，展示不同的文案排版
			// var left_code = (parseInt((params.activity_volume_number/params.activity_number)*100)>=90) ? 'left' : 'sales';
			// 始终显示 only xx left
			// var left_code = 'left';
			template = template.replace(/{lefts}/g, unescape(GESHOP_LANGUAGES['left']).replace('XX', params.left));

			template = $(template);
			if (params.left>0) template.find('.geshop-flash-sale-sold-out').remove();
			if (params.discount<=0) template.find('.geshop-flash-sale-discount').remove();

			return template;
		}

		function createDiscountLabel(discount, langCode, languages) {
			var label = unescape(JSON.parse(languages).discount[langCode].replace(/\u/g, "%u"));
				label = label.replace('XX%', '<span>XX%</span>');
				label = label.replace('-XX%', '<span>-XX%</span>');
				label = label.replace('%XX', '<span>%XX</span>');
			return label.replace('XX', parseInt(discount));
		}

		//
		function deepCloneObj(obj) {
			return JSON.parse(JSON.stringify(obj));
		}

        /**
         * 时间区间格式化
         * @param {String} dateRange 时间区间
         * @return {Array} [startTime, endTime] 时间戳
         */
        function dateFormate (dateRange) {
            if (dateRange === '') {
                return [0, 0];
            };
            try {
                var start = dateRange.split(' - ')[0].replace(/-/g, '/');
                var startTime = new Date(start).getTime();
                var end = dateRange.split(' - ')[1].replace(/-/g, '/');
                var endTime = new Date(end).getTime();
                return [startTime, endTime];
            } catch (err) {
                return [0, 0];
            }
        }


		var that = $(this);

		// 初始化倒计时
		if (params.isEditEnv == 0) {
            var timeArray = dateFormate(params.dataRange || '');
            var startTime = timeArray[0];
            var endTime = timeArray[1];
			countdownInit(startTime, endTime, that);
		} else {
			that.find('.geshop-loading').remove();
			that.find('.geshop-flash-sale-block').show();
		}


		var staticDomain = $(this).attr('data-static-domain');
		loadCss(staticDomain + '/resources/javascripts/library/swiper/swiper.min.css');

		if (params.goodsSKU!='') {
			var sku = params.goodsSKU.split(',').filter(function (e) {
				return e != ""
			}).join(',');
			var requestParams = {
				goodsSn: sku,
				lang:  typeof GESHOP_LANG != 'undefined' ? GESHOP_LANG : 'en',
				pipeline: (typeof GESHOP_PIPELINE != 'undefined' ? GESHOP_PIPELINE : ''),
				v:'2'
			};
			var url = GESHOP_INTERFACE.timeseckilldetail.url
            window.GEShopCommonFn_Vue.$jsonp(url, requestParams, { pid: pid }).done(function (res) {
                if (res.code == 0) {
                    /* 过滤空数据 */
                    var dataRows = [];
                    for (var i = 0; i < res.data.goodsInfo.length; i++) {
                        if (res.data.goodsInfo[i].goods_sn) {
                            dataRows.push(res.data.goodsInfo[i]);
                        }
                    }

                    for (var i = dataRows.length - 1; i >= 0; i--) {
                        var obj = dataRows[i];
                        obj['left'] = (dataRows[i].activity_number-dataRows[i].activity_volume_number);
                        obj['discountLabel'] = createDiscountLabel(obj.discount, params.langCode, params.languages);
                        obj['soldoutText'] = that.attr('data-soldouttext');
                        obj['goodsBtnText'] = that.attr('data-goodsbtntext');
                        obj['leftPercent'] = ((dataRows[i].activity_number-dataRows[i].activity_volume_number) / dataRows[i].activity_number).toFixed(2) * 100;
                        that.find('.js_geshopFlashSaleBody').prepend(createGoodNode(obj, params.langCode, JSON.parse(params.languages)||{}));
                    }
                    that.closest('.geshop-component-box').css('min-height','auto');
                    if (window.GLOBAL && window.GLOBAL.currency) {
                        window.GLOBAL.currency.change_html()
                    }
                    if (window.FUN && window.FUN.currency) {
                        window.FUN.currency.change_html()
                    }
                    $LAB.script(staticDomain + '/resources/javascripts/library/swiper/swiper.3.4.spec.min.js').wait(function() {
                        new Swiper3('.geshop-flash-sale-body.swiper-container', {
                            freeMode:true,
                            lazyLoading : true,
                            slidesPerView :'auto',
                            lazyLoadingInPrevNext : true,
                            lazyLoadingInPrevNextAmount:3,
                            scrollbar: '.swiper-scrollbar'
                        });
                    });
                }
            })
		} else {
			if (params.isEditEnv==0) return false;
			var goodTestData = { "goods_id": "972364", "goods_sn": "164559901", "is_on_sale": "1", "activity_number": 20, "activity_volume_number": 0, "left_time": 0, "url_title": "", "goods_img": "", "market_price": "0.00", "shop_price": 0, "discount": 50, "warehousecode": "SZ", "cateid": "12", "catename": "Swimwear" };
			var dataRows = [goodTestData, goodTestData, goodTestData, goodTestData];
			for(i in dataRows) {
				var obj = dataRows[i];
					obj['left'] = 10;
					obj['discountLabel'] = createDiscountLabel(obj.discount, params.langCode, params.languages);
					obj['soldoutText'] = that.attr('data-soldouttext');
					obj['goodsBtnText'] = that.attr('data-goodsbtntext');
					obj['goods_img'] = obj['goods_img'] || that.attr('data-defaultImg');
					obj['leftPercent'] = (dataRows[i].activity_volume_number / dataRows[i].activity_number).toFixed(2) * 100;
				that.find('.js_geshopFlashSaleBody').prepend(createGoodNode(obj, params.langCode, JSON.parse(params.languages)||{}));
			}
			that.closest('.geshop-component-box').css('min-height','auto');
		}
	}
});
$(function(){
	$('.wrap-U000141-default').each(function(){
		var pageInstanceId = $(this).data('id');
		$(this).flashSaleInit(window['FLASHDATA_'+pageInstanceId],pageInstanceId);
	})
});
