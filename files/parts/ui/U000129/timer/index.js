(function(){
    if (!gsGlobal) {
        var gsGlobal = function (my) {
            var $gsCountTarget = $('.gs-count-down .js_gsTimer');
            my.initCountDown = function () {
                $gsCountTarget = $('.gs-count-down .js_gsTimer');
                $gsCountTarget.each(function () {
                    var $self = $(this),
                        leftTime = 0,
                        status = 0, // ['0未开始', '1已开始', '2已结束']
                        serverTime = new Date().getTime() || $self.prev('input[name=serverTime]').val(),
                        dataStartTime = $self.prev('input[name=serverTime]').attr('data-starttime'),
                        dataEndTime = $self.prev('input[name=serverTime]').attr('data-endtime'),
                        dataTextArr = ($self.prev('input[name=serverTime]').attr('data-textobj')).split(',');

                    serverTime = Math.round(serverTime / 1000);
                    dataStartTime = Math.round(dataStartTime / 1000);
                    dataEndTime = Math.round(dataEndTime / 1000);

                    if (dataStartTime > serverTime) {
                        leftTime = dataStartTime - serverTime;
                    } else if (dataStartTime <= serverTime && dataEndTime > serverTime) {
                        $self.closest('.js_getCoupon').removeClass('noTime');
                        status = 1;
                        leftTime = dataEndTime - serverTime;

                    } else if (dataEndTime < serverTime) {
                        status = 2;
                        leftTime = 0;
                        // 结束遮罩显示
                        if ($self.closest('.js_getCoupon').siblings('.gs-timer-shade').length) {
                            $self.closest('.js_getCoupon').siblings('.gs-timer-shade').show();
                        }
                    }
                    // leftTime = Math.round(leftTime / 1000)
                    $self.data({ 'leftTime': leftTime, 'status': status })
                    $self.prev().prev().text(dataTextArr[status])
                    $self.closest('.js_getCoupon').find('.gs-get-coupon').find('span').text(function() {return ($(this).attr('data-cgetext').split(','))[status]});
                    /* 服务器倒计时时间模拟 */
                    /*              if (serverTimeInterval) clearTimeout(serverTimeInterval)
                                    var serverTimeInterval = setInterval(function () {
                                        serverTime = serverTime + 1;
                                        $self.prev('input[name=serverTime]').val(serverTime * 1000)
                                    }, 1000) */
                })

            }
            /* 倒计时组件 */
            my.compoentCountDown = function () {
                var _this = this;
                $gsCountTarget.each(function () {
                    var $self = $(this)
                    var leftTime = parseInt($self.data('leftTime'))
                    var dataStatus = parseInt($self.data('status'))
                    var seconds, minutes, hours, days, CDay, CHour, CMinute, CSecond
                    if (!isNaN(leftTime) && leftTime >= 0) {
                        seconds = leftTime
                        minutes = Math.floor(seconds / 60)
                        hours = Math.floor(minutes / 60)
                        days = Math.floor(hours / 24)
                        CDay = days
                        CHour = hours % 24
                        CMinute = minutes % 60
                        CSecond = Math.floor(seconds % 60)

                        CDay = CDay < 10 ? '0' + CDay : CDay
                        CHour = CHour < 10 ? '0' + CHour : CHour
                        CMinute = CMinute < 10 ? '0' + CMinute : CMinute
                        CSecond = CSecond < 10 ? '0' + CSecond : CSecond

                        $self.data('left-time', leftTime - 1);
                        $self.html('<span>' + CDay + '</span><em>:</em><span>' + CHour + '</span><em>:</em><span>' + CMinute + '</span><em>:</em><span>' + CSecond + '</span>')
                    } else {
                        $gsCountTarget = $gsCountTarget.not($self)
                        // $self.html('<span><strong>00 DAY(S) </strong>00:00:00</span>')
                        if (dataStatus !== '2') {
                            gsGlobal.initCountDown();
                        } else {
                            $self.html('<span>00</span><em>:</em><span>00</span><em>:</em><span>00</span><em>:</em><span>00</span>')
                        }
                    }
                })
                if (0 !== $gsCountTarget.length) {
                    setTimeout(gsGlobal.compoentCountDown, 1000)
                }
            }

            return my
        }(gsGlobal || {})

        function timerCouponDetailAjax(couponId, obj, len) {
            $.ajax({
                url: GESHOP_INTERFACE.coupondetail.url,
                type: 'GET',
                dataType: 'jsonp',
                data: { 
                    "content": JSON.stringify({
                        "lang": GESHOP_LANG, 
                        "couponid": couponId,
                        pipeline: (typeof GESHOP_PIPELINE != 'undefined' ? GESHOP_PIPELINE : '')
                    })
                },
                success: function(res) {
                    if (res.code == 0) {
                        var startTime = res.data.couponInfo.getStartTime,
                            endTime = res.data.couponInfo.getEndTime,
                            coupon = res.data.couponInfo.id;
                        couponDateInit(obj, (startTime * 1000), (endTime * 1000), coupon, len);
                    } 
                }
            });
        }

        var timerEmitTag = 0;
        function timerCouponDetail() {
            var len = $('.js_timerEmitTag').length;
            $('.js_couponId').each(function() {
                var couponId = $(this).data('couponid');
                timerCouponDetailAjax(couponId, $(this), len);
            });
        }

        timerCouponDetail();

        function couponDateInit(obj, startTime, endTime, coupon, len) {
            timerEmitTag+=1;
            var serverTime = new Date().getTime(),
                status = 0;
            if (startTime <= serverTime && endTime > serverTime) {
                status = 1;
            } else if (endTime < serverTime) {
                status = 2;
            }

            obj.closest('.js_getCoupon').find('.js_dataInfo').attr({
                'data-starttime': startTime,
                'data-endtime': endTime,
                'data-startstatus': status
            });

            if (timerEmitTag == len) {
                gsGlobal.initCountDown();
                gsGlobal.compoentCountDown();
            }
        }
        
        $('.js_getCoupon').click(function() {
            var _this = $(this),
                couponId = _this.find('.js_couponId').data('couponid');
            // 没到时间有noTime, 到时间才可以点
            if (!_this.hasClass('noTime')) {            
                $.ajax({
                    url: GESHOP_INTERFACE.getcoupon.url,
                    type: 'GET',
                    dataType: 'jsonp',
                    data: {
                        content: JSON.stringify({
                            "lang": GESHOP_LANG, 
                            "couponid": couponId,
                            pipeline: (typeof GESHOP_PIPELINE != 'undefined' ? GESHOP_PIPELINE : '')
                        })
                    },
                })
                .done(function(res) {
                    if (res.code == 0) {
                        GEShopSiteCommon.dialog.message(GESHOP_LANGUAGES.coupon_success_tips);
                    } else if (res.code == 1) {
                        location.href = res.data.loginurl + decodeURIComponent(location.href);
                    } else {
                        GEShopSiteCommon.dialog.message(res.message)
                    }
                });
            }
        });
    }
}());