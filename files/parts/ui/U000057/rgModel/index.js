
$(function(){
	$('.js_geshopFreeTips').each(function (i,v) {
		var target = $(this);
		var height = target.data('height') || 1;
		var $freeTips = target.find(".free_tipsList");
		var $freeList = $freeTips.find('.geshop-tip-item');

		$freeTips.height($($freeList[0]).height() * $freeList.length);
		if ($freeList.length <= 1) return false;
		setInterval(function() {
			$freeTips.animate({
				"top": '-' + String(height) + 'rem'
			}, 500, 'linear', function () {
				$(this).css({
					"top": 0
				}).find(".geshop-tip-item:first").appendTo(this)
			});
		}, 4000);
	})
});