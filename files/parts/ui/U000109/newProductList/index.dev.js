$(function () {
	var tabBox = $('[data-gid="U000109_newProductList"] .gs-tab-box');

	tabBox.each(function () {
		var $box = $(this),
     ulW = $box.find('.goods-nav-name').outerWidth();

    $box.find('.goods-nav-name > li').eq(0).addClass('current');

		if (ulW >= 900) {
			initSlideTab($box);
      $box.find(".icon-img-left").show();
      $box.find(".icon-img-right").show();
    } else {
      $box.find(".icon-img-left").hide();
      $box.find(".icon-img-right").hide();
		}
	})

	tabBox.on('click', 'li', function () {
		var $labelParent = $(this).parents('.gs-tab-wrap:eq(0)'),
			$tab_content = $labelParent.next('.gs-tab-content');

		$(this).addClass('current').siblings().removeClass('current')

        if ($.fn.lazyload) {
            $tab_content.find('.gs-tab-item img.js-geshopImg-lazyload').lazyload({
                threshold: 100,
                failure_limit: 20,
                skip_invisible: false
            })
        } else {
            window.GS_GOODS_LAZY_FN('.js-geshopImg-lazyload');
        }
	});

	function initSlideTab ($box) {
		var staticDomain = $('[data-static-domain]:eq(0)').attr('data-static-domain') ? $('[data-static-domain]:eq(0)').attr('data-static-domain') : "";

		loadCss(staticDomain + '/resources/javascripts/library/swiper/swiper.min.css');
		$LAB.script(staticDomain + '/resources/javascripts/library/swiper/swiper.3.4.spec.min.js').wait(function () {

			new Swiper3($box.find('.swiper-container'), {
				autoplay: false,
        loop: true,
        slidesPerView: 'auto',
        observer: true,
        observeParents: true,
				nextButton: $box.find('.next-btn'),
				prevButton: $box.find('.pre-btn')
			});
		})
  }
});

(function(){
 var staticDomain = $('[data-static-domain]:eq(0)').attr('data-static-domain') ? $('[data-static-domain]:eq(0)').attr('data-static-domain') : "";
  loadCss(staticDomain+'/resources/javascripts/library/swiper/swiper.min.css');

  $LAB.script(staticDomain+'/resources/javascripts/library/swiper/swiper.3.4.spec.min.js');

  var LBTplObj = {
    renderTpl: function () {
      gs_laytpl.config({ open: "<%", close: "%>" });

      // 遍历 - 兼容多个组件各自获取数据
      $('.geshop-leaderboard-async-list[data-gid="U000109_newProductList"]').each(function(i, element) {
        var $ele = $(element);

        // 异步接口分类id
        var cateid = parseInt($ele.find('.swiper-slide').attr('data-asyncdata-id'));

        var pagesize = $ele.find('.pagesize').val();

        var getTpl = $ele.find('.pc-leader-board-template').html(),
          view = $ele.find('.leader-board-container'),
          lang = typeof GESHOP_LANG != 'undefined' ? GESHOP_LANG : 'en'

        var firstid = $ele.find('.swiper-slide').eq(0).attr('data-asyncdata-id');
        var params = {
          type: 1,
          cateid: firstid,
          lang: lang,
          pageno: 1,
          pagesize: pagesize,
          pipeline: (typeof GESHOP_PIPELINE != 'undefined' ? GESHOP_PIPELINE : '')
        };

        var url = GESHOP_INTERFACE.getrankdetail.url;
        var content = { content: JSON.stringify(params) };

        // 组件已编辑过
        if ( cateid ) {
          $.ajax({
            url: url,
            type: 'get',
            dataType: 'jsonp',
            data: content,
            success: function (res) {
              var data = res.data;
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
        } else {
          var data = { goodsInfo: [] };
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

        $ele.find('.swiper-slide').click(function(element){
          var cateid = $(this).attr('data-asyncdata-id');
          var params = {
            type: 1,
            cateid: cateid,
            lang: lang,
            pageno: 1,
            pagesize: pagesize,
            pipeline: (typeof GESHOP_PIPELINE != 'undefined' ? GESHOP_PIPELINE : '')
          };

          var url = GESHOP_INTERFACE.getrankdetail.url;
          var content = { content: JSON.stringify(params) };

          // 组件已编辑过
          if ( cateid ) {
            $.ajax({
              url: url,
              type: 'get',
              dataType: 'jsonp',
              data: content,
              success: function (res) {
                var data = res.data;

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
        })
      });
    }
  }

  /**
   * 模板初始化
   */
	$LAB.script(staticDomain + "/resources/javascripts/library/gs_laytpl.js?2018100601")
		.wait(function () {
      LBTplObj.renderTpl();

      (function(){
        var $targetBox = $('.js_gs_tab_wrap_fixed');
        $.each($targetBox,function(index,elem){
            var $elem,$elemGsTabWrap,$elemFixbox,$elemFixBoxPic;
            $elem = $(elem);

            if($elem.data('isaddfixbox')==1){
              return;
            }else{
              $elem.data('isaddfixbox',1);
              $elemGsTabWrap = $elem.find('.gs-tab-wrap');
              $elemGsTabWrap.wrap('<div class="gs-tab-wrap-fixbox"></div>');

              $elemFixbox = $elem.find('.gs-tab-wrap-fixbox');
              $elemFixbox.height($elemGsTabWrap.find('.gs-tab-box').outerHeight());
              $elemFixbox.wrap('<div class="gs-tab-wrap-fixbox-Pic"></div>');

              $elemFixBoxPic = $elem.find('.gs-tab-wrap-fixbox-Pic');
              $elemFixBoxPic.height($elemGsTabWrap.find('.gs-tab-box').outerHeight());

              $elemFixbox.on('click','.swiper-slide',function(){
                  var targetScrollTop = $(this).closest('.gs-tab-wrap-fixbox-Pic').offset().top;
                  $('html,body').animate({scrollTop: targetScrollTop},500);
                  //  var $listGoodsBox = $(this).closest('.gs-tab').find('.leader-board-container');
                  // $listGoodsBox.html('<div style="height:'+$(this).closest('.gs-tab').find('.gs-tab-show').outerHeight()+'px;text-align:center;"><img style="margin-top:200px;" src="https://geshopimg.logsss.com/uploads/K016no8NLPkfFRXZ5IEsv4OJrl7VD3Wm.gif"></div>')
              })
            }
        });

        function scrollCalBackFn($targetBox,scrollTop){

            $targetBox.each(function(){
                var $this = $(this);
                var gsTabOffset = $(this).find(".gs-tab").offset();
                var gsTabBoxWidth = $this.find(".gs-tab-wrap-fixbox-Pic").outerWidth();
                var disId = $(this).closest('.geshop-component-box').data('id');
                // var fixedTop = GESHOP_UTIL&& GESHOP_UTIL.getSiteFiedHeader ? GESHOP_UTIL.getSiteFiedHeader() : 0;
                if (gsTabOffset.top <= scrollTop && scrollTop < gsTabOffset.top + $this.find('.geshop-content').outerHeight()) {
                  $this.find(".gs-tab-wrap-fixbox").css({"position":"fixe",top:0,left:gsTabOffset.left+'px',"width":gsTabBoxWidth+'px','z-index':9999});

                  // $('#nav-list').closest('[class*="fix"]').addClass('geshop-'+disId+'-fiexd');
                  // $('.geshop-'+disId+'-fiexd').hide();

                    $this.addClass('js-geshop-nav-fixed');
                    // 站点导航栏处理
                    if ($('.js-geshop-nav').length) {
                        $('.js-geshop-nav').hide();
                    }

                    // 页面中存在水平导航时，隐藏水平导航
                    if ($('div[data-key="U000027"]').length) {
                        $('div[data-key="U000027"]').find('ul').hide();
                    }

                } else {
                    $this.find(".gs-tab-wrap-fixbox").css({"position":"static"});
                    $this.removeClass('js-geshop-nav-fixed');
                    // $('.geshop-'+disId+'-fiexd').show().removeClass('geshop-'+disId+'-fiexd');

                    // 站点导航栏处理
                    if ($('.js-geshop-nav').length) {
                        $('.js-geshop-nav').show();
                    }

                    // 页面中存在水平导航时，隐藏水平导航
                    if ($('div[data-key="U000027"]').length) {
                        $('div[data-key="U000027"]').find('ul').show();
                    }
                }
            });

            if (GEShopSiteCommon) {
                GEShopSiteCommon.jsNavFixed();
            }

        }

        $(window).on('resize',function (params) {
            scrollCalBackFn($targetBox,$(window).scrollTop())
        });

        $(window).on('scroll',function(){
            var scrollTop = $(this).scrollTop();
            scrollCalBackFn($targetBox,scrollTop);
        });
    })()



    });
})();



