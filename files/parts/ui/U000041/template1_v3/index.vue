<template>
    <div class="geshop-U000041-template1_v3 geshop-col-12">
        <div class="gs-tab" >
            <ul class="gs-tab-label">
                <li v-for="(item, index) in ( data.navList && data.navList.length > 0 ? data.navList : 3 )"
                    :key="item.list"
                    :class="{on :curTabox == index}"
                    @click="handleChangeTab(index)"
                >
                    <span>{{ item.navName || 'Tab' }}</span>
                </li>
            </ul>
            <div class="gs-tab-content" ref="listWrap">
                <div class="gs-tab-item" :class="{on :curTabox == indexs}" v-for="(items, indexs) in  goodsArray || ( data.navList && data.navList.length > 0 ? data.navList.length : 3 )" :key="index">
                <!--<div class="gs-tab-item">-->
                    <ul class="clearfix">
                        <li class="list-item" v-for="(item, index) in showNum(items)" :key="index">
                        <!--<li class="list-item" v-for="(item, index) in (dataArr.length > 0 ? dataArr : 4)" :key="item.goods_sn + index">-->
                            <div class="list-item-img">
                                <geshop-analytics-href
                                    target="_blank"
                                    :href="item.url_title"
                                    :sku="item.goods_sn"
                                    :cate="item.cateid"
                                    :warehouse="item.warehousecode"
                                    :goods_id="item.goods_id"
                                    class="item-img">
                                    <geshop-image-goods :src="item.goods_img"
                                                        :sku="item.goods_sn"
                                                        :lazyload="lazyload"
                                                        :index="index">

                                    </geshop-image-goods>
                                    <geshop-discount :value="item.discount">
                                    </geshop-discount>
                                </geshop-analytics-href>
                                <geshop-soldout
                                        :visible="item.goods_number <= 0">
                                </geshop-soldout>
                            </div>
                            <div class="item-info-box">
                                <geshop-analytics-href
                                        target="_blank"
                                        :href="item.url_title"
                                        :sku="item.goods_sn"
                                        :cate="item.cateid"
                                        :warehouse="item.warehousecode"
                                        :goods_id="item.goods_id" class="item-title">{{ item.goods_title || 'Spaghetti Strap Criss Cross Bikini'}}
                                </geshop-analytics-href>

                                <p class="item-shop-price">
                                    <geshop-shop-price :value="item.shop_price"></geshop-shop-price><geshop-market-price :value="item.market_price" v-show="Number(item.shop_price) < Number(item.market_price)"></geshop-market-price>
                                </p>
                            </div>
                            <geshop-analytics-href
                                class="buy-content"
                                :item="item"
                                :index="index"
                                :target="target">
                                <img :src="data.buyBgImg ? data.buyBgImg : 'https://geshopimg.logsss.com/uploads/sRV7tqvraM8uTbGWJkwZCjUI3FfyYxHc.png'" width="32" height="32">
                                <span class="label" >{{languges.btn_buy_now}}</span>
                            </geshop-analytics-href>
                        </li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
export default {
    name: 'index.vue',
    props: ['data', 'pid'],
    data () {
        return {
            navLoad: [], // 存放导航索引，判断是否需要懒加载
            dataArr: [], // 存当前索引数据
            goodsArray: null, // 存放页面数据
            curTabox: 0, // 存放导航索引
            lazyload: true,
            languges: window.GESHOP_LANGUAGES, // 存放当前多语言翻译
            target: '_blank'
        };
    },
    computed: {
        isDateRes () {
            return this.$store.state.global.isDateRes;
        }
    },
    mounted () {
        // 初始化数据
        this.isDateRes && this.init();
    },
    methods: {
        init () {
            try {
                // 从变量里面去当前组件的商品信息
                this.goodsArray = window.GESHOP_ASYNC_DATA_INFO[this.pid] && window.GESHOP_ASYNC_DATA_INFO[this.pid].length > 0 ? [...window.GESHOP_ASYNC_DATA_INFO[this.pid]] : null;
                this.dataArr = this.goodsArray[this.curTabox].goodsInfo;
            } catch (e) {
            }

            // 去处loading
            this.$store.dispatch('global/loaded', this);
            // 当前索引写入数组，表示已进行懒加载
            this.pushIsLoad(0);
        },
        /**
         * 切换导航和内容
         * @param {number} index
         */
        handleChangeTab (index) {
            if (this.curTabox === index) {
                return;
            }
            // 当前索引写入数组，表示已进行懒加载
            this.curTabox = index;
            this.pushIsLoad(index);
            // this.dataArr = this.goodsArray[index].goodsInfo;
            // this.$store.dispatch('global/async_goods_init', this);
        },
        // 处理 当前索引的数据
        showNum (items) {
            if (typeof items != 'number' && items.goodsInfo.length > 0) {
                return items.goodsInfo;
            } else {
                return 4;
            }
        },
        /**
         * 触发懒加载
         * @param index 当前导航索引
         */
        pushIsLoad (index) {
            // 数组里面不存在  没有进行过懒加载
            if (this.navLoad.indexOf(index) < 0) {
                this.navLoad.push(index);
                // 商品懒加载
                this.$store.dispatch('global/async_goods_init', {
                    _that: this, // vue 实例
                    $el: $(this.$refs.listWrap).find('.gs-tab-item').eq(index), // 需要懒加载的dom
                    type: 2 // 懒加载类型 2 指display 为 none的元素不进行懒加载
                });
            }
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
    @import "template1_v3.less";
</style>
