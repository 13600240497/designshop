<template>
    <div class="geshop-dl-soldout" v-if="visible">
        <p>{{label}}</p>
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
        return {};
    }
};
</script>

<style lang="less" scoped>
.geshop-dl-soldout {
    position: absolute;
    left: 0px;
    top: 0px;
    right: 0px;
    bottom: 0px;
    background: rgba(255, 255, 255, 0.75);
    > p {
        width: 140px;
        height: 136px;
        background-size: 100% 100%;
        background-image: url(https://geshopcss.logsss.com/imagecache/geshop/resources/images/dl/goods-out-style.png);
        position: absolute;
        left: 50%;
        z-index: 12;
        font-size: 20px;
        color: #ff4546;
        text-align: center;
        line-height: 136px;
        margin-left: -70px;
        top: 50%;
        margin-top: -68px;
    }
}
</style>
