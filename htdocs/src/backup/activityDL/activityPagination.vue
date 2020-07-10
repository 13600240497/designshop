<template>
  <el-row v-if="oSearch.total > 10">
    <el-col :span="24" class="text-right geshop-article-page">
      <el-pagination layout="sizes, prev, pager, next" :page-size="10" :current-page="oSearch.currentPage" :total="oSearch.total" @size-change="handleSizeChange"  @current-change="handleCurrentChange"></el-pagination>
    </el-col>
  </el-row>
</template>

<script>
export default {
  props: {
    oSearch: {
      type: Object
    },
    getActivityList: {
      type: Function
    }
  },
  methods: {
    /**
		 * @description 页码切换
		 */
		handleCurrentChange(currentPage) {
			this.oSearch.currentPage = currentPage
			this.getActivityList()
		},
        handleSizeChange(pageSize) {
            this.oSearch.pageSize = pageSize;
            this.getActivityList();
        }
  }
}
</script>

