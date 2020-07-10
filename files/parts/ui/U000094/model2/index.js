;$(function () {
	if (GBTimer) {
		var GlobalLanguages = GESHOP_LANGUAGES ? GESHOP_LANGUAGES : {};

		var GlobalLang = {
			buy_now: GlobalLanguages.buy_now || 'Buy Now',
			deals_ended: GlobalLanguages.deals_ended || 'Deals Ended',
			coming_soon: GlobalLanguages.coming_soon || 'Coming soon',
			sold_out: GlobalLanguages.sold_out || 'Sold Out',
			starts_in: GlobalLanguages.starts_in || 'Starts In',
			ends_in: GlobalLanguages.ends_in || 'Ends In',
		};

		var nowTimer = new GBTimer(GESHOP_LANGUAGES);
		$('.ui-U000094_model2').each(function (i, element) {
			var $down = $(element).find('.gs-goodsRushDown');

			renderKillFn(nowTimer, element);

			if ($down.data('tplStatus') === true) {
				return;
			}
			var limit_type = $(element).data('limitendactive');
			var buyNowText = $(element).data('buytext');
			var isEditEnv = $('[data-editenv]').eq(0).data('editenv');
			nowTimer.add($down, {
				ingText: GlobalLang.ends_in, buyNowText: buyNowText, onEnd: function () {
					if (limit_type == 0 && isEditEnv != '1') {
						$($down).closest('li').hide();
					}
					;
				}
			});
			$down.data('tplStatus', true);
		});

		/*秒杀时间*/
		function renderKillFn (nowTimer, element) {
			var $killText = $(element).find('.gb-kill-statusText');
			var $killDown = $(element).find('.gs-kill-down');
			if ($killDown.data('tplStatus') === true) {
				return;
			}
			nowTimer.add($killDown, {
				renderFn: function (resObj) {
					var timeParam = resObj.timeParam;
					var statusTextCurrent = $killText.text();
					$killDown.html('<em>' + timeParam.CDay + '</em>:<em>' + timeParam.CHour + '</em>:<em>' + timeParam.CMinute + '</em>:<em>' + timeParam.CSecond + '</em>');
					if (statusTextCurrent !== resObj.statusText) {
						$killText.text(resObj.statusText);
					}

				}
			});
			$killDown.data('tplStatus', true);
		}
}
;


	if (GBViewMore) {
		$('.ui-U000094_model2 .view-more-btn').on('click',function(){
			var $this = $(this);
			var $ul = $(this).parent().siblings('.gb-list-default').eq(0);
			var shownum = $ul.data('shownum');
			var Lis = $ul.children().each(function(index,item){
				if(index>shownum-1){
					if( index ==$ul.children().length-1){
						if(!$(item).hasClass('isHide')){ // 收缩 
							$('html').scrollTop($ul.data('scrolltop'))
							$this.children('span').text($this.data('moretext'))
							$this.children('i').removeClass('gs-iconfont gs-icon-up icon-arrow_up').addClass('gs-iconfont gs-icon-down1 icon-arrow_down')
						}else{ // 展开
							$ul.data('scrolltop',$('html').scrollTop())
							$this.children('span').text($this.data('lesstext'))
							$this.children('i').removeClass('gs-iconfont gs-icon-down1 icon-arrow_down').addClass('gs-iconfont gs-icon-up icon-arrow_up')
						} 
					}
					$(item).toggleClass('isHide')
				}
			})
		})
	}

});
