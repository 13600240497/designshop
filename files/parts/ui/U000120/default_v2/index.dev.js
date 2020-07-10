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
            pagination: {
                el: '.swiper-pagination',
                clickable: true,
            },
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
            $('[data-gid="U000120_default"]').each(function(i,element){
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
                window.GEShopCommonFn_Vue.$jsonp(url, params, {target:element}).done(function (res) {
                    if (res.code != 0) return false;

                    var list = [];
                    var goodsInfo = res.data.goodsInfo;

                    // 判断状态
                    var localtime = new Date().getTime();
                    goodsInfo.map(function(item, index) {
                        item['is_ready'] = (localtime < item.activityInfo_starttime * 1000);
                        item['is_ended'] = (localtime > item.activityInfo_endtime * 1000);
                        // 更新 tab 上的价格
                        $item.find('.minPrice').eq(0).html('$' + item.activityInfo_thresholdamount);
                        $item.find('.minPrice').eq(0).attr({
                            'data-orgp': (item.activityInfo_thresholdamount * 1).toFixed(2),
                            'data-currency': item.currency,
                            'data-original_amount': item.currency_original_amount
                        });

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
                })
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


    // 加购弹窗
    var body = '' +
        '<div class="gs-model-120" style="text-align:left;">' +
        '<a href="javascript:;" class="geshop-dialog-close"></a>' +
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
        '<div class="color-title" style="width: auto;">' + GESHOP_LANGUAGES.color + '</div>' +
        '<div class="color-box clearfix">' +
        '<div class="color-item"><span></span></div>' +
        '<div class="color-item"><span></span></div>' +
        '</div>' +
        '</div>' +
        '<div class="add-bag logsss_event js_logsss_click_delegate">'+ GESHOP_LANGUAGES.add_to_bag +'</div>' +
        '</div>' +
        '</div>' +
        '</div>';

    //添加失败弹窗
    var addFail = `<div class="add-fail">${GESHOP_LANGUAGES.gift_fail_add}</div>`;

    //防止请求时间过长点击多次出现弹出窗
    //var modelIndex = false;
    //货币符号
    var currency = '';
    var ids = "";
    //打开弹出窗的序号
    var openIndex;
    //失败弹出窗的序号
    var failIndex;



    //给每个给每个商品添加绑定事件
    function handleGoodsItem(ele) {
        ids = $(ele).attr('data-activityid');
        var goodsSn = $(ele).attr('data-goodsSn');
        $(ele).on('click', '.gs-add-bag' ,function(e){
            e.stopPropagation();
            //GEShopSiteCommon.dialog.unblock();
            // 点击请求数据
            if (ids) {
                //获取同SPU商品列表参数
                var params = {
                    lang: typeof GESHOP_LANG != 'undefined' ? GESHOP_LANG : 'en',
                    goodsSn: goodsSn,
                    manzeng_id: ids,
                    pipeline: (typeof GESHOP_PIPELINE != 'undefined' ? GESHOP_PIPELINE : '')
                };
                var url = GESHOP_INTERFACE.getlistinspu.url;
                reqAjax(params,url,openModel,failFn);
                // 保存埋点信息
                goodsInfo.rank = $(this).parents('.goods-item').index() || 0;
                if(!goodsInfo.p){
                    goodsInfo.p = $(this).parents('.geshop-component-box').eq(0).attr('data-p')
                }
            } else {
                //没有商品的时候
                openModel(false);
            }
            return false;
        });
    }

    //打开模态框
    function openModel(obj){
        // openIndex = layer.load(0);
        // layer.open({
        //     title: '',
        //     type: 1,
        //     closeBtn: 1,
        //     shade: [0.8, '#000'],
        //     area: ['752px', '434px'],
        //     content: body,
        //     skin : 'gs-add-goods',
        //     success: function () {
        //         layer.close(openIndex);
        //         layer.closeAll('loading');
        //         modelSuccess(obj);
        //         $(".layui-layer-close").removeClass("layui-layer-ico").html("<span>+</span>");
        //     },
        //     cancel:function(){
        //         layer.close(openIndex);
        //         layer.closeAll('loading');
        //         modelIndex = false;
        //     }
        // });

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
        // failIndex = layer.load(1);
        // layer && layer.open({
        //     title: 'Message',
        //     type: 1,
        //     closeBtn: 1,
        //     shade: [0.1, '#000'],
        //     area: ['560px', '270px'],
        //     skin : 'gs-add-fail',
        //     btn: ['OK'],
        //     content: addFail,
        //     success: function () {
        //         layer.close(failIndex);
        //         layer.closeAll('loading');
        //         if(res){
        //             $('.add-fail').text(res.message);
        //         }
        //     },
        //     cancel:function(){
        //         layer.close(failIndex);
        //         layer.closeAll('loading');
        //     }
        // });
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

    // logsss数据
    var goodsInfo = {
        rank : 0
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
        // 添加埋点data-logsss-event-value
        var logObj = {
            pm: 'mp',
            p: goodsInfo.p,
            x: 'ADT', // 加购
            ubcta: {
                rank: goodsInfo.rank,
                sku: obj.goods_sn,
                price: obj.shop_price,
                p: goodsInfo.p,
                fmp: 'mp'
            },
            skuinfo: {
                sku: obj.goods_sn,
                pam: 1,
                pc: obj.cateid,
                k: obj.warehouse,
                zt: 0
            }
        }
        var logInfo = JSON.stringify(logObj).replace(/"/g, '\'')
        $('.blockUI .gs-model-120 .add-bag').attr('data-logsss-event-value',logInfo)

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
