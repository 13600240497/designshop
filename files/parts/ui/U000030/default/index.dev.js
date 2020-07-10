+(function () {
    // 用语判断是否执行 scroll 函数
    let scrollLocked = false;
    var navigateTarget = $('#nav-m-ul');
    var scrollFlag = 0;
    if(window.location.hash.split('#').length > 1){
        scrollFlag = 1;
        var pageTo = window.location.hash.split('#')[1].split('=')[1];
        // 滑动目标对象
        var pageToTarget = $('.geshop-component-box[data-id=' + pageTo + ']');
        var pageToTargetTop = 0;
        if (pageToTarget.length > 0) {
            pageToTargetTop = pageToTarget.offset().top + window.screen.height / 2;
            $('html, body').animate({
                scrollTop: pageToTargetTop
            }, 400, function(){
                scrollFlag = 0;
                navigateTarget.parent().addClass('component-m-nav-fixed');
                $('.component-m-nav-item[data-id='+ pageTo +']').addClass('m-current');
            });
        }
    }

    if (!$.fn.swiper3 && $LAB) {
        var staticDomain = GESHOP_STATIC;
        loadCss(staticDomain + '/resources/javascripts/library/swiper/swiper.min.css')
        $LAB.script(staticDomain + '/resources/javascripts/library/swiper/swiper.3.4.spec.min.js').wait(function () {
            startNavCheck()
        })
    } else {
        startNavCheck()
    }

    function startNavigation () {
        var mySwiper = new Swiper3('#js_topNav', {
            freeMode: false,
            slidesPerView: 'auto'
            //slideToClickedSlide:true
        });

        mySwiper.setWrapperTransition(100);


        var closestTarget = navigateTarget.closest('.component-m-nav');

        var timer = null;
        var totalWidth = 0;
        var isNeedTranslate = false;

        var swiperWidth = mySwiper.container[0].clientWidth;
        var maxTranslate = mySwiper.maxTranslate();
        var maxWidth = -maxTranslate + swiperWidth / 2


        navigateTarget.find('li').each(function () {
            totalWidth += $(this).outerWidth();
        });

        isNeedTranslate = totalWidth - $(window).width();

        $(window).scroll(function () {
            // 用于判断是否固定导航
            let nav_is_fixed = $(window).scrollTop() > navigateTarget.closest('.geshop-component-box').offset().top;

            // 点击事件会触发 scroll 函数，所以加个判断阻止她
            if (scrollLocked === true) return false;

            if (scrollFlag == 0) {
                // 处理固定的 class
                nav_is_fixed ? closestTarget.addClass('component-m-nav-fixed') : closestTarget.removeClass('component-m-nav-fixed');
            }

            // 处理 pageTo 参数 / 高亮对应的标题
            var scrollTopBefore = $(window).scrollTop();
            clearTimeout(timer);
            timer = setTimeout(function () {
                var scrollTopAfter = $(window).scrollTop();
                if (scrollTopBefore == scrollTopAfter) {

                    var navigations = navigateTarget.find('li');
                    var length = navigations.length;

                    // 记录命中的 data-id
                    var checked_data_id = -1;

                    for (var index = 0; index < length; index = index + 1) {
                        var target = $(navigations.get(index));
                        var navigatedId = target.data('id');
                        var titleTarget = $('.geshop-component-box[data-id=' + navigatedId + ']');

                        if (scrollTopAfter > parseInt(titleTarget.offset().top) - parseInt(navigateTarget.height())) {
                            target.addClass('m-current');
                            target.siblings().removeClass('m-current');
                            var slide = target;
                            var slideLeft = slide.position().left;
                            var slideWidth = slide.outerWidth(true);
                            var slideCenter = slideLeft + slideWidth / 2;

                            // var swiperWidth = mySwiper.container[0].clientWidth;
                            // var maxTranslate = mySwiper.maxTranslate();
                            // var maxWidth = -maxTranslate + swiperWidth / 2
                            
                            // 被点击slide的中心点
                            mySwiper.setWrapperTransition(300);
                            if (slideCenter < swiperWidth / 2) {
                                mySwiper.setWrapperTranslate(0)
                            } else if (slideCenter > maxWidth) {
                                mySwiper.setWrapperTranslate(maxTranslate);
                            } else {
                                var nowTlanslate = slideCenter - swiperWidth / 2;
                                mySwiper.setWrapperTranslate(-nowTlanslate);
                            }

                            // 更新命中的 data-id
                            checked_data_id = target.data('id');
                        } else {
                            target.removeClass('m-current');
                        }
                    }
                }
            }, 50);
        });

        navigateTarget.off('click');
        navigateTarget.on('click', 'li', function () {
            var target = $(this);
            var navigatedId = target.data('id');
            // 更改 class
            target.addClass('m-current').siblings().removeClass('m-current');
            // 滚动到对应的地方
            var top = $('.geshop-component-box[data-id=' + navigatedId + ']').offset().top - navigateTarget.height();
            scrollLocked = true;
            $(window).scrollTop(top);
            // swiper滚动效果追价
            swiperScrollCenter(target, mySwiper);
            // 清除 onSroll 的锁
            setTimeout(function() {
                scrollLocked = false;
            }, 100);
        });
    }

    function startNavCheck () {
        var is_edit = $('#nav-m-ul').data('isedit');
        if (!1 == is_edit) {
            var imgdefereds = [];
            $('img').each(function () {
                var dfd = $.Deferred();
                $(this).bind('load', function () {
                    dfd.resolve();
                }).bind('error', function () {
                });
                if (this.complete) setTimeout(function () {
                    dfd.resolve();
                }, 1000);
                imgdefereds.push(dfd);
            });
            $.when.apply(null, imgdefereds).done(function () {
                startNavigation();
            });
        }
    }

    function loadCss (href) {
        var link = document.createElement("link");
        link.setAttribute("rel", "stylesheet");
        link.setAttribute("type", "text/css");
        link.setAttribute("href", href);
        document.getElementsByTagName("head")[0].appendChild(link);
    }

    /**
     *
     * @param {jquery Object} target swpier滚动到对应节点的视窗
     */
    function swiperScrollCenter (target, mySwiper) {
        var slide = target;
        var slideLeft = slide.position().left;
        var slideWidth = slide.outerWidth(true);
        var slideCenter = slideLeft + slideWidth / 2;
        var swiperWidth = mySwiper.container[0].clientWidth;
        var maxTranslate = mySwiper.maxTranslate();
        var maxWidth = -maxTranslate + swiperWidth / 2;
        // 被点击slide的中心点
        mySwiper.setWrapperTransition(300);
        if (slideCenter < swiperWidth / 2) {
            mySwiper.setWrapperTranslate(0)
        } else if (slideCenter > maxWidth) {
            mySwiper.setWrapperTranslate(maxTranslate);
        } else {
            var nowTlanslate = slideCenter - swiperWidth / 2;
            mySwiper.setWrapperTranslate(-nowTlanslate);
        }
    }
})();
