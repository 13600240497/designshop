<template>
	<site-layout @publicReady="publicReady">
		<el-row :span="24" class="geshop-pagetemplate-tit">
			<span class="geshop-pagetpl-title">组件模板管理</span>
		</el-row>
		<!-- 头部操作栏 start -->
		<headerComponent 
			:search="search"
			:publicData="publicData"
			:componentList="componentList"
			:getComponentList="getComponentList"
			:getPageUiTemplateList="getPageUiTemplateList"></headerComponent>
		<!-- 头部操作栏 end -->
		
		<!-- 列表 start -->
		<listComponent 
			:templateList="templateList"
			:editTemplate="editTemplate"
			:deleteTemplate="deleteTemplate"
			:seeTemplate="seeTemplate"></listComponent>
		<!-- 列表 end -->

		<!-- 分页 start -->
		<paginationComponent 
			:publicData="publicData"
			:handleCurrentChange="handleCurrentChange"></paginationComponent>
		<!-- 分页 end -->

		<!-- 编辑模板 start -->
		<editTemplateComponent 
			:form="form"
			:publicData="publicData"
			:getPageUiTemplateList="getPageUiTemplateList"></editTemplateComponent>
		<!-- 编辑模板 end -->
		
		<!-- 模板 start -->
		<viewTemplateComponent 
			:publicData="publicData"
			:viewModel="viewModel"
			:viewModelClose="viewModelClose"></viewTemplateComponent>
		<!-- 模板 end -->
	</site-layout>
</template>

<script>
import siteLayout from './layouts/Layout.vue'
import headerComponent from './componentTemplate/header.vue'
import listComponent from './componentTemplate/list.vue'
import editTemplateComponent from './componentTemplate/editTemplate.vue'
import viewTemplateComponent from './componentTemplate/viewTemplate.vue'
import paginationComponent from './componentTemplate/pagination.vue'
import { DL_getUiComponentList, DL_getPageUiTplList, DL_deletePageUiTplList } from '../plugin/api'
import { getCookie } from '../plugin/mUtils'
import '../../resources/stylesheets/PageTemplate.css'

export default {
	components: { 
		siteLayout, 
		headerComponent, 
		listComponent, 
		editTemplateComponent, 
		viewTemplateComponent, 
		paginationComponent 
	},
	data () {
		return {
			publicData: {
				dialogVisible: false,
				submitLoading: false,
				total: '',
				pageLoading: false,
				templateTabName: 'web'
			},
			search: {
				uiKey: 0,
				type: '0',
				place: '1',
				currentPage: 1,
      },			
			form: {
				id: null,
				name: '',
				view_type: '1'
      },
			templateList: [],
			componentList: [],
			viewModel: {
				visible: false,
				html: '',
				sideType: 'web',
				sideWidth: '80%',
				src: ''
			}
		}
	},

	computed: {
		is_super () {
			return localStorage.getItem('isSuper')
		},
		isLeader () {
			return localStorage.getItem('isLeader')
		},
		userName () {
			return localStorage.getItem('userName')
		}
	},

	created () {
		// 获取组件列表
		this.getComponentList()
	},

	methods: {
		/**
		 * @description 获取组件模板列表
		 * @param { String } site_code 站点简码
		 * @param { Int } view_type
		 * @param { Int } place_type
		 * @param { Int } ui_key
		 * @param { Int } pageNo
		 * @param { Int } pageSize
		 */
		async getPageUiTemplateList () {
			let params = {
					ui_key: this.search.uiKey,
					view_type: this.search.type,
					place_type: this.search.place,
					pageNo: this.search.currentPage,
					pageSize: 10,
					site_code: `${getCookie('site_group_code')}-${this.publicData.templateTabName}`
				},
				res = await DL_getPageUiTplList(params)

			this.templateList = res.data.list
			this.publicData.total = res.data.totalCount
		},
		
		resetForm (formName) {
			this.$refs[formName].resetFields()
			this.publicData.dialogVisible = false
			this.publicData.submitLoading = false
		},

		/**
		 * @description 分页
		 */
		handleCurrentChange (currentPage) {
			this.search.currentPage = currentPage
			this.getPageUiTemplateList()
		},

		editTemplate (row) {
			this.form.id = row.id
			this.form.name = row.name
			this.form.view_type = String(row.view_type)
			this.publicData.dialogVisible = true
		},

		/**
		 * @description 获取组件列表
		 */
		async getComponentList (place) {
			let params = { 
					site_code: `${getCookie('site_group_code')}-${this.publicData.templateTabName}` 
				}, 
				res = await DL_getUiComponentList(params)
			if (res.code == 0) {
				// 如果有传place字段（应用环境）
				if (place) {
					this.componentList = res.data.filter(item => item.place == place)
				} else {
					this.componentList = res.data
				}
				// 如果有列表则取第1项
				if (this.componentList[0]) {
					this.search.uiKey = this.componentList[0]['key']
				} else {
					this.search.uiKey = ''
				}
				// 获取组件模板列表
				this.getPageUiTemplateList()
			} else {
				this.componentList = []
			}
    },
		
		deleteTemplate (id) {
			this.$confirm('确定要删除该页面模板？', '提示', {
				confirmButtonText: '确定',
				cancelButtonText: '取消',
				type: 'warning'
			}).then(async () => {
				let params = {
					id: id
				}

				let res = await DL_deletePageUiTplList(params)

				if (res.code == 0) {
					this.$message({
						type: 'success',
						message: '删除成功'
					})
					this.getPageUiTemplateList()
				}
			}).catch(() => {
				this.$message({
					type: 'info',
					message: '已取消删除'
				})
			})
		},

		/**
		 * @description 查看模板
		 */
		seeTemplate (row) {
			this.viewModel.visible = true
			this.publicData.pageLoading = true
			this.viewModel.src = row.preview_url || ''

			let sideType = row.platform_name.toLowerCase(), sideWidth
			if (sideType != 'web') {
				sideWidth = '400px'
			} else {
				sideWidth = '80%'
			}
			this.viewModel.sideType = sideType
			this.viewModel.sideWidth = sideWidth
			this.publicData.pageLoading = false
		},

		viewModelClose () {
			this.viewModel.visible = false
			this.viewModel.html = ''
			this.viewModel.src = ''
		},
		
		publicReady () {
			
		}
	}
}
</script>


