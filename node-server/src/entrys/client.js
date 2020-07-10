// 兼容包
import 'babel-polyfill';
// Require the polyfill before requiring any other modules.
import 'intersection-observer';

// VUEX
import store from '../store/index';
// 注册组件统一入口组件
import layout from '../geshop-component.vue';

import image_goods from '../../../files/parts/vueComponents/image_goods/index.vue'; // 图片
import dialog_add_bag from '../../../files/parts/vueComponents/dialog_add_bag/index.vue'; // 加购弹窗
import market_price from '../../../files/parts/vueComponents/market_price/index.vue'; // 市场价
import shop_price from '../../../files/parts/vueComponents/shop_price/index.vue'; // 销售价
import shop_price_rg_m_2 from '../../../files/parts/vueComponents/shop_price/rg-m-2.vue'; // 销售价 rg-m-2
import progressBar from '../../../files/parts/vueComponents/progress_bar/index.vue'; // 进度条
import progressText from '../../../files/parts/vueComponents/progress_text/index.vue'; // 进度条描述
import discount from '../../../files/parts/vueComponents/discount_float/index.vue'; // 折扣标
import discount_rg_m_2 from '../../../files/parts/vueComponents/discount_float/rg-m-2.vue'; // RG-M折扣标
import soldout from '../../../files/parts/vueComponents/sold_out/index.vue'; // 售罄
import analytics_href from '../../../files/parts/vueComponents/analytics_href/index.vue'; // 埋点跳转
import timer from '../../../files/parts/vueComponents/timer/index.vue'; // 倒计时
import buynow from '../../../files/parts/vueComponents/button_buy/index.vue'; // 购买按钮
import button_quick_view from '../../../files/parts/vueComponents/button_quick_view/index.vue'; // 快速购买按钮
import pagination from '../../../files/parts/vueComponents/pagination/index.vue'; // 分页
import title from '../../../files/parts/vueComponents/goods_title/index.vue'; // 商品title
import cutdown from '../../../files/parts/vueComponents/cutdown/index.vue'; // 纯倒计时
import stock_tip from '../../../files/parts/vueComponents/stock_tip/index.vue'; // 图片
import promotion from '../../../files/parts/vueComponents/promotion/index.vue'; // 营销信息
import promotionDL from '../../../files/parts/vueComponents/promotion-dl/index.vue'; // 营销信息DL

// 一如公共方法，必须定义在其他之前
import '../library/geshop-vue-common';

// 根据 GESHOP_LANG 获取 config.json 语言包，后面语言存到数据库，后续会干掉这个函数
import '../library/get_languages_by_code';
// 封装 JSONP, 支持promise语法
import '../library/promise-jsonp';
// HTML解码
import '../library/htmldecode';
// 商品运营平台接口
import '../library/goodsData';

/**
 * 页面各站点的初始化函数
 * 根据站点 code 区分
 * 目前有 zaful, rosegal, dresslily, gearbest
 * */
import initialize from '../initialize/index';

// 加入 in-view https://github.com/camwiegert/in-view
import inView from 'in-view';
// 新增复制链接插件
import VueClipboard from 'vue-clipboard2';

window.inView = inView;

// 插入组件公共图标, iconfont
const iconLink = window.document.createElement('link');
iconLink.rel = 'stylesheet';
iconLink.setAttribute('href', '//at.alicdn.com/t/font_1508924_68obwjkhx59.css');
window.document.body.appendChild(iconLink);

// 绑定到 windows
window.GESHOP_STORE = store;

const { Vue } = window;

// 批量全局注册组件和元素
[
    layout,
    image_goods,
    dialog_add_bag,
    market_price,
    shop_price,
    shop_price_rg_m_2,
    progressBar,
    progressText,
    discount,
    discount_rg_m_2,
    soldout,
    analytics_href,
    timer,
    buynow,
    button_quick_view,
    pagination,
    title,
    cutdown,
    stock_tip,
    promotion,
    promotionDL
].forEach(component => {
    Vue.component(component.name, component);
});

// pixel 转 rem
Vue.prototype.$px2rem = function (pixel = 0) {
    return (pixel / 75) + 'rem';
};

// 读取数据库的语言包，放到 Vue 原型里面，2019-07-19, By Cullen
Vue.prototype.$lang = (key) => {
    return GESHOP_LANGUAGES_V2[key] || key;
};

Vue.use(VueClipboard);
// 执行各个站点的初始化函数
initialize(window.GESHOP_SITECODE.split('-')[0]);
