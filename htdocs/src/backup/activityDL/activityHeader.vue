<template>
    <div>
        <el-row :span="24" class="geshop-Activity-tit">
            <span class="geshop-Activity-title">活动管理</span>
        </el-row>
        <el-row class="geshop-Activity-btn">
            <el-col>
                <el-button class="geshop-Activity-btn-add" @click="handleCreateActivity">
                    <span class="icon-geshop-pack-up"></span>
                    <span class="geshop-icon-add-text">新增活动</span>
                </el-button>
            </el-col>
            <el-col>
                <el-button class="geshop-Activity-btn-refresh" @click="refreshHeadFoot">
                    <span class="icon-geshop-reset"></span>
                    <span class="geshop-icon-refresh-text">一键刷新头尾部</span>
                </el-button>
            </el-col>
            <el-col>
                <el-form class="geshop-form-inline" :inline="true">
                    <el-form-item label="" width="100px">
                        <el-input size="medium" placeholder="" v-model="oSearch.id" class="input-with-select">
                            <el-select v-model="oSearch.searchType" slot="prepend" placeholder="请选择">
                                <el-option label="专题ID" value="1"></el-option>
                                <el-option label="子活动ID" value="2"></el-option>
                            </el-select>
                        </el-input>
                    </el-form-item>
                    <el-form-item label="活动名称" width="100px">
                        <el-input v-model="oSearch.searchWord" placeholder=""></el-input>
                    </el-form-item>
                    <el-form-item label="创建者" width="100px">
                        <el-input v-model="oSearch.createName" placeholder=""></el-input>
                    </el-form-item>
                    <el-form-item label="常用活动筛选" width="100px">
                        <el-select v-model="oSearch.is_frequently">
                            <el-option label="全部活动" :value="0"></el-option>
                            <el-option label="常用活动" :value="1"></el-option>
                        </el-select>
                    </el-form-item>
                    <el-form-item>
                        <el-button type="primary" @click="doSearch">搜索</el-button>
                    </el-form-item>
                </el-form>
            </el-col>
        </el-row>
    </div>
</template>

<script>
import { DL_refreshSite } from '../../plugin/api';
import { getCookie } from '../../plugin/mUtils';

export default {
    /**
     * @description props
     * @param { Object } commonData 表单公共数据
     * @param { Object } oSearch 搜索数据
     * @param { Function } createActivity 新建活动方法
     * @param { Function } getActivityList 获取活动列表方法
     */
    props: {
        commonData: {
            type: Object
        },
        oSearch: {
            type: Object
        },
        createActivity: {
            type: Function
        },
        getActivityList: {
            type: Function
        }
    },
    data () {
        return {};
    },
    methods: {

        /**
         * @description 搜索
         */
        doSearch () {
            this.oSearch.currentPage = 1;
            this.getActivityList();
        },

        /**
         * @description 刷新头尾部
         */
        refreshHeadFoot () {
            this.$confirm('一键头尾刷新中，请在【系统日志】-任务日志里对刷新详情进行查看，是否前往查看？', '提示', {
                confirmButtonText: '是',
                ancelButtonText: '否',
                type: 'warning'
            })
                .then(async () => {
                    let res = await DL_refreshSite({
                        site_code: `${getCookie('site_group_code')}-${this.commonData.activityTabName}`
                    });
                    this.commonData.isDetailActive = false;
                    if (res.code === 0) {
                        window.location.href = '/base/task-log/index';
                    } else {
                        this.$message({
                            message: res.message
                        });
                    }
                });
        },

        /**
         * @description 新增活动
         */
        handleCreateActivity () {
            this.createActivity();
        }
    }
};
</script>

