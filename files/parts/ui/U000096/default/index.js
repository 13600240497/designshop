; $( function() {

	var staticDomain = typeof GESHOP_STATIC == "undefined" ? "" : GESHOP_STATIC;

	if ($('.goods-category').eq(0).length == 0) {
		$('.goods-category-empty').addClass('category-active').siblings().removeClass('category-active');
	}

	$LAB.script(staticDomain + '/resources/javascripts/library/swiper/swiper.3.4.spec.min.js').wait(function () {
		$('[data-gid=U000096_default] .swiper-container').each( function() {

			var swiper = new Swiper3(this, {
			    freeMode: true,
			    freeModeMomentumRatio: 0.5,
			    slidesPerView: 'auto',
			});

			$(".swiper-container").on('touchstart', function(e) {//去掉按压阴影
				e.preventDefault()
			})

			swiper.on('tap', function(swiper, e) {
				var $this = $(e.target).parents('.tab-item');
				var $gb_goodsWrap = $(e.target).parents('.gb_goodsWrap');
				var $index = $this.index();
				$this.parent('.swiper-wrapper').css({
				})

				$this.addClass('tab-active').siblings().removeClass('tab-active');
				if ($('.goods-category').eq($index).length == 0) {
					$gb_goodsWrap.find('.goods-category-empty').addClass('category-active').siblings().removeClass('category-active');
				}else {
					$gb_goodsWrap.find('.goods-category').eq($index).find('img').each( function() {
						$(this).attr('src', $(this).attr('data-lazy'));

					})
					$gb_goodsWrap.find('.goods-category').eq($index).addClass('category-active').siblings().removeClass('category-active');
				}

				swiperWidth = swiper.container[0].clientWidth;
				maxTranslate = swiper.maxTranslate();
				maxWidth = -maxTranslate + swiperWidth / 2

				slide = swiper.slides[swiper.clickedIndex]
				slideLeft = slide.offsetLeft
				slideWidth = slide.clientWidth
				slideCenter = slideLeft + slideWidth / 2

				swiper.setWrapperTransition(300)
				if (slideCenter < swiperWidth / 2) {
					swiper.setWrapperTranslate(0)
				} else if (slideCenter > maxWidth) {
					swiper.setWrapperTranslate(maxTranslate)
				} else {
					nowTlanslate = slideCenter - swiperWidth / 2
					swiper.setWrapperTranslate(-nowTlanslate)
				}


			})

		})

	})

	if (GBViewMore) {
		$('[data-gid=U000096_default] .view-more-btn').on('click',function(){
			var $this = $(this);
			var $ul = $(this).parent().siblings('.gb-list-default').eq(0);
			var shownum = $ul.data('shownum');
			var Lis = $ul.children().each(function(index,item){
				if(index>shownum-1){
					if( index ==$ul.children().length-1){
						if(!$(item).hasClass('isHide')){ // 收缩 
							$('html').scrollTop($ul.data('scrolltop'))
							$this.children('span').text($this.data('moretext'))
							$this.children('i').removeClass('gs-iconfont gs-icon-up icon-arrow_up').addClass('gs-iconfont gs-icon-down1 icon-arrow_down')
						}else{ // 展开
							$ul.data('scrolltop',$('html').scrollTop())
							$this.children('span').text($this.data('lesstext'))
							$this.children('i').removeClass('gs-iconfont gs-icon-down1 icon-arrow_down').addClass('gs-iconfont gs-icon-up icon-arrow_up')
						} 
					}
					$(item).toggleClass('isHide')
				}
			})
		})
	}



})
