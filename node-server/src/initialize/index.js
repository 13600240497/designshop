import store from '../store/index';

/**
 * 根据站点编码，执行不同站点模块的初始化代码
 * @date 2019.05.28
 * @author Cullen
 * @description
 * 目前只对接了2个站点：
 * Zaful:
 * 1. 优惠券请求合并
 * Dresslily:
 * 1. 公共resize事件绑定
 * 2. 首页接入jetlore的 SDK
 */
import zaful from './modules/zaful';
import dresslily from './modules/dresslily';
import rosegal from './modules/rosegal';

/**
 * 根据站点简码执行对应的模块
 * @param {string} sitecode 站点简码
 */
export default function (sitecode) {
    // app预发布写入cookie GESHOP_PLATFORM = 'app'
    // GESHOP_ENV_TYPE
    // 1 开发环境
    // 2 测试环境
    // 3 预发布环境
    // 4 线上环境
    if (window.GESHOP_PLATFORM == 'app' && window.GESHOP_ENV_TYPE == 3) {
        // app 执行
        window.GEShopCommonFn_Vue && window.GEShopCommonFn_Vue.initAppCookieAction();
    };
    const map = {
        'zf': zaful,
        'rg': rosegal,
        'dl': dresslily,
        'gb': () => {
        }
    };
    typeof map[sitecode] === 'function' && map[sitecode]();
    // 发布后的页面 更新当前数据
    // GESHOP_PAGE_TYPE: [1=装修, 2=预览, 3=发布]
    // GESHOP_HAS_AUTO_REFRESH_UI: [1=有自动刷新组件, 0=没有]
    // ================== Test code ==================
    // window.GESHOP_PAGE_TYPE = 3;
    // window.GESHOP_HAS_AUTO_REFRESH_UI = 1;
    // ================== Test code ==================
    if (typeof window.GESHOP_PAGE_TYPE != 'undefined' && window.GESHOP_PAGE_TYPE == 3 && typeof window.GESHOP_HAS_AUTO_REFRESH_UI != 'undefined' && window.GESHOP_HAS_AUTO_REFRESH_UI == 1) {
        store.dispatch('global/updateNowInfo');
    } else {
        // 装修页 和预览页
        if (typeof GESHOP_ASYNC_DATA_INFO === 'undefined') {
            window.GESHOP_ASYNC_DATA_INFO = {};
        }
        // 装修预览也丢到 store 里面
        store.commit('global/UPDATE_GOODS_INFO', Object.assign({}, window.GESHOP_ASYNC_DATA_INFO));
    }
}
