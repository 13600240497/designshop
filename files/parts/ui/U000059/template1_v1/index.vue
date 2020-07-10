<template>
    <div class="geshop-u000059-template1_v1">
        <div class="geshop-u000059-template1_v1-container">
           <div class="box-hot">
                <template v-show="points.length > 0" v-for="(item, index) in points" >
                    <a
                        class="area-point js-geshop-ga-click js-geshop-ga-view"
                        :geshop-component-name="item.name || ''"
                        :href="item.url ? item.url : 'javascript:void(0)' "
                        :target="data.href_target == 1 ? '_blank' : '_self'"
                        :style="{ width: item.coords.width, height: item.coords.height, left: item.coords.left, top: item.coords.top }"
                        :key="index">
                    </a>
                </template>
                <img :src="data.base_img ? data.base_img : 'https://geshopcss.logsss.com/imagecache/geshop-test/resources/images/default/banner.png' " alt="hot-Pic">
           </div>
        </div>
    </div>
</template>

<script>
export default {
    props: ['data'],
    name: 'index',
    data () {
        return {
            // 热图详细信息
            points: []
        };
    },
    computed: {
        areas () {
            // 是否有热图区
            return this.data.areas || 0;
        }
    },
    mounted () {
        if (typeof this.areas !== 'undefined' && this.areas && this.areas != 0) {
            // 绘制区数据
            this.renderArea(this.areas.renderData);
        }

        // 初始化GA埋点的元素
        this.$nextTick(() => {
            window.geshop_ga.bind_event_by_dom(this.$el);
        });
    },
    methods: {
        renderArea (data) {
            this.points = JSON.parse(JSON.stringify(data));
        }
    }
};
</script>

<style scoped lang="less">
    .geshop-u000059-template1_v1 {
        text-align: center;
       .box-hot {
           display: inline-block;
           position: relative;
           font-size: 0;
       }
        img {
            max-width: 100%;
        }
        .area-point {
            position: absolute;
            font-size: 12px;
        }
    }
</style>
