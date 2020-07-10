;$(function () {
    var staticDomain = GESHOP_STATIC;

    loadCss(staticDomain + '/resources/javascripts/library/swiper/swiper.min.css');
    $LAB.script(staticDomain + '/resources/javascripts/library/waterfull/masonry.pkgd.min.js')
        .script(staticDomain + '/resources/javascripts/library/swiper/swiper.3.4.spec.min.js')
        .wait(function () {
            window.geshopIsLoading = false;
            $('.geshop-u000189-model1').each(function (index, item) {
                var $wrap = $(item);
                var navArr = [{id: 1, cat_name: 'nav1'}, {id: 1, cat_name: 'nav2'}, {id: 1, cat_name: 'nav3'}]; //默认导航
                var navStr = '';
                var mainStr = '';
                var $navWarp = $wrap.find('.geshop-navswiper');
                var $mainWarp = $wrap.find('.swiper-container2');
                var cats = $navWarp.find('.swiper-wrapper').data('cats');
                var catsID = '';
                var navurl = GESHOP_INTERFACE.community_category.url + '?cat_ids=' + cats + '&site=zafulcommunity';

                $.ajax({
                    url: navurl,
                    type: 'get',
                    timeout: 2000,
                    success: function (datas, status, xhr) {
                        if (datas.code == 0) {
                            if (datas.data.length > 0) {
                                navArr = datas.data;
                                initNavAndMain();
                            } else {
                                GEShopSiteCommon.dialog.message('data-length:' + datas.data.length);
                            }
                        }
                    },
                    // 网络异常
                    error: function (errs) {
                        GEShopSiteCommon.dialog.message(errs.message);
                    }
                });

                function initSwiper() {
                    var pageSwiper = new Swiper3($wrap.find('.swiper-container2'), {
                        paginationClickable: true,
                        uniqueNavElements: false,
                        speed: 100,
                        onSlideChangeStart: function (swiper) {
                            var $curSwipSlide = $wrap.find('.geshop-navswiper .swiper-slide');
                            var $curSwipSlideItem = $curSwipSlide.eq(swiper.activeIndex);
                            $curSwipSlideItem.addClass('active').siblings().removeClass('active');
                            var slide = mySwiper.slides[swiper.activeIndex];//获取当前的slide节点
                            var slideLeft = slide.offsetLeft;
                            var slideWidth = slide.clientWidth;
                            var slideCenter = slideLeft + slideWidth / 2;
                            // 被点击slide的中心点
                            mySwiper.setWrapperTransition(300)
                            if (slideCenter < swiperWidth / 2) {
                                mySwiper.setWrapperTranslate(0);
                            } else if (slideCenter > maxWidth) {
                                mySwiper.setWrapperTranslate(maxTranslate);
                            } else {
                                var nowTlanslate = slideCenter - swiperWidth / 2
                                mySwiper.setWrapperTranslate(-nowTlanslate);
                            }

                            var scrollTop = $(window).scrollTop();

                            var preHeight = $mainWarp.find('.swiper-slide').eq(swiper.previousIndex).find('.grid').height();

                            $curSwipSlide.eq(swiper.previousIndex).attr('data-scrolltop', scrollTop).attr('data-height', preHeight);
                            $mainWarp.find('.swiper-slide .grid').css({
                                'position': 'absolute',
                                'top': 0,
                                "left": 0,
                                'height': 0,
                                'z-index': '-1',
                                'overflow': 'hidden',
                            })

                            var curScrollTop = $wrap.find('.geshop-navswiper .swiper-slide').eq(swiper.activeIndex).attr('data-scrolltop');
                            var curHeight = $curSwipSlide.eq(swiper.activeIndex).attr('data-height') ? $curSwipSlide.eq(swiper.activeIndex).attr('data-height') : 0;
                            $mainWarp.find('.swiper-slide').eq(swiper.activeIndex).find('.grid').css({
                                'position': 'static',
                                'height': curHeight,
                                'overflow': 'visible',
                            })
                            $('html,body').scrollTop(!!curScrollTop ? curScrollTop : $wrap.find('.geshop-navswiper').position().top)

                        },
                        // 底部容器初始化
                        onInit: function () {
                            catsID = navArr[0].id;
                            $mainWarp.find('.swiper-slide-active').attr('data-load', 'load');
                            appendData($wrap, catsID);
                        },
                        // 底部容器滚动之后
                        onSlideChangeEnd: function (swiper) {
                            catsID = navArr[swiper.activeIndex].id;
                            if ($mainWarp.find('.swiper-slide-active').attr('data-load') !== 'load') {
                                appendData($wrap, catsID);
                                $mainWarp.find('.swiper-slide').eq(swiper.activeIndex).attr('data-load', 'load');
                            }
                        }
                    });

                    //监听滚动
                    var navTop = $wrap.find('.geshop-navswiper').offset().top;//导航的原始位置
                    $(window).scroll(function () {
                        var scrollTop = $(this).scrollTop();
                        var scrollH = $(document).height();
                        var winH = $(this).height();

                        if (scrollTop + winH >= scrollH) {
                            if (scrollTop != 0) {

                                appendData($wrap, catsID);
                            }
                        }
                        if (scrollTop > navTop) {
                            topFixd($wrap);
                        } else {
                            $wrap.find('.geshop-navswiper').css('position', 'static');
                            $wrap.find('.swiper-container2').css('paddingTop', 20 / 75 + 'rem');
                        }
                    });
                    var mySwiper = new Swiper3('.geshop-navswiper', {
                        freeMode: true,
                        freeModeMomentumRatio: 0.5,
                        slidesPerView: 'auto',
                    });

                    var swiperWidth = mySwiper.container[0].clientWidth;
                    var maxTranslate = mySwiper.maxTranslate();
                    var maxWidth = -maxTranslate + swiperWidth / 2;

                    $(".swiper-container").on('touchstart', function (e) {
                        e.preventDefault();
                    })

                    mySwiper.on('tap', function (swiper, e) {
                        pageSwiper.slideTo(swiper.clickedIndex, 300, true);//跳转
                        var slide = swiper.slides[swiper.clickedIndex];
                        var slideLeft = slide.offsetLeft;
                        var slideWidth = slide.clientWidth;
                        var slideCenter = slideLeft + slideWidth / 2;

                        // 被点击slide的中心点
                        mySwiper.setWrapperTransition(300)
                        if (slideCenter < swiperWidth / 2) {
                            mySwiper.setWrapperTranslate(0)
                        } else if (slideCenter > maxWidth) {
                            mySwiper.setWrapperTranslate(maxTranslate)
                        } else {
                            var nowTlanslate = slideCenter - swiperWidth / 2
                            mySwiper.setWrapperTranslate(-nowTlanslate)
                        }
                        $(".geshop-navswiper .swiper-slide").removeClass('active')
                        $(".geshop-navswiper .swiper-slide").eq(swiper.clickedIndex).addClass('active')
                    })
                }

                function initNavAndMain() {
                    navArr.forEach(function (value, index) {
                        if (index == 0) {
                            navStr += "<div class='swiper-slide active'><span>" + value.cat_name + "</span></div>";
                        } else {
                            navStr += "<div class='swiper-slide'><span>" + value.cat_name + "</span></div>";
                        }
                        mainStr += "<div class='swiper-slide' data-page=1><div class='grid'></div></div>";
                    });
                    $navWarp.find('.swiper-wrapper').html(navStr);
                    $mainWarp.find('.swiper-wrapper').html(mainStr);
                    initSwiper();
                };
            });

            // 导航栏固定
            function topFixd($wrap) {
                $wrap.find('.geshop-navswiper').css({'position': 'fixed', 'top': 0});
            };

            //ajax 加载数据
            function appendData(dom, id) {
                if (geshopIsLoading) {
                    return false;
                }

                var $activeDom = dom.find('.swiper-slide-active');
                var $swiperCont = dom.find('.swiper-container2').find('.swiper-slide-active');
                var nowPage = $swiperCont.attr('data-page');//当前页码
                var totalPage = $swiperCont.attr('data-totalPage');//总页数
                var url = GESHOP_INTERFACE.community_post.url + '?cat_id=' + id + '&country=&site=zafulcommunity&size=20&page=' + nowPage;
                
                if (nowPage != 1 && !!totalPage && (nowPage - 0) > (totalPage - 0)) {
                    return;
                } else {
                    var $laodingHtml = $('<div class="loadingGif" style="position: absolute;background:#fff;width: 100%; height: 50px;left: 0;bottom: 0;text-align: center;"><img src="https://geshopimg.logsss.com/uploads/K016no8NLPkfFRXZ5IEsv4OJrl7VD3Wm.gif" ></div>');
                    $swiperCont.css('paddingBottom', 50).append($laodingHtml);
                    var geshopIsLoading = true;

                    $.ajax({
                        url: url,
                        type: 'get',
                        data: {},
                        success: function (datas, status, xhr) {
                            geshopIsLoading = false;

                            if (datas.code == 0) {
                                if (datas.data.totalPage >= nowPage && datas.data.totalPage > 0) {
                                    appendHtml($activeDom, datas.data.lists);
                                    $swiperCont.css('paddingBottom', 0);
                                    $laodingHtml.remove();
                                    ++nowPage;
                                    $swiperCont.attr('data-page', nowPage);
                                    $swiperCont.attr('data-totalPage', datas.data.totalPage);
                                } else {
                                    GEShopSiteCommon.dialog.message('totalPage:' + datas.data.totalPage);
                                }
                            }
                        },
                        // 网络异常
                        error: function (errs) {
                            GEShopSiteCommon.dialog.message(errs.message);
                        }
                    });
                }

            }

            function appendHtml($activeDom, data) {
                var scale = 345 * ($('html').css('font-size').slice(0, -2) - 0) / 75;
                for (var i = 0, len = data.length; i < len; i++) {
                    if (typeof GS_GOODS_LAZY_FN != "undefined") {
                        var $str = $(' <a href="' + data[i].url + '" class="grid-item" style="height:' + scale * data[i].big_pic_height / data[i].big_pic_width + 'px"><img class="js-geshopImg-lazyload" src="" data-original="' + data[i].big_pic + '" alt="" height="' + scale * data[i].big_pic_height / data[i].big_pic_width + '"></a>');

                        var $grid = $activeDom.find('.grid').masonry({
                            // options
                            itemSelector: '.grid-item',
                        });
                        $grid.append($str).masonry('appended', $str).masonry();
                        GS_GOODS_LAZY_FN($str.find('.js-geshopImg-lazyload'));
                    } else {
                        var $str = $(' <a href="' + data[i].url + '" class="grid-item" style="height:' + scale * data[i].big_pic_height / data[i].big_pic_width + 'px"><img class="js-geshopImg-lazyload" src="' + data[i].big_pic + '" alt="" height="' + scale * data[i].big_pic_height / data[i].big_pic_width + '"></a>');

                        $grid = $activeDom.find('.grid').masonry({
                            // options
                            itemSelector: '.grid-item',
                        });
                        $grid.append($str).masonry('appended', $str).masonry();
                    }
                }
            }
        });
});

