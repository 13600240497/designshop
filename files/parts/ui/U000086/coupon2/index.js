$(function() {
   $.extend($.fn,{
        U0000862 : function(){
           var $box = this;
           // 优惠券按钮有三个状态  未领取(un-received) 1  已领取(received) 2  已失效(failed) 3
           var $hideInput = $box.find('.hide-info-input');
           var _getAttr = function _getAttr(attr){
               return $hideInput.attr((attr || '').toLowerCase()) || '';
           }
           var unReceivedText = _getAttr('unReceivedText');
           var receivedText = _getAttr('receivedText');
           var failedText = _getAttr('failedText');
           var couponId = _getAttr('couponId');
           var lang = _getAttr('lang');
           var successText = _getAttr('successText');
           var serverTime = _getAttr('serverTime');
           var staticDomain = _getAttr('staticDomain');
        //    var siteCode = _getAttr('siteCode').split('-')[0];
           var siteCode = GESHOP_SITECODE ? GESHOP_SITECODE.split('-')[0] : '';
           var _diffTime = parseInt(serverTime) - (new Date()).getTime();
           var currency = ''; //币种符号
           //如果是1的话就是编辑环境
           var __dev = _getAttr('env') === '1';
           var _jsonp = function _jsonp(param){
               param = param || {};
               return $.ajax($.extend({
                   dataType: "jsonp",
                   jsonp: 'callback',
               },param));
           };
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
               // {# 状态 这李的状态指示代表优惠券是否在进行中的 #}
               this.status = 0;
               // {# 这个状态是表示 未领取(un-received) 1  已领取(received) 2  已失效(failed) 3 #}
               this.couponStatus = 0;
               this.$node = $node;
               this.$timeLine = this.$node.find('.time-line');
               this.timer = null;
               this.diffTime = _diffTime;
               // {# 优惠券领取次数 需求是优惠券只能领取一次 #}
               this.receivedCount = 0;
               this.getCouponDetail();
               // {# 倒计时是否显示 1是显示 2是不显示 #}
               this.countDownFlag = false;
               this.bindEvent();
               this.isClick = false;
               if(!couponId){
                   this.goEdit();
               }
           };
           $.extend(CountDown.prototype,{
               changeCouponStatus : function(flag){
                   var map = {
                       '1' : {
                           class : 'un-received',//未领取
                           text : unReceivedText,
                       },
                       '2' : {
                           class : 'received',//已领取
                           text : receivedText,
                       },
                       '3' : {
                           class : 'failed',//领取完毕
                           text : failedText,
                       },
                   };
                   var status = map[flag];
                   if(status){
                       this.couponStatus = parseInt(flag);
                       this.$node.find('.coupon-btn').html(status.text);
                       this.$node.find('.coupon-btn').prop('class','coupon-btn inline-box ' + status.class);
                   }
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
                   }
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
                   //编辑环境就不需要事件
                   if(__dev)return;
                   var _this = this;
                   this.$node.on('click',function(){
                       //如果是未开始的时候点击
                        if(_this.status === 1){
                            GEShopSiteCommon.dialog.message(GESHOP_LANGUAGES.coupon_comming_msg);
                            return;
                        }
                        // {# 如果是进行中的时候点击 如果优惠券用户已经领取过 那么就直接提示用户 如果用户没有领取 就调用接口 #}
                        if(_this.couponStatus === 1){
                            if(_this.isClick)return;
                            _this.isClick = true;
                            // {# 正式上线之后的url是这个 GESHOP_INTERFACE.getcoupon.url #}
                            _jsonp({
                                url: GESHOP_INTERFACE.getcoupon.url,
                                data: {
                                    content : coupunParam,
                                },
                                success :function(res){
                                    _this.isClick = false;
                                    if(res.code === 0){
                                        GEShopSiteCommon.dialog.message(successText);
                                        //领取成功之后把状态改成已领取
                                        _this.changeCouponStatus('2');
                                    }else if(res.code === 1){
                                        // {# 获取优惠券未登陆状态 跳转登录页面 #}
                                        window.location.href = res.data.loginurl + window.location.href;
                                    }else if(res.code === 3){
                                        // 优惠券领取完毕的状态
                                        _this.changeCouponStatus('3');
                                    }else{
                                        GEShopSiteCommon.dialog.message(res.message);
                                    }
                                },
                                error : function(){
                                    _this.isClick = false;
                                }
                            });
                        }
                   })
               },
               // {# 获取优惠券详情 #}
               getCouponDetail : function(){
                   if (!couponId) return;
                   var _this = this;
                   var url = GESHOP_INTERFACE.coupondetail.url;
                   var pid = $box.attr('data-id');
                   var params = JSON.parse(coupunParam);
                   window.GEShopCommonFn_Vue.$jsonp(url, params, { pid: pid }).done(function (res) {
                        if (res.code === 0) {
                            res.data.couponInfo = res.data.couponInfo || {};
                            var couponInfo = res.data.couponInfo || {};
                            if(couponInfo.offerCurrency && typeof my_array_sign !== 'undefined') {
                            currency = my_array_sign[couponInfo.offerCurrency];
                            }
                            if(couponInfo.id){
                                _this.setDiscount(couponInfo.offerAmount, couponInfo.thresholdAmount, couponInfo.type);
                                // {# 这里做一层判断 如果通过就去倒计时那边 #}
                                // {# 已领取了 #}
                                if(couponInfo.receivedCount > 0){
                                    _this.changeCouponStatus('2');
                                }else{
                                    // {# 如果maxCount是0的话就是不限量 #}
                                    if(couponInfo.maxCount != 0){
                                        _this.changeCouponStatus(couponInfo.leftCount > 0 ? '1' : '3');
                                    }else{
                                        _this.changeCouponStatus('1');
                                    }
                                }
                                // {# 只有优惠券是未领取状态的时候才去走时间流程 #}
                                _this.couponStatus === 1 && _this.judge(res.data);
                            }else{
                                _this.changeCouponStatus('3');

                            }
                        }else{
                            // {# 获取优惠券信息错误 #}
                            _this.changeCouponStatus('3');
                        }
                   });
               },
               // {# 获取时间 返回当前时间的时间撮  time 时间 #}
               getTime : function(){
                   return (new Date()).getTime();
               },
               // {# 获取正确的时间  本地时间 + 服务器与本地的时间差 #}
               getCurTime : function(){
                   // return this.getTime() + this.diffTime;
                   return this.getTime();
               },
               // {# 未开始 #}
               notBegin : function(){
                   if(this.status !== 1){
                       this.status = 1;
                   }
                   var diffTime = parseInt(this.getCurTime() / 1000 - this.startTime / 1000);
                   if(diffTime >= 0){
                       this.processing();
                   }
               },
               // {# 进行中 #}
               processing : function(){
                   if(this.status !== 2){
                       this.status = 2;
                   }
                   var diffTime = parseInt(this.endTime / 1000 - this.getCurTime() / 1000);
                   if(diffTime <= 0){
                       this.end();
                   }
               },
               // {# 已结束 #}
               end: function(){
                   this.status = 3;
                   this.clearTimer();
                   this.changeCouponStatus('3');
               },
               // {# 开始运行倒计时 #}
               run : function(){
                   var _this = this;
                   this.timer = setInterval(function(){
                       _this.runTime();
                   },1000);
               },
               // {# 倒计时运行函数 #}
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
               // {# 清楚倒计时定时器 #}
               clearTimer: function(){
                   clearInterval(this.timer);
               },
               goEdit : function(){
                   this.changeCouponStatus('1');
                   this.setDiscount(50, 400, 2);
               },
               //设置折扣 跟折扣条件值
                setDiscount: function (n1, n2, type) {
                    var sign = lang === 'fr' ? '-' : '';

                    if (parseInt(type) === 1) {
                        this.$node.find('.coupon-discount').text(sign + String(n1) + '%');
                    } else if (parseInt(type) == 2) {
                        this.$node.find('.coupon-discount').attr('data-orgp',n1).html(sign + currency + n1);
                    }
                    this.$node.find('.coupon-discount-term i').attr('data-orgp',n2).html(currency + n2);
                }
            });

            new CountDown($box);
       }
   })

   $('[data-module="U000086-2"]').each(function(index,el){
        $(el).U0000862();
   })
});
