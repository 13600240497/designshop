<template>
    <div class="geshop-progress-bar" :style="{ 'background-color': total_color }">
        <span :style="{ 'background-color': left_color, 'width': left_pecent }"></span>
    </div>
</template>

<script>
export default {
    name: 'progress-zf-m',
    props: {
        // 剩余颜色
        left_color: {
            default: '#333333'
        },
        // 总颜色
        total_color: {
            default: '#EDEDED'
        },
        // 剩余
        left: {
            default: 0,
            type: Number
        },
        // 总的
        total: {
            default: 0,
            type: Number
        }
    },
    computed: {
        // 计算剩余的百分比
        left_pecent () {
            const a = parseInt(this.left);
            const b = parseInt(this.total);
            // 超卖
            if (a <= -1) {
                return '100%';
            } else {
                const pecent = parseInt((a / b) * 100);
                return pecent + '%';
            }
        }
    }
};
</script>

<style lang="less" scoped>
    .geshop-progress-bar {
        width: 100%;
        position: relative;
        height: 12 / 75rem;
        line-height: 12 / 75rem;
        overflow: hidden;
        background-color: #EDEDED;
        border-radius: 36 / 75rem;

        span {
            position: relative;
            display: block;
            height: 12 / 75rem;
            line-height: 12 / 75rem;
            border-radius: 36 / 75rem;
            background-color: #333;
        }
    }
</style>
