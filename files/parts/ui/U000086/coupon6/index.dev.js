$(function() {

    // 获取优惠券的ID，和默认的组件
    var coupon_ids = [];
    // 优惠券ID，组件ID对照
    var compoentMap = [];
    // 默认值的组件
    var coupon_defalut = [];


    var staticDomain = $('[data-static-domain]:eq(0)').attr('data-static-domain') ? $('[data-static-domain]:eq(0)').attr('data-static-domain') : "";
    $LAB.script(staticDomain + "/resources/javascripts/library/gs_laytpl.js?2018100101").wait(function () {
        // 初始化配置
        gs_laytpl.config({ open: '<%', close: '%>' });

        /// 获取值
        $('div[data-key="U000086"]').each(function(index, elem){
            var component = $(elem);
            var component_id = component.attr('data-id');
            var coupon_id = component.attr('data-coupon-id') || 0;
            if (coupon_id) {
                coupon_ids.push(coupon_id)
                // 增加对照
                compoentMap.push({
                    componentID: component_id,
                    coupon_id: coupon_id
                })
            } else {
                coupon_defalut.push(component_id)
            }
        });

        // 批量获取
        var coupunParam = {
            lang: GESHOP_LANG || 'en',
            couponid: coupon_ids.join(','),
            pipeline: (typeof GESHOP_PIPELINE != 'undefined' ? GESHOP_PIPELINE : '')
        }
        $.ajax({
            url: GESHOP_INTERFACE.couponlist.url,
            data: { content : JSON.stringify(coupunParam) },
            dataType: 'jsonp',
            jsonpCallback: 'geshop_callback_086_coupon6_list',
            success: function(res) {
                try {
                    res.data.couponInfo && res.data.couponInfo.map(function(item, index) {
                        gsInitCouponById(item, index);
                    })
                } catch (e) {
                    console.log(e);
                }
            }
        });

        // 处理默认值的
        coupon_defalut && coupon_defalut.map(function(componentID){
            gsInitCouponById({
                default: true,
                componentID: componentID,
                offerAmount: 10,
                type: 1,
                coupon_code: 'ADBVFDFFF',
                resource: 0,
                leftCount: 100,
                maxCount: 100,
            });
        })
    });


    function gsInitCouponById(params, index) {
        var componentID = params.componentID
        for(var i=0; i <= compoentMap.length; i++) {
            if (i == index) {
                componentID = compoentMap[i].componentID
            }
        }
        var coupon_id = params.id || ''
        var template = $('#js_gs_coupon_template_' + componentID).html()
        var view = $('#js_gs_coupon_view_' + componentID)
        var percentAllCategoryLink = $('[data-id="' + componentID + '"]').attr('data-percent-link')

        // 渲染步骤
        function gsCounponRender(data) {
            // 分类名调整
            data.category = '<a href="' + data.category_url + '">' + data.category + '</a>';
            // 计算百分比
            data.percent = gsCalcPercent(data.leftCount, data.maxCount)
            // 计算日期
            data.validDays = gsCalcDays(data.enableStartTime, data.enableEndTime)
            // 满减或者0门槛直减的文案兼容处理，16种情况(4x4)
            data.offerAmountString = gsCalcSign(data.type, data.offerAmount)
            data.thresholdAmountString = gsCalcSign(2, data.thresholdAmount)

            if (data.receivedCount >= 1) {
                // 更改右侧信息
                var use_link = ''
                if (data.resource == 0) {
                    use_link = data.type == 1 ? percentAllCategoryLink : data.site_url;
                }
                if (data.resource == 1) {
                    use_link = data.category_url
                }
                if (data.resource == 2) {
                    use_link = data.product_link
                }
                data.use_link = use_link
            }

            // 渲染
            var html = gs_laytpl(template).render(data)
            view.html(html);
            // 货币切换
            window.GLOBAL && window.GLOBAL.currency && window.GLOBAL.currency.change_html();
            window.FUN && window.FUN.currency && window.FUN.currency.change_html();
            // 初始化倒计时
            gsCounponCountdownInit(data, view.find('.geshop-coupon-style4'), componentID);
            // banding Events
            gsCounponBindEvents(view, data);
            // 环形进度条
            gsCouponProgress(view, data.percent);
            //
            // gsCounponDialogShow({
            //     componentID: componentID,
            //     type: 1,
            //     offerAmount: 10,
            //     coupon_url: ''
            // })
        }

        // 初始化倒计时
        function gsCounponCountdownInit(data, dom, componentID) {
            function setCountdownClass(type) {
                var countdownClassMap = ['is-countReady', 'is-countStart', 'is-countEnd']
                // 样式替换
                for( var i = 0; i< countdownClassMap.length; i++) {
                    i === type ? dom.addClass(countdownClassMap[i]) : dom.removeClass(countdownClassMap[i])
                }
            }
            function countdown(startTime, endTime, dom) {
                var timeid = setInterval(function() {
                    var now = (new Date().getTime())/1000;
                    var countType = now < parseInt(startTime) ? 0 : now < parseInt(endTime) ? 1 : 2
                    setCountdownClass(countType)
                    var timeming = countType === 0 ? parseInt(startTime) : parseInt(endTime)
                    if (countType < 2) {
                        var second = (timeming - now)
                        var timeString = formatSeconds(second)
                        dom.find('.countdown-number-hour').html(timeString[0])
                        dom.find('.countdown-number-minute').html(timeString[1])
                        dom.find('.countdown-number-second').html(timeString[2])
                        if (countType === 1 && second === 0) {
                            dom.find('.countdown-number-hour').html('00')
                            dom.find('.countdown-number-minute').html('00')
                            dom.find('.countdown-number-second').html('00')
                            return clearInterval(timeid)
                        }
                    } else {
                        dom.find('.countdown-number-hour').html('00')
                        dom.find('.countdown-number-minute').html('00')
                        dom.find('.countdown-number-second').html('00')
                        return clearInterval(timeid)
                    }
                }, 1000)
            }
            function formatSeconds(value) {
                var secondTime = parseInt(value);
                var minuteTime = 0;
                var hourTime = 0;
                if(secondTime > 60) {
                    minuteTime = parseInt(secondTime / 60);
                    secondTime = parseInt(secondTime % 60);
                    if(minuteTime > 60) {
                        hourTime = parseInt(minuteTime / 60);
                        minuteTime = parseInt(minuteTime % 60);
                    }
                }
                if (hourTime < 10 ) hourTime = '0' + hourTime
                if (minuteTime < 10 ) minuteTime = '0' + minuteTime
                if (secondTime < 10 ) secondTime = '0' + secondTime
                return [hourTime, minuteTime, secondTime];
            }
            // 判断是否开启倒计时
            if (data.limited == 1) {
                countdown(data.getStartTime, data.getEndTime, dom);
            } else {
                setCountdownClass(1);
            }
        }

        // 初始化弹窗
        function gsCounponDialogShow(params) {
            var template = $('#js_gs_coupon_dialog_template_' + params.componentID).html()
            var view = $('#js_gs_coupon_dialog_view_' + params.componentID)
            var data = JSON.parse(JSON.stringify(params))
            data.sign = data.type == 1 ? data.offerAmount + '%' : '<span class="js-currency" data-currency="price">$price</span>'.replace('price', data.offerAmount).replace('price', data.offerAmount)
            // render
            var res = gs_laytpl(template).render(data)
            view.html(res)
            // 货币切换
            window.GLOBAL && window.GLOBAL.currency && window.GLOBAL.currency.change_html();
            window.FUN && window.FUN.currency && window.FUN.currency.change_html();
            // 关闭
            view.find('.gc4d1-close').click(function() {
                var id = $(this).attr('data-id')
                $('#js_gs_coupon_dialog_view_'+id).html('')
            })
        }

        // 警告弹窗
        function gsCounponDialog2Show(componentID) {
            var template = $('#js_gs_coupon_dialog2_template_' + componentID).html()
            var view = $('#js_gs_coupon_dialog2_view_' + componentID)
            // render
            var res = gs_laytpl(template).render({})
            view.html(res)
            view.find('button').click(function() {
                view.html('')
            })
        }

        // 计算剩余百分比
        function gsCalcPercent(left, max) {
            if (left <= -1) return 0
            if (max == 0) return 100
            if (left > max) return 100
            return parseInt((left / max) * 100)
        }

        // 计算有效日期
        function gsCalcDays(t1, t2) {
            var second = t2 - t1
            var oneDaySecond = 60 * 60 * 24
            return second / oneDaySecond
        }

        // 计算货币符号或者百分号
        function gsCalcSign(type, price) {
            var template = '<span class="js-currency" data-currency="price">$price</span>';
            return type == 1 ? price + '%' : template.replace('price', price).replace('price', price)
        }

        function gsCouponProgress(view, percent) {
            var dom = view.find('.geshop-js-circle')
            var left = -135;
            var right = -135;
            if (percent < 50 ) {
                var reg = ((percent / 50) * 180)
                left = left + reg
            } else {
                var reg = ((percent - 50) / 50) * 180
                left = 45
                right = right + reg
            }
            dom.find('.rightcircle').css('transform', 'rotate(' + left + 'deg)');
            dom.find('.leftcircle').css('transform', 'rotate(' + right + 'deg)');
        }

        // 绑定事件
        function gsCounponBindEvents(elem, data) {
            var coupunParam = {
                lang: GESHOP_LANG || 'en',
                couponid: data.id,
                pipeline: (typeof GESHOP_PIPELINE != 'undefined' ? GESHOP_PIPELINE : '')
            }
            // getCoupon
            elem.on('click', '[data-event="getCoupon"]', function() {
                $.ajax({
                    // url: 'http://www.pc-rosegal.com.geshop.php5.egomsl.com/geshop/goods/getcoupon',
                    url: GESHOP_INTERFACE.getcoupon.url,
                    data: { content : JSON.stringify(coupunParam) },
                    dataType: 'jsonp',
                    jsonp: 'callback',
                    success :function(res) {
                        if (res.code === 0) {
                            // 弹窗
                            gsCounponDialogShow({
                                componentID: componentID,
                                offerAmount: data.offerAmount,
                                type: data.type,
                                coupon_url: res.data.couponurl
                            });
                            // 更改右侧信息
                            var dom = elem.find('.geshop-coupon-style4')
                            dom.addClass('is-get')
                            dom.find('.gc4-coupon-code').html(res.data.coupon_code)
                            dom.find('.gc4-get-message a').attr('href', res.data.couponurl)
                            var use_link = ''
                            if (data.resource == 0) {
                                use_link = data.type == 1 ? percentAllCategoryLink : res.data.site_url;
                            }
                            if (data.resource == 1) {
                                use_link = data.category_url
                            }
                            if (data.resource == 2) {
                                use_link = data.product_link
                            }
                            dom.find('.gc4-get-btn[data-type="canUse"]').attr('data-url', use_link)
                        } else if (res.code === 1) {
                            // {# 获取优惠券未登陆状态 跳转登录页面 #}
                            window.location.href = res.data.loginurl + window.location.href;
                        } else if (res.code == 5) {
                            gsCounponDialog2Show(componentID)
                        } else {
                            // {# 获取优惠券信息错误 #}
                            GEShopSiteCommon.dialog.message(res.message);
                        }
                    }
                });
            });
            // use coupon
            elem.on('click', '[data-event="useCoupon"]', function() {
                var url = $(this).attr('data-url')
                if (url) {
                    window.location.href = url
                }
            });
        }

        // 执行渲染
        gsCounponRender(params);
    }
});
