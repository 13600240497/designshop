<template>
    <div>
        <component :is="template" :pid="pid" :data="data"></component>
    </div>
</template>

<script>

export default {
    name: 'geshop_u000164',
    props: ['theme', 'pid', 'data'],
    data () {
        return {
            template: null
        }
    },
    created () {
        this.template = () => import(`./${this.theme}/index.vue`)
    }
}
</script>
