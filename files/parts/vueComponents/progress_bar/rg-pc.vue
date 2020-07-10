<template>
    <div class="geshop-progress-bar" :style="{ 'background-color': color2 }">
        <span :style="{ 'background-color': color1, 'width': percent }"></span>
    </div>
</template>

<script>
export default {
    name: 'geshop-progress-bar',
    props: {
        color1: {
            default: '#333333'
        },
        color2: {
            default: '#EDEDED'
        },
        current: {
            default: 0
        },
        total: {
            default: 0
        }
    },
    computed: {
        percent () {
            const a = parseInt(this.current);
            const b = parseInt(this.total);
            if (b <= 0) {
                return '0%';
            } else {
                return Math.floor((a / b * 100)) + '%';
            }
        }
    }
};
</script>

<style lang="less" scoped>
    .geshop-progress-bar {
        width: 100%;
        position: relative;
        height: 8px;
        line-height: 8px;
        overflow: hidden;
        background-color: #D8D8D8;
        border-radius: 4px;
        margin-top: 10px;
        margin-bottom: 4px;
        span {
            position: relative;
            display: block;
            width: 100%;
            height: 8px;
            line-height: 8px;
            border-radius: 4px;
            background-color: #333;
        }
    }
</style>
