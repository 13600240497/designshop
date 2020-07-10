<template>
    <site-layout @publicReady="publicReady">
        <el-row :span="24" class="geshop-Activity-tit">
            <span class="geshop-Activity-title">头尾部刷新管理</span>
        </el-row>
        <!-- <el-dialog title="更新详情列表" :visible.sync="viewDetail" width="1200px" class="Popup window" custom-class="refresh-detail-dialog"> -->
        <el-row :gutter="20" class="refresh-search-row">
            <el-col>
                <span class="tip-result">更新结果</span>
                <el-select v-model="searchInfo.status" @change="handleDetailStatusChange">
                    <el-option label="所有结果" :value="0"></el-option>
                    <el-option label="更新成功" :value="1"></el-option>
                    <el-option label="更新失败" :value="2"></el-option>
                </el-select>
                <!--<el-button type="primary" icon="el-icon-refresh" size="medium" @click="batchRelease()"-->
                           <!--style="margin-left: 10px;">批量重新刷新-->
                <!--</el-button>-->
            </el-col>
            <!-- <el-col :span="4" :offset="8" class="text-rt">
{{refreshDetail[0].page_id}}
                </el-col> -->
        </el-row>
        <el-table :data="refreshDetail" label-width="80px" @selection-change="handleSelectionChange"
                  :default-sort="{prop:'status',order:'descending'}"
                  v-loading="pageLoading" :class="refreshDetail.length>10?'gs-wrapper-scroll':''"
                  :row-class-name="tableRowClassName" class="refresh-detail-table">
            <!-- <el-table-column type="selection" width="55"></el-table-column> -->
            <el-table-column label="序号" type="index" width="150"></el-table-column>
            <template v-if="currentPlace == '1'">
                <el-table-column prop="activity_id" label="活动ID"></el-table-column>
                <el-table-column prop="activity_name" label="活动名称"></el-table-column>
                <el-table-column prop="page_name" label="子页面名称"></el-table-column>
            </template>
            <template v-if="currentPlace == '2'">
                <el-table-column prop="page_id" label="首页ID"></el-table-column>
                <el-table-column prop="page_name" label="首页名称"></el-table-column>
            </template>

            <el-table-column prop="status" label="更新结果" class="result">
                <template slot-scope="scope">
                    <span v-if="scope.row.status == 1" style="color:#000000;">更新成功</span>
                    <span v-else-if="scope.row.status == 2" style="color:#F56C6C;">更新失败</span>
                </template>
            </el-table-column>

            <el-table-column prop="status" label="操作信息" class="result">
                <template slot-scope="scope">
                    <span style="color:#000000;">{{ scope.row.message }}</span>
                </template>
            </el-table-column>
            <el-table-column prop="status" label="语言" class="result" v-if="showColLang">
                <template slot-scope="scope">
                    <span style="color:#000000;">{{ scope.row.lang }}</span>
                </template>
            </el-table-column>

            <el-table-column label="操作">
                <template slot-scope="scope">
                    <!-- <span v-if="scope.row.status == 1">{{ scope.row.success_time | moment('YYYY-MM-DD HH:mm:ss') }}</span> -->
                    <!-- 查看 -->
                    <el-dropdown trigger="click">
                        <i class="el-icon-search gs-option-icon" @click="viewRefreshPage(scope.row.page_id)"
                           size="medium"></i>
                        <el-dropdown-menu slot="dropdown" class="refresh-view-dropdown">
                            <span class="view-tip-text">查看页面</span>
                            <el-dropdown-item v-for="(item,index) in viewPageData.list" :key="index">
                                <a :href="item.page_url" class="view-page-link" target="_blank">{{item.lang_name}}</a>
                            </el-dropdown-item>
                        </el-dropdown-menu>
                    </el-dropdown>
                    <!-- 刷新 -->
                    <i class="el-icon-refresh gs-option-icon" v-if="scope.row.status == 2"
                       @click="redistribution(scope.row)"></i>
                </template>
            </el-table-column>
        </el-table>
        <!-- <div class="el-row batch">
                <el-button type="primary" size="small" @click="batchRelease()">批量发布</el-button>
            </div> -->
        <el-row v-if="detailTotal > searchInfo.pageSize" style="margin-top:20px;">
            <el-col :span="24" class="text-right">
                <el-pagination layout="prev, pager, next" :page-size="searchInfo.pageSize" :total="detailTotal"
                               :current-page.sync="searchInfo.pageNo"
                               @current-change="handleDetailCurrentChange"></el-pagination>
            </el-col>
        </el-row>
        <!-- </el-dialog> -->
    </site-layout>
</template>

<script>
import siteLayout from './layouts/Layout.vue';
import {
    refreshLogDetail,
    ZF_refreshLogDetail,
    DL_refreshLogDetail,
    GB_refreshLogDetail,
    refreshSite,
    ZF_refreshSite,
    DL_refreshSite,
    GB_refreshSite,
    refreshHomePage,
    getAccessLink,
    ZF_getAccessLink,
    DL_getAccessLink,
    GB_getAccessLink,
    getHomeLink
} from '../plugin/api';
import { getCookie, extendDeep, getRequest } from '../plugin/mUtils';

export default {
    components: { siteLayout },
    data () {
        return {
            page: 1,
            refreshList: [],
            currentPage: 1,
            total: '',
            viewDetail: false,
            refreshDetail: [],
            checked: true,
            currentLogId: '',
            currentPlace: 1, //1:活动页,2:首页
            pageIds: 'pageIds',
            searchInfo: {
                status: 0,
                pageSize: 10,
                pageNo: 1
            },
            detailTotal: 0,
            brotherInfo: {
                visible: false,
                idArr: [],
                ids: '',
                //级联数据
                options: [
                    {
                        label: '根目录',
                        value: 0,
                        id: 0,
                        children: []
                    }
                ],
                props: {
                    id: 'id',
                    value: 'value',
                    label: 'label',
                    children: 'children'
                },
                //当前选中的
                parent_id: 0,
                current_id: 0,
                parentArr: [],
                pageInfo: {
                    pageNo: 1,
                    pageSize: 10
                }
            },
            //查看页面
            viewPageData: { list: [], tips: '' },
            //文件选中
            fileSelectOp: {
                ids: '',
                batchStatu: false
            },
            dialogVisible: false,
            loading: false,
            pageLoading: false,
            showColLang: false
        };
    },
    created () {
        let query = getRequest();
        this.currentLogId = query.id;
        this.currentPlace = query.place;
    },
    computed: {
        site_group_code () {
            return getCookie('site_group_code');
        }
    },
    methods: {
        handleDetailCurrentChange (currentPage) {
            this.getrefreshDetail(this.currentLogId, this.currentPlace);
        },

        // 获取日志详情
        async getrefreshDetail (id, place) {
            this.pageLoading = true;
            let params = extendDeep({ id: id }, this.searchInfo);
            let res;
            switch (this.site_group_code) {
            case 'zf' :
            case 'dl' :
            case 'suk' :
            case 'rg':
                res = await ZF_refreshLogDetail(params);
                break;
            case 'gb':
                res = await GB_refreshLogDetail(params);
                break;
            default :
                res = await refreshLogDetail(params);
                break;
            }
            this.pageLoading = false;
            this.refreshDetail = res.data.list;
            if (Object.keys(this.refreshDetail[0]).includes('lang')) {
                this.showColLang = true;
            }
            this.currentLogId = id;
            this.currentPlace = place;
            this.detailTotal = res.data.pagination.totalCount;
            // this.viewDetail = true
        },
        publicReady () {
            this.getrefreshDetail(this.currentLogId, this.currentPlace);
        },

        /* 列表选中 */
        handleSelectionChange (val) {
            let arr = [];

            val.forEach(function (element) {
                arr.push(element.page_id);
            });

            this.brotherInfo.idArr = arr;
            this.brotherInfo.ids = arr.join(',');
        },
        async RepublishPage (row) {
            let _this = this;
            let selectPageIds = row ? row.page_id : '';

            if (row) {
                selectPageIds = row.page_id;
            } else {
                _this.refreshDetail.forEach((item) => {
                    selectPageIds += item.page_id + ',';
                });
                selectPageIds = selectPageIds.substring(0, selectPageIds.length - 1);
            }

            let params = {
                site_code: getCookie('SITECODE'),
                logId: this.currentLogId,
                pageIds: selectPageIds
            };
            let refreshFn = null;
            if (this.currentPlace == '1') {
                switch (getCookie('site_group_code')) {
                case 'zf' :
                case 'dl' :
                case 'suk' :
                case 'rg':
                    refreshFn = ZF_refreshSite;
                    break;
                case 'gb':
                    refreshFn = GB_refreshSite;
                    break;
                default:
                    refreshFn = refreshSite;
                    break;
                }
            } else {
                refreshFn = refreshHomePage;
            }

            let res = await refreshFn(params, { successOff: true });

            if (res.code == 0) {
                this.$message.success('更新任务发送成功');
                setTimeout(function () {
                    _this.getrefreshDetail(_this.currentLogId, _this.currentPlace);
                }, 500);
            }
        },

        // 重新发布
        redistribution (row) {
            this.RepublishPage(row);
        },

        //批量发布
        batchRelease () {
            this.RepublishPage();
            // if (this.brotherInfo.idArr.length < 1) {
            // 	this.$message({ type: 'warning', message: '请选中文件' })

            // 	return false
            // } else if (this.brotherInfo.idArr.length > 1) {
            // 	let params = {
            // 		site_code: getCookie('SITECODE'),
            // 		pageIds: this.brotherInfo.ids,
            // 		logId: this.currentLogId
            // 	}
            // 	await refreshSite(params)
            // }
        },
        //详情状态change search
        handleDetailStatusChange (value) {
            this.searchInfo.pageNo = 1;
            this.getrefreshDetail(this.currentLogId, this.currentPlace);
        },
        // 查看按钮
        async viewRefreshPage (id) {
            let currentPlace = this.currentPlace;
            let viewLinkFn = null;
            if (currentPlace == 1) {
                switch (this.site_group_code) {
                case 'zf' :
                case 'dl' :
                case 'suk' :
                case 'rg':
                    viewLinkFn = ZF_getAccessLink;
                    break;
                case 'gb':
                    viewLinkFn = GB_getAccessLink;
                    break;
                default :
                    viewLinkFn = getAccessLink;
                    break;
                }
            } else {
                viewLinkFn = getHomeLink;
            }
            ;
            let res = await viewLinkFn({ id: id, btn: 0 });
            if (res.code == 0) {
                let data = res.data;
                this.viewPageData = data;
                if (data.tips) {
                    this.$message.warning(data.tips);
                }
            } else {
                this.$message.warning(res.message);
            }
            this.$forceUpdate();
        },
        viewRefreshPageClear () {
            this.viewPageData = {
                list: [],
                tips: ''
            };
            this.$forceUpdate();
        },
        tableRowClassName ({ row, rowIndex }) {
            if (row.status == 2) {
                return 'error-row';
            } else {
                return 'success-row';
            }
        }
    }
};
</script>
<style scoped>
    .el-dialog {
        width: 80%;
    }

    .resultText {
        margin: 0;
    }

    .result-normal {
        color: #1e9fff;
    }

    .result-error {
        color: #f56c6c;
    }

    .tip-result {
        color: #3c4144;
        font-size: 14px;
    }

    .batch {
        margin-top: 20px;
        text-align: right;
    }

    .view-page-link {
        text-decoration: none;
        color: inherit;
        display: block;
        width: 100%;
        padding: 0 20px;
        margin-left: -20px;
    }
</style>


<style lang="less">
    .refresh-detail-dialog {
        .gs-wrapper-scroll .el-table__body-wrapper {
            max-height: 600px;
            overflow-y: scroll;
        }
    }

    .refresh-search-row .el-select .el-input__inner {
        margin-left: 10px;
    }

    .refresh-detail-table {
        .el-table__row {
            height: 60px;
        }
    }

    .error-row {
        background-color: #fef0f0 !important;
    }

    .success-row {
        background-color: #fff !important;
    }

    .refresh-view-dropdown {
        width: 120px;

        .view-tip-text {
            padding: 5px 20px;
            font-size: 14px;
            display: block;
            color: #9e9e9e;
        }
    }

    .gs-option-icon {
        font-size: 20px;
        padding: 0 5px;
        color: #000;
        cursor: pointer;
    }
</style>




