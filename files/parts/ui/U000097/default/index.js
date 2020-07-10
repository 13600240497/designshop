
; $(function () {
	var staticDomain = typeof GESHOP_STATIC == "undefined" ? "" : GESHOP_STATIC;

	var GlobalLanguages = GESHOP_LANGUAGES ? GESHOP_LANGUAGES : {};

	var GlobalLang = {
		buy_now:GlobalLanguages.buy_now || 'Buy Now',
		deals_ended:GlobalLanguages.deals_ended || 'Deals Ended',
		coming_soon:GlobalLanguages.coming_soon || 'Coming soon',
		sold_out:GlobalLanguages.sold_out || 'Sold Out',
		starts_in:GlobalLanguages.starts_in || 'Starts In',
		ends_in:GlobalLanguages.ends_in || 'Ends In',
	};

	$LAB.script(staticDomain + '/resources/javascripts/library/vanilla/lazyload.min.js').wait(function () {
		tabCallBack();
		// var buyNowText = $(element).data('buytext');
		add('.time-item');

		if (GBTimer) {
			var nowTimer = new GBTimer(GESHOP_LANGUAGES);

			$('[data-gid="U000097_default"]').each(function (i, element) {
				var $down = $(element).find('.gs-goodsRushDown');
				/*
				if ($down.data('tplStatus') == true) {
					return false;
				}
				*/
				var limit_type = $(element).data('limitendactive');
				var buyNowText = $(element).data('buytext');
				var isEditEnv = $('[data-editenv]:eq(0)').data('editenv');
				nowTimer.add($down, {
					ingText: GlobalLang.ends_in, buyNowText: buyNowText, onEnd: function () {
						if (limit_type == 0 && isEditEnv != '1') {
							$($down).closest('li').hide();
						};
					}
				});
				$down.data('tplStatus', true);

				var nowTime = new Date().getTime();
				var minIndex = 0,
					minVlue = +Infinity,
					minTime,
					indexNum;
				$(element).find('.gs-tab-label .gs-label-item').each(function(index,item) {
					var $this = $(this);
					if ( $this.attr("data-status") == "1" ) {
						var abc = Math.abs(nowTime - parseInt($this.attr('data-begin')));
						indexNum = index;
						if(abc<minVlue){
							minIndex = index;
							minVlue =abc;
							minTime = $this.attr('data-begin')
						}
					}
				})
				$('.gs-tab-label').find('[data-begin=' + minTime + ']').addClass('current').siblings().removeClass('gs-tab-show');
				$(element).find('.gs-tab-content .gs-tab-item').eq(indexNum).addClass('gs-tab-show').siblings().removeClass('gs-tab-show')

			});
		};
	})


	function tabCallBack () {
		var tab_item = $('.gs-tab-label li')
		tab_item.on('click', function () {
			var $labelParent = $(this).parent('ul'),
				$tab_content = $labelParent.next('.gs-tab-content'),
				$tab_index = $(this).index()
			$(this).addClass('current').siblings().removeClass('current')
			$tab_content.find('.gs-tab-item:eq(' + $tab_index + ')').addClass('gs-tab-show').siblings().removeClass('gs-tab-show')
		});
	}



	function add(jqselect, option) {
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
			// var $li = $self.closest('li');
			// if (!(startTime && endTime)) {
			// 	$target = $target.not($self);
			// 	if ($self.attr('data-begin') == '0' && $self.attr('data-end') == '0') {
			// 		$li.find('.buyLink').text(options.endText);
			// 		$li.addClass('good_dealEnded')
			// 	}
			// 	return;
			// }

			initStatus($self, options);
			var leftTime = parseInt($self.attr('data-leftTime'));
			var dataStatus = parseInt($self.attr('data-status'));
			var dataTabText = $self.attr('data-tabtext');
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
				$self.html('<span>' + dataTabText + '</span><span>' + statusText + ': ' + CDay + ':' + CHour + ':' + CMinute + ':' + CSecond + '</span>')
			} else {
				$target = $target.not($self);

				if (dataStatus !== 2) {
					initStatus($self, options);
				} else {
					$self.html('<span>' + dataTabText + '</span><span>' + statusText + ': ' + '00:00:00:00</span>')
					if (options.onEnd) {
						options.onEnd();
					}
				}

			}
		})

		if (0 !== $target.length) {
			setTimeout(function () {
				add(jqselect, option)
			}, 1000)
		}
	}

	function initStatus(target, options) {
		var $target = $(target);
		$target.each(function () {
			var $self = $(this);
			// var $li = $self.closest('li');
			var startTime = parseInt($self.attr('data-begin')) / 1000;
			var endTime = parseInt($self.attr('data-end')) / 1000;
			var nowTime = (new Date().getTime()) / 1000;
			var serverTime;
			var leftTime;
			var status = 0;	//['0未开始', '1已开始', '2已结束']
			var statusText = '';	//倒计时状态文案
			// var currentBtnText = $li.find('.buyLink').text();
			// var left_number = parseInt($self.attr('data-stock'));	//剩余库存

			/* is sold out check */
			// var isSoldOut = $li.hasClass('good_soldOut');
			var tabText = '';
			var buyTextArr = ['Coming Soon', 'Buy Now', 'Deals Ended'];
			var $buy_now_text = $('input[name=buy_now_text]').val();
			var $coming_soon_text = $('input[name=coming_soon_text]').val();
			var $deals_ended_text = $('input[name=deals_ended_text]').val();
			var $coming_soon_bgc = $('input[name=coming_soon_bgc]').val();
			var $coming_soon_ftc = $('input[name=coming_soon_ftc]').val();
			if (startTime > nowTime) {
				leftTime = startTime - nowTime;
				statusText = options.startText;
				tabText = $coming_soon_text;
				$self.addClass('tab-lose');
			} else if (startTime <= nowTime && endTime > nowTime) {
				status = 1;
				statusText = options.endText;
				leftTime = endTime - nowTime;
				tabText = $buy_now_text;
			} else if (endTime < nowTime) {
				status = 2;
				leftTime = -1;
				tabText = $deals_ended_text;
				$self.addClass('tab-lose');
			}

			$self.attr({ 'data-leftTime': leftTime, 'data-status': status, 'data-tabtext': tabText });

			/* 是否售完
			if (status == 1 && left_number == 0) {
				if (currentBtnText != 'Sold Out') {
					$li.find('.buyLink').text('Sold Out');
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
			} */

		})
	}

});
