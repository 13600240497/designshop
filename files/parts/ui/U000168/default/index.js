;$(function () {


	var isEditEnv = $('[data-editenv]').eq(0).data('editenv');

	var isHide = $("[data-hide-btn]").eq(0).data("hide-btn");

	if (!isHide) {
		if (isEditEnv) {
			var contentH = $('.geshop-page-content').height();
			$(".side-btn").css({
				top: (contentH / 2) + "px",
				bottom: "auto",
				"border-radius": "0 0.106rem 0.106rem 0"
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
		var top = ($wrapper - contentH) / 2 + contentH + 30;
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
		var H = $(".side-btn").height() / 2;
		$(".side-btn").css({
			"position": "fixed",
			top: (top - H) + "px",
			"border-radius": "0 0.106rem 0.106rem 0"
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
			var $wrapperH = $that.height(),
				contentH = $('.geshop-page-content', $that).height(),
				headerH = $('.geshop-page-header', $that).height(),
				mainH = $('.geshop-page-main', $that).height(),
				partH = $('.geshop-page-part', $that).height(),
				goTopH = $('.geshop-page-goTop', $that).height(),
				marginT = $('.geshop-page-content',$that).data('margintop');
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
	})

	;(function () {
		var obtn = document.getElementsByClassName('geshop-page-goTop')[0];  //获取回到顶部按钮
		var timer = null; //定义一个定时器
		var isTop = true; //定义一个布尔值，用于判断是否到达顶部
		obtn.onclick = function () {    //回到顶部按钮点击事件
			//设置一个定时器
			timer = setInterval(function () {
				//获取滚动条的滚动高度
				var osTop = document.documentElement.scrollTop || document.body.scrollTop;
				//用于设置速度差，产生缓动的效果
				var speed = Math.floor(-osTop / 6);
				document.documentElement.scrollTop = document.body.scrollTop = osTop + speed;
				isTop = true;  //用于阻止滚动事件清除定时器
				if (osTop == 0) {
					clearInterval(timer);
				}
			}, 30);
		};
	})();

	$(window).on('resize', function () {
		if (resizeTimeout) clearTimeout(resizeTimeout);
		var resizeTimeout = setTimeout(function () {
			initOffset();
		}, 100);
	});


	//最上面一屏幕和最下面半屏幕不显示导航 已写到公共的js里面

});
