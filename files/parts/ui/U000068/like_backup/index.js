$(function () {
    /* 初始化 */
    /*  var devStatic = 'http://www.geshop.com.develop.s2.egomsl.com/' */
    var staticDomain = $('[data-static-domain]:eq(0)').attr('data-static-domain') ? $('[data-static-domain]:eq(0)').attr('data-static-domain') : "";
    $LAB
        .script(staticDomain + "/resources/javascripts/library/gs_laytpl.js?2018100101")
        .wait(function () {
            if ($('#js_likeRecommendGoods').length) {
                $.ajax({
                    url: GESHOP_INTERFACE.recommendlist.url,
                    type: 'GET',
                    dataType: 'jsonp',
                    data: {
                        "content": JSON.stringify({
                            "lang": GESHOP_LANG
                        })
                    },
                })
                .done(function(res) {
                    if (res.code == 0) {
                        $('.js_likeSeeMore').attr('href', res.data.more);
                        gs_laytpl.config({
                            open: '<%',
                            close: '%>'
                        });

                        gs_laytpl($('#js_likeRecommendGoods').html()).render(res.data, function(_html) {
                            $('.gs_like_goods_recommend').html(_html);
                            $('.gs_like_goods_recommend').find('.js_likeImgLazy').each(function() {
                                var that = this;
                                if (typeof gbLogsss != 'undefined') {
                                    var _target = $(this);
                                    if ($.fn.lazyload) {
                                        $(this).lazyload({
                                            threshold: 200,
                                            effect: "show",
                                            failure_limit: 5,
                                            load: function (remains, settings) {
                                                gbLogsss.getsku($(this));
                                                gbLogsss.sendsku();
                                            }
                                        })
                                    } else {
                                        window.GS_GOODS_LAZY_FN('.js_likeImgLazy');
                                    }
                                }
                            });
                            if (window.GLOBAL && window.GLOBAL.currency) {
                                window.GLOBAL.currency.change_html();
                            }

                            if (window.FUN && window.FUN.currency) {
                                window.FUN.currency.change_html();
                            }
                        })
                    };
                });
            }
    });
});
