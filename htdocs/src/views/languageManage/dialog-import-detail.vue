<template>
    <a-modal
        class="geshop-modal geshop-modal-import-detail"
        width="900px"
        title="上传结果"
        :visible="visible"
        okText="确定"
        cancelText="取消"
        @ok="handleOk"
        @cancel="handleOk">

        <p> {{ message }} </p>
        <p v-if="fail_data">失败文件：<a href="javascript:;" @click="handleDownLoad">点击下载失败文件</a></p>
        <p>
            <a-button @click="handleReset">继续上传</a-button>
        </p>

        <!-- 模拟表单 -->
        <form
            ref="simForm"
            style="display:none;"
            method="POST"
            action="/base/language-data/export-package">
            <input type="text" name="export_data" :value="fail_data">
        </form>

    </a-modal>
</template>

<script>
export default {
    data () {
        return {
            visible: false, // 是否展示
            message: '',
            fail_data: '', // 失败内容
        };
    },
    methods: {
        /**
         * 展示结果弹窗
         * @param {string} res.message 错误提示信息
         * @param {string} res.data 错误的数据
         */
        show (res) {
            this.visible = true;
            this.message = res.message;
            this.fail_data = res.data;
        },

        /**
         * 确认按钮
         */
        handleOk () {
            this.visible = false;
        },

        /**
         * 取消按钮
         */
        handleReset () {
            this.visible = false;
            this.$emit('reset');
        },

        /**
         * 下载失败内容
         */
        handleDownLoad () {
            this.$refs.simForm.submit();
        }
    }
}
</script>

