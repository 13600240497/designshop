<template>
    <div class="geshop-dl-image-goods" :class="containerClassName">
        <span>
            <img
                :class="imageClassName"
                :src="value_src"
                :data-original="value_original"
                :data-logsss-browser-value="value_analytics"
            />
        </span>
    </div>
</template>

<script>
export default {
    props: {
        // 图片
        src: { type: String },
        // 图片比例类型，0=长方形(3x4),  1=正方形(1x1)
        type: { type: Number, default: 0 },
        // 曝光SKU
        sku: { type: String, default: '' },
        // 曝光顺序
        index: { type: Number, default: 0 },
        // 推荐位置
        mrlc: { type: String, default: '' }
    },
    data () {
        return {
            is_edit_env: this.$root.is_edit_env == 1,
            // 默认图片
            defaultImage3x4: 'https://geshopimg.logsss.com/uploads/nxQshzC1wXTYy8BmHD6GE39vLWjciKaR.png',
            defaultImage1x1: 'https://geshopimg.logsss.com/uploads/O8Tt6Sk5M7d4wesmfhWACKzj3cJyZLI0.png',
            loadingImage: 'https://geshopcss.logsss.com/imagecache/geshop/resources/images/dl/loading.gif'
        };
    },
    computed: {
        // 默认 type = 0， 比例：(3x4)的图片
        containerClassName () {
            return this.type === 0 ? 'is-rectangle' : 'is-square';
        },
        // <img> 标签样式名
        imageClassName () {
            return this.is_edit_env ? '' : 'js_gdexp_lazy js_logsss_browser';
        },
        // 埋点参数
        value_analytics () {
            const str = `{'pm':'mr','bv':{'mrlc': '${this.mrlc}', 'sku': '${this.sku}'}}`;
            return str;
        },
        // 默认图片，根据 type 区分比例
        defaultImage () {
            return this.type == 0 ? this.defaultImage3x4 : this.defaultImage1x1;
        },
        // 图片，装修页=原图，发布页=懒加载的图
        value_src () {
            return this.is_edit_env ? (this.src || this.defaultImage) : this.loadingImage;
        },
        // 原图，装修页=空。发布页=真实图
        value_original () {
            return this.is_edit_env ? '' : this.src || this.defaultImage;
        }
    }
};
</script>

<style lang="less" scoped>
.geshop-dl-image-goods {
    display: block;
    position: relative;
    width: 100%;
    overflow: hidden;

    &.is-square {
        padding-top: 100%;
        > span img {
            min-width: 100%;
            max-height: 100%;
        }
    }
    &.is-rectangle {
        padding-top: 133%;
        > span img {
            max-width: 100%;
        }
    }
    > span {
        position: absolute;
        left: 0px;
        top: 0px;
        right: 0px;
        bottom: 0px;
        display: flex;
        justify-items: center;
        align-content: center;
        align-items: center;
        img {
            display: block;
        }
    }
}
</style>
