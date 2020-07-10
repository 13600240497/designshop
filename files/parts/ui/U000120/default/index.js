
$(function () {

    var goodsList = {};
    var goodsObject = {};
    var staticDomain = $('[data-static-domain]:eq(0)').attr('data-static-domain') ? $('[data-static-domain]:eq(0)').attr('data-static-domain') : "";
    var siteCode = GESHOP_SITECODE ? GESHOP_SITECODE.split('-')[0] : '';

    function renderCurrency () {
        /* currency initial in Web and Wap */
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

    /**
     * 初始化轮播图
     * @param {*} dataId 
     */
    function swiperinit(dataId) {
        var container = $('.tab' + dataId).find('.swiper-container');
        new Swiper3(container, {
            slidesPerView: 2,
            centeredSlides: false,
            spaceBetween: 0,
            pagination: {
                el: '.swiper-pagination',
                clickable: true,
            },
            prevButton: '.swiper-button-prev',
            nextButton: '.swiper-button-next',
        })
    }

    var LBTplObj = {
        renderTpl: function () {
            gs_laytpl.config({ open: "<%", close: "%>" });

            // 遍历 - 兼容多个组件各自获取数据
            $('.geshop-gift-async').each(function (i, element) {
                var $ele = $(element);
                var component_id = $ele.attr('data-id');
                var priceArray = [];
                $ele.find('.rg-goods-price-wrap li').each(function() {
                    priceArray.push($(this).text());
                });
                var getTpl = $ele.find('.pc-gift-template').html();
                var view = $ele.find('.giftviewBox');

                // 遍历当前组件每个赠品ID的数据
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
                        thresholdamount = $ele.find('.rg-goods-price-wrap').find('li:eq('+index+')').text() ?  $ele.find('.rg-goods-price-wrap').find('li:eq('+index+')').text()*1 : 0;
                        params.thresholdamount = thresholdamount;
                    }

                    var url = GESHOP_INTERFACE.fullgiftlist.url;
                    var content = { content: JSON.stringify(params) };

                    $.ajax({
                        url: url,
                        type: 'GET',
                        dataType: 'jsonp',
                        data: content,
                        jsonpCallback: 'geshop_callback_' + component_id + '_' + index,
                        cache: true,
                        success: function (res) {
                            if (res.code == 0) {
                                var mount = res.data.activityInfo.thresholdamount;

                                goodsObject[mount] = res.data;
                                goodsList[ids] = res.data;
                                renderCurrency();

                                if (siteCode == 'rg') {
                                    $item.find('.minPrice').eq(0).html('$' + priceArray[index]);
                                    $item.find('.minPrice').eq(0).attr('data-orgp', (priceArray[index] * 1).toFixed(2));
                                    /*价格换算*/
                                    if (typeof GEShopSiteCommon != 'undefined') {
                                        GEShopSiteCommon.renderCurrency();
                                    }
                                }else{
                                    $item.find('.minPrice').eq(0).html('$' + res.data.activityInfo.thresholdamount);
                                    $item.find('.minPrice').eq(0).attr('data-orgp', (res.data.activityInfo.thresholdamount * 1).toFixed(2));
                                    /*价格换算*/
									if (typeof GEShopSiteCommon != 'undefined') {
                                        GEShopSiteCommon.renderCurrency();
                                    }
                                }

                                if (index==0) {
                                    if (siteCode == 'rg') {
                                        gs_laytpl(getTpl).render(goodsObject[mount], function (html) {
                                            view.html(html);
                                            /*价格换算*/
                                            if (typeof GEShopSiteCommon != 'undefined') {
                                                GEShopSiteCommon.renderCurrency();
                                            }
                                        });
                                    } else {
                                        gs_laytpl(getTpl).render(goodsList[ids], function (html) {
                                            view.html(html);
                                            /*价格换算*/
                                            if (typeof GEShopSiteCommon != 'undefined') {
                                                GEShopSiteCommon.renderCurrency();
                                            }
                                        });
                                    }
                                    GESHOP_UTIL.goodsLazy(view.find('.js-geshopImg-lazyload'));
                                }

                                $('[data-gid="U000120_default"]').each(function(i,element){

                                        $(element).find(".goods-item").each(function(i,ele) {
                                            handleGoodsItem(ele,element);
                                        })
                                })

                            }
                        }
                    });
                });
            })
        }
    }

    /**
     * 模板初始化
     */
    $LAB.script(staticDomain + "/resources/javascripts/library/gs_laytpl.js?2018100101")
        .wait(function () {
            LBTplObj.renderTpl();
        });

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
                    /*价格换算*/
                    if (typeof GEShopSiteCommon != 'undefined') {
                        GEShopSiteCommon.renderCurrency();
                    }
                    // tab切换重新给页当前组件的商品绑定事件
                    $(loadHtml).find(".goods-item").each(function(i,ele) {
                        handleGoodsItem(ele,loadHtml)
                    })
                });
            } else {
                gs_laytpl(getTpl).render(goodsList[isd], function (html) {
                    view.html(html);
									/*价格换算*/
									if (typeof GEShopSiteCommon != 'undefined') {
                                        GEShopSiteCommon.renderCurrency();
                                    }
                    // tab切换重新给页当前组件的商品绑定事件
                    $(loadHtml).find(".goods-item").each(function(i,ele) {
                        handleGoodsItem(ele,loadHtml)
                    })
                });
            }
            GESHOP_UTIL.goodsLazy(view.find('.js-geshopImg-lazyload'));

        }
    });

     // 弹窗
    var body = '' +
    '<div class="gs-model-120" style="text-align:left;">' +
        '<a href="javascript:;" class="geshop-dialog-close"></a>' +
        '<div class="free">' +
            '<div class="free-text">' + GESHOP_LANGUAGES.free + '</div>' +
        '</div>' +
        '<div class="model-main">' +
            '<div class="model-left">' +
                '<img class="js_gdexp_lazy js-geshopImg-lazyload js_gbexp_lazy" src="https://geshopcss.logsss.com/imagecache/geshop-test/resources/images/default/good.png" alt="">' +
            '</div>' +
            '<div class="model-right">' +
                '<div class="goods-title">' + GESHOP_LANGUAGES.gift_tips + '</div>' +
                '<div class="item-price">' +
                    '<span class="shop-price">' +
                        '<strong class="my_shop_price" data-orgp="0.00" >$0.00</strong>' +
                    '</span>' +
                    '<span class="market-price">' +
                        '<del class="my_shop_price" data-orgp="0.00" >$0.00</del>' +
                    '</span>' +
                '</div>' +
                '<div class="select-content select-size">' +
                    '<div class="size-title">'+ GESHOP_LANGUAGES.size +'</div>' +
                    '<div class="size-box clearfix">' +
                        '<div class="size-item">1X</div>' +
                        '<div class="size-item">2X</div>' +
                    '</div>' +
                '</div>' +
                '<div class="select-content select-color">' +
                    '<div class="color-title">' + GESHOP_LANGUAGES.color + '</div>' +
                    '<div class="color-box clearfix">' +
                        '<div class="color-item"><span></span></div>' +
                        '<div class="color-item"><span></span></div>' +
                    '</div>' +
                '</div>' +
                '<div class="add-bag">'+ GESHOP_LANGUAGES.add_to_bag +'</div>' +
            '</div>' +
        '</div>' +
    '</div>';

    //添加失败弹窗
    var addFail = '' +
        '<div class="add-fail">' +
        GESHOP_LANGUAGES.gift_fail_add +
        '</div>';

    //防止请求时间过长点击多次出现弹出窗
    //var modelIndex = false;
    //货币符号
    var currency = '';
    var ids = "";
    var lang = typeof GESHOP_LANG != 'undefined' ? GESHOP_LANG : 'en';
    //打开弹出窗的序号
    var openIndex;
    //失败弹出窗的序号
    var failIndex;



    //给每个给每个商品添加绑定事件
    function handleGoodsItem(ele,element){
        var addBag = $(ele).find(".gs-add-bag");
        var goodsSn = $(ele).attr('data-goodsSn');
        currency = $(ele).find(".market-price span").text().substr(0,1);
        $(ele).on('click', '.gs-add-bag' ,function(e){
                e.stopPropagation();
                //GEShopSiteCommon.dialog.unblock();
                // 点击请求数据
                ids = +$(element).find(".swiper-slide>.active").attr('data-activityid-id');
                if (ids) {
                        //获取同SPU商品列表参数
                        var params = {
                            lang: lang,
                            goodsSn: goodsSn,
                            manzeng_id: ids,
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

    //打开模态框
    function openModel(obj){
        GEShopSiteCommon.dialog.content(body,752,434,function(){
            modelSuccess(obj);
            GEShopSiteCommon.dialog.createsClosingStyle();
            $('body').on('click', '.geshop-dialog-close', function () {
                GEShopSiteCommon.dialog.unblock();
            });
        },function(){

        });
        return false;
    }

    //打开消息弹出窗
    function failFn(res){
        GEShopSiteCommon.dialog.message(addFail);
        if(res){
            $('.add-fail').text(res.message);
        }
    }


    //请求接口数据
    function reqAjax (params,url,successFn,failFn) {
        var content = { content: JSON.stringify(params) };
            $.ajax({
                url: url,
                type: 'GET',
                dataType: 'jsonp',
                data: content,
                success: function (res) {
                    if (res.code == 0) {
                        //有商品的时候
                        var obj = res.data.goodsInfo;
                        // openModel(obj);
                        successFn(obj,res);
                    }else{
                        failFn(res);

                    }
                }
            });
    }


    //渲染模态框的数据
    function modelSuccess(obj){
        //如果默认没有数据,只显示弹出窗内容,不交互
        if(!obj){
            $(".size-item").eq(0).addClass("selected-color");
            $(".color-item").eq(0).addClass("selected-size");
            $(".gs-model-120 .shop-price strong").text( '$0.00');
            $(".gs-model-120 .market-price del").text('$0.00');
            $(".gs-model-120 .add-bag").on('click',function(){
                failFn();
            });
            return;
        }
        $(".gs-model-120  .goods-title").text(obj.goods_title);
        $(".gs-model-120 img").attr("src",obj.goods_img);
        // $(".gs-model-120 .shop-price strong").text(currency + obj.shop_price);
        // $(".gs-model-120 .shop-price del").text(currency + obj.market_price);
        $(".gs-model-120 .shop-price strong").attr({"data-orgp":obj.shop_price}).text('$' + obj.shop_price);
        $(".gs-model-120 .market-price del").attr({"data-orgp":obj.market_price}).text('$' + obj.market_price);

        //尺寸模板
        var sizeHtml = '';
        var sizeArr = [];
        obj.size_list.forEach(function(size,i){
            sizeArr.push(size.title);
            sizeHtml+= '<div class="size-item" data-goodsku="' + size.goods_sn + '">' + size.title + '</div>';
        })

        $(".gs-model-120 .size-box").html(sizeHtml);

        var sizeIndex = sizeArr.indexOf(obj.size);
        if (sizeIndex >=0 ) {
            $(".size-item").eq(sizeIndex).addClass("selected-size");
        }

        if(sizeArr.length <= 0){
            $(".gs-model-120 .select-size").hide();
        }
        //颜色模板
        var colorHtml = '';
        var colorArr = [];
        obj.color_list.forEach(function(color,i){
            colorArr.push(color.color_value);

            if ($.trim(color.color_img).length == 0) {
                colorHtml += '<div class="color-item" data-goodsku="' + color.goods_sn + '"><span style="background-color:' + color.color_value + '"></span></div>';
            } else {
                colorHtml+= '<div class="color-item" data-goodsku="' + color.goods_sn + '"><img src="' + color.color_img + '" width="100%" height="100%"></div>';
            }
        })
        $(".gs-model-120 .color-box").html(colorHtml);
        var colorIndex = colorArr.indexOf(obj.color);
        if (colorIndex >=0 ) {
            $(".color-item").eq(colorIndex).addClass("selected-color");
        }

        $(".color-item,.size-item").off('click');
        $(".color-item,.size-item").on('click',function(){
            var sku = $(this).attr('data-goodsku');
            //获取同SPU商品列表参数
            var params = {
                lang:lang,
                goodsSn:sku,
                manzeng_id:ids,
                pipeline: (typeof GESHOP_PIPELINE != 'undefined' ? GESHOP_PIPELINE : '')
            };
            var url = GESHOP_INTERFACE.getlistinspu.url;
            reqAjax(params,url,modelSuccess,failFn);
        })

        //点击加入购物车,请求接口,如果不符合弹出消息框
        $(".gs-model-120 .add-bag").off('click');
        $(".gs-model-120 .add-bag").on('click',function(){
            //添加购物车参数
            var params = {
                lang:lang,
                goodsSn:obj.goods_sn,
                manzeng_id:ids,
                pipeline: (typeof GESHOP_PIPELINE != 'undefined' ? GESHOP_PIPELINE : '')
            };
            var url = GESHOP_INTERFACE.addgifttocart.url;
            reqAjax(params,url,addSuccess,failFn);
            function addSuccess(obj,res){
                GEShopSiteCommon.dialog.unblock();
                GEShopSiteCommon.dialog.message( GESHOP_LANGUAGES.gift_add_cart);
            }
        });



        /* currency initial in Web and Wap */
        if (window.GLOBAL && window.GLOBAL.currency) {
            window.GLOBAL.currency.change_html()
        }

    };

});
