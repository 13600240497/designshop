<template>
    <!-- 子活动列表 -->
    <div class="sub-page-list">

        <div class="main-page-pipelines-name" v-if="pipelines_name.length > 0">
            所属渠道：
            <a-tooltip>
                <template slot="title">
                    {{ pipelines_name.join(',') }}
                </template>
                {{ pipelines_name.slice(0, 2).join(',') }}...
            </a-tooltip>
        </div>

        <a-spin :spinning="loading">
            <ul>
                <li v-for="(item, index) in sub_page_list" :key="index">
                    <sub-page-item
                        :info="item"
                        :platform="platform"
                        :site="site"
                        :disabled="disabled"
                        @edit="handle_show_edit"
                        @convertPC="handle_show_canvertPC"
                        @convertWAP="handle_show_canvertWAP"
                        @show_dialog="handle_show_dialog"
                        @update="get_sub_page_list"
                        @onPreview="handle_show_preview" />
                </li>
            </ul>
        </a-spin>

        <!-- 新增编辑 -->
        <modal-edit
            ref="sub_page_edit"
            :allPipelines="main_page_pipelines"
            @onSuccess="get_sub_page_list">
        </modal-edit>

        <!-- PC转M -->
        <modal-convert-pc
            ref="sub_page_convert_pc"
            @onSuccess="handle_show_dialog">
        </modal-convert-pc>

        <!-- PC转M -->
        <modal-convert-wap
            ref="sub_page_convert_wap"
            @onSuccess="handle_show_dialog">
        </modal-convert-wap>

        <!-- PC转M成功 -->
        <modal-convert-success
            ref="sub_page_convert_success">
        </modal-convert-success>

        <!-- 查看普通页面所有的ID -->
        <modal-page-id
            ref="sub_page_id">
        </modal-page-id>

        <!-- 查看原生页面所有的ID -->
        <modal-native-id
            ref="sub_page_native_id">
        </modal-native-id>

        <!-- 子页面预览 -->
        <modal-preview
            ref="sub_page_preview">
        </modal-preview>

        <!-- 上线模块 -->
        <modal-online
            ref="sub_page_online"
            @onSuccess="get_sub_page_list">
        </modal-online>

        <!-- 下线模块 -->
        <modal-offline
            ref="sub_page_offline"
            @onSuccess="get_sub_page_list">
        </modal-offline>
    </div>
</template>

<script>

import bus from '../../../store/bus-index.js';

import {
    ZF_getPageList
} from '../../../plugin/api.js';

// 子页面列表item
import subPageItem from './sub-page-item.vue';

// 新增编辑
import modalEdit from './modal-sub-page-edit.vue';

// 查看所有页面ID
import modalPageId from'./modal-sub-page-ids.vue';
import modalNativeId from'./modal-sub-page-native-ids.vue';

// 页面预览
import modalPreview from './modal-sub-page-preview.vue';

// PC 转 M
import modalConvertPc from './modal-sub-page-convert-pc2m.vue';
import modalConvertWap from './modal-sub-page-convert-wap.vue';
import modalConvertSuccess from './modal-sub-page-convert-success.vue';

// 上下线
import modalOnline from './modal-sub-page-online.vue';
import modalOffline from './modal-sub-page-offline.vue';

export default {
    name: 'sub-page-list',
    props: {
        // 当前主活动页面ID
        'page-id': {
            type: Number,
            required: true,
        },
        // 当前站点 code
        site: {
            type: String,
            required: true,
        },
        // 当前选中的端， pc/wap/app/web
        platform: {
            type: String
        },
        // 当前选中的活动页，包含的应用端
        platform_list: {
            required: true
        },
        // 是否有全选可以操作
        disabled: {
            type: Boolean,
            default: false
        },
        // 当前选中的活动页面的所有渠道中文
        pipelines_name: {
            type: Array,
            default: function () {
                return [];
            }
        }
    },
    data () {
        return {
            loading: false,
            // 子页面的列表
            sub_page_list: [],
            // 当前主活动已勾选的渠道
            main_page_pipelines: [],
            // 当前登录信息
            loginInfo: {},

            // 子页面的权限列表
            activityPagePermission:{
                has_design_permission:0,
                special_permissions:[],
                all_special_permissions:{}
            },
        };
    },

    components: {
        subPageItem,
        modalEdit,
        modalConvertPc,
        modalConvertWap,
        modalConvertSuccess,
        modalPageId,
        modalNativeId,
        modalPreview,
        modalOnline,
        modalOffline
    },

    watch: {
        // 监听ID变更，获取子页面
        pageId (main_page_id) {
            this.get_sub_page_list(main_page_id);
        }
    },

    methods: {
        /**
         * 获取子页面的列表
         * @param {int} main_page_id 主页面ID
         */
        async get_sub_page_list (main_page_id = this.pageId) {
            if (main_page_id >= 1) {
                this.loading = true;
                const res = await ZF_getPageList({ activity_id: main_page_id });
                this.sub_page_list = res.data.page_list;
                this.main_page_pipelines = res.data.pipeline_list;
                // 子页面列表的权限数据，PC转M用到
                this.activityPagePermission = {
                    has_design_permission:res.data.has_design_permission,
                    special_permissions: res.data.special_permissions,
                    all_special_permissions: res.data.all_special_permissions
                }
                this.loading = false;
            } else {
                this.sub_page_list = [];
                this.loading = false;
            }
            // 在最前面追加1个，新增按钮
            if (!this.disabled) {
                this.sub_page_list.unshift({});
            }
        },

        /**
         * 打开弹窗
         * @param {string} name 模块名
         * @param {object} data 传参
         */
        handle_show_dialog ({ name, data = {}}) {
            this.$refs[name].show(data);
        },

        /**
         * 打开子页面编辑弹窗模块
         * @param {Object} item
         */
        handle_show_edit (item) {
            this.handle_check_auth().then(_ => {
                // 编辑
                if (item) {
                    this.$refs['sub_page_edit'].editPage({
                        version: item.version || 1,
                        activityId: this.pageId,
                        placeList: [],
                        is_lock: item.is_lock,
                        create_user: item.activity_create_user,
                        row: {
                            is_blog: item.is_blog, // 博客类型
                            url_name: item.url_name,
                            refresh_time: item.refresh_time,
                            tplId: item.tplId,
                            end_time: item.end_time,
                            id: item.id,
                            activity_id: item.activity_id,
                            refresh_time: item.refresh_time,
                            is_native: item.is_native, // 是否应用原生专题模式
                            is_redirect_country: item.is_redirect_country,
                        },
                        pipeline_list: this.main_page_pipelines,
                        platform: this.platform,
                        platform_list: this.platform_list,
                        group_languages: item.group_languages,
                    });
                }
                // 新增
                else {
                    this.$refs['sub_page_edit'].createPage({
                        activityId: this.pageId,
                        placeList: [],
                        row: {},
                        pipeline_list: this.main_page_pipelines,
                        platform: this.platform,
                        platform_list: this.platform_list
                    });
                }
            });
        },

        /**
         * 打开PC转M的模块
         */
        handle_show_canvertPC (item) {
            this.$refs['sub_page_convert_pc'].show(item.id, item.group_info, item.group_languages, item.group_id, item.activity_id, this.activityPagePermission);
        },

        /**
         * 打开M转APP的模块
         */
        handle_show_canvertWAP (item) {
            this.$refs.sub_page_convert_wap.show(item.id, item.group_info, item.group_languages, item.group_id, item.activity_id, this.activityPagePermission, this.platform);
        },

        /**
         * 打开页面预览弹窗
         * @param {Object} list 页面列表 [ zf: { lang_list: { en: xxx, fr: xxx } }];
         */
        handle_show_preview (list) {
            this.$refs.sub_page_preview.show(list);
        },

        /**
         * 检查权限
         * @param {Number} is_lock 当前活动是否已经锁定
         * @param {String} create_user 当前活动的创建人
         * @returns {Promise} reslove=reject=没有权限
         */
        handle_check_auth () {
            return new Promise((reslove, reject) => {
                if (this.disabled) {
                    this.$message.error('该活动已被创建者锁定，需创建者解锁后其他用户才能操作');
                } else {
                    reslove();
                }
            });
        },
    },

    created () {
        bus.$on('giveData', data => {
            this.loginInfo = data
        });
    }
}
</script>

<style lang="less" scoped>

// 主活动拥有的渠道（中文）
.main-page-pipelines-name {
    position: absolute;
    right: 20px;
    top: 16px;
    z-index: 1;
}

// 子活动列表
.sub-page-list {
    position: relative;
    margin-left: 24px;
    width: 100%;
    flex-shrink: 1;
    background: rgba(255, 255, 255, 0.4);
    min-height: 668px;
    box-sizing: border-box;

    ul {
        list-style: none;
        padding: 0px;
        margin: 0px;
        padding-left: 72px;
        padding-top: 52px;
        display: flex;
        flex-wrap: wrap;
        align-items: stretch;

        li {
            width: 290px;
            height: 272px;
            margin-right: 40px;
            margin-bottom: 36px;
            background-color: #fff;
            border-radius: 10px;
        }

        li:hover {
            box-shadow:0px 2px 20px 0px rgba(185,195,205,1);
        }
    }
}
</style>