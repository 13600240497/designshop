<template>
    <div class="design-layout">

        <!-- 加载效果 -->
        <a-spin v-if="loading" :spinning="loading"></a-spin>

        <!-- 真实组件 -->
        <template v-if="first_loaded">
            <!-- 头部 -->
            <layout-top />

            <!-- 左侧组件列表 -->
            <layout-component-list />

            <!-- 中间预览区域 -->
            <layout-preview />

            <!-- 组件表单 -->
            <layout-form />

            <!-- 组件顺序 -->
            <layout-sortable />
        </template>
    </div>
</template>

<script>

import {
    get_native_apis
} from '../../plugin/api.js';

import layoutTop from './design-top.vue'; // 头部区域
import layoutPreview from './preview/index.vue'; // 组件预览区域
import layoutForm from './form/index.vue'; // 组件表单区域
import layoutComponentList from './design-component-list.vue'; // 组件列表区域
import layoutSortable from './design-sortable.vue'; // 组件顺序区域

export default {
    components: {
        layoutTop,
        layoutPreview,
        layoutForm,
        layoutComponentList,
        layoutSortable,
    },

    computed: {
        first_loaded () {
            return this.$store.state.design.first_loaded;
        },
        loading () {
            return this.$store.state.design.loading;
        }
    },

    methods: {
        /**
         * 页面初始化
         */
        async page_init () {
            // 开启页面级loading
            this.$store.state.design.first_loaded = false;
            // 初始化表单状态
            this.$store.state.design.selected_id = null;
            this.$store.state.design.show_component_form = false;

            // 获取路由URL参数
            const { group_id = '', pipeline = '', lang = '', platform = 'm' } = this.$route.query;

            // 拦截非法请求
            if (group_id === '' || pipeline === '' || lang === '' || platform === '') {
                this.$message.error('非法请求，3秒后跳转回首页');
                setTimeout(() => {
                    window.location.href = '/';
                }, 3000);
                return false;
            }

            /**
             * 获取页面API接口数据
             */
            const res = await get_native_apis({ group_id, pipeline, lang, platform });
            window.GESHOP_INTERFACE = res.interfaceConfig;
            window.GESHOP_URL_SOP_ADD_RULE = res.sopAddRuleUrl;
    
            // 请求页面数据
            this.$store.dispatch('design/page_load', {
                group_id,
                pipeline,
                lang,
                platform
            });
        }
    },

    watch: {
        '$route' () {
            this.page_init();
        }
    },

    created () {
        
        /**
         * 页面初始化
         */
        this.page_init();

        // 设置dom的根样式
        document.documentElement.style.fontSize = '37.5px';

        // 轮训锁定装修页不给人操作
        this.$store.dispatch('design/page_keep_locking');

        // 装修页监听关闭页面提示
        // window.onbeforeunload = function (e) {
        // 　　var e = window.event||e;
        // 　　e.returnValue=("确定离开当前页面吗？");
        // }
    }
}
</script>

<style lang="less">
    #app {
        height: 100%;
    }
</style>

<style lang="less" scoped>
    .design-layout {
        display: block;
        position: relative;
        min-width: 100%;
        min-height: 100%;
        height: 100%;
        background: #EDEFF2;
    }

    // loding 菊花图
    .ant-spin.ant-spin-spinning {
        position: fixed;
        left: 0px;
        top: 0px;
        bottom: 0px;
        right: 0px;
        background: rgba(255, 255, 255, 0.5);
        z-index: 100;
        display: flex;
        justify-content: center;
        align-items: center;
    }
</style>
