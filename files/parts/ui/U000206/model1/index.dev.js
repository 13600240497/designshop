$(function() {
    function Countdown($el, isEdit) {
        this.$el = $($el);
        this.isEdit = typeof isEdit === 'undefined' ? false : isEdit;
        this.init();
    };

    Countdown.prototype = {
        constructor: Countdown,

        init: function() {
            this.$title = this.$el.find('.cd-title');
            this.$content = this.$el.find('.cd-content');
            this.beforeTitle = this.$el.data('beforeTitle');
            this.startedTitle = this.$el.data('startedTitle');
            this.endedTitle = this.$el.data('endedTitle');
            this.dayTitle = this.$el.data('day-label');
            this.daysTitle = this.$el.data('days-label');
            this.startTime = parseInt(this.$el.data('startTime')) || 0;
            this.endTime = parseInt(this.$el.data('endTime')) || 0;
            this.start();
        },

        start: function() {
            this.tick();
        },

        stop: function() {
            clearTimeout(this.timer);
        },

        tick: function() {
            var self = this;
            self.update();
            if (!self.isEdit) {
                this.timer = setTimeout(function() {
                    if(!self.update()) { // 返回false，代表倒计时结束
                        self.tick();
                    }
                }, 1000);
            }
        },

        renderTitle: function(title) {
            if (title !== this.title) {
                this.$title.html(title);
                this.title = title;
            }
        },

        getTimeObj: function(time) {
            var millseconds = time;
            var seconds = Math.floor(millseconds / 1000);
            var minutes = Math.floor(seconds / 60);
            var hours = Math.floor(minutes / 60);

            var time_show_type = this.$el.find('input[name=time_show_type]').val();
            if (time_show_type === 'seconds') {
                var days = Math.floor(hours / 24);
            } else {
                var days = Math.ceil(hours / 24);
            }
            // console.log(days, time, '***');
            return {
                d: days,
                h: hours % 24,
                m: minutes % 60,
                s: seconds % 60,
                ms: millseconds % 1000,
                leftTime: time
            };
        },

        getCurrentData: function() {
            var now = new Date().getTime();
            if (this.startTime > now) {
                return { title: this.beforeTitle, leftTime: this.startTime - now };
            } else if (this.startTime <= now && now <= this.endTime) {
                return { title: this.startedTitle, leftTime: this.endTime - now };
            } else {
                return { title: this.endedTitle, leftTime: 0 };
            }
        },

        renderTime: function(leftTime) {
            var timeObj = this.getTimeObj(leftTime);
            var time_show_type = this.$el.find('input[name=time_show_type]').val();
            if (time_show_type === 'seconds') {
                var dayHtml = '<span class="cd-value">' + (timeObj.d >= 10 ? timeObj.d : '0'+timeObj.d) + '</span><i class="cd-split">:</i>';
                var hourHtml = '<span class="cd-value">' + (timeObj.h >= 10 ? timeObj.h : '0'+timeObj.h) + '</span><i class="cd-split">:</i>';
                var minuteHtml = '<span class="cd-value">' + (timeObj.m >= 10 ? timeObj.m : '0'+timeObj.m) + '</span><i class="cd-split">:</i>';
                var secondHtml = '<span class="cd-value">' + (timeObj.s >= 10 ? timeObj.s : '0'+timeObj.s) + '</span>';
                this.$content.html(dayHtml + hourHtml + minuteHtml + secondHtml).addClass('is-second').removeClass('is-day');
            } else {
                var calc_day = 0;
                if (leftTime >= 86400000) {
                    calc_day = timeObj.d;
                } else if (leftTime > 0) {
                    calc_day = 1;
                } else {
                    calc_day = 0;
                }
                var dayHtml = '<span class="cd-value">' + calc_day + '</span><i class="cd-split"></i>';
                var dayText = calc_day > 1 ? this.daysTitle : this.dayTitle;
                this.$content.html(dayHtml + dayText).addClass('is-day').removeClass('is-second');
            }
        },

        update: function() {
            var currentData = this.getCurrentData();
            this.renderTitle(currentData.title);
            this.renderTime(currentData.leftTime);
            return currentData.leftTime === 0;
        }
    };

    $('.geshop-U000206-model1').each(function() {
        try {
            var $component = $(this);
            new Countdown($component.find('.cd-inner'), $component.hasClass('is-edit'));
        } catch (e) {
        }
    });
});
