<template>
	<site-layout @publicReady="publicReady">
		<el-row :span="24" class="gs-activity-ad-tit">
			<span class="gs-activity-ad-title">活动页统计</span>
		</el-row>
		<el-row :span="24" class="gs-activity-ad-btn">
      <el-col class="gs-activity-ad-port">
				<el-select v-model="search.type">
          <el-option label="汇总" value="1"></el-option>
          <el-option label="明细" value="2"></el-option>
        </el-select>
			</el-col>
			<el-col class="gs-activity-ad-port">
        <!-- <span class="gs-activity-ad-port-name">端口</span> -->
				<el-select v-model="search.platformValue" placeholder="全部" class="gs-activity-ad-port-select">
          <el-option
            v-for="item in platformList"
            :key="item.value"
            :label="item.label"
            :value="item.value">
          </el-option>
        </el-select>
        <!-- <el-select v-model="convertForm.activity_id" @change="getAppPages">
          <el-option v-for="item in appActivities" :label="item.name" :value="item.id" :key="item.id"></el-option>
        </el-select> -->
			</el-col>
      <el-col class="gs-activity-ad-port activity-select-list">
        <!-- <span class="gs-activity-ad-port-name" style="width:46px;">请选择</span> -->
				<el-select v-model="search.activityPage" filterable remote :remote-method="handleSearchActivityPages" clearable placeholder="专题选择" @visible-change="handleChangeActivityPageList" class="gs-activity-ad-port-select">
          <el-option
            v-for="item in handleActivityPageList(activityPageList)"
            :key="item.id"
            :label="item.title"
            :value="item.id">
          </el-option>
        </el-select>
			</el-col>
			<el-col class="gs-activity-ad-time">
			  <!-- <el-button-group v-model="periodValue">
          <el-button v-for="item in periodList" :key="item.label" :label="item.label">{{ item.name }}</el-button>
        </el-button-group> -->
        <el-radio-group v-model="options.periodValue" @change="handleChangePeriodList">
          <el-radio-button
            v-for="(item, index) in periodList"
            :key="index"
            :label="item.label">
            {{ item.name }}
          </el-radio-button>
        </el-radio-group>
			</el-col>
      <el-col class="gs-activity-ad-screening">
        <span class="gs-activity-ad-screening-time">时间段筛选</span>
        <el-date-picker
          @change="handleChangeDatePicker" 
          v-model="search.dateValue" 
          type="daterange" 
          class="gs-activity-ad-screening-select"
          range-separator="至"
          start-placeholder="开始日期"
          end-placeholder="结束日期"
          format="yyyy-MM-dd"
          value-format="yyyy-MM-dd"
          align="right">
        </el-date-picker>
      </el-col>
			<el-col class="gs-activity-ad-search">
        <el-button type="primary" :loading="search.btnStatus" @click="searchData">搜索</el-button>
			</el-col>
      <el-col class="gs-activity-ad-clear">
        <el-button type="info" @click="clearData">清空</el-button>
			</el-col>
      <el-col class="gs-activity-ad-export">
        <el-button @click="portExcel()">导出EXCEL</el-button>
			</el-col>
		</el-row>
    <el-tabs type="card" v-model="search.isNewListValue" class="gs-activity-ad-swtich" @tab-click="handleChangeNewList">
      <el-tab-pane v-for="(item, index) in isNewList" :key="index" :label="item.label" :name="item.name"></el-tab-pane>
    </el-tabs>
    <div class="gs-activity-ad-table gs-sale-data">
      <!-- <el-row class="gs-sale-sum-data">
        <div>
          总订单量：<span>0单</span>
        </div>
        <div>
          总销售额：<span>0万元</span>
        </div>
        <div>
          总UV：<span>0次</span>
        </div>
      </el-row> -->
      <!-- <el-table :data="tableData" style="width: 100%" :span-method="objectSpanMethod"> -->
      <el-table :data="tableData" style="width: 100%">
        <el-table-column prop="update_time" label="日期" width="220">
        </el-table-column>
        <el-table-column prop="platform" label="端口" width="100"></el-table-column>
        <el-table-column prop="sub_id" label="专题页ID" width="138">
        </el-table-column>
        <el-table-column prop="title" label="专题页名称" width="180">
        </el-table-column>
        <el-table-column prop="sub_ie_pv" label="PV" width="110">
        </el-table-column>
        <el-table-column prop="sub_uv" label="UV" width="110">
        </el-table-column>
        <el-table-column prop="sub_ic_pv" label="点击数" width="110">
        </el-table-column>
        <el-table-column prop="sub_cl_rate" label="点击率" width="110">
        </el-table-column>
        <el-table-column prop="sub_pur_numb" label="专题购买用户数" width="125">
        </el-table-column>
        <el-table-column prop="sub_pay_amount" label="专题购买金额" width="125">
        </el-table-column>
        <el-table-column prop="empty" label="订单数" width="110">
        </el-table-column>
        <el-table-column prop="empty" label="销售额" width="110">
        </el-table-column>
        <el-table-column prop="empty" label="客单价" width="110">
        </el-table-column>
        <el-table-column prop="empty" label="转化率" width="110">
        </el-table-column>
        <el-table-column prop="empty" label="销售占比" width="110">
        </el-table-column>
      </el-table>
    </div>
    
    <el-row class="gs-paging-areas" v-if="total > options.pageSize">
			<el-col :span="24" class="text-right geshop-article-page">
        <el-pagination layout="total, sizes, prev, pager, next" :page-count="total" :page-sizes="[10, 20]" :page-size="options.pageSize" :total="total" :current-page.sync="options.pageNo" @size-change="handleSizeChange" @current-change="handleCurrentChange"></el-pagination>
			</el-col>
		</el-row>

	</site-layout>
</template>

<script>
  import siteLayout from "./layouts/Layout.vue";
  import { getDataReport, getActivityPageList, getReportActivityList } from "../plugin/api";
  import { getCookie } from "../plugin/mUtils";
  import '../../resources/stylesheets/HomePageAdData.css'
  import '../../resources/stylesheets/actSaleData.css'

  export default {
    components: { siteLayout },
    data() {
      return {
        search: {
          type: '1',
          activityPage: '',
          platformValue: 'all',
          isNewListValue: '2',
          dateValue: [],
          dateRangeValue: [],
          btnStatus: false,
          activityPageID: ''
        },
        places: [],
        platformList: [{ label: '全部', value: 'all' },{ label: 'PC端', value: 'pc' },{ label: 'M端', value: 'm' }],
        
        isNewList: [{ label: '整体', name: '2' },{ label: '新客', name: '1' },{ label: '老客', name: '0' }],
        
        periodList: [{ name: '昨天', label: '0' },{ name: '七天', label: '1' },{ name: '一个月', label: '2' },{ name: '三个月', label: '3' }],
        
        tableData:[],
        
        activityPageList: [],
        total: 0,
        options: {
          pageNo: 1,
          pageSize: 20,
          periodValue: '0'
        },
        empty: 0
      }
    },
    created() {
      this.getTimeRange()

      // 获取活动列表
      this.getActivityPageList()

      
      
    },
    // 监听data变化
    watch: {
      'search.activityPageID': {
        handler (val, oldVal) {
          if (val) {
            this.search.activityPage = this.search.activityPageID
            if (this.search.activityPage) {
              // 获取报表数据
              this.getDataReportFn()
            }
          }
        },
        deep: true
      }
    },
    methods: {
      publicReady() {
        // 设置当前站点信息
        this.places = JSON.parse(localStorage.currentSites).sites
      },

      /**
       * 获取活动页列表
       * @param { String } platform - 端口 pc,m,all
       * @param { String } keyword - 搜索关键字
       */
      async getActivityPageList(keyword) {
        let param = {
          platform: this.search.platformValue
        }
        if (keyword) {
          param.keyword = keyword
        }
        let res = await getActivityPageList(param)
        if (res.code == 0) {
          this.search.activityPageID = res.data.default ? res.data.default.id : ''
          this.activityPageList = res.data.list
          
        }
      },

      async getDataReportFn() {
        let param = {
          platform: this.search.platformValue,
          show_type: this.search.type,
          buyer_identity: this.search.isNewListValue,
          page_id: this.search.activityPage,
          pageSize: this.options.pageSize,
          pageNo: this.options.pageNo,
        }
        if (this.search.dateRangeValue.length) {
          param.start_time = this.search.dateRangeValue[0]
          param.end_time = this.search.dateRangeValue[1]
        } else if (this.search.dateValue.length) {
          param.start_time = this.search.dateValue[0]
          param.end_time = this.search.dateValue[1]
        }

        let res = await getReportActivityList(param)
        let tableData = res.data.list
        tableData.forEach((item) => {
          item.empty = 0
        })
        this.tableData = tableData
        this.total = parseInt(res.data.pagination.totalCount)
      },
      handleCurrentChange (currentPage) {
        this.options.pageNo = currentPage
        this.getDataReportFn()
      },

      handleSizeChange (value) {
        this.options.pageSize = value
        this.options.pageNo = 1
        this.getDataReportFn()
      },
      
      /**
       * 导出表格
       * @param { Number } - excel 1 导出
       */
      exportExcel() {
        let param = {
          platform: this.search.platformValue,
          show_type: this.search.type,
          buyer_identity: this.search.isNewListValue,
          page_id: this.search.activityPage,
          pageSize: this.options.pageSize,
          pageNo: this.options.pageNo,
          excel: 1
        }
        if (this.search.dateRangeValue.length) {
          param.start_time = this.search.dateRangeValue[0]
          param.end_time = this.search.dateRangeValue[1]
        } else if (this.search.dateValue.length) {
          param.start_time = this.search.dateValue[0]
          param.end_time = this.search.dateValue[1]
        }

        let href = `${location.origin}/base/report/special-page-total-data-list?platform=${param.platform}&show_type=${param.show_type}&buyer_identity=${param.buyer_identity}&page_id=${param.page_id}&pageSize=${param.pageSize}&pageNo=${param.pageNo}&excel=1&start_time=${param.start_time}&end_time=${param.end_time}`

        window.location.href = href
      },
      
      /**
       * 导出表格
       */
      portExcel() {
			  this.$confirm('确认导出Excel？', '提示', {
          distinguishCancelAndClose: true,
          confirmButtonText: '确定',
          cancelButtonText: '取消'
        }).then(() => {
          this.exportExcel()
        }).catch(() => {
          this.$message({
            type: 'info',
            message: '已取消操作'
          });          
        });
      },

      /**
       * 获取活动页列表
       */
      handleChangeActivityPageList (msg) {
        if (msg) {
          // 清空搜索框
          this.search.activityPage = ''
          this.getActivityPageList()
        }
      },

      /**
       * 搜索活动列表
       */
      handleSearchActivityPages (value) {
        this.getActivityPageList(value)
      },

      /**
       * 列表数据处理
       */
      handleActivityPageList (activityPageList) {
        let pages = []
        activityPageList.forEach((item) => {
          let pageObj = {}
          pageObj['id'] = item['id']
          pageObj['title'] = `${item['id']} - ${item['title'] ? item['title'] : ''}`
          pages.push(pageObj)
        })
        return pages
      },

      /**
       * 搜索
       */
      searchData () {
        this.options.pageNo = 1
        this.getDataReportFn()
      },

      /**
       * 清空
       */
      clearData () {
        this.search.type = '1'
        this.options.periodValue = 0
        this.search.dateValue = []
        this.search.platformValue = 'all'
        this.search.isNewListValue = '2'
        this.search.activityPage = ''
        this.tableData = []
        this.total = 0
        // this.searchData()
      },

      handleChangePeriodList () {
        this.search.dateValue = []
        this.getTimeRange()
      },
      handleChangeNewList () {
        this.options.pageNo = 1
        this.getDataReportFn()
      },
      handleChangeDatePicker () {
        this.options.periodValue = ''
        this.search.dateRangeValue = []
      },
      getTimeRange () {
        const rangeType = this.options.periodValue
        const today = new Date()
        let end_Y = today.getFullYear() + '-'
        let end_M = (today.getMonth()+1 < 10 ? '0' + (today.getMonth()+1) : today.getMonth()+1) + '-'
        let end_D = (today.getDate() < 10 ? '0' + (today.getDate()) : today.getDate())
        let end_time = end_Y + end_M + end_D

        let date
        if (rangeType == '0') {
          date = new Date(today.getTime() - 1 * 24 * 60 * 60 * 1000)
        } else if (rangeType == '1') {
          date = new Date(today.getTime() - 7 * 24 * 60 * 60 * 1000)
        } else if (rangeType == '2') {
          date = new Date(today.getTime() - 30 * 24 * 60 * 60 * 1000)
        } else if (rangeType == '3') {
          date = new Date(today.getTime() - 90 * 24 * 60 * 60 * 1000)
        }
        let start_Y = date.getFullYear() + '-'
        let start_M = (date.getMonth()+1 < 10 ? '0' + (date.getMonth()+1) : date.getMonth()+1) + '-'
        let start_D = (date.getDate() < 10 ? '0' + (date.getDate()) : date.getDate())
        let start_time = start_Y + start_M + start_D

        this.search.dateRangeValue[0] = start_time
        this.search.dateRangeValue[1] = end_time
      },

      // objectSpanMethod({ row, column, rowIndex, columnIndex }) {
      //   if (columnIndex === 0 ||columnIndex === 1 || columnIndex === 2 || columnIndex === 3|| columnIndex === 4
      //   || columnIndex === 5 || columnIndex === 6 || columnIndex === 7 || columnIndex === 8) {
      //     if (rowIndex % 8 === 0) {
      //       return {
      //         rowspan: 8,
      //         colspan: 1
      //       };
      //     } else {
      //       return {
      //         rowspan: 0,
      //         colspan: 0
      //       };
      //     }
      //   }
      // }
    }
  }
</script>