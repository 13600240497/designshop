<template>
  <div>
    <el-row :span="24" style="margin-top: 15px;">
			<el-col>
				<el-button @click="refreshHeadFoot">一键刷新头尾部</el-button>
			</el-col>
    </el-row>
		<el-row>
			<el-col :span="24" class="geshop-activity-lists">
				<el-table :data="activityList" @expand-change="handleExpandChange" style="width: 100%" :row-key="getRowKey" :expand-row-keys="expandRowKeys" @row-click="handleExpandChange">
					<el-table-column type="expand">
						<template slot-scope="scope">
							<el-card v-if="scope.row.children && scope.row.children.length > 0" class="box-card geshop-activity-child-pages" v-for="list in scope.row.children" :key="list.key">
								<div>
									<div class="geshop-activity-child-pages-banner">
										<img :src="list.preview_pic_url" class="child-pages-image">
										<div class="child-page-flag-green" v-if="(list.status == 2)">上线</div>
										<div class="child-page-flag-red" v-if="(list.status == 4)">下线</div>
									</div>
								</div>
								<div class="child-pages-title">{{ list.title }}</div>
								<div class="child-pages-time">{{ list.create_time | moment('YYYY-MM-DD HH:mm:ss') }}</div>
								<div class="child-pages-name">{{ list.create_name }}</div>
								<div>
									<a class="child-pages-link" @click="redirect(list.preview_url)">预览</a>
								</div>
							</el-card>
							<el-card v-else class="box-card geshop-activity-child-pages">
								<el-col>
									<img src="/resources/images/default/banner_default.png" class="child-pages-image" style="height:112px;width:100%;display:block;">
									<p class="child-pages-add">暂无页面</p>
								</el-col>
							</el-card>
						</template>
					</el-table-column>
					<el-table-column prop="id" label="ID" width="100"></el-table-column>
					<el-table-column prop="name" label="活动名称" width="260"></el-table-column>
					<el-table-column prop="update_time" label="操作时间">
						<template slot-scope="scope">
							<span>{{ scope.row.update_time | moment('YYYY-MM-DD HH:mm:ss') }}</span>
						</template>
					</el-table-column>
					<el-table-column prop="create_name" label="创建者"></el-table-column>
					<el-table-column label="状态">
						<template slot-scope="scope">
							<span v-if="scope.row.status == 1">
								<em class="geshop-icon-online-stay"></em>
								<em style="font-style:normal;">待上线</em>
							</span>
							<span v-else-if="scope.row.status == 2">
								<em class="geshop-icon-online"></em>
								<em style="font-style:normal;">已上线</em>
							</span>
							<span v-else-if="scope.row.status == 4">
								<em class="geshop-icon-offline"></em>
								<em style="font-style:normal;">已下线</em>
							</span>
						</template>
					</el-table-column>
				</el-table>
			</el-col>
		</el-row>

		<el-row v-if="total > 10">
			<el-col :span="24" class="text-right geshop-article-page">
				<el-pagination layout="prev, pager, next" :page-size="Number(10)" :current-page="currentPage" :total="total" @current-change="handleCurrentChange"></el-pagination>
			</el-col>
		</el-row>

    <el-dialog title="活动访问地址" :visible.sync="dialogLinksVisible">
			<el-row>
				<el-button v-for="item in pageLinks" type="primary" :key="item.lang" @click="redirect(item.page_url)">{{ item.lang_name }}</el-button>
				<p>{{ tips }}
					<el-button @click="redistribution()" v-if="tips" type="primary">重新发布</el-button>
				</p>
			</el-row>
		</el-dialog>
  </div>
</template>

<script>
import { getActivityList, getPageList, getAccessLink, actReleased, refreshSite } from '../plugin/api'
import { getCookie, setCookie } from '../plugin/mUtils'
import '../../resources/stylesheets/activityManagement.css'
import '../../resources/stylesheets/icon.css'
import '../../resources/fonts/svg-fonts/style.css'

export default {
	data() {
		return {
			expandRowKeys: [],
			currentPage: 1,
			total: 0,
			activityList: [],
			dialogLinksVisible: false,
			pageLinks: [],
			tips: '',
			langList: []
		}
	},
	watch: {

	},
	mounted() {

	},
	methods: {
		async refreshHeadFoot() {
			this.$confirm('一键头尾刷新中，请在【系统日志】-任务日志里对刷新详情进行查看，是否前往查看？', '提示', {
				confirmButtonText: '确定',
				cancelButtonText: '取消',
				type: 'warning'
			}).then(async () => {
				let res = await refreshSite({
					site_code: getCookie('SITECODE')
				})

				if (res.code === 0) {
					window.location.href = '/base/task-log/index'
				} else {
					this.$message({
						message: res.message
					})
				}
			}).catch(async () => {

			})
		},
		async getActivities() {
			let params = {
					pageNo: this.currentPage,
					pageSize: 10,
					site_group_code: 'rg',
					site_code: getCookie('SITECODE')
				},
				res = await getActivityList(params)

			this.activityList = res.data.list
			this.total = res.data.pagination.totalCount
		},
		async getPages(activityId) {
			let params = {
					activity_id: activityId
				},
				res = await getPageList(params)

			let position, data, pages

			pages = res.data.list

			this.activityList.forEach(function (element, index) {
				if (element.id == activityId) {
					element.children = pages
					data = element
					position = index
				}
			})

			this.$set(this.activityList, position, data)
		},
		handleExpandChange(row) {
			if (!row.children) {
				this.getPages(row.id)
			}

			this.langList = row.langList

			if (this.expandRowKeys.indexOf(row.id) === -1) {
				this.expandRowKeys = [row.id]
			} else {
				this.expandRowKeys = []
			}
		},
		handleCurrentChange(currentPage) {
			this.currentPage = currentPage
			this.getActivities()
		},
		async viewPages(id) {
			let params = {
					id: id
				},
				res = await getAccessLink(params)
      
			if (res.code == 0) {
				this.dialogLinksVisible = true
				this.pageLinks = res.data.list
				this.tips = res.data.tips
			}
		},
		getRowKey(row) {
			return row.id
		},
		redirect(url) {
			window.open(url)
		},
		async redistribution() {
			let params = {
				page_id: this.urlID
			}
      
			await actReleased(params)
			this.dialogLinksVisible = false
		}
	},
	created() {
		this.getActivities()
	},
	beforeCreate () {
		let locationSearch = window.location.search
		let searchs = []
		let length = 0
		let site_code = ''

		if (locationSearch.length > 0) {
			searchs = locationSearch.substr(1).split('=')
      
			length = searchs.length

			for (var index = 0; index < length; index = index + 1) {
				if (searchs[index] == 'siteCode' && index % 2 == 0) {
					site_code = searchs[index + 1]
				}
			}
      
			setCookie('SITECODE', site_code)
		}
	}
}
</script>

<style scoped>
html {
  overflow: hidden !important;
}
.activity-detail-content {
  width: 360px;
}

.activity-detail-created-time {
  padding-bottom: 15px;
  border-top: 1px solid #ebeef5;
  padding-top: 15px;
}

.activity-detail-updated-time {
  border-bottom: 1px solid #ebeef5;
  padding-bottom: 15px;
}

.activity-detail-link {
  color: #409eff;
}

.model-item img {
  max-width: 100%;
  width: 150px;
  height: 150px;
  display: block;
  margin: 10px auto;
}
.el-table tr {
  height: 80px;
}
</style>

<style lang="less">
.model-box {
  width: 50%;
  float: left;
  text-align: center;
  .el-radio {
    position: relative;
    max-width: 100%;
  }
  .el-radio__input {
    position: absolute;
    right: 20px;
    top: 36px;
  }
}
.model-dialog .el-tabs__content {
  height: 400px;
  overflow-y: scroll;
}
.gs-col-all {
  width: 100% !important;
}
.activityPageDialog {
  .el-form-item {
    width: 500px;
  }
  .el-date-editor {
    width: 100%;
  }
}
.geshop-activity-child-pages:hover .child-pages-hover {
  z-index: 100;
  display: block;
}
.child-pages-hover {
  display: none;
}
.child-page-name .count-tip-box {
  position: absolute;
  right: 458px;
  top: 250px;
  z-index: 100;
}
.gs-col-all .count-tip-box {
  position: absolute;
  right: 65px;
  top: 42px;
}
.child-page-statistical-code .count-tip-box {
  position: absolute;
  right: 8px;
  bottom: 0px;
}
.child-page-introduction .count-tip-box {
  position: absolute;
  right: 0px;
  bottom: 0px;
}
.geshop-new-activities-name .count-tip-box {
  position: absolute;
  top: 42px;
  right: 10px;
}
.geshop-new-activities-introduction .count-tip-box {
  position: absolute;
  top: 101px;
  right: 10px;
}
.geshop-new-activities-lang .el-form-item__error {
  position: absolute;
  top: 0;
  left: 44px;
}
.geshop-new-activities-place .el-form-item__error {
  position: absolute;
  top: 0;
  left: 70px;
}
.geshop-activity-lists .has-gutter th {
  background-color: #f4f4f4 !important;
  padding: 8px 0px !important;
}
.geshop-activity-lists .el-table__header-wrapper {
  height: 40px !important;
}
.geshop-form-inline {
  display: block;
  height: 40px;
  border-radius: 4px;
  position: absolute;
  right: 24px;
  top: 16px;
}
</style>
