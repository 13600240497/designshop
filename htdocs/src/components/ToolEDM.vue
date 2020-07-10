<template>
    <site-layout @publicReady="publicReady" :footLink="1">
        <el-row :span="24" class="geshop-Activity-tit">
            <span class="geshop-Activity-title">系统>>营销邮件APP跳转链接生成器</span>
        </el-row>
        <!-- 合并链接表单 -->
        <el-row class="client-box deeplink-client-box">
            <el-form :inline="true" :rules="appFormRules" :model="appForm" ref="appForm">
                <el-form-item class="gs-col-all" label="站点：">
                    <el-select v-model="appForm.site_active" placeholder="请选择" @change = "siteChange">
                        <el-option v-for="(item,index) in appForm.site_list" :key="index"
                                   :label="item.name" :value="index"></el-option>
                    </el-select>
                </el-form-item>
                <el-form-item class="gs-col-all" label="PC端链接：" prop="pc_link">
                    <el-input class="gs-link-wh" v-model="appForm.pc_link"></el-input>
                </el-form-item>
                <el-form-item class="gs-col-all" label="Branch固定参数：">
                    <span>?$deep_link=true&branch_dp=</span>
                </el-form-item>
                <el-form-item class="gs-col-all" label="PC页面跳转类型：" prop="action_type">
                    <el-select v-model="appForm.action_type" placeholder="请选择">
                        <el-option v-for="(item,index) in appForm.action_list" :key="index"
                                   :label="item" :value="index"></el-option>
                    </el-select>
                </el-form-item>
                <el-form-item class="gs-col-all" label="APP Deeplink规则：" prop="link_rule">
                    <el-input class="gs-link-wh" v-model="appForm.link_rule"></el-input>
                </el-form-item>
                <el-form-item class="gs-col-all">
                    <div class="deep-rule-tips">
                        填写规则，需与栏目PC端页面跳转类型匹配： <br>
                        1.H5活动：填写该活动M端的活动链接；<br>
                        2.分类页：填写分类ID；<br>
                        3.搜索页：填写搜索词，填写英语搜索文案 <br>
                        4.虚拟分类可填：bestsellers、newfaves、sale、newarrivals、deals<br>
                        <template v-if="appForm.site_active == 'zf'">5.Geshop 原生专题：填写Geshop系统的专题ID</template>
                    </div>
                </el-form-item>
                <el-form-item class="deeplink-url gs-col-all" label="合并后链接：" prop="deepLink">
                    <el-input class="gs-link-wh" v-model="appForm.deepLink" disabled="disabled"></el-input>
                    <input class="fileLink" type="text" :value="appForm.deepLink" ref="deeplink_url">
                    <el-button @click="handleCopy">复制链接</el-button>
                </el-form-item>
                <el-form-item class="geshop-deeplink-btn gs-col-all">
                    <el-button type="primary" @click="submitForm('appForm')" size="medium" :loading="submitLoading"
                               class="submit-btn">
                        合并链接
                    </el-button>
                    <el-button @click="resetForm('appForm')" type="info" size="medium">清除</el-button>
                </el-form-item>
                <el-form-item class="gs-col-all deeplink-wiki">
                    附各网站deeplink跳转查阅表：
                    <div>ZF网：<a href="http://wiki.hqygou.com:8090/pages/viewpage.action?pageId=147588554" target="_blank">http://wiki.hqygou.com:8090/pages/viewpage.action?pageId=147588554</a></div>
                    <div>RG网：<a href="http://wiki.hqygou.com:8090/pages/viewpage.action?pageId=151651754" target="_blank">http://wiki.hqygou.com:8090/pages/viewpage.action?pageId=151651754</a></div>
                    <div>DL网：<a href="http://wiki.hqygou.com:8090/pages/viewpage.action?pageId=148603370" target="_blank">http://wiki.hqygou.com:8090/pages/viewpage.action?pageId=148603370</a></div>
                </el-form-item>

            </el-form>
        </el-row>
    </site-layout>
</template>

<script>
import siteLayout from './layouts/Layout.vue';
import bus from '../store/bus-index.js';
import { getDeepLinkData, postDeepLink } from '../plugin/api';

export default {
    name: "DeepLink.vue",
    components: { siteLayout },
    data () {
        return {
            siteInfo: {}, // 站点信息
            sitePlat: '',
            places: '', // 站点端信息
            submitLoading: false,
            appForm: {
                site_active: '',
                action_type: '',
                site_list: {},
                action_list: {},
                link_rule: '', // DeepLink 规则
                pc_link: '', // pc端链接
                deepLink: '' // 合并后链接
            }, // app表单
            appFormRules: {
                pc_link: [
                    { required: true, message: '请输入PC端链接', trigger: 'blur' }
                ],
                link_rule: [
                    { required: true, message: '请输入APP Deeplink规则', trigger: 'blur' }
                ],
                action_type: [
                    { required: true, message: '请选择PC页面跳转类型', trigger: 'change' }
                ]
            }
        };
    },

    methods: {
        siteChange (val) {
            this.appForm.action_list = this.appForm.site_list[val].action;
        },

        // 初始化流程
        publicReady () {
            this.handleOptions();
            this.permissions = JSON.parse(localStorage.getItem('actionPermissions')).data;

            bus.$on('giveData', data => {
                this.siteInfo = data;
                this.sitePlat = this.siteInfo.site.split('-')[1];
            });
            // 设置当前站点信息
            this.places = JSON.parse(localStorage.currentSites).sites;
        },
        /**
         * 获取跳转类型及支持的站点列表
         */
        async handleOptions () {
            const res = await getDeepLinkData();
            if (res.data) {
                let keys = Object.keys(res.data.site_list);
                this.appForm.site_list = res.data.site_list;
                this.appForm.action_list = res.data.site_list[keys[0]].action;
                this.appForm.site_active = Object.keys(res.data.site_list)[0];
                this.appForm.action_type = 0;
            }
        },
        /**
         * 触发复制链接
         */
        handleCopy (e) {
            let target = this.$refs.deeplink_url;
            if (!target.value) {
                return false
            }
            target.select();
            document.execCommand('copy');
            this.$message({ type: 'success', message: '复制成功' });
        },
        resetForm (formName) {
            this.$refs[formName].resetFields();
        },
        /**
         * 触发合并链接
         */
        async handleSubmit () {
            try {
                const params = {
                    website_code: this.appForm.site_active,
                    activity_url: this.appForm.pc_link,
                    action_type: this.appForm.action_type,
                    action_params: JSON.stringify([this.appForm.link_rule])
                };
                const res = await postDeepLink(params);
                this.appForm.deepLink = res.data.url;
                this.submitLoading = false;
            } catch (e) {
                this.submitLoading = false;
            }
        },
        /**
         * 提交表单
         * @param formName
         */
        submitForm (formName) {
            this.submitLoading = true;
            this.$refs[formName].validate(async (valid) => {
                if (valid) {
                    this.handleSubmit();
                } else {
                    this.submitLoading = false;
                }

            });
        }
    }
};
</script>

<style lang="less" scoped>
    .deeplink-client-box {
        display: flex;
        justify-content: center;
        align-items: center;
        min-width: 880px;
        min-height: calc(100vh - 300px);
        background-color: #FFFFFF;
        padding: 40px;

        .el-form--inline .el-form-item__label {
            width: 180px;
        }
        .fileLink{
            position: absolute;
            left: 0;
            right: 0;
            top: 0;
            bottom: 0;
            margin: auto;
            border: none;
            width: 100%;
            background: none;
            opacity: 0;
            z-index: -10;
        }
    }

    .geshop-Activity-title {
        width: 88px;
        font-size: 22px;
        color: #3C4144;
        line-height: 30px;
    }

    .gs-col-all {
        width: 100% !important;
    }

    .gs-link-wh {
        width: 500px;
    }

    .geshop-deeplink-btn {
        margin-top: 40px;

        button {
            width: 180px;
            height: 50px;
            font-size: 20px;
        }
    }

    .submit-btn {
        margin-left: 140px;
        margin-right: 40px;
    }

    .deep-rule-tips {
        margin-left: 155px;
    }
    .deeplink-wiki a{
        text-decoration: none;
    }
</style>
<style lang="less">
    .client-box {
        .el-form-item .el-form-item__label {
            width: 180px;
        }

        .deeplink-url .el-form-item__label {
            color: #999999;
        }
    }
</style>
