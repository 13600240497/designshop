$(function () {

	// 分享功能 ，参考 https://github.com/okmttdhr/sharer.npm.js/blob/master/sharer.js
	$('.test').attr({ 'data-title': document.title, 'data-url': window.location.href });
	/**
 * @preserve
 * Sharer.js
 *
 * @description Create your own social share buttons
 * @version 0.3.3
 * @author Ellison Leao <ellisonleao@gmail.com>
 * @license GPLv3
 *
 */


	//分享完回调函数
	function shareFn (Ysharer) {
		// key=726502f1a5f0d7bb81603eb52084ef2c&content={"id":100288,"integral_desc":"Geshop","integral_effective_end":1541779200000,"integral_effective_start":1541520000000,"share_integral":20,"share_type":"google"}
		var queryStr = "#geshop_share_" + Ysharer;
		var datas = $(queryStr).val();
		var key = datas.split("&")[0].split("=")[1];
		var content = JSON.parse(datas.split("&")[1].split("=")[1]);
		var contentList = content;
		var param = {
			key: key,
			data: JSON.stringify(contentList)
		};
		var data = {
			content: param
		}
		var url = GLOBAL.DOMAIN_MAIN + '/activity/geshop-share-point';
		Ajax(url, data)
	}

	//分享请求接口
	function Ajax (url, data) {
		$.ajax({
			url: url,
			type: 'POST',
			data: data,
			success: function (res) {
				// header  User-Agent weitengfei002  请求不需要加,模拟请求添加
				// {"status":1,"msg":"Shared successfully!","data":[]}
				if (res.status == 0) {
					layer.msg(res.msg);
				} else {
					if (res.data && res.data.redirectUrl) {
						window.location = res.data.redirectUrl;
					}
					layer.msg(res.msg);
				}
			},
			error: function (e) {
				layer.msg("服务器请求失败");
			}
		});
	}

	function detectWindow (frame) {
		return new Promise(function (resolve) {
			var handler = setInterval(function () {
				// eslint-disable-line
				if (frame.closed) {
					clearInterval(handler);
					resolve({
						closed: true
					});
				}
			}, 1000);
		});
	};



	// 2018-11-6 注释以前分享功能
	; (function (window, document) {
		'use strict';
    /**
     * @constructor
     */
		var Sharer = function (elem) {
			this.elem = elem;
		};

    /**
     *  @function init
     *  @description bind the events for multiple sharer elements
     *  @returns {Empty}
     */
		Sharer.init = function () {
			var fjs = document.getElementsByTagName('script')[0];
			var js = document.createElement('script');
			js.id = 'facebook-jssdk';
			js.src = 'https://connect.facebook.net/en_US/sdk.js';
			fjs.parentNode.insertBefore(js, fjs);
			var elems = document.querySelectorAll('[data-sharer]'),
				i,
				l = elems.length;
			for (i = 0; i < l; i++) {
				elems[i].addEventListener('click', Sharer.add);
			}
		};

    /**
     *  @function add
     *  @description bind the share event for a single dom element
     *  @returns {Empty}
     */
		Sharer.add = function (elem) {
			var target = elem.currentTarget || elem.srcElement;
			var sharer = new Sharer(target);
			sharer.share();
		};

		// instance methods
		Sharer.prototype = {
			constructor: Sharer,




			/**
			 *  @function getValue
			 *  @description Helper to get the attribute of a DOM element
			 *  @param {String} attr DOM element attribute
			 *  @param {String} defaultValue value to use for attr
			 *  @returns {String|Empty} returns the attr value or empty string
			 */
			getValue: function (attr, defaultValue) {
				defaultValue = (defaultValue === undefined) ? '' : defaultValue;
				var val = this.elem.getAttribute('data-' + attr);
				return (val === undefined || val === null) ? defaultValue : val;
			},

			/**
			 * @event share
			 * @description Main share event. Will pop a window or redirect to a link
			 * based on the data-sharer attribute.
			 */

			share: function () {
				var Ysharer = this.getValue('sharer');
				var sharer = this.getValue('sharer').toLowerCase();
				var sharers = {
					fb: {
						shareUrl: 'https://www.facebook.com/sharer/sharer.php',
						type: "fb",
						params: {
							u: this.getValue('url'),

							//添加
							// t: this.getValue('title'),
							// url: this.getValue('url'),
							// title: this.getValue('title'),
							// description: this.getValue('description'),
							// image: this.getValue('image')
						}
					},
					google: {
						shareUrl: 'https://plus.google.com/share',
						type: "google",
						params: {
							url: this.getValue('url'),
							// url:'https://www.gearbest.com/activity-11.11-sale.html',

							t: this.getValue('title'),
							//添加
							title: this.getValue('title'),
							description: this.getValue('description'),
							// image: this.getValue('image'),
							imageUrl: this.getValue('image'),
						}
					},
					twitter: {
						shareUrl: 'https://twitter.com/intent/tweet/',
						type: "twitter",
						params: {
							text: this.getValue('title') + ' ' + this.getValue('description') + ' ' ,
							url: this.getValue('url'),
							// url:"https://www.gearbest.com/promotion-TOP-SELLERS-GEAR-special-2814.html",
							// hashtags: this.getValue('description'),
							image: this.getValue('image'),
							via: this.getValue('via'),
							imageUrl: this.getValue('image'),
						}
					},
					telegram: {
						shareUrl: 'https://telegram.me/share/url',
						type: "telegram",
						params: {
							// text: this.getValue('title') + ' ' + this.getValue('url'),

							//添加
							url: this.getValue('url'),
							// url:"https://www.gearbest.com/promotion-TOP-SELLERS-GEAR-special-2814.html",
							text:this.getValue('description'),
							imageUrl: this.getValue("image")
						},
						// isLink: true
					},
					reddit: {
						shareUrl: 'https://www.reddit.com/submit',
						type: "reddit",
						params: {
							url: this.getValue('url'),

							//添加
							title: this.getValue('title'),
							// description: this.getValue('description'),
							// image: this.getValue('image'),
							imageUrl: this.getValue('image'),

						}
					},
					vk: {
						shareUrl: 'http://vk.com/share.php',
						type: "vk",
						params: {
							redir:1,
							url: this.getValue('url'),
							title: this.getValue('title') + ' ' + this.getValue('description'),
							description: this.getValue('description'),
							image: this.getValue('image'),
							imageUrl: this.getValue('image'),

						}
					},

				},
					s = sharers[sharer];

				// custom popups sizes
				if (s) {
					s.width = this.getValue('width');
					s.height = this.getValue('height');

				}
				return s !== undefined ? this.urlSharer(s, Ysharer) : false;
			},
			/**
			 * @event urlSharer
			 * @param {Object} sharer
			 */
			urlSharer: function (sharer, Ysharer) {
				var p = sharer.params || {},
					keys = Object.keys(p),
					i,
					str = keys.length > 0 ? '?' : '';
				for (i = 0; i < keys.length; i++) {
					if (str !== '?') {
						str += '&';
					}
					if (p[keys[i]]) {
						str += keys[i] + '=' + encodeURIComponent(p[keys[i]]);
					}
				}
				sharer.shareUrl += str;
				GESHOP.isLogin().then(function (res) {
					if (!res) {
						window.location = 'https://login.gearbest.com/m-users-a-sign.htm?type=1&ref=' + window.location;
					} else {
						if (sharer.type == "fb") {
							// var fjs = document.getElementsByTagName('script')[0];
							// var js = document.createElement('script');
							// js.id = 'facebook-jssdk';
							// js.src = 'https://connect.facebook.net/en_US/sdk.js';
							// fjs.parentNode.insertBefore(js, fjs);
							// window.fbAsyncInit = function () {
								var appId = $("meta[property='fb:app_id']").attr("content");  //900125666754558
								FB.init({ appId: appId, status: true, cookie: true, oauth: true, xfbml: true, version: 'v2.7' }); // eslint-disable-line
								setTimeout(function(){
									FB.ui({
										method: 'share',
										href: sharer.params.u,
										// href:'https://www.gearbest.com/promotion/release/dada31.html',
										mobile_iframe: false //是否是手机
									}, function (res) {
										if (res !== undefined) {
											// layer.msg("success");
											//分享回调
											shareFn(Ysharer);
										} else {
											// layer.msg("fail");
										}
									})
								},200)
							// }
						} else {
							var popWidth = sharer.width || 600,
								popHeight = sharer.height || 480,
								left = window.innerWidth / 2 - popWidth / 2 + window.screenX,
								top = window.innerHeight / 2 - popHeight / 2 + window.screenY,
								popParams = 'scrollbars=no, width=' + popWidth + ', height=' + popHeight + ', top=' + top + ', left=' + left,
								newWindow = window.open(sharer.shareUrl, '', popParams);
							//异步获取是否分享成功
							detectWindow(newWindow).then(function (data) {
								if (data.closed) {
									// layer.msg("success");
									shareFn(Ysharer);
								} else {
									// layer.msg("fail");
								}
							})
							if (window.focus) {
								newWindow.focus();
							}

						}
					}
				})


			}
		};

		// adding sharer events on domcontentload
		if (document.readyState === 'complete' || document.readyState !== 'loading') {
			var staticDomain = $('.U000142').attr("data-staticDomain");
			var isEditEnv = $('.U000142').attr("data-isEditEnv");
			var shareArr = $("#shareChannel") && $("#shareChannel").val() ? $("#shareChannel").val().split(",") : "";
			var share_area = $(".share-area");
			if (!shareArr) {
				share_area.css({ "padding": "0" })
			}
			var share_title = $("meta[property='og:title']").attr("content");
			var share_url = $("meta[property='og:url']").attr("content");
			var share_image = $("meta[property='og:image']").attr("content");
			var share_description = $("meta[property='og:description']").attr("content");
			var share_icon_Obj = {
				'fb': 'gs-icon-F',
				'google': 'gs-icon-google',
				'reddit': 'gs-icon-reddit',
				'telegram': 'gs-icon-telegram',
				'twitter': 'gs-icon-Twitter',
				'vk': 'gs-icon-VK'
			}

			if (share_area.length) {
				var html = ""
				for (var i = 0; i < shareArr.length; i++) {
					var imgUrl = staticDomain + '/resources/images/gb/u000142/' + shareArr[i] + '.png';
					var iconClass = 'gs-iconfont ' + share_icon_Obj[shareArr[i]];
					// html+='<div class="share-box"><a href="javascript:;" class="share-icon gb-share js-btnIntroShareFB" ' + ' data-title="'+ share_title +'" data-url="' + share_url +'" data-image="' + share_image + '" data-description="'+ share_description +'"  data-sharer="'+shareArr[i]+'" title="'+shareArr[i]+'"><img style="width:20px;height:20px;" src="'+ imgUrl +'"/></a></div>'
					html += '<div class="share-box"><a href="javascript:;" class="share-icon gb-share js-btnIntroShareFB" ' + ' data-title="' + share_title + '" data-url="' + share_url + '" data-image="' + share_image + '" data-description="' + share_description + '"  data-sharer="' + shareArr[i] + '" title="' + shareArr[i] + '"><i class="' + iconClass + '"></i></a></div>'
				}
				share_area.html(html);
			}
			Sharer.init();
		} else {
			var staticDomain = $('.U000142').attr("data-staticDomain");
			var isEditEnv = $('.U000142').attr("data-isEditEnv");
			var shareArr = $("#shareChannel").val() ? $("#shareChannel").val().split(",") : "";
			var share_area = $(".share-area");
			if (!shareArr) {
				share_area.css({ "padding": "0" })
			}
			var share_title = $("meta[property='og:title']").attr("content");
			var share_url = $("meta[property='og:url']").attr("content");
			var share_image = $("meta[property='og:image']").attr("content");
			var share_description = $("meta[property='og:description']").attr("content");
			if (share_area.length) {
				var html = ""
				for (var i = 0; i < shareArr.length; i++) {
					var imgUrl = staticDomain + '/resources/images/gb/u000142/' + shareArr[i] + '.png';
					html += '<div class="share-box"><a href="javascript:;" class="share-icon gb-share js-btnIntroShareFB" ' + ' data-title="' + share_title + '" data-url="' + share_url + '" data-image="' + share_image + '" data-description="' + share_description + '"  data-sharer="' + shareArr[i] + '" title="' + shareArr[i] + '"><img style="width:20px;height:20px;" src="' + imgUrl + '"/></a></div>'
				}
				share_area.html(html);
			}
			document.addEventListener('DOMContentLoaded', Sharer.init);
		}

		// turbolinks compatibility
		window.addEventListener('page:load', Sharer.init);

		// exporting sharer for external usage
		window.Sharer = Sharer;

	})(window, document);



	// 滚动功能+++++++++++++++++++++++++++++++++++
	var goods_list_box = $('div[attr="nav_flag"]'),               //商品列表 DOM
		goods_h_arr = [],                                         //商品列表高度集合
		is_move = false,                                          //用来限制点击滚动时触发 scroll 事件的 flag
		nav_list = $('.component-nav-vertical-item'),             //导航菜单 DOM
		nav_ul = $('#nav-vertical-ul'),                           //导航菜单容器
		nav_ul_w = nav_ul.width(),              //导航菜单宽度
		active_index = 0,                           //当前点亮导航菜单的 下标
		nav_item_w_arr = [],                    //导航菜单宽度集合
		nav_item_average_length,                //导航菜单平均长度
		first_offset_page_h,                    //第一个列表距离页面顶部的距离
		scrollContainer,                          //滚动的容器
		scrollContainerE,                         //滚动的容器(监听)
		flag;                                   //导航菜单需要横向滚动的 下标 flag

	var is_edit = nav_ul.data('isedit')         //是否处于编辑模式
	//获取 滚动的容器
	scrollContainer = is_edit == 1 ? $('.design-right') : $(document)
	scrollContainerE = is_edit == 1 ? $('.design-right') : $("body,html")

	if (!is_edit) {  // 处于预览模式添加不同样式
		$('.component-vertical-nav').addClass('component-vertical-nav-online')
	}

	//获取 列表距离页面顶端的高度
	var top_h = 0
	function getOffsetH (dom, oldH) {
		if (!dom) {
			return
		}

		oldH = oldH ? oldH : 0;

		top_h = dom.offsetTop + oldH;

		if (dom.offsetParent) {
			getOffsetH(dom.offsetParent, top_h)
		}

		return top_h
	}

	//获取列表距离页面顶部的距离
	first_offset_page_h = getOffsetH(goods_list_box.get(0));

	//获取 商品列表高度集合
	goods_list_box.each(function (i, n) {
		var totalH = getOffsetH(n)

		goods_h_arr.push({
			id: $(n).data('id'),
			height: totalH
		});
	});

	//导航菜单 点击 滚动逻辑
	nav_list.click(function () {
		var $this = $(this), scrollS;

		if ($this.hasClass('to-top')) {
			$this = nav_list
				.removeClass('vertical-current')
				.eq(0)
				.addClass('vertical-current');

			scrollS = 0;
		} else {
			$this
				.siblings()
				.removeClass('vertical-current')
				.end()
				.addClass('vertical-current');

			var id = Number($(this).data('id'));
			var length = goods_h_arr.length;
			var scrollS = 0;

			for (var index = 0; index < length; index++) {
				if (id === Number(goods_h_arr[index].id)) {
					scrollS = goods_h_arr[index].height;
				}
			}
		}

		is_move = true;

		scrollContainerE.animate({
			'scrollTop': scrollS
		}, 500, function () {
			//防止触发 scroll 事件
			setTimeout(function () {
				if (scrollS == 0) {
					$('.component-vertical-nav').hide();
				}
				is_move = false;
			}, 60);
		});
	});

	//最上面一屏幕和最下面半屏幕不显示导航
	var documentH = document.documentElement.clientHeight || document.body.clientHeight || window.innerHeight;
	var bodyH = document.getElementsByTagName("body")[0].offsetHeight;
	//监听 导航菜单 滚动逻辑
	scrollContainer.scroll(scrollFn)

	//监听 导航菜单 滚动逻辑
	function scrollFn () {
		var sT = $(this).scrollTop(); //滚动条滚动距离

		if (is_move) {
			return
		}
		if (goods_h_arr.length == 1) { // 少于一个
			active_index = 0;
		} else {  // 多余一个
			for (var i = 0, len = goods_h_arr.length; i < len; i++) {
				var nextHeight = goods_h_arr[i + 2] ? sT < goods_h_arr[i + 2].height : true
				if (sT < goods_h_arr[1].height) {
					active_index = 0
					break
				} else if (goods_h_arr[i + 1] && sT > goods_h_arr[i + 1].height && nextHeight) {
					active_index = i + 1
					break
				}
			}
		}

		if (!is_edit) {
			// if(sT > documentH && sT < (bodyH - 1.5*documentH) ){
			// 	$('.component-vertical-nav').show();
			// }else{
			// 	$('.component-vertical-nav').hide();
			// }
			// if(goods_h_arr.length==1){
			//     if(sT > first_offset_page_h){
			//         $('.component-vertical-nav').show();
			//     }else{
			// 	    $('.component-vertical-nav').hide();
			//     }
			// }else{
			//     if (sT > first_offset_page_h ) {
			//         $('.component-vertical-nav').show();
			//     } else {
			//         $('.component-vertical-nav').hide();
			//     }
			// }

			var active_goods_item = nav_list.filter('[data-id=' + goods_h_arr[active_index].id + ']')

			active_goods_item
				.siblings()
				.removeClass('vertical-current')
				.end()
				.addClass('vertical-current');

		}

	}
});
