$(function() {
if (!GS_goodsTab) {

	var buyBtnArr = [GESHOP_LANGUAGES.btn_coming_soon, GESHOP_LANGUAGES.btn_buy_now, GESHOP_LANGUAGES.btn_ended, GESHOP_LANGUAGES.btn_sold_out];
	var countDownTextArr = [GESHOP_LANGUAGES.down_starts, GESHOP_LANGUAGES.down_ends, ''];/* 倒计时状态 */

	var GS_goodsTab = (function (my) {

		my.FormatHour = function (timeStamp) {
			var now = timeStamp ? new Date(timeStamp) : new Date;
			var y = now.getFullYear();
			var M = now.getMonth() + 1;
			var d = now.getDate();
			var h = now.getHours();
			var m = now.getMinutes();
			var s = now.getSeconds();
			var e = now.getDay();
			var w;
			if (h < 12) { w = 'am' } else { w = 'pm' }
			h = h > 12 ? h - 12 : h;
			h = h < 10 ? "0" + h : h;
			m = m < 10 ? "0" + m : m;
			s = s < 10 ? "0" + s : s;
			var value = y + "年" + M + "月" + d + "日 星期" + "日一二三四五六".split("")[e]
				+ "<br />" + w + " " + h + ":" + m + ":" + s;
			var timeSecond = h + ":" + m + " " + w
			return timeSecond
		}
		my.tabSlide = function (target, defaultIndex) {
			/* 轮播tab */
			var $component = $(target).parent().parent();
			var $tabBd = $(target).find('gs-goods-bd');
			var tabBdDefault = $tabBd.attr('data-default');
			if (tabBdDefault == defaultIndex) {
				return false;
			} else {
				$tabBd.attr('data-default', defaultIndex);
			}
			var liLength = $component.data('vis');
			var vis = liLength > 4 ? 4 : liLength;
			var $tabItem = $(target).find('.bd-main li[data-index=' + defaultIndex + ']');
			var $tabContent = $(target).find('.gs-tab-content');
			var $tabHdli = $(target).find('.gs-goods-hd li:eq(' + defaultIndex + ')');
			$(target).find('.bd-main li').removeClass('gs-tab_going')
			$tabItem.addClass('gs-tab_going');

			var isEditEnv = $('[data-editenv]:eq(0)').data('editenv');
			if (liLength > 4) {
				if (!$(target).data('slide')) {
					$(target).slide({
						titCell: ".gs-goods-hd ul",
						mainCell: ".gs-goods-bd ul",
						prevCell: '.prev',
						nextCell: '.next',
						trigger: 'click',
						autoPage: true,
						effect: "left",
						autoPlay: false,
						vis: vis,
						defaultIndex: defaultIndex,
						startFun: function (i, c) {
							var $prev = $(target).find('.prev');
							var $next = $(target).find('.next');
							if (i == 0) {
								$prev.hide();
							}
							if (c > i && i != 0) {
								$prev.show();
							}

							if (i + 1 >= c) {
								$next.hide()
							} else {
								$next.show()
							}
						},
						endFun: function (i, c) {

						}
					}).data('slide', true);
				} else {
					/* 非编辑状态 */
					if (isEditEnv != '1') {
						$tabHdli.trigger('click')
					}
				}

			} else {
				/* 非编辑状态 */
				if (isEditEnv != '1') {
					$tabHdli.trigger('click')
				}
				/* 				$tabContent.find('.gs-tab-item[data-index=' + defaultIndex + ']').addClass('gs-tab-show').siblings().removeClass('gs-tab-show'); */

			}

			$tabContent.find('.gs-tab-item[data-index=' + defaultIndex + ']').addClass('gs-tab-show').siblings().removeClass('gs-tab-show');
		}

		return my
	}({}))


	/* tab切换 */
	$('.gs-goods-tab').on('click', '.gs-goods-bd li', function () {
		var $labelParent = $(this).parents('.gs-goods-bd:eq(0)'),
			$tab_content = $labelParent.next('.gs-tab-content'),
			$tab_index = $(this).data('index')
		$(this).addClass('gs-tab_select').siblings().removeClass('gs-tab_select')
		$tab_content.find('.gs-tab-item:eq(' + $tab_index + ')').addClass('gs-tab-show').siblings().removeClass('gs-tab-show')
		if ($.fn.lazyload) {
			$tab_content.find('.gs-tab-item:eq(' + $tab_index + ') img.lazyload').lazyload({
				threshold: 100,
				failure_limit: 20,
				skip_invisible: false
			})
		} else {
			window.GS_GOODS_LAZY_FN('.lazyload');
		}

	});

	/* 倒计时初始化,tpl初始化 */
	if (!gsKillGlobal) {
		var gsKillGlobal = function (my) {
			var $gsCountTarget = $('.gs-goods-tab .gs_component_countDown');
			my.initCountDown = function () {
				$gsCountTarget = $('.gs-goods-tab .gs_component_countDown');
				$gsCountTarget.each(function () {
					var $self = $(this),
						$tabItem = $(this).closest('.gs-tab-item'),
						$serverInput = $self.prev('input[name=serverTime]'),
						leftTime = 0,
						status = 0,	// ['0未开始', '1已开始', '2已结束']
						dayStatus = null,//['1yestorday', '2today', '3tomorrow'],
						dataClassArr = ['gs-tab-soon', 'gs-tab-already', 'gs-tab-end'],
						serverTime = new Date().getTime() || parseInt($serverInput.val()),
						dataStartTime = parseInt($serverInput.attr('data-start-time')),
						dataEndTime = parseInt($serverInput.attr('data-end-time')),
						dataTextArr = ($serverInput.attr('data-textobj')).split(','),
						dayStatusArr = ($serverInput.attr('data-dayobj')).split(',');
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

					if (serverYear === dataStartYear && dataStartMonth === serverMonth) {
						if (serverDay === dataStartDay) {
							dayStatus = 1
						} else if (serverDay - dataStartDay == 1) {
							dayStatus = 0
						} else if (dataStartDay - serverDay == 1) {
							dayStatus = 2
						}
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
					/* 倒计时状态text */
					$self.prev().prev().text(countDownTextArr[status])
					/* $self.prev().prev().text(dataTextArr[status]) */


					/* 倒计时tab状态变更 */
					if ($self.closest('.gs-tab-item')) {
						var liIndex = $self.closest('.gs-tab-item').data('index');
						var $tabWrapper = $self.parents('.gs-goods-tab:eq(0)');
						var $tabTarget = $('.gs-goods-bd li[data-index=' + liIndex + ']', $tabWrapper);
						if (dataStartTime) {
							$serverInput.attr('data-status', status)
							$tabItem.attr('data-status', status)
							/* tabItem 状态 */
							/* 							if (!$tabTarget.hasClass('gs-tab-status')) {
															$tabTarget.addClass('gs-tab-status').attr("data-class", dataClassArr[status])
														} */
							$tabTarget.addClass('gs-tab-status').attr("data-class", dataClassArr[status])

							if (status == '2') {
								$tabItem.find('.gs-time-inner').addClass('gs-visible-not')
							}

							$tabTarget.find('.gs-date-status').text(dataTextArr[status])
							$tabTarget.find('.gs-goods-date_hours').text(GS_goodsTab.FormatHour(dataStartTime))
							if (dayStatus || dayStatus === 0) {
								$tabTarget.find('.gs-date_day').text(dayStatusArr[dayStatus])
							} else {
								$tabTarget.find('.gs-date_day').text(startString)
							};

						}

					}

				})
				/* 当前活动或即将开始 */
				$(".gs-goods-tab").each(function () {
					var tabSelect = false;
					var tab_soonIndexArr = [];
					var $tab = $(this);
					$tab.find('input[name=serverTime]').each(function () {
						if (tabSelect !== false) return false;
						var liIndex = $(this).closest('.gs-tab-item').data('index');
						var $tabTarget = $('.gs-goods-bd li[data-index=' + liIndex + ']', $tab);
						var currentStatus = $(this).attr('data-status');

						if (currentStatus == '0') {
							tab_soonIndexArr.push(liIndex)
						} else if (currentStatus == '1') {
							// $tabTarget.addClass('gs-tab_going').siblings().removeClass('gs-tab_going');
							tabSelect = liIndex	//已选中
						}
					})
					var tab_notStart = tab_soonIndexArr.length > 0 ? tab_soonIndexArr[0] : 0
					var tabDefault = tabSelect || tabSelect === 0 ? tabSelect : tab_notStart;
					GS_goodsTab.tabSlide($tab, tabDefault)

				})


			}
			/* 倒计时组件 */
			my.compoentCountDown = function () {
				var _this = this;
				$gsCountTarget.each(function () {
					var $self = $(this);
					var $serverInput = $self.prev('input[name=serverTime]');
					var dataStartTime = parseInt($serverInput.attr('data-start-time'));
					if (!dataStartTime) { return };

					var leftTime = parseInt($self.data('leftTime'));
					var dataStatus = parseInt($self.data('status'));
					var seconds, minutes, hours, days, CDay, CHour, CMinute, CSecond
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

						$self.data('left-time', leftTime - 1)
						$self.html('<span><em>' + CDay + '</em>:<em>' + CHour + '</em>:<em>' + CMinute + '</em>:<em>' + CSecond + '</em></span>')
						// $self.html('<span><strong>' + CDay + 'DAY(S)</strong>' + CHour + ':' + CMinute + ':' + CSecond + '</span>')
					} else {
						$gsCountTarget = $gsCountTarget.not($self)
						// $self.html('<span><strong>00 DAY(S) </strong>00:00:00</span>')
						if (dataStatus !== 2) {
							gsKillGlobal.initCountDown();
						} else {
							$self.html('<span><em>00</em>:<em>00</em>:<em>00</em>:<em>00</em></span>')
						}
					}
				})
				if (0 !== $gsCountTarget.length) {
					setTimeout(gsKillGlobal.compoentCountDown, 1000)
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
				gs_laytpl.config({ open: "<%", close: "%>" });
			}


			my.getTplProduct = function (skus, dataid) {
/*				var lang = typeof GESHOP_LANG != 'undefined' ? GESHOP_LANG : 'en'
				var params = {
					skId: 9,
					lang: lang,
					goodsSn: skus
				}
				var zafulParams = {
					lang: lang,
					skuArr: skus,
					v: '2'
				}

				if (typeof GESHOP_PIPELINE != 'undefined') {
					zafulParams.pipeline = GESHOP_PIPELINE
				}

				var urlGroup = {
					'rw-pc': 'https://www.rosewholesale.com/m-interface-a-getSpikeGoods.html',
					'zf-pc': 'https://www.zaful.com/api/get_seckill_api.php?method=getSeckillData'   //'https://www.zaful.com/api/get_seckill_api.php?method=getSeckillData',

				}

				var paramGroup = {
					'rw-pc': { content: JSON.stringify(params) },
					'zf-pc': zafulParams,
				}

				var url = urlGroup[GESHOP_SITECODE]
				var content = paramGroup[GESHOP_SITECODE]*/

				var lang = typeof GESHOP_LANG != 'undefined' ? GESHOP_LANG : 'en'
				var params = {
					lang: lang,
					goodsSn: skus,
					pipeline: (typeof GESHOP_PIPELINE != 'undefined' ? GESHOP_PIPELINE : ''),
					v:'2'
				}

				var url = GESHOP_INTERFACE.timeseckilldetail.url;
				var content = { content: JSON.stringify(params) };

                return window.GEShopCommonFn_Vue.$jsonp(url, params, { pid:dataid });
			}
			/* 模板数据填充 */
			my.getTplInitData = function () {

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

			return my
		}(gsKillGlobal || {})

		/* tab时间格式 */

	}

	/* target tab target */
	function tplIntCallback (target) {
		/* 	var $target = $(".gs-tab-content"); */
		$(target).find('.gs-tab-item').each(function (i, element) {
			var currentSkus = $(element).data('skus');
			var $tpl = $(element).find('.gs_syncDefault');
			var tplHtml = $tpl.html();
			if (!currentSkus) { return false };
			var isEditEnv = $('[data-editenv]:eq(0)').data('editenv');
			var pid = $(target).parents('.geshop-component-box').eq(0).attr('data-id');
			gsKillGlobal.getTplProduct(currentSkus,pid).done(function (res) {
				var dataList = res.data.goodsInfo;
				var listArr = [];

				if (res.code == '0' && dataList)
				    for (var i =0; i<dataList.length; i++) {
				        var num = dataList[i].goods_number
                        if (num == 0) {
                            listArr.push(dataList[i]);
                            dataList.splice(i, 1);
                        }
                    }
                    dataList = dataList.concat(listArr);

					gs_laytpl.config({ open: "<%", close: "%>" });
				gs_laytpl(tplHtml).render(dataList, function (html) {
					if (html) {
						$(element).find('.gs-goodsWrap ul').html(html)

						/* 价格换算 rw*/
						if (typeof GLOBAL != 'undefined' && GLOBAL.currency.change_html) {
							var bizhong = gsKillGlobal.getCookie('bizhong') || 'USD';
							var $wrapElem = $('.gs-goods-tab')
							GLOBAL.currency.change_html(bizhong, $wrapElem)
						}

						/* btn status */
						var tabStatus = $(element).attr('data-status')
						$(element).find('.gs-btn-area .gs-btn').text(buyBtnArr[tabStatus])
						$(element).find('.kill_soldout .gs-btn-area .gs-btn').text(buyBtnArr[3])
					}
				})
			}).fail(function () {
				if (isEditEnv == '1') {
					$(element).find('.gs-goodsWrap ul').after('<span>错误:接口商品数据为空</span>')
				}
			})
		})
	}

	function tabScrollEvent () {
		var $goodTarget = $('[data-gid="U000078_default"] .gs-goods-tab');
		$goodTarget.length && $goodTarget.each(function (i, element) {
			tplIntCallback($(element))
		})
		// var $goodsTab = $(".gs-goods-tab");
		// var isEditEnv = $('[data-editenv]:eq(0)').data('editenv');
		// var scrollTarget = isEditEnv == '1' ? '.design-right' : window;
		// $goodsTab.length &&
		// 	$(scrollTarget).on('scroll.tabGoods', gsKillGlobal.throttle(function () {
		// 		$goodsTab.each(function (i, element) {
		// 			var $element = $(element),
		// 				tplStatus = parseInt($element.attr('data-tplStatus'));
		// 			if (!!tplStatus) { return };

		// 			var screenH = (document.documentElement || document.body).clientHeight,
		// 				scrollY = $(scrollTarget).scrollTop() || window.scrollY,
		// 				eleOffsetH = $element.offset().top,
		// 				wrapperH = $element.height(),
		// 				spaceH = 100;
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
		// 	if (!!tplStatus) { return };

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

	/* 加载初始化 */
	var staticDomain = $('[data-static-domain]:eq(0)').attr('data-static-domain') ? $('[data-static-domain]:eq(0)').attr('data-static-domain') : "";
	$LAB.script(staticDomain + '/resources/javascripts/library/jquery.SuperSlide.2.1.x.js')
		.script(staticDomain + "/resources/javascripts/library/gs_laytpl.js?2018100101").wait(function () {
			gsKillGlobal.initCountDown();
			gsKillGlobal.compoentCountDown();
			gsKillGlobal.gsTplInt();
			tabScrollEvent();
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
	tabScrollEvent();
}
});
