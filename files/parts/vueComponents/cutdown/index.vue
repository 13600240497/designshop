<template>
    <p class="times-tip site-font-bold">
        <!-- type == 2 精确到天-->
        <template v-if="type == 2">
            <span>{{ spiners[0] }}</span>:<span>{{ spiners[1] }}</span>:<span>{{ spiners[2] }}</span>:<span>{{ spiners[3] }}</span>
        </template>
        <!--精确到小时-->
        <template v-else>
            <span>{{ spiners[1] }}</span>:<span>{{ spiners[2] }}</span>:<span>{{ spiners[3] }}</span>
        </template>
    </p>
</template>

<script>
export default {
    name: 'geshop-cutdown',
    props: ['type', 'times'],
    data () {
        return {
            spiners: ['00', '00', '00', '00'],
            hoursContainDay: '00' // 天+时>时
        };
    },
    mounted () {
        this.sumTime = parseInt(this.times || 86400);
        this.cutDown();
    },
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
            this.sumTime -= 1;
            clearTimeout(timer);
            this.spiners = this.second_to_date(this.sumTime);
            if (this.sumTime > 0) {
                timer = setTimeout(this.cutDown, 1000);
            }
        }
    }
};
</script>

<style scoped lang="less">

</style>
