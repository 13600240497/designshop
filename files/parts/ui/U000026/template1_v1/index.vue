<template>
    <div class="geshop-component-box U0000026_banner_wrap">
        <div class="img_box">
            <!-- banner图片 -->
            <a :href="data.banner_href ? data.banner_href : 'javascript:void(0)'" target="_self">
                <img v-if="data.banner_img == '' || data.banner_img == undefined" src="https://geshoptest.s3.amazonaws.com/uploads/rTaQdSMiYzgptwhPAsRcK52vOejZfb0W.png" class="banner_img" alt="">
                <img v-else :src="data.banner_img" class="banner_img" alt="">
            </a>
            <template v-if="showMask">
                <!-- banner蒙层 -->
                <div class="mask"></div>
                <!-- banner 时间文案bg -->
                <div class="time-bg"></div>
                <!-- banner 时间文案 -->
                <div class="time-text">
                    {{ langText }}
                </div>
            </template>
        </div>
    </div>
</template>
<script>

export default {
    props: ['data'],
    data () {
        return {
            showMask: false,
            langText: ''
        };
    },
    mounted () {
        // 去loading
        this.$store.dispatch('global/loaded', this);
        this.$nextTick(() => {
            if (this.data.active_time != '' && this.data.active_time != undefined) {
                let times = this.data.active_time.split(' - ');
                let start = new Date(times[0].replace(/-/g, '/')).getTime();
                let end = new Date(times[1].replace(/-/g, '/')).getTime();
                let now = Date.now();
                // ing
                if (start <= now && now <= end) {
                    this.showMask = false;
                //    未开始
                } else if (start > now) {
                    this.langText = this.$lang('coming_soon');
                    this.showMask = true;
                } else { //    end
                    this.langText = this.$lang('ended');
                    this.showMask = true;
                }
            } else {
                this.showMask = false;
            }
        });
    },
    methods: {

    }
};
</script>
<style scoped lang="less">
@import "index_vue";
</style>
