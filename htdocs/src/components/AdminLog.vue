<template>
	<site-layout>
		<el-row>
			<el-col :span="6">
				<el-input v-model="searchInfo.keyword" size="small" class="search-inputSelect">
					<el-select filterable clearable v-model="searchInfo.field" slot="prepend" placeholder="请选择关键字类型">
						<el-option v-for="(item,index) in fieldOpt" :key="index" :label="item" :value="index"></el-option>
					</el-select>
				</el-input>
			</el-col>
			<el-col :span="3" :offset="1">
				<el-select filterable v-model="searchInfo.record_table" size="small" placeholder="请选择操作表">
					<el-option v-for="(item,index) in tableOpt" :key="index" :label="item" :value="index"></el-option>
				</el-select>
			</el-col>
			<el-col :span="6" :offset="1">
				<el-date-picker v-model="searchInfo.editDate" type="datetimerange" :editable="false" value-format="timestamp" size="small" class="search-datepicker" start-placeholder="开始日期" end-placeholder="结束日期"></el-date-picker>
			</el-col>
			<el-col :span="5" :offset="2" class="text-right">
				<el-button type="primary" size="small" @click="handleSearch()">搜索</el-button>
				<el-button type="danger" size="small" @click="resetSearch">清空</el-button>
			</el-col>
		</el-row>

		<el-row>
			<div class="el-container">
				<el-table :data="dataLists" v-loading="loading">
					<el-table-column prop="id" label="日志ID"></el-table-column>
					<el-table-column prop="admin_name" label="管理员"></el-table-column>
					<el-table-column prop="record_name" label="详情"></el-table-column>
					<el-table-column prop="title" label="日志标题">
						<template slot-scope="scope">
							<div v-html="scope.row.title"></div>
						</template>
					</el-table-column>
					<el-table-column prop="request_route" label="请求路由"></el-table-column>
					<el-table-column prop="ip" label="操作ip"></el-table-column>
					<el-table-column prop="id" label="操作时间">
						<template slot-scope="scope">
							<span>{{ Number(scope.row.create_time) | moment('YYYY-MM-DD HH:mm:ss') }}</span>
						</template>
					</el-table-column>
					<el-table-column prop="id" label="操作">
						<template slot-scope="scope">
							<el-button type="primary" size="small" @click="getInfo(scope.row.id)">详情</el-button>
						</template>
					</el-table-column>
				</el-table>
			</div>
		</el-row>

		<el-row v-if="pagination.totalCount > pagination.pageSize">
			<el-col :span="24" class="text-right">
				<el-pagination layout="prev, pager, next" :page-size="pagination.pageSize" :current-page.sync="pagination.pageNo" :total="pagination.totalCount" @current-change="handleCurrentChange"></el-pagination>
			</el-col>
		</el-row>
		<!-- 预览详情 -->
		<el-dialog title="预览" :visible.sync="prevDialog" size="large">
			<div v-html="prevInfo" class="log-info"></div>
		</el-dialog>
	</site-layout>
</template>

<script>
import siteLayout from './layouts/Layout.vue'
import { adminLogList, adminLogDetail } from '../plugin/api'

export default {
	components: { siteLayout },
	data () {
		return {
			dataLists: [],
			searchInfo: {
				pageNo: 1,
				pageSize: 10,
				field: '',
				keyword: '',
				record_table: '',
				editDate: [],
				create_time_from: '',
				create_time_to: ''
			},
			fieldOpt: {},
			tableOpt: {},
			pagination: {
				pageNo: 1,
				pageSize: 20,
				totalCount: 0,
			},
			loading: false,
			prevDialog: false,
			prevInfo: ''
		}
	},
	created () {
		this.handleLists()
	},
	methods: {
		handleSearch () {
			this.searchInfo.pageNo = 1
			this.pagination.pageNo = 1
			this.handleLists()
		},
		handleLists (type) {
			if ((this.searchInfo.keyword && !this.searchInfo.field) || (!this.searchInfo.keyword && this.searchInfo.field)) {
				this.$message.warning('关键字必须匹配搜索类型')
				return
			}
			if (type == 'searchForm') {
				this.pagination.pageNo = 1
			}
			let timeArr = this.searchInfo.editDate
			if (timeArr && timeArr.length > 0) {
				this.searchInfo.create_time_from = timeArr[0] / 1000
				this.searchInfo.create_time_to = timeArr[1] / 1000
			} else {
				this.searchInfo.create_time_from = ''
				this.searchInfo.create_time_to = ''
			}
			let params = {}
			for (let i in this.searchInfo) {
				if (i !== 'editDate' && i !== 'pageNo') {
					params[i] = this.searchInfo[i]
				}
			}
			params.pageNo = this.pagination.pageNo
			this.loading = true
			adminLogList(params).then(res => {
				this.loading = false
				if (res.code == '0') {
					let data = res.data
					this.pagination = data.pagination
					this.dataLists = data.list
					this.tableOpt = data.tables
					this.fieldOpt = data.fields
				} else {
					this.$message(res.message)
				}
			}).catch(() => {
				this.loading = false
			})
		},
		async getInfo (id) {
			let res = await adminLogDetail({ id: id })
			this.loading = false
			if (res.code == 0) {
				this.prevInfo = res.data.detail
				this.prevDialog = true
			} else {
				this.$message(res.message)
			}
		},
		handleCurrentChange (currentPage) {
			this.pagination.pageNo = currentPage
			this.handleLists()
		},
		resetForm (formName) {
			if (formName && formName == 'searchForm') {
				this.$refs['searchForm'].resetFields()
				return false
			}
			this.resetFields(formName)
		},
		resetFields (formName) {
			this.$refs[formName].resetFields()
		},
		resetSearch () {
			this.searchInfo.field = ''
			this.searchInfo.keyword = ''
			this.searchInfo.record_table = ''
			this.searchInfo.editDate = []
			this.searchInfo.create_time_from = ''
			this.searchInfo.create_time_to = ''
			this.searchInfo.pageNo = 1
		}
	}
}
</script>

<style lang="less">
.quick-search {
  width: 600px;
  .el-input-group__prepend {
    width: 30%;
  }
}
</style>

