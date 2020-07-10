$(function () {

    var isEditenv = $('input[name=is-editenv]').val();
    $("body").on("click", ".js-color-btn", function (e) {

        var $this = $(this);
        if ($this.next().data('attr-num') != 1) {
            $('body').addClass('fixed');
            if ($this.hasClass("on")) {
                $this.removeClass("on");
                $this.closest(".attribute-wrap").find(".shad-wrap").hide();
            } else {
                $this.addClass("on");
                $('.js-color-attribute').hide();
                $this.closest(".attribute-wrap").find(".js-color-attribute").show();
                $this.closest(".attribute-wrap").find(".shad-wrap").show();
            }
        }
        e.preventDefault();
    });
    $("body").on("click", ".js-size-btn", function (e) {
        var $this = $(this);

        if ($this.next().data('attr-num') != 1) {
            $('body').addClass('fixed');
            if ($this.hasClass("on") && $this.next().data('attr-num') != 1) {
                $this.removeClass("on");
                $this.closest(".attribute-wrap").find(".js-size-attribute").hide();
                $this.closest(".attribute-wrap").find(".shad-wrap").hide();
            } else {
                $this.addClass("on");
                $('.js-size-attribute').hide();
                $this.closest(".attribute-wrap").find(".js-size-attribute").show();
                $this.closest(".attribute-wrap").find(".shad-wrap").show();
            }
        }

        e.preventDefault();
    });

    $("body").on("click", function (e) {

        var ele = window.event || e;
        var element = ele.target || ele.srcElement;
        if (element && !$(element).hasClass("js-color-btn")) {
            $(".js-color-attribute").hide();
            $(".js-color-btn").removeClass("on");
        }
        if (element && !$(element).hasClass("js-size-btn")) {

            $(".js-size-attribute").hide();
            $(".js-size-btn").removeClass("on");

        }
        if (element && !$(element).hasClass("js-attribute-btn")) {
            $(this).removeClass('fixed');
            $('.shad-wrap').hide();
        }
        $(".js-attribute-list").each(function (k, v) {
            if ($(v).css("display") == "block") {
                if (($(v).closest("li").data("goods") != $(element).closest("li").data("goods")) && $(element).hasClass("js-attribute-btn")) {
                    $(v).hide();
                    $(v).closest(".attribute-wrap").find(".js-attribute-btn").removeClass("on");
                }
            }
        });

    });

    $("body").on("click", ".js-attribute-list a", function (e) {
        var $this = $(this);
        $(this).addClass('on').siblings().removeClass('on');
        var value = $this.text();
        var type = $this.data("type");
        $this.closest(".attribute-wrap").find(".js-attribute-value").text(value);
        $this.closest(".js-attribute-list").hide();
        $this.closest(".js-attribute-list").prev(".js-attribute-btn").removeClass("on").attr("title", value);
        var $parentLi = $this.closest("li");
        changeAttr($parentLi);
        e.preventDefault();
    });

    $(document).on("click", '.checknow-link', function (e) {

        if (GESHOP_SITECODE == 'rg-app') {
            window.location.href = "rosegal://action?actiontype=11&name=wishList&source=deeplink"
        } else if (GESHOP_SITECODE == 'rg-wap') {
            window.location.href = DOMAIN_CART + '/m-flow-a-cart.htm';
        }
    });

    $("body").on("click", ".ico-close", function (e) {
        layer.closeAll();
    });


    $("body").on("click", ".js-add-cart", function (e) {
        if ($(this).hasClass('unclickable')) {
            return;
        }
        var lis = $(this).parents('.combo-wrap').find('.goods-list li');
        var cartNum = $(this).parents('.combo-wrap').find('input[name=cart-num]');
        var sns = [], goodsIds = [];
        for (var index = 0; index < lis.length; index++) {
            var element = lis[index];
            var sn = $(element).attr('data-goods');
            sns.push(sn);
            var snObj = JSON.parse($(element).attr('data-sn'));
            var snId;
            for (var key in snObj) {
                if (snObj.hasOwnProperty(key)) {
                    var element = snObj[key];
                    if (element === sn) {
                        snId = key;
                    }
                }
            }
            goodsIds.push(snId);
            // var actNum = $(element).attr('data-activenum');
            // var isOnSale = $(element).attr('data-onsale');
            // if(actNum == 0 || isOnSale == 0){
            //   return;
            // }

        }
        if (GESHOP_SITECODE == 'zf-app') {
            window.location.href = "zaful://action?actiontype=16&url=" + goodsIds.join(',') + "&source=deepLink";
            setTimeout(function () {
                successCb();
            }, 1000);
        } else if (GESHOP_SITECODE == 'rg-app') {
            var goodsNum = new Array(goodsIds.length + 1).join('1').split('').join(',');
            window.location = "webAction://addToCart?goodsId=" + goodsIds.join(',') + "&num=" + goodsNum + "&callback=addsucess(a)&goods_type=&p_source=recruit2.0";
            setTimeout(function () {
                successCb();

            }, 1000);
        } else {

            var lang = typeof GESHOP_LANG != 'undefined' ? GESHOP_LANG : 'en'
            var params = {
                lang: lang,
                platform: GESHOP_PLATFORM,
                skus: sns.join(',')
            }
            var url = GESHOP_INTERFACE.goods_comboaddcart.url;
            window.GEShopCommonFn_Vue.$jsonp(url,params).done(function (res) {
                if (res.code == '0') {
                    successCb(cartNum)
                }
            });
        }

    })


    //renderHtml();  //请求数据

    function successCb(cartNum) {

        if (GESHOP_SITECODE == 'zf-wap' || GESHOP_SITECODE == 'zf-app') {
            var originalCartNum = $('#js_topCartNum').text() == '' ? 0 : parseInt($('#js_topCartNum').text());
            if (originalCartNum + parseInt(cartNum.val()) > 99) {
                $('#js_topCartNum').text('99+');
            } else {
                $('#js_topCartNum').text(originalCartNum + parseInt(cartNum.val()));
            }
            layer.open({
                content: GESHOP_LANGUAGES.addcart_success
                , skin: 'msg'
                , time: 2
            });
        } else {
            if (GESHOP_SITECODE == 'rg-wap') {
                GLOBAL.cart.cartItems();
            }
            layer.open({
                type: 1,
                style: 'border-radius:9px',
                content: $('.layer-pop').html()
            })
        }
    }

    function changeAttr(parentLi) {
        var parentLi = $(parentLi);
        var size = $.trim(parentLi.find('.js-size-value').text());
        var color = $.trim(parentLi.find('.js-color-value').text());
        var attributeObj = parentLi.data('attribute');
        var goodsidObj = parentLi.data('goodsimg');
        var priceOjb = parentLi.data('price');
        var urlOjb = parentLi.data('url');
        var snOjb = parentLi.data('sn');
        var skuId;
        if ($.trim(color).split(' ').length > 1) {
            color = color.split(' ').join('_')
        }
        if ($.trim(size).split(' ').length > 1) {
            size = size.split(' ').join('_')
        }
        if (size && color) {
            skuId = attributeObj[$.trim(color) + '/' + $.trim(size)];
        }
        parentLi.find('.js-attribute-img').attr('src', goodsidObj[skuId]);
        parentLi.find('>a').attr('href', urlOjb[skuId]);
        parentLi.find('.js-shop-price').text(priceOjb[skuId]);
        parentLi.find('.js-shop-price').attr('data-orgp', priceOjb[skuId]);
        parentLi.find('.js-shop-price').attr('data-bz', priceOjb[skuId]);
        var googsSn = snOjb[skuId];
        parentLi.attr('data-goods', googsSn);
        parentLi.attr('data-goodsid', skuId);
        var lis = parentLi.closest('.goods-list').find('li');
        var sns = [];
        for (var index = 0; index < lis.length; index++) {
            var element = lis[index];
            sns.push($(element).data('goods'));
        }
        if (isEditenv == 0) {
            GLOBAL.currency.change_html();
        }

        var stockObj = parentLi.data('stockobj');
        var isshow = parentLi.data('isshow');
        if (stockObj[skuId]) {
            var stock = stockObj[skuId].split('_');
            if (stock[0] == 0 || stock[1] == 0 || isshow == 0) {
                parentLi.find('.soldout').show();
                parentLi.parents('.combo-wrap').find('.cart-btn-wrap').addClass('unclickable');
            } else {
                parentLi.find('.soldout').hide();
                parentLi.parents('.combo-wrap').find('.cart-btn-wrap').removeClass('unclickable');
            }
        }
        //var lis = parentLi.parent().find('li');
        // lis.each(function(){
        //   var stockObj = $(this).data('stockobj');
        //   var isshow =  $(this).data('isshow');
        //   if(stockObj[skuId]){
        //     var stock = stockObj[skuId].split('_');
        //     if(stock[0] == 0 || stock[1] == 0 || isshow == 0 ){
        //       $(this).find('.soldout').show();
        //       $(this).parents('.combo-wrap').find('.cart-btn-wrap').addClass('unclickable');
        //     }else{
        //       $(this).find('.soldout').hide();
        //       $(this).parents('.combo-wrap').find('.cart-btn-wrap').removeClass('unclickable');
        //     }
        //   }
        //   var display =$(this).find('.soldout').css('display');
        //   if( display == 'block'){
        //     $(this).parents('.combo-wrap').find('.cart-btn-wrap').addClass('unclickable');
        //   }

        // })
        canAddCart();
        getComboPrice(sns, parentLi);
    }

    function canAddCart() {
        $('.geshop-U000192-modal1').each(function () {
            $(this).find('.goods-list li').each(function () {
                var display = $(this).find('.soldout').css('display');
                if (display == 'block') {
                    $(this).parents('.combo-wrap').find('.cart-btn-wrap').addClass('unclickable');
                }
            })
        })
    }

    function getComboPrice(sns, parentLi) {
        var lang = typeof GESHOP_LANG != 'undefined' ? GESHOP_LANG : 'en'
        var params = {
            lang: lang,
            platform: GESHOP_PLATFORM,
            skus: sns.join(',')
        }
        var url = GESHOP_INTERFACE.goods_comboprice.url;
        window.GEShopCommonFn_Vue.$jsonp(url,params).done(function (res) {
            if (res.code == '0') {
                $(parentLi).parents('.combo-wrap').find('.group-price').text(res.data);
                $(parentLi).parents('.combo-wrap').find('.group-price').attr('data-orgp', res.data);
                $(parentLi).parents('.combo-wrap').find('.group-price').attr('data-b', res.data);
                if (isEditenv == 0) {
                    GLOBAL.currency.change_html();
                }
            }
        })
    }

    function renderAttr() {
        $('.geshop-U000192-modal1').each(function () {
            $(this).find('.goods-list li').each(function () {
                var colorAttr = $(this).find('.js-color-attribute').data('color').split(',');
                var sizeAttr = $(this).find('.js-size-attribute').data('size').split(',');
                var colorHtml = "", sizeHtml = "";
                if (unique(colorAttr).length > 1) {
                    var size = $.trim($(this).find('.js-size-value').text());

                    for (var index = 0; index < unique(colorAttr).length; index++) {
                        if (size == unique(sizeAttr)[index]) {
                            colorHtml += '<a class="on" data-color="' + unique(colorAttr)[index] + '" data-type="color">' + unique(colorAttr)[index] + ' </a>';
                        } else {
                            colorHtml += '<a data-color="' + unique(colorAttr)[index] + '" data-type="color">' + unique(colorAttr)[index] + ' </a>';

                        }
                    }
                } else {
                    $(this).find('.js-color-attribute').attr('data-attr-num', 1);
                }

                if (unique(sizeAttr).length > 1) {
                    var size = $.trim($(this).find('.js-size-value').text());
                    for (var index = 0; index < unique(sizeAttr).length; index++) {
                        if (size == unique(sizeAttr)[index]) {
                            sizeHtml += '<a  class="on" data-size="' + unique(sizeAttr)[index] + '" data-type="size">' + unique(sizeAttr)[index] + '</a>';
                        } else {
                            sizeHtml += '<a data-size="' + unique(sizeAttr)[index] + '" data-type="size">' + unique(sizeAttr)[index] + '</a>';
                        }
                    }
                } else {
                    $(this).find('.js-size-attribute').attr('data-attr-num', 1);
                }

                $(this).find('.js-color-attribute').html(colorHtml);
                $(this).find('.js-size-attribute').html(sizeHtml);
                var isOnSale = $(this).attr('data-onsale');
                var actNum = $(this).attr('data-activenum');
                var isshow = $(this).attr('data-isshow');
                if (actNum == 0 || isOnSale == 0 || isshow == 0) {
                    $(this).find('.soldout').show();
                    $(this).parents('.combo-wrap').find('.js-add-cart').addClass('unclickable');
                }

            });
        });
    }

    if (isEditenv != 1) {
        $LAB.script(GESHOP_STATIC + '/resources/javascripts/library/in-view.js').wait(function () {
            $('.geshop-U000192-modal1').each(function (i, v) {

                inView('.geshop-U000192-modal1-' + $(v).data('id')).once('enter', function (el) {
                    renderHtml(el);
                });

            })
        })
    } else {
        renderHtml($('.geshop-U000192-modal1'));
    }

    function renderHtml(el) {

        setTimeout(function () {
            var lang = typeof GESHOP_LANG != 'undefined' ? GESHOP_LANG : 'en'

            var skus = [];
            if (isEditenv == 1) {
                $('.geshop-U000192-modal1').each(function () {
                    if ($(this).find('input[name=goods-sku]').val()) {
                        skus.push($(this).find('input[name=goods-sku]').val().split(','));
                    } else {
                        skus.push([]);
                    }
                });
            } else {
                if ($(el).find('input[name=goods-sku]').val()) {
                    skus.push($(el).find('input[name=goods-sku]').val().split(','));
                } else {
                    skus.push([]);
                }
            }
            var params = {
                lang: lang,
                platform: GESHOP_PLATFORM,
                skus: skus
            }
            var url = GESHOP_INTERFACE.goods_combogoods.url;
            window.GEShopCommonFn_Vue.$jsonp(url,params).done(function (res) {
                if (res.code == '0') {
                    for (var i = 0; i < res.data.length; i++) {
                        var component = isEditenv == 1 ? $('.geshop-U000192-modal1').eq(i) : $(el);
                        var html;
                        var comboArr = Object.keys(res.data[i].goods_list);
                        html = '<input type="hidden" name="cart-num" value="' + comboArr.length + '"/>';
                        html += "<ul class='goods-list clearfix'>";
                        if (res.data[i].goods_list.length == 0) {
                            var html = "<ul class='goods-list clearfix'>";
                            html += '<li>';
                            html += '<a class="goods-img">';
                            html += '<img src="https://geshopimg.logsss.com/uploads/FRcjv8nNOT0BbSfV9ht3ZLemuo1K2AqQ.png" class="js_loadingimg js-attribute-img" data-original="https://geshopimg.logsss.com/uploads/FRcjv8nNOT0BbSfV9ht3ZLemuo1K2AqQ.png" alt="zaful" title="zaful" style="display: inline;">';
                            html += '</a>';
                            html += '	<p class="mt10 price-wrap">';
                            html += '<span class="my_shop_price price js-shop-price" data-orgp="0.00" data-bz="0.00">0.00</span>';
                            html += '</p>';
                            html += '<div class="info-wrap">';
                            html += '<span class="attribute-wrap pr">';
                            html += '<span class="attribute-text  js-color-value js-attribute-value">COLOR</span>';
                            html += '	<span class="arrows js-color-btn js-attribute-btn" title="COLOR"></span>  ';
                            html += '<div class="attribute-list js-attribute-list js-color-attribute" data-color="COLOR">';
                            html += '</div>';
                            html += '</span>';
                            html += '<span class="attribute-wrap pr">';
                            html += '	<span class="attribute-text  js-size-value js-attribute-value">L</span>';
                            html += '<span class="arrows js-size-btn js-attribute-btn"></span>';
                            html += '<div class="attribute-list js-attribute-list js-size-attribute"  data-size="L">';
                            html += '	</div>';
                            html += '</span>';
                            html += '</div>';
                            html += '</li>';
                            html += '<li>';
                            html += '<a class="goods-img">';
                            html += '<img src="https://geshopimg.logsss.com/uploads/FRcjv8nNOT0BbSfV9ht3ZLemuo1K2AqQ.png" data-original="https://geshopimg.logsss.com/uploads/FRcjv8nNOT0BbSfV9ht3ZLemuo1K2AqQ.png" style="display: inline;">';
                            html += '</a>';
                            html += '	<p class=" price-wrap">';
                            html += '<span class="my_shop_price price js-shop-price" data-orgp="0.00" data-bz="0.00">0.00</span>';
                            html += '</p>';
                            html += '<div class="info-wrap">';
                            html += '<span class="attribute-wrap pr">';
                            html += '<span class="attribute-text  js-color-value js-attribute-value">COLOR</span>';
                            html += '	<span class="arrows js-color-btn js-attribute-btn" title="COLOR"></span>  ';
                            html += '<div class="attribute-list js-attribute-list js-color-attribute" data-color="COLOR">';
                            html += '</div>';
                            html += '</span>';
                            html += '<span class="attribute-wrap pr">';
                            html += '	<span class="attribute-text  js-size-value js-attribute-value">L</span>';
                            html += '<span class="arrows js-size-btn js-attribute-btn"></span>';
                            html += '<div class="attribute-list js-attribute-list js-size-attribute"  data-size="L">';
                            html += '	</div>';
                            html += '</span>';
                            html += '</div>';
                            html += '</li>';
                            html += '</ul>';
                            html += '<div class="cart-wrap">';
                            html += '<p><span class="my_shop_price group-price fb js-group-price" data-orgp="0.00" data-bz="0.00">0.00</span></p>';
                            html += '<p><del class="my_shop_price total-price fb js-total-price" data-orgp="0.00" data-bz="0.00">0.00</del></p>';
                            html += '<div class="cart-btn-wrap js-add-cart">';
                            html += '<i class="ico-cart"></i>';
                            html += '<span class="cart-text fb"> GET THE SET</span> ';
                            html += '</div>';

                            $('.geshop-U000192-modal1').each(function () {
                                if (!$(this).find('.combo-wrap').html()) {
                                    $(this).find('.combo-wrap').html(html);
                                }
                            });
                        } else {
                            var index = 0;
                            var skus = component.find('input[name=goods-sku]').val().split(',');
                            for (var key in res.data[i].goods_list) {
                                if (res.data[i].goods_list.hasOwnProperty(skus[index])) {

                                    var obj = res.data[i].goods_list[skus[index]];
                                    var size = '', color = '', attributeObj = {}, goodsIdObj = {}, colorObj = {},
                                        dataObj = {}, priceObj = {}, urlObj = {}, snObj = {}, stockObj = {};

                                    for (var h = 0; h < res.data[i].goods_list[skus[index]].length; h++) {
                                        var goodsItem = res.data[i].goods_list[skus[index]][h];
                                        size += goodsItem.size + ','
                                        color += goodsItem.color + ','

                                        var colorName = goodsItem.color.split(' ').join('_');
                                        sizeName = goodsItem.size.split(' ').join('_'),
                                            goodsId = goodsItem.goods_id,
                                            goodsSn = [skus[index]];

                                        attributeObj[colorName + '/' + sizeName] = goodsId;
                                        goodsIdObj[goodsId] = obj[h].goods_img;
                                        priceObj[goodsId] = obj[h].shop_price;
                                        urlObj[goodsId] = obj[h].url_title;
                                        snObj[goodsId] = obj[h].goods_sn;
                                        stockObj[goodsId] = obj[h].active_num + '_' + obj[h].is_on_sale;
                                        if (!colorObj.hasOwnProperty(colorName)) {
                                            colorObj[colorName] = sizeName;
                                        } else if (colorObj.hasOwnProperty(colorName)) {
                                            colorObj[colorName] = colorObj[colorName] + ',' + sizeName;
                                        }
                                    }

                                    var item;
                                    var goodsList = res.data[i].goods_list[skus[index]];
                                    if (goodsList.length > 0) {
                                        for (var l = 0; l < goodsList.length; l++) {
                                            if (goodsList[l].goods_sn == goodsSn) {
                                                item = goodsList[l];
                                            }
                                        }
                                    }

                                    html += '<li data-stockObj =' + JSON.stringify(stockObj) + ' data-goodsId = "' + goodsId + '" data-isshow="' + item.is_show + '" data-activenum="' + item.active_num + '" data-onSale="' + item.is_on_sale + '" data-goods="' + goodsSn + '" data-attribute=' + JSON.stringify(attributeObj) + ' data-url=' + JSON.stringify(urlObj) + ' data-price=' + JSON.stringify(priceObj) + ' data-goodsImg=' + JSON.stringify(goodsIdObj) + ' data-sn=' + JSON.stringify(snObj) + '>';
                                    var soldoutText = $(component).find('input[name=lang-soldout]').val();
                                    html += '<div class="soldout"><span>' + soldoutText + '</span></div>';
                                    //if(item.active_num == 0 || item.is_on_sale == 0){

                                    //}
                                    html += '<a class="goods-img" href="' + item.url_title + '">';
                                    html += '<img src="https://geshopimg.logsss.com/uploads/FRcjv8nNOT0BbSfV9ht3ZLemuo1K2AqQ.png"  data-original="' + item.goods_img + '" class="js_loadingimg js-attribute-img" style="display: inline;">';
                                    html += '</a>';
                                    html += '	<p class="price-wrap">';
                                    html += '<span class="my_shop_price price js-shop-price" data-orgp="' + item.shop_price + '" data-bz="' + item.shop_price + '">' + item.shop_price + '</span>';
                                    html += '</p>';
                                    html += '<div class="info-wrap">';
                                    html += '<div class="attribute-wrap pr">';
                                    html += '<span class="attribute-text  js-color-value js-attribute-value">' + item.color + '</span>';
                                    html += '<span class="shad-wrap"></span>';

                                    html += '	<span class="arrows js-color-btn js-attribute-btn" title="' + item.color + '"></span>  ';
                                    html += '<div class="attribute-list js-attribute-list js-color-attribute" data-color="' + color + '">';

                                    html += '</div>';
                                    html += '</div>';
                                    html += '<div class="attribute-wrap pr">';
                                    html += '	<span class="attribute-text  js-size-value js-attribute-value">' + item.size + '</span>';
                                    html += '<span class="shad-wrap"></span>';
                                    html += '<span class="arrows js-size-btn js-attribute-btn"></span>';
                                    html += '<div class="attribute-list js-attribute-list js-size-attribute"  data-size="' + size + '">';

                                    html += '	</div>';
                                    html += '</div>';
                                    html += '</div>';
                                    html += '</li>';
                                }
                                index++;
                            }
                            html += '</ul>';
                            html += '<div class="cart-wrap">';
                            html += '<p><span class="my_shop_price group-price fb js-group-price" data-orgp="' + res.data[i].group_purchase_price + '" data-bz="' + res.data[i].group_purchase_price + '">' + res.data[i].group_purchase_price + '</span></p>';
                            html += '<p><del class="my_shop_price total-price fb js-total-price" data-orgp="' + res.data[i].total_shop_price + '" data-bz="' + res.data[i].total_shop_price + '">' + res.data[i].total_shop_price + '</del></p>';
                            if (item.active_num == 0 || item.is_on_sale == 0) {
                                html += '<div class="cart-btn-wrap js-add-cart disabled">';
                            } else {
                                html += '<div class="cart-btn-wrap js-add-cart">';
                            }
                            html += '<i class="ico-cart"></i>';
                            html += '<span class="cart-text fb"> GET THE SET</span> ';
                            html += '</div>';

                            $(component).find('.combo-wrap').html(html);
                            if (isEditenv == 0) {
                                GLOBAL.currency.change_html();
                            }
                            var btnText = $(component).find('input[name=btn-text]').val();

                            $(component).find('.cart-text').text(btnText);
                            var imgObj = JSON.parse($(component).find('input[name=sku-img]').val());
                            var lis = $(component).find('.goods-list li');
                            if (imgObj) {
                                for (var g = 0; g < lis.length; g++) {
                                    var liSku = $(lis[g]).attr('data-goods');
                                    for (var k = 0; k < imgObj.length; k++) {
                                        if (liSku === imgObj[k].sku) {
                                            $(lis[g]).find('.js-attribute-img').attr('data-original', imgObj[k].img);
                                        }
                                    }
                                }
                            }

                            if (isEditenv == 0) {
                                if ($.fn.lazyload) {
                                    $("img.js_loadingimg").lazyload();
                                } else {
                                    window.GS_GOODS_LAZY_FN('.js_loadingimg');
                                }
                            } else {
                                $("img.js_loadingimg").each(function () {
                                    $(this).attr('src', $(this).attr('data-original'));
                                });
                            }
                            renderAttr();  //处理属性

                        }
                    }

                }
            })
        }, 200);

    }

    function changSizeValue(obj) {
        var $this = obj.index,
            sizeArr = String($this.data("size")).split(","),
            htmlStr = '';
        if (sizeArr && sizeArr.length > 0) {
            for (var j = 0, len = sizeArr.length; j < len; j++) {
                htmlStr += '<p>' + sizeArr[j] + '</p>';
            }
        }
        $this.closest("li").find(".js-size-attribute").html(htmlStr);
        $this.closest("li").find(".js-size-value").text(sizeArr[0]);

    }

    function unique(array) {
        var res = [];
        for (var i = 0, len = array.length; i < len; i++) {
            var current = array[i];
            if (res.indexOf(current) === -1 && current) {
                res.push(current)
            }
        }
        return res;
    }


});
