import Vue from 'vue';

// 图片
// import image_goods from '../../files/parts/vueComponents/image_goods/index.vue';
// 市场价
import market_price from '../../files/parts/vueComponents/market_price/index.vue';
// 销售价
import shop_price from '../../files/parts/vueComponents/shop_price/index.vue';
// 进度条
// import progressBar from '../../files/parts/vueComponents/progress_bar/index.vue';
// 折扣标
// import discount from '../../files/parts/vueComponents/discount_float/index.vue';
// 购买按钮
// import buynow from '../../files/parts/vueComponents/button_buy/index.vue';
// 售罄
// import soldout from '../../files/parts/vueComponents/sold_out/index.vue';
// 埋点跳转
// import analytics_href from '../../files/parts/vueComponents/analytics_href/index.vue';
// 倒计时
// import timer from '../../files/parts/vueComponents/timer/index.vue';

// 注册组件
import layout from './geshop-component.vue';
Vue.component('geshop-component', layout);

const commonComponents = [
    market_price,
    shop_price
    // image_goods,
    // progressBar,
    // discount,
    // buynow,
    // soldout,
    // analytics_href,
    // timer,
];

// 批量全局注册组件和元素
commonComponents.forEach(component => {
    Vue.component(component.name, component);
});

// 获取异步组件，调用获取数据的回调
function getAsyncData () {
    return new Promise((resolve, reject) => {
        const component = () => import(
            /* webpackMode: "lazy" */
            /* webpackPrefetch: true */
            /* webpackPreload: true */
            `../../files/parts/ui/U000224/test/index.vue`);
        component().then(module => {
            module.default.asyncData().then(res => {
                resolve(res);
            });
        });
    });
};

export default context => {
    return new Promise((resolve, reject) => {
        // 获取异步组件的数据
        getAsyncData(context).then(res => {
            context.state = res;
            const app = new Vue({
                data: context,
                render: h => h(layout, {
                    props: {
                        pid: context.pageInstanceId,
                        uikey: context.key,
                        theme: context.theme,
                        data: context.data
                    }
                })
            });
            resolve(app);
        });
    });
};
