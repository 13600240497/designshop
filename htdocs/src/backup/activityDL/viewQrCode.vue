<template>
  <el-dialog :visible.sync="commonData.isDetailActive" width="360px">
    <el-card>
      <div slot="header" class="text-center">{{ commonData.currentActivityRow.name }}</div>
      <div>
        <el-row v-if="Boolean(commonData.currentActivityRow.qrcode) || Boolean(commonData.currentActivityRow.preview)">
          <el-col :span="24">
            <h3>活动预览地址：</h3>
          </el-col>
          <el-col :span="24" class="text-center" v-if="Boolean(commonData.currentActivityRow.qrcode)"><img alt="二维码" :src="commonData.currentActivityRow.qrcode" width="120"></el-col>
            <el-col :span="24" class="text-center" v-if="Boolean(commonData.currentActivityRow.preview)">
              <a :href="commonData.currentActivityRow.preview" class="activity-detail-link">{{ commonData.currentActivityRow.name }}</a>
            </el-col>
        </el-row>
      </div>
    </el-card>
  </el-dialog>
</template>

<script>
export default {
  props: {
    commonData: {
      type: Object
    }
  },
  data () {
    return {

    }
  },
  methods: {

  }
}
</script>

