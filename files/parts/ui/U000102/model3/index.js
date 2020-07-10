
; $(function () {
	if (GBTimer) {
		var nowTimer = new GBTimer(GESHOP_LANGUAGES);
		$('[data-gid="U000102_default"]').each(function (i, element) {
			var $down = $(element).find('.gs-goodsRushDown');
			if ($down.data('tplStatus') == true) {
				return false;
			}
			var limit_type = $(element).data('limitendactive');
			var buyNowText = $(element).data('buytext');
			nowTimer.add($down, {
				     buyNowText: buyNowText, onEnd: function () {
					if (limit_type == 0) {
						$($down).closest('li').hide();
					};
				}
			});
			$down.data('tplStatus', true);
		});
	};
	$(".cart_good_soldOut").css("pointer-events","none");



	$('[data-gid="U000102_default"]').each(function (i, element) {
		// 复制到剪切板
	var copyText = function(text) {
		var textare, value;
		textare = document.createElement('textarea');
		value = document.createTextNode(text);
		textare.appendChild(value);
		document.body.appendChild(textare);
		document.getElementsByTagName('textarea')[0].select();
		try {
			document.execCommand('copy');
			document.body.removeChild(textare);
			layer.msg('Successfully Copied!!');
		} catch (e) {
			document.body.removeChild(textare);
		}
	};
	var copyBtn = $(element).find(".gb_copy");
	copyBtn.on('click', function () {
		var $this = $(this);
		var code = $this.attr('data-code');
		copyText(code);
	});


	// /* 添加购物车 */
	// var addCard = $(element).find(".js-addCart");
	// addCard.on('click', function () {
	// 	var $this = $(this);
	// 	var goodsSn = $this.attr('data-sku');
	// 	var warehouseCode = $this.attr('data-warehousecode');
	// 	var qty = $this.attr('data-qty');
	// 	var imgSrc = $this.attr('data-img');
	// 	GESHOP.addToCart({
	// 		reqData: [{
	// 		goodsSn: goodsSn,
	// 		qty: qty,
	// 		warehouseCode: warehouseCode,
	// 		}],
	// 		animation: {
	// 		imgSrc: imgSrc,
	// 		origin: $this,
	// 		}
	// 	});
	// 	});
	});
});










