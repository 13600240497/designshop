;
$(function() {
    var staticDomain = $('[data-static-domain]:eq(0)').attr('data-static-domain') ? $('[data-static-domain]:eq(0)').attr('data-static-domain') : "";
    var endTime;
    var LBTplObj = {
        renderTpl: function() {
            gs_laytpl.config({ open: "<%", close: "%>" });

            // 遍历 - 兼容多个组件各自获取数据
            $('.seckill').each(function(i, element) {
                var $ele = $(element);
                var goodsSn = $ele.find('input[name=goodsSKU]').val();

                if ($.trim(goodsSn).length > 0){ // 如果没有娶到商品sku就不要执行异步请求
                    var params = {
                        "lang": "en",
                        "goodsSn": goodsSn,
                        pipeline: (typeof GESHOP_PIPELINE != 'undefined' ? GESHOP_PIPELINE : ''),
                        v: '2'
                    };

                    var url = GESHOP_INTERFACE.timeseckilldetail.url;

                    var getTpl = $ele.find('.pc-leader-board-template').html(),
                        view = $ele.find('.leader-board-container'),
                        lang = typeof GESHOP_LANG != 'undefined' ? GESHOP_LANG : 'en';

                    params.lang = lang;
                    window.GEShopCommonFn_Vue.$jsonp(url,params,{target:element}).done(function(res) {
                        var goodsInfo = [];
                        var data = res.data;

                        for (var index = 0; index < data.goodsInfo.length; index++) {
                            !$.isEmptyObject(data.goodsInfo[index])&&goodsInfo.push(data.goodsInfo[index]);
                        }
                        var img = new Image(), imgRatio=0;
                        //     imgRatio;
                        // //img.src = goodsInfo[0].goods_img;
                        // if (GESHOP_SITECODE == "rw-pc") {
                        //     imgRatio = 1;
                        // } else {
                        //     imgRatio = 0;
                        // }

                        var dataParam = {
                            goodsInfo: goodsInfo,
                            lang: lang,
                            endTime: endTime,
                            imgRatio: imgRatio
                        };
                        gs_laytpl.config({ open: "<%", close: "%>" });
                        gs_laytpl(getTpl).render(dataParam, function(html) {
                            view.html(html);
                            if (typeof GEShopSiteCommon != 'undefined') {
                                GEShopSiteCommon.renderCurrency();
                            }
                            swiperFn();
                        });

                    })
                }
            });
        }
    }

    /**
     * 模板初始化
     */
    $LAB
        .script(staticDomain + "/resources/javascripts/library/gs_laytpl.js?2018101109").wait(function() {
            LBTplObj.renderTpl();
            gsKillGlobal.initCountDown();
            gsKillGlobal.compoentCountDown();
        });


    function swiperFn() {
        var length = $('.seckill-container .goods-list ul li').length;
        loadCss(staticDomain + '/resources/javascripts/library/swiper/swiper.min.css');
        $LAB.script(staticDomain + '/resources/javascripts/library/swiper/swiper.3.4.spec.min.js').wait(function() {
            // var Swiper3 = Swiper;
            $('.seckill .seckill-container').each(function(i, element) {
                var swiperContainer = $(element).find('.swiper-container');
                var prevButtonElement = $(element).find('.goods-list .pre-btn');
                var nextButtonElement = $(element).find('.goods-list .next-btn');
                var isAutoPlay = $(element).find('input[name=isAutoPlay]').val();
                var autoplay = 3000;
                if (isAutoPlay === "0") {
                    autoplay = false;
                }
                var swiper;
                if (length < 3) {
                    $('.seckill-container .goods-list .btn').hide();
                    swiper = new Swiper3(swiperContainer, {
                        autoplay: autoplay,
                        slidesPerView: length,

                        slidesPerGroup: 3,
                        prevButton: prevButtonElement,
                        nextButton: nextButtonElement,
                        lazyLoading: true,
                        autoplayDisableOnInteraction: false
                    })
                } else {
                    swiper = new Swiper3(swiperContainer, {
                        autoplay: autoplay,
                        slidesPerView: 3,
                        slidesPerGroup: 3,
                        prevButton: prevButtonElement,
                        nextButton: nextButtonElement,
                        lazyLoading: true,
                        autoplayDisableOnInteraction: false,
                    })
                }

                $(swiperContainer).hover(function() {
                    swiper.stopAutoplay();
                }, function() {
                    swiper.startAutoplay();
                });

            })
        });
    }
});

var now = new Date().getTime();

/* 倒计时初始化,tpl初始化 */
if (!gsKillGlobal) {
    var gsKillGlobal = function(my) {


        var startTime, endTime;

        var $gsCountTarget = $('.component-drop .seckill');
        my.initCountDown = function() {

                $gsCountTarget.each(function(i, element) {
                    var leftTime = 0;
                    var status = 0; // ['0未开始', '1已开始', '2已结束']
                    startTime = $(element).find('input[name=serverTime]').data('start-time');
                    endTime = $(element).find('input[name=serverTime]').data('end-time');

                    if (startTime > now) {
                        leftTime = (parseInt(startTime) - now) / 1000;
                        status = 0;

                    } else if (startTime <= now && endTime > now) {
                        status = 1;
                        leftTime = (endTime - now) / 1000;
                    } else if (endTime < now) {
                        status = 2;
                        leftTime = 0;
                    }
                    $(element).find('input[name=count_textStatus]').val(status);
                    $(this).data({ 'leftTime': leftTime, 'status': status });
                });

            }
            /* 倒计时组件 */
        my.compoentCountDown = function() {
            $gsCountTarget.each(function(i, element) {
                var timeId;
                var seconds, minutes, hours, days, CDay, CHour, CMinute, CSecond;
                var leftTime = parseInt($(this).data('leftTime'));
                var status = parseInt($(this).data('status'));
                if (!isNaN(leftTime) && startTime >= 0) {
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
                    $(element).find('.timer .s-days').text(CDay);
                    $(element).find('.timer .s-hours').text(CHour);
                    $(element).find('.timer .s-minutes').text(CMinute);
                    $(element).find('.timer .s-seconds').text(CSecond);
                    $(element).find('input[name=serverTime]').data('lefttime', leftTime);
                    var timed;
                    var copyrightText = $(element).find('.copywriter');
                    var timeGroup = JSON.parse($(element).find('input[name=timeGroup]').val());
                    var timeArr = ['Start In', 'Ends In', 'Already Ended']
                    copyrightText.text(timeGroup[status] || timeArr[status]);
                    if (status == 0) {} else if (status == 1) {} else if (status == 2) {
                        $(element).find('.timer .s-days').text('00');
                        $(element).find('.timer .s-hours').text('00');
                        $(element).find('.timer .s-minutes').text('00');
                        $(element).find('.timer .s-seconds').text('00');

                        var isEndShow = $(element).find('input[name=isEndShow]').val();
                        var isEditEnv = $(element).find('input[name=isEditEnv]').val();
                        if (isEndShow == '0' && isEditEnv == '0') {
                            $(this).remove();
                        }
                        clearTimeout(timeId);
                    }

                    $(this).data('left-time', leftTime - 1);
                    if (leftTime < 1) {
                        now = new Date().getTime();
                        gsKillGlobal.initCountDown();
                    }

                } else {
                    if (status !== 2) {
                        gsKillGlobal.initCountDown();
                    }
                }
            });
            setTimeout(gsKillGlobal.compoentCountDown, 1000);
        }
        return my
    }(gsKillGlobal || {})
}
