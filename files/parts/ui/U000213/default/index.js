"use strict";$(function(){$(".geshop-U000213-default").each(function(){var a,n,o=$(this);1==$(this).find("input[name=isEditEnv]").val()&&$(this).find(".layer-share").show(),"wap"==GESHOP_PLATFORM&&(a=function(){var a=document.createElement("script");"rg-wap"===GESHOP_SITECODE?a.src="//s7.addthis.com/js/300/addthis_widget.js#pubid=ra-54c2151b31fb2710":"zf-wap"===GESHOP_SITECODE&&(a.src="//s7.addthis.com/js/300/addthis_widget.js#pubid=ra-5a38671bb83b79fe"),document.getElementsByTagName("head")[0].appendChild(a)},n=window.onload,"function"!=typeof window.onload?window.onload=a:window.onload=function(){n(),a()}),$("body").on("touchstart",".share-btn",function(){if("wap"==GESHOP_PLATFORM){var a={content:$(".layer-share"),modalWidth:"9rem",className:"dialog-share"};"undefined"!=typeof GEShopSiteCommon&&GEShopSiteCommon.dialog.custom(a)}else if("app"==GESHOP_PLATFORM){var n=o.find("input[name=share_data_title]").val(),t=o.find("input[name=share_data_desc]").val(),e=o.find("input[name=share_data_link]").val(),i=o.find("input[name=share_data_img]").val();window.location.href="webAction://sharing?shareUrl="+e+"&shareTitle="+n+"&shareContent="+t+"&imageUrl="+i}}),$("body").on("touchstart",".cancel-btn",function(){"undefined"!=typeof GEShopSiteCommon&&GEShopSiteCommon.dialog.unblock()})})});