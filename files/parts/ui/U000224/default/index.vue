<template>
    <div class="geshop-u000224-default-body" :class="`is-${media_platform}`">
        <div class="swiper-container">
            <div class="swiper-wrapper">
                <template v-for="(item, index) in list">
                    <geshop-analytics-href
                        class="swiper-slide"
                        :target="openType"
                        :key="`${item.goods_sn}-${index}`"
                        :href="item.url_title"
                        :sku="item.goods_sn"
                        :cate="item.cateid"
                        :warehouse="item.warehousecode"
                        :goods_id="item.goods_id"
                        :index="index"
                        :mrlc="data.glb_ubcta_mrlc"
                        :zt="1"
                        pm="mr">

                        <div class="list-item">
                            <!--折扣标-->
                            <div class="item-discount" v-if="item.discount > 0">
                                <template v-if="data.discount_type == 3">
                                    <div class="dl-discount">-{{ item.discount }}%</div>
                                </template>
                                <template v-else>
                                    <geshop-discount :value="item.discount" :percent="item.discount"/>
                                </template>
                            </div>
                            <div v-if="data.cornermark_is_show === 'block'" class="cornermark"></div>
                            <div class="item-image">
                                <div class="geshop-dl-image-goods">
                                    <geshop-image-goods
                                        :src="item.goods_img"
                                        :sku="item.goods_sn"
                                        :mrlc="data.glb_ubcta_mrlc">
                                    </geshop-image-goods>
                                </div>
                            </div>

                            <div class="item-info">
                                <!--sku标题-->
                                <div class="item-title">
                                    <span>{{ item.goods_title || 'ZAFUL Asymmetric Striped Slit Shirt Dress - Dark Gree …' }}</span>
                                </div>

                                <div class="sale-mark">{{ $root.languages.sale }}</div>
                                <div class="item-shop-market">
                                    <!--销售价-->
                                    <div class="item-shop">
                                        <geshop-shop-price :value="item.shop_price"/>
                                        <template v-if="item.market_price > item.shop_price">
                                            <span style="color: #999">(
                                            <geshop-market-price :value="item.market_price"></geshop-market-price>
                                            )
                                            </span>
                                        </template>
                                    </div>
                                </div>

                                <div class="botton-info clearfix">
                                    <div class="rate" v-if="item.goods_grade">
                                        <div class="all">
                                            <span v-for="item in 5" :key="item.id" class="gs-icon gs-icon-dl-star"></span>
                                        </div>
                                        <div class="score" :style="'width: '+ (item.goods_grade /5)*100 +'%' ">
                                            <span v-for="item in 5" :key="item.id" class="gs-icon gs-icon-dl-star"></span>
                                        </div>
                                    </div>
                                    <span
                                        class="reviews"
                                        v-if="item.goods_reviews > 0">
                                        {{item.goods_reviews}} {{ $root.languages.reviews }}
                                    </span>
                                </div>
                            </div>
                        </div>

                    </geshop-analytics-href>
                </template>
            </div>
            <div class="swiper-pagination" v-show="mNavShow"></div>
            <div class="swiper-button swiper-button-prev" v-show="pcNavShow"></div>
            <div class="swiper-button swiper-button-next" v-show="pcNavShow"></div>
        </div>
    </div>
</template>

<script>
export default {
    props: ['data'],
    data () {
        return {
            // 远端模块是否加载完
            remote_modules: {
                swiper: false,
                data: false
            },
            mySwiper: Object,
            // 商品数据
            list: [
                { goods_sn: '', discount: 0 },
                { goods_sn: '', discount: 0 },
                { goods_sn: '', discount: 0 },
                { goods_sn: '', discount: 0 },
                { goods_sn: '', discount: 0 },
                { goods_sn: '', discount: 0 },
                { goods_sn: '', discount: 0 }
            ],
            pcNavShow: false,
            mNavShow: false,
            timeId: '',
            loadImg: 'https://geshopcss.logsss.com/imagecache/geshop/resources/images/dl/loading.gif',
            plan: 'a' // 接入BTS实验，默认为 a 页面。 [a/b]
        };
    },

    computed: {
        isEditEnv () {
            return this.data.isEditEnv;
        },
        // 媒体查询值
        media_platform () {
            return this.$store.state.dresslily.media_platform;
        },
        openType () {
            return this.media_platform === 'pc' || this.media_platform === 'pad' ? this.$root.data.is_open_new : this.$root.data.is_open_new_m;
        },
        // 第三方配置的layout值
        jetlore_layout () {
            return this.$root.data.jetlore_layout || '';
        }
    },

    watch: {
        // 媒体查询
        media_platform (newVal) {
            if (newVal == 'pc') {
                this.initSwiper(6, 20, 'pc');
            }
            if (newVal == 'pad') {
                this.initSwiper(4, 10, 'pad');
            }
            if (newVal == 'wap') {
                this.initSwiper(2, 15, 'm');
            }
        },

        // 远端模块变更
        remote_modules: {
            deep: true,
            handler (val) {
                // 当数据和 swiper 都加载完则初始化 swiper
                if (val.data == true && val.swiper == true) {
                    // 个数
                    const itemsMap = { pc: 6, pad: 4, wap: 2 };
                    // 间隔
                    const gapMap = { pc: 20, pad: 10, wap: 15 };
                    // 端
                    const platform = this.media_platform;
                    // 初始化 swiper
                    this.initSwiper(itemsMap[platform], gapMap[platform], platform);
                }
            }
        }
    },

    methods: {

        /**
         * 获取自填SKU的商品数据
         * @returns {Array}
         */
        get_list_by_sku () {
            // 因为传过来的是object，无序变量，需要手动的做个排序
            if (this.data.goodsSKU) {
                const list = [];
                this.data.goodsSKU.toString().split(',').map(sku => {
                    // PS: v1.8.9 D网做了网红链接的需求，所以 goodsSKU 数组中的值，在 goodsInfo 里面不一定存在，所以要做判断
                    if (this.data.goodsInfo.hasOwnProperty(sku)) {
                        list.push(this.data.goodsInfo[sku]);
                    }
                });
                return list;
            } else {
                return [];
            }
        },

        /**
         * 获取 jetlore 智能推荐算法的商品数据
         * @returns {Array}
         */
        async get_list_by_jetlore () {
            return new Promise((resolve, reject) => {
                // 站点 jetlore 初始化方法
                this.$store.dispatch('jetlore/ready', () => {
                    try {
                        JL_RANKER.get_layout({
                            layout: this.jetlore_layout,
                            jl_secure: true,
                            jl_add_info: true,
                            jl_response: 'full',
                            jl_backfill: 'yes',
                            labels: 'cart_coudanqu'
                        }, (res) => {
                            if (res.sections[0]) {
                                // 处理字段的差异性, 坑爹JETLORE返回的是美分，需要除100
                                const list = res.sections[0].products.map(x => {
                                    return {
                                        goods_sn: x.id,
                                        goods_title: x.title,
                                        goods_img: x.img,
                                        url_title: x.url,
                                        shop_price: (x.current_price / 100).toFixed(2),
                                        market_price: (x.original_price / 100).toFixed(2),
                                        discount: 0
                                    };
                                });
                                resolve(list);
                            } else {
                                resolve([]);
                            }
                        });
                    } catch (err) {
                        reject(err);
                    }
                });
            });
        },

        /**
         * 初始化 swiper 组件
         * @param {string} swiperCount 展示个数
         * @param {string} gap 间距
         * @param {string} platform 平台, pc,pad,wap
         */
        initSwiper (swiperCount, gap, platform) {
            const _this = this;

            this.$nextTick(() => {
                if (_this.mySwiper.destroy) {
                    _this.mySwiper.destroy(false);
                    _this.mySwiper = {};
                }
                let nextButton = null;
                let prevButton = null;
                let loopFlag = false;

                if (platform === 'pc') {
                    if (this.list.length > 6) {
                        nextButton = this.$el.querySelector('.swiper-button-next');
                        prevButton = this.$el.querySelector('.swiper-button-prev');
                        loopFlag = true;
                    } else {
                        this.pcNavShow = false;
                    }

                    _this.mySwiper = new Swiper(_this.$el.querySelector('.swiper-container'), {
                        spaceBetween: gap,
                        slidesPerGroup: swiperCount,
                        slidesPerView: swiperCount,
                        loop: loopFlag,
                        loopFillGroupWithBlank: true,
                        lazyLoading: true,
                        navigation: {
                            nextEl: nextButton,
                            prevEl: prevButton
                        },
                        on: {
                            // 初始化之后执行
                            init () {
                                _this.$store.dispatch('global/async_goods_init_v2', _this);
                                // 图片增加曝光埋点
                                $(document).triggerHandler('logsss_explore', {
                                    $elList: $(_this.$el).find('.js_logsss_browser')
                                });
                            },
                            slideChangeTransitionEnd () {
                                _this.$store.dispatch('global/async_goods_init_v2', _this);
                            }
                        }
                    });
                } else {
                    let slidesPerGroup = 2;
                    if (platform === 'pad') {
                        if (_this.list.length <= 4) {
                            _this.mNavShow = false;
                        } else {
                            loopFlag = true;
                        }
                    } else if (platform === 'wap') {
                        // 商品懒加载
                        this.$store.dispatch('global/async_goods_init', this);
                        return false;
                        // if (_this.list.length <= 2) {
                        //     _this.mNavShow = false;
                        // } else {
                        //     loopFlag = true;
                        // }
                    }

                    _this.mySwiper = new Swiper(_this.$el.querySelector('.swiper-container'), {
                        slidesPerView: swiperCount,
                        slidesPerGroup: slidesPerGroup,
                        spaceBetween: gap,
                        loop: loopFlag,
                        loopFillGroupWithBlank: true,
                        observer: true,
                        observeParents: true,
                        pagination: {
                            el: '.swiper-pagination'
                        },
                        on: {
                            // 初始化之后执行
                            init () {
                                _this.$store.dispatch('global/async_goods_init_v2', _this);
                                // 图片增加曝光埋点
                                $(document).triggerHandler('logsss_explore', {
                                    $elList: $(_this.$el).find('.js_logsss_browser')
                                });
                            },
                            slideChangeTransitionEnd: function () {
                                _this.$store.dispatch('global/async_goods_init_v2', _this);
                            }
                        }
                    });
                }
            });
        }
    },

    async created () {
        // 加载样式
        const css_url = `${window.GESHOP_STATIC}/resources/javascripts/library/swiper/swiper.4.5.min.css`;
        loadCss(css_url);

        // 获取自填的 SKU 的数据
        let list = await this.get_list_by_sku();

        // 根据 COOKIE 获取 BTS 实验结果
        try {
            if ($('html').hasClass('bts-mlnfy-b')) {
                this.plan = 'b';
                // 获取 JETLORE 的数据
                const list_b = await this.get_list_by_jetlore();
                if (list_b.length > 0) {
                    list = list_b;
                }
            }
        } catch (err) {
        }

        // 装修页填充数据
        if (this.isEditEnv && list.length <= 0) {
            list = [
                { goods_sn: '' },
                { goods_sn: '' },
                { goods_sn: '' },
                { goods_sn: '' },
                { goods_sn: '' },
                { goods_sn: '' },
                { goods_sn: '' }
            ];
        }
        ;
        this.list = list;
        this.remote_modules.data = true;
    },

    async mounted () {
        // 图片初始化
        this.$store.dispatch('global/loaded', this);

        // 加载 swiper 文件
        const js_url = `${window.GESHOP_STATIC}/resources/javascripts/library/swiper/swiper.4.5.min.js`;
        $LAB.script(js_url).wait(() => {
            this.remote_modules.swiper = true;

            // 是否展示导航
            if (this.media_platform === 'pc') {
                this.pcNavShow = true;
                this.mNavShow = false;
            } else {
                this.pcNavShow = false;
                this.mNavShow = true;
            }
        });
    }
};
</script>

<style lang="less" scoped>

    .geshop-u000224-default-body {

        .swiper-container {

            .swiper-slide {

                img {
                    width: 100%;
                    opacity: 1 !important;
                    display: block !important;
                }

                .swiper-pagination {
                    bottom: 18px;

                    .swiper-pagination-bullet {
                        background: #c9c6c6;

                        &.swiper-pagination-bullet-active {
                            background: #000;
                        }
                    }
                }
            }

            .item-discount {
                .dl-discount {
                    position: absolute;
                    right: 6px;
                    top: 6px;
                    width: 46px;
                    height: 20px;
                    line-height: 20px;
                    text-align: center;
                }
            }

            .cornermark {
                position: absolute;
                width: 88px;
                height: 32px;
                line-height: 32px;
                background-size: 100% 100%;
                text-align: center;
                z-index: 1;
            }

            .geshop-dl-image-goods {
                display: flex;
                align-items: center;
                align-content: center;

                img {
                    max-width: 100%;
                }
            }

            .geshop-zaful-discount {
                width: 46px;
                height: 46px;
                border-radius: 25px;
                right: 0 !important;
                top: 0 !important;

                span {
                    background: green;

                    label {
                        line-height: normal;
                    }
                }
            }

            .item-info {
                .item-title {
                    display: block;
                    width: 100%;
                    height: 13px;
                    font-size: 13px;
                    line-height: 13px;
                    margin-top: 16px;
                    margin-bottom: 10px;
                    overflow: hidden;
                    text-overflow: ellipsis;
                    white-space: nowrap;
                }

                .sale-mark {
                    display: inline-block;
                    width: auto;
                    padding: 0px 4px;
                    height: 20px;
                    line-height: 20px;
                    font-size: 13px;
                    text-align: center;
                }
            }

            .geshop-shop-price {
                font-size: 18px;
                font-weight: bold;
            }

            .geshop-market-price {
                font-size: 13px;
            }

            .gs-icon {
                font-size: 13px;
            }

            // 评分
            .rate {
                float: left;
                position: relative;
                margin-right: 4px;
                width: 81px;
                height: 20px;

                .all {
                    position: absolute;

                    .gs-icon {
                        margin-right: 3px;
                        color: #bcbcbc;
                    }
                }

                .score {
                    position: absolute;
                    overflow: hidden;

                    .gs-icon {
                        margin-right: 3px;
                    }
                }

                .reviews {
                    float: left;
                }
            }

            .swiper-button {
                width: 40px;
                height: 65px;
                opacity: 0.7;
                margin-top: -90px;
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
                    background-image: url(https://geshopimg.logsss.com/uploads/xhOnwqUI85BJSlVfHFbykMcmip2u3YD7.png);
                }
            }
        }

        &.is-pc {
            a.swiper-slide {
                max-width: 16.66%;
            }
        }

        &.is-pad {

        }

        &.is-wap {
            .swiper-wrapper {
                -webkit-box-orient: horizontal;
                -webkit-box-direction: normal;
                -ms-flex-flow: row wrap;
                flex-flow: row wrap;
            }

            .swiper-slide {
                width: 50% !important;
                margin-right: 0 !important;
                padding: 0 7.5px;
                box-sizing: border-box;
            }

            .swiper-pagination, .swiper-button-next, .swiper-button-prev {
                display: none;
            }

            .item-shop {
                white-space: nowrap;
            }
        }
    }
</style>

<style lang="less">
    .geshop-u000224-default-body {
        .swiper-pagination-bullet {
            background: #c9c6c6;

            &.swiper-pagination-bullet-active {
                background: #000;
            }
        }
    }
</style>
