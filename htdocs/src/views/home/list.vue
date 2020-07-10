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
            @onCreate="handle_show_eidt"
            @beforeResponse="handle_loading"
            @response="handle_get_main_page">
        </list-filter>

        <!-- 端切换 -->
        <a-tabs
            class="main-page-device-tabs"
            @change="handle_device_tab_change">
            <a-tab-pane
                v-for="item in site_platform_list"
                :tab="item.name"
                :key="item.code">
            </a-tab-pane>
        </a-tabs>

        <!-- 加载动画 -->
        <a-spin :spinning="loading">
            <!-- 主活动列表 -->
            <div class="main-page-list">
                <ul class="page-list">
                    <li
                        v-for="item in main_page_list"
                        :key="item.id">
                        <list-item
                            :info="item"
                            :site="site"
                            :disabled="!handle_check_auth(item.is_lock, item.create_user)"
                            @onEdit="handle_show_eidt"
                            @onPreview="handle_show_preview"
                            @onLock="handle_lock"
                            @onDelete="handle_delete"
                            @onQrcode="handle_show_qrcode">
                        </list-item>
                    </li>
                </ul>

                <!-- 主活动列表的分页 -->
                <a-pagination
                    class="page-pagination"
                    showQuickJumper
                    :current="main_page_pagination.current"
                    :total="main_page_pagination.total"
                    @change="handle_page_change" />
            </div>
        </a-spin>

        <!-- 更新头尾 -->
        <modal-update-head
            ref='updateHead'
            :platform="platform">
        </modal-update-head>

        <!-- 页面预览 -->
        <modal-preview
            ref="modalPreview">
        </modal-preview>

        <!-- 新增/编辑 -->
        <modal-page-edit
            ref="modalPageEdit"
            @onSuccess="handle_search">
        </modal-page-edit>
        
        <!-- 二维码 -->
        <modal-qrcode ref="modalQrcode"></modal-qrcode>
    </div>
</template>

<script>

import bus from '../../store/bus-index.js';
import { getCookie, getHasPermissionChannel,clone_simple } from '../../plugin/mUtils'

import {
    ZF_getCountrySiteList,
    ZF_lockingActivity,
    ZF_handlerLock,
    ZF_deleteIndex,
} from '../../plugin/api.js'

// 筛选组件
import listFilter from './unit/list-filter.vue';
// 列表项
import listItem from './unit/list-item.vue';
// 一键更新弹窗
import modalUpdateHead from './unit/modal-udpate-head.vue';
// 页面预览弹窗
import modalPreview from './unit/modal-page-preview.vue';
// 新增编辑弹窗
import modalPageEdit from './unit/modal-page-edit.vue';
// 二维码
import modalQrcode from './unit/modal-qrcode.vue';

export default {
    data () {
        return {
            // 当前端，[pc/wap/app]
            platform: '',
            // 当前站点拥有的渠道
            site_support_pipelines: {},
            // 状态
            loading: false,
            // 主页列表
            main_page_list: [],
            // 主分页的页码
            main_page_pagination: {
                current: 1,
                pageSize: 10,
                total: 1
            },
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
        },
        /**
         * 获取站点的所有端
         * 1. 不获取APP端
         */
        site_platform_list () {
            let base = [...this.$store.state.site.platforms];
            base = base.filter(x => x.code != 'app');
            return base;
        },
        // 当前站点
        site () {
            return this.$store.state.home.current_site;
        }
    },

    watch: {
        /**
         * 站点支持的平台端变更的时候
         */
        site_platform_list (val) {
            if (val[0]) {
                this.platform = val[0].code;
                this.$store.commit('home/update_platform', val[0].code);
            }
        }
    },

    components: {
        listFilter,
        listItem,
        modalUpdateHead,
        modalPreview,
        modalPageEdit,
        modalQrcode,
    },

    methods: {

        /**
         * 检查权限
         * @param {Number} is_lock 当前活动是否已经锁定
         * @param {String} create_user 当前活动的创建人
         * @returns {Boolean} true=拥有权限，false=没有权限
         */
        handle_check_auth (is_lock, create_user) {
            if (is_lock == 2 && create_user != this.loginInfo.username) {
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
         * @param {Array} data.top_page 置顶的AB首页
         */
        handle_get_main_page (data) {
            this.main_page_list = data.top_page.concat(data.list);
            this.main_page_pagination.total = parseInt(data.total);
            this.handle_loading(false);
        },
        
        /**
         * 应用端切换
         */
        handle_device_tab_change (code) {
            this.$store.commit('home/update_platform', code);
            this.main_page_pagination.current = 1;
        },

        /**
         * 分页切换
         */
        handle_page_change (page) {
            this.main_page_pagination.current = page;
        },

        /**
         * 打开编辑弹窗
         * @param {object} data
         */
        handle_show_eidt (data) {
            this.$refs.modalPageEdit.show(data)
        },

        /**
         * 打开预览弹窗
         * @param {number} id Mysql自增ID
         */
        handle_show_preview ({id, preview}) {
            this.$refs.modalPreview.show(id, preview);
        },

        /**
         * 打开二维码
         * @param {number} id 页面ID
         */
        handle_show_qrcode (id) {
            const target = this.main_page_list.filter(x => x.id == id);
            const title = target[0].page_languages[0].title || '';
            const qrcode = target[0].qrcode || '';
            this.$refs.modalQrcode.show(title, qrcode);
        },

        /**
         * 页面锁定/解锁
         * @param {string} group_id 渠道组合ID
         */
        async handle_lock (group_id) {
            const res = await ZF_handlerLock({group_id});
            if (res.code == 0) {
                this.handle_search();
                this.$message.success(res.message);
            }
        },

        /**
         * 删除页面
         * @param {string} group_id 渠道组合ID
         */
        async handle_delete (group_id) {
            const res = await ZF_deleteIndex({group_id});
            if (res.code == 0) {
                this.handle_search();
                this.$message.success(res.message);
            }
        }
    },

    created () {
        this.$store.commit('home/update_site', this.$store.state.siteCode);
        this.site_platform_list[0] && this.$store.commit('home/update_platform', this.site_platform_list[0].code);
    },
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

// 主活动列表(左侧)
.main-page-list {
    width: 100%;
    flex-shrink: 0;

    ul.page-list {
        list-style: none;
        padding: 0px;
        margin: 0px;
        padding-left: 40px;
        padding-top: 40px;
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

    // 分页
    .ant-pagination {
        text-align: center;
        margin-top: 24px;
    }
}

</style>