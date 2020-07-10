;$(function () {
    $('.geshop-u000209-default').each(function (item, elem) {

        var staticDomain = GESHOP_STATIC;
        loadCss(staticDomain + '/resources/javascripts/library/swiper/swiper.min.css');
        $LAB.script(staticDomain + '/resources/javascripts/library/swiper/swiper.3.4.spec.min.js')
            .script(staticDomain + "/resources/javascripts/library/gs_laytpl.js").wait(function () {


            gs_laytpl.config({ open: "<%", close: "%>" });
            var $elem = $(elem);
            // 一级导航下标s
            var fnavIndex = 0;
            // 二级导航下标
            var snavIndex = 0;
            var navTop; //导航的原始位置
            var pageSize = 20; // 每次显示数量
            var curPage = 0; // 当前页
            var pageFlag = false;
            var fixed = $elem.find('.geshop-containr').data('fixed');
            // 导航结构数据
            //var navList = JSON.parse($elem.find('.geshop-containr').attr('data-navList'));
            //sku数据
            //var goodsSKU = JSON.parse($elem.find('.geshop-containr').attr('data-goodssku'));
            var dataId  =  $elem.data('id');
            var navList = window['navList_'+dataId];
            var goodsSKU = window['goodsSku_'+dataId];

            // 导航点击居中
            function swiperCenter(mySwiper, callback) {
                /* if(navList) {

                 }*/
                centerSecd();
                if (mySwiper.container.length < 1) {
                    return
                }
                var clientW = document.body.clientWidth;
                // geshop-navswiper 显示宽度
                var swiperWidth = mySwiper.container[0].clientWidth;

                // 最大偏移
                var maxTranslate = mySwiper.maxTranslate();
                //geshop-navswiper
                var maxWidth = -maxTranslate + swiperWidth / 2;
                mySwiper.on('tap', function (swiper, e) {
                    var slide = swiper.slides[swiper.clickedIndex];
                    if (typeof slide !== 'undefined') {
                        // $elem.find('.second-nav-list').empty();
                        pageFlag = true;
                        $(slide).addClass('active').siblings().removeClass('active');
                        fnavIndex = $elem.find($elem.find('.geshop-navswiper').find('.swiper-wrapper').find('.swiper-slide.active')).index();
                        snavIndex = $elem.find('.geshop-secondswiper-' + fnavIndex + ' .swiper-slide.active').index();
                        // snavIndex = 0;
                        curPage = $elem.find('.list-' + fnavIndex + '-' + snavIndex).attr('data-curPage');
                        var slideLeft = slide.offsetLeft;
                        var slideWidth = slide.clientWidth;
                        var slideCenter = slideLeft + slideWidth / 2;
                        // 被点击slide的中心点
                        mySwiper.setWrapperTransition(300);
                        if (slideCenter < swiperWidth / 2) {
                            mySwiper.setWrapperTranslate(0);
                        } else if (slideCenter > maxWidth) {
                            mySwiper.setWrapperTranslate(maxTranslate);
                        } else {
                            var nowTlanslate = slideCenter - (swiperWidth - clientW / 375 * 20) / 2;
                            mySwiper.setWrapperTranslate(-nowTlanslate);
                        }
                        //  顶部图片切换
                        if (typeof callback == 'function') {
                            callback(swiper.clickedIndex, $elem);
                        }
                    }
                });
            }


            //  顶部图片切换
            function changeNavPic(index) {
                fnavIndex = index;
                $elem.find('.geshop-ad-wrap ul li').eq(index).addClass('on').siblings().removeClass('on');
            }

            // 初始化 二级导航
            function initSecdSwiper(item) {
                var secdSwiper = new Swiper3($elem.find('.geshop-secondswiper-' + item), {
                    freeMode: true,
                    freeModeMomentumRatio: 0.5,
                    slidesPerView: 'auto',
                });
                secdSwiper.setWrapperTranslate(0);
                // 二级导点击航居中
                swiperCenter(secdSwiper, changeList);
            }

            // 数据列表页切换
            function changeList(item) {
                if (navList != null && typeof navList != 'undefined' && navList.length > 0) {
                    var list = $elem.find('.list-' + fnavIndex + '-' + snavIndex);
                    // 切换tab
                    $elem.find('.fbox').eq(fnavIndex).addClass('on').siblings().removeClass('on');
                    $elem.find('.fbox.on .second-nav-list').removeClass('on').eq(snavIndex).addClass('on');
                    var fxd = $elem.find('.secdBox').find('.geshop-secondswiper.on').hasClass('fixed');
                    var nTop = list.attr('data-top');
                    if (fxd) {
                        if (nTop < navTop) {
                            $('html,body').scrollTop(navTop);
                        } else {
                            $('html,body').scrollTop(list.attr('data-top'));
                        }
                    }

                    // 加载过得不重新请求
                    if (typeof list.attr('data-load') == 'undefined') {
                        getGoodsList(fnavIndex, snavIndex);
                        list.attr('data-load', 'load');
                    }

                }
            }

            function getGoodsList(index1, index2) {
                var goodsSn;
                // 根据选品方式获取SKU
                for (var i = 0; i < goodsSKU.length; i++) {
                    if (goodsSKU[i].key == index1 + '-' + index2 && goodsSKU[i].skuFrom == 2) {
                        goodsSn = goodsSKU[i].ipsGoodsSKU;
                    } else if (goodsSKU[i].key == index1 + '-' + index2 && goodsSKU[i].skuFrom == 1) {
                        goodsSn = goodsSKU[i].inputSku;
                    }
                }

                goodsSn = goodsSn.split(',');
                var totalPage = Math.ceil(goodsSn.length / pageSize);
                var curGoodsSn = goodsSn.slice(curPage * pageSize, (curPage + 1) * pageSize).join(',');
                if (curPage < totalPage) {
                    getData(curGoodsSn);
                }

            }

            function getData(goodsSn) {
                $elem.find('.giftImg').show();
                
                var url = GESHOP_INTERFACE.goods_async_detail.url;
                var component_id = $elem.attr('data-id');
                var params = { lang: GESHOP_LANG || "en", goodsSn: goodsSn , filterStock : 1};

                window.GEShopCommonFn_Vue.$jsonp(url, params, { pid: component_id }).done(function (res) {
                    try {
                        initHtml(res.data.goodsInfo);
                    } catch (e) {}
                });
            }

            // 渲染商品
            function initHtml(data) {
                var getTpl = $elem.find('.cont-goodsList').html(),
                    view = $elem.find('.list-' + fnavIndex + '-' + snavIndex);
                gs_laytpl(getTpl).render(data, function (html) {
                    var ad = document.createElement('span');
                    ad.innerHTML = html;
                    /*if (typeof GS_GOODS_LAZY_FN != 'undefined') {
                        GS_GOODS_LAZY_FN($(ad).find('.js-geshopImg-lazyload'));
                    } else {
                        $elem.find('.second-nav-list').find('.js-geshopImg-lazyload').each(function () {
                            $(this).attr('src', $(this).data('original'));
                        });
                    }*/
                    $(ad).find('.js-geshopImg-lazyload').each(function () {
                        $(this).attr('src', $(this).data('original'));
                    });

                    view.append($(ad));
                    $elem.find('.giftImg').hide();
                    pageFlag = true;
                    window.GLOBAL && window.GLOBAL.currency && window.GLOBAL.currency.change_html();
                    window.FUN && window.FUN.currency && window.FUN.currency.change_html();

                });
            }


            // 渲染导航数据
            function initNav(navlist) {
                $elem.find('.geshop-ad-wrap').find('ul').html('');
                $elem.find('.geshop-navswiper .swiper-wrapper').html('');
                $elem.find('.secdBox').html('');
                navlist.forEach(function (item, index) {
                    var className = '', navClass = '';
                    if (index == 0) {
                        className = 'on';
                        navClass = 'active';
                    }
                    var topBanner = '';
                    if (item.banner) {
                        topBanner = '<li class="' + className + '"><img src=" ' + item.banner + ' " alt="banner"></li>';
                    } else {
                        topBanner = '<li class="' + className + '"></li>';
                    }
                    $elem.find('.geshop-ad-wrap').find('ul').append($(topBanner));

                    var firstNav = '<div class="swiper-slide ' + navClass + '"><span>' + item.navName + '</span></div>';
                    $elem.find('.geshop-navswiper').find('.swiper-wrapper').append($(firstNav));
                    var secdNavBox = '<div class="swiper-container geshop-secondswiper geshop-secondswiper-' + index + ' ' + className + '"><div class="swiper-wrapper"></div></div>';
                    $elem.find('.secdBox').append($(secdNavBox));
                    item.children.forEach(function (citem, cindex) {
                        var cClass = '';
                        var imgs = '';
                        if (citem.sku_img) {
                            imgs = '<img class="nav-img" src="' + citem.sku_img + '"/>'
                        } else {
                            if (citem.secondTabImg) {
                                imgs = '<img class="nav-img" src="' + citem.secondTabImg + '"/>'
                            }
                        }
                        if (cindex == 0) {
                            cClass = 'active'
                        }
                        var secdli = '<div class="swiper-slide ' + cClass + '" style="background-color: ' + citem.tabBgcolor + '">' + imgs + '<span>' + citem.navName + '</span><i class="btr" style="background-color: ' + citem.tabBgcolor + '"></i></div>';
                        $elem.find('.geshop-secondswiper-' + index).find('.swiper-wrapper').append($(secdli));
                    });

                });
            }

            if (typeof navList != 'undefined') {
                if (navList == null || navList.length == 0 || navList == '') {
                    $elem.find('.js-geshopImg-lazyload').each(function () {
                        $(this).attr('src', $(this).data('original'));
                    });
                } else {

                    // 导航加载
                    initNav(navList);
                    centerSecd();
                }
            }

            var navH = $elem.find('.secdBox').height() + document.body.clientWidth * 10 / 375;//导航
            function centerSecd() {
                // 导航小于屏幕宽度时居中
                var $firSlide = $elem.find('.geshop-navswiper').find('.swiper-slide').last();
                var $secdSlide = $elem.find('.geshop-secondswiper.on').find('.swiper-slide').last();

                if ($firSlide.position().left + $firSlide.width() < document.body.clientWidth) {
                    $elem.find('.geshop-navswiper .swiper-wrapper').addClass('wrap-center');
                } else {
                    $elem.find('.geshop-navswiper .swiper-wrapper').removeClass('wrap-center');
                }

                if ($secdSlide.position().left + $secdSlide.width() < document.body.clientWidth) {
                    $elem.find('.geshop-secondswiper.on .swiper-wrapper').addClass('wrap-center');
                } else {
                    $elem.find('.geshop-secondswiper.on .swiper-wrapper').removeClass('wrap-center');
                }
            }

            // 头部导航
            var mySwiper_item = new Swiper3($elem.find('.geshop-navswiper'), {
                freeMode: true,
                freeModeMomentumRatio: 0.5,
                slidesPerView: 'auto',
            });

            // 一级导航点击居中
            swiperCenter(mySwiper_item, changeNavPic);

            changeList();
            // 初始加载数据 [0][0] 一级导航0，二级导航0

            // 点击一级导航时切换二级导航
            mySwiper_item.on('tap', function (swiper) {

                pageFlag = false;
                var item = swiper.clickedIndex;
                $elem.find('.geshop-secondswiper').eq(item).addClass('on').siblings().removeClass('on');
                $elem.find('.geshop-secondswiper').eq(item).find('.swiper-slide').eq(0).addClass('active').siblings().removeClass('active');

                // 数据列表页切换
                if (typeof item != 'undefined') {
                    // 初始化 二级导航
                    initSecdSwiper(item);
                    snavIndex = 0;
                    changeList();
                }
                // $elem.find('.second-nav-list').empty();
            });
            initSecdSwiper(0);

            //监听滚动

            $(window).scroll(function () {
                navTop = Math.floor($elem.find('.secdBox').offset().top);
                var scrollH = $elem.height();
                var scrollTop = $(this).scrollTop();
                var winH = $(this).height();
                if (fixed == 1) {
                    // 当前滚动高度
                    if (scrollTop >= navTop && scrollTop < navTop + scrollH) {
                        $elem.find('.secdBox').css({ 'paddingBottom': navH + 'px' });
                        $elem.find('.geshop-secondswiper.on,.geshop-containr').addClass('fixed');

                        // 站点导航栏处理
                        if ($('.js-geshop-nav').length) {
                            $('.js-geshop-nav').hide();
                        }

                        $elem.addClass('js-geshop-nav-fixed');

                        // 页面中存在水平导航时，隐藏水平导航
                        if ($('div[data-key="U000030"]').length) {
                            $('div[data-key="U000030"]').find('nav').hide();
                        }

                    } else {
                        $elem.find('.geshop-secondswiper.on,.geshop-containr').removeClass('fixed')
                        $elem.find('.secdBox').css({ 'paddingBottom': '0' });

                        $elem.removeClass('js-geshop-nav-fixed');

                        // 站点导航栏处理
                        if ($('.js-geshop-nav').length) {
                            $('.js-geshop-nav').show();
                        }

                        // 页面中存在水平导航时，隐藏水平导航
                        if ($('div[data-key="U000030"]').length) {
                            $('div[data-key="U000030"]').find('nav').show();
                        }

                        if (GEShopSiteCommon) {
                            GEShopSiteCommon.jsNavFixed();
                        }

                    }
                }
                $elem.find('.list-' + fnavIndex + '-' + snavIndex).attr('data-top', scrollTop);


                if (scrollTop + winH >= scrollH) {
                    if (scrollTop != 0) {
                        if (pageFlag) {
                            pageFlag = false;
                            $elem.find('.list-' + fnavIndex + '-' + snavIndex).attr('data-curPage', curPage);
                            curPage++;
                            getGoodsList(fnavIndex, snavIndex);
                        }
                    }
                }
            });

        });


    })
  });


