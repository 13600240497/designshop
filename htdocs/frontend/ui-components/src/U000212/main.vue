<template>
    <div>
        <component :is="template" :data="data"></component>
    </div>
</template>

<script>
export default {
    name: 'geshop_u000212',
    props: ['theme', 'data'],
    data() {
        return {
            template: null
        }
    },
    created() {
        this.template = () => import(`./template/${this.theme}.vue`)
    }
}
</script>

<style>

</style>
