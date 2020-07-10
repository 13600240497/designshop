<template>
    <div class="geshop-components-default-image-goods">
        <span>
            <!-- 区分装修页和预览发布页 -->
            <template v-if="$root.is_edit_env == 1">
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
        </span>
    </div>
</template>

<script>
export default {
    name: 'geshop-image-goods',
    props: {
        src: {
            type: String,
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
            lazyImg: 'https://geshopimg.logsss.com/uploads/nxQshzC1wXTYy8BmHD6GE39vLWjciKaR.png'
        }
    },
    mounted () {
        if (this.$root.compKey !== 'U000109') {
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
    .geshop-components-default-image-goods img {
        display: block;
    }
</style>
