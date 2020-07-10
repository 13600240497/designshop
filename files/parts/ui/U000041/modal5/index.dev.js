;$(function () {
    class modal5List {
        constructor (el) {
            this.$box = el;
            this.ajx = false;
            this.pageSize = 140; // 一页加载多少商品
            this.pageCount = 0; // 总页码
            this.currentPage = 1; // 当前页
            this.goodList = this.$box.find('.nav-item').eq(0).attr('data-initSn');
            this.init();
        }

        init () {
            const staticDomain = GESHOP_STATIC;
            loadCss(staticDomain + '/resources/javascripts/library/swiper/swiper.min.css');
            $LAB.script(staticDomain + '/resources/javascripts/library/gs_laytpl.js').wait(() => {
                gs_laytpl.config({ open: '<%', close: '%>' });
                this.scrollCalBackFn();
                this.getSameList(this.goodList);
                this.bindEvent(); // 绑定同款切换事件
            });
        }

        getSameList (sku) {
            const self = this;
            const getTpl = this.$box.find('.u000041-modal5-goods-temp').html(),
                view = this.$box.find('.u000041-modal5-goods-list'),
                lang = typeof GESHOP_LANG != 'undefined' ? GESHOP_LANG : 'en';
            // const url = 'http://www.pc-rosegal.com.v0424.php5.egomsl.com/geshop/goods/samelist';

            if (!(typeof sku == 'undefined' || sku == '')) {
                const goodsArr = sku.split(',');
                self.pageCount = Math.ceil(goodsArr.length / self.pageSize);
                sku = goodsArr.slice((self.currentPage - 1) * self.pageSize, self.currentPage * self.pageSize);
                const params = {
                    lang: lang,
                    client: GESHOP_PLATFORM || 'pc',
                    goodsSn: sku.join(',')
                };
                const url = GESHOP_INTERFACE.goods_samelist.url;
                window.GEShopCommonFn_Vue.$jsonp(url, params, { target: this.$box }).done(function (res) {
                    const data = res.data;
                    self.ajx = true;
                    gs_laytpl(getTpl).render(data, function (html) {
                        // jq > 1.9 安全性问题需要转HTML ====》 $.parseHTML(html, document, true)
                        let $div = $($.parseHTML(html, document, true));
                        if (self.currentPage === 1) {
                            view.html('').append($div);
                        } else {
                            view.append($div);
                        }
                        if (window.GESHOP_PAGE_TYPE == 1) {
                            $('img.js_gbexp_lazy').each(function () {
                                $(this).attr('src', $(this).attr('data-original'));
                            });
                        } else {
                            if (typeof GS_GOODS_LAZY_FN != 'undefined') {
                                GS_GOODS_LAZY_FN(($div).find('img.js_gbexp_lazy'));
                            }
                        }
                        window.GS_GOODS_LAZY_FN();
                        window.GLOBAL && window.GLOBAL.currency && window.GLOBAL.currency.change_html();
                    });
                });
            }
        }

        bindEvent () {
            const self = this;
            this.$box.on('mouseenter', '.same-items', function () {
                $(this).addClass('on').siblings().removeClass('on');
                const $listWrap = $(this).parents('.mo5-good-list');

                $listWrap.find('.geshop-u000041-modal5-list-title a').text($(this).attr('data-title')).attr('href', $(this).attr('data-url'));
                $listWrap.find('.geshop-u000041-modal5-list-img a').attr('href', $(this).attr('data-url'));
                $listWrap.find('.js-geshopImg-lazyload').attr('src', $(this).attr('data-img'));
                $listWrap.find('.mol5_quick_shop').attr('data-qkurl', $(this).attr('data-qkurl'));
                // 更新商品库存告急
                var stock_value = window.GESHOP_LANGUAGES.left && window.GESHOP_LANGUAGES.left.replace('XX', $(this).attr('data-gsnumber'));
                $listWrap.find('.geshop__mask--stocktip p').text(stock_value);
            });

            this.$box.on('click', '.mol5_quick_shop', function () {
                GEShopSiteCommon.dialog.iframe($(this).attr('data-qkurl'), 1080, 597, true);
            });
            //  导航切换
            this.$box.on('click', '.sub-item', function () {
                if (self.$box.hasClass('js_nav_fixed')) {
                    self.ajx = false;
                    const targetScrollTop = self.$box.find('.geshop-containr').offset().top;
                    $('html,body').animate({ scrollTop: targetScrollTop }, 500, function () {
                        self.ajx = true;
                    });
                }
                /* if ($(this).hasClass('on')) {
                    return false;
                } else {

                } */
                self.$box.find('.nav-item').removeClass('on');
                self.$box.find('.sub-item').removeClass('on');
                $(this).parents('.nav-item').addClass('on');
                self.$box.find('.pagenation_nav .nav_1').text($(this).parents('.nav-item').find('span').eq(0).text());
                self.$box.find('.pagenation_nav .nav_2').text($(this).find('span').text());
                $(this).addClass('on');
                let sku = $(this).attr('data-goodsSn');
                self.currentPage = 1;
                self.getSameList(sku);
            });
        }

        scrollCalBackFn () {
            const _self = this;
            $(window).on('scroll', function () {
                const scrollTop = $(this).scrollTop();
                const gsTabOffset = _self.$box.find('.geshop-containr').offset();
                if (_self.$box.hasClass('js_nav_fixed')) {
                    if (gsTabOffset.top <= scrollTop && scrollTop < gsTabOffset.top + _self.$box.find('.geshop-containr').height()) {
                        $('#js_nav').hide();

                        // 站点导航栏处理
                        if ($('.js-geshop-nav').length) {
                            $('.js-geshop-nav').hide();
                        }

                        // 页面中存在水平导航时，隐藏水平导航
                        if ($('div[data-key="U000027"]').length) {
                            $('div[data-key="U000027"]').find('ul').hide();
                        }
                        _self.$box.addClass('js-geshop-nav-fixed');
                        _self.$box.find('.nav-hd-wrap,.nav-hd-fixd-wrap').addClass('fixed');
                    } else {
                        _self.$box.find('.nav-hd-wrap,.nav-hd-fixd-wrap').removeClass('fixed');
                        $('#js_nav').show();
                        _self.$box.removeClass('js-geshop-nav-fixed');

                        // 站点导航栏处理
                        if ($('.js-geshop-nav').length) {
                            $('.js-geshop-nav').show();
                        }

                        // 页面中存在水平导航时，隐藏水平导航
                        if ($('div[data-key="U000027"]').length) {
                            $('div[data-key="U000027"]').find('ul').show();
                        }

                        if (GEShopSiteCommon) {
                            GEShopSiteCommon.jsNavFixed();
                        }
                    }
                }

                // 以整个容器高度的1/3 作为判断 也可以 容器 - 固定高度 作为判断
                if (scrollTop > gsTabOffset.top + _self.$box.find('.geshop-containr').height() / 3) {
                    if (_self.pageCount > _self.currentPage) {
                        if (_self.ajx) {
                            _self.ajx = false;
                            _self.currentPage++;
                            _self.getSameList(_self.goodList);
                        }
                    }
                }
            });
        }
    }

    $('.geshop-U000041-modal5').each(function (item, elem) {
        new modal5List($(elem));
    });
});
