function geshop_U000025_rule_init(o){var i=$('.geshop-component-box[data-id="'+o+'"]');function n(){var o=i.find(".gs-bg-img").width();document.body.scrollHeight>(window.innerHeight||document.documentElement.clientHeight)&&(1900<=o&&o<=1920&&1920<=window.innerWidth?i.find(".geshop-U000025-rule").addClass("overflow"):i.find(".geshop-U000025-rule").removeClass("overflow"))}i.on("click",".dialog-btn",function(){i.find(".geshop-U000025-dialog-rule").show()}),i.on("click",".js-close-dialog",function(){i.find(".geshop-U000025-dialog-rule").hide()}),n(),$(window).on("resize",n)}