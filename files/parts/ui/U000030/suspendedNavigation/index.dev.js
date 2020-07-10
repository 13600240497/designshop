$(function () {
	var navigateTarget = $('.component-m-nav');
	var scrollFlag = 0;
	if(window.location.hash.split('#').length > 1){
    scrollFlag = 1;
    var pageTo = window.location.hash.split('#')[1].split('=')[1];
    $('html, body').animate({
      scrollTop: $('.geshop-component-box[data-id=' + pageTo + ']').offset().top + window.screen.height/2
		}, 500);
		navigateTarget.parent().addClass('component-m-nav-fixed');
		$('.component-m-nav-item[data-id='+ pageTo +']').addClass('m-current');
	}
	if (!window.Swiper && $LAB) {
		var staticDomain = $('[data-static-domain]:eq(0)').attr('data-static-domain') ? $('[data-static-domain]:eq(0)').attr('data-static-domain') : "";
		loadCss(staticDomain + '/resources/javascripts/library/swiper/swiper.min.css')
        $LAB.script(staticDomain + '/resources/javascripts/library/swiper/swiper.4.5.min.js').wait(function () {
			startNavCheck()
		})
	} else {
		startNavCheck()
	}
	
	// 初始化函数2
	function startNavigation () {
		$(function () {

			// swiper 初始化
			var mySwiper = new Swiper('#js_topNav_suspend2', {
				slidesPerView: 'auto',
				pagination: {
					el: '.swiper-pagination',
					clickable: true,
				}
			});

			// 公共变量
			var goods_list_box = $('div[attr="nav_flag"]'),         //商品列表
				goods_h_arr = [],                                   //商品列表高度集合
				is_move = false,                                    //用来限制点击滚动时触发 scroll 事件的 flag
				nav_list = $('.component-m-nav-item'),    //导航菜单 DOM
				nav_ul = $('#nav-m-ul'),                  //导航菜单容器
				active_index,                             //当前点亮导航菜单的 下标
				first_offset_page_h,                      //第一个列表距离页面顶部的距离
				scrollContainer,                          //滚动的容器
				scrollContainerE,                         //滚动的容器(监听)
				flag,                                     //导航菜单需要横向滚动的 下标 flag
				table_nav = $('#nav-table-ul'),
				is_show_downlist = true;				// 判断是否展示下拉菜单


			var is_edit = nav_ul.data('isedit')         //是否处于编辑模式
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

			//获取 商品列表高度集合
			function updateFlagsPoint() {
				goods_h_arr = [];
				goods_list_box.each(function (i, n) {
					var id = $(this).attr('data-id');
					// 检测此楼层是否在勾选列表中
					if (arrayInclude(menu_ids, id) == true) {
						// 
						var totalH = getOffsetH(n)
						var self_H = $(n).height()
						if ($('.component-m-nav-ul.swiper-wrapper').hasClass('show')) {
							var nav_H = $('.component-m-nav-ul.swiper-wrapper').height()
						} else {
							var nav_H = 0;
						}
						let _point = {
							id: $(n).data('id'),
							height: totalH - self_H - nav_H
						}
						goods_h_arr.push(_point);
					}
				});
			}


			// 检查数组中是否存在相应的值
			function arrayInclude(array, key) {
				var tof = false;
				for (var i=0; i<array.length; i++) {
					if (array[i]==key) tof = true;
				}
				return tof;
			}

			// 检查水平组件是否在视窗内
			function isInViewport(positon) {
				var viewport_start_y = $(window).scrollTop();
				var res = positon > viewport_start_y
				return res;
			}

			// 显示所有分类
			function showAllCategory(tof) {
				if (!tof) {
					$('#nav-down-ul').removeClass('show')
					$('.geshop-icon-up').removeClass('active');
				} else {
					$('#nav-down-ul').addClass('show')
					$('.geshop-icon-up').addClass('active');
				}
				is_show_downlist = !tof;
			}

			// 设定Tab导航样式
			function setTab(index) {
				// 设定横向导航样式
				$('#nav-m-ul').find('li').removeClass('m-current').eq(index||0).addClass('m-current');
				$('#nav-down-ul').find('li').removeClass('m-current').eq(index||0).addClass('m-current');
			}

			// 根据id获取对应楼层的高度，返回高度值px
			function getFlagPositionByID(id) {
				// 更新楼层的值
				updateFlagsPoint();
				// 计算
				var length = goods_h_arr.length;
				var scrollS = 0;
				for (var index = 0; index < length; index++) {
					if (id == goods_h_arr[index].id) {
						scrollS = goods_h_arr[index].height
					}
				}
				return scrollS;
			}

			// 根据滚动值找到对应的激活楼层，返回ID
			function getActiveIndexByScrollTop(sT) {
				// 更新各楼层的值
				updateFlagsPoint();
				var active_index = 0;
				for (var i = 0; i < goods_h_arr.length; i++) {
					if (!goods_h_arr[i+1]) {
						if (sT >= goods_h_arr[i].height ) {
							active_index = i;
						}
					} else {
						if (sT >= goods_h_arr[i].height && sT < goods_h_arr[i+1].height) {
							active_index = i;
						}
					}
				}
				return active_index;
			}

			//监听 导航菜单 滚动逻辑
			function scrollFn () {

				if (is_move == true) return false;

				var window_scrollTop = $(this).scrollTop(); //滚动条滚动距离

				// 导航栏组件的scrollTop
				var self_scrollTop = getOffsetH($('[data-gid="U000030_suspendNav"]').get(0));
				var self_height = $('[data-gid="U000030_suspendNav"]').height();

				// 首个组件的高度
				first_offset_page_h = getOffsetH(goods_list_box.get(0));

				// 获取最新激活的楼层 
				var active_index = getActiveIndexByScrollTop(window_scrollTop);

				// 设置 tab 内容
				setTab(active_index);

				// 更新 swiper
				mySwiper.slideTo(active_index);
				mySwiper.update();

				//如果页面滚动高度等于或大于 bodyH，添加 fixed 样式（固定在页面顶部 

				// 检查是否在viewport内
				if (isInViewport(self_scrollTop) === true) {
					$('[data-gid="U000030_suspendNav"]').removeClass('active')
					$('#pageHeader, #topheader').show();
				} else {
					$('[data-gid="U000030_suspendNav"]').addClass('active')
					$('#pageHeader, #topheader').hide();
				}
			}
			


			var menu_ids = [];
			$('#nav-table-ul').find('td').each(function(i, el){
				menu_ids.push($(this).attr('data-id'));
			});

			// 更新各节点
			updateFlagsPoint();

			//导航菜单 点击 滚动逻辑
			$('#nav-m-ul').find('li').click(function (event) {
				event.stopPropagation();
				var id = Number($(this).data('id'));
			
				var index = $(this).index();
				// 获取楼层高度
				var gotoHeight = getFlagPositionByID(id)
				// 设置tab的索引
				setTab(index)
				// 设置swpiper
				mySwiper.slideTo(index)
				mySwiper.update()
				// 关闭所有弹层
				showAllCategory(false)
				// 锁定滚动事件
				is_move = true;
				// 滚动到对应的层
				scrollContainerE.animate({
					'scrollTop': gotoHeight
				}, 300);
				setTimeout(() => {
					is_move = false
					// scrollFn();
				}, 600);
			});

			// 默认表格点击
			table_nav.find('td').click(function(event) {
				event.stopPropagation();
                var id = Number($(this).data('id'));
				var index = $(this).index();
				// 获取楼层高度
				var gotoHeight = getFlagPositionByID(id)
				// 设置tab的索引
				setTab(index)
				// 设置swpiper
				mySwiper.slideTo(index)
				mySwiper.update()
				// 关闭所有弹层
				showAllCategory(false)
				// 锁定滚动事件
				is_move = true;
				// 滚动到对应的层
				scrollContainerE.animate({
					'scrollTop': gotoHeight
				}, 500, function () {
					setTimeout(function () {
						is_move = false
						scrollFn();
					}, 100);
				});
			});

			// 弹层点击
			$('#nav-down-ul').find('li').click(function(){
				event.stopPropagation();
				var id = Number($(this).data('id'));
				var index = $(this).index();
				// 获取楼层高度
				var gotoHeight = getFlagPositionByID(id)
				// 设置tab的索引
				setTab(index)
				// 设置swpiper
				mySwiper.slideTo(index)
				mySwiper.update()
				// 关闭所有弹层
				showAllCategory(false)
				// 锁定滚动事件
				is_move = true;
				// 滚动到对应的层
				scrollContainerE.animate({
					'scrollTop': gotoHeight
				}, 500, function () {
					setTimeout(function () {
						is_move = false
						scrollFn();
					}, 100);
				});
			});

			// 展示或者关闭更多菜单
			$('.geshop-icon-up').click(function(){
				showAllCategory(is_show_downlist)
			});

			//监听 导航菜单 滚动逻辑
			scrollContainer.scroll(scrollFn);


		});
	}

	// 初始化函数1, 检查是否装修页
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
			// $.when.apply(null, imgdefereds).done(function () {
				startNavigation();
			// });
		}
	}

	// 加载 class
	function loadCss (href) {
		var link = document.createElement("link");
		link.setAttribute("rel", "stylesheet");
		link.setAttribute("type", "text/css");
		link.setAttribute("href", href);
		document.getElementsByTagName("head")[0].appendChild(link);
	}
});
