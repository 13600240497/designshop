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
    /*----- 宽度 -----*/
    .w(@px) {
        width: unit(@px / 37.5, rem);
    }

    /*----- 高度 -----*/
    .h(@px) {
        height: unit(@px / 37.5, rem);
    }

    /*----- 行高 -----*/
    .lh(@px) {
        line-height: unit(@px / 37.5, rem);
    }

    .fs(@px) {
        font-size: unit(@px / 37.5, rem);
    }

    .coups-bg {
        background: url("../images/coupon.png") 0 0 no-repeat;
        background-size: 454/75rem auto;
        .w(454/2);
        .h(140/2);
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
            font-size: 12/37.5rem;
            vertical-align: top;
            height: 100%;
        }

        .c-left {
            .w(153);
        }

        .c-right {
            .w(74);
            color: #FFFFFF;
            .fs(10);
            text-align: center;
            position: relative;
            top: 50%;
            margin-top: -12/37.5rem;

            .end-tip, .time-tip {
                -webkit-box-sizing: border-box;
                -moz-box-sizing: border-box;
                box-sizing: border-box;
                .h(12);
                .lh(12);
            }

            .end-tip {

            }

            .time-tip {

            }

        }

        .c-code, .c-tit {
            color: #FFFFFF;
            -webkit-box-sizing: border-box;
            -moz-box-sizing: border-box;
            box-sizing: border-box;
            padding-left: 18/37.5rem;
        }

        .c-code {
            .fs(8);
            margin-top: 2/37.5rem;
        }

        .c-title {
            /*margin-top: 36px;*/
            font-size: 18/37.5rem;
            .lh(22);
            .h(22);

        }

        .c-tit {
            text-transform: uppercase;
        }

        .c-title2 {
            .fs(16);
        }

        .c-title3 {
           .fs(12);
        }
    }
</style>
