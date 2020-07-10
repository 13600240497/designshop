if (!GS_goods_kill) {


	var buyBtnArr = [GESHOP_LANGUAGES.btn_coming_soon, GESHOP_LANGUAGES.btn_buy_now, GESHOP_LANGUAGES.btn_ended, GESHOP_LANGUAGES.btn_sold_out];
	var countDownTextArr = [GESHOP_LANGUAGES.down_starts, GESHOP_LANGUAGES.down_ends, ''];/* 倒计时状态 */

	/* 倒计时初始化,tpl初始化 */
	if (!GS_goods_kill) {
		var GS_goods_kill = function (my) {
			var $gsCountTarget = $('.gs-goods-singleTab .gs_component_countDown');
			my.initCountDown = function () {
				$gsCountTarget = $('.gs-goods-singleTab .gs_component_countDown');
				$gsCountTarget.each(function () {
					var $self = $(this),
						$serverInput = $self.prev('input[name=serverTime]'),
						leftTime = 0,
						status = 0,	// ['0未开始', '1已开始', '2已结束']
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
					$self.data({ 'leftTime': leftTime, 'status': status })
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
					var killLeftVisible = $self.closest('.gs-goods-singleTab').attr('data-killLeftVisible');
					var $wrapper = $self.parents('[data-gid="U000078_zaful"]:eq(0)');
					if (!leftTime && dataStatus == '2') {
						$gsCountTarget = $gsCountTarget.not($self);
						if (killLeftVisible == '0') {
							$wrapper.hide()
						} else {
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
							GS_goods_kill.initCountDown();
						} else {
							$self.html('<span><em>00</em>:<em>00</em>:<em>00</em>:<em>00</em></span>');
							if (killLeftVisible == '0') {
								$wrapper.hide()
							} else {
								$selfInner.find('.gs-down-inner').hide();
							}
						}
					}
				})
				if (0 !== $gsCountTarget.length) {
					setTimeout(GS_goods_kill.compoentCountDown, 1000)
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
				laytpl.config({ open: "<%", close: "%>" });
			}


			my.getTplProduct = function (skus, target) {
				var lang = typeof GESHOP_LANG != 'undefined' ? GESHOP_LANG : 'en';
				var params = {
					lang: lang,
					goodsSn: skus,
					pipeline: (typeof GESHOP_PIPELINE != 'undefined' ? GESHOP_PIPELINE : '')
				}
				var url = $(target).attr('data-interfaceDomain') + '/geshop/goods/timeseckilldetail';
				var pid = $(target).parents('.geshop-component-box').eq(0).attr('data-id');
				return window.GEShopCommonFn_Vue.$jsonp(url, params, { pid:pid })
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
				var $goodsTab = $(".gs-goods-singleTab");
				var isEditEnv = $('[data-editenv]:eq(0)').data('editenv');
				var scrollTarget = isEditEnv == '1' ? '.design-right' : window;
				$goodsTab.length &&
					$(scrollTarget).on('scroll.tabGoods', GS_goods_kill.throttle(function () {
						$goodsTab.each(function (i, element) {
							var $element = $(element),
								tplStatus = parseInt($element.attr('data-tplStatus'));
							if (!!tplStatus) { return };

							var screenH = (document.documentElement || document.body).clientHeight,
								scrollY = $(scrollTarget).scrollTop() || window.scrollY,
								eleOffsetH = $element.offset().top,
								wrapperH = $element.height(),
								spaceH = 350;
							if (eleOffsetH < screenH || screenH + scrollY + spaceH - (eleOffsetH + wrapperH) > 0 && screenH + scrollY + spaceH - (eleOffsetH + wrapperH) <= screenH) {
								$element.attr('data-tplStatus', 1)
								tplIntCallback($element)
							}
						})
					}, 100));
				/* 首屏加载 */
				$goodsTab.length && $goodsTab.each(function (i, element) {
					var $element = $(element),
						tplStatus = parseInt($element.attr('data-tplStatus'));
					if (!!tplStatus && isEditEnv !== '1') { return };

					var screenH = (document.documentElement || document.body).clientHeight,
						eleOffsetH = $element.offset().top,
						scrollY = $(scrollTarget).scrollTop() || window.scrollY,
						wrapperH = $element.height(),
						spaceH = 100;
					if (eleOffsetH < screenH || screenH + scrollY + spaceH - (eleOffsetH + wrapperH) > 0) {
						$element.attr('data-tplStatus', 1)
						tplIntCallback($element)
					}
				});
			}
			return my
		}(GS_goods_kill || {})

	}

	/* target tab target */
	function tplIntCallback (target) {
		/* 	var $target = $(".gs-tab-content"); */
		var element = target;
		var currentSkus = $(element).data('skus');
		var $tpl = $(element).find('.gs_syncDefault');
		var tplHtml = $tpl.html();
		if (!currentSkus) { return false };
		var isEditEnv = $('[data-editenv]:eq(0)').data('editenv');

		if (isEditEnv == '1') {
			// Design.enableLoading()
		}
		GS_goods_kill.getTplProduct(currentSkus, target).done(function (res) {
			if (isEditEnv == '1') {
				Design.disableLoading()
				Design.disableLayuiLoading()
			}
			var dataList = res.data.goodsInfo;
			if (res.code == '0' && dataList) {

				laytpl(tplHtml).render(dataList, function (html) {
					if (html) {
						$(element).find('.gs-goodsWrap ul').html(html)
						GESHOP_UTIL.goodsLazy($(element).find('.js-geshopImg-lazyload'));
						/* 价格换算 rw*/
						if (typeof GLOBAL != 'undefined' && GLOBAL.currency.change_html) {
							var bizhong = GS_goods_kill.getCookie('bizhong') || 'USD';
							var $wrapElem = $(target);
							GLOBAL.currency.change_html(bizhong, $wrapElem);
						}

						/* btn status */
						var tabStatus = $(element).find('.gs_component_countDown').data('status');
						$(element).find('.gs-btn-area .gs-btn').text(buyBtnArr[tabStatus]);
						$(element).find('.kill_soldout .gs-btn-area .gs-btn').text(buyBtnArr[3]);
						$(element).find('.kill_soldout a').removeAttr('href');
                        // 执行懒加载
                        window.GS_GOODS_LAZY_FN();
					}
				})
			}

		}).fail(function () {
			if (isEditEnv == '1') {
				$(element).find('.gs-goodsWrap ul').html('<span style="font-size: 14px;">错误:接口商品数据为空</span>')

				Design.disableLoading()
				Design.disableLayuiLoading()
			}
		}).always(function () {
			if (isEditEnv == '1') {
				Design.disableLoading()
				Design.disableLayuiLoading()
			}
		})
	}

	/* 加载初始化 */
	var staticDomain = $('[data-static-domain]:eq(0)').attr('data-static-domain') ? $('[data-static-domain]:eq(0)').attr('data-static-domain') : "";
	$LAB.script(staticDomain + '/resources/javascripts/library/jquery.SuperSlide.2.1.x.js')
		.script(staticDomain + "/resources/javascripts/library/laytpl.js").wait(function () {
			GS_goods_kill.initCountDown();
			GS_goods_kill.compoentCountDown();
			GS_goods_kill.gsTplInt();
			GS_goods_kill.tabScrollEvent();
			// tplIntCallback();
		});


} else {
	/* edit */
	var isEditEnv = $('[data-editenv]:eq(0)').data('editenv');
	if (isEditEnv == '1') {
		$('.gs-tab-content').find('.gs-tab-item:eq(0)').addClass('gs-tab-show').siblings().removeClass('gs-tab-show');
	}

	$('.gs-goods-title').next().css({
		'height': '72px', 'width': '850px'
	})
	$('.gs-goods-singleTab').removeAttr('data-tplstatus');
	setTimeout(function () {
		GS_goods_kill.tabScrollEvent();
	}, 200)

}
