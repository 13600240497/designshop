<template>
    <!-- M转APP start -->
    <el-dialog
        :title="title"
        :visible.sync="dialogConvertAPP"
        :close-on-click-modal="false"
        :close-on-press-escape="false">
        <el-form :model="convertForm" :rules="convertRules" ref="convertForm" class="home_add_piepeline pipe_tab_pane">
            <el-row>
                <el-col>
                    <span class="gb-label-title" style="margin: 0 0 15px;">被同步信息</span>
                </el-col>
            </el-row>

            <el-form-item label="被转化的渠道" prop="source_channelCurrent">
                <el-select
                    v-model="convertForm.source_channelCurrent"
                    clearable
                    placeholder="请选择"
                    @change="handleChangeConvertAppPipe">
                    <el-option
                        v-for="item in convertForm.wap_supportChannel"
                        :key="item.code"
                        :value="item.code"
                        :label="item.name">
                    </el-option>
                </el-select>
            </el-form-item>
            <!-- 被同步语言 -->
            <el-form-item label="被转化的语言页面" prop="source_langCurrent">
                <el-select v-model="convertForm.source_langCurrent" clearable placeholder="请选择"
                            v-if="convertForm.wap_supportChannel[convertForm.source_channelCurrent]">
                    <el-option v-for="item in convertForm.wap_supportChannel[convertForm.source_channelCurrent]['lang_list']"
                                :key="item.code"
                                :value="item.code"
                                :label="item.name"></el-option>
                </el-select>
            </el-form-item>

            <el-row>
                <el-col>
                    <span class="gb-label-title" style="margin: 0 0 15px;">转化到的页面内容</span>
                </el-col>
            </el-row>

            <el-form-item label="选择APP端活动" prop="activity_id" placeholder="请选择活动" v-if="convertForm.is_group==0">
                <el-select
                    v-model="convertForm.activity_id"
                    @change="getAppPages($event)">
                    <el-option
                        v-for="item in appActivities"
                        :label="item.name"
                        :value="item.id"
                        :key="item.id">
                    </el-option>
                </el-select>
            </el-form-item>

            <el-form-item label="选择活动子页面" prop="page_id" v-if="convertForm.is_group==0">
                <el-select
                    v-model="convertForm.page_id"
                    placeholder="请选择子页面"
                    @change="handleChangeConvertAppPges">
                    <el-option
                        v-for="item in appPages"
                        :label="item.title"
                        :value="item.id"
                        :key="item.id">
                    </el-option>
                </el-select>
            </el-form-item>
            <!-- 有关联+无关联 同步到的渠道-->
            <el-form-item label="渠道" prop="channelLang" class="geshop-gb-activities-place pipeline_list is-required">
                <el-tabs v-model="convertForm.target_channelCurrent">
                    <el-tab-pane v-for="(item,key) of convertForm.app_supportChannel" :label="item.name" :name="item.key"
                                    :key="item.key">
                        <el-form-item label="语言" prop="lang" class="geshop-new-activities-lang is-required"
                                        v-if="Object.keys(convertForm.target_channelLang).length > 0">
                            <el-checkbox v-model="convertForm.target_channelLang[item.key].check_all"
                                            :indeterminate="convertForm.target_channelLang[item.key].indeterminate"
                                            @change="handleConvertCheckAll($event,'convertForm',item)"
                                            style="margin-right:10px;" >全选</el-checkbox>
                            <el-checkbox-group v-model="convertForm.target_channelLang[key]['value']"
                                                @change="checked =>handleConvertChange(checked,item.key,'convertForm')"
                                                class="group_lang_list"
                                                style="display: inline-block">
                                <el-checkbox v-for="childItem of item.language" :label="childItem.key?childItem.key:childItem.lang"
                                                :key="childItem.key?childItem.key:childItem.lang">
                                    {{childItem.name?childItem.name:childItem.langName}}
                                </el-checkbox>
                            </el-checkbox-group>
                        </el-form-item>

                    </el-tab-pane>
                </el-tabs>
            </el-form-item>

            <el-row>
                <el-col>
                    <span class="gb-label-title" style="margin: 0 0 15px;">同步方式</span>
                </el-col>
            </el-row>
            <el-form-item label="选择方式">
                <el-radio-group v-model="convertForm.model">
                    <el-radio label="1">在所选子页面后追加</el-radio>
                    <el-radio label="2">覆盖子页面内容</el-radio>
                </el-radio-group>
                <el-alert title="此操作不会删除原APP活动页面内容，将转化过去的内容放至原有页面后面" type="warning" v-if="convertForm.model == 1"
                            :closable="false"></el-alert>
                <el-alert title="此操作直接覆盖所选子页面的原有装修效果，且不可还原，请慎用此选项。" type="warning" v-if="convertForm.model == 2"
                            :closable="false"></el-alert>
            </el-form-item>
            <el-form-item>
                <el-button @click="resetForm('convertForm')" size="small">取消</el-button>
                <el-button type="primary" @click="submitForm('convertForm')" size="small" :loading="submitLoading">确定
                </el-button>
            </el-form-item>
        </el-form>
    </el-dialog>
    <!-- M转APP end -->
</template>

<script>

import {
    ZF_getAppActivityList,
    ZF_checkPipelinePages,
    ZF_convertToAppPage
} from "../../../plugin/api.js";

import { getCookie,getHasPermissionChannel,clone_simple } from '../../../plugin/mUtils'

export default {
    data () {
        return {
            origin_platform: 'M',
            dialogConvertAPP: false,
            submitLoading: false,

            appActivities: [],
            appPages: [],

            convertForm: {
                id: '',
                activity_id: '',
                page_id: '',
                model: '1',
                is_group: 0,
                source_id: 0,
                target_id: 0,
                lang: '',
                wap_supportChannel: {},
                app_supportChannel: {},
                source_channelLang: {},
                source_channelCurrent: '',
                target_channelLang: {},
                app_target_channelCurrent: [],
                //单渠道下单语言选中
                source_langCurrent: '',
                group_platform_list: {},
                source_group_platform_list: {}
            },
            convertRules: {
                activity_id: [{
                    required: true,
                    message: '请选择活动',
                    trigger: 'change'
                }],
                page_id: [{
                    required: true,
                    message: '请选择页面',
                    trigger: 'change'
                }],
                source_channelCurrent: [{
                    required: true,
                    message: '请选择被转化渠道',
                    trigger: 'change'
                }],
                source_langCurrent: [{
                    required: true,
                    message: '请选择被转化的语言页面',
                    trigger: 'change'
                }],
                app_target_channelCurrent: [{
                    required: true,
                    message: '请选择同步渠道',
                    trigger: 'change'
                }],
            },
        }
    },

    computed: {
        title () {
            return `${this.origin_platform}转APP`;
        }
    },

    methods: {

        /**
         * 打开这个模块 M转APP
         * @param {String} origin_platform 来源设备端
         */
        show (id, group_info, group_languages, group_id, activity_id, activityPagePermission, origin_platform = 'M') {
            this.submitLoading = false;
            this.origin_platform = origin_platform.toUpperCase();

            // 初始化被转化渠道信息
            this.convertForm.wap_supportChannel = []
            this.convertForm.source_channelCurrent = ''

            // 异步获取被转化渠道信息
            this.checkAppPipelinePages(group_id, activity_id)

            // 不同端下的渠道信息
            this.convertForm.wap_group_languages = group_languages

            // m转app看是否存在wap字段，有则说明存在关联关系 wap: {ZF: {page_id: 158}, ZFES: {page_id: 159}, ZFDE: {page_id: 160}}
            // 同步到的渠道信息id值，用做勾选同步到的渠道筛选取出相应id值
            let group_platform_list = group_info.platform_list.app ? group_info.platform_list.app : ''

            // m转app源信息 wap: {ZF: {page_id: 155}, ZFES: {page_id: 156}, ZFDE: {page_id: 157}}
            // origin_platform 来源端
            let source_group_platform_list = group_info.platform_list[origin_platform] || '';

            // 同步到的渠道信息 app: {ZF: {key: "ZF", name: "全球站", page_id: "158",…}, ZFES: {key: "ZFES", name: "西班牙站", page_id: "159",…},…}
            let app_source_group_platform_list = group_languages.app ? getHasPermissionChannel(group_languages.app, activityPagePermission.all_special_permissions.app) : ''

            // 2019/07 初始化同步的空白渠道语言列表
            this.convertForm.target_channelLang = this.channelLangInit(app_source_group_platform_list);
            this.convertForm.target_channelCurrent = Object.keys(app_source_group_platform_list)[0];

            this.convertForm.id = id
            this.convertForm.activity_id = ''
            this.convertForm.page_id = ''
            this.convertForm.model = '1'
            this.convertForm.wap_target_channelCurrent = []

            // 如果有app端关联，则不需要手动选择主活动和子页面
            if (group_platform_list) {
                if(Object.keys(app_source_group_platform_list).length === 0){
                    this.$message.error('无同步渠道权限')
                    return false;
                }

                // 同步到的渠道信息
                this.convertForm.app_supportChannel = app_source_group_platform_list

                // this.convertForm.group_platform_list = group_platform_list

                this.convertForm.source_group_platform_list = source_group_platform_list

                // 2019/07 source target 下渠道page_id {ZF: {page_id: 155}, ZFES: {page_id: 156}, ZFDE: {page_id: 157}}
                this.convertForm.source_id = source_group_platform_list;
                this.convertForm.target_id = group_platform_list;

                this.convertForm.is_group = 1

            } else {
                this.convertForm.is_group = 0
                // 2019/07 更新渠道下page_id
                this.convertForm.source_id = source_group_platform_list;
                this.convertForm.target_id = 0;

                this.ZF_getAppActivityList()
            }

            this.dialogConvertAPP = true
        },

        /**
         * 获取活动页的列表信息
         */
        async ZF_getAppActivityList() {
            let res = await ZF_getAppActivityList({})

            if (res.code == 0) {
                this.appActivityList = res.data

                this.getAppActivities()
                // 选中第一个活动
                this.convertForm.activity_id = this.appActivityList[0].id;
                this.getAppPages(this.appActivityList[0].id)
            }
        },

        getAppPages(val) {
            let list = []
            let languages = []
            let activityIndex = 0 // 选中的活动序列

            this.convertForm.page = ''
            this.convertForm.page_id = ''
            this.convertForm.app_supportChannel = []
            this.appActivityList.forEach(function (element,key) {
                if (element.id == val) {
                    activityIndex = key;
                    element.page_ist.forEach(function (page, index) {
                        list.push({
                            id: page.group_id,
                            title: page.page_title,
                            pipeline_list: page.pipeline_list
                        })
                        // if (index == 0) {
                        // 	page.langList.forEach(function (lang) {
                        // 		languages.push({
                        // 		key: lang.key,
                        // 		name: lang.name
                        // 		})
                        // 	})
                        // }
                    })
                }
            })

            // this.convertLangs = languages
            this.appPages = list

            // 生成同步渠道语言列表信息 wap_supportChannel
            if(list && list.length > 0 ){
                let first_page_pipelist = list[0].pipeline_list
                let channel_pipeline_list = {}
                /* 获取wap_supportChannel {'ZF':'key':'ZF','name':'全球站','page_id':'111','language':{'en':{},'fr':{}}} */
                function getSupportChannelFromPipeline(first_page_pipelist){
                    first_page_pipelist.forEach(item =>{
                        let lang_list = {}
                        item.lang_list.forEach(aLang =>{
                            lang_list[aLang.code] = {
                                lang: aLang.code,
                                langName: aLang.name
                            }
                        })
                        channel_pipeline_list[item.code] = {
                            key: item.code,
                            name: item.name,
                            page_id: item.page_id,
                            language: lang_list
                        }
                    })
                    return channel_pipeline_list
                }
                // 同步到的渠道信息赋值
                this.convertForm.app_supportChannel = getSupportChannelFromPipeline(first_page_pipelist)
                // 选择同步活动 生成被转化target渠道列表
                this.convertForm.target_channelLang = this.channelLangInit(first_page_pipelist);
                this.convertForm.target_channelCurrent = first_page_pipelist[0] ? first_page_pipelist[0].code : '';
            }
        },

        getAppActivities() {
            let list = []

            this.appActivityList.forEach(function (element) {
                list.push({
                    id: element.id,
                    name: element.name
                })
            })

            this.appActivities = list
        },

        /**
         * 2019/07
         * M转APP
         */
        handleChangeConvertAppPipe(val) {
            if(val){
                this.convertForm.source_channelCurrent = val
                let langList = this.convertForm['wap_supportChannel'][val].lang_list
                this.convertForm.source_langCurrent = Object.keys(langList)[0];
            }
        },

        // 转化为APP端活动 选择活动子页面筛选同步渠道信息
        handleChangeConvertAppPges(id) {
            let appPages = this.appPages
            let target_id = {}
            appPages.forEach( item =>{
                if( id === item.id){
                    item.pipeline_list.forEach( aPipe =>{
                        target_id[aPipe.code] = {
                            page_id: aPipe.page_id
                        }
                    })
                }
            })
            this.convertForm.target_id = target_id
        },

        /**
         * M转APP - 异步获取被转化渠道信息
         * @param group_id
         * @param activity_id
         */
        async checkAppPipelinePages(group_id, activity_id) {
            let params = {
                    group_id: group_id,
                    activity_id: activity_id
                },
                res = await ZF_checkPipelinePages(params)

            if (res.code == 0) {
                if (res.data.length == 0) {
                    this.$message.error('被转化渠道没有装修页面');
                } else {
                    // 被转化的渠道信息
                    this.convertForm.wap_supportChannel = res.data
                    // 2019/07 选中第一个渠道及渠道下第一个语言
                    this.handleChangeConvertAppPipe(Object.keys(res.data)[0])
                }

            } else {
                this.$message.error(res.message)
            }
        },

        /**
         * 2019/07
         * 生成空白渠道数据对象
         * @param dataFrom
         */
        channelLangInit (dataFrom) {
            let channelLangList = dataFrom;
            let channelLang = {};
            if(Object.prototype.toString.call(dataFrom) === '[object Object]'){
                Object.keys(channelLangList).forEach((element) => {
                    channelLang[element] = {
                        check_all: false,
                        check_indeterminate: false,
                        value: []
                    };
                });
            }else if(Object.prototype.toString.call(dataFrom) === '[object Array]'){
                channelLangList.forEach((element) => {
                    channelLang[element.code] = {
                        check_all: false,
                        check_indeterminate: false,
                        value: []
                    };
                });
            }

            return channelLang;
        },

        /**
         * 2019/07/09
         * PC转M，M转App 端下渠道全选
         * @param value true|false
         * @param formName  'convertForm_M' | 'convertForm'
         * @param item 渠道对象
         * @return {boolean}
         */
        handleConvertCheckAll(value,formName,item){
            if(!formName){return false;}
            let $form = this[formName];
            let target_supportChannel = formName === 'convertForm_M' ? $form['wap_supportChannel'] : $form['app_supportChannel'];
            let {target_channelLang} = $form;
            let pipe_langs = Object.keys(target_supportChannel[item.key].language);
            if(value){
                target_channelLang[item.key] = {
                    check_all: true,
                    indeterminate: false,
                    value: pipe_langs
                }
            }else{
                target_channelLang[item.key] = {
                    check_all: false,
                    indeterminate: false,
                    value: []
                }
            }
        },

        /**
         * 2019/07/09
         * PC转M M转App 渠道checkbox change
         * @param checked
         * @param pipeCode
         * @param type ['add' | 'edit']
         * @return {boolean}
         */
        handleConvertChange(checked,pipeCode,formName){
            if(!pipeCode ||　!formName){
                return false;
            }
            /* arr1 中是否存在arr2所有元素*/
            function _hasAll(arr1, arr2) {
                if(arr1 && arr1.length > 0 && arr2 && arr2.length > 0){
                    return arr2.every(val => arr1.includes(val));
                }else{
                    return false;
                }

            }

            let $form = this[formName];
            let target_supportChannel = formName === 'convertForm_M' ? $form['wap_supportChannel'] : $form['app_supportChannel'];
            let {target_channelLang} = $form;
            let pipe_langs = Object.keys(target_supportChannel[pipeCode].language);
            let clone_target_channelLang = clone_simple(target_channelLang);
            if(_hasAll(checked,pipe_langs)){
                clone_target_channelLang[pipeCode].check_all = true;
                clone_target_channelLang[pipeCode].indeterminate = false;
            }else{
                clone_target_channelLang[pipeCode].check_all = false;
                clone_target_channelLang[pipeCode].indeterminate = checked.length > 0 ? true : false;
            }
            this.$set(this[formName],'target_channelLang',clone_target_channelLang)
        },

        /**
         * 表单提交
         */
        async submitForm (formName) {

            this.submitLoading = true

            this.$refs[formName].validate(async (valid) => {
                if (valid) {
                    let params, res
                    let activityFormObj = {}
                    let platformData = {}
                    let pipeline_list = {}

                    // M转APP
                    if (formName == 'convertForm') {
                        params = {
                            model: this.convertForm.model
                        }

                        // source
                        let source = {}
                        let target = {}

                        // 2019/07
                        let $form = this.convertForm;
                        let source_id = $form.source_id;
                        let target_id = $form.target_id;
                        let source_channelLang = $form.source_channelLang,
                            target_channelLang = $form.target_channelLang,
                            source_channel_keys = Object.keys(source_channelLang),
                            target_Channel_keys = Object.keys(target_channelLang),
                            source_channelLangObj = {},
                            target_channelLangObj = {},
                            source_langCurrent = $form.source_langCurrent
                        // 有关联关系
                        if (this.convertForm.is_group == 1) {

                            // 被转化的渠道信息
                            let sourceChannelList = this.convertForm.wap_supportChannel
                            // 被转化渠道当前选中code值
                            let source_channelCurrent = this.convertForm.source_channelCurrent

                            // 同步到的渠道信息
                            let targetChannelList = this.convertForm.app_supportChannel
                            // 同步到的渠道当前选中数组
                            let targetChannelCurrentList = this.convertForm.app_target_channelCurrent

                            // let group_pipelineList = this.convertForm_M.group_platform_list
                            let source_group_pipelineList = this.convertForm.source_group_platform_list

                            let targetPipelineObj = {}
                            let sourcePipelineArr = []

                            // 通过被选中渠道code值筛选
                            // key - ZF, ZFDE
                            Object.keys(sourceChannelList).forEach(key => {
                                if (key === source_channelCurrent) {
                                    targetPipelineObj = sourceChannelList[key]
                                }
                            })

                            // 通过被选中同步渠道值筛选数据
                            // key - ZF, ZFDE
                            // item - ZF, ZFDE
                            Object.keys(targetChannelList).forEach(key => {
                                targetChannelCurrentList.forEach(item => {
                                    if (key === item) {
                                        sourcePipelineArr.push(targetChannelList[item])
                                    }
                                })

                            })

                            // let lang = Object.keys(targetPipelineObj.lang_list)[0]
                            source[source_channelCurrent] = {
                                id: source_group_pipelineList[source_channelCurrent]['page_id'],
                                lang: source_langCurrent
                            }

                            /*sourcePipelineArr.forEach((item) => {
                                target[item.key] = {
                                    id: item.page_id,
                                    lang_list: Object.keys(item.language)
                                }
                            })*/
                            target_Channel_keys.forEach((item, index) => {
                                if (target_channelLang[item].value.length > 0) {
                                    target_channelLangObj[item] = target_channelLang[item].value;
                                    target[item] = {
                                        id: target_id[item].page_id,
                                        lang_list: target_channelLang[item].value
                                    };
                                }
                            });

                            params.source = JSON.stringify(source)
                            params.target = JSON.stringify(target)
                        }
                        // 无关联关系
                        else {
                            // 被同步渠道信息
                            let wap_group_languages = this.convertForm.wap_group_languages
                            // 被同步渠道key值
                            let source_channelCurrent = this.convertForm.source_channelCurrent

                            // 同步到的渠道总信息
                            let app_supportChannel = this.convertForm.app_supportChannel
                            // 选中的同步到的渠道key值
                            let app_target_channelCurrent = this.convertForm.app_target_channelCurrent

                            let current_wap_group_languages = wap_group_languages['wap'][source_channelCurrent]['language']
                            // let s_lang = Object.keys(current_wap_group_languages)[0]
                            let s_id = current_wap_group_languages[source_langCurrent]['page_id']
                            source[source_channelCurrent] = {
                                id: s_id,
                                lang: source_langCurrent
                            }

                            // 2019/07 遍历target_channellang 语言列表
                            target_Channel_keys.forEach((item, index) => {
                                if (target_channelLang[item].value.length > 0) {
                                    target_channelLangObj[item] = target_channelLang[item].value;
                                    target[item] = {
                                        id: target_id[item].page_id,
                                        lang_list: target_channelLang[item].value
                                    };
                                }
                            });

                            params.source = JSON.stringify(source)
                            params.target = JSON.stringify(target)

                        }
                        // 转化语言必选提示
                        if (!$form.source_langCurrent || Object.keys(target_channelLangObj).length <= 0) {
                            this.$message.error('请选择转化到的页面内容');
                            this.submitLoading = false;
                            return false;
                        }

                        res = await ZF_convertToAppPage(params)

                        if (res.code == 0) {
                            this.convertUrl = res.data.redirectUrl;
                            this.dialogConvertAPP = false;
                            // 打开成功的弹窗
                            this.$emit('onSuccess', {
                                name: 'sub_page_convert_success',
                                data: {
                                    url: res.data.redirectUrl,
                                    type: 1,
                                }
                            });
                        } else {
                            this.convertUrl = ''
                        }
                        this.submitLoading = false;
                    }
                    this.isDetailActive = false
                } else {
                    this.submitLoading = false
                }
            })
        },

        resetForm (formName) {
            this.resetFields(formName)
            this.dialogConvertAPP = false;
        },

        resetFields (formName) {
            this.$refs[formName].resetFields()
        },
    }
}
</script>


<style lang="less" scoped>
.gb-label-title {
    font-weight: bold;
    font-size: 16px;
    margin-top: 10px;
    display: block;
    margin: 0px 0px 15px;
    margin-bottom: 0px;
}
</style>