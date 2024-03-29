var GS_goodsSync = (function (my) {

	my.gsTplInt = function () {
		laytpl.config({ open: "<%", close: "%>" });
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
			skId: 9,
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
			'rw-pc': 'https://www.rosewholesale.com/m-interface-a-getSpikeGoods.html',
			'zf-pc': 'https://www.zaful.com/api/get_seckill_api.php?method=getSeckillData',
		}

		var paramGroup = {
			'rw-pc': { content: JSON.stringify(params) },
			'zf-pc': zafulParams,
		}

		var url = urlGroup[GESHOP_SITECODE]
		var content = paramGroup[GESHOP_SITECODE]
        var ext = new Date().getTime();
		return $.ajax({
			url: url,
			type: 'get',
			dataType: 'jsonp',
            jsonpCallback: `geshop_callback_${ext}`,
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
            if (res.code == '0' && res.data.length > 0) {
				laytpl(tplHtml).render(res.data, function (html) {
					if (html) {
						$(element).find('.gs-goodsWrap ul').html(html)

						/* 价格换算 rw*/
						if (typeof GLOBAL != 'undefined' && GLOBAL.currency.change_html) {
							var bizhong = my.getCookie('bizhong') || 'USD';
							var $wrapElem = $('.gs-goods-tab')
							GLOBAL.currency.change_html(bizhong, $wrapElem)
						}

						my.goodsCountDown(target);
						// 执行懒加载
                        window.GS_GOODS_LAZY_FN();
                    }
				})
			} else {
				if (isEditEnv == '1') {
					$(element).find('.gs-goodsWrap ul').after('<span>错误:接口商品数据异常</span>')
				}
			}

		}).fail(function () {
			if (isEditEnv == '1') {
				$(element).find('.gs-goodsWrap ul').after('<span>错误:接口商品数据为空</span>')
			}
		})
	}

	my.scrollEvent = function () {
		var $goodsWrapper = $(".geshop-goods-sync");
		var isEditEnv = $('[data-editenv]:eq(0)').data('editenv');
		var scrollTarget = isEditEnv == '1' ? '.design-right' : window;
		$goodsWrapper.length &&
			$(scrollTarget).on('scroll.goodsSync', my.throttle(function () {
				$goodsWrapper.each(function (i, element) {
					var $element = $(element),
						tplStatus = parseInt($element.attr('data-tplStatus'));
					if (!!tplStatus) { return };

					var screenH = (document.documentElement || document.body).clientHeight,
						scrollY = $(scrollTarget).scrollTop() || window.scrollY,
						eleOffsetH = $element.offset().top,
						wrapperH = $element.height(),
						spaceH = 100;
					if (eleOffsetH < screenH || screenH + scrollY + spaceH - (eleOffsetH + wrapperH) > 0 && screenH + scrollY + spaceH - (eleOffsetH + wrapperH) <= screenH) {
						$element.attr('data-tplStatus', 1)
						my.tplIntGoodsCallback($element)
					}
				})
			}, 100));
		/* 首屏加载 */
		$goodsWrapper.length && $goodsWrapper.each(function (i, element) {
			var $element = $(element),
				tplStatus = parseInt($element.attr('data-tplStatus'));
			if (!!tplStatus) { return };

			var screenH = (document.documentElement || document.body).clientHeight,
				eleOffsetH = $element.offset().top,
				scrollY = $(scrollTarget).scrollTop() || window.scrollY,
				wrapperH = $element.height(),
				spaceH = 100;
			if (eleOffsetH < screenH || screenH + scrollY + spaceH - (eleOffsetH + wrapperH) > 0) {
				$element.attr('data-tplStatus', 1)
				my.tplIntGoodsCallback($element)
			}
		});
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

	my.goodsCountDown = function (componentTarget) {
		var $target = $(componentTarget).find('.gs_countDown');
		$target.each(function () {
			var $self = $(this)
			var leftTime = parseInt($self.data('leftTime'))
			var seconds, minutes, hours, days, CDay, CHour, CMinute, CSecond
			if (!isNaN(leftTime) && leftTime >= 0) {
				seconds = leftTime
				minutes = Math.floor(seconds / 60)
				hours = Math.floor(minutes / 60)
				days = Math.floor(hours / 24)
				CDay = days
				CHour = hours % 24
				CMinute = minutes % 60
				CSecond = Math.floor(seconds % 60)

				CDay = CDay < 10 ? '0' + CDay : CDay
				CHour = CHour < 10 ? '0' + CHour : CHour
				CMinute = CMinute < 10 ? '0' + CMinute : CMinute
				CSecond = CSecond < 10 ? '0' + CSecond : CSecond

				$self.data('left-time', leftTime - 1)
				$self.html('<span><strong>' + CDay + 'DAY(S)</strong>' + CHour + ':' + CMinute + ':' + CSecond + '</span>')
			} else {
				$target = $target.not($self)
				$self.html('<span><strong>00 DAY(S) </strong>00:00:00</span>')
			}
		})
		if (0 !== $target.length) {
			setTimeout(function () {
				my.goodsCountDown(componentTarget);
			}, 1000)
		}
	}


	return my;
}({}))

/* 加载初始化 */
var staticDomain = $('[data-static-domain]:eq(0)').attr('data-static-domain') ? $('[data-static-domain]:eq(0)').attr('data-static-domain') : "";
$LAB.script(staticDomain + "/resources/javascripts/library/laytpl.js").wait(function () {
	GS_goodsSync.gsTplInt();
	GS_goodsSync.scrollEvent();
	// tplIntGoodsCallback();
});
