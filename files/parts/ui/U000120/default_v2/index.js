"use strict";$(function(){var r="undefined"!=typeof GESHOP_LANG?GESHOP_LANG:"en",l={},e=$("[data-static-domain]:eq(0)").attr("data-static-domain")?$("[data-static-domain]:eq(0)").attr("data-static-domain"):"";function g(e,i,t){gs_laytpl(e).render(t,function(e){i.html(e),window.GLOBAL&&window.GLOBAL.currency&&window.GLOBAL.currency.change_html(),window.FUN&&window.FUN.currency&&window.FUN.currency.change_html(),window.GS_GOODS_LAZY_FN&&window.GS_GOODS_LAZY_FN($("img.js_gdexp_lazy")),$('[data-gid="U000120_default"]').each(function(e,i){$(i).find(".goods-item").each(function(e,i){!function(e){p=$(e).attr("data-activityid");var i=$(e).attr("data-goodsSn");$(e).on("click",".gs-add-bag",function(e){(e.stopPropagation(),p)?(f({lang:"undefined"!=typeof GESHOP_LANG?GESHOP_LANG:"en",goodsSn:i,manzeng_id:p,pipeline:"undefined"!=typeof GESHOP_PIPELINE?GESHOP_PIPELINE:""},GESHOP_INTERFACE.getlistinspu.url,a,m),u.rank=$(this).parents(".goods-item").index()||0,u.p||(u.p=$(this).parents(".geshop-component-box").eq(0).attr("data-p"))):a(!1);return!1})}(i)})})})}loadCss(e+"/resources/javascripts/library/swiper/swiper.min.css"),$LAB.script(e+"/resources/javascripts/library/swiper/swiper.3.4.spec.min.js").wait(function(){setTimeout(function(){$(".geshop-gift-async").each(function(e,i){var t=$(i),a=t.attr("data-id");2<t.find(".data-goodsIDnum-domain").attr("data-goodsIDnum-domain")&&($(".tab"+a+".swiper-slide").show(),$(".tab"+a).find(".swiper-slide").show(),function(e){var i=$(".tab"+e).find(".swiper-container");new Swiper3(i,{slidesPerView:2,centeredSlides:!1,spaceBetween:0,pagination:{el:".swiper-pagination",clickable:!0},prevButton:".swiper-button-prev",nextButton:".swiper-button-next"})}(a))})},20)}),$LAB.script(e+"/resources/javascripts/library/gs_laytpl.js?2018100101").wait(function(){gs_laytpl.config({open:"<%",close:"%>"}),$(".geshop-gift-async").each(function(e,a){var i=$(a),c=i.find(".pc-gift-template").html(),r=i.find(".giftviewBox"),s=parseInt(i.attr("data-max"));i.find(".swiper-slide-item").each(function(o,e){var n=$(e),d=n.attr("data-activityid-id"),i={lang:GESHOP_LANG||"en",activityid:d,each_activity_count:s,pipeline:"undefined"!=typeof GESHOP_PIPELINE?GESHOP_PIPELINE:""},t=GESHOP_INTERFACE.fullgiftlist.url;window.GEShopCommonFn_Vue.$jsonp(t,i,{target:a}).done(function(e){if(0!=e.code)return!1;var t=[],a=e.data.goodsInfo,s=(new Date).getTime();a.map(function(e,i){e.is_ready=s<1e3*e.activityInfo_starttime,e.is_ended=s>1e3*e.activityInfo_endtime,n.find(".minPrice").eq(0).html("$"+e.activityInfo_thresholdamount),n.find(".minPrice").eq(0).attr({"data-orgp":(1*e.activityInfo_thresholdamount).toFixed(2),"data-currency":e.currency,"data-original_amount":e.currency_original_amount}),e.goods_number&&0==e.goods_number&&(t.push(e),a.splice(i,1))});var i={goodsInfo:a=a.concat(t),lang:e.data.lang};if(l[d]=i,0<o)return!1;g(c,r,l[d])})})})}),$(".geshop-tabs").click(function(e){if("swiper-slide-item"==e.target.className){$(e.target).parents(".geshop-gift-async").find(".swiper-slide-item").removeClass("active"),$(e.target).addClass("active");var i=$(e.target).attr("data-activityid-id");g($(e.target).parents(".geshop-gift-async").find(".pc-gift-template").html(),$(e.target).parents(".geshop-gift-async").find(".giftviewBox"),l[i])}});var i='<div class="gs-model-120" style="text-align:left;"><a href="javascript:;" class="geshop-dialog-close"></a><div class="model-main"><div class="model-left"><img class="js_gdexp_lazy js-geshopImg-lazyload js_gbexp_lazy" src="https://geshopcss.logsss.com/imagecache/geshop-test/resources/images/default/good.png" alt=""></div><div class="model-right"><div class="goods-title">'+GESHOP_LANGUAGES.gift_tips+'</div><div class="item-price"><span class="shop-price"><strong class="my_shop_price" data-orgp="0.00" >$0.00</strong></span><span class="market-price"><del class="my_shop_price" data-orgp="0.00" >$0.00</del></span></div><div class="select-content select-size"><div class="size-title">'+GESHOP_LANGUAGES.size+'</div><div class="size-box clearfix"><div class="size-item">1X</div><div class="size-item">2X</div></div></div><div class="select-content select-color"><div class="color-title" style="width: auto;">'+GESHOP_LANGUAGES.color+'</div><div class="color-box clearfix"><div class="color-item"><span></span></div><div class="color-item"><span></span></div></div></div><div class="add-bag logsss_event js_logsss_click_delegate">'+GESHOP_LANGUAGES.add_to_bag+"</div></div></div></div>",t='<div class="add-fail">'+GESHOP_LANGUAGES.gift_fail_add+"</div>",p="";function a(e){return GEShopSiteCommon.dialog.content(i,752,434,function(){_(e),GEShopSiteCommon.dialog.createsClosingStyle(),$("body").on("click",".geshop-dialog-close",function(){GEShopSiteCommon.dialog.unblock()})},function(){}),!1}function m(e){GEShopSiteCommon.dialog.message(t),e&&$(".add-fail").text(e.message)}function f(e,i,t,a){var s={content:JSON.stringify(e)};$.ajax({url:i,type:"GET",dataType:"jsonp",data:s,success:function(e){if(0==e.code){var i=e.data.goodsInfo;t(i,e)}else a(e)}})}var u={rank:0};function _(e){if(!e)return $(".size-item").eq(0).addClass("selected-color"),$(".color-item").eq(0).addClass("selected-size"),$(".gs-model-120 .shop-price strong").text("$0.00"),$(".gs-model-120 .market-price del").text("$0.00"),void $(".gs-model-120 .add-bag").on("click",function(){m()});$(".gs-model-120  .goods-title").text(e.goods_title),$(".gs-model-120 img").attr("src",e.goods_img),$(".gs-model-120 .shop-price strong").attr({"data-orgp":e.shop_price}).text("$"+e.shop_price),$(".gs-model-120 .market-price del").attr({"data-orgp":e.market_price}).text("$"+e.market_price);var t="",a=[];e.size_list.forEach(function(e,i){a.push(e.title),t+='<div class="size-item" data-goodsku="'+e.goods_sn+'">'+e.title+"</div>"}),$(".gs-model-120 .size-box").html(t);var i=a.indexOf(e.size);0<=i&&$(".size-item").eq(i).addClass("selected-size"),a.length<=0&&$(".gs-model-120 .select-size").hide();var s="",o=[];e.color_list.forEach(function(e,i){o.push(e.color_value),0==$.trim(e.color_img).length?s+='<div class="color-item" data-goodsku="'+e.goods_sn+'"><span style="background-color:'+e.color_value+'"></span></div>':s+='<div class="color-item" data-goodsku="'+e.goods_sn+'"><img src="'+e.color_img+'" width="100%" height="100%"></div>'}),$(".gs-model-120 .color-box").html(s);var n=o.indexOf(e.color);0<=n&&$(".color-item").eq(n).addClass("selected-color");var d={pm:"mp",p:u.p,x:"ADT",ubcta:{rank:u.rank,sku:e.goods_sn,price:e.shop_price,p:u.p,fmp:"mp"},skuinfo:{sku:e.goods_sn,pam:1,pc:e.cateid,k:e.warehouse,zt:0}},c=JSON.stringify(d).replace(/"/g,"'");$(".blockUI .gs-model-120 .add-bag").attr("data-logsss-event-value",c),$(".color-item,.size-item").off("click"),$(".color-item,.size-item").on("click",function(){var e=$(this).attr("data-goodsku");f({lang:r,goodsSn:e,manzeng_id:p,pipeline:"undefined"!=typeof GESHOP_PIPELINE?GESHOP_PIPELINE:""},GESHOP_INTERFACE.getlistinspu.url,_,m)}),$(".gs-model-120 .add-bag").off("click"),$(".gs-model-120 .add-bag").on("click",function(){f({lang:r,goodsSn:e.goods_sn,manzeng_id:p,pipeline:"undefined"!=typeof GESHOP_PIPELINE?GESHOP_PIPELINE:""},GESHOP_INTERFACE.addgifttocart.url,function(e,i){GEShopSiteCommon.dialog.unblock(),GEShopSiteCommon.dialog.message(GESHOP_LANGUAGES.gift_add_cart)},m)}),window.GLOBAL&&window.GLOBAL.currency&&window.GLOBAL.currency.change_html()}});