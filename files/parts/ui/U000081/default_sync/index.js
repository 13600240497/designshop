
var GS_goodsSync = (function (my) {
	var $loading = $(".ui-recommend-loading");

	my.gsTplInt = function () {
		gs_laytpl.config({ open: "<%", close: "%>" });
	}
	/**
	 *
	 * @param {*} skus
	 * @param {*} dataid 对应的组件id
	 */
	my.getTplProduct = function (skus) {
		var num = parseInt(Math.random() * 3)
		var testArr = ["168567304,165160503", "168567304,165160503,169658601,169596402", "168567304,165160503,162453302,165244301,169658601,169596402"]
		var lang = typeof GESHOP_LANG != 'undefined' ? GESHOP_LANG : 'en'
		var params = {
			lang: lang,
			goodsSn: skus
		}
		var zafulParams = {
			lang: lang,
			skuArr: skus
		}
		if (typeof GESHOP_PIPELINE != 'undefined') {
			zafulParams.pipeline = GESHOP_PIPELINE
		}
		var urlGroup = {
			'rw-pc': 'https://www.rosewholesale.com/m-interface-a-getGoodsDetailForWeb.html',
			'rw-wap': 'https://m.rosewholesale.com//geshop/interface/goods-detail-by-sku/',
			'rw-app': 'https://m.rosewholesale.com//geshop/interface/goods-detail-by-sku/',
			'rg-pc': 'https://www.rosegal.com/m-interface-a-getGoodsList.html',
			'rg-wap': 'https://m.rosegal.com/m-interface-a-getGoodsList.html',
			'rg-app': 'https://m.rosegal.com/m-interface-a-getGoodsList.html',
			'zf-pc': 'https://www.zaful.com/api/get_seckill_api.php?method=getGoods',
			'zf-wap': 'https://www.zaful.com/api/get_seckill_api.php?method=getGoods',
			'zf-app': 'https://www.zaful.com/api/get_seckill_api.php?method=getGoods'
		}


		var paramGroup = {
			'rw-pc': { content: JSON.stringify(params) },
			'rw-wap': { content: JSON.stringify(params) },
			'rw-app': { content: JSON.stringify(params) },
			'rg-pc': params,
			'rg-wap': params,
			'rg-app': params,
			'zf-pc': zafulParams,
			'zf-wap': zafulParams,
			'zf-app': zafulParams
		}

		var url = urlGroup[GESHOP_SITECODE]
		var content = paramGroup[GESHOP_SITECODE]

		return $.ajax({
			url: url,
			type: 'get',
			dataType: 'jsonp',
			data: content
		})
	}

	/* target geshop-goods-sync */
	my.tplIntGoodsCallback = function (target) {
		var element = target;
		var currentSkus = $(element).data('skus');
		var $tpl = $(element).find('.gs_syncDefault');
		var tplHtml = $tpl.html();
		if (!currentSkus) { return false };
		var isEditEnv = $('[data-editenv]:eq(0)').data('editenv');
		my.getTplProduct(currentSkus).done(function (res) {
			$loading.hide()
			//var res = { "code": 0, "data": [{ "goods_id": "1028181", "goods_sn": "162970201", "catid": "44", "goods_title": "Noble Rhinestone Design One-Shoulder Sleeveless Ombre Color Pleated Prom Dress For Women", "url_title": "noble-rhinestone-design-one-shoulder", "shop_price": "43.39", "market_price": "85.08", "goods_img": "http:\/\/gloimg.rowcdn.com\/ROSE\/2015\/201512\/goods-img\/1452474108271393390.jpg", "goods_thumb": "ROSE\/2015\/201512\/thumb-img\/1449010523799-thumb-P-3546505.jpg", "goods_number": "89", "is_on_sale": "1", "promote_price": "5.99", "promote_start_date": "1526533200", "promote_end_date": "1538370000", "discount": 93, "left_time": 3444707, "webgoodsn": "162970201", "thumb_url": "http:\/\/gloimg.rowcdn.com\/ROSE\/2015\/201512\/thumb-img\/1449010523799-thumb-P-3546505.jpg", "lang": "en", "warecode": "SZ", "activity_number": "100", "activity_volume_number": "20", "activity_left_number": "80", "promte_percent": "" }, { "goods_id": "1038274", "goods_sn": "169057904", "catid": "47", "goods_title": "Stylish Red V-Neck Sleeveless Dress For Women", "url_title": "stylish-v-neck-red-sleeveless", "shop_price": "14.36", "market_price": "19.15", "goods_img": "http:\/\/gloimg.rowcdn.com\/ROSE\/pdm-product-pic\/Clothing\/2016\/01\/15\/goods-img\/1453078742794638207.jpg", "goods_thumb": "ROSE\/pdm-product-pic\/Clothing\/2016\/01\/15\/thumb-img\/1452820219957589094.jpg", "goods_number": "8", "is_on_sale": "1", "promote_price": 0, "promote_start_date": "0", "promote_end_date": "0", "discount": 25, "left_time": 0, "webgoodsn": "169057904", "thumb_url": "http:\/\/gloimg.rowcdn.com\/ROSE\/pdm-product-pic\/Clothing\/2016\/01\/15\/thumb-img\/1452820219957589094.jpg", "lang": "en", "warecode": "SZ", "activity_number": "", "activity_volume_number": "", "activity_left_number": "", "promte_percent": "" }, { "goods_id": "1038226", "goods_sn": "169569403", "catid": "48", "goods_title": "Stylish V-Neck Half Sleeve Women's Floral Print Dress", "url_title": "stylish-v-neck-half-sleeve", "shop_price": "26.42", "market_price": "46.35", "goods_img": "http:\/\/gloimg.rowcdn.com\/ROSE\/pdm-product-pic\/Clothing\/2016\/01\/14\/goods-img\/1453075188454008959.jpg", "goods_thumb": "ROSE\/pdm-product-pic\/Clothing\/2016\/01\/14\/thumb-img\/1452705360052201978.jpg", "goods_number": "89", "is_on_sale": "1", "promote_price": 0, "promote_start_date": "0", "promote_end_date": "0", "discount": 43, "left_time": 0, "webgoodsn": "169569403", "thumb_url": "http:\/\/gloimg.rowcdn.com\/ROSE\/pdm-product-pic\/Clothing\/2016\/01\/14\/thumb-img\/1452705360052201978.jpg", "lang": "en", "warecode": "SZ", "activity_number": "", "activity_volume_number": "", "activity_left_number": "", "promte_percent": "" }, { "goods_id": "1038220", "goods_sn": "169684106", "catid": "45", "goods_title": "Stylish Sweetheart Neck Sleeveless Solid Color Women's Mini Dress", "url_title": "stylish-sweetheart-neck-sleeveless-solid", "shop_price": "19.05", "market_price": "30.73", "goods_img": "http:\/\/gloimg.rowcdn.com\/ROSE\/pdm-product-pic\/Clothing\/2016\/01\/14\/goods-img\/1453074882309794411.jpg", "goods_thumb": "ROSE\/pdm-product-pic\/Clothing\/2016\/01\/14\/thumb-img\/1452733823316813945.jpg", "goods_number": "95", "is_on_sale": "1", "promote_price": 0, "promote_start_date": "0", "promote_end_date": "0", "discount": 38, "left_time": 0, "webgoodsn": "169684106", "thumb_url": "http:\/\/gloimg.rowcdn.com\/ROSE\/pdm-product-pic\/Clothing\/2016\/01\/14\/thumb-img\/1452733823316813945.jpg", "lang": "en", "warecode": "SZ", "activity_number": "", "activity_volume_number": "", "activity_left_number": "", "promte_percent": "" }], "message": "success" };
			if (res.code == '0' && res.data) {
				if (GESHOP_SITECODE == 'zf-wap' || GESHOP_SITECODE == 'zf-app') {
					var arr = [];
					Object.keys(res.data).map(function(key) {
						arr.push(res.data[key]);
					});
					res.data = arr;
				}
				var list = my.sortData(res.data);

				gs_laytpl(tplHtml).render(list, function (html) {
					if (html) {
						$(element).find('.gs-goodsWrap ul').html(html)

						/* 价格换算 rw*/
						if (typeof GLOBAL != 'undefined' && GLOBAL.currency.change_html) {
							var bizhong = my.getCookie('bizhong') || 'USD';
							var $wrapElem = $('.gs-goods-tab')
							GLOBAL.currency.change_html(bizhong, $wrapElem)
						}

					}
				})
			} else {
				if (isEditEnv == '1') {
					$(element).find('.gs-goodsWrap ul').after('<span>错误:接口商品数据异常</span>')
				}
			}

		}).fail(function () {
			$loading.hide()
			if (isEditEnv == '1') {
				$(element).find('.gs-goodsWrap ul').after('<span>错误:接口商品数据为空</span>')
			}
		})
	}

	my.sortData = function (data) {
        var list = [];
	    data.forEach(function (item, index) {
            if (item.goods_number && item.goods_number == 0) {
                list.push(item);
                data.splice(index, 1);
            }
        });
        data = data.concat(list);
	    return data;
    }

	my.scrollEvent = function () {
		var $goodTarget = $(".geshop-goods-sync");
		$goodTarget.length && $goodTarget.each(function (i, element) {
			my.tplIntGoodsCallback($(element))
		})
		// var $goodsWrapper = $(".geshop-goods-sync");
		// var isEditEnv = $('[data-editenv]:eq(0)').data('editenv');
		// var scrollTarget = isEditEnv == '1' ? '.design-right' : window;
		// $goodsWrapper.length &&
		//   $(scrollTarget).on('scroll.goodsSync', my.throttle(function () {
		//     $goodsWrapper.each(function (i, element) {
		//       var $element = $(element),
		//         tplStatus = parseInt($element.attr('data-tplStatus'));
		//       if (!!tplStatus) { return };

		//       var screenH = (document.documentElement || document.body).clientHeight,
		//         scrollY = $(scrollTarget).scrollTop() || window.scrollY,
		//         eleOffsetH = $element.offset().top,
		//         wrapperH = $element.height(),
		//         spaceH = 100;
		//       if (eleOffsetH < screenH || screenH + scrollY + spaceH - (eleOffsetH + wrapperH) > 0 && screenH + scrollY + spaceH - (eleOffsetH + wrapperH) <= screenH) {
		//         $loading.show()
		//         $element.attr('data-tplStatus', 1)
		//         my.tplIntGoodsCallback($element)
		//       }
		//     })
		//   }, 100));
		// /* 首屏加载 */
		// $goodsWrapper.length && $goodsWrapper.each(function (i, element) {
		//   var $element = $(element),
		//     tplStatus = parseInt($element.attr('data-tplStatus'));
		//   if (!!tplStatus) { return };

		//   var screenH = (document.documentElement || document.body).clientHeight,
		//     eleOffsetH = $element.offset().top,
		//     scrollY = $(scrollTarget).scrollTop() || window.scrollY,
		//     wrapperH = $element.height(),
		//     spaceH = 100;
		//   if (eleOffsetH < screenH || screenH + scrollY + spaceH - (eleOffsetH + wrapperH) > 0) {
		//     $loading.show()
		//     $element.attr('data-tplStatus', 1)
		//     my.tplIntGoodsCallback($element)
		//   }
		// });
	}

	my.throttle = function (fn, delay, atleast) {
		var timer = null;
		var previous = null;

		return function () {
			var now = +new Date();
			if (!previous) {
				previous = now;
			}
			if (atleast && now - previous > atleast) {
				fn();
				previous = now;
				clearTimeout(timer);
			} else {
				clearTimeout(timer);
				timer = setTimeout(function () {
					fn();
					previous = null;
				}, delay);
			}
		};
	}

	//设置 cookie 方法
	my.setCookie = function (name, value) {
		var Days = 30
		var exp = new Date()
		exp.setTime(exp.getTime() + Days * 24 * 60 * 60 * 1000)
		document.cookie = name + '=' + escape(value) + ';expires=' + exp.toGMTString() + ';path=/' + ';domain=' + COOKIESDIAMON
	}

	//获取 cookie 方法
	my.getCookie = function (name) {
		var arr,
			reg = new RegExp('(^| )' + name + '=([^;]*)(;|$)')

		if (arr = document.cookie.match(reg)) {
			return arr[2]
		} else {
			return null
		}

	}


	return my;
}({}))


/* 加载初始化 */
var staticDomain = $('[data-static-domain]:eq(0)').attr('data-static-domain') ? $('[data-static-domain]:eq(0)').attr('data-static-domain') : "";
$LAB.script(staticDomain + "/resources/javascripts/library/gs_laytpl.js?2018100101").wait(function () {
	GS_goodsSync.gsTplInt();
	GS_goodsSync.scrollEvent();
	// tplIntGoodsCallback();
});
