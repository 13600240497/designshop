function geshop_U000026_rule_init(id) {
    const $root = $(`.geshop-component-box[data-id="${id}"]`);
    const $body = $root.find('.geshop-U000026-dialog-container');

    $root.on('click', '.dialog-btn', function () {
        if (GEShopSiteCommon) {
            GEShopSiteCommon.dialog.init(function () {
                $.blockUI({
                    message: $body,
                    css: {
                        transform: 'translate(-50%, -50%)',
                        width: 270,
                        top: '50%',
                        left: '50%',
                        border: 'none',
                        cursor: 'default'
                    },
                    onBlock: function () {
                        $body.on('click', '.dialog-close', function () {
                            GEShopSiteCommon.dialog.unblock();
                        });
                    }
                })
            });
        }
    });
}