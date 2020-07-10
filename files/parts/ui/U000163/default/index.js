$(function() {
	if (!GS_spikeGoodsTab) {

		var staticDomain = typeof $('[data-static-domain]:eq(0)').attr("data-static-domain") != 'undefined' ? $('[data-static-domain]:eq(0)').attr("data-static-domain") : '';

		var GS_spikeGoodsTab = (function () {

		}({}));

		/* 倒计时初始化,tpl初始化 */
		if (!gs_preProGlobal) {
			var gs_preProGlobal = function (my) {

				my.extendDeep = function (parent, child) {
					var i,
						toStr = Object.prototype.toString,
						astr = "[object Array]";

					child = child || {};

					for (i in parent) {
						if (parent.hasOwnProperty(i)) {
							if (typeof parent[i] === "object") {
								child[i] = toStr.call(parent[i]) === astr ? [] : {};
								my.extendDeep(parent[i], child[i]);
							} else {
								child[i] = parent[i];
							}
						}
					}
					return child;
				};

				my.gsTplInt = function () {
					gs_laytpl.config({ open: "<%", close: "%>" });
					if (typeof GEShopSiteCommon != 'undefined') {
						GEShopSiteCommon.renderCurrency();
					}
				}


				my.getTplProduct = function (skus, element) {
					var lang = typeof GESHOP_LANG != 'undefined' ? GESHOP_LANG : 'en';
					var params = {
						lang: lang,
						goodsSn: skus,
						pipeline: (typeof GESHOP_PIPELINE != 'undefined' ? GESHOP_PIPELINE : '')
					};

					var url = GESHOP_INTERFACE.prepromotion.url;
                    var pid = $(element).parents('.geshop-component-box').eq(0).attr('data-id');
                    return window.GEShopCommonFn_Vue.$jsonp(url,params);
				}
				/* 模板数据填充 */
				my.getTplInitData = function () {

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
				};

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

                // goods_number库存为0时，排序最后
                my.sortData = function (data) {
                    var listArr = [];
                    data.forEach(function (item, index) {
                        if (item.goods_number && item.goods_number == 0) {
                            listArr.push(item);
                            data.splice(index, 1);
                        }
                    });

                    data = data.concat(listArr);
                    return data;
                }

				return my
			}(gs_preProGlobal || {})
		}

		/* target tab target */
		function tplPreProIntCallback (index, target) {
			var element = target;
			var currentSkus = $(element).find('.gs-tab-item').data('skus');
			var skuLimit = $(element).find('.gs-tab-item').data('skulimit');
			var $tpl = $(element).find('.gs_syncDefault');
			var tplHtml = $tpl.html();
			if (!currentSkus) { return false };
			var isEditEnv = $('[data-editenv]:eq(0)').data('editenv');

			gs_preProGlobal.getTplProduct(currentSkus,target).done(function (res) {
				var dataList = res.data.goodsInfo;
				if (res.code == '0' && dataList.length) {
                    var list = gs_preProGlobal.sortData(dataList);

					gs_laytpl.config({ open: "<%", close: "%>" });
					gs_laytpl(tplHtml).render(list, function (html) {
						if (html) {
							$(element).find('.gs-goodsWrap ul').html(html);

							// 控制显示sku数量
							if (typeof skuLimit === "number") {
								if (skuLimit == 0) {
									$(element).find('.gs-goodsWrap li').hide();
								} else {
									$(element).find('.gs-goodsWrap li:gt('+(skuLimit-1)+')').hide();
								}
							}

							// 图片懒加载初始化
                            window.GS_GOODS_LAZY_FN && window.GS_GOODS_LAZY_FN();

							/* 价格换算 rw*/
							if (typeof GLOBAL != 'undefined' && GLOBAL.currency.change_html) {
								var bizhong = gs_preProGlobal.getCookie('bizhong') || 'USD';
								var $wrapElem = $('.gs-goods-tab')
								GLOBAL.currency.change_html(bizhong, $wrapElem)
							}

							// 移除隐藏的列表项
							$(element).find('.gs-goodsWrap li.hide').remove();

							// 若无显示列表项或全为售空状态，整个组件隐藏
							var liNotSoldoutLen = $(element).find('.gs-goodsWrap li:not(.prePromotion_soldout)').length;
							var liVisibleLen = $(element).find('.gs-goodsWrap li').length;
							if (liVisibleLen == 0 || liNotSoldoutLen == 0) {
								if (isEditEnv == '0') {
									$(element).hide();
								}
							}
							if (typeof GEShopSiteCommon != 'undefined') {
								GEShopSiteCommon.renderCurrency();
							}
						}
					})
				} else if (dataList.length == 0) {

				}
			}).fail(function () {

			})
			if (typeof GEShopSiteCommon != 'undefined') {
				GEShopSiteCommon.renderCurrency();
			}
		}

		function tabPreProScrollEvent () {
			var $goodTarget = $('[data-gid="U000163_default"] .gs-goods-tab');
			$goodTarget.length && $goodTarget.each(function (i, element) {
				tplPreProIntCallback(i, element);

			})
		}

		/* 加载初始化 */
		$LAB
			.script(staticDomain + "/resources/javascripts/library/gs_laytpl.js?2018102901").wait(function () {
				gs_preProGlobal.gsTplInt();
				tabPreProScrollEvent();
			});

	} else {
		/* edit */

		tabPreProScrollEvent();
	}
});
