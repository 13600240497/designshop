
$(function () {

	if (GBTimer) {
		var nowTimer = new GBTimer(GESHOP_LANGUAGES);

		$('[data-gid="U0000105_default"]').each(function (i, element) {
			var $down = $(element).find('.gs-goodsRushDown');
			if ($down.data('tplStatus') == true) {
				return false;
			}
			nowTimer.add($down);
			$down.data('tplStatus', true);
		});
	};
});
