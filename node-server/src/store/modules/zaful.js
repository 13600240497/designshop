import siteInfo from './actions/zf/siteInfo';

const zaful = {
    namespaced: true,
    state: {
        updateID: '',
        couponID: '',
        couponModel7Arr: [],
        siteInfo: {} // 站点数据
    },
    mutations: {
        coupon_all (state, d) {
            state.couponModel7Arr = [];
            state.couponModel7Arr = d.data;
            state.couponID = d.id;
        },
        siteInfo_update (state, d) {
            state.siteInfo = d;
        }
    },
    actions: {
        getCoupon (context, id) {
            if ($('.geshop-component-box.geshop-U000086-model7, .geshop-component-box.geshop-U000087-model7').length) {
                GEShopSiteCommon.appLogin();
                let couponArr = [];
                let platform = typeof GESHOP_PLATFORM != 'undefined' ? GESHOP_PLATFORM : 'PC';
                $('.geshop-component-box.geshop-U000086-model7, .geshop-component-box.geshop-U000087-model7').each(function (index, item) {
                    couponArr.push($(this).data('couponid'));
                });
                // 初始化
                if (id && couponArr.indexOf(id) < 0) {
                    couponArr.push(id);
                }
                const setArr = new Set(couponArr);
                let newArr = [...setArr];

                if (GESHOP_PLATFORM === 'app') { // app 区分ios 安卓
                    /* if (typeof GEShopSiteCommon !== 'undefined') {
                            //  APP端进来如果拿不到user-token再跳一次登录deeplink（若已登陆会直接重新刷新页面）
                            if (!GEShopSiteCommon.getCookie('user-token')) {
                                window.location.href = 'webAction://login?callback=appUserInfo()&isAlert=0';
                            }
                        } */
                    if (/(iPhone|iPad|iPod|iOS)/i.test(navigator.userAgent)) {
                        platform = 'ios';
                    } else if (/(Android)/i.test(navigator.userAgent)) {
                        platform = 'android';
                    }
                }
                const data = {
                    lang: GESHOP_LANG || 'EN',
                    couponid: newArr.join(','),
                    pipeline: (typeof GESHOP_PIPELINE != 'undefined' ? GESHOP_PIPELINE : ''),
                    platform: platform
                };
                $.ajax({
                    url: GESHOP_INTERFACE.goods_couponlist_new.url,
                    data: { content: JSON.stringify(data) },
                    dataType: 'jsonp',
                    jsonp: 'callback',
                    success: function (res) {
                        context.commit('coupon_all', { data: res.data, id: couponArr.join(',') });
                    }
                });
            }
        },
        getSiteInfo: siteInfo
    }
};

export default zaful;
