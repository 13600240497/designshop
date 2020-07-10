<template>
    <!-- 真实组件  -->
    <div :class="{ 'geshop-loading-real': preRender }">
        <component
            :is="components"
            :pid="pid"
            :uikey="uikey"
            :theme="theme"
            :data="data">
        </component>
    </div>
</template>

<script>
import LoadingComponent from './geshop-loading-component.vue';

/**
 * @function on-pre-loaded 当vue-loading组件加载完毕的执行回调
 */
export default {
    name: 'geshop-component',
    props: {
        // 组件生成的唯一ID
        pid: {
            type: Number,
            required: true
        },
        // 组件编码
        uikey: {
            type: String,
            required: true
        },
        // 组件模版
        theme: {
            type: String,
            required: true,
            default: 'default'
        },
        // 后台保存的数据
        data: {
            type: Object,
            required: true
        },
        // 是否开启骨架图预览
        'pre-render': {
            type: String,
            default: false
        },
        // 预渲染类型
        'pre-type': {
            type: String,
            default: 'block'
        }
    },

    components: {
        LoadingComponent
    },

    data () {
        return {
            components: null
        };
    },

    methods: {
        // 当 loading 组件渲染完毕，去除原生 html 的灰色底图
        handleLoadingComponentLoaded () {
            this.$emit('on-pre-loaded');
        }
    },

    beforeCreate () {},

    created () {},

    beforeMount () {},

    mounted () {
        // 加载异步组件
        this.components = () => import(`../../files/parts/ui/${this.uikey}/${this.theme}/index.vue`);

        // 判断是否开启了组件骨架图
        // fasle, 如果是没开启的话，在此组件调用方法去除 loading，
        // true, 如果是开启了，在异步组件里面调用方法去除 loading
        if (this.preRender == false || this.preRender == 'false') {
            this.$store.dispatch('global/loaded', this);
        }
    }
};
</script>

<style>
.component-fade-enter-active,
.component-fade-leave-active {
    transition: opacity .3s ease;
}
.component-fade-enter,
.component-fade-leave-to {
    opacity: 0;
}
</style>
