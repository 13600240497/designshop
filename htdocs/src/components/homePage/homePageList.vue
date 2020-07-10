<template>
    <div :class="prfClass">
        <el-table :data="oListData.homeList" style="width: 100%">
            <el-table-column width="48"></el-table-column>

            <el-table-column prop="id"  label="ID" width="100"></el-table-column>

            <el-table-column prop="pageLanguages[0].title" label="首页名称" width="250"></el-table-column>

            <el-table-column prop="status_name"  align="center" label="状态" width="100"></el-table-column>

            <el-table-column label="创建时间" width="160"  align="center">
                <template slot-scope="scope">
                    <span>{{ parseInt(scope.row.create_time)|moment('YYYY-MM-DD HH:mm:ss')}}</span>
                </template>
            </el-table-column>

            <el-table-column prop="create_name" align="center" label="创建者"></el-table-column>

            <el-table-column label="最后操作时间"  width="160"  align="center">
                <template slot-scope="scope">
                    <span>{{ parseInt(scope.row.update_time)|moment('YYYY-MM-DD HH:mm:ss')}}</span>
                </template>
            </el-table-column>

            <el-table-column prop="update_user" width="150" align="center" label="最后操作人"></el-table-column>

            <el-table-column label="操作" align="center" width="450" class="geshop-index-more">
                <template slot-scope="scope">
                    <el-tooltip content="装修" placement="bottom" effect="light">
                        <el-button class="icon-geshop-decorate"
                                   :class="{'is-lock': scope.row.is_lock == 2}"
                                   @click="decorate_redirect(scope.row.design_url, scope.row.is_lock, scope.row.create_user)"
                                   style="font-size: 24px;"></el-button>
                    </el-tooltip>

                    <el-tooltip content="上线" placement="bottom" effect="light">
                        <el-button class="icon-geshop-online"
                                   v-if="scope.row.status == 1 && oCommonData.site_code != 'dl'"
                                   @click="releasePage(scope.row.id)"
                                   style="font-size: 24px;"></el-button>
                    </el-tooltip>

                    <el-tooltip content="首页" placement="bottom" effect="light" v-if="oCommonData.site_code != 'dl'">
                        <el-button class="icon-geshop-home-page"
                                   v-if="scope.row.status == 3 || scope.row.status == 5 || scope.row.status == 8"
                                   @click="setAsHomePage(scope.row)"
                                   style="font-size: 24px;"></el-button>
                    </el-tooltip>

                    <el-tooltip content="预览" placement="bottom" effect="light">
                        <el-button class="icon-geshop-search"
                                   @click="previewPages(scope.row.pid, scope.row.pageLanguages)"
                                   style="font-size: 24px;"></el-button>
                    </el-tooltip>

                    <el-tooltip content="查看访问链接" placement="bottom" effect="light">
                        <el-button class="icon-geshop-view-link-2"
                                   v-if="scope.row.status == 2 || scope.row.status == 7 || scope.row.status == 8"
                                   @click="viewPages(scope.row.id, scope.row.status)"
                                   style="font-size: 24px;"></el-button>
                    </el-tooltip>

                    <el-dropdown split-button style="margin-left:10px">

                        <el-dropdown-menu slot="dropdown">
                            <el-dropdown-item
                                type="primary"
                                size="small"
                                @click.native="editIndex(scope.row, scope.row.is_lock, scope.row.create_user)">编辑</el-dropdown-item>

                            <el-dropdown-item
                                type="danger"
                                size="small"
                                @click.native="removeIndex(scope.row.id, scope.row.is_lock, scope.row.create_user)">删除</el-dropdown-item>

                            <el-dropdown-item
                                type="primary"
                                @click.native="viewDetail(scope.row)"
                                size="small">查看二维码</el-dropdown-item>
                        </el-dropdown-menu>

                    </el-dropdown>

                    <el-switch v-model="scope.row.lock_status"
                               inactive-text="锁定"
                               @change="lockingIndex(scope.row.id, scope.row.is_lock, scope.row.create_user)"
                               style="margin-left: 10px;"></el-switch>
                </template>
            </el-table-column>

        </el-table>
    </div>
</template>

<script>
    import {
        handlerLock,
        homeReleased,
        getBHomePages,
        getHomeLink,
        deleteIndex
    } from '../../plugin/api'
    export default {
        name: 'homePageList',
        props: {
            oCommonData: {
                type: Object
            },
            oListData: {
                type: Object
            },
            oHomeData: {
                type: Object
            },
            siteInfo: {
                type: Object
            },
            getIndexList: {
                type: Function
            },
            // 查看访问链接
            oViewAccessLinks: {
                type: Object
            },
            // 首页预览
            oHomePagePreview: {
                type: Object
            },
            // 查看二维码
            oViewQrCode: {
                type: Object
            },
            modelInfo: {
                type: Object
            },
            chouseRelaseType: {
                type: Object
            }
        },
        data () {
            return {
                prfClass: 'home-list'
            }
        },
        methods: {
            /**
             * 装修
             * **/
            decorate_redirect (url, is_lock, activity_create_user) {
                if ( is_lock == 2 && activity_create_user != this.siteInfo.userName && this.siteInfo.isSuper != 1) {
                    this.$message('该首页已被创建者锁定，需创建者解锁后其他用户才能操作')
                } else {
                    window.open(url)
                }
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

            /**
             * 上线
             * **/
            async releasePage (id) {
                this.confirm('确定要发布首页?', async vm => {
                    let params = {
                            page_id: id
                        },
                        res = await homeReleased(params)

                    if (res.code == 0) {
                        this.getIndexList()
                        vm.$message({
                            message: res.message,
                            type: 'success'
                        })
                    } else {
                        vm.$message.error(res.message)
                    }
                })
            },

            /**
             * 查看访问链接
             * **/
            async viewPages (id, status) {
                let params = {
                        id: id
                    },
                    res

                if (Number(status) == 8) {
                    res = await getBHomePages(params)
                } else {
                    res = await getHomeLink(params)
                }

                if (res.code == 0) {
                    this.oViewAccessLinks.dialogLinksVisible = true
                    this.oViewAccessLinks.pageLinks = res.data.list
                    this.oViewAccessLinks.tips = res.data.tips
                    this.oViewAccessLinks.urlID = id
                }
            },


            /**
             * 首页预览
             * **/
            previewPages (pid, langs) {
                let links = []
                let site_code = this.oCommonData.site_code ? this.oCommonData.site_code + '/' : ''

                if (this.oCommonData.site_code && (this.oCommonData.site_code === 'rg' || this.oCommonData.site_code === 'rw' || this.oCommonData.site_code === 'suk')) {
                    site_code = '';
                }

                langs.forEach(function (element) {
                    links.push({
                        lang: element.lang,
                        page_url: '/home/'+ site_code + 'design/preview?pid=' + pid + '&lang=' + element.lang,
                        lang_name: element.lang_name
                    })
                })
                this.oHomePagePreview.dialogPreviewLinksVisible = true
                this.oHomePagePreview.previewLinks = links
            },

            /**
             * 查看二维码
             * **/
            viewDetail (row) {
                this.oViewQrCode.currentActivityRow = row
                this.oViewQrCode.isDetailActive = true
                this.oCommonData.langList = this.oCommonData.langList
            },

            async removeIndex (id, is_lock, create_user) {
                if (is_lock == 2 && create_user != this.siteInfo.userName && this.siteInfo.isSuper != 1) {
                    this.$message('该首页已被创建者锁定，需创建者解锁后其他用户才能操作')
                } else {
                    this.confirm('确认删除该活动?', async vm => {
                        let params = {
                                id: id
                            },
                            res = await deleteIndex(params)

                        if (res.code === 0) {
                            this.getIndexList()
                            vm.$message({
                                type: 'success',
                                message: res.message
                            })
                        } else {
                            vm.$message({
                                type: 'error',
                                message: res.message
                            })
                        }
                    })
                }
            },

            /**
             * 编辑
             * **/
            editIndex (row, is_lock, create_user) {
                let currentLanguage = this.oHomeData.currentLanguage

                if (is_lock == 2 && create_user != this.siteInfo.userName && this.siteInfo.isSuper != 1) {
                    this.$message('该首页已被创建者锁定，需创建者解锁后其他用户才能操作')
                } else {
                    this.oHomeData.dialogName = '编辑首页'
                    this.oHomeData.indexForm.id = row.id
                    this.oHomeData.dialogAddVisible = true

                    let data = {}
                    // 新增时列出所有语言，编辑时仅列出当前端口首页支持的语言
                    this.oCommonData.langList.forEach(function (element) {
                        data[element.key] = {
                            title: '',
                            seo_title: '',
                            keywords: '',
                            tpl_id: '',
                            tpl_name: '未选中模板',
                            description: ''
                        }
                    })
                    row.pageLanguages.forEach(function (element) {
                        data[element.lang].title = element.title
                        data[element.lang].seo_title = element.seo_title
                        data[element.lang].keywords = element.keywords
                        data[element.lang].tpl_id = element.tpl_id
                        data[element.lang].tpl_name = element.tpl_name
                        data[element.lang].description = element.description
                    })

                    this.oHomeData.indexForm.data = data
                    this.oHomeData.indexForm.title = this.oHomeData.indexForm.data[currentLanguage].title

                    this.oHomeData.titleCount = this.oHomeData.indexForm.data[currentLanguage].title.split('').length
                    this.oHomeData.SEOTitleCount = this.oHomeData.indexForm.data[currentLanguage].seo_title.split('').length
                    this.oHomeData.SEOKeywordsCount = this.oHomeData.indexForm.data[currentLanguage].keywords.split('').length
                    this.oHomeData.SEODescriptionCount = this.oHomeData.indexForm.data[currentLanguage].description.split('').length

                    this.oHomeData.indexForm.description = this.oHomeData.indexForm.data[currentLanguage].description

                    this.oHomeData.indexForm.seo_title = this.oHomeData.indexForm.data[currentLanguage].seo_title
                    this.oHomeData.indexForm.keywords = this.oHomeData.indexForm.data[currentLanguage].keywords

                    this.oHomeData.descriptionCount = this.oHomeData.indexForm.data[currentLanguage].description.split('').length

                    let template = '未选中模板'
                    let selected = this.oHomeData.indexForm.data[currentLanguage].tpl_id

                    this.oHomeData.pageTemplateList.forEach(function (element) {
                        if (element.id == selected) {
                            template = element.name
                        }
                    })
                    this.oHomeData.indexForm.tpl_id = selected

                    this.modelInfo.modelSelect = selected
                    this.modelInfo.currentTemplate = template
                }
            },

            // 处理设置为首页的按钮点击事件
            setAsHomePage (record) {
                const recordStatus = this.chouseRelaseType.indexPageRecord.status
                this.chouseRelaseType.indexPageRecord = record
                if (recordStatus !== 5) {
                    this.chouseRelaseType.visible = true
                } else {
                    this.$message({
                        message: '首页正在生成中，勿需重复操作!',
                        type: 'warning'
                    })
                }
                if (recordStatus === 2 || recordStatus === 7) {
                    this.chouseRelaseType.radioStatus = true
                }
            },

            /**
             * 锁定
             * **/
            lockingIndex (id, is_lock, create_user) {
                if (create_user != this.siteInfo.userName && this.siteInfo.isSuper != 1) {
                    this.$message('只有首页创建者才具有此权限！')
                    this.oListData.homeList.forEach(function (element) {
                        if (element.is_lock == 1) {
                            element.lock_status = false
                        } else if (element.is_lock == 2) {
                            element.lock_status = true
                        }
                    })
                } else {
                    var tip = ''
                    if (is_lock == 2) {
                        tip = '解锁后，其他用户可以操作此活动'
                    } else if (is_lock == 1) {
                        tip = '加锁首页后，其他的用户只能查看首页二维码'
                    }

                    this.$confirm(tip, '提示', {
                        confirmButtonText: '确定',
                        cancelButtonText: '取消',
                        type: 'warning'
                    }).then(async () => {
                        let params = {
                                id: id
                            },
                            res = await handlerLock(params)

                        if (res.code === 0) {
                            this.getIndexList()
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
                    }).catch(() => {
                        this.oListData.homeList.forEach(function (element) {
                            if (element.is_lock == 1) {
                                element.lock_status = false
                            } else if (element.is_lock == 2) {
                                element.lock_status = true
                            }
                        })
                    })
                }
            }
        }
    }
</script>

<style scoped>

</style>
