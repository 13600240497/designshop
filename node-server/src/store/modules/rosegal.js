const rosegal = {
    namespaced: true,
    state: {
        coupon2Arr: [] //  存放U000164 和 166 优惠券的数据
    },
    mutations: {
        coupon_all (state, d) {
            state.coupon2Arr = d.data;
        }
    },
    actions: {
        /**
         * 统一获取优惠券
         * @param {*} context 上下文 
         * @param {String} params.component_id 组件ID
         * @param {String} params.coupon_ids 新的优惠券ID
         */
        getCoupon (context, params = {}) {
            if ($('.geshop-component-box.geshop-U000164-coupon2, .geshop-component-box.geshop-U000166-coupon2').length) {
                let couponArr = [];
                let platform = typeof GESHOP_PLATFORM != 'undefined' ? GESHOP_PLATFORM : 'PC';
                
                $('.geshop-component-box.geshop-U000164-coupon2, .geshop-component-box.geshop-U000166-coupon2').each(function (index, item) {
                    // 如果form填写了优惠券id
                    if ($(this).data('0164_couponid')) {
                        const component_id = $(this).data('id');
                        const coupon_ids = $(this).data('0164_couponid').toString().replace(/,/g, '|');
                        // 用数组存起来
                        couponArr.push({
                            component_id,
                            coupon_ids
                        });
                    }
                });

                // form 表单提交了新的coupon_id，根据组件ID替换掉老数据
                if (params.component_id && params.coupon_ids) { 
                    couponArr = couponArr.filter(x => {
                        if (x.component_id == params.component_id) {
                            x.coupon_ids = params.coupon_ids.toString().replace(/,/g, '|');
                        }
                        return x;
                    });
                }
                
                // 没有id返回，不发请求
                if (couponArr.length < 1) {
                    return false;
                }

                // app 区分ios 安卓
                if (GESHOP_PLATFORM === 'app') { 
                    if (!sessionStorage['is_app_login']) {
                        //  APP端进来跳一次登录deeplink（若已登陆会直接重新刷新页面）
                        // 该逻辑为 先调用站点的 info_check 之后才调站点回调函数 appUserInfo 向PHP注册 用户登录信息然后延迟刷新页面，重走页面接口请求流程
                        $.when(window.deferred).then(() => {
                            setTimeout(() => {
                                window.location.href = 'webAction://checkUserInfo?callback=appUserInfo()&isAlert=0';
                            }, 100);
                        });
                    }
                }

                // 拼装请求参数
                const request = {
                    lang: GESHOP_LANG || 'en',
                    couponid: couponArr.map(x => x.coupon_ids).join(','),
                    pipeline: (typeof GESHOP_PIPELINE != 'undefined' ? GESHOP_PIPELINE : ''),
                    platform: platform
                };
                try {
                    sessionStorage['164_166_couponInfo'] = null;
                } catch (e) {}
                $.ajax({
                    url: GESHOP_INTERFACE.couponlist.url,
                    data: { content: JSON.stringify(request) },
                    dataType: 'jsonp',
                    jsonp: 'callback',
                    success: function (res) {
                        couponArr.map((item, index) => {
                            res.data.couponInfo[index].component_id = item.component_id;
                        });
                        try {
                            sessionStorage['164_166_couponInfo'] = JSON.stringify(res.data.couponInfo);
                        } catch (e) {}
                        context.commit('coupon_all', { data: res.data.couponInfo });
                    }
                });
            }
        }
    }
};

export default rosegal;
