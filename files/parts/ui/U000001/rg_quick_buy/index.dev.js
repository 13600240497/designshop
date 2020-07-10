;$(function () {
    class qucikList {
        constructor (el) {
            this.$box = el;
            this.ajx = false; // 是否可以请求
            this.pageSize = 140; // 一页加载多少商品
            this.pageCount = 0; // 总页码
            this.currentPage = 1; // 当前页
            this.goodList = this.$box.attr('data-skus');
            this.init();
        }

        init () {
            const staticDomain = GESHOP_STATIC;
            $LAB.script(staticDomain + '/resources/javascripts/library/gs_laytpl.js').wait(() => {
                gs_laytpl.config({ open: '<%', close: '%>' });
                this.getSameList(this.goodList);
                this.bindEvent(); // 绑定同款切换事件
            });
        }

        getSameList (sku) {
            const self = this;
            const getTpl = this.$box.find('.u000001-quick-goods-temp').html(),
                view = this.$box.find('.u000001-quick-goods-list'),
                lang = typeof GESHOP_LANG != 'undefined' ? GESHOP_LANG : 'en';

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
                // const url = 'http://www.pc-rosegal.com.v0424.php5.egomsl.com/geshop/goods/samelist';
                const content = { content: JSON.stringify(params) };
                $.ajax({
                    url: url,
                    type: 'get',
                    dataType: 'jsonp',
                    data: content,
                    success: function (res) {
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
                            if (window.GESHOP_PAGE_TYPE  == 1) {
                                $('img.js_gbexp_lazy').each(function () {
                                    $(this).attr('src', $(this).attr('data-original'));
                                });
                            } else {
                                if (typeof GS_GOODS_LAZY_FN != 'undefined') {
                                    GS_GOODS_LAZY_FN(($div).find('img.js_gbexp_lazy'));
                                }
                            }
                            window.GLOBAL && window.GLOBAL.currency && window.GLOBAL.currency.change_html(); 
                        });
                    }
                });
            }
        }

        bindEvent () {
            const self = this;
            this.scrollCalBackFn();
            this.$box.on('mouseenter', '.same-items', function () {
                $(this).addClass('on').siblings().removeClass('on');
                const $listWrap = $(this).parents('.mo5-good-list');

                $listWrap.find('.geshop-u000001-quick-list-title a').text($(this).attr('data-title')).attr('href', $(this).attr('data-url'));
                $listWrap.find('.geshop-u000001-quick-list-img a').attr('href', $(this).attr('data-url'));
                $listWrap.find('.js-geshopImg-lazyload').attr('src', $(this).attr('data-img'));
                $listWrap.find('.mol5_quick_shop').attr('data-qkurl', $(this).attr('data-qkurl'));
            });

            this.$box.on('click', '.mol5_quick_shop', function () {
                GEShopSiteCommon.dialog.iframe($(this).attr('data-qkurl'), 1080, 597, true);
            });

            this.$box.on('click', '.collect-icon', function () {
                // 收藏调用 本期不做
                let url = 'http://www.pc-rosegal.com.v0424.php5.egomsl.com/geshop/goods/attention';
                $(this).toggleClass('on');
                /* $.ajax({
                    url: url,
                    type: 'get',
                    data: { content: JSON.stringify({ goods_id: $(this).attr('data-id')})},
                    dataType: 'jsonp',
                    success: function (res) {
                        console.log(res);
                    }
                }); */
            });

        }

        scrollCalBackFn () {
            const _self = this;
            $(window).on('scroll', function () {
                const scrollTop = $(this).scrollTop();
                const gsTabOffset = _self.$box.find('.geshop-containr').offset();
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

    $('.geshop-u000001-quick').each(function (item, elem) {
        new qucikList($(elem));
    });
});
