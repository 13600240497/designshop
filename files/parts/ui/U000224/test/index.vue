<template>
    <div class="geshop-u000224-test-body">
        test node
    </div>
</template>

<script>

// 异步获取数据
async function asyncData () {
    return new Promise((resolve, reject) => {
        setTimeout(() => {
            resolve([2, 2, 2, 2, 2]);
        }, 100);
    });
};

export default {
    // 自定义异步调用数据函数
    asyncData,
    data () {
        return {};
    }
};
</script>

<style lang="less" scoped>
    h1 {
        display: none;
    }
</style>
