<template>
	<site-layout>
		<el-row :span="24" class="gs-activity-ad-tit">
			<span class="gs-activity-ad-title">首页统计</span>
		</el-row>
		<el-row :span="24" class="gs-activity-ad-btn">
			
      <el-col class="gs-activity-ad-port" style="width: 124px !important;">
				<el-select v-model="search.type">
          <el-option label="汇总" value="1"></el-option>
          <el-option label="明细" value="2"></el-option>
        </el-select>
			</el-col>
      <el-col class="gs-activity-ad-time">
			  <el-radio-group v-model="options.periodValue" @change="handleChangePeriodList">
          <el-radio-button v-for="(item, index) in periodList" :key="index" :label="item.label">{{ item.name }}</el-radio-button>
        </el-radio-group>
			</el-col>
      <el-col class="gs-activity-ad-screening">
        <span class="gs-activity-ad-screening-time"  style="font-size: 14px;">时间段筛选</span>
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
			</el-col>
      <!-- 渠道筛选，only for Zaful -->
      <el-col class="gs-activity-ad-port" v-if="site_code == 'zf'">
        <el-select v-model="search.pipeline">
            <el-option
              v-for="item in pipelines"
              :key="item.code"
              :value="item.code"
              :label="item.name">
            </el-option>
        </el-select>
      </el-col>

			<el-col class="gs-activity-ad-search" style="margin-left: 16px;">
        <el-button :loading="search.btnStatus" @click="searchData">搜索</el-button>
			</el-col>
      <el-col style="width:60px;margin-left: 16px;">
        <el-button type="info" @click="clearData">清空</el-button>
			</el-col>
      <el-col class="gs-activity-ad-export">
        <el-button @click="portExcel()">导出EXCEL</el-button>
			</el-col>
		</el-row>
		<el-row style="background-color:#ffffff;padding-left:12px;">
      <!-- 不知道什么DIV层 -->
		</el-row>
    <el-tabs type="card" v-model="search.isNewListValue" class="gs-activity-ad-swtich" @tab-click="handleChangeNewList">
      <el-tab-pane v-for="(item, index) in isNewList" :key="index" :label="item.label" :name="item.name"></el-tab-pane>
    </el-tabs>
    <div class="gs-activity-ad-table">
      <!-- <el-table :data="tableData" :span-method="objectSpanMethod" style="width: 100%"> -->
      <el-table :data="tableData" style="width: 100%">
        <el-table-column prop="update_time" label="日期" width="220">
        </el-table-column>
        <el-table-column prop="platform" label="端口" width="100"></el-table-column>
        <el-table-column prop="sub_id" label="首页ID" width="138">
        </el-table-column>
        <el-table-column prop="title" label="首页名称" width="152">
        </el-table-column>
        <el-table-column prop="pipeline" label="渠道名称" v-if="site_code == 'zf'" width="152">
        </el-table-column>
        <el-table-column prop="sub_ie_pv" label="PV" width="108">
        </el-table-column>
        <el-table-column prop="sub_uv" label="UV" width="108">
        </el-table-column>
        <el-table-column prop="sub_ic_pv" label="点击数" width="108">
        </el-table-column>
        <el-table-column prop="sub_cl_rate" label="点击率" width="108">
        </el-table-column>
        <el-table-column prop="empty" label="购买客户数" width="125">
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
  import { getDataReport, getReportHOmeList, ZF_getCountrySiteList } from "../plugin/api";
  import { getCookie } from "../plugin/mUtils";
  import '../../resources/stylesheets/HomePageAdData.css'

  export default {
    components: { siteLayout },
    data() {
       return {
        search: {
          type: '1',
          platformValue: 'all',
          isNewListValue: '2',
          dateValue: [],
          dateRangeValue: [],
          btnStatus: false,
          pipeline: '' // 选中的ZAFUL的渠道
        },
        options: {
          pageNo: 1,
          pageSize: 20,
          periodValue: '0',
          excel: 0
        },
        total: 0,
        platformList: [{ label: '全部', value: 'all' },{ label: 'PC端', value: 'pc' },{ label: 'M端', value: 'm' }],
        isNewList: [{ label: '整体', name: '2' },{ label: '新客', name: '1' },{ label: '老客', name: '0' }],
        periodList: [{ name: '昨天', label: '0' },{ name: '七天', label: '1' },{ name: '一个月', label: '2' },{ name: '三个月', label: '3' }],
        tableData: [],
        site_code: '',
        pipelines: [], // ZAFUL的渠道列表数据
      }
    },
    created() {
      // 获取当前站点
      this.site_code = getCookie('site_group_code')      
      // 获取zaful的渠道列表
      if (this.site_code === 'zf') {
        this.getZafulCountrySiteList()
      }
      // ???
      this.getTimeRange()
      // 获取报表数据
      this.getHomeDataReportFn()
    },
    methods: {
      // 获取ZAFUL站点的所有渠道列表
      async getZafulCountrySiteList() {
        try {
          const res = await ZF_getCountrySiteList({ activity_type: 2 })
          this.pipelines = res.data.all_pipelines || [];
        } catch (err) {

        }
      },
      async getHomeDataReportFn() {
        let param = {
          platform: this.search.platformValue,
          show_type: this.search.type,
          buyer_identity: this.search.isNewListValue,
          pageSize: this.options.pageSize,
          pageNo: this.options.pageNo,
          pipeline: this.search.pipeline,
        }
        if (this.search.dateRangeValue.length) {
          param.start_time = this.search.dateRangeValue[0]
          param.end_time = this.search.dateRangeValue[1]
        } else if (this.search.dateValue.length) {
          param.start_time = this.search.dateValue[0]
          param.end_time = this.search.dateValue[1]
        }

        let res = await getReportHOmeList(param)
        if (res.code == 0) {
          // this.search.btnStatus = false
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
        this.getHomeDataReportFn()
      },

      handleSizeChange (value) {
        this.options.pageSize = value
        this.options.pageNo = 1
        this.getHomeDataReportFn()
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

        let href = `${location.origin}/base/report/home-page-total-data-list?platform=${param.platform}&show_type=${param.show_type}&buyer_identity=${param.buyer_identity}&pageSize=${param.pageSize}&pageNo=${param.pageNo}&excel=1&start_time=${param.start_time}&end_time=${param.end_time}`

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
       * 搜索
       */
      searchData () {
        // this.search.btnStatus = true
        this.options.pageNo = 1
        this.getHomeDataReportFn()
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
        this.getHomeDataReportFn()
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

      objectSpanMethod({ row, column, rowIndex, columnIndex}) {
        if (columnIndex === 0 ||columnIndex === 1 || columnIndex === 2 || columnIndex === 3 || columnIndex === 4 || columnIndex === 5) {
          if (rowIndex % 8 === 0) {
            return {
              rowspan: 8,
              colspan: 1
            };
          } else {
            return {
              rowspan: 0,
              colspan: 0
            }
          }
        }
        // if (columnIndex === 6) {
        //   const _row = this.spanArr[rowIndex];
        //   const _col = _row > 0 ? 1 : 0;
        //   return {
        //     rowspan: _row,
        //     colspan: _col
        //   }
        // }
      },
      // getSpanArr() {　
        
      //   for (var i = 0; i < place.length; i++) {
      //     if (i === 0) {
      //       this.spanArr.push(1);
      //       this.pos = 0
      //     } else {
      //     // 判断当前元素与上一个元素是否相同
      //       if (place[i] === place[i - 1]) {
      //         this.spanArr[this.pos] += 1;
      //         this.spanArr.push(0);
      //       } else {
      //         this.spanArr.push(1);
      //         this.pos = i;
      //       }
      //     }
      //   }
      // },
    }
  }
</script>








