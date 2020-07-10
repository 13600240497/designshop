<template>
    <component
            :is="components[sitecode]"
            :start="start"
            :end="end"
            :pid="pid"
            :format="format">
    </component>
</template>

<script>
import zfm from './zf-m.vue';
import dlweb from './dl-web.vue';
export default {
    name: 'geshop-timer',
    props: ['start', 'end', 'format', 'pid'],
    data () {
        return {
            components: {
                'zf-wap': zfm,
                'zf-app': zfm,
                'rg-pc': dlweb,
                'dl-web': dlweb,
                'dl-app': dlweb
            }
        };
    },
    computed: {
        sitecode () {
            return this.$root.sitecode;
        }
    }
};
</script>
