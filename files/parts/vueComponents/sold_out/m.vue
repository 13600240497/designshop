<template>
    <div class="geshop-m-soldout-layer" v-if="visible" :class="site">
        <span>{{ label }}</span>
    </div>
</template>

<script>
export default {
    props: {
        visible: {
            type: Boolean,
            default: false
        },
        label: {
            type: String,
            default: ''
        }
    },
    data () {
        return {
            site: GESHOP_SITECODE.split('-')[0]
        };
    }
};
</script>

<style lang="less" scoped>
    .geshop-m-soldout-layer {
        position: absolute;
        left: 0px;
        top: 0px;
        right: 0px;
        bottom: 0px;
        display: flex;
        align-content: center;
        align-items: center;
        justify-content: center;
        padding: 0px 24 / 75rem;

        &.rg {
            background: rgba(0, 0, 0, .4);

            > span {
                font-size: 36 / 75rem;
                background: rgba(0, 0, 0, 0);
                width: 46%;
                font-weight: normal;
            }
        }

        > span {
            width: 100%;
            padding: 17 / 75rem 24 / 75rem;
            color: #fff;
            text-align: center;
            background: rgba(0, 0, 0, .4);
            font-size: 24 / 75rem;
            border-radius: 60 / 75rem;
            box-sizing: border-box;
            line-height: 1.1em;
            font-weight: bold;
        }
    }
</style>
