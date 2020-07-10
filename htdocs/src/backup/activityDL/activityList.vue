<template>
  <el-col :span="24" class="geshop-activity-lists">
    <el-table 
      :data="commonData.activityList" 
      @expand-change="handleExpandChange" 
      style="width: 100%" 
      :row-key="getRowKey" 
      :expand-row-keys="commonData.expandRowKeys" 
      @row-click="handleExpandChange"
      :row-class-name="tableRowClassName">
      
      <!-- 子页面列表 start -->
      <activityListPageComponent
        :commonData="commonData"
        :pageForm="pageForm"
        :convertToAPPForm="convertToAPPForm"
        :publicPageForm="publicPageForm"
        :viewAccessLink="viewAccessLink"
        :getPages="getPages"
        :getActivityList="getActivityList"
        :createPage="createPage">
      </activityListPageComponent>
      <!-- 子页面列表 end -->
      
      <el-table-column prop="id" label="ID" width="100"></el-table-column>
      <el-table-column prop="name" label="活动名称" width="260"></el-table-column>
      <el-table-column label="状态" align="center">
        <template slot-scope="scope">
          <span v-if="scope.row.status == 1">
            <em class="geshop-icon-online-stay"></em>
            <em style="font-style:normal;">待上线</em>
          </span>
          <span v-else-if="scope.row.status == 2">
            <em class="geshop-icon-online"></em>
            <em style="font-style:normal;">已上线</em>
          </span>
          <span v-else-if="scope.row.status == 4">
            <em class="geshop-icon-offline"></em>
            <em style="font-style:normal;">已下线</em>
          </span>
        </template>
      </el-table-column>
      <el-table-column label="创建时间" width="160" align="center">
        <template slot-scope="scope">
          <span>{{ parseInt(scope.row.create_time)|moment('YYYY-MM-DD HH:mm:ss')}}</span>
        </template>
      </el-table-column>
      <el-table-column prop="create_name" align="center" label="创建者"></el-table-column>
      <el-table-column prop="update_time" align="center" width="160" label="最后操作时间">
        <template slot-scope="scope">
          <span>{{ parseInt(scope.row.update_time) | moment('YYYY-MM-DD HH:mm:ss') }}</span>
        </template>
      </el-table-column>
      <el-table-column prop="update_user" align="center" width="150" label="最后操作人"></el-table-column>
      <!-- 活动操作 -->
      <el-table-column label="操作" align="center" width="450" class-name="ge-activity-list-operating">
        <template slot-scope="scope" style="line-height:80px;">
          <el-tooltip content="新增子页面" placement="bottom" effect="light">
            <el-button class="ge-activity-list-icon icon-geshop-add-small" style="font-size:24px;" v-if="scope.row.is_lock == 0" @click.stop="createPage(scope.row.id, scope.row.group_info.lang_list, scope.row.group_info.platform_list, scope.row.is_lock, scope.row.create_user,scope.row)"></el-button>
          </el-tooltip>
          <el-button class="ge-activity-list-icon icon-geshop-add-small is-lock" v-if="scope.row.is_lock == 1" @click.stop="createPage(scope.row.id, scope.row.group_info.lang_list, scope.row.group_info.platform_list, scope.row.is_lock, scope.row.create_user,scope.row)"></el-button>
          <el-tooltip content="上线" placement="bottom" effect="light">
            <el-button class="ge-activity-list-icon icon-geshop-online" style="font-size:24px;" v-show="scope.row.is_lock == 0" @click.stop="verifyActivity(scope.row.id, 2, scope.row.is_lock, scope.row.create_user)" v-if="commonData.permissions.includes('activity/activity/verify') && [1, 4].includes(scope.row.status)">
            </el-button>
          </el-tooltip>
          <el-tooltip content="上线" placement="bottom" effect="light">
            <el-button class="ge-activity-list-icon icon-geshop-online" style="font-size:24px;color:#9E9E9E;background-color:#ffffff;" v-show="scope.row.is_lock == 1" @click.stop="verifyActivity(scope.row.id, 2, scope.row.is_lock, scope.row.create_user)" v-if="commonData.permissions.includes('activity/activity/verify') && [1, 4].includes(scope.row.status)">
            </el-button>
          </el-tooltip>
          <el-tooltip content="下线" placement="bottom" effect="light">
            <el-button class="ge-activity-list-icon icon-geshop-offline" style="font-size:24px;" v-show="scope.row.is_lock == 0" @click.stop="verifyActivity(scope.row.id, 4, scope.row.is_lock, scope.row.create_user)" v-if="commonData.permissions.includes('activity/activity/verify') && scope.row.status == 2"></el-button>
          </el-tooltip>
          <el-tooltip content="下线" placement="bottom" effect="light">
            <el-button class="ge-activity-list-icon icon-geshop-offline" style="font-size:24px;color:#9E9E9E;background-color:#ffffff;" v-show="scope.row.is_lock == 1" @click.stop="verifyActivity(scope.row.id, 4, scope.row.is_lock, scope.row.create_user)" v-if="commonData.permissions.includes('activity/activity/verify') && scope.row.status == 2"></el-button>
          </el-tooltip>
          <el-dropdown split-button style="margin-left:10px;">
            <el-dropdown-menu slot="dropdown">
              <el-dropdown-item type="primary" size="small" @click.native="editActivity(scope.row, scope.row.is_lock, scope.row.create_user)">编辑</el-dropdown-item>
              <el-dropdown-item type="danger" size="small" @click.native="removeActivity(scope.row.id, scope.row.is_lock, scope.row.create_user)">删除</el-dropdown-item>
              <el-dropdown-item type="primary" @click.native="viewDetail(scope.row)" size="small">查看二维码</el-dropdown-item>
                <el-dropdown-item type="primary" @click.native="handleActivityLabel(scope.row)" size="small" v-if="scope.row.is_frequently === 0">设置常用</el-dropdown-item>
                <el-dropdown-item type="primary" @click.native="handleActivityLabel(scope.row)" size="small" v-if="scope.row.is_frequently === 1">移除常用</el-dropdown-item>
            </el-dropdown-menu>
          </el-dropdown>
          <span style="margin-left:10px;line-height:80px;">锁定</span>
          <el-switch v-model="scope.row.lock_status" @change="lockingActivity(scope.row.id, scope.row.is_lock, scope.row.create_user, $event)"></el-switch>
        </template>
      </el-table-column>
    </el-table>
  </el-col>
</template>

<script>
import { DL_verifyActivity, DL_deleteActivity, DL_lockingActivity,DL_getFrequently } from '../../plugin/api'
import activityListPageComponent from './activityListPage.vue'
import '../../../resources/stylesheets/frequently.less'

export default {
  /**
   * @description props
   * @param { Object } commonData - 公共数据
   * @param { Object } activityForm - 活动表单数据
   * @param { Object } convertToAPPForm - WEB转APP表单数据
   * @param { Object } pageForm - 子页面表单数据
   * @param { Object } publicPageForm - 公共数据
   * @param { Object } viewAccessLink - 查看访问链接
   * @param { Function } getPages - 获取子页面列表方法
   * @param { Function } getActivityList - 获取活动列表方法
   */
  props: {
    commonData: {
      type: Object
    },
    activityForm: { 
      type: Object
    },
    convertToAPPForm: {
      type: Object
    },
    pageForm: {
      type: Object
    },
    publicPageForm: {
      type: Object
    },
    viewAccessLink: {
      type: Object
    },
    getPages: {
      type: Function
    },
    getActivityList: {
      type: Function
    }
  },
  components: {
    activityListPageComponent
  },
  data () {
    return {
      
    }
  },
  created () {
    
  },
  methods: {

    /**
		 * @description 表格展开收起
		 */
		handleExpandChange(row) {
			if (!row.children) {
        this.getPages(row.id)
			}

			this.commonData.langList = row.langList

			if (this.commonData.expandRowKeys.indexOf(row.id) === -1) {
				this.commonData.expandRowKeys = [row.id]
			} else {
				this.commonData.expandRowKeys = []
			}
    },

    /**
     * @description 活动锁定
     */
		lockingActivity(id, is_lock, create_user, $event) {
			event.stopPropagation()
			if ((create_user != this.commonData.siteInfo.userName) && this.commonData.siteInfo.isSuper != 1) {
				this.$message('只有活动创建者才具有此权限!')
			} else {
				var tip = ''
				if (is_lock == 1) {
					tip = '该活动解锁后，其他用户将拥有与您一样的操作权限，是否解锁？'
				} else if (is_lock == 0) {
					tip = '该活动加锁后，其他用户将不能操作此活动及其相关所有页面，是否加锁？'
				}

				this.$confirm(tip, '提示', {
					confirmButtonText: '是',
					cancelButtonText: '否',
					type: 'warning'
        })
        .then(async () => {
					let params = {
							id: id
						},
						res = await DL_lockingActivity(params)

					if (res.code === 0) {
						this.getActivityList()
						this.$message({
							type: 'success',
							message: res.message
						})
					} else {
						this.$message({
							type: 'error',
							message: res.message
						})
					}
        })
        .catch((err) => {
          if (err) {

					}
					this.commonData.activityList.forEach(function (element) {
						if (element.is_lock == 0) {
							element.lock_status = false
						} else if (element.is_lock == 1) {
							element.lock_status = true
						}
					})
				})
			}
		},

    /**
		 * @description 查看二维码
		 */
		viewDetail(row) {
			this.commonData.currentPageRow = {}
			this.commonData.currentActivityRow = row
			this.commonData.isDetailActive = true
			this.commonData.langList = row.langList
		},
    /**
		 * @description 活动上下线
		 */
		verifyActivity(id, status, is_lock, create_user) {
			if ((is_lock == 1 && create_user != this.commonData.siteInfo.userName) && this.commonData.siteInfo.isSuper != 1) {
				this.$message('该活动已被创建者锁定，需创建者解锁后其他用户才能操作')
			} else {
				var tip = ''

				if (status == 2) {
					tip = '确认上线该活动吗？'
				} else {
					tip = '确认下线该活动？下线后，该活动的所有页面将下线'
				}

				this.$confirm(tip, '提示', {
          confirmButtonText: '确定',
          cancelButtonText: '取消',
          type: 'warning'
        })
        .then(async () => {
          const fullscreenLoading = this.$loading({
            lock: true,
            text: 'Loading',
            spinner: 'el-icon-loading',
            background: 'rgba(0, 0, 0, 0.8)'
          })

          let params = {
              id: id,
              status: status
            },
            res = await DL_verifyActivity(params)

          fullscreenLoading.close()
          this.commonData.isDetailActive = false
          this.getActivityList()
          this.commonData.expandRowKeys = []
        })
        .catch((err) => {
          if (err) {

					}
          this.$message({
            type: 'info',
            message: '已取消操作'
          });          
        })
			}
    },

		/**
		 * @description 删除活动
		 */
		removeActivity(id, is_lock, create_user) {
			if ((is_lock == 1 && create_user != this.commonData.siteInfo.userName) && this.commonData.siteInfo.isSuper != 1) {
				this.$message('该活动已被创建者锁定，需创建者解锁后其他用户才能操作')
			} else {
        this.$confirm('活动删除后，其所有的子页面也将被删除，确认删除？', '提示', {
          confirmButtonText: '确定',
          cancelButtonText: '取消',
          type: 'warning'
        })
        .then(async () => {
          let params = {
            id: id
          },
          res = await DL_deleteActivity(params)

        this.commonData.isDetailActive = false
        this.getActivityList()

        if (res.code === 0) {
          this.$message({
            type: 'success',
            message: res.message
          })
        } else {
          this.$message({
            type: 'error',
            message: res.message
          })
        }
        })
        .catch((err) => {
          if (err) {

					}
          this.$message({
            type: 'info',
            message: '已取消操作'
          })
        })
			}
    },
    
    /**
		 * @description 编辑活动
		 */
		editActivity(row, is_lock, create_user) {
			if ((is_lock == 1 && create_user != this.commonData.siteInfo.userName) && this.commonData.siteInfo.isSuper != 1) {
				this.$message('该活动已被创建者锁定，需创建者解锁后其他用户才能操作')
			} else {
				// 当前为编辑活动状态
				this.activityForm.status = 2
				this.activityForm.id = row.id
				this.activityForm.type = String(row.type)
				this.activityForm.name = row.name
				this.activityForm.site_code = row.site_code.split('-')[1]
				this.activityForm.place = Array(row.site_code.split('-')[1])

				// 新增活动时已选应用端口列表
				this.commonData.editPlaces = row.group_info.platform_list
				this.commonData.editSupportLangs = row.group_info.lang_list

				// 主活动已勾选端口
				let editPlaceArr = []
				row.group_info.platform_list.forEach((item) => {
					editPlaceArr.push(item.code)
				})
				this.activityForm.editPlace = editPlaceArr

				// 主活动已勾选语言
				let editSupportLangArr = []
				row.group_info.lang_list.forEach((item) => {
					editSupportLangArr.push(item.key)
				})
				this.activityForm.editSupportLang = editSupportLangArr
				this.activityForm.description = row.description
				this.activityForm.refresh_time = row.refresh_time
				this.activityForm.dialogTitle = '编辑活动'
				this.activityForm.actNameCount = this.activityForm.name.split('').length
				this.activityForm.actIntroductionCount = this.activityForm.description.split('').length
				this.activityForm.dialogActivityVisible = true
			}
		},

    /**
		 * @description 添加子页面
		 */
		createPage(activityId, langList, placeList, is_lock, create_user, row) {
			if ((is_lock == 1 && create_user != this.commonData.siteInfo.userName) && this.commonData.siteInfo.isSuper != 1) {
				this.$message('该活动已被创建者锁定，需创建者解锁后其他用户才能操作')
			} else {
				this.commonData.currentPageRow = {}

				// 显示活动语言
				this.commonData.langList = langList

				// 新增活动时勾选的端口
				this.commonData.pagePlaces = placeList

				// 当前语言和默认第一种语言
				this.commonData.currentLanguage = this.commonData.langList[0].key
				this.commonData.firstLanguage = this.commonData.langList[0].key
				
				this.commonData.tplId = '0'
				this.commonData.urlName = ''
				this.commonData.refreshTime = 0
				this.pageForm.id = ''
				this.pageForm.activity_id = activityId
				this.pageForm.title = ''
				this.pageForm.seo_title = ''
				this.pageForm.titleCount = 0
				this.pageForm.keywords = ''
				this.pageForm.url_name = ''
				this.pageForm.urlCount = 0
				this.pageForm.description = ''
				this.pageForm.pageIntroductionCount = 0
				this.pageForm.statistics_code = ''
				this.pageForm.codeCount = 0
				this.pageForm.redirect_url = ''
				this.pageForm.refresh_time = 0
				this.pageForm.tpl_id = '0' // 每个语种都能选择自己的模板
        this.pageForm.tpl_name = '未选中模板'
        this.pageForm.web_tpl_id = '0' // 每个语种都能选择自己的模板
				this.pageForm.web_tpl_name = '未选中模板'
        this.pageForm.app_tpl_id = '0' // 每个语种都能选择自己的模板
				this.pageForm.app_tpl_name = '未选中模板'
				this.pageForm.web_end_url = ''
				this.pageForm.end_url = ''
				this.pageForm.need_redirect = false

				this.pageForm.share_place = ['FB', 'Twitter', 'Google+']
				this.commonData.share_entrance = ['FB', 'Twitter', 'Google+']
				this.pageForm.share_image = ''
				this.pageForm.share_title = ''
				this.pageForm.share_desc = ''
				this.pageForm.share_link = ''
				this.publicPageForm.end_time = ''

				this.commonData.supportLangs.forEach((item, index) => {
					if (item.key == this.commonData.currentLanguage) {
						this.pageForm.currentSiteUrl = item.url
					}
				})

				let data = {}
				let _this = this

				this.commonData.langList.forEach(function (element) {
					data[element.key] = {
						title: '',
						keywords: '',
						url_name: '',
						description: '',
						statistics_code: '',
						redirect_url: '',
						tpl_id: '0', // 模板id
            tpl_name: '未选中模板', // 模板名称
            web_tpl_id: '0', // 模板id
						web_tpl_name: '未选中模板', // 模板名称
            app_tpl_id: '0', // 模板id
						app_tpl_name: '未选中模板', // 模板名称
						activity_id: activityId,
						seo_title: '',
						end_url: '',
						web_end_url: '',
						share_image: '',
						share_title: '',
						share_desc: '',
						share_link: ''
					}
					
				})
				this.pageForm.data = data

				// 根据当前活动已勾选端口显示对应“跳转链接”和“页面模板”
				let pagePlacesArr = []
				placeList.forEach((item) => {
					pagePlacesArr.push(item.code)
				})

				this.publicPageForm.place = pagePlacesArr

				if (pagePlacesArr.indexOf('web') != -1) {
					this.pageForm.web_status = true
				} else {
					this.pageForm.web_status = false
        }
        
        if (pagePlacesArr.indexOf('app') != -1) {
					this.pageForm.app_status = true
				} else {
					this.pageForm.app_status = false
				}

				this.currentTemplate = '未选中模板'

				this.pageForm.dialogTitle = '新增子页面'

				let platformKeys = []

				row.group_info.platform_list.forEach(function (element) {
					platformKeys.push(element.code)
				})

				if (platformKeys.indexOf('web') != -1) {
					this.pageForm.need_redirect = true
				}

				this.pageForm.dialogPageVisible = true
			}
    },
    
    getRowKey(row) {
			return row.id
		},
      /**
       * 返回行class
       * @param row
       * @param rowIndex
       */
      tableRowClassName({row,rowIndex}){
		    return row.is_frequently === 1 ? 'commonly-label' : 'commonly-img'
      },
      /**
       * 设置常用活动，取消常用活动
       * @param row
       */
      async handleActivityLabel(row){
          try{
              const res = await DL_getFrequently({
                  id: row.id
              })
              const successTip = row.is_frequently === 0 ? '设置成功' : '移除成功';
              if(res.code === 0){
                  this.$message.success(successTip);
                  this.getActivityList();
              }
          }catch(err){}
      }
  }
}
</script>
