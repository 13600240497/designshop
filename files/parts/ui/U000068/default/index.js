$(function () {
  var gs_recommendData = {
    skus: [],
    index: 0,
    isLastPage: false,
    isLoading: false,
    pageindex: 0,
    pagesize: 4
  };

  function GetPageData () {
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
  $("[data-gid=U000068_zaful_index]").each(function () {
    $(this).data("pageInfo", new GetPageData());
  });

  var $loading = $(".ui-recommend-loading");

  var GsGoodsPcTpl = (function (my) {
    my.gsTplIntData = function (listData, dataid) {
      /* 筛选刷新的sku */
      var skuCurrent = [];
      for (var i = 0; i < listData.length; i++) {
        skuCurrent.push(listData[i].goodssn);
      }

      if (!dataid) {
        $("[data-gid=U000068_zaful_index]").each(function () {
          var targetId = $(this).attr("data-id");
          var infiniteTpl = $("#gsTpl_" + targetId).html();
          if (targetId) {
            var dataid = targetId;
          }
          gs_laytpl.config({ open: "<%", close: "%>" });
          gs_laytpl(infiniteTpl).render(listData, function (html) {
            /* var $componentTarget = $('[data-id=' + gidPage + ']') */
            var $componentTarget = dataid
              ? $("[data-gid=U000068_zaful_index][data-id=" + dataid + "]")
              : $("[data-gid=U000068_zaful_index]");

            if (html) {
              $componentTarget.find(".gs-goodsWrap ul").html(html);
              PRICEPAGECOMMON.renderPrice($componentTarget, skuCurrent);
              $componentTarget
                .find(".gs-goodsWrap ul")
                .find("img.js-geshopImg-lazyload")
                .each(function () {
                  var $img = $(this),
                    sku = +$img.data("sku");
                  if ($img.attr("src") != $img.attr("data-original")) {
                    $img.attr("src", $img.attr("data-original"));
                  }
                  if (gs_recommendData.skus.indexOf(sku) === -1) {
                    gs_recommendData.skus.push(sku);
                    if (typeof gbLogsss != "undefined") {
                      gbLogsss.getsku($img);
                      gbLogsss.sendsku();
                    }
                  }
                });
            } else {
              gs_recommendData.isLastPage = true;
            }
          });
        });
      } else {
        var infiniteTpl = $("#gsTpl_" + dataid).html();
        gs_laytpl.config({ open: "<%", close: "%>" });
        gs_laytpl(infiniteTpl).render(listData, function (html) {
          /* var $componentTarget = $('[data-id=' + gidPage + ']') */
          var $componentTarget = dataid
            ? $("[data-gid=U000068_zaful_index][data-id=" + dataid + "]")
            : $("[data-gid=U000068_zaful_index]");

          if (html) {
            $componentTarget.find(".gs-goodsWrap ul").append(html);
            PRICEPAGECOMMON.renderPrice($componentTarget, skuCurrent);
            $componentTarget
              .find(".gs-goodsWrap ul")
              .find("img.js-geshopImg-lazyload")
              .each(function () {
                var $img = $(this),
                  sku = +$img.data("sku");
                if ($img.attr("src") != $img.attr("data-original")) {
                  $img.attr("src", $img.attr("data-original"));
                }
                if (gs_recommendData.skus.indexOf(sku) === -1) {
                  gs_recommendData.skus.push(sku);
                  if (typeof gbLogsss != "undefined") {
                    gbLogsss.getsku($img);
                    gbLogsss.sendsku();
                  }
                }
              });
          } else {
            gs_recommendData.isLastPage = true;
          }
        });
      }

      $loading.hide();
      gs_recommendData.isLoading = false;
    };
    my.gsTplInt = function (dataid) {
      my.getTplProduct({}, dataid).done(function (res) {
        if (res.status == 1 && res.result) {
          my.gsTplIntData(res.result, dataid);
          gs_recommendData.pageindex++;
        }
      });
    };
    my.getTplProduct = function (params, dataid) {
      /* 			var pageData;
						if (dataid) {
							pageData = $('[data-gid=U000068_zaful_index][data-id=' + dataid + ']').data('pageInfo')
						} else {
							pageData = gs_recommendData
						} */
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
          platform: "M",
          regioncode: "",
          pageindex: gs_recommendData.pageindex,
          pagesize: gs_recommendData.pagesize
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
    my.throttle = function (fn, delay, atleast) {
      var timer = null;
      var previous = null;

      return function () {
        var now = +new Date();
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
    return my;
  })({});

  var PRICEPAGECOMMON = {
    goodSkus: [],
    getPageSkus: function ($componentTarget, renderPrice) {
      if (renderPrice) {
        return renderPrice;
      }
      var retArr = [];
      $(".my_shop_price[data-sku]", $componentTarget).each(function () {
        var sku = $(this).attr("data-sku");
        if (sku && sku !== "undefined") {
          if (PRICEPAGECOMMON.goodSkus.indexOf(sku) === -1) {
            PRICEPAGECOMMON.goodSkus.push(sku);
            retArr.push(sku);
          } else {
            /* 同一sku商品在页面上出现多处时，再次获取最新价格 */
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
          /* 1:秒杀2:新人专享3:app专享4:清仓5:促销6:预促销 */
          if (+obj.price !== 0) {
            /* 价格不为0，变更销售价 */
            $elem.attr("data-orgp", obj.price).attr("orgp", obj.price);
          }
          if (+obj.newUserPrice > 0 && $elem.hasClass("js-new-price")) {
            /* 新人专享价 */
            $elem
              .attr("data-orgp", obj.newUserPrice)
              .attr("orgp", obj.newUserPrice);
          }
          if (+obj.appPrice > 0 && $elem.hasClass("js-mobile-price")) {
            /* 手机专享价 */
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

      /* callback && callback(data.priceList); */
    },
    initRender: function (res) {
      res.priceList = res.priceList || res.discountPrice || [];
      this.render(res);
    },
    renderPrice: function ($componentTarget, renderPrice, callback) {
      var skus = PRICEPAGECOMMON.getPageSkus($componentTarget, renderPrice);
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

  $("[data-gid=U000068_zaful_index]").length &&
    $(window).on(
      "scroll.recommend",
      GsGoodsPcTpl.throttle(function () {
        var $wrapper = $("[data-gid=U000068_zaful_index]");
        var $wrapperId = $wrapper.attr("data-id");
        var screenH = (document.documentElement || document.body).clientHeight,
          scrollY = window.scrollY,
          /* 			bottomTop = $('#footer').offset().top, */
          boundary = 50,
          wrapperTop = $wrapper.offset().top,
          wrapperH = $wrapper.height();
        if (gs_recommendData.isLastPage) {
          $(window).off("scroll.recommend");
          return;
        }
        /* 		if(screenH + scrollY + boundary > bottomTop){} */
        if (screenH + scrollY + boundary > wrapperTop + wrapperH) {
          /* 加载下一页 */
          if (gs_recommendData.isLoading) {
            return;
          }
          $loading.show();
          gs_recommendData.isLoading = true;

          GsGoodsPcTpl.gsTplInt($wrapperId);
        }
      }, 30)
    );

  /* 初始化 */
  /* 	var devStatic = 'http://www.geshop.com.develop.s2.egomsl.com/' */
  var staticDomain = $('[data-static-domain]:eq(0)').attr('data-static-domain') ? $('[data-static-domain]:eq(0)').attr('data-static-domain') : "";
  $LAB
    .script(staticDomain + "/resources/javascripts/library/gs_laytpl.js?2018100101")
    .wait(function () {
      GsGoodsPcTpl.gsTplInt();
    });
});
