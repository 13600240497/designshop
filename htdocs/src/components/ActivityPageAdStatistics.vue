<template>
	<site-layout @publicReady="publicReady">
		<el-row :span="24" class="gs-activity-ad-tit">
			<span class="gs-activity-ad-title">活动页广告位统计</span>
		</el-row>
		<el-row :span="24" class="gs-activity-ad-btn">
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
      <el-col class="gs-activity-ad-port">
        <!-- <span class="gs-activity-ad-port-name" style="width:46px;">请选择</span> -->
				<el-select v-model="search.activityPage" filterable remote :remote-method="handleSearchActivityPages" clearable placeholder="请输入或选择" @change="handleChangeActivityPage" @visible-change="handleChangeActivityPageList" class="gs-activity-ad-port-select">
          <el-option
            v-for="item in handleActivityPageList(activityPageList)"
            :key="item.id"
            :label="item.title"
            :value="item.id">
          </el-option>
        </el-select>
			</el-col>
      <el-col class="gs-activity-ad-port">
				<el-select v-model="search.position" @change="handleChangePosition">
          <el-option label="广告位查看" value="1"></el-option>
          <el-option label="坑位查看" value="2"></el-option>
        </el-select>
			</el-col>
      <el-col class="gs-activity-ad-port" v-if="search.position == '2'">
				<el-select v-model="search.floor" placeholder="">
          <el-option 
            v-for="item in handleFloor(floorList)" 
            :key="item.id" 
            :label="item.name" 
            :value="item.id"
          ></el-option>
        </el-select>
			</el-col>
      <!-- <el-col class="gs-activity-ad-port" style="width: 124px !important;margin-right:15px;">
				<el-select v-model="search.componentId" disabled placeholder="">
          <el-option 
            v-for="item in componentList" 
            :key="item.id" 
            :label="item.name" 
            :value="item.id"
          ></el-option>
        </el-select>
			</el-col> -->

      <el-col class="gs-activity-ad-time" v-if="this.search.position == 1">
        <el-radio-group v-model="options.periodValue" @change="handleChangePeriodList">
          <el-radio-button v-for="(item, index) in periodList" :key="index" :label="item.label">{{ item.name }}</el-radio-button>
        </el-radio-group>
			</el-col>
      <el-col class="gs-activity-ad-screening" v-if="this.search.position == 1">
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

			<el-col class="gs-activity-ad-search" v-if="this.search.position == 1">
        <el-button type="primary" :loading="search.btnStatus" @click="searchData">搜索</el-button>
			</el-col>
      <el-col class="gs-activity-ad-clear" v-if="this.search.position == 1">
        <el-button type="info" @click="clearData">清空</el-button>
			</el-col>
      <el-col class="gs-activity-ad-export">
        <el-button @click="portExcel()">导出EXCEL</el-button>
			</el-col>
      
		</el-row>

		<!-- <el-row>
      <el-col class="gs-activity-ad-search" style="margin-left:10px;">
        <el-button type="primary" :loading="search.btnStatus" @click="searchData">搜索</el-button>
			</el-col>
      <el-col class="gs-activity-ad-export">
        <el-button @click="portExcel()">导出EXCEL</el-button>
			</el-col>
		</el-row> -->

    <el-row style="background-color:#ffffff;padding-left:12px;" v-if="this.search.position == 2">
      <el-col class="gs-activity-ad-time">
        <el-radio-group v-model="options.periodValue" @change="handleChangePeriodList">
          <el-radio-button v-for="(item, index) in periodList" :key="index" :label="item.label">{{ item.name }}</el-radio-button>
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
      <el-col class="gs-activity-ad-search" style="margin-left:0px;" v-if="this.search.position == 2">
        <el-button type="primary" :loading="search.btnStatus" @click="searchData">搜索</el-button>
			</el-col>
      <el-col style="width:60px;margin-left: 16px;" v-if="this.search.position == 2">
        <el-button type="info" @click="clearData">清空</el-button>
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
        <el-table-column prop="update_time" label="日期" width="180">
        </el-table-column>
        <el-table-column prop="platform" label="端口" width="100"></el-table-column>
        <el-table-column prop="location" v-if="search.position == '1'" label="位置" width="125">
        </el-table-column>
        <el-table-column prop="component_name" v-if="search.position == '1'" label="资源位类型" width="160">
        </el-table-column>
        <el-table-column prop="module_ie_pv" v-if="search.position == '1'" label="PV" width="108">
        </el-table-column>
        <el-table-column prop="empty" v-if="search.position == '1'" label="UV" width="108">
        </el-table-column>
        <el-table-column prop="module_ic_pv" v-if="search.position == '1'" label="点击数" width="108">
        </el-table-column>
        <el-table-column prop="module_pur_numb" v-if="search.position == '1'" label="组件购买用户数" width="125">
        </el-table-column>
        <el-table-column prop="module_pay_amount" v-if="search.position == '1'" label="组件购买金额" width="125">
        </el-table-column>

        <el-table-column prop="pit_id" v-if="search.position == '2'" label="坑位" width="125">
        </el-table-column>
        <el-table-column prop="pit_pur_numb" v-if="search.position == '2'" label="坑位购买用户数" width="125">
        </el-table-column>
        <el-table-column prop="pit_pay_amount" v-if="search.position == '2'" label="坑位购买金额" width="125">
        </el-table-column>
        <el-table-column prop="pit_ie_pv" v-if="search.position == '2'" label="PV" width="108">
        </el-table-column>
        <el-table-column prop="empty" v-if="search.position == '2'" label="UV" width="108">
        </el-table-column>
        <el-table-column prop="pit_ic_pv" v-if="search.position == '2'" label="点击数" width="108">
        </el-table-column>
        <el-table-column prop="pit_cl_rate" label="点击率" width="108">
        </el-table-column>
        <el-table-column prop="empty" label="订单数" width="108">
        </el-table-column>
        <el-table-column prop="empty" label="销售额" width="108">
        </el-table-column>
        <el-table-column prop="empty" label="客单价" width="108">
        </el-table-column>
        <el-table-column prop="empty" label="下单转化率" width="108">
        </el-table-column>
        <el-table-column prop="empty" label="销售占比" width="108">
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
  import { getDataReport, getActivityPageList, getActivityPageComponentList, getReportActivityAdList } from "../plugin/api";
  import { getCookie } from "../plugin/mUtils";
  import '../../resources/stylesheets/HomePageAdData.css'
  import '../../resources/stylesheets/actSaleData.css'

  export default {
    components: { siteLayout },
    data() {
      return {
        search: {
          activityPage: '',
          platformValue: 'all',
          position: '1',
          floor: '',
          componentId: 0,
          dateValue: [],
          dateRangeValue: [],
          isNewListValue: '2',
          activityPageID: ''
        },
        places: [],
        platformList: [{ label: '全部', value: 'all' },{ label: 'PC端', value: 'pc' },{ label: 'M端', value: 'm' }],
        isNewList: [{ label: '整体', name: '2' },{ label: '新客', name: '1' },{ label: '老客', name: '0' }],
        periodList: [{ name: '昨天', label: '0' },{ name: '七天', label: '1' },{ name: '一个月', label: '2' },{ name: '三个月', label: '3' }],
        tableData:[],
        activityPageList: [],
        // floorList: [{ id: 0, name: '预促销', location: 1 },{ id: 1, name: '多时段秒杀', location: 2 }],
        floorList: [],
        componentList: [{ id: 0, name: '预促销' },{ id: 1, name: '多时段秒杀' }],
        total: 0,
        options: {
          pageNo: 1,
          pageSize: 20,
          periodValue: '0',
        },
        empty: 0
      }
    },
    created() {
      this.getTimeRange()

      // 获取子页面数据
      this.getActivityPageList()
      
    },
    // 监听data变化
    watch: {
      'search.activityPageID': {
        handler (val, oldVal) {
          if (val) {
            this.search.activityPage = this.search.activityPageID
            if (this.search.activityPage) {
              // 获取报表数据（page_id必填，默认选择default字段值作为page_id参数）
              this.getReportActivityAdListFn()
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
       * 楼层数据处理
       */
      handleFloor(floorList) {
        let floors = []
        floorList.forEach((item) => {
          let floorObj = {}
          floorObj['id'] = item['id']
          floorObj['name'] = `楼层${item['location']} - ${item['name'] ? item['name'] : ''}`
          floors.push(floorObj)
        })
        // this.search.floor = floors[0]['id']
        return floors
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
          this.activityPageList = res.data.list
          this.search.activityPageID = res.data.default ? res.data.default.id : ''
        }
      },

      /**
       * 获取广告活动页列表
       * @param { String } platform - 平台 pc,m,all
       * @param { Number } view_type - 广告位或坑位 1,2
       * @param { Number } page_id - 页面id
       * @param { String } start_time - 开始时间
       * @param { String } end_time - 结束时间
       */
      async getReportActivityAdListFn() {
        let param = {
          platform: this.search.platformValue,
          view_type: this.search.position,
          page_id: this.search.activityPage || 0,
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

        // 坑位查看加传module_id
        if (this.search.position == 2) {
          param.module_id = this.search.floor
        }

        let res = await getReportActivityAdList(param)
        if (res.code == 0) {
          let tableData = res.data.list
          tableData.forEach((item) => {
            item.empty = 0
          })
          this.tableData = tableData
          this.total = parseInt(res.data.pagination.totalCount)
        }
      },

      handleCurrentChange (currentPage) {
        this.options.pageNo = currentPage
        this.getReportActivityAdListFn()
      },

      handleSizeChange (value) {
        this.options.pageSize = value
        this.options.pageNo = 1
        this.getReportActivityAdListFn()
      },
      
      /**
       * 导出表格
       * @param { Number } - excel 1 导出
       */
      exportExcel() {
        let param = {
          platform: this.search.platformValue,
          view_type: this.search.position,
          page_id: this.search.activityPage || 0,
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

        let href = `${location.origin}/base/report/special-page-detail-data-list?platform=${param.platform}&view_type=${param.view_type}&page_id=${param.page_id}&pageSize=${param.pageSize}&pageNo=${param.pageNo}&excel=1&start_time=${param.start_time}&end_time=${param.end_time}`
        
        // 坑位查看加传module_id
        if (this.search.position == 2) {
          param.module_id = this.search.floor 
          href = `${href}&module_id=${param.module_id}`
        }
        
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
       * 广告位坑位切换
       */
      handleChangePosition (value) {
        this.tableData = []
        this.total = 0
        this.search.floor = ''
      },

      /**
       * 根据活动子页面ID获取组件列表
       */
      handleChangeActivityPage (value) {
        this.getActivityPageComponentListFn()
      },
      /**
       * 获取活动组件列表
       * @param {Number} - page_id
       */
      async getActivityPageComponentListFn() {
        let param = {
          page_id: this.search.activityPage
        }
        let res = await getActivityPageComponentList(param)
        this.search.floor = ''
        this.floorList = res.data
      },

      searchData () {
        this.getReportActivityAdListFn()
      },

      /**
       * 清空
       */
      clearData () {
        this.search.platformValue = 'all'
        this.search.position = '1'
        this.search.dateValue = []
        this.options.periodValue = 0
        this.options.pageSize = 10
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
        this.getReportActivityAdListFn()
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