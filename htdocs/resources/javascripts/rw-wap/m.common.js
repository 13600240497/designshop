(function ($) {

	/**
 * by xingtao 2018.4.25
 */
	var GS = (function (my) {
		my.goTop = function () {
			var parent = jQuery('body'),
				target = jQuery('<div class="back-to-top none">top</div>')
			parent.append(target)
			target.on('click', function () {
				parent.animate({
					scrollTop: '0'
				}, 300)
			})

		}
		//header fix top
		my.fixNav = function () {
			var d = document
			jQuery(d).height() >= window.screen.availHeight * 1.3 && jQuery(d).scrollTop() >= 120 ? $('#header').addClass('slide--up').removeClass('slide--reset') : $('#header').addClass('slide--reset').removeClass('slide--up')
		}
		//search
		my.seach_submit = function (formObj) {
			var that = this
			formObj.submit(function (event) {
				return that.seachFormOpearal(this)
			})
		}
		//对用户输入的字符串进行转换
		my.ucwords = function (str) {
			var kwVal = (str + '').replace(/^([a-z\u00E0-\u00FC])|\s+([a-z\u00E0-\u00FC])/g, function ($1) {
				return $1.toUpperCase()
			})
			kwVal = encodeURIComponent($.trim(kwVal))
			kwVal = kwVal.replace('/[^\w\.]/g', '-')
			kwVal = kwVal.replace('%20', '-').toLowerCase()
			// kwVal = $.trim(str)
			kwVal = kwVal.replace(/\*/g, '~~')
			kwVal = kwVal.replace(/\|/g, ']]')
			kwVal = kwVal.replace(/\=/g, '((')
			kwVal = kwVal.replace(/\"/g, ' ')
			kwVal = kwVal.replace(/\</g, '))')
			kwVal = kwVal.replace(/\>/g, ')))')
			kwVal = kwVal.replace(/\?/g, '!!!')
			kwVal = kwVal.replace(/\+/g, '__')
			//        kwVal = kwVal.replace(/\-/g, '^^');
			kwVal = kwVal.replace(/\//g, '..')
			kwVal = kwVal.replace(/\\/g, '...')
			kwVal = kwVal.replace(/\%/g, '!!')
			kwVal = kwVal.replace(/\#/g, '~~~')
			kwVal = kwVal.replace(/\>/g, '___')
			kwVal = kwVal.replace(/\</g, '^^^')
			kwVal = kwVal.replace(/\"/g, '[[')
			kwVal = kwVal.replace(/\$/g, '[[[')
			kwVal = kwVal.replace(/\s+/g, '-')
			kwVal = kwVal.toLowerCase()
			kwVal = kwVal.replace(/\b\w+\b/g, function (word) {
				return word
			})

			return kwVal
		}
		// 跳转
		my.seachFormOpearal = function (obj) {
			var $this = $(obj)
			var $kw = $this.find('input[name="k2"]')
			var kwVal = this.ucwords($kw.val())
			if (kwVal == '') {
				$kw.focus()
			} else {
				layer.open({
					type: 2,
					shadeClose: false
				})
				var DOMAIN = 'https://m.rosewholesale.com/'
				window.location.href = DOMAIN + 'cheap/' + kwVal + '/'
			}
			return false
		}
		my.changeBizhong = function (currency, currencyIcon) {
			setCookie('bizhong', currency)

			//计算结果
			calculateFn(currency, currencyIcon)
			$(this).parents('.mm-listitem_vertical').removeClass('mm-listitem_opened')
			$('.js_currency-button').html('<label><i class="left-menu-icon bzIcon"></i>' + currencyIcon + '</label>' + currency)
			$.each($('#js_currencyMenu').find('li'), function (e, a) {
				var o = $(a)
				if (o.data('bizhong') == currency) {
					return o.addClass('current').siblings('li').removeClass('current')
				}
			})
		}
		return my
	}({}))


	var COOKIESDIAMON = getDiamon()
	/**
		 * 汇率换算
		 */

	//获取汇率数据
	var my_array = JSON.parse($('input[name="exchange"]').val())
	var my_Rate = my_array.Rate
	var my_Sign = my_array.Sign
	//获取页面币种 cookie
	var currency = getCookie('bizhong'), currencyIcon
	if (my_array) {
		currencyIcon = my_Sign[currency]
	}
	//默认币种是美元
	if (!currency) {
		currency = 'USD'
		currencyIcon = '$'
	}

	GS.changeBizhong(currency, currencyIcon)
	//计算汇率换算结果 方法
	function calculateFn (currency, currencyIcon) {
		var currencyDoms = $('.my_shop_price')

		var parameterOfSin = currencyIcon
		var parameterOfPrice = my_Rate[currency]

		currencyDoms.each(function (index, item) {
			var price = item.dataset.orgp
			if (!price) return

			item.innerHTML = parameterOfSin + ' ' + accMul(price, parameterOfPrice).toFixed(2)
		})

	}

	//设置 cookie 方法
	function setCookie (name, value) {
		var Days = 30
		var exp = new Date()
		exp.setTime(exp.getTime() + Days * 24 * 60 * 60 * 1000)
		document.cookie = name + '=' + escape(value) + ';expires=' + exp.toGMTString() + ';path=/' + ';domain=' + COOKIESDIAMON
	}

	//获取 cookie 方法
	function getCookie (name) {
		var arr,
			reg = new RegExp('(^| )' + name + '=([^;]*)(;|$)')

		if (arr = document.cookie.match(reg)) {
			return arr[2]
		} else {
			return null
		}

	}

	/* 获取域名常量 */
	function getDiamon () {
		var url = window.location.host, temp
		url.replace(/.[A-Za-z0-9_-]*/, function ($1) {
			temp = $1
		})
		return window.location.host.substr(temp.length, url.length)
	}

	//js 乘法运算数字处理
	function accMul (arg1, arg2) {
		var m = 0, s1 = arg1.toString(), s2 = arg2.toString()
		try {
			m += s1.split('.')[1].length
		}
		catch (e) { }

		try {
			m += s2.split('.')[1].length
		}
		catch (e) { }

		return Number(s1.replace('.', '')) * Number(s2.replace('.', '')) / Math.pow(10, m)
	}


	//header with footer
	jQuery(document).ready(function ($) {

		GS.goTop()
		//menu initial
		var menuButton = $('#js_navTopmenu'),
			menuAside = $('#gs_menuAside')
		menuAside.mmenu({
			extensions: [
				'border-full', 'fx-menu-zoom', 'fx-panels-slide-0', 'widescreen', 'shadow-page'
			]
		})
		var menuObj = menuAside.data('mmenu')
		menuButton.on('click', function () {
			menuButton.addClass('drawer-open')
			menuObj.open()
		})
		$('.mm-page__blocker').on('touchstart tap click', function () {
			menuButton.removeClass('drawer-open')
			menuObj.close()
		})
		//search
		$('#js_showSearchBox').on('click', function () {
			$('div[data-position=\'top\']').slideToggle('fast').addClass('on')
		})
		var $searchBox = $('.js_searchBox'),
			// $searchData = $searchBox.data('position'),
			$searchInput = $searchBox.find('input[name=\'k2\']'),
			$searchClearIpt = $searchBox.find('.js_clearIpt')
		$searchInput.on('keyup', function () {
			var e = $(this)
			e.val() ? e.closest('.js_searchBox').addClass('focus') : e.closest('.js_searchBox').removeClass('focus')
		})
		$searchClearIpt.on('click', function () {
			var e = $(this)
			e.prev('input[name=\'k2\']').val('').end().closest('.js_searchBox').removeClass('focus')
		})
		//scroll event
		var scrollTimer = null
		var scrollT = 0,
			top = 0
		$(window).on('scroll', function () {

			//header toggle
			if (scrollTimer) {
				clearTimeout(scrollTimer)
			}
			scrollTimer = setTimeout(function () {
				scrollT = $(this).scrollTop()
				if (top < scrollT || scrollT < 50) {
					$('.back-to-top').fadeOut()
				} else {
					$('.back-to-top').fadeIn()
				}
				setTimeout(function () {
					top = scrollT
				}, 0)

				GS.fixNav()
				return false
			}, 25)
		})

		//币种切换
		$('body').on('click', '#js_currencyMenu li', function (event) {
			// var ticDom = event.target || event.srcElement
			// var bizhong = ticDom.dataset.bizhong
			// var icon = ticDom.dataset.icon
			var bizhong = $(this).attr('data-bizhong')
			var icon = $(this).attr('data-icon')
			if (!bizhong) return

			var currency = bizhong
			var currencyIcon = icon

			setCookie('bizhong', currency)

			//计算结果
			calculateFn(currency, currencyIcon)
			$(this).parents('.mm-listitem_vertical').removeClass('mm-listitem_opened')
			$('.js_currency-button').html('<label><i class="left-menu-icon bzIcon"></i>' + icon + '</label>' + currency)
			$.each($('#js_currencyMenu').find('li'), function (e, a) {
				var o = $(a)
				if (o.data('bizhong') == currency) {
					return o.addClass('current').siblings('li').removeClass('current')
				}
				// if (o.children('a').data('bizhong') == currency) {
				// 	return o.addClass('current').siblings('li').removeClass('current')
				// }
			})
		})

		$('.back-to-top').on('click', function () {
			$('html').animate({
				scrollTop: '0'
			}, 300)
		})

		// 搜索
		; (function () {
			var top = $('#js_topSeachForm')
			var left = $('#js_leftSeachForm')
			top.length ? GS.seach_submit(top) : ''
			left.length ? GS.seach_submit(left) : ''
		})()

		/**
 * 懒加载
 */
		if ($.fn.lazyload) {
			$('img.lazyload').lazyload({
				threshold: 100,
				effect: 'fadeIn',
				failure_limit: 20,
				skip_invisible: false
			})
		} else {
			window.GS_GOODS_LAZY_FN('.lazyload');
		}
	})
}($))
