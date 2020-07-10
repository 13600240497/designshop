<template>
    <div class="geshop-u000206-template2" :class="boxWrapMedia" ref="countdown">
        <div class="countdowm-box">
            <span class="cd_text">{{coupon_text}}</span>
            <geshop-timer :start="start" :end="end" :pid="pid"></geshop-timer>
        </div>
    </div>
</template>

<script>
export default {
    props: ['data', 'pid'],
    data () {
        return {
            paltform: GESHOP_PLATFORM,
            lang: GESHOP_LANG, // 语言
            boxWrapMedia: 'geshop_dl_pc', // 屏幕大小的类名
            languages: GESHOP_LANGUAGES,
            coupon_text: 'Comming soon'
        };
    },
    computed: {
        start () {
            // IOS5中的Safari能正确解析new Date()那么必须这么写 new Date('2013/10/21');
            return this.data.dataRange ? new Date(this.data.dataRange.split(' - ')[0].replace(/-/g, '/')).getTime() : '';
        },
        end () {
            return this.data.dataRange ? new Date(this.data.dataRange.split(' - ')[1].replace(/-/g, '/')).getTime() : '';
        },
        status () {
            return this.$store.state.dresslily.countdown_status[this.pid];
        }
    },
    created () {
        this.$nextTick(() => {

        });
    },
    mounted () {
        // 追加函数到队列
        this.updateStatus(this.status);
        this.$store.commit('dresslily/update_onresize_marque', this.onChange);
    },
    updated () {
    },
    methods: {
        /**
         *  * 根据时间戳返回状态
         * 0 = 未开始
         * 1 = 已经开始
         * 2 = 结束
         * 3 = 异常情况
         * @param status
         */
        updateStatus (val) {
            switch (val) {
            case 0:
                this.coupon_text = this.data.before_title || 'Comming soon';
                break;
            case 1:
                this.coupon_text = this.data.started_title || 'Ends In';
                break;
            case 2:
                this.coupon_text = this.data.ended_title || 'Already Ended';
                break;
            default:
                this.coupon_text = this.data.before_title || 'Comming soon';
                break;
            }
        },
        onChange () {
            /* let w = this.$goodsList.width();
            if (w > 1200) {
                this.boxWrapMedia = 'geshop_dl_pc';
            } else if (w <= 1200 && w >= 768) {
                this.boxWrapMedia = 'geshop_dl_pad';
            } else if (w < 768 && w >= 375) {
                this.boxWrapMedia = 'geshop_dl_wap';
            } else {
                this.boxWrapMedia = 'geshop_dl_wap geshop_dl_m';
            } */
        }
    },
    watch: {
        status (val) {
            this.updateStatus(val);
        }
    }
};
</script>
<style lang="less">
    @import "countdown";
</style>
