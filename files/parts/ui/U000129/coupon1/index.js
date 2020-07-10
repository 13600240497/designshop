(function(){
    $.extend($.fn,{
        U0001291 : function(){
            var $box = this;
            var $hideInput = $box.find('.hide-info-input');
            var _getAttr = function _getAttr(attr){
                return $hideInput.attr((attr || '').toLowerCase()) || '';
            }

            var _jsonp = function _jsonp(param){
                param = param || {};
                return $.ajax($.extend({
                    dataType: "jsonp",
                    jsonp: 'callback',
                },param));
            };
            // {# 倒计时距离开始文案 #}
            var startIn = _getAttr('startIn');
            // {# 倒计时距离结束文案 #}
            var endIn = _getAttr('endIn');
            // {# 倒计时未开始按钮文案 #}
            var startText = _getAttr('startText');
            // {# 倒计时进行中按钮文案 #}
            var getCouponText = _getAttr('getCouponText');
            var lang = _getAttr('lang');
            var serverTime = _getAttr('serverTime');
            var _diffTime = parseInt(serverTime) - (new Date()).getTime();
            var staticDomain = _getAttr('staticDomain');
            // var siteCode = _getAttr('siteCode').split('-')[0];
            var siteCode = GESHOP_SITECODE ? GESHOP_SITECODE.split('-')[0] : '';
            var couponId = _getAttr('couponId');
            var countDown = _getAttr('countDown');
            var successText = _getAttr('successText');

            //如果env是1的话那么就是测试环境
            var __dev = _getAttr('env') === '1';
            var fillZero = function fillZero(n){
                return n < 10 ? '0' + n : n ;
            };
            // {# 优惠券参数 #}
            var coupunParam = JSON.stringify({
                                lang : lang,
                                couponid : couponId,
                                pipeline: (typeof GESHOP_PIPELINE != 'undefined' ? GESHOP_PIPELINE : '')
                            });
            // {# 开始按钮结束按钮 #}
            var CountDown = function CountDown($node){
                this.status = 0;
                this.$node = $node;
                this.$timeLine = this.$node.find('.time-line');
                this.timer = null;
                this.diffTime = _diffTime;
                // {# 优惠券领取次数 需求是优惠券只能领取一次 #}
                this.receivedCount = 0;

                this.getCouponDetail();
                // {# 倒计时是否显示 1是显示 2是不显示 #}
                this.countDownFlag = parseInt(countDown) === 1;
                this.bindEvent();
                this.isClick = false;
                if(!couponId){
                    this.goEdit();
                }
            };
            $.extend(CountDown.prototype,{
                // {# 删除定时器盒子 #}
                removeCountDownBox : function(){
                    this.$node.find('.coupon-bottom').remove();
                },
                // {# 判断当前优惠券属于哪个状态 times 包含优惠券起始时间跟结束时间 #}
                judge : function(data){
                    this.receivedCount = data.receivedCount;

                    // RG和ZF站点各自取不同的开始结束时间
                    // RG： getStartTime getEndTime
                    // ZF： enableStartTime enableEndTime
                   if (siteCode == 'rg') {
                    this.startTime = parseInt(data.couponInfo.getStartTime) * 1000;
                    this.endTime = parseInt(data.couponInfo.getEndTime) * 1000;
                    } else {
                    this.startTime = parseInt(data.couponInfo.enableStartTime) * 1000;
                    this.endTime = parseInt(data.couponInfo.enableEndTime) * 1000;
                    }

                    var _curTime = this.getCurTime();
                    // {# 如果是结束时间设置成0的话 那么就是没有设置过期时间 那么就是一直可以领取 那么倒计时也显示不了了 #}
                    if(!this.endTime){
                        this.processing();
                        this.countDownFlag = false;
                        return this.removeCountDownBox();
                    }
                    // {# 如果不显示定时器的话就把定时器那个栏目给干掉 #}
                    !this.countDownFlag && this.removeCountDownBox();
                    if(_curTime >= this.endTime){
                        this.end();
                    }else if(_curTime < this.startTime){
                        this.notBegin();
                        this.run();
                    }else{
                        this.processing();
                        this.run();
                    }
                },
                // {# 事件绑定 #}
                bindEvent : function(){
                    //如果是测试环境就不需要绑定事件了
                    if(__dev)return;
                    var _this = this;
                    this.$node.on('click',function(){
                        // {# 没有开始的时候点击 #}
                        if(_this.status === 1){

                        }
                        // {# 进行中的时候点击 #}
                        if(_this.status === 2){
                            // {# 如果是进行中的时候都可以点击 因为多语言后台配置了 #}
                            // {# 正式上线之后的url是这个 GESHOP_INTERFACE.getcoupon.url #}
                            if(_this.isClick)return;
                            _this.isClick = true;
                            _jsonp({
                                url: GESHOP_INTERFACE.getcoupon.url,
                                data: {
                                    content : coupunParam,
                                },
                                success :function(res){
                                    _this.isClick = false;
                                    if(res.code === 0){
                                        GEShopSiteCommon.dialog.message(successText);
                                    }else if(res.code === 1){
                                        // {# 获取优惠券未登陆状态 跳转登录页面 #}
                                        window.location.href = res.data.loginurl + window.location.href;
                                    }else{
                                        // {# 获取优惠券信息错误 #}
                                        GEShopSiteCommon.dialog.message(res.message);
                                    }
                                },
                                error : function(){
                                    _this.isClick = false;
                                }
                            });
                        }
                        // {# 已结束的时候点击 #}
                        if(_this.status === 3){

                        }
                    })
                },
                // {# 获取优惠券详情 #}
                getCouponDetail : function(){
                    if(!couponId)return;
                    // {# GESHOP_INTERFACE.coupondetail 这个是正式版本的优惠券接口url地址 #}
                    // var _url = 'http://www.pc-zaful.com.v1011.php5.egomsl.com/geshop/goods/coupondetail';
                    var _url = GESHOP_INTERFACE.coupondetail.url;
                    var _this = this;
                    _jsonp({
                        url: _url,
                        data: {
                            content : coupunParam
                        },
                        success :function(res){
                            if(res.code === 0){
                                res.data.couponInfo = res.data.couponInfo || {};
                                _this.judge(res.data);
                            }else{
                                //{# 获取优惠券信息错误 #}
                            }
                        }
                    });
                },
                // {# 获取时间 返回当前时间的时间撮  time 时间 #}
                getTime : function(){
                    return (new Date()).getTime();
                },
                //{# 获取正确的时间  本地时间 + 服务器与本地的时间差 #}
                getCurTime : function(){
                    // return this.getTime() + this.diffTime;
                    return this.getTime();
                },
                //{# 未开始 #}
                notBegin : function(){
                    if(this.status !== 1){
                        this.status = 1;
                        this.$node.find('.coupon-text').html(startIn);
                        this.$node.find('.coupon-btn').html(startText);
                    }
                    var diffTime = parseInt(this.getCurTime() / 1000 - this.startTime / 1000);
                    if(diffTime >= 0){
                        this.processing();
                    }else{
                        this.runCountDown(Math.abs(diffTime));
                    }
                },
                //{# 进行中 #}
                processing : function(){
                    if(this.status !== 2){
                        this.status = 2;
                        this.$node.find('.coupon-text').html(endIn);
                        this.$node.find('.coupon-btn').html(getCouponText);
                    }

                    var diffTime = parseInt(this.endTime / 1000 - this.getCurTime() / 1000);
                    if(diffTime <= 0){
                        this.end();
                    }else{
                        this.runCountDown(diffTime);
                    }
                },
                //{# 已结束 #}
                end: function(){
                    this.status = 3;
                    this.clearTimer();
                    this.$node.find('.coupon-text').html(endIn);
                    this.$node.find('.coupon-btn').html(getCouponText);
                    this.$node.find('.over-box').show();
                    this.setCountDown(0,0,0,0);
                },
                setCountDown : function(_d,_h,_m,_s){
                    if(!this.countDownFlag)return;
                    this.$timeLine.eq(0).html(fillZero(_d));
                    this.$timeLine.eq(1).html(fillZero(_h));
                    this.$timeLine.eq(2).html(fillZero(_m));
                    this.$timeLine.eq(3).html(fillZero(_s));
                },
                //{# 倒计时 #}
                runCountDown: function(time){
                    time = time * 1000;
                    return this.setCountDown(
                            Math.floor(time / 1000 / 60 / 60 / 24),
                            Math.floor(time / 1000 / 60 / 60 % 24),
                            Math.floor(time / 1000 / 60 % 60),
                            Math.floor(time / 1000 % 60)
                        );
                },
                //{# 开始运行倒计时 #}
                run : function(){
                    var _this = this;
                    this.timer = setInterval(function(){
                        _this.runTime();
                    },1000);
                },
                //{# 倒计时运行函数 #}
                runTime : function(){
                    if(this.status === 1){
                        this.notBegin();
                    }
                    if(this.status === 2){
                        this.processing();
                    }
                    if(this.status === 3){
                        this.end();
                    }
                },
                //{# 清楚倒计时定时器 #}
                clearTimer: function(){
                    clearInterval(this.timer);
                },
                //编辑
                goEdit : function(){
                    this.$node.find('.coupon-text').html(endIn);
                    this.$node.find('.coupon-btn').html(getCouponText);
                    this.setCountDown(0,0,0,0);
                    if(countDown !== '1'){
                        this.removeCountDownBox();
                    }
                }
            });

            new CountDown($box);
        }
    })
    $('[data-module="U000129-1"]').each(function(index,el){
        $(el).U0001291();
    })
}());
