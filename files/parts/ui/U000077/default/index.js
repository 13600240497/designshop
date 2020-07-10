(function() {
    function commonLazy (element) {
       
        if (typeof GESHOP_UTIL == "object") {
            GESHOP_UTIL.goodsLazy($(element))
        }else{
            $.each($(element),function(i,v){
                var $this = $(v);
                var originalUrl = $this.data('original');
                originalUrl.length > 0 && $this.attr('src',$this.data('original'))
            })
        }

    }
    function getRecommendCategoryGoods(tid, page, callback) {
        var $loadingHTML = $(
            '<div class="geshop-loading">' +
            '<div class="geshop-loading-img"></div>' +
            '</div>');
        var $currenGeshopRecommendGoodsSubRecommendLink = $('.geshop-recommend-goods-sub[data-cat-id=' + tid + ']').find('.geshop-recommend-link');

        $currenGeshopRecommendGoodsSubRecommendLink.hide().before($loadingHTML);


        $.ajax({
            url: 'https://m.rosewholesale.com/geshop/interface/trends-goods-list/' + tid + '/?page=' + page,
            jsonp: 'callback',
            dataType: 'jsonp',
            success: function(res) {
                var component = $('.geshop-component-box[data-key=U000077]').get(0);
                var pageId = component.getAttribute('data-page-id');
                var pageInstanceId = component.getAttribute('data-page-instance-id');
                var compKey = component.getAttribute('data-comp-key');
                var uiIndex = component.getAttribute('data-ui-index');
                var layoutIndex = component.getAttribute('data-layout-index');

                $loadingHTML.remove();
                $currenGeshopRecommendGoodsSubRecommendLink.show();

                if (res.code == 0) {
                    var html = '',$html='';
                    var length = res.data.length;

                    if ((length % 2 == 0) && page <= 4) {
                        for (var index = 0; index < length; index = index + 1) {
                            html += '' +
                                '<div class="geshop-recommend-item">' +
                                '<a href="' + res.data[index].link + '" class="geshop-recommend-picture logsss_event"' +
                                    'data-logsss-event-value="{ \'pm\':\'mr\',\'p\':\'p-' + pageId + '\',\'ubcta\':{\'cpID\':\'' + pageInstanceId + '\',\'cpnum\':\'' + compKey + '\',\'cplocation\':\'' + uiIndex + '\',\'sku\':\'' + res.data[index].sku + '\',\'cporder\':\'' + layoutIndex + '\',\'rank\':\'' + index + '\'},\'skuinfo\':{\'sku\':\'' + res.data[index].sku + '\',\'pam\':\'0\',\'pc\':\'' + res.data[index].category_id + '\',\'k\':\'' + res.data[index].warehouse + '\'} }"' +
                                '>' +
                                '<img alt="" class="js-geshopImg-lazyload" src="data:image/gif;base64,R0lGODlhAQABAIAAAPHx8QAAACwAAAAAAQABAAACAkQBADs=" data-original="' + res.data[index].picture + '"' +
                                    'data-logsss-browser-value="{ \'pm\':\'mr\',\'p\':\'p-' + pageId + '\',\'bv\':{\'cpID\':\'' + pageInstanceId + '\',\'cpnum\':\'' + compKey + '\',\'cplocation\':\'' + uiIndex + '\',\'sku\':\'' + res.data[index].sku + '\',\'cporder\':\'' + layoutIndex + '\',\'rank\':\'' + index + '\'} }"' +
                                '>' +
                                '</a>' +
                                '<p class="geshop-recommend-price">' +
                                '<span class="geshop-recommend-price-sale">' +
                                '<strong class="bizhong">USD</strong><span class="bz_icon"></span>' +
                                '<span class="my_shop_price" data-orgp="' + res.data[index].shop_price + '">' + '$' + res.data[index].shop_price + '</span></span>' +
                                '<span class="geshop-recommend-price-market">' +
                                '<strong class="bizhong">USD</strong><span class="bz_icon"></span>' +
                                '<span class="my_shop_price js_market_wrap" data-orgp="' + res.data[index].market_price + '">' + '$' + res.data[index].market_price + '</span></span>' +
                                '</p>' +
                                '</div>';
                        }
                        $html = $(html);
                        $currenGeshopRecommendGoodsSubRecommendLink.before($html);
                        commonLazy($html.find('.js-geshopImg-lazyload'));
                        $html.find('.js-geshopImg-lazyload').each(function () {
                            if (typeof gbLogsss != "undefined") {
                                gbLogsss.getsku($(this));
                                gbLogsss.sendsku();
                            }
                        });
                       
                        if (Number(page) == 4) {
                            $currenGeshopRecommendGoodsSubRecommendLink.remove();
                        } else {
                            $('.js_geshopRecommendBlock [data-cat-id=' + tid + ']').attr('data-page', Number(page) + 1);
                        }

                        if (typeof FUN == 'object') {
                            FUN.currency.change_houbi();
                        }
                    } else {
                        $currenGeshopRecommendGoodsSubRecommendLink.remove();
                    }
                }

                callback && typeof callback == 'function' && callback(tid);
            }
        })
    }

    $.ajax({
        url: 'https://m.rosewholesale.com/geshop/interface/trends/',
        jsonp: 'callback',
        dataType: 'jsonp',
        success: function(res) {
            if (res.code == 0) {
                var length = res.data.length;
                var inlineHTML = '';
                var blockHTML = '';
                var index;
                var component = $('.geshop-component-box[data-key=U000077]').get(0);
                $(component).find('.js_geshopRecommendBlock').show();
                for (index = 0; index < length; index = index + 1) {
                    inlineHTML += '' +
                        '<li' + (index == 0 ? ' class="geshop-recommend-category-inline-active"' : '') + '>' +
                        '<a href="javascript:;" data-cat-id="' + res.data[index].id + '" data-page="1"' +
                        (index == 0 ? (' style="color: ' + component.getAttribute('data-fontColorActive') + ';"') : '') + '>' + res.data[index].name +
                        '</a>' +
                        '</li>';
                    blockHTML += '' +
                        '<div class="geshop-recommend-goods-sub" data-cat-id="' + res.data[index].id + '" style="display: ' + (index == 0 ? 'block' : 'none') + ';">' +
                        '<div class="geshop-recommend-link">' +
                        '<a href="javascript:;" class="js_geshopRecommendSeeMore" style="' +
                        'color: ' + component.getAttribute('data-fontColorBtn') + '; ' +
                        'background-color: ' + component.getAttribute('data-bgColorBtn') + '; ' +
                        'border: 1px solid ' + component.getAttribute('data-borderColorBtn') + '; ' +
                        '">SEE MORE</a>' +
                        '</div>' +
                        '</div>';
                }

                $('.js_geshopRecommendCategoryInline ul').html(inlineHTML);
                $('.js_geshopRecommendGoods').html(blockHTML);

                var width = 0;

                $('.js_geshopRecommendCategoryInline li').each(function() {
                    width += $(this).width();
                });

                $('.js_geshopRecommendCategoryInline ul').css('width', width + 1);

                var allHTML = '';

                for (index = 0; index < length; index = index + 1) {
                    allHTML += '' +
                        '<li' + (index == 0 ? ' class="geshop-recommend-category-all-active"' : '') + '>' +
                        '<a href="javascript:;" data-cat-id="' + res.data[index].id + '" data-page="1"' +
                        (index == 0 ? ('style="color: ' + component.getAttribute('data-fontColorActive') + '; border: 1px solid ' + component.getAttribute('data-fontColorActive') + ';"') : '') + '>' + res.data[index].name +
                        '</a>' +
                        '</li>';
                }

                $('.js_geshopRecommendCategoryAll').html(allHTML);
                getRecommendCategoryGoods(res.data[0].id, 1);
            }
        }
    });

    $('body')
        .on('click', '.js_geshopShowCategory', function() {
            var target = $(this);

            if (target.hasClass('geshop-recommend-category-arrow-active')) {
                target.removeClass('geshop-recommend-category-arrow-active');
                $(this).closest('.geshop-recommend-category-inline')
                    .next('.geshop-recommend-category-all').slideUp();
            } else {
                target.addClass('geshop-recommend-category-arrow-active');
                $(this).closest('.geshop-recommend-category-inline')
                    .next('.geshop-recommend-category-all').slideDown();
            }
        })
        .on('click', '.js_geshopRecommendCategoryInline li a', function() {

            var target = $(this);
            var catId = target.data('catId');
            var page = target.data('page');
            var component = $('.geshop-component-box[data-key=U000077]').get(0);


            target.parent().addClass('geshop-recommend-category-inline-active');
            target.css({
                color: component.getAttribute('data-fontColorActive')
            });
            target.parent().siblings().removeClass('geshop-recommend-category-inline-active');
            target.parent().siblings().find('a').css({
                color: '#000000'
            });

            var total = $('.js_geshopRecommendCategoryInline li').length;
            var boxWidth = $('.js_geshopRecommendCategoryInline').width();
            var offsetLeft = 0;
            var offsetRight = 0;

            $('.geshop-recommend-category-inline-active').prevAll().each(function() {
                offsetLeft += $(this).width();
            });

            if (catId < total) {
                $('.geshop-recommend-category-inline-active').nextAll().each(function() {
                    offsetRight += $(this).width();
                });
            }

            if (offsetLeft < (boxWidth / 2)) {
                $('.js_geshopRecommendCategoryInline').animate({
                    scrollLeft: 0
                }, 500);
            }

            if (offsetRight < (boxWidth / 2)) {
                $('.js_geshopRecommendCategoryInline').animate({
                    scrollLeft: offsetLeft + offsetRight + target.parent().width() + $('.js_geshopShowCategory').width() - boxWidth
                }, 500);
            }

            if (!(offsetLeft < (boxWidth / 2) || offsetRight < (boxWidth / 2))) {
                $('.js_geshopRecommendCategoryInline').animate({
                    scrollLeft: Math.abs(offsetLeft - (boxWidth / 2))
                }, 500);
            }



            var currentCat = $('.geshop-recommend-goods-sub[data-cat-id=' + catId + ']');

            currentCat.height($('.geshop-recommend-goods-sub:visible').height());
            $('.geshop-recommend-goods-sub').not('.geshop-recommend-goods-sub[data-cat-id=' + catId + ']').hide();
            currentCat.show();
            $(document).scrollTop($('.js_geshopRecommendBlock').offset().top);

            if (currentCat.find('.geshop-recommend-item').length == 0) {

                getRecommendCategoryGoods(catId, page, function(tid) {



                    $('.geshop-recommend-goods-sub[data-cat-id=' + tid + ']').css('height', 'auto')
                });
            } else {
                $(document).scrollTop($('.js_geshopRecommendBlock').offset().top);

                $('.geshop-recommend-goods-sub[data-cat-id=' + catId + ']').css('height', 'auto');
            }



            $('.js_geshopRecommendCategoryAll li').removeClass('geshop-recommend-category-all-active');
            $('.js_geshopRecommendCategoryAll li a').css({
                color: '#000000',
                border: '1px solid #000000',
            });
            $('.js_geshopRecommendCategoryAll [data-cat-id=' + catId + ']').parent().addClass('geshop-recommend-category-all-active');
            $('.js_geshopRecommendCategoryAll [data-cat-id=' + catId + ']').css({
                color: component.getAttribute('data-fontColorActive'),
                border: '1px solid ' + component.getAttribute('data-fontColorActive')
            });

            if ($('.geshop-recommend-category-arrow').hasClass('geshop-recommend-category-arrow-active')) {
                $('.geshop-recommend-category-arrow').trigger('click');
            }

            //var headerHeight= $('.headroom--not-top.slide--up').length > 0 ?$('.headroom--not-top.slide--up').eq(0).height():0;



        })
        .on('click', '.js_geshopRecommendCategoryAll a', function() {
            var catId = $(this).data('catId');

            $('.js_geshopRecommendCategoryInline a[data-cat-id=' + catId + ']').trigger('click');
        })
        .on('click', '.js_geshopRecommendSeeMore', function() {
            var currentCat = $('.geshop-recommend-category-inline-active a');
            var catId = currentCat.attr('data-cat-id');
            var page = currentCat.attr('data-page');

            getRecommendCategoryGoods(catId, page);
        });
    $(document).scroll(function() {
        var offsetTop = $('[data-id][data-key=U000077]').offset().top;
        var scrollTop = $(document).scrollTop();


        if (scrollTop > offsetTop && scrollTop < offsetTop + $('[data-id][data-key=U000077]').height()) {

            $('.headroom--not-top,.headroom--not').hide();
            $('.geshop-recommend-category-block').css({
                position: 'fixed',
                top: 0,
                right: 0,
                left: 0,
                zIndex: 10
            });
        } else {
            if (scrollTop < offsetTop - 60 || scrollTop > (offsetTop + $('[data-id][data-key=U000077]').height())) {
                $('.headroom--not-top,.headroom--not').show();
            }

            $('.geshop-recommend-category-block').css({
                position: 'relative'
            });
        }
    });
})();