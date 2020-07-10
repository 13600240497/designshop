$(function () {
    if (!$.fn.swiper && $LAB) {
        var staticDomain = typeof GESHOP_STATIC == "undefined" ? "" : GESHOP_STATIC;
        loadCss(staticDomain + '/resources/javascripts/library/swiper/swiper.min.css')
        $LAB.script(staticDomain + '/resources/javascripts/library/swiper/swiper.4.5.min.js').wait(function () {
            startNavCheck()
        })
    } else {
        startNavCheck()
    }
    function startNavigation () {
        $(function () {



            var mySwiper = new Swiper('#js_topNav', {
                freeMode: false,
                slidesPerView: 'auto'
            });

            var nav_list_offtop = [],                     //导航栏偏移位置集合
                is_move = false,                          //用来限制点击滚动时触发 scroll 事件的 flag
                nav_list = $('.component-m-nav-item'),    //导航菜单 DOM
                nav_ul = $('#nav-m-ul'),                  //导航菜单容器
                active_index = 0,                         //当前点亮导航菜单的 下标
                scrollContainer,                          //滚动的容器
                scrollContainerE;                         //滚动的容器(监听)

            var is_edit = nav_ul.data('isedit');         //是否处于编辑模式
            var item_id = nav_list.eq(active_index).attr('data-id');
            var next_item_id;

            // 默认选中第一个导航条
            if(window.location.hash.split('#').length > 1){

                var pageTo = window.location.hash.split('#')[1].split('=')[1];
                var curentIndex;
                $('.component-m-nav-item[data-id='+ pageTo +']').addClass('m-current');
                $('.component-m-nav-ul li').each(function(index,item){
                  if($(item).data('id') == pageTo){
                    curentIndex = index;
                  }
                })
                tabShow(pageTo);
                mySwiper.slideTo(curentIndex);
              }else{
                nav_list.eq(active_index).siblings().removeClass('m-current').end().addClass('m-current');
                // 选中第一个导航
                tabShow(item_id);
            }


            //获取 滚动的容器
            scrollContainer = is_edit == 1 ? $('.design-right') : $(document)
            scrollContainerE = is_edit == 1 ? $('.design-right') : $("body,html")

            // 导航栏ID集合
            var nav_items_id = nav_ul.find('li').map((index, item) => {
                return $(item).data('id')
            });

            // 获取所有导航栏偏移位置
            nav_items_id.map((index, id) => {
                var target = $(`.geshop-component-box[data-id="${id}"]`);
                var target_top = target.offset().top || 0;
                nav_list_offtop.push({ id, top: target_top});
            });



            // 获取导航栏显示
            function tabShow(id) {
                nav_ul.find('li').each(function () {
                    if ($(this).hasClass('m-current')) {
                        next_item_id = $(this).next('li').attr('data-id');
                    }
                });

                $('.geshop-component-box[data-id]').hide();

                // 当前导航页
                $(`.geshop-component-box[data-id="${id}"]`).show().nextAll('.geshop-component-box[data-key]').show();

                if (next_item_id != undefined) {
                    $(`.geshop-component-box[data-id="${next_item_id}"]`).hide().nextAll('.geshop-component-box[data-key]').hide();
                }
                // 导航栏显示
                $('.geshop-component-box[data-key="U000030"]').show();
            }


            //导航菜单 点击 滚动逻辑
            nav_list.click(function (event) {
                event.stopPropagation();
                var $this = $(this);
                $this.addClass('m-current').siblings().removeClass('m-current');

                var id = Number($this.data('id'));

                var _index = $(this).index();

                // 锁定滚动事件
                is_move = true;

                // tab页显示
                tabShow(id);

                // 点击导航就定位到顶部
                if (!nav_ul.parent().hasClass('component-m-nav-fixed')) {
                    nav_ul.parent().addClass('component-m-nav-fixed');
                }

                // 设置swpiper
                mySwiper.slideTo(_index);
                mySwiper.update();
                scrollContainerE.stop().animate({
                    'scrollTop': 0
                }, 500);
            });

            scrollContainer.scroll(scrollThrottle(scrollFn, 50));

            //监听 导航菜单 滚动逻辑
            function scrollFn () {
                var sT = $(this).scrollTop(); //偏移距离
                if (is_move) {
                    return;
                }
                var activity_items = nav_list_offtop.filter(item => {
                    if (item.top <= sT) {
                        return item;
                    }
                });
                fixFn(activity_items);
            }

            // 导航栏固定在顶部
            function fixFn(activity_items) {
                if (activity_items.length > 0) {
                    if (!nav_ul.parent().hasClass('component-m-nav-fixed')) {
                        nav_ul.parent().addClass('component-m-nav-fixed');
                    }
                } else {
                    if (nav_ul.parent().hasClass('component-m-nav-fixed')) {
                        nav_ul.parent().removeClass('component-m-nav-fixed')
                    }
                }
            }
        });

    }

    function scrollThrottle(fn, delay) {
        var _lastTime = null;
        return function () {
            var _this = this;
            var args = arguments;
            var _nowTime = + new Date()
            if (_nowTime - _lastTime > delay || !_lastTime) {
                fn.apply(_this, args)
                _lastTime = _nowTime
            }
        }
    };

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
            startNavigation();
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
