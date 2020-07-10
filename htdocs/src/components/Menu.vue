<template>
	<site-layout>
		<el-row>
			<el-col :span="12" :offset="12" class="text-right">
				<el-button type="danger" size="small" icon="el-icon-plus" @click="addMenu('dialogForm')">新增菜单</el-button>
			</el-col>
		</el-row>

		<el-row>
			<el-col :span="24">
				<div class="activity-list-main">
					<div class="activity-list-box">
						<el-row>
							<el-col :span="24">
								<el-table :data="dataLists" border :row-style="showFilterTr" @expand-change="handleExpandChange">
									<el-table-column prop="name" label="菜单名称" min-width="150">
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
											<span>{{scope.row.name}}</span>
										</template>
									</el-table-column>
									<el-table-column prop="route" label="路由"></el-table-column>
									<el-table-column prop="icon_class" label="图标">
										<template slot-scope="scope">
											<i :class="scope.row.icon_class"></i>
										</template>
									</el-table-column>
									<el-table-column prop="update_time" label="更新时间" min-width="180">
										<template slot-scope="scope">
											<span>{{ Number(scope.row.update_time) | moment('YYYY-MM-DD HH:mm:ss') }}</span>
										</template>
									</el-table-column>
									<el-table-column label="是否菜单显示">
										<template slot-scope="scope">
											<span v-if="scope.row.type == 1">
												<i class="el-icon-checkStatu el-icon-success color-success"></i>
											</span>
											<span v-else-if="scope.row.type == 0">
												<i class="el-icon-checkStatu el-icon-error color-error"></i>
											</span>
										</template>
									</el-table-column>
									<el-table-column label="是否公共接口">
										<template slot-scope="scope">
											<span v-if="scope.row.is_public == 1">
												<i class="el-icon-checkStatu el-icon-success color-success"></i>
											</span>
											<span v-else-if="scope.row.is_public == 0">
												<i class="el-icon-checkStatu el-icon-error color-error"></i>
											</span>
										</template>
									</el-table-column>
									<el-table-column label="路由状态">
										<template slot-scope="scope">
											<span v-if="scope.row.status == 1">
												<i class="el-icon-checkStatu el-icon-success color-success"></i>
											</span>
											<span v-else-if="scope.row.status == 0">
												<i class="el-icon-checkStatu el-icon-error color-error"></i>
											</span>
										</template>
									</el-table-column>
									<el-table-column prop="order" label="排序"></el-table-column>
									<el-table-column label="操作" min-width="160">
										<template slot-scope="scope">
											<el-button size="small" v-if="permissions.includes('base/menu/edit')" @click="getItem(scope.row)">编辑</el-button>
											<el-button size="small" v-if="permissions.includes('base/menu/delete')" @click="delOne(scope.row.id)">删除</el-button>
										</template>
									</el-table-column>
								</el-table>
							</el-col>
						</el-row>

						<!-- <el-row>
              <el-col :span="24" class="text-right">
                <el-pagination layout="prev, pager, next" page-size="10" :total="total" @current-change="handleCurrentChange"></el-pagination>
              </el-col>
            </el-row> -->
					</div>

				</div>
			</el-col>
		</el-row>
		<!-- 详情弹窗 -->

		<el-dialog :title="dialogFormInfo.title" :visible.sync="dialogVisible" @close="closeDialogEvent('dialogForm')">
			<el-form :model="dialogForm" :rules="menuAddRules" ref="dialogForm" label-width="80px">
				<el-form-item label="菜单名称" prop="name" required>
					<el-input v-model="dialogForm.name"></el-input>
				</el-form-item>
				<el-form-item label="所属分类">
					<el-select v-model="dialogFormInfo.node" placeholder="所属分类">
						<el-option label="顶级菜单" value="0"></el-option>
						<el-option v-for="(item,index) in publicSelectOptions" :key="index" :label="item.name" :value="item.id"></el-option>
					</el-select>
				</el-form-item>
				<el-form-item label="路由" prop="route" required>
					<el-autocomplete class="inline-input" v-model="dialogForm.route" :fetch-suggestions="searchRoute" placeholder="请输入内容"></el-autocomplete>
				</el-form-item>
				<el-form-item label="排序" prop="order">
					<el-input v-model="dialogForm.order" placeholder="越小越靠前"></el-input>
				</el-form-item>
				<el-form-item label="权限类型" prop="type" required>
					<el-select v-model="dialogForm.type" placeholder="请选择类型">
						<el-option label="菜单" value="1"></el-option>
						<el-option label="操作" value="0"></el-option>
					</el-select>
				</el-form-item>
				<el-form-item label="是否公开" prop="is_public">
					<el-radio-group v-model="dialogForm.is_public">
						<el-radio label="1">是</el-radio>
						<el-radio label="0">否</el-radio>
					</el-radio-group>
				</el-form-item>
				<el-form-item label="状态" prop="status" required>
					<el-radio-group v-model="dialogForm.status">
						<el-radio label="1">开启</el-radio>
						<el-radio label="0">关闭</el-radio>
					</el-radio-group>
				</el-form-item>
				<el-form-item label="图标" prop="icon_class">
					<el-input v-model="dialogForm.icon_class"></el-input>
				</el-form-item>
				<el-form-item label="备注" prop="remark">
					<el-input type="textarea" v-model="dialogForm.remark" :rows="4"></el-input>
				</el-form-item>
				<el-form-item>
					<el-button @click="resetForm('dialogForm')" size="small">取消</el-button>
					<el-button type="primary" @click="submitForm('dialogForm')" size="small" :loading="loading">确定</el-button>
				</el-form-item>
			</el-form>
		</el-dialog>
	</site-layout>
</template>

<script>
import siteLayout from './layouts/Layout.vue'
import { DataTransfer } from '../plugin/mUtils'
import { getMenuList, getMenuSelectOp, menuDel } from '../plugin/api'
import fetch from '../plugin/fetchApi'

export default {
	components: { siteLayout },
	data () {
		return {
			searchWord: '',
			total: 0,
			dataSource: [],
			isActive: false,
			pageForm: {},
			dialogForm: {
				is_public: 0
			},
			dialogFormInfo: {
				title: '',
				submitType: 'add',
				parent_id: '0',
				node: 0
			},
			dialogVisible: false,
			dialogPageVisible: false,
			loading: false,
			publicSelectOptions: [],
			publicRoutes: [],
			menuAddRules: {
				name: [{ required: true, message: '请输入菜单名称' }],
				parent_id: [
					{ required: true, message: '请选择父节点', trigger: 'change' }
				],
				route: [{ required: true, message: '请输入路由路径' }],
				node: [
					{ required: true, message: '  请选择所属分类', trigger: 'change' }
				],
				order: [{ required: true, message: '请输入排序号' }],
				type: [
					{ required: true, message: '请选择路由类型', trigger: 'change' }
				],
				is_public: [
					{ required: true, message: '请选择是否公开', trigger: 'change' }
				],
				status: [
					{ required: true, message: '请选择开启状态', trigger: 'change' }
				]
			},
			permissions: []
		}
	},
	props: {
		defaultExpandAll: {
			type: Boolean,
			default: function () {
				return false
			}
		}
	},
	created: function () {
		this.permissions = JSON.parse(localStorage.getItem('actionPermissions')).data

		this.handleLists()
		this.handlePublicSelect()
	},
	computed: {
		dataLists: function () {
			var _this = this
			var data = DataTransfer.treeToArray1(_this.dataSource, null, null, _this.defaultExpandAll)
			this.loadingHide()
			return data
		}
	},
	methods: {
		async handleLists () {
			let res = await getMenuList()
			this.dataSource = res.data
		},
		async handlePublicSelect () {
			let res = await getMenuSelectOp()
			let data = res.data
			let tempRoute = []
			for (let i in data.phpRoutes.available) {
				tempRoute.push({
					value: data.phpRoutes.available[i]
				})
			}
			this.publicSelectOptions = data.menus
			this.publicRoutes = tempRoute
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
		submitForm (formName) {
			this.$refs[formName].validate(valid => {
				if (valid) {
					var submitUrl = ''
					var submitMessage = ''
					var params = {
						icon_class: this.dialogForm.icon_class ? this.dialogForm.icon_class : '',
						is_public: this.dialogForm.is_public,
						name: this.dialogForm.name,
						order: this.dialogForm.order,
						parent_id: this.dialogFormInfo.node,
						remark: this.dialogForm.remark ? this.dialogForm.remark : '',
						route: this.dialogForm.route,
						status: this.dialogForm.status,
						// treeInfo: this.dialogForm.treeInfo,
						type: this.dialogForm.type
					}

					if (this.dialogFormInfo.submitType == 'add') {
						submitUrl = '/base/menu/add'
						submitMessage = '新增成功'
					} else if (this.dialogFormInfo.submitType == 'edit') {
						submitUrl = '/base/menu/edit'
						submitMessage = '编辑成功'
						params.id = this.dialogForm.id
						params.node = this.dialogForm.node
					}
					this.loading = true
					fetch.post(submitUrl, params).then(res => {
						this.loading = false
						if (res.code == 0) {
							this.$message.success(res.message || submitMessage)
							this.handleLists()
							this.handlePublicSelect()
						} else {
							this.$message.error(res.message)
						}
					}).catch(() => {
						this.loading = false
					})
					this.resetFields(formName)
					this.closeDialog(formName)
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
				this.dialogVisible = false
			}
		},
		closeDialogEvent (formName) {
			this.resetFields(formName)
		},
		//新增菜单
		addMenu () {
			this.dialogForm = {}
			this.dialogFormInfo.title = '新增菜单'
			this.dialogFormInfo.submitType = 'add'
			this.dialogFormInfo.node = '0'
			this.dialogVisible = true
		},
		//编辑
		getItem (row) {
			this.dialogForm = Object.assign({}, row)
			this.dialogFormInfo.title = '编辑菜单'
			this.dialogFormInfo.submitType = 'edit'
			this.dialogFormInfo.node = row.parent_id
			this.dialogVisible = true
		},
		delOne (id) {
			var _this = this
			this.$confirm('是否删除该项', '提示', {
				confirmButtonText: '确定',
				cancelButtonText: '取消',
				type: 'warning'
			}).then(function () {
				menuDel({ id: id }).then(() => {
					_this.handleLists()
				})
			}).catch(function () {
				_this.$message({
					type: 'info',
					message: '已取消删除'
				})
			})
		},
		//显示tr
		showFilterTr (rowObj) {
			var row = rowObj.row
			var show = row._parent ? row._parent._expanded && row._parent._show : true
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
			}, 500)
		},
		searchRoute (queryString, cb) {
			let queryResource = this.publicRoutes
			let results = queryString ? queryResource.filter(this.createFilter(queryString)) : queryResource
			//返回cb列表数据
			cb(results)

		},
		createFilter (queryString) {
			return (state => {
				return (state.value.toLowerCase().indexOf(queryString.toLowerCase()) === 0)
			})
		}
	}
}
</script>

<style>
.inline-input {
  width: 100%;
}
</style>
