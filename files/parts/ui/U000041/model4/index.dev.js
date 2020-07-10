;
$(function () {
    $('.geshop-U000041-model4').each(function (item, elem) {
        let staticDomain = GESHOP_STATIC;
        loadCss(staticDomain + '/resources/javascripts/library/swiper/swiper.min.css');
        $LAB.script(staticDomain + '/resources/javascripts/library/swiper/swiper.3.4.spec.min.js')
            .script(staticDomain + '/resources/javascripts/library/gs_laytpl.js').wait(function () {
                gs_laytpl.config({
                    open: '<%',
                    close: '%>'
                });
                let $elem = $(elem);
                // 一级导航下标s
                let fnavIndex = 0;
                // 二级导航下标
                let snavIndex = 0;

                let fixed = $elem.find('.geshop-containr').data('fixed');
                // 导航结构数据
                // var navList = JSON.parse($elem.find('.geshop-containr').attr('data-navList'));
                // sku数据
                // var goodsSKU = JSON.parse($elem.find('.geshop-containr').attr('data-goodssku'));
                let dataId = $elem.data('id');
                let navList = window['navList_' + dataId];
                let goodsSKU = window['goodsSku_' + dataId];

                let mySwiper_item;

                // console.log(navList);
                let navLists = [{
                    navName: 'Little Black Dresses1',
                    banner: 'https://geshopimg.logsss.com/uploads/G6yp2z1vAX8SCFVEIbQx93Tkeon7f0jK.png',
                    children: [{
                        navName: 'Little Black1_1',
                        secondTabImg: 'https://geshopimg.logsss.com/uploads/4NTvJ3E9HbDxGShyOcrkQ7u1dqlaZw5i.png',
                        goodsSKU: '238996404,236354404,222579001,240623604',
                        tabBgcolor: '#4d034d'
                    },
                    {
                        navName: 'Little Black1_2',
                        secondTabImg: '',
                        goodsSKU: '221928001,224252201,224648005',
                        tabBgcolor: '#4d034d'
                    }
                    ]
                },
                {
                    navName: 'Little Black Dresses2',
                    banner: 'https://geshopimg.logsss.com/uploads/G6yp2z1vAX8SCFVEIbQx93Tkeon7f0jK.png',
                    children: [{
                        navName: 'Little Black2_1',
                        secondTabImg: '',
                        goodsSKU: '218833501,223117701,210229302',
                        tabBgcolor: '#4d034d'
                    },
                    {
                        navName: 'Little Black2_1',
                        secondTabImg: '',
                        goodsSKU: '160084301,151318207,151327501',
                        tabBgcolor: '#4d034d'
                    }
                    ]
                },
                {
                    navName: 'Little Black Dresses3',
                    banner: 'https://geshopimg.logsss.com/uploads/G6yp2z1vAX8SCFVEIbQx93Tkeon7f0jK.png',
                    children: [{
                        navName: 'Little Black3_1',
                        secondTabImg: '',
                        goodsSKU: '238996404,236354404,222579001,221928001,224252201,224648005,218833501,223117701',
                        tabBgcolor: '#4d034d'
                    },
                    {
                        navName: 'Little Black3_2',
                        secondTabImg: '',
                        goodsSKU: '224648005,218833501,223117701,210229302,160084301',
                        tabBgcolor: '#4d034d'
                    }
                    ]
                },
                {
                    navName: 'Little Black Dresses4',
                    banner: 'https://geshopimg.logsss.com/uploads/G6yp2z1vAX8SCFVEIbQx93Tkeon7f0jK.png',
                    children: [{
                        navName: 'Little Black4_1',
                        secondTabImg: '',
                        goodsSKU: '236354404,222579001,221928001,224252201,224648005',
                        tabBgcolor: '#4d034d'
                    },
                    {
                        navName: 'Little Black4_2',
                        secondTabImg: '',
                        goodsSKU: '160084301,151318207,151327501',
                        tabBgcolor: '#4d034d'
                    }
                    ]
                }
                ];

                // 导航点击居中
                function swiperCenter (mySwiper, callback) {
                    let clientW = document.body.clientWidth;
                    // geshop-navswiper 显示宽度
                    let swiperWidth = mySwiper.container[0].clientWidth;
                    // 最大偏移
                    let maxTranslate = mySwiper.maxTranslate();
                    // geshop-navswiper

                    let maxWidth = -maxTranslate + swiperWidth / 2;
                    mySwiper.on('click', function (swiper, e) {
                        let slide = swiper.slides[swiper.clickedIndex];
                        if (typeof slide !== 'undefined') {
                            $(slide).addClass('active').siblings().removeClass('active');
                            //  顶部图片切换
                            if (typeof callback == 'function') {
                                callback(swiper.clickedIndex, $elem);
                            }
                        }
                    });
                    calcCenter();
                    mySwiper.setWrapperTranslate(0);
                }

                /* 判断一级导航二级导航是否居中 */
                function calcCenter () {
                    let firstSwiperWidth = 0;
                    // $elem.find('.swiper-container.on .swiper-wrapper').removeClass('wrap-center');
                    $elem.find('.geshop-navswiper').find('.swiper-slide').each(function () {
                        firstSwiperWidth += $(this).outerWidth(true);
                    });

                    // 遍历所有的二级导航计算宽度是否居中
                    $elem.find('.secdBox .swiper-container.on').each(function () {
                        let secondSwiperWidth = 0;
                        $(this).find('.swiper-slide').each(function () {
                            secondSwiperWidth += $(this).outerWidth(true);
                        });

                        if (secondSwiperWidth < 1200) {
                            $(this).find('.swiper-wrapper').addClass('wrap-center');
                        } else {
                            $(this).find('.swiper-wrapper').removeClass('wrap-center');
                        }
                    });

                    if (firstSwiperWidth < 1200) {
                        $elem.find('.geshop-navswiper .swiper-wrapper').addClass('wrap-center');
                    } else {
                        $elem.find('.geshop-navswiper .swiper-wrapper').removeClass('wrap-center');
                    }
                }
                //  顶部图片切换
                function changeNavPic (index) {
                    $elem.find('.geshop-ad-wrap ul li').eq(index).addClass('on').siblings().removeClass('on');
                    $elem.find('.geshop-ad-wrap ul li').hide();
                    $elem.find('.geshop-ad-wrap ul li').eq(index).show();
                }

                // 初始化二级导航
                function initSecdSwiper (item) {
                    let secdSwiper = new Swiper3($elem.find('.geshop-secondswiper-' + item), {
                        freeMode: true,
                        slidesPerView: 'auto',
                        slidesPerGroup: 6,
                        freeModeMomentumRatio: 0.5,
                        prevButton: '.swiper-button-prev',
                        nextButton: '.swiper-button-next',
                        touchMoveStopPropagation: true,
                        onClick: function (swiper) {
                            $(swiper.wrapper).parent().removeAttr('fixed');
                        }
                    });
                    // 二级导点击航居中
                    swiperCenter(secdSwiper, changeList);
                }

                // 数据列表页切换
                function changeList (item) {
                    if (typeof navList != 'undefined' && navList) {
                        snavIndex = $elem.find('.geshop-secondswiper-' + fnavIndex + ' .swiper-slide.active').index();
                        getGoodsList(fnavIndex, snavIndex);
                    }
                }

                function getGoodsList (index1, index2) {
                    let goodsSn;
                    // 根据选品方式获取SKU
                    for (let i = 0; i < goodsSKU.length; i++) {
                        if (goodsSKU[i].key == index1 + '-' + index2 && goodsSKU[i].skuFrom == 2) {
                            goodsSn = goodsSKU[i].ipsGoodsSKU;
                        } else if (goodsSKU[i].key == index1 + '-' + index2 && goodsSKU[i].skuFrom == 1) {
                            goodsSn = goodsSKU[i].inputSku;
                        }
                    }

                    let params = {
                        lang: GESHOP_LANG || 'en',
                        goodsSn: goodsSn,
                        filterStock: 1
                    };
                    let url = GESHOP_INTERFACE.goods_async_detail.url;
                    // url: 'http://www.pc-rosegal.com.master.php5.egomsl.com/geshop/goods/getdetail',
                    window.GEShopCommonFn_Vue.$jsonp(url,params,{target:$elem}).done(function(res){
                        try {
                            $elem.find('.second-nav-list').empty();
                            initHtml(res.data.goodsInfo);
                        } catch (e) {
                            // console.log(e);
                        }
                    });
                }

                // 渲染商品
                function initHtml (data) {
                    let getTpl = $elem.find('.cont-goodsList').html(),
                        view = $elem.find('.second-nav-list');
                    gs_laytpl(getTpl).render(data, function (html) {
                        view.html(html);
                        if (window.GESHOP_PAGE_TYPE != 1) {
                            GS_GOODS_LAZY_FN($elem.find('.js-geshopImg-lazyload'));
                        } else {
                            $elem.find('.js-geshopImg-lazyload').each(function () {
                                $(this).attr('src', $(this).data('original'));
                            });
                        }
                        window.GLOBAL && window.GLOBAL.currency && window.GLOBAL.currency.change_html();
                        window.FUN && window.FUN.currency && window.FUN.currency.change_html();
                    });
                }

                // 渲染导航数据
                function initNav (navlist) {
                    $elem.find('.geshop-ad-wrap').find('ul').html('');
                    $elem.find('.geshop-navswiper .swiper-wrapper').html('');
                    $elem.find('.secdBox').html('');
                    navlist.forEach(function (item, index) {
                        let className = '',
                            navClass = '';
                        if (index == 0) {
                            className = 'on';
                            navClass = 'active';
                        }
                        if (item.banner) {
                            var topBanner = '<li class="' + className + '"><img src=" ' + item.banner + ' " alt="banner"></li>';
                        }

                        $elem.find('.geshop-ad-wrap').find('ul').append($(topBanner));

                        let firstNav = '<div class="swiper-slide ' + navClass + '"><span>' + item.navName + '</span></div>';
                        $elem.find('.geshop-navswiper').find('.swiper-wrapper').append($(firstNav));
                        let secdNavBox = '<div class="swiper-container geshop-secondswiper geshop-secondswiper-' + index + ' ' + className + '">' +
            '<div class="swiper-wrapper">' +
            '</div>' +
            '<div class="outfit-prev swiper-button-prev j-tags-prev"><i class="icon-left"></i></div>' +
            '<div class="outfit-next swiper-button-next j-tags-next"><i class="icon-right"></i></div>' +
            '</div>';
                        $elem.find('.secdBox').append($(secdNavBox));
                        item.children.forEach(function (citem, cindex) {
                            let cClass = '';
                            let imgs = '';
                            if (citem.sku_img) {
                                imgs = '<img class="nav-img" src="' + citem.sku_img + '"/>';
                            } else {
                                if (citem.secondTabImg) {
                                    imgs = '<img class="nav-img" src="' + citem.secondTabImg + '"/>';
                                } else {
                                    imgs = '<img class="nav-img" src="https://geshopimg.logsss.com/uploads/4NTvJ3E9HbDxGShyOcrkQ7u1dqlaZw5i.png "/>';
                                }
                            }

                            if (cindex == 0) {
                                cClass = 'active';
                            }
                            let secdli = '<div class="swiper-slide ' + cClass + '" style="background-color: ' + citem.tabBgcolor + '">' + imgs + '<span>' + citem.navName + '</span><i class="btr" style="background-color: ' + citem.tabBgcolor + '"></i></div>';
                            $elem.find('.geshop-secondswiper-' + index).find('.swiper-wrapper').append($(secdli));
                        });
                        calcCenter();
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

                        // 导航小于屏幕宽度时居中
                        let $firSlide = $elem.find('.geshop-navswiper').find('.swiper-slide').last();
                        let $secdSlide = $elem.find('.geshop-secondswiper.on').find('.swiper-slide').last();

                        calcCenter();
                        // if ($secdSlide.position().left + $secdSlide.width() < document.body.clientWidth) {
                        //   $elem.find('.geshop-secondswiper.on .swiper-wrapper').addClass('wrap-center');
                        // } else {
                        //   $elem.find('.geshop-secondswiper.on .swiper-wrapper').removeClass('wrap-center');
                        // }
                    }
                }

                // 头部导航
                mySwiper_item = new Swiper3($elem.find('.geshop-navswiper'), {
                    freeMode: true,
                    freeModeMomentumRatio: 0.5,
                    slidesPerView: 'auto',
                    prevButton: '.swiper-button-prev',
                    nextButton: '.swiper-button-next',
                    touchMoveStopPropagation: true,
                    onlyExternal: true
                });

                // 一级导航点击居中
                swiperCenter(mySwiper_item, changeNavPic);

                // 初始加载数据 [0][0] 一级导航0，二级导航0
                changeList();

                // 点击一级导航时切换二级导航
                mySwiper_item.on('tap', function (swiper) {
                    let item = swiper.clickedIndex;
                    fnavIndex = item;
                    $elem.find('.geshop-secondswiper').eq(item).addClass('on').siblings().removeClass('on');
                    $elem.find('.geshop-secondswiper').eq(item).find('.swiper-slide').eq(0).addClass('active').siblings().removeClass('active');
                    // 初始化 二级导航
                    initSecdSwiper(item);
                    // 数据列表页切换
                    if (typeof item != 'undefined') {
                        snavIndex = 0;
                        changeList();
                    }
                });
                initSecdSwiper(0);

                function initSwiper () {
                    let initSwiper = new Swiper3($elem.find('.geshop-secondswiper.on'), {
                        freeMode: true,
                        slidesPerView: 'auto',
                        slidesPerGroup: 6,
                        freeModeMomentumRatio: 0.5,
                        prevButton: '.swiper-button-prev',
                        nextButton: '.swiper-button-next',
                        touchMoveStopPropagation: true
                    });
                    initSwiper.setWrapperTranslate(0);
                }

                // 监听滚动

                $(window).scroll(function () {
                    if (fixed == 1) {
                        let navTop = $elem.find('.secdBox').offset().top; // 导航的原始位置
                        let navH = $elem.find('.secdBox').height(); // 导航的原始位置
                        // var fixTop;
                        // // 如里站点也有悬浮导航则要悬浮在下面
                        // if($("#js_topFixWrap").length > 0 && $("#js_topFixWrap").find('.fix-wrap-nav').length > 0  ){
                        //   fixTop = $("#js_topFixWrap").find('.fix-wrap-nav').height()
                        // }else{
                        //   fixTop = 0;
                        // }
                        setTimeout(function () {
                            // 当前滚动高度
                            let scrollTop = $(this).scrollTop();
                            // console.log('****',scrollTop,navTop);
                            if (navTop <= scrollTop && scrollTop < navTop + $elem.find('.geshop-containr').height()) {
                                // $elem.find('.secdBox').height(navH);
                                $elem.find('.geshop-secondswiper.on').addClass('fixed');
                                $elem.find('.geshop-secondswiper.on').attr('fixed', 1);
                                // 站点导航栏处理
                                if ($('.js-geshop-nav').length) {
                                    $('.js-geshop-nav').hide();
                                }

                                // 页面中存在水平导航时，隐藏水平导航
                                if ($('div[data-key="U000027"]').length) {
                                    $('div[data-key="U000027"]').find('ul').hide();
                                }

                                $elem.addClass('js-geshop-nav-fixed');

                                /* 吸顶状态下计算是否居中 */
                                let fixedWidth = 0;
                                $elem.find('.geshop-secondswiper.on .swiper-wrapper').find('.swiper-slide').each(function () {
                                    fixedWidth += $(this).outerWidth();
                                });
                                if (fixedWidth < document.body.clientWidth) {
                                    $elem.find('.geshop-secondswiper.on .swiper-wrapper').addClass('wrap-center');
                                } else {
                                    $elem.find('.geshop-secondswiper.on .swiper-wrapper').removeClass('wrap-center');
                                }
                                initSwiper();
                            } else {
                                $elem.find('.geshop-secondswiper.on').removeClass('fixed');

                                // 站点导航栏处理
                                if ($('.js-geshop-nav').length) {
                                    $('.js-geshop-nav').show();
                                }

                                // 页面中存在水平导航时，隐藏水平导航
                                if ($('div[data-key="U000027"]').length) {
                                    $('div[data-key="U000027"]').find('ul').show();
                                }

                                $elem.removeClass('js-geshop-nav-fixed');

                                if (GEShopSiteCommon) {
                                    GEShopSiteCommon.jsNavFixed();
                                }

                                calcCenter();
                            }

                            if (!$elem.find('.geshop-secondswiper.on').hasClass('fixed') && $elem.find('.geshop-secondswiper.on').attr('fixed') == 1) {
                                initSwiper();
                            }
                        }, 10);
                    }
                });
            });
    });

    $(document).on('click', '.quick_view', function (e) {
        e.stopPropagation();
        let sites = GESHOP_SITECODE.split('-')[0];
        // var goods_sn = $(this).attr('data-goodsSn');
        let goods_id = $(this).attr('data-goodsId');
        let siteDomain = $(this).attr('data-siteDomain');
        let url = '';
        switch (sites) {
        case 'rw':
            goods_id = goods_id.slice(0, 7);
            url = siteDomain + '/m-goods_fast-a-info.htm?goods_id=' + goods_id;
            break;
        case 'zf':
        // success
            goods_id = goods_id.slice(0, 6);
            url = siteDomain + '/m-goods_fast-a-info.htm?goods_id=' + goods_id;
            break;
        case 'rg':
        // success
            goods_id = goods_id.slice(0, 7);
            url = siteDomain + '/m-goods-a-fast-id-' + goods_id + '.html';
            break;
        default:
            break;
        }
        /*
        RW:  https://www.rosewholesale.com/m-goods_fast-a-info.htm?goods_id=5507255&ids=
        ZF:  https://www.zaful.com/m-goods_fast-a-info.htm?goods_id=548472&ids=
        RG:  https://www.rosegal.com/m-goods-a-fast-id-1838468.html
        */
        if (url) {
            // console.log(url);
            GEShopSiteCommon.dialog.iframe(url, 1080, 597, true);
        }
        return false;
    });

    // 优惠信息滚动
    let scrollText;
    $(document).on('mouseover', '.shop-fast', function () {
        if ($(this).find('.rice-info p').length > 1) {
            let _this = this;
            scrollText = setInterval(AutoScroll(_this), 2000);
        }
    }).on('mouseleave', '.shop-fast', function () {
        clearInterval(scrollText);
    });
});

function AutoScroll (obj) {
    $(obj).find('.ri-list:first').animate({
        marginTop: '-40px'
    }, 1500, function () {
    // $(obj).find(".ri-list p").appendTo($(obj).find(".ri-list"));
        $(this).css({
            marginTop: '0px'
        }).find('p:first').appendTo(this);
    });
}
