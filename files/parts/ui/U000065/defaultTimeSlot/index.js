$(function () {
	(function(){
		var staticDomain = $('[data-static-domain]:eq(0)').attr('data-static-domain') ? $('[data-static-domain]:eq(0)').attr('data-static-domain') : "";
		function sendLoggssData($target){
			gbLogsss.getsku($target);
			gbLogsss.sendsku();
		}
		loadCss(staticDomain + '/resources/javascripts/library/swiper/swiper.min.css');

        if(!GS_Time_banner){
            var GS_Time_banner = (function(my){
                /* 初始化单个模板 */
                my.initSwiper = function(target,config){
                    if($(target).data("is-swiper") != 1){
                        new Swiper3(target, config);
                        $(target).data("is-swiper",1);
                    }else{
                        if(typeof gbLogsss != 'undefined'){
                            sendLoggssData($(target).find('.swiper-slide').find('.js-swiper-img'))
                        }
                    }
                }
                /* 获取当前时间段列表 */
                my.initCurrentBanner = function() {
                    var currentTime = new Date().getTime();
                    var config = {
                        autoplay: 5000,
                        pagination: '.swiper-pagination',
                        lazyLoading : true,
                        onLazyImageLoad:function(swiper, slide, image) {
                            if (typeof gbLogsss != 'undefined'&&swiper.realIndex>0) {
                                sendLoggssData($(image))
                            }
                        },
                        onInit:function(swiper){//第一张图片发送大数据统计数据
                            if(typeof gbLogsss != 'undefined'){
                                sendLoggssData($(swiper.slides[0]).find('.js-swiper-img'))
                            }
                        }
                    }
                    $('[data-key="U000065"] .geshop-index-banner').each(function(i,element){
                        var dataGroup = JSON.parse($(element).attr('data-group'));
                        var result = '';
                        dataGroup.forEach(function(aData){
                            if(currentTime > aData.startTimeStamp * 1000 && currentTime < aData.endTimeStamp * 1000){
                                result = aData;
                            }
                        })

                        var $that = $(this);
                        if(result){
                            my.render(element,result,config);
                        }else{
                            my.initSwiper(element,config)
                        }
                        $(element).removeClass('init-not');

                    })

                }
                /* 渲染TPL */
                my.render = function(element,result,config){
                    var $tpl = $(element).find('.gs_time_banner');
                    var tplHtml = $tpl.html();
                    gs_laytpl.config({ open: "<%", close: "%>" });
                    gs_laytpl(tplHtml).render(result, function (html) {
                        if (html) {
                            $(element).find('.swiper-wrapper').html(html);
                        }
                        my.initSwiper(element,config);
                    })
                }
                return my;
            }({}))
        }

			//var length = $('[data-key="U000065"] .geshop-index-banner .swiper-slide').length;

        $LAB.script(staticDomain + '/resources/javascripts/library/swiper/swiper.3.4.spec.min.js')
            .script(staticDomain + "/resources/javascripts/library/gs_laytpl.js?2018101101").wait(function() {
            GS_Time_banner.initCurrentBanner();
        })
			
	})()

});
