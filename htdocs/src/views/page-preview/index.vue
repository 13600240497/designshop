<template>
    <div id="app" :class="`page-preview-m page-site-${site_code} ${text_direction}`">
        <!-- 遍历所有的布局信息 -->
        <div v-for="id in layouts" :key="id">

            <!-- 根据组件ID开始加载实体组件 -->
            <load-component
                v-if="get_component(id)"
                :id="id"
                :uikey="get_component(id).component_key"
                :template="get_component(id).template_name">
            </load-component>

        </div>
    </div>
</template>

<script>
// 组件加载程序
import loadComponent from '../../components/ui-component-load/index.vue';

export default {
    name: 'app',
    
    components: {
        loadComponent,
    },

    data () {
        return {
            site_code: 'zf'
        };
    },

    computed: {
        // 所有布局
        layouts () {
            return this.$store.state.page.layouts;
        },
        // 当前页面所有组件
        components () {
            return this.$store.state.page.components;
        },
        // 文案方向
        text_direction () {
            const map = ['he'];
            const lang = this.$store.state.page.info.lang || 'en';
            return map.includes(lang) ? 'rtl' : 'ltr';
        }
    },

    methods: {
        // 获取组件数据
        get_component (id) {
            return this.components.filter(x => Number(x.id) === Number(id))[0];
        }
    }
};
</script>

<style lang="less" scoped>
.page-preview-m {
    display: block;
    padding-top: 1px;
    margin: 0 auto;
    margin-top: -1px;
}
</style>

<style lang="less">

@import '../../less/zaful.less';

a {
    text-decoration: none;
}

ul {
    list-style: none;
}

</style>
