<template>
  <site-layout>
    <el-row>
      <el-col :span="18">
        <el-form :inline="true" :model="search">
          <el-form-item label="组件类型">
            <el-select v-model="search.type" placeholder="请选择">
              <el-option label="布局" value="1"></el-option>
              <el-option label="内容" value="2"></el-option>
            </el-select>
          </el-form-item>
					<el-form-item label="应用环境">
						<el-select v-model="search.place">
							<el-option label="活动页" value="1"></el-option>
							<el-option label="首页" value="2"></el-option>
						</el-select>
          </el-form-item>
          <el-form-item label="组件名称">
            <el-input v-model="search.key"></el-input>
          </el-form-item>
          <el-form-item>
            <el-button type="primary" @click="doSearch">搜索</el-button>
          </el-form-item>
        </el-form>
      </el-col>
      <el-col :span="6" class="text-right">
        <el-button type="danger" icon="el-icon-plus" @click="addCategory()">新增</el-button>
      </el-col>
    </el-row>
    <el-row>
      <el-col :span="24">
        <el-table :data="categoryList" style="width: 100%">
          <el-table-column prop="id" label="ID"></el-table-column>
          <el-table-column prop="name" label="名称"></el-table-column>
          <el-table-column label="类型">
            <template slot-scope="scope">
              <span v-if="scope.row.type == 1">布局</span>
              <span v-else>内容</span>
            </template>
          </el-table-column>
					<el-table-column label="应用环境">
						<template slot-scope="scope">
              <span v-if="scope.row.place == 1">活动页</span>
              <span v-else>首页</span>
            </template>
					</el-table-column>
          <el-table-column label="操作">
            <template slot-scope="scope">
              <el-button type="danger" size="small" @click="editCategory(scope.row)">编辑</el-button>
            </template>
          </el-table-column>
        </el-table>
      </el-col>
    </el-row>

    <el-row v-if="total > 10">
      <el-col :span="24" class="text-right">
        <el-pagination layout="prev, pager, next" page-size="10" :total="total" @current-change="handleCurrentChange"></el-pagination>
      </el-col>
    </el-row>

    <el-dialog title="新增分类" :visible.sync="dialogVisible">
      <el-form :model="form" :rules="rules" ref="form" label-width="80px">
        <el-row>
          <el-col :span="12">
            <el-form-item label="类型" prop="type">
              <el-select v-model="form.type" placeholder="请选择类型" :disabled="Boolean(form.id)">
                <el-option label="布局" value="1"></el-option>
                <el-option label="内容" value="2"></el-option>
              </el-select>
            </el-form-item>
          </el-col>
        </el-row>
				<el-form-item label="应用环境" prop="place">
					<el-radio-group v-model="form.place" :disabled="Boolean(form.id)">
						<el-radio :label="1">活动页</el-radio>
						<el-radio :label="2">首页</el-radio>
					</el-radio-group>
				</el-form-item>
        <el-form-item label="名称" prop="name">
          <el-input v-model="form.name"></el-input>
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
import { getCategoryList, addCategory, updateCategory } from '../plugin/api'

export default {
	components: { siteLayout },
	data() {
		return {
			submitLoading: false,
			total: '',
			search: {
				key: '',
				type: '1',
				place: '1'
			},
			currentPage: 1,
			categoryList: [],
			dialogVisible: false,
			form: {
				id: '',
				type: '',
				name: '',
				radio: '1',
				place: 1
			},
			rules: {
				type: [{ required: true, message: '请选择类型', trigger: 'change' }],
				name: [{ required: true, message: '请输入名称', trigger: 'blur' }],
				place: [
					{ required: false, message: '请选择应用环境', trigger: 'change' }
				]
			}
		}
	},
	created: function() {
		this.getCategories()
	},
	methods: {
		async getCategories() {
			let params = {
					type: this.search.type,
					place: this.search.place,
					key: this.search.key,
					pageNo: this.currentPage,
					pageSize: 10
				},
				res = await getCategoryList(params)

			this.categoryList = res.data.list
			this.total = res.data.totalCount
		},
		addCategory() {
			this.form.id = ''
			this.form.type = ''
			this.form.name = ''

			this.dialogVisible = true
		},
		editCategory(row) {
			this.form.id = row.id
			this.form.type = String(row.type)
			this.form.name = row.name
			this.form.place = row.place

			this.dialogVisible = true
		},
		async submitForm(formName) {
			this.submitLoading = true

			this.$refs[formName].validate(async valid => {
				if (valid) {
					let res,
						params = {
							type: this.form.type,
							place: this.form.place,
							name: this.form.name
						}
            
					if (this.form.id !== '') {
						params.id = this.form.id
						res = await updateCategory(params)
					} else {
						res = await addCategory(params)
					}
          
					if (res.code == 0) {
						this.getCategories()
						this.dialogVisible = false
						this.submitLoading = false
					} else {
						this.submitLoading = true
					}
				} else {
					this.submitLoading = false
				}
			})
		},
		resetForm(formName) {
			this.$refs[formName].resetFields()
			this.dialogVisible = false
			this.submitLoading = false
		},
		handleCurrentChange(currentPage) {
			this.currentPage = currentPage
			this.getCategories()
		},
		doSearch() {
			this.currentPage = 1
			this.getCategories()
		}
	}
}
</script>
