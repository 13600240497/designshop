<template>
    <site-layout @publicReady="publicReady">
        <el-row :span="24" class="geshop-index-tit">
            <span class="geshop-index-title">首页日志</span>
        </el-row>
        <el-row>
            <el-col :span="24" class="geshop-index-lists">
                <template>
                    <el-tabs v-model="homeTabName" type="card" @tab-click="handleHomeTabClick" class="tab-header">
                        <el-tab-pane v-for="(item,key) in places" :label="item.platform_name" :key="key"
                                     :name="key"></el-tab-pane>
                    </el-tabs>
                </template>
                <template v-if="siteInfo.site === 'zf' || siteInfo.site === 'rg' || siteInfo.site === 'dl' || siteInfo.site === 'suk'">
                    <el-table :data="homeList" style="width: 100%">
                        <el-table-column width="48"></el-table-column>
                        <!--                        <el-table-column prop="id" label="ID" width="100"></el-table-column>-->
                        <el-table-column prop="title" label="页面名称"></el-table-column>
                        <el-table-column prop="pipeline_name" label="渠道"></el-table-column>
                        <el-table-column label="语言">
                            <template slot-scope="scope">
                                <span>{{ scope.row.langList[0].name }}</span>
                            </template>
                        </el-table-column>
                        <el-table-column prop="detail" label="操作详情"></el-table-column>
                        <el-table-column prop="update_user" label="发布人"></el-table-column>
                        <el-table-column label="发布时间">
                            <template slot-scope="scope">
                                <span>{{ parseInt(scope.row.create_time)|moment('YYYY-MM-DD HH:mm:ss')}}</span>
                            </template>
                        </el-table-column>
                        <el-table-column prop="online_user" label="上线人"></el-table-column>
                        <el-table-column label="上线时间">
                            <template slot-scope="scope">
                                <span>{{ parseInt(scope.row.online_time)|moment('YYYY-MM-DD HH:mm:ss')}}</span>
                            </template>
                        </el-table-column>
                        <!-- <el-table-column prop="status_name" label="状态"></el-table-column> -->
                        <!--                        <el-table-column prop="ip" label="操作IP"></el-table-column>-->
                        <el-table-column prop="rollback_user" label="回退执行人"></el-table-column>
                        <el-table-column label="回退执行时间">
                            <template slot-scope="scope">
                                <span v-if="scope.row.rollback_time">{{ parseInt(scope.row.rollback_time)|moment('YYYY-MM-DD HH:mm:ss')}}</span>
                            </template>
                        </el-table-column>
                        <el-table-column label="操作" class="geshop-index-more">
                            <template slot-scope="scope">
                                <el-tooltip content="回退首页" placement="bottom" effect="light">
                                    <el-button class="icon-geshop-rollback geshop-table-operation-icon"
                                               @click="rollBack_zf(scope.row)"
                                               style="font-size: 18px;"></el-button>
                                </el-tooltip>
                            </template>
                        </el-table-column>
                    </el-table>
                </template>
                <template v-else>
                    <el-table :data="homeList" style="width: 100%">
                        <el-table-column width="48"></el-table-column>
                        <el-table-column prop="id" label="ID" width="100"></el-table-column>
                        <el-table-column prop="create_name" label="操作人"></el-table-column>
                        <el-table-column prop="detail" label="详情"></el-table-column>
                        <!-- <el-table-column prop="status_name" label="状态"></el-table-column> -->
                        <el-table-column label="操作时间">
                            <template slot-scope="scope">
                                <span>{{ parseInt(scope.row.update_time)|moment('YYYY-MM-DD HH:mm:ss')}}</span>
                            </template>
                        </el-table-column>
                        <el-table-column prop="ip" label="操作IP"></el-table-column>
                        <el-table-column label="操作" class="geshop-index-more">
                            <template slot-scope="scope">
                                <el-tooltip content="查看访问链接" placement="bottom" effect="light">
                                    <el-button class="icon-geshop-view-link-2 geshop-table-operation-icon"
                                               @click="viewPages(scope.row.langList)"
                                               style="font-size: 24px;"></el-button>
                                </el-tooltip>
                                <el-tooltip content="返回此版本" placement="bottom" effect="light">
                                    <el-button class="icon-geshop-rollback geshop-table-operation-icon"
                                               @click="rollBack(scope.row.page_id, scope.row.version)"
                                               style="font-size: 18px;"></el-button>
                                </el-tooltip>
                            </template>
                        </el-table-column>
                    </el-table>
                </template>

            </el-col>
        </el-row>
        <el-dialog title="首页访问地址" :visible.sync="dialogLinksVisible">
            <el-row>
                <el-button v-for="item in pageLinks" type="primary" :key="item.name" @click="redirect(item.viewUrl)">{{
                    item.name }}
                </el-button>
                <p>{{ tips }}</p>
            </el-row>
        </el-dialog>
        <el-row v-if="total > options.pageSize">
            <el-col :span="24" class="text-right geshop-article-page">
                <el-pagination layout="prev, pager, next" :page-size="options.pageSize" :total="total"
                               :current-page.sync="options.pageNo"
                               @current-change="handleCurrentChange"></el-pagination>
            </el-col>
        </el-row>

        <roll-zf :data="rollbackInfo.data" :visible="rollbackInfo.visible" :loading="rollbackInfo.loading"
                 @closeRollZF="closeRollZF"
                 @rollZFCall="rollZFCall"></roll-zf>
    </site-layout>
</template>

<script>
import siteLayout from './layouts/Layout.vue';
import {
    homeLogList,
    homeLogListWithZaful,
    DL_homeLogList,
    rollBackPage,
    rollBackPageWithZaful,
    DL_rollBackPage,
    rollBackSelect_ZF,
    rollBackLists_ZF
} from '../plugin/api';
import rollZf from './homeLog/rollbackZF.vue';
import bus from '../store/bus-index.js';
import {getCookie} from '../plugin/mUtils';
import '../../resources/stylesheets/homePage.css';
import '../../resources/fonts/svg-fonts/style.css';

export default {
    components: { siteLayout, rollZf },
    data () {
        return {
            options: {
                pageNo: 1,
                pageSize: 10
            },
            homeList: [],
            total: 0, //默认列表的总页数为0
            homeTabName: getCookie('site_group_code') === 'dl' ? 'web' : 'pc',
            places: [],
            siteInfo: '',
            pageLink: [],
            dialogLinksVisible: false,
            pageLinks: [],
            tips: '',
            currentPage: 1, //默认查找首页的数据
            // 回退版本信息
            rollbackInfo: {
                visible: false,
                data: [],
                loading: false
            }
        };
    },
    created: function () {
        bus.$on('giveData', data => {
            this.siteInfo = data;
        });
    },
    methods: {
        async getIndexList () {
            let siteGroupCode = getCookie('site_group_code');
            let params = {
                pageNo: this.currentPage,
                pageSize: 10,
                site_code: siteGroupCode + '-' + this.homeTabName
            };
            let res;

            if (siteGroupCode == 'zf' || siteGroupCode == 'rg' || siteGroupCode == 'suk' || siteGroupCode === 'dl') {
                res = await homeLogListWithZaful(params);
            } else {
                res = await homeLogList(params);
                // res = await homeLogListWithZaful(params);
            }

            this.homeList = res.data.list;
            this.total = parseInt(res.data.pagination.totalCount);
        },
        publicReady () {
            this.getIndexList();
            bus.$on('giveData', data => {
                this.siteInfo = data;
            });
            // 设置当前站点信息
            let places = JSON.parse(localStorage.currentSites).sites;
            delete places.app;
            this.places = places;
        },
        handleCurrentChange (currentPage) {
            this.currentPage = currentPage;
            this.getIndexList();
        },
        // PC，M，APP切换
        handleHomeTabClick (event) {
            this.homeTabName = event.name;
            this.currentPage = 1;
            this.options.pageNo = 1;
            this.getIndexList();
        },
        async viewPages (langList) {
            this.dialogLinksVisible = true;
            this.pageLinks = langList;
            // this.tips = res.data.tips
            // this.urlID = id
        },
        redirect (url) {
            window.open(url);
        },
        async rollBack (pageId, verison) {
            this.$confirm('确定回滚到此版本？', '提示', {
                confirmButtonText: '确定',
                cancelButtonText: '取消',
                type: 'warning'
            }).then(async () => {
                let siteGroupCode = getCookie('site_group_code');
                let params = {
                    site_code: siteGroupCode + '-' + this.homeTabName,
                    page_id: pageId,
                    version: verison
                };
                let res;

                if (siteGroupCode == 'zf' || siteGroupCode == 'rg' || siteGroupCode == 'suk' || siteGroupCode == 'dl') {
                    res = await rollBackPageWithZaful(params);
                } else {
                    res = await rollBackPage(params);
                }

                if (res.code == 0) {
                    this.$message({
                        type: 'success',
                        message: res.message
                    });
                } else {
                    this.$message({
                        type: 'warning',
                        message: res.message
                    });
                }
            }).catch(() => {

            });
        },
        async rollBack_zf (row) {
            if (typeof row != 'object') {
                return false;
            }
            let { page_id, pipeline, lang } = row;
            let siteGroupCode = getCookie('site_group_code');
            let res = await rollBackLists_ZF({
                site_code: siteGroupCode + '-' + this.homeTabName,
                page_id: page_id || '',
                pipeline: pipeline || '',
                lang: lang || 'en'
            });
            if (res.code === 0 && res.data) {
                let now_versions = res.data.now_versions || [];
                let before_versions = res.data.before_versions || [];
                this.rollbackInfo.data = now_versions.concat(before_versions);
                this.rollbackInfo.visible = true;
            } else {
                this.$message.warning(res.message);
            }
        },

        /**
         * 关闭ZF 回退首页弹窗
         * @param event
         */
        closeRollZF (event) {
            this.rollbackInfo = {
                visible: false,
                data: []
            };
        },

        /**
         * 确认回退选中版本
         * @param item 选中的版本对象
         * @param event
         * @return {Promise<void>}
         */
        async rollZFCall (event, item) {
            if (!(item && item.log_id)) {
                this.$message('请选择回退版本');
                return false;
            }
            this.rollbackInfo.loading = true;
            try {
                let res = await rollBackSelect_ZF({
                    log_id: item.log_id
                });
                this.rollbackInfo.loading = false;
                if(res.message){
                    this.$message(res.message);
                }
                if (res.code === 0) {
                    setTimeout(() => {
                        this.closeRollZF();
                    }, 200);
                }
            } catch (err) {
                this.rollbackInfo.loading = false;
                this.$message('回退错误');
            }

        }
    }
};
</script>
<style lang="less">
    .geshop-index-lists .has-gutter th {
        background-color: #f4f4f4 !important;
        padding: 8px 0px !important;
    }

    .geshop-index-lists .el-table__header-wrapper {
        height: 40px !important;
    }

    .geshop-table-operation-icon {
        padding: 0px 0px;
        border: none;
        width: 40px;
        height: 40px;
        border-radius: 20px;
        display: inline-block;
        vertical-align: middle;
    }

    .geshop-table-operation-icon:hover {
        background-color: #F4F4F4;
    }
</style>
