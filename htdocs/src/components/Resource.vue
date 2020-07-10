<template>
	<site-layout>
		<el-row class="handle-box">
			<!-- <i class="el-icon-arrow-left" @click="getPrePth">返回上一级</i>|<i class="el-icon-document" @click="getAllPath">&nbsp;全部文件</i>
      <el-col :span="15" class="currentPath">当前目录：{{fileClass.filePath}}</el-col> -->
			<el-col :span="10" class="path-padding">
				<i class="el-icon-arrow-left back-path" v-if="preVisible" @click="getPrePth">返回</i>
				<el-breadcrumb separator="/">
					<el-breadcrumb-item>
						<span @click="crumbChange(0)">文件列表</span>
					</el-breadcrumb-item>
					<el-breadcrumb-item v-for="item in crumbLists" :key="item.id">
						<span @click="crumbChange(item.id)">{{item.name}}</span>
					</el-breadcrumb-item>
				</el-breadcrumb>
			</el-col>
			<el-col :span="4" class="text-center search-type">
				<el-button size="small" @click="SearchTypeChange(0)" :class="searchType == 0 ?'active':''">我的资源
				</el-button>
				<!-- <el-button size="small" @click="SearchTypeChange(1)" :class="searchType == 1 ?'active':''">公共资源
				</el-button> -->
			</el-col>

		</el-row>
		<el-row>

			<el-col :span="3">
				<el-input v-model="searchOp.searchWord" placeholder="搜索文件名称" @change="handleSearchChange()">
					<i slot="suffix" class="el-input__icon el-icon-search"></i>
				</el-input>
			</el-col>
			<el-col :span="12" :offset="9" class="text-right">
				<el-button type="primary" size="small" icon="el-icon-plus" @click="resourceOp(2)">新增目录
				</el-button>
				<el-button type="success" size="small" icon="el-icon-plus" @click="resourceOp(0)" v-if="resourceCurrentId !== 0">新增资源
				</el-button>
				<!-- v-if="fileSelectOp.batchStatu" -->
				<el-button type="warning" size="small" icon="el-icon-sort" @click="moveResourceFn(1)">批量移动
				</el-button>
				<el-button type="danger" size="small" icon="el-icon-delete" @click="handleDeleteList">批量删除
				</el-button>

			</el-col>
		</el-row>

		<el-row>
			<el-col :span="24">
				<el-table :data="dataLists" border @cell-click="clickIntoList"  @cell-dblclick="handleRowClick" v-loading="loading" @selection-change="handleSelectionChange"
				  class="resource-table">
					<el-table-column type="selection" width="55"></el-table-column>
					<el-table-column  prop="name" label="名称" min-width="100">
						<template slot-scope="scope">
							<i v-if="scope.row.type !=='1'" class="fa fa-fw" :class="{'fa-folder':(scope.row.type == 0),
                        'fa-file-sound-o':(scope.row.type == 2),
                        'fa-file-movie-o':(scope.row.type == 3)}"></i>
							<img :src="scope.row.thumbnail_url" alt="图片" v-else style="display:block;">
							<span>{{scope.row.name}}</span>
						</template>
					</el-table-column>
					<el-table-column prop="type" label="文件类型" minWidth="80">
						<template slot-scope="scope">
							<span v-if="scope.row.type == '1'">{{scope.row.name|photoTypeSplit}}</span>
							<span v-else>{{scope.row.type | fileListText}}</span>
						</template>
					</el-table-column>
					<el-table-column label="尺寸">
						<template slot-scope="scope">
							<span>{{scope.row.width+'*'+scope.row.height}}</span>
						</template>
					</el-table-column>
					<el-table-column label="文件大小">
						<template slot-scope="scope">
							<span v-if="scope.row.size!=='0'">{{scope.row.size | fileSizeTrans}}</span>
						</template>
					</el-table-column>
					<el-table-column prop="url" label="文件地址(双击复制)" min-width="160">
						<template slot-scope="scope">
							<div class="fileLinkWrap">
								{{scope.row.url}}
								<input type="text" :value="scope.row.url" class="fileLink">
							</div>

						</template>
					</el-table-column>
					<el-table-column prop="create_user" label="创建人"></el-table-column>
					<el-table-column label="创建时间" sortable prop="create_time">
						<template slot-scope="scope">
							<span>{{ Number(scope.row.create_time) | moment('YYYY-MM-DD HH:mm:ss') }}</span>
						</template>
					</el-table-column>
					<el-table-column label="操作" min-width="234">
						<template slot-scope="scope">
							<el-button type="primary" size="small" @click="resourceOp(1,scope.row.id)">重命名</el-button>
							<el-button type="primary" size="small" @click="moveResourceFn(0,scope.row.id)">移动</el-button>
							<el-button type="danger" size="small" @click="removeResource(scope.row.id)">删除</el-button>
						</template>
					</el-table-column>
				</el-table>
			</el-col>
		</el-row>

		<el-row>
			<el-col :span="24" class="text-right" v-if="pageInfo.totalCount>10">
				<el-pagination layout="prev, pager, next" :total="pageInfo.totalCount" :current-page.sync="pageInfo.pageNo" @current-change="handleCurrentChange"></el-pagination>
			</el-col>
		</el-row>

		<el-dialog :title="dialogForm.title?dialogForm.title:'新增资源'" :visible.sync="dialogResourceVisible" width="60%" @close="resourceEditClose">
			<el-form :model="resourceForm" :rules="resourceRules" ref="resourceForm" label-width="80px" v-loading="dialogForm.dialogLoading"
			  :element-loading-text="dialogForm.loadingText">
				<!-- 文件夹级联 -->
				<el-form-item label="文件夹" v-if="dialogForm.type!='1'">
					<el-cascader :options="brotherInfo.options" @active-item-change="handleItemChange" :props="brotherInfo.props" @change="handleCasChange"
					  change-on-select clearable ref="addTreeCascader" name="treeList" placeholder="默认当前目录"></el-cascader>
				</el-form-item>
				<el-form-item label="类型" prop="type" v-if="resourceForm.type !==0">
					<el-radio-group v-model="resourceFormLabel" @change="fileTypeChange">
						<el-radio-button v-for="item in fileType" :key="item.value" :label="item.label" :disabled="disabledType"></el-radio-button>
					</el-radio-group>
				</el-form-item>
				<el-form-item label="名称" prop="name" v-if="resourceForm.type == 0 || dialogForm.type == '1'">
					<el-input v-model="resourceForm.name"></el-input>
				</el-form-item>
				<el-form-item prop="url" v-if="dialogForm.type==0 && resourceForm.type && resourceForm.type!==0 && resourceForm.type!==1 && resourceForm.type!==4"
				  :data-type="resourceForm.type">
					<el-input v-model="resourceForm.url" style="display:none;"></el-input>
					<el-upload action="/admin/resource/upload" multiple name="files" :limit="1" :on-exceed="fileLimitFn" :file-list="fileList"
					  :on-success="handleAvatarSuccess" :on-error="handleAvatarError" :before-upload="beforeAvatarUpload">
						<el-button size="small" type="primary">点击上传</el-button>
						<!-- <div slot="tip" class="el-upload__tip">只能上传jpg/png文件，且不超过500kb</div> -->
					</el-upload>
				</el-form-item>
				<div v-if="dialogForm.type == 0 && resourceForm.type == 1">
					<input type="file" ref="upload1" id="upload1" name="files" style="position:absolute; clip:rect(0 0 0 0);" accept="image/png, image/jpeg, image/gif, image/jpg"
					  @change="uploadImg($event, 2)">
					<el-row class="cropperWrapper">
						<label for="upload1" ref="cropperBtn" style="display:none;">点击上传</label>
						<el-col :span="24" v-if="!cropperOp.visible" class="cropper-outWrap">
							<div class="button-box">
								<label for="upload1" class="el-button el-button--primary el-button--small">点击上传</label>
								<span class="cropper-tip">支持jpg,png,gif格式，图片大小不超过3M</span>
							</div>
						</el-col>
						<div :span="24" v-if="cropperOp.visible" class="cropperInner">
							<VueCropper ref="cropper" :img="cropperOp.img" :outputType="cropperOp.outputType" :outputSize="cropperOp.size" :canScale="cropperOp.canScale"
							  :autoCrop="cropperOp.autoCrop" :autoCropWidth="cropperOp.autoCropWidth" :autoCropHeight="cropperOp.autoCropHeight"
							  :original="cropperOp.original" :full="cropperOp.full"></VueCropper>
							<button type="button" class="el-button el-button--primary el-button--small icon-cropper" @click="cropperToggle"><img src="/resources/images/icon/icon-cropper.png" alt="cropper">
								<span>{{cropperOp.cropperText}}</span>
							</button>
							<i class="el-icon-close cropper-close" @click="cropperClose"></i>
						</div>
						<!-- <el-col :span="12" class="cropperView text-center">
                            <img :src="this.resourceForm.url" alt="">
                        </el-col> -->
					</el-row>
					<el-row style="margin-left: 80px;" v-if="cropperOp.cropperStatus">
						<el-col :span="12" class="cropperSize">
							<span style="margin-right:80px;">裁剪尺寸设置(px)</span>
							宽
							<el-input v-model="cropperOp.cropW" placehodler="宽" label="宽"></el-input>
							高
							<el-input v-model="cropperOp.cropH" placehodler="高" label="高"></el-input>
						</el-col>
					</el-row>
				</div>
				<el-row v-if="dialogForm.type == 1 && resourceForm.type == 1">
					<el-col :span="24" class="cropperView text-center">
						<img :src="this.resourceForm.url" alt="">
					</el-col>
				</el-row>
				<!-- 多图片上传 -->
				<el-form-item v-if="dialogForm.type == 0 && resourceForm.type == 4" style="width:440px;">
					<!-- <input type="file" ref="uploadMultiple" id="uploadMultiple" multiple name="files[]" style="position:absolute; clip:rect(0 0 0 0);" accept="image/png, image/jpeg, image/gif, image/jpg" @change="uploadImg($event)"> -->
					<!-- <label for="uploadMultiple" class="el-button el-button--primary el-button--small">批量上传</label> -->
					<el-upload class="uploadMultiple" ref="uploadMultiple" name="files" :data="uploadData" action="/admin/resource/multi-file-upload"
					  :file-list="fileList" accept="image/*" multiple drag :limit="20" :on-exceed="exceedLimit" :before-upload="beforeAvatarUpload"
					  :on-error="multiUploadError" :on-success="multiUploadSuccess" :on-change="multiHandleChange">
						<i class="el-icon-upload"></i>
						<div class="el-upload__text">将文件拖到此处，或
							<em>点击上传</em>
						</div>
						<div class="el-upload__tip" slot="tip">只能上传jpg/png/gif/bmp/wepb文件，且不超过3M</div>
						<!-- <el-button slot="trigger" size="small" type="primary">选取文件</el-button>
						<div slot="tip" class="el-upload__tip">只能上传jpg/png/gif文件，且不超过3M</div> -->
					</el-upload>

				</el-form-item>
				<el-form-item>
					<el-button @click="resetForm('resourceForm')" size="small">取消</el-button>
					<!-- <el-button v-if="dialogForm.type == 0 && (resourceForm.type == 1 || resourceForm.type == 4)" type="primary" @click="cropperSure" size="small">确定</el-button> -->
					<el-button v-if="dialogForm.type == 0 && (resourceForm.type == 1)" type="primary" @click="cropperSure" size="small" :loading="submitLoading">确定</el-button>
					<el-button v-else-if="dialogForm.type == 0 && (resourceForm.type == 4)" type="primary" @click="multiUploadCallback" size="small">确定</el-button>
					<el-button v-else type="primary" @click="submitForm('resourceForm')" size="small" :loading="submitLoading">确定</el-button>
				</el-form-item>
			</el-form>
			<form ref="copperForm" enctype="multipart/form-data">

			</form>
		</el-dialog>

		<el-dialog title="图片预览" :visible.sync="imgPreviewOp.visible" width="50%" @close="imgPreviewClose" v-loading="loading">
			<el-row>
				<el-col :span="24" class="imgPreview text-center">
					<img :src="imgPreviewOp.url">
				</el-col>
			</el-row>
		</el-dialog>
		<!-- 素材移动 -->
		<el-dialog title="素材移动" :visible.sync="brotherInfo.visible" width="50%" @close="moveDialogClose">
			<el-row>
				<el-col :span="24" class="resource-moveWrape">
					<label for="treeList" class="el-form-item__label" style="width: 80px;">目录列表</label>
					<div class="cascaderWrap" style="margin-left: 80px;">
						<el-cascader :options="brotherInfo.options" clearable @active-item-change="handleItemChange" :props="brotherInfo.props" @change="handleCasChange"
						  change-on-select ref="resouceCascader" name="treeList" placeholder="请移动目录"></el-cascader>
					</div>
				</el-col>
			</el-row>
			<el-row>
				<el-button size="small" @click="handleMoveCancel">取消</el-button>
				<el-button type="primary" size="small" @click="handleMoveSure">确定</el-button>
			</el-row>
		</el-dialog>
		<!-- 文件上传容器 -->
		<!-- <el-row>
			<div class="upload-ft-wrapper">
				<el-upload class="uploadMultiple" ref="uploadMultiple" name="files" :data="uploadData" action="/admin/resource/multi-file-upload" :file-list="fileList" accept="image/*" multiple :limit="20" :on-exceed="exceedLimit" :before-upload="beforeAvatarUpload" :on-error="multiUploadError">
					<i class="el-icon-upload"></i>
				</el-upload>
			</div>
		</el-row> -->
	</site-layout>
</template>

<script>
import siteLayout from './layouts/Layout.vue'
import VueCropper from 'vue-cropper'
import { getResourceCrumbs, getResourceList, getResourceListAll, getResourceInfo, resourceDel, resourceDelList, resourceSearch, resourceSearchAll, resourceUpload, resourceMultiUpload, resourceMove } from '../plugin/api'
import fetch from '../plugin/fetchApi'

export default {
	components: { siteLayout, VueCropper },
	data () {
		return {
			dataLists: [],
			pageInfo: {
				pageSize: 10,
				pageNo: 1,
				totalCount: 0
			},
			fileType: [
				// { label: '目录', value: 0 },
				{ label: '多图片', value: 4 },
				{ label: '单图裁剪', value: 1 },
				// { label: '音频', value: 2 },
				// { label: '视频', value: 3 },
			],
			/* 是否公共资源,0个人,1全部 */
			searchType: 0,
			categoryForm: {
				parent_id: '',
				name: ''
			},
			categoryRules: {
				parent_id: [
					{ required: true, message: '请输入分类', trigger: 'blur' }
				],
				name: [
					{ required: true, message: '请输入名称', trigger: 'blur' }
				]
			},
			//查询条件
			searchOp: {
				searchWord: ''
			},
			dialogCategoryVisible: false,
			resourceForm: {
				parent_id: 0,
				type: 0,	//0目录,1图片,2音频,3视频,4批量
				name: '',
				url: ''
			},
			/* 当前文件类型 */
			resourceFormLabel: '目录',
			/* 当前层级id */
			resourceCurrentId: 0,
			resourceParentId: 0,
			resourceRules: {
				parent_id: [
					{ required: true, message: '请输入分类', trigger: 'blur' }
				],
				type: [
					{ required: true, message: '请选择类型', trigger: 'blur' }
				],
				name: [
					{ required: true, message: '请输入名称', trigger: 'blur' }
				]
			},
			uploadData: {
				parent_id: 0
			},
			dialogForm: {
				title: '新增资源',
				type: 0,	//0 新增 1 编辑 2新增目录
				dialogLoading: false,
				loadingText: '',
				progress: 0
			},
			dialogResourceVisible: false,
			fileList: [],
			fileLimit: 5,
			loading: false,
			disabledType: true,
			fileClass: {
				filePath: '/',
				filePathArr: ['/'],
				fileArr: [0]
			},
			//面包屑
			crumbLists: [],
			cropperOp: {
				visible: false,
				outputType: 'jpeg',
				size: .8,
				img: '',
				canScale: true,
				autoCrop: true,
				autoCropWidth: 250,
				autoCropHeight: 200,
				full: true,//是否输出原图比例的截图
				original: false,
				fixed: false,
				fixedNumber: [4, 3],
				//是否使用裁剪
				cropperStatus: false,
				//裁剪状态描述
				cropperText: '开始裁剪',
				cropW: 250,
				cropH: 200
			},
			imgPreviewOp: {
				visible: false,
				img: ''
			},
			preVisible: false,
			popstateCount: 0,
			//文件选中
			fileSelectOp: {
				ids: '',
				batchStatu: false,
			},
			//素材移动信息
			brotherList: [],
			brotherInfo: {
				visible: false,
				idArr: [],
				ids: '',
				//级联数据
				options: [{
					label: '根目录',
					value: 0,
					id: 0,
					children: []
				}],
				props: {
					id: 'id',
					value: 'value',
					label: 'label',
					children: 'children'
				},
				//当前选中的
				parent_id: 0,
				current_id: 0,
				parentArr: [],
				pageInfo: {
					pageNo: 1,
					pageSize: 10
				}
			},
			/* 按钮loading */
			submitLoading: false
		}
	},
	computed: {
		fileListType: function () {
			return 'text/picture/picture-card'
			// return this.resourceForm.type !== 1 ? "picture" : "text/picture/picture-card";
		},
	},
	filters: {
		photoTypeSplit: function (value) {
			return value.split('.')[1] ? value.split('.')[1] : '图片'
		},
		fileSizeTrans: function (value) {
			let size = Number(value)
			let kb = 1024
			let mb = kb * 1024
			let gb = mb * 1024
			if (size < mb) {
				return (size / kb).toFixed(2) + 'KB'
			} else if (size < gb) {
				return (size / mb).toFixed(2) + 'MB'
			}
		},
		fileListText: function (value) {
			let text = '目录'
			if (value == 0) {
				text = '目录'
			} else if (value == 1) {
				text = '单图裁剪'
			} else if (value == 2) {
				text = '音频'
			} else if (value == 3) {
				text = '视频'
			}
			return text
		}
	},
	created () {
		window.addEventListener('popstate', this.handlePopState)
		this.crumbChange(0)
	},
	beforeDestroy () {
		window.removeEventListener('popstate', this.handlePopstate)
	},
	watch: {
		'resourceForm.type': {
			handler (newValue) {
				this.fileList = []
				let text = '目录'
				if (newValue == 0) {
					text = '目录'
				} else if (newValue == 1) {
					text = '单图裁剪'
				} else if (newValue == 4) {
					text = '多图片'
				}
				// else if (newValue == 2) {
				// 	text = '音频'
				// } else if (newValue == 3) {
				// 	text = '视频'
				// }
				this.resourceFormLabel = text
			},
			deep: true
		},
		resourceCurrentId: function (val) {
			if (val != 0 && this.preVisible == false) {
				this.preVisible = true
			} else if (val == 0 && this.preVisible == true) {
				this.preVisible = false
			}
		},
		//裁剪框
		'cropperOp.cropW': {
			handler (newValue) {
				this.$refs.cropper.changeCrop(newValue, this.cropperOp.cropH)
			}
		},
		'cropperOp.cropH': {
			handler (newValue) {
				this.$refs.cropper.cropH = newValue
				this.$refs.cropper.changeCrop(this.cropperOp.cropW, newValue)
			}
		}
	},
	methods: {
		submitForm (formName) {
			let _this = this
			this.$refs[formName].validate((valid) => {
				if (valid) {
					let params, url
					let selectTree = []
					//选择上传文件夹
					if (this.$refs.addTreeCascader) {
						selectTree = this.$refs.addTreeCascader.currentValue
					}
					let currentValue = selectTree.length == 0 ? this.resourceCurrentId : selectTree[selectTree.length - 1]
					//判断提交类型
					if (formName == 'resourceForm') {
						params = this.resourceForm
						if (this.dialogForm.type === 0 || this.dialogForm.type === 2) {
							url = '/admin/resource/add'
							params.parent_id = currentValue
							if (params.type !== 0 && !params.url) {
								this.$message('请上传资源')
								return false
							}
						} else if (this.dialogForm.type === 1) {
							url = '/admin/resource/edit'
							params.id = this.dialogForm.id
							params.parent_id = this.resourceCurrentId
						}
					}
					this.loading = true
					this.submitLoading = true
					fetch.post(url, params).then(res => {
						this.loading = false
						this.submitLoading = false
						if (res.code == '0') {
							if (selectTree.length == 0) {
								this.handleLists(this.pageInfo.pageNo, this.resourceForm.parent_id)
							} else {
								this.crumbChange(selectTree[selectTree.length - 1])
							}

							this.closeDialog(formName)
						} else {
							this.$message(res.message)
						}
					}).catch(() => {
						_this.loading = false
						_this.submitLoading = false
					})
				}
			})

		},
		SearchTypeChange (statu) {
			let oldStatu = this.searchType
			if (statu !== oldStatu) {
				this.searchType = statu
				this.crumbChange(0)
			}

		},
		handlePopState () {
			let param = history.state
			if (param) {
				this.pageInfo.pageNo = param.pageNo
				//历史变更获取数据
				if (this.resourceCurrentId != '0') {
					this.crumbChange(param.id, true)
				}
			}


		},
		handleCrumbs (id) {
			getResourceCrumbs({ id: id }).then(res => {
				if (res.code == '0') {
					let data = res.data
					this.crumbLists = data
					if (data) {
						this.resourceParentId = data[data.length - 1].parent_id
						// this.resourceCurrentId = data.id
					}
				}
			})
		},
		//资源层级跳转
		crumbChange (id, history) {
			//init
			// this.resourceCurrentId = id
			this.pageInfo.pageNo = 1
			this.searchOp.searchWord = ''

			const param = {
				id: id,
				pageNo: this.pageInfo.pageNo,
				pageSize: this.pageInfo.pageSIze,
			}
			if (!history) {
				if (id !== 0) {
					window.history.pushState(param, '', '')
				} else {
					window.history.replaceState(param, '', '')
				}
			}
			if (id !== 0) {
				this.handleCrumbs(id)
			}
			this.handleLists(1, id)
		},
		/* id 目录id */
		handleLists (currentPage, id) {
			this.loading = true
			let params = {
				id: id,
				pageNo: currentPage ? currentPage : this.pageInfo.pageNo,
				pageSize: this.pageInfo.pageSize
			}
			if (id === 0) {
				// this.fileClass.filePath = '/';
				// this.fileClass.fileArr = [0];
				this.crumbLists = []
			}
			let listFn = this.searchType == 0 ? getResourceList : getResourceListAll


			listFn(params).then(res => {
				this.loading = false
				if (res.code == '0') {
					let data = res.data
					this.dataLists = data.list
					this.pageInfo.pageNo = data.pagination.pageNo
					this.pageInfo.totalCount = Number(data.pagination.totalCount)
					this.resourceCurrentId = id
				} else {
					this.$message(res.message)
					if (this.searchType == 1) {
						this.SearchTypeChange(0)
					}
				}
			}).catch(() => {
				this.loading = false
			})

		},
		handleCurrentChange (currentPage) {
			this.pageInfo.pageNo = currentPage
			if (this.searchOp.searchWord) {
				this.handleSearch(currentPage)
			} else {
				this.handleLists(currentPage, this.resourceCurrentId)
			}

		},
		handleInfo (id) {
			let _this = this
			this.submitLoading = true
			getResourceInfo({ id: id }).then(res => {
				this.submitLoading = false
				if (res.code == '0') {
					let data = res.data
					this.resourceForm = data
					this.dialogResourceVisible = true
				} else {
					this.$message(res.message)
				}
			}).catch(() => {
				_this.submitLoading = false
			})
		},
		clickIntoList(row, column, cell, event){
			if (column.label == '名称' && column.label) {
				this.crumbChange(row.id)
			}
		},
		handleRowClick (row, column, cell, event) {
			if (column.label == '操作' || !column.label) {
				return false
			}
			if (column.label == '文件地址(双击复制)') {
				let target = event.target.nodeName == 'INPUT' ? event.target : event.target.getElementsByTagName('INPUT')[0]
				if (!target.value) {
					return false
				}
				target.select()
				document.execCommand('copy')
				this.$message({ type: 'success', message: '复制成功' })
				return false
			}
			if (row.type !== '0' && row.type !== '1') {
				return false
			}
			if (row.type == '1') {
				this.imgPreviewVisible(row.id)
				return false
			}
			// this.fileClass.filePath += row.name + "/";
			// this.fileClass.fileArr.push(row.id);
			// this.fileClass.filePathArr.push(row.name + "/");
			this.resourceForm.parent_id = row.id
			this.crumbChange(row.id)
		},
		resetForm (formName) {
			this.resetFields(formName)
			this.closeDialog(formName)
		},
		resetFields (formName) {
			this.$refs[formName].resetFields()
		},
		closeDialog (formName) {
			if (formName == 'categoryForm') {
				this.dialogCategoryVisible = false
			} else {
				this.dialogResourceVisible = false
			}
		},
		//关闭资源编辑
		resourceEditClose () {
			this.resetForm('resourceForm')
			// this.resourceForm.type = 0
			// this.resourceFormLabel = '目录'
			this.dialogForm.loadingText = ''
			this.cropperOp.cropperText = '开始裁剪'
			this.cropperOp.cropperStatus = false
			this.resourceForm = {
				parent_id: this.resourceCurrentId,
				type: 0,
				name: '',
				url: ''
			}
			if (this.$refs.upload1) {
				this.$refs.upload1.value = ''
			}
			if (this.$refs.cropper) {
				this.cropperClose(1)	//
			}

			//清空级联
			let obj = {}
			obj.stopPropagation = () => { }
			if (this.$refs.resouceCascader) {
				this.$refs.resouceCascader.clearValue(obj)
			}
			if (this.$refs.addTreeCascader) {
				this.$refs.addTreeCascader.clearValue(obj)
			}
			this.brotherInfo.parent_id = 0
			this.brotherInfo.parentArr = []
		},
		removeCategory () {
			this.confirm('确认删除该分类?', function () {
				this.$message({
					type: 'success',
					message: '删除成功!'
				})
			})
		},
		removeResource (id) {
			let _this = this
			this.confirm('确认删除该资源?', function () {
				resourceDel({ id: id }).then(res => {
					if (res.code == 0) {
						_this.$message({
							type: 'success',
							message: '删除成功!'
						})
						_this.handleLists(_this.pageInfo.pageNo, _this.resourceCurrentId)
					} else {
						_this.$message(res.message)
					}
				})

			})
		},
		/* resource delete select */
		handleDeleteList () {
			let _this = this
			if (this.brotherInfo.idArr.length < 1) {
				this.$message({ type: 'warning', message: '请选中文件' })
				return false
			}
			this.confirm('确认删除该资源?', function () {
				resourceDelList({ ids: _this.brotherInfo.ids }).then(() => {
					_this.handleLists(_this.pageInfo.pageNo, _this.resourceCurrentId)
				})
			})
		},
		confirm (message, callback) {
			this.$confirm(message, {
				confirmButtonText: '确定',
				cancelButtonText: '取消',
				type: 'warning',
				title: '提示'
			}).then(() => {
				if (typeof callback == 'function') {
					callback()
				}
			}).catch(() => {
				this.$message({
					type: 'info',
					message: '已取消操作!'
				})
			})
		},
		handleSearchChange () {
			let keyword = this.searchOp.searchWord
			if (keyword) {
				this.handleSearch()
			} else {
				this.crumbChange(this.resourceCurrentId || 0)
			}
		},
		handleSearch (currentPage) {
			let _this = this
			let param = {
				search: this.searchOp.searchWord,
				pageNo: currentPage || 1,
				pageSize: this.pageInfo.pageSize
			}
			this.loading = true
			let handleFn = this.searchType == 0 ? resourceSearch : resourceSearchAll
			handleFn(param).then(res => {
				_this.loading = false
				if (res.code == 0) {
					let data = res.data
					_this.dataLists = data.list
					_this.pageInfo.pageNo = data.pagination.pageNo
					_this.pageInfo.totalCount = Number(data.pagination.totalCount)
				}
			})
		},
		//图片预览
		async imgPreviewVisible (id) {
			let res = await getResourceInfo({ id: id })
			this.imgPreviewOp.url = res.data.url
			this.imgPreviewOp.visible = true
		},
		imgPreviewClose () {
			this.imgPreviewOp.url = ''
		},
		//新增资源
		resourceOp (type, id) {
			this.fileList = []
			this.dialogForm.type = type
			this.cropperOp.img = ''
			if (type === 0) {
				this.dialogForm.title = '新增资源'
				this.resourceForm.type = 4
				this.resourceFormLabel = '多图片'
				this.resourceForm.name = ''
				this.resourceForm.url = ''
				this.dialogResourceVisible = true
				this.disabledType = false
				this.cropperOp.autoCrop = false
			} else if (type === 1) {
				this.dialogForm.title = '资源编辑'
				this.dialogForm.id = id
				this.disabledType = true
				this.handleInfo(id)
			} else if (type === 2) {
				this.dialogForm.title = '新增目录'
				this.resourceForm.type = 0
				this.resourceFormLabel = '新增目录'
				this.resourceForm.name = ''
				this.resourceForm.url = ''
				this.dialogResourceVisible = true
				this.disabledType = false
			}
		},
		handleAvatarSuccess (res) {
			let data = res.data
			this.resourceForm.url = data.url
			this.submitForm('resourceForm')
		},
		handleAvatarError () {
			this.$message.error('上传失败')
		},
		beforeAvatarUpload (file) {
			/* 上传根目录校验 */
			if (this.brotherInfo.parent_id == 0 && this.brotherInfo.parentArr.length > 0) {
				this.$message.warning('根目录不允许上传图片')
				return false
			}

			let fileList = this.fileList
			let newArr = fileList.sort()
			for (var i = 0; i < fileList.length; i++) {
				if (newArr[i + 1] && fileList[i].name == newArr[i + 1].name) {
					this.$message.warning('请勿重复上传')
					return false
				}
			}
			/* file Verification*/
			let uploadType = this.resourceForm.type
			const isOGG = file.type === 'audio/ogg'
			const isMPEG = file.type === 'audio/mpeg'
			const isMP4 = file.type === 'video/mp4'
			const isWEBM = file.type === 'video/webm'
			const isJPG = file.type === 'image/jpeg'
			const isPNG = file.type === 'image/png'
			const isGIF = file.type === 'image/gif'
			const isBMP = file.type === 'image/x-ms-bmp'
			const isWEBP = file.type === 'image/webp'
			const isLt3M = file.size / 1024 / 1024 < 3
			if (uploadType == 2) {
				if (!isOGG && !isMPEG) {
					this.$message.warning('音频类型必须是mpeg,ogg中的一种')
					return false
				}
			}
			if (uploadType == 3) {
				if (!isMP4 && !isWEBM && !isOGG) {
					this.$message.warning('视频类型必须是mp4,ogg,webm中的一种')
					return false
				}
			}
			if (!isLt3M) {
				this.$message.warning('文件大小需不超过3M')
				return false
			}
			if (!4 == uploadType && !this.resourceForm.name) {
				this.$message.warning('请补充名称')
				return false
			}
			if (uploadType == 4 || uploadType == 1) {
				if (!isJPG && !isPNG && !isGIF && !isBMP && !isWEBP) {
					this.$message.warning('图片格式不正确')
					return false
				}
			}

			let selectTree = []
			//选择上传文件夹
			if (this.$refs.addTreeCascader) {
				selectTree = this.$refs.addTreeCascader.currentValue
			}
			let currentValue = selectTree.length == 0 ? this.resourceCurrentId : selectTree[selectTree.length - 1]
			this.uploadData.parent_id = currentValue
		},
		fileLimitFn () {
			this.$message.error('文件列表个数限制为1个')
		},
		exceedLimit () {
			this.$message('文件最多同时上传20个')
		},
		/* 返回上一级 */
		getPrePthLast () {
			let currentPathArr = this.fileClass.filePath.split('/')
			let prePath = currentPathArr.slice(0, currentPathArr.length - 2).toString().replace(/,/g, '/') + '/'

			let preFileArr = this.fileClass.fileArr.slice(0, this.fileClass.fileArr.length - 1)
			let preFileId = preFileArr[preFileArr.length - 1]
			this.fileClass.filePath = prePath
			this.fileClass.fileArr = preFileArr
			this.resourceForm.parent_id = preFileId
			this.crumbChange(preFileId)
		},
		getPrePth () {
			if (this.resourceCurrentId !== 0) {
				if (history) {
					history.go(-1)
				} else {
					this.crumbChange(this.resourceParentId, true)
				}
			} else {
				return false
			}
		},
		/* 全部文件,（已取消） */
		getAllPath () {
			this.resourceForm.parent_id = 0
			this.handleLists(1, 0)
		},
		fileTypeChange (val) {
			let type
			if (val == '目录') {
				type = 0
			} else if (val == '单图裁剪') {
				type = 1
			} else if (val == '音频') {
				type = 2
			} else if (val == '视频') {
				type = 3
			} else if (val == '多图片') {
				type = 4
			}
			this.resourceForm.type = type
			this.resourceFormLabel = val
		},
		uploadImg (e, num) {
			//上传图片
			// this.cropperOp.img
			var file = e.target.files[0]
			if (!/\.(gif|jpg|jpeg|png|bmp|webp|GIF|JPG|PNG|WEBP)$/.test(e.target.value)) {
				this.$message('图片类型必须是.gif,jpeg,jpg,png,bmp,webp中的一种')
				return false
			}
			if (!(file.size / 1024 / 1024 < 3)) {
				this.$message.warning('文件大小需不超过3M')
				return false
			}

			this.cropperOp.name = file.name
			var reader = new FileReader()
			//多图上传处理
			if (this.resourceForm.type == 4) {
				return false
			}
			//单图裁剪处理
			reader.onload = (e) => {
				let data
				if (typeof e.target.result === 'object') {
					data = window.URL.createObjectURL(new Blob([e.target.result]))
				} else {
					data = e.target.result
				}
				if (num === 1) {
					this.cropperOp.img = data
				} else if (num === 2) {
					this.cropperOp.img = data
				}
				this.cropperOp.visible = true
			}
			// 转化为base64
			// reader.readAsDataURL(file)
			// 转化为blob
			reader.readAsArrayBuffer(file)
		},
		cropperToggle () {
			let cropperText = this.cropperOp.cropperText
			let $cropper = this.$refs.cropper
			//开始裁剪
			if (cropperText == '开始裁剪') {
				this.cropperOp.autoCrop = true
				this.cropperOp.cropperText = '取消裁剪'
				this.cropperOp.cropperStatus = true
				$cropper.cropW = this.cropperOp.cropW
				$cropper.cropH = this.cropperOp.cropH
				$cropper.reload()
				// $cropper.startCrop()

				// $cropper.cropping = true
			} else {
				$cropper.clearCrop()
				this.cropperOp.cropperText = '开始裁剪'
				this.cropperOp.cropperStatus = false
			}

		},
		cropperClose (type = 0) {
			this.$refs.cropper.clearCrop()
			this.cropperOp.visible = false
			document.getElementById('upload1').value = ''
			this.cropperOp.cropperText = '开始裁剪'
			this.cropperOp.cropperStatus = false
			this.cropperOp.autoCrop = false
			this.cropperOp.cropW = this.cropperOp.autoCropWidth
			this.cropperOp.cropH = this.cropperOp.autoCropHeight
			//完全关闭
			if (type == 0) {
				this.$refs.cropperBtn.click()
			}

		},
		cropperSure () {
			let _this = this
			/* 上传根目录校验 */
			if (this.brotherInfo.parent_id == 0 && this.brotherInfo.parentArr.length > 0) {
				this.$message.warning('根目录不允许上传图片')
				return false
			}

			/* 裁剪确认 */
			if (this.$refs.cropper) {
				this.$refs.cropper.stopCrop()
			}

			if (this.resourceForm.type !== 4) {
				let form = document.forms[1]
				let formData = new FormData(form)
				//若裁剪开始
				if (this.cropperOp.cropperStatus && this.$refs.cropper.cropping) {
					this.$refs.cropper.getCropBlob((data) => {

						formData.append('files', data, this.cropperOp.name)
						_this.resouceUpSure(formData)
					})
				} else if (this.resourceForm.type == 1) {
					let files = this.$refs.upload1.files[0]
					if (!files) {
						this.$message.warning('请上传图片')
						return false
					}

					formData.append('files', files, files.name)
					_this.resouceUpSure(formData)
				}

			} else {
				/* 多图批量模式 */
				let form = document.forms[1]
				let formData = new FormData(form)
				let files = this.$refs.uploadMultiple.uploadFiles//this.$refs.uploadMultiple.files
				for (var i = 0; i < files.length; i++) {
					formData.append('files[]', files[i].raw, files[i].raw.name)
				}
				let selectTree = []
				//选择上传文件夹
				if (this.$refs.addTreeCascader) {
					selectTree = this.$refs.addTreeCascader.currentValue
				}
				let currentValue = selectTree.length == 0 ? this.resourceCurrentId : selectTree[selectTree.length - 1]
				formData.append('parent_id', currentValue)
				_this.resouceUpSure(formData)
			}

			this.dialogForm.dialogLoading = true
			this.submitLoading = true
			this.dialogForm.loadingText = '拼命上传中...'
		},
		resouceUpSure (formData) {
			if (this.resourceForm.type !== 4) {
				this.uploadCallback(resourceUpload, formData)
			} else {
				this.uploadCallback(resourceMultiUpload, formData, this.progressListen)
			}
		},
		//文件上传回调
		uploadCallback (uploadFn, formData, progressFn) {
			let _this = this
			uploadFn(formData, { type: 'file', 'progressFn': progressFn }).then(function (res) {
				_this.dialogForm.dialogLoading = false
				_this.submitLoading = false
				if (res.code == '0') {
					let data = res.data
					_this.resourceForm.url = data.url
					_this.resourceForm.thumbnail_url = data.thumbnail_url
					_this.resourceForm.width = data.width
					_this.resourceForm.height = data.height
					_this.resourceForm.size = data.size
					_this.resourceForm.name = data.name
					/* 单文件上传提交 */
					if (_this.resourceForm.type !== 4) {
						_this.submitForm('resourceForm')
					} else {
						//选择上传文件夹刷新
						let selectTree = []
						if (_this.$refs.addTreeCascader) {
							selectTree = _this.$refs.addTreeCascader.currentValue
						}
						if (selectTree.length == 0) {
							_this.handleLists(_this.pageInfo.pageNo, _this.resourceCurrentId)
						} else {
							_this.crumbChange(selectTree[selectTree.length - 1])
						}

						_this.closeDialog('resourceForm')
					}

				} else {
					_this.$message(res.message || '上传异常')
				}
			}).catch(() => {
				_this.$message({ type: 'warning', message: '上传超时' })
				_this.dialogForm.dialogLoading = false
				_this.submitLoading = false
				_this.closeDialog('resourceForm')
				_this.handleLists(_this.pageInfo.pageNo, _this.resourceCurrentId)
			})
		},
		/* 多图-单文件提交形式*/
		multiUploadCallback () {
			//选择上传文件夹刷新
			let _this = this
			let selectTree = []
			if (_this.$refs.addTreeCascader) {
				selectTree = _this.$refs.addTreeCascader.currentValue
			}
			if (selectTree.length == 0) {
				_this.handleLists(_this.pageInfo.pageNo, _this.resourceCurrentId)
			} else {
				_this.crumbChange(selectTree[selectTree.length - 1])
			}
			this.$message.success('添加成功')
			_this.closeDialog('resourceForm')
		},
		multiUploadError (err, file) {
			this.$message.error(file.raw.name + ' 上传失败')
		},
		multiUploadSuccess (response, file, fileList) {
			if (response && response.code !== 0) {
				this.$message.warning(response.message)
				fileList.forEach(function (element, index) {
					if (element.name == file.name) {
						fileList.splice(index, 1)
					}
				})
			}
		},
		multiHandleChange (file, fileList) {
			this.fileList = fileList
		},
		progressListen () {
			// this.dialogForm.progress = res
		},
		//级联数据重组
		/**
		 * data 数据源
		 * target 需添加的目标
		 */
		casDataTrans (data, target) {
			let selectArr = this.brotherInfo.ids.split(',')
			let newData = []
			data.forEach(function (element) {
				selectArr.forEach(function () {
					if (selectArr.indexOf(element.id) == -1 && newData.indexOf(element.id) == -1 && element.type == '0') {
						newData.push(element.id)
						target.push({
							label: element.name,
							value: element.id,
							id: element.id,
							children: []
						})
					}
				})
			})
		},
		/**@augments
		*options 遍历数据
		*source 需重组的数据
		*id 匹配的id
		 */
		casDataEach (options, source, id) {
			let _this = this
			options.forEach(function (item) {
				if (item.id == id) {
					item.children = []
					_this.casDataTrans(source, item.children)
				}

				if (typeof (item.children) == 'object') {
					_this.casDataEach(item.children, source, id)
				}
			})
		},
		//获取id下cas列表
		async handleMoveList (id) {
			let params = {
				id: id,
				pageNo: 1,
				pageSize: 1000
			}
			let res = await getResourceList(params)
			let source = res.data.list
			// let sourceData = []
			// let parentArr = this.brotherInfo.parentArr 		//多层级['4','9']
			let options = this.brotherInfo.options

			if (options.length == 1 && options[0] == 0) {
				this.casDataTrans(source, options[0].children)
			} else {
				this.casDataEach(options, source, id)
			}

		},
		//素材移动
		moveResourceFn (type, id) {
			if (type && this.brotherInfo.idArr.length < 1) {
				this.$message({ type: 'warning', message: '请选中文件' })
				return false
			}
			this.brotherInfo.visible = true
			if (id) {
				this.brotherInfo.ids = id
			}
			// this.handleMoveList(0, 'first')
		},
		//级联选中变更
		handleItemChange () {

		},
		//级联变更
		handleCasChange (val) {
			if (val.length == 0) {
				return false
			}
			let value = val[val.length - 1]
			this.brotherInfo.parent_id = value
			this.brotherInfo.parentArr = val
			this.handleMoveList(value)
		},
		async handleMoveSure () {
			let $select = this.$refs.resouceCascader.currentValue
			let parentId = $select[$select.length - 1]
			if ($select.length == 0) {
				this.$message.warning('请选中目录列表')
				return false
			}
			let param = {
				parent_id: parentId,
				ids: this.brotherInfo.ids
			}
			resourceMove(param).then(res => {
				if (res.code == 0) {
					this.handleMoveCancel()
					this.crumbChange(parentId)
				}

			})

		},
		handleMoveCancel () {
			this.brotherInfo.visible = false
		},
		moveDialogClose () {
			this.brotherInfo.options = [{
				label: '根目录',
				value: 0,
				id: 0,
				children: []
			}]
		},
		/* 列表选中 */
		handleSelectionChange (val) {
			let arr = []
			val.forEach(function (element) {
				arr.push(element.id)
			})
			this.brotherInfo.idArr = arr
			this.brotherInfo.ids = arr.toString()

			this.fileSelectOp.ids = arr.toString()
			this.fileSelectOp.batchStatu = arr.length > 0 ? true : false
		},

	},


}
</script>

<style lang="less" scoped>
.resource-table {
  cursor: pointer;
}
.path-padding {
  padding: 10px 0;
}
.back-path {
  float: left;
  font-size: 14px !important;
}
.handle-box {
  margin-bottom: 20px;
  color: #8492a6;
}

.handle-box i {
  color: #8492a6;
  margin: 0 15px 0 0;
  /* padding: 10px 0; */
  font-size: 1em;
  vertical-align: middle;
}

.handle-box .el-icon-arrow-left {
  margin-left: 0;
}
.currentPath {
  float: none !important;
  display: inline-block;
  margin-left: 50px;
}
/* 个人资源 */
.search-type {
  min-width: 200px;
}
.search-type .active {
  color: #409eff !important;
  border-color: #c6e2ff !important;
  background-color: #ecf5ff !important;
}

.search-type .el-button:focus,
.search-type .el-button:hover {
  color: inherit;
  border-color: #dcdfe6;
  background-color: inherit;
}
/* 裁剪 */
.cropperWrapper {
  position: relative;
  /* width: 50%; */
  height: 400px;
  margin: 0 0 40px 80px;
  .cropper-outWrap {
    width: 100%;
    height: 100%;
    background: #f4f4f4;
    position: relative;
    .button-box {
      position: absolute;
      left: 0;
      right: 0;
      top: 0;
      bottom: 0;
      width: 100%;
      height: 60px;
      margin: auto;
      text-align: center;
    }
    .cropper-tip {
      color: #d4d4d4;
      white-space: nowrap;
      text-align: center;
      display: block;
      padding: 5px;
    }
  }
  .cropperInner {
    width: 100%;
    height: 100%;
  }
}
.cropperBtnGroup {
  margin: 20px 0 20px 80px;
}
.cropperView img {
  width: auto;
  max-width: 80%;
}

.icon-cropper {
  position: absolute;
  left: 0;
  bottom: 0;
  border-radius: 0;
  img {
    vertical-align: middle;
  }
}

.cropper-close {
  position: absolute;
  right: 0;
  top: 0;
  background: #666666;
  color: #ffffff;
  padding: 2px;
  cursor: pointer;
}

.cropperSize {
  .el-input {
    width: 70px;
  }
}

.imgPreview img {
  max-width: 100%;
  box-sizing: border-box;
  border: 1px dashed #d9d9d9;
  border-radius: 6px;
  cursor: pointer;
  position: relative;
  overflow: hidden;
  padding: 10px;
}

.fileLinkWrap {
  position: relative;
  .fileLink {
    position: absolute;
    left: 0;
    right: 0;
    top: 0;
    bottom: 0;
    margin: auto;
    border: none;
    width: 100%;
    background: none;
    opacity: 0;
  }
}

.resource-moveWrape {
  .el-cascader {
    width: 100%;
  }
}
</style>

