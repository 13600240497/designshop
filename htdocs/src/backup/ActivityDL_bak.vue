<template>
	<site-layout @publicReady="publicReady" :footLink="1">

		<!-- 列表页头部 -->
		<activityHeaderComponent
			:commonData="commonData"
			:oSearch="oSearch"
			:createActivity="createActivity"
			:getActivityList="getActivities">
		</activityHeaderComponent>

		<el-row class="geshop-port-switch">

			<!-- Tab切换 -->
			<activityTabComponent
				:commonData="commonData"
				:oSearch="oSearch"
				:getActivityList="getActivities">
			</activityTabComponent>

			<!-- 活动列表 -->
			<activityListComponent
				:commonData="commonData"
				:activityForm="activityForm"
				:convertToAPPForm="convertToAPPForm"
				:pageForm="pageForm"
				:publicPageForm="publicPageForm"
				:viewAccessLink="viewAccessLink"
				:getPages="getPages"
				:getActivityList="getActivities">
			</activityListComponent>
		</el-row>

		<!-- 分页 -->
		<activityPaginationComponent
			:oSearch="oSearch"
			:getActivityList="getActivities">
		</activityPaginationComponent>

		<!-- 新建编辑活动 -->
		<createAndEditActivityComponent
			:activityForm="activityForm"
			:activityRules="activityRules"
			:commonData="commonData"
			:getActivityList="getActivities">
		</createAndEditActivityComponent>

		<!-- 新增编辑子页面 -->
		<createAndEditPageComponent
			:pageForm="pageForm"
			:pageRules="pageRules"
			:publicPageForm="publicPageForm"
			:publicPageRules="publicPageRules"
			:commonData="commonData"
			@getPages="getPages">
		</createAndEditPageComponent>

		<!-- 查看访问链接 -->
		<viewAccessLinkComponent
			:viewAccessLink="viewAccessLink">
		</viewAccessLinkComponent>

		<!-- 查看二维码 -->
		<viewQrCodeComponent
			:commonData="commonData">
		</viewQrCodeComponent>

		<!-- WEB转APP -->
		<convertToAPPComponent
			:commonData="commonData"
			:convertToAPPForm="convertToAPPForm"
			:convertToAPPRules="convertToAPPRules">
		</convertToAPPComponent>

		<!-- WEB转APP跳转 -->
		<convertRedirectComponent
			:convertToAPPForm="convertToAPPForm">
		</convertRedirectComponent>

	</site-layout>
</template>

<script>
import siteLayout from './layouts/Layout.vue'
import activityHeaderComponent from './activityDL/activityHeader.vue'
import viewAccessLinkComponent from './activityDL/viewAccessLink.vue'
import createAndEditActivityComponent from './activityDL/createAndEditActivity.vue'
import createAndEditPageComponent from './activityDL/createAndEditPage.vue'
import viewQrCodeComponent from './activityDL/viewQrCode.vue'
import activityListComponent from './activityDL/activityList.vue'
import convertToAPPComponent from './activityDL/convertToAPP.vue'
import activityTabComponent from './activityDL/activityTab.vue'
import activityPaginationComponent from './activityDL/activityPagination.vue'
import convertRedirectComponent from './activityDL/convertRedirect.vue'

import {
	DL_getActivityList,
	DL_getPageList,
	DL_deleteActivity,
	DL_verifyActivity,
	DL_lockingActivity,
	DL_refreshSite,
	DL_refreshSelete,
	DL_deletePage,
	getLangKeyList,
	DL_getAppActivityList,
	DL_getMActivityList,
	DL_getAccessLink,
	DL_actReleased
} from '../plugin/api'
import { getCookie, uniqueArray } from '../plugin/mUtils'
import bus from '../store/bus-index.js'
import '../../resources/stylesheets/activityManagement.css'
import '../../resources/stylesheets/icon.css'
import '../../resources/fonts/svg-fonts/style.css'

export default {
	components: {
		siteLayout,
		activityHeaderComponent,
		viewAccessLinkComponent,
		createAndEditActivityComponent,
		createAndEditPageComponent,
		viewQrCodeComponent,
		activityListComponent,
		convertToAPPComponent,
		activityTabComponent,
		activityPaginationComponent,
		convertRedirectComponent
	},
	data() {
		return {
			// 共用字段
			commonData: {
				supportLangs: [],
				editSupportLangs: [],
				places: [],
				editPlaces: [],
				submitLoading: false,
				expandRowKeys: [],
				isDetailActive: false,
				pagePlaces: [],
				currentLanguage: '', //en
				firstLanguage: '',
				langList: [],
				share_entrance: [],
				pickerOptions1: {
					disabledDate(time) {
						let currentDate = new Date(),
							year = currentDate.getFullYear(),
							month = currentDate.getMonth() + 6,
							day = currentDate.getDate(),
							hours = currentDate.getHours(),
							min = currentDate.getMinutes(),
							second = currentDate.getSeconds()
						if (month > 12) {
							month = month - 12
							year += 1
						}
						let lastDateTime = new Date(year + '-' + month + '-' + day + ' ' + hours + ':' + min + ':' + second).getTime()
						return (time.getTime() > lastDateTime) || (time.getTime() < currentDate.getTime() - 86400)
					}
				},
				currentPageRow: {},
				siteInfo: '',
				activityTabName: 'web',
				currentActivityRow: {
					start_time: new Date().getTime() / 1000,
					end_time: new Date().getTime() / 1000
				},
				pageIds: this.pageIds,
				activityList: [],
				options: [],
				permissions: [],
				allSupportLangArrs: [],
				tplId: '',
				urlName: '',
				refreshTime: 0,
				sitePlat: '',
				appActivityList: [],
				appActivities: [],
				mActivityList: [],
				mActivities: [],
				activityDefaultTime: ['00:00:00', '23:59:59'],
			},

			// 搜索
			oSearch: {
				currentPage: 1,
				pageSize: 10,
				total: 0,
				searchWord: '',
				createName: '',
				searchType: '1',
				id: '',
                is_frequently: 0
			},

			// 查看访问链接
			viewAccessLink: {
				title: '活动访问地址',
				dialogLinksVisible: false,
				pageLinks: [],
				tips: '',
				urlID: '',
			},

			// 子页面表单数据
			publicPageForm: {
				place: [''],
				end_time: '',
			},

			// 公共表单page rules
			publicPageRules: {
				place: [{
					required: true,
					message: '请至少选择一个应用端口',
					trigger: 'change'
				}],
				end_time: [{
					required: false,
					message: '请选择时间',
					trigger: 'blur'
				}]
			},

			// 新建编辑活动表单数据
			activityForm: {
				id: '',
				type: '',
				lang: ['en'],
				place: ['web'],
				editPlace: [],
				editSupportLang: [],
				name: '',
				url_name: '',
				description: '',
				range_time: [],
				start_time: '',
				end_time: '',
				dialogTitle: '新增活动',
				status: 1,
				miss_count: 1,
				miss_count_status: 1,
				dialogActivityVisible: false,
				actNameCount: 0,
				actIntroductionCount: 0,
			},

			// 新建编辑活动表单规则
			activityRules: {
				type: [{
					required: true,
					message: '请输入类型',
					trigger: 'change'
				}],
				name: [{
					required: true,
					message: '请输入名称',
					trigger: 'blur'
				},
				{
					max: 100,
					message: '长度不能超过100个字符',
					trigger: 'blur'
				}
				],
				lang: [{
					required: true,
					message: '请至少选择一种语言',
					trigger: 'change'
				}],
				place: [{
					required: true,
					message: '请至少选择一个应用端口',
					trigger: 'change'
				}],
				url_name: [{
					required: true,
					message: '请输入网址',
					trigger: 'blur'
				},
				{
					max: 64,
					min: 3,
					message: '长度在3-64个字符之间',
					trigger: 'blur'
				}
				]
			},

			// 子页面表单数据
			pageForm: {
				id: '',
				title: '',
				keywords: '',
				url_name: '',
				description: '',
				statistics_code: '',
				tpl_id: '0',
				tpl_name: '',
				web_tpl_id: '0',
				web_tpl_name: '',
				app_tpl_id: '0',
				app_tpl_name: '',
				seo_title: '',
				web_status: false,
				web_end_url: '',
				app_status: false,
				share_image: '',
				share_title: '',
				share_desc: '',
				share_link: '',
				share_place: ['FB', 'Twitter', 'Google+'],
				share_places: ['FB', 'Twitter', 'Google+', 'Pinterest', 'Snapchat', 'Messenger', 'Gmail'],
				uploadloading: false,
				uploadpercent: 0,
				data: {},
				refresh_time: 0,
				end_url: '',
				end_time: '',
				dialogTitle: '子页面新增',
				redirect_url: '',
				need_redirect: false,
				dialogPageVisible: false,
				currentSiteUrl: '',
				urlCount: 0,
				codeCount: 0,
				pageIntroductionCount: 0,
				titleCount: 0
			},

			// 子页面表单验证规则
			pageRules: {
				title: [{
					required: true,
					message: '请输入名称',
					trigger: 'blur'
				},
				{
					max: 100,
					min: 1,
					message: '长度在100个字符以内',
					trigger: 'blur'
				}
				],
				seo_title: [{
					required: true,
					message: '请输入SEO标题',
					trigger: 'blur'
				}],
				url_name: [{
					required: true,
					message: '请输入有效url地址',
					trigger: 'blur'
				},
				{
					pattern: /(?!^(\d+|[A-Za-z]+|[-]+)$)^[\w-]{3,64}$/,
					message: '请输入3-64位的英文字母，-，数字的两种及以上组合',
					trigger: 'blur'
				}
				],
				keywords: [{
					required: false,
					message: '有利于SEO优化',
					trigger: 'blur'
				},
				{
					max: 200,
					min: 0,
					message: '长度在200个字符以内',
					trigger: 'blur'
				}
				],
				description: [{
					required: false,
					message: '有利于SEO优化',
					trigger: 'blur'
				},
				{
					max: 200,
					min: 0,
					message: '长度在200个字符以内',
					trigger: 'blur'
				}
				]
			},

			// web转app表单数据
			convertToAPPForm: {
				id: '',
				activity_id: '',
				page_id: '',
				model: '',
				is_group: '',
				source_id: '',
				target_id: '',
				convertLangs: '',
				appActivityList: [],
				appActivities: [],
				appPages: [],
				convertUrl: '',
				dialogConvertAPP: false,
				dialogConvertVisible: false
			},

			// web转app表单验证规则
			convertToAPPRules: {
				activity_id: [{
					required: true,
					message: '请选择活动',
					trigger: 'change'
				}],
				page_id: [{
					required: true,
					message: '请选择页面',
					trigger: 'change'
				}]
			}
		}
	},
	computed: {
		firstLanguage() {
			if (this.commonData.langList.length > 0) {
				var firstLangu = this.commonData.langList[0].key
				return firstLangu
			} else {
				return ''
			}
		}
	},
	watch: {
		'commonData.currentLanguage': function (val) {
			let supportLangs = this.commonData.supportLangs,
				currentLanguage = this.commonData.currentLanguage
			supportLangs.forEach((item, index) => {
				if (item.key == currentLanguage) {
					this.pageForm.currentSiteUrl = item.url
				}
			})
		}
	},
	mounted() {

	},
	methods: {
		/**
		 * @description 获取活动列表
		 */
		async getActivities() {
			let params = {
					pageNo: this.oSearch.currentPage,
					pageSize: this.oSearch.pageSize,
					name: this.oSearch.searchWord,
					create_name: this.oSearch.createName,
					searchType: this.oSearch.searchType,
					id: this.oSearch.id,
                    is_frequently: this.oSearch.is_frequently,
					site_code: `${getCookie('site_group_code')}-${this.commonData.activityTabName}`
				},
				res = await DL_getActivityList(params);
			this.commonData.activityList = res.data.list
			var array = []
			for (var index in this.commonData.activityList) {
				array.push(this.commonData.activityList[index].id)
			}
			this.commonData.pageIds = array
			this.oSearch.total = res.data.pagination.totalCount

			let length = res.data.list.length

			for (index = 0; index < length; index++) {
				if (res.data.list[index].is_lock == 0) {
					res.data.list[index].lock_status = false
				} else if (res.data.list[index].is_lock == 1) {
					res.data.list[index].lock_status = true
				}
			}
		},

		/**
		 * @description 新增活动
		 */
		createActivity() {
			// 当前为新增活动状态
			this.activityForm.status = 1
			this.activityForm.id = ''
			this.activityForm.type = ''
			this.activityForm.lang = ['en']
			this.activityForm.start_time = ''
			this.activityForm.end_time = ''
			this.activityForm.name = ''
			this.activityForm.description = ''
			this.activityForm.range_time = ['', '']
			this.activityForm.actNameCount = 0
			this.activityForm.actIntroductionCount = 0
			this.activityForm.dialogTitle = '新增活动'
			this.activityForm.miss_count = 1
			this.activityForm.miss_count_status = 1
			this.activityForm.site_code = ''
			this.activityForm.place = ['web'] // 新增活动默认选中pc
			let date = new Date()
			this.commonData.activityDefaultTime[0] = date.getHours() + ':' + date.getMinutes() + ':' + date.getSeconds()
			this.activityForm.dialogActivityVisible = true
		},

		/**
		 * @description 获取子页面列表
		 * @param { Number } activity_id - 活动ID
		 */
		async getPages(activityId) {
			let params = {
					activity_id: activityId
				},
				res = await DL_getPageList(params)

			let position, data, pages, isEnglishSetted = false

			pages = res.data.list
			let firstLang = res.data.langList[0].key

			pages.forEach(function (element) {

				element.pageLanguages.forEach(function (item) {
					if (item.lang === firstLang) {
						element.title = item.title
						isEnglishSetted = true
					}
				})

				if (!isEnglishSetted) {
					element.title = element.pageLanguages[0].title
				}
			})

			this.commonData.activityList.forEach(function (element, index) {
				if (element.id == activityId) {
					element.children = pages
					data = element
					position = index
				}
			})

			this.$set(this.commonData.activityList, position, data)
		},

		/**
		 * @description 页面初始化获取语言列表
		 */
		async getSupportLangs() {
			let res = await getLangKeyList()
			this.commonData.allSupportLangArrs = res.data

			let supportLangArrs = []
			for (var key in res.data) {
				res.data[key].forEach(item => {
					supportLangArrs.push(item)
				})
			}
			supportLangArrs = JSON.parse(JSON.stringify(supportLangArrs))
			supportLangArrs = uniqueArray(supportLangArrs, 'name')

			this.commonData.supportLangs = supportLangArrs
			this.commonData.currentLanguage = this.commonData.supportLangs[0].key //设置第一种语言

			supportLangArrs.forEach((item, index) => {
				if (item.key == this.commonData.currentLanguage) {
					this.pageForm.currentSiteUrl = item.url
				}
			})
		},

		publicReady() {
			this.getActivities()
			this.getSupportLangs()
			this.commonData.permissions = JSON.parse(localStorage.getItem('actionPermissions')).data

			bus.$on('giveData', data => {
				this.commonData.siteInfo = data
			})
			this.commonData.sitePlat = this.commonData.siteInfo.site.split('-')[1]
			// 设置当前站点信息
			this.commonData.places = JSON.parse(localStorage.currentSites).sites
		}

	},
	created() {
		var _this = this

		bus.$on('giveData', data => {
			this.commonData.siteInfo = data
		})

		DL_refreshSelete().then(function (res) {
			_this.commonData.options = res.data
		})
	},
}
</script>

<style scoped>
html {
  overflow: hidden !important;
}

.activity-detail-content {
  width: 360px;
}

.activity-detail-created-time {
  padding-bottom: 15px;
  border-top: 1px solid #ebeef5;
  padding-top: 15px;
}

.activity-detail-updated-time {
  border-bottom: 1px solid #ebeef5;
  padding-bottom: 15px;
}

.activity-detail-link {
  color: #409eff;
}

.model-item img {
  max-width: 100%;
  width: 150px;
  height: 150px;
  display: block;
  margin: 10px auto;
}

.el-table tr {
  height: 80px;
}
</style><style lang="less">
.model-box {
  width: 50%;
  float: left;
  text-align: center;

  .el-radio {
    position: relative;
    max-width: 100%;
  }

  .el-radio__input {
    position: absolute;
    right: 20px;
    top: 36px;
  }
}

.model-dialog .el-tabs__content {
  height: 400px;
  overflow-y: scroll;
}

.gs-col-all {
  width: 100% !important;
}

.activityPageDialog {
  .el-form-item {
    width: 500px;
  }

  .el-date-editor {
    width: 100%;
  }
}

.geshop-activity-child-pages:hover .child-pages-hover {
  z-index: 100;
  display: block;
}

.child-pages-hover {
  display: none;
}

.child-page-name .count-tip-box {
  position: absolute;
  right: 458px;
  top: 250px;
  z-index: 100;
}

.gs-col-all .count-tip-box {
  position: absolute;
  right: 65px;
  top: 42px;
}

.child-page-statistical-code .count-tip-box {
  position: absolute;
  right: 8px;
  bottom: 0px;
}

.child-page-introduction .count-tip-box {
  position: absolute;
  right: 0px;
  bottom: 0px;
}

.geshop-new-activities-name .count-tip-box {
  position: absolute;
  top: 42px;
  right: 10px;
}

.geshop-new-activities-introduction .count-tip-box {
  position: absolute;
  top: 101px;
  right: 10px;
}

.geshop-new-activities-lang .el-form-item__error {
  position: absolute;
  top: 0;
  left: 44px;
}

.geshop-new-activities-place .el-form-item__error {
  position: absolute;
  top: 0;
  left: 70px;
}

.geshop-activity-lists .has-gutter th {
  background-color: #f4f4f4 !important;
  padding: 8px 0px !important;
}

.geshop-activity-lists .el-table__header-wrapper {
  height: 40px !important;
}

.geshop-form-inline {
  display: block;
  height: 40px;
  border-radius: 4px;
  position: absolute;
  right: 24px;
  top: 16px;
}

.input-with-select {
	width: 200px;
}
.input-with-select .el-select .el-input {
	width: 130px;
}

.geshop-activity-lists .geshop-activity-child-pages .child-pages-hover .icon-geshop-decorate,
.geshop-activity-lists .geshop-activity-child-pages .child-pages-hover .icon-geshop-online,
.geshop-activity-lists .geshop-activity-child-pages .child-pages-hover .icon-geshop-offline,
.geshop-activity-lists .geshop-activity-child-pages .child-pages-hover .icon-geshop-search,
.geshop-activity-lists .geshop-activity-child-pages .child-pages-hover .icon-geshop-mobile {
	position: static;
}

.geshop-activity-lists .geshop-activity-child-pages .child-pages-hover .child-page-mask-inner {
	display: flex;
	align-items: center;
	justify-content: space-around;
	height: 100%;
}
</style>
