; $(function () {
    var isEidtenv = $('.geshop-U000203-appprice input[name=editEnv]').val();
    if(isEidtenv == 0){
        var platform = GLOBAL.util.getPlatform();
    }

    $(".geshop-U000203-appprice").each(function (item) {
        var $component = $(this);
        $component.find('.goods-list').eq(0).show();
        $component.find('.nav-list li').eq(0).addClass('active');
        $component.find('.goods-list .btn-seemore').eq(0).css('display','block');
        /* 同步改为异步 */
        var staticDomain = GESHOP_STATIC;
        $LAB.script(staticDomain + "/resources/javascripts/library/gs_laytpl.js").wait(function () {

            gs_laytpl.config({ open: "<%", close: "%>" });
            var goodsHtml = $component.find('.goods-list .clearfix');
            var goodsSn = $component.find('.nav-list li:first').data('sku');
            var priceType = $component.find('input[name=priceType]').val();
            if(goodsSn){
                getData(goodsSn);
            }


            // 首次加载第一个导航的数据

            $component.find('.nav-list li').on('click', function (index) {
                var index = $(this).index();
                $(this).siblings().removeClass('active');
                $(this).addClass('active');
                // $component.find('.goods-list').hide();
                // $component.find('.goods-list').eq(index).show();
                goodsSn = $(this).data('sku');
                if($(this).data('link')){
                    $component.find('.goods-list .btn-seemore').hide();
                    $component.find('.goods-list .btn-seemore').eq(index).css('display','block');
                }else{
                    $component.find('.goods-list .btn-seemore').hide();
                }
                // view more 切换
                $component.find('.tab-item:eq('+ index +')').addClass('active').siblings().removeClass('active');
                getData(goodsSn);

            });

            function getData(goodsSn) {
                var params = {
                    lang: GESHOP_LANG || "en",
                    client: GESHOP_PLATFORM || 'web',
                    goodsSn: goodsSn,
                    pipeline: 'DL',
                    filterStock: 1,
                    promotePriceType: priceType
                };

                var url = GESHOP_INTERFACE.goods_async_detail.url;
                var component_id = $component.attr('data-id');
                window.GEShopCommonFn_Vue.$jsonp(url, params, { pid: component_id }).done(function (res) {
                    if (res.data.goodsInfo) {
                        initHtml(res.data.goodsInfo);
                        // m版数据处置
                        $component.find('.goods-list').attr('data-len', res.data.goodsInfo.length);
                        renderMobileGoods();
                    }
                });
            }

            // 渲染商品
            function initHtml(data) {
                var getTpl = $component.find('.cont-goodsList-js').html();

                gs_laytpl(getTpl).render(data, function (html) {
                    goodsHtml.html(html);
                    window.GLOBAL && window.GLOBAL.currency && window.GLOBAL.currency.change_html();
                    window.FUN && window.FUN.currency && window.FUN.currency.change_html();

                    if (typeof GS_GOODS_LAZY_FN != 'undefined') {
                        GS_GOODS_LAZY_FN($(goodsHtml).find('.js-lazy'));
                    } else {
                        $component.find('.goods-list').find('.js-lazy').each(function () {
                            $(this).attr('src', $(this).data('original'));
                        });
                    }

                    addMoreSign();
                    window.GLOBAL && window.GLOBAL.currency && window.GLOBAL.currency.change_html();
                    window.FUN && window.FUN.currency && window.FUN.currency.change_html();

                });
            }
        });

        $component.find('.goods-list .goods-item').each(function () {
          if ($(this).find('.geshop-goods-img').hasClass('soldout-bg')) {
              $(this).find('.shop-now-container').show();
              $(this).find('.shop-now-text').text('');
          }
        });



        function addMoreSign(){
            if (isEidtenv == 1) {
                $("img.js-lazy").each(function () {
                    $(this).attr('src', $(this).attr('data-original'));
                });
            } else {


                if ($.fn.lazyload) {
                    $component.find('img.js-lazy').lazyload();
                } else {
                    window.GS_GOODS_LAZY_FN('.js-lazy');
                }

                $component.find('.goods-list .goods-item').each(function () {
                    $(this).find('.geshop-goods-promotions .promotions-text').each(function(){

                        var promotionsLength = $(this).data('promotions-length');
                        if (promotionsLength > 1) {
                            if (platform === 2) {
                                $(this).append(' ···');
                            } else {
                                $(this).append('<img src="https://geshoptest.s3.amazonaws.com/uploads/WaiETHlZ8ShDeFRXMB97Yjc3uQ4InAJr.png">');
                                // $(this).append('<span class="down-arrow"></span>');
                            }
                        }

                    })
                });
            }
        }



    });

    $(document)
        .on('mouseenter', '.geshop-U000203-appprice .geshop-goods-img', function () {
            if (platform === 2 && !$(this).hasClass('soldout-bg')) {
                $(this).find('.shop-now-container').show();
            }


        }).on('mouseleave', '.geshop-U000203-appprice .geshop-goods-img', function () {
            if (platform === 2 && !$(this).hasClass('soldout-bg')) {
                $(this).find('.shop-now-container').hide();
            }

        }).on('mouseenter', '.geshop-U000203-appprice .geshop-goods-promotions', function () {

            // pc
            var sonLength = $(this).has('.promotions-text').length;
            var promotionsLength = $(this).find('.promotions-text').data('promotions-length');
            if (platform === 2 && sonLength > 0 && promotionsLength > 1) {
                $(this).next('.geshop-goods-promotions-more').removeClass('none');
            }
        })
        .on('mouseleave', '.geshop-U000203-appprice .geshop-goods-promotions', function () {

            // pc
            if (platform === 2) {
                $(this).next('.geshop-goods-promotions-more').addClass('none');
            }
        })
        .on('click', '.geshop-U000203-appprice .geshop-goods-promotions', function () {
            var $more = $(this).next('.geshop-goods-promotions-more');
            // mobile, pad
            if (platform !== 2 ) {
                // if ($(this).find('.down-arrow').hasClass('up')) {
                //     $(this).find('.down-arrow').removeClass('up');
                // } else {
                //     $(this).find('.down-arrow').addClass('up');
                // }

                if($(this).find('img').hasClass('up')){
                    $(this).find('img').removeClass('up');
                }else{
                    $(this).find('img').addClass('up');
                }

                if ($more.is('.none')) {
                    $more.removeClass('none');
                }
                else {
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

    /**
     * 商品显示
     * @param {jqueryNode} element 
     * @param {Page} pageInfo 
     */
    function showPageGoods(element,pageInfo){
        // show_num 商品显示个数
        var show_num = pageInfo.page * pageInfo.pageSize;
        var $pageVisible = show_num < pageInfo.totalCount ? $('.goods-list-ul .goods-item-li:eq('+ show_num +')',$(element)).prevAll() : $('.goods-list-ul .goods-item-li',$(element));
        if($pageVisible.length > 0){
            $('.goods-list-ul .goods-item-li',$(element)).removeClass('page-visible');
            $pageVisible.addClass('page-visible');
        }
        try {
            if ($.fn.lazyload) {
                $(element).find("img.js-lazy").lazyload();
            } else {
                window.GS_GOODS_LAZY_FN('.js-lazy');
            }
        } catch (err) {}
    }


    /* 遍历组件m端数据展示 */
    function renderMobileGoods(){
        $(".geshop-U000203-appprice").each(function(item){
            var $component = $(this);
            /*var platform = GLOBAL.util.getPlatform();*/
            // 遍历tab下商品数据
            var view_more_text = $component.find('input[name=view_more_text]').val();
            var view_less_text = $component.find('input[name=view_less_text]').val();
            var pageSize = parseInt($(this).find('input[name=page_show_goods_number]').val());

            // view more 切换
            var $itemMore = $component.find('.tab-item.active .item-more-wrap');
            if($component.find('.goods-list').attr('data-len') > pageSize){
                $itemMore.show();
                $component.find('.view_more_btn span').text(view_more_text)
            }else{
                $itemMore.hide();
            }


            $component.find('.goods-list').each(function(index,element){
                // 保存tab下总分页数
                var totalCount = $(element).attr('data-len');
                var page = $component.find('.nav-list .active').attr('data-page');
                var pageInfo = new pages({totalCount:totalCount,pageSize:pageSize});
/*                // 保留原分页
                var pageInfo = new pages({totalCount:totalCount,pageSize:pageSize,page:page});
                // 记录page
                $component.find('.nav-list .active').attr('data-page',pageInfo.page);*/
                $(element).attr('data-pageCount',pageInfo.pageCount);
                // 显示第一个
                showPageGoods(element,pageInfo);
                /* view_more_btn 事件*/
                $component.find('.view_more_btn').off('click').on('click',function () {
                    var $btn = $(this);
                    pageInfo.page = pageInfo.page === pageInfo.pageCount ? 0 : pageInfo.page;
                    var currentPage = pageInfo.page + 1 <= pageInfo.pageCount ? pageInfo.page + 1 : pageInfo.pageCount;
                    // view more
                    if(currentPage <= pageInfo.pageCount){
                        if(currentPage === 1){
                            pageInfo.page = currentPage;
                            showPageGoods(element,pageInfo);
                            $btn.find('span').text(view_more_text);
                            $(window).scrollTop($component.offset().top)
                        }else{
                            pageInfo.page = currentPage;
                            showPageGoods(element,pageInfo);
                            if( pageInfo.page ===pageInfo.pageCount ){
                                $btn.find('span').text(view_less_text);
                            }
                        }

                    }
                    $component.find('.nav-list .active').attr('data-page',pageInfo.page);
                    if(currentPage === pageInfo.pageCount){
                        pageInfo.page = 0;
                    }
                });
            })

        });
    }

  });

