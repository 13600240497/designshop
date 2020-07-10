<!-- soldout -->
<template>
    <component
        :is="components[sitecode]"
        :visible="visible"
        :label="sold_value">
    </component>
</template>

<script>
import pc from './pc.vue';
import PcBottom from './pc-bottom.vue';
import m from './m.vue';
import MBottom from './m-bottom.vue';
import rgpc from './rgpc.vue';
import rgm from './rg-m.vue';
import web from './dl-web.vue';
import WebBottom from './dl-web-bottom.vue';

export default {
    name: 'geshop-soldout',
    props: ['visible', 'type'],
    data () {
        return {
            components: {
                'zf-pc': this.type && this.type === 'bottom' ? PcBottom : pc,
                'zf-wap': this.type && this.type === 'bottom' ? MBottom : m,
                'zf-app': this.type && this.type === 'bottom' ? MBottom : m,
                'rg-pc': this.type && this.type === 'bottom' ? PcBottom : rgpc,
                'suk-pc': this.type && this.type === 'bottom' ? PcBottom : rgpc,
                'rg-wap': rgm,
                'suk-wap': rgm,
                'rg-app': rgm,
                'dl-web': this.type && this.type === 'bottom' ? WebBottom : web,
                'dl-app': this.type && this.type === 'bottom' ? WebBottom : web
            }
        };
    },
    computed: {
        sitecode () {
            return window.GESHOP_SITECODE;
        },
        sold_value () {
            return this.$lang('sold_out');
        }
    }
};
</script>
