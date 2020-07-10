<template>
  <el-dialog :title="activityForm.dialogTitle" :visible.sync="activityForm.dialogActivityVisible" class="geshop-new-activities">
    <el-form :model="activityForm" :rules="activityRules" ref="activityForm">
      <!-- 新增状态应用端口 -->
      <el-form-item label="应用端口" prop="place" class="geshop-new-activities-place" v-if="activityForm.status==1">
        <el-checkbox-group v-model="activityForm.place">
          <!-- <el-checkbox v-for="(item,key) in commonData.places" :disabled="key == activityForm.site_code" :label="key" :key="key">{{item.platform_name }}</el-checkbox> -->
          <el-checkbox v-for="(item,key) in commonData.places" :label="key" :key="key">{{item.platform_name }}</el-checkbox>
        </el-checkbox-group>
      </el-form-item>
      <!-- 编辑状态应用端口 -->
      <el-form-item label="应用端口" prop="place" class="geshop-new-activities-place" v-if="activityForm.status==2">
        <el-checkbox-group v-model="activityForm.editPlace">
          <el-checkbox v-for="(item,key) in commonData.editPlaces" disabled :label="item.code" :key="key">{{ item.name }}</el-checkbox>
        </el-checkbox-group>
      </el-form-item>
      <!-- 新增状态语言 -->
      <el-form-item label="语言" prop="lang" class="geshop-new-activities-lang" v-if="activityForm.status==1">
        <el-checkbox-group v-model="activityForm.lang">
          <el-checkbox v-for="item in commonData.supportLangs" :label="item.key" :key="item.key">{{ item.name }}</el-checkbox>
        </el-checkbox-group>
      </el-form-item>
      <p style="margin:0;color:#b7b7b7;">注：请选择装修需要的最多语种，不同端无此语种，不装修即可</p>
      <!-- 编辑状态语言 -->
      <el-form-item label="语言" prop="lang" class="geshop-new-activities-lang" v-if="activityForm.status==2">
        <el-checkbox-group v-model="activityForm.editSupportLang">
          <el-checkbox v-for="item in commonData.editSupportLangs" disabled :label="item.key" :key="item.key">{{ item.name }}</el-checkbox>
        </el-checkbox-group>
      </el-form-item>
      <el-form-item label="名称" prop="name" class="geshop-new-activities-name" v-on:keyup.native="handleNameKeyup">
        <el-input v-model="activityForm.name"></el-input>
        <span class="count-tip-box">{{ activityForm.actNameCount }}/100</span>
      </el-form-item>

      <el-form-item label="简介" prop="description" class="geshop-new-activities-introduction" v-on:keyup.native="handleIntroductionKeyup">
        <el-input type="textarea" v-model="activityForm.description" :rows="4" placeholder="请简单描述一下这个活动......"></el-input>
        <span class="count-tip-box">{{ activityForm.actIntroductionCount }}/200</span>
      </el-form-item>
      <p style="margin:5px 0 5px 0;color:#b7b7b7;">注：勾选应用端口及语言提交后不可再修改了哦！</p>
      <el-form-item class="geshop-new-activities-btn">
        <el-button @click="resetActivityForm" size="small">取消</el-button>
        <el-button type="primary" @click="handleActivityFormSubmit('activityForm')" size="small" :loading="commonData.submitLoading">确定</el-button>
      </el-form-item>
    </el-form>
  </el-dialog>
</template>

<script>

import { getCookie } from '../../plugin/mUtils'
import { DL_addActivity, DL_updateActivity } from '../../plugin/api'

export default {
  /**
   * props
   * @param { Object } activityForm - 新建编辑活动数据
   * @param { Object } activityRules - 新建编辑活动表单验证规则
   * @param { Object } commonData - 共用数据
   * @param { Function } getActivityList - 获取活动列表方法
   */
  props: ['activityForm', 'activityRules', 'commonData', 'getActivityList'],
  data () {
    return {

    }
  },
  created () {
    
  },
  methods: {
    
    /**
     * 名称 - keyup事件监听
     */
    handleNameKeyup() {
			var data = this.activityForm.name.split('')
			var length = data.length

			if (length > 100) {
				data.splice(100, length - 99)
				this.activityForm.name = data.join('')
			}

			this.activityForm.actNameCount = data.length
    },
    
    /**
     * 简介 - keyup事件监听
     */
    handleIntroductionKeyup() {
			var data = this.activityForm.description.split('')
			var length = data.length

			if (length > 200) {
				data.splice(200, length - 199)
				this.activityForm.description = data.join('')
			}

			this.activityForm.actIntroductionCount = data.length
    },

    /**
     * 重置表单和关闭弹窗
     */
    resetActivityForm() {
      this.$refs['activityForm'].resetFields()
      this.activityForm.dialogActivityVisible = false
      this.commonData.submitLoading = false
		},
		
		/**
		 * 时间转换
		 */
		formatTimestamp (timestamp) {
			return parseInt(timestamp / 1000)
		},

    setActivityTime() {
			let nowDate = new Date()
			let now = nowDate.getTime() - nowDate.getHours() * 3600000 - nowDate.getMinutes() * 60000 - nowDate.getMinutes() * 1000

			if (new Date(this.activityForm.range_time[1]).getDate() <= nowDate.getDate() &&
        new Date(this.activityForm.range_time[1]).getHours() <= nowDate.getHours()) {
				this.$message.error('结束时间不能小于当前时间')

				this.activityForm.range_time = ['', '']
			} else if (new Date(this.activityForm.range_time[1]).getDate() == new Date(this.activityForm.range_time[0]).getDate() &&
        new Date(this.activityForm.range_time[1]).getHours() <= new Date(this.activityForm.range_time[0]).getHours()) {
				this.$message.error('结束时间不能小于开始时间')

				this.activityForm.range_time = ['', '']
			}

			this.activityForm.start_time = this.activityForm.range_time[0]
			this.activityForm.end_time = this.activityForm.range_time[1]
		},
    
    /**
     * 提交
     */
    async handleActivityFormSubmit (formName) {
      
      if (formName == 'activityForm') {
				this.setActivityTime()

				if (this.activityForm.start_time == '' || this.activityForm.end_time == '') {
					this.activityForm.range_time = ''
				}
			}

			this.commonData.submitLoading = true

			this.$refs[formName].validate(async (valid) => {
				if (valid) {
					let params, res
					let activityFormObj = {}
					let platformData = {}

					if (formName == 'activityForm') {
						params = {
							type: this.activityForm.type,
							name: this.activityForm.name,
							description: this.activityForm.description,
							start_time: this.formatTimestamp(this.activityForm.start_time),
							end_time: this.formatTimestamp(this.activityForm.end_time)
						}

						// 新增活动组装数据
						this.activityForm.place.forEach((item) => {
							activityFormObj[item] = {
								type: this.activityForm.type,
								lang: this.activityForm.lang.join(','),
								name: this.activityForm.name,
								description: this.activityForm.description,
								start_time: this.formatTimestamp(this.activityForm.start_time),
								end_time: this.formatTimestamp(this.activityForm.end_time),
								site_code: `${getCookie('site_group_code')}-${item}`
							}
						})

            platformData['platform_list'] = JSON.stringify(activityFormObj)
            
						// 保存分“新增”和“编辑”两种逻辑
						if (this.activityForm.id == '') {
							// 首次提交code返回101时
							if (this.activityForm.miss_count_status > 1) {
								platformData['miss_count'] = ++this.activityForm.miss_count
								res = await DL_addActivity(platformData)
							} else {
								res = await DL_addActivity(platformData)
							}
						} else {
							params.id = this.activityForm.id
							
							res = await DL_updateActivity(params)
						}

						if (res.code == 0) {
							this.getActivityList()
						}
						// code - 101 端口没有相关语言仍要提交状态
						else if (res.code == 101) {
							this.activityForm.miss_count_status = 2
							this.activityForm.miss_count = res.data.miss_count
						}

						if (params.id) {
							this.commonData.expandRowKeys = []
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

