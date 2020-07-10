(function ($) {

	var GS = (function (my) {
		/*
		* element jq class selector
		*/
		my.goodsLazy = function (element) {

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


	/* document ready  */
	$(function () {
		GS.goodsLazy('img.lazyload')
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
})(jQuery);

function geshopUrlToApp (goodsUrl,goods_id) {
	var urlconfig = {
		'zf-app':'zaful://action?actiontype=3&url='+goods_id+'&name=scoop-neck-loose-knit-sweater&source=deeplink',
		'rw-app':'rosewholesale://product?goods_id='+goods_id+'&source=',
		'rg-app':'rosegal://product?goods_id='+goods_id+'&source='
	}
	if(GESHOP_PLATFORM || GESHOP_SITECODE){
		if(GESHOP_PLATFORM != 'app' ){
			return goodsUrl;
		}else{
			return urlconfig[GESHOP_SITECODE]
		} 
	}else{
		return goodsUrl
	}
	
}