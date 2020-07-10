<template>
    <div class="geshop-U000182-model2-body" :class="!data.goodsSKU && !data.goodsInfo?'geshop_blank_component':''"
         :style="style_body">
        <div class="wrap">
            <div class="default-left-image-box" v-if="left_active[this.platform]=='1'">
                <a :href="data.img_links||'javascript:;'">
                    <img :src="left_img[this.platform]" :class="'img_'+this.platform">
                </a>
            </div>

            <div class="swiper-container">
                <div class="swiper-wrapper">
                    <div class="swiper-slide" v-for="(item, index) in list" :key="index">
                        <div class="list-item">
                            <!--折扣标-->
                            <div class="item-discount">
                                <geshop-discount :value="item.discount" :percent="item.discount"></geshop-discount>
                                <div v-if="!data.goodsSKU && !data.goodsInfo"
                                     class="geshop-components-discount geshop-discount-default"
                                     style="width: 50px; height: 50px; right: 0px; top: 0px; color: rgb(255, 255, 255); background-color: rgb(21, 21, 21);">
                                    <span><label>20%<br><i>OFF</i></label></span>
                                </div>
                            </div>

                            <div class="item-image" :class="!item.goods_img ? 'item_blank_img' : ''">
                                <a class="js_logsss_click_delegate_ps" :href="item.url_title || 'javascript:;'"
                                   :data-skus="item.goods_sn">
                                    <div class="geshop-dl-image-goods">
                                        <img v-if="item.goods_img && isEditEnv !== 1"
                                             class="default-swiper-image swiper-lazy js_gdexp_lazy js-geshopImg-lazyload js_gbexp_lazy"
                                             src="https://geshopcss.logsss.com/imagecache/geshop/resources/images/dl/category-loading.gif"
                                             :data-src="item.goods_img" :data-original="item.goods_img">
                                        <img v-else-if="isEditEnv === 1"
                                             :src="item.goods_img||'https://geshopimg.logsss.com/uploads/nxQshzC1wXTYy8BmHD6GE39vLWjciKaR.png'">
                                        <img v-else
                                             src="https://geshopimg.logsss.com/uploads/nxQshzC1wXTYy8BmHD6GE39vLWjciKaR.png"
                                             data-src="https://geshopimg.logsss.com/uploads/nxQshzC1wXTYy8BmHD6GE39vLWjciKaR.png"
                                             alt="">
                                        <!--                                    <div class="swiper-lazy-preloader"></div>-->
                                    </div>
                                </a>
                            </div>

                            <div class="item-content">
                                <div class="item-info">
                                    <!--sku标题-->
                                    <div class="item-title"
                                         :class="platform === 'm' && data.title_is_show == 0 ? 'hide':''">
                                        <span>{{ item.goods_title || 'ZAFUL Asymmetric Striped Slit Shirt Dress - Dark Gree …' }}</span>
                                    </div>

                                    <div class="item-shop-market">
                                        <!--销售价-->
                                        <div class="item-shop">
                                            <geshop-shop-price :value="item.shop_price"></geshop-shop-price>
                                            <div class="inline-block"
                                                 :class="(platform == 'm' && data.price_is_show == 0) || Number(item.market_price) <= Number(item.shop_price) ? 'hide':''">
                                                <geshop-market-price :value="item.market_price"></geshop-market-price>
                                            </div>

                                        </div>
                                    </div>
                                </div>
                                <div class="item-buy-buttom align-center"
                                     :class="platform === 'm' && data.buy_is_show == 0 ? 'hide':''">
                                    <!--                                <a class="goods-buy-buttom js_logsss_click_delegate_ps"-->
                                    <!--                                   :href="item.url_title || 'javascript:;'" :data-skus="item.goods_sn">{{data.buy_text||-->
                                    <!--                                    this.$root.languages['btn_buy_now'] ||'Buy Now'}}</a>-->
                                    <geshop-buynow :href="item.url_title" :skus="item.goods_sn"
                                                   :value="data.buy_text"></geshop-buynow>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div>
                    <span class="swiper-button swiper-button-next hide"></span><span
                    class="swiper-button swiper-button-prev hide"></span>
                </div>
                <div class="swiper-pagination hide"></div>
            </div>
        </div>

    </div>
</template>

<script>
export default {
    props: ['pid', 'theme', 'data'],
    data () {
        return {
            bodyWidth: 0,
            platform: 'pc',
            mySwiper: Object,
            mNavShow: false,
            pcNavShow: false,
            style_body: '',
            list: [
                { 1: 0 },
                { 1: 0 },
                { 1: 0 }
            ],
            left_img: {
                pc: 'https://geshopimg.logsss.com/uploads/ktIZfa7N1S68pLzAB2o5gTrC4jMV0beQ.png',
                pad: 'https://geshopimg.logsss.com/uploads/ktIZfa7N1S68pLzAB2o5gTrC4jMV0beQ.png',
                m: 'https://geshopimg.logsss.com/uploads/30HiEDfyl7ov8bmkw6xSqQY1dCjnZXKU.png'
            },
            left_active: {
                pc: 1,
                pad: 1,
                m: 1
            },
            defaultList: [
                {
                    goods_img: 'https://geshopimg.logsss.com/uploads/nxQshzC1wXTYy8BmHD6GE39vLWjciKaR.png',
                    goods_title: 'ZAFUL Asymmetric Striped Slit Shirt Dress - Dark Gree …',
                    shop_price: 16.9,
                    market_price: 20.56
                }
            ],
            scale: 264 / 352,
            imgLoadTime: 0
        };
    },
    watch: {
        bodyWidth (newValue) {
            if (newValue >= 1025) {
                // pc
                this.platform = 'pc';
            } else if (newValue <= 1024 && newValue >= 768) {
                // pad
                this.platform = 'pad';
            } else if (newValue <= 767) {
                // m
                this.platform = 'm';
            }
            if (typeof Swiper != 'undefined') {
                this.reInitSwiper();
            }

        }
    },
    computed: {
        isEditEnv () {
            return this.data.isEditEnv;
        }
    },

    created () {
        let dataLists = [];
        if (this.data.goodsSKU) {
            this.data.goodsSKU.toString().split(',').forEach(sku => {
                if (this.data.goodsInfo.hasOwnProperty(sku)) {
                    dataLists.push(this.data.goodsInfo[sku]);
                }
            });
            this.list = dataLists;
        }
        // let goodsSKUArr = this.data.goodsSKU.toString().split(',');
        // let col = this.platform == 'm' ? 2 : 3;
        // let Remainder = (goodsSKUArr.length > col && goodsSKUArr.length % col) || 0;
        // let _this = this;
        // if (goodsSKUArr) {
        //     this.list = goodsSKUArr.map(sku => {
        //         return this.data.goodsInfo[sku];
        //     });
        //     if (Remainder === 1) {
        //         _this.list.push(_this.list[0]);
        //         _this.list.push(_this.list[col]);
        //     } else if (Remainder === 2) {
        //         _this.list.push(_this.list[0]);
        //         _this.list.push(_this.list[col]);
        //     }
        // }
        ;

        this.left_img = {
            'pc': this.data.pc_link_image || 'https://geshopimg.logsss.com/uploads/ktIZfa7N1S68pLzAB2o5gTrC4jMV0beQ.png',
            'pad': this.data.pc_link_image || 'https://geshopimg.logsss.com/uploads/ktIZfa7N1S68pLzAB2o5gTrC4jMV0beQ.png',
            'm': this.data.m_link_image || 'https://geshopimg.logsss.com/uploads/30HiEDfyl7ov8bmkw6xSqQY1dCjnZXKU.png'
        };

        this.left_active = {
            'pc': this.data.pc_img_is_show || 1,
            'pad': this.data.pc_img_is_show || 1,
            'm': this.data.m_img_is_show || 1
        };

        if (!this.data.goodsSKU && this.left_active[this.platform] == '0') {
            this.list = [
                { 1: 0 },
                { 1: 0 },
                { 1: 0 },
                { 1: 0 }
            ];
        }
    },
    methods: {
        debounce (fn, delay) {
            let args = arguments;
            let context = this;
            let timer = null;

            return function () {
                if (timer) {
                    clearTimeout(timer);

                    timer = setTimeout(function () {
                        fn.apply(context, args);
                    }, delay);
                } else {
                    timer = setTimeout(function () {
                        fn.apply(context, args);
                    }, delay);
                }
            };
        },
        initSwiper (slideNum, spaceNum, platfromValue) {
            this.$nextTick(() => {
                if (this.mySwiper.destroy) {
                    this.mySwiper.destroy(false);
                    this.mySwiper = {};
                }
                let pagination = null;
                let nextButton = null;
                let prevButton = null;
                let loopState = false;
                let _this = this;
                let platfrom = this.platform;
                if (platfrom !== 'm' && this.left_active[this.platform] == '0') {
                    slideNum += 1;
                }

                if (platfrom === 'pc') {
                    $(this.$el).find('.swiper-pagination').addClass('hide');
                    if (this.data.goodsInfo) {
                        if (Object.keys(this.data.goodsInfo).length > slideNum) {
                            nextButton = this.$el.querySelector('.swiper-button-next');
                            prevButton = this.$el.querySelector('.swiper-button-prev');
                            loopState = true;
                            $(this.$el).find('.swiper-button-next,.swiper-button-prev').removeClass('hide');
                        } else {
                            this.pcNavShow = false;
                            $(this.$el).find('.swiper-button-next,.swiper-button-prev').addClass('hide');
                        }
                    }
                    this.mySwiper = new Swiper(this.$el.querySelector('.swiper-container'), {
                        spaceBetween: spaceNum,
                        slidesPerView: slideNum,
                        slidesPerGroup: slideNum,
                        loopedSlides: slideNum,
                        loop: loopState,
                        loopFillGroupWithBlank: true,
                        lazyLoading: false,
                        lazyLoadingInPrevNext: true,
                        lazyLoadingOnTransitionStart: true,
                        watchSlidesVisibility: true,
                        loadPrevNextAmount: slideNum,
                        navigation: {
                            nextEl: nextButton,
                            prevEl: prevButton
                        },
                        on: {
                            init: function (swiper) {
                                _this.updateImageHeight();
                                _this.updateLeftHeight();
                                if (_this.data.goodsInfo && slideNum == Object.keys(_this.data.goodsInfo).length) {
                                    swiper.wrapper[0].style.transform = 'none';
                                }
                            },
                            slideChangeTransitionEnd: function () {
                                if (_this.mySwiper.lazy) {
                                    _this.mySwiper.lazy.load();
                                }
                            },
                            lazyImageReady: function () {
                                _this.updateLeftHeight();
                            }
                        }
                    });
                } else {
                    pagination = this.$el.querySelector('.swiper-pagination');
                    $(this.$el).find('.swiper-button-next,.swiper-button-prev').addClass('hide');
                    // let slidesPerGroup;
                    if (platfrom === 'pad') {
                        if (this.data.goodsInfo) {
                            if (Object.keys(this.data.goodsInfo).length > slideNum) {
                                $(this.$el).find('.swiper-pagination').removeClass('hide');
                            } else {
                                $(this.$el).find('.swiper-pagination').addClass('hide');
                            }
                        }
                        // slidesPerGroup = 3;
                        if (this.data.goodsInfo && Object.keys(this.data.goodsInfo).length > slideNum) {
                            loopState = true;
                        } else {
                            this.mNavShow = false;
                        }
                        this.mySwiper = new Swiper(this.$el.querySelector('.swiper-container'), {
                            slidesPerGroup: slideNum,
                            spaceBetween: spaceNum,
                            slidesPerView: slideNum,
                            observer: true,
                            loop: loopState,
                            loopFillGroupWithBlank: true,
                            lazyLoading: false,
                            watchSlidesVisibility: true,
                            iOSEdgeSwipeDetection: true,
                            iOSEdgeSwipeThreshold: 50,
                            touchRatio: 0.5,
                            pagination: {
                                el: pagination,
                                clickable: true
                            },
                            on: {
                                init: function () {
                                    _this.updateImageHeight();
                                    _this.updateLeftHeight();
                                },
                                slideChangeTransitionEnd: function () {
                                    if (_this.mySwiper.lazy) {
                                        _this.mySwiper.lazy.load();
                                    }
                                },
                                lazyImageReady: function () {
                                    _this.updateLeftHeight();
                                }
                            }
                        });
                    } else if (platfrom === 'm') {
                        if (this.data.goodsInfo && Object.keys(this.data.goodsInfo).length > slideNum) {
                            loopState = true;
                            $(this.$el).find('.swiper-pagination').removeClass('hide');
                        } else {
                            this.mNavShow = false;
                            $(this.$el).find('.swiper-pagination').addClass('hide');
                        }
                        this.mySwiper = new Swiper(this.$el.querySelector('.swiper-container'), {
                            slidesPerGroup: slideNum,
                            spaceBetween: spaceNum,
                            slidesPerView: slideNum,
                            loop: loopState,
                            loopFillGroupWithBlank: true,
                            observer: true,
                            lazyLoading: false,
                            watchSlidesVisibility: true,
                            pagination: {
                                el: pagination,
                                clickable: true
                            },
                            on: {
                                init: function () {
                                    _this.updateImageHeight();
                                },
                                slideChangeTransitionEnd: function () {
                                    if (_this.mySwiper.lazy) {
                                        _this.mySwiper.lazy.load();
                                    }
                                }
                                // lazyImageReady:function(){
                                //     _this.updateLeftHeight();
                                // }
                            }
                        });
                    }
                    if (this.isEditEnv === 1 && window.GS_GOODS_LAZY_FN) {
                        window.GS_GOODS_LAZY_FN($('.geshop-U000182-model2-body img.js_gdexp_lazy'));
                    }
                    this.updateLeftHeight();
                    this.mySwiper.update(true);
                }
            });

            if (this.isEditEnv === 1) {
                $('.swiper-container .swiper-slide').each((index, item) => {
                    $(item).attr('data-src', 'src');
                });
            }
        },

        reInitSwiper () {
            let _this = this;
            let platform = _this.platform;
            let colArr = {
                pc: 3,
                pad: 3,
                m: 2
            };
            let rightArr = {
                pc: 16,
                pad: 14,
                m: 10
            };

            _this.initSwiper(colArr[platform], rightArr[platform], platform);
            /* if (sessionStorage.getItem('gs_platform') == 'pc') {
                // this.list = this.data.goodsInfo || this.defaultList
                this.initSwiper(3, 16, 'pc')
                this.pcNavShow = true
                this.mNavShow = false
            } else if (sessionStorage.getItem('gs_platform') == 'pad') {
                this.initSwiper(3, 16, 'pad')
                this.mNavShow = true
                this.pcNavShow = false
            } else if (sessionStorage.getItem('gs_platform') == 'wap') {
                this.initSwiper(2, 15, 'm')
                this.mNavShow = true
                this.pcNavShow = false
            } */
        },
        setPlatform () {
            let platform_type = typeof GLOBAL != 'undefined' && typeof GLOBAL.util.getPlatform() != 'undefined' ? GLOBAL.util.getPlatform() : 2;
            if (platform_type === 1) {
                sessionStorage.setItem('gs_platform', 'wap');
            } else if (platform_type === 2) {
                sessionStorage.setItem('gs_platform', 'pc');
            } else if (platform_type === 3) {
                sessionStorage.setItem('gs_platform', 'pad');
            }
        },
        updateLeftHeight (type) {
            let _this = this;
            if (!type) {
                this.imgLoadTime = 0;
            }
            if (_this.left_active[_this.platform] == 1) {
                let imgHeight = _this.$el.querySelector('.list-item .geshop-dl-image-goods').offsetHeight;
                if (imgHeight === 0) {
                    if (!this.imgLoadTime) {
                        if (updateTimeOut) clearTimeout(updateTimeOut);
                        let updateTimeOut = setTimeout(() => {
                            this.imgLoadTime = 1;
                            this.updateLeftHeight('reload');
                        }, 500);
                    }
                    return false;
                }
                if (_this.left_active[_this.platform] == 1) {
                    _this.$el.querySelector('.default-left-image-box img').style.height = _this.$el.querySelector('.list-item').offsetHeight + 'px';
                }
            }
        },
        updateImageHeight () {
            if (this.$el.querySelector('.swiper-slide').offsetWidth) {
                let imageHeight = (this.$el.querySelector('.swiper-slide').offsetWidth - 24) / this.scale;
                let imageList = this.$el.querySelectorAll('.item-image');
                for (let item of imageList) {
                    item.style.height = imageHeight + 'px';
                    item.querySelector('img').style.maxHeight = imageHeight + 'px';
                }
            }
        }
    },
    async mounted () {
        const _this = this;
        // if (_this.isEditEnv == 1) {
        //     let platform = sessionStorage.getItem('gs_platform') || 'pc';
        //     sessionStorage.setItem('gs_platform', platform);
        //
        //     //监听当前选择的平台
        //     window.addEventListener('storage', () => {
        //         _this.reInitSwiper()
        //         _this.bodyWidth = document.body.clientWidth;
        //     }, false)
        // }

        this.setPlatform();
        this.bodyWidth = document.body.clientWidth;
        // 监听窗口拖放
        window.addEventListener('resize', this.debounce(() => {
            _this.bodyWidth = document.body.clientWidth;
            _this.setPlatform();
        }, 200), false);

        let staticDomain = typeof GESHOP_STATIC == 'undefined' ? '' : GESHOP_STATIC;
        loadCss(staticDomain + '/resources/javascripts/library/swiper/swiper.4.5.min.css');
        $LAB.script(staticDomain + '/resources/javascripts/library/swiper/swiper.4.5.min.js').wait(function () {
            _this.reInitSwiper();

            /* if (_this.isEditEnv === 0) {
                            let platform_type = typeof GLOBAL.util.getPlatform() != 'undefined' ? GLOBAL.util.getPlatform() : 2;
                            if (platform_type === 2) {
                                _this.initSwiper(3, 16, "pc");
                                _this.pcNavShow = true;
                                _this.mNavShow = false;
                            } else if (platform_type === 3) {
                                _this.initSwiper(3, 10, "pad");
                                _this.pcNavShow = false;
                                _this.mNavShow = true;
                            } else if (platform_type === 1) {
                                _this.initSwiper(2, 15, "m");
                                _this.pcNavShow = false;
                                _this.mNavShow = true;
                            }
                        } else {
                            let platform = _this.platform, col, right;
                            let colArr = {
                                pc: 3,
                                pad: 3,
                                m: 2
                            };
                            let rightArr = {
                                pc: 16,
                                pad: 14,
                                m: 10
                            };

                            _this.initSwiper(colArr[platform], rightArr[platform], platform);
                        } */
        });
        // 初始化页面宽度
        const that = this;
        that.$nextTick(() => {
            // 预览页 m = 1 pc =2 ipad = 3
            let cw = document.body.clientWidth;
            if (that.isEditEnv === 0) {
                try {
                    const platformt = typeof GLOBAL.util.getPlatform() != 'undefined' ? GLOBAL.util.getPlatform() : 2;
                    if (platformt == 1) {
                        // m
                        that.platform = 'm';
                    } else if (platformt == 2) {
                        // pc
                        that.platform = 'pc';
                    } else if (platformt == 3) {
                        // pad
                        that.platform = 'pad';
                    }
                } catch (e) {
                }
            } else {
                // 编辑模式
                if (cw >= 1025) {
                    // pc
                    that.platform = 'pc';
                } else if (cw <= 1024 && cw >= 768) {
                    // pad
                    that.platform = 'pad';
                } else if (cw <= 767) {
                    // m
                    that.platform = 'm';
                }
            }

            // 懒加载
            if (window.GS_GOODS_LAZY_FN) {
                window.GS_GOODS_LAZY_FN($('.geshop-U000182-model2-body img.js_gdexp_lazy'));
            }
        });
    },
    updated () {
    }
};
</script>

<style lang="less" scoped>
    .geshop-U000182-model2-body {
        box-sizing: border-box;
        padding: 30px;
        max-width: 1200px;
        margin: auto;
        overflow: hidden;
        background-color: #FFFFFF;

        .hide {
            display: none !important;
        }

        .wrap {
            display: flex;
            position: relative;
            justify-content: space-between;
            overflow: hidden;

            .swiper-container {
                flex: 1;
                max-height: 700px;

                .swiper-slide {
                    /*padding: 12px;*/
                    background-color: #fff;

                    img {
                        width: 100%;
                        opacity: 1 !important;
                        display: block !important;
                    }

                    .cursorDefault {
                        cursor: default;
                    }

                    .list-item {
                        padding: 0;
                        overflow: hidden;
                        /*padding: 12px;*/
                    }
                }

                .swiper-button {
                    width: 40px;
                    height: 65px;
                    opacity: 0.7;
                    margin-top: -30px !important;
                    background-size: 100%;
                    transition: all 0.3s;

                    &:hover {
                        opacity: 1;
                        transition: all 0.3s;
                    }

                    &.swiper-button-prev {
                        background-image: url(https://geshopimg.logsss.com/uploads/c2Ipt87XRzgOy3KavesirQCbWMmnVZGF.png);
                        left: 0;
                    }

                    &.swiper-button-next {
                        right: 0;
                        background-image: url(https://geshopimg.logsss.com/uploads/xhOnwqUI85BJSlVfHFbykMcmip2u3YD7.png)
                    }

                }

            }

        }

        .inline-block {
            display: inline-block;
        }

        .item-content {
            padding: 0 12px;
        }

        .item_blank_img {
            display: block;
        }

        .item-buy-buttom {
            font-size: 0;
            line-height: 1;
        }

        .geshop-discount-default {
            position: absolute;
            right: 0px;
            top: 0px;
            border-radius: 50px;
            overflow: hidden;
            z-index: 1;

            > span {
                display: table;
                width: 100%;
                height: 100%;

                > label {
                    display: table-cell;
                    text-align: center;
                    vertical-align: middle;
                    font-size: 16px;
                    line-height: 0.9em;
                    font-weight: bold;

                    > i {
                        font-size: 12px;
                        font-style: normal;
                        font-weight: 400;
                    }
                }
            }
        }
    }

    .item-image {
        font-size: 0;
        vertical-align: middle;
        position: relative;
        height: 250px;
        overflow: hidden;
        /*display: table-cell;*/
        display: -webkit-box;
        display: -ms-flexbox;
        display: flex;
        webkit-box-align: center;
        -ms-flex-align: center;
        align-items: center;
    }

    .default-left-image-box {
        font-size: 0;

        img {
            width: 100%;
            height: 100%;
            max-width: 100%;
            max-height: 100%;
        }

        a {
            display: block;
        }
    }

    .item-title {
        overflow: hidden;
        text-overflow: ellipsis;
        white-space: nowrap;
        padding: 12px 0 4px;
    }

    .item-shop-market {
        margin-bottom: 8px;
    }

    .item-shop {
        .geshop-shop-price {
            margin-right: 4px;
            font-weight: bold;
            font-family: LatoBold, Lato, Arial, sans-serif;
        }
    }

    .align-center {
        text-align: center;
    }

    .goods-buy-buttom {
        display: inline-block;
        text-align: center;
        border-radius: 2px;
        font-size: 16px;
        max-width: 100%;
        width: 100%;
        font-weight: 600;
        overflow: hidden;
    }

    @media (max-width: 767px) {
        .geshop-U000182-model2-body {
            padding: 10px;

            .wrap {
                padding: 0;
                display: block;
            }

            .default-left-image-box {
                margin-bottom: 10px;
                /*margin: 10px 0;*/

                img {
                    height: auto !important
                }
            }

            .item-shop-market {
                margin-bottom: 9px;
            }

            .item-buy-buttom {
                margin-bottom: 10px;
            }

            .item-content {
                padding: 0 8px;
            }
        }

        .goods-buy-buttom {
            /*width: 148px;*/
            font-size: 14px;
            height: 30px;
            line-height: 30px;
        }
    }

    @media (min-width: 768px) and (max-width: 1024px) {

        .geshop-U000182-model2-body {
            /*padding: 20px 30px;*/

            .wrap {
                /*padding: 20px 30px;*/

                .default-left-image-box {
                    max-width: 24%;
                    width: 229px;
                    height: 100%;
                    margin-right: 16px;
                }
            }

            .item-buy-buttom {
                margin-bottom: 16px;
            }

            .swiper-pagination {

            }
        }

        .goods-buy-buttom {
            /*width: 205px;*/
            height: 36px;
            line-height: 36px;
        }
    }

    @media (min-width: 1025px) {

        .geshop-U000182-model2-body {
            .wrap {
                /*padding: 0 100px;*/

                .default-left-image-box {
                    max-width: 24%;
                    width: 273px;
                    height: 100%;
                    margin-right: 16px;
                }
            }

            .item-buy-buttom {
                margin-bottom: 16px;
            }
        }

        .goods-buy-buttom {
            /*width: 249px;*/
            height: 36px;
            line-height: 36px;
        }
    }
</style>
<style lang="less">
    .geshop-U000182-model2-body {
        .wrap {
            position: relative;

            .swiper-pagination-bullet {
                background: #c9c6c6;

                &.swiper-pagination-bullet-active {
                    background: #000;
                }
            }
        }

        .geshop-components-discount {
            span {
                > label {
                    font-size: 15px !important;
                    line-height: 1.1em !important;

                    > i {
                        font-size: 12px !important;
                    }
                }

            }
        }
    }

    @media (max-width: 767px) {
        .geshop-U000182-model2-body {
            .geshop-components-discount {
                span {
                    > label {
                        font-size: 14px !important;

                        > i {
                            font-size: 12px !important;
                        }
                    }

                }
            }
        }
    }
</style>
