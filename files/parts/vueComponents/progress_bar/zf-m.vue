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
        },
        type: {
            default: 'empty'
        },
        limitType: {
            default: '1'
        }
    },
    computed: {
        percent () {
            const a = parseInt(this.current);
            const b = parseInt(this.total);
            const limitType = this.limitType;
            // 秒杀值为空，[0%,100%] 进度条0%，进度条100%
            if (b <= 0) {
                return this.type && this.type === 'full-100' ? '100%' : '0%';
            } else {
                return limitType == '1' ? parseInt(((b - a) / b) * 100) + '%' : parseInt((a / b) * 100) + '%';
            }
        }
    }
};
</script>

<style lang="less" scoped>
    .geshop-progress-bar {
        width: 100%;
        position: relative;
        height: 8 / 75rem;
        line-height: 8 / 75rem;
        overflow: hidden;
        background-color: #EDEDED;
        border-radius: 36 / 75rem;

        span {
            position: relative;
            display: block;
            height: 8 / 75rem;
            line-height: 8 / 75rem;
            border-radius: 36 / 75rem;
            background-color: #333;
        }
    }
</style>
