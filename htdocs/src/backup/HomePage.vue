<template>
	<site-layout @publicReady="publicReady">
		<!--首页头部-->
		<home-page-header
			:oCommonData="oCommonData"
			:addHomeIndex="addIndex"
			@doSearch="doSearch"
			:refreshHomePage="refreshHomePage"></home-page-header>

		<el-row>
			<el-col :span="24" class="geshop-index-lists">
				<!--tab页切换-->
				<home-page-tabs
					:homeTabClick="handleHomeTabClick"
					:oCommonData="oCommonData"></home-page-tabs>

				<!--列表-->
				<home-page-list
					:oCommonData="oCommonData"
					:oHomeData="oHomeData"
					:modelInfo="modelInfo"
					:siteInfo="siteInfo"
					:chouseRelaseType="chouseRelaseType"
					:getIndexList="getIndexList"
					:oViewAccessLinks="oViewAccessLinks"
					:oHomePagePreview="oHomePagePreview"
					:oViewQrCode="oViewQrCode"
					:oListData="oListData"></home-page-list>
			</el-col>
		</el-row>

		<!--分页-->
		<home-page-pagination
				:oListData="oListData"
			  	:getIndexList="getIndexList"></home-page-pagination>

		<!--查看访问链接-->
		<home-view-access-links :oViewAccessLinks="oViewAccessLinks"></home-view-access-links>

		<!--首页预览-->
		<home-page-preview :oHomePagePreview="oHomePagePreview"></home-page-preview>

		<!--查看二维码-->
		<view-qr-code :oViewQrCode="oViewQrCode"></view-qr-code>

		<!--新增/修改首页-->
		<add-and-edit-home-page
				:siteInfo="siteInfo"
				:oCommonData="oCommonData"
				:oHomeData="oHomeData"
				:getIndexList="getIndexList"
				:modelInfo="modelInfo"
				:tplInfo="tplInfo"
				@handleModelTemp="handleModelTempSelect"
				:pageRules="pageRules"></add-and-edit-home-page>

		<!--页面模板-->
		<create-page-template
				:getIndexList="getIndexList"
				:modelInfo="modelInfo"
				:tplInfo="tplInfo"
				:oHomeData="oHomeData"
				:viewModel="viewModelData"
                :siteInfo="siteInfo"
				@tplTabClick="tplTabClick"
				:oCommonData="oCommonData"></create-page-template>

		<!--查看模板-->
		<view-model
				:getIndexList="getIndexList"
				:oHomeData="oHomeData"
				:viewModel="viewModelData"
				:oCommonData="oCommonData"></view-model>

		<!--设为首页-->
		<set-home-page-type
				:getIndexList="getIndexList"
				:viewModel="viewModelData"
				:chouseRelaseType="chouseRelaseType"
				:oCommonData="oCommonData"></set-home-page-type>

	</site-layout>
</template>

<script>
import siteLayout from './layouts/Layout.vue'
import homePageHeader from './homePage/homePageHeader.vue'
import homePageTabs from './homePage/homePageTabs.vue'
import homePageList from './homePage/homePageList.vue'
import homePagePagination from './homePage/homePagePagination.vue'
import homeViewAccessLinks from './homePage/homeViewAccessLinks.vue'
import homePagePreview from './homePage/homePagePreview.vue'
import viewQrCode from './homePage/viewQrCode.vue'
import addAndEditHomePage from './homePage/addAndEditHomePage.vue'
import createPageTemplate from './homePage/createPageTemplate.vue'
import setHomePageType from './homePage/setHomePageType.vue'
import viewModel from './homePage/viewModel.vue'

import {
	indexList,
	getPageTemplateList,
	refreshHome,
	getLangKeyList
} from '../plugin/api'
import bus from '../store/bus-index.js'
import { getCookie } from '../plugin/mUtils'
import '../../resources/stylesheets/homePage.css'
import '../../resources/fonts/svg-fonts/style.css'

export default {
	components: {
	    siteLayout,
		homePageHeader,
        homePageTabs,
        homePageList,
		homePagePagination,
        homeViewAccessLinks,
        homePagePreview,
        viewQrCode,
        addAndEditHomePage,
        createPageTemplate,
        setHomePageType,
        viewModel
	},
	data () {
		return {
			searchWord: '',
			options: {
				pageNo: 1,
				pageSize: 10
			},
			langIndexEn: '0',
            siteInfo: {},
            permissions: [],

			// 公共字段
			oCommonData: {
                homeTabName: 'pc',
                places: [], // tab
                langList: [{ code: '', name: '', key: '' }], //首页所支持的语言
                supportLangs: [],
                site_code: ''
			},

			// 新增修改首页
			oHomeData: {
                dialogAddVisible: false, //默认不弹出新增首页的弹窗
                dialogName: '',
                currentLanguage: 'en',
                titleCount: 0,
                descriptionCount: 0,
                pc_status: true,
                m_status: false,
                SEOTitleCount: 0,
                SEOKeywordsCount: 0,
                SEODescriptionCount:0,
                submitLoading: false,
                pageLoading: false,
                indexForm: {
                    id: '0',
                    title: '',
                    seo_title: '',
                    keywords: '',
                    tpl_id: '0',
                    tpl_name: '未选中模板',
                    m_tpl_id: '0',
                    m_tpl_name: '未选中模板',
                    description: '',
                    data: {},
                    place: ['pc']
                },
                indexFormPlace: ['pc'],
                templateSelectPlace: 'pc',
                getTmpListValue: 'pc',
                getTmpListStatus: false,
                pageTemplateList: [],
                pageTemplateListWarn: '当前没有可用模板',
			},

            // 首页规则
            pageRules: {
                title: [
                    { required: true, message: '请输入名称', trigger: 'blur' },
                    { max: 100, message: '长度不能超过100个字符', trigger: 'blur' }
                ],
                place: [
                    { required: true, message: '请至少选择一个应用端口', trigger: 'change' }
                ],
                seo_title: [
                    { required: true, message: '请输入SEO标题', trigger: 'blur' }
                ],
                description: [
                    { max: 200, message: '长度在1-200个字符之间', trigger: 'blur' }
                ]
            },

			// 列表
			oListData: {
                homeList: [],
                total: 0, //默认列表的总页数为0
                pageSize: 10, // 页码数
                currentPage: 1, // 当前页

			},

			// 查看访问链接
			oViewAccessLinks: {
                pageLinks: [],
                tips: '',
                dialogLinksVisible: false,
                urlID: ''
			},

			// 首页预览
			oHomePagePreview: {
                dialogPreviewLinksVisible: false,
                previewLinks: []
			},

			// 查看二维码
			oViewQrCode: {
                isDetailActive: false,
                currentActivityRow: {
                    pageLanguages: [{ title: '' }]
                }
			},
			// 页面模板
            modelInfo: {
                visible: false,
                tabActive: '2',
                modelSelect: '0',
                tempLength1: 0,
                tempLength2: 0,
                currentTemplate: '未选中模板'
            },

            // 设为首页的类型：1为A，2为B
            chouseRelaseType: {
                visible: false,
                indexType: '1',
                radioStatus: false,
                abLoading: false,
                langArr: [],
                indexPageRecord: {} // 当前点击操作的记录
            },

            tplInfo: {
                pageNo: 1,
                pageSize: 100,
                loading: false
            },

			// 查看模板大图
            viewModelData: {
                visible: false,
                html: '',
                sideType: 'pc',
                sideWidth: '100%',
                src: ''
            }
		}
	},
	created: function () {
        let site_group_code = getCookie('site_group_code')
        this.oCommonData.site_code = site_group_code ? site_group_code: ''

        if (site_group_code && site_group_code == 'dl') {
            this.oCommonData.homeTabName = 'web'
            this.oHomeData.indexForm.place = ['web']
            this.oHomeData.indexFormPlace = ['web']
        }
		bus.$on('giveData', data => {
			this.siteInfo = data
		})
	},
	methods: {
		async getIndexList () {
			let params = {
					pageNo: this.oListData.currentPage,
					pageSize: this.oListData.pageSize,
					keywords: this.searchWord,
					site_code: getCookie('site_group_code') + '-' + this.oCommonData.homeTabName
				},
				res = await indexList(params),
				list = []

			if (res.code == 0) {
                list = res.data.list

                if (res.data.topPage.length > 0) {
                    list.unshift(res.data.topPage[0])
                }

                list.map(item => {
                    item.pageLanguages.map((content, index) => {
                        if (content.lang == 'en') {
                            item.pageLanguages.splice(index, 1)
                            item.pageLanguages.unshift(content)
                        }
                    })
                })

                let length = list.length
                let index

                for (index = 0; index < length; index++) {
                    if (list[index].is_lock == 1) {
                        list[index].lock_status = false
                    } else if (list[index].is_lock == 2) {
                        list[index].lock_status = true
                    }
                }

                this.oListData.homeList = res.data.list

                // 处理返回语言列表数据结构
                let langList = res.data.langList
                let resArr = []
                langList.forEach(item => {
                    let resObject = {}
                    resObject['key'] = item.key
                    resObject['code'] = item.name.code
                    resObject['name'] = item.name.name
                    resArr.push(resObject)
                })
                this.oCommonData.langList = resArr

                this.oListData.total = parseInt(res.data.total)
            }
		},
		/**
		 * 获取所有语言列表
		 */
		async getSupportLangs () {
			let res = await getLangKeyList()

			let supportLangArrs = []
			for (var key in res.data) {
				res.data[key].forEach(item => {
					// delete item.url
					supportLangArrs.push(item)
				})
			}
			supportLangArrs = JSON.parse(JSON.stringify(supportLangArrs))
			supportLangArrs = this.unique(supportLangArrs, 'name')

			this.oCommonData.supportLangs = supportLangArrs
		},
		/**
		 * 语言列表去重
		 */
		unique (arr, key) {
			var n = [arr[0]]
			for (var i = 1; i < arr.length; i++) {
				if (key === undefined) {
					if (n.indexOf(arr[i]) == -1) n.push(arr[i])
				} else {
					inner: {
						var has = false
						for (var j = 0; j < n.length; j++) {
							if (arr[i][key] == n[j][key]) {
								has = true
								break inner
							}
						}
					}
					if (!has) {
						n.push(arr[i])
					}
				}
			}
			return n
		},
		publicReady () {
			this.getIndexList()
			this.getSupportLangs()
			this.permissions = JSON.parse(
				localStorage.getItem('actionPermissions')
			).data
			bus.$on('giveData', data => {
				this.siteInfo = data
			})
			// 设置当前站点信息
			let places = JSON.parse(localStorage.currentSites).sites
			delete places.app
			this.oCommonData.places = places
		},

		// PC，M，APP切换
		handleHomeTabClick () {
			this.oListData.currentPage = 1
			this.options.pageNo = 1
			this.getIndexList()
		},

		doSearch (data) {
			this.oListData.currentPage = 1
			this.searchWord = data;
			this.getIndexList()
		},

		/**
		 * 新增首页
		 * **/
		addIndex () {
			this.oHomeData.indexForm.id = ''
			this.oHomeData.titleCount = 0
			this.oHomeData.descriptionCount = 0
			this.oHomeData.dialogName = '新增首页'

			let data = {}
			// this.oHomeData.indexForm.place = ['pc'] // 新增首页默认选中pc
			this.oHomeData.indexForm.keywords = ''
			this.oHomeData.pc_status = true
			this.oHomeData.m_status = false
			// 所有语言
			this.oCommonData.supportLangs.forEach(function (element) {
				data[element.key] = {
					title: '',
					tpl_id: '0',
					tpl_name: '未选中模板',
					m_tpl_id: '0',
					m_tpl_name: '未选中模板',
					pc_status: true,
					m_status: false,
					seo_title: '',
					keywords: '',
					description: ''
				}
			})
			this.oHomeData.indexForm.data = data
			this.oHomeData.dialogAddVisible = true
		},

		// confirm 弹窗
		confirm (message, callback) {
			this.$confirm(message, '提示', {
				confirmButtonText: '确定',
				cancelButtonText: '取消',
				type: 'warning'
			})
				.then(() => {
					if (typeof callback == 'function') {
						callback(this)
					}
				})
				.catch(() => {
					this.$message({
						type: 'info',
						message: '已取消操作!'
					})
				})
		},

        tplTabClick () {
            let _self = this
            this.tplInfo.pageNo = 1
            let contContainer = document.getElementById('pane-2').parentNode
            contContainer.removeEventListener('scroll', _self.handlePanelScroll)

            if (this.oHomeData.templateSelectPlace == 'pc') {
                this.oHomeData.getTmpListValue = 'pc'
            } else if (this.oHomeData.templateSelectPlace == 'wap') {
                this.oHomeData.getTmpListValue = 'wap'
            } else if (this.oHomeData.templateSelectPlace == 'web') {
                this.oHomeData.getTmpListValue = 'web'
            }

            this.oHomeData.getTmpListStatus = true

            this.getPageTemplates('scroll');
        },

        /**
         * 选择模板
         *
         * **/
        handleModelTempSelect (val) {
            let _this = this

			// 针对D网，模板为web
			if (this.oCommonData.site_code == 'dl') {
                val = 'web'
			}
            // 新增子页面模板选择区分来自PC、M或APP
            this.oHomeData.templateSelectPlace = val

            // PC、M、APP页面模板数据获取
            if (this.oHomeData.templateSelectPlace == 'pc') {
                this.oHomeData.getTmpListValue = 'pc'
            } else if (this.oHomeData.templateSelectPlace == 'wap') {
                this.oHomeData.getTmpListValue = 'wap'
            } else if (this.oHomeData.templateSelectPlace == 'web') {
                this.oHomeData.getTmpListValue = 'web'
            }
            this.oHomeData.getTmpListStatus = true

            this.getPageTemplates()

            this.modelInfo.visible = true
            this.modelInfo.modelSelect = this.oHomeData.indexForm.tpl_id
            setTimeout(function () {
                _this.handlePanelScroll()
            }, 100)
        },

        async getPageTemplates (scrollType) {
            this.tplInfo.loading = true
            let _this = this
            let pageNo = scrollType == 'scroll' ? this.tplInfo.pageNo : 1
            let type = this.modelInfo.tabActive == '1' ? 1 : 0

            let params = {
                place: 2,
                type: type,
                pageNo: pageNo,
                pageSize: this.tplInfo.pageSize
            }

            // 如果是选择模板
            if (this.oHomeData.getTmpListStatus) {
                params.site_code = getCookie('site_group_code') + '-' + this.oHomeData.getTmpListValue
            } else {
                params.site_code = getCookie('site_group_code') + '-' + this.oCommonData.homeTabName
            }

            let res = await getPageTemplateList(params)

            let data = res.data.list
            this.tplInfo.totalCount = res.data.totalCount
            this.tplInfo.maxPageNo = Math.ceil(
                res.data.totalCount / this.tplInfo.pageSize
            )
            if (scrollType == 'scroll' && pageNo > 1) {
                let oldList = this.oHomeData.pageTemplateList
                this.oHomeData.pageTemplateList = oldList.concat(data)
            } else {
                this.oHomeData.pageTemplateList = data
            }

            this.checkCurrentPageForm()

            setTimeout(function () {
                _this.tplInfo.loading = false
            }, 200)
        },

        /* 校验当前模板列表 */
        checkCurrentPageForm () {
            let pageTemplateList = this.oHomeData.pageTemplateList,
                tabActive = this.modelInfo.tabActive,
                siteInfo = this.siteInfo,
                tempLength1 = 0,
                tempLength2 = 0

            let pageTemplateListWarn =
                tabActive == '2' ? '您还没有自己的模板' : '暂无页面模板供使用'
            this.oHomeData.pageTemplateListWarn = pageTemplateListWarn

            pageTemplateList.forEach(function (item) {
                if (item.create_user == siteInfo.userName) {
                    tempLength1 += 1
                } else if (
                    item.create_user != siteInfo.userName &&
                    item.tpl_type == 1
                ) {
                    tempLength2 += 1
                }
            })
            this.modelInfo.tempLength1 = tempLength1
            this.modelInfo.tempLength2 = tempLength2
        },

        handlePanelScroll () {
            let panelCont0 = document.getElementById('pane-2').parentNode,
                _this = this,
                timer
            panelCont0.addEventListener('scroll', function () {
                if (timer) clearTimeout(timer)
                timer = setTimeout(function () {
                    if (panelCont0.clientHeight + panelCont0.scrollTop == panelCont0.scrollHeight) {
                        let tempNum = _this.tplInfo.pageNo + 1
                        if (tempNum <= _this.tplInfo.maxPageNo) {
                            _this.tplInfo.pageNo = tempNum

                            if (_this.oHomeData.templateSelectPlace == 'pc') {
                                _this.oHomeData.getTmpListValue = 'pc'
                            } else if (_this.oHomeData.templateSelectPlace == 'wap') {
                                _this.oHomeData.getTmpListValue = 'wap'
                            }

                            _this.oHomeData.getTmpListStatus = true

                            _this.getPageTemplates('scroll')
                        }
                    }
                }, 600)
            })
        },

		async refreshHomePage () {
			if (this.siteInfo.isSuper != 1) {
				this.$message('该操作只有超级管理员才有权限!')
			} else {
				this.confirm(
					'一键头尾刷新中，请在【系统日志】-任务日志里对刷新详情进行查看，是否前往查看？',
					async vm => {
						let params = {
							// site_code: getCookie("SITECODE")
								site_code: getCookie('site_group_code') + '-' + this.oListData.homeTabName
							},
							res = await refreshHome(params)

						if (res.code == 0) {
							window.location.href = '/base/task-log/index'
						} else {
							vm.$message.error(res.message)
						}
					}
				)
			}
		}
	}
}
</script>
<style lang="less" scoped>
.model-item img {
  max-width: 100%;
  width: 150px;
  height: 150px;
  display: block;
  margin: 10px auto;
}

.showChoseIndexType{
	.dialog-footer,.items,.tips_msg{
		text-align: center;
	}
	.items{
		margin-bottom:20px;
	}
	.channel {
		.title {
			margin-top: 0;
			margin-bottom: 15px;
		}
		.el-checkbox {
			margin-right: 30px;
			margin-left: 0;
		}
	}
}
</style>
<style lang="less">
.model-box {
  width: 50%;
  float: left;
  text-align: center;
  .el-radio {
    position: relative;
    max-width: 100%;
  }
  .el-radio__input {
    position: absolute;
    right: 20px;
    top: 36px;
  }
}
.model-dialog .el-tabs__content {
  height: 400px;
  overflow-y: scroll;
}
.geshop-index-lists .has-gutter th {
  background-color: #f4f4f4 !important;
  padding: 8px 0px !important;
}
.geshop-index-lists .el-table__header-wrapper {
  height: 40px !important;
}
/* .geshop-new-index .el-dialog {
  height: 600px;
}*/
</style>
