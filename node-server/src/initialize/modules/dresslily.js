import store from '../../store/index';

// 绑定 resize 事件
function bind_resize () {
    let resize_timer_locker = null;

    // 首次更新状态
    store.dispatch('dresslily/handleResize');

    // 变更屏幕宽度
    window.addEventListener('resize', () => {
        clearTimeout(resize_timer_locker);
        resize_timer_locker = setTimeout(() => {
            store.dispatch('dresslily/handleResize');
        }, 80);
    }, false);
}

// 装修页接收自动刷新的数据，然后更新 state 的值
const receive_messgae = () => {
    window.addEventListener('message', (res) => {
        if (res && res['data'] && res.data['message_type']) {
            if (res.data.message_type == 'dresslily_update_goods_info') {
                store.commit('global/UPDATE_GOODS_INFO', res.data.data);
            }
        }
    });
};

/**
 * 初始化积分兑换优惠券的信息，合并数据只发1次请求
 */
function init_coupon () {
    // 1. 获取所有的 ID
    // 2. 发1次请求
    // 3. 放到 store 里面
    // store.commit('zaful/coupon_all', res.data);
    store.dispatch('dresslily/getCoupon');
}

/**
 * 初始化 jetlore
 */
function init_jetlore () {
    store.dispatch('jetlore/loadScript');
}

export default function () {
    // 绑定 resize 事件
    bind_resize();
    // 接受数据
    receive_messgae();
    // 1.初始化积分兑换优惠券
    init_coupon();
    // 初始化 jetlore
    init_jetlore();
}
