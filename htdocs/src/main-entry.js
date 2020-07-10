import Vue from 'vue'

// ant-design
import Antd from 'ant-design-vue/es'
import 'ant-design-vue/dist/antd.css'
Vue.use(Antd);

import ElementUI from 'element-ui';
Vue.use(ElementUI);

import Moment from 'vue-moment'
Vue.use(Moment);

import { Modal, message } from 'ant-design-vue/es';
Vue.prototype.$confirm = Modal.confirm;
Vue.prototype.$message = message;

// 前端路由
import router from './router/index.js'

// 前端数据中心
import store from './store/design-index.js'

// 新统一样式弹出层
import dialog from './components/dialog/dialog.vue'
Vue.component('design-dialog', dialog);

// 接口
import api from './plugin/api'
Vue.prototype.$api = api;

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

// 统一校验，依赖store的page模块的参数
import GEShopCommonValidFn from './library/geshop-common-validate';
Vue.prototype.$valid = new GEShopCommonValidFn({ store });

// 页面入口
import App from './layout/main-entry.vue';

// 设置站点COOKIE
(function () {
    const setCookie = function (name, value) {
        var Days = 30
        var exp = new Date()
        exp.setTime(exp.getTime() + Days * 24 * 60 * 60 * 1000)
        document.cookie = name + '=' + escape(value) + ';expires=' + exp.toGMTString() + ';path=/' + ';';
    }
    var href = window.location.href || '';
    var params_str = href.split('?')[1] || '';
    var params = params_str.split('&');
    params.map(str => {
        if (str) {
            const key = str.split('=')[0];
            if (key == 'site_group_code') {
                const value = str.split('=')[1] || '';
                value != '' && setCookie('site_group_code', value);
                console.log(value);
            }
        }
    });
})();

/* exported GESHOP_VM */
const GESHOP_VM = new Vue({
    el: '#app',
    router,
    store,
    render: h => h(App)
});
window.GESHOP_VM = GESHOP_VM;
window.GESHOP_STORE = store;
