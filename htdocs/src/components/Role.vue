<template>
	<site-layout>
		<el-form label-width="80px" inline="" v-model="options" ref="searchForm" class="searchForm">
			<el-row>
				<el-form-item prop="field">
					<el-input v-model="options.keyword" class="quick-search search-inputSelect">
						<el-select filterable clearable="" v-model="options.field" slot="prepend" placeholder="请选择关键字类型" style="width:180px !important;">
							<el-option label="角色名称" value="name"></el-option>
							<el-option label="角色创建人" value="create_user"></el-option>
							<el-option label="最后修改人" value="update_user"></el-option>
						</el-select>
					</el-input>
				</el-form-item>
			</el-row>
			<el-row>
				<el-col :span="4">
					<el-form-item prop="status" class="col-full">
						<el-select filterable v-model="options.status" placeholder="请选择角色状态">
							<el-option v-for="(item, index) in statusOpt" :key="index" :value="index" :label="item"></el-option>
						</el-select>
					</el-form-item>
				</el-col>
				<el-col :span="12" :offset="1">
					<el-form-item prop="editDate">
						<el-date-picker v-model="options.editDate" type="datetimerange" :editable="false" value-format="timestamp" placeholder="选择修改时间"></el-date-picker>
					</el-form-item>
				</el-col>
				<el-col :span="6" :offset="1" class="text-right">
					<el-button type="primary" size="small" @click="handleSearch()">搜索</el-button>
					<el-button type="danger" size="small" @click="resetForm('searchForm')">清空</el-button>
					<el-button type="danger" size="small" icon="el-icon-plus" @click="addRole" v-if="is_super == '1' || isLeader == '1'">新增</el-button>
				</el-col>

			</el-row>
		</el-form>
		<el-row>
			<el-col :span="24">
				<div class="activity-list-main">
					<div class="activity-list-box">
						<el-row>
							<el-col :span="24">
								<template>
									<el-tabs v-model="roleTabName" type="card" @tab-click="handleRoleTabClick">
										<el-tab-pane v-for="(item,key) in places" :label="item.platform_name" :name="key" :key="key"></el-tab-pane>
									</el-tabs>
								</template>
								<el-table :data="dataSource" @expand-change="handleExpandChange" v-loading="loading">
									<el-table-column prop="name" label="角色名称"></el-table-column>
									<!-- <el-table-column prop="site_code" label="站点"></el-table-column> -->
									<el-table-column prop="privileges_num" label="权限数量"></el-table-column>
									<el-table-column prop="user_num" label="用户数量"></el-table-column>
									<el-table-column prop="create_name" label="角色创建人"></el-table-column>
									<!-- <el-table-column prop="update_user" label="最后修改人"></el-table-column> -->
									<el-table-column prop="update_time" label="最后修改时间">
										<template slot-scope="scope">
											<span>{{ Number(scope.row.update_time) | moment('YYYY-MM-DD HH:mm:ss') }}</span>
										</template>
									</el-table-column>
									<el-table-column label="角色状态">
										<template slot-scope="scope">
											<span v-if="scope.row.status == 1">启用</span>
											<span v-else-if="scope.row.status == 0">禁用</span>
										</template>
									</el-table-column>
									<el-table-column label="操作" min-width="225">
										<template slot-scope="scope">
											<el-button size="small" v-if="permissions.includes('base/role/info') && userName == scope.row.create_user" @click="editItem(scope.row)">编辑</el-button>
											<el-button size="small" v-else @click="editItem(scope.row,0)">查看</el-button>
											<el-button size="small" @click="detailItem(scope.row)">查看权限拥有明细</el-button>
										</template>
									</el-table-column>
								</el-table>
							</el-col>
						</el-row>

						<el-row v-if="total > options.pageSize">
							<el-col :span="24" class="text-right">
								<el-pagination layout="prev, pager, next" :page-size="options.pageSize" :total="total" :current-page.sync="options.pageNo"
								  @current-change="handleCurrentChange"></el-pagination>
							</el-col>
						</el-row>
					</div>

				</div>
			</el-col>
		</el-row>
		<!-- 角色编辑/新增 -->
		<el-dialog :title="dialogFormInfo.title" :visible.sync="dialogVisible" class="role-edit" width="80%" :center="dialogFormInfo.center"
		  @close="dialogFormClose">
			<el-form :model="childOption.option" :rules="editRules" ref="dialogForm" label-width="80px" :disabled="dialogFormInfo.disabled">
				<el-row>
					<el-col>
						<el-form-item label="角色名" prop="name" :rules="[{required: true, message: '该项不能为空'}]">
							<el-input v-model="childOption.option.name"></el-input>
						</el-form-item>
						<el-form-item label="角色状态" prop="status">
							<el-radio-group v-model="childOption.option.status">
								<el-radio label="1">启用</el-radio>
								<el-radio label="0">禁用</el-radio>
							</el-radio-group>
						</el-form-item>
						<!-- v-if="!childOption.isAdmin" -->
						<el-form-item label="站点" prop="site_code" :rules="[{required: true, message:'该项不能为空'}]">
							<el-select v-model="childOption.option.site_code" @change="changeSite">
								<el-option v-for="(item, index) in childOption.tableData" :key="index" :value="item.site_code" :label="item.site_code"></el-option>
							</el-select>
						</el-form-item>
					</el-col>
				</el-row>

				<el-form-item label="功能权限">
					<div class="department-site">
						<div class="title">
							<el-checkbox :indeterminate="childOption.checkAllStatus" v-model="childOption.checkAllFlag" @change="checkAll">全选</el-checkbox>
						</div>
						<div>
							<el-checkbox-group v-model="childOption.checkedList" @change="changeGroup">
								<el-tabs v-model="childOption.activeName" @tab-click="tabClick">
									<el-tab-pane v-for="(item,index) in childOption.menuList" :key="index" :label="item.name" :name="String(item.id)">
										<div>
											<el-checkbox :label="item.id" @change="checkModule($event,item)">全选</el-checkbox>
										</div>
										<my-list :data="item.children" :level="1" @checkCat="checkModule"></my-list>
									</el-tab-pane>
								</el-tabs>
							</el-checkbox-group>
						</div>
					</div>
				</el-form-item>

				<el-form-item>
					<el-button @click="resetForm('dialogForm')" size="small">取消</el-button>
					<el-button type="primary" @click="submitForm('dialogForm')" size="small" :loading="submitLoading">确定</el-button>
				</el-form-item>
			</el-form>
		</el-dialog>

		<!-- 权限拥有明细列表 -->
		<el-dialog :title="dialogDetailFormInfo.title" :visible.sync="dialogDetailFormVisible" class="role-edit role-detail" width="60%"
		  :center="dialogDetailFormInfo.center">
			<el-form>
				<el-row>
					<el-col :span="14">
						角色名称:{{dialogDetailFormInfo.name}}
					</el-col>
					<el-col :span="5" :offset="5">
						角色状态:{{dialogDetailFormInfo.status == '1'?'启用':'禁用'}}
					</el-col>
				</el-row>
				<el-form-item></el-form-item>
				<el-form-item>
					<el-table :data="dialogDetailSource" max-height="400" style="display:block;overflow-y:auto;">
						<el-table-column prop="id" label="id"></el-table-column>
						<el-table-column prop="username" label="拥有人员"></el-table-column>
						<el-table-column label="超级管理员">
							<template slot-scope="scope">
								<span v-if="scope.row.is_super == 1">是</span>
								<span v-else-if="scope.row.is_super == 0">不是</span>
							</template>
						</el-table-column>
						<el-table-column label="部门负责人">
							<template slot-scope="scope">
								<span v-if="scope.row.is_leader == 1">是</span>
								<span v-else-if="scope.row.is_leader == 0">否</span>
							</template>
						</el-table-column>
						<el-table-column prop="create_time" label="赋予时间" min-width="162">
							<template slot-scope="scope">
								<span>{{ Number(scope.row.create_time) | moment('YYYY-MM-DD HH:mm:ss') }}</span>
							</template>
						</el-table-column>
					</el-table>
				</el-form-item>
			</el-form>
		</el-dialog>
	</site-layout>
</template>

<script>
import siteLayout from './layouts/Layout.vue'
import { getRoleList, getRoleInfo, getAvalprivileges, getRoleDetailList } from '../plugin/api'
import fetch from '../plugin/fetchApi'
import { getCookie } from '../plugin/mUtils'

export default {
	components: {		siteLayout,
		'my-list': {
			template: `<div>
                    <div class="my-list-not-leaf" v-for="(item, index) in data" :key="item.id" v-if="!item.treeInfo.leaf">
                        <el-checkbox :label="item.id" @change="checkCat($event, item)">{{item.name}}</el-checkbox>
                        <name-list :data="item.children" :level="level + 1" @checkCat="checkCat"></name-list>
                    </div>
                    <div class="my-list-leaf">
                        <el-checkbox v-for="(item, index) in data" :key="item.id" v-if="item.treeInfo.leaf" :label="item.id" @change="checkCat($event, item)">{{item.name}}</el-checkbox>
                    </div>

                </div>`,
			props: ['data', 'level'],
			name: 'name-list',
			methods: {
				checkCat: function (event, params) {
					this.$emit('checkCat', event, params)
				}
			}
		}
	},
	data () {
		return {
			loading: false,
			searchWord: '',
			total: 0,
			dataSource: [],
			isActive: false,
			pageForm: {},
			checkedSites: [],
			options: {
				pageNo: 1,
				pageSize: 10,
				status: '',
				field: '',
				keyword: '',
				editDate: '',
				update_time_start: '',
				update_time_end: '',
			},

            // pc, m, app
            // D网的默认是 web
			roleTabName: getCookie('site_group_code') === 'dl' ? 'web' : 'pc',
			places: [],

			statusOpt: {
				'0': '禁用',
				'1': '启用'
			},
			dialogForm: {
				department: {},
				site: {}
			},
			dialogFormInfo: {
				title: '',
				submitType: 'add',
				parent_id: '0',
				node: 0,
				center: true,
				disabled: true
			},
			dialogVisible: false,
			dialogPageVisible: false,
			editRules: {
				name: [{ required: true, message: '请输入角色名称' }],
				status: [{ required: true, message: '请选择状态', trigger: 'change' }]
			},
			//dialog option
			childOption: {
				checkAllStatus: false,//indeterminate select
				checkAllFlag: false,  //all checked array
				activeName: '',
				isAdmin: false,
				checkedList: [],
				option: {
					name: '',
					site_code: '',
					status: null,
				},
				menuList: [],//总列表(tab+checkgroup)
				totalIdList: [],
				groupList: {},
				tableData: {},
				postLoading: false
			},
			submitLoading: false,
			permissions: [],
			/* 角色用户列表 */
			dialogDetailSource: [],
			dialogDetailFormInfo: {
				center: true,
				title: '权限拥有明细',
				name: '',
				status: 1,
				loading: false
			},
			dialogDetailFormVisible: false
		}
	},
	filters: {

	},
	computed: {
		userName () {
			return localStorage.getItem('userName')
		},
		is_super () {
			return localStorage.getItem('isSuper')
		},
		isLeader () {
			return localStorage.getItem('isLeader')
		},
	},
	created () {
		this.permissions = JSON.parse(localStorage.getItem('actionPermissions')).data

		this.handleLists()
		this.handleOpt()
	},
	mounted () {
		// this.site_current = getCookie('site_group_code') + '-' + this.roleTabName
		// 设置当前站点信息
		this.places = JSON.parse(localStorage.currentSites).sites
	},
	methods: {
		handleSearch () {
			this.options.pageNo = 1
			this.handleLists()
		},
		// PC，M，APP切换
		handleRoleTabClick (event) {
			this.roleTabName = event.name
			this.options.pageNo = 1
			this.handleLists()
		},
		handleLists () {
			// search
			let _this = this
			if ((this.options.keyword && !this.options.field) || (!this.options.keyword && this.options.field)) {
				this.$message.warning('关键字必须匹配搜索类型')
				return
			}
			let timeArr = this.options.editDate
			if (timeArr && timeArr.length > 0) {
				this.options.update_time_start = timeArr[0] / 1000
				this.options.update_time_end = timeArr[1] / 1000
			} else {
				this.options.update_time_start = ''
				this.options.update_time_end = ''
			}

			let params = {}
			for (let i in this.options) {
				if (i !== 'editDate') {
					params[i] = this.options[i]
				}
			}
			params.site_code = getCookie('site_group_code') + '-' + this.roleTabName
			
			this.loading = true
			getRoleList(params).then(res => {
				this.loading = false
				if (res.code == '0') {
					this.total = res.data.pagination.totalCount
					this.dataSource = res.data.list
				} else {
					this.$message.error(res.message)
				}
			}).catch(function () {
				_this.loading = false
			})
		},
		submitForm (formName) {
			this.$refs[formName].validate(valid => {
				let submitUrl = '', submitMessage = '', params = {}
				if (valid) {
					let childOption = this.childOption
					if (this.dialogFormInfo.submitType == 'add') {
						submitUrl = '/base/role/add'
						submitMessage = '新增成功'
					} else if (this.dialogFormInfo.submitType == 'edit') {
						submitUrl = '/base/role/edit'
						submitMessage = '编辑成功'
					}
					params.status = Number(childOption.option.status)
					params.privilege_id = childOption.checkedList.join(',')
					params.site_code = childOption.option.site_code ? childOption.option.site_code : ''
					if (!params.privilege_id || params.privilege_id == '') {
						this.$message.warning('该角色所拥有的功能权限集合不能为空')
						return false
					}
					if (this.dialogForm.id) {
						params.id = this.dialogForm.id
						params.name = childOption.option.name
					} else {
						params.name = childOption.option.name
					}
					this.loading = true
					this.submitLoading = true
					fetch.post(submitUrl, params).then(res => {
						this.loading = false
						this.submitLoading = false
						if (res.code == 0) {
							this.handleLists()
							this.$message.success(res.message || submitMessage)
							this.closeDialog(formName)
						} else {
							this.$message.error(res.message)
						}
					}).catch(() => {
						this.loading = false
						this.submitLoading = false
					})
					// this.resetFields(formName)
				}
			})
		},
		handleCurrentChange (currentPage) {
			this.options.pageNo = currentPage
			this.handleLists(currentPage)
		},
		handleExpandChange (row) {
			if (!row.children) {
				this.getPages(row.id)
			}
		},
		//角色可选权限
		handleOpt () {
			getAvalprivileges().then(res => {
				if (res.code === 0) {
					let data = res.data
					this.childOption.isAdmin = data.isSuperAdmin
					data.sitePrivileges.forEach(item => {
						this.$set(this.childOption.tableData, item.site_code, item)
					})
					// if (this.childOption.isAdmin)
					this.childOption.menuList = data.sitePrivileges[0].menuList
					this.menuOpt()

					this.loading = false
				}
			})
		},
		menuOpt () {
			this.childOption.activeName = String(this.childOption.menuList[0].id)
			// 勾选交互配置
			this.childOption.totalIdList = this.getTotalIdList(this.childOption.menuList, [])
			this.childOption.menuList = this.getChildId(this.childOption.menuList)
			this.childOption.menuList.forEach(group => {
				this.childOption.groupList[group.id] = this.getTotalIdList(group.children, [])
			})
		},
		getTotalIdList (obj, tempArr) {
			for (let i in obj) {
				tempArr.push(obj[i].id)

				if (typeof obj[i].children === 'object') {
					this.getTotalIdList(obj[i].children, tempArr)
				}
			}

			return tempArr
		},
		getChildId (menuList) {
			for (let i in menuList) {
				let item = menuList[i]
				if (item.children && item.children.length > 0) {
					item.childList = this.getTotalIdList(item.children, [])
					this.getChildId(item.children)
				}

			}

			return menuList
		},
		addOption () { },
		dialogFormRest () {
			this.dialogForm = {}
			this.childOption = {}
		},
		//编辑
		editItem (row, type) {
			this.childOption.checkedList = []
			this.dialogForm = row
			this.dialogFormInfo.title = '编辑角色'
			this.dialogFormInfo.submitType = 'edit'
			this.dialogFormInfo.node = row.id
			this.loading = true
			if (type == 0) {
				this.dialogFormInfo.disabled = true
			} else {
				this.dialogFormInfo.disabled = false
			}

			getRoleInfo({ id: row.id }).then(res => {
				this.loading = false
				if (res.code === 0) {
					let data = res.data
					let childOption = this.childOption
					if (typeof data.privilege_id == 'object') {
						// this.childOption.checkedList = data.privilege_id.map(item => { return Number(item) });
						this.childOption.checkedList = data.privilege_id
					} else if (typeof data.privilege_id == 'string') {
						this.childOption.checkedList = data.privilege_id.split(',')
					}

					this.childOption.option.name = data.name
					this.childOption.option.site_code = data.site_code
					this.childOption.option.status = data.status

					if (childOption.isAdmin) {
						if (this.childOption.checkedList.length > 0 && this.childOption.checkedList.length < this.childOption.totalIdList.length) {
							this.childOption.checkAllStatus = true
						} else if (this.childOption.checkedList.length === 0) {
							this.childOption.checkAllStatus = false
							this.childOption.checkAllFlag = false
						} else {
							this.childOption.checkAllFlag = true
							this.childOption.checkAllStatus = false
						}
					}
					this.dialogVisible = true
				} else {
					this.$message.error(res.message)
				}
			}).catch(() => {
				this.loading = false
			})
		},
		//新增角色
		addRole () {
			this.dialogForm = {}
			this.childOption.checkedList = []
			this.childOption.option = {}
			this.dialogFormInfo.title = '新增角色'
			this.dialogFormInfo.submitType = 'add'
			this.dialogFormInfo.node = '0'
			this.dialogFormInfo.disabled = false
			this.dialogVisible = true
		},
		resetForm (formName) {
			if (formName && formName == 'searchForm') {
				this.$refs['searchForm'].resetFields()
				this.options.field = ''
				this.options.keyword = ''
				this.options.status = ''
				this.options.editDate = []
				this.options.update_time_start = ''
				this.options.update_time_end = ''
				return false
			}
			this.resetFields(formName)
			this.closeDialog(formName)
		},
		resetFields (formName) {
			this.$refs[formName].resetFields()
		},
		closeDialog (formName) {
			if (formName == 'dialogForm') {
				this.dialogVisible = false
			}
		},
		//checkbox
		checkAll (val) {
			if (val) {
				this.childOption.checkedList = this.childOption.totalIdList
			} else {
				this.childOption.checkedList = []
			}
			this.childOption.checkAllStatus = false
		},
		checkModule (newVal, val) {
			let groupIndex = this.childOption.activeName
			// let groupList = this.childOption.groupList[groupIndex];

			let checkStatus = newVal
			let subList = []
			if (val.childList) {
				subList = val.childList
			}

			if (checkStatus && subList.length > 0) {
				for (let i in subList) {
					if (this.childOption.checkedList.indexOf(subList[i]) === -1) {
						this.childOption.checkedList.push(subList[i])
					}
				}
				this.tabClick()
			} else {
				for (let j in subList) {
					if (this.childOption.checkedList.indexOf(subList[j]) !== -1) {
						let delIndex = this.childOption.checkedList.indexOf(subList[j])
						this.childOption.checkedList.splice(delIndex, 1)
					}
				}

				let tempIndex = this.childOption.checkedList.indexOf(groupIndex * 1)
				if (tempIndex !== -1) {
					this.childOption.checkedList.splice(tempIndex, 1)
				}
			}
		},
		//tab切换
		tabClick () {
			let defaultGroupList
			let count = 0
			let groupIndex = this.childOption.activeName * 1
			for (let i in this.childOption.menuList) {
				if (this.childOption.menuList[i].id == groupIndex) {
					defaultGroupList = this.childOption.menuList[i].childList
					break
				}
			}
			for (let j in defaultGroupList) {
				let id = defaultGroupList[j]
				if (this.childOption.checkedList.indexOf(id) !== -1) {
					count++
				}
			}
			if (defaultGroupList && count == defaultGroupList.length) {
				if (this.childOption.checkedList.indexOf(groupIndex) == -1) {
					this.childOption.checkedList.push(groupIndex)
				}
			}
		},
		//功能权限change-event
		changeGroup () {
			if (this.childOption.checkedList.length > 0 && this.childOption.checkedList.length < this.childOption.totalIdList.length) {
				this.childOption.checkAllStatus = true
			} else if (this.childOption.checkedList.length === 0) {
				this.childOption.checkAllStatus = false
				this.childOption.checkAllFlag = false
			} else {
				this.childOption.checkAllFlag = true
				this.childOption.checkAllStatus = false
			}
		},
		changeSite (val) {
			this.menuList = this.childOption.tableData[val].menuList
			this.menuOpt()
			this.tabClick()

			if (this.childOption.checkedList.length > 0 && this.childOption.checkedList.length < this.childOption.totalIdList.length) {
				this.childOption.checkAllStatus = true
				this.childOption.checkAllFlag = false
			} else if (this.childOption.checkedList.length === 0) {
				this.childOption.checkAllStatus = false
				this.childOption.checkAllFlag = false
			} else {
				this.childOption.checkAllFlag = true
				this.childOption.checkAllStatus = false
			}
		},
		/* 查看权限明细 */
		detailItem (row) {
			let _this = this
			this.dialogDetailFormVisible = true
			this.dialogDetailFormInfo.name = row.name
			this.dialogDetailFormInfo.status = row.status
			this.dialogDetailFormInfo.loading = true
			getRoleDetailList({ id: row.id }).then(function (res) {
				_this.dialogDetailFormInfo.loading = false
				_this.dialogDetailSource = res.data
			})
		},
		/* dialog关闭 */
		dialogFormClose () {
			this.resetFields('dialogForm')
		}

	}
}
</script>

<style lang="less">
.role-edit {
  .department-site {
    border: 1px solid #ccc;
    padding: 30px;
    position: relative;
    margin-top: 30px;
    margin-bottom: 30px;

    .title {
      position: absolute;
      line-height: 36px;
      top: -18px;
      left: 50px;
      background: #fff;
      padding: 0 15px;
    }

    .el-checkbox-group {
      &.site {
        .el-checkbox {
          margin-left: 60px;
        }
      }
    }
  }
  .my-list-leaf {
    margin-left: 45px !important;
    .el-checkbox {
      margin-left: 15px;
    }
  }

  .my-list-not-leaf {
    margin: 10px 0 10px 45px !important;
    .el-checkbox {
      margin-left: 15px;
    }
  }
}
.role-detail {
  .el-dialog {
    min-width: 612px;
  }
}
</style>
