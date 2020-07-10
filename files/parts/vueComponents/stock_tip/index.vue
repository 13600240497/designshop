<template>
    <component :is="components[site_code]" :item="item"></component>
</template>

<script>
import rgpc from './rg-pc.vue';
import rgm from './rg-m.vue';

export default {
    name: 'geshop-stocktip',
    props: {
        // 商品详情对象
        item: {
            type: Object | Number,
            default: {}
        }
    },
    data () {
        return {
            components: {
                'rg-pc': rgpc,
                'rg-wap': rgm,
                'rg-app': rgm
            }
        };
    },
    computed: {
        site_code () {
            return window.GESHOP_SITECODE;
        }
    }
};
</script>
