
;(function(){

  var staticDomain = $('[data-static-domain]:eq(0)').attr('data-static-domain') ? $('[data-static-domain]:eq(0)').attr('data-static-domain') : '',
    rankdetailObj = GESHOP_INTERFACE ? GESHOP_INTERFACE.getrankdetail : {};
  
  var LBTplObj = {
    renderTpl: function () {
      gs_laytpl.config({ open: "<%", close: "%>" });

      // 遍历 - 兼容多个组件各自获取数据
      $('.wrap-U000178-default').each(function(i, element) {
        var $ele = $(element);
        
        // 异步接口type
        var type = parseInt($ele.find('.leader-board-container').attr('data-asyncdata-id')),
          count = parseInt($ele.find('.leader-board-container').attr('data-asyncdata-count')),
          cate_id = parseInt($ele.find('.leader-board-container').attr('data-cate-id'));

        var getTpl = $ele.find('.m-leader-board-template').html(),
          view = $ele.find('.leader-board-container'),
          lang = typeof GESHOP_LANG != 'undefined' ? GESHOP_LANG : 'en'

        var params = {
          type: type,
          lang: lang,
          pageno: 1,
          pagesize: count,
          cateid: cate_id,
          pipeline: (typeof GESHOP_PIPELINE != 'undefined' ? GESHOP_PIPELINE : '')
        };
        
        var url = rankdetailObj.url
        
        // 组件已编辑过
        if (type) {
            window.GEShopCommonFn_Vue.$jsonp(url,params,{target:$(this)}).done(function(res){
                if(res.code != 0){return false;}
                var data = res.data;
                data.type = type;

                var renderFn = {
                    f: function () {
                        gs_laytpl.config({ open: "<%", close: "%>" });
                        gs_laytpl(getTpl).render(data, function(html, e){
                            view.html(html);

                            if (window.GLOBAL && window.GLOBAL.currency) {
                                window.GLOBAL.currency.change_html()
                            }
                            if (window.FUN && window.FUN.currency) {
                                window.FUN.currency.change_html()
                            }
                            initialImage(element);
                            // 图片懒加载初始化
                            if (window.GESHOP_UTIL && typeof window.GESHOP_UTIL.goodsLazy === 'function') {
                                window.GESHOP_UTIL.goodsLazy($('.js-leader-board-container-U000178:eq('+i+')').find('.js-geshopImg-lazyload'));
                            }
                        });
                    }
                }
                // 临时解决方案：稍后请求数据
                // 当与异步商品列表同时使用时，同时请求数据会出现乱码，初步判断是gs_laytpl渲染问题，考虑更换模板引擎或修改源码定义重新渲染方法
                setTimeout(function () {
                    renderFn.f();
                }, 2000);
            });
        } 
        // 首次拖入组件
        else {
          var data = { goodsInfo: [] };
          gs_laytpl.config({ open: "<%", close: "%>" });
          gs_laytpl(getTpl).render(data, function(html){
            view.html(html);
            if (window.GLOBAL && window.GLOBAL.currency) {
              window.GLOBAL.currency.change_html()
            }
            if (window.FUN && window.FUN.currency) {
              window.FUN.currency.change_html()
            }
          });
        }
      });

    }
  }

  /**
   * 模板初始化
   */
  $LAB
		.script(staticDomain + "/resources/javascripts/library/gs_laytpl.js?2018100601").wait(function () {
      $(function() {
        LBTplObj.renderTpl();
      });
		});
  
  /**
   * 初始化图片
   */
  function initialImage(element) {
    $(element).find('img.js_gdexp_lazy.js-geshopImg-lazyload.js_gbexp_lazy').each(function(i,image) {
      if (typeof gbLogsss != 'undefined') {
        gbLogsss.getsku($(image));  
        gbLogsss.sendsku();
      }
    });
  }
  
})();
