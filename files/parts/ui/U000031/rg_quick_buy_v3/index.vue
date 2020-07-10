<template>
    <component :is="template" :pid="pid" :data="indexData"></component>
</template>

<script>
export default {
    props: ['data', 'pid'],
    name: 'index_render',
    data () {
        return {
            indexData: '', // 父级data数据
            template: ''
        };
    },
    created () {
        this.indexData = this.$root.data;
        const goods_tab_from = Number(this.$root.data.goods_tab_from) || 0;
        const template = goods_tab_from === 0 ? 'indexDefault' : 'indexCatId';
        /* indexDefault 同步模板 indexCatId 异步调取+分页模板 */
        this.template = () => import(`./${template}.vue`);
    }
};
</script>
