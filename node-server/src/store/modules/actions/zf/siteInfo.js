function _getCookie (name) {
    let arr = [];
    let reg = new RegExp('(^| )' + name + '=([^;]*)(;|$)');
    if ((arr = document.cookie.match(reg))) {
        return arr[2];
    } else {
        return null;
    }
}

/**
 * 获取当前设备platform
 * web(默认)——pc端
 * wap——手机浏览器端
 * ios——苹果手机端
 * android——安卓手机端
 * pad——平板电脑端
 * aff——AFF平台
 * prom——营销
 * sitemap——站点地图
 * @private
 */
function _getPlatForm () {
    let result = typeof GESHOP_PLATFORM != 'undefined' ? GESHOP_PLATFORM : 'web';
    switch (result) {
    case 'pc':
        result = 'web';
        break;
    case 'wap':
        result = 'wap';
        break;
    case 'app':
        if (/(iPhone|iOS)/i.test(navigator.userAgent)) {
            result = 'ios';
        } else if (/(Android)/i.test(navigator.userAgent)) {
            result = 'android';
        } else if (/(iPad|iPod)/i.test(navigator.userAgent)) {
            result = 'pad';
        }
        break;
    default:
        result = 'web';
    }
    return result;
}

/**
 * action 获取站点信息
 * @param commit
 */
export default ({ commit }) => {
    $(function () {
        let platform = _getPlatForm();
        setTimeout(() => {
            window.g_infocheck_promise && window.g_infocheck_promise.done(function (res) {
                // 同步商品数据到store
                const params = {
                    od: _getCookie('od') || '', // 千人千面需求, 对应cookie_id
                    bts_unique_id: '', // BTS分流用的ID
                    country_code: res.country_code || '', // 访问用户国家简码
                    platform: platform
                };
                commit('siteInfo_update', params);
            }).fail(function () {
                const params = {
                    platform: platform
                };
                commit('siteInfo_update', params);
            });
        }, 0);
    });
};
