<template>
  <site-layout>

    <el-row>
      <el-col :span="20">
        <span>当前语言：{{ lang_text }}</span>
        <el-select
          size="small"
          v-model="lang"
          @change="(val) => handleLangChange(val, 'lang')"
          placeholder="请选择语言"
          class="upload-box">
          <el-option
            v-for="item in lang_opt"
            :key="item.code"
            :label="item.name"
            :value="item.key"/>
        </el-select>

        <el-select
          clearable
          size="small"
          v-model="activity_id"
          @change="(val) => handleLangChange(val, 'activity')"
          placeholder="请选择所属活动"
          class="upload-box">
          <el-option
            v-for="item in active_list"
            :key="item.id"
            :label="item.name"
            :value="item.id"/>
        </el-select>
      </el-col>
    </el-row>

    <el-row>
      <el-col :span="5">
        <el-input
          v-model="key_name"
          size="small"
          placeholder="请输入多语言键名"/>
      </el-col>
      <el-col
        :span="5"
        :offset="1">
        <el-input
          v-model="content"
          size="small"
          placeholder="请输入译文内容"/>
      </el-col>
      <el-col
        :span="1"
        :offset="1">
        <el-button
          type="primary"
          size="small"
          @click="() => handleSearch()">查询</el-button>
      </el-col>
      <el-col
        :span="10"
        class="t-right">

        <el-button
          type="warning"
          size="small"
          @click="handleClear">更新</el-button>
        <el-button
          type="success"
          size="small"
          @click="handleAdd">新增</el-button>
        <el-button
          type="primary"
          size="small"
          @click="handleDownLoad">导出</el-button>

        <el-upload
          class="upload-box"
          name="name"
          :show-file-list="false"
          action="/admin/language/import"
          :on-error="handleUploadErr"
          :on-success="handleUploadSuccess">
          <el-button
            size="small"
            type="primary">导入</el-button>
        </el-upload>

      </el-col>
    </el-row>

    <el-row>
      <el-col :span="24">
        <el-table :data="lang_list">
          <el-table-column
            prop="id"
            label="ID"
            width="70"/>
          <el-table-column label="是否需要前端翻译">
            <template slot-scope="scope">
              {{ scope.row.is_js === '1' ? '是' : '否' }}
            </template>
          </el-table-column>
          <el-table-column
            prop="remark"
            label="语言项说明"/>
          <el-table-column
            prop="alias"
            label="语言键名"/>
          <el-table-column
            prop="key_name"
            label="原文"/>
          <el-table-column
            :label="lang_text"
            v-if="lang_text !== '英文'">
            <template slot-scope="scope">
              {{ scope.row.contents ? (scope.row.contents.length ? scope.row.contents[0]['content'] : '') : '' }}
            </template>
          </el-table-column>
          <el-table-column label="操作">
            <template slot-scope="scope">
              <el-button
                type="text"
                size="small"
                @click="() => handleInfo(scope.row)">查看</el-button>
              <el-button
                type="text"
                size="small"
                @click="() => handleUpdate(scope.row)">编辑</el-button>
              <el-button
                type="text"
                size="small"
                class="color-red"
                @click="() => handleDel(scope.row)">删除</el-button>
            </template>
          </el-table-column>
        </el-table>
      </el-col>
    </el-row>

    <el-row v-if="pagination.totalCount > pagination.pageSize">
      <el-col
        :span="24"
        class="t-right">
        <el-pagination
          background
          layout="prev, pager, next"
          :current-page.sync="pagination.pageNo"
          :page-size="pagination.pageSize"
          :total="pagination.totalCount"
          @current-change="handleCurrentChange"/>
      </el-col>
    </el-row>

    <el-dialog
      :title="is_add ? '新增语言包' : '编辑语言包'"
      :visible.sync="dialogFormVisible"
      @close="handleClose('dialogForm')">
      <el-form
        :model="dialogForm"
        label-width="140px"
        ref="dialogForm"
        :rules="rules">
        <el-form-item
          label="原文"
          prop="key_name">
          <el-input
            v-model="dialogForm.key_name"
            auto-complete="off"/>
        </el-form-item>
        <el-form-item
          label="语言键名"
          prop="alias">
          <el-input
            v-model="dialogForm.alias"
            auto-complete="off"/>
        </el-form-item>
        <el-form-item label="是否需要前端翻译">
          <el-radio-group v-model="dialogForm.is_js">
            <el-radio label="1">是</el-radio>
            <el-radio label="0">否</el-radio>
          </el-radio-group>
        </el-form-item>
        <el-form-item label="所属活动">
          <el-select
            clearable
            size="small"
            v-model="dialogForm.activity_id"
            placeholder="请选择所属活动">
            <el-option
              v-for="item in active_list"
              :key="item.id"
              :label="item.name"
              :value="item.id"/>
          </el-select>
        </el-form-item>
        <el-form-item
          label="译文语言简称"
          prop="lang">
          <el-select
            size="small"
            v-model="dialogForm.lang"
            placeholder="请选择译文语言">
            <el-option
              v-for="item in lang_opt"
              :key="item.code"
              :label="item.name"
              :value="item.key"/>
          </el-select>
        </el-form-item>
        <el-form-item
          label="译文内容"
          prop="content">
          <el-input
            type="textarea"
            autosize
            placeholder="请输入内容"
            v-model="dialogForm.content"/>
        </el-form-item>
        <el-form-item label="备注">
          <el-input
            type="textarea"
            autosize
            placeholder="请输入备注"
            v-model="dialogForm.remark"/>
        </el-form-item>
      </el-form>

      <div
        slot="footer"
        class="t-right">
        <el-button @click="dialogFormVisible = false">取 消</el-button>
        <el-button
          type="primary"
          @click="handleSubmit">确 定</el-button>
      </div>
    </el-dialog>

    <el-dialog
      title="语言包详情"
      :visible.sync="dialogDetailVisible">
      <p class="paragraph-fomart">
        <span class="paragraph-headline">语言键名：</span>
        {{ dialogDetail.alias }}
      </p>
      <p class="paragraph-fomart">
        <span class="paragraph-headline">原文内容：</span>
        {{ dialogDetail.key_name }}
      </p>
      <p class="paragraph-fomart">
        <span class="paragraph-headline">译文所属语言：</span>
        {{ lang_text }}
      </p>
      <p class="paragraph-fomart">
        <span class="paragraph-headline">译文内容：</span>
        {{ dialogDetail.content }}
      </p>
      <p class="paragraph-fomart">
        <span class="paragraph-headline">是否需要前端翻译：</span>
        {{ dialogDetail.is_js }}
      </p>
      <p class="paragraph-fomart">
        <span class="paragraph-headline">是否删除：</span>
        {{ dialogDetail.is_delete }}
      </p>
      <p class="paragraph-fomart">
        <span class="paragraph-headline">备注：</span>
        {{ dialogDetail.remark }}
      </p>
      <p class="paragraph-fomart">
        <span class="paragraph-headline">创建者 -- 创建时间：</span>
        {{ dialogDetail.create_user }} -- {{ dialogDetail.create_time }}
      </p>
      <p class="paragraph-fomart">
        <span class="paragraph-headline">最近修改人/修改时间：</span>
        {{ dialogDetail.update_user }} -- {{ dialogDetail.update_time }}
      </p>
    </el-dialog>

  </site-layout>
</template>

<script>
import siteLayout from './layouts/Layout.vue'
import { getLangList, getLangKeyList, addLang, deleteLang, updateLang, getLangInfo, updateLangCache, getActivityList, } from '../plugin/api'

export default {
	components: { siteLayout },
	data() {
		return {
			dialogFormVisible: false,
			dialogDetailVisible: false,
			is_add: false,
			key_name: '',
			content: '',
			lang: '',
			activity_id: '',
			lang_text: '',
			lang_opt: [],
			lang_list: [],
			active_list: [],
			pagination: {},
			dialogForm: {
				id: '',
				content_id: '',
				activity_id: '',
				lang: '',
				content: '',
				is_js: '',
				key_name: '',
				alias: '',
				remark: '',
			},
			dialogDetail: {
				is_js: '',
				alias: '',
				key_name: '',
				is_delete: '',
				remark: '',
				create_user: '',
				create_time: '',
				content: '',
			},

			rules: {
				key_name: [
					{ required: true, message: '请输入语言键名', trigger: 'blur' }
				],
				alias: [
					{ required: true, message: '请输入语言键别名', trigger: 'blur' }
				],
				lang: [
					{ required: true, message: '请选择译文语言', trigger: 'blur' }
				],
				content: [
					{ required: true, message: '请输入译文内容', trigger: 'blur' }
				],
			},

			searchCount: 0

		}
	},
	async created() {
		//拉取语言选项
		const res_key = await getLangKeyList()
		this.lang_opt = res_key.data
		this.lang_text = '英文'
		this.lang = 'en'

		//拉取活动列表
		const res_list = await getActivityList({ pageSize: 1000, type: 0 })
		this.active_list = res_list.data.list

		//拉取语言包列表
		this.fetchPage()

		//绑定popstate事件
		window.addEventListener('popstate', this.handlePopstate)

	},
	beforeDestroy() {
		//解绑popstate事件
		window.removeEventListener('popstate', this.handlePopstate)
	},
	watch: {
		searchCount: function (newVal, oldVal) {
			if (!newVal) return

			//刷新页面
			if (newVal < oldVal) {
				let param = history.state

				this.pagination.pageNo = param.pageNo
				this.key_name = param.key_name
				this.content = param.content
				this.lang = param.lang

				this.fetchPage(param)
			}
		}
	},
	methods: {
		handleUploadErr() {
			this.$message({
				message: '上传失败',
				type: 'error'
			})
		},
		handleUploadSuccess() {
			this.$message({
				message: '上传成功',
				type: 'success'
			})
		},
		handlePopstate() {
			if (!this.searchCount) {
				return
			}

			--this.searchCount
		},
		async fetchPage(param) { //刷新页面数据
			if (!param) {
				const { lang, key_name, content, pagination, activity_id } = this
				let pageNo = pagination.pageNo

				param = {
					lang,
					key_name,
					content,
					pageNo,
					activity_id,
					pageSize: 10
				}
			}

			const res_list = await getLangList(param)
			res_list.data.pagination.totalCount = parseInt(res_list.data.pagination.totalCount)

			this.lang_list = res_list.data.list
			this.pagination = res_list.data.pagination
		},
		handleSearch(pageNo) { //条件查询
			const { lang, key_name, content, activity_id } = this
			pageNo = pageNo ? pageNo : 1

			const param = {
				lang,
				key_name,
				content,
				pageNo,
				activity_id,
				pageSize: 10
			}

			//push histor
			window.history.pushState(param, null, '')
			++this.searchCount

			//刷新页面
			this.fetchPage(param)
		},
		handleClear() { //刷新缓存
			updateLangCache().then(() => {
				this.$message({
					message: '更新缓存成功!',
					type: 'success'
				})
			})
		},
		handleLangChange(val, type) { //选择当前语言
			if (type === 'lang') { //选择所属语言
				//存储当前选择的语言包
				let selectItem = this.lang_opt.find((item) => item.key === val)
				this.lang_text = selectItem.name
			} else { //选择所属活动

			}

			//刷新页面
			this.handleSearch()

		},
		handleCurrentChange(pageNo) { //分页
			this.handleSearch(pageNo)
		},
		handleAdd() { //增加语言包
			this.is_add = true
			this.dialogFormVisible = true

			this.dialogForm = {
				id: '',
				content_id: '',
				activity_id: '',
				lang: '',
				content: '',
				is_js: '0',
				key_name: '',
				alias: '',
				remark: '',
			}

		},
		handleUpdate(row) { //更新语言包
			this.is_add = false
			this.dialogFormVisible = true

			this.dialogForm.id = row.id
			this.dialogForm.content_id = row.contents ? (row.contents[0] ? row.contents[0]['id'] : '') : ''
			this.dialogForm.lang = this.lang
			this.dialogForm.content = row.contents ? (row.contents[0] ? row.contents[0]['content'] : '') : ''
			this.dialogForm.is_js = row.is_js
			this.dialogForm.key_name = row.key_name
			this.dialogForm.alias = row.alias
			this.dialogForm.remark = row.remark
			this.dialogForm.activity_id = row.activity_id
		},
		handleDel(row) { //删除语言包
			this.$confirm('此操作将删除该语言包, 是否继续?', '提示', {
				confirmButtonText: '确定',
				cancelButtonText: '取消',
				type: 'warning'
			}).then(() => {
				deleteLang({ id: row.id }).then(res => {
					//刷新页面
					if (!res.code) {
						this.fetchPage()
					}
				})
			}).catch(() => {
				this.$message({
					type: 'info',
					message: '已取消删除'
				})
			})
		},
		async handleInfo(row) { //查看语言包详情
			this.is_add = false
			this.dialogDetailVisible = true
			let content_id = row.contents ? (row.contents.length ? row.contents[0]['id'] : '') : ''

			let res = await getLangInfo({ id: row.id, content_id })

			this.dialogDetail.is_js = res.data.is_js ? '是' : '否'
			this.dialogDetail.is_delete = res.data.is_delete ? '已删除' : '未删除'
			this.dialogDetail.content = res.data.content
			this.dialogDetail.create_user = res.data.create_user
			this.dialogDetail.create_time = new Date(res.data.create_time).toLocaleString()
			this.dialogDetail.update_user = res.data.update_user
			this.dialogDetail.update_time = res.data.update_time
			this.dialogDetail.key_name = res.data.key_name
			this.dialogDetail.alias = res.data.alias
			this.dialogDetail.remark = res.data.remark
		},
		handleSubmit() { //提交修改/新增
			const _dialogForm = Object.assign({}, this.dialogForm)
			this.$refs['dialogForm'].validate((valid) => {
				if (valid) {
					if (this.is_add) { //提交新增
						delete _dialogForm.id
						delete _dialogForm.content_id

						addLang(_dialogForm).then(res => {
							if (res && !res.code) {
								this.dialogFormVisible = false
								this.fetchPage()
							}
						})
					} else { //提交修改
						updateLang(_dialogForm).then(res => {
							if (res && !res.code) {
								this.dialogFormVisible = false
								this.fetchPage()
							}
						})
					}
				} else {
					return false
				}
			})
		},
		handleDownLoad() { //导出语言包
			// window.open('http://' + location.host + '/admin/language/export')
			window.open('/admin/language/export')
		},
		handleClose(formname) {
			this.$refs[formname].resetFields()
		}
	}
}
</script>

<style lang="less" scoped>
  .t-right {
    text-align: right;
  }

  .color-red {
    color: #f56c6c;
  }

  .paragraph-fomart {
    line-height: 1;
  }

  .paragraph-headline {
    display: inline-block;
    width: 160px;
    color: #409eff;
  }

  .upload-box {
    display: inline-block;
    margin-left: 10px;
  }
</style>
