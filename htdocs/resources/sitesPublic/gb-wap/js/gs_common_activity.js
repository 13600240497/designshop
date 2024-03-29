/*
 * @Author: wuxingtao
 * @Date: 2018-10-10 17:03:52
 * @Last Modified by: wuxingtao
 * @Last Modified time: 2018-11-15 19:38:28
 */



(function ($) {

	var GS = (function (my) {
		/*
		* element jq class selector
		*/
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
			if (GESHOP_OFFLINE_TO_URL[GESHOP_OFFLINE_TO_URL.length - 1] === '/') {
				jumpLink = GESHOP_OFFLINE_TO_URL.substr(0, GESHOP_OFFLINE_TO_URL.length - 1)
			}

			var activityJumpLink = jumpLink + window.location.search
			function callbackEvent () {
				var linkTextInit = '该活动已结束,<em id="get_opcode" class="gs-activity-secondDown">5</em>秒钟后将跳转至'
				var linkText = GESHOP_OFFLINE_TIPS_TEXT || linkTextInit
				// var linkTextOther = '' ||"<span class='link-color'>网站首页</span>,<span class='more-tip gs-block'>更多精彩等你发现~</span>"
				var linkNow = GESHOP_OFFLINE_BUTTON_TEXT || '马上跳转'
				var content = ''
				content += '<div class=\'gs-activity-alert\'>\n';
				content += '  <div class="gs-activity-content">'
				content += '<div><span>' + linkText + '</span></div>'
				content += '<\/div>\n';
				content += '  <div class="gs-btn-area gs-block">\n';
				content += '    <a href="' + activityJumpLink + '" class="btn btn-trans">' + linkNow + '<\/a>\n';
				content += '  <\/div>\n';
				content += '<\/div>\n';
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
				gs_commoncss: GESHOP_STATIC + '/resources/sitesPublic/gb-pc/css/gs_common_activity.css?version=2018080801'
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

		return my
	}({}))

	window.GESHOP_UTIL = GS;

	/* document ready  */
	$(function () {
		GS.activityEndAlertCheck()
	})

	$(function () {
		/**
		 * 根据当前时间判断是在设置的哪个时间段对页面进行样式设置
		 * @param { int } style_type - 1 系统设置 2 自定义设置
		 */
		if (typeof GESHOP_MULTI_TIME_STYLE != 'undefined' && GESHOP_MULTI_TIME_STYLE.length) {
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
})($)



/* gb css */
// if (typeof GESHOP_STATIC == 'string') {
// 	loadCss(GESHOP_STATIC + '/resources/sites/gb/css/icon.css?version=2018080801');
// }


/* GB common start */

/**
 * GBTimer
 * 倒计时,包含按钮文案,商品售空状态切换
 */
var GlobalLang;
function GBTimer (GESHOP_LANGUAGES) {
	var GlobalLanguages = GESHOP_LANGUAGES ? GESHOP_LANGUAGES : {};
	GlobalLang = {
		buy_now: GlobalLanguages.buy_now || 'Buy Now',
		deals_ended: GlobalLanguages.deals_ended || 'Deals Ended',
		coming_soon: GlobalLanguages.coming_soon || 'Coming soon',
		sold_out: GlobalLanguages.sold_out || 'Sold Out',
		starts_in: GlobalLanguages.starts_in || 'Starts In',
		ends_in: GlobalLanguages.ends_in || 'Ends In',
	};
}



GBTimer.prototype.add = function (jqselect, option) {
	var $target = $(jqselect);
	var _this = this;
	var options = Object.assign({
		startText: GlobalLang.starts_in,
		ingText: GlobalLang.ends_in, //进行中描述,
		endText: GlobalLang.deals_ended, //结束描述,
		buyNowText: GlobalLang.buy_now,
		onStart: $.noop, // 计时开始前回调函数
		onEnd: $.noop, // 计时结束后回调函数
	}, option)
	$target.each(function () {
		var $self = $(this);
		var startTime = parseInt($self.attr('data-begin'));
		var endTime = parseInt($self.attr('data-end'));
		var $li = $self.closest('li');
		if (!(startTime && endTime)) {
			$target = $target.not($self);
			if ($self.attr('data-begin') == '0' && $self.attr('data-end') == '0') {
				$li.find('.buyLink').text(options.endText);
				$li.addClass('good_dealEnded')
			}
			return;
		}

		_this.initStatus($self, options);
		var leftTime = parseInt($self.attr('data-leftTime'));
		var dataStatus = parseInt($self.attr('data-status'));
		var statusText = dataStatus == '0' ? options.startText : options.ingText;
		// var statusText = dataStatus == '0' ? options.startText : dataStatus == '1' ? options.ingText : options.endText;
		// erverTime = endTime - nowTime > 0 ? endTime - nowTime : 0;
		// leftTime = parseInt($self.attr('data-leftTime')) ? parseInt($self.attr('data-leftTime')) : serverTime;
		var seconds, minutes, hours, days, CDay, CHour, CMinute, CSecond;
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

			$self.attr('data-leftTime', leftTime - 1);
			//渲染文件格式
			if(options.renderFn){
				options.renderFn({
					dataStatus:dataStatus,
					statusText:statusText,
					timeParam:{
						CDay:CDay,
						CHour:CHour,
						CMinute:CMinute,
						CSecond:CSecond
					}
				})
			}else{
				$self.html('<span>' + statusText + ': ' + CDay + ':' + CHour + ':' + CMinute + ':' + CSecond + '</span>')
			}
		} else {
			$target = $target.not($self);

			if (dataStatus !== 2) {
				_this.initStatus($self, options);
			} else {
				if(options.renderFn){
					options.renderFn({
						dataStatus:dataStatus,
						statusText:statusText,
						timeParam:{
							CDay:'00',
							CHour:'00',
							CMinute:'00',
							CSecond:'00'
						}
					})
				}else{
					$self.html('<span>' + statusText + ': ' + '00:00:00:00</span>')
				}

				if (options.onEnd) {
					options.onEnd();
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
	var $target = $(target);
	$target.each(function () {
		var $self = $(this);
		var $li = $self.closest('li');
		var startTime = parseInt($self.attr('data-begin'));
		var endTime = parseInt($self.attr('data-end'));
		var nowTime = (new Date().getTime()) / 1000;
		var serverTime;
		var leftTime;
		var status = 0;	//['0未开始', '1已开始', '2已结束']
		var statusText = '';	//倒计时状态文案
		var currentBtnText = $li.find('.buyLink').text();
		var left_number = parseInt($self.attr('data-stock'));	//剩余库存

		/* is sold out check */
		var isSoldOut = $li.hasClass('good_soldOut');
		var buyNowText = options.buyNowText || GlobalLang.buy_now;
		var buyTextArr = [GlobalLang.coming_soon, buyNowText, GlobalLang.deals_ended];
		if (startTime > nowTime) {
			leftTime = startTime - nowTime;
			statusText = options.startText;
		} else if (startTime <= nowTime && endTime > nowTime) {
			status = 1;
			statusText = options.endText;
			leftTime = endTime - nowTime;
		} else if (endTime < nowTime) {
			status = 2;
			leftTime = -1;
		}

		$self.attr({ 'data-leftTime': leftTime, 'data-status': status });

		/* 是否售完 */
		if (status == 1 && left_number == 0) {
			if (currentBtnText != GlobalLang.sold_out) {
				$li.find('.buyLink').text(GlobalLang.sold_out);
			}
		} else if (currentBtnText != buyTextArr[status]) {
			$li.find('.buyLink').text(buyTextArr[status]);
		}

		if (status !== 1) {
			$li.addClass('good_dealEnded');
		} else {
			$li.removeClass('good_dealEnded');
		}
		if (status == 1 && left_number == 0) {
			$li.addClass('good_soldOut');
		} else {
			$li.removeClass('good_soldOut');
		}

	})
}

window.GBTimer = GBTimer;

; $(function () {
	/**
	 * 添加购物车
	 * callback 购物车回调
	 * GESHOP.appSdk (ios,android端sdk)
	 */
	$('.js-addCart').on('click', function () {
		var $this = $(this);
		var goodsSn = $this.attr('data-sku');
		var warehouseCode = $this.attr('data-warehousecode');
		var goodsType = $this.attr('data-type');
		var qty = $this.attr('data-qty');
		var imgSrc = $this.attr('data-img');
		if (GESHOP.appSdk && GESHOP.appSdk.IS_APP === 1 && GESHOP.appSdk.actCartChanged) {
			GESHOP.addToCart({
				reqData: [{
					goodsSn: goodsSn,
					qty: qty,
					warehouseCode: warehouseCode,
					goodsType: goodsType
				}],
				callback: function () {
					GESHOP.appSdk.actCartChanged();
				}
			});
		} else {
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
			});
		}

	})
});

; function GBViewMore (element) {
	$(element).find(".view-more-btn").on("click", function () {
		var $this = $(this);
		// var $ul = $this.parent().siblings('.gb-list-default').eq(0);
		var $ul = $this.parents(".geshop-component-box").find('.gb-list-default').eq(0);
		var shownum = $ul.data('shownum');
		$ul.children("li").each(function (index, item) {
			if (index > shownum) {
				if (index == $ul.children().length - 1) {
					if (!$(item).hasClass('isHide')) { // 收缩
						$('html').scrollTop($ul.data('scrolltop'))
						$this.children('span').text($this.data('moretext'))
						$this.children('i').removeClass('gs-iconfont gs-icon-up').addClass('gs-iconfont gs-icon-down1')
					} else { // 展开
						$ul.data('scrolltop', $('html').scrollTop())
						$this.children('span').text($this.data('lesstext'))
						$this.children('i').removeClass('gs-iconfont gs-icon-down1').addClass('gs-iconfont gs-icon-up')
					}
				}
				$(item).toggleClass('isHide');
			}
		})
	})
};
window.GBViewMore = GBViewMore;


// 服务标app打开链接
; (function () {
	$(".gs-goods-label p[data-label-link]").click(function (e) {
		e.stopPropagation();
		var link = $(this).attr("data-label-link");
		if (GESHOP.appSdk && GESHOP.appSdk.IS_APP === 1 && GESHOP.appSdk.link) {
			GESHOP.appSdk.link({ appUrl: link });
		} else {
			window.location = link;
		}
		return false;
	})
})();

// M端置顶
; (function () {
	//生成置顶按钮
	var gb_go_top = '<div class="gb_go_top" style="position fixed!important;bottom:2rem;right:0.266rem;width:1.06rem;height:1.06rem;line-height:1.06rem;background:rgba(0,0,0,0.3);border-radius:50%;text-align:center;z-index:-1000;opacity:0">' +
		'<i class="gs-iconfont gs-icon-upxiangshang" style="color:#FFFFFF;font-size:0.56rem"></i>  </div>';
	$("body").append(gb_go_top);
	setTimeout(function () {
		$(".gb_go_top").css({ "position": "fixed" })
	}, 100);
	//最上面一屏幕和最下面半屏幕不显示导航
	var documentH = document.documentElement.clientHeight || document.body.clientHeight || window.innerHeight;
	var bodyH = document.getElementsByTagName("body")[0].offsetHeight;
	window.onscroll = function () {
		var osTop = document.documentElement.scrollTop || document.body.scrollTop;
		if (osTop > documentH) {
			$(".gb_go_top").css({ "z-index": "1000", "opacity": "1" });
		} else {
			$(".gb_go_top").css({ "z-index": "-1000", "opacity": "0" });
		}

		//页面间导航 顶部和底部隐藏
		if (osTop > documentH && osTop < (bodyH - 1.5 * documentH)) {
			$(".geshop-page-navigator,.side-btn").css({ "z-index": "1001", "opacity": "1" })
		} else {
			$(".geshop-page-navigator,.side-btn").css({ "z-index": "-1001", "opacity": "0" })
		}
	}


	//  M端置顶置顶
	function goTop () {
		var timer = null; //定义一个定时器
		var isTop = true; //定义一个布尔值，用于判断是否到达顶部
		$(".gb_go_top")[0].onclick = function () {    //回到顶部按钮点击事件
			//设置一个定时器
			timer = setInterval(function () {
				//获取滚动条的滚动高度
				var osTop = document.documentElement.scrollTop || document.body.scrollTop;
				//用于设置速度差，产生缓动的效果
				var speed = Math.floor(-osTop / 6);
				document.documentElement.scrollTop = document.body.scrollTop = osTop + speed;
				isTop = true;  //用于阻止滚动事件清除定时器
				if (osTop == 0) {
					clearInterval(timer);
				}
			}, 30);
		}
	}
	goTop();


})();









window.onload = function () {
	function GBMShare () {
		this.isHide = true;
		this.staticDomain = typeof GESHOP_STATIC == "undefined" ? "" : GESHOP_STATIC;
		this.elem = ""
	};



	//分享完回调函数
	GBMShare.prototype.shareFn = function (Ysharer) {
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
		this.Ajax(url, data)
	}

	//分享请求接口
	GBMShare.prototype.Ajax = function (url, data) {
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

	GBMShare.prototype.detectWindow = function (frame) {
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

	GBMShare.prototype.init = function () {
		this.creatShareBtn();
	}
	//创建分享按钮dom
	GBMShare.prototype.creatShareBtn = function () {
		var that = this;
		var imgUrl = that.staticDomain + '/resources/images/icon/share.png';
		var gb_share_btn = '<div class="gb_share_btn" style="position fixed!important;bottom:3.2rem;right:0.266rem;width:1.06rem;height:1.06rem;background:rgba(0,0,0,0.3);border-radius:50%;z-index:200000;">' +
			'<img style="width:0.56rem;height:0.56rem;margin-top:0.25rem;margin-left:0.25rem;" src="' + imgUrl + '" />  </div>';
		$("body").append(gb_share_btn);
		setTimeout(function () {
			$(".gb_share_btn").css({ "position": "fixed" })
		}, 100);
		var gb_share_model = '<div class="gb_share_model" style="width:0;height:0;background:rgba(30,30,30,0.3);position fixed;top:0;left:0;z-index:-200000"></div>';
		var gb_share_box = '<div class="gb_share_box" style="position fixed!important;bottom:-4.33rem;left:0px;width:100%;height:4.33rem;background:#FFFFFF;z-index:200001;transition:bottom linear 0.3s;-webkit-transition:bottom linear 0.3s;text-align:center;"></div>';

		$("body").append(gb_share_model, gb_share_box);
		setTimeout(function () {
			$(".gb_share_box").css({ "position": "fixed" });
			$(".gb_share_model").css({ "position": "fixed" });
			that.creatShareItem();
			that.addEvent();
		}, 200);
		$(".gb_share_btn").click(function () {
			that.toogleShare();
		});
		$(".gb_share_model").click(function () {
			that.toogleShare();
        });


	}
	//分享区域隐藏显示
	GBMShare.prototype.toogleShare = function () {
		if (this.isHide) {
			$(".gb_share_box").css({ "bottom": "0" });
			$(".gb_share_model").css({ "width": "100vw", "height": "100vh", "z-index": "200000" });
		} else {
			$(".gb_share_box").css({ "bottom": "-4.33rem" });
			$(".gb_share_model").css({ "width": "0", "height": "0", "z-index": "-200000" });

		}
		this.isHide = !this.isHide;
	}
	GBMShare.prototype.addEvent = function () {
		var that = this;
		var elem = $(".gb-share-icon");
		for (var i = 0; i < elem.length; i++) {
			elem[i].addEventListener('click', function (e) {
				that.elem = e.currentTarget || e.srcElement;
				that.findShare();
			});
		}
	}
	GBMShare.prototype.findShare = function () {
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
					url: this.getValue('url'),
					title: this.getValue('title') + ' ' + this.getValue('description'),
					description: this.getValue('description'),
					image: this.getValue('image'),
					imageUrl: this.getValue('image'),

				}
			},
		},
			s = sharers[sharer];
		if (s) {
			s.width = this.getValue('width');
			s.height = this.getValue('height');
			this.openUrl(s, Ysharer);
		}
	}
	GBMShare.prototype.openUrl = function (sharer, Ysharer) {
		var that = this;
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
		if (!GESHOP.isLogin()) {
			window.location = 'https://loginm.gearbest.com/m-users-a-sign.htm?ref=' + window.location;
		} else {
			var popWidth = sharer.width || 600,
				popHeight = sharer.height || 480,
				left = window.innerWidth / 2 - popWidth / 2 + window.screenX,
				top = window.innerHeight / 2 - popHeight / 2 + window.screenY,
				popParams = 'scrollbars=no, width=' + popWidth + ', height=' + popHeight + ', top=' + top + ', left=' + left,
				newWindow = window.open(sharer.shareUrl, '', popParams);
			//异步获取是否分享成功
			this.detectWindow(newWindow).then(function (data) {
				that.toogleShare();
				if (data.closed) {
					// layer.msg("success");
					that.shareFn(Ysharer);
				} else {
					// layer.msg("fail");
				}
			})
			if (window.focus) {
				newWindow.focus();
			}

		}


	}

	//创建分享平台dom
	GBMShare.prototype.creatShareItem = function () {
        var that = this;
		var shareArr = $("#shareChannel").val().split(",");
		var share_area = $(".gb_share_box");
		var share_title = $("meta[property='og:title']").attr("content");
		var share_url = $("meta[property='og:url']").attr("content");
		var share_image = $("meta[property='og:image']").attr("content");
		var share_description = $("meta[property='og:description']").attr("content");
		var shareItemCss = "display:inline-block;text-align:center;margin-right:0.26rem";
		var shareIconCss = "display:inline-block;width:1.13rem;height:1.13rem;line-height:1.13rem;background:#d8d8d8;text-align:center;border-radius:15%;line-height:0.8rem;padding-top:0.2rem;";
		var colorArr = ['#3b5998', '#1da1f2', '#dc4e41', '#ff5700', '#5a799d', '#31A9DD'];
		var shareIconStyle = "";
		if (share_area.length) {
			var html = '<div style="height:3rem;line-height:3rem;background:#f2f5f7">';
			for (var i = 0; i < shareArr.length; i++) {
				var imgUrl = this.staticDomain + '/resources/images/gb/u000142/' + shareArr[i] + '.png';
				// var imgUrl = 'https://gloimg.zafcdn.com/zaful/pdm-product-pic/Clothing/2017/05/06/goods-img/1496715782753786870.jpg';
				var share_icon_Obj = {
					'fb': 'gs-icon-F',
					'google': 'gs-icon-google',
					'reddit': 'gs-icon-reddit',
					'telegram': 'gs-icon-telegram',
					'twitter': 'gs-icon-Twitter',
					'vk': 'gs-icon-VK'
				}
				var iconClass = 'gs-iconfont ' + share_icon_Obj[shareArr[i]];

				var t = shareArr[i];
				var itemColor = "";
				switch (t) {
					case "fb": itemColor = "#3b5998";
						break;
					case "twitter": itemColor = "#1da1f2";
						break;
					case "google": itemColor = "#dc4e41";
						break;
					case "reddit": itemColor = "#ff5700";
						break;
					case "vk": itemColor = "#5a799d";
						break;
					case "telegram": itemColor = "#31A9DD";
						break;
					default:
						break;
				}
				shareIconCss += ('background:' + itemColor + ";")
				shareIconStyle += ('font-size:.64rem;color: #ffffff;')
				// html += '<div class="share-Item" style=' + shareItemCss + '><a href="javascript:;" class="gb-share-icon" style="' + shareIconCss + '" ' + ' data-title="' + share_title + '" data-url="' + share_url + '" data-image="' + share_image + '" data-description="' + share_description + '"  data-sharer="' + shareArr[i] + '" title="' + shareArr[i] + '"><img style="width:80%;height:80%;" src="' + imgUrl + '"/></a></div>'
				html += '<div class="share-Item" style=' + shareItemCss + '><a href="javascript:;" class="gb-share-icon" style="' + shareIconCss + '" ' + ' data-title="' + share_title + '" data-url="' + share_url + '" data-image="' + share_image + '" data-description="' + share_description + '"  data-sharer="' + shareArr[i] + '" title="' + shareArr[i] + '"><i class="'+iconClass+'" style="'+shareIconStyle+'"></i></a></div>'
			}
			html += '</div>';
			var cancel = '<div class="gb-share-cancel" style="height:1.333rem;line-height:1.333rem;border-top:0.0133rem solid #ccc;text-align:center;color:#ff8a00;">Cancel</div>';
			html += cancel;
            share_area.html(html);
            $(".gb-share-cancel").click(function () {
                that.toogleShare();
            });
		}
	}
	GBMShare.prototype.getValue = function (attr, defaultValue) {
		defaultValue = (defaultValue === undefined) ? '' : defaultValue;
		var val = this.elem.getAttribute('data-' + attr);
		return (val === undefined || val === null) ? defaultValue : val;
	}
	if (typeof GESHOP_ISEDITENV == 'undefined') {
		if ($("#shareChannel").length > 0 && $("#shareChannel").val()) {
			var gbmshare = new GBMShare();
			gbmshare.init();
		}
	}
}





/* GB common end */

