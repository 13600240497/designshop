$(function() {
    // 辅助工具行数，使用评率高，应该要放到公用文件的，但是由于只是临时过来帮忙，时间又紧急，不敢大改动 by罗晓辉@20190221
    var utils = {
        debounce: function(fn, wait, options) {
            wait = wait || 0;
            var timerId;

            function debounced() {
                if (timerId) {
                    clearTimeout(timerId);
                    timerId = null;
                }

                var args = Array.prototype.slice.call(arguments);
                timerId = setTimeout(function() { fn.apply(options.context, args.concat(options)); }, wait);
            }
            return debounced;
        },
        throttle: function(fn, wait, options) {
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
                    timerId = setTimeout(function() { fn.apply(options.context, args.concat(options)); }, wait);
                }
            }
            return throttled;
        }
    };

    function Rank($el) {
        this.$el = $el;

        this.EVENT_SUBFIX = this.$el.data('key') + '_' + this.$el.data('id');

        var $nav = this.$el.find('.rank-nav-inner');
        this.$nav = $nav.length > 0 ? $nav : null;
        this.$goodContainer = this.$el.find('.goods-section');
        this.navFixed = this.$el.data('isFixed') === 'y';
        this.isEdit = this.$el.hasClass('is-edit');
        this.goods = this.$el.data('goods');

        this.topGoodsTpl = gs_laytpl(this.$el.find('.rank-top-goods-tpl').html());
        this.otherGoodsTpl = gs_laytpl(this.$el.find('.rank-other-goods-tpl').html());

        if (this.isNeedArrow()) {
            this.showArrow();
        } else {
            this.hideArrow();
        }

        // 装修界面不需要滑动
        if (!this.isEdit) {
            // swipter初始化要在箭头判断的后面执行，否则容器宽度不正确
            this.initSwiper();
        }

        var skus = '';
        if (this.$nav) {
            var index = this.$nav.find('.rank-nav-item').eq(0).data('index');
            skus = this.goods[index - 1] ? this.goods[index - 1].lists : '';
        } else {
            skus = this.goods[0].lists;
        }

        this.fetchGoods(skus);

        // 装修界面不需要注册时间
        if (!this.isEdit) {
            this.addEvents();
        }
    }

    Rank.prototype = {
        constructor: Rank,
        // 滚动处理
        scrollFn: function() {
            var context = this;
            if (context.$nav) {
                var $window = $(window);
                var scrollTop = $window.scrollTop();
                var top = context.$nav.parent().offset().top;
                var bottom = top + context.$goodContainer.outerHeight();

                if (scrollTop > top && scrollTop < bottom) {
                    context.$nav.addClass('is-fixed');
                    context.$nav.parents('.geshop-component-box').addClass('js-geshop-nav-fixed');

                    // 站点导航栏处理
                    if ($('.js_header').length) {
                        $('.js_header').hide();
                    }

                    // 页面中存在水平导航时，隐藏水平导航
                    if ($('div[data-key="U000186"]').length) {
                        $('div[data-key="U000186"]').find('nav').hide();
                    }

                } else {
                    context.$nav.removeClass('is-fixed');
                    context.$nav.parents('.geshop-component-box').removeClass('js-geshop-nav-fixed');

                    // 站点导航栏处理
                    if ($('.js_header').length) {
                        $('.js_header').show();
                    }

                    // 页面中存在水平导航时，显示水平导航
                    if ($('div[data-key="U000186"]').length) {
                        $('div[data-key="U000186"]').find('nav').show();
                    }

                    if (GEShopSiteCommon) {
                        GEShopSiteCommon.jsNavFixed()
                    }
                }
            }
        },
        // 窗口大小变化处理
        resizeFn: function() {
            var context = this;
            if (context.$nav) {
                if (context.isNeedArrow()) {
                    context.showArrow();
                } else {
                    context.hideArrow();
                }
                context.activeNav(context.$nav.find('.active'));
            }
        },
        // 选择一个导航激活处理，需要高亮和居中
        activeNav: function($el) {
            $el.addClass('active').siblings('.rank-nav-item').removeClass('active');

            if (this.swiper) {
                var containerWidth = this.$nav.find('.rank-nav').width();
                var centerValue = containerWidth / 2;
                var wrapWidth = this.$nav.find('.rank-nav-content').width();
                var minValue = centerValue;
                var maxValue = wrapWidth - centerValue;
                var navWidth = $el.outerWidth();
                var offsetLeft = $el.position().left + navWidth / 2;
                if (wrapWidth > containerWidth) {
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
            }
        },
        // 所有事件注册：要使用事件后缀以及代理，方便维护
        addEvents: function() {
            var self = this;
            // 气泡点击
            this.$el
            // 点击切换分类
            .on('click.' + this.EVENT_SUBFIX, '.rank-nav-item', function() {
                var $self = $(this);
                if ($self.hasClass('active')) {
                    return;
                }

                var index =  $self.data('index');
                var skus = self.goods[index - 1].lists;
                self.fetchGoods(skus);
                self.activeNav($self);
            })
            // 点击显示气泡
            .on('click.' + this.EVENT_SUBFIX, '.rank-good-promotion.has-more', function(evt) {
                self.$el.find('.rank-good-promotion.has-more').not(this).removeClass('opened');
                $(this).toggleClass('opened');
                $(document).one('click' + Rank.EVENT_SUBFIX, function() {
                    self.$el.find('.rank-good-promotion.has-more').removeClass('opened');
                });
                evt.stopPropagation();
            })
            // 上一个
            .on('click.' + this.EVENT_SUBFIX, '.rank-nav-pre', function() {
                if (self.swiper) {
                    self.slidePrev();
                }
            })
            // 下一个
            .on('click.' + this.EVENT_SUBFIX, '.rank-nav-next', function() {
                if (self.swiper) {
                    self.slideNext();
                }
            });

            var $window = $(window);
            // 导航开启了吸附功能
            if (this.$nav && this.navFixed) {
                this.scrollHandler = utils.throttle(this.scrollFn, 100, { context: this });
                $window.on('scroll.' + this.EVENT_SUBFIX, this.scrollHandler);
            }

            this.resizeHandler = utils.debounce(this.resizeFn, 300, { context: this });
            $window.on('resize.' + this.EVENT_SUBFIX, { context: this }, this.resizeHandler);
        },
        // 移除所有事件，暂时未用到，保留
        removeEvents: function() {
            this.$el.off(this.EVENT_SUBFIX);
        },
        // 是否需要箭头
        isNeedArrow: function() {
            if (!this.$nav) {
                return false;
            }

            var winWidth = $(window).outerWidth(true);
            if (winWidth < 768) {
                return false;
            }

            var contentWidth = this.$nav.find('.rank-nav-content').width();
            var navWidth = this.$nav.find('.rank-nav').width();
            return contentWidth > navWidth;
        },
        // 显示上一个、下一个箭头
        showArrow: function() {
            if (this.$nav) {
                this.$nav.find('.rank-nav').addClass('has-arrow');
                this.$nav.find('.rank-nav-pre, .rank-nav-next').show();
            }
        },
        // 隐藏上一个、下一个箭头
        hideArrow: function() {
            if (this.$nav) {
                this.$nav.find('.rank-nav').removeClass('has-arrow');
                this.$nav.find('.rank-nav-pre, .rank-nav-next').hide();
            }
        },
        // 向左滑动一个导航项位置（位于最左边的可见导航项）
        // 向上向下滚动的计算真的把我自己也给算晕了
        slidePrev: function() {
            var $el = this.getFirstNavItemInview();
            if ($el) {
                var $prev = $el.prev();
                var elLeft = Math.floor($el.position().left);
                var distance = null;
                if ($prev.length > 0) {
                    var parentLeft = -Math.floor(this.$nav.find('.rank-nav-content').position().left) + parseInt(this.$nav.find('.rank-nav').css('paddingLeft'));
                    distance = elLeft < parentLeft ? elLeft : Math.floor($prev.position().left);
                } else {
                    distance = 0;
                }
                this.swiper.setWrapperTranslate(-distance);
                this.swiper.setWrapperTransition(300);
            }
        },
        // 向右滑动一个导航项位置（位于最左边的可见导航项）
        slideNext: function() {
            var $el = this.getFirstNavItemInview();
            if ($el) {
                var $next = $el.next();
                var $content = this.$nav.find('.rank-nav-content');
                var contentWidth = Math.floor($content.width());
                var $nav = this.$nav.find('.rank-nav');
                var navWidth = Math.floor($nav.width());
                var navPaddingLeft = parseInt($nav.css('paddingLeft'));
                var nextLeft = null;
                // 抵达右边了，不能再向左移动
                var toTheRight = null;
                if ($next.length > 0) {
                    nextLeft = Math.floor($next.position().left);
                    toTheRight = (nextLeft + navPaddingLeft) >= (contentWidth - navWidth);
                } else {
                    toTheRight = true;
                }

                var distance = 0;
                // 只能移动到导航内容的最后边
                if (toTheRight) {
                    distance = contentWidth - navWidth;
                } else {
                    distance = nextLeft;
                }

                this.swiper.setWrapperTranslate(-distance);
                this.swiper.setWrapperTransition(300);
            }
        },
        // 获取位于最左边的文字可见的导航项
        getFirstNavItemInview: function() {
            var distance = -Math.floor(this.$nav.find('.rank-nav-content').position().left) + parseInt(this.$nav.find('.rank-nav').css('paddingLeft'));
            var $el = null;
            this.$nav.find('.rank-nav-item').each(function() {
                var $item = $(this);
                var left = Math.floor($item.position().left);
                var itemWidth = Math.floor($item.width());
                var itemPaddingLeft = parseInt($item.css('paddingLeft'));
                if (left + itemWidth + itemPaddingLeft > distance) {
                    $el = $item;
                    return false;
                }
            });
            return $el;
        },
        // 向右滑动一个导航项位置（位于最左边的可见导航项）
        // 初始化滑动
        initSwiper: function() {
            if (this.$nav) {
                // 价格异常捕获，至少swiper出错，其他功能还能用
                try {
                    this.swiper = new Swiper3(this.$nav.find('.rank-nav')[0], {
                        wrapperClass: 'rank-nav-content',
                        slideClass: 'rank-nav-item',
                        slidesPerView: 'auto',
                        freeMode: true
                    });
                } catch (e) {
                }
            }
        },
        // 图片懒加载
        imageLazy: function() {
            if (this.isEdit) {
                this.$el.find('img.js_rank_lazy').each(function() {
                    var $img = $(this);
                    $img.attr('src', $img.attr('data-original'));
                });
            } else {
                if ($.fn.lazyload) {
                    this.$el.find('img.js_rank_lazy').lazyload({ failure_limit: 60 }).removeClass('js_rank_lazy');
                } else {
                    window.GS_GOODS_LAZY_FN('.js_rank_lazy');
                }
            }
        },
        // js触发商品大数据曝光埋点
        triggerExplore: function() {
            $(document).triggerHandler('logsss_explore', {
                $elList: this.$el.find('.rank-good.js_logsss_browser')
            });
        },
        // 商品渲染
        renderGoods: function(list) {
            var topGoodsHtml = this.topGoodsTpl.render(list.slice(0, 3));
            var otherGoodsHtml = this.otherGoodsTpl.render(list.slice(3));
            this.$goodContainer.html(topGoodsHtml + otherGoodsHtml);
        },
        // 滚动到商品模块
        scrollToGoods: function() {
            if (this.$nav && this.$nav.hasClass('is-fixed')) {
                var self = this;
                var $window = $(window);
                var top = this.$goodContainer.offset().top - this.$nav.height();
                $window.off('scroll.' + this.EVENT_SUBFIX);
                $('html, body').stop(true).animate({ scrollTop: top }, function() {
                    $window.on('scroll.' + self.EVENT_SUBFIX, self.scrollHandler);
                });
            }
        },
        // 请求商品
        fetchGoods: function(skus) {
            var self = this;

            if (!skus) {
                if (!this.isEdit) {
                    this.$goodContainer.html('');
                }
                return;
            }

            var url = GESHOP_INTERFACE.getrankdetail.url;
            var component_id = this.$el.attr('data-id');
            var params = { lang: GESHOP_LANG, client: GESHOP_PLATFORM, goodsSn: skus };
            window.GEShopCommonFn_Vue.$jsonp(url, params, { pid: component_id }).done(function (res) {
                if (res.code === 0) {
                    var goods = res.data || [];
                    self.renderGoods(goods);
                    self.scrollToGoods();
                    self.imageLazy();
                    self.triggerExplore();
                    // @FIXME: 后面应该还得考虑app的默认货币
                    window.GLOBAL && window.GLOBAL.currency && window.GLOBAL.currency.change_huobi();
                }
            });
        }
    };


    // 初始化，总入口
    function init() {
        $('.geshop-U000208-model1').each(function() {
            try {
                new Rank($(this));
            } catch (e) {
            }
        });
    }

    // 使用LABjs避免重复加载js文件
    // swipter3在ie9下有兼容问题，加个classList垫片解决
    var chained = $LAB;
    if (!("classList" in document.createElement("_"))  || document.createElementNS && !("classList" in document.createElementNS("http://www.w3.org/2000/svg","g"))) {
        chained = $LAB.script(GESHOP_STATIC + '/resources/javascripts/library/classList.min.js');
    }
    chained.script(GESHOP_STATIC + '/resources/javascripts/library/gs_laytpl.js')
        .script(GESHOP_STATIC + '/resources/javascripts/library/swiper/swiper.3.4.spec.min.js')
        .wait(function () {
            // laytpl默认分隔符和twig的分隔符冲突了
            gs_laytpl.config({ open: "<%", close: "%>" });
            init();
        });
});
