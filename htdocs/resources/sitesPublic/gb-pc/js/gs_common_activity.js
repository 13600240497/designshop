/*
 * @Author: wuxingtao
 * @Date: 2018-10-10 17:03:52
 * @Last Modified by: wuxingtao
 * @Last Modified time: 2018-11-08 09:23:35
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


// // 竖直导航和页面间导航顶部底部隐藏冲突,写到公共js
;(function(){
    window.onload = function(){
        var documentH = document.documentElement.clientHeight||document.body.clientHeight||window.innerHeight;
        var bodyH = document.getElementsByTagName("body")[0].offsetHeight;
        if(timer){clearTimeout(timer);}
        var timer = setTimeout(function(){
            window.onscroll = function(){
                var osTop = document.documentElement.scrollTop || document.body.scrollTop;
                if(osTop > documentH && osTop < (bodyH - 2*documentH) ){
                    $(".geshop-page-navigator,.side-btn").css({"z-index":"1001","opacity":"1"})
                    $('.component-vertical-nav').show();
                }else{
                    $(".geshop-page-navigator,.side-btn").css({"z-index":"-1001","opacity":"0"})
                    $('.component-vertical-nav').hide();
                }
            }
        },100)
        $(window).on('resize',function(){
            documentH = document.documentElement.clientHeight||document.body.clientHeight||window.innerHeight;
        })
    }

})();

/* GB common end */

