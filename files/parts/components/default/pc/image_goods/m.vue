<template>
    <div class="geshop-zaful-image-goods">
        <!-- 区分装修页和预览发布页 -->
        <template v-if="$root.is_edit_env == 1 || lazyload == false">
            <img :src="src || lazyImg" />
        </template>
        <!-- 预览发布页开启懒加载，埋点 -->
        <template v-else>
            <img 
                class="js_gdexp_lazy js-geshopImg-lazyload js_gbexp_lazy"
                :src="lazyImg"
                :data-original="src"
                :data-logsss-browser-value="analytics" />
        </template>
    </div>
</template>

<script>
export default {
    name: 'geshop-image-goods',
    props: {
        src: {
            type: String,
        },
        lazyload: {
            type: Boolean,
            default: true,
        },
        sku: {
            type: String,
            default: '',
        },
        index: {
            type: Number,
            default: 0,
        }
    },
    computed: {
        analytics() {
            const params = {
                pm: `mp`,
                p: `p-${this.$root.pageId}`,
                bv: {
                    cpID: `${this.$root.pageInstanceId}`,
                    cpnum: `${this.$root.compKey}`,
                    cplocation: `${this.$root.uiIndex}`,
                    sku: `${this.sku}`,
                    cporder: `${this.$root.layoutIndex}`,
                    rank: `${this.index}`,
                }
            }
            return JSON.stringify(params);
        }
    },
    data() {
        return {
            lazyImg: this.$root.data.default.lazyImg||'https://geshopcss.logsss.com/imagecache/geshop/resources/images/rg/loading_big.gif'
        }
    },
    mounted() {
        if (this.$root.compKey !== 'U000110' && this.$root.compKey !== 'U000209' && this.$root.theme !== 'rg_quick_buy') {
            this.$nextTick(() => {
                if (window.GS_GOODS_LAZY_FN) {
                    window.GS_GOODS_LAZY_FN($('img.js_gdexp_lazy'))
                }
            });
        }
    }
}
</script>


<style lang="less" scoped>
    .geshop-zaful-image-goods {
        display: flex;
        align-items: center;
        align-content: center;
        img {
            width: 100%;
        }
    }
</style>
