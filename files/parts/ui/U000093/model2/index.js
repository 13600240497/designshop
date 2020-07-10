;$(function () {
	if (GBTimer) {
		var nowTimer = new GBTimer(GESHOP_LANGUAGES);

		var GlobalLanguages = GESHOP_LANGUAGES ? GESHOP_LANGUAGES : {};

		var GlobalLang = {
			buy_now: GlobalLanguages.buy_now || 'Buy Now',
			deals_ended: GlobalLanguages.deals_ended || 'Deals Ended',
			coming_soon: GlobalLanguages.coming_soon || 'Coming soon',
			sold_out: GlobalLanguages.sold_out || 'Sold Out',
			starts_in: GlobalLanguages.starts_in || 'Starts In',
			ends_in: GlobalLanguages.ends_in || 'Ends In',
		};

		$('.ui-U000093_model2').each(function (i, element) {
			var $down = $(element).find('.gs-goodsRushDown');

			renderKillFn(nowTimer,element);

			if ($down.data('tplStatus') === true) {
				return;
			}
			var limit_type = $(element).data('limitendactive');	//是否倒计时结束隐藏商品
			var buyNowText = $(element).data('buytext');
			var isEditEnv = $('[data-editenv]:eq(0)').data('editenv');
			nowTimer.add($down, {
				ingText: GlobalLang.ends_in, buyNowText: buyNowText, onEnd: function () {
					if (limit_type == 0 && isEditEnv != '1') {
						$($down).closest('li').hide();
					}
				}
			});
			$down.data('tplStatus', true);
		});

		/*秒杀时间*/
		function renderKillFn(nowTimer,element){
			var $killText = $(element).find('.gb-kill-statusText');
			var $killDown = $(element).find('.gs-kill-down');
			if ($killDown.data('tplStatus') === true) {
				return;
			}
			nowTimer.add($killDown, {
				renderFn: function (resObj) {
					var timeParam = resObj.timeParam;
					var statusTextCurrent = $killText.text();
					$killDown.html('<em>'+timeParam.CDay+'</em>:<em>'+timeParam.CHour+'</em>:<em>'+timeParam.CMinute+'</em>:<em>'+timeParam.CSecond+'</em>');
					if(statusTextCurrent !== resObj.statusText){
						$killText.text(resObj.statusText);
					}

				}
			});
			$killDown.data('tplStatus', true);
		}
	}
});
