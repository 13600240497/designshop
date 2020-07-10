import store from '../../store/index';

/**
 * 初始化积分兑换优惠券的信息，合并数据只发1次请求
 */
function init_coupon () {
    // 1. 获取所有的 ID
    // 2. 发1次请求
    // 3. 放到 store 里面
    // store.commit('zaful/coupon_all', res.data);
    store.dispatch('rosegal/getCoupon');
}

/**
 * RG 活动页最外层容器增加站点标识
 */
function init_site_class () {
    const $target = $('#geshop-page-content,#design_view');
    $target.addClass('geshop_is_' + window.GESHOP_PLATFORM || 'pc').attr('data-style', 'rg');
}

/**
 * RG-PC，加购弹窗，加购成功回调事件
 * @param {object} res postMessage 消息提示回调的数据
 * @param {string} res.data.message_type 消息提示的类型, [1234=加购成功发的消息类型]
 * @param {Number} res.data.status 判断加购动作是否成功，[1=成功]，[0=失败]
 */
const add_bag_callback = (res) => {
    if (res.data.message_type === 1234 && res.data.status == 1 && GESHOP_SITECODE == 'rg-pc') {
        // 1. 关闭弹层
        layer && layer.closeAll();

        // 2. 弹出加购提示
        const html = `
            <style>.gs-common-add-success { background: #ffffff; border-radius: 0.24rem; position: relative; word-break: break-all; } .gs-common-add-success .ico-close { position: absolute; right: -12px; top: -15px; z-index: 10002; width: 30px; height: 30px; margin-left: 0; background-image: url(https://geshopimg.logsss.com/uploads/3JljRyBxmUb6iDrAGvpcwH1Lh7sY9WFz.png); background-repeat: no-repeat; background-position: center; cursor: pointer; } .gs-common-add-success p { text-align: center; padding: 60px 15px 30px 15px; font-size: 20px; } .gs-common-add-success .gs-common-add-checknow-link { border: none; width: 150px; height: 40px; background: #fa386a; font-size: 16px; color: #fff; line-height: 40px; display: block; text-align: center; margin: 0 auto; font-weight: bold; text-transform: uppercase; padding: 0 20px; box-sizing: border-box; }</style>
            <div class="gs-common-add-dialog">
                <div class="gs-common-add-success">
                    <i class="ico-close"></i>
                    <p>${GESHOP_LANGUAGES['rg_addcart_success']}</p>
                    <div class="gs-common-add-checknow-link">${GESHOP_LANGUAGES['check_now']}</div>
                </div>
            </div>`;
        typeof GEShopSiteCommon !== 'undefined' && GEShopSiteCommon.dialog.content(html, 420, 220);

        // 关闭弹窗按钮事件
        $('.gs-common-add-dialog').off('click').click(function () {
            GEShopSiteCommon.dialog.unblock();
        });

        // 跳转购物车页
        $('.gs-common-add-checknow-link').off('click').click(function () {
            window.location.href = DOMAIN_CART + GESHOP_LANG + '/m-flow-a-cart.htm';
        });

        // 定时3秒关闭弹窗事件
        setTimeout(() => {
            GEShopSiteCommon.dialog.unblock();
        }, 3000);
    }
};

export default function () {
    // 1.初始化积分兑换优惠券
    init_coupon();
    init_site_class();
    // 2. 监听站点 iframe 加购成功的 postMessage, 2019.06.15 Cullen
    window.addEventListener('message', add_bag_callback);
}
