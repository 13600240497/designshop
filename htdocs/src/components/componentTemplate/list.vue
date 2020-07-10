
<template>
  <el-row class="geshop-pagetpl-lists">
    <el-col :span="24">
      <el-table :data="templateList" style="width: 100%">
        <el-table-column prop="id" label="ID" width="80" margin-left="48"></el-table-column>
        <el-table-column prop="pic_url" label="图片" width="120">
          <template slot-scope="scope">
            <div class="pagetpl-preview-img" v-if="scope.row.pic_url">
              <img :src="scope.row.pic_url" />
            </div>
            <div class="pagetpl-preview-default-img" v-if="scope.row.pic_url == ''">
              <img src="/resources/images/icon/temp-default-image.png"/>
            </div>
          </template>
        </el-table-column>
        <el-table-column prop="templateTabName" label="应用端口">
          <template slot-scope="scope">
            <span>{{ scope.row.platform_name }}</span>
          </template>
        </el-table-column>
        <el-table-column prop="name" label="名称"></el-table-column>
        <el-table-column prop="tpl_type" label="模板类型">
          <template slot-scope="scope">
            <span v-if="scope.row.view_type == 1">公有模板</span>
            <span v-else-if="scope.row.view_type == 2">私有模板</span>
          </template>
        </el-table-column>
        <el-table-column prop="place_type" label="应用环境">
          <template slot-scope="scope">
            <span v-if="scope.row.place_type==1">活动页</span>
            <span v-else-if="scope.row.place_type==2">首页</span>
            <span v-else>推广页</span>
          </template>
        </el-table-column>
        <el-table-column prop="ui_name" label="组件类型"></el-table-column>
        <el-table-column prop="real_name" label="创建者"></el-table-column>
        <el-table-column prop="create_time" label="存入时间" width="200">
          <template slot-scope="scope">
            <span>{{ Number(scope.row.create_time) | moment('YYYY-MM-DD HH:mm:ss') }}</span>
          </template>
        </el-table-column>
        <el-table-column prop="update_time" label="最后修改时间" width="200">
          <template slot-scope="scope">
            <span>{{ Number(scope.row.update_time) | moment('YYYY-MM-DD HH:mm:ss') }}</span>
          </template>
        </el-table-column>
        <el-table-column label="操作" min-width="120" class-name="component-template-actionbar">
          <template slot-scope="scope">
            <el-button type="primary" size="small" @click="editTemplate(scope.row)" v-if="scope.row.opt == 1 || is_super == 1">编辑</el-button>
            <el-button type="danger" size="small" @click="deleteTemplate(scope.row.id)" v-if="scope.row.opt == 1 || is_super == 1">删除</el-button>
            <el-tooltip content="查看" placement="bottom" effect="light">
              <el-button class="icon-geshop-search" @click="seeTemplate(scope.row)"></el-button>
            </el-tooltip>
          </template>
        </el-table-column>
      </el-table>
    </el-col>
  </el-row>
</template>

<script>

export default {
  /**
   * @description props
   * @param { Array } templateList 模板列表数据
   */
  props: {
    templateList: {
      type: Array
    },
    editTemplate: {
      type: Function
    },
    deleteTemplate: {
      type: Function
    },
    seeTemplate: {
      type: Function
    }
  },
  data() {
    return {
      
    }
  },
  methods: {
    
  },
  mounted () {
    
  }
}
</script>

<style scoped>
  .geshop-pagetpl-lists .pagetpl-preview-img,
  .geshop-pagetpl-lists .pagetpl-preview-default-img {
    width: 54px;
    height: 54px;
    overflow: hidden;
  }
  .geshop-pagetpl-lists .pagetpl-preview-img img {
    /* width: 100%; */
    /* 产品要求图片始终显示54 x 54 */
    width: 54px;
    height: 54px;
  }
  .geshop-pagetpl-lists .pagetpl-preview-default-img {
    background-color: #EDEDED;
    text-align: center;
    line-height: 54px;
  }
  .geshop-pagetpl-lists .pagetpl-preview-default-img img {
    vertical-align: middle;
  }
</style>

