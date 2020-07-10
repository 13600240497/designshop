<template>
    <div class="geshop-m-timer" :style="style_body">
        <div class="geshop-m-timer-title">
            {{ $root.data.timer_label || 'Flash Sale' }}
        </div>
        <div class="geshop-m-timer-right">
            {{ status_label }}
            <!-- [时-分-秒] 显示 -->
            <template v-if="format && format === 'hms'">
                <span class="timer-spiner timer-hours-all" :style="style_span">{{ hoursContainDay }}</span>
            </template>
            <!-- [天-时-分-秒] 显示 -->
            <template v-else>
                <span class="timer-spiner" :style="style_span">{{ spiners[0] }}</span>
                :
                <span class="timer-spiner" :style="style_span">{{ spiners[1] }}</span>
            </template>
            :
            <span class="timer-spiner" :style="style_span">{{ spiners[2] }}</span>
            :
            <span class="timer-spiner" :style="style_span">{{ spiners[3] }}</span>
        </div>
    </div>
</template>

<script>
export default {
    props: ['start', 'end', 'format'],
    data () {
        return {
            localtime: new Date().getTime(),
            spiners: ['00', '00', '00', '00'],
            hoursContainDay: '00' // 天+时>时
        };
    },
    computed: {
        /**
         * 根据时间戳返回状态
         * 0 = 未开始
         * 1 = 已经开始
         * 2 = 结束
         * 3 = 异常情况
         *  */
        status () {
            if (this.localtime < this.start) {
                return 0;
            }
            if (this.localtime >= this.start && this.localtime < this.end) {
                return 1;
            }
            if (this.localtime > this.end) {
                return 2;
            }
            return 3;
        },
        status_label () {
            const down_ends = this.$lang('down_ends');
            const map = [
                this.$lang('down_starts'),
                down_ends,
                down_ends,
                down_ends
            ];
            return map[this.status];
        },
        style_body () {
            return {
                'color': this.$root.data.timer_font_color || '#333'
            };
        },
        style_span () {
            return {
                'color': this.$root.data.timer_span_font_color || '#fff',
                'background': this.$root.data.timer_span_bg_color || '#333'
            };
        }
    },
    methods: {
        // 获取 second 秒数
        get_second (timestamp) {
            if (timestamp - this.localtime <= 0) {
                return 0;
            } else {
                return parseInt(((timestamp - this.localtime) / 1000));
            }
        },
        // return array
        second_to_date (s) {
            let t = ['00', '00', '00', '00'];
            if (s > -1) {
                t = [];
                const day = Math.floor(s / 3600 / 24);
                const hour = Math.floor(s / 3600) % 24;
                const min = Math.floor(s / 60) % 60;
                const sec = s % 60;
                if (day < 10) {
                    t.push('0' + day);
                } else {
                    t.push(day);
                }
                if (hour < 10) {
                    t.push('0' + hour);
                } else {
                    t.push(hour);
                }
                if (min < 10) {
                    t.push('0' + min);
                } else {
                    t.push(min);
                }
                if (sec < 10) {
                    t.push('0' + sec);
                } else {
                    t.push(sec);
                }
                this.dayHour_to_hour(t);
            }
            return t;
        },
        /**
         * 格式转换 [天：时] => [时]
         * @param t [天, 时，分，秒]
         */
        dayHour_to_hour (t) {
            let hours;
            hours = parseFloat(t[0]) * 24 + parseFloat(t[1]);
            hours = hours >= 10 ? hours : '0' + hours;
            this.hoursContainDay = hours;
        }
    },
    mounted () {
        // 定时器开始
        setInterval(() => {
            // 更新时间
            this.localtime = new Date().getTime();
            let second = 0;
            // 获取计算状态
            switch (this.status) {
            case 0:
                second = this.get_second(this.start);
                this.spiners = this.second_to_date(second);
                break;
            case 1:
                second = this.get_second(this.end);
                this.spiners = this.second_to_date(second);
                break;
            case 2:
                break;
            default:
                break;
            }
        }, 1000);
    }
};
</script>

<style lang="less" scoped>
    .geshop-m-timer {
        padding: 0 24 / 75rem;
        height: 88 / 75rem;
        line-height: 88 / 75rem;
        color: #333;
        display: flex;
        background-color: #fff;
        .geshop-m-timer-title {
            font-size: 28 / 75rem;
            width: 100%;
        }
        .geshop-m-timer-right {
            width: 100%;
            text-align: right;
            span.timer-spiner {
                display: inline-block;
                width: 36 / 75rem;
                height: 36 / 75rem;
                line-height: 36 / 75rem;
                background: #333;
                color: #fff;
                text-align: center;
                font-size: 24 / 75rem;
                border-radius: 6 / 75rem;
            }
            .timer-spiner.timer-hours-all{
                min-width: 36 /75rem;
                width: auto;
                padding: 0 4 / 75rem;
                box-sizing: border-box;
            }
        }
    }
</style>
