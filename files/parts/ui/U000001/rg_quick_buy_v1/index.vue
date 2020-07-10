<template>
    <div :class="['geshop_U000001_rg_quick_buy_v1_wrapper','geshop-component-body']" v-if="list.length > 0"
         ref="component">
        <ul class="goods_list_wrap">
            <li v-for="(aGroup, aKey) in list" :key="aKey">
                <div :class="['list_item',{ hide:idx!=0 }]"
                     v-for="(item,idx) in aGroup" :key="item.goods_sn"
                     :data-idx="idx">
                    <div class="item_image">
                        <!--折扣标-->
                        <geshop-discount
                            :value="typeof item.discount != 'undefined' ? item.discount: 50"></geshop-discount>

                        <!-- 有库存链接 -->
                        <geshop-analytics-href
                            v-if="item.goods_number > 0"
                            :href="item.url_title"
                            :sku="item.goods_sn"
                            :cate="item.cateid"
                            :warehouse="item.warehousecode"
                            :goods_id="item.goods_id">
                            <geshop-image-goods
                                :src="idx==0 ? item.goods_img : ''"
                                :lazyload="idx == 0" />
                        </geshop-analytics-href>

                        <!-- 无库存链接 -->
                        <geshop-image-goods
                            v-else
                            :src="idx==0 ? item.goods_img : ''"
                            :lazyload="idx == 0"/>

                        <!--sold out-->
                        <geshop-soldout class="item_soldout" :visible="Number(item.goods_number) <= 0"></geshop-soldout>

                        <!-- 库存告急 -->
                        <geshop-stocktip class="item_stocktip" :item="item"></geshop-stocktip>

                        <!-- 快速购买 -->
                        <geshop-button-quick-view style="text-transform: uppercase"
                                                  class="item_quick_view site-bold-strict"
                                                  :item="item"
                                                  :index="idx"
                                                  v-if="Number(item.goods_number) > 0"
                                                  :url_quick="item.url_quick">
                            <span>{{ $lang('quick_shop') }}</span>
                        </geshop-button-quick-view>

                        <!--商品营销信息-->
                        <div class="item_marketing_info"
                             v-if="showMarketing == 1 && item.promotions && item.promotions.length > 0">
                            <span v-html="item.promotions[item.promotions.length -1 ]"></span>
                        </div>
                    </div>

                    <div class="item_content">
                        <!--sku标题-->
                        <div class="item_title">
                            <geshop-analytics-href
                                :item="item"
                                :index="index">
                                <geshop-goods-title>{{item.goods_title}}</geshop-goods-title>
                            </geshop-analytics-href>
                        </div>

                        <div class="item_price">
                            <!--销售价-->
                            <div class="item_shop inline-block">
                                <geshop-shop-price :value="item.shop_price"></geshop-shop-price>
                            </div>
                            <!--市场价-->
                            <div class="item_market inline-block">
                                <geshop-market-price
                                    v-if="Number(item.shop_price) < Number(item.market_price) || editStatus"
                                    :value="item.market_price"></geshop-market-price>
                            </div>
                        </div>
                        <!-- 同款 -->
                        <div class="item_same_wrap" v-if="showSame === 1">
                            <ul class="same_list" v-if="aGroup.length > 1">
                                <li :class="{ is_same: same_index == 0 }"
                                    v-for="(val, same_index) in aGroup.slice(0, 5)"
                                    v-if="same_index <= 5"
                                    :key="val.goods_sn"
                                    @mouseenter="handlerMouseEnterSame($event, same_index, val.goods_img)">
                                    <geshop-image-goods
                                        v-if="val.goods_img_sm"
                                        :src="val.goods_img_sm"
                                        class="same_img">
                                    </geshop-image-goods>
                                </li>
                                <li v-if="aGroup.length > 5" class="ellipsis">
                                    <geshop-analytics-href
                                        :item="aGroup[0]">
                                    </geshop-analytics-href>
<!--                                    <a :href="aGroup[0].url_title"></a>-->
                                </li>
                            </ul>
                        </div>
                    </div>
                </div>
            </li>
        </ul>
        <div class="item_more_less">
            <template v-if="view_more_loading">
                <img src="https://uidesign.rglcdn.com/RG/image/z_promo/20190311_8431/loading_tm.gif" alt=""
                     style="height: 64px;">
            </template>
        </div>
    </div>
</template>

<script>

import GeshopStocktip from '../../../vueComponents/stock_tip/rg-pc';
export default {
    name: 'rg_quick_buy_v1',
    components: { GeshopStocktip },
    props: ['data', 'pid'],
    data () {
        return {
            showSame: 1, // 商品同款是否显示
            showMarketing: 1, // 商品营销信息
            cateType: 1, // 分类商品类型[1,2,3] > [all,促销，非促销]
            list: [], // 商品列表
            editStatus: 1,
            editList: [],
            editListChild: [
                {
                    goods_title: 'Plus Size Color Block Flare Tankini Set …',
                    goods_img_sm: 'https://geshopimg.logsss.com/uploads/O8Tt6Sk5M7d4wesmfhWACKzj3cJyZLI0.png',
                    url_title: '',
                    promotions: ['<span class="bngo-list">Buy 1 Get <strong class="red_font">15%</strong> Off</span>']
                },
                {
                    goods_title: 'Plus Size Color Block Flare Tankini Set …',
                    goods_img_sm: 'https://geshopimg.logsss.com/uploads/O8Tt6Sk5M7d4wesmfhWACKzj3cJyZLI0.png',
                    url_title: '',
                    promotions: ['<span class="bngo-list">Buy 1 Get <strong class="red_font">15%</strong> Off</span>']
                },
                {
                    goods_title: 'Plus Size Color Block Flare Tankini Set …',
                    goods_img_sm: 'https://geshopimg.logsss.com/uploads/O8Tt6Sk5M7d4wesmfhWACKzj3cJyZLI0.png',
                    url_title: '',
                    promotions: ['<span class="bngo-list">Buy 1 Get <strong class="red_font">15%</strong> Off</span>']
                },
                {
                    goods_title: 'Plus Size Color Block Flare Tankini Set …',
                    goods_img_sm: 'https://geshopimg.logsss.com/uploads/O8Tt6Sk5M7d4wesmfhWACKzj3cJyZLI0.png',
                    url_title: '',
                    promotions: ['<span class="bngo-list">Buy 1 Get <strong class="red_font">15%</strong> Off</span>']
                },
                {
                    goods_title: 'Plus Size Color Block Flare Tankini Set …',
                    goods_img_sm: 'https://geshopimg.logsss.com/uploads/O8Tt6Sk5M7d4wesmfhWACKzj3cJyZLI0.png',
                    url_title: '',
                    promotions: ['<span class="bngo-list">Buy 1 Get <strong class="red_font">15%</strong> Off</span>']
                }
            ], // 默认商品列表
            goodsSn: null, // 商品的sku
            goods_tab_from: null, // [0,1]>[0选品，1分类id]
            pagination: {
                pageSize: 24,
                pageNo: 1,
                totalCount: null,
                pageMax: 1,
                seeMoreHide: false
            },
            view_more_loading: false,
            ajx: false, // 是否可以请求,
            imgFilter: true // 懒加载过滤已加载的
        };
    },
    mounted () {
        this.isDateRes && this.init();
    },
    computed: {
        isDateRes () {
            // ajax 请求 json文件回来存放的信息
            return this.$store.state.global.isDateRes;
        }
    },
    methods: {
        init () {
            this.initEditData();
            this.initFormData();
            this.getGoodsList();
            this.scrollCalBackFn();
        },
        /**
         * 初始化默认数据
         */
        initEditData () {
            this.editList = Array.apply(null, { length: 4 }).map(() => this.editListChild);
            if (this.$root.is_edit_env) {
                this.$set(this.pagination, 'pageSize', 100);
            }
        },
        /**
         * 初始化表单数据配置
         */
        initFormData () {
            const $data = this.$root.data;
            this.showSame = Number($data.pic_is_show ? $data.pic_is_show : 1);
            this.showMarketing = $data.marketing_is_show ? $data.marketing_is_show : 1;
            this.cateType = $data.cat_goods_type ? $data.cat_goods_type : 1;
            let sku = window.GESHOP_ASYNC_DATA_INFO[this.pid] && window.GESHOP_ASYNC_DATA_INFO[this.pid].length && window.GESHOP_ASYNC_DATA_INFO[this.pid][0].goodsSku;
            this.goodsSn = sku || (($data.goodsDataFrom || 1) == 1 ? $data.goodsSKU : $data.ipsGoodsSKU);
            this.goods_tab_from = Number($data.goods_tab_from) || 0;
        },
        getGoodsList () {
            const pagination = this.pagination;
            const $data = this.$root.data;
            const jsonParam = {
                catGroup: $data.navList,
                catType: this.cateType,
                pageNo: pagination.pageNo,
                pageSize: pagination.pageSize
            };
            this.handleList(jsonParam).then(resData => {
                if (resData) {
                    this.ajx = true; // 允许再请求
                    const dataPagination = resData.pagination;
                    this.pagination.pageMax = Math.ceil(dataPagination.totalCount / dataPagination.pageSize);
                    if (dataPagination.pageNo + 1 <= this.pagination.pageMax) {
                        this.pagination.pageNo += 1;
                    } else {
                        this.pagination.seeMoreHide = true;
                    }
                }
                this.handleEditList();
            }).catch(() => {
                this.handleEditList();
            });
        },
        /**
         * 获取商品数据
         * @returns {Promise<void>}
         */
        async handleList (jsonData = {}) {
            // 请求loading
            this.view_more_loading = true;
            const url = GESHOP_INTERFACE.goods_samelistCatIdMultiple.url;
            let params = {
                lang: GESHOP_LANG,
                platform: GESHOP_PLATFORM || 'pc',
                pageNo: jsonData.pageNo || 1,
                pageSize: jsonData.pageSize || 24
            };
            if (this.goods_tab_from === 0) {
                params = Object.assign(params, {
                    goodsSn: this.goodsSn || ''
                });
            } else {
                params = Object.assign(params, {
                    catGroup: jsonData.catGroup || [],
                    catType: jsonData.catType
                });
            }
            try {
                return await this.$jsonp(url, params).then(res => {
                    if (res.code === 0 && res.data && res.data.goodsInfo && res.data.goodsInfo.length > 0) {
                        this.editStatus = false;
                        this.list = [...this.list, ...res.data.goodsInfo];
                    }
                    this.handleDispatch();
                    this.view_more_loading = false;
                    return res.data;
                });
            } catch (err) {
                this.view_more_loading = false;
                this.handleDispatch();
            }
        },
        handleEditList () {
            // 装修页数据的处理
            if (window.GESHOP_PAGE_TYPE == 1 && this.list.length == 0) {
                this.list = [...this.editList];
            }
        },
        handleDispatch () {
            // 去除loading骨架图
            this.$store.dispatch('global/loaded', this);
            // 商品初始化
            this.$store.dispatch('global/async_goods_init', this);
        },
        /**
         * 同款mouseenter事件
         * @param {Object} event jQuery click 事件参数
         * @param {Number} idx 索引
         * @param {String} image_url 需要切换展示的图片地址
         */
        handlerMouseEnterSame (event, idx, image_url) {
            const $target = $(event.target);
            const $li = $target.parents('li');
            $target.addClass('is_same').siblings('li').removeClass('is_same');
            $li.find('.list_item').each((index, item) => {
                if ($(item).attr('data-idx') == idx) {
                    $(item).removeClass('hide');
                    // 切换图片
                    const current_image = $(item).find('.item_image img').attr('src');
                    if (current_image != image_url) {
                        $(item).find('.item_image img').attr('src', image_url);
                    }
                } else {
                    $(item).addClass('hide');
                }
            });
        },
        /**
         * 监听滚动距离，进行分页加载
         */
        scrollCalBackFn () {
            const _self = this;
            $(window).on('scroll', _self.throttle(function () {
                const pagination = _self.pagination;
                const target = _self.$refs.component;
                const scrollTop = $(window).scrollTop();
                const gsTabOffset = $(target).offset();
                if (!pagination.seeMoreHide && document.documentElement.clientHeight + scrollTop > gsTabOffset.top + $(target).height() * 2 / 3) {
                    if (_self.ajx) {
                        _self.ajx = false;
                        _self.getGoodsList();
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
    },
    watch: {
        isDateRes () {
            this.init();
        }
    }
};
</script>

<style scoped lang="less">
    @import "./component.less";
</style>
<style lang="less">
    .geshop_U000001_rg_quick_buy_v1_wrapper {
        .same_img img {
            width: auto;
            height: 20px;
            margin: auto;
        }
    }
</style>
