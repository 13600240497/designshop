;$(function() {
  var isEidtenv = $('.geshop-U000203-default input[name=editEnv]').val();

  $(".geshop-U000203-default").each(function(item){
      var $component = $(this)
      $component.find('.goods-list').eq(0).show();
      $component.find('.nav-list li').eq(0).addClass('active');

      $component.find('.nav-list li').on('click',function(){
          var index = $(this).index();
          $(this).siblings().removeClass('active');
          $(this).addClass('active');
          $component.find('.goods-list').hide();
          $component.find('.goods-list').eq(index).show();
          if ($.fn.lazyload) {
              $component.find("img.js-lazy").lazyload({
                  skip_invisible: true
              });
          } else {
              window.GS_GOODS_LAZY_FN('.js-lazy');
          }
      });
  });

  $('.geshop-U000203-default .goods-list .goods-item').each(function(){
    if($(this).find('.geshop-goods-img').hasClass('soldout-bg')){
      $(this).find('.shop-now-container').show();
      $(this).find('.shop-now-text').text('');
    }
  });

  if(isEidtenv == 1){
      $("img.js-lazy").each(function(){
          $(this).attr('src',$(this).attr('data-original'));
      });
  }else{
      var platform = GLOBAL.util.getPlatform();
      if ($.fn.lazyload) {
          $(".geshop-U000203-default img.js-lazy").lazyload({
              skip_invisible: true
          });
      } else {
          window.GS_GOODS_LAZY_FN('.js-lazy');
      }
      $('.geshop-U000203-default .goods-list .goods-items').each(function(){

          $(this).find('.geshop-goods-promotions .promotions-text').each(function(){
              var promotionsLength = $(this).data('promotions-length');
              if(promotionsLength > 1){
                  if(platform === 2){
                      $(this).append(' ···');
                  }else{
                      $(this).append('<img src="https://geshoptest.s3.amazonaws.com/uploads/WaiETHlZ8ShDeFRXMB97Yjc3uQ4InAJr.png">');
                  }

              }
          });

      })
  }

  $(document)
  .on('mouseenter', '.geshop-U000203-default .geshop-goods-img', function() {
    if (platform === 2 && !$(this).hasClass('soldout-bg')){
        $(this).find('.shop-now-container').show();
    }


  }).on('mouseleave', '.geshop-U000203-default .geshop-goods-img', function() {
    if (platform === 2 && !$(this).hasClass('soldout-bg')){
        $(this).find('.shop-now-container').hide();
    }

  }).on('mouseenter', '.geshop-U000203-default .geshop-goods-promotions', function() {
      var platform = GLOBAL.util.getPlatform();
      // pc
      var sonLength = $(this).has('.promotions-text').length;
      var promotionsLength = $(this).find('.promotions-text').data('promotions-length');
      if (platform === 2 && sonLength > 0 && promotionsLength > 1) {
          $(this).next('.geshop-goods-promotions-more').removeClass('none');
      }
  })
  .on('mouseleave', '.geshop-U000203-default .geshop-goods-promotions', function() {
      var platform = GLOBAL.util.getPlatform();
      // pc
      if (platform === 2) {
          $(this).next('.geshop-goods-promotions-more').addClass('none');
      }
  })
  .on('click', '.geshop-U000203-default .geshop-goods-promotions', function() {
    var platform = GLOBAL.util.getPlatform();
    var $more =$(this).next('.geshop-goods-promotions-more');
    // mobile, pad
    if (platform !== 2 && $(this).data('flag') == 1) {
        var $parent = $(this).parent();
        if($(this).find('img').hasClass('up')){
            $(this).find('img').removeClass('up');
        }else{
            $(this).find('img').addClass('up');
        }

        if ($more.is('.none')) {
            $more.removeClass('none');
        }
        else{
            $more.addClass('none');
        }
    }
  });
  /* m端 view_more */
    class pages{
        constructor (data){
            this._page = data.page ? parseInt(data.page) : 1;
            this._pageSize = parseInt(data.pageSize);
            this._totalCount = parseInt(data.totalCount);
            this._pageCount = Math.ceil(data.totalCount / data.pageSize);
        }
        get page(){
            return this._page
        }
        get pageSize(){
            return this._pageSize
        }
        get totalCount(){
            return this._totalCount
        }
        get pageCount(){
            return this._pageCount
        }
        set page(val){
            return this._page = parseInt(val)
        }
    }

    function showPageGoods(element,pageInfo){
        // show_num 商品显示个数
        var show_num = pageInfo.page * pageInfo.pageSize;
        var $pageVisible = show_num < pageInfo.totalCount ? $('.goods-list-ul li:eq('+ show_num +')',$(element)).prevAll() : $('.goods-list-ul li',$(element));
        if($pageVisible.length > 0){
            $('.goods-list-ul li',$(element)).removeClass('page-visible');
            $pageVisible.addClass('page-visible');
        }
        if ($.fn.lazyload) {
            $(element).find("img.js-lazy").lazyload({
                skip_invisible: true
            });
        } else {
            window.GS_GOODS_LAZY_FN('.js-lazy');
        }
    }


    /* 遍历组件m端数据展示 */
    $(".geshop-U000203-default").each(function(item){
        var $component = $(this);
        /*var platform = GLOBAL.util.getPlatform();*/
        // 遍历tab下商品数据
        var view_more_text = $component.find('input[name=view_more_text]').val();
        var view_less_text = $component.find('input[name=view_less_text]').val();
        var pageSize = parseInt($(this).find('input[name=page_show_goods_number]').val());
        $component.find('.goods-list').each(function(index,element){
            // 保存tab下总分页数
            var totalCount = $(element).attr('data-len');
            // var pageCount = Math.ceil(totalNum/pageSize);
            var pageInfo = new pages({totalCount:totalCount,pageSize:pageSize});
            $(element).attr('data-pageCount',pageInfo.pageCount);
            // 显示第一个
            showPageGoods(element,pageInfo);
            /* view_more_btn 事件*/
            $(element).find('.view_more_btn').off('click').on('click',function () {
                var prevPage = pageInfo.page;
                var currentPage = pageInfo.page + 1;
                // view more
                if(currentPage <= pageInfo.pageCount){
                    if(currentPage === 1){
                        pageInfo.page = currentPage;
                        showPageGoods(element,pageInfo);
                        $(element).find('.view_more_btn span').text(view_more_text);
                        $(window).scrollTop($component.offset().top)
                    }else{
                        pageInfo.page = currentPage;
                        showPageGoods(element,pageInfo);
                        if( pageInfo.page ===pageInfo.pageCount ){
                            $(element).find('.view_more_btn span').text(view_less_text);
                        }
                    }

                }
                if(currentPage === pageInfo.pageCount){
                    pageInfo.page = 0;
                    return false;
                }
            })
        })

    });
});

