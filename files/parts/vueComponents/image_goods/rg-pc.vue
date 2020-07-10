<template>
    <div class="geshop-components-default-image-goods">
        <span :data-re="lazyload">
            <!-- 区分装修页和预览发布页 -->
            <template v-if="$root.is_edit_env == 1 || lazyload == false">
                <img :src="src"/>
            </template>
            <!-- 预览发布页开启懒加载，埋点 -->
            <template v-else>
                <img
                    class="js_gdexp_lazy js-geshopImg-lazyload js_gbexp_lazy"
                    :src="lazyImg"
                    :data-original="src"
                    :data-src="swiperLazy ? src : ''"
                    :data-logsss-browser-value="analytics"/>
            </template>
        </span>
    </div>
</template>

<script>
export default {
    props: {
        // 商品图片
        src: {
            type: String,
            default: 'https://geshopimg.logsss.com/uploads/nxQshzC1wXTYy8BmHD6GE39vLWjciKaR.png'
        },
        // 是否开启懒加载
        lazyload: {
            type: Boolean,
            default: true
        },
        // 是否开启swiper懒加载
        swiperLazy: {
            type: Boolean,
            default: false
        },
        // 商品SKU，用于埋点，曝光
        sku: {
            type: String,
            default: ''
        },
        // 当前商品曝光顺序
        index: {
            type: Number,
            default: 0
        }
    },
    computed: {
        // 埋点参数构造
        analytics () {
            const params = {
                pm: `mp`,
                p: `p-${this.$root.pageId}`,
                bv: {
                    cpID: `${this.$root.pageInstanceId}`,
                    cpnum: `${this.$root.compKey}`,
                    cplocation: `${this.$root.uiIndex}`,
                    sku: `${this.sku}`,
                    cporder: `${this.$root.layoutIndex}`,
                    rank: `${this.index}`
                }
            };
            return JSON.stringify(params);
        },
        // 3个站点的 loading 图都不一样的，直接取 default.lazyImg 即可
        lazyImg () {
            return this.$root.data.default.lazyImg || this.src;
        }
    }
};
</script>

<style lang="less" scoped>
    .geshop-components-default-image-goods img {
        display: block;
    }
</style>
