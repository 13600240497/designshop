;$(function () {
    var componentFn = (function (my) {

        /**
         * 组件默认信息
         * @type {{lis_width: number}}
         */
        my.options = {
            lis_width: 0
        };
        /**
         * 初始化swiper
         * @param element
         */
        my.initSwiper = function (element) {
            var $boxWrap = $(element);
            new Swiper($boxWrap.find('.swiper-container'), {
                slidesPerView: 'auto',
                slideToClickedSlide: true,
                observer: true,
                observeParents: true,
                navigation: {
                    nextEl: $boxWrap.find('.swiper-button-next'),
                    prevEl: $boxWrap.find('.swiper-button-prev')
                }
            });
        };
        /**
         * 更新商品数据列表
         * 商品tab点击事件
         * @param element
         */
        my.initGoodsData = function (element) {
            $(element).find('.tab-no-swipe .nav-container ul li:first').addClass('current');
            $(element).find('.tab-no-swipe .goods-list ul:first').show();
            // window.GESHOP_UTIL && window.GESHOP_UTIL.goodsLazy($(element).find('.tab-no-swipe .goods-list ul:first').find('img.js-lazyload'));
            window.GS_GOODS_LAZY_FN && window.GS_GOODS_LAZY_FN('img.js-lazyload');
            // 增加居中配置
            $(element).find('.tab-no-swipe .nav-container ul li').each(function (i, item) {
                my.options.lis_width += $(this).width() + 20;
            });
            if (my.options.lis_width <= 1150) {
                $(element).find('.swiper-wrapper').width(my.options.lis_width);
            }

            var tab_item = $(element).find('.tab-no-swipe .nav-container li');
            tab_item.on('click', function () {
                var $tab_index = $(this).index();
                $(this).addClass('current').siblings().removeClass('current');
                $(element).find('.tab-no-swipe .goods-list ul:eq(' + $tab_index + ')').show().siblings().hide();
                // window.GESHOP_UTIL && window.GESHOP_UTIL.goodsLazy($(element).find('.tab-no-swipe .goods-list ul:eq(' + $tab_index + ')').find('img.js-lazyload'));
                window.GS_GOODS_LAZY_FN && window.GS_GOODS_LAZY_FN('img.js-lazyload');
            });
        };
        return my;
    })({});
    // 加载swiper
    loadCss(GESHOP_STATIC + '/resources/javascripts/library/swiper/swiper.4.5.min.css');
    $LAB.script(GESHOP_STATIC + '/resources/javascripts/library/swiper/swiper.4.5.min.js').wait(function () {
        $('[data-gid="U000133"].rgindex-pc-notab').find('.pagination-btn').removeClass('hide');
        $('[data-gid="U000133"].rgindex-pc-notab').each(function (i, element) {
            componentFn.options = { lis_width: 0 };
            componentFn.initGoodsData(element);
            componentFn.initSwiper(element);
        });
    });
});
