(function () {
    var gs_recommendData = {
        skus: [],
        index: 0,
        isLastPage: false,
        isLoading: false,
        pageindex: 0,
        pagesize: 4
    };

    function GetPageData() {
        return {
            skus: [],
            index: 0,
            isLastPage: false,
            isLoading: false,
            pageindex: 0,
            pagesize: 4
        };
    }

    /* 状态变量 */
    $("[data-gid=U000067_zaful_index]").each(function () {
        $(this).data("pageInfo", new GetPageData());
    });

    var GsGoodsPcTpl = (function (my) {
        my.gsTplIntData = function (listData, dataid) {
            var skuCurrent = [];
            for (var i = 0; i < listData.length; i++) {
                skuCurrent.push(listData[i].goodssn);
            }
            if (!dataid) {
                $("[data-gid=U000067_zaful_index]").each(function () {
                    var targetId = $(this).attr("data-id");
                    var infiniteTpl = $("." + targetId).html();
                    if (targetId) {
                        var dataid = targetId;
                    };
                    gs_laytpl.config({ open: "<%", close: "%>" });
                    gs_laytpl(infiniteTpl).render(listData, function (html) {
                        var $componentTarget = dataid
                            ? $("[data-gid=U000067_zaful_index][data-id=" + dataid + "]")
                            : $("[data-gid=U000067_zaful_index]");
                        var pageData = typeof $componentTarget.data("pageInfo") != 'undefined' ? $componentTarget.data("pageInfo") : {};

                        if (html) {
                            $componentTarget.find(".gs-goodsWrap ul").html(html);
                            PRICEPAGECOMMON.renderPrice($componentTarget, skuCurrent);

                            $componentTarget
                                .find(".gs-goodsWrap ul")
                                .find("img.js-geshopImg-lazyload")
                                .each(function () {
                                    var $img = $(this);
                                    if ($img.attr("src") != $img.attr("data-original")) {
                                        $img.attr("src", $img.attr("data-original"));
                                    }

                                    if (typeof gbLogsss != "undefined") {
                                        gbLogsss.getsku($img);
                                        gbLogsss.sendsku();
                                    }
                                });

                            pageData.isLastPage = false;
                        } else {
                            pageData.isLastPage = true;
                        }

                        if (listData.length < pageData.pagesize) {
                            pageData.isLastPage = true;
                        }

                        pageData.isLoading = false;
                        $componentTarget.data("pageInfo", pageData);
                    });
                });
            } else {
                var infiniteTpl = $("." + dataid).html();
                gs_laytpl.config({ open: "<%", close: "%>" });
                gs_laytpl(infiniteTpl).render(listData, function (html) {
                    var $componentTarget = dataid
                        ? $("[data-gid=U000067_zaful_index][data-id=" + dataid + "]")
                        : $("[data-gid=U000067_zaful_index]");
                    var pageData = $componentTarget.data("pageInfo");

                    if (html) {
                        $componentTarget.find(".gs-goodsWrap ul").html(html);
                        PRICEPAGECOMMON.renderPrice($componentTarget, skuCurrent);

                        $componentTarget
                            .find(".gs-goodsWrap ul")
                            .find("img.js-geshopImg-lazyload")
                            .each(function () {
                                var $img = $(this);
                                if ($img.attr("src") != $img.attr("data-original")) {
                                    $img.attr("src", $img.attr("data-original"));
                                }

                                if (typeof gbLogsss != "undefined") {
                                    gbLogsss.getsku($img);
                                    gbLogsss.sendsku();
                                }
                            });

                        pageData.isLastPage = false;
                    } else {
                        pageData.isLastPage = true;
                    }

                    if (listData.length < pageData.pagesize) {
                        pageData.isLastPage = true;
                    }

                    pageData.isLoading = false;
                    $componentTarget.data("pageInfo", pageData);
                });
            }
        };
        my.gsTplInt = function (dataid) {
            if (dataid) {
                var $componentTarget = $(
                    "[data-gid=U000067_zaful_index][data-id=" + dataid + "]"
                );
                var pageData = $componentTarget.data("pageInfo");
                pageData.isLoading = true;
                $componentTarget.data("pageInfo", pageData);
            }

            my.getTplProduct({}, dataid)
                .done(function (res) {
                    if (res.status == 1 && res.result) {
                        my.gsTplIntData(res.result, dataid);
                    }
                })
                .fail(function () {
                    if (dataid) {
                        var $componentTarget = $(
                            "[data-gid=U000067_zaful_index][data-id=" + dataid + "]"
                        );
                        var pageData = $componentTarget.data("pageInfo");
                        pageData.isLoading = false;
                        $componentTarget.data("pageInfo", pageData);
                    }
                });
        };
        my.getTplProduct = function (params, dataid) {
            var pageData;
            if (dataid) {
                pageData = $(
                    "[data-gid=U000067_zaful_index][data-id=" + dataid + "]"
                ).data("pageInfo");
            } else {
                pageData = gs_recommendData;
            }
            var lang;
            if (typeof JS_LANG === "undefined") {
                lang = "en";
            } else {
                lang = JS_LANG.slice(0, -1) || "en";
            }

            var data = my.extendDeep(
                {
                    cookie: "",
                    lang: lang,
                    platform: "PC",
                    regioncode: "",
                    pageindex: pageData.pageindex,
                    pagesize: pageData.pagesize
                },
                params
            );
            return $.ajax({
                url: "https://glbg.logsss.com/",
                type: "post",
                data: {
                    params: JSON.stringify(data),
                    recommendType: 2010102
                }
            });
        };
        my.extendDeep = function (parent, child) {
            var i,
                toStr = Object.prototype.toString,
                astr = "[object Array]";

            child = child || {};

            for (i in parent) {
                if (parent.hasOwnProperty(i)) {
                    if (typeof parent[i] === "object") {
                        child[i] = toStr.call(parent[i]) === astr ? [] : {};
                        my.extendDeep(parent[i], child[i]);
                    } else {
                        child[i] = parent[i];
                    }
                }
            }
            return child;
        };
        return my;
    })({});

    var PRICEPAGECOMMON = {
        goodSkus: [],
        getPageSkus: function ($componentTarget, skuCurrent) {
            if (skuCurrent) {
                return skuCurrent;
            }
            var retArr = [];
            $(".my_shop_price[data-sku]", $componentTarget).each(function () {
                var sku = $(this).attr("data-sku");
                if (sku && sku !== "undefined") {
                    if (PRICEPAGECOMMON.goodSkus.indexOf(sku) === -1) {
                        PRICEPAGECOMMON.goodSkus.push(sku);
                        retArr.push(sku);
                    } else {
                        /*  同一sku商品在页面上出现多处时，再次获取最新价格; */
                        if (
                            $('.my_shop_price[data-sku="' + sku + '"]', $componentTarget)
                                .length > 1
                        ) {
                            retArr.push(sku);
                        }
                        if ($(this).hasClass("js-refresh-all")) {
                            retArr.push(sku);
                        }
                    }
                }
            });
            return retArr;
        },
        render: function (data, callback) {
            if (!data.priceList) return;
            $.each(data.priceList, function (key, obj) {
                var $elems = $('.my_shop_price[data-sku="' + key + '"]');

                $elems.each(function () {
                    var $elem = $(this);
                    /* 	1:秒杀2:新人专享3:app专享4:清仓5:促销6:预促销; */
                    if (+obj.price !== 0) {
                        /* 价格不为0，变更销售价; */
                        $elem.attr("data-orgp", obj.price).attr("orgp", obj.price);
                    }
                    if (+obj.newUserPrice > 0 && $elem.hasClass("js-new-price")) {
                        /* 新人专享价; */
                        $elem
                            .attr("data-orgp", obj.newUserPrice)
                            .attr("orgp", obj.newUserPrice);
                    }
                    if (+obj.appPrice > 0 && $elem.hasClass("js-mobile-price")) {
                        /* 手机专享价; */
                        $elem.attr("data-orgp", obj.appPrice).attr("orgp", obj.appPrice);
                    }
                });

                if (obj.symbol) {
                    $(".js-price-mark-" + key).text("(" + obj.symbol + ")");
                    $(".js-price-mark" + key).text("(" + obj.symbol + ")");
                } else {
                    $(".js-price-mark-" + key).remove();
                    $(".js-price-mark" + key).remove();
                }
                $elems.removeClass("hidden");
            });
            if (typeof GLOBAL != "undefined") {
                GLOBAL.currency.change_html();
            }

            /* 		callback && callback(data.priceList); */
        },
        initRender: function (res) {
            res.priceList = res.priceList || res.discountPrice || [];
            this.render(res);
        },
        renderPrice: function ($componentTarget, skuCurrent, callback) {
            var skus = PRICEPAGECOMMON.getPageSkus($componentTarget, skuCurrent);
            if (skus.length === 0) return;
            g_getPriceBySkusTest(skus).done(function (res) {
                if (res.status === 0) {
                    PRICEPAGECOMMON.render(res, callback);
                }
            });
        }
    };

    window.g_getPriceBySkusTest = function (skus) {
        if (typeof skus != "object") {
            return;
        }
        var domain = "https://www.zaful.com";
        var ext = new Date().getTime();
        return $.ajax({
            url: domain + "/fun/index.php?act=discountPrice",
            type: "get",
            dataType: "jsonp",
            jsonpCallback: `geshop_callback_${ext}`,
            data: {
                skus: skus.join(","),
                pipeline: (typeof GESHOP_PIPELINE != 'undefined' ? GESHOP_PIPELINE : '')
            },
            cache: false
        });
    };

    /* prev next btn */
    $("body")
        .on("click", ".recommend-nav-prev", function () {
            var $this = $(this);
            var $wrapper = $this.parents('[data-gid="U000067_zaful_index"]');
            var $wrapperId = $wrapper.attr("data-id");
            var pageData;
            if (!$wrapperId) {
                pageData = gs_recommendData;
            } else {
                pageData = $(
                    "[data-gid=U000067_zaful_index][data-id=" + $wrapperId + "]"
                ).data("pageInfo");
            }

            if (pageData.isLoading) {
                return;
            }

            if (pageData.pageindex > 0) {
                pageData.pageindex--;
                $("[data-gid=U000067_zaful_index][data-id=" + $wrapperId + "]").data(
                    "pageInfo",
                    pageData
                );
            } else {
                return;
            }
			/* 			if (gs_recommendData.pageindex > 0) {
							gs_recommendData.pageindex--;
						} else {
							return;
						} */

            GsGoodsPcTpl.gsTplInt($wrapperId);
        })
        .on("click", ".recommend-nav-next", function () {
            var $this = $(this);
            var $wrapper = $this.parents('[data-gid="U000067_zaful_index"]');
            var $wrapperId = $wrapper.attr("data-id");
            var pageData;
            if (!$wrapperId) {
                pageData = gs_recommendData;
            } else {
                pageData = $(
                    "[data-gid=U000067_zaful_index][data-id=" + $wrapperId + "]"
                ).data("pageInfo");
            }

            if (pageData.isLoading) {
                return;
            }

            if (!pageData.isLastPage) {
                pageData.pageindex++;
                $("[data-gid=U000067_zaful_index][data-id=" + $wrapperId + "]").data(
                    "pageInfo",
                    pageData
                );
            } else {
                return;
            }
            GsGoodsPcTpl.gsTplInt($wrapperId);
        });

    /* 初始化异步列表 */
    /* 	var devStatic = 'http://www.geshop.com.develop.s2.egomsl.com/'; */
    var staticDomain = $('[data-static-domain]:eq(0)').attr('data-static-domain') ? $('[data-static-domain]:eq(0)').attr('data-static-domain') : "";
    $LAB
        .script(staticDomain + "/resources/javascripts/library/gs_laytpl.js?2018100101")
        .wait(function () {
            GsGoodsPcTpl.gsTplInt();
        });
})();
