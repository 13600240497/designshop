;$(function () {
	$('body').on('click', '.js_quickShop', function() {
		var url = $(this).attr('data-href');
		
		GEShopSiteCommon.dialog.iframe(url, 1080, 597, true);
	});
});