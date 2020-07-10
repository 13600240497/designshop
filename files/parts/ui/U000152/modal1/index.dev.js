/***
 * 新加的字段    商品标题字体颜色  shopTitleColor  加价购文字大小  purchasePriceTextSizeize
 * 加价购名文案 purchasePriceText 加价购文字颜色  purchasePriceTextColor
 * bannerBackgroundImage banner背景图片
 * **/
$(function () {
    //替换金钱
    var __$replace = function __$replace() {
        if (window.GLOBAL && window.GLOBAL.currency) {
            window.GLOBAL.currency.change_html()
        }
        if (window.FUN && window.FUN.currency) {
            window.FUN.currency.change_html()
        }
    }
    // {# 开始按钮结束按钮 #}
    var CountDown1 = function CountDown1(params, cb) {
        this.startTime = parseInt(params.starttime) * 1000;
        this.endTime = parseInt(params.endtime) * 1000;
        this.diffTime = params.diffTime
        this.timer = null;
        this.cb = cb;
        var _curTime = this.getCurTime();
        if (this.endTime === 0) {
            this.processing();
        }
        if (_curTime >= this.endTime) {
            this.end();
        } else if (_curTime < this.startTime) {
            this.notBegin();
            this.run();
        } else {
            this.processing();
            this.run();
        }

    };
    $.extend(CountDown1.prototype, {
        // {# 获取时间 返回当前时间的时间撮  time 时间 #}
        getTime: function () {
            return (new Date()).getTime();
        },
        //{# 获取正确的时间  本地时间 + 服务器与本地的时间差 #}
        getCurTime: function () {
            return this.getTime() + this.diffTime;
        },
        //{# 未开始 #}
        notBegin: function () {
            if (this.status !== 1) {
                this.status = 1;
            }
            var diffTime = parseInt(this.getCurTime() / 1000 - this.startTime / 1000);
            if (diffTime >= 0) {
                this.processing();
            }
        },
        //{# 进行中 #}
        processing: function () {
            if (this.status !== 2) {
                this.status = 2;
            }
            var diffTime = parseInt(this.endTime / 1000 - this.getCurTime() / 1000);
            if (diffTime <= 0) {
                this.end();
            }
        },
        //{# 已结束 #}
        end: function () {
            this.cb && this.cb();
        },
        //{# 开始运行倒计时 #}
        run: function () {
            var _this = this;
            this.timer = setInterval(function () {
                _this.runTime();
            }, 1000);
        },
        //{# 倒计时运行函数 #}
        runTime: function () {
            if (this.status === 1) {
                this.notBegin();
            }
            if (this.status === 2) {
                this.processing();
            }
            if (this.status === 3) {
                this.end();
            }
        },
        //{# 清楚倒计时定时器 #}
        clearTimer: function () {
            clearInterval(this.timer);
        },
    });
    $.extend($.fn, {
        hiddenAttr: function (attr) {
            return this.attr((attr || '').toLowerCase()) || '';
        },
        U000152: function (element) {
            var $this = this;
            var $hidden = $this.find('.hidden-input');
            var _goodsList = JSON.parse($this.find('.tabs-list').html()),//商品数据  有几个table 格式 [{}]
                _productsNum = parseInt($hidden.hiddenAttr('productsNum')) || 4;//编辑中的商品数量
            var _tmp = $this.find('.pc-gift-template').html();
            var goodsList = {};
            var staticDomain = GESHOP_STATIC;
            var $tabItem = $this.find('.tab-ul-item');
            var $slide = $this.find('.geshop-tabs .swiper-slide');
            var _isLoop = _goodsList.length > 2;
            var serverTime = $hidden.hiddenAttr('serverTime');//服务器时间
            var _diffTime = parseInt(serverTime) - (new Date()).getTime();
            var defaultImage = 'https://geshopimg.logsss.com/uploads/nxQshzC1wXTYy8BmHD6GE39vLWjciKaR.png';//默认图片路劲
            var __url = GESHOP_INTERFACE.increasebuylist.url;
            var _swiper;//swiper
            $tabItem.eq(0).show();//初始化让第一个显示
            loadCss(staticDomain + '/resources/javascripts/library/swiper/swiper.min.css');
            $LAB.script(staticDomain + '/resources/javascripts/library/swiper/swiper.3.4.spec.min.js').wait(function () {
                setTimeout(function () {

                    _swiper = swiperinit();
                    window._swiper = _swiper;
                    $.each(_goodsList, _addTab);
                }, 20);
            })
            //默认的商品数组
            var _defaultList = (function (n) {
                var _arr = [];
                for (var i = 0; i < n; i++) {
                    _arr.push({
                        goods_title: 'Floral Print Mini Wrap Tea Dress DASDASD asdas',
                        shop_price: 19.99,
                        increaseamount: 11.00,
                    })
                }
                return _arr;
            }(_productsNum));
            //字符串模板解析
            var _substitute = function _substitute(s, obj) {
                return s.replace(/\{\{([^{}]*)\}\}/g, function (s1, s2) {
                    if (s2 == "activity_number") {
                        if (obj[s2] == 0) {
                            return "false"
                        } else {
                            return (obj || {})[s2] || ''
                        }

                    } else {
                        return (obj || {})[s2] || ''
                    }
                })
            }


            //添加倒计时 这里会有两种状态 第一种是活动结束 显示end (act-end)  第二种是活动库存卖完 (act-sale-out) 显示 多语言配置
            //这里现在只是做一个判断库存有没有的动作  库存没有了的话就显示没有库存的蒙层
            var _addCountDown1 = function _addCountDown1($li, item) {
                if ($this.find("li[activity_number = 'false']").find('.act-end').css('display') == 'block') {
                    return;
                }
                $this.find("li[activity_number = 'false']").find('.geshop-components-default-soldout').show()
            }
            //初始化渲染列表内容
            /**
             * TODO
             * @param index 索引 第几个ul的内容
             * @param list 列表数据
             * ***/
            var _initGoods = function _initGoods(index, list, act) {
                var _arr = [];
                act = act || {};
                for (var i = 0; i < list.length; i++) {
                    //加入默认图片路劲跟活动信息  因为图片路劲的懒加载在编辑里面是没有生效的
                    _arr.push(_substitute(_tmp, $.extend({ goods_img: defaultImage, index: i + 1 }, list[i], act)))

                }
                var $ul = $tabItem.eq(index);
                $tabItem.eq(index).html(_arr.join(''));
                //如果活动信息有的话
                if (act.id) {
                    //判断是不是卖完的状态
                    $.each(list, function (index, obj) {
                        _addCountDown1($ul.find('li').eq(index), obj);
                    });
                    //活动结束

                    new CountDown1($.extend({ diffTime: _diffTime }, act), function () {
                        $ul.find('li').find('.geshop-components-default-soldout').hide();
                        $ul.find('li').find('.act-end').show();
                    });

                }
                initialImage($tabItem.eq(index));
            }
            /**
             * TODO 添加列表内容
             * @param index 索引 第几个ul
             * @param obj 列表数据 编辑的时候的对象
             * @param act 加价购信息
             * ***/
            var _addTab = function _addTab(index, obj) {
                var _id = obj.ids;
                if (!_id) {
                    _initGoods(index, _defaultList);
                } else {
                    var lang = typeof GESHOP_LANG != 'undefined' ? GESHOP_LANG : 'en';
                    var params = {
                        lang: lang,
                        activityid: _id,
                        pageno: 1,
                        pagesize: _productsNum,
                        pipeline: (typeof GESHOP_PIPELINE != 'undefined' ? GESHOP_PIPELINE : '')
                    };
                    var url = __url;
                    window.GEShopCommonFn_Vue.$jsonp(url,params,{target:element}).done(function (res) {
                        _initGoods(index, res.data.goodsInfo, res.data.activityInfo);
                    })
                }
            }

            function swiperinit(dataId) {
                var $container = $this.find('.swiper-container');
                return new Swiper3($container, {
                    slidesPerView: _isLoop ? 2 : _goodsList.length,
                    loopedSlides: _goodsList.length,
                });
            }

            var __index = 0;
            //滚动点击事件 因为会有问题 _swiper
            var _moveTo = function _moveTo(n) {

                if (n <= 0) {
                    n = 0
                }
                if (n >= _goodsList.length - 1) {
                    n = _goodsList.length - 1
                }

                if (n === __index || n <= 0 || n >= _goodsList.length - 1) {
                    if (n === __index || n <= 0) {
                        $this.find('.swiper-button-prev-1').addClass('off');
                        $this.find('.swiper-button-next-1').removeClass('off');
                    }
                    if (n >= _goodsList.length - 1) {
                        $this.find('.swiper-button-prev-1').removeClass('off');
                        $this.find('.swiper-button-next-1').addClass('off');
                    }
                } else {
                    $this.find('.swiper-button-prev-1').removeClass('off');
                    $this.find('.swiper-button-next-1').removeClass('off');
                }
                if (n < _goodsList.length - 1) {
                    _swiper.slideTo(n);
                }
                $slide.removeClass('swiper-slide-active-1').eq(n).addClass('swiper-slide-active-1');
                $tabItem.hide().eq(n).show();
                __index = n;
            }
            $this.find('.swiper-button-next-1').click(function () {
                _moveTo(__index + 1);
            })
            $this.find('.swiper-button-prev-1').click(function () {
                _moveTo(__index - 1);
            })
            //列表tab点击 因为sswiper 在 loop:false 的时候是不会切换的
            $this.find('.swiper-wrapper .swiper-slide-item-1').click(function (e) {
                var _index = parseInt($(this).attr('index'));
                _moveTo(_index)
            })

            /**
             * 初始化图片
             */
            function initialImage($item) {
                //替换金钱标识符
                __$replace();
                try {
                    GESHOP_UTIL.goodsLazy('img.js_gdexp_lazy.js-geshopImg-lazyload')
                } catch (e) {
                }
            }
        }
    });

    $(function () {
        $('.geshop-U000152-modal1').each(function (index, el) {
            $(el).U000152(el);
        });
    });
});
