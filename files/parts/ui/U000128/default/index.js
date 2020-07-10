$(function () {
    var goodsList = {};
    var goodsObject = {};
   var staticDomain = $('[data-static-domain]:eq(0)').attr('data-static-domain') ? $('[data-static-domain]:eq(0)').attr('data-static-domain') : "";
   var siteCode = GESHOP_SITECODE ? GESHOP_SITECODE.split('-')[0] : '';

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

    /**
     * 模版引擎配置
     */
    var LBTplObj = {
        renderTpl: function () {
            gs_laytpl.config({ open: "<%", close: "%>" });

            // 遍历 - 兼容多个组件各自获取数据
            $('.geshop-gift-async').each(function (i, element) {
                var $ele = $(element);
                var getTpl = $ele.find('.pc-gift-template').html();
                var view = $ele.find('.giftviewBox');
                var pid = $ele.attr('data-id');

                $ele.find('.swiper-slide-item').each(function (index, item) { 
                    var thresholdamount = 0; // rg站点thresholdamount字段
                    var $item = $(item);
                    var ids = $item.attr('data-activityid-id');
                    
                    var lang = typeof GESHOP_LANG != 'undefined' ? GESHOP_LANG : 'en';

                    var pagesize = $ele.find('.pagesize').val();
                    if (pagesize == '0') {
                        pagesize = 20;
                    }
                    var params = {
                        lang: lang,
                        activityid: ids,
                        pageno: 1,
                        pagesize: pagesize,
                        pipeline: (typeof GESHOP_PIPELINE != 'undefined' ? GESHOP_PIPELINE : '')
                    };

                    /* rg站点传thresholdamount字段 */
                    if (siteCode == 'rg') {
                        params.thresholdamount = 0;
                        thresholdamount = $ele.find('.rg-goods-price-wrap').find('li:eq('+index+')').text() ? $ele.find('.rg-goods-price-wrap').find('li:eq('+index+')').text()*1 : 0;
                        params.thresholdamount = thresholdamount;
                    }

                    var url = GESHOP_INTERFACE.fullgiftlist.url;
                    var jsonpCallback = 'geshop_callback_' + pid + '_' + index;
                    window.GEShopCommonFn_Vue.$jsonp(url, params, {jsonpCallback}).done(function (res) {
                        if (res.code == 0) {
                            var mount = res.data.activityInfo.thresholdamount;

                            goodsObject[mount] = res.data
                            goodsList[ids] = res.data;

                            if (siteCode == 'rg') { } else {
                                $item.find('.minPrice').eq(0).html('$' + res.data.activityInfo.thresholdamount);
                                $item.find('.minPrice').eq(0).attr('data-orgp', res.data.activityInfo.thresholdamount);
                            }
                            if (typeof GEShopSiteCommon != 'undefined') {
                                GEShopSiteCommon.renderCurrency();
                            }

                            if (index == 0) {
                                if (siteCode == 'rg') {
                                    gs_laytpl(getTpl).render(goodsObject[mount], function (html) {
                                        view.html(html);
                                        if (typeof GEShopSiteCommon != 'undefined') {
                                            GEShopSiteCommon.renderCurrency();
                                        }
                                    });
                                } else {
                                    gs_laytpl(getTpl).render(goodsList[ids], function (html) {
                                        view.html(html);
                                        if (typeof GEShopSiteCommon != 'undefined') {
                                            GEShopSiteCommon.renderCurrency();
                                        }
                                    });
                                }
                            }

                        }
                    });
                });
            });
        }
    }

    /**
     * 给每个给每个商品添加绑定事件
     * @param {*} ele 
     * @param {*} element 
     */
    function handleGoodsItem(ele, element){
        var addBag = $(ele).find(".gs-add-bag");
        var goodsSn = $(ele).attr('data-goodsSn');
        currency = $(ele).find(".market-price span").text().substr(0,1);
        addBag.on('click',function(e){
                e.stopPropagation();
                if(modelIndex){
                    return;
                }
                modelIndex = true;
                // 点击请求数据
                ids = +$(element).find(".swiper-slide>.active").attr('data-activityid-id');
                if (ids) {
                        //获取同SPU商品列表参数
                        var params = {
                            lang:lang,
                            goodsSn:goodsSn,
                            manzeng_id:ids,
                            pipeline: (typeof GESHOP_PIPELINE != 'undefined' ? GESHOP_PIPELINE : '')
                        };
                        var url = GESHOP_INTERFACE.getlistinspu.url;
                        reqAjax(params,url,openModel,failFn);
                } else {
                    //没有商品的时候
                    openModel(false);
                }
                return false;
            })
    }

    /**
     * 模板初始化
     */
    $LAB.script(staticDomain + "/resources/javascripts/library/gs_laytpl.js?2018100101").wait(function () {
        LBTplObj.renderTpl();
    });

    /**
     * 绑定点击事件
     */
    $('.geshop-tabs').click(function (e) {
        if (e.target.className == 'swiper-slide-item') {

            $(e.target).parents('.geshop-gift-async').find('.swiper-slide-item').removeClass('active');
            $(e.target).addClass('active');
            var i = ($(e.target).find('.minPrice').attr('data-orgp')*1).toFixed(0);
            var isd = $(e.target).attr('data-activityid-id');
            var getTpl = $(e.target).parents('.geshop-gift-async').find('.pc-gift-template').html();
            var view = $(e.target).parents('.geshop-gift-async').find('.giftviewBox');
            var loadHtml = $(e.target).parents('.geshop-gift-async');

            if (siteCode == 'rg') {
                gs_laytpl(getTpl).render(goodsObject[i], function (html) {
                    view.html(html);
                    // tab切换重新给页当前组件的商品绑定事件
                    $(loadHtml).find(".goods-item").each(function(i,ele) {
                        handleGoodsItem(ele,loadHtml)
                    })
                    if (typeof GEShopSiteCommon != 'undefined') {
                        GEShopSiteCommon.renderCurrency();
                    }
                });
            } else {
                gs_laytpl(getTpl).render(goodsList[isd], function (html) {
                    view.html(html);
                    // tab切换重新给页当前组件的商品绑定事件
                    $(loadHtml).find(".goods-item").each(function(i,ele) {
                        handleGoodsItem(ele,loadHtml)
                    })
                    if (typeof GEShopSiteCommon != 'undefined') {
                        GEShopSiteCommon.renderCurrency();
                    }
                });
            }

            
        }
    })


});
