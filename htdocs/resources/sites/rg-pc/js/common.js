window.mobileAndTabletcheck = function () {
	var check = false;

	(function (a) { if (/(android|bb\d+|meego).+mobile|avantgo|bada\/|blackberry|blazer|compal|elaine|fennec|hiptop|iemobile|ip(hone|od)|iris|kindle|lge |maemo|midp|mmp|mobile.+firefox|netfront|opera m(ob|in)i|palm( os)?|phone|p(ixi|re)\/|plucker|pocket|psp|series(4|6)0|symbian|treo|up\.(browser|link)|vodafone|wap|windows ce|xda|xiino|android|ipad|playbook|silk/i.test(a) || /1207|6310|6590|3gso|4thp|50[1-6]i|770s|802s|a wa|abac|ac(er|oo|s\-)|ai(ko|rn)|al(av|ca|co)|amoi|an(ex|ny|yw)|aptu|ar(ch|go)|as(te|us)|attw|au(di|\-m|r |s )|avan|be(ck|ll|nq)|bi(lb|rd)|bl(ac|az)|br(e|v)w|bumb|bw\-(n|u)|c55\/|capi|ccwa|cdm\-|cell|chtm|cldc|cmd\-|co(mp|nd)|craw|da(it|ll|ng)|dbte|dc\-s|devi|dica|dmob|do(c|p)o|ds(12|\-d)|el(49|ai)|em(l2|ul)|er(ic|k0)|esl8|ez([4-7]0|os|wa|ze)|fetc|fly(\-|_)|g1 u|g560|gene|gf\-5|g\-mo|go(\.w|od)|gr(ad|un)|haie|hcit|hd\-(m|p|t)|hei\-|hi(pt|ta)|hp( i|ip)|hs\-c|ht(c(\-| |_|a|g|p|s|t)|tp)|hu(aw|tc)|i\-(20|go|ma)|i230|iac( |\-|\/)|ibro|idea|ig01|ikom|im1k|inno|ipaq|iris|ja(t|v)a|jbro|jemu|jigs|kddi|keji|kgt( |\/)|klon|kpt |kwc\-|kyo(c|k)|le(no|xi)|lg( g|\/(k|l|u)|50|54|\-[a-w])|libw|lynx|m1\-w|m3ga|m50\/|ma(te|ui|xo)|mc(01|21|ca)|m\-cr|me(rc|ri)|mi(o8|oa|ts)|mmef|mo(01|02|bi|de|do|t(\-| |o|v)|zz)|mt(50|p1|v )|mwbp|mywa|n10[0-2]|n20[2-3]|n30(0|2)|n50(0|2|5)|n7(0(0|1)|10)|ne((c|m)\-|on|tf|wf|wg|wt)|nok(6|i)|nzph|o2im|op(ti|wv)|oran|owg1|p800|pan(a|d|t)|pdxg|pg(13|\-([1-8]|c))|phil|pire|pl(ay|uc)|pn\-2|po(ck|rt|se)|prox|psio|pt\-g|qa\-a|qc(07|12|21|32|60|\-[2-7]|i\-)|qtek|r380|r600|raks|rim9|ro(ve|zo)|s55\/|sa(ge|ma|mm|ms|ny|va)|sc(01|h\-|oo|p\-)|sdk\/|se(c(\-|0|1)|47|mc|nd|ri)|sgh\-|shar|sie(\-|m)|sk\-0|sl(45|id)|sm(al|ar|b3|it|t5)|so(ft|ny)|sp(01|h\-|v\-|v )|sy(01|mb)|t2(18|50)|t6(00|10|18)|ta(gt|lk)|tcl\-|tdg\-|tel(i|m)|tim\-|t\-mo|to(pl|sh)|ts(70|m\-|m3|m5)|tx\-9|up(\.b|g1|si)|utst|v400|v750|veri|vi(rg|te)|vk(40|5[0-3]|\-v)|vm40|voda|vulc|vx(52|53|60|61|70|80|81|83|85|98)|w3c(\-| )|webc|whit|wi(g |nc|nw)|wmlb|wonu|x700|yas\-|your|zeto|zte\-/i.test(a.substr(0, 4))) check = true })(navigator.userAgent || navigator.vendor || window.opera)

	return check
}

if (mobileAndTabletcheck() &&
	window.location.href.search(/https:\/\/www\.rosegal\.com/) != -1) {
	window.location.href = window.location.href.replace(/https:\/\/www\.rosegal\.com/, 'https://m.rosegal.com')
}

if (mobileAndTabletcheck() &&
	window.location.href.search(/https:\/\/fr\.rosegal\.com/) != -1) {
	window.location.href = window.location.href.replace(/https:\/\/fr\.rosegal\.com/, 'https://fr-m.rosegal.com')
}

window.GLOBAL = {}
/**
 * [LANG 多语言变量]
 */
GLOBAL.LANG = JS_LANG != '' ? JS_LANG.toString().substr(0, 2) : ''
var currency_lang = JS_LANG != '' ? JS_LANG.toString().substr(0, 2) : 'en'
var change_language = currency_lang == $.cookie('cookie_lang') ? 0 : 1//记录语言切换  0->没切换  1->切换
$.cookie('cookie_lang', currency_lang, {
	expires: 30,
	path: '/',
	domain: COOKIESDIAMON
})

	; (function () {
		var oTomobile = document.getElementById('tomobile')
		if (!oTomobile) return
		var sTomobile = eval(oTomobile.value)
		if (device.mobile() && sTomobile[0]) {
			location.href = sTomobile[1]
		}
	})()

/*
 *订阅邮件
 * @param {Object} obj 需要处理数据的输入框
 */
GLOBAL.subscribe = function (Obj) {
	var $obj = $(Obj),
		email = $.trim($obj.val()),
		new_arrivals = $obj.closest('form').find('input[name=\'new_arrivals\']:checked'),
		subscribe_btn = $('#js_subscribe'),
		hot_sellers = $obj.closest('form').find('input[name=\'hot_sellers\']:checked')
	//合法邮箱则提交数据到后台
	if (GLOBAL.checkmail(email)) {
		subscribe_btn.attr('disabled', true)
		$.ajax({
			type: 'POST',
			url: '/m-cemails.htm',
			data: {
				act: 'add',
				action: 2,
				txtEMail: email,
				new_arrivals: new_arrivals.val(),
				hot_sellers: hot_sellers.val()
			},
			dataType: 'json',
			success: function (res) {
				function alertMsg (msg) {
					GLOBAL.PopObj.alert({
						dialog: {
							msg: msg,
							btn: [jsLg.ok],
							type: 1,
							yes: function (index) {
								layer.close(index)
								$('#txtEMail').val('')
							}
						}
					})
				}

				if (res.status == 1) {
					alertMsg(jsLg.subscribe_ok)
				}
				if (res.status == 2) {
					alertMsg(jsLg.subscribe_repeat)
				}
				subscribe_btn.removeAttr('disabled')
			},
			error: function (err) {
				subscribe_btn.removeAttr('disabled')
			}
		})
	}
	//不合法则显示提示信息
	else {
		$('#js_subscribe_tip').show().text(jsLg.email_require).delay(3000).fadeOut()
		$obj.focus()
	}
	return false
}
/**
 * 获取URL参数(类似PHP的$_GET)
 *
 * @param {string} name 参数
 * @param {string} str  待获取字符串
 *
 * @return {string} 参数值
 */
function _GET (name, str) {
	var pattern = new RegExp('[\?&]' + name + '=([^&]+)', 'g')
	str = str || location.search
	var arr, match = ''

	while ((arr = pattern.exec(str)) !== null) {
		match = arr[1]
	}

	return match
};

//删除数组方法
Array.prototype.indexOf = function (val) {
	for (var i = 0; i < this.length; i++) {
		if (this[i] == val) return i
	}
	return -1
}
Array.prototype.remove = function (val) {
	if (val instanceof Array === true) {
		var index = this.indexOf(val)
		if (index > -1) {
			this.splice(index, 1)
		}
	}
};

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
				alert(jsLg.deBugMsg.noCallBack)
			}
		})
		return $(this)
	}
})(jQuery)
/*
 *验证邮箱
 *@param {String} Email 需要验证的字符串
 *@return {Boolean} 验证通过返回true，否则返回false
 */
GLOBAL.checkmail = function (Email) {
	var pattern = /^[\w][\.\w_-]+[\w]@([a-zA-Z0-9_-])+(\.[a-zA-Z0-9_-])+/
	var flag = pattern.test(Email)
	return flag ? true : false
}


/*
 *自定义layer弹窗属性
 */
GLOBAL.PopObj = {
	openPop: function (options) {
		var defaultOpts = {
			shade: [0.5, '#000', true],
			type: 1,
			area: ['auto', 'auto'],
			offset: ['', ''],
			title: false,
			move: false,
			border: [8, 0.8, '#666', true],
			page: {
				dom: '#popElem'
			},
			close: function (index) {
				layer.close(index)
			}
		}
		defaultOpts = $.extend(true, defaultOpts, options)
		return $.layer(defaultOpts)
	},
	iframe: function (options) {
		var defaultOpts = {
			shade: [0.5, '#000', true],
			type: 2,
			title: false,
			shadeClose: true,
			bgcolor: '#fff',
			closeBtn: [1, true],
			area: ['auto', 'auto'],
			offset: ['', ''],
			border: [8, 0.8, '#666', true],
			iframe: {
				src: ''
			},
			close: function (index) {
				layer.close(index)
			}
		}
		defaultOpts = $.extend(true, defaultOpts, options)
		return $.layer(defaultOpts)
	},
	confirm: function (options) {
		var defaultOpts = {
			shade: [0.5, '#000', true],
			area: ['auto', 'auto'],
			title: jsLg.popTitle,
			border: [1, 1, '#ddd', true],
			dialog: {
				msg: options.msg ? options.msg : '',
				btns: 2,
				type: 4,
				btn: [jsLg.yes, jsLg.no],
				yes: function () { },
				no: function (index) {
					layer.close(index)
				}
			}
		}

		defaultOpts = $.extend(true, defaultOpts, options)
		return $.layer(defaultOpts)
	},
	alert: function (options) {
		var defaultOpts = {
			shade: [0.5, '#000', true],
			area: ['auto', 'auto'],
			title: jsLg.popTitle,
			border: [1, 1, '#ddd', true],
			dialog: {
				msg: options.msg ? options.msg : '',
				btns: 1,
				type: 1,
				btn: [jsLg.ok],
				yes: function (index) {
					layer.close(index)
				},
				close: function (index) {
					layer.close(index)
				}

			}
		}
		defaultOpts = $.extend(true, defaultOpts, options)
		return $.layer(defaultOpts)
	},
	closePop: function (index) {
		var id = ''
		id = arguments.length > 0 ? index : ''
		layer.close(index)
	}
}

//顶部搜索
GLOBAL.Search = function (sKeyword, sCatgory) {

	var reg = /[^\w\.]/g,
		$keyword = sKeyword,
		$category = sCatgory,
		kw, cat_id
	kw = $.trim($keyword.val())
	kw = kw.replace(/\*/g, '~~')
	kw = kw.replace(/\|/g, ']]')
	kw = kw.replace(/\=/g, '((')
	kw = kw.replace(/\"/g, ' ')
	kw = kw.replace(/\</g, '))')
	kw = kw.replace(/\>/g, ')))')
	kw = kw.replace(/\?/g, '!!!')
	kw = kw.replace(/\+/g, '__')
	kw = kw.replace(/\-/g, ' ')
	kw = kw.replace(/\//g, '..')
	kw = kw.replace(/\\/g, '...')
	kw = kw.replace(/\%/g, '!!')
	kw = kw.replace(/\#/g, '~~~')
	kw = kw.replace(/\>/g, '___')
	kw = kw.replace(/\</g, '^^^')
	kw = kw.replace(/\"/g, '[[')
	kw = kw.replace(/\$/g, '[[[')
	kw = kw.replace(/\s+/g, '-')
	kw = kw.toLowerCase()
	var find_str = 'À,Á,Â,Ã,Ä,Å,Ç,È,É,Ê,Ë,Ì,Í,Î,Ï,Ð,Ñ,Ò,Ó,Ô,Õ,Ö,Ø,Ù,Ú,Û,Ü,Ý,Þ,ß,à,á,â,ã,ä,å,ç,è,é,ê,ë,ì,í,î,ï,ð,ñ,ò,ó,ô,õ,ö,ø,ù,ú,û,ü,þ,ÿ,ā,ē,ě,ī,ń,ň,ō,Š,š,ū,Ÿ,ƒ,ǎ,ǐ,ǒ,ǔ,ǖ,ǘ,ǚ,ǜ,ǹ,ɑ'
	var replace_str = 'a,a,a,a,a,a,c,e,e,e,e,i,i,i,i,d,n,o,o,o,o,o,o,u,u,u,u,y,p,b,a,a,a,a,a,a,c,e,e,e,e,i,i,i,i,n,n,o,o,o,o,o,o,u,u,u,u,p,y,a,e,e,i,n,n,o,s,s,u,y,f,a,i,o,u,u,u,u,u,n,a'
	var find_arr = find_str.split(',')
	var replace_arr = replace_str.split(',')
	for (i = 0; i < find_arr.length; i++) {
		var reg = new RegExp(find_arr[i], 'g')
		kw = kw.replace(reg, replace_arr[i])
	}
	kw = kw.replace(/\b\w+\b/g, function (word) {
		return word
	})
	if (kw.length < 1) {
		$keyword.focus()
	} else {
		kw = kw.replace('%20', '-')
		cat_id = $category.val()
		var after_str = ''
		switch (currency_lang) {
			case 'ru':
				after_str = '/kupit/'
				break
			case 'fr':
				after_str = '/magasin/'
				break
			default:
				after_str = '/shop/'
		}
		window.location.href = DOMAIN + kw + after_str
	}
	return false
}
/*****登陆相关*****/
GLOBAL.login = {
	//检查登陆
	info_check: function (callBack) {
		var lkid = GLOBAL._GET('lkid') ? GLOBAL._GET('lkid') : '', //获取URL地址中是否有lkid
			referrer_url = document.referrer ? encodeURIComponent(document.referrer) : '', //来源URL地址
			url = '&lkid=' + lkid + '&referrer_url=' + referrer_url
		$.ajax({
			type: 'GET',
			url: '/fun/?act=info_check&action=1' + url,
			dataType: 'json',
			data: {
				location_url: location.href
			},
			cache: false,
			success: function (res) {
				USER_ID = res.user_id
				//登录后改变用户图标状态
				if (res.status == 1) {
					//处理用户昵称
					var nickName = ''
					res.data.firstname ? nickName = res.data.firstname : nickName = res.data.shortname
					if (nickName.length > 5) {
						nickName = nickName.substr(0, 5) + '...'
					}

					$('#js_nickname').html(jsLg.hi + ', ' + nickName.link(DOMAIN_USER + JS_LANG + 'm-users-a-order_list.htm'))
					$('#js_isLogin').show()
					$('#js_notLogin').hide()
					$('#coupons_num').html(res.data.coupon_num)
					$('#points_num').html(res.data.points)

					if (res.is_check_in != 1) {
						$('.sign_in_points').show()
					}
				} else {
					$('#js_nickname').html(jsLg.not_sign_in.link(HTTPS_LOGIN_DOMAIN + JS_LANG + 'm-users-a-sign.htm'))
					$('#js_isLogin').hide()
					$('#js_notLogin').show()
				}

				//顶部banner倒计时
				if (res.topad_time.isset_time - 0 > 0) {
					topAdTime(res.topad_time.left_time)
				}
				//如果有回调函数
				if (callBack && typeof (callBack) == 'function') {
					callBack(res)
				}

			}
		})
	}
}
/*****货币处理（货币单位：USD，货币图标：$）*****/
GLOBAL.currency = {
	/*
		 *货币转换
		 *@param {String} bz 需要处理的货币单位
		 *@param {Objext} $wrapElm	需要单独处理某个父级元素下的货币，默认值为$("body")
		 */
	change_houbi: function (bz, $wrapElm) {
		var bizhong = $.cookie('bizhong')
		var icon
		var cookie_lang = $.cookie('cookie_lang')
		var bizhong = bizhong ? bizhong : 'USD'
		var bizhong = arguments[0] ? arguments[0] : bizhong
		if (change_language == 1 && cookie_lang == 'fr') {
			bizhong = 'EUR'
			change_language = 0
		} else if (change_language == 1 && cookie_lang == 'en') {
			bizhong = 'USD'
			change_language = 0
		} else if (change_language == 1 && cookie_lang == 'ru') {
			bizhong = 'RUB'
			change_language = 0
		}
		var hrefBizhong = GLOBAL._GET('currency', window.location.href)
		var bizhong = hrefBizhong ? hrefBizhong : bizhong
		$.cookie('bizhong', bizhong, {
			expires: 7,
			path: '/',
			domain: COOKIESDIAMON
		})
		//改变页面货币按钮显示
		$('#js_currencySign').html(this.getIcon(bizhong))
		$('#js_currencyBtn').html(bizhong)

		//根据当前cookie的货币类型，为货币按钮添加高亮样式
		$.each($('#js_currencyList').find('span'), function (index, val) {
			var $this = $(val)
			if ($this.data('currency-word') == bizhong) {
				$this.addClass('current').siblings('span').removeClass('current')
				return false
			}
		})
		//如果传入 $wrapElm 参数
		arguments.length > 1 ? this.change_html(bizhong, $wrapElm) : this.change_html(bizhong)

		//列表页侧边栏加个属性筛选不要币种符号，此做特殊处理
		if ($('.js_hideBizhong').length) {
			this.hideBizhong($('.js_hideBizhong'), bizhong)
		}
		if ($('.js_getBizhongIcon').length) {
			$('.js_getBizhongIcon').html(this.getIcon(bizhong))
		}
	},
	/*
		 *获取货币符号
		 *@param {String} bizhong 根据传来的货币单位获取对应的货币符号
		 *@return {String} 返回对应的货币符号
		 */
	getIcon: function (bizhong) {
		var icon
		// var my_array_sign = new Array();
		//     my_array_sign['USD'] = '$';
		//     my_array_sign['EUR'] = '€';
		//     my_array_sign['GBP'] = '£';
		//     my_array_sign['AUD'] = '$';
		//     my_array_sign['CAD'] = '$';
		//     my_array_sign['CHF'] = '₣';
		//     my_array_sign['HKD'] = '$';
		//     my_array_sign['JPY'] = '¥';
		//     my_array_sign['RUB'] = 'р.';
		//     my_array_sign['BRL'] = 'R$';
		//     my_array_sign['CLP'] = '$';
		//     my_array_sign['NOK'] = 'kr.';
		//     my_array_sign['DKK'] = 'kr.';
		//     my_array_sign['SEK'] = 'Kr.';
		//     my_array_sign['KRW'] = '₩';
		//     my_array_sign['ILS'] = '₪';
		//     my_array_sign['CNY'] = '¥';
		//     my_array_sign['NZD'] = '$';

		icon = myArraySign[bizhong] ? myArraySign[bizhong] : '$'
		return icon
	},
	/*
		 *改变页面需要处理货币的元素
		 *@param {String} bz 需要处理的货币单位
		 *@param {Objext} $wrapElm	需要单独处理某个父级元素下的货币，默认值为$("body")
		 */
	change_html: function (bz, $wrapElm) {
		var $wrap = arguments.length > 1 ? $($wrapElm) : $('body'),
			$label = $wrap.find('.bizhong'),
			//$icon = $wrap.find(".bz_icon"),
			$price = $wrap.find('.my_shop_price'),
			bizhong = bz,
			cookie_bizhong = $.cookie('bizhong')
		that = this
		var orgp, price, icon
		//如果没有传递币种，则读取cookie中的，如果cookie中没有币种，则默认为USD并写入cookie中
		if (!bizhong) {
			if (!cookie_bizhong) {
				$.cookie('bizhong', 'USD', {
					expires: 7,
					path: '/',
					domain: COOKIESDIAMON
				})
			}
			bizhong = $.cookie('bizhong')
		}
		$label.html(bizhong)
		icon = this.getIcon(bizhong)
		$price.each(function (i, o) {
			orgp = $(this).attr('data-orgp') || $(this).attr('orgp')
			that.processer($(o), orgp, bizhong, icon)
		})
	},
	/**
		 * 判断货币图标是否位于价格右边
		 * @param  {String}  bizhong 货币种类
		 * @param  {String}  price   价格
		 * @param  {String}  icon    该货币种类图标
		 * @return {String}          返回该货币种类的完整价格（带图标）
		 */
	setIconPosition: function (bizhong, price, icon) {
		var isRight = bizhong && bizhong != '' && myArrayPosition[bizhong] == 2
		return isRight ? price + '&nbsp;' + icon : icon + price
	},
	/**
		 *根据汇率计算价格
		 *@param {Object} $object 需要处理的对象
		 *@param {String} orgp 默认价格属性容器
		 *@param {String} bizhong 需要转换汇率的货币单位
		 *@param {String} icon 货币单位对应的货币图标
		 */
	processer: function ($object, orgp, bizhong, icon) {
		var huilv = parseFloat(my_array[bizhong]),
			completePrice = ''

		if (bizhong == 'JPY') {
			var price = (huilv * orgp).toFixed(0)
			var jpy = price.replace(/(\d)(?=(\d{3})+(?!\d))/g, '$1,')
			completePrice = this.setIconPosition(bizhong, jpy, icon)

		} else if (bizhong == 'RUB') {

			/*切换卢布时，产品价格取整数. 切换至卢布之后再四舍五入*/
			completePrice = this.setIconPosition(bizhong, (huilv * orgp).toFixed(0), icon)

		} else if (bizhong == 'BRL') {

			/*切换巴西货币的时候,小数点后第二位数字必须为0.倒数第一位数字根据倒数第二位原数字来四舍五入.例如BRL5.87要显示R$ 5.90*/
			completePrice = this.setIconPosition(bizhong, (huilv * orgp).toFixed(1) + '0', icon)

		} else if (bizhong == 'CLP') {

			/*智利比索按照汇率. 取到小数点后第三位,中间不用空格*/
			completePrice = this.setIconPosition(bizhong, (huilv * orgp).toFixed(3), icon)

		} else if (bizhong == 'DKK') {
			/*丹麦克朗上千后用.隔开.低于千用英文逗号隔开*/
			var dkkPrice = (huilv * orgp).toFixed(2)
			if (dkkPrice > 1000) {
				dkkPrice = dkkPrice.replace('.', ',')
				dkkPrice = dkkPrice.replace(/(\d)(?=(\d{3})+(?!\d))/g, '$1.')

			} else {
				dkkPrice = dkkPrice.replace('.', ',')
			}
			completePrice = this.setIconPosition(bizhong, dkkPrice, icon)

		} else if (bizhong == 'NOK' || bizhong == 'SEK') {
			/*挪威克朗/瑞典克朗取整,直接把小数点后面的去掉,无需四舍五入*/
			completePrice = this.setIconPosition(bizhong, parseInt(huilv * orgp), icon)

		} else if (bizhong == 'ILS') {
			/*以色列谢克尔,取整,直接把小数点后面的去掉,无需四舍五入*/
			completePrice = this.setIconPosition(bizhong, parseInt(parseInt((huilv * orgp).toFixed(2))), icon)

		} else if (bizhong == 'EUR') {
			var eurPrice = (huilv * orgp).toFixed(2)
			eurPrice = eurPrice.replace('.', ',')

			completePrice = this.setIconPosition(bizhong, eurPrice, icon)

		} else {
			completePrice = this.setIconPosition(bizhong, (parseFloat(my_array[bizhong]) * parseFloat(orgp)).toFixed(2), icon)

			// if(bizhong == "HKD" || bizhong == "CHF" || bizhong == "NZD"){

			//     $object.html(currency_img + ((parseFloat(my_array[bizhong]) * parseFloat(orgp)).toFixed(2)));
			// }else{
			//     $object.html(currency_img + ((parseFloat(my_array[bizhong]) * parseFloat(orgp)).toFixed(2)));
			// }

		}
		$object.html(completePrice)

	},
	hideBizhong: function ($wrapElm, bizhong) {

		var _bizhong = this.getIcon(bizhong)

		$wrapElm.find('.my_shop_price').each(function () {
			var _html = $(this).html().replace(_bizhong, '')
			$(this).html(_html)
		})
	}
}
/*
 *更新头部购物车
 *@param {Function} callBack 成功后的回调函数
 */
GLOBAL.MiniCart = function (callBack) {
	$.ajax({
		type: 'GET',
		url: '/fun/?act=cart_list',
		dataType: 'json',
		cache: false,
		success: function (res) {
			// GLOBAL.updateCartNum(res.total.real_goods_count)
			//渲染顶部购物车模板
			var miniCart_tpl = $('#miniCartTpl').html(); console.log(res)
			//异步加载
			laytpl.config({ open: '<~', close: '~>' })
			laytpl(miniCart_tpl).render(res, function (tpl_result) {
				$('#js_miniCart').html(tpl_result)
			})
			GLOBAL.currency.change_houbi()
			//如果有回调函数
			if (callBack && typeof (callBack) == 'function') {
				callBack(res)
			}

		}
	})
}

//更新购物车数量
GLOBAL.updateCartNum = function (num) {
	$('#js-topCartNum').text(num)
}

/*
 *更新头部收藏夹
 *@param {Function} callBack 成功后的回调函数
 */
GLOBAL.MiniFav = function (callBack) {
	laytpl.config({ open: '<~', close: '~>' })
	$.ajax({
		type: 'GET',
		url: '/fun/?act=collect_list',
		dataType: 'json',
		cache: false,
		success: function (res) {
			//渲染顶部收藏夹模板
			if (res.status != 0 && res.items != 0) {
				var miniFav_tpl = $('#miniFavTpl').html()
				laytpl(miniFav_tpl).render(res, function (tpl_result) {
					$('#js_miniFav').attr('data-drop', 'child').html(tpl_result)
				})

				$('#js_miniFav').css('border', '1px solid #ddd')
			} else {
				$('#js_miniFav').removeAttr('data-drop')
			}
			//如果有回调函数
			if (callBack && typeof (callBack) == 'function') {
				callBack(res)
			}
		}
	})
}
/*
	 *加入收藏夹
	 *@param {Number} goods_id 商品ID
	 *@param {Function} callBack 回调函数
	 */
GLOBAL.addFavorite = function (goods_id, callBack) {
	$.ajax({
		type: 'GET',
		url: '/m-flow-a-drop_to_collect-id-' + goods_id + '.htm',
		dataType: 'json',
		beforeSend: function () {
			layer.load(jsLg.loading + '...')
		},
		success: function (res) {
			layer.closeAll()
			//如果已登录
			if (res.status != 0) {
				if (callBack && typeof (callBack) == 'function') {
					callBack(res)
				}
			}
			//如果未登录
			else if (res.status == 0) {
				window.location.href = HTTPS_LOGIN_DOMAIN + JS_LANG + 'sign-in.html?ref=' + window.location.href
			}
			//其他异常
			else {
				GLOBAL.PopObj.alert({
					dialog: {
						msg: res.msg,
						btn: [jsLg.ok],
						type: 0,
						yes: function (index) {
							layer.close(index)
						}
					}
				})
			}
		}
	})
}

/*
 *商品加入购物车
 *@param {Number} goods_id 商品ID
 *@param {Function} callBack 回调函数
 */
GLOBAL.addToCart = function (obj, is_custom, callBack) {
	var $this = $(obj),
		goodsId = $this.data('goods-id'),
		cartText = $this.find('span').text(),
		goodsNum = $this.data('goods-num'),
		coupon = $this.data('coupon-code') || '',
		peijian = $this.data('goods-peijian') || ''
	var crossDomain = coupon ? DOMAIN_USER : '/'//get it free 加入购物车 跨域支持
	$.ajax({
		url: crossDomain + 'm-flow-a-add_to_cart.htm',
		type: 'POST',
		data: {
			goods_id: goodsId,
			number: goodsNum,
			peijian_id: peijian
		},
		dataType: 'json',
		beforeSend: function () {
			$this.prop('disabled', true).find('span').text(jsLg.loading).addClass('loadBar')
		},
		success: function (res) {
			//成功
			if (res.status == 1) {
				window.location.href = DOMAIN_CART + JS_LANG + 'm-flow-a-cart.htm?coupon=' + coupon
			}
			//失败或其他异常
			else {
				GLOBAL.PopObj.alert({
					dialog: {
						msg: res.msg,
						btn: [jsLg.ok],
						type: 0,
						yes: function (index) {
							layer.close(index)
							$this.prop('disabled', false).find('span').text(cartText).removeClass('loadBar')
						}
					}
				})
				$this.prop('disabled', false).find('span').text(jsLg.loading).addClass('loadBar')
			}
			if (callBack && typeof (callBack) == 'function') {
				$this.prop('disabled', false)
				callBack(res)
			}
		}
	})
}


/*
 *商品点赞
 *@param {Number} goods_id 商品ID
 *@param {Function} callBack 回调函数
 */
GLOBAL.Like = function (goods_id, callBack) {
	$.ajax({
		type: 'GET',
		url: DOMAIN + 'index.php?m=goods&a=goods_attention',
		data: {
			goods_id: goods_id
		},
		dataType: 'json',
		success: function (res) {
			//回调函数
			if (callBack && typeof (callBack) == 'function') {
				callBack(res)
			}
		}
	})
}

//返回顶部
GLOBAL.backToTop = function () {
	$('body').append('<div id="backToTop">' + jsLg.top + '</div>')
	//添加到页面
	//
	//添加到页面添加到页面
	var obj = $('#backToTop')
	//控制蝴蝶显示
	var lastScrollPosition = 0

	$(window).scroll(function () {
		var newScrollPosition = window.scrollY
		if (newScrollPosition < lastScrollPosition) {
			if (newScrollPosition <= 100) {
				obj.hide()
				return
			}
		} else {
			//downward - code here
		}
		if ($(obj).css('display') == 'none') {
			obj.fadeIn()
		}
		lastScrollPosition = newScrollPosition

	})



	//点击执行蝴蝶动画并返回顶部
	$(obj).click(function () {

		$('html,body').animate({
			scrollTop: 0
		}, 'slow', function () {

		})
	})
}

/*
 *公用下拉菜单
 *使用方法：给需要下拉的父级元素添加属性 data-drop='parent' 、子元素添加属性 data-drop='child' 即可
 */
GLOBAL.dropDown = function () {
	var timer = null

	function topNavImgLazy (obj) {
		if ($.fn.lazyload) {
			obj.find('[data-drop=\'child\']').find('img.js_topNavLazyImg').lazyload({})
		} else {
			window.GS_GOODS_LAZY_FN('.js_topNavLazyImg');
		}

	}

	$('[data-drop=\'parent\']').mousemove(function (e) {

		var $this = $(this),
			delayTime = $this.data('delay')
		if (delayTime) {
			clearTimeout(timer)
		}
		$this.find('[data-drop=\'child\']').addClass('active').show()
		if ($this.find('[data-drop=\'child\']').hasClass('js_sub_nav')) {
			var win_w = $(window).width()
			if (win_w > 1200) {
				var margin_h = (win_w - 1200) / 2
				$this.find('[data-drop=\'child\']').css({ 'left': -margin_h + 'px' })
				$this.find('[data-drop=\'child\']').css({ 'right': -margin_h + 'px' })
			}
		}

		if ($this.hasClass('category-item')) {
			$('#topNavWrap .category-item').not($this).addClass('others')
		}
		topNavImgLazy($this)
	})

	$('[data-drop=\'parent\']').mouseleave(function (e) {
		var $this = $(this),
			delayTime = $this.data('delay')
		if (delayTime) {
			;
			timer = setTimeout(function () {
				$this.find('[data-drop=\'child\']').removeClass('active').hide()
			}, delayTime)
			return false
		} else {
			$this.find('[data-drop=\'child\']').removeClass('active').hide()
		}
		if ($this.hasClass('category-item')) {
			$('#topNavWrap .category-item').removeClass('others')
		}
	})
}

/*
 *美化checkbox
 *@param {String} ele 需要操作的元素 ID或者Class （需要CSS支持，详见checkbox.less）
 */
GLOBAL.myCheckBox = function (ele) {
	if (ele) {
		$(ele).each(function (i, v) {
			var checkbox = $(v).find('input[type=\'checkbox\']'),
				icon = $(v).find('i.checkbox_icon')
			if (checkbox.prop('checked') == true) {
				icon.addClass('checked')
			} else {
				icon.removeClass('checked')
			}
		})
		$(ele).on('click', function () {
			var $this = $(this),
				checkbox = $this.find('input[type=\'checkbox\']'),
				icon = $this.find('i.checkbox_icon')

			if (checkbox.prop('checked') == true) {
				icon.addClass('checked')
			} else {
				icon.removeClass('checked')
			}
		})

	} else {
		alert(jsLg.deBugMsg.argumentsIllegal)
	}
}

/*
 *通用提示
 *@param {String} ele 需要操作的元素 ID或者Class
 *@param {String} text 提示的文字，支持HTML标签
 *
 * 创建 yukai
 * 修改 2015-12-10 luoxh
 */
GLOBAL.helpTips = function (ele, text, options) {
	//创建模板
	var sText = text ? text : '<em>' + jsLg.deBugMsg.notSetText + '</em>',
		Timer = null,
		Tpl = '',
		options = options || {}

	Tpl += '<div class="help_tips" id="js_helpTips">'
	Tpl += '<div class="tips_content">' + sText + '</div>'
	Tpl += '<i class="triangle bd_tri"></i> <i class="triangle bg_tri"></i>'
	Tpl += '</div>'
	//添加事件
	$(ele).mouseenter(function () {
		var $this = $(this),
			Tip = $('#js_helpTips'),
			posX, posY

		Tip.length && Tip.remove()
		$('body').append(Tpl)
		Tip = $('#js_helpTips')

		// 支持不同的元素显示不同的提示信息，写在data-tips属性上
		!text && Tip.children('.tips_content').html($this.data('tips'))

		if (options.addClass) {
			Tip.addClass(options.addClass)
		}

		if (options.zIndex) {
			Tip.css('z-index', options.zIndex)
		}

		switch (options.position) {
			case 'top':
				posY = $this.offset().top - Tip.height() - $this.height()
				posX = $this.offset().left - Tip.width() / 2 + $this.width() / 2
				Tip.addClass('top')
				break

			//自定义
			case 'zdy':
				posY = $this.offset().top + $this.height() + 15 - options.zdyY
				posX = $this.offset().left - Tip.width() / 2 + $this.width() / 2 - options.zdyX
				Tip.addClass('zdy')
				break

			default:
				posY = $this.offset().top + $this.height() + 15
				posX = $this.offset().left - Tip.width() / 2 + $this.width() / 2
				break
		}

		//显示提示框
		Tip.css({
			'left': posX,
			'top': posY
		}).show()

	})
	//移除提示框
	$(ele).mouseleave(function () {
		Timer = window.setTimeout(function () {
			$('#js_helpTips').remove()
			window.clearTimeout(Timer)
		}, 100)
	})

	$('body').on('mousemove', '#js_helpTips', function () {
		window.clearTimeout(Timer)
		$(this).show()
	})
	$('body').on('click', '#js_helpTips', function (e) {
		e.stopPropagation()
	})
	$('body').on('click', function () {
		$('#js_helpTips').remove()
		window.clearTimeout(Timer)
	})

}

/*
 *根据hash切换tab选项卡
 *@param {Object} tablink 需要操作的tab选项卡里的a标签父级元素
 *@param {Object} tabitem a标签对应的内容板块
 */
GLOBAL.hashTab = {
	int: function (tablink, tabitem) {
		$(tablink).find('a').on('click', function (e) {
			var $this = $(this),
				hashHref = $this.attr('href')
			//阻止默认行为
			e.preventDefault()
			//设置hash
			window.location.hash = hashHref
			//添加当前样式
			$(tablink).find('a').removeClass('current')
			$this.addClass('current')
			//根据锚点匹配hash-id
			$(tabitem).each(function () {
				if (hashHref == '#' + $(this).data('hash-id')) {
					$(this).show().siblings(tabitem).hide()
				}
			})
		})
		GLOBAL.hashTab.getHash(tablink, tabitem)
	},
	getHash: function (tablink, tabitem) {
		var hash = window.location.hash,
			hashArr = []

		$(tablink).find('a').each(function () {
			hashArr.push($(this).attr('href'))
		})

		//没有hash时候默认显示第一个
		if (!hash || hashArr.toString().indexOf(hash) == -1) {
			$(tablink).find('a').eq(0).addClass('current')
			$(tabitem).eq(0).show()
		} else {
			//根据hash显示添加当前样式
			$(tablink).find('a').each(function () {
				if (hash.indexOf($(this).attr('href')) != -1) {
					$(tablink).find('a').removeClass('current')
					$(this).addClass('current')
				}
			})
			//根据hash显示对应的选项
			$(tabitem).each(function () {
				if (hash.indexOf('#' + $(this).data('hash-id')) != -1) {
					$(this).show().siblings(tabitem).hide()
				}
			})
		}
	}
}

/*
 *判断元素是否出现在可视区
 *@param {String} ele 需要操作的元素 ID或者Class
 */
GLOBAL.isViewArea = function (ele) {
	if ($(ele).offset().top < $(window).height() + $(window).scrollTop() && $(ele).offset().top + $(ele).outerHeight() > $(window).scrollTop()) {
		return true
	}
	return false
}

/**
 *获取url参数
 *@param {string} name [参数名称]
 *@param {string} str [页面url]
 */
GLOBAL._GET = function (name, str) {
	var pattern = new RegExp('[\?&]' + name + '=([^&]+)', 'g')
	str = str || location.search
	var arr, match = ''

	while ((arr = pattern.exec(str)) !== null) {
		match = arr[1]
	}

	return match
}


GLOBAL.CountDown = function () { }

GLOBAL.CountDown.prototype = {
	constructor: GLOBAL.CountDown,

	// 默认配置
	_defaults: function () {
		return {
			el: null,
			interval: 1000,
			leftTime: 0,
			showMillsecond: false,
			update: this._update,
			end: $.noop,
			data: null
		}
	},

	// 从元素属性获得配置
	_dataOptions: function (el) {
		var showMillsecond = el.data('showMillsecond')
		var opts = {
			interval: parseInt(el.data('interval')) || void 0,
			leftTime: parseInt(el.data('leftTime')) || void 0,
			showMillsecond: $.type(showMillsecond) == 'boolean' && showMillsecond
		}

		return opts
	},

	init: function (opts) {
		if (opts.el) {
			opts.el = $(opts.el)
		}

		this._options = $.extend({}, this._defaults(), this._dataOptions(opts.el), opts)

		this._timer = null // 保存setTimeout的返回值
		this._initLeftTime = 0 // 保存出始化时的剩余时间，用于恢复初始状态

		this._cbParams = {
			el: this._options.el,
			timeObj: null,
			showMillsecond: this._options.showMillsecond,
			data: this._options.data
		}

		return this
	},

	start: function () {
		this._nowTime = $.now()
		this._initLeftTime = this._options.leftTime
		!this._timer && this._count()

		return this
	},

	stop: function () {
		this._timer && clearTimeout(this._timer)

		return this
	},

	/**
	 * [reset 重置，再次调用start，将从最初状态开始倒计时]
	 * @return {[type]} [description]
	 */
	reset: function () {
		this.stop()
		this.options._leftTime = this._initLeftTime
		this._initLeftTime = 0

		return this
	},

	// 倒计时
	_count: function () {
		var leftTime = this._options.leftTime
		var interval = this._options.interval
		var self = this
		var now = $.now()

		leftTime = leftTime < 0 ? 0 : leftTime
		this._cbParams.timeObj = this.convertTime(leftTime)

		this._options.update.call(this, this._cbParams)

		if (leftTime > 0) {
			this._options.leftTime -= now - self._nowTime
			self._nowTime = now
			setTimeout(function () {
				self._count()
			}, interval)
		} else {
			this.stop()._options.end.call(this, this._cbParams)
		}

		return this
	},

	// 默认界面更新方法
	_update: function (params) {
		var html = this._toHTML(params.timeObj, params.showMillsecond)
		params.el.html(html)

		return this
	},

	// 生成html，内部使用
	_toHTML: function (timeObj, showMillsecond) {
		var timeArr = [timeObj.h, timeObj.m, timeObj.s]
		var timeTextArr = [' : ', ' : ']
		var self = this

		if (timeObj.d > 0) {
			timeArr.unshift(timeObj.d)
			timeTextArr.unshift(' day(s) ')
		}
		if (showMillsecond) {
			timeArr.push(timeObj.ms)
			timeTextArr.push(' : ')
		}

		timeTextArr.push('')

		var html = $.map(timeArr, function (v, i) {
			return self.fixTime(v) + timeTextArr[i]
		}).join('')

		return html
	},

	/**
	 * [convertTime 把总毫秒数转换成天，时，分，秒，毫秒]
	 * @param  {[int]} time [毫秒数]
	 * @return {[object]}      [时间数据对象]
	 *    {
	 *        d: 天,
	 *        h: 小时,
	 *        m: 分,
	 *        s: 秒,
	 *        ms: 毫秒,
	 *        leftTime: 总的毫秒数
	 *    }
	 */
	convertTime: function (time) {
		var millseconds = time
		var seconds = Math.floor(millseconds / 1000)
		var minutes = Math.floor(seconds / 60)
		var hours = Math.floor(minutes / 60)
		var days = Math.floor(hours / 24)

		return {
			d: days,
			h: hours % 24,
			m: minutes % 60,
			s: seconds % 60,
			ms: millseconds % 1000,
			leftTime: time
		}
	},

	/**
	 * [fixTime 把一个数字转换为字符串，数字不足两位前面加0]
	 * @param  {[int]} num [要转换的数字]
	 * @return {[string]}     [转换后的字符串]
	 */
	fixTime: function (num) {
		return (num < 10 ? '0' : '') + num
	}
}

//弹窗youtube视频
GLOBAL.showYouTuBeVideo = function (video, callback) {
	GLOBAL.PopObj.iframe({
		iframe: { src: 'https://www.youtube.com/embed/' + video },
		area: ['800px', '450px']
	})
	if (typeof callback == 'function' && callback) {
		callback()
	}
}

//促销码弹窗
GLOBAL.promoDialog = function () {
	if (window.location.href.indexOf('?1&utm_source=mail_api&utm_medium=mail') > -1 || window.location.href.indexOf('?utm_source=mail_api&utm_medium=mail') > -1) {

		//删除cookie
		if ($.cookie('is_get_promocode') != null) {
			$.cookie('is_get_promocode', 'yes', {
				expires: -1,
				path: '/',
				domain: COOKIESDIAMON
			})
		}

		var tpl = ''
		$.ajax({
			type: 'get',
			url: window.location.href + '&act=promo_code',
			dataType: 'json',
			success: creatHtml
		})
	}

	function creatHtml (data) {
		//失败
		if (data.status != 0) {
			//弹窗
			if (data.status == 1) {
				tpl += '<div class="promo-code-box" id="js_promoCodeBox">'
				tpl += '<p class="lucky-tips">Here is your lucky coupon:</p>'
				tpl += '<p class="coupon-name"><span>" ' + data.data + ' "</span></p>'
				tpl += '<p class="coupon-tips"><em class="red_font">' + data.pcode_lv + '</em></p>'
				tpl += '<dl>'
				tpl += '<dt>NOTE:</dt>'
				tpl += '<dd>1.Please enter the coupon at the Checkout page for an instant discount.</dd>'
				if (data.num == 0) {
					tpl += '<dd>2.The coupon is valid for all customers who USE and PAY for the order.</dd>'
				} else {
					tpl += '<dd>2.The coupon is valid for the first ' + data.num + ' customers who USE and PAY for the order.</dd>'
				}
				tpl += '<dd>3.The coupon cannot be used with SALE price, PRESALE price and CLEARANCE price at the same time.</dd>'
				tpl += '<dd>4.This coupon automatically expires in 10 days.</dd>'
				tpl += '</dl>'
				tpl += '</div>'
			}
			//优惠码用完
			if (data.status == 2) {
				tpl += '<div class="promo-code-box failed" id="js_promoCodeBox">'
				tpl += '<dl>'
				tpl += '<dt>But never mind, <br/>you can acquire another big coupon:</dt>'
				tpl += '<dd class="red_font"><b class="f20">Emailonly</b></dd>'
				tpl += '<dd>for 10% OFF</dd>'
				tpl += '</dl>'
				tpl += '</div>'
			}

			$('body').append(tpl)
			//弹窗
			GLOBAL.PopObj.openPop({
				type: 1,
				area: ['auto', 'auto'],
				page: {
					dom: '#js_promoCodeBox'
				}
			})
		}
	}
}

/***************validate添加扩展验证规则(扩展规则请全部添加在该处)************/
if (jQuery.validator) {
	//只能输入英文和下划线 改为中间可以有空格
	jQuery.validator.addMethod('isEnglish', function (value, element) {
		return this.optional(element) || /^[A-Za-z\-_]+[A-Za-z\-_\s]*[A-Za-z\-_]+$/.test(value)
	}, 'Only English characters allowed.')

	//只能输入数字、+、-、()
	jQuery.validator.addMethod('isTelphone', function (value, element) {
		return this.optional(element) || /^[0-9\-\+\(\)]+$/.test(value)
	}, 'Please only use: numbers, +, -, or ().')

	//必须包含数字
	jQuery.validator.addMethod('isContainNum', function (value, element) {
		return this.optional(element) || /[0-9]+/.test(value)
	}, 'Please input numbers.')

	//（数字 + 字母）组合
	jQuery.validator.addMethod('numAndLetter', function (value, element) {
		return this.optional(element) || (/[A-Za-z]+/.test(value) && /[0-9]+/.test(value))
	}, jsLg.walletMsg.not_numAndLetter)

	// 地址栏不能是纯数字
	jQuery.validator.addMethod('notAllNum', function (value, element) {
		return this.optional(element) || /\D+/.test(value)
	}, jsLg.walletMsg.not_numAndLetter)

	// 新用户邮箱规则
	jQuery.validator.addMethod('newUser', function (value, element) {
		return this.optional(element) || (/^[a-zA-Z0-9_-]+(\.[a-zA-Z0-9_-]+)*@([a-zA-Z0-9_-](\.)*)+((\.[a-zA-Z]+)+)$/.test(value))
	}, jsLg.signFormMsg.not_newUser)

	// 新用户密码规则
	jQuery.validator.addMethod('newPwd', function (value, element) {
		return this.optional(element) || (/[A-Za-z]+/.test(value) && /[0-9]+/.test(value))
	}, jsLg.signFormMsg.not_newPwd)
}

/***************公共方法调用****************/
//linkid优化
(function (linkid) {
	if (!linkid) return

	$.get('/fun/index.php?act=statisticsLkid', { lkid: linkid, location_url: location.href, referrer_url: document.referre })
})(_GET('lkid'))


//登录检测
GLOBAL.login.info_check(function (data) {
	GLOBAL.updateCartNum(data.data.cart_items)
})
//返回顶部
GLOBAL.backToTop()
//下拉菜单
GLOBAL.dropDown()

//URL改变币种
var currencySign = GLOBAL._GET('currency', window.location.href),
	currencyArr = [],
	myArraySign = window.my_array_sign || [],
	myArrayPosition = window.my_array_position || [],
	currencyListsHtml = '<em class="triangle_bg"></em><em class="triangle_bd"></em>'


if (currencySign !== '') {
	for (var key in myArraySign) {
		if (key == currencySign) {
			$.cookie('bizhong', currencySign, {
				expires: 7,
				path: '/',
				domain: COOKIESDIAMON
			})
			break
		}
	}
}

//页面加载改变币种
GLOBAL.currency.change_houbi();
//公共页面操作事件
(function () {
	if ($.fn.lazyload) {
		$('img.js_lazyimg, img.lazyload').lazyload({
			effect: 'fadeIn',
			load: function () {
				typeof _logsss == 'object' && _logsss.imglazyLoad($(this))
			}
		})
	} else {
		window.GS_GOODS_LAZY_FN('.js_lazyimg,.lazyload');
	}

	//顶部搜索 and 搜索结果页搜索
	$('.js_seachSubmit').parent().find('input[name=\'keyword\']').on('keydown', function (e) {
		var $this = $(this)
		if (e.keyCode === 13) {
			$this.parent().find('.js_seachSubmit').trigger('click')
		}
	})

	$('.js_seachSubmit').click(function () {
		var $this = $(this),
			$kwd = $this.parent().find('input[name=\'keyword\']'),
			$cat = $this.parent().find('input[name=\'category\']')
		GLOBAL.Search($kwd, $cat)
	});

	//鼠标划过顶部用户图标
	(function () {
		$('.js-topUserItem').on('mouseenter', function (e) {
			var $target = $(e.target),
				isCollection = $target.closest('#js_topFavorite').length,
				isCart = $target.closest('#js_topCart').length,
				isUser = $target.closest('#js_topUser').length,
				$this = $(this)

			$this.find('.js-topUserDrop').stop(true, true).show()
			//切换文字
			$(this).find('.user-item-title').stop(true, true).show()

			//用户 - 获取my share状态
			if (isUser) {

				jsGetTicketNum()

				$.get('/fun/?act=get_shareid_result', function (data) {
					var dot = $('.js-login_share').find('.dot')
					data.status == 1 ? dot.show() : dot.hide()
				})
			}

			//收藏
			if (isCollection) {
				GLOBAL.MiniFav(function (data) {
					if (data.items <= 0) {
						$this.find('.js-topUserDrop').html('')
					}
				})
			}

			//购物车
			if (isCart) {
				GLOBAL.MiniCart(function (data) {
					var cartNum = data.total.real_goods_count
					if (cartNum > 0) {
						// GLOBAL.updateCartNum(cartNum)
					}
					else {
						$this.find('.js-topUserDrop').html('')
					}
				})
			}

		}).on('mouseleave', function (e) {
			var $this = $(this)
			$this.find('.js-topUserDrop').stop(true, true).hide()
			$(this).find('.user-item-title').stop(true, true).hide()
		})
	})();

	//搜索 auto complete
	(function () {
		//注释掉对语言的控制，法语俄语也需要推荐词 @px16.12.20
		//if(!GLOBAL.LANG){
		var sBox = $('#js_seachComplete'), //下拉框div
			sIpt = $('#js_seachInput'), //搜索输入框
			sBtn = $('#js_seachSubmit'), //搜索按钮
			oldKeyWord = sBox.html() //页面默认热门搜索词

		//点击空白区域隐藏下拉框
		$(document).on('click', function (event) {
			searchTipsHide()
		})

		//点击搜索框显示下拉框
		$('#js_seachInput,#js_seachComplete').on('click', function (event) {
			$('.js_logsssOtherSearch').hide()
			event.stopPropagation()
			searchTipsShow()
		})

		sIpt.delayKeyup(function (event) {
			//方向键,F5不执行
			if (event.keyCode == 37 || event.keyCode == 38 || event.keyCode == 39 || event.keyCode == 40 || event.keyCode == 116 || event.keyCode == 27) {
				return
			}
			searchAjax()

			//保存大数据输入词
			window.GLOBAL.logsssSiws = sIpt.val()

		}, 600)

		//选择提示的搜索词
		sBox.on('click', 'li a', function (event) {
			var $this = $(this),
				searchId = $this.data('id') || ''
			//统计点击搜索词
			searchStatistics(searchId)

			if ($this.data('url') && $.trim($this.data('url')).length) {
				window.location.href = $.trim($this.data('url'))
			} else {
				sIpt.val($this.find('span').text())
				sBtn.trigger('click')
			}

			return false
		})

		//搜索接口
		function searchAjax () {
			var val = sIpt.val(),
				valArr = val.split(' ')

			if (!valArr[1] && !valArr[valArr.length - 1]) {
				val = valArr[0]
			}

			if (val) {
				$.ajax({
					type: 'GET',
					url: DOMAIN + 'index.php?m=keyword_search',
					data: {
						keyword: val
					},
					dataType: 'jsonp',
					beforeSend: function () {
						$('#js_seachLoading').show()
					},
					success: function (data) {
						$('#js_seachLoading').hide()
						if (data.res != 'fail') {
							sBox.html(data.res)
							searchTipsShow()
						} else {
							searchTipsHide()
						}
					}
				})
			} else {
				searchTipsHide()
			}
		}

		function searchTipsShow () {
			sBox.show() //显示下拉框
			var searchTipsLi = sBox.find('li'),
				size = searchTipsLi.size(),
				val = sIpt.val(),
				downCount = 0

			//鼠标划过li元素
			sBox.find('li').on('mouseover', function (event) {
				event.preventDefault()
				var $this = $(this)
				downCount = $this.index() + 1
				$this.siblings('li').find('a').removeClass('current')
				$this.find('a').addClass('current')
			})


			//按上下键时
			$(document).on('keydown', function (event) {
				//向上
				if (event.keyCode == 38) {
					downCount--
					if (downCount <= 0) {
						downCount = size
					}
				}
				////向下
				else if (event.keyCode == 40) {
					downCount++
					if (downCount > size) {
						downCount = 1
					}
				}
				//ESC关闭
				else if (event.keyCode == 27) {
					searchTipsHide()
					return
				} else {
					return
				}

				var searchTipsLiEq = searchTipsLi.eq(downCount - 1)

				sBox.find('li').find('a').removeClass('current') //移出元素current样式
				searchTipsLiEq.find('a').addClass('current') //当前元素添加current样式
				sIpt.val(searchTipsLiEq.find('span').text()) //给搜索框赋值

			})
		}

		function searchTipsHide () {
			var val = sIpt.val()
			if (!val) {
				sBox.html(oldKeyWord)
			}
			sBox.hide()
			$(document).off('keydown') //移除键盘事件
		}
		//}
	})()


	//顶部点击选择币种
	$('#js_currencyList').on('click', 'span', function () {
		var $this = $(this),
			bizhong = $this.data('currency-word')
		GLOBAL.currency.change_houbi(bizhong)
	})

	//邮件订阅
	$('#js_subscribe').click(function () {
		var $txtEMail = $('#txtEMail')
		return GLOBAL.subscribe($('#txtEMail'))
	})

	//商品点赞
	$('.js_addToFav').click(function () {
		var $this = $(this),
			goodsId = $this.data('goods-id')
		GLOBAL.Like(goodsId, function (res) {
			if (res && res.status == 2) {
				window.location.href = res.data.url
				return
			}
			var oldNum = parseInt($this.find('em').data('attention')),
				newNum = oldNum + 1
			if (newNum >= 1000) {
				newNum = (newNum / 1000).toFixed(1) + 'k'
			}
			$this.find('em').text(newNum)
			$this.find('.icon_goods_cart').addClass('active')
		})
		$this.off('click')
	})

	//商品评论视频
	$('body').on('click', '.js-showVideo', function () {
		var video = $(this).data('video')
		if (video) {
			GLOBAL.showYouTuBeVideo(video)
		}
	})

	//搜索下拉词和导航下拉词， 点击统计
	function searchStatistics (searchId) {
		$.ajax({
			url: '/index.php?m=search&keywordStaticId=' + searchId,
			type: 'GET'
		})
			.done(function (res) {
			})
	};

	$('.sub_nav_link').on('click', 'a', function () {
		var searchId = $(this).data('id')
		searchStatistics(searchId)
	})

})()

//倒计时
function topAdTime (dataTime) {
	var $el = $('#js_topCountDown')
	if ($el.length > 0) {
		$el.show().data('left-time', dataTime)

		new GLOBAL.CountDown().init({
			el: $el,
			showMillsecond: true,
			interval: 10,
			update: function (params) {
				var obj = params.timeObj
				var timeArr = [this.fixTime(obj.d * 24 + obj.h), this.fixTime(obj.m), this.fixTime(obj.s), this.fixTime(obj.ms)]
				var html = '<i>' + timeArr[0] + '</i><span></span><i>' + timeArr[1] + '</i><span></span><i>' + timeArr[2] + '</i><span></span><i>' + timeArr[3] + '</i>'
				params.el.html(html)
			}
		}).start()
	}
}

//获取未读信息数量, 改为鼠标移上去触发
function jsGetTicketNum () {
	var numFromCookie = parseInt($.cookie('ticketNum'))
	if (isNaN(numFromCookie)) {
		$.get('/fun/?act=getTicketNum', function (json) {
			var date = null
			if (json && json.status == 1) {
				updateTicketView(json)
				date = new Date()
				date.setMilliseconds(date.getMilliseconds() + 10 * 60 * 1000) // 10分钟
				// 登录页面不记录cookie，因为在登录页面要清除cookie
				if (window.location.pathname.indexOf('/sign-up.html') == -1) {
					$.cookie('ticketNum', json.count, {
						expires: date,
						path: '/',
						domain: COOKIESDIAMON
					})
				}
			}
		}, 'jsonp')
	}
	else {
		var json = {
			count: numFromCookie
		}
		updateTicketView(json)
	}
	function updateTicketView (data) {
		var num = data.count
		if (num > 0) {
			$('#js_isLogin #msg_num').text(num > 99 ? '99+' : num)
			$('.user_head .new_msg').text(num > 99 ? '99+' : num)
		} else {
			$('#js_isLogin #msg_num').text(0)
			$('.user_head .new_msg').text(0)
		}
	}
}

//app宣传页弹窗打开
$('#js_yoshopBtn').on('click', function () {
	GLOBAL.PopObj.openPop({
		type: 1,
		area: ['auto', 'auto'],
		page: {
			dom: '#js_yoshopPopup'
		}
	})

	//GA
	ga('send', {
		hitType: 'event',
		eventCategory: 'yoshop',
		eventAction: 'clickShow',
		eventLabel: 'download yoshop app dialog'
	})
});


//鼠标划入触发懒加载
(function () {
	var hoverThis = $('.js_hoverLazy')
	hoverThis.mouseenter(function () {
		if ($.fn.lazyload) {
			$(this).find('.hover-lazy').lazyload({})
		} else {
			window.GS_GOODS_LAZY_FN('.hover-lazy');
		}
	}).mouseleave(function () { })
})()

$(function () {
	if (!$('.js_hotNav').length) {
		var _width = $('#js_seachInput').outerWidth(true) + $('#js_seachSubmit').outerWidth(true) - 2
		$('#js_seachComplete').css('width', _width)
	}
})

$('.js_hotNav').click(function (e) {
	$('#js_seachComplete').hide()
	if ($('.hot-nav-list').is(':hidden')) {
		$(this).addClass('active')
		$('.hot-nav-list').show()
	} else {
		$(this).removeClass('active')
		$('.hot-nav-list').hide()
	}
	e.stopPropagation()
})
$('.hot-nav-list').mouseleave(function () {
	$(this).hide()
	$('.js_hotNav').removeClass('active')
})
$('body').click(function () {
	if (!$('.hot-nav-list').is(':hidden')) {
		$('.js_hotNav').removeClass('active')
		$('.hot-nav-list').hide()
	}
})

//check页和订单详情页 vat相关事件
$('.js_vatTip').mouseenter(function () {
	$('.vat-tip-pop').text($(this).data('tip')).show()
}).mouseleave(function () {
	$('.vat-tip-pop').hide()
})
