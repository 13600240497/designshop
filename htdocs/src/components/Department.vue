<template>
	<site-layout>
		<el-form label-width="100px" inline="" :model="options" ref="searchForm" class="searchForm">
			<el-row>
				<el-form-item label="快速搜索" prop="keyword">
					<el-input v-model="options.keyword" class="quick-search search-inputSelect">
						<el-select filterable clearable v-model="options.field" slot="prepend" placeholder="请选择">
							<el-option label="部门名称" value="name"></el-option>
							<!-- <el-option label="最后修改人" value="update_user"></el-option> -->
						</el-select>
					</el-input>
				</el-form-item>
			</el-row>
			<el-row>
				<el-col :span="12">
					<el-form-item label="部门">
						<el-cascader filterable change-on-select class="department-tree" :options="departmentOpt" :props="prop" v-model="options.department_id" :disabled="is_super !=='1'"></el-cascader>
					</el-form-item>
				</el-col>
				<el-col :span="6" :offset="6" class="text-right">
					<el-button type="primary" size="small" @click="handleLists()">搜索</el-button>
					<el-button type="danger" size="small" @click="resetSearch()">清空</el-button>
				</el-col>
			</el-row>
		</el-form>

		<el-row>
			<el-col :span="24">
				<div class="activity-list-main">
					<div class="activity-list-box">
						<el-row>
							<el-col :span="24">
								<el-table v-loading="loading" :data="dataLists" border :row-style="showFilterTr" @expand-change="handleExpandChange">
									<el-table-column prop="name" label="部门名称" min-width="100">
										<template slot-scope="scope">
											<span v-for="(space, levelIndex) in scope.row._level" :key="levelIndex" class="ms-tree-space"></span>
											<button v-if="toggleIconShow(scope.row)" class="el-button el-button--default el-button--small" @click="toggle(scope.$index)">
												<i v-if="!scope.row._expanded" class="el-icon-plus" aria-hiden="true"></i>
												<i v-if="scope.row._expanded" class="el-icon-minus" aria-hiden="true"></i>
											</button>
											<span v-else>
												<span class="ms-tree-space"></span>
												<span class="ms-tree-space"></span>
											</span>
											<!-- <span v-else-if="index===0" class="ms-tree-space"></span> -->
											<span>{{scope.row.name}}</span>
										</template>
									</el-table-column>
									<el-table-column prop="site_code" label="可访问站点">
										<template slot-scope="scope">
											<span>{{scope.row.site_code | siteLists}}</span>
										</template>
									</el-table-column>
									<el-table-column prop="sys_update_time" label="更新时间">
										<template slot-scope="scope">
											<span>{{ scope.row.sys_update_time | moment('YYYY-MM-DD HH:mm:ss') }}</span>
										</template>
									</el-table-column>
									<!-- <el-table-column prop="update_user" label="最后修改人"></el-table-column> -->
									<el-table-column label="操作" min-width="80" v-if="is_super == '1'">
										<template slot-scope="scope">
											<el-button size="small" @click="editItem(scope.row.id,1)">编辑</el-button>
										</template>
									</el-table-column>
									<el-table-column label="操作" min-width="80" v-if="is_super == '0'">
										<template slot-scope="scope">
											<el-button size="small" @click="editItem(scope.row.id,1)" v-if="permissions.includes('base/department/edit') && scope.row._level !== 0">编辑</el-button>
											<el-button size="small" @click="editItem(scope.row.id,0)" v-else>查看</el-button>
											<!-- <el-button size="small" @click="editItem(scope.row.id,1)" v-if="is_super == '1' || departmentId == scope.row.parent_id">编辑</el-button> -->
											<!-- <el-button size="small" @click="editItem(scope.row.id,0)" v-else>查看</el-button> -->
										</template>
									</el-table-column>
								</el-table>
							</el-col>
						</el-row>
						<el-row>
							<el-col :span="24" class="text-right">
								<el-pagination layout="prev, pager, next" :page-size="options.pageSize" :total="total" @current-change="handleCurrentChange"></el-pagination>
							</el-col>
						</el-row>
					</div>
				</div>
			</el-col>
		</el-row>

		<el-dialog :title="dialogFormInfo.title" :visible.sync="editVisible" @close="closeDialogEvent">
			<el-form :model="dialogForm" :rules="editRules" ref="dialogForm" label-width="80px" :disabled="dialogFormInfo.checkDisable">
				<el-form-item label="部门" required>
					<span>{{dialogForm.department.name}}</span>
				</el-form-item>
				<el-form-item label="数据权限">
					<el-checkbox-group v-if="dialogForm.site && dialogForm.site.length>0" v-model="checkedSites">
						<el-checkbox v-for="(item,index) in dialogForm.site" :key="index" :label="item.site_code" name="site_code">{{item.site_code}}</el-checkbox>
					</el-checkbox-group>
					<span v-else>暂无权限</span>
				</el-form-item>
				<el-form-item>
					<el-button @click="resetForm('dialogForm')" size="small">取消</el-button>
					<el-button type="primary" @click="submitForm('dialogForm')" size="small" :loading="submitLoading">确定</el-button>
				</el-form-item>
			</el-form>
		</el-dialog>
	</site-layout>
</template>

<script>
import siteLayout from './layouts/Layout.vue'
import { DataTransfer } from '../plugin/mUtils'
import { getDepartmentList, getDepartmentPublic, getDepartmentInfo, departmentEdit } from '../plugin/api'

export default {
	components: { siteLayout },
	data: function () {
		return {
			searchWord: '',
			total: 0,
			dataSource: [],
			options: {
				page: 1,
				pageSize: 10,
				status: '',
				field: '',
				keyword: '',
				department_id: [],
				editDate: '',
				update_time_start: '',
				update_time_end: '',
			},
			//部门级联
			defaultIds: [],
			departmentOpt: [],
			prop: {
				value: 'id',
				label: 'name',
				children: 'children'
			},
			//dialog
			isActive: false,
			pageForm: {},
			checkedSites: [],
			dialogForm: {
				department: {},
				site: {}
			},
			dialogFormInfo: {
				title: '查看',
				checkDisable: true
			},
			editVisible: false,
			dialogPageVisible: false,
			editRules: {},
			loading: false,
			submitLoading: false,
			permissions:[]
		}
	},
	computed: {
		dataLists: function () {
			var _this = this
			var data = DataTransfer.treeToArray(_this.dataSource, null, null, _this.defaultExpandAll)
			return data
		},
		checkedSitesAble () {
			let isSuper = localStorage.getItem('isSuper')
			return isSuper == '1' ? false : true
		},
		is_super () {
			return localStorage.getItem('isSuper')
		},
		userName () {
			return localStorage.getItem('userName')
		},
		departmentId () {
			return localStorage.getItem('departmentId')
		}
	},
	created: function () {
		this.permissions = JSON.parse(localStorage.getItem('actionPermissions')).data

		this.handlePublicLine()
	},
	props: {
		defaultExpandAll: {
			type: Boolean,
			default: function () {
				return false
			}
		}
	},
	methods: {
		handleLists (currentPage) {
			if (
				(this.options.keyword && !this.options.field) ||
				(!this.options.keyword && this.options.field)
			) {
				this.$message.warning('关键词必须匹配搜索类型')
				return
			}
			let params = {}
			for (let i in this.options) {
				if (i !== 'editDate') {
					params[i] = this.options[i]
				}
			}
			if (currentPage) {
				params.page = currentPage
			}
			this.loading = true
			getDepartmentList(params).then(res => {
				if (res.code == '0') {
					this.dataSource = res.data.list
				} else {
					this.$message(res.message)
				}
				this.loadingHide()
			}).catch(() => {
				this.loading = false
			})
		},
		handlePublicLine () {
			getDepartmentPublic().then(res => {
				if (res.code == '0') {
					let data = res.data
					if (data.department_ids && data.department_ids.length > 0) {
						this.options.department_id = data.department_ids
						this.defaultIds = data.department_ids
						// this.setDisabled(data.outline, data.department_ids.length);
					}
					this.departmentOpt = data.outline
					this.handleLists()
				} else {
					this.$message(res.message)
				}
			}).catch(() => {
				this.handleLists()
			})
		},
		handleRowClick (row) {
			this.isActive = true

			// set activityForm data
			this.activityForm.id = row.id
			this.activityForm.type = row.type
			this.activityForm.name = row.name
			this.activityForm.description = row.description
			this.activityForm.start_time = row.start_time
			this.activityForm.end_time = row.end_time

			// set pageForm data
			this.pageForm.activity_id = row.id
		},
		handleExpandChange (row) {
			if (!row.children) {
				this.getPages(row.id)
			}
		},
		handleCurrentChange () { },
		async editItem (id, type) {
			if (type == 0) {
				this.dialogFormInfo.title = '查看'
				this.dialogFormInfo.checkDisable = true
			} else if (type == 1) {
				this.dialogFormInfo.title = '编辑'
				this.dialogFormInfo.checkDisable = false
			}
			let res = await getDepartmentInfo({ id: id })
			this.dialogForm = res.data

			var lists = []
			res.data.site.forEach(function (value) {
				if (value.selected == true) {
					lists.push(value.site_code)
				}
			})
			this.checkedSites = lists
			this.editVisible = true
		},
		submitForm (formName) {
			this.$refs[formName].validate(valid => {
				if (valid) {
					var params = {}
					params.data = []
					params.id = this.dialogForm.department.id
					this.checkedSites.forEach(function (v, i) {
						params.data[i] = {}
						params.data[i]['site_code'] = v
					})
					this.submitLoading = true
					departmentEdit(params).then(res => {
						this.submitLoading = false
						if (res.code == 0) {
							this.$message('编辑完成')
							this.handleLists()
						} else {
							this.$message(res.message)
						}
						this.resetFields(formName)
						this.closeDialog(formName)
					}).catch(() => {
						this.submitLoading = false
					})
				}
			})
		},
		resetForm (formName) {
			this.resetFields(formName)
			this.closeDialog(formName)
		},
		resetFields (formName) {
			this.$refs[formName].resetFields()
		},
		closeDialog (formName) {
			if (formName == 'dialogForm') {
				this.editVisible = false
			}
		},
		//显示tr
		showFilterTr (rowObj) {
			var row = rowObj.row
			var show = row._parent
				? row._parent._expanded && row._parent._show
				: true
			row._show = show
			return show ? '' : 'display:none;'
		},
		// 展开下级
		toggle (trIndex) {
			var me = this
			var record = me.dataLists[trIndex]
			record._expanded = !record._expanded
		},
		// 显示层级关系的空格和图标
		spaceIconShow (index) {
			if (index === 0) {
				return true
			}
			return false
		},
		// 点击展开和关闭的时候，图标的切换
		toggleIconShow (record) {
			if (record.children && record.children.length > 0) {
				return true
			}
			return false
		},
		loadingHide () {
			var _this = this
			setTimeout(function () {
				_this.loading = false
			}, 1000)
		},
		closeDialogEvent () {
			this.checkedSites = []
		},
		resetSearch () {
			this.$refs['searchForm'].resetFields()
			this.options.field = ''
			this.options.department_id = this.defaultIds
		},
	},
	filters: {
		siteLists (data) {
			return data.toString()
		}
	}
}
</script>

