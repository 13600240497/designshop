<template>
    <div :class="['geshop_U000252_template1_wrapper']" v-if="is_component_show" ref="component">
        <ul class="wrap">
            <li v-for="(item, index) in (list && list.length > 0 ? list : 4)" :key="item.goods_sn + '_' + index"
                class="goods list-item">
                <div class="list_item">
                    <div class="item_image">
                        <geshop-analytics-href
                            v-if="item.goods_number > 0"
                            :href="item.url_title"
                            :sku="item.goods_sn"
                            :cate="item.cateid"
                            :warehouse="item.warehousecode"
                            :goods_id="String(item.goods_id)">
                            <geshop-image-goods :src="item.goods_img"></geshop-image-goods>
                        </geshop-analytics-href>

                        <geshop-image-goods v-else :src="item.goods_img"></geshop-image-goods>

                        <geshop-button-quick-view class="item_view"
                                                  :item="item"
                                                  :index="index"
                                                  v-if="item.goods_number > 0"
                                                  :url_quick="item.url_quick">
                            <span>+{{ $lang('quick_view') }}</span>
                        </geshop-button-quick-view>

                        <!--sold out-->
                        <geshop-soldout class="item_soldout" :visible="item.goods_number <= 0"></geshop-soldout>

                        <!--折扣标-->
                        <geshop-discount :value="item.discount"></geshop-discount>

                    </div>

                    <!--sku标题-->
                    <div class="item_title">
                        <geshop-analytics-href
                            :item="item"
                            :index="index">
                            {{ item.goods_title }}
                        </geshop-analytics-href>
<!--                        <a :href="item.url_title">{{ item.goods_title }}</a>-->
                    </div>

                    <div class="item_content">
                        <!--销售价-->
                        <div class="item_shop inline-block">
                            <geshop-shop-price :value="item.shop_price"></geshop-shop-price>
                        </div>

                        <!--市场价-->
                        <div class="item_market inline-block" v-if="market_price_active == 1">
                            <geshop-market-price v-if="item.show_market_price"
                                                 :value="item.market_price"></geshop-market-price>
                        </div>
                    </div>

                    <!--营销信息-->
                    <geshop-promotion :value="item.promotions"></geshop-promotion>
                </div>
            </li>
        </ul>
        <!--loading-->
        <div class="loading-more" v-show="view_more_loading" style="padding: 3%;text-align: center;">
            <img src="https://css.zafcdn.com/imagecache/MZF/images/loading_zf.gif">
        </div>
    </div>
</template>

<script>

export default {
    name: 'template1',
    props: ['data', 'pid'],
    data () {
        return {
            list: [], // 商品列表
            editList: [
                {
                    'goods_title': 'ZAFUL Fleece Vest And Corduroy JackJackJackJack',
                    'shop_price': '0.00',
                    'market_price': '0.00',
                    'promotions': ['Buy 1 Get 10% off']
                },
                {
                    'goods_title': 'ZAFUL Fleece Vest And Corduroy JackJackJackJack',
                    'shop_price': '0.00',
                    'market_price': '0.00',
                    'promotions': ['Buy 1 Get 10% off']
                },
                {
                    'goods_title': 'ZAFUL Fleece Vest And Corduroy JackJackJackJack',
                    'shop_price': '0.00',
                    'market_price': '0.00',
                    'promotions': ['Buy 1 Get 10% off']
                },
                {
                    'goods_title': 'ZAFUL Fleece Vest And Corduroy JackJackJackJack',
                    'shop_price': '0.00',
                    'market_price': '0.00',
                    'promotions': ['Buy 1 Get 10% off']
                }
            ], // 默认商品列表
            market_price_active: 1, // 市场价是否显示
            view_more_loading: false, // 加载更多图片
            pagination: {
                page_no: 1,
                has_more: true,
                can_request: false // 是否允许请求
            },
            imgFilter: true // 懒加载过滤已加载的
        };
    },
    mounted () {
        this.init();
        if (Number(this.$root.data.is_pagination) === 1) {
            this.scrollEvent();
        }
    },
    computed: {
        is_component_show () {
            return GESHOP_PAGE_TYPE == 1 || (this.list && this.list.length > 0);
        }
    },
    methods: {
        /**
         * 初始加载
         * @returns {Promise<void>}
         */
        async init () {
            try {
                await this.handleList();
            } catch (err) {
            }
            // 装修页
            if (window.GESHOP_PAGE_TYPE == 1 && this.list.length == 0) {
                this.list = [...this.editList];
            }
            this.view_more_loading = false;
            // 去处loading
            this.$store.dispatch('global/loaded', this);
            // 页面初始化
            this.$store.dispatch('global/async_goods_init', this);
        },
        /**
         * 请求商品运营平台数据
         * @returns {Promise<void>}
         */
        async handleList () {
            this.view_more_loading = true;
            // 异步获取商品运营平台数据
            let res = await this.$GESHOP_DATA_FN(this, { page_no: this.pagination.page_no });
            this.view_more_loading = false;
            this.pagination.can_request = true;
            //
            const { goods_list, pagination } = res.data;
            const filterGoodsInfo = [...goods_list].map(item => {
                // 如果市场价大于销售价，则不展示市场价
                item['show_market_price'] = Number(item.shop_price) < Number(item.market_price);
                return item;
            });
            this.list = [...this.list, ...filterGoodsInfo];
            // 页面初始化
            this.$store.dispatch('global/async_goods_init', this);

            // 判断是否存在更多分页
            const page_max = Math.ceil(pagination.total_count / pagination.page_size);
            if (Number(pagination.page_num) + 1 <= page_max) {
                this.pagination.page_no += 1;
            } else {
                this.pagination.has_more = false;
            }
        },
        /**
         * 滚动监听
         */
        scrollEvent () {
            const _self = this;
            // 兼容装修页加载数据
            // const scrollTarget = GESHOP_PAGE_TYPE == '1' ? '.design-right' : window;
            const scrollTarget = window;
            $(scrollTarget).on('scroll', _self.throttle(function () {
                const pagination = _self.pagination;
                const target = _self.$refs.component;
                const scrollTop = $(scrollTarget).scrollTop();
                const gsTabOffset = target && $(target).offset();
                if (pagination.has_more && document.documentElement.clientHeight + scrollTop > gsTabOffset.top + $(target).height() * 2 / 3) {
                    if (_self.pagination.can_request) {
                        _self.pagination.can_request = false;
                        _self.handleList();
                    }
                }
            }, 100));
        },
        /**
         * throttle fn
         * @param fn
         * @param delay
         * @param atleast
         * @returns {Function}
         */
        throttle (fn, delay, atleast) {
            let timer = null;
            let previous = null;

            return function () {
                let now = +new Date();
                if (!previous) {
                    previous = now;
                }
                if (atleast && now - previous > atleast) {
                    fn();
                    previous = now;
                    clearTimeout(timer);
                } else {
                    clearTimeout(timer);
                    timer = setTimeout(function () {
                        fn();
                        previous = null;
                    }, delay);
                }
            };
        }
    }
};
</script>

<style scoped lang="less">
    .geshop_U000252_template1_wrapper {
        width: 1200px;
        margin: 0 auto;

        ul.wrap {
            display: block;
            width: 1184px;
            margin: 12px auto 4px;
            font-size: 0;

            li.goods {
                display: inline-block;
                position: relative;
                width: 288px;
                margin: 0 4px 8px;
                border: 1px solid #FFFFFF;
                background: #fff;
                padding: 12px;
                box-sizing: border-box;
                vertical-align: top;
            }
        }

        .list_item {

            .item_title {
                width: 258px;
                font-size: 14px;
                height: 20px;
                line-height: 20px;
                text-overflow: ellipsis;
                white-space: nowrap;
                word-wrap: break-word;
                overflow: hidden;
                margin-top: 12px;
                margin-bottom: 4px;
                color: #333333;
            }

            .item_shop {
                font-size: 20px;
                font-weight: 600;
                color: #333333;
                line-height: 30px;
                margin-right: 10px;
            }

            .item_market {
                line-height: 19px;
                height: 19px;
                color: #999999;
                font-size: 14px;
            }

            .item_soldout {
                z-index: 99;
            }
        }

        .item_image {
            position: relative;
            display: block;
            width: 264px;
            height: 352px;
            overflow: hidden;

            &:hover {
                .item_view {
                    display: block;
                }
            }

            .item_view {
                display: none;
                position: absolute;
                top: 159px;
                left: 63px;
                width: 138px;
                height: 34px;
                text-align: center;
                background-color: #FFFFFF;
                cursor: pointer;
                opacity: 0.7;
                z-index: 1;

                &:hover {
                    opacity: 1;
                }
            }

            .item_view span {
                display: inline-block;
                width: 138px;
                height: 34px;
                line-height: 34px;
                font-weight: 600;
                font-size: 14px;
            }
        }

        .inline-block {
            display: inline-block;
        }
    }
</style>
