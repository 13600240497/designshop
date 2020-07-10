import Vue from 'vue';
import ElementUI from 'element-ui';
import 'element-ui/lib/theme-chalk/index.css';
import App from './components/DesignZF.vue';
import xValid from './components/plugins/xValid'

Vue.use(ElementUI);
Vue.use(xValid);

import Antd from 'ant-design-vue/es';
Vue.use(Antd);

// 统一校验，依赖store的page模块的参数
import GEShopCommonValidFn from './library/geshop-common-validate';
Vue.prototype.$valid = new GEShopCommonValidFn({
	site_code: window.GESHOP_SITECODE,
	lang: window.GESHOP_LANG,
	client: window.GESHOP_PLATFORM,
	pipeline: window.GESHOP_PIPELINE
});

// 新统一样式弹出层
import dialog from './components/dialog/dialog.vue'
Vue.component('design-dialog', dialog);

window.vm = new Vue({
	el: '#app',
	components: { App }
});
