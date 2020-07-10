<template>

    <el-dialog title="请选择设为首页类型" width="30%" :visible.sync="chouseRelaseType.visible" @close="viewModelClose" class="geshop-index-preview-address showChoseIndexType">
        <el-row class="channel" v-if="chouseRelaseType.indexPageRecord.status != 3">
            <h3 class="title">语言</h3>
            <el-checkbox-group v-model="chouseRelaseType.langArr">
                <el-checkbox v-for="item in oCommonData.langList" :label="item.key" :key="item.key">{{ item.name }}</el-checkbox>
            </el-checkbox-group>
        </el-row>
        <el-row class="items">
            <el-col>
                <el-radio-group v-model="chouseRelaseType.indexType" size="medium">
                    <el-radio label="1">A首页</el-radio>
                    <el-radio label="2" :disabled="chouseRelaseType.radioStatus">B首页</el-radio>
                </el-radio-group>
            </el-col>
        </el-row>
        <el-row class="tips_msg">
            <el-col>
                A首页为正常首页，B首页为测试首页，设为A首页以后，不可以转换为B首页！
            </el-col>
        </el-row>
        <div slot="footer" class="dialog-footer">
            <el-button size="small" @click="handleCancelReleaseIndexType">取消</el-button>
            <el-button type="primary" size="small" @click="setHomePage" :loading="chouseRelaseType.abLoading">确定</el-button>
        </div>
    </el-dialog>

</template>

<script>
    import {
        setAsHomePageA,
        setAsHomePageB
    } from '../../plugin/api'

    export default {
        name: 'setHomePageType',
        props: {
            chouseRelaseType: {
                type: Object
            },
            viewModel: {
                type: Object
            },
            oCommonData: {
                type: Object
            },
            getIndexList: {
                type: Function
            }
        },
        data () {
            return {}
        },
        methods: {
            handleCancelReleaseIndexType () {
                this.chouseRelaseType.visible = false
            },

            viewModelClose () {
                this.viewModel.visible = false
                this.viewModel.html = ''
                this.viewModel.src = ''
            },

            // 确认设置为首页
            setHomePage() {
                this.setIndexPage()
            },

            async setIndexPage() {
                this.chouseRelaseType.abLoading = true
                const indexType = this.chouseRelaseType.indexType
                const record = this.chouseRelaseType.indexPageRecord
                const lang = this.chouseRelaseType.langArr.join(',')

                if (record.status != 3 && this.chouseRelaseType.langArr.length == 0) {
                    this.$message({
                        type: 'warning',
                        message: '请选择语言'
                    })
                    this.chouseRelaseType.abLoading = false
                    return false
                }

                const params = { page_id: record.id }

                if (record.status != 3) {
                    params.langList = lang
                }

                const res = parseInt(indexType) === 1 ? await setAsHomePageA(params) : await setAsHomePageB(params)
                if (res.code === 0) {
                    this.getIndexList()
                    this.$message({
                        type: 'success',
                        message: res.message
                    })
                    this.chouseRelaseType.visible = false
                } else {
                    this.$message({
                        type: 'error',
                        message: res.message
                    })
                }
                this.chouseRelaseType.abLoading = false
            }
        }
    }
</script>

<style scoped>

</style>