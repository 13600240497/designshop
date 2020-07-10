<template>
    <el-dialog
        title="选择页面模板"
        width="800px"
        append-to-body
        :visible.sync="visible"
        :close-on-click-modal="false"
        :close-on-press-escape="false"
        class="geshop-page-template"
        @close="handle_close">

        <el-tabs
            type="border-card"
            class="model-dialog"
            v-model="global.type"
            v-loading="loading"
            @tab-click="handle_tab_change">

            <el-tab-pane label="我的模板" name="0" ref="tpl0">
                <template v-for="(item,index) in pageTemplateList">
                    <div
                        class="model-box"
                        v-if="item.create_user == current_username"
                        :key="index">
                        <el-radio
                            name="modelSelect"
                            :label="item.id"
                            v-model="modelInfo.modelSelect">
                            <div class="model-item">
                                <span>{{item.name}}</span>
                                <img :src="item.pic?item.pic:'/resources/images/default/picture.png'">
                                <div
                                    class="icon-geshop-search"
                                    @click="handleOpenPreview(item)">
                                </div>
                            </div>
                        </el-radio>
                    </div>
                </template>
            </el-tab-pane>

            <el-tab-pane label="共用模板" name="1" ref="tpl1">
                <template v-for="(item,index) in pageTemplateList">
                    <div
                        class="model-box"
                        v-if="item.create_user != current_username && item.tpl_type == 1"
                        :key="index">
                        <el-radio
                            name="modelSelect"
                            :label="item.id"
                            v-model="modelInfo.modelSelect">
                            <div class="model-item">
                                <span>{{item.name}}</span>
                                <img :src="item.pic?item.pic:'/resources/images/default/picture.png'">
                                <div class="icon-geshop-search" @click="handleOpenPreview(item)"></div>
                            </div>
                        </el-radio>
                    </div>
                </template>
            </el-tab-pane>
            <el-row v-if="pageTemplateList.length == 0|| pageTemplateList.length>0 && (modelInfo.tabActive == '2' && modelInfo.tempLength1 == 0 || modelInfo.tabActive == '1' && modelInfo.tempLength2 == 0)">
                <el-col :span="24" style="text-align: center;margin: 20px 0;">{{pageTemplateListWarn}}</el-col>
            </el-row>
        </el-tabs>

        <div slot="footer" class="dialog-footer">
            <el-button @click="handle_close">取 消</el-button>
            <el-button type="primary" @click="handle_confirm">确定</el-button>
        </div>

        <modal-preview ref="modalPreview"></modal-preview>
    </el-dialog>
</template>

<script>

import {
    ZF_getCountrySiteList,
    ZF_getPageTemplateList,
    ZF_getPageTemplateListNative,
} from '../../plugin/api.js';

import { getCookie } from '../../plugin/mUtils'
import modalPreview from './modal-preview.vue';

/**
 * 获取模版列表
 * @returns {Promise}
 */
const getPageTemplates = async ({
    place = 1,
    type,
    site_code,
    platform,
    lang_code
}) => {
    let params = {
        place: place,
        type: type,
        pageNo: 1,
        pageSize: 99999,
        site_code: site_code + '-' + platform,
        lang: lang_code,
    }
    if (platform == 'native') {
        return await ZF_getPageTemplateListNative(params);
    } else {
        return await ZF_getPageTemplateList(params)
    }
};

export default {
    components: {
        modalPreview,
    },

    data  () {
        return {
            loading: false,
            visible: false,
            // 公共查询参数
            global: {
                site_code: '',
                platform: '',
                lang: '',
                pipeline: '',
                place: 1, // 1=活动页, 2=首页
                type: '0', // 0=我的模版，1=公共模版
            },
            // 模版列表
            pageTemplateList: [],
            modelInfo: {
                visible: false,
                modelSelect: ''
            },
            /* 模板提示 */
            pageTemplateListWarn: '当前没有可用模板',
            // 页面模版预览
            viewModel: {
                visible: false,
                html: '',
                sideType: 'pc',
                sideWidth: '100%',
                src: ''
            },
            // 当前的渠道
            pipeline_code: '',
            // 当前的语言
            lang_code: '',
        }
    },

    computed: {
        current_username () {
            return this.$store.state.auth.username;
        }
    },

    methods: {

        /**
         * 打开弹窗
         * @param {string} site_code 站点编码
         * @param {string} platform 应用端 [pc/wap/app/native]
         * @param {int} tpl_id 当前选中的模版ID
         * @param {String} pipeline_code 渠道
         * @param {String} lang_code 语言
         */
        show ({site_code, platform, tpl_id, pipeline_code, lang_code, place = 1}) {

            this.global.place = place;
            this.global.site_code = site_code;
            this.global.platform = platform;
            this.global.pipeline = pipeline_code;
            this.global.lang = lang_code;

            // 获取数据
            this.loading = true;
            getPageTemplates(this.global).then(res => {
                this.loading = false;
                this.pageTemplateList = res.data.list;
                // ?????
                this.checkCurrentPageForm();
            });

            // 当前选中的ID
            this.visible = true;
            this.modelInfo.modelSelect = tpl_id;
        },
        
        /**
         * 模板选中数据回填
         * @returns {object} 
         */
        handle_confirm () {
            // 回填参数
            this.$emit('onSuccess', {
                pipeline_code: this.global.pipeline,
                lang_code: this.global.lang,
                platform: this.global.platform, // 当前选中的应用端
                tpl_id: this.modelInfo.modelSelect, // 选中的模版ID
                tpl_name: this.helper_get_tpl_name(this.modelInfo.modelSelect), // 选中的模版名字
            });
            // 关闭弹层
            this.visible = false;
        },

        /**
         * 获取模版名字
         * @param {int} tpl_id
         * @returns {string}
         */
        helper_get_tpl_name (tpl_id) {
            return this.pageTemplateList.filter(x => x.id == tpl_id)[0].name;
        },

        /**
         * 关闭弹层
         */
        handle_close () {
            this.visible = false;
        },

        /**
         * 切换模版类型
         */
        handle_tab_change () {
            getPageTemplates(this.global).then(res => {
                this.pageTemplateList = res.data.list;
                // ?????
                this.checkCurrentPageForm();
            });
        },

        /**
         * 页面模版预览
         * @param {String} pid 页面group_id
         * @param {String} id 模版ID
         */
        handleOpenPreview ({pid, id}) {
            this.$refs.modalPreview.show({
                pid,
                tpl_id: id,
                lang: this.global.lang_code,
                site_code: this.global.site_code,
                platform: this.global.platform
            });
        },

        /* 校验当前模板列表 */
        checkCurrentPageForm () {
            let pageTemplateList = this.pageTemplateList,
                tabActive = this.modelInfo.tabActive,
                tempLength1 = 0,
                tempLength2 = 0

            let pageTemplateListWarn = tabActive == '2' ? '您还没有自己的模板' : '暂无页面模板供使用'
            this.pageTemplateListWarn = pageTemplateListWarn

            pageTemplateList.forEach((item, index) => {
                if (item.create_user == this.current_username) {
                    tempLength1 += 1
                } else if (item.create_user != this.current_username && item.tpl_type == 1) {
                    tempLength2 += 1
                }
            });
            this.modelInfo.tempLength1 = tempLength1
            this.modelInfo.tempLength2 = tempLength2
        }
    }
}
</script>

<style lang="less">
.geshop-page-template {
    .el-tabs__content > .el-tab-pane {
        display: flex;
        flex-wrap: wrap;
    }
}
.geshop-page-template .el-tabs__content .model-box {
  width: 20%;
  text-align: left;
}
.geshop-page-template .el-tabs__content .model-box .model-item {
  width: 128px;
  height: 150px;
  border: 1px solid #E6E6E6;
  position: relative;
}
.geshop-page-template .el-tabs__content .model-box .el-radio__input {
  position: absolute;
  right: 4px;
  top: 18px;
}
.geshop-page-template .el-radio__input .el-radio__inner {
  width: 18px;
  height: 18px;
}
.geshop-page-template .el-radio__input.is-checked .el-radio__inner {
  background-color: #FFFFFF;
}
.geshop-page-template .el-radio__input.is-checked .el-radio__inner::after {
  width: 10px;
  height: 10px;
  border-radius: 5px;
  background-color: #1E9FFF;
}
.geshop-page-template .el-tabs__content .model-box .model-item>span {
  line-height: 17px;
  font-size: 12px;
  color: #333333;
  position: absolute;
  top: 10px;
  left: 10px;
  right: 15px;
  overflow: hidden;
  text-overflow:ellipsis;
  white-space: nowrap;
}
.geshop-page-template .el-tabs__content .model-box .model-item > img {
  width: 108px;
  height: 108px;
  margin-top: 30px;
  margin-left: 8px;
}
.geshop-page-template .el-dialog__footer {
  text-align: center;
  padding: 37px 0px 40px 0px !important;
}
.geshop-page-template .el-dialog__footer .el-button {
  width: 100px;
  height: 40px;
}
.geshop-page-template .model-item {
  position: relative;
}
.geshop-page-template .model-item:hover {
  border: 1px solid #1E9FFF !important;
}
.geshop-page-template .model-item .icon-geshop-search {
  display: none;
  text-align: center;
  line-height: 40px;
}
.geshop-page-template .model-item:hover .icon-geshop-search {
	display: block;
}
.geshop-page-template .icon-geshop-search {
  width: 40px;
  height: 40px;
  border-radius: 20px;
  background: #FFFFFF;
  position: absolute;
  top: 64px;
  left: 44px;
  font-size: 24px;
  z-index: 100;
}
.geshop-page-template .icon-geshop-search:hover {
  background: #1E9FFF;
  color:#FFFFFF;
}
.geshop-page-template .el-dialog__header .el-dialog__close,
.geshop-page-template .el-dialog__header .el-dialog__close:hover {
  font-size: 24px;
  color: #FFFFFF;
}
.geshop-page-template .el-radio__input.is-checked+.el-radio__label {
  color: #333333;
}

/* 页面模板弹窗结束 */

/* 查看模板大图开始 */
.geshop-template-model .el-dialog {
  margin-top: 0px !important;
  height: 100%;
}
.geshop-template-model .el-dialog .el-dialog__header {
	height: 64px;
	padding: 20px 0px 19px 40px;
	/* width: 100%; */
	font-size: 18px;
	background-color: #1A233B;
}
.geshop-template-model .el-dialog .el-dialog__header .el-dialog__title {
  color: #FFFFFF;
}
.geshop-template-model .el-dialog .el-dialog__body {
  height: 100%;
  /* width: 100%; */
}
.geshop-template-model .el-row {
  /* height: 100% !important; */
  height: 90% !important;
}
.geshop-template-model .el-dialog__header .el-dialog__close,
.geshop-template-model .el-dialog__header .el-dialog__close:hover {
  font-size: 24px;
  color: #FFFFFF;
}
/* 查看模板大图结束 */
</style>