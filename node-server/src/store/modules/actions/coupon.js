/**
 * DL积分兑换优惠券
 * @param context  vuex context
 * @param id coupon_id
 */
export const getCoupon = (context, id) => {
    if ($('.geshop-U000246-template1_v1').length) {
        typeof GEShopSiteCommon !== 'undefined' && GEShopSiteCommon.appLogin(); // 初始判断app是否登录
        let couponArr = [];
        let platform = typeof GESHOP_PLATFORM != 'undefined' ? GESHOP_PLATFORM : 'PC';
        $('.geshop-U000246-template1_v1').each(function (index, item) {
            if ($(this).data('couponid')) {
                couponArr.push($(this).data('couponid'));
            }
        });
        // 初始化
        if (id && couponArr.indexOf(id) < 0) {
            couponArr.push(id);
        }
        const setArr = new Set(couponArr);
        let newArr = [...setArr];
        if (GESHOP_PLATFORM === 'app') { // app 区分ios 安卓
            if (/(iPhone|iPad|iPod|iOS)/i.test(navigator.userAgent)) {
                platform = 'ios';
            } else if (/(Android)/i.test(navigator.userAgent)) {
                platform = 'android';
            }
        }
        // const user_id = typeof GEShopSiteCommon !== 'undefined' && GEShopSiteCommon.getCookie('WEBF-user_id') ? GEShopSiteCommon.getCookie('WEBF-user_id').substr(1) : '';
        const data = {
            lang: GESHOP_LANG || 'EN',
            couponid: newArr.join(','),
            pipeline: (typeof GESHOP_PIPELINE !== 'undefined' ? GESHOP_PIPELINE : ''),
            platform: platform
            // user_id: user_id
        };
        const url = GESHOP_INTERFACE.couponlist.url;
        $.ajax({
            url: url,
            data: { content: JSON.stringify(data) },
            dataType: 'jsonp',
            jsonp: 'callback',
            success: function (res) {
                context.commit('coupon_all', { data: res.data, id: couponArr.join(',') });
            }
        });
    }
};
