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

    // 切换 Tab
    function setTab(index) {
        inlineTabs.removeClass('active').eq(index).addClass('active');
        innerTabs.removeClass('active').eq(index).addClass('active');
        goodsSub.hide().eq(index).show();
    }
    
    var component = $('.egeshop-component-box[data-key=U000077][data-theme="zaful"]').eq(0);
    var inlineTabs = component.find('.js_geshopRecommendCategoryInline li');
    var innerTabs = component.find('.js_geshopRecommendCategoryAll li');
    var goodsSub = component.find('.js_geshopRecommendGoods .egeshop-recommend-goods-sub');

    // 初始化
    function init() {
        // 
        setTab(0);
        // 
        commonLazy(component.find('.js-geshopImg-lazyload'));
        // 
        if (typeof FUN == 'object') {
            FUN.currency.change_houbi();
        }
        // set Tab width
        var ul_width = 0;
        inlineTabs.each(function(index, el){
            ul_width = ul_width + $(this).width() + parseInt($(this).css('margin-left')) + 5; });
        inlineTabs.parent().width(ul_width);
    }
    init();
    

    $('body')
        .on('click', '.js_geshopShowCategory', function() {
            var target = $(this);
            var className = 'egeshop-recommend-category-arrow-active';

            if (target.hasClass(className)) {
                target.removeClass(className);
                $('.js_geshopRecommendCategoryAll').slideUp();
            } else {
                target.addClass(className);
                $('.js_geshopRecommendCategoryAll').slideDown();
            }
        })
        .on('click', '.js_geshopRecommendCategoryInline li', function() {
            var index = $(this).attr('data-key');
            setTab(index);
        })
        .on('click', '.js_geshopRecommendCategoryAll li', function() {
            var index = $(this).attr('data-key');
            setTab(index);
        });
        
        $(document).scroll(function() {
            var offsetTop = $('[data-id][data-key=U000077]').offset().top;
            var scrollTop = $(document).scrollTop();

            if (scrollTop > offsetTop && scrollTop < offsetTop + $('[data-id][data-key=U000077]').height()) {
                $('.headroom--not-top,.headroom--not').hide();
                $('.egeshop-recommend-category-block').css({
                    position: 'fixed',
                    right: 0,
                    top: 0,
                    left: 0,
                    zIndex: 1
                });
            } else {
                if (scrollTop < offsetTop - 60 || scrollTop > (offsetTop + $('[data-id][data-key=U000077]').height())) {
                    $('.headroom--not-top,.headroom--not').show();
                }
                $('.egeshop-recommend-category-block').css({
                    position: 'relative'
                });
            }
        });
})();