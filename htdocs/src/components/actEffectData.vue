<template>
	<site-layout>
		<el-row :span="24" class="gs-activity-ad-tit">
			<span class="gs-activity-ad-title">活动页整体效果统计</span>
		</el-row>
		<el-row :span="24" class="gs-activity-ad-btn">
			<el-col class="gs-activity-ad-port">
        <span class="gs-activity-ad-port-name">端口</span>
				<el-select v-model="value" placeholder="全部" class="gs-activity-ad-port-select">
          <el-option
            v-for="item in options"
            :key="item.value"
            :label="item.label"
            :value="item.value">
          </el-option>
        </el-select>
			</el-col>
			<el-col class="gs-activity-ad-time">
			  <el-button-group>
          <el-button>昨天</el-button>
          <el-button>七天</el-button>
          <el-button>一个月</el-button>
          <el-button>三个月</el-button>
        </el-button-group>
			</el-col>
      <el-col class="gs-activity-ad-screening">
        <span class="gs-activity-ad-screening-time">时间段筛选</span>
        <el-date-picker v-model="value5" type="datetimerange" class="gs-activity-ad-screening-select"
          :picker-options="pickerOptions2"
          range-separator="至"
          start-placeholder="开始日期"
          end-placeholder="结束日期" align="right">
        </el-date-picker>
      </el-col>
			<el-col class="gs-activity-ad-search">
        <el-button type="primary">搜索</el-button>
			</el-col>
      <el-col class="gs-activity-ad-export">
        <el-button @click="portExcel()">导出EXCEL</el-button>
			</el-col>
		</el-row>
    <el-tabs type="card" class="gs-activity-ad-swtich">
      <el-tab-pane label="整体" name="first">整体</el-tab-pane>
      <el-tab-pane label="新客" name="second">新客</el-tab-pane>
      <el-tab-pane label="老客" name="third">老客</el-tab-pane>
    </el-tabs>
    <div class="gs-activity-ad-table gs-effect-data">
      <el-table :data="tableData" style="width: 100%" :span-method="objectSpanMethod">
        <el-table-column prop="date" label="日期" width="108">
        </el-table-column>
        <el-table-column prop="indicators" label="指标" width="134">
        </el-table-column>
        <el-table-column label="专题一">
          <el-table-column prop="pv" label="PV" width="104">
          </el-table-column>
          <el-table-column prop="uv" label="UV" width="104">
          </el-table-column>
          <el-table-column prop="NewYorkerNum" label="新客数" width="104">
          </el-table-column>
          <el-table-column prop="NewYorkerProportion" label="新客占比" width="104">
          </el-table-column>
          <el-table-column prop="porderNum" label="销售额" width="104">
          </el-table-column>
          <el-table-column prop="salesProportion" label="销售占比" width="104">
          </el-table-column>
          <el-table-column prop="conversionRate" label="转换率" width="104">
          </el-table-column>
        </el-table-column>
        <el-table-column label="专题二">
          <el-table-column prop="pv_t" label="PV" width="104">
          </el-table-column>
          <el-table-column prop="uv_t" label="UV" width="104">
          </el-table-column>
          <el-table-column prop="NewYorkerNum_t" label="新客数" width="104">
          </el-table-column>
          <el-table-column prop="NewYorkerProportion_t" label="新客占比" width="104">
          </el-table-column>
          <el-table-column prop="porderNum_t" label="销售额" width="104">
          </el-table-column>
          <el-table-column prop="salesProportion_t" label="销售占比" width="104">
          </el-table-column>
          <el-table-column prop="conversionRate_t" label="转换率" width="104">
          </el-table-column>
        </el-table-column>
      </el-table>
    </div>
    <el-row class="gs-paging-areas">
			<el-col :span="24" class="text-right geshop-article-page">
				<el-pagination layout="prev, pager, next" :total="500"></el-pagination>
			</el-col>
		</el-row>
	</site-layout>
</template>

<script>
  import siteLayout from "./layouts/Layout.vue";
  import {} from "../plugin/api";
  import { getCookie } from "../plugin/mUtils";
  import '../../resources/stylesheets/HomePageAdData.css'
  import '../../resources/stylesheets/actEffectData.css'

  export default {
    components: { siteLayout },
    data() {
      return {
        tableData: [{
          date: '2018-12-21',
          indicators: '全站销售(实收)',
          pv:'1000',
          uv:'1000',
          NewYorkerNum:'1000',
          NewYorkerProportion:'10%',
          porderNum:'1000',
          salesProportion:'10%',
          conversionRate:'10%',
          pv_t:'1000',
          uv_t:'1000',
          NewYorkerNum_t:'1000',
          NewYorkerProportion_t:'10%',
          porderNum_t:'1000',
          salesProportion_t:'10%',
          conversionRate_t:'10%',
        }, {
          date: '2018-12-21',
          indicators: 'PC端',
          pv:'1000',
          uv:'1000',
          NewYorkerNum:'1000',
          NewYorkerProportion:'10%',
          porderNum:'1000',
          salesProportion:'10%',
          conversionRate:'10%',
          pv_t:'1000',
          uv_t:'1000',
          NewYorkerNum_t:'1000',
          NewYorkerProportion_t:'10%',
          porderNum_t:'1000',
          salesProportion_t:'10%',
          conversionRate_t:'10%',
        }, {
          date: '2018-12-21',
          indicators: 'APP端',
          pv:'1000',
          uv:'1000',
          NewYorkerNum:'1000',
          NewYorkerProportion:'10%',
          porderNum:'1000',   
          salesProportion:'10%',
          conversionRate:'10%',
          pv_t:'1000',
          uv_t:'1000',
          NewYorkerNum_t:'1000',
          NewYorkerProportion_t:'10%',
          porderNum_t:'1000',
          salesProportion_t:'10%',
          conversionRate_t:'10%',
        }, {
          date: '2018-12-21',
          indicators: 'M端',
          pv:'1000',
          uv:'1000',
          NewYorkerNum:'1000',
          NewYorkerProportion:'10%',
          porderNum:'1000',
          salesProportion:'10%',
          conversionRate:'10%',
          pv_t:'1000',
          uv_t:'1000',
          NewYorkerNum_t:'1000',
          NewYorkerProportion_t:'10%',
          porderNum_t:'1000',
          salesProportion_t:'10%',
          conversionRate_t:'10%',
        }, {
          date: '2018-12-21',
          indicators: 'PC端占比',
          pv:'1000',
          uv:'1000',
          NewYorkerNum:'1000',
          NewYorkerProportion:'10%',
          porderNum:'1000',
          salesProportion:'10%',
          conversionRate:'10%',
          pv_t:'1000',
          uv_t:'1000',
          NewYorkerNum_t:'1000',
          NewYorkerProportion_t:'10%',
          porderNum_t:'1000',
          salesProportion_t:'10%',
          conversionRate_t:'10%',
        }, {
          date: '2018-12-21',
          indicators: 'APP端占比',
          pv:'1000',
          uv:'1000',
          NewYorkerNum:'1000',
          NewYorkerProportion:'10%',
          porderNum:'1000',
          salesProportion:'10%',
          conversionRate:'10%',
          pv_t:'1000',
          uv_t:'1000',
          NewYorkerNum_t:'1000',
          NewYorkerProportion_t:'10%',
          porderNum_t:'1000',
          salesProportion_t:'10%',
          conversionRate_t:'10%',
        }, {
          date: '2018-12-21',
          indicators: 'M端占比',
          pv:'1000',
          uv:'1000',
          NewYorkerNum:'1000',
          NewYorkerProportion:'10%',
          porderNum:'1000',
          salesProportion:'10%',
          conversionRate:'10%',
          pv_t:'1000',
          uv_t:'1000',
          NewYorkerNum_t:'1000',
          NewYorkerProportion_t:'10%',
          porderNum_t:'1000',
          salesProportion_t:'10%',
          conversionRate_t:'10%',
        }, {
          date: '2018-12-20',
          indicators: '全站销售(实收)',
          pv:'1000',
          uv:'1000',
          NewYorkerNum:'1000',
          NewYorkerProportion:'10%',
          porderNum:'1000',
          salesProportion:'10%',
          conversionRate:'10%',
          pv_t:'1000',
          uv_t:'1000',
          NewYorkerNum_t:'1000',
          NewYorkerProportion_t:'10%',
          porderNum_t:'1000',
          salesProportion_t:'10%',
          conversionRate_t:'10%',
        }, {
          date: '2018-12-20',
          indicators: 'PC端',
          pv:'1000',
          uv:'1000',
          NewYorkerNum:'1000',
          NewYorkerProportion:'10%',
          porderNum:'1000',
          salesProportion:'10%',
          conversionRate:'10%',
          pv_t:'1000',
          uv_t:'1000',
          NewYorkerNum_t:'1000',
          NewYorkerProportion_t:'10%',
          porderNum_t:'1000',
          salesProportion_t:'10%',
          conversionRate_t:'10%',
        }, {
          date: '2018-12-20',
          indicators: 'APP端',
          pv:'1000',
          uv:'1000',
          NewYorkerNum:'1000',
          NewYorkerProportion:'10%',
          porderNum:'1000',
          salesProportion:'10%',
          conversionRate:'10%',
          pv_t:'1000',
          uv_t:'1000',
          NewYorkerNum_t:'1000',
          NewYorkerProportion_t:'10%',
          porderNum_t:'1000',
          salesProportion_t:'10%',
          conversionRate_t:'10%',
        }, {
          date: '2018-12-20',
          indicators: 'M端',
          pv:'1000',
          uv:'1000',
          NewYorkerNum:'1000',
          NewYorkerProportion:'10%',
          porderNum:'1000',
          salesProportion:'10%',
          conversionRate:'10%',
          pv_t:'1000',
          uv_t:'1000',
          NewYorkerNum_t:'1000',
          NewYorkerProportion_t:'10%',
          porderNum_t:'1000',
          salesProportion_t:'10%',
          conversionRate_t:'10%',
        }, {
          date: '2018-12-20',
          indicators: 'PC端占比',
          pv:'1000',
          uv:'1000',
          NewYorkerNum:'1000',
          NewYorkerProportion:'10%',
          porderNum:'1000',
          salesProportion:'10%',
          conversionRate:'10%',
          pv_t:'1000',
          uv_t:'1000',
          NewYorkerNum_t:'1000',
          NewYorkerProportion_t:'10%',
          porderNum_t:'1000',
          salesProportion_t:'10%',
          conversionRate_t:'10%',
        }, {
          date: '2018-12-20',
          indicators: 'APP端占比',
          pv:'1000',
          uv:'1000',
          NewYorkerNum:'1000',
          NewYorkerProportion:'10%',
          porderNum:'1000',
          salesProportion:'10%',
          conversionRate:'10%',
          pv_t:'1000',
          uv_t:'1000',
          NewYorkerNum_t:'1000',
          NewYorkerProportion_t:'10%',
          porderNum_t:'1000',
          salesProportion_t:'10%',
          conversionRate_t:'10%',
        }, {
          date: '2018-12-20',
          indicators: 'M端占比',
          pv:'1000',
          uv:'1000',
          NewYorkerNum:'1000',
          NewYorkerProportion:'10%',
          porderNum:'1000',
          salesProportion:'10%',
          conversionRate:'10%',
          pv_t:'1000',
          uv_t:'1000',
          NewYorkerNum_t:'1000',
          NewYorkerProportion_t:'10%',
          porderNum_t:'1000',
          salesProportion_t:'10%',
          conversionRate_t:'10%',
        }]
      }
    },
    created() {},
    methods: {
      async portExcel() {
			  this.$confirm('确认导出Excel？', '提示', {
          distinguishCancelAndClose: true,
          confirmButtonText: '确定',
          cancelButtonText: '取消'
        })
		  },
      objectSpanMethod({ row, column, rowIndex, columnIndex }) {
        if (columnIndex === 0) {
          if (rowIndex % 7 === 0) {
            return {
              rowspan: 7,
              colspan: 1
            };
          } else {
            return {
              rowspan: 0,
              colspan: 0
            };
          }
        }
      }
    }
  }
</script>



