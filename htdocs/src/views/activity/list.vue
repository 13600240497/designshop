<template>
    <div id="page-activity" style="background: #F0F2F5;">

        <!-- 筛选项 -->
        <list-filter
            ref="filter"
            :site="site"
            :platform="platform"
            :pageNo="main_page_pagination.current"
            :pageSize="main_page_pagination.pageSize"
            @showDialog="handle_show_dialog"
            @beforeResponse="handle_loading"
            @response="handle_get_main_page">
        </list-filter>

        <!-- 端切换 -->
        <a-tabs
            class="main-page-device-tabs"
            v-model="platform"
            @change="handle_device_tab_change">
            <a-tab-pane
                v-for="item in site_platform_list"
                :tab="item.name"
                :key="item.code">
            </a-tab-pane>
        </a-tabs>

        <!-- 加载动画 -->
        <a-spin :spinning="loading">
            
            <div class="page-list-layout">

                <!-- 主活动列表 -->
                <div class="main-page-list">
                    <ul>
                        <li
                            v-for="item in main_page_list"
                            :key="item.id"
                            :class="{ 'active': main_page_selected_id == item.id, 'is_frequently': item.is_frequently == 1 }"
                            @click="handle_select_main_page(item)">
                            <span class="main-page-id">{{item.id}}</span>
                            <span class="main-page-title">{{item.name}}</span>
                            <span class="main-page-user">{{item.create_name}} {{ item.create_time | moment('YYYY-MM-DD HH:mm:ss') }}</span>

                            <!-- 更多选项 -->
                            <main-page-more-actions
                                :id="item.id"
                                :is_frequently="item.is_frequently"
                                :is_lock="item.is_lock"
                                :disabled="!handle_check_auth(item.is_lock, item.create_user)"
                                @showDialog="handle_show_dialog"
                                @deleteCallback="handle_search" 
                                @onLock="handle_lock"
                                @onEdit="handle_show_eidt"/>
                        </li>
                    </ul>

                    <!-- 主活动列表的分页 -->
                    <a-pagination
                        showQuickJumper
                        :current="main_page_pagination.current"
                        :total="main_page_pagination.total"
                        @change="handle_page_change" />
                </div>

                <!-- 子活动页面 -->
                <sub-page-list
                    ref="subPageList"
                    :page-id="main_page_selected_id"
                    :platform="platform"
                    :site="site"
                    :platform_list="main_page_selected_platform_list"
                    :disabled="!handle_check_auth(this.main_page_selected_is_lock, this.main_page_selected_create_user)"
                    :pipelines_name="main_page_selected_pipelines_name" />

                <!-- 活动页编辑 -->
                <modal-main-page-edit
                    ref="main_page_edit"
                    :site="site"
                    @success="handle_edit_success" />

                <!-- 更新头尾 -->
                <modal-update-head
                    ref='updateHead'
                    :platform="platform">
                </modal-update-head>
                
            </div>
            
        </a-spin>

    </div>
</template>

<script>

import bus from '../../store/bus-index.js';
import { getCookie, getHasPermissionChannel,clone_simple } from '../../plugin/mUtils'

import {
    ZF_getCountrySiteList,
    ZF_lockingActivity
} from '../../plugin/api.js'

// 筛选组件
import listFilter from './unit/list-filter.vue';

// 主页的更多操作菜单
import mainPageMoreActions from './unit/main-page-more-actions.vue';

// 子页面列表
import subPageList from './unit/sub-page-list.vue';

// 新增/编辑主页的弹窗
import modalMainPageEdit from './unit/modal-main-page-edit.vue';

// 一键更新
import modalUpdateHead from './unit/modal-udpate-head.vue';

export default {
    data () {
        return {
            // 当前端，pc/wap/app/web
            platform: '',
            // 当前站点拥有的应用端列表
            site_platform_list: [],

            // 当前站点拥有的渠道
            site_support_pipelines: {},

            loading: false,
            // 站点编码
            site: '',
            // 主页列表
            main_page_list: [],
            // 主分页的页码
            main_page_pagination: {
                current: 1,
                pageSize: 10,
                total: 1
            },
            // 当前选中活动页数据
            main_page_selected_id: -1, // 主活动页面的ID
            main_page_selected_is_lock: 0, // 主活动页面的状态是否锁定
            main_page_selected_create_user: '', // 主活动页面的创建人是谁
            // 当前选中的主页的应用端列表
            main_page_selected_platform_list: [],
            // 当前选中的主页的渠道列表（中文） ["a", "b", "c"]
            main_page_selected_pipelines_name: [],
        }
    },

    computed: {
        // 登录信息
        loginInfo () {
            return {
                username: this.$store.state.auth.username,
                realName: this.$store.state.auth.realName,
                isSuper: this.$store.state.auth.isSuper,
            }
        }
    },

    components: {
        listFilter,
        mainPageMoreActions,
        subPageList,
        modalMainPageEdit,
        modalUpdateHead
    },

    methods: {

        /**
         * 检查权限
         * @param {Number} is_lock 当前活动是否已经锁定
         * @param {String} create_user 当前活动的创建人
         * @returns {Boolean} true=拥有权限，false=没有权限
         */
        handle_check_auth (is_lock, create_user) {
            if (is_lock == 1 && create_user != this.loginInfo.username) {
                return false;
            } else {
                return true;
            }
        },

        /**
         * 开启/关闭loading效果
         */
        handle_loading (tof) {
            this.loading = tof;
        },
        
        /**
         * 打开弹窗
         * @description 组件必须暴露 show 这个方法
         * @param {string} name 弹窗的名字 vue.$refs[name]
         * @param {object} data 弹窗的传参，默认空对象
         */
        handle_show_dialog ({ name, data = {} }) {
            this.$refs[name].show(data);
        },

        /**
         * 执行查询操作
         */
        handle_search () {
            this.$refs.filter.handle_search();
        },

        /**
         * 获取列表数据
         * @param {Array} data.list 页面列表
         */
        handle_get_main_page (data) {
            this.main_page_list = [...data.list];
            this.main_page_pagination.current = data.pagination.pageNo;
            this.main_page_pagination.total = data.pagination.totalCount;
            this.handle_loading(false);
            // 自动筛选第1个主活动
            this.main_page_selected_id == -1 && this.handle_select_main_page(this.main_page_list[0]);
        },

        /**
         * 选中主页面交互
         */
        handle_select_main_page (item) {
            this.main_page_selected_id = item.id;
            this.main_page_selected_is_lock = item.is_lock;
            this.main_page_selected_create_user = item.create_user;
            this.main_page_selected_platform_list = item.group_info.platform_list;
            // 获取活动页下拥有的渠道（中文），type Array, ["全球", "西班牙"]
            this.main_page_selected_pipelines_name = Object.keys(item.pipeline_list).map(code => item.pipeline_list[code].name);
        },
        
        /**
         * 应用端切换
         */
        handle_device_tab_change (code) {
            this.platform = code;
            this.main_page_pagination.current = 1;
            this.main_page_selected_id = -1;
        },

        /**
         * 分页切换
         */
        handle_page_change (page) {
            this.main_page_pagination.current = page;
            this.main_page_selected_id = -1;
        },

        /**
         * 打开编辑弹窗
         */
        handle_show_eidt (id) {
            const result = this.main_page_list.filter(row => row.id == id)[0];
            this.$refs.main_page_edit.show(result)
        },

        /**
         * 编辑活动页回调
         */
        handle_edit_success () {
            this.handle_search();
            this.$refs.subPageList.get_sub_page_list();
        },

        /**
         * 锁定活动页
         */
        handle_lock (id) {
            // 判断权限（根据用户名）
            const result = this.main_page_list.filter(row => row.id == id);
            const create_user = result[0].create_user;
            const premission = this.handle_check_auth(1, create_user);
            if (!premission) {
                this.$message.error('只有活动创建者才具有此权限!');
                return false;
            };
            // 提示
            const self = this;
            const tips = result[0].is_lock == 0 ? '该活动加锁后，其他用户将不能操作此活动及其相关所有页面，是否加锁？' : '该活动解锁后，其他用户将拥有与您一样的操作权限，是否解锁？';
            this.$confirm({
                title: '提示',
                content: tips,
                okText: '是',
                okType: 'danger',
                cancelText: '取消',
                onOk () {
                    ZF_lockingActivity({id}).then(res => {
                        self.$message.success(res.message);
                        self.handle_search();
                    });
                },
                onCancel () {},
            });
        }
    },

    created () {
        this.site = this.$route.query.site_group_code || getCookie('site_group_code') || 'zf';

        // 获取当前站点支持的platform_list
        ZF_getCountrySiteList({activity_type: 1}).then(res => {
            this.site_platform_list = [...res.data.all_platforms];
            this.site_support_pipelines = res.data.support_pipelines;

            // 应用端默认选第一个
            this.platform = this.site_platform_list[0].code;

            // 更新编辑模块支持的渠道
            this.$refs.main_page_edit.init_site_support_pipelines(this.site_support_pipelines);
        });
    }
}
</script>

<style lang="less" >
#page-activity {
    position: relative;

    // tab 的定制样式
    .main-page-device-tabs {
        height: 50px;
        .ant-tabs-nav-container {
            font-size: 18px !important;
        }
    }


}
</style>

<style lang="less" scoped>

.page-list-layout {
    display: flex;
    padding-top: 20px;
}


// 主活动列表(左侧)
.main-page-list {
    width: 645px;
    flex-shrink: 0;

    ul {
        list-style: none;
        padding: 0px;
        margin: 0px;
        li {
            position: relative;
            margin-bottom: 12px;
            padding: 0 24px;
            height: 56px;
            line-height: 54px;
            border-radius: 8px;
            border: solid 1px #fff;
            background-color: #fff;
            box-sizing: border-box;
            cursor: pointer;
            > span {
                display: inline-block;
                margin-right: 40px;
            }
        }

        // 鼠标经过效果
        li.is_frequently {
            &::before {
                position: absolute;
                width:4px;
                height:18px;
                background:rgba(64,158,255,1);
                content: " ";
                left: 0px;
                top: 19px;
            }
        }

        // 选中效果
        li.active {
            border-color: rgba(64,158,255,1);
            &:after {
                position: absolute;
                width: 10px;
                height: 10px;
                right: -5px;
                top: 23px;
                border-top: solid 1px #409EFF;
                border-right: solid 1px #409EFF;
                border-left: solid 1px #fff;
                border-bottom: solid 1px #fff;
                background-color: #fff;
                transform: rotate(45deg);
                content: " ";
                z-index: 1;
            }
        }
    }

    .main-page-id {
        width: 40px;
    }
    .main-page-title {
        width: 220px;
        height: 100%;
        overflow: hidden;
        text-overflow: ellipsis;
        vertical-align: middle;
    }

    // 分页
    .ant-pagination {
        text-align: center;
        margin-top: 24px;
    }
}

</style>