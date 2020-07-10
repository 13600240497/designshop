<template>
    <div class="geshop-component-body" v-if="list.length > 0">
        <ul class="list__box" :class="liClass">
            <li class="list_item" v-for="(item,index) in list" :key="item.goods_sn + '_' + index">
                <!-- 商品图片 -->
                <div class="item_image">
                    <geshop-analytics-href
                        v-if="item.goods_number > 0"
                        :href="item.url_title"
                        :sku="item.goods_sn"
                        :cate="item.cateid"
                        :warehouse="item.warehousecode"
                        :goods_id="item.goods_id">
                        <geshop-image-goods :src="item.goods_img"></geshop-image-goods>
                    </geshop-analytics-href>
                    <geshop-image-goods v-else :src="item.goods_img"></geshop-image-goods>
                </div>
                <div class="item_content">
                    <div class="item_price">
                        <!--销售价-->
                        <div class="item_shop">
                            <geshop-shop-price :value="item.shop_price"></geshop-shop-price>
                        </div>
                    </div>
                </div>
            </li>
        </ul>
        <div class="item_more_less" v-if="!pagination.seeMoreHide">
            <!-- 点击 viewmore loading 的效果 -->
            <template v-if="view_more_loading">
                <img src="https://uidesign.rglcdn.com/RG/image/z_promo/20190311_8431/loading_tm.gif" alt=""
                     style="height: 0.96rem;">
            </template>
            <template v-else>
                <div class="view_more" @click="getGoodsList">
                    <span>{{ see_more }}</span>
                </div>
            </template>
        </div>
    </div>
</template>

<script>
export default {
    name: 'like',
    props: ['data', 'pid'],
    data () {
        return {
            list: [], // 商品列表
            editList: [
                {
                    'goods_title': 'ZAFUL Fleece Vest And Corduroy JackJackJackJack',
                    'shop_price': '0.00',
                    'market_price': '0.00'
                },
                {
                    'goods_title': 'ZAFUL Fleece Vest And Corduroy JackJackJackJack',
                    'shop_price': '0.00',
                    'market_price': '0.00'
                },
                {
                    'goods_title': 'ZAFUL Fleece Vest And Corduroy JackJackJackJack',
                    'shop_price': '0.00',
                    'market_price': '0.00'
                },
                {
                    'goods_title': 'ZAFUL Fleece Vest And Corduroy JackJackJackJack',
                    'shop_price': '0.00',
                    'market_price': '0.00'
                }
            ], // 默认商品列表
            see_more: '',
            view_less: '',
            view_more_loading: false, // 点击 viewmore 之后的 loading 效果
            pagination: {
                pageSize: 24,
                pageNo: 1,
                totalCount: null,
                pageMax: 1,
                seeMoreHide: false
            },
            imgFilter: true
        };
    },
    computed: {
        liClass () {
            return parseInt(this.$root.data.box_multi_column) === 3 ? 'li-col-3' : '';
        }
    },
    mounted () {
        this.init();
    },
    methods: {
        init () {
            this.viewMoreInit();
            this.getGoodsList();
        },
        handleEditList () {
            // 装修页
            if (this.$root.data.isEditEnv == 1 && this.list.length == 0) {
                this.list = [...this.editList];
                this.$store.dispatch('global/loaded', this);
            }
        },
        /**
         * 加载商品并初始化商品
         */
        getGoodsList () {
            const pagination = this.pagination;
            const _data = this.$root.data;
            const jsonParam = {
                catGroup: _data.navList,
                pageNo: pagination.pageNo,
                pageSize: pagination.pageSize
            };
            this.handleList(jsonParam).then((resData) => {
                if (resData) {
                    const dataPagination = resData.pagination;
                    this.pagination.pageMax = Math.ceil(dataPagination.totalCount / dataPagination.pageSize);
                    if (dataPagination.pageNo + 1 <= this.pagination.pageMax) {
                        this.pagination.pageNo += 1;
                    } else {
                        this.pagination.seeMoreHide = true;
                    }
                }
                this.handleEditList();
            }).catch(err => {
                console.log(err);
                this.handleEditList();
            });
        },
        /**
         * 获取分类商品列表
         * @returns {Promise<null>}
         * @param jsonParam
         */
        async handleList (jsonParam = {}) {
            let result = null;
            // 请求loading
            this.view_more_loading = true;

            const url = GESHOP_INTERFACE.goods_recommendlistadvanced.url;
            const param = {
                lang: GESHOP_LANG,
                pageNo: jsonParam.pageNo || 1,
                pageSize: jsonParam.pageSize || 24,
                catGroup: jsonParam.catGroup || [],
                platform: GESHOP_PLATFORM || 'm'
            };
            const jsonData = param;
            try {
                result = await this.$jsonp(url, jsonData).then(res => {
                    if (res.code === 0 && res.data && res.data.goodsInfo && res.data.goodsInfo.length > 0) {
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
            return result;
        },
        /**
         * view more 初始化
         */
        viewMoreInit () {
            this.see_more = this.$lang('see_more');
            this.view_less = this.$lang('view_less');
        },
        // see more
        handleSeeMore () {

        },
        handleDispatch () {
            // 去除loading骨架图
            this.$store.dispatch('global/loaded', this);
            // 商品初始化
            this.$store.dispatch('global/async_goods_init', this);
        }
    }
};
</script>

<style lang="less" scoped>
    @import "./component.less";
</style>
