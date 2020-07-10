<template>
    <site-layout @publicReady="publicReady">
		<!-- 筛选项 -->
		<filterLayout
            ref="filterLayer"
            @handleAdd="addComponent"
            @handleSearch="doSearch">
		</filterLayout>

		<!-- 表格 -->
        <div style="margin-bottom: 15px;">
			<el-table v-loading="loadingList" :data="componentList" style="width: 100%">
				<el-table-column type="expand">
					<template slot-scope="props">
					<el-form label-position="left" inline>
						<el-form-item label="描述：">
						<span>{{ props.row.description }}</span>
						</el-form-item>
					</el-form>
					</template>
				</el-table-column>

				<el-table-column prop="id" label="ID" width="70px"></el-table-column>
				<el-table-column prop="component_key" label="编码">
					<template slot-scope="scope">
						{{scope.row.component_key}}
						<i class="el-icon-zoom-in" @click="handleGoToTemplate(scope.row.component_key)"></i>
					</template>
				</el-table-column>

				<el-table-column prop="place" label="组件应用环境">
					<template slot-scope="scope">
						<span v-if="scope.row.place == 1">活动页</span>
						<span v-else>首页</span>
					</template>
				</el-table-column>

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

				<el-table-column prop="name" label="名称"></el-table-column>

				<el-table-column label="上线状态">
					<template slot-scope="scope">
					<span v-if="scope.row.status == 2">待上线</span>
					<span v-else-if="scope.row.status == 3">已上线</span>
					<span v-else-if="scope.row.status == 5">已下线</span>
					</template>
				</el-table-column>

				<el-table-column label="应用访问端">
					<template slot-scope="scope">
						<span v-if="scope.row.range == 1">桌面端</span>
						<span v-else-if="scope.row.range == 2">移动端</span>
						<span v-else-if="scope.row.range == 3">响应式</span>
                        <span v-else-if="scope.row.range == 4">原生App</span>
					</template>
				</el-table-column>

				<el-table-column prop="create_user" label="创建者"></el-table-column>

				<el-table-column label="创建时间">
					<template slot-scope="scope">
					<span>{{ scope.row.create_time | moment('YYYY-MM-DD HH:mm:ss') }}</span>
					</template>
				</el-table-column>

				<el-table-column label="操作">
					<template slot-scope="scope">
						<el-button v-if="scope.row.status == 2 || scope.row.status == 5" type="default" size="small" @click="changeStatus(scope.row.component_key, 3)">上线</el-button>
						<el-button v-if="scope.row.status == 3" type="danger" size="small" @click="changeStatus(scope.row.component_key, 5)">下线</el-button>
						<el-button
							type="primary"
							size="small"
							@click="editComponent(scope.row)">
							编辑
						</el-button>
					</template>
				</el-table-column>
				
			</el-table>
        </div>

		<!-- ????? -->
        <el-row v-if="total > 10">
            <el-col :span="24" class="text-right">
                <el-pagination layout="prev, pager, next" :page-size="10" :total="total" @current-change="handleCurrentChange"></el-pagination>
            </el-col>
        </el-row>

		<!-- ????? -->
        <el-dialog :title="dialogTitle" :visible.sync="dialogVisible" width="55%" @close="resetForm('form')">
            <el-form :model="form" :rules="rules" ref="form" label-width="85px">
                <el-row>
                <el-col :span="6">
                    <el-form-item label="类型" prop="type">
                    <el-select v-model="form.type" placeholder="请选择类型" @change="changeComponentType" :disabled="Boolean(form.id)">
                        <el-option label="布局" value="1"></el-option>
                        <el-option label="内容" value="2"></el-option>
                    </el-select>
                    </el-form-item>
                </el-col>
                <el-col :span="6">
                    <el-form-item label="应用环境" label-width="100px" prop="place">
                    <el-select v-model="form.place" placeholder="请选择" @change="handlePlaceChange" :disabled="Boolean(form.id)">
                        <el-option label="活动页" value="1"></el-option>
                        <el-option label="首页" value="2"></el-option>
                    </el-select>
                    </el-form-item>
                </el-col>
                <el-col :span="6">
                    <el-form-item label="分类" prop="category_id">
                    <el-select v-model="form.category_id" placeholder="请选择分类">
                        <el-option v-for="item in options" :key="item.id" :label="item.name" :value="item.id"></el-option>
                    </el-select>
                    </el-form-item>
                </el-col>
                <el-col :span="6" v-if="Boolean(form.id) && form.type == 2">
                    <el-form-item label="默认模板" prop="tid">
                    <el-select v-model="form.tid" placeholder="请选择分类">
                        <el-option label="暂无选项" value="0"></el-option>
                        <el-option v-for="item in templateOptions" :key="item.id" :label="item.name" :value="item.id"></el-option>
                    </el-select>
                    </el-form-item>
                </el-col>
                </el-row>
                <el-row class="form-range">
                    <el-col :span="8">
                        <el-form-item label="组件应用端" label-width="100px" prop="range">
                            <el-radio-group v-model="form.range" @change="handleRangeChange">
                                <el-radio :label="1">桌面</el-radio>
                                <el-radio :label="2">移动</el-radio>
                                <el-radio v-if="site_code == 'dl'" :label="3">响应式</el-radio>
                                <el-radio v-if="site_code == 'zf'" :label="4">原生App</el-radio>
                            </el-radio-group>
                        </el-form-item>
                    </el-col>
                    <el-col :span="8">
                        <el-form-item label="导航" prop="need_navigate" v-if="form.type == 2">
                            <el-radio-group v-model="form.need_navigate">
                                <el-radio :label="1">需要</el-radio>
                                <el-radio :label="0">不需要</el-radio>
                            </el-radio-group>
                        </el-form-item>
                    </el-col>
                    <el-col :span="8">
                        <el-form-item label="自定义" prop="need_navigate">
                            <el-radio-group v-model="form.is_custom" :disabled="Boolean(form.id)">
                                <el-radio :label="1">是</el-radio>
                                <el-radio :label="0">否</el-radio>
                            </el-radio-group>
                        </el-form-item>
                    </el-col>
                </el-row>
                <el-row>
                    <el-col :span="24">
                        <el-form-item label="组件应用站点" label-width="110px" prop="checkedSites" id="check-box">
                            <el-checkbox :disabled="siteDisabled" v-model="checkAll" @change="handleCheckAllChange">所有站点</el-checkbox>
                            <el-checkbox-group v-model="form.checkedSites" @change="handleCheckedSitesChange">
                                <el-checkbox :disabled="siteItem.disabled" v-for="(siteItem,key) in sites" :label="siteItem.code" :key="key">{{siteItem.name}}</el-checkbox>
                            </el-checkbox-group>
                        </el-form-item>
                    </el-col>
                </el-row>
                <el-form-item label="icon">
                    <el-input v-model="form.icon"></el-input>
                </el-form-item>
                <el-form-item label="名称" prop="name">
                    <el-input v-model="form.name"></el-input>
                </el-form-item>
                <el-form-item label="描述" prop="description">
                <el-input type="textarea" v-model="form.description" :rows="4"></el-input>
                </el-form-item>
                <el-form-item>
                <el-upload action="/component/index/upload-logo" name="files" accept="image/jpg,image/jpeg,image/png" :limit="1"
                                :on-success="handleUploadSuccess"
                                :on-exceed="handleUploadExceed"
                                :on-error="handleUploadError"
                                :file-list="fileList"
                                :before-upload="handleBeforeUpload">
                    <el-button size="small" type="primary">点击上传</el-button>
                    <div slot="tip" class="el-upload__tip">只能上传jpeg/png文件，且不超过3M</div>
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
	getComponentList,
	addComponent,
	updateComponent,
	changeComponentStatus,
	getCategoryList,
	getTemplateList
} from '../plugin/api'
import { getCookie } from '../plugin/mUtils'

// 筛选项
import filterLayout from './componentManage/filter.vue'

export default {
	components: {
		siteLayout,
		filterLayout
	},
	data() {
		return {
			checkAll: false,
			sites: [],
			submitLoading: false,
			site_code: '',
			total: '',
			currentPage: 1,
			fileList: [],
			componentList: [],
            categoryList: [],
            dialogTitle: '新增组件',
			dialogVisible: false,
			uploadedImg: '',
			form: {
				id: null,
				type: '',
				range: '1',
				place: '',
				need_navigate: 0,
				category_id: '',
                icon: '',
				name: '',
				checkedSites: [],
				description: '',
				logo_url: '',
				is_custom: 0,
				tid: ''
			},
			rules: {
				type: [
					{ required: true, message: '请输入类型', trigger: 'change' }
				],
				category_id: [
					{ required: true, message: '请输入分类', trigger: 'change' }
				],
				name: [
					{ required: true, message: '请输入名称', trigger: 'blur' }
				],
				description: [
					{ required: true, message: '请输入描述', trigger: 'blur' }
				],
				range: [
					{ required: true, message: '请选择组件应用端', trigger: 'change' }
				],
				place: [
					{ required: true, message: '请选择组件应用环境', trigger: 'change' }
				],
				checkedSites: [
					{ required: true, message: '请选择组件应用站点', trigger: 'change' }
				]
			},
			options: [],
			layoutOptions: [],
			componentOptions: [],
			templateOptions: [],
			// 判断是否请求列表，阻止多次请求
			loadingList: false,
		}
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
        
        // ADD - Dialog 获取模版列表
		async getTemplates(key) {
			var res = await getTemplateList({
				key: key,
				status: 1,
				pageNo: 1,
				pageSize: 10
			});
			this.templateOptions = res.data.list;
        },

        /**
         * ADD - Dialog 获取组件分类
         * type 组件类型，1=布局，2=内容
         * place 应用场景，1=活动，2=首页
         */
		async getOptions(type, place) {
            // 获取当前筛选的值
            const filterLayer = Object.assign(this.$refs.filterLayer.formdata, {});

            // 组装数据
			let params = {
				type: filterLayer.type,
				place: filterLayer.place
			}

			if (type) {
				params.type = this.form.type
			}

			if (place) {
				params.place = this.form.place
			}

            try {
                const res = await getCategoryList(params);
    			this.options = res.data.list || [];
            } catch (err) {
                this.$message.error('获取分类错误');
            }
        },

        // 获取组件列表
		async getComponents() {
			// 避免多次请求
			if (this.loadingList == true) return false;

            // 获取筛选项的参数, 补全 page 页码的参数
            let request = Object.assign(this.$refs.filterLayer.formdata, {
                pageNo: this.currentPage,
                pageSize: 10,
            });
            try {
				this.loadingList = true;
                const res = await getComponentList(request);
                this.componentList = res.data.list || [];
				this.total = res.data.totalCount || 0;
            } catch (err) {
				this.$message.error('服务端错误，请重试');
			}
			this.loadingList = false;
        },
        // ADD - Dialog - 更改类型
		changeComponentType(val) {
			this.form.category_id = ''
			this.getOptions(val, this.form.place)
        },
        // ADD - Dialog - 提交表单
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
						logo_url: this.uploadedImg,
						range: this.form.range,
						need_navigate: this.form.need_navigate,
						category_id: this.form.category_id,
                        icon: this.form.icon,
						name: this.form.name,
						type: this.form.type,
						place: this.form.place,
						description: this.form.description,
						siteGroups: this.form.checkedSites.toString()
					}

					if (this.form.id !== null) {
						params.id = this.form.id
						params.tid = this.form.tid
						res = await updateComponent(params)
					} else {
						params.is_custom = this.form.is_custom
						params.type = this.form.type
						res = await addComponent(params)
					}

					if (res.code == 0) {
						this.resetForm('form')
						this.getComponents()
					} else {
						this.submitLoading = false
					}
				} else {
					this.submitLoading = false
				}
			})
		},
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
			this.getComponents()
		},
		handlePlaceChange(val) {
			this.form.category_id = ''
			this.getOptions(this.form.type, val)
		},
		async changeStatus(componentKey, statusCode) {
			let message

			if (statusCode == 5) {
				message = '确认下线该组件？'
			} else if (statusCode == 3) {
				message = '确认上线该组件？'
			}

			this.$confirm(message, '提示', {
				confirmButtonText: '确定',
				cancelButtonText: '取消',
				type: 'warning'
			}).then(async () => {
				let params = {
					key: componentKey,
					status: statusCode
				}

				await changeComponentStatus(params)
				this.getComponents()
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

		/**
		 * 搜索
		 */
		doSearch () {
            // 重置页码为1
            this.currentPage = 1;
            // 搜索组件
			this.getComponents();
        },
        
        // 编辑组件
		editComponent(row) {
            this.dialogTitle = '编辑组件';

            // 因为列表数据不包含判断组件类型的字段，所以获取当前筛选项的值
            const filterData = Object.assign(this.$refs.filterLayer.formdata, {});
            this.form.type = String(filterData.type);
            // 获取组件的字段
			this.form.id = row.id
			this.form.logo_url = row.logo_url
			this.form.place = row.place
			this.form.range = row.range
			this.form.need_navigate = row.need_navigate
			this.form.category_id = row.category_id
			this.form.name = row.name
			this.form.description = row.description
			this.form.is_custom = row.is_custom
			this.form.tid = row.tpl_id
            this.form.icon = row.icon

			if (row.siteGroups) {
				this.form.checkedSites = row.siteGroups.split(",")
				this.checkAll = this.form.checkedSites.length === this.sites.length
			}

			this.fileList.push({
				name: row.logo_url,
				url: row.logo_url
			})
			this.uploadedImg = row.logo_url

			this.getOptions(this.form.type, this.form.place)

			this.getTemplates(row.component_key)

			this.dialogVisible = true
        },
        // 新增组件弹出层
		addComponent() {
            this.dialogTitle = '新增组件';
			this.form.id = null
			this.form.logo_url = ''
			this.form.type = ''
			this.form.place = ''
			this.form.range = 1
			this.form.need_navigate = 0
			this.form.category_id = ''
			this.form.name = ''
			this.form.description = ''
			this.form.is_custom = 0
			this.dialogVisible = true
		},

		/**
		 * 跳转到当前组件模版列表
		 */
		handleGoToTemplate (component_key) {
			window.location.href = '/component/component-tpl/index?key=' + component_key;
		},

        handleRangeChange (val) {
            this.form.checkedSites = [];
            const siteMaps = this.sites.map(item => {
                item.disabled = false;
                if (val == 4 && item.code != 'zf') {
                    item.disabled = true;
                }
                return item;
            });
            this.sites = [...siteMaps];
        }
    },
    mounted () {
        // 获取 options
        this.getOptions();

        // 获取组件列表
        this.getComponents();
        
		// 存储cookie值
		this.site_code = getCookie('site_group_code');
	},
    computed: {
        siteDisabled () {
            return this.form.range == 4 ? true : false;
        }
    }
}
</script>

<style lang="less" scoped>
	#check-box .el-checkbox-group {
		display: inline-block;
		margin-left: 30px;
	}
    .form-range .el-radio+.el-radio{
        margin-left: 24px;
    }
</style>
