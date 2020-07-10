<template>
    <design-dialog
        :width="400"
        title="二维码"
        :visible="visible"
        @isOk="handle_confirm"
        @isCancel="handle_cancel">

        <div class="dialog-home-page-qrcode">
            <img :src="qrcode" alt="" class="qrcode">
        </div>

    </design-dialog>
</template>

<script>

export default {
    data () {
        return {
            visible: false,
            title: '',
            qrcode: '',
        }
    },

    methods: {
        /**
         * 打开弹窗
         * @param {number} id Mysql自增ID
         */
        show (title, qrcode) {
            this.visible = true;
            this.title = title;
            this.qrcode = qrcode;
        },
        
        // 确定
        handle_confirm () {
            this.visible = false;
        },

        // 取消
        handle_cancel () {
            this.visible = false;
        }
    }
}
</script>

<style lang="less" scoped>

img.qrcode {
    display: block;
    margin-left: auto;
    margin-right: auto;
}

</style>
