$(function () {
	var staticDomain = typeof GESHOP_STATIC == "undefined" ? "" : GESHOP_STATIC;
	$LAB.script(staticDomain + '/resources/javascripts/library/vanilla/lazyload.min.js').wait(function () {
		tabCallBack();
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

});
