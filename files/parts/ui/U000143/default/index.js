// if($){
// 	$('.component-m-nav-item').eq(0).addClass('m-current')
// };
if (typeof jQuery == 'undefined') {
	$LAB.script(window.GESHOP_STATIC + '/resources/javascripts/library/jquery.js').wait(function () {
		init()
		var jq = $.noConflict();
	})
} else {
	init()
}

function init () {
	(function ($) {
		$(function () {
			if (!$.fn.swiper3 && $LAB) {
				var staticDomain = typeof GESHOP_STATIC == "undefined" ? "" : GESHOP_STATIC;
				loadCss(staticDomain + '/resources/javascripts/library/swiper/swiper.min.css')
				$LAB.script(staticDomain + '/resources/javascripts/library/swiper/swiper.3.4.spec.min.js').wait(function () {
					startNavCheck()
				})
			} else {
				startNavCheck()
			}
			function startNavigation () {
				$(function () {
					var mySwiper = new Swiper3('#js_topNav', {
						freeMode: false,
						slidesPerView: 'auto'
					});

					mySwiper.setWrapperTransition(300);

					var goods_list_box = $('div[attr="nav_flag"]'),         //商品列表
						goods_h_arr = [],                                   //商品列表高度集合
						is_move = false,                                    //用来限制点击滚动时触发 scroll 事件的 flag
						nav_list = $('.component-m-nav-item'),    //导航菜单 DOM
						nav_ul = $('#nav-m-ul'),                  //导航菜单容器
						nav_ul_w = nav_ul.width(),                //导航菜单宽度
						active_index = 0,                             //当前点亮导航菜单的 下标
						nav_item_w_arr = [],                      //导航菜单宽度集合
						nav_item_average_length,                  //导航菜单平均长度
						first_offset_page_h,                      //第一个列表距离页面顶部的距离
						scrollContainer,                          //滚动的容器
						scrollContainerE,                         //滚动的容器(监听)
						flag,                                     //导航菜单需要横向滚动的 下标 flag
						maxTranX;

					//定义导航栏高度常量
					var NAV_UL_H = nav_ul.outerHeight(true)

					var is_edit = nav_ul.data('isedit')         //是否处于编辑模式
					if (is_edit == 0) {
						// 给body元素添加 导航高度的padding-bottom;防止导航栏遮盖底部内容
						$('body').css({ paddingBottom: NAV_UL_H + 'px' })
					} else if (is_edit == 1) {
						$('#js_topNav').css({ 'position': 'absolute' })
					}
					// 默认选中第一个导航条
					nav_list.eq(active_index).siblings().removeClass('m-current').end().addClass('m-current');

					//获取 滚动的容器
					scrollContainer = is_edit == 1 ? $('.design-right') : $(document)
					scrollContainerE = is_edit == 1 ? $('.design-right') : $("body,html")

					//获取 列表距离页面顶端的高度
					var top_h = 0
					function getOffsetH (dom, oldH) {
						if (!dom) {
							return
						}

						oldH = oldH ? oldH : 0;

						top_h = dom.offsetTop + oldH;

						if (dom.offsetParent) {
							getOffsetH(dom.offsetParent, top_h)
						}

						return top_h
					}

					//获取列表距离页面顶部的距离
					first_offset_page_h = getOffsetH(goods_list_box.get(0));

					function cacl () {
						maxTranX = nav_item_w_arr[nav_item_w_arr.length - 1] - $(window).width()
					}

					//resize监听
					var resizeTimeout = null
					$(window).on('resize', function () {
						if (resizeTimeout) clearTimeout(resizeTimeout)
						resizeTimeout = setTimeout(function () {
							cacl();
						}, 100)
					})

					//获取 商品列表高度集合
					var TOPHeight =  0
					goods_list_box.each(function (i, n) {
						if(i===0){
							TOPHeight = getOffsetH(n)||0;
						}
						var totalH = getOffsetH(n)
						// 导航条固定定位，不需要再减去导航条高度；
						totalH -= TOPHeight

						goods_h_arr.push({
							id: $(n).data('id'),
							height: totalH
						});
					});

					//获取 导航菜单宽度集合
					nav_ul.find('li').each(function (i, n) {
						var nav_w = n.offsetWidth;
						var totalW = i > 0 ? nav_item_w_arr[i - 1] + nav_w : nav_w;

						if (totalW) {
							nav_item_w_arr.push(totalW);
						}
					});

					//获取 导航菜单平均长度
					nav_item_average_length = Math.ceil(nav_item_w_arr[nav_item_w_arr.length - 1] / nav_item_w_arr.length)

					//获取 导航菜单需要横向滚动的 下标 flag
					for (var k = 0, len1 = nav_item_w_arr.length; k < len1; k++) {
						if (nav_item_w_arr[k] >= nav_ul_w) {
							flag = k;
							break
						}
					}

					maxTranX = nav_item_w_arr[nav_item_w_arr.length - 1] - $(window).width()
					var clickTimer = null
					//导航菜单 点击 滚动逻辑
					nav_list.click(function () {
						var $this = $(this);
						$this
							.siblings()
							.removeClass('m-current')
							.end()
							.addClass('m-current');

						if ($this.length > 0 && maxTranX > 0) {

							// if left>ul.width/2,移动到中线附近； if ul.width - left
							var left = $this.position().left, ulWidth = $(window).width(), thisWidth = $(this).outerWidth();
							var maxL = nav_item_w_arr[nav_item_w_arr.length - 1], tranX = 0
							if ((left + thisWidth / 2) < ulWidth / 2) {
								mySwiper.setWrapperTranslate(0, 0, 0);
							} else if (maxL - left < ulWidth / 2) { // 右侧
								mySwiper.setWrapperTranslate(-(maxL - ulWidth), 0, 0);
							} else {

								tranX = left + thisWidth / 2 - ulWidth / 2
								if (maxL - (left + thisWidth / 2) < ulWidth / 2) {
									tranX = maxL - ulWidth
								}
								mySwiper.setWrapperTranslate(-tranX, 0, 0);
							}

						}


						// var index = nav_list.index( $(this) );
						var id = Number($this.data('id'));
						var length = goods_h_arr.length;
						var scrollS = 0;

						for (var index = 0; index < length; index++) {
							if (id === Number(goods_h_arr[index].id)) {
								scrollS = goods_h_arr[index].height;
							}
						}

						is_move = true;

						scrollContainerE.stop().animate({
							'scrollTop': scrollS
						}, 500, function () {
							clearTimeout(clickTimer)
							//防止触发 scroll 事件
							clickTimer = setTimeout(function () {
								is_move = false

								//如果页面滚动高度等于或大于 bodyH，添加 fixed 样式（固定在页面顶部）

								if (scrollS >= first_offset_page_h) {
									if (!nav_ul.parent().hasClass('component-m-nav-fixed')) {
										nav_ul.parent().addClass('component-m-nav-fixed');

									}
								} else {
									if (nav_ul.parent().hasClass('component-m-nav-fixed')) {
										nav_ul.parent().removeClass('component-m-nav-fixed')
									}
								}

							}, 50);



						});

					});

					//监听 导航菜单 滚动逻辑
					scrollContainer.scroll(scrollFn)

					//监听 导航菜单 滚动逻辑
					function scrollFn () {
						var sT = $(this).scrollTop(); //滚动条滚动距离

						var flagLen;

						if (is_move) {
							return
						}

						for (var i = 0, len = goods_h_arr.length; i < len; i++) {
							var nextHeight = goods_h_arr[i + 2] ? sT < goods_h_arr[i + 2].height : true
							if (sT < goods_h_arr[1].height) {
								active_index = 0
								break
							} else if (goods_h_arr[i + 1] && sT > goods_h_arr[i + 1].height && nextHeight) {
								active_index = i + 1
								break
							}

						}

						if (active_index >= flag) {
							if (nav_ul_w >= nav_item_w_arr[nav_item_w_arr.length - 1]) {
								flagLen = 0
							} else {
								flagLen = (active_index + 1 - flag) * nav_item_average_length
							}
						}

						//控制当前商品列表对应的导航 item 显示出来
						if (flagLen) {
							nav_ul.scrollLeft(flagLen)
						} else {
							nav_ul.scrollLeft(0)
						}

						var active_goods_item = nav_list.eq(active_index);  //当前商品列表

						active_goods_item
							.siblings()
							.removeClass('m-current')
							.end()
							.addClass('m-current');



						//如果页面滚动高度等于或大于 bodyH，添加 fixed 样式（固定在页面顶部）
						if (sT >= first_offset_page_h) {

							nav_ul.parent().addClass('component-m-nav-fixed')
						} else {
							nav_ul.parent().removeClass('component-m-nav-fixed')
						}

						if (active_goods_item.length > 0 && maxTranX > 0) {
							
							var left = active_goods_item.position().left, ulWidth = $(window).width(), thisWidth = active_goods_item.outerWidth();
							var maxL = nav_item_w_arr[nav_item_w_arr.length - 1], tranX = 0
							if ((left + thisWidth / 2) < ulWidth / 2) {
								mySwiper.setWrapperTranslate(0, 0, 0);
							} else if (maxL - left < ulWidth / 2) {
								mySwiper.setWrapperTranslate(-(left + thisWidth - ulWidth), 0, 0);

							} else {
								tranX = left + thisWidth / 2 - ulWidth / 2
								if (maxL - (left + thisWidth / 2) < ulWidth / 2) {
									tranX = maxL - ulWidth
								}
								mySwiper.setWrapperTranslate(-tranX, 0, 0);
							}
						}
					}
				});

			}

			function startNavCheck () {
				var is_edit = $('#nav-m-ul').data('isedit');
				if (!1 == is_edit) {
					var imgdefereds = [];
					$('img').each(function () {
						var dfd = $.Deferred();
						$(this).bind('load', function () {
							dfd.resolve();
						}).bind('error', function () {
						});
						if (this.complete) setTimeout(function () {
							dfd.resolve();
						}, 1000);
						imgdefereds.push(dfd);
					});
					$.when.apply(null, imgdefereds).done(function () {
						startNavigation();
					});
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
	}(jQuery))
}

