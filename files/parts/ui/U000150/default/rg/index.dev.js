$(function() {
    var staticDomain = $('[data-static-domain]:eq(0)').attr('data-static-domain') ? $('[data-static-domain]:eq(0)').attr('data-static-domain') : "";

    $('.rgindex-pc-tab').each(function(i, element) {
        var defaultOptions = {
            col: 5, // 默认显示5个
            colTotal: 1,    // col总数
            colLeftTotal: 1 // col 剩余数
        }
        var $tab_index = 0;
        $(element).data('scrollIndex', 0);

        var navCount = $(element).find('.tab-swipe .nav-container ul li').length;

        if(!navCount){return false;}
        defaultOptions.colTotal = navCount;
        defaultOptions.colLeftTotal = navCount - defaultOptions.col;
        $(element).data('colTotal',defaultOptions.colTotal);
        $(element).data('colLeftTotal',defaultOptions.colLeftTotal);

        if (defaultOptions.colTotal > defaultOptions.col) {
            $(element).find('.tab-swipe .right-btn').show();
        }
        var navWidth = $(element).find('.tab-swipe .nav-container ul li:first').outerWidth(true);
        $(element).find('.tab-swipe .nav-container ul').css('width', navCount * navWidth + 'px');
        $(element).find('.tab-swipe .nav-container ul li:first').addClass('current');
        $(element).find('.tab-swipe .goods-list-swipe .goods-scroll:first').show();
        var tab_item = $(element).find('.tab-swipe .nav-container li');
        swiperFn(element);

        tab_item.on('click', function() {
            $tab_index = $(this).index()
            $(this).addClass('current').siblings('').removeClass('current');
            $(element).find('.tab-swipe .goods-list-swipe .goods-scroll:eq(' + $tab_index + ')').show().siblings('.goods-scroll').hide();
            if ($.fn.lazyload) {
                $(element).find('.tab-swipe .goods-list-swipe ul:eq(' + $tab_index + ') img.lazyload').lazyload({
                    threshold: 100,
                    failure_limit: 20,
                    skip_invisible: false
                })
            } else {
                window.GS_GOODS_LAZY_FN('.lazyload');
            }
            // window.GS_GOODS_LAZY_FN();
            swiperFn(element);
        })


        $(element).find('.tab-swipe .nav-container .right-btn').on('click', function() {
            scrollFn('right', element);
        })

        $(element).find('.tab-swipe .nav-container .left-btn').on('click', function() {
            scrollFn('left', element);
        })

    });



    /* 导航滚动 */
    function scrollFn(direction, element) {
        var scrollIndex = $(element).data('scrollIndex');
        var $ulWidth = $(element).find('.tab-swipe .nav-container ul').width();
        var $navConWidth = $(element).find('.tab-swipe .nav-container').width();
        var $liWidth = $(element).find('.tab-swipe .nav-container ul li:first').width();
        var $scrollCount = Math.floor($ulWidth / $liWidth) - 5;
        if (direction === 'right') {

            if (scrollIndex < $scrollCount) {
                scrollIndex++;
                $(element).find('.tab-swipe .nav-container ul').css('transform', 'translateX(' + scrollIndex * -$liWidth + 'px)');
            }

        } else if (direction === 'left') {
            if (scrollIndex > 0) {
                $(element).find('.tab-swipe .nav-container ul').css('transform', 'translateX(' + (scrollIndex - 1) * -$liWidth + 'px)');
                scrollIndex--;
            }
        }
        $(element).data('scrollIndex', scrollIndex);
        if(GESHOP_SITECODE.indexOf('rg') >= 0) {
            verifyBtnStatu(element,direction)
        }
    }

    /**
     * 检测是否显示轮播按钮
     */
    function verifyBtnStatu(element,direction){
        var data = $(element).data();
        var scrollIndex = parseInt(data.scrollIndex);
        var colLeftTotal = parseInt(data.colLeftTotal);

        var $rightBtn = $(element).find('.tab-swipe .right-btn');
        var $leftBtn = $(element).find('.tab-swipe .left-btn');
        if(colLeftTotal === scrollIndex || colLeftTotal < scrollIndex ){
            $rightBtn.hide();
        }else{
            $rightBtn.show();
        }

        if(scrollIndex > 0){
            $leftBtn.show();
        }else{
            $leftBtn.hide();
        }
    }
    function swiperFn(element) {
        var length = $(element).find('.tab-swipe .goods-scroll:visible ul li').length;
        loadCss(staticDomain + '/resources/javascripts/library/swiper/swiper.min.css');
        $LAB.script(staticDomain + '/resources/javascripts/library/swiper/swiper.3.4.spec.min.js').wait(function() {
            var swiperContainer = $(element).find('.tab-swipe .goods-scroll:visible');
            var prevButtonElement = $(element).find('.tab-swipe .goods-list-swipe .goods-pre-btn');
            var nextButtonElement = $(element).find('.tab-swipe .goods-list-swipe .goods-next-btn');
            if (length <= 4) {
                $(element).find('.tab-swipe .btn').hide();
            } else {
                $(element).find('.tab-swipe .btn').show();
                var swiper2 = new Swiper3(swiperContainer, {
                    slidesPerView: 4,
                    slidesPerGroup: 4,
                    spaceBetween : 16,
                    nextButton: nextButtonElement,
                    prevButton: prevButtonElement,
                    loop: true,
                    lazyLoading: true,
                    onLazyImageReady: function(swiper, slide, image) {
                        gbLogsss.getsku($(image));
                        gbLogsss.sendsku();
                    }
                });
            }

        });
    }

});
