;$(function () {
	var isEditEnv = $('[data-editenv]').eq(0).data('editenv');

	var isHide = $("[data-hide-btn]").eq(0).data("hide-btn");

	if (!isHide) {
		if (isEditEnv) {
			var contentH = $('.geshop-page-content').height();
			$(".side-btn").css({
				top: (contentH / 2) + "px",
				bottom: "auto",
				"border-radius": "0px 8px 8px 0px"
			});
		} else {
			initSIdeShow();
		}
	} else {
		initSIdeHide();
	}

	function initSIdeHide () {
		var $wrapper = $('.geshop-page-navigator').height();
		var contentH = $('.geshop-page-content').height();
		// var top = ($wrapper - contentH) / 2 + contentH + 15;
		$(".side-btn").css({
			// top:top + "px",
			"position": "relative",
			"top": "initial",
			"border-radius": "50%"
		});
	}

	function initSIdeShow () {
		var $wrapper = $('.geshop-page-navigator').height();
		var top = $wrapper / 2;
		$(".side-btn").css({
			"position": "fixed",
			top: top + "px",
			"border-radius": "0px 8px 8px 0px"
		});
	}

	if (isEditEnv == '1') {
		return false;
	}

	function initOffset () {
		var documentH = document.documentElement.clientHeight || document.body.clientHeight || window.innerHeight;
		var $wrapper = $('.geshop-page-navigator');
		$wrapper.each(function () {
			var $that = $(this); 
			var contentH = $('.geshop-page-content', $that).height();
			var marginT = $('.geshop-page-content',$that).data('margintop');
			if (contentH < documentH) {
				var offsetTop = marginT == '0' || marginT ? marginT : (documentH - contentH) / 2;
				$('.geshop-page-content', $that).css("margin-top", offsetTop + 'px');
			} else {
				$('.geshop-page-content', $that).css("margin-top", '0');
			}

		});


	}

	initOffset();

	$('.side-btn').on('click', function () {
		var $this = $(this).parents('.geshop-navigator-wrapper');
		if ($this.hasClass('geshop-statu-fold')) {
			initSIdeHide();
			$this.addClass('geshop-statu-expand').removeClass('geshop-statu-fold');
			return false;
		}
		if ($this.hasClass('geshop-statu-expand')) {
			initSIdeShow();
			$this.addClass('geshop-statu-fold').removeClass('geshop-statu-expand');
		}
	});

	$('.geshop-page-goTop').click(function () {
		$('body,html').animate({
			scrollTop: '0px'
		}, 500);

	});

	$(window).on('resize', function () {
		if (resizeTimeout) clearTimeout(resizeTimeout);
		var resizeTimeout = setTimeout(function () {
			initOffset();
		}, 100);
	});

	// //最上面一屏幕和最下面半屏幕不显示导航  和竖直导航冲突,写在公共js


});
