<template>
    <component
        :is="components[siteCode]"
        :src="src"
        :lazyload="lazyload"
        :swiperLazy="swiperLazy"
        :sku="sku"
        :index="index"
        :type="type"
        :mrlc="mrlc"
        :pid="pid">
    </component>
</template>

<script>
import zfpc from './zf-pc.vue';
import zfm from './zf-m.vue';
import rgpc from './rg-pc.vue';
import rgm from './rg-m.vue';
import dl from './dl-pc.vue';

export default {
    name: 'geshop-image-goods',
    props: {
        // 商品图片
        src: {
            type: String
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
        },
        // 图片比例类型，0=长方形(3x4),  1=正方形(1x1)
        type: { type: Number, default: 0 },
        // DL埋点-推荐位置
        mrlc: { type: String, default: '' }
    },
    data () {
        return {
            components: {
                'zf-pc': zfpc,
                'zf-wap': zfm,
                'zf-app': zfm,
                'rg-pc': rgpc,
                'suk-pc': rgpc,
                'rg-wap': rgm,
                'suk-wap': rgm,
                'rg-app': rgm,
                'dl-web': dl,
                'dl-app': dl
            }
        };
    },
    computed: {
        // 返回站点编码
        siteCode () {
            return window.GESHOP_SITECODE;
        },
        // 返回GeshopComponent组件pid
        pid () {
            return this.$root.$children.length > 0 && this.$root.$children[0].pid;
        }
    }
};
</script>
