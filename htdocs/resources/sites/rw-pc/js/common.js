window.mobileAndTabletcheck = function () {
	var check = false;

	(function (a) { if (/(android|bb\d+|meego).+mobile|avantgo|bada\/|blackberry|blazer|compal|elaine|fennec|hiptop|iemobile|ip(hone|od)|iris|kindle|lge |maemo|midp|mmp|mobile.+firefox|netfront|opera m(ob|in)i|palm( os)?|phone|p(ixi|re)\/|plucker|pocket|psp|series(4|6)0|symbian|treo|up\.(browser|link)|vodafone|wap|windows ce|xda|xiino|android|ipad|playbook|silk/i.test(a) || /1207|6310|6590|3gso|4thp|50[1-6]i|770s|802s|a wa|abac|ac(er|oo|s\-)|ai(ko|rn)|al(av|ca|co)|amoi|an(ex|ny|yw)|aptu|ar(ch|go)|as(te|us)|attw|au(di|\-m|r |s )|avan|be(ck|ll|nq)|bi(lb|rd)|bl(ac|az)|br(e|v)w|bumb|bw\-(n|u)|c55\/|capi|ccwa|cdm\-|cell|chtm|cldc|cmd\-|co(mp|nd)|craw|da(it|ll|ng)|dbte|dc\-s|devi|dica|dmob|do(c|p)o|ds(12|\-d)|el(49|ai)|em(l2|ul)|er(ic|k0)|esl8|ez([4-7]0|os|wa|ze)|fetc|fly(\-|_)|g1 u|g560|gene|gf\-5|g\-mo|go(\.w|od)|gr(ad|un)|haie|hcit|hd\-(m|p|t)|hei\-|hi(pt|ta)|hp( i|ip)|hs\-c|ht(c(\-| |_|a|g|p|s|t)|tp)|hu(aw|tc)|i\-(20|go|ma)|i230|iac( |\-|\/)|ibro|idea|ig01|ikom|im1k|inno|ipaq|iris|ja(t|v)a|jbro|jemu|jigs|kddi|keji|kgt( |\/)|klon|kpt |kwc\-|kyo(c|k)|le(no|xi)|lg( g|\/(k|l|u)|50|54|\-[a-w])|libw|lynx|m1\-w|m3ga|m50\/|ma(te|ui|xo)|mc(01|21|ca)|m\-cr|me(rc|ri)|mi(o8|oa|ts)|mmef|mo(01|02|bi|de|do|t(\-| |o|v)|zz)|mt(50|p1|v )|mwbp|mywa|n10[0-2]|n20[2-3]|n30(0|2)|n50(0|2|5)|n7(0(0|1)|10)|ne((c|m)\-|on|tf|wf|wg|wt)|nok(6|i)|nzph|o2im|op(ti|wv)|oran|owg1|p800|pan(a|d|t)|pdxg|pg(13|\-([1-8]|c))|phil|pire|pl(ay|uc)|pn\-2|po(ck|rt|se)|prox|psio|pt\-g|qa\-a|qc(07|12|21|32|60|\-[2-7]|i\-)|qtek|r380|r600|raks|rim9|ro(ve|zo)|s55\/|sa(ge|ma|mm|ms|ny|va)|sc(01|h\-|oo|p\-)|sdk\/|se(c(\-|0|1)|47|mc|nd|ri)|sgh\-|shar|sie(\-|m)|sk\-0|sl(45|id)|sm(al|ar|b3|it|t5)|so(ft|ny)|sp(01|h\-|v\-|v )|sy(01|mb)|t2(18|50)|t6(00|10|18)|ta(gt|lk)|tcl\-|tdg\-|tel(i|m)|tim\-|t\-mo|to(pl|sh)|ts(70|m\-|m3|m5)|tx\-9|up(\.b|g1|si)|utst|v400|v750|veri|vi(rg|te)|vk(40|5[0-3]|\-v)|vm40|voda|vulc|vx(52|53|60|61|70|80|81|83|85|98)|w3c(\-| )|webc|whit|wi(g |nc|nw)|wmlb|wonu|x700|yas\-|your|zeto|zte\-/i.test(a.substr(0, 4))) check = true })(navigator.userAgent || navigator.vendor || window.opera)

	return check
}

if (mobileAndTabletcheck() &&
	window.location.href.search(/((https:\/\/www)|(https:\/\/es))\.rosewholesale\.com/) != -1) {
	window.location.href = window.location.href.replace(/((https:\/\/www)|(https:\/\/es))\.rosewholesale\.com/, 'https://m.rosewholesale.com')
}

/*
 * keyup延迟函数 delayKeyup
 * 使用方法: $("#input").delayKeyup(function(){},1000);
 */
(function($) {
	$.fn.delayKeyup = function(callback, ms) {
		var timer = 0
		$(this).on('keyup', function(event) {
			clearTimeout(timer)
			if (callback && typeof(callback) == 'function') {
				timer = setTimeout(function() {
					callback(event)
				}, ms)
			} else {
				throw new Error('Callback Error')
			}
		})
		return $(this)
	}
})(jQuery)

GLOBAL.Search = {
	seach_submit: function() {
		var reg = /[^\w\.]/g,
			$keyword = $('#js_topSearch'),
			$hidden_cate = $('#hidden-searchCategory'),
			kw, cat_id
		kw = $.trim($keyword.val())
		kw = kw.replace(/\s+/g, '-')
		kw = kw.replace(/\'/g, '')
		kw = kw.toLowerCase()

		kw = kw.replace(/\b\w+\b/g, function(word) {
			return word
		})
		var languageType
		if(JS_LANG == 'es/'){
			languageType = 's/'
		}else if(JS_LANG == ''){ 
			languageType = 'cheap/' 
		}
		if(kw.length < 1) {
			$keyword.val('')
			$keyword.focus()
		}
		else{
			kw = kw.replace('%20', '-')
			cat_id = $hidden_cate.val()
			window.location.href = DOMAIN + languageType + kw + '/'
		}
		return false
	},
	
	searchAjax:{
		//参数
		param : {
			requestUrl : '/index.php?m=keyword_search',
			searchInput : $('#js_topSearch'),
			searchDrop : $('#js_topSearchDrop'),
			highlightClass : 'hover'
		},
		defaultWords : '',
		downCount : 0,
		//获取数据
		getData : function(keyword){
			var _this = this,
				keywordArr =  keyword.split(' ')
			//处理空格
			if (!keywordArr[1] && !keywordArr[keywordArr.length - 1]) {
				keyword = keywordArr[0]
			}
			if(keyword){
				$.get(_this.param.requestUrl,{
					keyword : keyword,
					dataType:'json',
					action: 'show_word'
				},function(data){
					_this.renderData(data)
				},'jsonp')
			}
			else{
				_this.setDefaultWords()
			}
		},
		//渲染数据到页面
		renderData : function(data){
			var _this = this
			//如果有搜索数据则显示下拉,并且填充数据
			if(data.res!='fail'){
				_this.toggleSearch(false,function(){
					_this.toggleSearch(true,function(){
						_this.param.searchDrop.empty().html(data.res)
						_this.switchWordsList()
					})
				})
			}
			//隐藏搜索,并设置默认关键词
			else{
				_this.setDefaultWords()
			}
		},
		//设置默认搜索词
		setDefaultWords : function(){
			var _this = this
			_this.toggleSearch(false,function(){
				_this.param.searchDrop.empty().html(_this.defaultWords)
			})
		},
		//显示/隐藏搜索框
		toggleSearch : function(flag,callback){
			var _this = this
			//显示
			if(flag){
				_this.param.searchDrop.show()
			}
			//隐藏
			else{
				_this.param.searchDrop.hide()
				_this.downCount = 0
				$(document).off('keydown')
			}
			//回调
			if(typeof callback == 'function' && callback){
				callback()
			}
		},
		//设置输入框关键词
		setKeyword : function(keyword,url){
			this.param.searchInput.val(keyword)
			//如果默认搜索词设置了跳转地址,则直接跳转
			if(url){
				window.location.href = url
			}
			else{
				$('#t_searchform').trigger('submit')
			}
			this.toggleSearch(false)
		},
		switchWordsList : function(){
			var _this = this,
				item = _this.param.searchDrop.find('li').not('.search-drop-title'),
				size = item.size()

			//按上下键时
			$(document).on('keydown', function(event) {
				//向上
				if (event.keyCode == 38) {
					_this.downCount--
					if (_this.downCount <= 0) {
						_this.downCount = size
					}
				}
				//向下
				else if (event.keyCode == 40) {
					_this.downCount++
					if (_this.downCount > size) {
						_this.downCount = 1
					}
				}
				//ESC关闭搜索框
				else if (event.keyCode == 27) {
					_this.toggleSearch(false)
					return
				}
				//其他按键
				else{
					return
				}

				var currentLi = item.eq(_this.downCount - 1),
					text  = currentLi.find('a').text()

				_this.param.searchDrop.find('a').removeClass(_this.param.highlightClass) //移出元素高亮样式
				currentLi.find('a').addClass(_this.param.highlightClass) //当前元素添加高亮样式
				_this.param.searchInput.val(text) //给搜索框赋值
			})
		},
		//事件绑定
		bindEvent : function(){
			var _this = this,
				ele = _this.param.searchInput
			//输入事件(用到了延时函数)
			ele.delayKeyup(function(event) {
				var ingoreKey = [37,38,39,40,116,27]//屏蔽的键值
				if(ingoreKey.indexOf(event.keyCode)!=-1){
					return
				}
				_this.getData(ele.val())
			},600)

			//点击空白区域隐藏
			$(document).on('click',function(e){
				var id = $(e.target).closest('form').attr('id')
				if(id != 't_searchform'){
					_this.toggleSearch(false)
				}
			})

			//点击输入框显示搜索下拉框
			ele.on('click',function(){
				_this.toggleSearch(true)
			})

			//操作关键词
			_this.param.searchDrop.on('mouseover','li',function(event) {
				var $this = $(this)
				_this.downCount = $this.index() + 1
				$this.siblings('li').find('a').removeClass(_this.param.highlightClass)
				$this.find('a').addClass(_this.param.highlightClass)

			}).on('click','li',function(event){
				var $this = $(this),
					url = $this.find('a').data('url'),
					text = $this.find('a').text()
				if(text){
					_this.setKeyword(text,url)
				}
			})

		},
		//初始化
		init : function(options){
			var _this = this,
				config = $.extend(true,_this.param, options)
			_this.defaultWords = _this.param.searchDrop.html()
			_this.bindEvent()
		}
	}
}

//*********************************************************************cosplay placeholder
GLOBAL.cosplayPlaceholder = function() {
	var $elem = $('.js_placeholder'),
		$searchInput = $elem.siblings('.t_seachInput')
	$elem.each(function(index, el) {
		var $target = $('#' + $(el).prop('for'))
		if ($target.length) {
			(function() {
				var $labelElem = $(el)
				$target.focus(function(event) {
					/* Act on the event */
					$labelElem.hide()
				})
				$target.blur(function(event) {
					/* Act on the event */

					if ($.trim($(this).val()).length < 1) {
						$labelElem.show()
					}
				})
			})(el)
		}
		if ($.trim($target.val()).length < 1) {
			$(el).show()
		}
	})
}

//更新mini购物车数据
GLOBAL.miniCart = function(callBack) {
	var _mini_cart = $('#mini_cart')
	if (JS_LANG)
	{
		var lang=JS_LANG.split('/')[0]
	}
	else
	{
		var lang=''
	}
	$.ajax({
		// url : '/temp/skin4/test.json',
		url: '/fun/index.php?act=cart_list&lang='+lang,
		type: 'GET',
		dataType: 'json'
	})
		.done(function(data) {
			var goodsItem = data.goods_list
			$('html,body').animate({
				scrollTop: 0
			}, 500) //回到顶部
			$('#shuliang').text(data.total.real_goods_count)
			if (data.total.real_goods_count == 0) {
				_mini_cart.empty()

				if ($('#null_minicart').length == 0) {
					_mini_cart.append('<i class="top-line"></i><p id="null_minicart" class="tc" style="line-height: 20px; color: #000; font-family: Verdana; font-size: 14px; padding-top: 20px;">'+jsLg.multi.lang_28+'</p>')
				}
			} else {
				// 更新底部购物车数量
				var goodsHtml = ''

				goodsHtml += '<i class="top-line"></i><ul><li class="title item"><h3>'+jsLg.multi.lang_29+'</h3></li>'
				for (var i = 0; i < goodsItem.length; i++) {
					if (i == 4) break
					var saleStatus = ''
					var canBuyStatus = '' //下架状态或缺货状态
					//判断预售还是非预售
					if (goodsItem[i].is_presale !== undefined) {
						switch (goodsItem[i].is_presale) {
						case 0:
							saleStatus = ''
							break
						case 1:
							saleStatus = '<span class="big_sale">PRESALE</span>'
							break
						}
					}
					//判断清仓还是非清仓
					if (goodsItem[i].is_clearance !== undefined) {
						switch (goodsItem[i].is_clearance) {
						case 0:
							saleStatus = (saleStatus === '') ? '' : saleStatus
							break
						case 1:
							saleStatus = '<span class="big_sale">CLEARANCE</span>'
							break
						}
					}
					//判断促销还是非促销
					if (goodsItem[i].is_sale !== undefined) {
						switch (goodsItem[i].is_sale) {
						case 0:
							saleStatus = (saleStatus === '') ? '' : saleStatus
							break
						case 1:
							saleStatus = '<span class="big_sale">SALE</span>'
							break
						}
					}
					//判断售完还是非售完
					if (goodsItem[i].is_soldout !== undefined) {
						switch (goodsItem[i].is_soldout) {
						case 0:
							saleStatus = (saleStatus === '') ? '' : saleStatus
							canBuyStatus = (canBuyStatus === '') ? '' : canBuyStatus
							break
						case 1:
							saleStatus = '<span class="big_sale">SOLD OUT</span>'
							canBuyStatus = 'canBuyThisPro'
							break
						}
					}
					//判断下架还是非下架
					if (goodsItem[i].is_outofstock !== undefined) {
						switch (goodsItem[i].is_outofstock) {
						case 0:
							saleStatus = (saleStatus === '') ? '' : saleStatus
							canBuyStatus = (canBuyStatus === '') ? '' : canBuyStatus
							break
						case 1:
							saleStatus = '<span class="big_sale">OUT OF STOCK</span>'
							canBuyStatus = 'canBuyThisPro'
							break
						}
					}

					goodsHtml += '<li class="item ' + canBuyStatus + ' clearfix"><a class="itempic" href="' + goodsItem[i].url_title + '"><img class="js_loadingimg" src="' + goodsItem[i].goods_thumb + '" height="60" width="60" title="' + goodsItem[i].goods_name + '">' + saleStatus + '</a>'
					goodsHtml += '<div class="itemtext">' +
											'<h5 class="tit"><a href="' + goodsItem[i].url_title + '">' + goodsItem[i].goods_name + '</a></h5>' +
											'<p class="li-tatal"><span class="my_shop_price fb" data-orgp="' + goodsItem[i].goods_price + '">' + goodsItem[i].goods_price + '</span>' +
											'<a href="javascript:;" class="itemDel js_del tag_c none" data-gid="' + goodsItem[i].rec_id + '" title="Delete"></a>' +
											'</p></div></li>'
				};

						 

				goodsHtml += '</ul>'
				goodsHtml += '<div id="nextCheckout" class="none pl10 pr10">'
				goodsHtml += '<p class="item-num-info"><strong>' + data.total.real_goods_count + '</strong> '+jsLg.multi.lang_58+'</p>'
				// goodsHtml += data.tags ? '<p class="tc pb20 f14">'+data.tags+'</p>': '';
				goodsHtml += '<p class="tc mb10"><a href="' + HTTPS_CART_DOMAIN + JS_LANG + 'm-flow-a-cart.htm" class="checkoutBtn">'+jsLg.multi.lang_57+'</a></p>'
				goodsHtml += '</div>'
							

				_mini_cart.html(goodsHtml)
				// GLOBAL.currency.change_html($.cookie('bizhong'), _mini_cart)
				var bizhong = getCookie('bizhong')||'USD'
				var bizhongIcon = getCookie('bizhong-icon')|| '$'
				calculateFn(bizhong, bizhongIcon)
				$('#nextCheckout').show()

				_mini_cart.on('mouseenter', 'li', function() {
					$(this).find('.itemDel').stop(false, true).fadeIn('fast')
				}).on('mouseleave', 'li', function() {
					$(this).find('.itemDel').stop(false, true).fadeOut('fast')
				})
			}
			if (callBack) {
				callBack()
			}
		})
}

//操作mini购物车
GLOBAL.controlMiniCart = function() {
	var _mini_cart = $('#mini_cart')
	var miniCartBlank = null
	if ($('#cart_list').length > 0 || $('#checkout_list').length > 0) { //如果是购物车页面、支付页面，不显示购物袋
		return false
	} else { //否则，显示购物袋
		$('#js_topCart').parent().hover(function() {
			if (miniCartBlank) {
				clearTimeout(miniCartBlank)
			}
			if (!_mini_cart.find('li').length && !_mini_cart.find('#null_minicart').length) {
				GLOBAL.miniCart()
			}

			_mini_cart.fadeIn('fast')
		}, function() {
			miniCartBlank = setTimeout(function() {
				_mini_cart.fadeOut('fast')
			}, 300)
		})
	}

	//删除一个普通商品
	_mini_cart.on('click', '.js_del', function() {
		var rec_id = $(this).data('gid')
		$.ajax({
			url: DOMAIN_CART + 'm-flow-a-drop_goods_top-id-' + rec_id + '.htm',
			type: 'GET',
			dataType: 'jsonp'
		})
			.done(function(result) {
				if ($('#cart_list').length) {
					location.reload()
				} else {
					if (result.status == 1) {
						GLOBAL.miniCart()
					} else {
						location.reload()
					}
				}
			})
	})
}

function _GET(name, str) {
	var pattern = new RegExp('[\?&#]' + name + '=([^&]+)', 'g')
	str = str || location.search
	var arr, match = ''

	while ((arr = pattern.exec(str)) !== null) {
		match = arr[1]
	}

	return match
}

//*********************************************************************登录相关
GLOBAL.login = {

	//检查信息（登录信息，购物车信息，记录商品详情页点击率,，每周特销时间）
	//action = 1 普通登录信息，购物车信息检查 ， action = 2登录信息，购物车信息，记录商品详情页点击率 ， action = 3 登录信息，购物车信息，每周特销时间
	info_check: function(action, callBack, callbackArg) {
		var that = this
		var url = '/fun/?act=info_check&action=' + action
		var query_url = window.location.href //当前页面URL地址
		var lkid = _GET('lkid', query_url) //获取URL地址中是否有lkid

		$.cookie('isloginInfo', 0, {
			expires: 0,
			path: '/',
			domain: COOKIESDIAMON
		}) //清空记录登录的cookie值

		if (lkid) {
			var referrer_url = encodeURIComponent(document.referrer) //来源URL地址
			url += '&lkid=' + lkid + '&referrer_url=' + referrer_url
		}

		if (action == 2) {
			itemId = $('#hidden-goodsId').val()
			url += '&itemId=' + itemId
		}

		$.ajax({
			type: 'GET',
			url: url,
			dataType: 'json',
			cache: false,
			data: {
				location_url: location.href
			},
			success: function(msg) {
				//(msg.is_lable == 1 && GLOBAL.isHomePage()) && $("#js_conponBtn").show();

				$('#shuliang').html(msg.cart_items)

				if (msg.firstname) {
					$.cookie('isloginInfo', 1, {
						expires: 0,
						path: '/',
						domain: COOKIESDIAMON
					}) //登录了,写入一个cookie 记录是登录了

					that.successLogin(msg)
					//登录成功有回调函数，执行回调函数

				} else {
					$.cookie('isloginInfo', 0, {
						expires: 0,
						path: '/',
						domain: COOKIESDIAMON
					})
					$('.js_loginShow').show()
								 
				}

				if (action == 3) {
					$('#loadtime').html(msg.LeftTime)
				}
				if (callBack) {
					arguments.length > 2 ? callBack(msg, callbackArg) : callBack(msg)
				}
			}
		})
	},
	successLogin: function(data) {
		var msg = data
		//var $topLogin = $("#js_topLogin"),
		//$topAcc = $("#js_topAcc");

		$('#header .sign_box').hide()
		$('#header .user_box').show()
		$('#header .user_box .nam').text('Hi, '+msg.firstname)

		//$topLogin.html('<p class="menu">' + 'Hi ' + msg.firstname + '</p>');
		//$topAcc.show();

		$('#Js_topFavorites').attr('href', $('#Js_topFavorites').data('url'))

		if (msg.isShowPoint && $.trim(msg.isShowPoint) == 1) {

			var gotPointHtml = $('<div class="loginPoints fr" id="js_topGotPoints"><img src="' + JS_IMG_URL + 'images/domeimg/poinimg.gif"/></div>')
			gotPointHtml.click(function() {
				$.get('/fun/?act=check_login_point', function(data) {
					GLOBAL.PopObj.alert({
						msg: jsLg.multi.lang_23.replace(/\{{\w+}}/,data.getPoint)
					})
				}, 'json')

				gotPointHtml.hide()
			})
			$('#js_topCart').parent().append(gotPointHtml)
		}
	}
};

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

	setCookie('bizhong', currency)
	setCookie('bizhong-icon', currencyIcon)
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
	setCookie('bizhong-icon', currencyIcon)

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

$('#t_searchform').submit(function(e) {
	var str = $('#js_topSearch').val()
	if (str != '' && str != null) {
		var his = $.cookie('search_history')
		if (his == '' || his == null) {
			his = new Array()
		} else {
			his = his.split(',')
		}
		his.push(str)
		if (his.length > 3) {
			his.shift()
		}
		his = his.join(',')
		$.cookie('search_history', his, {expires: 30, path: '/', domain: COOKIESDIAMON })
	}
	e.preventDefault()
	GLOBAL.Search.seach_submit()
})

GLOBAL.Search.searchAjax.init()

GLOBAL.cosplayPlaceholder()

GLOBAL.controlMiniCart()

//新版用户中心下拉框
$('#header .user_box').hover(function() {
	$(this).find('ul').show()
}, function() {
	$(this).find('ul').hide()
})

//判断是否登录了
GLOBAL.login.info_check(1,function(res){
	// if(res && res.left_time > 0){
	// 	var $self = $('.js_top_banner_time')
	// 	$self.data('left-time', res.left_time)
	// 	seckill_timer = setInterval(countDown, 1000)
	// 	function countDown(){
	// 		var leftTime = parseInt($self.data('leftTime')),
	// 			seconds, minutes, hours, days,
	// 			CDay, CHour, CMinute, CSecond

	// 		if (!isNaN(leftTime) && leftTime >= 0) {
	// 			seconds = leftTime
	// 			minutes = Math.floor(seconds / 60)
	// 			hours = Math.floor(minutes / 60)
	// 			days = Math.floor(hours / 24)
	// 			CDay = days
	// 			// CHour = hours % 24;
	// 			CHour = hours
	// 			CMinute = minutes % 60
	// 			CSecond = Math.floor(seconds % 60)

	// 			if (CDay < 10) {
	// 				CDay = '0' + CDay
	// 			}
	// 			if (CHour < 10) {
	// 				CHour = '0' + CHour
	// 			}
	// 			if (CMinute < 10) {
	// 				CMinute = '0' + CMinute
	// 			}
	// 			if (CSecond < 10) {
	// 				CSecond = '0' + CSecond
	// 			}
	// 			$self.data('left-time', leftTime - 1)
	// 			$self.html('<span class="time-label" >'+jsLg.countDown_banner+':</span><span class="time-number">'+CHour+':'+CMinute+':'+CSecond+'</span>')
	// 			if(leftTime == 0){
	// 				clearTimeout(seckill_timer)
	// 				$self.html('<span class="time-label" >'+jsLg.countDown_banner+':</span><span class="time-number">00:00:00</span>')
	// 			}
	// 		} else {
	// 			$self.html('<span class="time-label" >'+jsLg.countDown_banner+':</span><span class="time-number">00:00:00</span>')
	// 		}
	// 	}
	// }
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
