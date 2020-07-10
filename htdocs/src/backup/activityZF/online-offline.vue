<!-- 上下线渠道页面 -->
<template>
  <el-dialog :title="data.title" :visible.sync="data.dialogReleaseVisible">
    <el-row>
      <el-checkbox-group v-model="data.activityPageChannelValue" @change="handleChangeCallback">
        <el-checkbox v-for="(item, index) in data.activityPageChannelList" :key="index" :label="item.code">{{ item.name }}</el-checkbox>
      </el-checkbox-group>
    </el-row>
    <span slot="footer" class="dialog-footer">
      <el-button @click="data.dialogReleaseVisible = false">取 消</el-button>
      <el-button type="primary" @click="submitCallback" element-loading-text="加载中" element-loading-spinner="el-icon-loading" element-loading-background="rgba(0, 0, 0, 0.8)" v-loading.fullscreen.lock="data.fullscreenLoading">确 定</el-button>
    </span>
  </el-dialog>
</template>

<script>
export default {
  props: ['data', 'submitCallback', 'handleChangeCallback'],
  data () {
    return {
      
    }
  },
  created () {
    
  },
  methods: {

  }
}
</script>

