<template>
  <el-dialog :title="viewAccessLink.title" :visible.sync="viewAccessLink.dialogLinksVisible">
    <el-row>
      <el-button v-for="item in viewAccessLink.pageLinks" type="primary" :key="item.lang" @click="redirect(item.page_url)">{{ item.lang_name }}</el-button>
      <p>
        {{ viewAccessLink.tips }}
        <el-button @click="handleRepublish" v-if="viewAccessLink.tips" type="primary">重新发布</el-button>
      </p>
    </el-row>
  </el-dialog>
</template>

<script>
import { DL_actReleased } from '../../plugin/api'

export default {
  /**
   * props
   * @param { Object } viewAccessLink - 组件数据 { title: '', dialogLinksVisible: false, pageLinks: [], tips: '', urlID: '' }
   */
  props: {
    viewAccessLink: {
      type: Object
    },
    republish: {
      type: Function
    }
  },
  data() {
    return {
      
    }
  },
  methods: {

    /**
		 * @description 查看访问链接 - 重新发布
		 */
		async handleRepublish() {
      const fullscreenLoading = this.$loading({
        lock: true,
        text: 'Loading',
        spinner: 'el-icon-loading',
        background: 'rgba(0, 0, 0, 0.8)'
      })
			let params = {
					page_id: this.viewAccessLink.urlID
				},
        res = await DL_actReleased(params)
      
      fullscreenLoading.close()
			this.viewAccessLink.dialogLinksVisible = false
		},
    
    /**
     * 链接跳转
     */
    redirect(url) {
			window.open(url)
		}
  }
}
</script>

