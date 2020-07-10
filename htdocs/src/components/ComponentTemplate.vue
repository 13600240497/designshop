<template>
	<site-layout @publicReady="publicReady">
		<div>
            <filter-layer
                ref="filterLayer"
                @handleSearch="doSearch"
                @handleAdd="addTemplate">
            </filter-layer>
        </div>

		<el-row>
			<el-col :span="24">
				<el-table v-loading="tableLoading" :data="templateList" style="width: 100%">
					<el-table-column prop="id" label="ID" width="70px"></el-table-column>
					<el-table-column prop="name" label="中文名称"></el-table-column>
					<el-table-column prop="place" label="组件应用环境">
						<template slot-scope="scope">
							<span v-if="scope.row.place == 1">活动页</span>
							<span v-else>首页</span>
						</template>
					</el-table-column>
					<el-table-column prop="name_en" label="文件夹名称"></el-table-column>
					<el-table-column prop="siteGroups" label="应用站点" width="150px">
						<template slot-scope="scope">
							<span v-if="!scope.row.siteGroups"></span>
							<span v-for="(item,index) in scope.row.siteGroups.split(',')" :key="index" v-else>
								<span v-for="(element,key) in sites" :key="key" v-if="element.code == item">
									<span v-if="index != 0">,</span>{{ element.name}}
								</span>
							</span> 
						</template>
					</el-table-column> 
					<el-table-column prop="component_key" label="组件编码"></el-table-column>
					<el-table-column prop="status" label="启用状态">
						<template slot-scope="scope">
							<span v-if="scope.row.status == 1">启用</span>
							<span v-else>停用</span>
						</template>
					</el-table-column>
					<el-table-column label="预览图">
						<template slot-scope="scope">
							<img v-if="scope.row.pic" :src="scope.row.pic" style="max-height: 64px">
							<span v-else>暂无图片</span>
						</template>
					</el-table-column>
					<el-table-column prop="create_user" label="创建者"></el-table-column>
					<el-table-column label="创建时间" width="160px">
						<template slot-scope="scope">
						<span>{{ scope.row.create_time | moment('YYYY-MM-DD HH:mm:ss') }}</span>
						</template>
					</el-table-column>
					<el-table-column label="操作" width="260">
						<template slot-scope="scope">
							<el-button v-if="scope.row.status == 0" type="default" size="small" @click="changeStatus(scope.row.id, 1)">启用</el-button>
							<el-button v-if="scope.row.status == 1" type="danger" size="small" @click="changeStatus(scope.row.id, 0)">停用</el-button>
							<el-button type="primary" size="small" @click="editComponent(scope.row)">编辑</el-button>
							<el-button type="danger" size="small" @click="deleteComponent(scope.row.id)">删除</el-button>
						</template>
					</el-table-column>
				</el-table>
			</el-col>
		</el-row>

		<el-row v-if="total > 10">
			<el-col :span="24" class="text-right">
				<el-pagination layout="prev, pager, next" :page-size="10" :total="total" @current-change="handleCurrentChange"></el-pagination>
			</el-col>
		</el-row>

		<el-dialog :title="form.title" :visible.sync="dialogVisible" @close="resetForm('form')">
			<el-form :model="form" :rules="rules" ref="form" label-width="100px">
				<el-form-item label="中文名称" prop="name">
					<el-input v-model="form.name"></el-input>
				</el-form-item>

				<el-form-item label="文件夹名称" prop="name_en">
					<el-input v-model="form.name_en"></el-input>
				</el-form-item>

				<el-form-item label="组件 KEY" prop="key">
					<el-input v-model="form.key" :disabled="Boolean(form.id)"></el-input>
				</el-form-item>

                <el-form-item label="语言键值" prop="langKeys">
                    <el-select v-model="form.langKeys" filterable multiple  style="width: 100%;">
                        <el-option
                            v-for="item in langKeysLlist"
                            :key="item.key"
                            :label="item.key"
                            :value="item.key">
                        </el-option>
                    </el-select>
                </el-form-item>

				<el-form-item label="应用环境" prop="place">
					<el-radio-group v-model="form.place" :disabled="Boolean(form.id)">
						<el-radio :label="1">活动页</el-radio>
						<el-radio :label="2">首页</el-radio>
					</el-radio-group>
				</el-form-item>

			 	<el-form-item label="应用站点"  prop="checkedSites" id="check-box">
					<el-checkbox  v-model="checkAll" @change="handleCheckAllChange">所有站点</el-checkbox>
					<el-checkbox-group v-model="form.checkedSites" @change="handleCheckedSitesChange"> 
						<el-checkbox v-for="(siteItem,key) in sites" :label="siteItem.code" :key="key">{{siteItem.name}}</el-checkbox>
					</el-checkbox-group> 
				</el-form-item>

                <el-form-item label="Node渲染" prop="is_vue_ssr">
                    <el-radio-group v-model="form.is_vue_ssr">
						<el-radio :label="1">是</el-radio>
						<el-radio :label="0">否</el-radio>
					</el-radio-group>
                </el-form-item>

				<el-form-item label="异步组件" prop="is_async">
                    <el-radio-group v-model="form.is_async">
						<el-radio :label="1">是</el-radio>
						<el-radio :label="0">否</el-radio>
					</el-radio-group>
                </el-form-item>

				<el-form-item>
					<el-upload action="/component/component-tpl/upload-pic" name="files" accept="image/jpg,image/jpeg,image/png" :limit="1"
						:on-success="handleUploadSuccess"
						:on-exceed="handleUploadExceed"
						:on-error="handleUploadError"
						:file-list="fileList"
						:before-upload="handleBeforeUpload">
						<el-button size="small" type="primary">点击上传</el-button>
						<div slot="tip" class="el-upload__tip">只能上传jpg/png文件，且不超过3M</div>
					</el-upload>
				</el-form-item>
				
				<el-form-item>
					<el-button @click="resetForm('form')" size="small">取消</el-button>
					<el-button type="primary" @click="submitForm('form')" size="small" :loading="submitLoading">确定</el-button>
				</el-form-item>
			</el-form>
		</el-dialog>
	</site-layout>
</template>

<script>
import siteLayout from './layouts/Layout.vue'
import {
    getTemplateList,
    addTemplate,
    updateTemplate,
    deleteTemplate,
    changeTemplateStatus,
    getLangKeysList
} from '../plugin/api'
import { getCookie } from '../plugin/mUtils'

import filterLayer from './componentManage/template-filter.vue';

export default {
	components: { siteLayout, filterLayer },
	data() {
		return {
            tableLoading: false,
			checkAll: false,
			sites: [],
			submitLoading: false,
			total: '',
			currentPage: 1,
			fileList: [],
			templateList: [],
			dialogVisible: false,
			uploadedImg: '',
			form: {
				title: '新增模版', // 弹窗标题
				id: null,
				name: '',
				name_en: '',
				key: '',
				pic: '',
				place: 1,
                checkedSites: [],
                is_vue_ssr: 0, // 是否开启 Vue SSR Node 渲染，默认 0=不开启,
				langKeys: [], // 绑定的语言 keys
				is_async: 0, // 是否异步组件 1=是, 0=否
			},
			rules: {
				name: [
					{ required: true, message: '请输入中文名称', trigger: 'change' }
				],
				name_en: [
					{ required: true, message: '请输入英文名称', trigger: 'change' }
				],
				key: [
					{ required: true, message: '请输入组件 Key 值', trigger: 'change' }
				],
				place: [
					{ required: true, message: '请选择组件应用环境', trigger: 'change' }
				],
				checkedSites: [
					{ required: true, message: '请选择组件应用站点', trigger: 'change' }
				]
            },
            // 语言KEY的列表
            langKeysLlist: [],
		}
	},
	mounted () {
        // 获取所有的模版
        this.getTemplates();

        // 获取所有语言 KEYS
        this.getLangKeys();
	},
	methods: {
		publicReady() {
			this.sites = JSON.parse(localStorage.getItem("supportSites")).data
		},
		handleCheckAllChange(val) {
			var siteArray = []
			this.sites.forEach(element => {
				siteArray.push(element.code)
			});
			this.form.checkedSites = val ? siteArray : []
		},
		handleCheckedSitesChange(value) {
			let checkedCount = value.length;
			this.checkAll = checkedCount === this.sites.length
        },
        
		async getTemplates() {
            this.tableLoading = true;
            const formdata = Object.assign({}, this.$refs.filterLayer.formdata);
			let params = {
				status: formdata.status,
				name: formdata.name,
				key: formdata.key,
				place: formdata.place,
                siteGroup: formdata.siteGroup,
                pageNo: this.currentPage,
				pageSize: 10,
			};
			let res = await getTemplateList(params);
			this.templateList = res.data.list
            this.total = res.data.totalCount
            this.tableLoading = false;
        },
        /**
         * 提交表单
         */
		async submitForm(formName) {
			this.submitLoading = true

			if (!this.uploadedImg) {
				this.$message('请先上传图片')
				this.submitLoading = false

				return false
			}

			this.$refs[formName].validate(async (valid) => {
				if (valid) {
					let res, params = {
						name: this.form.name,
						name_en: this.form.name_en,
						key: this.form.key,
						pic: this.uploadedImg,
						place: this.form.place,
                        siteGroups: this.form.checkedSites.toString(),
                        is_vue_ssr: this.form.is_vue_ssr || 0,
						lang_keys: this.form.langKeys.join(','),
						is_async: this.form.is_async || 0
					}

					if (this.form.id !== null) {
						params.id = this.form.id
						res = await updateTemplate(params)
					} else {
						res = await addTemplate(params)
					}

					if (res.code == 0) {
						this.resetForm('form')
						this.getTemplates()
					} else {
						this.submitLoading = false
					}
				} else {
					this.submitLoading = false
				}
			})
        },
        
        /**
         * 重置表单
         */
		resetForm(formName) {
			this.checkAll = false
			this.form.checkedSites = []
			this.$refs[formName].resetFields()
			this.dialogVisible = false
			this.submitLoading = false
			this.uploadedImg = ''
			this.fileList = []
        },
        
		handleCurrentChange(currentPage) {
			this.currentPage = currentPage
			this.getTemplates()
		},
		async changeStatus(id, statusCode) {
			let message

			if (statusCode == 1) {
				message = '确认启用该模板？'
			} else if (statusCode == 0) {
				message = '确认停用该模板？'
			}

			this.$confirm(message, '提示', {
				confirmButtonText: '确定',
				cancelButtonText: '取消',
				type: 'warning'
			}).then(async () => {
				let params = {
					id: id,
					status: statusCode
				}

				await changeTemplateStatus(params)
				this.getTemplates()
			}).catch(() => {
				this.$message({
					type: 'info',
					message: '已取消操作!'
				})
			})
		},
		handleUploadSuccess(response) {
			if (response.code == 0) {
				this.uploadedImg = response.data.url
			} else {
				this.$message(response.data.message)
			}
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
		doSearch() {
			this.currentPage = 1
			this.getTemplates()
        },
        
        /**
         * 打开弹窗-编辑模版
         */
		editComponent (row) {
			this.form.title = '编辑模版';
			this.form.id = row.id
			this.form.name = row.name
			this.form.name_en = row.name_en
			this.form.key = row.component_key
            this.form.place = row.place
            this.form.is_vue_ssr = row.is_vue_ssr || 0;
			this.form.langKeys = row.lang_keys ? row.lang_keys : [];
			this.form.is_async = row.is_async || 0;

			if (row.siteGroups) {
				this.form.checkedSites = row.siteGroups.split(",")
				this.checkAll = this.form.checkedSites.length === this.sites.length
			}

			this.fileList.push({
				name: row.pic,
				url: row.pic
			})
			this.uploadedImg = row.pic

			this.dialogVisible = true
		},

		/**
		 * 删除模版
		 */
		deleteComponent(id) {
			this.$confirm('确定要删除该组件模板', '提示', {
				confirmButtonText: '确定',
				cancelButtonText: '取消',
				type: 'warning'
			}).then(async () => {
				let params = {
					id: id
				}

				let res = await deleteTemplate(params)

				if (res.code == 0) {
					this.$message({
						type: 'success',
						message: '删除成功'
					})
					this.getTemplates()
				}
			}).catch(() => {
				this.$message({
					type: 'info',
					message: '已取消删除',
				})
			})
		},

		/**
		 * 打开弹窗-新增模版
		 */
		addTemplate() {
			this.form.title = '新增模版';
			this.form.id = null
			this.form.name = ''
			this.form.name_en = ''
			this.form.key = ''
			this.form.pic = ''
			this.dialogVisible = true
			this.form.is_async = 0;
        },
        
        /**
         * 获取语言包列表
         */
        async getLangKeys () {
            getLangKeysList({
                search_key: '',
                search_value: '',
                site_code: getCookie('site_group_code')
            }).then((res) => {
                this.langKeysLlist = res.data.list;
            })
        }
	}
}
</script>
<style lang="less" scoped>
#check-box .el-checkbox-group {
  display: inline-block;
  margin-left: 30px;
}
</style>
