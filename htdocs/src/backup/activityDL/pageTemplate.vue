<template>
  <div>
    <el-dialog title="页面模板" :visible.sync="modelInfo.visible" @close="handleModelClose" append-to-body class="geshop-page-template">
      <div class="geshop-page-template-title">
        <span class="icon-geshop-backward"></span>
        <span class="geshop-page-template-word">选择页面模板</span>
      </div>
      <el-row>
        <el-tabs type="border-card" class="model-dialog" @tab-click="tplTabClick" v-model="modelInfo.tabActive" v-loading="tplInfo.loading">
          <el-tab-pane label="我的模板" name="2" ref="tpl0">
            <div class="model-box" v-for="(item,index) in pageTemplateList" :key="index">
              <el-radio name="modelSelect" :label="item.id" v-model="modelInfo.modelSelect" v-if="item.create_user == commonData.siteInfo.userName" @change="updateModelSelect">
                <div class="model-item">
                  <span>{{item.name}}</span>
                  <img :src="item.pic?item.pic:'/resources/images/default/picture.png'">
                  <div class="icon-geshop-search" @click="seeTemplate(item.pid,item.lang.key,item.id,item.site_code)"></div>
                </div>
              </el-radio>
            </div>
          </el-tab-pane>
          <el-tab-pane label="共用模板" name="1" ref="tpl1">
            <div class="model-box" v-for="(item,index) in pageTemplateList" :key="index">
              <el-radio name="modelSelect" :label="item.id" v-model="modelInfo.modelSelect" v-if="item.create_user != commonData.siteInfo.userName && item.tpl_type == 1" @change="updateModelSelect">
                <div class="model-item">
                  <span>{{item.name}}</span>
                  <img :src="item.pic?item.pic:'/resources/images/default/picture.png'">
                  <div class="icon-geshop-search" @click="seeTemplate(item.pid,item.lang.key,item.id,item.site_code)"></div>
                </div>
              </el-radio>
            </div>
          </el-tab-pane>
          <el-row v-if="pageTemplateList.length == 0|| pageTemplateList.length>0 && (modelInfo.tabActive == '2' && modelInfo.tempLength1 == 0 || modelInfo.tabActive == '1' && modelInfo.tempLength2 == 0)">
            <el-col :span="24" style="text-align: center;margin: 20px 0;">{{ pageTemplateListWarn }}</el-col>
          </el-row>
        </el-tabs>
      </el-row>
      <div slot="footer" class="dialog-footer">
        <el-button @click="handleCancelSelectedModel">取 消</el-button>
        <el-button type="primary" @click="handleSureModel">确定</el-button>
      </div>
    </el-dialog>
    <el-dialog title="页面模板" class="geshop-template-model" :visible.sync="viewModel.visible" @close="viewModelClose" :width="viewModel.sideWidth">
      <el-row v-loading="pageLoading">
        <el-col class="imgPreview text-center" style="height:100%;">
          <iframe frameborder="0" :src="viewModel.src" class="iframePreview" style="width:100%;height:100%;"></iframe>
        </el-col>
      </el-row>
    </el-dialog>
  </div>
</template>

<script>
import { getCookie } from '../../plugin/mUtils'
import { DL_getPageTemplateList } from '../../plugin/api'

export default {
  /**
   * @desc 组件props
   * @param { Object } commonData - 共用数据
   * @param { Object } pageForm - 子页面表单数据
   */
  props: ['commonData', 'pageForm'],
  name: 'pageTemplateComponent',
  data () {
    return {
      modelInfo: {
				visible: false,
				tabActive: '2',
				modelSelect: '0',
				tempLength1: 0,
        tempLength2: 0,
        templateSelectPlace: 'web',
				getTmpListValue: 'web',
				getTmpListStatus: false,
      },
      tplInfo: {
				pageNo: 1,
				pageSize: 100,
				loading: false
      },
      pageTemplateList: [],
      pageTemplateList1: [],
      viewModel: {
				visible: false,
				html: '',
				sideType: 'web',
				sideWidth: '100%',
				src: ''
      },
      pageLoading: false,
      pageTemplateListWarn: '当前没有可用模板',
    }
  },
  created () {
    
  },
  methods: {

    /**
     * @desc 关闭弹窗
     */
    handleModelClose() {
			this.tplInfo.pageNo = 1
    },

    /**
     * @description 取消模板选择
     */
    handleCancelSelectedModel() {
			this.modelInfo.visible = false
    },

    viewModelClose() {
			this.viewModel.visible = false
			this.viewModel.html = ''
			this.viewModel.src = ''
    },
    
    /**
     * @desc 新增子页面模板选择
     * @param { String } place - 端口
     */
		handleModelTempSelect(place) {

			// 新增子页面模板选择区分来自PC、M或APP
			this.modelInfo.templateSelectPlace = place

			// PC、M、APP页面模板数据获取
			if (this.modelInfo.templateSelectPlace == 'web') {
				this.modelInfo.getTmpListValue = 'web'
			} else if (this.modelInfo.templateSelectPlace == 'app') {
				this.modelInfo.getTmpListValue = 'app'
			}
			this.modelInfo.getTmpListStatus = true

			this.getPageTemplates()

			let _this = this
			this.modelInfo.visible = true
			this.modelInfo.modelSelect = this.pageForm.tpl_id

			setTimeout(function () {
				_this.handlePanelScroll()
			}, 100)

		},
    
    /**
     * @description 确定选择模板
     */
    handleSureModel() {
			let currentPlace = this.modelInfo.templateSelectPlace

			let selected = this.modelInfo.modelSelect

			if (currentPlace == 'web') {
				this.pageForm.web_tpl_id = selected
			} else if (currentPlace == 'app') {
				this.pageForm.app_tpl_id = selected
			}

			this.modelInfo.visible = false

			this.pageForm.data[this.commonData.currentLanguage].tpl_id = this.pageForm.tpl_id
			this.pageForm.data[this.commonData.currentLanguage].web_tpl_id = this.pageForm.web_tpl_id
			this.pageForm.data[this.commonData.currentLanguage].app_tpl_id = this.pageForm.app_tpl_id

			let _this = this

			this.pageTemplateList.forEach(function (element) {
				if (element.id == selected) {
					if (currentPlace == 'web') {
						_this.pageForm.web_tpl_name = element.name
					} else if (currentPlace == 'app') {
						_this.pageForm.app_tpl_name = element.name
					}
				}
			})

			this.pageForm.data[this.commonData.currentLanguage].tpl_name = this.pageForm.tpl_name
			this.pageForm.data[this.commonData.currentLanguage].web_tpl_name = this.pageForm.web_tpl_name
			this.pageForm.data[this.commonData.currentLanguage].app_tpl_name = this.pageForm.app_tpl_name

		},

    /**
     * @desc 查看模板大图
     */
		async seeTemplate(pid, lang, id, site_code) {
			if (!pid) {
				this.$message('活动pid不存在')
				return false
			}
			this.viewModel.visible = true
			this.pageLoading = true
			let langDefualt = lang || 'en'
			this.viewModel.src = '/activity/dl/page-tpl/preview?pid=' + pid + '&lang=' + langDefualt + '&id=' + id + '&site_code=' + site_code + ''

			let sideType = site_code.split('-')[1],
				sideWidth
			
			if (sideType != 'web') {
				sideWidth = '400px'
			} else {
				sideWidth = '100%'
			}

			this.viewModel.sideType = sideType
			this.viewModel.sideWidth = sideWidth
			this.pageLoading = false
    },
    
    /**
     * @desc 校验当前模板列表
     */
		checkCurrentPageForm() {
			let pageTemplateList = this.pageTemplateList,
				tabActive = this.modelInfo.tabActive,
				siteInfo = this.commonData.siteInfo,
				tempLength1 = 0,
				tempLength2 = 0

			let pageTemplateListWarn = tabActive == '2' ? '您还没有自己的模板' : '暂无页面模板供使用'
			this.pageTemplateListWarn = pageTemplateListWarn

			pageTemplateList.forEach(function (item, index) {
				if (item.create_user == siteInfo.userName) {
					tempLength1 += 1
				} else if (item.create_user != siteInfo.userName && item.tpl_type == 1) {
					tempLength2 += 1
				}
			})
			this.modelInfo.tempLength1 = tempLength1
			this.modelInfo.tempLength2 = tempLength2
		},

    updateModelSelect() {},

    tplTabClick() {
			this.tplInfo.pageNo = 1
			let contContainer = document.getElementById('pane-2').parentNode
			contContainer.removeEventListener('scroll', this.handlePanelScroll)

			if (this.modelInfo.templateSelectPlace == 'web') {
				this.modelInfo.getTmpListValue = 'web'
			} else if (this.modelInfo.templateSelectPlace == 'app') {
				this.modelInfo.getTmpListValue = 'app'
			}
			this.modelInfo.getTmpListStatus = true

			this.getPageTemplates('scroll')
    },
    
    handlePanelScroll() {
			let panelCont0 = document.getElementById('pane-2').parentNode,
				_this = this
			
			let timer
			panelCont0.addEventListener('scroll', function () {
				if (timer) clearTimeout(timer)
				timer = setTimeout(function () {
					if (panelCont0.clientHeight + panelCont0.scrollTop == panelCont0.scrollHeight) {
						var tempNum = _this.tplInfo.pageNo + 1
						if (tempNum <= _this.tplInfo.maxPageNo) {
							_this.tplInfo.pageNo = tempNum

							if (_this.modelInfo.templateSelectPlace == 'web') {
								_this.modelInfo.getTmpListValue = 'web'
							} else if (_this.modelInfo.templateSelectPlace == 'app') {
								_this.modelInfo.getTmpListValue = 'app'
							}
							_this.modelInfo.getTmpListStatus = true

							_this.getPageTemplates('scroll')
						}
					}
				}, 600)
			})
    },
    
    /**
     * @desc 获取模板列表
     * @param { Number } place - 应用环境 1:活动页 2:首页
		 * @param { String } type - 模板类型 1:我的模板 2:共用模板
		 * @param { Number } pageNo - 当前页码
		 * @param { Number } pageSize - 页数
		 * @param { String } site_code - 站点site code
     */
    async getPageTemplates(scrollType) {
			this.tplInfo.loading = true
			let _this = this
			let pageNo = scrollType == 'scroll' ? this.tplInfo.pageNo : 1
			let type = this.modelInfo.tabActive == '1' ? 1 : 0

			let params = {
				place: 1,
				type: type,
				pageNo: pageNo,
				pageSize: this.tplInfo.pageSize,
				lang: this.commonData.currentLanguage
			}

			// 如果是选择模板
			if (this.modelInfo.getTmpListStatus) {
				params.site_code = `${getCookie('site_group_code')}-${this.modelInfo.getTmpListValue}`
			} else {
				params.site_code = `${getCookie('site_group_code')}-${this.commonData.activityTabName}`
			}

			let res = await DL_getPageTemplateList(params)

			let data = res.data.list
			this.tplInfo.totalCount = res.data.totalCount
			this.tplInfo.maxPageNo = Math.ceil(res.data.totalCount / this.tplInfo.pageSize)
			if (scrollType == 'scroll' && pageNo > 1) {
				let oldList = this.pageTemplateList
				this.pageTemplateList = oldList.concat(data)
			} else {
				this.pageTemplateList = data
			}

			this.checkCurrentPageForm()

			setTimeout(function () {
				_this.tplInfo.loading = false
			}, 200)
		}
  }
}
</script>

