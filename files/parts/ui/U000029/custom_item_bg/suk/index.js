$(function () {
    var navigateTarget = $('#nav-vertical-ul');
    var navId = navigateTarget.find('li:first').data('id') || 0;
    var closestTarget = navigateTarget.closest('.component-vertical-nav');
    var navigateOffsetTop = $('.geshop-component-box[data-id=' + navId + ']:first').offset().top;
    var timer = null;

    if (navigateTarget.data('isedit') == 0) {
        closestTarget.addClass('component-vertical-nav-online');
    }

    var windowWidth = $(window).width();
    $("#js-backToTop,#backToTop").remove();

    $('.wrap-u000029-custom_item_bgnav-vertical-ul-wrap').width($('.wrap-u000029-custom_item_bgnav-vertical-ul>li').outerWidth());

    var navCtrolFlag = $('.component-vertical-nav-ctronlFlag');

    var resizeTimeout = null;
    $(window).resize(function () {
        resizeTimeout && clearTimeout(resizeTimeout)
        resizeTimeout = setTimeout(function () {
            windowWidth = $(window).width();
            //smallWindowNavShowStyleObject.isShowed = false;
            if (windowWidth < 1700) {
                navCtrolFlag.show();
                smallWindowNavSlideHide(navCtrolFlag);
                if(closestTarget.hasClass('component-show-always')){
                    smallWindowNavShowStyle(navCtrolFlag);
                }
            } else {
                navCtrolFlag.hide();
                smallWindowNavSlideShow(navCtrolFlag);
            }
        }, 1000);
    });


    var smallWindowNavShowStyleObject = {
        isFirst: true,
        isShowed: false,
        closestTargetOffsetValue: 0,
        navCtrolFlagOffsetValue: 0,
        timeFn: null
    }
    /**
     * always show slideInitial
     */
    if(closestTarget.hasClass('component-show-always')){
        slideInitial(navCtrolFlag);
    }

    function slideInitial(navCtrolFlag){
        windowWidth = $(window).width();
        //smallWindowNavShowStyleObject.isShowed = false;
        if(windowWidth < 1700){
            navCtrolFlag.show();
            smallWindowNavSlideHide(navCtrolFlag);
            if(closestTarget.hasClass('component-show-always')){
                smallWindowNavShowStyle(navCtrolFlag);
            }else{
                navCtrolFlag.hide();
                smallWindowNavSlideShow(navCtrolFlag);}
        }
    }

    function smallWindowNavShowStyle(navCtrolFlag) {
        if (smallWindowNavShowStyleObject.isFirst) {
            smallWindowNavShowStyleObject.isFirst = false;
            if (!smallWindowNavShowStyleObject.rcodePosition) {
                smallWindowNavShowStyleObject.rcodePosition = true;
                if (navCtrolFlag.data('position') == 'left') {
                    smallWindowNavShowStyleObject.closestTargetOffsetValue = closestTarget.offset().left;
                    smallWindowNavShowStyleObject.navCtrolFlagOffsetValue = navCtrolFlag.offset().left;
                } else if (navCtrolFlag.data('position') == 'right') {
                    smallWindowNavShowStyleObject.closestTargetOffsetValue = $(window).width() - closestTarget.offset().left - closestTarget.outerWidth();
                    smallWindowNavShowStyleObject.navCtrolFlagOffsetValue = $(window).width() - closestTarget.offset().left;
                }
            }

            clearTimeout(smallWindowNavShowStyleObject.timeFn);
            smallWindowNavShowStyleObject.timeFn = setTimeout(function (params) {
                smallWindowNavSlideHide(navCtrolFlag);

            }, 500)

        }
    }

    /**
     * RG  小屏幕隐藏侧边栏
     * 2019.04.26 如果头图宽度大于中间的宽度，定位 [right] 值会有偏差，调整为 [width] + [margin-right], modify by Cullen
    */
    function smallWindowNavSlideHide(navCtrolFlag) {
        closestTarget.css("transition", "all 0.3s");
        smallWindowNavShowStyleObject.isShowed = false;
        /** 左侧边栏 */
        if (navCtrolFlag.data('position') == 'left') {
            var animateWidth = 0 - smallWindowNavShowStyleObject.navCtrolFlagOffsetValue;
            navCtrolFlag.removeClass('component-vertical-nav-ctronlFlag-narrowLeft').addClass('component-vertical-nav-ctronlFlag-narrowRight');
            closestTarget.css("left", animateWidth);
        /** 右侧边栏 */
        } else if (navCtrolFlag.data('position') == 'right') {
            // 最大宽度
            var navCtrolFlagOffsetValue = smallWindowNavShowStyleObject.navCtrolFlagOffsetValue;
            /** 中间区域的真实宽度，不带 [margin] */
            var realWidth = $('.wrap-u000029-custom_item_bgnav-vertical-ul-wrap').width();
            /** 中间区域的一半[margin]值  */
            var realMargin = (navCtrolFlagOffsetValue - realWidth) / 2;
            /** 计算偏移值  */
            var animateWidth = 0 - navCtrolFlagOffsetValue + realMargin;
            navCtrolFlag.removeClass('component-vertical-nav-ctronlFlag-narrowRight').addClass('component-vertical-nav-ctronlFlag-narrowLeft');
            closestTarget.css("right", animateWidth);
        }
        closestTarget.css("transition", null)
    }
    function smallWindowNavSlideShow(navCtrolFlag) {
        smallWindowNavShowStyleObject.isShowed = true;
        if (navCtrolFlag.data('position') == 'left') {

            closestTarget.css("left", smallWindowNavShowStyleObject.closestTargetOffsetValue + 'px');
        } else if (navCtrolFlag.data('position') == 'right') {
            closestTarget.css("right", smallWindowNavShowStyleObject.closestTargetOffsetValue + 'px');

        }
    }

    navCtrolFlag.on("click", function () {
        if (navCtrolFlag.hasClass('component-vertical-nav-ctronlFlag-narrowLeft')) {
            navCtrolFlag.removeClass('component-vertical-nav-ctronlFlag-narrowLeft').addClass('component-vertical-nav-ctronlFlag-narrowRight');
        } else if (navCtrolFlag.hasClass('component-vertical-nav-ctronlFlag-narrowRight')) {
            navCtrolFlag.removeClass('component-vertical-nav-ctronlFlag-narrowRight').addClass('component-vertical-nav-ctronlFlag-narrowLeft');
        }
        if (smallWindowNavShowStyleObject.isShowed) {
            smallWindowNavSlideHide(navCtrolFlag);
        } else {
            smallWindowNavSlideShow(navCtrolFlag);
        }
    });

    $(window).scroll(function () {
        if ($(window).scrollTop() > navigateOffsetTop) {
            closestTarget.show();
            if (windowWidth < 1700) {
                navCtrolFlag.show();
                smallWindowNavShowStyle(navCtrolFlag);

            } else {
                navCtrolFlag.hide();
                smallWindowNavSlideShow(navCtrolFlag);
            }
        } else {
            closestTarget.hide();
        }

        var scrollTopBefore = $(window).scrollTop();

        clearTimeout(timer);
        timer = setTimeout(function () {
            var scrollTopAfter = $(window).scrollTop();

            if (scrollTopBefore == scrollTopAfter) {
                var navigations = navigateTarget.find('li');
                var length = navigations.length;

                for (var index = 0; index < length; index = index + 1) {
                    var target = $(navigations.get(index));
                    var navigatedId = target.data('id');
                    if (navigatedId) {
                        var titleTarget = $('.geshop-component-box[data-id=' + navigatedId + ']');

                        if (scrollTopAfter > titleTarget.offset().top - titleTarget.height()) {
                            target.addClass('vertical-current');
                            target.siblings().removeClass('vertical-current');
                        }
                    }

                }
            }
        }, 50);
    });

    navigateTarget.on('click', 'li', function () {
        var target = $(this);
        var navigatedId = target.data('id');

        if (target.hasClass('to-top')) {
            navigateTarget.find('li').removeClass('vertical-current');
            $('html, body').animate({
                scrollTop: 0
            }, 500);
        } else {
            if (navigatedId) {
                target.addClass('vertical-current').siblings().removeClass('vertical-current');
                $('html, body').animate({
                    scrollTop: $('.geshop-component-box[data-id=' + navigatedId + ']').offset().top - 60
                }, 500);
            }
        }
    });
});
