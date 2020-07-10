;$(function () {
    $('.geshop-U000207-default').each(function (index, target) {

        var $elem = $(target).find('.geshop-saleBox');
        // 商品容器
        var $goodsWrap = $elem.find('.gs-goods-wrap');
        // 商品列表
        var $goodsList = $goodsWrap.find('.goods-list');
        // sku
        var goodsSnArr = $elem.attr('data-goodsSku');
        var priceType = $elem.attr('data-pricetype');
        var showNow = $elem.attr('data-shopnow');
        var dateLeftTime = $elem.attr('data-lefttime');
        // 倒计时状态 0 未开始，1 已开始， 2 已结束
        var state = parseInt($elem.attr('data-state'));
        var timer;


        // 渲染倒计时HTML
        function renderTime(day, hour, minute, seconds) {
            var timeHtml = '<span><em>' + day + '</em>:<em>' + hour + '</em>:<em>' + minute + '</em>:<em>' + seconds + '</em></span>'
            $elem.find('.gs_component_countDown').html(timeHtml);
        }

        function initDate() {
            var start = $elem.attr('data-start');
            var end = $elem.attr('data-end');
            var serverTime = new Date().getTime();
            /*状态*/
            var status = 0,
                leftTime = 0,
                textCont = "",
                textArr = ['未开始', '已开始', '已结束'];
            if (start > serverTime) {
                leftTime = start - serverTime;
                $elem.find('.gs-down-text').text($elem.attr('data-starTxt'))
            } else if (start <= serverTime && end > serverTime) {
                status = 1;
                leftTime = end - serverTime;
                $elem.find('.gs-down-text').text($elem.attr('data-ingTxt'))
            } else if (end < serverTime) {
                $elem.find('.gs-down-text').text($elem.attr('data-endTxt'))
                status = 2;
                leftTime = 0;
            }
            textCont = textArr[status];
            $elem.attr('data-lefttime', leftTime / 1000);
            $elem.attr('data-state', status);
            $elem.attr('data-statetext', textCont);
        }

        function csTime() {
            initDate();
            // 倒计时时间剩余
            var leftTime = parseInt($elem.attr('data-lefttime'));
            if (!isNaN(leftTime) && leftTime >= 0) {
                var seconds, minutes, hours, days, CDay, CHour, CMinute, CSecond;
                seconds = leftTime;
                minutes = Math.floor(seconds / 60);
                hours = Math.floor(minutes / 60);
                days = Math.floor(hours / 24);
                CDay = days;
                CHour = hours % 24;
                CMinute = minutes % 60;
                CSecond = Math.floor(seconds % 60);
                CDay = CDay < 10 ? '0' + CDay : CDay;
                CHour = CHour < 10 ? '0' + CHour : CHour;
                CMinute = CMinute < 10 ? '0' + CMinute : CMinute;
                CSecond = CSecond < 10 ? '0' + CSecond : CSecond;
                renderTime(CDay, CHour, CMinute, CSecond);
                $elem.attr('data-lefttime', leftTime - 1);
                if ($elem.attr('data-state') != 2) {
                    timer = setTimeout(function () {
                        csTime();
                    }, 1000)
                }
            }
        }

        // 初始化时间
        clearTimeout(timer);
        if (+dateLeftTime > 0) {
            csTime();
        } else {
            $elem.find('.gs-down-text').text($elem.attr('data-endTxt') || 'Starts in')
        }

        function redernGoodsList(data) {
            var per = Math.round(parseInt(data.activity_volume_number) / parseInt(data.activity_number) * 100);
            // 售空验证
            var soldOutVerify = (data.activity_number - data.activity_volume_number) <= 0 || data.goods_number <= 0;
            if (data.activity_number == 0) {
                per = 0
            }
            if(soldOutVerify){
                per = 100;
            }
            // 图片懒加载
            var imgUrl = '';
            if (typeof GS_GOODS_LAZY_FN != 'undefined') {
                imgUrl =
                    '<img src="https://geshopcss.logsss.com/imagecache/geshop/resources/images/dl/loading.gif"' +
                    'data-original="' + data.goods_img + '"' +
                    'class="js_gdexp_lazy js-lazy"/>'
            } else {
                imgUrl = '<img src="' + data.goods_img + '"/>'
            }
            // 已售完
            var soleOut = '';
            if (soldOutVerify) {
                soleOut = '<div class="soldOut"><p>' + GESHOP_LANGUAGES.btn_sold_out + '</p></div>'
            }
            // ceshi
            // 折扣显示
            var priceOff = '';
            if (priceType == 0) {
                priceOff = '<div class="goods-priceOff-tips"><span>' + Math.round(data.discount) + '%</span><span>OFF</span></div>'
            } else {
                priceOff = '<div class="goods-priceOff-tips mt10"><span>-' + Math.round(data.discount) + '%</span></div>'
            }
            var html = $('<li>' +
                '           <div class="list-img">' +
                '               <a href="' + data.url_title + '">' + imgUrl +
                '                   <div class="shop-now-container">' +
                '                       <div class="inner-wrapper">' +
                '                           <i class="icon-bag"></i>' +
                '                           <p class="shop-now-text">' + showNow + '</p>' +
                '                       </div>' +
                '                   </div>' + priceOff +
                '               </a>' + soleOut +
                '           </div>' +
                '           <p class="goods-title">' +
                '               <a href="' + data.url_title + '" target="_blank">' + data.goods_title + '</a>\n' +
                '           </p>' +
                '           <p class="goods-price">' +
                '           <span class="shop-price">' +
                '               <span class="my-shop-price" data-orgp="' + data.shop_price + '">$' + data.shop_price + '</span>' +
                '           </span>' +
                '               <span class="market-price">' +
                '                   <span class="my-shop-price dl-has-rrp-tag" data-orgp="' + data.market_price + '">$' + data.market_price + '</span>' +
                '               </span>' +
                '           </p>' +
                '           <div class="gs-goods-limit">' +
                '               <span class="goods-limit-tips_left"><b>' + per + '%</b>' + GESHOP_LANGUAGES.claimed + '</span>' +
                '               <span class="process-bar">' +
                '               <em class="process-inner" style="width:' + per + '%;"></em>' +
                '           </span>' +
                '           </div>' +
                '       </li>');
            $goodsList.append(html);
            if (typeof GS_GOODS_LAZY_FN != 'undefined') {
                GS_GOODS_LAZY_FN(html.find('.js_gdexp_lazy'));
            }
            typeof GEShopSiteCommon !== 'undefined' && GEShopSiteCommon.renderCurrency();
            // 货币切换
            window.GLOBAL && window.GLOBAL.currency && window.GLOBAL.currency.change_html();
            window.FUN && window.FUN.currency && window.FUN.currency.change_html();
        }


        // 批量获取
        var coupunParam = {
            lang: GESHOP_LANG || 'en',
            goodsSn: goodsSnArr,
            client: GESHOP_PLATFORM || 'web'
        };

        if (goodsSnArr) {
            var url = GESHOP_INTERFACE.timeseckilldetail.url;
            var component_id = $(target).attr('data-id');
            window.GEShopCommonFn_Vue.$jsonp(url, coupunParam, { pid: component_id }).done(function (res) {
                $goodsList.empty();
                res.data.goodsInfo.forEach(function (index) {
                    redernGoodsList(index)
                });
            });
        } else {
            $elem.find("img.js-lazy").each(function(){
                $(this).attr('src',$(this).attr('data-original'));
            })
        }
    });
});
