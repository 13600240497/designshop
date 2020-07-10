$(function () {
  var gs_recommendData = {
    skus: [],
    index: 0,
    isLastPage: false,
    isLoading: false,
    pageindex: 0,
    pagesize: 10,
    firstLoadingWating:0
  };

  function GetPageData () {
    return {
      skus: [],
      index: 0,
      isLastPage: false,
      isLoading: false,
      pageindex: 0,
      pagesize: 10
    };
  }

  function getQueryString(str,name) {
    var reg = new RegExp("(^|&)" + name + "=([^&]*)(&|$)", "i");
    var r = str.match(reg);
    var context = "";
    if (r != null)
         context = r[2];
    reg = null;
    r = null;
    return context == null || context == "" || context == "undefined" ? "" : context;
  }

  /* 状态变量 */
  $(".wrap_U000181_model1").each(function () {
    $(this).data("pageInfo", new GetPageData());
  });

  $('.re-request').on('click',function(){
    GsGoodsPcTpl.gsTplInt();
  })

  var $loading = $(".ui-recommend-loading");

  function _lozayLoadImg (items){
    var $items = $(items);
    var device_id = "",appsflyer_device_id = "";
    if(GESHOP_PLATFORM === 'app'){
      var userAgent = window.navigator.userAgent;
      device_id = getQueryString(userAgent,'device_id');
      appsflyer_device_id = getQueryString(userAgent,'appsflyer_device_id');
    }
    if (!$.fn.lazyload && typeof GESHOP_STATIC != 'undefined') {
      $items.each(function () {
        var $img = $(this)

        if ($img.attr("src") != $img.attr("data-original")) {
          $img.attr("src", $img.attr("data-original"));
        }
        if (typeof gbLogsss != "undefined") {
          gbLogsss.getsku($img);
          gbLogsss.sendsku();
        }
        if (typeof aigbLogsss != "undefined") {
          aigbLogsss.getsku($img,device_id,appsflyer_device_id)
          aigbLogsss.sendsku();
        }
      });
    }else{
      if ($.fn.lazyload) {
        $items.lazyload({
          threshold: 100,
          effect: 'fadeIn',
          failure_limit: 20,
          skip_invisible: false,
          load: function (remains, settings) {
            if (typeof gbLogsss != "undefined") {
              gbLogsss.getsku($(this));
              gbLogsss.sendsku();
            }

            if (typeof aigbLogsss != "undefined") {
              aigbLogsss.getsku($(this),device_id)
              aigbLogsss.sendsku();
            }

          }
        })
      } else {
        window.GS_GOODS_LAZY_FN('.js-geshopImg-lazyload');
      }

    }
  }

  function _addLoadSku($items){
    $items.each(function () {
      var $img = $(this),
        sku = +$img.data("sku");
      if (gs_recommendData.skus.indexOf(sku) === -1) {
        gs_recommendData.skus.push(sku);
      }
    });


  }

  var GsGoodsPcTpl = (function (my) {
    my.gsTplIntData = function (listData, dataid) {
      var skuCurrent = [];
      for (var i = 0; i < listData.length; i++) {
        skuCurrent.push(listData[i].goodssn);
      }

      if (!dataid) {
        $(".wrap_U000181_model1").each(function () {
          var targetId = $(this).attr("data-id");
          var infiniteTpl = $("#gsTpl_" + targetId).html();
          if (targetId) {
            var dataid = targetId;
          }

          $('.geshop_eurogentec_' + dataid).remove();
          gs_recommendData.firstLoadingWating = 1;
         // console.log(gs_recommendData.firstLoadingWating)

          gs_laytpl.config({ open: "<%", close: "%>" });
          gs_laytpl(infiniteTpl).render(listData, function (html) {
            /* var $componentTarget = $('[data-id=' + gidPage + ']') */
            var $componentTarget = dataid
              ? $(".wrap_U000181_model1[data-id=" + dataid + "]")
              : $(".wrap_U000181_model1");

            if (html) {
              var $html = $(html);
              var $imgItem = $html.find("img.js-geshopImg-lazyload");
              $componentTarget.find(".gs-goodsWrap ul").append($html);
              _addLoadSku($imgItem);
              _lozayLoadImg($imgItem);
            } else {
              gs_recommendData.isLastPage = true;
            }
            if (window.GLOBAL && window.GLOBAL.currency) {
              window.GLOBAL.currency.change_html();
            }
          });
        });
      } else {
        var infiniteTpl = $("#gsTpl_" + dataid).html();
        gs_laytpl.config({ open: "<%", close: "%>" });
        gs_laytpl(infiniteTpl).render(listData, function (html) {
          /* var $componentTarget = $('[data-id=' + gidPage + ']') */
          var $componentTarget = dataid
            ? $(".wrap_U000181_model1[data-id=" + dataid + "]")
            : $(".wrap_U000181_model1");

          if (html) {
            var $html = $(html);
            var $imgItem = $html.find("img.js-geshopImg-lazyload");
              $componentTarget.find(".gs-goodsWrap ul").append($html);
              _addLoadSku($imgItem);
              _lozayLoadImg($imgItem);

          } else {
            gs_recommendData.isLastPage = true;
          }
          if (window.GLOBAL && window.GLOBAL.currency) {
            window.GLOBAL.currency.change_html();
          }
        });
      }

      $loading.hide();
      gs_recommendData.isLoading = false;
    };
    my.gsTplInt = function (dataid) {
      my.getTplProduct({}, dataid).done(function (res) {
        $('div[name=netError]').hide();
        if (res.status == 1 && res.result) {

          /* 根据ai返回的数据去请求php后台数据 */
          my.getPhpGoodsData(res.result).done(function (res) {
            var goodsList = res.data.goods_list;
            // 如果是app则替换deeplink`
            if(GESHOP_PLATFORM === 'app'){
              goodsList.forEach(function(item){
                item.url_title = item.url_title.replace('deeplink',document.title);
              });
            }

            my.gsTplIntData(res.data.goods_list, dataid);
            gs_recommendData.pageindex++;
          });
        }
      }).fail(function(res){
        if(gs_recommendData.pageindex == 0){
          $('div[name=netError]').show();
          $loading.hide();
          gs_recommendData.isLoading = false;
        }else if(gs_recommendData.pageindex <= 10){
          var $wrapper = $(".wrap_U000181_model1");
          var $wrapperId = $wrapper.attr("data-id");
          $loading.show();
          gs_recommendData.isLoading = true;
          GsGoodsPcTpl.gsTplInt($wrapperId);
        }
      });


    };
    my.getTplProduct = function (params, dataid) {
      var lang;
      if (typeof JS_LANG === "undefined") {
        lang = "en";
      } else {
        lang = JS_LANG.slice(0, -1) || "en";
      }

      var platform,cookie,regioncode;
      if(GESHOP_PLATFORM === 'wap'){
        platform = 'M';
        cookie = '';
        regioncode = '';
      }else if(GESHOP_PLATFORM === 'app'){
        var userAgent = window.navigator.userAgent;
        cookie = getQueryString(userAgent,'appsflyer_device_id');

        platform = getQueryString(userAgent,'platform');
        platform = platform === null ? 'IOS' : platform.toUpperCase();
        regioncode = getQueryString(userAgent,'country_code');
      }

      var data = my.extendDeep(
        {
          cookie: cookie,
          lang: lang,
          platform: platform,
          regioncode: "",
          pageindex: gs_recommendData.pageindex,
          pagesize: gs_recommendData.pagesize,
          ip: '',
          pipelinecode: GESHOP_PIPELINE
        },
        params
      );
      return $.ajax({
        url: "https://glbg.logsss.com/",
        //url: "http://34.226.113.254:80",
        type: "post",
        timeout : 6000,
        data: {
          params: JSON.stringify(data),
          recommendType: 2060101
        }
      });
    };
    my.getPhpGoodsData = function(result){
      var lang;
      var goodsSn = [];
      if (typeof JS_LANG === "undefined") {
        lang = "en";
      } else {
        lang = JS_LANG.slice(0, -1) || "en";
      }

      if(result.length > 0){
        result.forEach(function (element) {
          goodsSn.push(element.goodssn);
        });
      }


      var params = {
        lang: lang,
        goodsSn: goodsSn.join(","),
        platform: GESHOP_PLATFORM,
        pipeline: (typeof GESHOP_PIPELINE != 'undefined' ? GESHOP_PIPELINE : '')
      }
      var domain = GESHOP_INTERFACE.cmsgoods.url;
      return $.ajax({
        url: domain,
        type: "post",
        dataType: "json",
        data: {
          content: JSON.stringify(params)
        },
        cache: false
      });
    };

    /* 深拷贝 */
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

    /* 节流事件 */
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
      //if (!data.priceList) return;
      $.each(data.data.goods_list, function (key, obj) {
        var $elems = $('.my_shop_price[data-sku="' + obj.goods_sn + '"]');

        $elems.each(function () {
          var $elem = $(this);
          /* 1:秒杀2:新人专享3:app专享4:清仓5:促销6:预促销 */
          if (+obj.shop_price !== 0) {
            /* 价格不为0，变更销售价 */
            $elem.attr("data-orgp", obj.price).attr("orgp", obj.shop_price);
          }
        });
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

    }
  };



  /* 上拉加载更多 */
  $(".wrap_U000181_model1").length &&
    $(window).on(
      "scroll.recommend",
      GsGoodsPcTpl.throttle(function () {
       // console.log(gs_recommendData.firstLoadingWating)
        if (gs_recommendData.firstLoadingWating !=1){return;}
        var $wrapper = $(".wrap_U000181_model1");
        var $wrapperId = $wrapper.attr("data-id");
        var screenH = (document.documentElement || document.body).clientHeight,
          scrollY = window.scrollY,
          /* 			bottomTop = $('#footer').offset().top, */
          boundary = 50,
          wrapperTop = $wrapper.offset().top,
          wrapperH = $wrapper.height();
        if (gs_recommendData.isLastPage || gs_recommendData.pageindex == 10) {
          $(window).off("scroll.recommend");
          $('div[name=ended]').show();
          return;
        }
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


