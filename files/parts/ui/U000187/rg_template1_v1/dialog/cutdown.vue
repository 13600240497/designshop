<template>
    <div class="coups-bg">
        <div class="c-left">
            <div class="boxmaind">
                <div class="cop-2" v-if="userData.point == 1 || userData.point == 3 || userData.point == 4">
                    <p class="c-tit c-title2 site-font-bold">{{ userData.msg.split('|')[0] || '*****' }}</p>
                    <p class="c-tit c-title3 site-font-bold">{{ userData.msg.split('|')[1] || '*******' }}</p>
                </div>
                <div class="cop-1" v-else>
                    <p class="c-tit c-title site-font-bold">{{ userData.msg || '*****' }}</p>
                </div>
                <p class="c-code">{{ language.use_code }}: {{userData.code || '*******'}}</p>
            </div>
        </div>
        <div class="c-right">
            <p class="end-tip">{{ language.ends_in }}</p>
            <p class="time-tip site-font-bold">{{ this.spiners[1] }}:{{ this.spiners[2] }}:{{ this.spiners[3] }}</p>
        </div>
    </div>
</template>

<script>
export default {
    name: 'cutdown',
    props: ['userData', 'language'],
    data () {
        return {
            localTime: 60 * 60 * 24,
            spiners: ['00', '00', '00', '00'],
            hoursContainDay: '00' // 天+时>时
        };
    },
    mounted () {
        this.cutDown();
    },
    components: {},
    methods: {
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
         * 转换天：时>时
         * @param t [天,时，分，秒]
         */
        dayHour_to_hour (t) {
            let hours;
            hours = parseFloat(t[0]) * 24 + parseFloat(t[1]);
            hours = hours >= 10 ? hours : '0' + hours;
            this.hoursContainDay = hours;
        },
        cutDown () {
            let timer = null;
            this.localTime -= 1;
            clearTimeout(timer);
            this.spiners = this.second_to_date(this.localTime);
            if (this.localTime > 0) {
                timer = setTimeout(this.cutDown, 1000);
            }
        }
    }
};
</script>

<style scoped lang="less">
    .coups-bg {
        background: url("../images/coupon.png") 0 0 no-repeat;
        width: 454px;
        height: 140px;
        margin: 0 auto;
        font-size: 0;
        text-align: left;

        .boxmaind {
            position: relative;
            transform: translateY(-50%);
            top: 50%;
        }

        .c-left, .c-right {
            display: inline-block;
            font-size: 12px;
            vertical-align: top;
            height: 100%;
        }

        .c-left {
            width: 312px;
        }

        .c-right {
            width: 142px;
            color: #FFFFFF;
            font-size: 20px;
            .end-tip,.time-tip {
                -webkit-box-sizing: border-box;
                -moz-box-sizing: border-box;
                box-sizing: border-box;
                padding-left: 33px;
                height: 24px;
                line-height: 24px;
            }
            .end-tip {
                margin-top: 46px;
            }
            .time-tip {

            }

        }

        .c-code,.c-tit {
            color: #FFFFFF;
            -webkit-box-sizing: border-box;
            -moz-box-sizing: border-box;
            box-sizing: border-box;
            padding-left: 36px;
        }
        .c-code {
            font-size:16px;
            line-height: 19px;
            height: 19px;
            margin-top: 6px;
        }
        .c-title {
            /*margin-top: 36px;*/
            font-size: 36px;
            line-height: 43px;
            height: 43px;

        }
        .c-tit {
            text-transform: uppercase;
        }

        .c-title2 {
            font-size: 36px;
        }

        .c-title3 {
            font-size: 28px;
        }
    }
</style>
