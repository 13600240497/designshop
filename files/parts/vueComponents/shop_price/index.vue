<template>
    <component :is="components[site_code]" :value="value" :currency="currency"></component>
</template>

<script>
import zfpc from './zf-pc.vue';
import rgpc from './rg-pc.vue';
import rgm from './rg-m.vue';
import dlweb from './dl-web.vue';

export default {
    name: 'geshop-shop-price',
    props: {
        value: {
            default: '0.00'
        },
        currency: {
            default: 'USD'
        }
    },
    data () {
        return {
            components: {
                'zf-pc': zfpc,
                'zf-wap': zfpc,
                'zf-app': zfpc,
                'rg-pc': rgpc,
                'suk-pc': rgpc,
                'rg-wap': rgm,
                'suk-wap': rgm,
                'rg-app': rgm,
                'dl-web': dlweb,
                'dl-app': dlweb
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
