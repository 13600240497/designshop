import Vue from 'vue'
// 预览页主页面
import App from './views/page-preview/index.vue';

// 装修预览页的 vuex
import store from './store/preview-index.js';

// 所有的UI组件的公共控件
import All_ui_unit from '../../files/parts/vueComponents/index';
Vue.use(All_ui_unit);

// 组件级别懒加载
import VueLazyComponent from '@xunlei/vue-lazy-component';
Vue.use(VueLazyComponent);

// pixel 转 rem
Vue.prototype.$px2rem = function (pixel = 0) {
    return (pixel / 75) + 'rem';
};

// 读取 cookie
Vue.prototype.$getCookie = function (name) {
    let arr = [];
    let reg = new RegExp('(^| )' + name + '=([^;]*)(;|$)');
    if ((arr = document.cookie.match(reg))) {
        return arr[2];
    } else {
        return null;
    }
};

// VUE实例
const app = new Vue({
    el: '#preview-app',
    store,
    render: h => h(App),
    created () {
        /**
         * 用户人群处理
         * @param {number} cookie['WEBF-dan_num'] 订单数量
         */
        const order_number = Number(this.$getCookie('WEBF-dan_num')) || 0;
        store.state.page.isNewGuys = order_number < 1;

        // 获取页面数据

        // 语言包
        this.$store.state.page.languages = window.GESHOP_LANGUAGES || {};
        // 加载页面读取页面数据
        this.$store.state.page.env = 2;
        this.$store.state.page.info.page_id = GESHOP_PAGE_ID;
        this.$store.state.page.info.site_code = GESHOP_SITE_CODE;
        this.$store.state.page.info.lang = GESHOP_LANG;
        this.$store.state.page.info.pipeline = GESHOP_PIPELINE;
        // 组件布局数据
        this.$store.state.page.layouts = [...window.source_data.layouts];
        this.$store.state.page.components = [...window.source_data.list];
        this.$store.dispatch('page/load_remote_goods_data', {
            is_first: 1
        });
        // 埋点初始化
        this.$store.dispatch('growingio/init');
    }
});
window.GESHOP_STORE = store;
window.GESHOP_VM = app;
