<template>
	<site-layout @publicReady="publicReady">
		<el-row :span="24" class="geshop-Activity-tit">
			<span class="geshop-Activity-title">头尾部刷新</span>
		</el-row>
		<el-row>
			<el-col :span="24">
        <template>
          <el-tabs v-model="refreshLogTabName" type="card" @tab-click="handleRefreshLogTabClick">
            <el-tab-pane v-for="(item,key) in places" :key="key" :label="item.platform_name" :name="key"></el-tab-pane>
          </el-tabs>
        </template>
				<template>
					<el-table :data="refreshList" style="width: 100%" v-loading="loading">
						<el-table-column prop="id" label="序号" width="120"></el-table-column>
						<el-table-column prop="title" label="操作任务"></el-table-column>
						<el-table-column prop="place" label="应用环境">
							<template slot-scope="scope">
								<span v-if="scope.row.place == 1">活动页</span>
								<span v-if="scope.row.place == 2">首页</span>
							</template>
						</el-table-column>
						<!-- <el-table-column prop="status" label="操作状态">
							<template slot-scope="scope">
								<span v-if="scope.row.status == 0">未开始</span>
								<span v-if="scope.row.status == 1">进行中</span>
								<span v-if="scope.row.status == 2">已完成</span>
							</template>
						</el-table-column> -->
						<el-table-column label="更新结果">
							<template slot-scope="scope">
								<!-- <span>{{ scope.row.result | opResult }}</span> -->
								<p v-if="scope.row.result" v-for="(item, index) in (scope.row.result.split('，'))" :key="index" class="resultText">
									<span>{{item.split("：")[0]}}：</span>
									<span :class="index==2?'result-error':'result-normal'">{{item.split("：")[1]}}</span>
								</p>
								<p v-else>
									<span>更新中</span>
								</p>
							</template>
						</el-table-column>
						<el-table-column label="操作时间">
							<template slot-scope="scope">
								<span>{{ scope.row.create_time | moment('YYYY-MM-DD HH:mm:ss') }}</span>
							</template>
						</el-table-column>
						<el-table-column label="完成时间">
							<template slot-scope="scope">
								<span>{{ scope.row.complete_time | moment('YYYY-MM-DD HH:mm:ss') }}</span>
							</template>
						</el-table-column>
						<el-table-column prop="create_name" label="操作人"></el-table-column>
						<el-table-column label="操作">
							<template slot-scope="scope">
								<div class="gs-option-hover">
									<i class="el-icon-search gs-option-icon" @click="getrefreshDetail(scope.row.id,scope.row.place)" size="medium"></i>
									<el-button type="info" size="mini" class="gs-option-hoverTip" @click="getrefreshDetail(scope.row.id,scope.row.place)" style="display:none;padding: 5px;">查看</el-button>
								</div>

							</template>
						</el-table-column>
					</el-table>
				</template>
			</el-col>
		</el-row>

		<!-- 分页 -->
		<el-row v-if="total > 10">
			<el-col :span="24" class="text-right">
				<el-pagination layout="prev, pager, next" :page-size="Number(10)" :total="total" @current-change="handleCurrentChange"></el-pagination>
			</el-col>
		</el-row>

		<el-dialog title="更新详情列表" :visible.sync="viewDetail" width="1200px" class="Popup window" custom-class="refresh-detail-dialog">
			<el-row :gutter="20">
				<el-col :span="12">
					<span>更新结果</span>
					<el-select v-model="searchInfo.status" @change="handleDetailStatusChange">
						<el-option label="所有结果" :value="0"></el-option>
						<el-option label="更新成功" :value="1"></el-option>
						<el-option label="更新失败" :value="2"></el-option>
					</el-select>
					<el-button type="primary" icon="el-icon-refresh" size="medium" @click="batchRelease()">批量更新</el-button>
				</el-col>
				<!-- <el-col :span="4" :offset="8" class="text-rt">

				</el-col> -->
			</el-row>
			<el-table :data="refreshDetail" label-width="80px" @selection-change="handleSelectionChange" :default-sort="{prop:'status',order:'descending'}"
			  v-loading="pageLoading" :class="refreshDetail.length>10?'gs-wrapper-scroll':''" :row-class-name="tableRowClassName">
				<!-- <el-table-column type="selection" width="55"></el-table-column> -->
				<el-table-column label="序号" type="index" width="150"></el-table-column>
				<el-table-column prop="activity_id" label="活动ID"></el-table-column>
				<el-table-column prop="activity_name" label="活动名称"></el-table-column>
				<el-table-column prop="page_name" label="子页面名称"></el-table-column>
				<el-table-column prop="status" label="更新结果" class="result">
					<template slot-scope="scope">
						<span v-if="scope.row.status == 1" style="color:#000000;">更新成功</span>
						<span v-else-if="scope.row.status == 2" style="color:#F56C6C;">更新失败</span>
					</template>
				</el-table-column>
				<el-table-column label="操作">
					<template slot-scope="scope">
						<!-- <span v-if="scope.row.status == 1">{{ scope.row.success_time | moment('YYYY-MM-DD HH:mm:ss') }}</span> -->
						<el-dropdown trigger="click">
							<i class="el-icon-search gs-option-icon" @click="viewRefreshPage(scope.row.page_id)" size="medium"></i>
							<el-dropdown-menu slot="dropdown" class="refresh-view-dropdown">
								<span class="view-tip-text">查看页面</span>
								<el-dropdown-item v-for="(item,index) in viewPageData.list" :key="index">
									<a :href="item.page_url" class="view-page-link" target="_blank">{{item.lang_name}}</a>
								</el-dropdown-item>
							</el-dropdown-menu>
						</el-dropdown>
						<i class="el-icon-refresh gs-option-icon" v-if="scope.row.status == 2" @click="redistribution(scope.row)"></i>
					</template>
				</el-table-column>
			</el-table>
			<!-- <div class="el-row batch">
				<el-button type="primary" size="small" @click="batchRelease()">批量发布</el-button>
			</div> -->
			<el-row v-if="detailTotal > searchInfo.pageSize" style="margin-top:20px;">
				<el-col :span="24" class="text-right">
					<el-pagination layout="prev, pager, next" :page-size="searchInfo.pageSize" :total="detailTotal" :current-page.sync="searchInfo.pageNo"
					  @current-change="handleDetailCurrentChange"></el-pagination>
				</el-col>
			</el-row>
		</el-dialog>
	</site-layout>
</template>

<script>
import siteLayout from "./layouts/Layout.vue";
import {
  refreshLogList,
  refreshSite,
  getAccessLink,
  getHomeLink
} from "../plugin/api";
import { getCookie, setCookie } from "../plugin/mUtils";

export default {
  components: { siteLayout },
  data() {
    return {
      page: 1,
      refreshList: [],
      currentPage: 1,
      total: "",
      viewDetail: false,
      refreshDetail: [],
      checked: true,
      currentLogId: "",
      currentPlace: 1, //1:活动页,2:首页
      pageIds: "pageIds",


        // pc, m, app
        // D网的默认是 web
        refreshLogTabName: getCookie('site_group_code') === 'dl' ? 'web' : 'pc',
        places: [],

      searchInfo: {
        status: 0,
        pageSize: 10,
        pageNo: 1
      },
      detailTotal: 0,
      brotherInfo: {
        visible: false,
        idArr: [],
        ids: "",
        //级联数据
        options: [
          {
            label: "根目录",
            value: 0,
            id: 0,
            children: []
          }
        ],
        props: {
          id: "id",
          value: "value",
          label: "label",
          children: "children"
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
      //查看页面
      viewPageData: { list: [], tips: "" },
      //文件选中
      fileSelectOp: {
        ids: "",
        batchStatu: false
      },
      dialogVisible: false,
      loading: false,
      pageLoading: false
    };
  },
  created() {},
  mounted () {
		// 设置当前站点信息
		this.places = JSON.parse(localStorage.currentSites).sites
        setCookie('SITECODE', getCookie('site_group_code') + '-' + this.refreshLogTabName);  // 设置site_code
	},
  methods: {
    // 获取日志列表
    async getrefreshList() {
      this.loading = true;
      let params = {
          // site_code: getCookie("SITECODE"),
          site_code: getCookie('site_group_code') + '-' + this.refreshLogTabName,
          pageNo: this.currentPage,
          pageSize: 10
        },
        res = await refreshLogList(params);
      this.loading = false;
      this.refreshList = res.data.list;
      this.total = res.data.pagination.totalCount;
    },
    // PC，M，APP切换
		handleRefreshLogTabClick (event) {
			this.refreshLogTabName = event.name
			this.currentPage = 1;

            setCookie('SITECODE', getCookie('site_group_code') + '-' + this.refreshLogTabName)

			this.getrefreshList()
    },
    handleCurrentChange(currentPage) {
      this.currentPage = currentPage;
      this.getrefreshList();
    },
    handleDetailCurrentChange(currentPage) {
      this.getrefreshDetail(this.currentLogId);
    },
    doSearch() {
      this.currentPage = 1;
      this.getrefreshList();
    },
    // 获取日志详情
    async getrefreshDetail(id, place) {
        let menuIndex = sessionStorage.getItem("menuIndex"),
            routeOpeneds = sessionStorage.getItem("routeOpeneds");
        sessionStorage.setItem("menuIndexOld", menuIndex);
        sessionStorage.setItem("routeOpenedsOld", routeOpeneds);

        let site_code = getCookie('site_group_code');

        // zaful站点
        let site_code_id = '/zf';

        window.location.href = `/base${site_code_id}/task-log/detail?id=${id}&place=${place}`;


      // this.pageLoading = true
      // let params = extendDeep({ id: id }, this.searchInfo)
      // // let res = { "code": 0, "message": "success", "data": { "list": [{ "page_id": 1125, "message": "发布成功", "status": 1, "success_time": 1533195777, "page_name": "title1", "activity_id": "300", "activity_name": "xingtao", "place": 1 }, { "page_id": 821, "message": "发布成功", "status": 1, "success_time": 1533195777, "page_name": "我的首页", "activity_id": "304", "activity_name": "测试后建的活动123", "place": 1 }, { "page_id": 1019, "message": "发布成功", "status": 1, "success_time": 1533195777, "page_name": "abc-def", "activity_id": "346", "activity_name": "abc-123", "place": 1 }, { "page_id": 1108, "message": "发布成功", "status": 1, "success_time": 1533195777, "page_name": "123-456-789", "activity_id": "346", "activity_name": "abc-123", "place": 1 }, { "page_id": 1089, "message": "发布成功", "status": 1, "success_time": 1533195777, "page_name": "测试1111", "activity_id": "353", "activity_name": "商品组件", "place": 1 }, { "page_id": 1101, "message": "发布成功", "status": 1, "success_time": 1533195777, "page_name": "1", "activity_id": "353", "activity_name": "商品组件", "place": 1 }, { "page_id": 1103, "message": "发布成功", "status": 2, "success_time": 1533195777, "page_name": "123", "activity_id": "358", "activity_name": "测试发布结果接口", "place": 1 }, { "page_id": 1107, "message": "发布成功", "status": 1, "success_time": 1533195777, "page_name": "fsafasfasdf", "activity_id": "358", "activity_name": "测试发布结果接口", "place": 1 }], "pagination": { "pageNo": 1, "pageSize": 10, "totalCount": 8 } }, "localData": null }

      // let res = await refreshLogDetail(params)
      // this.pageLoading = false
      // this.refreshDetail = res.data.list
      // this.currentLogId = id
      // this.currentPlace = place
      // this.detailTotal = res.data.pagination.totalCount
      // this.viewDetail = true
    },
    publicReady() {
      this.getrefreshList();
    },

    /* 列表选中 */
    handleSelectionChange(val) {
      let arr = [];

      val.forEach(function(element) {
        arr.push(element.page_id);
      });

      this.brotherInfo.idArr = arr;
      this.brotherInfo.ids = arr.join(",");
    },

    // 重新发布
    async redistribution(row) {
      let params = {
        site_code: getCookie("SITECODE"),
        logId: this.currentLogId,
        pageIds: row.page_id
      };

      await refreshSite(params);
    },

    //批量发布
    async batchRelease() {
      if (this.brotherInfo.idArr.length < 1) {
        this.$message({ type: "warning", message: "请选中文件" });

        return false;
      } else if (this.brotherInfo.idArr.length > 1) {
        let params = {
          site_code: getCookie("SITECODE"),
          pageIds: this.brotherInfo.ids,
          logId: this.currentLogId
        };
        await refreshSite(params);
      }
    },
    //详情状态change search
    handleDetailStatusChange(value) {
      this.searchInfo.pageNo = 1;
      this.getrefreshDetail(this.currentLogId);
    },
    async viewRefreshPage(id) {
      let currentPlace = this.currentPlace;
      let viewLinkFn = currentPlace == 1 ? getAccessLink : getHomeLink;
      let res = {
        data: {
          list: [
            {
              lang_name: "英文",
              page_url:
                "https://www.rosewholesale.com/promotion/home/index.html"
            },
            { lang_name: "中文" },
            { lang_name: "aaa" },
            { lang_name: "bb" }
          ],
          tips: ""
        }
      };

      if (id == "821") {
        res = {
          data: {
            list: [{ lang_name: "aaa" }, { lang_name: "bb" }],
            tips: ""
          }
        };
      }

      // let res = await viewLinkFn({ id: id })
      let data = res.data;
      if (data.list.length == 0) {
        this.$message.warning(data.tips);
      }
      this.viewPageData = data;
      this.$forceUpdate();
    },
    viewRefreshPageClear() {
      this.viewPageData = {
        list: [],
        tips: ""
      };
      this.$forceUpdate();
    },
    tableRowClassName({ row, rowIndex }) {
      if (row.status == 2) {
        return "error-row";
      } else {
        return "success-row";
      }
    }
  }
};
</script>
<style scoped>
.el-dialog {
  width: 80%;
}
.resultText {
  margin: 0;
}

.result-normal {
  color: #1e9fff;
}
.result-error {
  color: #f56c6c;
}

.batch {
  margin-top: 20px;
  text-align: right;
}
.view-page-link {
  text-decoration: none;
  color: inherit;
}
</style>


<style lang="less">
.refresh-detail-dialog .gs-wrapper-scroll .el-table__body-wrapper {
  max-height: 600px;
  overflow-y: scroll;
}

.refresh-detail-dialog {
  .error-row {
    background-color: #fef0f0 !important;
  }
  .success-row {
    background-color: #fff !important;
  }
}
.refresh-view-dropdown {
  width: 120px;
  .view-tip-text {
    padding: 5px 20px;
    font-size: 14px;
    display: block;
    color: #9e9e9e;
  }
}
.gs-option-icon {
  font-size: 20px;
  color: #000;
  cursor: pointer;
}
.gs-option-hover {
  .gs-option-icon {
    margin: 10px 0 5px 5px;
    display: block;
  }
  &:hover {
    .gs-option-icon {
      color: #23a1ff;
    }
    .gs-option-hoverTip {
      // visibility: visible !important;
      display: block !important;
    }
  }
}
</style>



