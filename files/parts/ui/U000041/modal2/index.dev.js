$(function () { 
	
	var tab_item = $('.wrap-U000041_zaful').find('.gs-tab-box li');

	$('.gs-tab-box').each(function () {
		var $box = $(this),
			boxW = $box.width(),
			ulW = $box.find('.goods-nav-name').width();
		if (ulW > boxW) {
			$('.geshop-tab-btn', $box).addClass('gs-btn-show');
			initSlideTab($box);
		}
	})

	tab_item.on('click', function () {
		var $labelParent = $(this).parents('.gs-tab-wrap:eq(0)'),
			$tab_content = $labelParent.next('.gs-tab-content'),
			$tab_index = $(this).index()
		$(this).addClass('current').siblings().removeClass('current')
		$tab_content.find('.gs-tab-item:eq(' + $tab_index + ')').addClass('gs-tab-show').siblings().removeClass('gs-tab-show')
		// if ($.fn.lazyload) {
		// 	$tab_content.find('.gs-tab-item:eq(' + $tab_index + ') img.js-geshopImg-lazyload').lazyload({
		// 		threshold: 100,
		// 		failure_limit: 20,
		// 		skip_invisible: false
		// 	})
		// }
		GESHOP_UTIL&&GESHOP_UTIL.goodsLazy($tab_content.find('.gs-tab-item:eq(' + $tab_index + ') img.js-geshopImg-lazyload'));
		$('html,body').animate({'scrollTop': $(this).closest('.geshop-component-box').offset().top-GESHOP_UTIL.getSiteFiedHeader()},500);
	});

	function initSlideTab ($box) {
		var staticDomain = $('[data-static-domain]:eq(0)').attr('data-static-domain') ? $('[data-static-domain]:eq(0)').attr('data-static-domain') : "";

		loadCss(staticDomain + '/resources/javascripts/library/swiper/swiper.min.css');
		$LAB.script(staticDomain + '/resources/javascripts/library/swiper/swiper.3.4.spec.min.js').wait(function () {

			new Swiper3($box.find('.swiper-container'), {
				autoplay: false,
				slidesPerView: 'auto',
				slideToClickedSlide:true,
				// slidesPerGroup: 4,
				nextButton: $box.find('.next-btn'),
				prevButton: $box.find('.pre-btn'),
				lazyLoading: true
			});
		})
	}

	turnGoods()

	setInterval( function() { turnGoods() }, 1000)


	/* 每隔四个小时切换一次商品 */
	function turnGoods() {
		$('[data-gid="U000041_zaful"]').each(function(index, element) {

			if (window.localStorage) {
				var nowTime = new Date().getTime();
				var $fixed_goods_count = parseInt($(element).find('.gs-tab').attr('data-fixed-g-count'));
				var $default_goods_count = parseInt($(element).find('.gs-tab').attr('data-default-g-count'));
				var $key = $(element).attr('data-key');
				var $id = $(element).attr('data-id');
				$(element).find('.gs-tab-item').each( function(i, e) {
					var default_g = JSON.parse(localStorage.getItem("geshop_goods_" + $key + "_" + $id + "_tab_" + i));
					if (default_g) {
						var default_time = default_g.time;
						var default_skus = default_g.skus;
						var default_count = default_g.default_count;
						var fixed_count = default_g.fixed_count;
						var time_range = Math.floor((nowTime - default_time))/(1000*60*60);
						if ( $default_goods_count == default_count && $fixed_goods_count == fixed_count ) {
							if ( time_range >= 4 ) {
								showGoodsRandom(i, e)	
							}else {
								$(e).find('.goods-item').each(function(index, ele){
									if ( index > $fixed_goods_count - 1 ) {
										$(ele).css("display", "none");
									}
								})
								for (var i = 0; i < default_skus.length; i++) {
									$(e).find('.goods-item').eq(default_skus[i]).css("display", "block");				
								}
							}
						}else {
							default_time_sku(element)
						}
					}else {
						default_time_sku(element)
					}
				})


			} else {
				setInterval( function() {
					showGoodsRandom(element)
				}, 14400000)
			}
		})

	}


	/* 获取不重复的任意值 */ 
	function createRandom(num, from, to){
	    var arr=[];
	    if ( from <= to && num >= 0 ) {
		    for(var i=from;i<=to;i++)
		        arr.push(i);
		    arr.sort(function(){
		        return 0.5-Math.random();
		    });
		    if (num > to + 1 - from) {
		    	arr.length=to + 1 - from;
		    }else {
		    	arr.length=num;
		    }
	    }
	    return arr;
	}

	/* 随机显示商品 */
	function showGoodsRandom(indexNum, e) {
		var $default_goods_count = parseInt($(e).parents('.gs-tab').attr('data-default-g-count'));
		var $fixed_goods_count = parseInt($(e).parents('.gs-tab').attr('data-fixed-g-count'));
		var $key = $(e).parents(".clearfix").attr('data-key');
		var $id = $(e).parents(".clearfix").attr('data-id');
		var $total = parseInt($(e).attr('data-total'));
		var goodsArray = createRandom($default_goods_count - $fixed_goods_count, $fixed_goods_count, $total -1);
		var default_goods = {
			time: new Date().getTime(),
			skus: goodsArray,
			default_count: $default_goods_count,
			fixed_count: $fixed_goods_count,
		};
		localStorage.setItem("geshop_goods_" + $key + "_" + $id + "_tab_" + indexNum, JSON.stringify(default_goods));
		if ($default_goods_count < $fixed_goods_count) {
			$(e).find('.goods-item').css("display", "none");
			for (var i = 0; i < $default_goods_count; i++) {
				$(e).find('.goods-item').eq(i).css("display", "block");
			}
			return false;
		}
		$(e).find('.goods-item').each(function(index, ele){
			if ( index > $fixed_goods_count - 1 ) {
				$(ele).css("display", "none");
			}
		})
		for (var i = 0; i < goodsArray.length; i++) {
			$(e).find('.goods-item').eq(goodsArray[i]).css("display", "block");
		}
	} 

	/* 首次加载存储时间和默认sku */
	function  default_time_sku(e) {
		var $default_goods_count = parseInt($(e).find('.gs-tab').attr('data-default-g-count'));
		var $fixed_goods_count = parseInt($(e).find('.gs-tab').attr('data-fixed-g-count'));
		var $key = $(e).attr('data-key');
		var $id = $(e).attr('data-id');
		var init_time = parseInt($(e).find('.gs-tab').attr('data-init-time'));
		var default_goods, goodsArray = [];

	    if ($default_goods_count) {
			$(e).find('.gs-tab-item').each( function(index, element) {
				var $total = parseInt($(element).attr('data-total'));
				for(var i=$fixed_goods_count;i<=$total - 1;i++) {
			        goodsArray.push(i)
			    }
			    goodsArray.length=$default_goods_count - $fixed_goods_count;
				default_goods = {
					time: init_time,
					skus: goodsArray,
					default_count: $default_goods_count,
					fixed_count: $fixed_goods_count,
				};
				localStorage.setItem("geshop_goods_" + $key + "_" + $id + "_tab_" + index, JSON.stringify(default_goods));
				$(element).find('.goods-item').each(function(index, ele){
					if ( index > $fixed_goods_count - 1 ) {
						$(ele).css("display", "none");
					}
				})
				for (var i = 0; i < goodsArray.length; i++) {
					$(element).find('.goods-item').eq(goodsArray[i]).css("display", "block");				
				}
			})
	    }

	}


		
	// function gotSiteFixHeaderHeight(){
	// 	var site = $(".gs-tab-site").attr("data-site").split("-")[0];
	// 	var headHeight=0,headHeightAll=0
	// 	if(site == "zf"){
	// 		// headHeightAll = 
	// 		// headHeight = $("#js-innerWrap").outerHeight(true) + $("#js-nav").outerHeight(true);
	// 		headHeightAll = $('.js-headerFixed').outerHeight();
	// 		//headHeightAll = $(".header-main-head").outerHeight(true) + $(".custom_top_box").outerHeight(true) + headHeight;
	// 	}
		
	// 	return headHeightAll;
	// }
	
	
	var components = [];
	//var gotSiteFixHeaderHeight = gotSiteFixHeaderHeight();
	$(".gs_tab_wrap_fixed").each(function(i,element){
		var $tabWrap = $(element);
		if($tabWrap.data('isaddfixbox')==1){
              return;
        }else{
            $tabWrap.data('isaddfixbox',1);
			var $content = $tabWrap.find('.gs-tab-content');
			var tabHeight  = $tabWrap.find(".gs-tab-wrap").outerHeight(true);
			var contentHeight =  $tabWrap.find(".gs-tab-content").outerHeight(true);
			$tabWrap.find('.gs-tab-wrap').children().wrap('<div class="gs-tab-wrap-fixBox" style="height:'+tabHeight+'px"></div>');
			var $tab = $tabWrap.find('.gs-tab-wrap-fixBox');
			components.push({
				tabWrap:$tabWrap,
				tab:$tab,
				content: $content,
				// top: site =="zf" ? tab.offset().top - headHeightAll : tab.offset().top,
				// left:tab.offset().left,
				tabHeight:tabHeight,
				//siteFixHeaderHeight :gotSiteFixHeaderHeight,
				contentHeight:contentHeight
			});
		}
	});
	function setFixed(top){
		$.each(components,function(i,item){
			if(item.tabWrap.data('tab-fix') == 1){
				var tabWrapTop = item.tabWrap.offset().top;
				var disId = $(item.tab).closest('.geshop-component-box').data('id');
			
			
				if(top  >= item.tabWrap.offset().top && item.tabWrap.offset().top + item.tabWrap.outerHeight(true) >=  item.tabHeight + top){
					item.tab.css({
						position:'fixed',
						height:item.tabHeight,
						width:item.tabWrap.outerWidth(),
						left:item.tabWrap.offset().left,
						top:0, //siteFixHeaderHeight,
						overflow:"hidden",
						'z-index':9999
					});
                    item.tabWrap.parents('[data-key="U000041"]').addClass('js-geshop-nav-fixed');

                    // if (GESHOP_SITECODE ==="rg-pc" && $('#nav').length) {
                    //     $('#nav').addClass('geshop-' + disId + '-fiexd');
                    // } else if (GESHOP_SITECODE ==="zf-pc" &&  $('#nav-list').length) {
                    //     $('#nav-list').closest('[class*="fix"]').addClass('geshop-' + disId + '-fiexd');
                    // }
                    // $('.geshop-' + disId + '-fiexd').hide();

                    // 站点导航栏处理
                    if ($('.js-geshop-nav').length) {
                        $('.js-geshop-nav').hide();
                    }

                    // 页面中存在水平导航时，隐藏水平导航
                    if ($('div[data-key="U000027"]').length) {
                        $('div[data-key="U000027"]').find('ul').hide();
                    }

				}else{
			
					item.tab.css({
						position:"static",
						top:0,
					});

                    item.tabWrap.parents('[data-key="U000041"]').removeClass('js-geshop-nav-fixed');

                    // 站点导航栏处理
                    if ($('.js-geshop-nav').length) {
                        $('.js-geshop-nav').show();
                    }

                    // 页面中存在水平导航时，隐藏水平导航
                    if ($('div[data-key="U000027"]').length) {
                        $('div[data-key="U000027"]').find('ul').show();
                    }

					// $('.geshop-'+disId+'-fiexd').show().removeClass('geshop-'+disId+'-fiexd');
				}
			}
		});

        if (GEShopSiteCommon) {
            GEShopSiteCommon.jsNavFixed()
        }
	}

	$(window).scroll(function(){
		var top  = $(this).scrollTop();
		setFixed(top);
	});
	$(window).resize(function(){
		var top  = $(this).scrollTop();
		setFixed(top);
	});

});


