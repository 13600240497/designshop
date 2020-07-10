<template>
    <component :is="components[site_code]" :value="value"><slot></slot></component>
</template>

<script>
import rgpc from './rg-pc.vue';
import rgm from './rg-m.vue';

export default {
    name: 'geshop-goods-title',
    props: {
        value: {
            type: String,
            default: ''
        }
    },
    data () {
        return {
            components: {
                'zf-pc': rgpc,
                'zf-wap': rgm,
                'zf-app': rgm,
                'rg-pc': rgpc,
                'suk-pc': rgpc,
                'rg-wap': rgm,
                'suk-wap': rgm,
                'rg-app': rgm,
                'dl-web': rgpc,
                'dl-app': rgm
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
