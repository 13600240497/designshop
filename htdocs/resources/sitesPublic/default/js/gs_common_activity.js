/*
 * @Author: wuxingtao
 * @Date: 2018-10-10 17:03:52
 * @Last Modified by: wuxingtao
 * @Last Modified time: 2018-12-06 20:31:04
 */


if (typeof jQuery == 'undefined') {
	$LAB.script(GESHOP_STATIC + '/resources/javascripts/library/jquery.js').wait(function () {
		geshopCommon()
	})
} else {
	geshopCommon()
}


/**
 * geshop with jq
 */

function geshopCommon () {
	(function ($) {

		var GS = (function (my) {
			/*
            * element jq class selector
            */
			/*my.goodsLazy = function (element) {

                function commonLazy () {
                    if ($.fn.lazyload) {
                        $(element).lazyload({
                            threshold: 100,
                            effect: 'fadeIn',
                            failure_limit: 20,
                            skip_invisible: false
                        })
                    }

                }

                if (!$.fn.lazyload && typeof GESHOP_STATIC != 'undefined') {
                    $LAB.script(GESHOP_STATIC + '/resources/javascripts/library/lazyLoad.js').wait(function () {
                        commonLazy()
                    })
                } else {
                    commonLazy()
                }

            }*/
			my.goodsLazy = function(e) {
				function commonLazy() {
					new LazyLoad({
						elements_selector: typeof e != 'undefined' && typeof e == 'string' ? e : '.js_gdexp_lazy,.js-geshopImg-lazyload,.lazy',
						load_delay: 300,
						data_src: "original",
						thresholds: GESHOP_PLATFORM == 'pc' ? "500px" :"70%",
						callback_error: function(e) {
							var t = e.getAttribute("data-original");
							t = t.replace(".webp", ""),
								e.src = t
						},
                        callback_loaded: window.GESHOP_SITECODE.indexOf('zf') > -1 ? my.gbLogsss_sku : null
					})
				}
				if(window.LazyLoad) {
					commonLazy()
				} else {
					if (typeof window.GESHOP_STATIC != 'undefined') {
						$LAB.script(GESHOP_STATIC + '/resources/javascripts/library/intersection-observer.js').wait()
							.script(GESHOP_STATIC + '/resources/javascripts/library/lazyload.min.js').wait(function () {
							commonLazy()
						})
					}
				}
			}
            /**
             * 商品手动曝光
             * @param e nodelist
             */
            my.gbLogsss_sku = function(e){
                if( !($(e).hasClass('js-rendered')) ){
                    gbLogsss.getsku($(e));
                    gbLogsss.sendsku();
                }
            }
			/**
			 * 校验库是否存在
			 * name 库名
			 * src  库路径
			 */
			my.judgeLibExist = function (src, callback) {
				$LAB.script(src).wait(function () {
					if (callback) {
						callback()
					}
				})
			}
			/**
			 * activity already down alert
			 */
			my.activityEndAlertCheck = function () {
				if (typeof GESHOP_OFFLINE_TO_URL === 'undefined') {
					return false
				}
				var jumpLink = GESHOP_OFFLINE_TO_URL
				if (GESHOP_OFFLINE_TO_URL[GESHOP_OFFLINE_TO_URL.length - 1] === '/' && GESHOP_OFFLINE_TO_URL.length != 1) {
					jumpLink = GESHOP_OFFLINE_TO_URL.substr(0, GESHOP_OFFLINE_TO_URL.length - 1)
				}

				var activityJumpLink = jumpLink + window.location.search
				function callbackEvent () {
					var linkTextInit = '该活动已结束,<em id="get_opcode" class="gs-activity-secondDown">5</em>秒钟后将跳转至'
					var linkText = GESHOP_OFFLINE_TIPS_TEXT || linkTextInit
					// var linkTextOther = '' ||"<span class='link-color'>网站首页</span>,<span class='more-tip gs-block'>更多精彩等你发现~</span>"
					var linkNow = GESHOP_OFFLINE_BUTTON_TEXT || '马上跳转'
					var content = ''
					content += '<div class=\'gs-activity-alert\'>\n'
					content += '  <div class="gs-activity-content">'
					content += '<div><span>' + linkText + '</span></div>'
					content += '<\/div>\n'
					content += '  <div class="gs-btn-area gs-block">\n'
					content += '    <a href="' + activityJumpLink + '" class="btn btn-trans">' + linkNow + '<\/a>\n'
					content += '  <\/div>\n'
					content += '<\/div>\n'
					if (layer && layer.open) {
						layer.open({
							type: 1,
							skin: 'layui-gs-alert',
							title: false,
							closeBtn: false,
							// area: ['340px', '240px'],
							offset: '250px',
							content: content,
							success: function () {
								getOpCode.get(function () {
									window.location.href = activityJumpLink
								})
							}
						})
					}

				}

				var getOpCode = (function (my) {
					var seconds = 5
					var curCount    //当前剩余秒数
					var intervalTime
					my.get = function (callback) {
						curCount = seconds
						intervalTime = window.setInterval(function () {
							my.setResidual(callback)
						}, 1000)
					}
					my.setResidual = function (callback) {
						if (curCount == 0) {
							window.clearInterval(intervalTime)
							if (callback) callback()
						} else {
							curCount--
							$('#get_opcode').text(curCount)
						}
					}
					return my
				}({}))

				// var devConfig = {
				// 	layerjs: "https://cdn.bootcss.com/layer/3.0.3/layer.min.js",
				// 	layercss: "https://cdn.bootcss.com/layer/3.0/skin/default/layer.min.css",
				// 	gs_commoncss: "http://www.geshop.com.develop.s2.egomsl.com/resources/sitesPublic/css/gs_common.css"
				// }

				var prodConfig = {
					layerjs: GESHOP_STATIC + '/resources/javascripts/library/layer/layer.min.js',
					layercss: GESHOP_STATIC + '/resources/javascripts/library/layer/layer.min.css',
					gs_commoncss: GESHOP_STATIC + '/resources/sitesPublic/css/gs_common_activity.css?version=2018080801'
				}

				var linkConfig = prodConfig

				loadCss(linkConfig.gs_commoncss)
				if (!layer || (layer && layer.v.substr(0, 3) < 2.1)) {
					loadCss(linkConfig.layercss)
					my.judgeLibExist(linkConfig.layerjs, callbackEvent)
				} else {
					callbackEvent()
				}

			}
			/**
			 * 折扣倒计时
			 */
			my.goodsCountDown = function () {
				var $target = $('.gs_countDown')
				$target.each(function () {
					var $self = $(this)
					var leftTime = parseInt($self.data('leftTime'))
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
						$self.html('<span><strong>' + CDay + 'DAY(S)</strong>' + CHour + ':' + CMinute + ':' + CSecond + '</span>')
					} else {
						$target = $target.not($self)
						$self.html('<span><strong>00 DAY(S) </strong>00:00:00</span>')
					}
				})
				if (0 !== $target.length) {
					setTimeout(my.goodsCountDown, 1000)
				}
			}

			/**
			 * 判断站点头部悬浮高度是屏蔽站点差异
			 *  */

			my.getSiteFiedHeader = function () {
				var sizeCode = GESHOP_SITECODE
				var fiexdHeaderHeight = 0
				var $eleNavFiex = $('.elf-nav-fixed')//使用自定义导航定义的导航悬停时会有这个class

				if ($eleNavFiex.length) {
					fiexdHeaderHeight = $eleNavFiex.outerHeight(true)
					return fiexdHeaderHeight
				}
				if (sizeCode == 'zf-pc') {//zaful比较变态，单独处理,这里时一个坑
					var $zfPCNavFixd = $('.elf-nav-zaful-wrapper.geshop')
					if($zfPCNavFixd.length){
						fiexdHeaderHeight = $zfPCNavFixd.outerHeight(true)
						return fiexdHeaderHeight
					}
					return fiexdHeaderHeight
				}

				return fiexdHeaderHeight
			}

			my.getCookie = function(name){
				var arr
				var reg = new RegExp('(^| )' + name + '=([^;]*)(;|$)')
				if ((arr = document.cookie.match(reg))) return arr[2]
				else return null
			}
			my.userGroupHandle = userGroupHandle
			return my
		}({}))

		window.GESHOP_UTIL = GS

		/**
		 * 图片懒加载初始化
		 * @param {String} - selector class类名
		 */
		window.GS_GOODS_LAZY_FN = function (selector) {
			GS.goodsLazy(selector)
		}

		/**
		 * 用户人群处理
		 * dan_num 订单数量 2老用户 1 新用户
		 * userStatus 隐藏用户
		 */
		function userGroupHandle(vm){
			var dan_num = Number(GS.getCookie('WEBF-dan_num')),
				userStatus = dan_num>0 ? 1 : 2,
				$target = vm && vm.$el.classList && vm.$el.classList.contains('.geshop_user_'+userStatus) ? $(vm.$el) : $('.geshop_user_'+userStatus);
			if($target.length>0){
				$target.addClass('geshop-hidden-box')
			}

			var titleFlag = $('.geshop_user_'+userStatus+'[attr=nav_flag]')
			if(titleFlag.length>0){
				titleFlag.each(function(index,item){
					var dataId = $(item).attr('data-id')
					$('[data-key="U000029"] [data-id='+dataId+'],[data-key="U000027"] [data-id='+dataId+'],[data-key="U000030"] [data-id='+dataId+'],' +
						'[data-key="U000116"] [data-id='+dataId+'],[data-key="U000117"] [data-id='+dataId+']').remove()
				})
			}

		}
		/* document ready  */
		$(function () {
			userGroupHandle()

			GS.goodsLazy('img.lazyload, .js-geshopImg-lazyload')
			GS.goodsCountDown()
			GS.activityEndAlertCheck()
			!window.gtla && GS.goodsLazy('.js-geshopImg-lazyload')

			/* currency initial in Web and Wap */
			if (window.GLOBAL && window.GLOBAL.currency) {
				window.GLOBAL.currency.change_html()
			}
			if (window.FUN && window.FUN.currency) {
				window.FUN.currency.change_html()
			}

		})

		$(function () {
			/**
			 * 根据当前时间判断是在设置的哪个时间段对页面进行样式设置
			 * @param { int } style_type - 1 系统设置 2 自定义设置
			 */
			if (GESHOP_MULTI_TIME_STYLE && GESHOP_MULTI_TIME_STYLE.length) {
				var geshop_time_list = JSON.parse(GESHOP_MULTI_TIME_STYLE)
				var nowTime = Date.now()
				var currentTimeItem = {}
				if (geshop_time_list.length) {
					for (var i = 0; i < geshop_time_list.length; i++) {
						if (nowTime > geshop_time_list[i].start_time && nowTime < geshop_time_list[i].end_time) {
							currentTimeItem = geshop_time_list[i]
						}
					}
				}

				if (currentTimeItem.style_type == 1) {
					$('#geshop-page-content').css({
						backgroundColor: currentTimeItem.style.background_color ? currentTimeItem.style.background_color : 'transparent',
						backgroundImage: 'url(' + currentTimeItem.style.background_image + ')',
						backgroundPosition: currentTimeItem.style.background_position,
						backgroundRepeat: currentTimeItem.style.background_repeat
					})
				} else if (currentTimeItem.style_type == 2) {
					$('head').append('<style type="text/css">' + currentTimeItem.style.custom_css + '</style>')
				}
			}

		})
	})(jQuery)
}

function loadJsRun (options) {
	var ulr = GESHOP_STATIC + options.url
	if (options.valueof) {
		!valueof && $LAB.script(ulr).wait(function () {
			options.callback()
		})
	} else {
		$LAB.script(ulr).wait(function () {
			options.callback()
		})
	}
}

/* gb 专属样式 css */
if (GESHOP_SITECODE.split('-')[0] === 'gb') {
	loadCss(GESHOP_STATIC + '/resources/sites/gb/css/icon.css?version=20190615')
}

/* GB common */

function GBTimer () {

}

GBTimer.prototype.add = function (jqselect, option) {
	var $target = $(jqselect)
	var _this = this
	var options = Object.assign({
		startText: 'Starts In',
		ingText: 'Ends In', //进行中描述,
		endText: 'Deals Ended', //结束描述,
		buyNowText: 'Buy Now',
		onStart: $.noop, // 计时开始前回调函数
		onEnd: $.noop, // 计时结束后回调函数
	}, option)
	$target.each(function () {

		var $self = $(this)
		var startTime = parseInt($self.attr('data-begin'))
		var endTime = parseInt($self.attr('data-end'))
		var $li = $self.closest('li')
		if (!(startTime && endTime)) {
			$target = $target.not($self)
			if ($self.attr('data-begin') == '0' && $self.attr('data-end') == '0') {
				$li.find('.buyLink').text(options.endText)
				$li.addClass('good_dealEnded')
			}
			return
		}

		_this.initStatus($self, options)
		var leftTime = parseInt($self.attr('data-leftTime'))
		var dataStatus = parseInt($self.attr('data-status'))
		var statusText = dataStatus == '0' ? options.startText : options.ingText
		// var statusText = dataStatus == '0' ? options.startText : dataStatus == '1' ? options.ingText : options.endText;
		// erverTime = endTime - nowTime > 0 ? endTime - nowTime : 0;
		// leftTime = parseInt($self.attr('data-leftTime')) ? parseInt($self.attr('data-leftTime')) : serverTime;
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

			$self.attr('data-leftTime', leftTime - 1)
			$self.html('<span>' + statusText + ': ' + CDay + ':' + CHour + ':' + CMinute + ':' + CSecond + '</span>')
		} else {
			$target = $target.not($self)

			if (dataStatus !== 2) {
				_this.initStatus($self, options)
			} else {
				$self.html('<span>' + statusText + ': ' + '00:00:00:00</span>')
				if (options.onEnd) {
					options.onEnd()
				}
			}

		}
	})

	if (0 !== $target.length) {
		setTimeout(function () {
			_this.add(jqselect, option)
		}, 1000)
	}
}

GBTimer.prototype.initStatus = function (target, options) {
	var $target = $(target)
	$target.each(function () {
		var $self = $(this)
		var $li = $self.closest('li')
		var startTime = parseInt($self.attr('data-begin'))
		var endTime = parseInt($self.attr('data-end'))
		var nowTime = (new Date().getTime()) / 1000
		var serverTime
		var leftTime
		var status = 0	//['0未开始', '1已开始', '2已结束']
		var statusText = ''	//倒计时状态文案
		var currentBtnText = $li.find('.buyLink').text()
		var left_number = parseInt($self.attr('data-stock'))	//剩余库存

		/* is sold out check */
		var isSoldOut = $li.hasClass('good_soldOut')
		var buyTextArr = ['Coming Soon', options.buyNowText, 'Deals Ended']
		if (startTime > nowTime) {
			leftTime = startTime - nowTime
			statusText = options.startText
		} else if (startTime <= nowTime && endTime > nowTime) {
			status = 1
			statusText = options.endText
			leftTime = endTime - nowTime
		} else if (endTime < nowTime) {
			status = 2
			leftTime = -1
		}

		$self.attr({ 'data-leftTime': leftTime, 'data-status': status })

		/* 是否售完 */
		if (status == 1 && left_number == 0) {
			if (currentBtnText != 'Sold Out') {
				$li.find('.buyLink').text('Sold Out')
			}
		} else if (currentBtnText != buyTextArr[status]) {
			$li.find('.buyLink').text(buyTextArr[status])
		}

		if (status !== 1) {
			$li.addClass('good_dealEnded')
		} else {
			$li.removeClass('good_dealEnded')
		}
		if (status == 1 && left_number == 0) {
			$li.addClass('good_soldOut')
		} else {
			$li.removeClass('good_soldOut')
		}

	})
}


window.GBTimer = GBTimer

$(function () {
	/* 添加购物车 */
	$('.js-addCart').on('click', function () {
		var $this = $(this)
		var goodsSn = $this.attr('data-sku')
		var warehouseCode = $this.attr('data-warehousecode')
		var goodsType = $this.attr('data-type')
		var qty = $this.attr('data-qty')
		var imgSrc = $this.attr('data-img')
		GESHOP.addToCart({
			reqData: [{
				goodsSn: goodsSn,
				qty: qty,
				warehouseCode: warehouseCode,
				goodsType: goodsType
			}],
			animation: {
				imgSrc: imgSrc,
				origin: $this,
			}
		})
	})
})

function geshopUrlToApp (goodsUrl, goods_id) {
	var urlconfig = {
		'zf-app': 'zaful://action?actiontype=3&url=' + goods_id + '&name=scoop-neck-loose-knit-sweater&source=deeplink',
		'rw-app': 'rosewholesale://product?goods_id=' + goods_id + '&source=',
		'rg-app': 'rosegal://product?goods_id=' + goods_id + '&source='
	}
	if (GESHOP_PLATFORM || GESHOP_SITECODE) {
		if (GESHOP_PLATFORM != 'app') {
			return goodsUrl
		} else {
			return urlconfig[GESHOP_SITECODE]
		}
	} else {
		return goodsUrl
	}

}

/**
 * APP切换货币符号的函数
 */
function getCurrencyInfoInGEShop () {
	if (!GEShopSiteCommon.getSearch('is_app')) {
		return
	}

	var agent = window.navigator.userAgent.toLowerCase(), agentArr, agentArrLen, currencyStr = '', currency = ''

	if (DOMAIN.search('rosegal.com') != -1) {
		agentArr = agent.substr(agent.indexOf('app_')).split('_')

		GLOBAL.currency.change_html(agentArr[7].toUpperCase())
	} else if (DOMAIN.search('zaful.com') != -1) {
		// Zaful 网站APP货币
		if (agent.match(/android/i)) {
			agentArr = agent.split(';')
			agentArrLen = agentArr.length

			for (var index = 0; index < agentArrLen; index = index + 1) {
				if (agentArr[index].indexOf('zaful_currency') != -1) {
					currencyStr = agentArr[index]
					currency = currencyStr.substring(currencyStr.indexOf('=') + 1).toUpperCase()
				}
			}
			GLOBAL.currency.change_html(currency)
		} else if (agent.match(/iphone/i) || agent.match(/ipad/i)) {
			window.location.href = 'webAction://currency?callback=getZafulIosCurrencyInfoInGEShop()'
		}
	} else if (DOMAIN.search('dresslily') != -1) {
		// Dresslily 网站
		GLOBAL.currency.change_html();
	}
}

function getZafulIosCurrencyInfoInGEShop (currency) {
	GLOBAL.currency.change_html(currency)
}

function updateRosegalUserInfoInGEShop () {
	if (!GEShopSiteCommon.getSearch('is_app')) {
		return
	}

	if (DOMAIN.search('rosegal.com') != -1) {
		window.location.href = 'webAction://checkUserInfo?callback=getRosegalUserInfoInGEShop()&isAlert=0'
	}
}

function getRosegalUserInfoInGEShop (user_id, api_token) {
	if (api_token) {
		$.ajax({
			url : '/index.php?m=app_h5&a=coupon_zone&do=a_join',
			type: 'post',
			data : { 'api_token': api_token }
		}).done(function (data) {
			//window.location.reload()
		})
	}
}

window.onload = function () {
	getCurrencyInfoInGEShop()
	updateRosegalUserInfoInGEShop()
}

/**
 * Common Class of GEShop for Clothes Sites
 *
 * @author 创新研发部前端组
 */
var GEShopSiteCommon = {}

/**
 * get cookie value
 *
 * @param {String} cookieKey The cookie key
 *
 * @returns {String} The cookie value
 */
GEShopSiteCommon.getCookie = function (cookieKey) {
	var cookieArray = document.cookie.split(';')
	var length = cookieArray.length
	var cookieVal = ''

	for (var index = 0; index < length; index = index + 1) {
		if (cookieArray[index].search(cookieKey) != -1) {
			cookieVal = cookieArray[index].substring(cookieArray[index].indexOf('='))

			break
		}
	}

	return cookieVal
}

/**
 * get location search value
 *
 * @param {String} searchKey The search key
 *
 * @returns {String} The search value
 */
GEShopSiteCommon.getSearch = function (searchKey) {
	var searchArray = window.location.search.substring(1).split('&')
	var length = searchArray.length
	var searchVal = false

	for (var index = 0; index < length; index = index + 1) {
		if (searchArray[index].search(searchKey) != -1) {
			searchVal = searchArray[index].substr(searchArray[index].indexOf('='))

			break
		}
	}

	return searchVal
}

/**
 * render currency 老版本，以后不要再用这个了
 */
GEShopSiteCommon.renderCurrency = function () {
	if (window.GLOBAL && window.GLOBAL.currency) {
		window.GLOBAL.currency.change_html()
	}

	if (window.FUN && window.FUN.currency) {
		window.FUN.currency.change_html()
	}
}

/**
 * 货币切换渲染函数，version 2
 * Date: 2019-05-22
 * By: Cullen
 * 支持 zaful, rosegal 调用
 * 支持 pc, m, app 调用
 */
GEShopSiteCommon.renderCurrency_v2 = function () {
	// PC or M 端
	if (!GEShopSiteCommon.getSearch('is_app')) {
		if (window.GLOBAL && window.GLOBAL.currency) {
			window.GLOBAL.currency.change_html()
		}
		if (window.FUN && window.FUN.currency) {
			window.FUN.currency.change_html()
		}
	}
	// APP 端
	else {
		getCurrencyInfoInGEShop()
	}
}

/**
 * App Login
 *
 * @param {String} webLoginUrl The web login url
 */
GEShopSiteCommon.webLoginAction = function (webLoginUrl) {
	if (!this.getSearch('is_app')) {
		try {
			window.location.href = webLoginUrl + window.location.href
		} catch (e) {
			//console.log(e)
		}
	} else {
		var site = 0
		site = window.DOMAIN.search('zaful.com') != -1 ? 1 : site
		site = (site == 0 && window.DOMAIN.search('rosegal.com') != -1) ? 2 : site
		site = (site == 0 && window.DOMAIN.search('rosewholesale.com') != -1) ? 3 : site

		switch (site) {
			case 1:
				window.location.href = 'webAction://login?callback=appUserInfo()&isAlert=1'
				break
			case 2:
				window.location.href = 'webAction://checkUserInfo?callback=appUserInfo()&isAlert=1'
				break
			case 3:
				window.location.href = webLoginUrl
		}
	}
}

/**
 * app 登陸功能 注：该JS文件只能手动压缩 坑啊
 * state 0 不是 app端 1 初始化并已经登录 2 只初始化未登录
 * @returns {{msg: string, state: number}|{msg: string, state: number}}
 */

GEShopSiteCommon.appLogin = function (fn, sessionID ) {
	var sessionIDs = sessionID;
	// 空数据获取 cookie 的值，装修页没有 $.cookie 的函数
	if (!sessionID && $.cookie) {
		sessionIDs = $.cookie('WEBF-luck-wheel-session-id');
	}
	var res = {
		state: 0,
		msg: 'not app'
	}
	// app端才调用
	// if (GESHOP_PLATFORM === 'app') {
	if (typeof _GET !== 'undefined' && _GET('is_app')) {
		var callFn = window.deferred || window.g_infocheck_promise || (window.info_check && window.info_check.deferred )// 站点的用户信息校验
		var platform = GESHOP_SITECODE.split('-')[0] // 站点
		var flag =  0 // 参数 0 不弹窗 1 为弹出登陆
		if (fn == 1) {
			flag = 1
		}
		// 各个站点的deeplink
		var urlArr = {
			'zf': 'webAction://login?callback=appUserInfo()&isAlert=' + flag,
			'rg': 'webAction://checkUserInfo?callback=appUserInfo()&isAlert=' + flag + '&sessionID=' + sessionIDs,
			'dl': 'webAction://login?callback=geshopAppUserInfo()&isAlert=' + flag,
		}
		if (fn == 1) { // fn == 1 时不需要初始化，为调起登陆弹出功能
			window.location.href = urlArr[platform]
		} else if (typeof fn === 'function') {
			//传入函数 扩展 未测试正确性 慎用！
			if( !JSON.stringify(sessionStorage['is_app_login']) ) { // 未登录
				$.when(callFn).then(function () {
					window.location.href = urlArr[platform]
				})
			}
			setTimeout(function () {
				fn()
			}, 1500)
		} else {
			// 默认处理
			// DL geshopAppUserInfo方法 不存在session['is_app_login']
			// GEShopSiteCommon.getCookie('WEBF-user_id') 获取页面cookie， 没有代表未登录
			var needUserCookie = platform === 'dl' ? GEShopSiteCommon.getCookie('WEBF-user_id') : JSON.stringify(sessionStorage['is_app_login']);
			if(!needUserCookie){ // 未登录
				$.when(callFn).then(function () {
					// 保证站点校验完成 执行跳转
					setTimeout(function () {
						window.location.href = urlArr[platform]
					}, 1000)
				})
			}
		}
	} else { // 非app端
		if (fn && typeof fn === 'function') {
			fn(res)
		} else {
			return res
		}
	}
}

/**
 * Dialog
 */
GEShopSiteCommon.dialog = {}

/**
 * initialize dialog
 *
 * @param {Function} callback The callback function
 */
GEShopSiteCommon.dialog.init = function (callback) {
	$LAB
		.script(GESHOP_STATIC + '/resources/javascripts/library/jquery.blockUI.js').wait(function () {
		if (typeof callback == 'function') {
			callback()
		}
	})
}

/**
 * creates closing style code
 */
GEShopSiteCommon.dialog.createsClosingStyle = function () {
	if (document.getElementById('block_ui_style')) {
		return false
	}

	var styleElement = document.createElement('style')
	var stylesheetCode = '' +
		'.geshop-dialog-close { position: absolute; right: -15px; top: -15px; z-index: 10002; width: 30px; height: 30px; margin-left: 0; background-image: url(data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAB4AAAAeCAYAAAA7MK6iAAAAGXRFWHRTb2Z0d2FyZQBBZG9iZSBJbWFnZVJlYWR5ccllPAAAAyJpVFh0WE1MOmNvbS5hZG9iZS54bXAAAAAAADw/eHBhY2tldCBiZWdpbj0i77u/IiBpZD0iVzVNME1wQ2VoaUh6cmVTek5UY3prYzlkIj8+IDx4OnhtcG1ldGEgeG1sbnM6eD0iYWRvYmU6bnM6bWV0YS8iIHg6eG1wdGs9IkFkb2JlIFhNUCBDb3JlIDUuMy1jMDExIDY2LjE0NTY2MSwgMjAxMi8wMi8wNi0xNDo1NjoyNyAgICAgICAgIj4gPHJkZjpSREYgeG1sbnM6cmRmPSJodHRwOi8vd3d3LnczLm9yZy8xOTk5LzAyLzIyLXJkZi1zeW50YXgtbnMjIj4gPHJkZjpEZXNjcmlwdGlvbiByZGY6YWJvdXQ9IiIgeG1sbnM6eG1wPSJodHRwOi8vbnMuYWRvYmUuY29tL3hhcC8xLjAvIiB4bWxuczp4bXBNTT0iaHR0cDovL25zLmFkb2JlLmNvbS94YXAvMS4wL21tLyIgeG1sbnM6c3RSZWY9Imh0dHA6Ly9ucy5hZG9iZS5jb20veGFwLzEuMC9zVHlwZS9SZXNvdXJjZVJlZiMiIHhtcDpDcmVhdG9yVG9vbD0iQWRvYmUgUGhvdG9zaG9wIENTNiAoV2luZG93cykiIHhtcE1NOkluc3RhbmNlSUQ9InhtcC5paWQ6NDg1QjM5RUREQjcxMTFFODlBNDZCRjdFMjlGNkExN0EiIHhtcE1NOkRvY3VtZW50SUQ9InhtcC5kaWQ6NDg1QjM5RUVEQjcxMTFFODlBNDZCRjdFMjlGNkExN0EiPiA8eG1wTU06RGVyaXZlZEZyb20gc3RSZWY6aW5zdGFuY2VJRD0ieG1wLmlpZDo0ODVCMzlFQkRCNzExMUU4OUE0NkJGN0UyOUY2QTE3QSIgc3RSZWY6ZG9jdW1lbnRJRD0ieG1wLmRpZDo0ODVCMzlFQ0RCNzExMUU4OUE0NkJGN0UyOUY2QTE3QSIvPiA8L3JkZjpEZXNjcmlwdGlvbj4gPC9yZGY6UkRGPiA8L3g6eG1wbWV0YT4gPD94cGFja2V0IGVuZD0iciI/PmkvRE0AAAPXSURBVHjarJdNSBtBFIDfLir4ExPUSFBIrVYUBRsoFATBS2NLD72U0oiHCPEHL1IoufZWPJceSrRiDXpJeym9tBgVSqhQQayC+IPWamNDQiT+EKii6XvbzHay2c2apA8ek5ndfd+bmffeTATIQi4vLzux6UC1olpQL1AjgiB8xzaAbQD+lyQSCRsCPajhhI7gOz9RX6K25AO0ooGRRI6C3z5DNWvZFzSgd1Df4tKZ+PFIJAKrq6sQi8Xg9PQU8DmUlpZCRUUFtLW1Sa3Czi9sHKIoftYFo5dONPiGH1tZWQG/3w/hcDjjKtXU1EBXVxc0NzcrHXiA8A+aYITeRehH1j85OYHp6WnY3d3NapsaGxuhu7sbSkpKeHgnP3OBg17H5huCDdQPBoMwOTkJx8fHOcVIZWUluFwuefkR/ANt30Q9or7IvTvEoATLB0oSjUZhfHwc4vH43xkKwjVisOdi0ptb+MDNBqempvKC8nCfz8cPjeDK3uDBsifLy8uwt7enGjiZpKioCEwmU9r4+vo6bG5u8kNDMhhn62Kjs7OzaR/bbDYYHh6G/v5+KCwsTHtusVjA7XZLSg4oZWZm5l80J1kiTt3OBil6KVeVsr29LS19Q0MD9Pb2psCrq6slhwwGA+zs7MDZ2Vna9/v7+xAKhVjXiMzbYrL2SrK1taW6jJRWHo8nDU7QwcFBqYgQ1Ov1am6FwnYHgZv4ypQpUHg4pQoPnZiYgPPzc83vFbabRFxzC+tRGdSL0rGxMQleV1d3ZaiKbYuYbYpQfSaV81EUc0o1EVNJ3vWysrKML5vNZjmQKBBp72nmtOxq0c6LwnaI3N3gDWeCDgwMyNFLVWl0dDQFrpZKGrY3CBzgi7uaUGHgoWxPKWB4uNPp1Fx6he0ABdcc61mt1rQzlaS2tlaCUj4rA4nBWcAVFBSofk9FJilH6NxXIXkyfUEH2un30tKSsr7KhSLTeUx7XFxcrFrjKe/ZGY0x9QLBT9i6POXLI3moFL1LAK2CGrS+vl55MXgl12r0YAE9WWDp4XA4Ug7xXKW8vFyyxV0GnqP9jbTzGB/8ZhFIUUr7mqtQQPb19UnwJDTKZqt29XmIe/2O9elSR/X34OAgKygFWU9PT4rjCL6Hs/2U1WVvcXER5ufn4fDwULfA2O126capuOw9RqhP93qL8PvYvEcHUnKD7mFra2tS3lKwUTxUVVWB0WiE1tZWPmUYMIY2HqH6s/m70kL/CvK40NOfAWvOQYIG2lG9qPEr8GL43mu6w+keNlk6YU9eHGhNW3AJLxBC6RGUyqAozl3V1h8BBgAskqDnc4fYywAAAABJRU5ErkJggg==); background-repeat: no-repeat; background-position: center; }' +
		'.geshop-dialog-close:hover { background-image: url(data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAB4AAAAeCAYAAAA7MK6iAAAAGXRFWHRTb2Z0d2FyZQBBZG9iZSBJbWFnZVJlYWR5ccllPAAAAyJpVFh0WE1MOmNvbS5hZG9iZS54bXAAAAAAADw/eHBhY2tldCBiZWdpbj0i77u/IiBpZD0iVzVNME1wQ2VoaUh6cmVTek5UY3prYzlkIj8+IDx4OnhtcG1ldGEgeG1sbnM6eD0iYWRvYmU6bnM6bWV0YS8iIHg6eG1wdGs9IkFkb2JlIFhNUCBDb3JlIDUuMy1jMDExIDY2LjE0NTY2MSwgMjAxMi8wMi8wNi0xNDo1NjoyNyAgICAgICAgIj4gPHJkZjpSREYgeG1sbnM6cmRmPSJodHRwOi8vd3d3LnczLm9yZy8xOTk5LzAyLzIyLXJkZi1zeW50YXgtbnMjIj4gPHJkZjpEZXNjcmlwdGlvbiByZGY6YWJvdXQ9IiIgeG1sbnM6eG1wPSJodHRwOi8vbnMuYWRvYmUuY29tL3hhcC8xLjAvIiB4bWxuczp4bXBNTT0iaHR0cDovL25zLmFkb2JlLmNvbS94YXAvMS4wL21tLyIgeG1sbnM6c3RSZWY9Imh0dHA6Ly9ucy5hZG9iZS5jb20veGFwLzEuMC9zVHlwZS9SZXNvdXJjZVJlZiMiIHhtcDpDcmVhdG9yVG9vbD0iQWRvYmUgUGhvdG9zaG9wIENTNiAoV2luZG93cykiIHhtcE1NOkluc3RhbmNlSUQ9InhtcC5paWQ6NDg1QjM5RjFEQjcxMTFFODlBNDZCRjdFMjlGNkExN0EiIHhtcE1NOkRvY3VtZW50SUQ9InhtcC5kaWQ6NDg1QjM5RjJEQjcxMTFFODlBNDZCRjdFMjlGNkExN0EiPiA8eG1wTU06RGVyaXZlZEZyb20gc3RSZWY6aW5zdGFuY2VJRD0ieG1wLmlpZDo0ODVCMzlFRkRCNzExMUU4OUE0NkJGN0UyOUY2QTE3QSIgc3RSZWY6ZG9jdW1lbnRJRD0ieG1wLmRpZDo0ODVCMzlGMERCNzExMUU4OUE0NkJGN0UyOUY2QTE3QSIvPiA8L3JkZjpEZXNjcmlwdGlvbj4gPC9yZGY6UkRGPiA8L3g6eG1wbWV0YT4gPD94cGFja2V0IGVuZD0iciI/PuRr4IwAAARHSURBVHjarJddbFRFFMfPzL12d7tbCqFfhgTjA5b0ASi0BgJpKxFJjBII/SAQgxGjbr8wgUTelAcSTCSBttr4oOHFCpQiyIuAaULZuNWwGFSMVRNTw1e6W2CLa3fb7l7/c9k7TNm73WvbSWbPnXvvnN/MnHPPOcvof7RUKlULsQF9KXoZehI9zBj7CzIAGaD5aoZhrALwU/QRI0fDOzfRO9Ercull1sVIfcO0B8W9p5ZClx+7ODDLBb8P0R1pbAqr90tO92YHA/oiJvYCulAecyRCD7+5QInQNWL3RonicczGdF8BsUWLKK96DXnr6kgrKVHhdyB2AD6QE1x06uRuAI+rwPtdn1Dqxg2hKucBapWVtPCtN4kXFakL2AL4eRXM1WmAblahscsDFGlpA/QXB1ATQckfr9FoWzuNDww8Xg5jX0N3ja2N4RDPQlzHSwVi/OCLHpo8e3ZOjunZuZN827Zaux6G7pXo0Sd37LegsYuX5gwVbbynh+KBgLXrZwRj2o6xmjUQVy2bRlpaiaVS8/M96jot7uyQNgdrGef8T54eyJXcO9qRAWUuF7k3bpxRf15VFWnFxZkPpqZo7LPP1Tt+edQ4hj1CJsNhMoZ+e8JRGRUeeI8K/O+Qd0eTLdS1di0V7t9HhQc/IOb1ZjyfvBoydassDqfaZL3w8KtzdpGAxmFzSiYpf/v2DLiALnh3L5GmUeLKFTJiMVtvj1+WXl4I5vM8HXvNNvHTz7Y7SgSDNHasIwOuQv89c4ZiX57IaopEKKQON+j4KZfD+6PZJwo45IK97SZcW7KEXNXVjqCmGW/fVoflHGdeJs05OTnjZLlzOJ/YrVOo2USIfdzK+Cyi/8xjh43jU7ordehPzfiytCnnlBj8PqvD2Ta3Wx3dFTsekkNkmZxQcbx9fTR25AiNHT3mGM7LnlaHQwIsq4a8FSscQWMnTj6y+eCgY7i7ukodBoRz9Vsj39YtNpmOkWfzSxlQ6XAK3FVTYxtARGR218rkFEXI/EEHOIkPOgi5Ti8tJbbsOTL++H2a80QPf0iu9esp3t9v7+2ARz+aouTwsG0A0VdXynAKnzoukwTA6wD+zvzeRkZotLWdmDFPSUJDkuialiSWY8emjeGkPIgbQfM9lC7ePW/MW7Ho87+tQg8JaEY+xoOEuPDCpnmvvDpnqKexkTy1tRZUhMVu6eXWBWqi6xC7ZCTf/RrlNzeTwfksjlcjX0sz+Rrq1bu7wLiVAU7D+7Cy162x94U6Kvq4i1j5cud5f+Uq2LSTPKg4lWKvCbov5KyrUZi9DHEODqer1eY/orxFbjUiYWITE4+UokjgJaVIGFWUj4Vq+DIU4APoaAg3NH7rqK5OwyvSdVjrLAv6w5jbDejfdgW9nm0ijuZXiDYsoCddrtRDkScHLwrgaQHE/JCjvzAO/7RtShcOIpVWiOADkPg8bplhkPN+p7r+E2AAT1cDCJQri0AAAAAASUVORK5CYII=); }'

	styleElement.id = 'block_ui_style'
	styleElement.type = 'text/css'
	styleElement.innerHTML = stylesheetCode

	document.getElementsByTagName('head')[0].appendChild(styleElement)
}

/**
 * unblock
 */
GEShopSiteCommon.dialog.unblock = function () {
	$.unblockUI({ fadeOut: 200 })
}

/**
 * message
 *
 * @param {String} message The message string
 */
GEShopSiteCommon.dialog.message = function (message) {
	this.init((function (message) {
		return function () {
			$.blockUI({
				message: message,
				css: {
					padding: '15px',
					marginLeft: '-100px',
					width: '200px',
					left: '50%',
					opacity: .8,
					fontSize: '14px',
					color: '#fff',
					border: 'none',
					backgroundColor: '#000',
					cursor: 'auto'
				},
				overlayCSS: {
					backgroundColor: 'transparent',
					opacity: 1,
					cursor: 'default'
				}
			})

			setTimeout($.unblockUI, 2000)
		}
	})(message))
}

/**
 * iframe
 *
 * @param {String} iframeSrc The iframe attribute src's value
 * @param {Number} iframeWidth The iframe width
 * @param {Number} iframeHeight The iframe height
 * @param {Boolen} closing The flag that needs closing or not
 */
GEShopSiteCommon.dialog.iframe = function (iframeSrc, iframeWidth, iframeHeight, closing) {
	this.init((function (iframeSrc, iframeWidth, iframeHeight, closing) {
		return function () {
			var closingHtml = ''

			if (closing) {
				closingHtml = '<a href="javascript:;" class="geshop-dialog-close"></a>'
			}

			if (GESHOP_SITECODE == 'rg-pc') {

				var params = {
					area: [iframeWidth + 'px', iframeHeight + 'px'],
					offset: [($(window).height() - 550) / 2 + 'px', '50%'],
					shadeClose: true,
					iframe: {
						src: iframeSrc
					}

				}
				GLOBAL && GLOBAL.PopObj.iframe(params)
			} else if (GESHOP_SITECODE == 'zf-pc') {
				if (GESHOP_LANG == 'en') {
					var params = {
						area: [iframeWidth + 'px', iframeHeight + 'px'],
						offset: [($(window).height() - 550) / 2 + 'px', '50%'],
						shadeClose: true,
						iframe: {
							src: iframeSrc
						},
						closeBtn: false,
						scrollbar: false
					}
				} else {
					var params = {
						area: [iframeWidth + 'px', iframeHeight + 'px'],
						offset: [($(window).height() - 550) / 2 + 'px', '50%'],
						shadeClose: true,
						iframe: {
							src: iframeSrc
						},
						scrollbar: false
					}
				}

				GLOBAL.PopObj.iframe(params)
			} else {
				$.blockUI({
					message: $(closingHtml + '<iframe src="' + iframeSrc + '" width="' + iframeWidth + '" height="' + iframeHeight + '" style="border: none;"></iframe>'),
					css: {
						margin: '-' + iframeHeight / 2 + 'px auto auto -' + iframeWidth / 2 + 'px',
						width: iframeWidth,
						top: '50%',
						left: '50%',
						border: 'none',
						cursor: 'default'
					},
					blockMsgClass: 'geshop-dialog-iframe',
					onBlock: function () {
						GEShopSiteCommon.dialog.createsClosingStyle()

						$('body').on('click', '.geshop-dialog-close', function () {
							GEShopSiteCommon.dialog.unblock()
						})
					}
				})
			}
		}
	})(iframeSrc, iframeWidth, iframeHeight, closing))
}

/**
 * modal
 *
 * @param {String} selector The selector string
 * @param {Number} modalWidth The modal width
 * @param {Number} modalHeight The modal height
 * @param {Function} blockCallback The block callback function
 * @param {Function} unblockCallback The unblock callback function
 */
GEShopSiteCommon.dialog.modal = function (selector, modalWidth, modalHeight, blockCallback, unblockCallback) {
	this.init((function (selector, modalWidth, modalHeight, blockCallback, unblockCallback) {
		return function () {
			$.blockUI({
				message: $(selector),
				css: {
					margin: '-' + modalHeight / 2 + 'px auto auto -' + modalWidth / 2 + 'px',
					width: modalWidth,
					top: '50%',
					left: '50%',
					border: 'none',
					cursor: 'default'
				},
				onBlock: function () {
					if (typeof blockCallback == 'function') {
						blockCallback()
					}
				},
				onUnblock: function () {
					if (typeof unblockCallback == 'function') {
						unblockCallback()
					}
				}
			})
		}
	})(selector, modalWidth, modalHeight, blockCallback, unblockCallback))
}

/**
 * content
 *
 * @param {String} content The selector string
 * @param {Number} modalWidth The modal width
 * @param {Number} modalHeight The modal height
 * @param {Function} blockCallback The block callback function
 * @param {Function} unblockCallback The unblock callback function
 */
GEShopSiteCommon.dialog.content = function (content, modalWidth, modalHeight, blockCallback, unblockCallback) {
	this.init((function (content, modalWidth, modalHeight, blockCallback, unblockCallback) {
		return function () {
			$.blockUI({
				message: content,
				css: {
					margin: '-' + modalHeight / 2 + 'px auto auto -' + modalWidth / 2 + 'px',
					width: modalWidth,
					height: modalHeight,
					top: '50%',
					left: '50%',
					border: 'none',
					cursor: 'default'
				},
				onBlock: function () {
					if (typeof blockCallback == 'function') {
						blockCallback()
					}
				},
				onUnblock: function () {
					if (typeof unblockCallback == 'function') {
						unblockCallback()
					}
				}
			})
		}
	})(content, modalWidth, modalHeight, blockCallback, unblockCallback))
}

GEShopSiteCommon.dialog.custom = function (options) {
	this.init((function (options) {
		return function () {
			var css = {
				margin: '-' + options.modalHeight / 2 + 'px auto auto -' + options.modalWidth / 2 + 'px',
				width: options.modalWidth,
				height: options.modalHeight,
				top: '50%',
				left: '50%',
				border: 'none',
				cursor: 'default'
			}

			var newcss = Object.assign(css, options.cssoptions)
			$.blockUI({
				message: options.content,
				css: newcss,
				overlayCSS:{
					cursor:options.overlayCSS && options.overlayCSS.cursor || 'wait'
				},
				blockMsgClass: options.className || '',
				timeout: options.timeOut,
				onOverlayClick: options.onOverlayClick ? $.unblockUI : null,
				onBlock: function () {
					if (typeof options.blockCallback == 'function') {
						options.blockCallback()
					}
				},
				onUnblock: function () {
					if (typeof options.unblockCallback == 'function') {
						options.unblockCallback()
					}
				}
			})
		}
	})(options))
}

/**
 *  @Description 遍历当前活动页所有组件，是否存在js-geshop-nav-fixed, 判断隐藏站点导航栏及页面水平导航组件
 *  @params param object
 */

GEShopSiteCommon.jsNavFixed = function (params) {
	var param = params || {}
	var $box = param.box || $('.geshop-component-box'),
		$nav = $('.js-geshop-nav'),
		$navDl = $('.js_header'),
		$rowNav = $('div[data-key="U000027"]'),
		$rowNavWap = $('div[data-key="U000030"]'),
		$rowNavDl = $('div[data-key="U000186"]')

	var $currentBox = param.currentBox
	var isFixed = param.isFixed

	// 当前组件
	if ($currentBox) {
		// 添加样式
		if (isFixed) {
			if (!$currentBox.hasClass('js-geshop-nav-fixed')) {
				$currentBox.addClass('js-geshop-nav-fixed')
			}
		} else {
			$currentBox.removeClass('js-geshop-nav-fixed')
		}
	}

	$box.each(function(){
		var $this = $(this)

		if ($this.hasClass('js-geshop-nav-fixed')) {
			// zf,rg站点导航栏
			if ($nav.length) {
				$nav.hide()
			}

			// D网站点导航栏
			if ($navDl.length) {
				$navDl.hide()
			}

			// zf,rg水平导航(PC端)
			if ($rowNav.length) {
				$rowNav.find('ul').hide()
			}

			// zf,rg水平导航(M端)
			if ($rowNavWap.length) {
				$rowNavWap.find('nav').hide()
			}

			// D网水平导航
			if ($rowNavDl.length) {
				$rowNavDl.find('nav').hide()
			}
			return false
		} else {
			// zf,rg站点导航栏
			if ($nav.length) {
				$nav.show()
			}

			// D网站点导航栏
			if ($navDl.length) {
				$navDl.show()
			}

			// zf,rg水平导航(PC端)
			if ($rowNav.length) {
				$rowNav.find('ul').show()
			}

			// zf,rg水平导航(M端)
			if ($rowNavWap.length) {
				$rowNavWap.find('nav').show()
			}

			// D网水平导航
			if ($rowNavDl.length) {
				$rowNavDl.find('nav').show()
			}
		}
	})
}

/**
 * 判断元素是否吸顶并显示display:block;
 * @param target
 * @returns {jQuery|boolean}
 */
GEShopSiteCommon.isTargetFixedShow = function(target){
	return $(target).length && $(target).css('position') === 'fixed' && $(target).css('display') !== 'none';
}

/**
 * 获取吸顶高度 包含（水平导航高度 + 站点导航js-geshop-nav高度）
 * navFixedH 站点自带吸顶高度
 * navCompFixed 水平导航吸顶高度
 * @return {number}
 */
GEShopSiteCommon.getNavFixedHeight = function(){
	var navFixedH = GEShopSiteCommon.isTargetFixedShow('.js-geshop-nav') ? $('.js-geshop-nav').height() : 0;
	var navCompFixedH = GEShopSiteCommon.isTargetFixedShow('#geshop-page-content .component-nav-fixed') ? $('#geshop-page-content .component-nav-fixed').height() : 0;
	return navFixedH + navCompFixedH
}

/**
 * animate 导航定位页面滚动
 * @param target 标题组件选择器 默认 '.geshop-component-box[data-id=' + navigatedId + ']'
 * @returns {boolean}
 */
GEShopSiteCommon.geshopNavAnimate = function(target){
	var $target = $(target);
	if(!$target){
		return false;
	}
	var navFixedH = typeof GEShopSiteCommon !== 'undefined' && GEShopSiteCommon.getNavFixedHeight() || 0;
	$('html, body').animate({
		scrollTop: $target.offset().top - navFixedH
	}, 500);
}
// content, modalWidth, modalHeight, blockCallback, unblockCallback,cssoptions,onOverlayClick

