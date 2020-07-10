$(function () {
	(function(){
		var staticDomain = $('[data-static-domain]:eq(0)').attr('data-static-domain') ? $('[data-static-domain]:eq(0)').attr('data-static-domain') : "";
		function sendLoggssData($target){
			gbLogsss.getsku($target);
			gbLogsss.sendsku();
		}
		loadCss(staticDomain + '/resources/javascripts/library/swiper/swiper.min.css');
		
			//var length = $('[data-key="U000065"] .geshop-index-banner .swiper-slide').length;
			var config = {};
			$('[data-key="U000065"] .geshop-index-banner').each(function(i,v){
				
				if($(this).find('.swiper-slide').length > 1 && $(this).data("is-swiper")!=1){
					$(this).data("is-swiper",1);//已经new Swiper过的组件不用重复Swiper
					config = {
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
					var $that = $(this);
					$LAB.script(staticDomain + '/resources/javascripts/library/swiper/swiper.3.4.spec.min.js').wait(function() {
						new Swiper3($that, config);
					})
				}else{
					if(typeof gbLogsss != 'undefined'){
						sendLoggssData($(this).find('.swiper-slide').find('.js-swiper-img'))
					}
				}
			})
			
	})()

});
