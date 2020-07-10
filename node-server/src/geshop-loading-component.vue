<template>
    <div class="geshop-loading-component">
        <div class="geshop-loading-compoenent-list">
            <ul>
                <li><span></span></li>
                <li><span></span></li>
                <li><span></span></li>
                <li><span></span></li>
            </ul>
        </div>
    </div>
</template>

<script>
export default {
    props: ['height', 'type'],
    data () {
        return {};
    },
    mounted () {
        console.log(this.type);
        // this.$emit('onLoaded');
    }
};
</script>

<style lang="less" scoped>
.geshop-loading-component {
    position: relative;
    width: 100%;
    left: 0px;
    right: 0px;
    background: #f8f8f8;
}

</style>
