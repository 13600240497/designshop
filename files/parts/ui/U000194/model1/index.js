;$(function () {
    $('[data-gid="U000194_default"] .gb-list-imglist').each(function (index, item) {
        var isDefault = $(this).data('is-default');
        $(item).hover(function () {
            var haveProduct = $(this).data('have-product');
            if (isDefault == 1) {
                $(this).find('.gb-list-product-box').removeClass('box-hidden');
            } else {
                if (haveProduct == 0) {
                    $(this).find('.gb-list-product-box').addClass('box-hidden');
                } else {
                    var haveHidden = $(this).find('.gb-list-product-box').hasClass('box-hidden');
                    if (haveHidden) {
                        $(this).find('.gb-list-product-box').removeClass('box-hidden');
                    }
                };
            }
        }, function () {
            if (isDefault == 1) {
                $(this).find('.gb-list-product-box').addClass('box-hidden');
            } else {
                var haveGoodsImg = $(this).data('have-goodsimg');
                if (haveGoodsImg == 1) {
                    $(this).find('.gb-list-product-box').addClass('box-hidden');
                }
            }
        })
    })
});