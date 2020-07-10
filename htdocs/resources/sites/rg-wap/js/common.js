// JavaScript Document
//定义空间命名
window.GLOBAL = {}

var LANG = JS_LANG != '' ? JS_LANG.toString().substr(0, 2) : '',
	currency_lang = LANG != '' ? LANG : 'en'

//根据语言设置默认币种
var $_bizhong = $.cookie('bizhong')
if ((CUR_LANG == 'fr' || CUR_LANG == 'es') && !$_bizhong) {
	$.cookie('bizhong', 'EUR', { path: '/', domain: COOKIESDIAMON })
}

//*********************搜索相关
GLOBAL.Search = {
	seach_submit: function () {
		var that = this
		var $thatForm = $('#js_topSeachForm,#js_seachPage_form')
		//var reg = /[^\w\.]/g;
		$thatForm.submit(function (event) {
			/* Act on the event */
			return that.seachFormOpearal(this)
		})

	},
	seachFormOpearal: function (obj) {
		var $this = $(obj)

		var $kw = $this.find('input.js_k2')
		var categoryVal = 0
		var kwVal = encodeURIComponent($.trim($kw.val()))

		kwVal = kwVal.replace('%2F', '-')
		kwVal = kwVal.replace('%5C', '-')
		kwVal = kwVal.replace(/(\%20)+/g, '-')
		kwVal = kwVal.toLowerCase()

		kwVal = kwVal.replace(/\b\w+\b/g, function (word) {
			return word.substring(0, 1) + word.substring(1)
		})

		if (kwVal == '') {
			$kw.focus()

		} else {
			window.location.href = DOMAIN + '/' + kwVal + '/shop/'
		}
		return false
	}
}


var myArraySign = window.my_array_sign || [], //获取货币种类
	myArrayPosition = window.my_array_position || [] //获取货币图标位置
//*********************货币相关对象
GLOBAL.currency = {
	//页面打开调用函数改变币种
	change_houbi: function (bz, $wrapElm) {
		var bizhong = $.cookie('bizhong')
		var icon

		bizhong = bizhong ? bizhong : 'USD'
		bizhong = arguments[0] ? arguments[0] : bizhong

		$.cookie('bizhong', bizhong, { expires: 7, path: '/', domain: COOKIESDIAMON })

		$('#js_currency').val(bizhong)

		arguments.length > 1 ? this.change_html(bizhong, $wrapElm) : this.change_html(bizhong)

	},

	//循环遍历币种列表，获取币种的符合
	getIcon: function (bizhong) {
		var icon
		icon = myArraySign[bizhong] ? myArraySign[bizhong] : '$'
		return icon
	},

	change_html: function (bz, $wrapElm) {
		var $wrap = arguments.length > 1 ? $($wrapElm) : $('body'),
			$label = $wrap.find('.bizhong'),
			$price = $wrap.find('.my_shop_price'),
			bizhong = bz,
			cookie_bizhong = $.cookie('bizhong')
		that = this
		var orgp, price, icon
		//如果没有传递币种，则读取cookie中的，如果cookie中没有币种，则默认为USD并写入cookie中
		if (!bizhong) {
			if (!cookie_bizhong) {
				$.cookie('bizhong', 'USD', { expires: 7, path: '/', domain: COOKIESDIAMON })
				//$.cookie('bizhong',"USD", {expires: 7, path: '/',domain:COOKIESDIAMON});
			}
			bizhong = $.cookie('bizhong')
		}
		$label.html(bizhong)
		icon = this.getIcon(bizhong)
		$price.each(function (i, o) {
			orgp = $(this).attr('orgp') || $(this).attr('data-orgp')
			price = (parseFloat(my_array[bizhong]) * parseFloat(orgp)).toFixed(2)
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
		return isRight ? price + icon : icon + price
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

		} else {
			completePrice = this.setIconPosition(bizhong, (parseFloat(my_array[bizhong]) * parseFloat(orgp)).toFixed(2), icon)

		}
		$object.html(completePrice)

	}
}
//*********************弹出框相关对象
GLOBAL.PopObj = {
	openPop: function (options) {
		var html = '', $popMain
		var defaultOpts = {
			shade: [0.3, '#000', true],
			area: ['auto', 'auto'],
			offset: ['100px', '50%'],
			title: false,
			page: { dom: '#popElem' },
			close: function () {
				GLOBAL.PopObj.closePop(1)
			}
		}

		defaultOpts = $.extend(true, defaultOpts, options)

		html += '<div class="popBox" id="js_popBox">'

		if (defaultOpts.shade || defaultOpts.shade[2]) {
			html += '<div class="popMask" style="opacity:' + defaultOpts.shade[0] + ';background:' + defaultOpts.shade[1] + '"></div>'
		}
		html += '<div class="popMain" id="js_pageElemBox" style="width:' + defaultOpts.area[0] + '; height:' + defaultOpts.area[1] + '">'

		html += '</div>'
		html += '</div>'


		$('body').append($(html))
		$popMain = $('body').find('div.popMain')

		$popMain.append($(defaultOpts.page.dom).css('display', 'block'))

		$popMain.css({ marginLeft: 0 - $popMain.width() / 2, marginTop: 0 - $popMain.height() / 2, top: '50%', left: '50%' })

	},
	confirm: function (options) {
		var html = '', $popMain
		var defaultOpts = {
			shade: [0.3, '#000', true],
			area: ['auto', 'auto'],
			dialog: {
				msg: 'Are you sure?',
				btns: 2,
				btn: ['Yes', 'No'],
				yes: function () {

				},
				no: function () {
					GLOBAL.PopObj.closePop()
				}
			}
		}

		defaultOpts = $.extend(true, defaultOpts, options)

		html += '<div class="popBox" id="js_popBox">'

		if (defaultOpts.shade || defaultOpts.shade[2]) {
			html += '<div class="popMask" style="opacity:' + defaultOpts.shade[0] + ';background:' + defaultOpts.shade[1] + '"></div>'
		}
		html += '<div class="popMain" style="width:' + defaultOpts.area[0] + '; height:' + defaultOpts.area[1] + '">'
		html += '<div class="popText">' + defaultOpts.dialog.msg + '</div>'
		html += '<div class="popBtns">'

		if (defaultOpts.dialog.btns == 1) {
			html += '<a href="javascript:void(0)" class="popOnBTns" id="js_popYesBtn">' + defaultOpts.dialog.btn[0] + '</a>'
		} else if (defaultOpts.dialog.btns == 2) {
			html += '<a href="javascript:void(0)" class="leftBtn"  id="js_popNoBtn">' + defaultOpts.dialog.btn[0] + '</a>'
			html += '<a href="javascript:void(0)" class="rightBtn" id="js_popYesBtn">' + defaultOpts.dialog.btn[1] + '</a>'
		}
		html += '</div>'
		html += '</div>'
		html += '</div>'

		$('body').append(html)

		$popMain = $('body').find('div.popMain')
		$popMain.css({ marginLeft: 0 - $popMain.width() / 2, marginTop: 0 - $popMain.height() / 2, top: '50%', left: '50%' })

		$('#js_popBox').on('click', '#js_popYesBtn', function () {
			defaultOpts.dialog.yes(this)

		})

		$('#js_popBox').on('click', '#js_popNoBtn', function () {
			defaultOpts.dialog.no(this)
		})
	},
	loadPop: function () {
		var html = ''
		html += '<div class="popBox" id="js_popBox">'
		html += '<div class="popMask" style="background:rgba(0,0,0,0.5)"></div>'
		html += '<i class="icon_tag popLoading"></i>'
		html += '</div>'

		$('body').append(html)
	},
	/**
   * [closePop]
   * @param  {[type]} pageElem [pageElem 为1 关闭的弹出框是 页面上的Dom]
   * @param  {[type]} callBack [回调函数]
   * @return {[void]}
   */
	closePop: function (pageElem, callBack) {
		pageElem == 1 ? $('body').append($('body').find('div.popMain').children().removeAttr('style')) : ''
		callBack ? callBack() : ''
		$('body').find('#js_popBox').remove()

	},
	tipsShow: function (options) {
		var windowWidth = $(window).width()

		var ThisOffset = options.Obj.offset(),
			thisLeft = ThisOffset.left - 10,
			thisTop = ThisOffset.top + options.Obj.outerHeight() + 3


		if (windowWidth - ThisOffset.left < 120) {
			thisLeft = ThisOffset.left - 80
		}

		var innerH = '<div id="popTips" style="position:absolute; font-size:12px; color:#666; border-radius:3px; border:1px solid #ddd; z-index:9999;left:' + thisLeft + 'px;top:' + thisTop + 'px;">'
		innerH += '<div style="position:relative;z-index:10003;padding:5px 10px;border-radius:3px; background-color:#fff;">' + options.msg + '</div>'
		innerH += '</div>'

		$('body').append(innerH)
	},
	closeTipsShow: function () {
		$('#popTips').remove()
	}
}
//*********************图片懒加载
GLOBAL.lazyLoad = {
	scrollLazyLoad: function (selectBox) {
		var $selectBox = $(selectBox)


		if ($.fn.lazyload) {
			$selectBox.lazyload({
				threshold: 200,
				effect: 'fadeIn',
				failure_limit: 60,
				skip_invisible: false
			})
		} else {
			window.GS_GOODS_LAZY_FN(selectBox);
		}
	},

	tableLayout: function (selectBox) {
		var $selectBox = $(selectBox)


		if ($.fn.lazyload) {
			$selectBox.lazyload({
				effect: 'fadeIn',
				event: 'tabEvent'
			})
		} else {
			window.GS_GOODS_LAZY_FN(selectBox);
		}
	},

}

/***************validate添加扩展验证规则(扩展规则请全部添加在该处)************/
if (jQuery.validator) {
	// 新用户邮箱规则
	jQuery.validator.addMethod('newUser', function (value, element) {
		return this.optional(element) || (/^[a-zA-Z0-9_-]+(\.[a-zA-Z0-9_-]+)*@([a-zA-Z0-9_-](\.)*)+((\.[a-zA-Z]+)+)$/.test(value))
	}, jsLg.new_userEmail)

	// 新用户密码规则
	jQuery.validator.addMethod('newPwd', function (value, element) {
		return this.optional(element) || (/[A-Za-z]+/.test(value) && /[0-9]+/.test(value))
	}, jsLg.new_userPwd)
}

//**********************linkid优化
$(function () {
	(function (linkid) {
		if (!linkid) return

		$.get('/fun/index.php?act=statisticsLkid', { lkid: linkid, location_url: location.href, referrer_url: document.referre })
	})(_GET('lkid'))
})
//*********************顶部登录
GLOBAL.login = {
	isLogin: function (callBack, callbackArg) {
		var that = this
		$.ajax({
			type: 'GET',
			//cache:false,
			url: '/fun/index.php?act=chk_sign',
			success: function (msg) {
				if (msg) {
					that.isLoginEnd(msg)
				}
				if (callBack) {
					arguments.length > 1 ? callBack.call(this, msg, callbackArg) : callBack.call(msg)
				}
			}
		})
	},
	isLoginEnd: function (msg) {
		if (msg) {
			$('#isNotLogin').hide()
			$('#isLosgin').show().html(msg + '<a href="' + DOMAIN_LOGIN + '/m-users-a-logout.htm" class="ml5">' + window.jsLg.logout + ' <i class="icon-logout"></i></a>')
		}
	},
	sign: function ($formBox, callBack) {
		$formBox.validate({
			rules: {
				email: {
					required: true,
					maxlength: 60,
					email: true
				},
				password: {
					required: true,
					minlength: 6,
					maxlength: 60
				},
				login_access_code: {
					required: true
				}
			},
			messages: {
				email: {
					required: jsLg.formMsg.email_require_msg,
					email: jsLg.formMsg.email_require_msg,
					maxlength: jsLg.formMsg.email_maxlength_msg
				},
				password: {
					required: jsLg.formMsg.password,
					minlength: jsLg.formMsg.password_minlength
				},
				login_access_code: {
					required: jsLg.formMsg.access_code_error
				}
			},
			submitHandler: function () {
				if (callBack) {
					callBack()
				}
			},
			errorPlacement: function (error, element) {
				element.parent().find('label.checked').remove()
				error.appendTo(element.parent())

			},
			success: function (label) {
				label.remove()
			}
		})
	},
	register: function ($formBox, callBack) {

		$formBox.validate({
			rules: {
				email: {
					required: true,
					maxlength: 60,
					email: true,
					newUser: true,
					remote: {
						url: '/' + JS_LANG + 'm-users-a-check_email.htm',
						type: 'post',
						data: {
							email: function () {
								return $('#reg_email').val()
							}
						}
					}
				},
				password: {
					required: true,
					maxlength: 60,
					minlength: 8,
					newPwd: true
				},
				password_confirm: {
					required: true,
					minlength: 8,
					maxlength: 60,
					newPwd: true,
					equalTo: '#password'
				},
				sign_access_code: {
					required: true
				},
				agreement: {
					required: true
				},
				eu_agreement: {
					required: true
				},
				sign_information: {
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
					minlength: jsLg.new_userPwd,
					rangelength: jQuery.format(jsLg.formMsg.password_minlength)
				},
				password_confirm: {
					required: jsLg.formMsg.password_repeat,
					minlength: jsLg.new_userPwd,
					equalTo: jsLg.formMsg.password_repeat
				},
				sign_access_code: {
					required: jsLg.formMsg.access_code_error
				},
				agreement: {
					required: jsLg.formMsg.register_agreement
				},
				eu_agreement: {
					required: $('.js_euAgreement').data('msg-tip')
				},
				sign_information: {
					required: $('.js_information').data('msg-tip')
				}
			},
			// specifying a submitHandler prevents the default submit, good for the demo
			submitHandler: function () {
				if (callBack) {
					callBack()
				}
			},
			errorPlacement: function (error, element) {
				element.parent().find('label.checked').remove()
				error.appendTo(element.parent())

			},
			success: function (label) {
				label.remove()
			}
		})
	},
	fbregister: function ($formBox, callBack) {

		$formBox.validate({
			rules: {
				email: {
					required: true,
					maxlength: 60,
					email: true,
					//json_remote: DOMAIN_USER+'/'+JS_LANG+"index.php?m=users&a=check_email&jsoncallback=?"
					remote: {
						url: '/' + JS_LANG + 'm-users-a-check_email.htm',
						type: 'post',
						data: {
							email: function () {
								return $('#fb_email').val()
							}
						}
					}
				}

			},
			messages: {
				email: {
					required: jsLg.formMsg.email_require_msg,
					maxlength: jsLg.formMsg.email_maxlength_msg,
					email: jsLg.formMsg.email_require_msg,
					remote: jQuery.format(jsLg.formMsg.email_in_user)
				}
			},
			// specifying a submitHandler prevents the default submit, good for the demo
			submitHandler: function () {
				if (callBack) {
					callBack()
				}
			},
			errorPlacement: function (error, element) {
				element.parent().find('label.checked').remove()
				error.appendTo(element.parent())

			},
			success: function (label) {
				label.remove()
			}
		})
	},
	getpassInput: function (popIndx) {
		var v = $('#myInput').val()
		v = v.replace('-', '=')
		if (v == '' || v.indexOf('@') < 0 || v.indexOf('.') < 0)
			//alert(jsLg.formMsg.email_require_msg);
			GLOBAL.PopObj.confirm({
				dialog: {
					msg: jsLg.formMsg.email_require_msg,
					btns: 1,
					btn: [jsLg.confirm],
					yes: function () {
						GLOBAL.PopObj.closePop()
					}
				}
			})
		else {
			window.location.href = '/' + JS_LANG + 'm-users-a-send_pwd_email-e-' + v + '.htm'
			layer.close(popIndx)
		}

	}
}

//州和国家联动
GLOBAL.CountryChange = function (options) {// options = {country_id:"当前城市的value"，address_id:"当前address在address list中对应的index",state_str:"表示是否有默认的州地址，可不空"}
	var country = $('#country_json').val()//获得国家的json数据
	if (country) {
		var countrys = eval('(' + country + ')')
	}
	var state_str = options.state_str ? options.state_str : ''
	var address_id = parseInt(options.address_id)
	var selectcountry = countrys[options.country_id]
	var $codeBox = $('.code_' + address_id)
	var $stateWrapBox = $('.state_' + address_id + '_' + address_id)

	if (selectcountry) {//如果查询的国家存在
		var state = selectcountry['state']
		var code = selectcountry['code']
		//国家的区号
		$codeBox.html('+' + code + '<input type=\'hidden\' name=\'code\' value=\'' + code + '\'>')

		//如果能查到该国家的州就循环出来，否则提供一个输入框，让用户自己输入

		if (state && state.length > 0) {
			$stateWrapBox.html('<div  class="choiceCountryW icon-arrow-down selWrap"><select id="states_' + address_id + '_' + address_id + '" name="province" class="choiceCountry formControl"></select><span class="selTriangle"></span></div>')
			var province = ''
			document.getElementById('states_' + address_id + '_' + address_id).options[document.getElementById('states_' + address_id + '_' + address_id).length] = new Option(jsLg.addCart_2, '')
			for (var i = 0; i < state.length; i++) {
				//var len = $('#states_'+address_id+'_'+address_id).length;
				province = state[i].replace('`', '\'')
				document.getElementById('states_' + address_id + '_' + address_id).options[document.getElementById('states_' + address_id + '_' + address_id).length] = new Option(province, province)

			}
			$('#states_' + address_id + '_' + address_id).val($.trim(state_str).length > 0 ? $.trim(state_str) : '')
		} else {
			$stateWrapBox.html('<input type=\'text\' name=\'province\' class=\'text formControl \' value=\'' + state_str + '\' />')
		}
	} else {//如果查询的国家不存在
		$codeBox.html('')
		$stateWrapBox.html('<input type=\'text\' name=\'province\' class=\'text formControl \' value=\'' + state_str + '\' />')
	}
}

//检查信息（登录信息，购物车信息，记录商品详情页点击率,，每周特销时间）
//action = 1 普通登录信息，购物车信息检查 ， action = 2登录信息，购物车信息，记录商品详情页点击率 ， action = 3 登录信息，购物车信息，每周特销时间
function info_check (action, callback) {
	var url = DOMAIN + '/fun/?act=info_check&action=' + action
	var query_url = window.location.href   //当前页面URL地址
	//var lkid = _GET('lkid', query_url);     //获取URL地址中是否有lkid
	var lkid = _GET('lkid', location.hash.indexOf('lkid=') > 0 ? '?' + location.hash.substr(1) : null)
	if (lkid) {
		var referrer_url = encodeURIComponent(document.referrer)   //来源URL地址
		url += '&lkid=' + lkid + '&referrer_url=' + referrer_url
	}
	$.ajax({
		type: 'GET',
		url: url,
		dataType: 'json',
		data: {
			location_url: location.href
		},
		cache: false,
		success: function (msg) {
			//msg={firstname:"",cart_items:"",LeftTime:"",user_id:"0"}
			USER_ID = msg.user_id
			var cartNumber = msg.cart_items
			if (!cartNumber) { cartNumber = 0 }
			$('#js_cart_items').html(parseInt(cartNumber, 10))

			// heade information replace if login
			if (USER_ID) {
				var userName = msg.firstname !== '&nbsp;&nbsp;' && msg.firstname ? ',' + msg.firstname : msg.firstname
				$('#nav .nav_login').html('<a href=\'' + DOMAIN_USER + '/m-users.htm\'><div class=\'iconBefore icon_navuser\'>Hi' + userName + '</div></a>')
			}


			GLOBAL.login.isLoginEnd(msg.firstname)

			if (typeof callback == 'function') {
				callback(msg)
			}
			//$(".index_time_coutDown").attr('data-time',msg.LeftTime);
		}
	})
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



//*********************购物车相关
GLOBAL.cart = {
	// 显示当前加入购物车的产品
	hideMinCartTimer: null,
	showMinCartTimer: null,
	showCurrentAddGoods: function (gid, obj) {
		var self = this,
			res = {
				price: $('#unit_price').data('orgp'),
				image: $('#bannerList').find('img').eq(0).attr('src')
			},
			addText = obj.innerHTML,
			$view = $('#miniCartDom')

		if (!$view.length) {
			console.log($view)
			return
		};

		obj.innerHTML = obj.getAttribute('data-added-text')
		laytpl(document.getElementById('miniCartData').innerHTML).render(res, function (html) {
			$view.html(html).show()
			GLOBAL.currency.change_html('', $view)

			// 3s 后关闭mini cart
			self.hideMinCartTimer = setTimeout(function () {
				$view.hide()
			}, 3000)
			setTimeout(function () {
				obj.innerHTML = addText
			}, 3000)

			$('body').one('touchstart.oneEvent', function (event) {
				($(event.target).closest('#miniCartDom').length > 0) || self.hideCurrentAddGoods()
			})
			$(window).one('scroll.oneEvent', function (event) {
				self.hideCurrentAddGoods()
			})

		})
	},
	hideCurrentAddGoods: function () {
		$('#miniCartDom').hide()
		clearInterval(this.hideMinCartTimer)
		$('body').off('.oneEvent')
		$(window).off('.oneEvent')
	},

	//更新购物车数量
	cartItems: function () {
		var URL = '/fun/?act=cart_item&noscript=1&jsoncallback=?'
		$.get(URL, function (data) {
			msg = data
			$('#js_cart_items').html(msg)
		})
	},
	//商品属性更改
	change_same_goods: function (obj) {
		var goods_id = parseInt(obj.value)
		var url = $(obj.options[obj.selectedIndex]).attr('attr') || $(obj.options[obj.selectedIndex]).attr('data-attr')
		if (goods_id > 0) {
			window.location.href = url
		}
	},
	//异步刷新页面
	re_load: function (page_url, selectStr, callBack) {
		var that = this
		$.ajax({
			type: 'GET',
			url: page_url,
			cache: false,
			success: function (data) {
				var $msg = $(data)

				var stext = $msg.find(selectStr).html()
				$(selectStr).html(stext)
				//更改币种
				GLOBAL.currency.change_html('', $(selectStr))
				//更新购物车中的数量
				that.cartItems()

				if (callBack) {
					callBack.call(this, data)
				}
			}
		})
	}
}

//回到顶部
GLOBAL.gotoUp = function () {
	var Jhtml = $('<div class="ui-gotop none"></div>')
	Jhtml.appendTo('body')

	Jhtml.on('click', function () {
		$('html,body').animate({ scrollTop: 0 }, 500)
	})

	$(window).scroll(function (event) {
		if ($(this).scrollTop() > document.documentElement.clientHeight * 2) {
			Jhtml.addClass('ui-gotopShow').show()
		} else {
			Jhtml.removeClass('ui-gotopShow').hide()
		}
	})

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
				timer = setTimeout(function () { callback(event) }, ms)
			}
			else {
				alert('Your callback is not valid')
			}
		})
		return $(this)
	}
})(jQuery)




$(function () {

	//判断是否登录了
	info_check(1)

	//改变币种
	var currencyListsHtml = ''
	//获取货币种类
	for (var n in myArraySign) {
		currencyListsHtml += '<option value="' + n + '">' + myArraySign[n] + ' ' + n + '</option>'
	}

	$('#js_currency').html(currencyListsHtml).change(function (event) {
		/* Act on the event */
		var $this = $(this)
		GLOBAL.currency.change_houbi($this.val())
	}).focus(function () {
		$(this).parent().addClass('on')
	}).blur(function (event) {
		$(this).parent().removeClass('on')
	})

	GLOBAL.currency.change_houbi()

	//搜索**提交表单
	GLOBAL.Search.seach_submit()

	$('.js_k2').on('keyup', function () {
		var $this = $(this)
		var $closeBtn = $('#js_topSeachForm').find('i.icon_close')

		if ($.trim($this.val()).length) {
			$closeBtn.show()
		} else {
			$closeBtn.hide()
		}
		if ($('#js_seachInput').val() == false) {
			$('#js_m_search_key').show()
		} else {
			$('#js_m_search_key').hide()
		}
	})


	$('#js_topSeachForm').on('tap', 'i.icon_close', function () {
		var $this = $(this)
		var $form = $this.closest('form')
		$form.find('input.js_k2').val('')
		$('#js_m_search_key').show()
		$this.hide()
	})

	//下拉搜索栏目
	$('#js_top_cate').on('click', function (e) {
		var $this = $(this)
		var $slidBox = $('#header').find('div.top_slidBox'), $nav = $('nav'),
			isInited = $('#js_collapse').hasClass('inited')

		$('body').css('overflow', 'hidden')
		$('.js_closeSideBar').show()
		$('.ui-gotop').removeClass('ui-gotopShow')

		$slidBox.find('.nav_list_first').removeClass('active')
		$slidBox.find('.icon_collapse').removeClass('expand')
		if (isInited) {
			$slidBox.find('dd').height(0)
		}
		$slidBox.show().css({
			'left': function () {
				if (!isInited) {
					initListHeight()
				}
				return 0
			}
		})
		$nav.addClass('show')

		var navHeight = $('#nav').outerHeight()
		var windowHeight = window.screen.availHeight
		var pb = windowHeight - navHeight
		if (pb > 0) {
			$('#js_collapse').css('padding-bottom', pb + 'px')
		}

		$('.side-mask').show()
		e.stopPropagation()
	})

	//收起下拉菜单
	$('.js_closeSideBar').on('click', function () {
		$(this).hide().closest('.top_slidBox').css({ 'left': '-100%' })
		$('body').css('overflow', 'scroll')
		$('nav').removeClass('show')
		$('.side-mask').hide()
		$('.ui-gotop').addClass('ui-gotopShow')
	});

	// 下拉栏目的最大高度设置
	(function () {
		var topHeight = $('#topheader').height()
		$('#topheader .top_slidBox').css('maxHeight', $(window).height())

	})()

	//下拉栏目的二级目录联动

	function initListHeight () {
		$('#js_collapse .nav_list_first dd').each(function (item, value) {
			$(value).attr('data-height', getActualRect(value, $(value).parent('dl')[0]).height)
			$(value).height(0).css('display', 'block')
		})
		$('#js_collapse').addClass('inited')
	}
	function expandFun (target) {
		var dd = target.parent('dt').next('dd')
		dd.height(dd.attr('data-height'))
		target.addClass('expand')
		target.parents('.nav_list_first').addClass('active')
		// target.parent("dt").next("dd").slideDown("normal");
	}
	function collapseFun (target) {
		var dd = target.parent('dt').next('dd')
		target.removeClass('expand')
		target.parents('.nav_list_first').removeClass('active')
		dd.height(0)
	}
	$('#js_collapse').on('tap', function (e) {
		$target = $(e.target)
		if ($target.hasClass('icon_collapse')) {
			$active = $(this).find('.active .icon_collapse')
			if ($target.hasClass('expand')) {
				collapseFun($target)
			} else {
				collapseFun($active)
				expandFun($target)
			}
		}
	})

	//下拉搜索框
	$('#js_top_search').on('click', function (e) {
		var $this = $(this)
		var $slidBox = $('#header').find('div.search')

		if ($slidBox.is(':visible')) {
			$slidBox.slideUp().data('down', 0)
		}
		else {
			$slidBox.slideDown().data('down', 1)
		}
		e.stopPropagation()
	})

	$('body').click(function (e) {
		var $slidBox = $('#header').find('div.search')
		if ($slidBox.is(':visible')) {
			if (e.target.className != 'search' && e.target.id != 'js_seachInput') {
				$slidBox.slideUp().data('down', 0)
			}
		}
	})

	$(window).scroll(function () {
		if ($(this).scrollTop() > 5) {
			var $slidBox = $('#header').find('div.search')
			if ($slidBox.is(':visible')) {
				$slidBox.slideUp().data('down', 0)
			}
		}

	});

	//搜索 auto complete
	(function () {
		var sBox = $('#js_seachComplete'),//下拉框div
			sIpt = $('#js_seachInput'),//搜索输入框
			sBtn = $('#js_seachSubmit'),//搜索按钮
			oldKeyWord = sBox.html() //页面默认热门搜索词
		searchKey = $('#js_m_search_key')//初始化的搜索词

		//点击空白区域隐藏下拉框
		$(document).on('click', function (event) {
			searchTipsHide()
		})

		sIpt.delayKeyup(function (event) {
			//方向键,F5不执行
			if (event.keyCode == 37 || event.keyCode == 38 || event.keyCode == 39 || event.keyCode == 40 || event.keyCode == 116 || event.keyCode == 27) {
				return
			}
			searchAjax()
		}, 300)

		//选择提示的搜索词
		sBox.on('click', 'li a', function (event) {
			var $this = $(this)
			sIpt.val($this.find('span').text())
			sBtn.trigger('click')
			return false
		})
		searchKey.on('click', 'li a', function (event) {
			var $this = $(this)
			sIpt.val($this.find('span').text())
			sBtn.trigger('click')
			return false
		})
		//搜索接口
		function searchAjax () {
			var val = sIpt.val(),
				valArr = val.split(' '),
				_DOMAIN = DOMAIN.replace('m.', '')

			if (!valArr[1] && !valArr[valArr.length - 1]) {
				val = valArr[0]
			}

			if (val) {
				$.ajax({
					type: 'GET',
					url: DOMAIN + '/index.php?m=keyword_search',
					data: { keyword: val },
					dataType: 'jsonp',
					beforeSend: function () {
					},
					success: function (data) {
						if (data.res != 'fail') {
							sBox.html(data.res)
							searchTipsShow()
						}
						else {
							searchTipsHide()
						}
					}
				})
			}
			else {
				searchTipsHide()
			}
		}

		function searchTipsShow () {
			sBox.show()//显示下拉框
		}

		function searchTipsHide () {
			var val = sIpt.val()
			if (!val) {
				sBox.html(oldKeyWord)
			}
			sBox.hide()
			$(document).off('keydown')//移除键盘事件
		}
	})()

	// $("body").on("tap",function(){
	//     var $slidBox = $("#header").find('div.top_slidBox'),$nav = $("nav");

	//     $slidBox.slideUp(function(){
	//         $nav.removeClass('show');
	//     });
	//     $("#header").find('div.top').removeClass('on');
	// });

	$('#header').find('div.top_slidBox').on('tap', function (e) {
		e.stopPropagation()
	})

	//底部
	$('.js_f_Operal').find('p').on('tap', function () {
		var $this = $(this), $thisParent = $this.closest('.js_f_Operal'), $slideBox = $thisParent.find('div.f_OperalBox')

		if ($thisParent.hasClass('on')) {
			$slideBox.slideUp()
			$thisParent.removeClass('on')
		} else {
			$slideBox.slideDown()
			$thisParent.addClass('on')
		}
	})

	//tips提示
	$('.js_tips').hover(function () {
		var $this = $(this)

		GLOBAL.PopObj.tipsShow({
			Obj: $this,
			msg: $this.data('tips')
		})
	}, function () {
		GLOBAL.PopObj.closeTipsShow()
	})

	//回到顶部
	GLOBAL.gotoUp()
})



/*
* 获取隐藏元素的大小
* dom 需要或的元素
* until dom祖先元素最后一个隐藏的元素
*/
function getActualRect (dom, until) {
	if (!dom) {
		return { 'width': 0, 'height': 0 }
	}
	var current = dom,
		nodes = [dom],
		backUp = [],
		actual
	while (current.parentNode) {
		nodes.push(current.parentNode)
		current = current.parentNode
		if (current === until) {
			break
		}
	}
	style = 'display:block!important;visibility:hidden!important;'
	nodes.forEach(function (el, index, array) {
		var cssText = el.style.cssText
		backUp.push(cssText)
		el.style.cssText = cssText + style
	})
	actual = {
		'width': dom.clientWidth,
		'height': dom.clientHeight
	}
	nodes.forEach(function (el, index, array) {
		el.style.cssText = backUp[index]
	})
	return actual
}

//app专享价弹窗提示下载app（goods.html 和 cart.html）
(function () {
	var lk = /linkid=(\d+)/.exec(document.cookie),
		lkid = lk ? lk[1] : '',
		android_lkid = 'https://play.google.com/store/apps/details?id=com.globalegrow.app.rosegal&referrer=af_tranid%3DGfHmEb98blZnTZ0b_dOMqg%26pid%3DM_Rosegal%26c%3DM_Rosegal',
		android = 'https://app.appsflyer.com/com.globalegrow.app.rosegal?pid=Rosegal_M&c=Rosegal_M'
	$('.js_appPriceTips').on('click', function (e) {
		e.stopPropagation()
		var $this = $(this),
			ref = $this.data('ref')

		if (lkid) {
			ref = android_lkid + '%26lkid%3D' + lkid
		} else {
			ref = android
		}
		GLOBAL.PopObj.confirm({
			dialog: {
				msg: 'Download The App?',
				btns: 2,
				btn: ['No', 'Yes'],
				yes: function (index) {
					window.location.href = 'https://go.onelink.me/731485670?pid=Rosegal&c=M'
					GLOBAL.PopObj.closePop()
				}
			}
		})
	})
})();


//full site按钮
(function () {
	$('#full_site').click(function () {
		$.cookie('fullsite', 'true', { expires: 1, path: '/', domain: '.rosegal.com' })
		window.location.href = $(this).attr('data-href')
	})
})();

// 浮动头部
(function () {
	var header = document.querySelector('#topheader')
	var search = $('#header').find('div.search')
	if (header && header != undefined) {
		if (window.location.hash) {
			header.classList.add('slide--up')
		}
		new Headroom(header, {
			offset: 60,
			classes: {
				initial: 'slide',
				pinned: 'slide--reset',
				unpinned: 'slide--up',
			},
			onPin: function () {
				if (search.data('down')) {
					search.slideDown()
				}
			},
			onUnpin: function () {
				var $slidBox = $('#header').find('div.top_slidBox'), $nav = $('nav')

				$('#header').find('div.top').removeClass('on')

				search.slideUp()
			},
			onTop: function () {
			}
		}).init()
	}
}())

//底部订阅
; (function () {
	var $sign_up = $('#js_signUp'),
		email_reg = /^[a-zA-Z0-9_-]+[\.a-zA-Z0-9_-]+@[a-zA-Z0-9_-]+(\.[a-zA-Z0-9_-]+)+$/

	$sign_up.on('click', function () {
		checkEmail($.trim($('#js_subscribe').val()))
	})

	function checkEmail (email) {
		if (email_reg.test(email)) {
			postEmail(email)
		} else {
			noticeMes('Email format error!')
		}
	}

	function postEmail (email) {
		$.post(
			DOMAIN + '/m-cemails.html',
			{ email: email },
			function (data) {
				if (data.status == 0) {//success
					noticeMes(window.jsLg.subscribe_email.tip1)
				} else if (data.status == 1) {//已经订阅
					noticeMes(window.jsLg.subscribe_email.tip2)
				} else {
					noticeMes(window.jsLg.subscribe_email.tip3)
				}
			},
			'json'
		)
	}

	function noticeMes (mes) {
		layer.open({
			content: mes,
			btn: [window.jsLg.ok]
		})
	}


})();

(function () {


	if ($.fn.lazyload) {
		$('img.js_lazyimg,img.lazyload').lazyload({
			threshold: 200,
			effect: 'fadeIn',
			failure_limit: 60,
			skip_invisible: false
		})
	} else {
		window.GS_GOODS_LAZY_FN('img.js_lazyimg,img.lazyload');
	}


	$('#js_subscribe').focus(function () {
		$('.js_tip_sub').show()
	}).blur(function () {
		$('.js_tip_sub').slideUp(100)
	})


})()

function customLayer (html, btnText, callback, closeCallback) {
	var allHtml = ''
	allHtml += '<div class="custom-layer"><div class="content">' + html + '</div><div class="action-btn"><button class="fb closeLayer">' + (btnText ? btnText : 'OK') + '</button></div></div>'

	var layerIndex = layer.open({
		type: 1,
		content: allHtml,
		style: 'width: 88%; background-color: transparent',
		success: function (elem) {
			callback && callback(elem)
			$(elem).on('click', '.closeLayer', function (event) {
				event.preventDefault()
				closeCallback && closeCallback()
				layer.close(layerIndex)
			})
		}
	})
}
