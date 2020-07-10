$(function () {
    var utils = {
        // 是否是移动设备
        isMobile: function () {
            if (navigator.userAgent.match(/Android/i)
                || navigator.userAgent.match(/webOS/i)
                || navigator.userAgent.match(/iPhone/i)
                || navigator.userAgent.match(/iPad/i)
                || navigator.userAgent.match(/iPod/i)
                || navigator.userAgent.match(/BlackBerry/i)
                || navigator.userAgent.match(/Windows Phone/i)
            ) {
                return true;
            } else {
                return false;
            }
        },

        debounce: function (fn, wait, options) {
            wait = wait || 0;
            var timerId;

            function debounced() {
                if (timerId) {
                    clearTimeout(timerId);
                    timerId = null;
                }

                var args = Array.prototype.slice.call(arguments);
                timerId = setTimeout(function () {
                    fn.apply(options.context, args.concat(options));
                }, wait);
            }

            return debounced;
        },

        throttle: function (fn, wait, options) {
            wait = wait || 0;
            var timerId, lastTime = 0;

            function throttled() {
                var currentTime = new Date().getTime();
                var args = Array.prototype.slice.call(arguments);
                if (currentTime >= lastTime + wait) {
                    fn.apply(options.context, args.concat(options));
                    lastTime = currentTime;
                } else {
                    if (timerId) {
                        clearTimeout(timerId);
                        timerId = null;
                    }
                    timerId = setTimeout(function () {
                        fn.apply(options.context, args.concat(options));
                    }, wait);
                }
            }

            return throttled;
        },

        loadCss: function (href) {
            var link = document.createElement("link");
            link.setAttribute("rel", "stylesheet");
            link.setAttribute("type", "text/css");
            link.setAttribute("href", href);
            document.getElementsByTagName("head")[0].appendChild(link);
        }
    };

    function DlNav(component) {
        var self = this;
        this.isShow = false;
        this.type = this.getType();
        this.$component = $(component);
        this.isEdit = this.$component.hasClass('is-edit');
        // console.log('isEdit', this.isEdit);
        // 装修的时候看一下样式就可以了，不需要有具体的功能
        if (this.isEdit) return;

        // 事件后缀，方便区分
        this.EVENT_SUFFIX = DlNav.EVENT_SUFFIX + (DlNav.SEED++);

        this.$container = this.$component.find('.nav-container');
        this.$navList = this.$component.find('.nav-item-text');
        this.pageSectionMap = {};
        this.$navList.each(function () {
            var $nav = $(this);
            var floorId = $nav.data('floorId');
            self.pageSectionMap[floorId] = $('.geshop-component-box[data-id=' + floorId + ']');
        });

        if (!utils.isMobile()) {
            this.$component.addClass('is-not-mobile'); // 为了去除移动设备上点击会触发hover效果
        }

        if (!$.fn.swiper3 && $LAB) {
            this.swiper = null;
            // 使用LAB，避免js库重复加载
            $LAB.script(GESHOP_STATIC + '/resources/javascripts/library/swiper/swiper.3.4.spec.min.js').wait(function () {
                self.init();
            });
        } else {
            self.init();
        }
    }

    DlNav.SEED = 1;
    DlNav.EVENT_SUFFIX = 'dl_nav';
    DlNav.TYPE_BREAK_POINT = 1366; // 垂直导航和水平导航的分界点
    DlNav.TYPE_H = 'horizontal'; // 水平导航
    DlNav.TYPE_V = 'vertical'; // 垂直导航

    DlNav.prototype = {
        constructor: DlNav,
        init: function () {
            this.initShare();
            this.initSwiper();
            this.bindEvents();
            // $(window).triggerHandler('scroll');
        },
        initShare() {
            var script = document.createElement('script');
            script.src = '//s7.addthis.com/js/300/addthis_widget.js#pubid=ra-5cc04961c58fe781';
            document.getElementsByTagName('head')[0].appendChild(script);
        },

        isNeedSwiper: function () {
            if (this.type === DlNav.TYPE_V) return false;

            var originalDisplay = this.$container.css('display');
            this.$container.css({ display: 'block', visibility: 'hidden' });
            var containerWidth = this.$container.width();
            var contentWicth = this.$container.find('.nav-list').width();
            this.$container.css({ display: originalDisplay, visibility: 'visible' });
            if (contentWicth <= containerWidth) return false;

            return true;
        },

        initSwiper: function () {
            if (this.isNeedSwiper() && !this.swiper) {
                // console.log('init swiper...');
                var originalDisplay = this.$container.css('display');
                // 隐藏中的元素new swiper()，有问题
                this.$container.css({ display: 'block', visibility: 'hidden' });
                this.swiper = new Swiper3(this.$container[0], {
                    wrapperClass: 'nav-list',
                    slideClass: 'nav-item',
                    slidesPerView: 'auto',
                    freeMode: true
                });
                this.$container.css({ display: originalDisplay, visibility: 'visible' });
            }
        },

        removeSwiper: function () {
            if (this.swiper) {
                // console.log('removeSwiper...');
                var originalDisplay = this.$container.css('display');
                var swiper = this.swiper;
                this.swiper = null;
                swiper.destroy(true, true);
                this.$container.css({ display: originalDisplay });
            }
        },

        getType: function () {
            // 记得要把滚动条算上
            return $(window).outerWidth(true) >= DlNav.TYPE_BREAK_POINT ? DlNav.TYPE_V : DlNav.TYPE_H;
        },

        activeNav: function ($nav) {
            $nav.addClass('active')
                .parent().siblings('.nav-item').find('.nav-item-text').removeClass('active');

            if (this.swiper) {
                var containerWidth = this.$container.width();
                var centerValue = containerWidth / 2;
                var wrapWidth = this.$container.find('.nav-list').width();
                var minValue = centerValue;
                var maxValue = wrapWidth - centerValue;
                var navWidth = $nav.outerWidth();
                var offsetLeft = $nav.parent().position().left + navWidth / 2;
                if (offsetLeft <= minValue) {
                    this.swiper.setWrapperTranslate(0);
                    this.swiper.setWrapperTransition(300);
                } else if (offsetLeft > minValue && offsetLeft < maxValue) {
                    this.swiper.setWrapperTranslate(-offsetLeft + centerValue);
                    this.swiper.setWrapperTransition(300);
                } else {
                    this.swiper.setWrapperTranslate(-wrapWidth + containerWidth);
                    this.swiper.setWrapperTransition(300);
                }
            }
        },

        scrollToElement: function ($element) {
            var self = this;
            var $window = $(window);
            var top = $element.offset().top;
            if (this.type === DlNav.TYPE_H) {
                top -= this.$container.outerHeight();
            } else {
                top -= this.$container.parents().find('.header-container.js_header').height();
            }
            $window.off('scroll.' + this.EVENT_SUFFIX);
            $('html, body').stop(true, true).animate({ scrollTop: top }, 100, function () {
                $window.on('scroll.' + self.EVENT_SUFFIX, self.scrollHandler);
            });
        },

        resizeFn: function (evt) {
            // console.log('resize...');
            var context = this;
            var isNeedSwiper = context.isNeedSwiper();
            context.type = context.getType();
            if (isNeedSwiper && !this.swiper) {
                this.initSwiper();
                $(window).triggerHandler('scroll');
            } else if (!isNeedSwiper && this.swiper) {
                this.removeSwiper();
                $(window).triggerHandler('scroll');
            }
        },

        scrollFn: function () {
            // console.log('scroll...');
            var context = this;
            var $window = $(window);
            var scrollTop = $window.scrollTop();
            var windowHeightHalf = $window.height() / 2;
            var $nav = null;
            context.$navList.each(function () {
                var $currntNav = $(this);
                var floorId = $currntNav.data('floorId');
                var $currentPageSection = context.pageSectionMap[floorId];
                if (scrollTop + windowHeightHalf >= $currentPageSection.offset().top) {
                    $nav = $currntNav;
                } else {
                    return false; // 结束循环
                }
            });

            if ($nav !== null && scrollTop > 100) {
                if (!this.isShow) {
                    // console.log('show...');
                    this.isShow = true;
                    this.$container.stop(true, true).fadeIn();
                }
                context.activeNav($nav);
            } else if (this.isShow) {
                // console.log('hidden...');
                this.isShow = false;
                this.$container.stop(true, true).fadeOut();
            }
        },

        clickHandler: function (evt) {
            var context = evt.data.context;
            var $nav = $(evt.target);
            var floorId = $nav.data('floorId');
            var $pageSection = context.pageSectionMap[floorId];
            if (!$nav.hasClass('active')) {
                context.activeNav($nav);
                context.scrollToElement($pageSection);
            }
        },

        bindEvents: function () {
            var that = this;
            var $window = $(window);
            this.$component.on('click.' + this.EVENT_SUFFIX, '.nav-item-text', { context: this }, this.clickHandler);

            this.scrollHandler = utils.throttle(this.scrollFn, 100, { context: this });
            $window.on('scroll.' + this.EVENT_SUFFIX, this.scrollHandler);

            this.resizeHandler = utils.debounce(this.resizeFn, 300, { context: this });
            $window.on('resize.' + this.EVENT_SUFFIX, { context: this }, this.resizeHandler);

            // 右侧分享小按钮
            this.$component.on('click','.share-btn',function(){
                if(GESHOP_PLATFORM == 'web'){
                    var options = {
                        content: $('.layer-share'),
                        modalWidth: '90%',
                        className: 'geshop-U000201-modal2-share',
                    }
                    typeof GEShopSiteCommon !== 'undefined' && GEShopSiteCommon.dialog.custom(options);
                }else if(GESHOP_PLATFORM == 'app'){
                    var shareTitle =  that.$component.find('input[name=share_data_title]').val();
                    var shareDesc = that.$component.find('input[name=share_data_desc]').val();
                    var shareLink =  that.$component.find('input[name=share_data_link]').val();
                    var shareImg =  that.$component.find('input[name=share_data_img]').val();
                    window.location.href = "webAction://sharing?shareUrl="+ shareLink +"&shareTitle="+ shareTitle +"&shareContent="+ shareDesc +"&imageUrl=" + shareImg ;
                }
            });


            $('body').on('click','.U000201-modal2-cancel-btn',function(){
                typeof GEShopSiteCommon !== 'undefined' && GEShopSiteCommon.dialog.unblock();
            });
        },

        removeEvents: function () {
            this.$component.off(this.EVENT_SUFFIX);
            this.scrollHandler = null;
            this.resizeHandler = null;
        },

        destroy: function () {
            this.removeSwiper();
            this.removeEvents();

            this.type = null;
            this.$component = null;
            this.$container = null;
            this.$navList = null;
            this.pageSectionMap = null;
        }
    }

    $('.geshop-U000201-modal2').each(function () {
        new DlNav(this);
    });
});
