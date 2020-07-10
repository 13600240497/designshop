// quick view by luoxiaohui @2018-07-04
(function () {
    let DEFAULT_HEIGHT = '300px';
    let HTML =
        '<div class="js-fast-good fast-goods">' +
        '<span class="fast-goods-arrow"></span>' +
        '<span class="fast-goods-close"></span>' +
        '<div class="fast-goods-body">' +
        '<div class="fast-goods-content"><iframe src="about:blank" frameborder="0" width="100%" style="height:' + DEFAULT_HEIGHT + '"></iframe></div>' +
        '</div>' +
        '<div class="fast-good-mask" style="display:none;"><img src="' + window.JS_IMG_URL + 'img/common/loading-1.gif" width="32" height="32">' +
        '</div>';

    // containerSelector: 商品列表容器的选择器
    // itemSelector: 商品元素的选择器
    // clickElSelector: 点击元素的选择器（quick view按钮）
    function QuickView (containerSelector, itemSelector, clickElSelector, clickParent) {
        this.containerSelector = containerSelector;
        this.itemSelector = itemSelector;
        this.clickElSelector = clickElSelector;
        this.clickParentEl = clickParent;
    }

    QuickView.prototype.init = function () {
        let _html = `<div class="${this.clickParentEl}" style="display:none;">` + HTML + '</div>';
        $('body').append(_html);
        this.el = $('.' + this.clickParentEl).find('.js-fast-good');
        this.arrowEl = this.el.find('.fast-goods-arrow');
        this.iframeEl = this.el.find('iframe');
        this.maskEl = this.el.find('.fast-good-mask');
        this.addEvents();

        return this;
    };

    QuickView.prototype.destroy = function () {
        this.removeEvents();
        this.maskEl = null;
        this.iframeEl = null;
        this.arrowEl = null;
        this.el.remove();
        this.el = null;
    };

    QuickView.prototype.addEvents = function () {
        let self = this;
        $('.' + this.clickParentEl).on('click.quickview', this.clickElSelector, function () {
            self.show($(this));
        });

        this.el.find('.fast-goods-close').on('click.quickview', function () {
            self.el.stop().slideUp(function () {
                self.hide(true);
            });
        });

        // 由内嵌的quick view iframe回调的信息，在fast_goods.htm和goods.js中
        $(window).on('message.quickview', function (event) {
            // console.log(event.originalEvent.data);
            try {
                let data = JSON.parse(event.originalEvent.data);
                let action = data.action;
                // iframe 加载完毕
                if (action === 'FAST_GOOD_LOAD') {
                    self.maskEl.hide();
                    // 有可能回调还没有出发，就已经关闭了quick view层
                    if (!self.isHide()) {
                        self.iframeEl.stop(true, true).animate({ height: data.height + 'px' });
                    };
                    // 更新购物车
                } else if (action === 'FAST_GOOD_CART_NUM') {
                    $('.js-cart-num').html(data.num).trigger('custom-minicart-show');
                    // iframe 卸载页面
                } else if (action === 'FAST_GOOD_UPLOAD') {
                    self.maskEl.show();
                    // 跳转 url
                } else if (action === 'FAST_GOOD_JUMP') {
                    window.location.href = data.url;
                }
            } catch (e) {
                // console.log(e);
            }
        })
            .on('resize.quickview', function () {
                self.hide();
            });
    };

    QuickView.prototype.removeEvents = function () {
        $(window).off('resize.quickview').off('message.quickview');
        this.el.find('.fast-goods-close').off('click.quickview');
        $(document).off('click.quickview');
    };

    // 获取最终插入位置的前一个兄弟节点
    // 因为pc版的可能一行商品是5个，4个，所以要动态计算一行的商品的个数
    QuickView.prototype.getAfterItem = function (clickEl) {
        let containerEl = $('.' + this.clickParentEl).find(this.containerSelector);
        let allItemEl = containerEl.children(this.itemSelector);
        let allItemElLength = allItemEl.length;
        let itemEl = clickEl.closest(this.itemSelector);
        let index = itemEl.index();
        let colNum = allItemElLength;
        let left = 0;
        let testElLeft;
        for (let i = 0; i < allItemElLength; i++) {
            testElLeft = allItemEl.eq(i).offset().left;
            if (testElLeft < left) {
                colNum = i;
                break;
            } else {
                left = testElLeft;
            }
        }

        let pos = index + colNum - index % colNum - 1;

        if (pos >= allItemElLength) {
            pos = allItemElLength - 1;
        }

        return allItemEl.eq(pos);
    };

    // 获取箭头left位置的值
    QuickView.prototype.getArrowLeftPosition = function (clickEl) {
        let containerEl = $('.' + this.clickParentEl).find(this.containerSelector);
        let itemEl = clickEl.closest(this.itemSelector);
        let containerOffset = containerEl.offset();
        let itemOffset = itemEl.offset();
        let itemWidth = itemEl.outerWidth();
        let left = Math.floor(itemOffset.left - containerOffset.left + itemWidth / 2 - 5); // 5是箭头宽度的一般

        return left;
    };

    QuickView.prototype.show = function (clickEl) {
        let left = this.getArrowLeftPosition(clickEl);
        this.arrowEl.css('left', left + 'px');

        let afterEl = this.getAfterItem(clickEl);
        this.el.insertAfter(afterEl).show();

        // let top = this.el.offset().top - 100; // 100是随意取得，只要效果ok就行

        // $('html, body').animate({ scrollTop: top });

        let fastGoodURL = clickEl.data('fastGood');
        this.load(fastGoodURL);
    };

    QuickView.prototype.hide = function (force) {
        // 在resize会频繁调用，所以先判断一下是否已经隐藏，提高效率
        let self = this;
        if (force === true || !this.isHide()) {
            self.el.stop(true, true).hide(function () {
                self.el.detach(); // 从dom移除，否则，窗口resize后，列的排版会出问题
            }); // 注意，先停止动画
            self.iframeEl.css('height', DEFAULT_HEIGHT) // 默认高度
                .attr('src', 'about:blank'); // 清空内容，节省资源
        }
    };

    QuickView.prototype.isHide = function () {
        return this.el.css('display') === 'none';
    };

    QuickView.prototype.load = function (url) {
        this.maskEl.show();
        this.iframeEl.attr('src', url);
    };

    if (window.GLOBAL) {
        window.GLOBAL.QuickView = QuickView;
    }
})();
