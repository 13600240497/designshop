; $(function () {
	var staticDomain = typeof GESHOP_STATIC == "undefined" ? "" : GESHOP_STATIC;
	$LAB.script(staticDomain + '/resources/javascripts/library/jquery.qrcode.min.js').wait(function () {
		$('.jsQRcode').map( function() {
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
});
