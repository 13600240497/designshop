<template>
    <site-layout @publicReady="publicReady">

        <el-row :span="24" class="geshop-pagetemplate-tit">
            <span class="geshop-pagetpl-title">原生页面模板管理</span>
        </el-row>

        <el-row class="geshop-pagetpl-btn">
            <el-col :span="24">
                <el-form :inline="true" :model="search">

                    <el-form-item label="模板类型">
                        <el-select v-model="search.type" placeholder="请选择">
                            <el-option label="所有类型" value="0"></el-option>
                            <el-option label="公有模板" value="1">公有模板</el-option>
                            <el-option label="私有模板" value="2">私有模板</el-option>
                        </el-select>
                    </el-form-item>

                    <el-form-item label="名称">
                        <el-input v-model="search.name"></el-input>
                    </el-form-item>

                    <el-form-item label="创建者">
                        <el-input v-model="search.create_name"></el-input>
                    </el-form-item>

                    <el-form-item>
                        <el-button type="primary" @click="doSearch">查询</el-button>
                    </el-form-item>

                </el-form>
            </el-col>
        </el-row>

        <el-row class="geshop-pagetpl-lists">
            <el-col :span="24">
                <el-table :data="templateList" style="width: 100%">
                    <el-table-column prop="id" label="ID" width="100" margin-left="48"></el-table-column>

                    <el-table-column prop="name" label="名称"></el-table-column>

                    <el-table-column prop="tpl_type" label="模板类型">
                        <template slot-scope="scope">
                            <span v-if="scope.row.tpl_type == 1">公有模板</span>
                            <span v-else-if="scope.row.tpl_type == 2">私有模板</span>
                        </template>
                    </el-table-column>

                    <el-table-column prop="real_name" label="创建者"></el-table-column>

                    <el-table-column prop="create_time" label="存入时间" width="200">
                        <template slot-scope="scope">
                            <span>{{ Number(scope.row.create_time) | moment('YYYY-MM-DD HH:mm:ss') }}</span>
                        </template>
                    </el-table-column>

                    <el-table-column prop="update_time" label="最后修改时间" width="200">
                        <template slot-scope="scope">
                            <span>{{ Number(scope.row.update_time) | moment('YYYY-MM-DD HH:mm:ss') }}</span>
                        </template>
                    </el-table-column>

                    <el-table-column label="操作" min-width="120">
                        <template slot-scope="scope">
                            <el-button type="primary" size="small" @click="editTemplate(scope.row)"
                                       v-if="scope.row.opt == 1 || is_super == 1">编辑
                            </el-button>
                            <el-button type="danger" size="small" @click="deleteTemplate(scope.row.id)"
                                       v-if="scope.row.opt == 1 || is_super == 1">删除
                            </el-button>
                            <el-tooltip content="查看" placement="bottom" effect="light">
                                <el-button class="icon-geshop-search btn_preview_template" @click="seeTemplate(scope.row)"
                                           v-if="scope.row.pid"></el-button>
                            </el-tooltip>
                        </template>
                    </el-table-column>

                </el-table>

            </el-col>

        </el-row>

        <el-row v-if="total > 10">
            <el-col :span="24" class="text-right geshop-article-page">
                <el-pagination layout="prev, pager, next" :page-size="10" :total="total"
                               @current-change="handleCurrentChange"></el-pagination>
            </el-col>
        </el-row>

        <!--模板编辑-->
        <el-dialog title="模板编辑" :visible.sync="dialogVisible" @close="resetForm('form')">
            <el-form :model="form" :rules="rules" ref="form" label-width="100px">

                <el-form-item label="中文名称" prop="name">
                    <el-input v-model="form.name"></el-input>
                </el-form-item>

                <el-form-item label="模板类型">
                    <el-select v-model="form.tpl_type" placeholder="请选择">
                        <el-option label="公有模板" value="1">公有模板</el-option>
                        <el-option label="私有模板" value="2">私有模板</el-option>
                    </el-select>
                </el-form-item>

                <el-form-item label="预览图">
                    <div class="prewimg">
                        <img :src="form.pic" alt="">
                    </div>
                </el-form-item>

                <el-form-item>
                    <el-button @click="resetForm('form')" size="small">取消</el-button>
                    <el-button type="primary" @click="submitForm('form')" size="small" :loading="submitLoading">确定
                    </el-button>
                </el-form-item>

            </el-form>
        </el-dialog>

        <!--查看模板-->
        <el-dialog title="查看模板" class="geshop-page-template" :visible.sync="viewModel.visible"
                   :width="viewModel.sideWidth" @close="viewModelClose">
            <el-row v-loading="pageLoading">
                <el-col :span="24" class="imgPreview text-center">
                    <iframe frameborder="0" :src="viewModel.src" class="iframePreview"></iframe>
                </el-col>
            </el-row>
        </el-dialog>

    </site-layout>
</template>

<script>
import siteLayout from './layouts/Layout.vue';
import {
    ZF_getNativePageTemplateList,
    ZF_updateNativePageTemplate,
    ZF_deletePageTemplateNative,
    ZF_getCountrySiteList
} from '../plugin/api';
import {getCookie} from '../plugin/mUtils';
import '../../resources/stylesheets/PageTemplate.css';

export default {
    components: { siteLayout },
    data () {
        return {
            submitLoading: false,
            pageLoading: false,
            supportSites: [],
            supportLangs: [],
            total: '',

            // pc,m,app
            templateTabName: '',
            places: [], // 端口

            // 渠道信息
            allFallenSiteList: [],
            fallValue: '',

            search: {
                lang: '',
                pipeline: '',
                name: '',
                type: '0',
                place: '',
                create_name: ''
            },
            currentPage: 1,
            fileList: [],
            templateList: [],
            dialogVisible: false,
            uploadedImg: '',
            form: {
                id: null,
                name: '',
                pic: '',
                place: '',
                tpl_type: '1'
            },
            rules: {
                name: [
                    { required: true, message: '请输入名称', trigger: 'change' }
                ],
                pic: [
                    { required: true, message: '请输入图片', trigger: 'change' }
                ]
            },
            viewModel: {
                visible: false,
                html: '',
                sideType: 'pc',
                sideWidth: '80%',
                src: ''
            }
        };
    },
    computed: {
        is_super () {
            return localStorage.getItem('isSuper');
        },
        isLeader () {
            return localStorage.getItem('isLeader');
        },
        userName () {
            return localStorage.getItem('userName');
        }
    },
    watch: {
        'search.place': function (val) {
            this.getSupportCountrySites();
            this.search.pipeline = '';
            this.search.lang = '';
        }
    },

    created () {
        this.getTemplates();
    },
    methods: {
        /**
         * 获取支持渠道信息
         */
        async getSupportCountrySites () {
            let param = {
                activity_type: this.search.place !== '2' ? 1 : 2
            };
            let res = await ZF_getCountrySiteList(param);
            let supportPipelines = res.data.support_pipelines;

            // 取出支持的渠道，以code字段去重
            let supportCountrySites = [];
            for (var key in supportPipelines) {
                for (let k in supportPipelines[key]) {
                    supportCountrySites.push(supportPipelines[key][k]);
                }
            }
            supportCountrySites = JSON.parse(JSON.stringify(supportCountrySites));
            supportCountrySites = this.unique(supportCountrySites, 'code');
            this.allFallenSiteList = supportCountrySites;
        },
        async getTemplates () {
            let params = {
                    name: this.search.name,
                    create_name: this.search.create_name,
                    type: this.search.type,
                    place: this.search.place,
                    pageNo: this.currentPage,
                    pageSize: 10,
                    site_code: this.templateTabName ? getCookie('site_group_code') + '-' + this.templateTabName : ''
                },
                res = await ZF_getNativePageTemplateList(params);

            this.templateList = res.data.list;
            this.total = res.data.totalCount;
        },
        async submitForm (formName) {
            this.submitLoading = true;
            this.$refs[formName].validate(async (valid) => {
                if (valid) {
                    let params = {
                        id: this.form.id,
                        name: this.form.name,
                        pic: this.form.pic,
                        place: this.form.place,
                        type: this.form.tpl_type
                    };

                    let res = await ZF_updateNativePageTemplate(params);
                    if (res.code == 0) {
                        this.resetForm('form');
                        this.getTemplates();
                    } else {
                        this.submitLoading = false;
                    }
                } else {
                    this.submitLoading = false;
                }
            });
        },
        resetForm (formName) {
            this.$refs[formName].resetFields();
            this.dialogVisible = false;
            this.submitLoading = false;
            this.uploadedImg = '';
            this.fileList = [];
        },

        handleCurrentChange (currentPage) {
            this.currentPage = currentPage;
            this.getTemplates();
        },

        doSearch () {
            this.currentPage = 1;
            this.getTemplates();
        },

        editTemplate (row) {
            this.form.id = row.id;
            this.form.name = row.name;
            this.form.tpl_type = String(row.tpl_type);
            this.form.place = String(row.place);
            this.form.pic = row.pic;
            this.fileList.push({
                name: row.pic,
                url: row.pic
            });
            this.uploadedImg = row.pic;

            this.dialogVisible = true;
        },

        deleteTemplate (id) {
            this.$confirm('确定要删除该页面模板', '提示', {
                confirmButtonText: '确定',
                cancelButtonText: '取消',
                type: 'warning'
            }).then(async () => {
                let params = {
                    id: id
                };

                let res = await ZF_deletePageTemplateNative(params);

                if (res.code == 0) {
                    this.$message({
                        type: 'success',
                        message: '删除成功'
                    });
                    this.getTemplates();
                }
            }).catch(() => {
                this.$message({
                    type: 'info',
                    message: '已取消删除'
                });
            });
        },
        /* place pid, lang, id, site_code */
        async seeTemplate (row) {
            let place = row.place,
                pid = row.pid,
                lang = row.lang.key,
                id = row.id,
                site_code = row.site_code,
                pipeline = row.pipeline;
            if (!pid) {
                this.$message('活动pid不存在');
                return false;
            }
            this.viewModel.visible = true;
            this.pageLoading = true;
            let langDefualt = lang || 'en';
            let placeDomain = '/activity';
            this.viewModel.src = placeDomain + '/zf/native-page-tpl/preview?pid=' + pid + '&lang=' + langDefualt + '&id=' + id + '&site_code=' + site_code + '&pipeline=' + pipeline;

            let sideType = site_code.split('-')[1], sideWidth;
            if (sideType != 'pc') {
                sideWidth = '400px';
            } else {
                sideWidth = '80%';
            }
            this.viewModel.sideType = sideType;
            this.viewModel.sideWidth = sideWidth;
            this.pageLoading = false;
        },

        viewModelClose () {
            this.viewModel.visible = false;
            this.viewModel.html = '';
            this.viewModel.src = '';
        },

        unique (arr, key) {
            var n = [arr[0]];
            for (var i = 1; i < arr.length; i++) {
                if (key === undefined) {
                    if (n.indexOf(arr[i]) == -1) n.push(arr[i]);
                } else {
                    inner: {
                        var has = false;
                        for (var j = 0; j < n.length; j++) {
                            if (arr[i][key] == n[j][key]) {
                                has = true;
                                break inner;
                            }
                        }
                    }
                    if (!has) {
                        n.push(arr[i]);
                    }
                }
            }
            return n;
        },
        publicReady () {
            this.supportSites = JSON.parse(localStorage.getItem('supportSites')).data;
            // 设置当前站点信息
            let places = JSON.parse(localStorage.currentSites).sites;
            this.places = places;
            this.getSupportCountrySites();
        }
    }
};
</script>

<style lang="less" scoped>
    .iframePreview {
        border: 0;
        width: 100%;
        height: 600px;
    }

    .geshop-pagetpl-lists .el-table__row .el-table_1_column_11 .el-button {
        padding: 0px 0px;
        border: none;
        width: 40px;
        height: 40px;
        border-radius: 20px;
        display: inline-block;
        vertical-align: middle;
    }
    .geshop-pagetpl-lists{
        .btn_preview_template{
            padding: 0px 0px;
            border: none;
            width: 40px;
            height: 40px;
            border-radius: 20px;
            display: inline-block;
            vertical-align: middle;
        }
    }

    .prewimg {
        width: 300px;
        height: 300px;
        overflow: scroll;
    }
</style>
