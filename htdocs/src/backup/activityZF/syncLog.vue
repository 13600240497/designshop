<!-- 选择预览页面 -->
<template>
	<el-dialog title="三端数据绑定操作日志" class="geshop-activity-lists geshop-new-activities" custom-class="geshop-sync-dialog-container" :visible.sync="data.visible" @close="handleSyncLogClose" top="10vh">
		<!--<el-row>-->
		<!--<el-button v-for="item in data.list" type="primary" :key="item.code" @click="redirect(item.preview_url)">{{ item.name }}</el-button>-->
		<!--</el-row>-->
		<el-table :data="data.lists" class="geshop-sync-log-table" v-loading="data.loading">
			<el-table-column label="ID" prop="id" width="80" align="center" header-align="center"></el-table-column>
			<el-table-column label="操作人" prop="user.realname" width="100" align="center" header-align="center"></el-table-column>
			<el-table-column label="操作类型" prop="type" width="80" align="center" header-align="center"></el-table-column>
			<el-table-column label="操作内容" prop="content" min-width="300" align="center" header-align="center"></el-table-column>
			<el-table-column label="操作时间" prop="create_time" width="200" align="center" header-align="center">
				<template slot-scope="scope">
					<span>{{ parseInt(scope.row.create_time) | moment('YYYY-MM-DD HH:mm:ss') }}</span>
				</template>
			</el-table-column>
		</el-table>
		<el-row>
			<el-col :span="24">
				<el-pagination layout="prev, pager, next" :page-size="data.pagination.pageSize" :current-page="Number(data.pagination.pageNo)" :total="data.pagination.totalCount"
											 @current-change="handleCurrentChange"></el-pagination>
			</el-col>
		</el-row>
	</el-dialog>
</template>

<script>
	export default {
		props: {
			data: {
				type: Object,
				required: true
			}
		},
		data () {
			return {
			};
		},
		created () {

		},
		methods: {
			handleCurrentChange (value) {
				this.$emit('handleSyncLogChange',value)
			},
			handleSyncLogClose(){
				this.$emit('handleSyncLogClose')
			}
		}
	};
</script>
