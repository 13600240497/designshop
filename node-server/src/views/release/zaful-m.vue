<template>
    <div id="app" :class="`page-preview-m page-site-zf ${text_direction}`">
        <!-- 遍历所有的布局信息 -->
        <div v-for="id in layouts" :key="id">

            <!-- 根据组件ID开始加载实体组件 -->
            <load-component
                :id="id"
                :uikey="get_component(id).component_key"
                :template="get_component(id).template_name">
            </load-component>

        </div>
    </div>
</template>

<script>

// 组件加载程序
import loadComponent from '@htdocs/src/components/ui-component-load/index.vue';

export default {
    name: 'app',
    
    components: {
        loadComponent
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
            return this.components.filter(x => x.id === Number(id))[0];
        }
    },
 
    created () {
        // 语言包
        this.$store.state.page.languages = window.GESHOP_LANGUAGES || {};
        // 加载页面读取页面数据
        this.$store.state.page.env = 3;
        this.$store.state.page.info.page_id = window.GESHOP_PAGE_ID;
        this.$store.state.page.info.site_code = window.GESHOP_SITE_CODE;
        this.$store.state.page.info.lang = window.GESHOP_LANG;
        this.$store.state.page.info.pipeline = window.GESHOP_PIPELINE;
        // 组件布局数据
        this.$store.state.page.layouts = [...window.source_data.layouts];
        this.$store.state.page.components = [...window.source_data.list];
        this.$store.state.page.remote_data_loaded = false;
        // 获取站点编码
        const self = this;
        $(function () {
            setTimeout(function () {
                window.g_infocheck_promise && window.g_infocheck_promise.done(function (res) {
                    // 同步商品数据到store
                    self.$store.state.page.info.od = self.$getCookie('od') || '';
                    self.$store.state.page.info.bts_unique_id = '';
                    self.$store.state.page.info.country_code = res.country_code || '';
                    self.$store.dispatch('page/load_remote_goods_data', {
                        is_first: 1
                    });
                });
            }, 0);
        });
        // [...window.source_data.list].map(cmpt => {
        //     Array.isArray(cmpt.goodsSKU) && cmpt.goodsSKU.map(item => {
        //         this.$store.state.page.goodsSKU.push(Object.assign({}, item));
        //     });
        // });
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

@import '../../../../htdocs/src/less/zaful.less';

a {
    text-decoration: none;
}

ul {
    list-style: none;
}

</style>
