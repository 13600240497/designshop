;$(function () {
	var staticDomain = $('[data-static-domain]:eq(0)').attr('data-static-domain') ? $('[data-static-domain]:eq(0)').attr('data-static-domain') : "";

	loadCss(staticDomain + '/resources/javascripts/library/swiper/swiper.min.css');

	if(!GS_Time_banner){
        var GS_Time_banner = (function(my){
            /* 初始化单个模板 */
            my.initSwiper = function(element){
                var id = $(element).data('id');
                var length = $(element).find('.swiper-slide').length;
                var swiperContainer = $('.geshop-swiper-container-' + id);
                var warpComponentBox = $('.wrap-default-timesolt_fullscreen-' + id);
                var prevButtonElement = $(element).find('.swiper-button-prev');
                var nextButtonElement = $(element).find('.swiper-button-next');
                var paginationElement = $(element).find('.swiper-pagination');
                if (length == 0 && warpComponentBox.data('isedit')!=1){
                    warpComponentBox.hide();
                }
                if (length == 1) {
                    new Swiper3(swiperContainer, {
                        autoplayDisableOnInteraction: true,
                        loop: false,
                        lazyLoading: true,
                        onLazyImageLoad: function (swiper, slide, image) {
                            if (typeof gbLogsss !== 'undefined') {
                                gbLogsss.getsku($(image));
                                gbLogsss.sendsku();
                            }

                        }
                    });
                    
                }else {
                    new Swiper3(swiperContainer, {
                        autoplay: 5000,
                        autoplayDisableOnInteraction: false,
                        prevButton: prevButtonElement,
                        nextButton: nextButtonElement,
                        pagination: paginationElement,
                        paginationClickable: true,
                        lazyLoading: true,
                        loop: true,
                        onLazyImageLoad: function(swiper, slide, image) {
                            if (swiper.realIndex != 0 && !$(slide).hasClass('swiper-slide-duplicate') && typeof gbLogsss != 'undefined') {
                                gbLogsss.getsku($(image));
                                gbLogsss.sendsku();
                            }
                        },
                        onInit: function (swiper) {
                            if (swiper.realIndex == 0 && typeof gbLogsss != 'undefined') {
                                gbLogsss.getsku($(swiper.slides[1]).find('.swiper-lazy'));
                                gbLogsss.sendsku();
                            }
                        }
                    });

                    $(element).hover(function() {
                        prevButtonElement.css('display', 'block');
                        nextButtonElement.css('display', 'block');
                    }, function() {
                        prevButtonElement.css('display', 'none');
                        nextButtonElement.css('display', 'none');
                    });
                }
            }
            /* 获取当前时间段列表 */
            my.initCurrentBanner = function() {
                var currentTime = new Date().getTime();
                $('[data-gid="U000058_default_timeslot_fullscreen"] .geshop-index-banner').each(function (i, element) {
                    
                    var dataGroup = JSON.parse($(element).attr('data-group'));
                    var result = '';
                    dataGroup.forEach(function(aData){
                        if(currentTime > aData.startTimeStamp * 1000 && currentTime < aData.endTimeStamp * 1000){
                            result = aData
                        }
                    });
                  
                    if(!!result){
                        my.render(element,result);
                    } else {
                       
                        my.initSwiper(element);
                    }

                })
            }
            /* 渲染TPL */
            my.render = function(element,result){
                var $tpl = $(element).parents('.component-drop:eq(0)').find('.gs_time_banner');
                var tplHtml = $tpl.html();
                gs_laytpl.config({ open: "<%", close: "%>" });
                gs_laytpl(tplHtml).render(result, function (html) {
                    if (html) {
                        $(element).find('.swiper-wrapper').html(html);
                    }
                    my.initSwiper(element);
                })
            }
            return my;
        }({}))
    }


	$LAB.script(staticDomain + '/resources/javascripts/library/swiper/swiper.3.4.spec.min.js')
        .script(staticDomain + "/resources/javascripts/library/gs_laytpl.js?2018101101").wait(function () {

        GS_Time_banner.initCurrentBanner();
	});

	});
