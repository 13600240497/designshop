<template>
    <div>
        <component
            :is="components[sitecode]"
            :current="current"
            :total="total"
            :goods_number="goods_number"
            :item="item"
            :type="type"
        >
        </component>
    </div>
</template>

<script>
import dlweb from './dl-web.vue';
import rgpc from './rg-pc.vue';
import rgm from './rg-m.vue';
// import dlm from './dl-web.vue';

export default {
    name: 'geshop-progress-text',
    // current 活动剩余库存，total 活动总库存 type 库存显示类型， goods_number 普通库存，item 商品详情
    props: ['current', 'total', 'goods_number', 'type', 'item'],
    data () {
        return {
            components: {
                'zf-pc': dlweb,
                'zf-wap': dlweb,
                'zf-app': dlweb,
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
        }
    }
};
</script>
