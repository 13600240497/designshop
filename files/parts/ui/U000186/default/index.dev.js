
$(function () {
	if (!$.fn.swiper3 && $LAB) {
		var staticDomain = $('[data-static-domain]:eq(0)').attr('data-static-domain') ? $('[data-static-domain]:eq(0)').attr('data-static-domain') : "";
		loadCss(staticDomain + '/resources/javascripts/library/swiper/swiper.min.css')
		$LAB.script(staticDomain + '/resources/javascripts/library/swiper/swiper.3.4.spec.min.js').wait(function () {
			startNavCheck()
		})
	} else {
		startNavCheck()
  }

  $('.geshop-U000186-default').each(function(){
    var totalWidth = 0;
    $(this).find('.component-m-nav .component-m-nav-ul li').each(function(){
      var liWidth =  $(this).outerWidth();
      totalWidth += liWidth;
    });
    if(totalWidth < document.body.clientWidth){
      $(this).find('.component-m-nav-ul').addClass('center');
    }else{
      $(this).find('.component-m-nav-ul').removeClass('center');
    }
  });

	function startNavigation () {
		var mySwiper = new Swiper3('#js_topNav', {
			freeMode: false,
			slidesPerView: 'auto'
			//slideToClickedSlide:true
		});

		mySwiper.setWrapperTransition(100);

		var navigateTarget = $('#nav-m-ul');
		var closestTarget = navigateTarget.closest('.component-m-nav');
		var navigateOffsetTop = navigateTarget.offset().top;
		var timer = null;
		var totalWidth = 0;
		var isNeedTranslate = false;

		var swiperWidth = mySwiper.container[0].clientWidth;
		var maxTranslate = mySwiper.maxTranslate();
		var maxWidth = -maxTranslate + swiperWidth / 2
	

		navigateTarget.find('li').each(function () {
			totalWidth += $(this).outerWidth();
		});

		isNeedTranslate = totalWidth - $(window).width();

		$(window).scroll(function () {
			if ($(window).scrollTop() > navigateOffsetTop) {
				closestTarget.addClass('component-m-nav-fixed');
                // 站点导航栏处理
                if ($('.js_header').length) {
                    $('.js_header').hide();
                }
			} else {
				closestTarget.removeClass('component-m-nav-fixed');

                // 站点导航栏处理
                if ($('.js_header').length) {
                    $('.js_header').show();
                }
			}
			var scrollTopBefore = $(window).scrollTop();
			clearTimeout(timer);
			timer = setTimeout(function () {
				var scrollTopAfter = $(window).scrollTop();
				if (scrollTopBefore == scrollTopAfter) {
					
					var navigations = navigateTarget.find('li');
					var length = navigations.length;

					for (var index = 0; index < length; index = index + 1) {
						var target = $(navigations.get(index));
						var navigatedId = target.data('id');
						var titleTarget = $('.geshop-component-box[data-id=' + navigatedId + ']');
			
						if (scrollTopAfter > titleTarget.offset().top - titleTarget.height() - navigateTarget.height()) {
							target.addClass('m-current');
							target.siblings().removeClass('m-current');
							var slide = target;
							var slideLeft = slide.position().left;
							var slideWidth = slide.outerWidth(true);
							var slideCenter = slideLeft + slideWidth / 2;
							// 被点击slide的中心点
							mySwiper.setWrapperTransition(300);
							if (slideCenter < swiperWidth / 2) {
								mySwiper.setWrapperTranslate(0)
							} else if (slideCenter > maxWidth) {
								mySwiper.setWrapperTranslate(maxTranslate);
							} else {
								var nowTlanslate = slideCenter - swiperWidth / 2;
								mySwiper.setWrapperTranslate(-nowTlanslate);
							}
						}
					}
				}
			}, 50);
		

		});

		navigateTarget.on('click', 'li', function () {
			var target = $(this);
			var navigatedId = target.data('id');

			//target.addClass('m-current').siblings().removeClass('m-current');
			$('html, body').animate({
				scrollTop: $('.geshop-component-box[data-id=' + navigatedId + ']').offset().top - navigateTarget.height()
			}, 80);
			
			
    });
    
    
	}

	function startNavCheck () {
		var is_edit = $('#nav-m-ul').data('isedit');
		if (!1 == is_edit) {
            startNavigation();
			// var imgdefereds = [];
			// $('img').each(function () {
			// 	var dfd = $.Deferred();
			// 	$(this).bind('load', function () {
			// 		dfd.resolve();
			// 	}).bind('error', function () {
			// 	});
			// 	if (this.complete) setTimeout(function () {
			// 		dfd.resolve();
			// 	}, 1000);
			// 	imgdefereds.push(dfd);
			// });
			// $.when.apply(null, imgdefereds).done(function () {
			// 	startNavigation();
			// });
		}
	}

	function loadCss (href) {
		var link = document.createElement("link");
		link.setAttribute("rel", "stylesheet");
		link.setAttribute("type", "text/css");
		link.setAttribute("href", href);
		document.getElementsByTagName("head")[0].appendChild(link);
	}
});
