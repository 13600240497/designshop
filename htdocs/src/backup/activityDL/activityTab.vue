<template>
  <el-tabs v-model="commonData.activityTabName" type="card" @tab-click="handleActivityTabClick" class="tab-header">
    <el-tab-pane v-for="(item,key) in commonData.places" :key="key" :label="item.platform_name" :name="key"></el-tab-pane>
  </el-tabs>
</template>

<script>
export default {
  props: {
    commonData: {
      type: Object
    },
    oSearch: {
      type: Object
    },
    getActivityList: {
      type: Function
    }
  },
  methods: {
    /**
		 * @description PC，M，APP切换
		 */
		handleActivityTabClick(event) {
			this.commonData.activityTabName = event.name
			this.oSearch.currentPage = 1
			this.getActivityList()
		},
  }
}
</script>
