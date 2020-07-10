<template>
  <el-table-column type="expand">
    <template slot-scope="scope">
      <el-card class="box-card geshop-activity-child-pages" v-for="item in scope.row.children" :key="item.key">
        <div>
          <div class="geshop-activity-child-pages-banner">
            <img :src="item.preview_pic_url" class="child-pages-image">
            <div class="child-page-flag-green" v-if="(item.status == 2)">上线</div>
            <div class="child-page-flag-red" v-if="(item.status == 4)">下线</div>
          </div>
          <div class="child-pages-hover">
            <div class="child-page-mask-inner">
              <el-tooltip content="装修" placement="bottom" effect="light">
                <el-button class="icon-geshop-decorate" @click="decorate_redirect(item.design_url, item.is_lock, item.activity_create_user)"></el-button>
              </el-tooltip>
              <el-tooltip content="上线" placement="bottom" effect="light">
                <el-button class="icon-geshop-online" v-if="(item.status == 1 || item.status == 4)" @click="verifyPage(item.id, item.activity_id, 2, item.is_lock , item.activity_create_user)"></el-button>
              </el-tooltip>
              <el-tooltip content="下线" placement="bottom" effect="light">
                <el-button class="icon-geshop-offline" v-if="item.status == 2" @click="verifyPage(item.id, item.activity_id, 4, item.is_lock, item.activity_create_user)"></el-button>
              </el-tooltip>
              <el-tooltip content="预览" placement="bottom" effect="light">
                <el-button class="icon-geshop-search" @click="redirect(item.preview_url)"></el-button>
              </el-tooltip>
              <el-tooltip content="转APP端" placement="bottom" effect="light">
                <el-button class="icon-geshop-mobile" @click="convertToAPP(item.id, item.is_lock, item.activity_create_user, item.group_info, item.group_languages)" v-if="item.activity_type == 7"></el-button>
              </el-tooltip>
            </div>
          </div>
        </div>
        <div class="child-pages-title">{{ item.title }}</div>
        <div class="child-pages-time">创建时间：{{ parseInt(item.create_time) | moment('YYYY-MM-DD HH:mm:ss') }} {{item.create_name}}</div>
        <div class="child-pages-time">修改时间：{{ parseInt(item.update_time) | moment('YYYY-MM-DD HH:mm:ss') }} {{item.update_user}}</div>
        <div class="child-pages-id-name">ID: {{ item.id }} <span>{{ item.create_name }}</span></div>
        <div>
          <a @click="viewPages(item.id)" class="child-pages-link">查看访问链接</a>
          <el-dropdown split-button style="margin-left:207px;">
            <el-dropdown-menu slot="dropdown">
              <el-dropdown-item type="primary" size="small" @click.native="editPage(item, item.is_lock, item.activity_create_user, scope.row.group_info.lang_list, scope.row.group_info.platform_list)">编辑</el-dropdown-item>
              <el-dropdown-item type="danger" size="small" @click.native="removePage(item, item.is_lock, item.activity_create_user, item.id, item.activity_id)">删除</el-dropdown-item>
            </el-dropdown-menu>
          </el-dropdown>
        </div>
      </el-card>
      <el-card class="box-card geshop-activity-child-pages">
        <el-col style="text-align:center">
          <img src="/resources/images/default/banner_default.png" class="child-pages-image" style="height:112px;width:100%;display:block;">
          <el-button class="icon-geshop-add-big" @click="createPage(scope.row.id, scope.row.group_info.lang_list, scope.row.group_info.platform_list, scope.row.is_lock, scope.row.create_user,scope.row)" style="font-size:40px;margin-top:28px;padding:0px 0px;"></el-button>
          <p class="child-pages-add" style="margin-left:0">添加子页面</p>
        </el-col>
      </el-card>
    </template>
  </el-table-column>
</template>

<script>
import { DL_verifyPage, DL_getAccessLink, DL_deletePage, DL_getAppActivityList } from '../../plugin/api'

export default {
  /**
   * @description props
   * @param { Object } commonData - 共用数据
   * @param { Object } viewAccessLink - 查看访问链接数据
   * @param { Object } pageForm - 公共数据
   * @param { Object } convertToAPPForm - WEB转APP数据
   * @param { Object } publicPageForm - 公共数据
   * @param { Function } getActivityList - 获取活动列表方法
   * @param { Function } getPages - 获取子页面列表方法
   * @param { Function } createPage - 初始化添加子页面表单方法
   */
  props: {
    commonData: {
      type: Object
    },
    viewAccessLink: {
      type: Object
    },
    pageForm: {
      type: Object
    },
    convertToAPPForm: {
      type: Object
    },
    publicPageForm: {
      type: Object
    },
    getActivityList: {
      type: Function
    },
    getPages: {
      type: Function
    },
    createPage: {
      type: Function
    }
  },
  created () {

  },
  methods: {

    /**
		 * @description 装修页跳转
		 */
		decorate_redirect(url, is_lock, activity_create_user) {
			if (is_lock == 1 && activity_create_user != this.commonData.siteInfo.userName && this.commonData.siteInfo.isSuper != 1) {
				this.$message('该活动已被创建者锁定，需创建者解锁后其他用户才能操作')
			} else {
				window.open(url)
			}
    },

    /**
		 * @description 链接跳转
		 */
		redirect(url) {
			window.open(url)
    },

    /**
     * @description WEB转APP
     */
    convertToAPP(id, is_lock, activity_create_user, group_info, group_languages) {
			if (is_lock == 1 && activity_create_user != this.commonData.siteInfo.userName && this.commonData.siteInfo.isSuper != 1) {
				this.$message('该活动已被创建者锁定，需创建者解锁后其他用户才能操作')
			} else {
				this.convertToAPPForm.id = id
				this.convertToAPPForm.activity_id = ''
				this.convertToAPPForm.page_id = ''
				this.convertToAPPForm.model = '1'

				// 如果有app端关联，则不需要手动选择主活动和子页面
				if (group_info.platform_list.app) {
					let languages = []

					this.convertToAPPForm.is_group = 1
					this.convertToAPPForm.source_id = group_info.platform_list.web.page_id
					this.convertToAPPForm.target_id = group_info.platform_list.app.page_id

					Object.keys(group_languages.app).forEach(function (key) {
						languages.push({
							key: group_languages.app[key].lang,
							name: group_languages.app[key].langName
						})
					})

					this.convertToAPPForm.convertLangs = languages
				} else {
					this.convertToAPPForm.is_group = 0
					this.convertToAPPForm.source_id = 0
					this.convertToAPPForm.target_id = 0
					// 获取APP端活动列表（包含子页面数据）
					this.getAppActivityList()
				}

				this.convertToAPPForm.dialogConvertAPP = true
			}
    },

    /**
		 * @description 查看访问链接
		 */
		async viewPages(id) {
			let params = {
					id: id
				},
				res = await DL_getAccessLink(params)
			if (res.code == 0) {
				this.viewAccessLink.dialogLinksVisible = true
				this.viewAccessLink.pageLinks = res.data.list
				this.viewAccessLink.tips = res.data.tips
				this.viewAccessLink.urlID = id
			}
		},

    /**
     * @description 子页面上下线
		 * @param { Number } id - 子页面ID
		 * @param { Number } status - 子页面上下线状态
     */
		verifyPage(id, activityId, status, is_lock, activity_create_user) {
			if (is_lock == 1 && activity_create_user != this.commonData.siteInfo.userName && this.commonData.siteInfo.isSuper != 1) {
				this.$message('该活动已被创建者锁定，需创建者解锁后其他用户才能操作')
			} else {
				var tip = ''

				if (status == 4) {
					tip = '确认下线该页面？'
				} else if (status == 2) {
					tip = '确认上线该页面？'
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
            res = await DL_verifyPage(params)

          if (res.code === 0) {
            this.$message({
              message: res.message,
              type: 'success'
            })
            this.getActivityList()
            this.commonData.expandRowKeys = []
            fullscreenLoading.close()
          } else {
            this.$message({
              message: res.message,
              type: 'warning'
            })
            fullscreenLoading.close()
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
     * @description web转app获取app活动列表
     */
    async getAppActivityList() {
			let res = await DL_getAppActivityList({})

			if (res.code == 0) {
				this.convertToAPPForm.appActivityList = res.data

				this.getAppActivities()
			}
    },

    getAppActivities() {
			let list = []

			this.convertToAPPForm.appActivityList.forEach(function (element) {
				list.push({
					id: element.id,
					name: element.name
				})
			})

			this.convertToAPPForm.appActivities = list
		},

    /**
		 * @description 删除子页面
		 */
		removePage(row, is_lock, activity_create_user, id, activity_id) {
			if (is_lock == 1 && activity_create_user != this.commonData.siteInfo.userName && this.commonData.siteInfo.isSuper != 1) {
				this.$message('该活动已被创建者锁定，需创建者解锁后其他用户才能操作')
			} else {

        this.$confirm('确认删除该页面?', '提示', {
          confirmButtonText: '确定',
          cancelButtonText: '取消',
          type: 'warning'
				})
				.then(async () => {
					let params = {
							id: id
						},
						res = await DL_deletePage(params)

					this.getPages(activity_id)
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
		 * @description 编辑子页面
		 */
		editPage(row, is_lock, activity_create_user, langList, placeList) {
			let _this = this
			if (is_lock == 1 && activity_create_user != this.commonData.siteInfo.userName && this.commonData.siteInfo.isSuper != 1) {
				this.$message('该活动已被创建者锁定，需创建者解锁后其他用户才能操作')
			} else {
				this.commonData.currentLanguage = this.commonData.langList[0].key
				this.commonData.firstLanguage = this.commonData.langList[0].key
				this.commonData.tplId = ''

				let lang = this.commonData.currentLanguage
				this.commonData.currentPageRow = row

				// 主活动语言
				this.commonData.langList = langList

				// 主活动端口
				this.commonData.pagePlaces = placeList

				let data = {}
				langList.forEach(function (element) {
					data[element.key] = {
						title: '',
						url_name: '',
						seo_title: '',
						keywords: '',
						description: '',
						statistics_code: '',
						web_end_url: '',
						redirect_url: '',
						share_place: [],
						share_image: '',
						share_title: '',
						share_desc: '',
						share_link: ''
					}
				})

				// k - pc, wap
				// key - en, es, ...
				let group_languages = this.commonData.currentPageRow.group_languages
				for (let k in group_languages) {
					for (let key in group_languages[k]) {
						data[key].title = group_languages[k][key].title
						data[key].url_name = group_languages[k][key].url_name
						data[key].seo_title = group_languages[k][key].seo_title
						data[key].keywords = group_languages[k][key].keywords
						data[key].description = group_languages[k][key].description
						data[key].statistics_code = group_languages[k][key].statistics_code
						data[key].redirect_url = group_languages[k][key].redirect_url
						if (k == 'web') {
							data[key].web_end_url = group_languages[k][key].end_url
            }
						let share_place = group_languages[k][key].share_place
						share_place = share_place.substr(1, share_place.length - 2).replace(/\"/g, '')
						data[key].share_place = share_place.split(',')

						data[key].share_image = group_languages[k][key].share_image
						data[key].share_title = group_languages[k][key].share_title
						data[key].share_desc = group_languages[k][key].share_desc
						data[key].share_link = group_languages[k][key].share_link
					}
				}

				this.pageForm.id = this.commonData.currentPageRow.id
				this.pageForm.need_redirect = false
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

				this.pageForm.title = this.pageForm.data[lang].title
				this.pageForm.titleCount = this.pageForm.data[lang].title.split('').length

				this.pageForm.keywords = this.pageForm.data[lang].keywords
				this.pageForm.redirect_url = this.pageForm.data[lang].redirect_url

				this.pageForm.url_name = this.pageForm.data[lang].url_name
				this.pageForm.urlCount = this.pageForm.data[lang].url_name.split('').length

				this.pageForm.description = this.pageForm.data[lang].description
				this.pageForm.pageIntroductionCount = this.pageForm.data[lang].description.split('').length

				this.pageForm.statistics_code = this.pageForm.data[lang].statistics_code
				this.pageForm.codeCount = this.pageForm.data[lang].statistics_code.split('').length

				this.pageForm.activity_id = this.commonData.currentPageRow.activity_id
				this.pageForm.refresh_time = this.commonData.currentPageRow.refresh_time

				this.pageForm.web_end_url = this.pageForm.data[lang].web_end_url

				this.pageForm.seo_title = this.pageForm.data[lang].seo_title

				this.pageForm.share_place = this.pageForm.data[lang].share_place
				this.commonData.share_entrance = this.pageForm.data[lang].share_place
				this.pageForm.share_image = this.pageForm.data[lang].share_image
				this.pageForm.share_title = this.pageForm.data[lang].share_title
				this.pageForm.share_desc = this.pageForm.data[lang].share_desc
				this.pageForm.share_link = this.pageForm.data[lang].share_link

				this.commonData.urlName = this.commonData.currentPageRow.url_name
				this.commonData.refreshTime = this.commonData.currentPageRow.refresh_time
				this.commonData.tplId = this.commonData.currentPageRow.tplId
				this.publicPageForm.end_time = this.commonData.currentPageRow.end_time == '0' ? '' : this.commonData.currentPageRow.end_time * 1000

				this.pageForm.dialogTitle = '编辑子页面'

				let platformKeys = Object.keys(row.group_info.platform_list)

				if (platformKeys.indexOf('web') != -1) {
					this.pageForm.need_redirect = true
				}

				this.pageForm.dialogPageVisible = true
			}
		}
  }
}
</script>

