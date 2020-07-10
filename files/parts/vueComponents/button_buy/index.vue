<template>
    <component :is="components[site_code]" :visible="visible" :value="value" :target="target"></component>
</template>

<script>
import zfpc from './zf-pc.vue';
import zfm from './zf-m.vue';
import rgpc from './rg-pc.vue';
import rgm from './rg-m.vue';
import dlweb from './dl-web.vue';

export default {
    name: 'geshop-buynow',
    props: {
        visible: {
            type: Boolean,
            default: true
        },
        value: {
            type: String,
            default: ''
        },
        // 链接跳转类型
        target: {
            type: String,
            default: '_self'
        }
    },
    data () {
        return {
            components: {
                'zf-pc': zfpc,
                'zf-wap': zfm,
                'zf-app': zfm,
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
