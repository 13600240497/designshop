<template>
    <div class="geshop-zaful-image-goods">
        <img
            class="js_gdexp_lazy js-geshopImg-lazyload js_gbexp_lazy"
            :src="local_src"
            :data-original="src"
            :data-logsss-browser-value="analytics" />
    </div>
</template>

<script>
const defaultImg = 'https://geshopimg.logsss.com/uploads/nxQshzC1wXTYy8BmHD6GE39vLWjciKaR.png';
export default {
    name: 'goods-image-zaful',
    props: {
        // 商品图片
        src: {
            type: String,
            default: defaultImg
        },
        default_img: {
            type: String
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
    data () {
        const lazyload = this.$store.state.page.env != 1;
        const img = this.default_img ? this.default_img : defaultImg;
        const src = lazyload === true ? img : this.src;
        return {
            local_src: src, // 默认图
            lazyload // 是否开启懒加载
        };
    },
    computed: {
        // 埋点参数构造
        analytics () {
            const params = {
                pm: `mp`,
                p: `p-${this.$root.pageId}`,
                bv: {
                    // cpID: `${this.$root.pageInstanceId}`,
                    // cpnum: `${this.$root.compKey}`,
                    // cplocation: `${this.$root.uiIndex}`,
                    sku: `${this.sku}`,
                    // cporder: `${this.$root.layoutIndex}`,
                    rank: `${this.index}`
                }
            };
            return JSON.stringify(params);
        }
    }
};
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
