$(function () {
	var tab_item = $('[data-gid="U000110_discountList"] .gs-tab-box li');

	$('[data-gid="U000110_discountList"] .gs-tab-box').each(function () {
		var $box = $(this),
      ulW = $box.find('.goods-nav-name > li').length;
      $box.find('.goods-nav-name > li').eq(0).addClass('current');
		// if (ulW > 3) {
		// 	initSlideTab($box);
    // }
    initSlideTab($box, ulW);
  })

  tab_item.on('click', function () {
		var $labelParent = $(this).parents('.gs-tab-wrap:eq(0)'),
      $tab_content = $labelParent.next('.gs-tab-content');
    var $closestTabbox = $(this).closest('.geshop-content');
		$(this).addClass('current').siblings().removeClass('current');

    !$('[data-key="U000030"]').length && $('html,body').animate({scrollTop: $closestTabbox.offset().top},'slow');
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

	function initSlideTab ($box, uls) {
    $box.find('.goods-nav-name > li').eq(0).addClass('current');
    var staticDomain = $('[data-static-domain]:eq(0)').attr('data-static-domain') ? $('[data-static-domain]:eq(0)').attr('data-static-domain') : "";

    // 根据tab个数展示不同样式
    var slidesPerView = 1;
    var noSwiping = false;
    var noSwipingClass = '';
    if (uls == 1 || uls == 2 || uls == 3) {
      slidesPerView = uls;
      noSwiping = true;
      noSwipingClass = 'stop-swiping'
    } else {
      slidesPerView = 'auto';
    }

		// loadCss(staticDomain + '/resources/javascripts/library/swiper/swiper.min.css');
		$LAB.script(staticDomain + '/resources/javascripts/library/swiper/swiper.3.4.spec.min.js').wait(function () {

			new Swiper3($box.find('.swiper-container'), {
				autoplay: false,
        slidesPerView: slidesPerView,
				slideToClickedSlide:true,
				lazyLoading: true,
        onTransitionEnd: function(swiper){
          if(swiper.progress==1){
            swiper.activeIndex=swiper.slides.length-1
          }
        }
			});
		})
  };
var $hotSaleList = $('.wrap-U000110_discountList');
  $(window).on('scroll',function(){
    var scrollTop = $(this).scrollTop();
     $hotSaleList.each(function(){
       var $this = $(this);
       var gsTabTop = $(this).find(".gs-tab").offset().top;

       if(gsTabTop <= scrollTop && scrollTop < gsTabTop + $this.find('.geshop-content').outerHeight() && !$('[data-key="U000030"]').length){
          $this.find(".gs-tab").addClass('fixGsTab');
       }else{
          $this.find(".gs-tab").removeClass('fixGsTab');
       }
     })
  })

  var swiperList = $('.swiper-wrapper').children();
  if(swiperList.length > 6){
    $("#pre-button .icon-img-left").attr('style','display:block');
    $("#next-button .icon-img-right").attr('style','display:block');
  }
});

(function(){
 var staticDomain = $('[data-static-domain]:eq(0)').attr('data-static-domain') ? $('[data-static-domain]:eq(0)').attr('data-static-domain') : "";
  loadCss(staticDomain+'/resources/javascripts/library/swiper/swiper.min.css');

//   // $LAB.script(staticDomain + "/resources/layui/layui.all.js");
  // $LAB.script(staticDomain+'/resources/javascripts/library/swiper/swiper.3.4.spec.min.js');
  var LBTplObj = {
    renderTpl: function () {
      gs_laytpl.config({ open: "<%", close: "%>" });

      // 遍历 - 兼容多个组件各自获取数据
      $('.geshop-leaderboard-async-list[data-gid="U000110_discountList"]').each(function(i, element) {
        var $ele = $(element);

        var getTpl = $ele.find('.m-leader-board-template').html(),
            view = $ele.find('.leader-board-container'),
            lang = typeof GESHOP_LANG != 'undefined' ? GESHOP_LANG : 'en',
            url = GESHOP_INTERFACE.getrankdetail.url,
            pageInstanceId = $ele.attr('data-id');

        /* 列表渲染函数 */
        function _listRender(data) {
            gs_laytpl(getTpl).render(data, function(html){
                view.html(html);

                var $viewParent = view.parent();
                if(params.nomore){
                  $viewParent.find('.layui-laypage-next').addClass('layui-disabled');
                  params.pageno == 1 && $viewParent.find('.layui-pager').hide();
                }else{
                  $viewParent.find('.layui-laypage-next').removeClass('layui-disabled');
                }

                if( params.pageno > 1){
                  $viewParent.find('.layui-laypage-prev').removeClass('layui-disabled');
                }else{
                  $viewParent.find('.layui-laypage-prev').addClass('layui-disabled');
               }

                if (window.GLOBAL && window.GLOBAL.currency) {
                  window.GLOBAL.currency.change_html()
                }
                if (window.FUN && window.FUN.currency) {
                  window.FUN.currency.change_html()
                }

            });
        }

        var params = {
            type: 3,                // 1新品2热卖3低价
            cateid: null,           // 类目ID
            lang: lang,             // 语言包
            pageno: 1,              // 当前页码
            pagesize: 10,           // 每页条数
            nomore: false,          // 是否最后一页
            activeTabClassName: '', // 存放当前选中的Tab的Class
            pipeline: (typeof GESHOP_PIPELINE != 'undefined' ? GESHOP_PIPELINE : ''),
            client: (typeof GESHOP_PLATFORM != 'undefined' ? GESHOP_PLATFORM : '')
        };

        /* set default params */
        params.pagesize = $ele.find('.pagesize').val() || 10;
        params.cateid = $ele.find('.swiper-slide').eq(0).attr('data-asyncdata-id') || null;
        params.activeTabClassName = $ele.find('.swiper-slide').eq(0).attr('class') || '';

        /* 判断读取正式数据或者测试数据 */
        params.cateid != null ? loadPage(params, 'current', _listRender) : _listRender({goodsInfo: []});

        /* 事件绑定：tab点击 */
        $ele.find('.swiper-slide').click(function(element){

          var targetCateid = $(this).attr('data-asyncdata-id') || null;
          var targetClassName = $(this).attr('class') || '';

          /* 避免重复点击提交ajax */
          if (targetClassName == params.activeTabClassName && targetCateid == params.cateid) return false;

          /* 重置相关参数 */
          params.cateid = targetCateid;
          params.activeTabClassName = targetClassName;
          params.pageno = 1;
          params.nomore = false;

          loadPage(params, 'current', _listRender);
        });

        /* 事件绑定：分页加载点击 */
        $ele.on('click', '.layui-laypage-prev, .layui-laypage-next', function() {
          var $this = $(this);
          var _act = $this.hasClass('layui-laypage-prev') ? 'prev' : 'next';

          loadPage(params, _act, _listRender);
        });

        // console.log(params);

        $('#goodsNum-' + pageInstanceId).addClass('layui-pager').html('<div class="layui-box layui-laypage layui-laypage-default"><a href="javascript:;" class="layui-laypage-prev layui-disabled"><div class="prev-page" data-act="prev"></div></a><a href="javascript:;" class="layui-laypage-next"><div class="next-page" data-act="next"></div></a></div>')

        /* 初始化组件分页模块 */
        // layui.laypage.render({
        //   elem: 'goodsNum-' + pageInstanceId,
        //   count: params.pagesize,
        //   limit: 10,
        //   groups: 0,
        //   prev: '<div class="prev-page" data-act="prev"></div>',
        //   next: '<div class="next-page" data-act="next"></div>',
        // });

      });
    }
  }

  /*
    @params 请求接口的参数
    @action 获取页面数据的行为 [prev=上一页][next=下一页][current=当前页]
    @callback 渲染回调方法
  */
  function loadPage(params, action, callback) {
    var _emptyResponse = { goodsInfo: [] };

    if (typeof callback != "function") return false;
    if (!params || !params.cateid) return callback(_emptyResponse);

    switch(action) {
      case 'prev':
        if (params.pageno <= 1) return false;
        params.pageno = params.pageno >= 1 ? params.pageno - 1 : params.pageno;
        break;
      case 'next':
        if (params.nomore == true) return false;
        ++params.pageno;
        break;
      default:
        break;
    }

    $.ajax({
      url: GESHOP_INTERFACE.getrankdetail.url,
      type: 'get',
      dataType: 'jsonp',
      data: { content: JSON.stringify(params) },
      success: function(res) {
        // console.log(res.data.goodsInfo)
        // 判断是否最后一页
        params.nomore = res.data.goodsInfo.length < params.pagesize;
        params.isShowPage = res.data.goodsInfo.length > params.pagesize;
        res.data['cateid'] = params.cateid;
        res.data['pageno'] = params.pageno;
        res.data['pagesize'] = params.pagesize;
        // 回调
        callback(res.data);
        /* currency initial in Web and Wap */
        if (window.GLOBAL && window.GLOBAL.currency) {
          window.GLOBAL.currency.change_html()
        }
        if (window.FUN && window.FUN.currency) {
          window.FUN.currency.change_html()
        }
      },
      fail: function() {
        callback && callback(_emptyResponse);
      }
    });
  }
  var staticDomain = $('[data-static-domain]:eq(0)').attr('data-static-domain') ? $('[data-static-domain]:eq(0)').attr('data-static-domain') : "";
  $LAB
    .script(staticDomain + "/resources/javascripts/library/gs_laytpl.js?2018100601")
    .wait(function () {
      LBTplObj.renderTpl();
    });
})();



