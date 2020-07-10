<template>
    <design-dialog
        :width="900"
        :title="dialogTitle"
        :confirmLoading="loading"
        :visible="visible"
        @isOk="handle_confirm"
        @isCancel="handle_cancel">

        <!-- 加载动画 -->
        <a-spin :spinning="loading">
        <div class="dialog-home-page-edit">
            <el-tag type="danger" v-if="has_new_pipeline">
                有新增渠道信息请补充完整
            </el-tag>
            <el-form>
                <el-form-item label="页面名称" prop="title" class="is-required">
                    <el-input
                        type="text"
                        v-model="form.title"
                        maxlength="100"
                        placeholder="请输入页面名称"
                        show-word-limit>
                        <i class="el-input__icon" slot="suffix">{{form.title.length}}/100</i>
                    </el-input>
                </el-form-item>
                
                <!-- 遍历渠道 -->
                <el-tabs type="border-card" v-model="current_pipeline_code">

                    <!-- 编辑时显示该首页支持的语言 -->
                    <el-tab-pane
                        v-for="pipeline_item in support_pipelines"
                        :key="pipeline_item.code"
                        :label="pipeline_item.name"
                        :name="pipeline_item.code">

                        <!-- 遍历语言 -->
                        <el-tabs v-model="pipeline_item.current_lang">
                            <el-tab-pane
                                v-for="lang_item in pipeline_item.lang_list"
                                :key="lang_item.code"
                                :label="lang_item.name"
                                :name="lang_item.code">

                                <div v-if="is_edit == false">
                                    <el-button
                                        @click="handleModelTempSelect(pipeline_item.code, lang_item.code, 'pc', lang_item.tpl_id)"
                                        size="small"
                                        type="primary">
                                        选择PC模板
                                        <template v-if="lang_item.tpl_name">
                                            （已选择：{{ lang_item.tpl_name }}）
                                        </template>
                                    </el-button>
                                    <el-button
                                        @click="handleModelTempSelect(pipeline_item.code, lang_item.code, 'wap', lang_item.m_tpl_id)"
                                        size="small"
                                        type="primary"
                                        v-if="site != 'dl'">
                                        选择M模板
                                        <template v-if="lang_item.m_tpl_name">
                                            （已选择：{{ lang_item.m_tpl_name }}）
                                        </template>
                                    </el-button>
                                </div>

                                <el-row :gutter="14">
                                    <el-col :span="12">
                                        <el-form-item label="SEO标题" class="is-required" style="margin-bottom: 0px">
                                            <el-input
                                                placeholder="有利于SEO优化"
                                                v-model="lang_item.seo_title">
                                            </el-input>
                                            <!-- <span class="count-tip-box">{{ lang_item.seo_title.length }}/200</span> -->
                                        </el-form-item>
                                    </el-col>
                                    <el-col :span="12">
                                        <el-form-item label="SEO关键字" style="margin-bottom: 0px">
                                            <el-input
                                                placeholder="有利于SEO优化"
                                                v-model="lang_item.keywords">
                                            </el-input>
                                            <!-- <span class="count-tip-box">{{ lang_item.keywords.length }}/200</span> -->
                                        </el-form-item>
                                    </el-col>
                                    
                                </el-row>

                                <el-form-item label="SEO 简介">
                                    <el-input
                                        :rows="4"
                                        placeholder="有利于SEO优化"
                                        type="textarea"
                                        v-model="lang_item.description">
                                    </el-input>
                                    <!-- <span class="count-tip-box">{{ lang_item.description.length }}/200</span> -->
                                </el-form-item>
                            </el-tab-pane>
                        </el-tabs>
                    </el-tab-pane>
                </el-tabs>
            </el-form>
        </div>
        </a-spin>

        <!-- 页面模版选择 -->
        <theme-selector
            ref="themeSelector"
            @onSuccess="handle_page_template_callback">
        </theme-selector>

    </design-dialog>
</template>

<script>

import {
    ZF_getCountrySiteList,
    ZF_addIndex,
    ZF_editIndex,
} from '../../../plugin/api.js';

// 模版选择的弹窗
import themeSelector from '../../../components/modal-page-template/index.vue';

/**
 * 应用端数据对象转数组
 * 1. 转数组
 * 2. 根据paltform传参规则，处理数据
 * 2.1 paltform=all，所有端数据并集
 * @param {Object} obj 原数据
 * @param {String} platform 根据设备端获取对应的渠道数据，默认=all=所有渠道，可选值 [all/pc/wap/app/web]
 * @returns {Array}
 */
const convert_pipelines_to_array = (obj, prop_platform = 'all') => {
    const all_pipelines = [];
    const all_pipelines_code = [];
    // 遍历端
    Object.keys(obj).map(platform => {
        // 过滤规则
        if (prop_platform != 'all' && prop_platform != platform) return false;

        // 遍历渠道
        const pipelines = obj[platform];
        Object.keys(pipelines).map(code => {
            const pipeline = pipelines[code];
            // 是否是否已经包含了
            if (all_pipelines_code.includes(code) == false) {
                all_pipelines_code.push(code);
                const new_pipeline = {
                    page_id: '',
                    code,
                    name: pipeline.name,
                    lang_list: Object.keys(pipeline.lang_list).map(langCode => {
                        const lang = pipeline.lang_list[langCode];
                        return {
                            code: lang.code,
                            name: lang.name,
                            seo_title: '',
                            keywords: '',
                            description: '',
                            tpl_id: '',
                            tpl_name: '',
                            m_tpl_id: '',
                            m_tpl_name: '',
                        }
                    }),
                };
                // 默认选中第一个
                new_pipeline.current_lang = new_pipeline.lang_list[0].code;
                all_pipelines.push(new_pipeline);
            }
        });
    });
    return all_pipelines;
}

export default {
    data () {
        return {
            is_edit: false,
            loading: false,
            visible: false,
            version: 1, // 数据版本
            // 是否有新增渠道
            has_new_pipeline: false,
            form: {
                title: '',
                place: [],
                keywords: '',
                description: '',
                seo_title: '',
            },
            // 支持的渠道
            support_pipelines: [],
            // 当前选中的渠道code
            current_pipeline_code: '',
        }
    },

    computed: {
        // 当前站点
        site () {
            return this.$store.state.home.current_site;
        },
        // 当前站点支持的终端
        site_support_platforms () {
            return this.$store.state.site.platforms;
        },
        dialogTitle () {
            return this.is_edit ? '编辑首页' : '新增首页';
        },
        // 当前选中的终端
        current_platform () {
            return this.$store.state.home.current_platform;
        }
    },

    components: {
        themeSelector
    },

    methods: {
        /**
         * 展示弹窗
         * @param {Object} data 传入的页面数据，用于判断【新增/编辑】模式
         */
        async show (data = null) {
            // 相同的处理
            this.form.place = this.site_support_platforms.map(x => x.code);

            // 获取国家站的数据
            const remote_pipelins = await ZF_getCountrySiteList({ activity_type: 2 });

            // 新增模式
            if (data == null) {
                this.is_edit = false;
                this.form.title = '';
                this.version = 1;
                
                // 新增模式，获取所有端的国家站数据
                this.support_pipelines = convert_pipelines_to_array(remote_pipelins.data.support_pipelines, 'all');
                this.current_pipeline_code = this.support_pipelines[0].code;

                // 国家站数据的初始化
                this.support_pipelines.map(pipeline => {
                    pipeline.page_id = '';
                    pipeline.lang_list.map(lang => {
                        lang.seo_title = '';
                        lang.keywords = '';
                        lang.description = '';
                        lang.tpl_id = '';
                        lang.tpl_name = '';
                        lang.m_tpl_id = '';
                        lang.m_tpl_name = '';
                    });
                });
            }
            // 编辑模式
            else {
                // 编辑模式，只获取当前设备端的国家站数据
                this.support_pipelines = convert_pipelines_to_array(remote_pipelins.data.support_pipelines, this.current_platform);
                this.current_pipeline_code = this.support_pipelines[0].code;

                this.is_edit = true;
                this.has_new_pipeline = data.has_new_pipeline;
                // 标题
                this.form.title = data.page_languages[0].title || '';
                this.version = data.version || 1;
                // 渠道里面的参数
                this.support_pipelines.map(pipeline => {
                    // 如果数据里面有这个渠道数据
                    if (data.group_languages[pipeline.code]) {
                        pipeline.page_id = data.group_languages[pipeline.code].page_id;
                        pipeline.lang_list.map(lang => {
                            lang.seo_title = data.group_languages[pipeline.code]['lang_list'][lang.code].seo_title || '';
                            lang.keywords = data.group_languages[pipeline.code]['lang_list'][lang.code].keywords || '';
                            lang.description = data.group_languages[pipeline.code]['lang_list'][lang.code].description || '';
                            lang.tpl_id = data.group_languages[pipeline.code]['lang_list'][lang.code].tpl_id || '';
                            lang.tpl_name = data.group_languages[pipeline.code]['lang_list'][lang.code].tpl_name || '';
                            lang.m_tpl_id = data.group_languages[pipeline.code]['lang_list'][lang.code].tpl_id || '';
                            lang.m_tpl_name = data.group_languages[pipeline.code]['lang_list'][lang.code].tpl_name || '';
                        });
                    } else {
                        pipeline.page_id = '';
                    }
                });
            }
            this.visible = true;
        },

        /**
         * 选择页面模版
         */
        handleModelTempSelect (pipeline_code, lang_code, platform, tpl_id) {
            // D网适配
            if (this.site == 'dl' && platform == 'pc') {
                platform = 'web';
            }
            this.$refs.themeSelector.show({
                place: 2,
                site_code: this.site,
                platform: platform,
                tpl_id: tpl_id,
                pipeline_code: pipeline_code,
                lang_code: lang_code
            });
        },

        /**
         * 选择模版后的回调
         * @param {string} platform 端  pc/wap/app/native/web/
         * @param {int} tpl_id 模版ID
         * @param {string} tpl_name 模版名字
         */
        handle_page_template_callback ({ pipeline_code, lang_code, platform, tpl_id, tpl_name }) {
            this.support_pipelines.map(pipeline => {
                if (pipeline.code != pipeline_code) return false;
                pipeline.lang_list.map(lang => {
                    if (lang.code != lang_code) return false;
                    // 匹配到对应的数据修改
                    if (['web', 'pc'].includes(platform)) {
                        lang.tpl_name = tpl_name;
                        lang.tpl_id = tpl_id;
                    } else {
                        lang.m_tpl_name = tpl_name;
                        lang.m_tpl_id = tpl_id;
                    }
                });
            });
        },

        /**
         * 保存事件，通过判断执行【新增/编辑】函数
         * 因为2个接口提交的参数数据结构不一致的！！！
         */
        handle_confirm () {
            this.loading = true;
            this.is_edit ? this.handle_page_update() : this.handle_page_create();
        },

        /**
         * 新增页面
         */
        handle_page_create () {
            // 构造新增页面的传参
            const request = {};
            this.site_support_platforms.map(platform => {
                request[platform.code] = {};
                this.support_pipelines.map(pipeline => {
                    request[platform.code][pipeline.code] = {
                        page_id: this.form.id || "",
                        title: this.form.title,
                        default_lang: pipeline.lang_list[0].code,
                        list: {}
                    };
                    pipeline.lang_list.map(lang => {
                        request[platform.code][pipeline.code]['list'][lang.code] = {
                            // 模版选择，根据不同的应用端，取不同的值。统一传给后端的字段。
                            tpl_id: ['web','pc'].includes(platform.code) ? lang.tpl_id : lang.m_tpl_id,
                            tpl_name: ['web','pc'].includes(platform.code) ? lang.tpl_name : lang.m_tpl_name,
                            // 如果没填，默认取第一个渠道里面的第一个语言的值
                            seo_title: lang.seo_title || this.support_pipelines[0].lang_list[0].seo_title,
                            keywords: lang.keywords,
                            description: lang.description
                        }
                    });
                });
                request[platform.code] = JSON.stringify(request[platform.code]);
            });
        
            // AJAX
            ZF_addIndex(request).then(result => {
                this.loading = false;
                if (result.code == 0) {
                    this.visible = false;
                    this.$emit('onSuccess');
                }
            });
        },

        /**
         * 更新页面
         */
        handle_page_update () {
            // 构造编辑页面的传参
            const request = {
                site_code: `${this.site}-${this.$store.state.home.current_platform}`,
                version: this.version
            };
            // 遍历渠道
            request['pipeline_list'] = {};
            this.support_pipelines.map(pipeline => {
                request['pipeline_list'][pipeline.code] = {
                    title: this.form.title,
                    default_lang: pipeline.lang_list[0].code,
                    list: {},
                    page_id: pipeline.page_id,
                };
                pipeline.lang_list.map(lang => {
                    request['pipeline_list'][pipeline.code]['list'][lang.code] = {
                        page_id: pipeline.page_id,
                        // 如果没填，默认取第一个渠道里面的第一个语言的值
                        seo_title: lang.seo_title || this.support_pipelines[0].lang_list[0].seo_title,
                        keywords: lang.keywords,
                        description: lang.description
                    }
                });
            });
            request['pipeline_list'] = JSON.stringify(request['pipeline_list']);

            ZF_editIndex(request).then(result => {
                this.loading = false;
                if (result.code == 0) {
                    this.visible = false;
                    this.$emit('onSuccess');
                }
            });
        },

        /**
         * 取消
         */
        handle_cancel () {
            this.visible = false;
        },
    },

    async created () {
        
    }
}
</script>