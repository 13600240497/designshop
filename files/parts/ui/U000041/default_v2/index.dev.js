$(function () {
	var tab_item = $('.gs-tab-label li')

	tab_item.on('mouseenter click', function () {
		var $labelParent = $(this).parent('ul'),
			$tab_content = $labelParent.next('.gs-tab-content'),
			$tab_index = $(this).index()
		$(this).addClass('current').siblings().removeClass('current')
		$tab_content.find('.gs-tab-item:eq(' + $tab_index + ')').addClass('gs-tab-show').siblings().removeClass('gs-tab-show')
		if ($.fn.lazyload) {
			$tab_content.find('.gs-tab-item:eq(' + $tab_index + ') img.lazyload').lazyload({
				threshold: 100,
				failure_limit: 20,
				skip_invisible: false
			})
		} else {
			window.GS_GOODS_LAZY_FN('.lazyload');
		}
	});
});
