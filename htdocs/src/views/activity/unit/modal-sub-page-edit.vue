<template>
<div>
    <!-- 新增编辑子页面 start -->
    <el-dialog
        width="800px"
        class="geshop-new-child-page"
        :title="pageForm.dialogTitle"
        :close-on-click-modal="false"
        :close-on-press-escape="false"
        :visible.sync="dialogPageVisible"
        @close="handle_cancel">


        <el-form
            :model="publicPageForm"
            :rules="publicPageRules"
            ref="publicPageForm">

            <el-form-item label="专题活动名称" prop="title" style="margin-bottom: 22px">
                <el-input v-model="publicPageForm.title" @change="updatePageTitle"></el-input>
            </el-form-item>

            <el-form-item label="下线时间" prop="end_time" style="margin:10px 0px 10px">
                <el-date-picker
                    v-model="publicPageForm.end_time"
                    type="datetime"
                    :disabled="this.currentPipeline != this.firstChannel"
                    v-on:change="setChildEndTime"
                    :picker-options="pickerOptions1"
                    value-format="timestamp">
                </el-date-picker>
            </el-form-item>

            <el-form-item label="已选应用端口" prop="place" class="gs-col-all child-page-place">
                <el-checkbox-group v-model="publicPageForm.place">
                    <el-checkbox
                        v-for="(item,key) in platform_list"
                        :label="item.code"
                        disabled
                        :key="key">{{ item.name }}
                    </el-checkbox>
                </el-checkbox-group>
            </el-form-item>

            <template v-if="site_group_code.indexOf('zf') >= 0">
                <el-form-item label="是否应用原生专题模式" v-if="is_native_show">
                    <el-radio-group
                        v-model="publicPageForm.is_native_theme"
                        :disabled="is_edit_mode == 1"
                        @change="nativeThemeChange">
                        <el-radio :label="1">是</el-radio>
                        <el-radio :label="0">否</el-radio>
                    </el-radio-group>
                </el-form-item>

                <el-form-item label="国家站活动链接是否需要自动跳转">
                    <el-radio v-model="publicPageForm.is_redirect_country" :label="1">是</el-radio>
                    <el-radio v-model="publicPageForm.is_redirect_country" :label="0">否</el-radio>
                </el-form-item>

                <el-form-item>
                    <el-radio-group v-model="linkType" :disabled="is_edit_mode == 1">
                        <el-radio :label="0">专题链接</el-radio>
                        <el-radio :label="1">博客链接</el-radio>
                    </el-radio-group>
                </el-form-item>
            </template>
        </el-form>

        <!-- 渠道公共的信息 -->
        <el-form
            :model="pageForm"
            :rules="pagePipeRules"
            class="home_add_piepeline"
            ref="pagePipeRules">

            <!-- 子活动渠道 -->
            <el-form-item class="pipeline_list is-required" label="渠道">
                <el-tabs type="card" @tab-click="handleTabClick('pipe')" v-model="currentPipeline">
                    <el-tab-pane v-for="item in channelList" :label="item.name" :name="item.code" :key="item.code"></el-tab-pane>
                </el-tabs>
            </el-form-item>

            <!-- 渠道下公共-->
            <el-form-item :label="calc_link_type_label" prop="url_name" style="margin-bottom: 10px">
                <label class="current-site-url">{{ calc_url_prefix }}/{{ calc_link_type_tag }}/</label>
                <el-input
                    type="text"
                    v-model="pageForm.url_name"
                    @change="updatePageUrlName"
                    v-on:keyup.native="handleUrlKeyup"
                    maxlength="64"
                    show-word-limit
                    style="max-width: 745px;">
                    <template slot="append">.html</template>
                    <i class="el-input__icon" slot="suffix">{{pageForm.url_name.length}}/64</i>
                </el-input>
            </el-form-item>

            <el-form-item
                label="PC活动结束后跳转链接"
                v-if="pageForm.pc_status"
                prop="pc_end_url"
                class="child-page-link">
                <el-input
                    v-model="pageForm.pc_end_url"
                    placeholder="请输入url链接"
                    @change="updatePCEndUrl">
                </el-input>
                <span style="color: #b7b7b7;">备注：不填默认为跳转至首页</span>
            </el-form-item>

            <el-form-item
                label="M活动结束后跳转链接"
                v-if="pageForm.m_status"
                prop="m_end_url"
                class="child-page-link">
                <el-input
                    v-model="pageForm.m_end_url"
                    placeholder="请输入url链接"
                    @change="updateMEndUrl">
                </el-input>
                <span style="color: #b7b7b7;">备注：不填默认为跳转至首页</span>
            </el-form-item>

            <!-- 子活动语言 -->
            <el-form-item class="pipeline_list pipeline_list_lang is-required" label="语言">
                <el-tabs
                    type="card"
                    @tab-click="handleTabClick('lang')"
                    v-model="currentLanguage"
                    v-if="pageForm.channelLanguageArr && pageForm.channelLanguageArr">
                    <el-tab-pane
                        v-for="(item,index) in pageForm.channelLanguageArr"
                        :label="item.name"
                        :name="item.code"
                        :key="index"
                    ></el-tab-pane>
                </el-tabs>
            </el-form-item>
        </el-form>

        <!-- 语言下包含的表单 -->
        <el-form
            ref="pageForm"
            :model="pageForm"
            :rules="pageFormRules"
            class="geshop-new-child-page-title">

            <el-form-item label="PC端自动跳转M端链接" prop="redirect_url" class="gs-col-all" v-show="pageForm.need_redirect">
                <el-input v-model="pageForm.redirect_url" placeholder="PC端自动跳转M端链接" @change="updatePageField($event,'redirect_url')"></el-input>
            </el-form-item>

            <el-form-item
                label="PC端页面模版"
                prop="model"
                v-if="!Boolean(pageForm.id) && pageForm.pc_status"
                class="child-page-model">
                <el-tag type="info">{{ pageForm.tpl_name }}</el-tag>
                <el-button
                    type="primary"
                    size="small"
                    :disabled="Boolean(pageForm.id)"
                    @click="handleModelTempSelect('pc')">
                    选择模版
                </el-button>
            </el-form-item>

            <template v-if="publicPageForm.is_native_theme == 0">
                <el-form-item
                    label="M端页面模版"
                    prop="model"
                    v-if="!Boolean(pageForm.id) && pageForm.m_status"
                    class="child-page-model">
                    <el-tag type="info">{{ pageForm.m_tpl_name }}</el-tag>
                    <el-button
                        type="primary"
                        size="small"
                        :disabled="Boolean(pageForm.id)"
                        @click="handleModelTempSelect('wap')">选择模版</el-button>
                </el-form-item>

                <el-form-item label="APP端页面模版" prop="model" v-if="!Boolean(pageForm.id) && pageForm.app_status" class="child-page-model">
                    <el-tag type="info">{{ pageForm.app_tpl_name }}</el-tag>
                    <el-button
                        type="primary"
                        size="small"
                        :disabled="Boolean(pageForm.id)"
                        @click="handleModelTempSelect('app')">
                        选择模版
                    </el-button>
                </el-form-item>
            </template>

            <template v-if="publicPageForm.is_native_theme == 1">
                <el-form-item label="移动端页面模版" prop="model" v-if="!Boolean(pageForm.id) && pageForm.native_status" class="child-page-model">
                    <el-tag type="info">{{ pageForm.native_tpl_name }}</el-tag>
                    <el-button type="primary" size="small" :disabled="Boolean(pageForm.id)" @click="handleModelTempSelect('native')">选择模版</el-button>
                </el-form-item>
            </template>

            <el-form-item label="SEO标题" class="child-page-keywords is-required">
                <el-input v-model="pageForm.seo_title" @change="updatePageField($event,'seo_title')" placeholder=""></el-input>
            </el-form-item>
            <el-form-item label="SEO关键字" prop="keywords" class="child-page-keywords">
                <el-input v-model="pageForm.keywords" @change="updatePageField($event,'keywords')" placeholder="有利于SEO优化"></el-input>
            </el-form-item>

            <el-form-item label="统计代码" prop="statistics_code" class="child-page-statistical-code">
                <el-input
                    type="textarea"
                    v-model="pageForm.statistics_code"
                    v-on:keyup.native="handleCodeKeyup"
                    :rows="4" @change="updatePageField($event,'statistics_code')">
                </el-input>
                <span class="count-tip-box">{{ pageForm.statistics_code.length }}/200</span>
            </el-form-item>

            <el-form-item label="SEO简介" prop="description" class="child-page-introduction">
                <el-input
                    type="textarea"
                    v-model="pageForm.description"
                    v-on:keyup.native="handleDescriptionKeyup"
                    :rows="4"
                    @change="updatePageField($event,'description')"
                    placeholder="有利于SEO优化"></el-input>
                    <span class="count-tip-box">{{ pageForm.description.length }}/200</span>
            </el-form-item>

            <!-- 分享模块 start -->
            <div class="share-container" style="clear:both">
                <el-form-item style="clear:both;">
                    <h3 style="border-bottom:1px solid #EEE;">导航分享信息</h3>
                </el-form-item>
                <el-form-item label="分享入口" prop="share_place" class="geshop-new-activities-place">
                    <el-checkbox-group v-model="pageForm.share_place" @change="handleSharePlaceChange">
                        <el-checkbox
                            v-for="item in pageForm.share_places"
                            :label="item"
                            :key="item">
                            {{ item }}
                        </el-checkbox>
                    </el-checkbox-group>
                </el-form-item>
                <el-form-item label="分享图片" prop="share_image" class="child-page-keywords" style="position:relative;">
                    <el-upload action="/component/index/upload-logo" name="files" style="position:absolute;top:41px;z-index:9;" accept="image/jpg,image/jpeg,image/png"
                                :on-progress="handleUploadProgress"
                                :on-success="handleUploadSuccess"
                                :on-exceed="handleUploadExceed"
                                :on-error="handleUploadError"
                                :show-file-list="false"
                                :before-upload="handleBeforeUpload">
                        <el-button class="el-icon-picture" :plain="true"></el-button>
                        <!-- <div slot="tip" class="el-upload__tip">只能上传jpeg/png文件，且不超过3M</div> -->
                    </el-upload>
                    <el-input v-model="pageForm.share_image" @change="updatePageField($event,'share_image')" style="padding-left:50px;box-sizing: border-box;" placeholder=""></el-input>
                </el-form-item>
                <el-progress :percentage="pageForm.uploadpercent" v-if="pageForm.uploadloading"></el-progress>
                <el-form-item label="分享标题" prop="share_title" class="child-page-keywords">
                    <el-input v-model="pageForm.share_title" @change="updatePageField($event,'share_title')" placeholder=""></el-input>
                </el-form-item>
                <el-form-item label="分享描述" prop="share_desc" class="child-page-keywords">
                    <el-input v-model="pageForm.share_desc" @change="updatePageField($event,'share_desc')" placeholder=""></el-input>
                </el-form-item>
                <el-form-item label="分享链接" prop="share_link" class="child-page-keywords">
                    <el-input v-model="pageForm.share_link"  @change="updatePageField($event,'share_link')" placeholder=""></el-input>
                </el-form-item>
            </div>
            <!-- 分享模块 end -->
            <el-form-item class="child-page-btns" style="padding-top: 20px; clear:both; text-align:center">
                <el-button
                    @click="handle_cancel"
                    size="small">取消</el-button>
                <el-button
                    type="primary"
                    :loading="submitLoading"
                    size="small"
                    @click="handle_submit">
                    确定
                </el-button>
            </el-form-item>
        </el-form>

    </el-dialog>
    <!-- 新增编辑子页面 end -->

    <theme-selector
        ref="themeSelector"
        @onSuccess="handle_page_template_callback">
    </theme-selector>

</div>

</template>


<script>
import {
    ZF_batchEditPage,
    ZF_deletePage,
    ZF_getCountrySiteList,
    ZF_getPageTemplateList,
    ZF_getPageTemplateListNative,
    ZF_batchAddPage,
} from '../../../plugin/api.js';

// 模版选择的弹窗
import themeSelector from '../../../components/modal-page-template/index.vue';

import { getCookie } from '../../../plugin/mUtils'

export default {
    data() {
        return {
            /**
             * 用于判断 【新增/编辑】模式，不同模式下可配置的配置项有区别，看 computed 变量
             * 0 = 新增
             * 1 = 编辑
             */
            is_edit_mode: 0,

            // 当前的数据版本ID
            version: 1,

            // 搜索
            site_group_code: getCookie('site_group_code'),
            submitLoading: false,

            // 所有的应用端列表
            platform_list: [],
            // 渠道列表
            channelList: [], // 数组形式
            channelObject: {}, // 对象形式
            currentPipeline: '', // 当前选中的渠道 code
            currentLanguage: '', // en

            // [pc/m/app/web]
            activityTabName: 'pc',

            // 什么表单
            publicPageForm: {
                place: [''],
                title: '',
                end_time: '',
                is_redirect_country: 0,
                is_native_theme: 0
            },

            share_entrance: [],
            currentPageRow: {},
            
            // 公共表单page rules
            publicPageRules: {
                place: [
                    { required: true, message: '请至少选择一个应用端口', trigger: 'change' }
                ],
                title: [
                    { required: true, message: '请输入名称', trigger: 'blur' },
                    { max: 100, min: 1, message: '长度在100个字符以内', trigger: 'blur' }
                ],
                end_time: [
                    { required: true, message: '请选择下线时间', trigger: 'blur' }
                ]
            },
            
            // 语言下的表单
            pageForm: {
                id: '',
                keywords: '',
                url_name: '',
                description: '',
                statistics_code: '',
                tpl_id: '0',
                m_tpl_id: '0',
                app_tpl_id: '0',
                native_tpl_id: '0',
                tpl_name: '',
                m_tpl_name: '',
                native_tpl_name: '',
                app_tpl_name: '',
                seo_title: '',
                pc_status: false,
                m_status: false,
                app_status: false,
                native_status: false,
                pc_end_url: '',
                m_end_url: '',

                share_image: '',
                share_title: '',
                share_desc: '',
                share_link: '',
                share_place: ['FB', 'Twitter'], // 记录选中的分享渠道
                share_places: ['FB', 'Twitter', 'Pinterest', 'Snapchat', 'Messenger'], // 记录所有分享渠道
                uploadloading: false,
                uploadpercent: 0,

                // 渠道
                channelLanguage: [],
                channelLanguageArr: [],
                channelLanguageCode: '',

                data: {},
                refresh_time: 0,
                end_url: '',
                dialogTitle: '子页面新增',
                redirect_url: '',
                obsPage: {
                    selected: {},
                },
                need_redirect: false
            },

            // 语言下的表单规则
            pageFormRules: {
                seo_title: [
                    { required: true, message: '请输入SEO标题', trigger: 'blur' }
                ],
                seo_title: [{
                    required: true,
                    message: '请输入SEO标题',
                    trigger: 'blur'
                }],
                url_name: [
                    {
                        required: true,
                        message: '请输入有效url地址',
                        trigger: 'blur'
                    },
                    {
                        pattern: /(?!^(\d+|[A-Za-z]+|[-]+)$)^[\w-]{3,64}$/,
                        message: '请输入3-64位的英文字母，-，数字的两种及以上组合',
                        trigger: 'blur'
                    }
                ],
                keywords: [{
                        required: false,
                        message: '有利于SEO优化',
                        trigger: 'blur'
                    },
                    {
                        max: 200,
                        min: 0,
                        message: '长度在200个字符以内',
                        trigger: 'blur'
                    }
                ],
                description: [
                    { required: false, message: '有利于SEO优化', trigger: 'blur' },
                    { max: 200, min: 0, message: '长度在200个字符以内', trigger: 'blur' }
                ]
            },

            // 渠道表单验证规则
            pagePipeRules: {
                url_name: [{
                    required: true,
                    message: '请输入有效url地址',
                    trigger: 'blur'
                },
                    {
                        pattern: /(?!^(\d+|[A-Za-z]+|[-]+)$)^[\w-]{3,64}$/,
                        message: '请输入3-64位的英文字母，-，数字的两种及以上组合',
                        trigger: 'blur'
                    }
                ]
            },

            pickerOptions1: {
                disabledDate(time) {
                    let currentDate = new Date(),
                        year = currentDate.getFullYear(),
                        month = currentDate.getMonth() + 7,
                        day = currentDate.getDate(),
                        hours = currentDate.getHours(),
                        min = currentDate.getMinutes(),
                        second = currentDate.getSeconds()
                    if (month > 12) {
                        month = month - 12
                        year += 1
                    }
                    let lastDateTime = new Date(year + '-' + month + '-' + day + ' ' + hours + ':' + min + ':' + second).getTime()
                    return (time.getTime() > lastDateTime) || (time.getTime() < currentDate.getTime() - 86400)
                }
            },

            dialogPageVisible: false,
            
            langList: [],
            tplId: '',
            urlName: '',
            end_time: '',
            refreshTime: 0,
            siteInfo: '',
            linkType: 0,
        }
    },

    components: {
        themeSelector,
    },

    computed: {
        /**
         * 首个渠道？
         */
        firstChannel () {
            if (this.channelList.length > 0) {
                const firstChannel = this.channelList[0].code
                return firstChannel
            } else {
                return ''
            }
        },

        /**
         * 判断是否展示原生专题的选项
         * @description
         *  1. 应用端包括 wap && app
         * @returns {boolean} 
         */
        is_native_show () {
            try {
                const list = this.platform_list.map(x => x.code);
                return list.indexOf('wap') != -1 && list.indexOf('app') != -1;
            } catch (e) {
                return true;
            }
        },

        /**
         * 判断URL类型，获取中文提示
         * @returns {string}
         */
        calc_link_type_label () {
            return ['专题URL', '博客URl'][this.linkType];
        },

        /**
         * 判断URL类型，获取英文目录
         * @returns {string}
         */
        calc_link_type_tag () {
            return ['promotion', 'blog'][this.linkType];
        },

        /**
         * 获取当前渠道的域名前缀
         * @returns {string}
         */
        calc_url_prefix () {
            if (this.currentPipeline && this.channelList) {
                const lang_list = this.handleChannelLangAssociate(this.channelList, this.currentPipeline);
                return lang_list[0].url_prefix.replace('/promotion', '');
            } else {
                return ''
            }
        }
    },

    methods: {

        handleUrlKeyup () {
            var data = this.pageForm.url_name.split('')
            var length = data.length

            if (length > 64) {
                data.splice(64, length - 63)
                this.pageForm.url_name = data.join('')
            }

        },

        handleCodeKeyup () {
            var data = this.pageForm.statistics_code.split('')
            var length = data.length

            if (length > 500) {
                data.splice(499, length - 499)
                this.pageForm.statistics_code = data.join('')
            }

        },

        handleDescriptionKeyup () {
            var data = this.pageForm.description.split('')
            var length = data.length

            if (length > 200) {
                data.splice(200, length - 199)
                this.pageForm.description = data.join('')
            }
        },

        /**
         * 添加子页面 - 根据渠道编码，获取语言对象转成数组形式
         * @param {Object/Array} all_pipelines 兼容 obj/array 的传入
         * @param {String} pipe 渠道码
         */
        handleChannelLangAssociate(all_pipelines, pipe) {
            let lang_list_object = {};
            let lang_list_arr = [];
            // 判断数据格式
            if (Array.isArray(all_pipelines)) {
                const pipeline = all_pipelines.filter(x => x.code == pipe)[0];
                lang_list_object = JSON.parse(JSON.stringify(pipeline.lang_list));
            } else {
                lang_list_object = JSON.parse(JSON.stringify(all_pipelines[pipe]['lang_list']));
            }
            Object.keys(lang_list_object).forEach((item) => {
                lang_list_arr.push(lang_list_object[item])
            })
            return lang_list_arr;
        },

        /**
         * 检查权限
         * @param {String} create_user 活动创建者
         * @returns {Boolean}
         */
        handle_check_auth (create_user) {
            if (create_user != this.siteInfo.userName || this.siteInfo.isSuper != 1) {
                this.$message.error('该活动已被创建者锁定，需创建者解锁后其他用户才能操作');
                return false;
            } else {
                return true;
            }
        },

        /**
         * 新增子页面
         * @param {Int} version 当前数据版本的ID
         * @param {Int} activityId 活动页ID
         * @param {Object} row 
         * @param {Object} pipeline_list 渠道列表
         * @param {String} platform 当前选中的设备 [pc/wap/app/web/]
         * @param {Array} platform_list 设备端列表
         */
        createPage ({
            version = 1,
            activityId,
            row,
            pipeline_list,
            platform = 'pc',
            platform_list
        }) {
            // 启动新增模式
            this.is_edit_mode = 0;
            this.version = version;
            this.activityTabName = platform;

            // 重置链接类型
            this.linkType = 0;

            // 当前活动渠道信息
            this.channelObject = JSON.parse(JSON.stringify(pipeline_list));

            // 活动所选渠道信息 { Array } channelList - [{ code: 'ZF', lang_list: {...}, name: '...' } }]
            this.channelList = Object.keys(pipeline_list).map((item) => {
                return pipeline_list[item];
            });

            // 默认第一个渠道的CODE值
            this.currentPipeline = Object.keys(pipeline_list)[0];

            // 获取默认第一个渠道的语言数组
            let channelLanguage_item = this.handleChannelLangAssociate(pipeline_list, this.currentPipeline);

            // 当前语言默认为第一个语言
            this.currentLanguage = channelLanguage_item[0]['code'];
            this.pageForm.channelLanguageArr = pipeline_list[this.currentPipeline].lang_list;

            this.pageForm.id = ''
            this.pageForm.activity_id = activityId
            this.pageForm.seo_title = ''
            this.pageForm.keywords = ''
            this.pageForm.url_name = ''
            this.pageForm.description = ''
            this.pageForm.statistics_code = ''
            this.pageForm.redirect_url = ''
            this.pageForm.refresh_time = 0
            this.pageForm.tpl_id = '0' // 每个语种都能选择自己的模版
            this.pageForm.m_tpl_id = '0'
            this.pageForm.app_tpl_id = '0'
            this.pageForm.native_tpl_id = '0'
            this.pageForm.tpl_name = '未选中模版'
            this.pageForm.m_tpl_name = '未选中模版'
            this.pageForm.app_tpl_name = '未选中模版'
            this.pageForm.native_tpl_name = '未选中模版'
            this.pageForm.pc_end_url = ''
            this.pageForm.m_end_url = ''
            this.pageForm.end_url = ''
            this.pageForm.need_redirect = false
            this.pageForm.share_place = ['FB', 'Twitter']
            this.share_entrance = ['FB', 'Twitter']
            this.pageForm.share_image = ''
            this.pageForm.share_title = ''
            this.pageForm.share_desc = ''
            this.pageForm.share_link = ''
            this.publicPageForm.title = ''
            this.publicPageForm.end_time = ''
            this.publicPageForm.is_redirect_country = 0
            this.publicPageForm.is_native_theme = 0

            // 为什么要复制一个 data 对象出来？？？
            this.pageForm.data = {};
            
            // 遍历渠道信息，然后复制到 pageForm.data[.....] 里面
            this.channelList.forEach((channel, index) => {
                this.pageForm.data[channel.code] = {};
                this.pageForm.data.channelLanguageArr = JSON.parse(JSON.stringify(channel.lang_list));
                // 渠道下公共数据
                this.pageForm.data[channel.code].channel_common_info = {
                    url_name: '',
                    pc_end_url: '',
                    m_end_url: ''
                };
                Object.keys(channel.lang_list).forEach((lang) => {
                    this.pageForm.data[channel.code][lang] = {
                        keywords: '',
                        url_name: '',
                        description: '',
                        statistics_code: '',
                        redirect_url: '',
                        tpl_id: '0', // 模版id
                        m_tpl_id: '0',
                        app_tpl_id: '0',
                        native_tpl_id: '0',
                        tpl_name: '未选中模版', // 模版名称
                        m_tpl_name: '未选中模版',
                        app_tpl_name: '未选中模版',
                        native_tpl_name: '未选中模版',
                        activity_id: activityId,
                        seo_title: '',
                        end_url: '',
                        pc_end_url: '',
                        m_end_url: '',
                        share_image: '',
                        share_title: '',
                        share_desc: '',
                        share_link: '',
                    }
                });
            });

            // 应用端口全选
            this.platform_list = [...platform_list];
            this.publicPageForm.place = platform_list.map(item => item.code);
            
            // 适配D网， 当 place == web 的时候, pc_status = true
            this.pageForm.pc_status = (this.publicPageForm.place.indexOf('pc') != -1) || this.publicPageForm.place.indexOf('web') != -1;
            this.pageForm.m_status = this.publicPageForm.place.indexOf('wap') != -1;
            this.pageForm.app_status = this.publicPageForm.place.indexOf('app') != -1;
           
            // ???
            this.pageForm.native_status = true;
            this.currentTemplate = '未选中模版'
            this.pageForm.dialogTitle = '新增子页面'

            // 同时存在PC和WAP，则开启这个选项
            // const platformKeys = platform_list && platform_list.map(x => x.code);
            // this.pageForm.need_redirect = (platformKeys.indexOf('pc') != -1 && platformKeys.indexOf('wap') == -1);

            this.dialogPageVisible = true;
        },

        /**
         * 编辑子页面
         * @param {Number} version 当前数据版本的ID
         * @param {Object} row
         * @param {Boolean} is_lock
         * @param {String} activity_create_user
         * @param {Array} langList
         * @param {String} platform 当前选中的设备端，[pc/wap/app/web]
         * @param {Array} platform_list 支持的设备端列表
         * @param {Object} pipelineObject
         * @param {Object} group_languages
         */
        editPage ({
            version = 1,
            row,
            is_lock,
            activity_create_user,
            langList,
            platform = 'pc',
            platform_list,
            pipeline_list,
            group_languages
        }) {
            // 编辑模式
            this.is_edit_mode = 1;
            this.version = version;
            this.activityTabName = platform;

            let _this = this;

            if (is_lock == 1 && activity_create_user != this.siteInfo.userName && this.siteInfo.isSuper != 1) {
                this.$message.error('该活动已被创建者锁定，需创建者解锁后其他用户才能操作')
            } else {
                this.pageForm.dialogTitle = '编辑子页面';

                this.linkType = row.is_blog; // 博客类型
                this.urlName = row.url_name;
                this.refreshTime = row.refresh_time;
                this.tplId = row.tplId;
                this.end_time = row.end_time == '0' ? '' : row.end_time * 1000;

                this.pageForm.id = row.id;
                this.pageForm.activity_id = row.activity_id;
                this.pageForm.refresh_time = row.refresh_time;

                this.publicPageForm.is_native_theme = row.is_native; // 是否应用原生专题模式
                this.publicPageForm.end_time = row.end_time == '0' ? '' : row.end_time * 1000; // end_time设为空日期选择器从当前时间选择，设为0不会从当前时间选择！
                this.publicPageForm.is_redirect_country = row.is_redirect_country;

                // 同时存在PC和WAP，则开启这个选项
                const platformKeys = platform_list && platform_list.map(x => x.code);

                // 渠道对象
                this.channelObject = JSON.parse(JSON.stringify(pipeline_list));
                
                // 活动所选渠道信息 { Array } channelList - [{ code: 'ZF', lang_list: {...}, name: '...' } }]
                this.channelList = Object.keys(pipeline_list).map((item) => {
                    return pipeline_list[item];
                });

                // 默认第一个渠道的CODE值
                this.currentPipeline = Object.keys(pipeline_list)[0];

                // 获取默认第一个渠道的语言数组
                let channelLanguage_item = this.handleChannelLangAssociate(pipeline_list, this.currentPipeline);

                // 当前语言默认为第一个语言
                this.currentLanguage = channelLanguage_item[0].code;
                this.pageForm.channelLanguageArr = pipeline_list[this.currentPipeline].lang_list;

                // 主活动端口
                this.platform_list = [...platform_list];

                let data = {}
                this.channelList.forEach(function (element) {
                    data[element.code] = {}
                    data[element.code].channelLanguageArr = JSON.parse(JSON.stringify(element.lang_list));
                    // 渠道下公共数据
                    data[element.code].channel_common_info = {
                        url_name: '',
                        pc_end_url: '',
                        m_end_url: ''
                    }
                    let langList = element.lang_list
                    Object.keys(langList).forEach((k) => {
                        data[element.code][k] = {
                            title: '',
                            url_name: '',
                            seo_title: '',
                            keywords: '',
                            description: '',
                            statistics_code: '',
                            pc_end_url: '',
                            m_end_url: '',
                            redirect_url: '',
                            share_place: [],
                            share_image: '',
                            share_title: '',
                            share_desc: '',
                            share_link: '',
                            // 渠道
                            channelLanguageArr: {},
                            channelLanguageCode: '',
                            // 渠道下公共数据
                            channel_common_info : {
                                url_name: '',
                                pc_end_url: '',
                                m_end_url: ''
                            }
                        }
                    })
                });

                // 遍历获取当前子页面的每个语言下的数据
                let key = this.activityTabName
                for (let k in group_languages[key]) {
                    // 生成渠道下公共数据form
                    let $pipeForm = group_languages[key][k];
                    let $firstLang = Object.keys($pipeForm['language'])[0];
                    let $firstLangData = $pipeForm['language'][$firstLang];
                    let $channelForm = data[k];
                    $channelForm.channel_common_info.url_name = $firstLangData['url_name']
                    if (key === 'pc' || key === 'web') {
                        $channelForm.channel_common_info.pc_end_url = $firstLangData['end_url']
                    } else if(key === 'wap') {
                        $channelForm.channel_common_info.m_end_url = $firstLangData['end_url']
                    }
                    // 遍历回填各语言信息
                    for (let lang in group_languages[key][k]['language']) {
                        let $language = group_languages[key][k]['language'][lang],
                            $langForm = data[k][lang]
                        if (typeof $language != 'undefined') {
                            $langForm.title = $language && $language['title']
                            $langForm.url_name = $language && $language['url_name']
                            $langForm.seo_title = $language && $language['seo_title']
                            $langForm.keywords = $language && $language['keywords']
                            $langForm.description = $language && $language['description']
                            $langForm.statistics_code = $language && $language['statistics_code']
                            $langForm.redirect_url = '';
                            // $langForm.redirect_url = $language && $language['redirect_url']
                            if (key === 'pc' || key === 'web') {
                                $langForm.pc_end_url = $language && $language.end_url;
                            } else if (key == 'wap') {
                                $langForm.m_end_url = $language && $language.end_url;
                            }
                            // 2020-06-15 编辑模式下，屏蔽掉 Google+ 渠道
                            let share_place = $language && $language.share_place;
                            share_place = share_place && share_place.toString().replace(/\"/g, '')
                            let remote_share_place_arr = share_place.split(',');
                                remote_share_place_arr = remote_share_place_arr.filter(x => x != 'Google+');
                            $langForm.share_place = [...remote_share_place_arr];
                            // 其他分享数据
                            $langForm.share_image = $language && $language.share_image
                            $langForm.share_title = $language && $language.share_title
                            $langForm.share_desc = $language && $language.share_desc
                            $langForm.share_link = $language && $language.share_link
                        }
                    }
                }
                this.pageForm.data = data

                // 根据当前活动已勾选端口显示对应“跳转链接”和“页面模版”
                let pagePlacesArr = platform_list.map(item => item.code);
                this.publicPageForm.place = pagePlacesArr;

                // 每个端是否可用？
                this.pageForm.pc_status = pagePlacesArr.indexOf('pc') != -1 || pagePlacesArr.indexOf('web') != -1;
                this.pageForm.m_status = pagePlacesArr.indexOf('wap') != -1;
                this.pageForm.app_status = pagePlacesArr.indexOf('app') != -1;

                // 把当前渠道和语言下的数据，提取到公共表单区域里面
                if (this.pageForm.data[this.currentPipeline][this.currentLanguage]) {
                    const obj = this.pageForm.data[this.currentPipeline][this.currentLanguage];
                    this.pageForm.keywords = obj.keywords;
                    this.pageForm.redirect_url = '';
                    // this.pageForm.redirect_url = obj.redirect_url;
                    this.publicPageForm.title = obj.title;
                    this.pageForm.url_name = obj.url_name;
                    this.pageForm.description = obj.description;
                    this.pageForm.statistics_code = obj.statistics_code;
                    this.pageForm.pc_end_url = obj.pc_end_url;
                    this.pageForm.m_end_url = obj.m_end_url;
                    this.pageForm.seo_title = obj.seo_title;
                    this.pageForm.share_place = obj.share_place;
                    this.share_entrance = obj.share_place;
                    this.pageForm.share_image = obj.share_image;
                    this.pageForm.share_title = obj.share_title;
                    this.pageForm.share_desc = obj.share_desc;
                    this.pageForm.share_link = obj.share_link;
                }
                // 
                this.dialogPageVisible = true
            }
        },

        /**
         * 取消弹窗
         */
        handle_cancel () {
            this.resetFields();
            this.dialogPageVisible = false;
            this.pageForm.tpl_id = '0';
            this.end_time = ''
            if (this.siteInfo.site == 'gb') {
                this.obsPage.selected = {}
                this.obsPage.data = []
                this.obs.selected = {}
                this.pageForm.obsPage = {
                    selected: {}
                }
            }
        },

        /**
         * 表单提交
         */
        async handle_submit (formName = 'pageForm') {
            // 新增活动表单验证
            this.submitLoading = true

            this.$refs.pageForm.validate(async (valid) => {
                if (valid) {
                    let params, res
                    let activityFormObj = {}
                    let platformData = {}
                    let pipeline_list = {}

                    // 新增活动，公共表单校验
                    this.$refs['publicPageForm'].validate(async (valid) => {
                        if (!valid) {
                            this.submitLoading = false;
                            return false;
                        };

                        // 渠道表单校验
                        this.$refs['pagePipeRules'].validate(async (valid) => {

                            if (!valid) {
                                this.submitLoading = false;
                                return false;
                            };

                            // 活动名称和下线时间必填
                            if (this.publicPageForm.title == '' || this.publicPageForm.end_time == '') {
                                this.$message.error('请填写活动名称和页面下线时间！')
                                this.submitLoading = false
                                return false
                            }

                            let first_channel = this.channelList[0].code;
                            let first_lang = Object.keys(this.channelList[0].lang_list)[0];
                            let first_data = this.pageForm.data[first_channel][first_lang];

                            if( first_data.seo_title === ''){
                                this.$message.error('请填写活动SEO标题');
                                this.submitLoading = false;
                                return false;
                            }

                            let activityFormObj = {}
                            let jsonObject = {}
                            // 遍历当前渠道列表
                            this.channelList.forEach((item) => {
                                let key = item['code'] // { code: 'ZF', lang_list: {...}, name: '全球站' }
                                let languages = {}
                                let langList = item['lang_list']
                                let default_lang = Object.keys(langList)[0];
                                let formData = {}
                                let channel_common_info = this.pageForm.data[key].channel_common_info

                                Object.keys(langList).forEach((lang) => {
                                    let param = {}

                                    formData = this.pageForm.data[key][lang]
                                    // 修改 this.pageForm.data 中 渠道语言下的值
                                    formData.pc_end_url = channel_common_info.pc_end_url;
                                    formData.m_end_url = channel_common_info.m_end_url;

                                    // 遍历当前端口 - 需要过滤掉“页面模版”和“活动结束跳转链接”之外的所有字段
                                    this.platform_list.forEach((it) => {
                                        let k = it['code'] // { code: 'pc', name: 'PC' }
                                        param[k] = JSON.parse(JSON.stringify(formData))

                                        // 删除无用字段
                                        delete param[k].url_name
                                        delete param[k].seo_title
                                        delete param[k].keywords
                                        delete param[k].statistics_code
                                        delete param[k].description
                                        delete param[k].redirect_url
                                        delete param[k].activity_id
                                        delete param[k].share_place
                                        delete param[k].share_image
                                        delete param[k].share_title
                                        delete param[k].share_desc
                                        delete param[k].share_link

                                        // 根据当前端口是pc，m或app保留对应字段值
                                        if (k == 'pc' || k == 'web') {
                                            param[k].end_url = param[k].pc_end_url // 将pc端的end_url重命名
                                        } else if (k == 'wap') {
                                            if (this.publicPageForm.is_native_theme == 1) {
                                                param[k].tpl_id = param[k].native_tpl_id
                                                param[k].tpl_name = param[k].native_tpl_name
                                            } else {
                                                param[k].end_url = param[k].m_end_url // 将m端的end_url重命名
                                                param[k].tpl_id = param[k].m_tpl_id
                                                param[k].tpl_name = param[k].m_tpl_name
                                            }
                                        } else if (k == 'app') {
                                            if (this.publicPageForm.is_native_theme == 1) {
                                                param[k].tpl_id = param[k].native_tpl_id
                                                param[k].tpl_name = param[k].native_tpl_name
                                            } else {
                                                param[k].tpl_id = param[k].app_tpl_id
                                                param[k].tpl_name = param[k].app_tpl_name
                                            }
                                        }

                                        // 重命名后删除字段
                                        delete param[k].m_end_url
                                        delete param[k].pc_end_url
                                        delete param[k].m_tpl_id
                                        delete param[k].m_tpl_name
                                        delete param[k].app_tpl_id
                                        delete param[k].app_tpl_name
                                        delete param[k].native_tpl_id
                                        delete param[k].native_tpl_name
                                        //删除obs选中字段
                                        delete param[k].obsPage
                                        // 删除渠道字段
                                        delete param[k].channelLanguageArr
                                        delete param[k].channelLanguageCode
                                        delete param[k].channel_common_info
                                    })

                                    let $langSelect = this.pageForm.data[key][lang];
                                    let $first_channel = this.channelList[0].code;
                                    let $first_lang = Object.keys(this.channelList[0].lang_list)[0];
                                    let $first_data = this.pageForm.data[$first_channel][$first_lang];
                                    // 语言对象赋值
                                    languages[lang] = {
                                        url_name: channel_common_info.url_name ? channel_common_info.url_name : this.pageForm.data[$first_channel].channel_common_info.url_name,
                                        seo_title: $langSelect.seo_title ? $langSelect.seo_title : $first_data.seo_title,
                                        keywords: $langSelect.keywords,
                                        statistics_code: $langSelect.statistics_code,
                                        description: $langSelect.description,
                                        redirect_url: '',
                                        // redirect_url: $langSelect.redirect_url,
                                        share_place: this.share_entrance,
                                        share_image: $langSelect.share_image,
                                        share_title: $langSelect.share_title,
                                        share_desc: $langSelect.share_desc,
                                        share_link: $langSelect.share_link,
                                        platform: param
                                    }
                                })

                                activityFormObj[key] = {
                                    default_lang: default_lang,
                                    languages: languages
                                }
                            });

                            jsonObject['end_time'] = this.publicPageForm.end_time / 1000
                            jsonObject['title'] = this.publicPageForm.title
                            jsonObject['is_redirect_country'] = this.publicPageForm.is_redirect_country

                            let postData = {}

                            for (let key in activityFormObj) {
                                postData[key] = activityFormObj[key]
                            }
                            jsonObject['lang_list'] = JSON.stringify(postData)

                            // 应用端口
                            let platformsArr = []
                            this.platform_list.map((item) => {
                                return platformsArr.push(item.code)
                            })

                            jsonObject['platforms'] = platformsArr.join(',');
                            jsonObject['is_blog'] = this.linkType;
                            jsonObject['version'] = this.version;

                            // 是否应用原生专题模式
                            jsonObject['is_native_theme'] = this.publicPageForm.is_native_theme;

                            // 编辑
                            if (this.pageForm.id != '') {
                                // 编辑子页面传page_id
                                jsonObject['page_id'] = this.pageForm.id
                                res = await ZF_batchEditPage(jsonObject)
                            }

                            // 新增
                            else {
                                let flag = true
                                for (let item in this.pageForm.data) {
                                    if (this.pageForm.data[item].title == '') {
                                        this.$message.error('请确认所有语言所有必填项已经填写，再提交！')
                                        if (flag) {
                                            flag = false
                                        }
                                    }
                                }
                                if (!flag) {
                                    this.submitLoading = false
                                    window.GESHOP_FULL_LOADING.close();
                                    return false
                                }
                                // 新增子页面传activity_id
                                jsonObject['activity_id'] = this.pageForm.activity_id

                                res = await ZF_batchAddPage(jsonObject)
                            }

                            // AJAX回调结果
                            if (res.code == 0) {
                                this.$emit('onSuccess');
                                this.resetFields();
                                this.submitLoading = false;
                                this.dialogPageVisible = false;
                            } else {
                                this.$message.error(res.message);
                                this.submitLoading = false;
                            }
                        });
                        // end 渠道表单校验
                    });
                } else {
                    this.submitLoading = false
                }
            })
        },

        /**
         * 重置表单
         */
        resetFields() {
            this.$refs.publicPageForm.resetFields();
            this.$refs.pageForm.resetFields();
        },

        // 是否应用原生专题模式
        nativeThemeChange (val) {
            if(val === 1){
                this.publicPageForm.is_native_theme = 1
            } else if(val === 0){
                this.publicPageForm.is_native_theme = 0
            }
        },

        // 分享入口表单操作
        handleSharePlaceChange(value) {
            this.share_entrance = value
            this.pageForm.share_place = value
        },

        /**
         * 图片上传
         */
        handleUploadSuccess(response, file) {
            if (response.code == 0) {
                this.pageForm.data[this.currentPipeline][this.currentLanguage].share_image = response.data.url
                this.pageForm.share_image = response.data.url
                this.pageForm.uploadpercent = file.percentage
                setTimeout(() => {
                    this.pageForm.uploadloading = false
                }, 800)
            } else {
                this.$message.error(response.data.message)
            }
        },

        handleUploadProgress(event, file, fileList) {
            this.pageForm.uploadloading = true
            let percentage = Math.ceil(Math.random() * 50)
            this.pageForm.uploadpercent = percentage
        },

        handleUploadExceed () {
            this.$message.error('只允许上传一张图片！')
        },

        handleUploadError () {
            this.$message.error('文件上传失败！')
        },

        handleBeforeUpload (file) {
            if (['image/jpeg', 'image/png'].indexOf(file.type) == -1) {
                this.$message.error('请选择合适的图片格式！');
                return false
            }

            if (file.size >= 3 * 1024 * 1024) {
                this.$message.error('请选择合适的图片大小！');
                return false
            }
        },

        /**
         * 子页面的tab切换，包括渠道却换，语言切换
         * @param {string} type [pipe/lang];
         */
        handleTabClick(type = 'pipe') {
            // 重置表单
            this.$refs.pageForm.resetFields();

            // 切换渠道重新获取语言
            if (type == 'pipe') {
                // 获取语言对象
                let channelLanguage_item = this.handleChannelLangAssociate(this.channelObject, this.currentPipeline);
                // 获取当前渠道下的第1个语言编码
                this.currentLanguage = channelLanguage_item[0]['code'];
                 // 获取当前渠道下的数据对象
                this.pageForm.channelLanguageArr = JSON.parse(JSON.stringify(this.channelObject[this.currentPipeline].lang_list));
            }

            // 判断专题url同步（切换同步） 2019/07/05
            let firstChannel = this.channelList[0].code;
            if( this.currentPipeline !== firstChannel) {
                // 判断当前渠道下公共专题链接为空
                if( this.pageForm.data[this.currentPipeline].channel_common_info.url_name == '' ){
                    this.pageForm.url_name = this.pageForm.data[firstChannel].channel_common_info.url_name
                    this.pageForm.data[this.currentPipeline].channel_common_info.url_name = this.pageForm.data[firstChannel].channel_common_info.url_name
                } else {
                    this.pageForm.url_name = this.pageForm.data[this.currentPipeline].channel_common_info.url_name
                }
            }else{
                this.pageForm.url_name = this.pageForm.data[this.currentPipeline].channel_common_info.url_name
            }

            this.pageForm.refresh_time = this.refreshTime
            this.publicPageForm.end_time = this.end_time
            this.pageForm.pc_end_url = this.pageForm.data[this.currentPipeline].channel_common_info.pc_end_url
            this.pageForm.m_end_url = this.pageForm.data[this.currentPipeline].channel_common_info.m_end_url

            // 变量地址引用，下面一堆的变量赋值
            const shot_var = this.pageForm.data[this.currentPipeline][this.currentLanguage];

            // 模版字段
            this.pageForm.keywords = shot_var.keywords;
            this.pageForm.description = shot_var.description;
            this.pageForm.statistics_code = shot_var.statistics_code;

            this.pageForm.tpl_id = shot_var.tpl_id
            this.pageForm.tpl_name = shot_var.tpl_name
            this.pageForm.m_tpl_id = shot_var.m_tpl_id
            this.pageForm.m_tpl_name = shot_var.m_tpl_name
            this.pageForm.app_tpl_id = shot_var.app_tpl_id
            this.pageForm.app_tpl_name = shot_var.app_tpl_name
            this.pageForm.native_tpl_id = shot_var.native_tpl_id
            this.pageForm.native_tpl_name = shot_var.native_tpl_name
            this.pageForm.seo_title = shot_var.seo_title
            this.pageForm.redirect_url = '';
            // this.pageForm.redirect_url = shot_var.redirect_url
            
            // 分享字段
            this.pageForm.share_place = this.share_entrance
            this.pageForm.share_image = shot_var.share_image
            this.pageForm.share_title = shot_var.share_title
            this.pageForm.share_desc = shot_var.share_desc
            this.pageForm.share_link = shot_var.share_link;
        },

        /**
         * 子页面各渠道数据修改 data value
         * name 属性名
         * param 参数名
         */
        updatePageField (data, name, param) {
            if (!param) {
                this.pageForm.data[this.currentPipeline][this.currentLanguage][name] = data;
            } else {
                this.pageForm.data[this.currentPipeline][this.currentLanguage][name][param] = data;
            }

        },

        updatePageTitle (value) {
            this.publicPageForm.title = value
        },

        updatePageKeywords(value) {
            this.pageForm.data[this.currentPipeline][this.currentLanguage].keywords = value
        },

        updatePageSeoTitle(value) {
            this.pageForm.data[this.currentPipeline][this.currentLanguage].seo_title = value
        },

        updatePageRedirectUrl(value) {
            this.pageForm.data[this.currentPipeline][this.currentLanguage].redirect_url = value
        },

        updateShareImage(value) {
            this.pageForm.data[this.currentPipeline][this.currentLanguage].share_image = value
        },

        updateShareTitle(value) {
            this.pageForm.data[this.currentPipeline][this.currentLanguage].share_title = value
        },

        updateShareDesc(value) {
            this.pageForm.data[this.currentPipeline][this.currentLanguage].share_desc = value
        },

        updateShareLink(value) {
            this.pageForm.data[this.currentPipeline][this.currentLanguage].share_link = value
        },

        updatePageUrlName(value) {
            var reg = /(?!^(\d+|[A-Za-z]+|[-]+)$)^[\w-]{3,64}$/
            var result = value.match(reg)
            if (result != null) {
                this.pageForm.data[this.currentPipeline].channel_common_info.url_name = value
            }
        },

        setChildEndTime(value) {
            if (value != null) {
                this.end_time = value
                this.publicPageForm.end_time = value // end_time为公共数据
            }
        },

        updatePCEndUrl(value) {
            this.pageForm.data[this.currentPipeline].channel_common_info.pc_end_url = value
        },

        updateMEndUrl(value) {
            this.pageForm.data[this.currentPipeline].channel_common_info.m_end_url = value
        },

        /**
         * 打开选择模版的弹窗
         */
        handleModelTempSelect (platform) {
            // D网适配
            if (this.site_group_code == 'dl' && platform == 'pc') {
                platform = 'web';
            }
            this.$refs.themeSelector.show({
                site_code: this.site_group_code,
                platform: platform,
                tpl_id: this.pageForm.tpl_id,
                pipeline_code: this.currentPipeline,
                lang_code: this.currentLanguage
            });
        },

        /**
         * 模版选择的回调
         * @param {string} platform 端  pc/wap/app/native/web/
         * @param {int} tpl_id 模版ID
         * @param {string} tpl_name 模版名字
         */
        handle_page_template_callback ({ platform, tpl_id, tpl_name }) {
            // 当前选中的语言 code
            let currentLanguage = this.currentLanguage;
            // 字段对照表
            const keyMap = {
                pc: { id: 'tpl_id', name: 'tpl_name' },
                web: { id: 'tpl_id', name: 'tpl_name' },
                wap: { id: 'm_tpl_id', name: 'm_tpl_name' },
                app: { id: 'app_tpl_id', name: 'app_tpl_name' },
                native: { id: 'native_tpl_id', name: 'native_tpl_name' },
            }
            // 更新字段
            this.pageForm[keyMap[platform].id] = tpl_id;
            this.pageForm[keyMap[platform].name] = tpl_name;
            this.pageForm.data[this.currentPipeline][currentLanguage][keyMap[platform].id] = tpl_id;
            this.pageForm.data[this.currentPipeline][currentLanguage][keyMap[platform].name] = tpl_name;
        }

    }
}
</script>

<style scoped lang="less">
   

</style>

<style lang="less">

//
.geshop-new-child-page {
    .el-form-item {
        margin-bottom: 0px;
    }
}

// 模版选择
.model-box {
    width: 50%;
    float: left;
    text-align: center;
    .el-radio {
        position: relative;
        max-width: 100%;
    }
    .el-radio__input {
        position: absolute;
        right: 20px;
        top: 36px;
    }
}
</style>
