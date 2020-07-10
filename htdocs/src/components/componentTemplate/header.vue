<template>
  <el-row class="geshop-pagetpl-btn">
    <el-col :span="24">
      <el-form :inline="true" :model="search">
        <el-form-item label="模板类型">
          <el-select v-model="search.type" placeholder="请选择">
            <el-option label="所有类型" value="0"></el-option>
            <el-option label="公有模板" value="1">公有模板</el-option>
            <el-option label="私有模板" value="2">私有模板</el-option>
          </el-select>
        </el-form-item>
        <el-form-item label="应用环境">
          <el-select v-model="search.place" placeholder="请选择" @change="handleChangePlace">
            <el-option label="活动页" value="1">活动页</el-option>
            <el-option label="首页" value="2">首页</el-option>
            <el-option label="推广页" value="3">推广页</el-option>
          </el-select>
        </el-form-item>
        <el-form-item label="应用端口">
          <el-select v-model="publicData.templateTabName" @change="handleTemplateSelect" placeholder="请选择">
            <el-option
                v-for="(item, key) in places"
                :label="item.platform_name"
                :key="key"
                :name="key"
                :value="key">
            </el-option>
          </el-select>
        </el-form-item>
        <el-form-item label="组件类型">
          <el-select v-model="search.uiKey" filterable clearable placeholder="请选择">
            <el-option v-for="item in componentList" :key="item.key" :label="item.name" :value="item.key"></el-option>
          </el-select>
        </el-form-item>
        <el-form-item>
          <el-button type="primary" @click="doSearch">搜索</el-button>
        </el-form-item>
      </el-form>
    </el-col>
  </el-row>
</template>

<script>

export default {
  /**
   * @description props
   * @param { Array } componentList 组件列表数据
   * @param { Function } getComponentList 获取组件列表方法
   * @param { Function } getPageUiTemplateList 获取组件模板列表方法
   */
  props: {
    search: {
      type: Object
    },
    publicData: {
      type: Object
    },
    componentList: {
      type: Array
    },
    getComponentList: {
      type: Function
    },
    getPageUiTemplateList: {
      type: Function
    }
  },
  data() {
    return {
      places: [],
    }
  },
  methods: {
    doSearch () {
			this.search.currentPage = 1
			this.getPageUiTemplateList()
    },
    /**
		 * 应用端口切换
		 */
		handleTemplateSelect (val) {
      this.publicData.templateTabName = val
      this.getComponentList(this.search.place)
    },
    /**
     * 应用环境区分 */
    handleChangePlace(val) {
      this.getComponentList(val)
    }
  },
  mounted () {
    // 设置当前站点信息
		this.places = JSON.parse(localStorage.currentSites).sites
  }
}
</script>

