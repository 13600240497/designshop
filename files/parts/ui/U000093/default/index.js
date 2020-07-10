; $(function () {
	if (GBTimer) {
		var nowTimer = new GBTimer(GESHOP_LANGUAGES);

		var GlobalLanguages = GESHOP_LANGUAGES ? GESHOP_LANGUAGES : {};

		var GlobalLang = {
			buy_now:GlobalLanguages.buy_now || 'Buy Now',
			deals_ended:GlobalLanguages.deals_ended || 'Deals Ended',
			coming_soon:GlobalLanguages.coming_soon || 'Coming soon',
			sold_out:GlobalLanguages.sold_out || 'Sold Out',
			starts_in:GlobalLanguages.starts_in || 'Starts In',
			ends_in:GlobalLanguages.ends_in || 'Ends In',
		};

		$('[data-gid="U000093_default"]').each(function (i, element) {
			var $down = $(element).find('.gs-goodsRushDown');
			if ($down.data('tplStatus') == true) {
				return;
			}
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
		});
	};
});
