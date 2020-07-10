(function () {
	function bpGoodsInit ($item, params) {
		
		/* 
			创建产品Html Node 标签
			@params {
				...商品列表接口返回的字段
				leftPercent: 剩余百分比
			}
			return String
		*/
		function createGoodNode(template, goodsInfo) {
			var _site = GESHOP_SITECODE.match(/\w*/)[0];
			var _temp = template;
				_temp = _temp.replace(/{goods_title}/g, goodsInfo.goods_title);
				_temp = _temp.replace(/{integral_price}/g, goodsInfo.integral_price);
				_temp = _temp.replace(/{market_price}/g, _site == 'zf' ? goodsInfo.shop_price : goodsInfo.market_price);
				_temp = _temp.replace(/{integral}/g, goodsInfo.integral);
				_temp = _temp.replace(/{url_title}/g, goodsInfo.url_title);
				_temp = _temp.replace(/XX/g, goodsInfo.activity_left_number || 0);
				_temp = _temp.replace(/{goods_img}/g, goodsInfo.goods_img || defaultImg);
			return '<li>'+_temp+'</li>';
		}


		var that = $item;
		// 产品html模版
		var template = that.find('.geshop-BP-good-template').clone().html();
		// 产品列表 dom
		var GoodsList = that.find('.geshop-BP-good-list ul');
		// 判断环境
		var isEditEnv = that.attr('data-isEditEnv');
		// 默认图片
		var defaultImg = 'https://geshopimg.logsss.com/uploads/nxQshzC1wXTYy8BmHD6GE39vLWjciKaR.png';
		
		if (params.goodsSKU!='') {
			var requestParams = {
				goodsSn: params.goodsSKU,
				lang:  typeof GESHOP_LANG != 'undefined' ? GESHOP_LANG : 'en',
				pipeline: (typeof GESHOP_PIPELINE != 'undefined' ? GESHOP_PIPELINE : '')
			};
			$.ajax({
				url: GESHOP_INTERFACE.redeemlist.url,
				type: 'GET',
				dataType: 'jsonp',
				data: {content: JSON.stringify(requestParams)},
				success: function (res) {
					if (res.code == 0 && res.data.goodsInfo.length > 0) {
						var dataRows = res.data.goodsInfo;
						var count = 0;
						for( i in dataRows ) {
							var obj = dataRows[i];
							// 判断库存
							if (parseInt(obj.activity_left_number) > 0) {
								// 只展示前4个
								if (count < 4) {
									GoodsList.append(createGoodNode(template, obj));
									count++;
								}
							}
						}
						// 如果大于0件则展示组件
						if (count >= 1 || isEditEnv == 1) {
							that.show();
						}
						if (window.GLOBAL && window.GLOBAL.currency) {
							window.GLOBAL.currency.change_html()
						}
						if (window.FUN && window.FUN.currency) {
							window.FUN.currency.change_html()
						}

						// 图片懒加载初始化
						if (typeof GS_GOODS_LAZY_FN != "undefined") {
							GS_GOODS_LAZY_FN(GoodsList.find('.BP-goods-img img.js-geshopImg-lazyload'))
						} else {
							GoodsList.find('.BP-goods-img img.js-geshopImg-lazyload').each((index, item) => {
								$(item).attr('src', $(item).data('original'));
							})
						}
					} else {
						if (isEditEnv==1) {
							that.show();
						} else {
							that.hide();
						}
					}
				}
			});
		} else {
			var goodTestData = {
				url_title: '/',
				goods_title: 'Floral Print Mini Wrap Tea Dress Print Mini Wrap Tea Dress',
				goods_img: '',
				shop_price: '0.00',
				market_price: '0.00',
				integral_price: '0.00',
				integral: 99,
				activity_left_number: 100,
			};
			var dataRows = [goodTestData, goodTestData, goodTestData, goodTestData];
			for(var i=0; i<dataRows.length; i++) {
				var obj = dataRows[i];
				GoodsList.append(createGoodNode(template, obj));
			}
			that.show();
			that.find('.BP-goods-img').addClass('goods-empty-status')
		}
	}

	$(function () {
		$('.geshop-U000164-default-v3').each(function (i, v) {
			var $this = $(this);
			var pageInstanceId = $this.data('id');
			var key = $this.data('key');
			bpGoodsInit($this, window[key + '_' + pageInstanceId]);
		});
	});
})();