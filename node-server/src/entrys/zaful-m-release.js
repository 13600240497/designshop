import store from '../store/index';
import App from '../views/release/zaful-m.vue'; // ZAFUL - M 端发布页主页面
import All_ui_unit from '../../../files/parts/vueComponents/index.js'; // 所有的UI组件的公共控件
import VueLazyComponent from '@xunlei/vue-lazy-component'; // 组件级别懒加载

// inView 兼容垫包
require('intersection-observer'); 

// VUE Core library
const { Vue } = window;

// 注册所有的UI公共组件
Vue.use(All_ui_unit);

// 注册组件懒加载模块
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
    el: '#release-app',
    store,
    render: h => h(App),
    created () {
        /**
         * 用户人群处理
         * @param {number} cookie['WEBF-dan_num'] 订单数量
         */
        const order_number = Number(this.$getCookie('WEBF-dan_num')) || 0;
        store.state.page.isNewGuys = order_number < 1;
        // 初始化埋点
        store.dispatch('growingio/init');
    }
});
window.GESHOP_VM = app;
