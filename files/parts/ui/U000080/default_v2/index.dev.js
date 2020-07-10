function getTplProduct(skus) {
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
		'rw-wap': 'https://m.rosewholesale.com/geshop/interface/goods-detail-by-sku/',
		'rg-pc': 'https://www.rosegal.com/m-interface-a-getGoodsList.html',
		'rg-wap': 'https://m.rosegal.com/m-interface-a-getGoodsList.html',
		'zf-pc': 'https://www.zaful.com/api/get_seckill_api.php?method=getGoods',
		'zf-wap': 'https://www.zaful.com/api/get_seckill_api.php?method=getGoods'
	}

	var paramGroup = {
		'rw-pc': { content: JSON.stringify(params) },
		'rw-wap': { content: JSON.stringify(params) },
		'rg-pc': params,
		'rg-wap': params,
		'zf-pc': zafulParams,
		'zf-wap': zafulParams
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

function get_a_analytics({ pageId, pageInstanceId, compKey, uiIndex, sku, layoutIndex, columnIndex, cateid, warehousecode }) {
    return `{'pm':'mp','p':'p-${pageId}','ubcta':{'cpID':'${pageInstanceId}','cpnum':'${compKey}','cplocation':'${uiIndex}','sku':'${sku}','cporder':'${layoutIndex}','rank':'${columnIndex}'},'skuinfo':{'sku':'${sku}','pam':'0','pc':'${cateid}','k':'${warehousecode}'} }`
}

function get_img_analytics({ pageId, pageInstanceId, compKey, uiIndex, layoutIndex, columnIndex, sku }) {
    return `{ 'pm':'mp','p':'p-${pageId}','bv':{'cpID':'${pageInstanceId}','cpnum':'${compKey}','cplocation':'${uiIndex}','sku':'${sku}','cporder':'${layoutIndex}','rank':'${columnIndex}'} }`;
}

$(function() {

    /* 加载初始化 */
    var staticDomain = $('[data-static-domain]:eq(0)').attr('data-static-domain') ? $('[data-static-domain]:eq(0)').attr('data-static-domain') : "";
    $LAB.script(staticDomain + "/resources/javascripts/library/gs_laytpl.js?2018100101").wait(function () {
        
        // 初始化配置
        gs_laytpl.config({ open: '<%', close: '%>' });
        
        $('.geshop-U000080-default').each(function() {
            var template = $(this).find('.geshop-U000080-default-tempalte').html();
            var view = $(this).find('.geshop-U000080-default-wrapper ul');
            var sku = $(this).attr('data-skus');
            var pageId = $(this).attr('data-pageId');
            var pageInstanceId = $(this).attr('data-pageInstanceId');
            var compKey = $(this).attr('data-compKey');
            var uiIndex = $(this).attr('data-uiIndex');
            var layoutIndex = $(this).attr('data-layoutIndex');
            var list = [
                {
                    goods_title: 'ZAFUL Fleece Vest And Corduroy Jack',
                    shop_price: 0,
                    market_price: 0,
                    discount: 0,
                    goods_img: 'https://geshopimg.logsss.com/uploads/nxQshzC1wXTYy8BmHD6GE39vLWjciKaR.png',
                    url_title: '',
                },
                {
                    goods_title: 'ZAFUL Fleece Vest And Corduroy Jack',
                    shop_price: 0,
                    market_price: 0,
                    discount: 0,
                    goods_img: 'https://geshopimg.logsss.com/uploads/nxQshzC1wXTYy8BmHD6GE39vLWjciKaR.png',
                    url_title: '',
                },
                {
                    goods_title: 'ZAFUL Fleece Vest And Corduroy Jack',
                    shop_price: 0,
                    market_price: 0,
                    discount: 0,
                    goods_img: 'https://geshopimg.logsss.com/uploads/nxQshzC1wXTYy8BmHD6GE39vLWjciKaR.png',
                    url_title: '',
                }];

            if (sku) {
                // AJAX 请求
                getTplProduct(sku).done(function (res) {
                    // 组装数据
                    list = [];
                    let index = 0;
                    Object.keys(res.data).map(function(key) {
                        var product = res.data[key]
                        list.push({
                            goods_title: product.goods_title,
                            shop_price: product.shop_price || 0,
                            market_price: product.market_price || 0,
                            discount: product.discount || 0,
                            goods_img: product.goods_img,
                            url_title: product.url_title,
                            a_analytics: get_a_analytics({
                                pageId,
                                pageInstanceId,
                                compKey,
                                uiIndex,
                                layoutIndex,
                                columnIndex: index,
                                sku: product.goods_sn,
                                cateid: product.cat_id,
                                warehousecode: product.warecode
                            }),
                            img_analytics: get_img_analytics({
                                pageId,
                                pageInstanceId,
                                compKey,
                                uiIndex,
                                layoutIndex,
                                columnIndex: index,
                                sku: product.goods_sn,
                            }),
                            goods_number: product.goods_number || 0
                        })
                        index++;
                    })

                    // goods_number为0, 排序放到最后
                    var listArr = [];
                    list.forEach(function (item, index) {
                        if (item.goods_number == 0) {
                            listArr.push(item);
                            list.splice(index, 1);
                        }
                    });
                    list = list.concat(listArr);

                    // 渲染
                    view.html(gs_laytpl(template).render(list));
                    GS_GOODS_LAZY_FN && GS_GOODS_LAZY_FN(view.find('.js-geshopImg-lazyload'));
                })
            } else {
                view.html(gs_laytpl(template).render(list));
            }
        });
        
    });





})
