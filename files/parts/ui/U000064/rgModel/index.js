
; $(function () {
	var staticDomain = $('[data-static-domain]:eq(0)').attr('data-static-domain') ? $('[data-static-domain]:eq(0)').attr('data-static-domain') : "";
	loadCss(staticDomain + '/resources/javascripts/library/swiper/swiper.min.css');
	$LAB.script(staticDomain + '/resources/javascripts/library/swiper/swiper.3.4.spec.min.js').wait(function () {

		var mSwiper = $('[data-gid="U000064"].m-swiper-goods');
		mSwiper.each(function (i, element) {
			$(element).find('.swiper-container:first').show();
			$(element).find('.nav-container ul li:first').addClass('current');
			swiperFn(element);
			var navLi = $(element).find('.nav-container ul li');
			var navLiLength = $(element).find('.nav-container ul li').length;

			if(navLiLength < 3){
				$(element).find('.nav-container ul').addClass('center');
			}
			
			navLi.on('click', function (e) {
				var $tab_index = $(this).index();
				$(element).find('.swiper-container:eq(' + $tab_index + ')').show().siblings('.swiper-container').hide();
				$(this).addClass('current').siblings('').removeClass('current');
				swiperFn(element);
				
				/*点击滚动到中间*/
				var windowWidth = $(window).width();
				var divWidth = 0;
				var resPlaceX = 0;//最终的位置X
				var moveDistance = 0;//移动的距离
				var ev = e || event;
				var disX = ev.clientX - ev.offsetX;//当前div距离屏幕左边距离
				divWidth = $(this).width();
				resPlaceX = (windowWidth - divWidth) / 2;
				moveDistance = disX - resPlaceX;
				var startTranform = 0;//当前的transform值
				startTranform = $(this).parent().scrollLeft();
				if (startTranform == '') { startTranform = 0 };
				$(this).parent().animate({scrollLeft: (parseInt(startTranform) + moveDistance)},500);
			});
		});
	});
	function swiperFn(element) {
		var swiperTarget = $(element).find('.swiper-container:visible');
		new Swiper3(swiperTarget, {
			slidesPerView: "auto",
			centeredSlides: !0,
			slidesPerGroup: 1,
			paginationClickable: !0,
			loop: true,
			lazyLoading: true,
			slidesOffsetBefore: 10,
			slidesOffsetAfter: 10,
			lazyLoadingInPrevNext: true,
			onLazyImageReady: function (swiper, slide, image) {
				gbLogsss.getsku($(image));
				gbLogsss.sendsku();
			}
		});
	}
});

