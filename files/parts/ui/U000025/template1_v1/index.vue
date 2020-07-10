<template>
    <div class="geshop-component-box U0000025_banner_wrap" ref="rankBox">
        <div class="img_box">
            <!-- banner图片 -->
            <a :href="data.banner_href ? data.banner_href : 'javascript:void(0)'" :target="data.banner_href ? '_blank' : '_self'" :class="data.banner_href ? '' : 'i-none'">
                <img v-if="data.banner_img == '' || data.banner_img == undefined" src="https://geshopimg.logsss.com/uploads/GbQJA0SDdhjUvMsEu9KL1ktNoYn3qXIP.png" class="banner_img" alt="">
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
            const $box = $(this.$refs.rankBox);
            let boxW = $box.width();
            const imgs = $box.find('.img_box img');

            if (imgs.width() > boxW) {
                imgs.css('width', '100%');
            }

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
