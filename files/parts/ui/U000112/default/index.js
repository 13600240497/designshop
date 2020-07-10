
$(function () {
	var isEditEnv = $('.geshop-component-box-U000112-default [data-editenv]:eq(0)').data('editenv');
	if (isEditEnv == '1') {
		return false;
	}
	function initOffset () {
		var documentH = document.documentElement.clientHeight;
		var $wrapper = $('.geshop-component-box-U000112-default .geshop-page-navigator');
		$wrapper.each(function () {
			var $that = $(this);
			var $wrapperH = $that.height(),
				contentH = $('.geshop-page-content', $that).height();
			headerH = $('.geshop-page-header', $that).height(),
				mainH = $('.geshop-page-main', $that).height(),
				partH = $('.geshop-page-part', $that).height(),
				goTopH = $('.geshop-page-goTop', $that).height()
			if (contentH < documentH) {
				var offsetTop = (documentH - contentH) / 2;
				$('.geshop-page-content', $that).css("margin-top", offsetTop + 'px')
			} else {
				$('.geshop-page-content', $that).css("margin-top", '0')
			}

		})
	}

	initOffset();

	$('.geshop-component-box-U000112-default .side-btn').on('click', function () {
		var $this = $(this).parent('.geshop-navigator-wrapper');
		if ($this.hasClass('geshop-statu-fold')) {
			$this.addClass('geshop-statu-expand').removeClass('geshop-statu-fold');
			return false;
		}
		if ($this.hasClass('geshop-statu-expand')) {
			$this.addClass('geshop-statu-fold').removeClass('geshop-statu-expand');
		}
	})

	$('.geshop-component-box-U000112-default .geshop-page-goTop').click(function () {
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


});
