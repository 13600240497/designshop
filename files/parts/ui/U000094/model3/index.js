
; $(function () {
	var $target = $('.ui-U000094_model3');
	if (GBTimer) {
		var GlobalLanguages = GESHOP_LANGUAGES ? GESHOP_LANGUAGES : {};

		var GlobalLang = {
			buy_now:GlobalLanguages.buy_now || 'Buy Now',
			deals_ended:GlobalLanguages.deals_ended || 'Deals Ended',
			coming_soon:GlobalLanguages.coming_soon || 'Coming soon',
			sold_out:GlobalLanguages.sold_out || 'Sold Out',
			starts_in:GlobalLanguages.starts_in || 'Starts In',
			ends_in:GlobalLanguages.ends_in || 'Ends In',
		};

		var nowTimer = new GBTimer(GESHOP_LANGUAGES);
		$target.each(function (i, element) {
			var $down = $(element).find('.gs-goodsRushDown');
			if ($down.attr('data-tplStatus') === true) {
				return;
			}
			var limit_type = $(element).attr('data-limitendactive');
			var isEditEnv = $('[data-editenv]').eq(0).data('editenv');
			nowTimer.add($down, {
				ingText: GlobalLang.ends_in, onEnd: function () {
					if (limit_type == 0 && isEditEnv != '1') {
						$($down).closest('li').hide();
					};
				}
			});
			$down.attr('data-tplStatus', true);
		});
    };

    if (GBViewMore) {
			$target.each(function(i,element){
			GBViewMore(element);
		});
	}

	// good ad
	$target.each(function (index, item) {
		var $adItem = $('.gs-list-item-ad', item);
		var liHeight = $adItem.parent().find('.gs-list-item--m').not('.gs-list-item-ad').eq(0).height();
		$adItem.height(liHeight);
	});




});
