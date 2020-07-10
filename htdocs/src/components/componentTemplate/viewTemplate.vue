<template>
  <el-dialog title="查看模板" class="geshop-page-template" :visible.sync="viewModel.visible" :width="viewModel.sideWidth" @close="viewModelClose">
    <el-row v-loading="publicData.pageLoading">
      <el-col :span="24" class="imgPreview text-center">
        <iframe frameborder="0" :src="viewModel.src" class="iframePreview"></iframe>
      </el-col>
    </el-row>
  </el-dialog>
</template>

<script>
export default {
  props: {
    publicData: {
      type: Object
    },
    viewModel: {
      type: Object
    },
    viewModelClose: {
      type: Function
    },
    
  },
  data() {
    return {
      
    }
  },
  methods: {

  }
}
</script>

<style lang="less" scoped>
  .iframePreview {
    border: 0;
    width: 100%;
    height: 600px;
  }
  .prewimg{
    width: 300px;
    height: 300px;
    overflow: scroll;
  }
</style>
