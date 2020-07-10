<template>
    <span class="geshop-goods-title rg-ellipsis-1" :class="`is-${$root.platform} is-${site_code}`">
        <slot></slot>
    </span>
</template>

<script>
export default {
    name: 'geshop-goods-title',
    props: {
        value: {
            type: String,
            default: ''
        }
    },
    computed: {
        site_code () {
            return GESHOP_SITECODE;
        }
    }
};
</script>

<style lang="less" scoped>
    .geshop-goods-title {
        font-size: 14px;
        color: #222222;

        a {
            color: inherit
        }

        &.rg-ellipsis-1 {
            display: block;
            height: auto;
            overflow: hidden;
            white-space: nowrap;
            -ms-text-overflow: ellipsis;
            text-overflow: ellipsis;
            box-sizing: border-box;
        }
    }
</style>
