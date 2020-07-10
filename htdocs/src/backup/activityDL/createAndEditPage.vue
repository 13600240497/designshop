<template>
  <div>
    <el-dialog :title="pageForm.dialogTitle" :visible.sync="pageForm.dialogPageVisible" class="geshop-new-child-page" @close="resetActivityForm">
      <el-form :model="publicPageForm" :rules="publicPageRules" ref="publicPageForm">
        <el-form-item label="已选应用端口" prop="place" class="gs-col-all child-page-place">
          <el-checkbox-group v-model="publicPageForm.place">
            <el-checkbox v-for="(item,key) in commonData.pagePlaces" :label="item.code" disabled :key="key">{{ item.name }}</el-checkbox>
          </el-checkbox-group>
        </el-form-item>
        <el-form-item label="下线时间" prop="end_time" class="gs-col-all" style="margin:0px 0px 10px">
          <el-date-picker v-model="publicPageForm.end_time" type="datetime" :disabled="commonData.currentLanguage != commonData.firstLanguage" v-on:change="setChildEndTime" :picker-options="commonData.pickerOptions1" value-format="timestamp"></el-date-picker>
        </el-form-item>
      </el-form>

      <el-tabs type="card" @tab-click="handleTabClick" v-model="commonData.currentLanguage">
        <el-tab-pane v-for="item in commonData.langList" :label="item.name" :name="item.key" :key="item.key"></el-tab-pane>
      </el-tabs>

      <el-form :model="pageForm" :rules="pageRules" ref="pageForm" class="geshop-new-child-page-title">
        <el-form-item label="专题活动名称" prop="title" class="gs-col-all">
          <el-input v-model="pageForm.title" @change="updatePageTitle" v-on:keyup.native="handleTitleKeyup"></el-input>
        </el-form-item>
        <el-form-item label="自定义url" prop="url_name" class="gs-col-all child-page-url">
          <label class="current-site-url">{{ pageForm.currentSiteUrl }}/</label>
          <el-input v-model="pageForm.url_name" @change="updatePageUrlName" v-on:keyup.native="handleUrlKeyup" style="max-width: 745px;"></el-input>
          <span class="count-tip-box">{{ pageForm.urlCount }}/64</span>
          <label>.html</label>
        </el-form-item>
        <el-form-item label="WEB活动结束后跳转链接" v-if="pageForm.web_status" prop="web_end_url" class="child-page-link">
          <el-row :gutter="20">
            <el-col :span="15" style="width:400px;">
              <el-input v-model="pageForm.web_end_url" placeholder="请输入url链接" @change="handleWebUrlKeyup"></el-input>
            </el-col>
            <el-col class="child-page-note">
              <span>备注：不填默认为跳转至首页</span>
            </el-col>
          </el-row>
        </el-form-item>
        <el-form-item label="WEB端页面模板" prop="model" v-if="!Boolean(pageForm.id) && pageForm.web_status" class="child-page-model">
          <el-button type="primary" size="small" :disabled="Boolean(pageForm.id)" @click="handleModelTempSelect('web')">选择模板</el-button>
          <el-tag type="info">{{ pageForm.web_tpl_name }}</el-tag>
        </el-form-item>
				<el-form-item label="APP端页面模板" prop="model" v-if="!Boolean(pageForm.id) && pageForm.app_status" class="child-page-model">
          <el-button type="primary" size="small" :disabled="Boolean(pageForm.id)" @click="handleModelTempSelect('app')">选择模板</el-button>
          <el-tag type="info">{{ pageForm.app_tpl_name }}</el-tag>
        </el-form-item>

        <el-form-item label="SEO标题" prop="seo_title" class="child-page-keywords">
          <el-input v-model="pageForm.seo_title" @change="updatePageSeoTitle" placeholder=""></el-input>
        </el-form-item>
        <el-form-item label="SEO关键字" prop="keywords" class="child-page-keywords">
          <el-input v-model="pageForm.keywords" @change="updatePageKeywords" placeholder="有利于SEO优化"></el-input>
        </el-form-item>
        <el-form-item label="统计代码" prop="statistics_code" class="child-page-statistical-code">
          <el-input type="textarea" v-model="pageForm.statistics_code" v-on:keyup.native="handleCodeKeyup" :rows="4" @change="updatePageStatisticsCode"></el-input>
          <span class="count-tip-box">{{ pageForm.codeCount }}/500</span>
        </el-form-item>
        <el-form-item label="SEO简介" prop="description" class="child-page-introduction">
          <el-input type="textarea" v-model="pageForm.description" v-on:keyup.native="handleDescriptionKeyup" :rows="4" @change="updatePageDescription" placeholder="有利于SEO优化"></el-input>
          <span class="count-tip-box">{{ pageForm.pageIntroductionCount }}/200</span>
        </el-form-item>
        <!-- 分享模块 start -->
        <div class="share-container" style="clear:both">
          <el-form-item style="clear:both;">
            <h3 style="border-bottom:1px solid #EEE;">导航分享信息</h3>
          </el-form-item>
          <el-form-item label="分享入口" prop="share_place" class="geshop-new-activities-place">
            <el-checkbox-group v-model="pageForm.share_place" @change="handleSharePlaceChange">
              <el-checkbox v-for="item in pageForm.share_places" :label="item" :key="item">{{ item }}</el-checkbox>
            </el-checkbox-group>
          </el-form-item>
          <el-form-item label="分享图片(请上传158px*158px的图片)" prop="share_image" class="child-page-keywords" style="position:relative;">
            <el-upload
              action="/component/index/upload-logo"
              name="files"
              style="position:absolute;top:41px;z-index:9;"
              accept="image/jpg,image/jpeg,image/png"
              :on-progress="handleUploadProgress"
              :on-success="handleUploadSuccess"
              :on-exceed="handleUploadExceed"
              :on-error="handleUploadError"
              :show-file-list="false"
              :before-upload="handleBeforeUpload"
            >
              <el-button class="el-icon-picture" :plain="true"></el-button>
            </el-upload>
            <el-input v-model="pageForm.share_image" @change="updateShareImage" style="padding-left:50px;box-sizing: border-box;" placeholder=""></el-input>
          </el-form-item>
          <el-progress :percentage="pageForm.uploadpercent" v-if="pageForm.uploadloading"></el-progress>
          <el-form-item label="分享标题" prop="share_title" class="child-page-keywords">
            <el-input v-model="pageForm.share_title" @change="updateShareTitle" placeholder=""></el-input>
          </el-form-item>
          <el-form-item label="分享描述" prop="share_desc" class="child-page-keywords">
            <el-input v-model="pageForm.share_desc" @change="updateShareDesc" placeholder=""></el-input>
          </el-form-item>
          <el-form-item label="分享链接(请不要使用下划线“_”)" prop="share_link" class="child-page-keywords">
            <el-input v-model="pageForm.share_link" @change="updateShareLink" placeholder=""></el-input>
          </el-form-item>
        </div>
        <!-- 分享模块 end -->
        <el-form-item class="child-page-btns" style="clear:both">
          <el-button @click="resetActivityForm" size="small">取消</el-button>
          <el-button type="primary" @click="handlePageFormSubmit('pageForm')" size="small" :loading="commonData.submitLoading">确定</el-button>
        </el-form-item>
      </el-form>
    </el-dialog>

    <!-- 页面模板选择 start -->
    <pageTemplateComponent
      ref="pageTemplateComponent"
      :commonData="commonData"
      :pageForm="pageForm">
		</pageTemplateComponent>
    <!-- 页面模板选择 end -->
  </div>
</template>

<script>
import pageTemplateComponent from './pageTemplate.vue'
import { DL_batchAddPage, DL_batchEditPage } from '../../plugin/api'

export default {
  /**
   * @desc 组件props
   * @param { Object } pageForm - 新建编辑子页面数据
   * @param { Object } pageRules - 新建编辑活动表单验证规则
   * @param { Object } publicPageForm - 新建编辑子页面数据
   * @param { Object } publicPageRules - 新建编辑活动表单验证规则
   * @param { Object } commonData - 共用数据
   * @param { Function } getPages - 获取子页面列表
   * @param { Function } handleModelTempSelect - 子页面模板选择
   */
  props: {
    pageForm: {
      type: Object
    },
    pageRules: {
      type: Object
    },
    publicPageForm: {
      type: Object
    },
    publicPageRules: {
      type: Object
    },
    commonData: {
      type: Object
    }
  },
  components: {
		pageTemplateComponent
	},
  data () {
    return {

    }
  },
  created () {

  },
  methods: {

    /**
     * 语言切换
     */
    handleTabClick() {

      this.$refs['pageForm'].resetFields()

			let lang = this.commonData.currentLanguage
			this.pageForm.title = this.pageForm.data[lang].title
			this.pageForm.titleCount = this.pageForm.data[lang].title.split('').length
			this.pageForm.keywords = this.pageForm.data[lang].keywords
			this.pageForm.description = this.pageForm.data[lang].description
			this.pageForm.pageIntroductionCount = this.pageForm.data[lang].description.split('').length
			this.pageForm.statistics_code = this.pageForm.data[lang].statistics_code
			this.pageForm.codeCount = this.pageForm.data[lang].statistics_code.split('').length

			//判断当前是否有英语（切换同步）
			if (this.commonData.langList[0].key == 'en') {
				//当前语言不是英语
				if (lang != 'en') {
					//判断当前语言是否为空
					if (this.pageForm.data[lang].url_name == '') {
						this.pageForm.url_name = this.pageForm.data.en.url_name
						this.pageForm.data[lang].url_name = this.pageForm.data.en.url_name
					} else {
						this.pageForm.url_name = this.pageForm.data[lang].url_name //不同语言url
					}
				} else {
					this.pageForm.url_name = this.pageForm.data[lang].url_name //不同语言url
				}
			} else {
				this.pageForm.url_name = this.pageForm.data[lang].url_name //不同语言url
			}

			this.pageForm.urlCount = this.pageForm.data[lang].url_name.split('').length
			this.pageForm.refresh_time = this.refreshTime
			this.pageForm.end_time = this.end_time
			this.pageForm.web_end_url = this.pageForm.data[lang].web_end_url

			// 模板字段
			this.pageForm.tpl_id = this.pageForm.data[lang].tpl_id
			this.pageForm.web_tpl_id = this.pageForm.data[lang].web_tpl_id
			this.pageForm.app_tpl_id = this.pageForm.data[lang].app_tpl_id
			this.pageForm.tpl_name = this.pageForm.data[lang].tpl_name
			this.pageForm.web_tpl_name = this.pageForm.data[lang].web_tpl_name
			this.pageForm.app_tpl_name = this.pageForm.data[lang].app_tpl_name
			this.pageForm.seo_title = this.pageForm.data[lang].seo_title
			this.pageForm.redirect_url = this.pageForm.data[lang].redirect_url

			// 分享字段
			this.pageForm.share_place = this.commonData.share_entrance
			this.pageForm.share_image = this.pageForm.data[lang].share_image
			this.pageForm.share_title = this.pageForm.data[lang].share_title
			this.pageForm.share_desc = this.pageForm.data[lang].share_desc
			this.pageForm.share_link = this.pageForm.data[lang].share_link
			this.pageForm.id = this.commonData.currentPageRow.id ? this.commonData.currentPageRow.id : ''

			if (this.commonData.currentPageRow.activity_id) {
				this.pageForm.activity_id = this.commonData.currentPageRow.activity_id
			}
		},
    setChildEndTime(value) {
			if (value != null) {
				this.publicPageForm.end_time = value
				this.pageForm.end_time = value // end_time为公共数据
			}
    },

    /**
     * 重置表单和关闭弹窗
     */
    resetActivityForm() {
      this.$refs['pageForm'].resetFields()
      this.pageForm.dialogPageVisible = false
      this.commonData.submitLoading = false
    },

    /**
     * @desc 子页面模板选择
     * @param { String } place - 端口
     */
    handleModelTempSelect (place) {
      this.$refs.pageTemplateComponent.handleModelTempSelect(place)
    },

    /**
     * 子页面输入框事件监听
     */
    updatePageTitle(value) {
			this.pageForm.data[this.commonData.currentLanguage].title = value
    },

    handleTitleKeyup() {
			var data = this.pageForm.title.split('')
			var length = data.length

			if (length > 100) {
				data.splice(100, length - 99)
				this.pageForm.title = data.join('')
			}

			this.pageForm.titleCount = data.length
    },

    updatePageUrlName(value) {
			var reg = /(?!^(\d+|[A-Za-z]+|[-]+)$)^[\w-]{3,64}$/
			var result = value.match(reg)
			if (result != null) {
				this.pageForm.data[this.commonData.currentLanguage].url_name = value
			}
    },

    handleUrlKeyup() {
			var data = this.pageForm.url_name.split('')
			var length = data.length

			if (length > 64) {
				data.splice(64, length - 63)
				this.pageForm.url_name = data.join('')
			}
			this.pageForm.urlCount = data.length
    },

    handleWebUrlKeyup(value) {
			this.pageForm.data[this.commonData.currentLanguage].web_end_url = value
		},

    updatePageRedirectUrl(value) {
			this.pageForm.data[this.commonData.currentLanguage].redirect_url = value
    },

    updatePageSeoTitle(value) {
			this.pageForm.data[this.commonData.currentLanguage].seo_title = value
    },

    updatePageKeywords(value) {
			this.pageForm.data[this.commonData.currentLanguage].keywords = value
    },

    handleCodeKeyup() {
			var data = this.pageForm.statistics_code.split('')
			var length = data.length

			if (length > 500) {
				data.splice(499, length - 499)
				this.pageForm.statistics_code = data.join('')
			}
			this.pageForm.codeCount = data.length
    },

    updatePageStatisticsCode(value) {
			this.pageForm.data[this.commonData.currentLanguage].statistics_code = value
    },

    handleDescriptionKeyup() {
			var data = this.pageForm.description.split('')
			var length = data.length

			if (length > 200) {
				data.splice(200, length - 199)
				this.pageForm.description = data.join('')
			}
			this.pageForm.pageIntroductionCount = data.length
    },

    updatePageDescription(value) {
			this.pageForm.data[this.commonData.currentLanguage].description = value
    },

    /**
     * 分享入口表单操作
     */
		handleSharePlaceChange(value) {
			this.commonData.share_entrance = value
			this.pageForm.share_place = value
    },

    /**
     * 图片上传
     */
    handleUploadSuccess(response, file) {
			if (response.code == 0) {
				this.pageForm.data[this.commonData.currentLanguage].share_image = response.data.url
				this.pageForm.share_image = response.data.url
				this.pageForm.uploadpercent = file.percentage
				setTimeout(() => {
					this.pageForm.uploadloading = false
				}, 800)
			} else {
				this.$message(response.data.message)
			}
		},
		handleUploadProgress(event, file, fileList) {
			this.pageForm.uploadloading = true
			let percentage = Math.ceil(Math.random() * 50)
			this.pageForm.uploadpercent = percentage
    },
    handleUploadExceed() {
			this.$message('只允许上传一张图片！')
		},
		handleUploadError() {
			this.$message('文件上传失败！')
    },
    handleBeforeUpload(file) {
			if (['image/jpeg', 'image/png'].indexOf(file.type) == -1) {
				this.$message({
					type: 'info',
					message: '请选择合适的图片格式！'
				})
				return false
			}
			if (file.size >= 3 * 1024 * 1024) {
				this.$message({
					type: 'info',
					message: '请选择合适的图片大小！'
				})
				return false
			}
    },

    updateShareImage(value) {
			this.pageForm.data[this.commonData.currentLanguage].share_image = value
    },
    updateShareTitle(value) {
			this.pageForm.data[this.commonData.currentLanguage].share_title = value
    },
    updateShareDesc(value) {
			this.pageForm.data[this.commonData.currentLanguage].share_desc = value
		},
		updateShareLink(value) {
			this.pageForm.data[this.commonData.currentLanguage].share_link = value
    },

    /**
     * 提交
     */
    async handlePageFormSubmit (formName) {
      this.commonData.submitLoading = true

			this.$refs[formName].validate(async (valid) => {
				if (valid) {
					let params, res
					if (formName == 'pageForm') {

						let oPageForm = {}, oResult = {}

						// 遍历当前语言列表
						this.commonData.langList.forEach((item) => {
							let param = {},
							  key = item['key'], // { key: 'en', name: '英文' }
							  formData = this.pageForm.data[key]

							// 遍历当前端口 - 需要过滤掉“页面模板”和“活动结束跳转链接”之外的所有字段
							this.commonData.pagePlaces.forEach((it) => {
								let k = it['code'] // { code: 'pc', name: 'PC' }
								param[k] = JSON.parse(JSON.stringify(formData))

								// 删除无用字段
								delete param[k].title
								delete param[k].url_name
								delete param[k].seo_title
								delete param[k].keywords
								delete param[k].statistics_code
								delete param[k].description
								delete param[k].redirect_url
								delete param[k].activity_id
								delete param[k].share_place
								delete param[k].share_image
								delete param[k].share_title
								delete param[k].share_desc
								delete param[k].share_link

								// 根据当前端口保留对应字段值
								if (k == 'web') {
									param[k].end_url = param[k].web_end_url // 将web端的end_url重命名
									param[k].tpl_id = param[k].web_tpl_id ? param[k].web_tpl_id : 0
									param[k].tpl_name = param[k].web_tpl_name ? param[k].web_tpl_name : ''
								} else if (k == 'app') {
									param[k].tpl_id = param[k].app_tpl_id ? param[k].app_tpl_id : 0
									param[k].tpl_name = param[k].app_tpl_name ? param[k].app_tpl_name : ''
								}

								// 重命名后删除字段
								delete param[k].web_end_url
								delete param[k].web_tpl_id
								delete param[k].web_tpl_name
								delete param[k].app_tpl_id
								delete param[k].app_tpl_name
							})

							oPageForm[key] = {
								title: this.pageForm.data[key].title,
								url_name: this.pageForm.data[key].url_name,
								seo_title: this.pageForm.data[key].seo_title,
								keywords: this.pageForm.data[key].keywords,
								statistics_code: this.pageForm.data[key].statistics_code,
								description: this.pageForm.data[key].description,
								redirect_url: this.pageForm.data[key].redirect_url,
								share_place: this.commonData.share_entrance,
								share_image: this.pageForm.data[key].share_image,
								share_title: this.pageForm.data[key].share_title,
								share_desc: this.pageForm.data[key].share_desc,
								share_link: this.pageForm.data[key].share_link,
								platform: param
							}

						})


						oResult['end_time'] = this.publicPageForm.end_time / 1000

						let oJson = {}

						for (let key in oPageForm) {
							oJson[key] = oPageForm[key]
						}
						oResult['lang_list'] = JSON.stringify(oJson)

						// 编辑
						if (this.pageForm.id != '') {
							// 编辑子页面传page_id
							oResult['page_id'] = this.pageForm.id
							res = await DL_batchEditPage(oResult)
						}
						// 新增
						else {
							let flag = true
							for (let item in this.pageForm.data) {
								if (this.pageForm.data[item].title == '') {
									this.$message.error('请确认所有语言所有必填项已经填写，再提交！')
									if (flag) {
										flag = false
									}
								}
							}
							if (!flag) {
								this.commonData.submitLoading = false
								return false
							}
							// 新增子页面传activity_id
							oResult['activity_id'] = this.pageForm.activity_id
							res = await DL_batchAddPage(oResult)
						}

						if (res.code == 0) {
              this.$emit('getPages', this.pageForm.activity_id)
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
