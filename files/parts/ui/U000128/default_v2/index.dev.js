$(function () {
    var lang = typeof GESHOP_LANG != 'undefined' ? GESHOP_LANG : 'en';
    var goodsList = {};
   var staticDomain = $('[data-static-domain]:eq(0)').attr('data-static-domain') ? $('[data-static-domain]:eq(0)').attr('data-static-domain') : "";

    function renderCurrency () {
        if (window.GLOBAL && window.GLOBAL.currency) {
            window.GLOBAL.currency.change_html()
        }
        if (window.FUN && window.FUN.currency) {
            window.FUN.currency.change_html()
        }
    }

    loadCss(staticDomain + '/resources/javascripts/library/swiper/swiper.min.css');
    $LAB.script(staticDomain + '/resources/javascripts/library/swiper/swiper.3.4.spec.min.js').wait(function () {
        setTimeout(function () {
            $('.geshop-gift-async').each(function (i, element) {
                var $ele = $(element);
                var dataId = $ele.attr('data-id');
                var goodsIDnum = $ele.find('.data-goodsIDnum-domain').attr('data-goodsIDnum-domain');
                if (goodsIDnum > 2) {
                    $('.tab' + dataId + '.swiper-slide').show();
                    $('.tab' + dataId).find('.swiper-slide').show();
                    swiperinit(dataId);
                }
            })
        }, 20);
    }) 

    // 初始化 swiper
    function swiperinit(dataId) {

        var container = $('.tab' + dataId).find('.swiper-container');
        new Swiper3(container, {
            slidesPerView: 2,
            centeredSlides: false,
            spaceBetween: 0,
            slideToClickedSlide:true,
            prevButton: '.swiper-button-prev',
            nextButton: '.swiper-button-next',

        })
    }

    // 渲染商品列表HTML
    function renderGoodsList(template, target, data) {
        gs_laytpl(template).render(data, function (html) {
            target.html(html);
            /*价格换算*/
            renderCurrency();
            // 图片懒加载
            if (window.GS_GOODS_LAZY_FN) {
                window.GS_GOODS_LAZY_FN($('img.js_gdexp_lazy'))
            }
            // tab切换重新给页当前组件的商品绑定事件
            $('.U000128_default_v2').each(function(i,element){
                $(element).find(".goods-item").each(function(i, ele) {
                    handleGoodsItem(ele,element);
                })
            })
        });
    }


    function initEach() {
        gs_laytpl.config({ open: "<%", close: "%>" });
        
        // 遍历 - 兼容多个组件各自获取数据
        $('.geshop-gift-async').each(function (i, element) {
            var $ele = $(element);
            var tamplte = $ele.find('.pc-gift-template').html();
            var view = $ele.find('.giftviewBox');
            var max = parseInt($ele.attr('data-max'));

            $ele.find('.swiper-slide-item').each(function (index, item) {
                var $item = $(item);
                var ids = $item.attr('data-activityid-id');
                var params = {
                    lang: GESHOP_LANG || 'en',
                    activityid: ids,
                    each_activity_count: max,
                    pipeline: (typeof GESHOP_PIPELINE != 'undefined' ? GESHOP_PIPELINE : '')
                };
                var url = GESHOP_INTERFACE.fullgiftlist.url;
                var pid = $(element).attr('data-id');
                var jsonpCallback = 'geshop_callback_' + pid + '_' + index;
                
                window.GEShopCommonFn_Vue.$jsonp(url, params, { jsonpCallback: jsonpCallback }).done(function (res) {
                    if (res.code != 0) return false;

                    var list = [];
                    var goodsInfo = res.data.goodsInfo;

                    // 判断状态
                    var localtime = new Date().getTime();
                    goodsInfo.map(function(item, index) {
                        item['is_ready'] = (localtime < parseInt(item.activityInfo_starttime) * 1000);
                        item['is_ended'] = (localtime > parseInt(item.activityInfo_endtime) * 1000);
                        // 更新 tab 上的价格
                        $item.find('.minPrice').eq(0).html('$' + item.activityInfo_thresholdamount);
                        $item.find('.minPrice').eq(0).attr({
                            'data-orgp': (item.activityInfo_thresholdamount * 1).toFixed(2),
                            'data-currency': item.currency,
                            'data-original_amount': item.currency_original_amount
                        });

                        // 根据端获取不同的跳转链接，M端和APP端
                        item['final_location_url'] = (GESHOP_PLATFORM === 'app' ? item.app_gift_url : item.url_gift) || geshopUrlToApp(item.url_title, item.goods_id);

                        // goods_number 库存等于0时，商品排序到最后
                        if (item.goods_number && item.goods_number == 0) {
                            list.push(item);
                            goodsInfo.splice(index, 1);
                        }
                    });
                    goodsInfo = goodsInfo.concat(list);

                    // 重新组装
                    var oObj = {
                        goodsInfo: goodsInfo,
                        lang: res.data.lang
                    }

                    goodsList[ids] = oObj;
                    if (index > 0) return false;
                    // 渲染商品列表
                    renderGoodsList(tamplte, view, goodsList[ids]);
                
                });
            });
        })
    }


    /**
     * 模板初始化
     */
    $LAB.script(staticDomain + "/resources/javascripts/library/gs_laytpl.js?2018100101").wait(function () {
        initEach();
    });

    // 点击切换tab
    $('.geshop-tabs').click(function (e) {
        if (e.target.className == 'swiper-slide-item') {
            $(e.target).parents('.geshop-gift-async').find('.swiper-slide-item').removeClass('active');
            $(e.target).addClass('active');
            var id = $(e.target).attr('data-activityid-id');
            var getTpl = $(e.target).parents('.geshop-gift-async').find('.pc-gift-template').html();
            var view = $(e.target).parents('.geshop-gift-async').find('.giftviewBox');
            // 渲染HTML
            renderGoodsList(getTpl, view, goodsList[id]);
        }
    });

    //给每个给每个商品添加绑定事件
    function handleGoodsItem(ele, element){
    }

});
