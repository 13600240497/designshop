<template>
    <el-menu
        ref="nav"
        class="geshop-aslide"
        :class="{ 'is-prerelease': env === 'prerelease' }"
        :unique-opened="true"
        :default-active="activeIdx"
        :default-openeds="asideObj.routeOpeneds"
        @select="handleSelect">
        <div v-for="(row,index) in dataLists" :key="index">
            <el-submenu :index="String(row.id)" v-if="row.children">
                <template slot="title">
                    <i :class="row.icon_class?row.icon_class:'el-icon-menu'"></i>
                    <span slot="title">{{row.name}}</span>
                </template>
                <el-submenu v-for="item in row.children" v-if="item.children" :key="String(item.id)"
                            :index="''+item.id">
                    <template slot="title">{{item.name}}</template>
                    <el-menu-item v-for="thirdNav in item.children" :key="thirdNav.id" :index="thirdNav.id">
                        <a :href="thirdNav.route | location">{{thirdNav.name}}</a>
                    </el-menu-item>
                </el-submenu>
                <el-menu-item v-for="item in row.children" v-if="!item.children" :key="item.id" :index="item.id">
                    <a :href="item.route | location">{{item.name}}</a>
                </el-menu-item>
            </el-submenu>
            <el-menu-item v-if="!row.children" :index="String(row.id)">
                <!-- <span slot="title">{{row.name}}</span> -->
                <a :href="row.route | location" slot="title" class="menu-inline">
                    <i :class="row.icon_class?row.icon_class:'el-icon-menu'"></i>{{row.name}}</a>
            </el-menu-item>
        </div>
        <div class="menu__footer--desc">
            <span><i class="el-icon-info"></i>有新版本说明， </span>
            <a target="_blank" href="https://docs.google.com/spreadsheets/d/1uEio7j1MHhydfPXjtLQ8jCanbGKkCJ2Me2CM3Ao1EkM/edit#gid=0">去查看</a>
        </div>
    </el-menu>
</template>

<style scoped>

</style>

<script>
import '../../../resources/stylesheets/icon.css';
import { getCookie } from '../../plugin/mUtils';

export default {
    data () {
        return {
            tableData: [
                {
                    name: '',
                    children: [
                        {
                            name: '',
                            children: []
                        }
                    ]
                }
            ],
            routeActive: '',
            routeOpeneds: [],
            env: '' // 当前环境，可选值 prelrease
        };
    },
    props: ['dataLists', 'asideObj'],
    filters: {
        location (data) {
            return window.location.origin + '/' + data;
        }
    },
    computed: {
        activeIdx () {
            let routeActive = this.asideObj.routeActive;
            if (!routeActive) {
                routeActive = '42';
            }
            return routeActive;
        }
    },
    created () {
        // 获取当前环境，是否已发布环境
        this.env = eval(getCookie('staging') || 'false') ? 'prerelease' : '';
    },
    methods: {
        handleSelect (key, keyPath) {
            this.$emit('asideExtend', key, keyPath);
        }
    }
};
</script>

<style>
    .menu-inline {
        display: inline-block !important;
    }

    .navigation .el-menu {
        height: 100%;
        background-color: #1A233B;
    }

    .navigation .el-menu a {
        display: block;
        color: #FFFFFF;
        text-decoration: none;
        width: 100%;
    }

    .el-submenu__title {
        color: #ffffff;
        border-left: 6px solid #1A233B;
    }

    .el-submenu__title:hover {
        background-color: #121B2C;
        border-left: 6px solid #1E9FFF;
    }

    .geshop-aslide .el-menu-item.is-active {
        background-color: #121B2C;
        border-left: 6px solid #409EFF !important;
    }

    .el-menu-item {
        border-left: 6px solid #1A233B;
    }

    .el-menu-item:hover {
        background-color: #121B2C;
        border-left: 6px solid #1E9FFF;
    }

    .el-submenu__icon-arrow .el-icon-arrow-down::before {
        width: 20px;
        height: 20px;
    }
</style>

<style>
    .geshop-aslide .el-submenu__title .el-submenu__icon-arrow {
        position: absolute;
        right: 16px !important;
    }

    .geshop-aslide .el-submenu .el-icon-arrow-down::before {
        background: url('../../../resources/images/activity/add_white.png') no-repeat;
        width: 20px;
        line-height: 20px;
        display: block;
    }

    .geshop-aslide .el-submenu.is-opened .el-icon-arrow-down::before {
        background: url('../../../resources/images/activity/reduce_white.png') no-repeat;
        width: 20px;
        line-height: 20px;
        display: block;
    }
</style>

<style lang="less">
    // 预发布的样式
    .geshop-aslide.is-prerelease {
        background: #111;

        .el-menu-item:hover {
            border-color: red;
        }

        .el-submenu.is-active .el-submenu__title {
            border-color: red;
        }

        .el-menu-item.is-active {
            border-color: red !important;
            background: #ff6600 !important;
        }

        .el-submenu__title:hover {
            border-color: red;
        }

        // 子菜单组
        .el-menu {
            background: #111;
        }
    }

    .geshop-aslide {
        // 版本说明
        .menu__footer--desc {
            position: fixed;
            bottom: 0;
            width: 239px;
            font-size: 12px;
            padding: 14px;
            display: flex;
            -ms-flex-pack: center;
            justify-content: center;
            -ms-flex-align: center;
            align-items: center;
            text-align: center;
            color: #ffffff;
            background-color: #1A233B;
            box-sizing: border-box;

            i {
                padding-right: 5px;
            }

            a {
                width: auto;
                text-decoration: underline;
                color: #55a0fd;
            }
        }
    }
</style>

