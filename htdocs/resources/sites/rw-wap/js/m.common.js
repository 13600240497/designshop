/* global code added  start*/

/*扩展browse方法*/
jQuery.extend({
	browser: function () {
		var rwebkit = /(webkit)\/([\w.]+)/,
			ropera = /(opera)(?:.*version)?[ \/]([\w.]+)/,
			rmsie = /(msie) ([\w.]+)/,
			rmozilla = /(mozilla)(?:.*? rv:([\w.]+))?/,
			browser = {},
			ua = window.navigator.userAgent,
			browserMatch = uaMatch(ua)
		if (browserMatch.browser) {
			browser[browserMatch.browser] = true
			browser.version = browserMatch.version
		}
		return { browser: browser }
	}
})

function uaMatch (ua) {
	ua = ua.toLowerCase()
	var match = rwebkit.exec(ua)
		|| ropera.exec(ua)
		|| rmsie.exec(ua)
		|| ua.indexOf('compatible') < 0 && rmozilla.exec(ua)
		|| []
	return {
		browser: match[1] || '',
		version: match[2] || '0'
	}
}

/*
* keyup延迟函数 delayKeyup
* 使用方法: $("#input").delayKeyup(function(){},1000);
*/
(function ($) {
	$.fn.delayKeyup = function (callback, ms) {
		var timer = 0
		$(this).on('keyup', function (event) {
			clearTimeout(timer)
			if (callback && typeof (callback) == 'function') {
				timer = setTimeout(function () {
					callback(event)
				}, ms)
			} else {
				throw new Error('Callback Error')
			}
		})
		return $(this)
	}
})(jQuery)

window.FUN = {}
/****搜索****/
FUN.Search = {
	seach_submit: function (formObj) {
		var that = this
		formObj.submit(function (event) {
			return that.seachFormOpearal(this)
		})

	},
	//对用户输入的字符串进行转换
	ucwords: function (str) {
		var kwVal = (str + '').replace(/^([a-z\u00E0-\u00FC])|\s+([a-z\u00E0-\u00FC])/g, function ($1) {
			return $1.toUpperCase()
		})
		kwVal = encodeURIComponent($.trim(kwVal))
		kwVal = kwVal.replace('/[^\w\.]/g', '-')
		kwVal = kwVal.replace('%20', '-').toLowerCase()
		kwVal = $.trim(str)
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
	},
	// 跳转
	seachFormOpearal: function (obj) {
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
			window.location.href = DOMAIN + 'cheap/' + kwVal + '/'
		}
		return false
	},
	/**
	 * [searchAjax 异步搜索]
	 * @author by yukai
	 * @date 2016-07-15
	 */
	searchAjax: {
		//参数
		param: {
			requestUrl: DOMAIN + 'index/search',
			searchInput: $('#js_topSearch'),
			searchDrop: $('#js_topSearchDrop')
		},
		defaultWords: '',
		//获取数据
		getData: function (keyword) {
			var _this = this,
				keywordArr = keyword.split(' ')
			//处理空格
			if (!keywordArr[1] && !keywordArr[keywordArr.length - 1]) {
				keyword = keywordArr[0]
			}
			if (keyword) {
				$.get(_this.param.requestUrl, {
					keyword: keyword,
					dataType: 'json',
					action: 'show_word'
				}, function (data) {
					_this.renderData(data)
				}, 'jsonp')
			}
			else {
				_this.setDefaultWords()
			}
		},
		//渲染数据到页面
		renderData: function (data) {
			var _this = this
			//如果有搜索数据则显示下拉,并且填充数据
			if (data.res != 'fail') {
				_this.toggleSearch(true, function () {
					_this.param.searchDrop.empty().html(data.res)
				})
			}
			//隐藏搜索,并设置默认关键词
			else {
				_this.setDefaultWords()
			}
		},
		//设置默认搜索词
		setDefaultWords: function () {
			var _this = this
			_this.toggleSearch(false, function () {
				_this.param.searchDrop.empty().html(_this.defaultWords)
			})
		},
		//显示/隐藏搜索框
		toggleSearch: function (flag, callback) {
			var _this = this
			//显示
			if (flag) {
				_this.param.searchDrop.show()
			}
			//隐藏
			else {
				_this.param.searchDrop.hide()
			}
			//回调
			if (typeof callback == 'function' && callback) {
				callback()
			}
		},
		//设置输入框关键词
		setKeyword: function (keyword, url) {
			this.param.searchInput.val(keyword)
			//如果默认搜索词设置了跳转地址,则直接跳转
			if (url) {
				window.location.href = url
			}
			else {
				$('#js_topSeachForm').trigger('submit')
			}
			this.toggleSearch(false)
		},
		//事件绑定
		bindEvent: function () {
			var _this = this,
				ele = _this.param.searchInput
			//输入事件(用到了延时函数)
			ele.delayKeyup(function (event) {
				var ingoreKey = [37, 38, 39, 40, 116, 27]//屏蔽的键值
				if (ingoreKey.indexOf(event.keyCode) != -1) {
					return
				}
				_this.getData(ele.val())
			}, 600)

			//点击空白区域隐藏
			$(document).on('click', function (e) {
				var id = $(e.target).closest('form').attr('id')
				if (id != 'js_topSeachForm') {
					_this.toggleSearch(false)
				}
			})

			//点击输入框显示搜索下拉框
			ele.on('click', function () {
				_this.toggleSearch(true)
			})

			//操作关键词
			_this.param.searchDrop.on('click', 'li', function (event) {
				var $this = $(this),
					url = $this.find('a').data('url'),
					text = $this.find('a').text()
				if (text) {
					_this.setKeyword(text, url)
				}
			})
		},
		//初始化
		init: function (options) {
			var _this = this,
				config = $.extend(true, _this.param, options)
			_this.defaultWords = _this.param.searchDrop.html()
			_this.bindEvent()
		}
	}
}

/****购物车****/
FUN.cart = {
	//更新购物车数量
	cartItems: function () {
		$.ajax({
			url: WAP_URL + 'misc/get-cart-num',
			dataType: 'jsonp',
			crossDomain: true,
			jsonp: 'jsoncallback',
		})
			.done(function (result) {
				document.getElementById('cartNum').innerHTML = result.data.cart_num
			})
	},
	//数量 加
	addNum: function (numObj) {
		var orig = Number(numObj.val())
		numObj.val(orig + 1).keyup()
		if (orig >= 1) {
			numObj.siblings('.js_reduceNum').removeClass('disabled')
		}
	},
	//数量 减
	reduceNum: function (numObj) {
		var orig = Number(numObj.val())
		orig < 3 ? numObj.siblings('.js_reduceNum').addClass('disabled') : ''
		orig > 1 ? numObj.val(orig - 1).keyup() : ''
	},
	//商品页面输入数量 (只在商品页面)
	enterNum: function (that, isSpecialGoods) {
		var $that = $(that),
			cur_num = parseInt($that.val()),
			$unit_price = $('#unit_price'),
			rang_mu, cur_pice_orgp

		var $addCarBtn = $('#addCart,#add_stay')
		if (!$.trim($that.val()).length) {
			return
		}
		//如果键入的不是数字 或者是0
		if (isNaN(cur_num) || cur_num == 0) {
			$that.val(1)
			return
		}

		//如果是特效商品，不用阶梯价格比较，单价保持不变
		if (arguments.length > 1 && isSpecialGoods == 1) {
			//改变购物按钮上的数量，以便加入购物车数量正确
			$addCarBtn.data('num', cur_num)
			return
		}
		if (cur_num < 9999) {
			$('input.js_orangNum').each(function (index, el) {
				rang_mu = $(this).data('val').toString()
				rang_mu = parseInt(rang_mu.replace(/[\u4e00-\u9fa5]/g, ''), 10)

				//比较是否在价格区间
				if (cur_num >= rang_mu) {
					cur_pice_orgp = $('#pk' + $(this).data('atrp')).attr('data-orgp')
					$unit_price.attr('data-orgp', cur_pice_orgp)
				}
			})
			FUN.currency.change_houbi('', $unit_price.parent())

			//改变购物按钮上的数量，以便加入购物车数量正确
			$addCarBtn.data('num', cur_num)

			$that.val(cur_num)
		}
	},
	/* 加入商品到购物车
	@desc
	@param      {object||string}  obj            点击那个按钮到购物车 该按钮需要有属性 data-gid/data-num/data-attrchage
	@param      {function}        callback       加入成功后，还需要何操作？
	@author     2015/05           jiangminjing
	*/
	addCart: function (obj, callback) {
		var addBtn = $(obj),
			peijianAttr = $(obj).data('peijian'),
			peijianId = '',//捆绑销售的配件id
			active_typeAttr = $(obj).data('active_type'),
			active_type = '',//区分特殊的专题活动
			stayAttr = $(obj).data('stay'),
			sk_id = $(obj).data('sk'),
			stay = 0
		if (typeof (peijianAttr) != 'undefined') {
			peijianId = peijianAttr.toString().split('|')
		}
		if (typeof (active_typeAttr) != 'undefined') {
			active_type = active_typeAttr
		}
		if (typeof (stayAttr) != 'undefined') {
			stay = stayAttr
		}
		if (addBtn.hasClass('disabled')) {
			return false
		}
		addBtn.prop('disabled', true)

		$.ajax({
			url: CART_URL + 'order/cart/add',
			dataType: 'jsonp',
			crossDomain: true,
			jsonp: 'jsoncallback',
			data: {
				goods_id: addBtn.data('gid'),
				peijian_id: peijianId,
				sk_id: sk_id,
				active_type: active_type,
				number: addBtn.data('num') ? addBtn.data('num') : 1
			},
			beforeSend: function () {
				layer.open({
					type: 2,
					shadeClose: false
				})
			}
		})
			.done(function (result) {
				layer.closeAll()
				if (result.code == 0) {
					if (stay == 1) {
						FUN.cart.cartItems()
						var pic = result.data.goods_thumb
						var size = result.data.size
						var color = result.data.color
						var price = result.data.shop_price
						$('#js_pop_add_cart .js_pic').attr('src', pic)
						$('#js_pop_add_cart .js_size').text(size)
						$('#js_pop_add_cart .js_color').text(color)
						$('#js_pop_add_cart .js_price').text(price)
						if (size == '') {
							$('#js_pop_add_cart .js_size').parent().hide()
						}
						if (color == '') {
							$('#js_pop_add_cart .js_color').parent().hide()
						}
						$('#js_pop_add_cart').show()
						setTimeout(function () {
							$('#js_pop_add_cart').hide()
						}, 3000)
						addBtn.prop('disabled', false)
					}
					else {
						location.href = CART_URL + 'order/cart/cart'
					}

					if (callback && typeof callback === 'function') {
						addBtn.prop('disabled', false)
						callback(result)
					}

				} else if (result.code == 2) {  // 秒杀商品过期提示
					$('.js_backdrop').fadeIn('fast', function () {
						$('.js_msg_alert').fadeIn('swing')
					})
				} else {
					addBtn.prop('disabled', false)
					layer.open({
						content: result.msg,
						shadeClose: false,
						btn: ['OK'],
						yes: function () {
							window.location.reload()
						}
					})
				}

			})
	},
	/* 加入商品到收藏夹
	@desc
	@param      {object||string}  obj            点击那个按钮到收藏夹 该按钮需要有属性 gid
	@param      {function}        callback       加入成功后，还需要何操作？
	@author     2015/05           jiangminjing
	*/
	addWishList: function (obj, callback) {
		var addBtn = $(obj)
		var URL = addBtn.data('url') ? addBtn.data('url') : DOMAIN + 'goods/goods/add-to-collect/' + addBtn.data('gid')
		// 判断登录
		$.ajax({
			url: URL,
			dataType: 'jsonp',
			crossDomain: true,
			jsonp: 'jsoncallback'
		})
			.done(function (result) {
				layer.closeAll()
				switch (result.code) {
				// 加入成功
				case 0:

					// 收藏+1
					var favNum = addBtn.find('.fav_num')
					if (favNum.length) {
						favNum.html(parseInt(favNum.data('num')) + 1)
					}
					// 添加收藏状态class
					addBtn.addClass('on')

					// 消息
					layer.open({
						btn: [jsLg.ok],
						content: jsLg.userCenter.addWishList
					})

					if (callback && typeof callback === 'function') {
						callback(result)
					}
					break

					// 重复加入
				case 1:
					addBtn.addClass('on')
					layer.open({
						btn: [jsLg.ok],
						content: jsLg.userCenter.repeatAddWishList
					})
					break

					// 没登录
				case 2:
					location.href = LOGIN_URL + 'user/login/sign?return=back'
					break
				}
			})

	}

},
/****登录****/
FUN.sign = {
	isLogin: function (callback) {
		$.ajax({
			url: WAP_URL + 'misc/check-login',
			dataType: 'jsonp',
			crossDomain: true,
			jsonp: 'jsoncallback',
		})
			.done(function (result) {
				if (callback && typeof (callback) == 'function') {
					callback(result)
				}
			})
	},
	register: function (formObj, callback) {
		formObj.validate({
			rules: {
				email: {
					required: true,
					maxlength: 60,
					email: true,
					remote: {
						url: '/dist/json/emailValidation.json',
						type: 'post',
						data: {
							email: function () {
								return $('input[name=email]').val()
							}
						}
					}
				},
				password: {
					required: true,
					maxlength: 60,
					minlength: 6
				},
				re_password: {
					required: true,
					minlength: 6,
					maxlength: 60,
					equalTo: 'input[name=password]'
				},
				birthday: {
					required: true
				}
			},
			messages: {
				email: {
					required: jsLg.formMsg.email_require_msg,
					maxlength: jsLg.formMsg.email_maxlength_msg,
					email: jsLg.formMsg.email_require_msg,
					remote: jQuery.format(jsLg.formMsg.email_in_user)
				},
				password: {
					required: jsLg.formMsg.password,
					minlength: jsLg.formMsg.password_minlength,
					rangelength: jQuery.format(jsLg.formMsg.password_minlength)
				},
				re_password: {
					required: jsLg.formMsg.password_repeat,
					minlength: jQuery.format(jsLg.formMsg.password_minlength),
					equalTo: jsLg.formMsg.password_repeat
				},
				birthday: {
					required: jsLg.formMsg.email_require_msg
				}
			},
			submitHandler: function (formObj) {
				if (callback && typeof (callback) == 'function') {
					callback(formObj)
				}
			},
			errorPlacement: function (error, element) {
				error.appendTo(element.parent())
			},
			success: function (label) {
				label.remove()
			}
		})
	},
	login: function (formObj, callback) {
		formObj.validate({
			rules: {
				email: {
					required: true,
					maxlength: 60,
					email: true
				},
				password: {
					required: true,
					maxlength: 60,
					minlength: 6
				}
			},
			messages: {
				email: {
					required: jsLg.formMsg.email_require_msg,
					maxlength: jsLg.formMsg.email_maxlength_msg,
					email: jsLg.formMsg.email_require_msg
				},
				password: {
					required: jsLg.formMsg.password,
					minlength: jsLg.formMsg.password_minlength
				}
			},
			submitHandler: function (formObj) {
				if (callback && typeof (callback) == 'function') {
					callback(formObj)
				}
			},
			errorPlacement: function (error, element) {
				error.appendTo(element.parent())
			},
			success: function (label) {
				label.remove()
			}
		})
	}
}

FUN._GET = function (name, str) {
	var pattern = new RegExp('[\?&]' + name + '=([^&]+)', 'g')
	str = str || location.search
	var arr, match = ''

	while ((arr = pattern.exec(str)) !== null) {
		match = arr[1]
	}
	return decodeURIComponent(match)
}

/* 页面跳转
@desc       如果url没有传参数过来，就跳转到jumpUrl (url?return=back或者url?return=http://..)
@param      {string}  jumpUrl       跳转地址
@author     2015/05   jiangminjing
*/
FUN.referrer = function (jumpUrl) {
	var back = FUN._GET('return')
	if (back == 'back') {
		window.location.href = document.referrer
	} else if (back.indexOf('http://') >= 0 || back.indexOf('https://') >= 0) {
		window.location.href = back
	} else {
		window.location.href = jumpUrl
	}
}


/* global code added end */

; (function ($) {
	/**
																						 * by xingtao 2018.4.25
																						 */
	var GS = (function (my) {
		/* 获取域名常量 */
		my.getDiamon = function () {
			var url = window.location.host, temp
			url.replace(/.[A-Za-z0-9_-]*/, function ($1) {
				temp = $1
			})
			return window.location.host.substr(temp.length, url.length)
		}
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


	var COOKIESDIAMON = GS.getDiamon()
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
		}


		// // 搜索
		// ; (function () {
		// 	var top = $('#js_topSeachForm')
		// 	var left = $('#js_leftSeachForm')
		// 	top.length ? GS.seach_submit(top) : ''
		// 	left.length ? GS.seach_submit(left) : ''
		// })()

		// 更新购物车数量
		FUN.cart.cartItems()

		// 搜索
		; (function () {
			var top = $('#js_topSeachForm')
			var left = $('#js_leftSeachForm')
			top.length ? FUN.Search.seach_submit(top) : ''
			left.length ? FUN.Search.seach_submit(left) : ''
		})()

		//检测登录
		FUN.sign.isLogin(function (result) {
			if (result.data.logined) {
				$('.isLogin').show()
				if (result.data.giftCard == 1) {
					$('#js_giftCard').show()
				}
			} else {
				$('.isNoLogin').show()
			}
		})

	})






}($));

/* code added */
/*搜索框操作*/
(function () {
	var searchBox = $('.js_searchBox'),
		position = searchBox.data('position'),
		ipt = searchBox.find('input[name=\'k2\']'),
		clearIpt = searchBox.find('.js_clearIpt')

	//显示隐藏搜索按钮
	ipt.on('keyup', function () {
		var $this = $(this)
		$this.val() ? $this.closest('.js_searchBox').addClass('focus') : $this.closest('.js_searchBox').removeClass('focus')
	})

	//清空搜索框
	clearIpt.on('tap', function () {
		var $this = $(this)
		$this.prev('input[name=\'k2\']').val('').end().closest('.js_searchBox').removeClass('focus')
	})

	//显示隐藏页面头部搜索框
	$('#js_showSearchBox').on('tap', function () {
		$('div[data-position=\'top\']').slideToggle('fast').addClass('on')
	})

	//滚动页面时隐藏搜索下拉
	$(window).on('scroll', function () {
		$('#js_topSearchDrop').hide(0)
	})
})()



//调用ajax搜索
FUN.Search.searchAjax.init()

//底部邮箱输入框
$('#subscribe_form').submit(function () {
	var that = $(this),
		emailDom = that.find('input[type=text]'),
		emailRe = /\w+([-+.]\w+)*@\w+([-.]\w+)*\.\w+([-.]\w+)*/
	if (!emailRe.test(emailDom.val())) {
		layer.open({
			content: jsLg.formMsg.email_require_msg,
			btn: [jsLg.yes],
			yes: function () {
				layer.closeAll()
			},
			end: function () {
				emailDom.focus()
			}
		})
		return false
	}
	else {
		$.ajax({
			type: 'POST',
			dataType: 'json',
			data: {
				email_check: 1,
				user_email: emailDom.val()
			},
			url: DOMAIN + 'misc/check-email-subscribe/',
			beforeSend: function () {
				layer.open({ type: 2, shadeClose: false })
			},
			success: function (data) {
				layer.closeAll()
				if (data.status == '0') {
					layer.open({
						content: data.msg,
						btn: [jsLg.yes],
						yes: function () {
							layer.closeAll()
						},
						end: function () {
							emailDom.focus()
						}
					})
				}
				else {
					var email = emailDom.val()
					var encodestr = String.fromCharCode(email.charCodeAt(0) + email.length)
					for (var i = 1; i < email.length; i++) {
						encodestr += String.fromCharCode(email.charCodeAt(i) + email.charCodeAt(i - 1))
					}
					encodestr = encodeURIComponent(encodestr)
					location.href = DOMAIN + 'misc/email-subscribe/?email=' + encodestr
				}
			}
		})
	}
})
