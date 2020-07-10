
$(function () {
	var isEditEnv = $('[data-editenv]:eq(0)').data('editenv');
	if (isEditEnv == '1') {
		return false;
	}
	function initOffset () {
		var documentH = document.documentElement.clientHeight;
		var $wrapper = $('.geshop-page-navigator');
		$wrapper.each(function () {
			var $that = $(this);
			var $wrapperH = $that.height(),
				contentH = $('.geshop-page-content', $that).height() || 0,
			headerH = $('.geshop-page-header', $that).height() || 0,
				mainH = $('.geshop-page-main', $that).height() || 0,
				partH = $('.geshop-page-part', $that).height() || 0,
				goTopH = $('.geshop-page-goTop', $that).height() || 0;
			if (contentH < documentH) {
				var offsetTop = (documentH - contentH) / 2;
				$('.geshop-page-content', $that).css("margin-top", offsetTop + 'px')
			} else {
				$('.geshop-page-content', $that).css("margin-top", '0')
			}

		})
	}

	initOffset();

	$('.side-btn').on('click', function () {
		var $this = $(this).parent('.geshop-navigator-wrapper');
		if ($this.hasClass('geshop-statu-fold')) {
			$this.addClass('geshop-statu-expand').removeClass('geshop-statu-fold');
			return false;
		}
		if ($this.hasClass('geshop-statu-expand')) {
			$this.addClass('geshop-statu-fold').removeClass('geshop-statu-expand');
		}
	})

	$('.geshop-page-goTop').click(function () {
		$('body,html').animate({
			scrollTop: '0px'
		}, 500)
	})

	$(window).on('resize', function () {
		if (resizeTimeout) clearTimeout(resizeTimeout)
		var resizeTimeout = setTimeout(function () {
			initOffset();
		}, 100)
	})

	// // 竖直导航和页面间导航顶部底部隐藏冲突,写到公共js
	;(function(){
		setTimeout(function(){
			window.onscroll = function(){
				var documentH = document.documentElement.clientHeight||document.body.clientHeight||window.innerHeight;
				var bodyH = document.getElementsByTagName("body")[0].offsetHeight;

				var osTop = document.documentElement.scrollTop || document.body.scrollTop;
				if(osTop > documentH && osTop < (bodyH - 1.5*documentH) ){
					$(".geshop-page-navigator,.geshop-navigator-wrapper .side-btn").css({"z-index":"1001","visibility":"visible"})
				}else{
					$(".geshop-page-navigator,.geshop-navigator-wrapper .side-btn").css({"z-index":"-1001","visibility":"hidden"})
				}
			}
		},200)
	})();

});
