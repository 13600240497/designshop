$(function(){
	function turnGoods () {
		$('[data-gid=U000001_zaful]').each(function (index, element) {

		/* 每隔四个小时切换一次商品 */
		if (window.localStorage) {
			var nowTime = new Date().getTime();
			var $fixed_goods_count = parseInt($(element).find('.gs-goods-singleTab-zaful').attr('data-fixed-g-count'));
			var $default_goods_count = parseInt($(element).find('.gs-goods-singleTab-zaful').attr('data-default-g-count'));
			var $key = $(element).attr('data-key');
			var $id = $(element).attr('data-id');
			var default_g = JSON.parse(localStorage.getItem("geshop_goods_" + $key + "_" + $id));
			if (default_g) {
				var default_time = default_g.time;
				var default_skus = default_g.skus;
				var default_count = default_g.default_count;
				var fixed_count = default_g.fixed_count;
				var time_range = Math.floor((nowTime - default_time)) / (1000 * 60 * 60);
				if ($default_goods_count == default_count && $fixed_goods_count == fixed_count) {

					if (time_range >= 4) {
						showGoodsRandom(element)
					} else {
						$(element).find('.goods-item').each(function (index, ele) {
							if (index > $fixed_goods_count - 1) {
								$(ele).css("display", "none");
							}
						})
						for (var i = 0; i < default_skus.length; i++) {
							$(element).find('.goods-item').eq(default_skus[i]).css("display", "block");
						}
					}

				} else {
					default_time_sku(element)
				}
			} else {
				default_time_sku(element)
			}
		} else {
			setInterval(function () {
				showGoodsRandom(element)
			}, 14400000) //14400000
		}
	})

}

/* 获取不重复的任意值 */
function createRandom (num, from, to) {
	var arr = [];
	if (from <= to && num >= 0) {
		for (var i = from; i <= to; i++)
			arr.push(i);
		arr.sort(function () {
			return 0.5 - Math.random();
		});
		if (num > to + 1 - from) {
			arr.length = to + 1 - from;
		} else {
			arr.length = num;
		}
	}
	return arr;
}

/* 随机显示商品 */
function showGoodsRandom (e) {
	var $default_goods_count = parseInt($(e).find('.gs-goods-singleTab-zaful').attr('data-default-g-count'));
	var $fixed_goods_count = parseInt($(e).find('.gs-goods-singleTab-zaful').attr('data-fixed-g-count'));
	var $key = $(e).attr('data-key');
	var $id = $(e).attr('data-id');
	var $total = $(e).find('.gs-goods-singleTab-zaful').attr('data-skus') != "" ? parseInt($(e).find('.gs-goods-singleTab-zaful').attr('data-skus').split(',').length) : "";
	var goodsArray = createRandom($default_goods_count - $fixed_goods_count, $fixed_goods_count, $total - 1);
	var default_goods = {
		time: new Date().getTime(),
		skus: goodsArray,
		default_count: $default_goods_count,
		fixed_count: $fixed_goods_count,
	};
	localStorage.setItem("geshop_goods_" + $key + "_" + $id, JSON.stringify(default_goods));

	if ($default_goods_count < $fixed_goods_count) {
		$(e).find('.goods-item').css("display", "none");
		for (var i = 0; i < $default_goods_count; i++) {
			$(e).find('.goods-item').eq(i).css("display", "block");
		}
		return false;
	}
	$(e).find('.goods-item').each(function (index, ele) {
		if (index > $fixed_goods_count - 1) {
			$(ele).css("display", "none");
		}
	})
	for (var i = 0; i < goodsArray.length; i++) {
		$(e).find('.goods-item').eq(goodsArray[i]).css("display", "block");
	}
}

/* 首次加载存储时间和默认sku */
function default_time_sku (e) {
	var $default_goods_count = parseInt($(e).find('.gs-goods-singleTab-zaful').attr('data-default-g-count'));
	var $fixed_goods_count = parseInt($(e).find('.gs-goods-singleTab-zaful').attr('data-fixed-g-count'));
	var $key = $(e).attr('data-key');
	var $id = $(e).attr('data-id');
	var $total = parseInt($(e).find('.gs-goods-singleTab-zaful').attr('data-skus').split(',').length);
	var init_time = parseInt($(e).find('.gs-goods-singleTab-zaful').attr('data-init-time'));
	var default_goods, goodsArray = [];

	if ($default_goods_count) {
		for (var i = $fixed_goods_count; i <= $total - 1; i++) {
			goodsArray.push(i)
		}

		goodsArray.length = $default_goods_count - $fixed_goods_count > 0 ? $default_goods_count - $fixed_goods_count : 0;
		default_goods = {
			time: init_time,
			skus: goodsArray,
			default_count: $default_goods_count,
			fixed_count: $fixed_goods_count,
		};
		localStorage.setItem("geshop_goods_" + $key + "_" + $id, JSON.stringify(default_goods));
		$(e).find('.goods-item').each(function (index, ele) {
			if (index > $fixed_goods_count - 1) {
				$(ele).css("display", "none");
			}
		})
		for (var i = 0; i < goodsArray.length; i++) {
			$(e).find('.goods-item').eq(goodsArray[i]).css("display", "block");
		}
	}

}


if (!GS_goods_kill_normal) {
	var buyBtnArr = [GESHOP_LANGUAGES.btn_coming_soon, GESHOP_LANGUAGES.btn_buy_now, GESHOP_LANGUAGES.btn_ended, GESHOP_LANGUAGES.btn_sold_out];
	var countDownTextArr = [GESHOP_LANGUAGES.down_starts, GESHOP_LANGUAGES.down_ends, '']; /* 倒计时状态 */

	/* 倒计时初始化,tpl初始化 */
	if (!GS_goods_kill_normal) {
		var GS_goods_kill_normal = function (my) {
			var $gsCountTarget = $('.warp-U000001_zaful .gs-goods-singleTab-zaful .gs_component_countDown');
			my.initCountDown = function () {
				$gsCountTarget = $('.warp-U000001_zaful .gs-goods-singleTab-zaful .gs_component_countDown');
				$gsCountTarget.each(function () {
					var $self = $(this),
						$serverInput = $self.prev('input[name=serverTime]'),
						leftTime = 0,
						status = 0, // ['0未开始', '1已开始', '2已结束']
						serverTime = new Date().getTime() || parseInt($serverInput.val()),
						dataStartTime = parseInt($serverInput.attr('data-start-time')),
						dataEndTime = parseInt($serverInput.attr('data-end-time')),
						dataTextArr = ($serverInput.attr('data-textobj')).split(',');
					/* tab日期 */
					if (dataStartTime) {
						var startDateValue = new Date(dataStartTime),
							serverDateValue = new Date(serverTime),
							dataStartDay = startDateValue.getDate(),
							dataStartMonth = startDateValue.getMonth(),
							dataStartYear = startDateValue.getFullYear(),
							serverDay = serverDateValue.getDate(),
							serverMonth = serverDateValue.getMonth(),
							serverYear = serverDateValue.getFullYear();

						var startDay = dataStartDay < 10 ? "0" + (dataStartDay) : (dataStartDay);
						var startMonth = dataStartMonth + 1 < 10 ? "0" + (dataStartMonth + 1) : (dataStartMonth + 1);
						var startString = startMonth + "-" + startDay;
					};


					/* 剩余秒数 */
					var serverTime1 = Math.round(serverTime / 1000);
					var dataStartTime1 = Math.round(dataStartTime / 1000);
					var dataEndTime1 = Math.round(dataEndTime / 1000);

					if (dataStartTime1 > serverTime1) {
						leftTime = dataStartTime1 - serverTime1;
					} else if (dataStartTime1 <= serverTime1 && dataEndTime1 > serverTime1) {
						status = 1;
						leftTime = dataEndTime1 - serverTime1;
					} else if (dataEndTime1 < serverTime1) {
						status = 2;
						leftTime = 0;
					}
					// leftTime = Math.round(leftTime / 1000)
					$self.data({
						'leftTime': leftTime,
						'status': status
					})
					/* 倒计时状态text  defualt: countDownTextArr[status]*/
					$self.prev().prev().text(dataTextArr[status])


				})


			}
			/* 倒计时组件 */
			my.compoentCountDown = function () {
				var _this = this;
				$gsCountTarget.each(function () {
					var $self = $(this);
					var $selfInner = $self.closest('.gs-time-inner');
					var $serverInput = $self.prev('input[name=serverTime]');
					var dataStartTime = parseInt($serverInput.attr('data-start-time'));
					var leftTime = parseInt($self.data('leftTime'));
					var dataStatus = parseInt($self.data('status'));
					var killLeftVisible = $self.closest('.gs-goods-singleTab-zaful').attr('data-killLeftVisible');
					var $wrapper = $self.parents('[data-gid="U000001_zaful"]:eq(0)');
					if (!leftTime && dataStatus == '2') {
						$gsCountTarget = $gsCountTarget.not($self);
						if (killLeftVisible === '0') {
							$selfInner.find('.gs-down-inner').hide();
						}

						return;
					};

					var seconds, minutes, hours, days, CDay, CHour, CMinute, CSecond, CHours;
					if (!isNaN(leftTime) && leftTime >= 0) {
						seconds = leftTime
						minutes = Math.floor(seconds / 60)
						hours = Math.floor(minutes / 60)
						days = Math.floor(hours / 24)
						CDay = days
						CHour = hours % 24
						CMinute = minutes % 60
						CSecond = Math.floor(seconds % 60)
						CDay = CDay < 10 ? '0' + CDay : CDay
						CHour = CHour < 10 ? '0' + CHour : CHour
						CMinute = CMinute < 10 ? '0' + CMinute : CMinute
						CSecond = CSecond < 10 ? '0' + CSecond : CSecond
						CHours = hours < 10 ? '0' + hours : hours

						$self.data('left-time', leftTime - 1)
						var siteCode = typeof GESHOP_SITECODE != 'undefined' ? GESHOP_SITECODE : 'rw-pc';
						if (siteCode.indexOf('zf') > -1) {
							$self.html('<span><em>' + CHours + '</em>:<em>' + CMinute + '</em>:<em>' + CSecond + '</em></span>')
						} else {
							$self.html('<span><em>' + CDay + '</em>:<em>' + CHour + '</em>:<em>' + CMinute + '</em>:<em>' + CSecond + '</em></span>')
						}

						// $self.html('<span><strong>' + CDay + 'DAY(S)</strong>' + CHour + ':' + CMinute + ':' + CSecond + '</span>')
					} else {
						$gsCountTarget = $gsCountTarget.not($self)
						// $self.html('<span><strong>00 DAY(S) </strong>00:00:00</span>')
						if (dataStatus !== 2) {
							GS_goods_kill_normal.initCountDown();
						} else {
							$self.html('<span><em>00</em>:<em>00</em>:<em>00</em>:<em>00</em></span>');
							if (killLeftVisible === '0') {
								$selfInner.find('.gs-down-inner').hide();
							}
						}
					}
				})
				if (0 !== $gsCountTarget.length) {
					setTimeout(GS_goods_kill_normal.compoentCountDown, 1000)
				}
			}

			my.extendDeep = function (parent, child) {
				var i,
					toStr = Object.prototype.toString,
					astr = "[object Array]";

				child = child || {};

				for (i in parent) {
					if (parent.hasOwnProperty(i)) {
						if (typeof parent[i] === "object") {
							child[i] = toStr.call(parent[i]) === astr ? [] : {};
							my.extendDeep(parent[i], child[i]);
						} else {
							child[i] = parent[i];
						}
					}
				}
				return child;
			};

			my.gsTplInt = function () {
				gs_laytpl.config({
					open: "<%",
					close: "%>"
				});
			}


			my.getTplProduct = function (skus, target) {
				var lang = typeof GESHOP_LANG != 'undefined' ? GESHOP_LANG : 'en';
				var url = GESHOP_INTERFACE['goods_async_detail'].url;
				var params = {
					"pipeline": (typeof GESHOP_PIPELINE != 'undefined' ? GESHOP_PIPELINE : ''),
					"lang": lang,
					"goodsSn": skus,
					"filterStock": "0",
					"promotePriceType": "2"
				}
				var content = {
					content: JSON.stringify(params)
				};
				var ext = $(target).parents('.geshop-component-box').eq(0).attr('data-id');
				return $.ajax({
					url: url,
					type: 'get',
					dataType: 'jsonp',
					jsonpCallback: `geshop_callback_${ext}`,
					cache: true,
					data: content
				})
			}
			my.throttle = function (fn, delay, atleast) {
				var timer = null;
				var previous = null;

				return function () {
					var now = +new Date();
					if (!previous) {
						previous = now;
					}
					if (atleast && now - previous > atleast) {
						fn();
						previous = now;
						clearTimeout(timer);
					} else {
						clearTimeout(timer);
						timer = setTimeout(function () {
							fn();
							previous = null;
						}, delay);
					}
				};
			};

			//设置 cookie 方法
			my.setCookie = function (name, value) {
				var Days = 30
				var exp = new Date()
				exp.setTime(exp.getTime() + Days * 24 * 60 * 60 * 1000)
				document.cookie = name + '=' + escape(value) + ';expires=' + exp.toGMTString() + ';path=/' + ';domain=' + COOKIESDIAMON
			}

			//获取 cookie 方法
			my.getCookie = function (name) {
				var arr,
					reg = new RegExp('(^| )' + name + '=([^;]*)(;|$)')

				if (arr = document.cookie.match(reg)) {
					return arr[2]
				} else {
					return null
				}

			}

			my.tabScrollEvent = function () {
				var $goodTarget = $('.warp-U000001_zaful .gs-goods-singleTab-zaful');
				$goodTarget.length && $goodTarget.each(function (i, element) {
					tplIntCallback($(element))
				})

				// var $goodsTab = $(".gs-goods-singleTab-zaful");
				// var isEditEnv = $('[data-editenv]:eq(0)').data('editenv');
				// var scrollTarget = isEditEnv == '1' ? '.design-right' : window;
				// $goodsTab.length &&
				// 	$(scrollTarget).on('scroll.tabGoods', GS_goods_kill_normal.throttle(function () {
				// 		$goodsTab.each(function (i, element) {
				// 			var $element = $(element),
				// 				tplStatus = parseInt($element.attr('data-tplStatus'));
				// 			if (!!tplStatus) { return };

				// 			var screenH = (document.documentElement || document.body).clientHeight,
				// 				scrollY = $(scrollTarget).scrollTop() || window.scrollY,
				// 				eleOffsetH = $element.offset().top,
				// 				wrapperH = $element.height(),
				// 				spaceH = 350;
				// 			if (eleOffsetH < screenH || screenH + scrollY + spaceH - (eleOffsetH + wrapperH) > 0 && screenH + scrollY + spaceH - (eleOffsetH + wrapperH) <= screenH) {
				// 				$element.attr('data-tplStatus', 1)
				// 				tplIntCallback($element)
				// 			}
				// 		})
				// 	}, 100));
				// /* 首屏加载 */
				// $goodsTab.length && $goodsTab.each(function (i, element) {
				// 	var $element = $(element),
				// 		tplStatus = parseInt($element.attr('data-tplStatus'));
				// 	if (!!tplStatus && isEditEnv !== '1') { return };

				// 	var screenH = (document.documentElement || document.body).clientHeight,
				// 		eleOffsetH = $element.offset().top,
				// 		scrollY = $(scrollTarget).scrollTop() || window.scrollY,
				// 		wrapperH = $element.height(),
				// 		spaceH = 100;
				// 	if (eleOffsetH < screenH || screenH + scrollY + spaceH - (eleOffsetH + wrapperH) > 0) {
				// 		$element.attr('data-tplStatus', 1)
				// 		tplIntCallback($element)
				// 	}
				// });
			}
			return my
		}(GS_goods_kill_normal || {})

	}

	/* target tab target */
	function tplIntCallback (target) {
		/* 	var $target = $(".gs-tab-content"); */
		var element = target;
		var currentSkus = $(element).data('skus');
		var $tpl = $(element).find('.gs_syncDefault');
		var tplHtml = $tpl.html();
		if (!currentSkus) {
			return false
		};
		var isEditEnv = $('[data-editenv]:eq(0)').data('editenv');

		if (isEditEnv == '1') {
			// Design.enableLoading()
		}
		GS_goods_kill_normal.getTplProduct(currentSkus, target).done(function (res) {
			if (isEditEnv == '1' && typeof Design != 'undefined') {
				Design.disableLoading()
				Design.disableLayuiLoading()
			}
			var goodsInfo = res.data.goodsInfo;
			var dataList = [];
            goodsInfo.forEach(function (item, index) {
                if (Number(item.is_on_sale) === 1) {
                    dataList.push(item)
                }
            })

			if (res.code == '0' && dataList.length > 0) {
			    // 库存为0排序放到最后
                var arrList = [];
                dataList.forEach(function (item, index) {
                    if (item.goods_number && item.goods_number <= 0) {
                        arrList.push(item)
                        dataList.splice(index, 1)
                    }
                });
                dataList = dataList.concat(arrList);

				gs_laytpl.config({ open: "<%", close: "%>" });
				gs_laytpl(tplHtml).render(dataList, function (html) {
					if (html) {
						$(element).find('.gs-goodsWrap ul').html(html)
						turnGoods();
						$(element).find('.gs-goodsWrap').css('min-height', 'auto');
						if (typeof GESHOP_UTIL != 'undefined') {
							GESHOP_UTIL.goodsLazy($(element).find('.js-geshopImg-lazyload'));
						}

						/* 价格换算 rw*/
						if (typeof GLOBAL != 'undefined' && GLOBAL.currency.change_html) {
							var bizhong = GS_goods_kill_normal.getCookie('bizhong') || 'USD';
							var $wrapElem = $(target);
							GLOBAL.currency.change_html(bizhong, $wrapElem);
						}

						/* btn status */
						var tabStatus = $(element).find('.gs_component_countDown').data('status');
						/*							var buyNowText = $(element).attr('data-buy-now');
													var buyBtnText = buyNowText && tabStatus === 1 ? buyNowText: buyBtnArr[tabStatus];

													$(element).find('.gs-btn-area .gs-btn').text(buyBtnText);*/
						$(element).find('.kill_soldout .gs-btn-area .gs-btn').text(buyBtnArr[3]);
						$(element).find('.kill_soldout a').removeAttr('href');
					}
				})
			}

		}).fail(function () {
			if (isEditEnv == '1' && typeof Design != 'undefined') {
				$(element).find('.gs-goodsWrap ul').html('<span style="font-size: 14px;">错误:接口商品数据为空</span>')

				Design.disableLoading()
				Design.disableLayuiLoading()
			}
		}).always(function () {
			if (isEditEnv == '1' && typeof Design != 'undefined') {
				Design.disableLoading()
				Design.disableLayuiLoading()
			}
		})
	}

	/* 加载初始化 */
	var staticDomain = $('[data-static-domain]:eq(0)').attr('data-static-domain') ? $('[data-static-domain]:eq(0)').attr('data-static-domain') : "";
	$LAB.script(staticDomain + '/resources/javascripts/library/jquery.SuperSlide.2.1.x.js')
		.script(staticDomain + "/resources/javascripts/library/gs_laytpl.js?2018100101").wait(function () {
			GS_goods_kill_normal.initCountDown();
			GS_goods_kill_normal.compoentCountDown();
			GS_goods_kill_normal.gsTplInt();
			GS_goods_kill_normal.tabScrollEvent();
			// tplIntCallback();
		});


} else {
	/* edit */
	var $component = $('.warp-U000001_zaful');
	var isEditEnv = $('[data-editenv]:eq(0)').data('editenv');
	if (isEditEnv == '1') {
		$('.gs-tab-content', $component).find('.gs-tab-item:eq(0)').addClass('gs-tab-show').siblings().removeClass('gs-tab-show');
	}

	$('.gs-goods-title', $component).next().css({
		'height': '72px',
		'width': '850px'
	})
	$('.gs-goods-singleTab-zaful', $component).removeAttr('data-tplstatus');
	setTimeout(function () {
		GS_goods_kill_normal.tabScrollEvent();
		GS_goods_kill_normal.initCountDown();
	}, 200)

}
});
