window.mobileAndTabletcheck = function () {
	var check = false;

	(function (a) { if (/(android|bb\d+|meego).+mobile|avantgo|bada\/|blackberry|blazer|compal|elaine|fennec|hiptop|iemobile|ip(hone|od)|iris|kindle|lge |maemo|midp|mmp|mobile.+firefox|netfront|opera m(ob|in)i|palm( os)?|phone|p(ixi|re)\/|plucker|pocket|psp|series(4|6)0|symbian|treo|up\.(browser|link)|vodafone|wap|windows ce|xda|xiino|android|ipad|playbook|silk/i.test(a) || /1207|6310|6590|3gso|4thp|50[1-6]i|770s|802s|a wa|abac|ac(er|oo|s\-)|ai(ko|rn)|al(av|ca|co)|amoi|an(ex|ny|yw)|aptu|ar(ch|go)|as(te|us)|attw|au(di|\-m|r |s )|avan|be(ck|ll|nq)|bi(lb|rd)|bl(ac|az)|br(e|v)w|bumb|bw\-(n|u)|c55\/|capi|ccwa|cdm\-|cell|chtm|cldc|cmd\-|co(mp|nd)|craw|da(it|ll|ng)|dbte|dc\-s|devi|dica|dmob|do(c|p)o|ds(12|\-d)|el(49|ai)|em(l2|ul)|er(ic|k0)|esl8|ez([4-7]0|os|wa|ze)|fetc|fly(\-|_)|g1 u|g560|gene|gf\-5|g\-mo|go(\.w|od)|gr(ad|un)|haie|hcit|hd\-(m|p|t)|hei\-|hi(pt|ta)|hp( i|ip)|hs\-c|ht(c(\-| |_|a|g|p|s|t)|tp)|hu(aw|tc)|i\-(20|go|ma)|i230|iac( |\-|\/)|ibro|idea|ig01|ikom|im1k|inno|ipaq|iris|ja(t|v)a|jbro|jemu|jigs|kddi|keji|kgt( |\/)|klon|kpt |kwc\-|kyo(c|k)|le(no|xi)|lg( g|\/(k|l|u)|50|54|\-[a-w])|libw|lynx|m1\-w|m3ga|m50\/|ma(te|ui|xo)|mc(01|21|ca)|m\-cr|me(rc|ri)|mi(o8|oa|ts)|mmef|mo(01|02|bi|de|do|t(\-| |o|v)|zz)|mt(50|p1|v )|mwbp|mywa|n10[0-2]|n20[2-3]|n30(0|2)|n50(0|2|5)|n7(0(0|1)|10)|ne((c|m)\-|on|tf|wf|wg|wt)|nok(6|i)|nzph|o2im|op(ti|wv)|oran|owg1|p800|pan(a|d|t)|pdxg|pg(13|\-([1-8]|c))|phil|pire|pl(ay|uc)|pn\-2|po(ck|rt|se)|prox|psio|pt\-g|qa\-a|qc(07|12|21|32|60|\-[2-7]|i\-)|qtek|r380|r600|raks|rim9|ro(ve|zo)|s55\/|sa(ge|ma|mm|ms|ny|va)|sc(01|h\-|oo|p\-)|sdk\/|se(c(\-|0|1)|47|mc|nd|ri)|sgh\-|shar|sie(\-|m)|sk\-0|sl(45|id)|sm(al|ar|b3|it|t5)|so(ft|ny)|sp(01|h\-|v\-|v )|sy(01|mb)|t2(18|50)|t6(00|10|18)|ta(gt|lk)|tcl\-|tdg\-|tel(i|m)|tim\-|t\-mo|to(pl|sh)|ts(70|m\-|m3|m5)|tx\-9|up(\.b|g1|si)|utst|v400|v750|veri|vi(rg|te)|vk(40|5[0-3]|\-v)|vm40|voda|vulc|vx(52|53|60|61|70|80|81|83|85|98)|w3c(\-| )|webc|whit|wi(g |nc|nw)|wmlb|wonu|x700|yas\-|your|zeto|zte\-/i.test(a.substr(0, 4))) check = true })(navigator.userAgent || navigator.vendor || window.opera)

	return check
}

if (mobileAndTabletcheck() &&
	window.location.href.search(/((https:\/\/www)|(https:\/\/es))\.rosewholesale\.com/) != -1) {
	window.location.href = window.location.href.replace(/((https:\/\/www)|(https:\/\/es))\.rosewholesale\.com/, 'https://m.rosewholesale.com')
}

(function ($) {
	(function ($) {
		$.fn.animateFade = function (options, isReverse) {
			options = $.extend({}, {
				start: {},
				end: {},
				duration: 300,
				callBack: $.noop
			}, options)
			isReverse = isReverse || false
			if (isReverse) {
				// 反转参数 start end
				var t
				t = options.start
				options.start = options.end
				options.end = t
			}
			this.each(function () {
				$(this).css(options.start).stop(true, false).animate(options.end, options.duration, options.callBack)
			})
		}
	})(jQuery)


	/**
	 * 汇率换算
	 */

	//获取汇率数据
	var my_array = JSON.parse($('input[name="exchange"]').val())
	//获取货币符号数据
	var my_sign = JSON.parse($('input[name="currency"]').val())

	var COOKIESDIAMON = getDiamon()
	var JS_IMG_URL = 'https://css.rowcdn.com/imagecache/RW_V3/'
	var js_type = 1

	//获取页面币种 cookie
	var currency = getCookie('bizhong'), currencyIcon

	if (!currency) {
		//默认币种是美元	TODO:后续换成cookie获取
		currency = 'USD'
		currencyIcon = '$'
	} else {
		currencyIcon = my_sign[currency].icon

		$('.js_showBZ').html('<label>' + currencyIcon + '</label>' + currency)
		//计算结果
		calculateFn(currency, currencyIcon)
	}

	goTop()

	//计算汇率换算结果 方法
	function calculateFn (currency, currencyIcon) {
		var currencyDoms = $('.my_shop_price')

		var parameterOfSin = currencyIcon
		var parameterOfPrice = my_array[currency]

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
	/* 获取域名常量 */
	function getDiamon () {
		var url = window.location.host, temp
		url.replace(/.[A-Za-z0-9_-]*/, function ($1) {
			temp = $1
		})
		return window.location.host.substr(temp.length, url.length)
	}

	function goTop () {
		var $gotoTop = $('<div class="backToTop tag_c"></div>')
			.click(function () {
				$('body,html').animate({
					scrollTop: '0px'
				}, 500)
			})
		var width = 250

		if (typeof (js_type) == 'undefined') {
			width = 130
		}
		var str = '<div class="app-pop-wrap" style="width:' + width + 'px;"><i class="triangle bord-tri"></i><i class="triangle bg-tri"></i><div class="app-box"><div class="item item-web"><img src="' + JS_IMG_URL + '/images/loadingbg.gif" data-original="' + JS_IMG_URL + 'images/domeimg/app/web_microcode.png?01" width="90" height="90"/><span class="text">Mobile Site</span></div>'
		if (typeof (js_type) != 'undefined') {
			str += '<div class="item item-web"><img src="' + JS_IMG_URL + '/images/loadingbg.gif" data-original="' + JS_IMG_URL + '/images/domeimg/app/approut.png?01" width="90" height="90"/><span class="text">APP</span></div>'
		}
		str += '</div></div>'
		var $appPop = $(str)

		var $appBox = $('<div />').attr('class', 'app-icon tag_c').append($appPop).hover(function () {
			$appPop.find('.item.item-web img').each(function (item) {
				var url = $(this).data('original')
				if ($(this).attr('src') != url) {
					$(this).attr('src', url)
				}
			})
			$appPop.animateFade({
				start: {
					right: '44px',
					opacity: 0,
					display: 'block'
				},
				end: {
					right: '34px',
					opacity: 1
				}
			})
		}, function () {
			$appPop.animateFade({
				start: {
					right: '44px',
					opacity: 0,
					display: 'block'
				},
				end: {
					right: '34px',
					opacity: 1
				},
				callBack: function () {
					$(this).hide()
				}
			}, true)
		})

		var $bottomTip = $('<div />').attr('class', 'bottom-tip none')
			.append($appBox)
			.append($gotoTop)

		$('body').append($bottomTip)

		$(window).scroll(function () {

			if ($(this).scrollTop() > 50) {
				$bottomTip.show()
			} else {
				$bottomTip.hide()
			}
		})
	}

	//列表点击切换币种
	$('#js-changeMoney li').on('click', function (event) {
		var ticDom = $(this)
		var bizhong = ticDom.attr('data-bizhong')
		var icon = ticDom.attr('data-icon')

		if (!bizhong) return

		currency = bizhong
		currencyIcon = icon

		setCookie('bizhong', currency)

		$('.js_showBZ').html('<label>' + icon + '</label>' + bizhong)

		//计算结果
		calculateFn(currency, currencyIcon)
		$('#js_topOperal .huilv .jd_bzList').hide()
	})

	$('#js_topOperal .huilv').mouseenter(function () {
		$('#js_topOperal .huilv .jd_bzList').show()
	}).mouseleave(function () {
		$('#js_topOperal .huilv .jd_bzList').hide()
	})



	//页面初始化 执行一次
	// calculateFn( currency, currencyIcon )

	/**
	 * 汇率换算 - end
	 */

	/**
	 * 搜索字段处理
	 */
	$('#t_searchform').on('submit', function (e) {
		//TODO: 确认 domain 是否需要写死？
		var $keyword = $('#js_topSearch'),
			// domain = window.location.protocol + '//' + window.location.host,
			domain = 'https://www.rosewholesale.com/',
			kw

		kw = $.trim($keyword.val())
		kw = kw.replace(/\s+/g, '-')
		kw = kw.replace(/\'/g, '')
		kw = kw.toLowerCase()

		if (kw.length < 1) {
			$keyword.val('')
			$keyword.focus()
		}
		else {
			kw = kw.replace('%20', '-')
			window.location.href = domain + 'cheap/' + kw + '/'
		}

		return false
	})

	/**
* 搜索字段处理 - end
*/

	/**
	 * 折扣倒计时
	 */
	var $target = $('.gs_countDown')

	function goodsCountDown () {
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
			setTimeout(goodsCountDown, 1000)
		}
	}

	goodsCountDown()

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

	$('.js_more_language').click(function () {
		$(this).hide()
		$(this).next().show().trigger('click')
	})

	$('#nav').find('li').hover(function () {
		var $this = $(this),
			$target = $this.find('div.sub_menu')

		$this.addClass('on')

		if ($target.length) {
			$target.addClass('move').show()
		}

		var banner_data = $(this).find('.js_nav_img_wrap').data('banner')

		if ($(this).find('.js_nav_img_wrap').length != 0 && $(this).find('.js_nav_img_wrap').html().trim() == '') {
			var banner_elem = ''

			$.each(banner_data, function (i, item) {
				banner_elem += '<a class="block" href="' + item['url'] + '">'
				banner_elem += '<div class="img"><img src="' + item['pic_url'] + '" alt="" data-original="' + item['pic_url'] + '" data-sku="' + item['id'] + '" data-module="ad" class="js_gdexp_lazy"></div>'
				banner_elem += '<p>' + item['pic_position_com'] + '</p></a>'
			})
			$(this).find('.js_nav_img_wrap').html(banner_elem)
		}
	}, function () {
		var $this = $(this),
			$target = $this.find('div.sub_menu')

		$this.removeClass('on')

		if ($target.length) {
			$target.removeClass('move').hide()
		}
	})
})($)
