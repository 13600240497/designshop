<template>
    <div>
        <component
            :is="components[sitecode]"
            :value="value"
            :percent="percent">
        </component>
    </div>
</template>

<script>
import zfpc from './zf-pc.vue';
import zfm from './zf-m.vue';
import dlweb from './dl-web.vue';
import rgpc from './rg-pc.vue';
import rgm from './rg-m.vue';

export default {
    name: 'geshop-discount',
    props: {
        visible: {
            type: Boolean,
            default: null
        },
        value: {
            default: 0
        },
        percent: {
            default: 0
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
        sitecode () {
            return GESHOP_SITECODE;
        }
    }
};
</script>
