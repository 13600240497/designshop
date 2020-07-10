import { getCoupon } from './actions/coupon';

const get_media_paltform = () => {
    const e = document.documentElement.clientWidth || 1200;
    const t = 'pc';
    const s = 'pad';
    const i = 'wap';
    return e < 768 ? i : e > 1024 ? t : s;
};

const dresslily = {
    namespaced: true,
    state: {
        /**
         * D网，用户访问的真实设备 [web/app]
         * D网首页没有 [APP]
         */
        device_platform: window.GESHOP_PLATFORM ? window.GESHOP_PLATFORM : 'web',
        /**
         * D网根据媒体查询区分3个值
         * @default pc
         * @return {string} [pc/pad/wap]
         * @example
         * pc > 1024
         * 1024 > pad > 768
         * wap < 768
         */
        media_platform: sessionStorage.getItem('gs_media_platform') || 'pc',
        /**
         * onResize 绑定事件回调队列
         */
        onresize_marque: [],
        // 倒计时组件状态
        /**
         * 根据时间戳返回状态
         * 0 = 未开始
         * 1 = 已经开始
         * 2 = 结束
         * 3 = 异常情况
         *  */
        countdown_status: {},
        lott_params: {},
        left_times: 0, // 转盘抽奖次数
        coupon_redeem: [] // 存放积分兑换优惠券数据
    },
    mutations: {
        /**
         * window.onResize 函数存储的队列
         */
        update_onresize_marque (state, callback) {
            state.onresize_marque.push(callback);
        },
        /**
         * 更新 media_platform 值
         */
        update_media_platform (state, val) {
            state.media_platform = val;
            sessionStorage && sessionStorage.setItem('gs_media_platform', val);
        },
        // 更新倒计时状态
        updateStatus (state, val) {
            state.countdown_status = Object.assign({}, state.countdown_status, val);
        },
        coupon_all (state, d) {
            state.coupon_redeem = d.data;
        },
        set_left_time (state, d) {
            state.left_times = d;
        },
        update_lott_params (state, d) {
            state.lott_params = Object.assign({}, d);
        }

    },
    actions: {
        /**
         * window.onResize 事件触发，调用 marque 队列里面所有的函数
         */
        handleResize ({ state, commit }) {
            // 更新值
            commit('update_media_platform', get_media_paltform());
            // 调用函数队列
            state.onresize_marque.map(callback => {
                callback();
            });
        },
        getCoupon: getCoupon
    }
};

export default dresslily;
