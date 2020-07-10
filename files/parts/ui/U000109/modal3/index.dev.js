;$(function () {
    class GoodsTab3 {
        constructor(el) {
            this.$box = el;
            this.swiperTop = this.$box.find('.gs-tab-box');
            this.tabContent = this.$box.find('.gs-tab-content');
            this.ulW = this.$box.find('.goods-nav-name').outerWidth();
            this.cateid = this.$box.find('.swiper-slide').eq(0).attr('data-asyncdata-id') || -1; // 异步接口分类id
            this.pagesize = parseInt(this.$box.find('.pagesize').val());
            this.timer = null;
            this.navTb = this.swiperTop.find('.swiper-container');
            this.init();
        }

        init() {
            const staticDomain = GESHOP_STATIC;
            loadCss(staticDomain + '/resources/javascripts/library/swiper/swiper.min.css');
            $LAB.script(staticDomain + '/resources/javascripts/library/swiper/swiper.3.4.spec.min.js')
                .script(staticDomain + "/resources/javascripts/library/gs_laytpl.js?2018100901")
                .wait(() => {
                    gs_laytpl.config({ open: "<%", close: "%>" });
                    this.initSwiper();
                    if (this.$box.hasClass('js_gs_tab_wrap_fixed')) {
                        this.scrollCalBackFn();
                    }
                })
        }

        initSwiper() {
            this.$box.find('.goods-nav-name > li').eq(0).addClass('current');
            if (this.ulW >= 1064) {
                this.$box.find(".icon-img-left").show();
                this.$box.find(".icon-img-right").show();
            } else {
                this.$box.find(".icon-img-left").hide();
                this.$box.find(".icon-img-right").hide();
            }
            this.initSlideTab();

        }

        initSlideTab() {
          setTimeout(()=>{
              new Swiper3(this.navTb, {
                  slidesPerView: 'auto',
                  slideToClickedSlide: true,
                  nextButton: this.swiperTop.find('.next-btn'),
                  prevButton: this.swiperTop.find('.pre-btn'),
              });
          },100)

            this.handleSwiperClick();
            this.getRankList();

        }

        handleSwiperClick() {
            const _self = this;
            this.swiperTop.on('click', 'li', function () {
                $(this).addClass('current').siblings().removeClass('current');
                _self.cateid = $(this).attr('data-asyncdata-id') || -1;
                _self.getRankList();
                if (_self.$box.hasClass('.gs-tab-wrap-fixbox')) {
                    const targetScrollTop = _self.$box.find('.gs-tab-wrap-fixbox').offset().top;
                    $('html,body').animate({ scrollTop: targetScrollTop }, 500);
                }
            })
        }

        getRankList() {
            const self = this;
            const getTpl = this.$box.find('.pc-leader-board-template').html(),
                view = this.$box.find('.leader-board-container'),
                lang = typeof GESHOP_LANG != 'undefined' ? GESHOP_LANG : 'en';
            const params = {
                type: 1,
                cateid: this.cateid,
                lang: lang,
                pageno: 1,
                pagesize: this.pagesize,
                pipeline: (typeof GESHOP_PIPELINE != 'undefined' ? GESHOP_PIPELINE : '')
            };
            const url = GESHOP_INTERFACE.getrankdetail.url;
            const content = { content: JSON.stringify(params) };
            if (typeof this.cateid == 'undefined' || parseInt(this.cateid) < 0) {
                gs_laytpl(getTpl).render({ goodsInfo: [] }, function (html) {
                    view.html(html);
                })
            } else {
                var component_id = this.$box.attr('data-id');
                $.ajax({
                    url: url,
                    type: 'get',
                    dataType: 'jsonp',
                    data: content,
                    cache: true,
                    jsonpCallback: `geshop_callback_${component_id}`,
                    success: function (res) {
                        const data = res.data;
                        gs_laytpl(getTpl).render(data, function (html) {
                            view.html(html);
                            if (typeof GS_GOODS_LAZY_FN != "undefined") {
                                GS_GOODS_LAZY_FN(self.tabContent.find('.gs-tab-item img.js-geshopImg-lazyload'))
                            } else {
                                self.tabContent.find('.gs-tab-item img.js-geshopImg-lazyload').each((index, item) => {
                                    $(item).attr('src', $(item).data('original'));
                                })
                            }
                            if(typeof  GEShopSiteCommon !== 'undefined') {
                                GEShopSiteCommon.renderCurrency();
                            }
                        });
                    }
                });
            }

           if(typeof  GEShopSiteCommon !== 'undefined') {
                GEShopSiteCommon.renderCurrency();
           }
        }

        scrollCalBackFn() {
            const _self = this;
            const $tabWrap = this.$box.find('.gs-tab-wrap');
            $tabWrap.wrap('<div class="gs-tab-wrap-fixbox"></div>');
            $(window).on('scroll', function () {
                clearTimeout(_self.timer);
                const scrollTop = $(this).scrollTop();
                _self.timer = setTimeout(() => {
                    const gsTabOffset = _self.$box.find(".geshop-content").offset();
                    const fixH = $tabWrap.height();
                    const gsTabBox = $tabWrap.find('.gs-tab-box');
                    const disId = _self.$box.data('id');
                    if (gsTabOffset.top <= scrollTop && scrollTop < gsTabOffset.top + _self.$box.find(".geshop-content").height()) {
                        _self.$box.find('.gs-tab-wrap-fixbox').height(fixH);
                        gsTabBox.css({
                            "position": "fixed",
                            top: 0,
                            height: fixH,
                            left: gsTabBox.offset().left + 'px',
                            'z-index': 9999
                        });

                        _self.$box.addClass('js-geshop-nav-fixed');

                        // 站点导航栏处理
                        if ($('.js-geshop-nav').length) {
                            $('.js-geshop-nav').hide();
                        }

                        // 页面中存在水平导航时，隐藏水平导航
                        if ($('div[data-key="U000027"]').length) {
                            $('div[data-key="U000027"]').find('ul').hide();
                        }

                        // $('#nav-list').closest('[class*="fix"]').addClass('geshop-' + disId + '-fiexd');
                        // $('.geshop-' + disId + '-fiexd').hide();
                    } else {
                        gsTabBox.css({
                            "position": "relative",
                            top: 'auto',
                            left: 'auto',
                            'z-index': 100
                        });

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
                        // $('.geshop-' + disId + '-fiexd').show().removeClass('geshop-' + disId + '-fiexd');
                    }
                }, 2);
            });
        }
    }

    $('.geshop-U000109-modal3').each((index, el) => {
        new GoodsTab3($(el));
    });
});
