<template>
    <el-dialog :title="oHomeData.dialogName" :visible.sync="oHomeData.dialogAddVisible" @close="subDialogClose" class="geshop-new-index">

        <el-tabs type="card" @tab-click="handleTabClick" v-model="oHomeData.currentLanguage">
            <!-- 编辑时显示该首页支持的语言 -->
            <el-tab-pane v-if="oHomeData.indexForm.id != ''" v-for="item in oCommonData.langList"
                         :label="item.name"
                         :name="item.key"
                         :key="item.key"></el-tab-pane>

            <!-- 新增时显示所有语言 -->
            <el-tab-pane v-if="oHomeData.indexForm.id == ''" v-for="item in oCommonData.supportLangs"
                         :label="item.name"
                         :name="item.key"
                         :key="item.key"></el-tab-pane>

        </el-tabs>

        <el-form :rules="pageRules" :model="oHomeData.indexForm" ref='indexForm'>

            <el-form-item label="应用端口" prop="place" v-if="!Boolean(oHomeData.indexForm.id)" class="home-page-place">
                <div>
                    <el-checkbox-group v-model="oHomeData.indexForm.place" @change="handleCheckedPlacesChange">
                        <el-checkbox v-for="(item, key) in oCommonData.places"
                                     :disabled="key == oHomeData.indexForm.site_code"
                                     :label="key"
                                     :name="key"
                                     :key="key">{{ item.platform_name }}</el-checkbox>
                    </el-checkbox-group>
                </div>
            </el-form-item>

            <p style="margin:0 0 5px 0;color:#b7b7b7;" v-if="!Boolean(oHomeData.indexForm.id)">备注：此处的语言为站点的所有语言，如果某端口无对应的语言时，同步时，会自动过滤</p>

            <el-form-item label="名称" prop="title">
                <el-input v-model="oHomeData.indexForm.title" @change="updateIndexTitle" v-on:keyup.native="handleTitleKeyup"></el-input>
                <span class="count-tip-box">{{ oHomeData.titleCount }}/100</span>
            </el-form-item>

            <el-form-item label="页面模板"
                          prop="model"
                          v-if="oHomeData.pc_status && !Boolean(oHomeData.indexForm.id)"
                          style="margin-top:10px" class="new-index-model">

                <el-button type="primary" size="small" @click="handleModelTempSelect('pc')">选择模板</el-button>
                <el-tag type="info">{{ oHomeData.indexForm.tpl_name }}</el-tag>

            </el-form-item>

            <el-form-item label="M端页面模板" prop="model" v-if="oHomeData.m_status && !Boolean(oHomeData.indexForm.id)"
                          class="new-index-model">

                <el-button type="primary" size="small" @click="handleModelTempSelect('wap')">选择模板</el-button>
                <el-tag type="info">{{ oHomeData.indexForm.m_tpl_name }}</el-tag>

            </el-form-item>

            <el-form-item label="SEO标题" prop="seo_title" style="margin-top:5px;">
                <el-input v-model="oHomeData.indexForm.seo_title" placeholder="有利于SEO优化" @change="updateIndexSeoTitle" v-on:keyup.native="handleSEOTitleKeyup"></el-input>
                <span class="count-tip-box">{{ oHomeData.SEOTitleCount }}/200</span>
            </el-form-item>

            <el-form-item label="SEO关键字" prop="keywords" style="margin-top:5px">
                <el-input v-model="oHomeData.indexForm.keywords" placeholder="有利于SEO优化" @change="updateIndexSeoKeywords" v-on:keyup.native="handleSEOKeywordsKeyup"></el-input>
                <span class="count-tip-box">{{ oHomeData.SEOKeywordsCount }}/200</span>
            </el-form-item>

            <el-form-item label="SEO 简介" prop="description" class="new-index-introduction">

                <el-input type="textarea" v-model="oHomeData.indexForm.description" :rows="4" placeholder="有利于SEO优化" @change='updateIndexSeoDescription' v-on:keyup.native="handleSEODescriptionKeyup"></el-input>
                <span class="count-tip-box">{{ oHomeData.SEODescriptionCount }}/200</span>

            </el-form-item>

            <el-form-item class="new-index-btns">
                <el-button @click="resetForm('indexForm')" size="small">取消</el-button>
                <el-button type="primary" @click="submitForm('indexForm')" size="small" :loading="oHomeData.submitLoading">确定</el-button>
            </el-form-item>

        </el-form>
    </el-dialog>
</template>

<script>
    import { getCookie } from '../../plugin/mUtils'
    import { addIndex, editIndex } from '../../plugin/api'
    export default {
        name: 'addAndEditHomePage',
        props: {
            oHomeData: {
                type: Object
            },
            oCommonData: {
                type: Object
            },
            // 规则
            pageRules: {
                type: Object
            },
            getIndexList: {
                type: Function
            },
            // 页面模板
            modelInfo: {
                type: Object
            },
            // 选择模板信息
            tplInfo: {
                type: Object
            },
            siteInfo: {
                type: Object
            }
        },
        methods: {
            async submitForm () {
                this.oHomeData.submitLoading = true

                this.$refs['indexForm'].validate(async valid => {
                    this.oHomeData.submitLoading = false
                    this.indexForm = this.oHomeData.indexForm

                    if (valid) {
                        for (var i in this.indexForm.data) {
                            if (this.indexForm.data[i].title == '') {
                                return this.$message.error(
                                    '请确认所有语言所有必填项已经填写，再提交！'
                                )
                            }
                        }

                        // 编辑
                        if (this.indexForm.id != '') {
                            let params = {
                                page_id: this.indexForm.id,
                                data: JSON.stringify(this.indexForm.data),
                                site_code: getCookie('site_group_code') + '-' + this.oCommonData.homeTabName
                            }
                            let res = await editIndex(params)
                            if (res.code == 0) {
                                this.getIndexList()
                                this.subDialogClose()
                                this.oHomeData.submitLoading = false
                            } else {
                                this.oHomeData.submitLoading = false
                            }
                        }
                        // 新增
                        else {
                            let indexFormObj = {}
                            let jsonObject = {}

                            this.oHomeData.indexFormPlace.forEach((item) => {

                                let param = {
                                    page_id: this.indexForm.id,
                                    list: this.indexForm.data,
                                    // site_code: getCookie('site_group_code') + '-' + this.homeTabName
                                }
                                if (item == 'pc') {
                                    param.site_code = getCookie('site_group_code') + '-pc'
                                } else if (item == 'wap') {
                                    param.site_code = getCookie('site_group_code') + '-wap'
                                } else if (item == 'web') {
                                    param.site_code = getCookie('site_group_code') + '-web'
                                }

                                indexFormObj[item] = JSON.parse(JSON.stringify(param))

                                this.oCommonData.langList.forEach((it) => {
                                    if (item == 'pc') {
                                        delete indexFormObj[item].list[it['key']].m_tpl_id
                                        delete indexFormObj[item].list[it['key']].m_tpl_name
                                        delete indexFormObj[item].list[it['key']].pc_status
                                        delete indexFormObj[item].list[it['key']].m_status
                                        delete indexFormObj[item].list[it['key']].place
                                    } else if (item == 'wap') {
                                        delete indexFormObj[item].list[it['key']].tpl_id
                                        delete indexFormObj[item].list[it['key']].tpl_name
                                        indexFormObj[item].list[it['key']].tpl_name = indexFormObj[item].list[it['key']].m_tpl_name
                                        delete indexFormObj[item].list[it['key']].m_tpl_name
                                        indexFormObj[item].list[it['key']].tpl_id = indexFormObj[item].list[it['key']].m_tpl_id
                                        delete indexFormObj[item].list[it['key']].m_tpl_id
                                        delete indexFormObj[item].list[it['key']].pc_status
                                        delete indexFormObj[item].list[it['key']].m_status
                                        delete indexFormObj[item].list[it['key']].place
                                    }
                                })

                                for (let key in indexFormObj) {
                                    jsonObject[key] = JSON.stringify(indexFormObj[key])
                                }
                            })

                            // let res = await addIndex(params);
                            let res = await addIndex(jsonObject)
                            if (res.code == 0) {
                                this.getIndexList()
                                this.subDialogClose()
                                this.oHomeData.submitLoading = false
                            } else {
                                this.oHomeData.submitLoading = false
                            }
                        }


                    }
                })
            },

            /***
             * 关闭首页弹窗
             * **/
            subDialogClose () {
                this.resetForm('indexForm')
                this.oHomeData.indexForm.id = '0'
                this.oHomeData.indexForm.tpl_id = '0'
                this.oHomeData.indexForm.title = ''
                this.oHomeData.indexForm.description = ''
                // this.oHomeData.indexFormPlace = ['pc']

                this.modelInfo.tabActive = '2'
                this.modelInfo.currentTemplate = '未选中模板'
                this.modelInfo.modelSelect = '0'

                this.getIndexList()
            },
            /**
             * 重置表单
             * **/
            resetForm (forName) {
                this.oHomeData.currentLanguage = 'en'
                this.resetFields(forName)
                this.oHomeData.dialogAddVisible = false
            },

            /**
             * 重置校验
             * **/
            resetFields (forName) {
                this.$refs[forName].resetFields()
            },

            /**
             * 多语言切换
             *
             * **/
            handleTabClick () {
                this.resetFields('indexForm')
                let lang = this.oHomeData.currentLanguage

                this.oHomeData.indexForm.title = this.oHomeData.indexForm.data[lang].title
                this.oHomeData.indexForm.seo_title = this.oHomeData.indexForm.data[lang].seo_title
                this.oHomeData.indexForm.keywords = this.oHomeData.indexForm.data[lang].keywords
                this.oHomeData.indexForm.description = this.oHomeData.indexForm.data[lang].description

                this.oHomeData.indexForm.tpl_id = this.oHomeData.indexForm.data[lang].tpl_id
                this.oHomeData.indexForm.tpl_name = this.oHomeData.indexForm.data[lang].tpl_name
                this.oHomeData.indexForm.m_tpl_id = this.oHomeData.indexForm.data[lang].m_tpl_id
                this.oHomeData.indexForm.m_tpl_name = this.oHomeData.indexForm.data[lang].m_tpl_name
                this.oHomeData.indexForm.place = this.oHomeData.indexFormPlace // 新增首页tab切换时，保存应用端口状态

                this.oHomeData.titleCount = this.oHomeData.indexForm.title.split('').length
                this.oHomeData.descriptionCount = this.oHomeData.indexForm.description.split('').length
                this.oHomeData.SEOTitleCount = this.oHomeData.indexForm.seo_title.split('').length
                this.oHomeData.SEOKeywordsCount = this.oHomeData.indexForm.keywords.split('').length
                this.oHomeData.SEODescriptionCount = this.oHomeData.indexForm.description.split('').length
            },

            /**
             * 选择应用端口
             *
             * **/
            handleCheckedPlacesChange (value) {
                let arr = value
                let currentLanguage = this.oHomeData.currentLanguage
                this.oHomeData.indexFormPlace = value

                if (arr.indexOf('pc') != -1) {
                    this.oHomeData.pc_status = true
                    this.oHomeData.indexForm.data[currentLanguage].pc_status = true
                } else {
                    this.oHomeData.pc_status = false
                    this.oHomeData.indexForm.data[currentLanguage].pc_status = false
                }

                if (arr.indexOf('wap') != -1) {
                    this.oHomeData.m_status = true
                    this.oHomeData.indexForm.data[currentLanguage].m_status = true
                } else {
                    this.oHomeData.m_status = false
                    this.oHomeData.indexForm.data[currentLanguage].m_status = false
                }

                if (arr.indexOf('web') != -1) {
                    this.oHomeData.pc_status = true
                    this.oHomeData.indexForm.data[currentLanguage].pc_status = true
                } else {
                    this.oHomeData.pc_status = false
                    this.oHomeData.indexForm.data[currentLanguage].pc_status = false
                }
            },
            /**
             * 选择模板
             *
             * **/
            handleModelTempSelect (val) {
                this.$emit('handleModelTemp', val)
            },
            /**
             * 名称
             *
             * **/
            updateIndexTitle (value) {
                let currentLanguage = this.oHomeData.currentLanguage
                this.oHomeData.indexForm.data[currentLanguage].title = value
            },

            /**
             * SEO标题
             *
             * **/
            updateIndexSeoTitle (value) {
                let currentLanguage = this.oHomeData.currentLanguage
                this.oHomeData.indexForm.data[currentLanguage].seo_title = value
            },

            /**
             * SEO关键字
             *
             * **/
            updateIndexSeoKeywords (value) {
                let currentLanguage = this.oHomeData.currentLanguage
                this.oHomeData.indexForm.data[currentLanguage].keywords = value
            },

            updateIndexSeoDescription (value) {
                let currentLanguage = this.oHomeData.currentLanguage
                this.oHomeData.indexForm.data[currentLanguage].description = value
            },

            /**
             * 名称绑定原生keyup事件
             *
             * **/
            handleTitleKeyup () {
                let data = this.oHomeData.indexForm.title.split('')
                let length = data.length

                if (length > 100) {
                    data.splice(100, length - 99)
                    this.oHomeData.indexForm.title = data.join('')
                }

                this.oHomeData.titleCount = data.length
            },

            /**
             * SEO标题绑定原生keyup事件
             *
             * **/
            handleSEOTitleKeyup () {
                let data = this.oHomeData.indexForm.seo_title.split('')
                let length = data.length

                if (length > 200) {
                    data.splice(200, length - 199)
                    this.oHomeData.indexForm.seo_title = data.join('')
                }

                this.oHomeData.SEOTitleCount = data.length
            },

            handleSEOKeywordsKeyup () {
                let data = this.oHomeData.indexForm.keywords.split('')
                let length = data.length

                if (length > 200) {
                    data.splice(200, length - 199)
                    this.oHomeData.indexForm.keywords = data.join('')
                }

                this.oHomeData.SEOKeywordsCount = data.length
            },

            handleSEODescriptionKeyup () {
                let data = this.oHomeData.indexForm.description.split('')
                let length = data.length

                if (length > 200) {
                    data.splice(200, length - 199)
                    this.oHomeData.indexForm.description = data.join('')
                }
                this.oHomeData.SEODescriptionCount = data.length
            }
        }
    }
</script>

<style scoped>
    .model-item img {
        max-width: 100%;
        width: 150px;
        height: 150px;
        display: block;
        margin: 10px auto;
    }
    .count-tip-box {
        position: absolute;
        bottom: 5px;
        right: 5px;
        font-size: 12px;
        background-color: #ffffff;
        line-height: 1;
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