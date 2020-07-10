<template>
  <el-dialog title="转化为APP页面" :visible.sync="convertToAPPForm.dialogConvertAPP">
    <el-form :model="convertToAPPForm" :rules="convertToAPPRules" ref="convertToAPPForm" label-width="100px">
      <el-form-item label="APP活动" prop="activity_id" v-if="convertToAPPForm.is_group==0">
        <el-select v-model="convertToAPPForm.activity_id" @change="getAppPages">
          <el-option v-for="item in convertToAPPForm.appActivities" :label="item.name" :value="item.id" :key="item.id"></el-option>
        </el-select>
      </el-form-item>
      <el-form-item label="APP页面" prop="page_id" v-if="convertToAPPForm.is_group==0">
        <el-select v-model="convertToAPPForm.page_id" placeholder="请选择APP页面">
          <el-option v-for="item in convertToAPPForm.appPages" :label="item.title" :value="item.id" :key="item.id"></el-option>
        </el-select>
      </el-form-item>
      <el-form-item label="选择语言" prop="lang">
        <el-select v-model="convertToAPPForm.lang" placeholder="请选择语言">
          <el-option v-for="item in convertToAPPForm.convertLangs" :label="item.name" :value="item.key" :key="item.key"></el-option>
        </el-select>
      </el-form-item>
      <el-form-item label="选择方式">
        <el-radio-group v-model="convertToAPPForm.model">
          <el-radio label="1">在所选子页面后追加</el-radio>
          <el-radio label="2">覆盖子页面内容</el-radio>
        </el-radio-group>
        <el-alert title="此操作不会删除原APP活动页面内容，仅在页面最后增加。" type="warning" v-if="convertToAPPForm.model == 1" :closable="false"></el-alert>
        <el-alert title="此操作直接覆盖所选子页面的原有装修效果，且不可还原，请慎用此选项。" type="warning" v-if="convertToAPPForm.model == 2" :closable="false"></el-alert>
      </el-form-item>
      <el-form-item>
        <el-button @click="resetActivityForm" size="small">取消</el-button>
        <el-button type="primary" @click="handleConvertAPPFormSubmit('convertToAPPForm')" size="small" :loading="commonData.submitLoading">确定</el-button>
      </el-form-item>
    </el-form>
  </el-dialog>
</template>

<script>
import { DL_convertToAppPage } from '../../plugin/api'

export default {
  /**
   * @desc 组件props
   * @param { Object } commonData - 共用数据
   * @param { Object } convertToAPPForm - WEB转APP表单数据
   * @param { Object } convertToAPPRules - WEB转APP表单验证规则
   */
  props: {
    commonData: { 
      type: Object
    },
    convertToAPPForm: { 
      type: Object
    },
    convertToAPPRules: { 
      type: Object
    }
  },
  data () {
    return {

    }
  },
  methods: {
    /**
     * 重置表单和关闭弹窗
     */
    resetActivityForm() {
      this.$refs['convertToAPPForm'].resetFields()
      this.convertToAPPForm.dialogConvertAPP = false
      this.commonData.submitLoading = false
    },

    /**
     * @description 根据APP活动ID筛选APP子页面
     * @param { Number } activity_id 活动ID
     */
    getAppPages(activity_id) {
			let list = []
			let languages = []

			this.convertToAPPForm.page = ''
			this.convertToAPPForm.appActivityList.forEach(function (element) {
				if (element.id == activity_id) {
					element.pageList.forEach(function (page, index) {
						list.push({
							id: page.page_id,
							title: page.title
						})
						if (index == 0) {
							page.langList.forEach(function (lang) {
								languages.push({
									key: lang.key,
									name: lang.name
								})
							})
						}
					})
				}
			})

			this.convertToAPPForm.convertLangs = languages
			this.convertToAPPForm.appPages = list
		},

    /**
     * @description WEB转APP表单提交
     */
    async handleConvertAPPFormSubmit (formName) {
      this.commonData.submitLoading = true

			this.$refs[formName].validate(async (valid) => {
				if (valid) {
					let params, res
					if (formName == 'convertToAPPForm') {

						params = {
							model: this.convertToAPPForm.model
						}

						let source_id = this.convertToAPPForm.source_id
						let target_id = this.convertToAPPForm.target_id
						if (source_id != 0 && target_id != 0) {
							params.source_id = source_id
							params.target_id = target_id
						} else {
							params.source_id = this.convertToAPPForm.id
							params.target_id = this.convertToAPPForm.page_id
						}

						params.lang = this.convertToAPPForm.lang
						res = await DL_convertToAppPage(params)

						if (res.code == 0) {
							this.convertToAPPForm.convertUrl = res.data.redirectUrl
							this.convertToAPPForm.dialogConvertVisible = true
						} else {
							this.convertToAPPForm.convertUrl = ''
						}
					}

					this.commonData.isDetailActive = false

					if (res.code == 0) {
						this.resetActivityForm()
					} else {
						this.commonData.submitLoading = false
					}
				} else {
					this.commonData.submitLoading = false
				}
			})
    }
  }
}
</script>

