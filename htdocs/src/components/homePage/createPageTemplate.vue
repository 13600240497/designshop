<template>
    <el-dialog title="页面模板" :visible.sync="modelInfo.visible" append-to-body class="geshop-template-model">
        <div class="geshop-template-model-title">
            <span class="icon-geshop-backward"></span>
            <span class="geshop-template-model-word">选择页面模板</span>
        </div>
        <el-row>
            <el-tabs type="border-card" class="model-dialog" @tab-click="tplTabClick" v-model="modelInfo.tabActive" v-loading="tplInfo.loading">
                <el-tab-pane label="我的模板" name="2">
                    <div class="model-box" v-for="(item,index) in oHomeData.pageTemplateList" :key="index">
                        <el-radio name="modelSelect" :label="item.id" v-model="modelInfo.modelSelect" v-if="item.create_user == siteInfo.userName">
                            <div class="model-item">
                                <img :src="item.pic?item.pic:'/resources/images/default/picture.png'">
                                <span>{{item.name}}</span>
                                <div class="icon-geshop-search" @click="seeTemplate(item.pid,item.lang.key,item.id,item.site_code)"></div>
                            </div>
                        </el-radio>
                    </div>
                </el-tab-pane>
                <el-tab-pane label="共用模板" name="1">
                    <div class="model-box" v-for="(item,index) in oHomeData.pageTemplateList" :key="index">
                        <el-radio name="modelSelect" :label="item.id" v-model="modelInfo.modelSelect" v-if="item.create_user != siteInfo.userName && item.tpl_type == 1">
                            <div class="model-item">
                                <img :src="item.pic?item.pic:'/resources/images/default/picture.png'">
                                <span>{{item.name}}</span>
                                <div class="icon-geshop-search" @click="seeTemplate(item.pid,item.lang.key,item.id,item.site_code)"></div>
                            </div>
                        </el-radio>
                    </div>
                </el-tab-pane>
                <el-row v-if="oHomeData.pageTemplateList.length == 0|| oHomeData.pageTemplateList.length>0 && (modelInfo.tabActive == '2' && modelInfo.tempLength1 == 0 || modelInfo.tabActive == '1' && modelInfo.tempLength2 == 0)">
                    <el-col :span="24" style="text-align: center;margin: 20px 0;">{{ oHomeData.pageTemplateListWarn }}</el-col>
                </el-row>
            </el-tabs>
        </el-row>
        <div slot="footer" class="dialog-footer">
            <el-button size="small" @click="handleCancelSelectedModel">取消</el-button>
            <el-button type="primary" size="small" @click="handleSureModel">确定</el-button>
        </div>
    </el-dialog>
</template>

<script>
    export default {
        name: 'createPageTemplate',
        props: {
            oHomeData: {
                type: Object
            },
            getIndexList: {
                type: Function
            },
            oCommonData: {
                type: Object
            },
            // 页面模板
            modelInfo: {
                type: Object
            },
            // 选择模板信息
            tplInfo: {
                type: Object
            },
            viewModel: {
                type: Object
            },
            siteInfo: {
                type: Object
            }
        },
        methods: {
            tplTabClick () {
                this.$emit('tplTabClick')
            },

            // 查看模板大图
            seeTemplate (pid, lang, id, site_code) {
                if (!pid) {
                    this.$message('活动pid不存在')
                    return false
                }
                this.viewModel.visible = true
                this.oHomeData.pageLoading = true

                let langDefualt = lang || 'en'

                let site_group_code = site_code.split('-')[0] || ''
                if (site_group_code) {
                    switch (String(site_group_code)) {
                        case 'dl':
                            site_group_code = 'dl/'
                            break
                        default:
                            site_group_code = ''
                            break
                    }
                }

                this.viewModel.src = '/home/' + site_group_code + 'page-tpl/preview?pid=' + pid + '&lang=' + langDefualt + '&id=' + id + '&site_code=' + site_code + ''
                let sideType = site_code.split('-')[1], sideWidth
                sideWidth = '100%'

                this.viewModel.sideType = sideType
                this.viewModel.sideWidth = sideWidth

                this.oHomeData.pageLoading = false
            },

            handleCancelSelectedModel () {
                this.modelInfo.visible = false
            },

            handleSureModel () {
                let _this = this
                let currentPlace = this.oHomeData.templateSelectPlace
                let selected = this.modelInfo.modelSelect
                let currentLanguage = this.oHomeData.currentLanguage

                // this.indexForm.tpl_id = selected;
                if (currentPlace == 'pc' || currentPlace == 'web') {
                    this.oHomeData.indexForm.tpl_id = selected
                } else if (currentPlace == 'wap') {
                    this.oHomeData.indexForm.m_tpl_id = selected
                }

                this.modelInfo.visible = false

                this.oHomeData.indexForm.data[currentLanguage].tpl_id = this.oHomeData.indexForm.tpl_id
                this.oHomeData.indexForm.data[currentLanguage].m_tpl_id = this.oHomeData.indexForm.m_tpl_id

                // let template = "未选中模板";
                this.oHomeData.pageTemplateList.forEach(function (element) {
                    if (element.id == selected) {
                        if (currentPlace == 'pc' || currentPlace == 'web') {
                            _this.oHomeData.indexForm.tpl_name = element.name
                        } else if (currentPlace == 'wap') {
                            _this.oHomeData.indexForm.m_tpl_name = element.name
                        }
                        // template = element.name;
                    }
                })
                // this.indexForm.data[this.currentLanguage].tpl_id = selected;
                // this.currentTemplate = template;
                this.oHomeData.indexForm.data[currentLanguage].tpl_name = this.oHomeData.indexForm.tpl_name
                this.oHomeData.indexForm.data[currentLanguage].m_tpl_name = this.oHomeData.indexForm.m_tpl_name
            }
        }
    }
</script>

<style scoped>

</style>
