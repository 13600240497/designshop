
; $(function () {
	var staticDomain = $('[data-static-domain]:eq(0)').attr('data-static-domain') ? $('[data-static-domain]:eq(0)').attr('data-static-domain') : "";
	var endTime;
	var LBTplObj = {
		renderTpl: function () {
			gs_laytpl.config({ open: "<%", close: "%>" });

			// 遍历 - 兼容多个组件各自获取数据
			$('.seckill').each(function (i, element) {
				var goodsSn = $(element).find('input[name=goodsSKU]').val();
				var $ele = $(element);
				var component_id = $ele.attr('data-id');
				var params = {
					"lang": GESHOP_LANG,
					"goodsSn": goodsSn,
					pipeline: (typeof GESHOP_PIPELINE != 'undefined' ? GESHOP_PIPELINE : '')
				}

				var content = { content: JSON.stringify(params) };
				var url = GESHOP_INTERFACE.timeseckilldetail.url;

				var getTpl = $ele.find('.pc-leader-board-template').html(),
					view = $ele.find('.leader-board-container'),
					lang = typeof GESHOP_LANG != 'undefined' ? GESHOP_LANG : 'en';
				
				/** 如果没有了HTML模版，则不再执行模版渲染 */
				if (!getTpl) {
					return false;
				}

				/** 没有SKu不请求数据 */
				if (goodsSn == '') {
					return false;
				}

				$.ajax({
					url: url,
					type: 'get',
					dataType: 'jsonp',
					jsonpCallback: `geshop_callback_${component_id}`,
					cache: true,
					data: content,
					success: function (res) {
						var goodsInfo = [];
						var data = res.data;
						for (var goodsInfoindex in data.goodsInfo) {
							goodsInfo.push(data.goodsInfo[goodsInfoindex]);
						}
						var img = new Image(), imgRatio;
						//if(goodsInfo[0]){
						img.src = goodsInfo[0].goods_img;
						//}

						if (img.width / img.height == 1) {
							imgRatio = 1;
						} else {
							imgRatio = 0;
						}

						var dataParam = {
							goodsInfo: goodsInfo,
							lang: lang,
							endTime: endTime,
							imgRatio: imgRatio
						}
						gs_laytpl(getTpl).render(dataParam, function (html) {
							view.html(html);
						});

					}
				});
			});

		}
	}

  	/** 模板初始化 */
	$LAB.script(staticDomain + "/resources/javascripts/library/gs_laytpl.js?2018100101").wait(function () {
		LBTplObj.renderTpl();
		gsKillGlobal.initCountDown();
		gsKillGlobal.compoentCountDown();
	});
});
 
var now = new Date().getTime();
/* 倒计时初始化, tpl初始化 */
if (!gsKillGlobal) {
	var gsKillGlobal = function (my) {
		var startTime, endTime;
		var $gsCountTarget = $('.seckill');
		/** 初始化倒计时 */
		my.initCountDown = function () {
			/** 遍历组件 */
			$gsCountTarget.each(function (i, element) {
				var leftTime = 0;
				var status = 0;	// ['0未开始', '1已开始', '2已结束']
				startTime = $(element).find('input[name=serverTime]').data('start-time');
				endTime = $(element).find('input[name=serverTime]').data('end-time');
				$(element).find('input[name=isEndShow]').val();
				$(this).find('p[name=sTime]').attr('starttime');
				if (startTime > now) {
					leftTime = (parseInt(startTime) - now) / 1000;
					status = 0;

				} else if (startTime <= now && endTime > now) {
					status = 1;
					leftTime = (endTime - now) / 1000;
				} else if (endTime < now) {
					status = 2;
					leftTime = 0;
				}
				$(element).find('input[name=count_textStatus]').val(status);
				$(this).data({ 'leftTime': leftTime, 'status': status })
			});

		}
		/* 倒计时组件 */
		my.compoentCountDown = function () {
			$gsCountTarget.each(function (i, element) {
				var timeId;
				var seconds, minutes, hours, days, CDay, CHour, CMinute, CSecond
				var leftTime = parseInt($(this).data('leftTime'));
				var status = parseInt($(this).data('status'));
				if (!isNaN(leftTime) && startTime >= 0) {
					seconds = leftTime;
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
					$(element).find('.timer .s-days').text(CDay);
					$(element).find('.timer .s-hours').text(CHour);
					$(element).find('.timer .s-minutes').text(CMinute);
					$(element).find('.timer .s-seconds').text(CSecond);
					$(element).find('input[name=serverTime]').data('lefttime', leftTime);
					var timed;
					var copyrightText = $(element).find('.copywriter');
					if (status == 0) {
						copyrightText.text('Start In:');
					} else if (status == 1) {
						copyrightText.text('Ends In:');
					} else if (status == 2) {
						copyrightText.text('Already Ended:');
						$(element).find('.timer .s-days').text('00');
						$(element).find('.timer .s-hours').text('00');
						$(element).find('.timer .s-minutes').text('00');
						$(element).find('.timer .s-seconds').text('00');

						var isEndShow = $(element).find('input[name=isEndShow]').val();
						var isEditEnv = $(element).find('input[name=isEditEnv]').val();
						clearTimeout(timeId);
						if (isEndShow == '0' && isEditEnv == '0') {
							$(this).remove();
						}
						return;
					}

					$(this).data('left-time', leftTime - 1);
					if (leftTime < 1) {
						now = new Date().getTime();
						gsKillGlobal.initCountDown();
					}

				} else {
					if (status !== 2) {
						gsKillGlobal.initCountDown();
					}
				}
			});
			timeId = setTimeout(gsKillGlobal.compoentCountDown, 1000);
		}

		return my
	}(gsKillGlobal || {})
}
