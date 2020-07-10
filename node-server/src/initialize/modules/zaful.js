import store from '../../store/index';
import GA from '../../library/google-analytics';

/**
 * 初始化优惠券的信息，合并数据只发1次请求
 */
function init_coupon () {
    // 1. 获取所有的 ID
    // 2. 发1次请求
    // 3. 放到 store 里面
    // store.commit('zaful/coupon_all', res.data);
    // GEShopSiteCommon.appLogin(() => {
    store.dispatch('zaful/getCoupon');
    // });
}

/**
 * 更新store.state.zaful.siteInfo
 */
function init_site () {
    store.dispatch('zaful/getSiteInfo');
}

export default function () {
    // 1.初始化优惠券
    init_coupon();
    init_site();
    // 2. M端接入 growing-io
    if (window.GESHOP_PLATFORM === 'app' || window.GESHOP_PLATFORM === 'wap') {
        store.dispatch('growingio/init');
    }
    // 3. 初始化 Google Analytics 埋点
    const ga = new GA();
    ga.init();
    window.geshop_ga = ga;
}
