<template>
  <el-row v-if="Number(publicData.total) > 10">
    <el-col :span="24" class="text-right geshop-article-page">
      <el-pagination layout="prev, pager, next" :page-size="10" :total="Number(publicData.total)" @current-change="handleCurrentChange"></el-pagination>
    </el-col>
  </el-row>
</template>

<script>
export default {
  props: {
    publicData: {
      type: Object
    },
    handleCurrentChange: {
      type: Function
    }
  },
  data() {
    return {
      
    }
  },
  methods: {
    
  },
}
</script>

