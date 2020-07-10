
(function () {

	var staticDomain = $('[data-static-domain]:eq(0)').attr('data-static-domain') ? $('[data-static-domain]:eq(0)').attr('data-static-domain') : "",
		rankdetailObj = GESHOP_INTERFACE ? GESHOP_INTERFACE.getrankdetail : {};

	loadCss(staticDomain + '/resources/javascripts/library/swiper/swiper.min.css');

	$LAB.script(staticDomain + '/resources/javascripts/library/swiper/swiper.3.4.spec.min.js');

	var LBTplObj = {
		renderTpl: function () {
			gs_laytpl.config({ open: "<%", close: "%>" });

			// 遍历 - 兼容多个组件各自获取数据
			$('.wrap-U000090-default').each(function (i, element) {
				var $ele = $(element);

				// 异步接口type
				var type = parseInt($ele.find('.leader-board-container').attr('data-asyncdata-id'));
				var cate_id = parseInt($ele.find('.leader-board-container').attr('data-cate-id'));

				var getTpl = $ele.find('.pc-leader-board-template').html(),
					view = $ele.find('.leader-board-container'),
					lang = typeof GESHOP_LANG != 'undefined' ? GESHOP_LANG : 'en';

				var params = {
					type: type,
					lang: lang,
					pageno: 1,
					pagesize: 12,
					cateid: cate_id,
					pipeline: (typeof GESHOP_PIPELINE != 'undefined' ? GESHOP_PIPELINE : '')
				};

				var url = rankdetailObj.url;

				// 组件已编辑过
				if (type) {
				    window.GEShopCommonFn_Vue.$jsonp(url, params, {target:element}).done(function (res) {
                        var data = res.data;
                        data.type = type;
                        gs_laytpl(getTpl).render(data, function (html) {
                            view.html(html);
                            if (window.GLOBAL && window.GLOBAL.currency) {
                                window.GLOBAL.currency.change_html()
                            }
                            if (window.FUN && window.FUN.currency) {
                                window.FUN.currency.change_html()
                            }
                            renderSwiperFn(element);
                            // 图片懒加载初始化
                            if (window.GESHOP_UTIL && typeof window.GESHOP_UTIL.goodsLazy === 'function') {
                                window.GESHOP_UTIL.goodsLazy($('.js-leader-board-container-U000090:eq('+i+')').find('.js-geshopImg-lazyload'));
                            }
                        });
                    });
				}
				// 首次拖入组件
				else {
					var data = { goodsInfo: [] };
					gs_laytpl(getTpl).render(data, function (html) {
						view.html(html);
						if (window.GLOBAL && window.GLOBAL.currency) {
							window.GLOBAL.currency.change_html()
						}
						if (window.FUN && window.FUN.currency) {
							window.FUN.currency.change_html()
						}
						renderSwiperFn(element);
					});
				}
			});

		}
	}

	

  /**
   * 模板初始化
   */
	$LAB
		.script(staticDomain + "/resources/javascripts/library/gs_laytpl.js?2018100601").wait(function () {
			LBTplObj.renderTpl();
		});

  /**
   * 初始化swiper
   * @param {*} element - 当前组件容器元素
   */
	function renderSwiperFn (element) {
		var swiperContainer = $(element).find('.swiper-container');
		var prevButtonElement = $(element).find('.swiper-button-prev');
		var nextButtonElement = $(element).find('.swiper-button-next');

		new Swiper3(swiperContainer, {
			cssWidthAndHeight: true,
			slidesPerView: 3,
			spaceBetween: 10,
			autoResize: false,
			slidesPerGroup: 3,
			preventClicks: false,// 当swiper在触摸时阻止默认事件
			followFinger: false, // 禁止拖拽
			autoplay: 5000,
			loop: true,
			paginationClickable: true,
			prevButton: prevButtonElement,
			nextButton: nextButtonElement,
			lazyLoading: true,
			onLazyImageLoad: function (swiper, slide, image) {
				if (typeof gbLogsss != 'undefined') {
					gbLogsss.getsku($(image));
					gbLogsss.sendsku();
				}
			}
		});
	}
})();
