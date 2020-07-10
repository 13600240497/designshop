<template>
    <!-- PC转M成功 -->
    <el-dialog
        :title="dialog_title"
        :visible.sync="visible"
        :close-on-click-modal="false"
        :close-on-press-escape="false"
        width="30%">
        <span><img src="/resources/images/moblie.png" alt="" style="display:block;margin-left:50%;transform: translateX(-50%);"></span>
        <p style="text-align:center;font-size:16px">已转换完成</p>
        <p style="text-align:center;font-size:14px;color:black">{{ dialog_tips }}</p>
        <span slot="footer" class="ToM">
            <a :href="url" target="_BLANK">进入{{target_platform}}端装修页面</a>
        </span>
    </el-dialog>
</template>

<script>
export default {
    data () {
        return {
            visible: false,
            type: 0, // 0=PC转M，1=M转APP
            url: '',
        }
    },
    computed: {
        dialog_title () {
            return this.type == 0 ? '转换成M端活动' : '转换成APP端活动';
        },
        dialog_tips () {
            return this.type == 0 ? '您可点击下方按钮进入M端装修页查看' : '您可点击下方按钮进入APP端装修页查看';
        },
        target_platform () {
            return this.type == 0 ? 'M' : 'APP'
        }
    },
    methods: {
        /**
         * 打开这个模块 PC转M
         */
        show (data) {
            this.visible = true;
            this.url = data.url;
            this.type = data.type;
        }
    }
}
</script>