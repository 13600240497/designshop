$(function () {
    if (!GS_spikeGoodsTab) {
        let staticDomain = typeof $('[data-static-domain]:eq(0)').attr('data-static-domain') != 'undefined' ? $('[data-static-domain]:eq(0)').attr('data-static-domain') : '';
        let siteCode = $('[data-siteCode]:eq(0)').attr('data-siteCode');

        var GS_spikeGoodsTab = (function () {

        }({}));

        /* 倒计时初始化,tpl初始化 */
        if (!gs_preProGlobal) {
            var gs_preProGlobal = (function (my) {
                my.extendDeep = function (parent, child) {
                    let i,
                        toStr = Object.prototype.toString,
                        astr = '[object Array]';

                    child = child || {};

                    for (i in parent) {
                        if (parent.hasOwnProperty(i)) {
                            if (typeof parent[i] === 'object') {
                                child[i] = toStr.call(parent[i]) === astr ? [] : {};
                                my.extendDeep(parent[i], child[i]);
                            } else {
                                child[i] = parent[i];
                            }
                        }
                    }
                    return child;
                };

                my.gsTplInt = function () {
                    gs_laytpl.config({ open: '<%', close: '%>' });
                };

                my.getTplProduct = function (skus, element) {
                    let lang = typeof GESHOP_LANG != 'undefined' ? GESHOP_LANG : 'en';
                    let params = {
                        lang: lang,
                        goodsSn: skus,
                        pipeline: (typeof GESHOP_PIPELINE != 'undefined' ? GESHOP_PIPELINE : '')
                    };

                    let url = GESHOP_INTERFACE.prepromotion.url;
                    var pid = $(element).parents('.geshop-component-box').eq(0).attr('data-id');
                    return window.GEShopCommonFn_Vue.$jsonp(url, params);
                };
                /* 模板数据填充 */
                my.getTplInitData = function () {

                };
                my.throttle = function (fn, delay, atleast) {
                    let timer = null;
                    let previous = null;

                    return function () {
                        let now = +new Date();
                        if (!previous) {
                            previous = now;
                        }
                        if (atleast && now - previous > atleast) {
                            fn();
                            previous = now;
                            clearTimeout(timer);
                        } else {
                            clearTimeout(timer);
                            timer = setTimeout(function () {
                                fn();
                                previous = null;
                            }, delay);
                        }
                    };
                };

                // 设置 cookie 方法
                my.setCookie = function (name, value) {
                    let Days = 30;
                    let exp = new Date();
                    exp.setTime(exp.getTime() + Days * 24 * 60 * 60 * 1000);
                    document.cookie = name + '=' + escape(value) + ';expires=' + exp.toGMTString() + ';path=/' + ';domain=' + COOKIESDIAMON;
                };

                // 获取 cookie 方法
                my.getCookie = function (name) {
                    let arr,
                        reg = new RegExp('(^| )' + name + '=([^;]*)(;|$)');

                    if (arr = document.cookie.match(reg)) {
                        return arr[2];
                    } else {
                        return null;
                    }
                };

                // goods_number库存为0时，排序最后
                my.sortData = function (data) {
                    let listArr = [];
                    data.forEach(function (item, index) {
                        if (item.goods_number && item.goods_number == 0) {
                            listArr.push(item);
                            data.splice(index, 1);
                        }
                    });

                    data = data.concat(listArr);
                    return data;
                };

                return my;
            }(gs_preProGlobal || {}));
        }

        /* target tab target */
        function tplPreProIntCallback (index, target) {
            let element = target;
            let currentSkus = $(element).find('.gs-tab-item').data('skus');
            let skuLimit = $(element).find('.gs-tab-item').data('skulimit');
            let $tpl = $(element).find('.gs_syncDefault');
            let tplHtml = $tpl.html();
            if (!currentSkus) { return false; };
            let isEditEnv = $('[data-editenv]:eq(0)').data('editenv');

            gs_preProGlobal.getTplProduct(currentSkus,target).done(function (res) {
                let dataList = res.data.goodsInfo;

                if (res.code == '0' && dataList.length) {
				    let list = gs_preProGlobal.sortData(dataList);

                    gs_laytpl.config({ open: '<%', close: '%>' });
                    gs_laytpl(tplHtml).render(list, function (html) {
                        if (html) {
                            $(element).find('.gs-goodsWrap ul').html(html);

                            // 控制显示sku数量
                            if (typeof skuLimit === 'number') {
                                if (skuLimit == 0) {
                                    $(element).find('.gs-goodsWrap li').hide();
                                } else {
                                    $(element).find('.gs-goodsWrap li:gt(' + (skuLimit - 1) + ')').hide();
                                }
                            }

                            // 图片懒加载初始化
                            window.GS_GOODS_LAZY_FN && window.GS_GOODS_LAZY_FN();

                            /* 价格换算 rw */
                            if (typeof GLOBAL != 'undefined' && GLOBAL.currency.change_html) {
                                let bizhong = gs_preProGlobal.getCookie('bizhong') || 'USD';
                                let $wrapElem = $('.gs-goods-tab');
                                GLOBAL.currency.change_html(bizhong, $wrapElem);
                            }

                            /* 价格换算 */
                            if (typeof GEShopSiteCommon != 'undefined') {
                                GEShopSiteCommon.renderCurrency();
                            }

                            // 动态计算价格区域价格标题高度（pc采用js动态计算，m采用flex布局）
                            $(element).find('.gs-goodsWrap li').each(function (index, ele) {
                                let s_titleHeight = $(ele).find('.surprice-price .price-title').height() || 20;
                                let c_titleHeight = $(ele).find('.current-price .price-title').height() || 20;
                                let titleHeight = Math.max(s_titleHeight, c_titleHeight);
                                $(ele).find('.surprice-price .price-title').height(titleHeight);
                                $(ele).find('.current-price .price-title').height(titleHeight);
                            });

                            // 若无显示列表项或全为售空状态，整个组件隐藏
                            let liNotSoldoutLen = $(element).find('.gs-goodsWrap li:not(.prePromotion_soldout)').length;
                            let liVisibleLen = $(element).find('.gs-goodsWrap li:not(.hide)').length;
                            if (liVisibleLen == 0 || liNotSoldoutLen == 0) {
                                if (isEditEnv == '0') {
                                    $(element).hide();
                                }
                            }
                        }
                    });
                } else if (dataList.length == 0) {

                }
            }).fail(function (err) {
                /* if (isEditEnv == '1') {
                    $(element).find('.gs-goodsWrap ul').after('<span>错误:接口商品数据为空</span>');
                } */
            });
        }

        function tabPreProScrollEvent () {
            let $goodTarget = $('[data-gid="U000162_default"] .gs-goods-tab');
            $goodTarget.length && $goodTarget.each(function (i, element) {
                tplPreProIntCallback(i, element);
            });
        }

        /* 加载初始化 */
        $LAB
            .script(staticDomain + '/resources/javascripts/library/gs_laytpl.js?2018102901').wait(function () {
                gs_preProGlobal.gsTplInt();
                tabPreProScrollEvent();
            });
    } else {
        /* edit */

        tabPreProScrollEvent();
    }
});
