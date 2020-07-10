; $(function () {
	var staticDomain = typeof GESHOP_STATIC == "undefined" ? "" : GESHOP_STATIC;
	if (typeof jQuery == 'undefined') {
		$LAB.script(staticDomain + '/resources/javascripts/library/jquery.js').wait(function () {
			init();
			var jq = $.noConflict();
		})
	} else {
		init();
	}
	function init () {
		$LAB.script(staticDomain + '/resources/javascripts/library/jquery.qrcode.min.js').wait(function () {
			$('.jsQRcode').map(function () {
				var webgoodsn = $(this).data('webgoodsn');
				var warehousecode = $(this).data('warehousecode');
				if (webgoodsn != '' && warehousecode != '') {
					var qrcode = $(this).qrcode({
						width: 80,
						height: 80,
						text: 'gearbest://product?goods_id=' + webgoodsn + '&wid=' + warehousecode
					});
					return qrcode;
				}
			})
		})
	}
	if (GBViewMore) {
		$('[data-gid=U000100]').each(function(i,element){
			GBViewMore(element);
		});
	}
	
});
