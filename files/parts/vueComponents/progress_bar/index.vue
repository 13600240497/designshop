<template>
    <div>
        <component
            :is="components[sitecode]"
            :color1="color1"
            :color2="color2"
            :current="goodsInfo.current"
            :total="goodsInfo.total"
            :type="type"
            :limitType="limitType"
        >
        </component>
    </div>
</template>

<script>
import zfpc from './zf-pc.vue';
import zfm from './zf-m.vue';
import rgpc from './rg-pc.vue';
import rgm from './rg-m.vue';
import dlweb from './dl-web.vue';

export default {
    name: 'geshop-progress-bar',
    props: ['color1', 'color2', 'current', 'total', 'type', 'limitType'],
    data () {
        return {
            components: {
                'zf-pc': zfpc,
                'zf-wap': zfm,
                'zf-app': zfm,
                'rg-pc': rgpc,
                'rg-wap': rgm,
                'rg-app': rgm,
                'dl-web': dlweb,
                'dl-app': dlweb
            }
        };
    },
    computed: {
        sitecode () {
            return this.$root.sitecode;
        },
        // 初始化库存信息,避免null值
        goodsInfo () {
            return {
                total: Number(this.total),
                current: Number(this.current)
            };
        }
    }
};
</script>
