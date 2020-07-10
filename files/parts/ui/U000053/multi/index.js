if (!gsGlobal) {
    var gsGlobal = function(my) {
        var $gsCountTarget = $('.gs-count-down .gs_component_countDown');
        my.initCountDown = function() {
                $gsCountTarget = $('.gs-count-down .gs_component_countDown');
                $gsCountTarget.each(function() {
                    var $self = $(this),
                        leftTime = 0,
                        status = 0, // ['0未开始', '1已开始', '2已结束']
                        serverTime = new Date().getTime() || $self.prev('input[name=serverTime]').val(),
                        dataStartTime = $self.prev('input[name=serverTime]').attr('data-start-time'),
                        dataEndTime = $self.prev('input[name=serverTime]').attr('data-end-time'),
                        dataTextArr = ($self.prev('input[name=serverTime]').attr('data-textobj')).split(',');

                    serverTime = Math.round(serverTime / 1000);
                    dataStartTime = Math.round(dataStartTime / 1000);
                    dataEndTime = Math.round(dataEndTime / 1000);

                    if (dataStartTime > serverTime) {
                        leftTime = dataStartTime - serverTime;
                    } else if (dataStartTime <= serverTime && dataEndTime > serverTime) {
                        status = 1;
                        leftTime = dataEndTime - serverTime;
                    } else if (dataEndTime < serverTime) {
                        status = 2;
                        leftTime = 0;
                    }
                    // leftTime = Math.round(leftTime / 1000)
                    $self.data({ 'leftTime': leftTime, 'status': status })
                    $self.prev().prev().text(dataTextArr[status] + ':')

                    /* 服务器倒计时时间模拟 */
                    /* 				if (serverTimeInterval) clearTimeout(serverTimeInterval)
                    				var serverTimeInterval = setInterval(function () {
                    					serverTime = serverTime + 1;
                    					$self.prev('input[name=serverTime]').val(serverTime * 1000)
                    				}, 1000) */
                })

            }
            /* 倒计时组件 */
        my.compoentCountDown = function() {
            var _this = this;
            $gsCountTarget.each(function() {
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

                    $self.data('left-time', leftTime - 1)
                    $self.html('<span><em>' + CDay + '</em>:<em>' + CHour + '</em>:<em>' + CMinute + '</em>:<em>' + CSecond + '</em></span>')
                        // $self.html('<span><strong>' + CDay + 'DAY(S)</strong>' + CHour + ':' + CMinute + ':' + CSecond + '</span>')
                } else {
                    $gsCountTarget = $gsCountTarget.not($self)
                        // $self.html('<span><strong>00 DAY(S) </strong>00:00:00</span>')
                    if (dataStatus !== '2') {
                        gsGlobal.initCountDown();
                    } else {
                        $self.html('<span><em>00</em>:<em>00</em>:<em>00</em>:<em>00</em></span>')
                    }
                }
            })
            if (0 !== $gsCountTarget.length) {
                setTimeout(gsGlobal.compoentCountDown, 1000)
            }
        }

        return my
    }(gsGlobal || {})
    gsGlobal.initCountDown();
    gsGlobal.compoentCountDown();
}

function MultiDataRange(el, dataSrouce) {

    this.dom = el;
    this.startTime = dataSrouce.startTime || null;
    this.endTime = dataSrouce.endTime || new Date().getTime();
    this.intervalID = null;
    this.status = 0; // 0=未开始, 1=进行中, 2=结束
    this.lastTime = 0;
    this.texts = this.dom.find('.multi-dateRange-text').attr('data-text').split(',');
    this.bgColor = dataSrouce.bgColor || "#ffffff";
    this.textColor = dataSrouce.textColor || "#333333";
    this.gridBgColor = dataSrouce.gridBgColor || '#333333';
    this.gridTextColor = dataSrouce.gridTextColor || "#FFFFFF";
    this.test = this.startTime == null;
    this.isEditEnv = false;

    this.helper = {
        // 计算当前状态  return [0/1/2]
        checkStatus: function(timestamp) {
            var nowTime = new Date().getTime();
            if (nowTime > this.startTime && nowTime < this.endTime) {
                return 1;
            } else if (nowTime <= this.startTime) {
                return 0;
            } else if (nowTime > this.endTime ) {
                return 2;
            }
            return 0;
        },
        // 计算剩余的秒数
        calculate: function(status) {
            var nowTime = new Date().getTime();
            // if (status == 0) return this.startTime - nowTime;
            if (status == 0) return 0;
            if (status == 1) return this.endTime - nowTime;
            if (status == 2) return 0;
        }
    }

    this.init = function() {
        if (this.startTime != null) {
            this.status = this.helper.checkStatus.call(this);
            this.lastTime = this.helper.calculate.call(this, this.status);
            if (this.status != 2) this.start();
        }

        // set nomal stylesheet
        this.dom.css({
            'background-color': this.bgColor,
        });
        if(this.textFontSize == 0) {
            this.dom.find('.multi-dateRange-text').hide();
        } else {
            this.dom.find('.multi-dateRange-text').css({
                'color': this.textColor,
                'font-size': this.textFontSize+'px',
            });
        }
        this.dom.find('.multi-dateRange-numbers label').css({
            'background-color': this.gridBgColor,
            'color': this.gridTextColor,
            'border-radius': this.gridRadius+'px',
            'font-size': this.numbersFontSize+'px',
        });
        this.dom.find('.multi-dateRange-numbers span').css({
            'color': this.gridBgColor
        });

        this.render();
        return this;
    }
    this.start = function() {
        clearInterval(this.intervalID)
        var that = this;
        this.intervalID = setInterval(function() {
            if (that.status > 1) return clearInterval(that.intervalID);

            var _checkStatus = that.helper.checkStatus.call(that);
            var _lastTime = that.helper.calculate.call(that, _checkStatus);

            that.lastTime = _lastTime;
            that.status = _checkStatus;
            that.render();
        }, 1000);
        return this;
    }
    this.format = function(second) {
        var day = 0;
        var hour = 0;
        var minute = 0;
        var second = parseInt(second / 1000);
        if (second >= 3600*24 ) {
            day = parseInt(second / (3600 * 24));
            second = second - day * 3600 * 24;
        }
        if (second >= 3600) {
            hour = parseInt(second / 3600);
            second = second - hour * 3600;
        }
        if (second >= 60) {
            minute = parseInt(second / 60);
            second = second - minute * 60;
        }
        return [day, hour, minute, second];
    }
    this.render = function () {
        var arr = this.format(this.lastTime);
        var day = this.numberToString(arr[0]);
        var hour = this.numberToString(arr[1]);
        var minute = this.numberToString(arr[2]);
        var second = this.numberToString(arr[3]);
        this.dom.find('.multi-dateRange-numbers label').eq(0).html(day);
        this.dom.find('.multi-dateRange-numbers label').eq(1).html(hour);
        this.dom.find('.multi-dateRange-numbers label').eq(2).html(minute);
        this.dom.find('.multi-dateRange-numbers label').eq(3).html(second);
        this.dom.find('.multi-dateRange-text').html(this.texts[this.status]);
        if (this.isEditEnv == true) {
            // the last one
            if (this.status != 1) {
                if (this.dom.parent().find('.multi-dateRange-inner').length>1) {
                    this.dom.remove();
                } else {
                    this.dom.addClass('active');
                }
            } else {
                this.dom.addClass('active');
            }
        } else {
            if (this.status != 1) this.dom.hide();
            if (this.status == 1) this.dom.show().addClass('active');
        }

    }
    this.numberToString = function (num) {
        return (num.toString().length == 1) ?  "0" + num : num.toString();
    }
}


$(function() {

    function getDataRow(source, id) {
        var obj = source[0];
        for(var i=0; i<source.length; i++) {
            if (source[i].key==id) obj = source[i];
        }
        return obj;
    }

    var domTargets = $('.multi-dateRange').each(function(boxIndex, boxDom){
        var dataSrouce = JSON.parse($(boxDom).find('[data-source]').html()) || [{test:true}];
        $(boxDom).find('.multi-dateRange-inner').each(function(i, el) {
            var id = $(el).attr('data-key');
            var multiDataRange = new MultiDataRange($(el), getDataRow(dataSrouce, id));
                multiDataRange.textFontSize =  $(boxDom).find('[name=textFontSize]').val();
                multiDataRange.numbersFontSize =  $(boxDom).find('[name=numbersFontSize]').val();
                multiDataRange.isEditEnv = $(boxDom).find('[name=isEditEnv]').val() == 1;
                multiDataRange.init();
        });
    });

});
