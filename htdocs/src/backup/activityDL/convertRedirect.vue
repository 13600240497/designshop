
<template>
  <el-dialog title="转换成APP端活动" :visible.sync="convertToAPPForm.dialogConvertVisible" width="30%">
    <span><img src="/frontend/geshop/images/moblie.png" alt="" style="display:block;margin-left:50%;transform: translateX(-50%);"></span>
    <p style="text-align:center;font-size:16px">已转换完成</p>
    <p style="text-align:center;font-size:14px；color:black">您可点击下方按钮进入APP端装修页查看</p>
    <span slot="footer" class="ToM">
      <el-button style="text-align:center" type="primary" @click="convertRedirect()">进入APP端装修页面</el-button>
    </span>
  </el-dialog>
</template>

<script>
export default {
  /**
   * @description props
   * @param { Object } convertToAPPForm - WEB转APP表单数据
   */
  props: {
    convertToAPPForm: {
      type: Object
    }
  },
  methods: {
    /**
     * @description WEB转APP后跳转
     */
    convertRedirect() {
			window.location.href = this.convertToAPPForm.convertUrl
		},
  }
}
</script>

