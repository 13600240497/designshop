<template>
    <div class="geshop-component-body" :style="style_body"
         :class="[{ 'is-whole': whole }]"
         :data-id="pid" ref="component">
        <div v-if="is_component_show" class="list-wrap">
            <ul :style="style_bg_radius" class="goods-list">
                <li v-for="(item, index) in (list && list.length > 0 ? list : 4)" :key="item.goods_sn + '_' + index"
                    :style="style_item"
                    class="list-li">
                    <div class="list-item">
                        <!--折扣标-->
                        <div class="item-discount">
                            <geshop-discount :value="item.discount"></geshop-discount>
                        </div>

                        <div class="item-image" :style="style_item">
                            <geshop-analytics-href
                                :href="item.url_title"
                                :sku="item.goods_sn"
                                :cate="item.cateid"
                                :warehouse="item.warehousecode"
                                :goods_id="String(item.goods_id)">
                                <geshop-image-goods
                                    :src="item.goods_img"
                                    :sku="item.goods_sn"
                                    :index="index">
                                </geshop-image-goods>
                            </geshop-analytics-href>
                            <!--sold out-->
                            <geshop-soldout class="item_soldout" :visible="item.goods_number <= 0"></geshop-soldout>
                        </div>

                        <div class="item-info">
                            <!--sku标题-->
                            <div class="item-title">
                                <geshop-analytics-href
                                    :item="item"
                                    :index="index">
                                    {{ item.goods_title || itemInfo.goods_title }}
                                </geshop-analytics-href>
                            </div>

                            <div class="item-shop-market">
                                <!--销售价-->
                                <div class="item-shop">
                                    <geshop-shop-price :value="item.shop_price"></geshop-shop-price>
                                </div>
                                <!--市场价-->
                                <div class="item-market" v-if="(data.market_price_active || 1) == 1">
                                    <template
                                        v-if="Number(item.shop_price) < Number(item.market_price) || (!item.shop_price && !item.market_price)">
                                        <geshop-market-price :value="item.market_price"></geshop-market-price>
                                    </template>
                                </div>
                            </div>
                            <!--营销信息-->
                            <geshop-promotion :value="item.promotions"
                                              :color="data.promotions_text_color"
                                              v-if="item.promotions && item.promotions.length > 0"></geshop-promotion>
                        </div>

                    </div>
                </li>
            </ul>
            <div class="loading-more" v-show="view_more_loading" style="padding: 3%;text-align: center;">
                <img src="https://css.zafcdn.com/imagecache/MZF/images/loading_zf.gif">
            </div>
        </div>
    </div>
</template>

<script>
export default {
    props: ['data', 'pid'],
    data () {
        return {
            list: [],
            editList: [{}, {}, {}, {}],
            itemInfo: {
                goods_title: 'ZAFUL Asymmetric Striped Slit Shirt Dress-Dark Gree …'
            },
            view_more_loading: false, // 加载更多图片
            pagination: {
                page_no: 1,
                has_more: true,
                can_request: false // 是否允许请求
            },
            imgFilter: true // 懒加载过滤已加载的
        };
    },
    computed: {
        is_component_show () {
            return GESHOP_PAGE_TYPE == 1 || (this.list && this.list.length > 0);
        },
        style_body () {
            const style = {
                marginBottom: this.$px2rem(this.data.box_margin_bottom),
                backgroundColor: this.data.box_bg_color ? this.data.box_bg_color : '#F8F8F8'
            };
            return style;
        },
        style_item () {
            let _radius = this.data.goods_radius_size ? this.data.goods_radius_size : '12';
            const style = {
                'border-radius': this.$px2rem(_radius)
            };
            return style;
        },
        style_bg_radius () {
            let style = '';
            this.box_is_whole = this.data.box_is_whole ? this.data.box_is_whole : 1;

            // 背景是否整体式， 1:是，0:否
            if (this.box_is_whole == 1) {
                let _radius = this.data.goods_bg_radius_size ? this.data.goods_bg_radius_size : '12';
                style = {
                    'border-radius': this.$px2rem(_radius),
                    'background-color': '#FFFFFF'
                };
            }
            return style;
        },
        style_title () {
            let _color = this.data.shop_price_color ? this.data.shop_price_color : '#333333';
            const style = {
                'color': _color
            };
            return style;
        },
        whole () {
            return this.data.box_is_whole == 0;
        }
    },
    mounted () {
        this.init();
        if (Number(this.$root.data.is_pagination) === 1) {
            this.scrollEvent();
        }
    },
    methods: {
        filterVal (val) {
            return val.replace(/&quot;/g, '"').replace(/&lt;/g, '<').replace(/&gt;/g, '>').replace(/&#39;/g, '\'');
        },
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
            const component_ref = _self.$refs.component;
            $(scrollTarget).on('scroll', _self.throttle(function () {
                const pagination = _self.pagination;
                const scrollTop = $(scrollTarget).scrollTop();
                const gsTabOffset = component_ref && $(component_ref).offset();
                if (pagination.has_more && document.documentElement.clientHeight + scrollTop > gsTabOffset.top + $(component_ref).height() * 2 / 3) {
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

<style lang="less" scoped>
    .geshop-component-body {
        background-color: #F8F8F8;
        width: 750/75rem;
        opacity: 0.99;

        .list-wrap {
            overflow: hidden;
        }

        .goods-list {
            display: flex;
            width: 702/75rem;
            margin: 24/75rem;
            padding: 24/75rem 24/75rem 0rem;
            border-radius: 12/75rem;
            justify-content: space-between;
            flex-flow: row wrap;
            box-sizing: border-box;

            .list-li {
                display: flex;
                width: 318/75rem;
                margin-bottom: 18/75rem;
                background-color: #FFFFFF;

                /*                &:nth-last-child(2) {
                                    margin-bottom: 0rem;
                                }

                                &:last-child {
                                    margin-bottom: 0rem;
                                }*/
            }

            .list-item {
                position: relative;
                width: 100%;

                .item-image {
                    position: relative;
                    display: flex;
                    justify-content: center;
                    align-items: center;
                    width: 100%;
                    height: 424/75rem;
                    overflow: hidden;
                    border-bottom-left-radius: 0;
                    border-bottom-right-radius: 0;
                }

                .item-info {

                    background-color: #FFFFFF;
                    // margin-bottom: 24/75rem;
                }

                .item-title {
                    box-sizing: content-box;
                    /*width: 315/75rem;*/
                    font-size: 22/75rem;
                    height: 30/75rem;
                    line-height: 30/75rem;
                    overflow: hidden;
                    text-overflow: ellipsis;
                    white-space: nowrap;
                    word-break: keep-all;
                    word-wrap: break-word;
                    padding-top: 12/75rem;
                    margin-bottom: 4/75rem;
                    margin-right: 24/75rem;
                    color: #333333;

                    a {
                        color: #333333 !important;

                        &:hover {
                            color: #333333 !important;
                        }
                    }
                }

                .item-shop-market {
                    line-height: 48/75rem;
                    width: 100%;

                    .item-shop {
                        display: flex;
                        flex-flow: row wrap;
                        color: #333333;
                        line-height: 48/75rem;
                        align-items: baseline;

                        .shop-title {
                            font-size: 26/75rem;
                            padding-right: 8/75rem;
                            line-height: 33/75rem;
                        }
                    }

                    .item-market {
                        color: #999999;
                        line-height: 33/75rem;
                    }
                }

                .item-promotions {
                    position: relative;
                    height: 24/75rem;
                    line-height: 24/75rem;
                    margin-top: 6/75rem;
                    margin-bottom: 24/75rem;
                    font-size: 24/75rem;

                    .promotion {
                        position: absolute;
                        top: 22px;
                        left: 0;
                        display: none;
                        padding: 12px;
                        background-color: #FFFFFF;
                        border: 1px solid rgba(238, 238, 238, 1);
                        z-index: 99;
                    }

                    .gs-off-text {
                        .special {
                            font-weight: 700;
                            font-family: OpenSans-Bold, arial, serif;
                        }
                    }

                    .sjx {
                        position: absolute;
                        right: 0;
                        top: 0;
                        width: 28/75rem;
                        height: 28/75rem;
                    }

                    .icon-downs {
                        width: 100%;
                        height: 100%;
                    }
                }

                .item-soldOut {
                    position: absolute;
                    top: 182/75rem;
                    left: 24/75rem;
                    width: 270/75rem;
                    height: 60/75rem;
                    border-radius: 80/75rem;
                    background-color: #000000;
                    opacity: 0.4;
                    z-index: 1;

                    & span {
                        display: inline-block;
                        text-align: center;
                        font-weight: 600;
                        line-height: 26/75rem;
                        font-size: 24/75rem;
                        color: #ffffff;
                        position: absolute;
                        left: 50%;
                        top: 50%;
                        transform: translate(-50%, -50%);
                        z-index: 2;
                    }
                }

                .item-button {
                    margin-top: 18/75rem;
                    padding: 0rem 0rem 24/75rem 0rem;
                }
            }
        }
    }

    .geshop-component-body.is-whole {
        .goods-list {
            margin: 24/75rem 24/75rem 6/75rem;
            padding: 0;

            .list-li {
                display: flex;
                width: 342/75rem;
                margin-bottom: 18/75rem;
            }

            .list-item {
                .item-image {
                    height: 456/75rem;
                }

                .item-soldOut {
                    position: absolute;
                    top: 198/75rem;
                    left: 24/75rem;
                    width: 294/75rem;
                    height: 60/75rem;
                }

                .item-info {
                    padding-left: 24/75rem;
                    margin-bottom: 24/75rem;
                }

                .item-promotions {
                    padding-left: 24/75rem;

                    .sjx {
                        position: absolute;
                        right: 10/75rem;
                        top: 0;
                    }
                }

                .item-button {
                    padding: 0rem 24/75rem 24/75rem;
                }
            }
        }
    }

    .loading-more img {
        width: 1rem;
        height: 1rem;
    }
</style>

<style lang="less">
    .geshop-component-body {
        .gs-off-text {
            em {
                font-style: normal;
            }
        }
    }

</style>
