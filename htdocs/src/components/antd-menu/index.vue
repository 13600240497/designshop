<template>
    <a-layout-sider
        width="100"
        theme="dark"
        collapsible
        v-model="collapsed"
        collapsedWidth="100"
        :trigger="null">

        <!-- LOGO -->
        <div class="geshop-side-logo">
            <a href="/">
                <i class="iconfont logo"></i>
            </a>
        </div>

        <!-- 菜单 -->
        <a-menu
            mode="inline"
            theme="dark"
            v-model="selectedKeys">

            <!-- 循环遍历菜单 -->
            <template v-for="(row, index) in dataLists">
                <!-- 多层级的菜单 -->
                <a-sub-menu
                    v-if="row.children"
                    :key="`/${row.route}`.replace('/#', '')"
                    :index="index" >
                    <span slot="title">
                        <i class="iconfont geshop-icon" :class="row.icon_class || 'geshop-icon-setting'"></i>
                        <span>{{ row.name | formate_name }}</span>
                    </span>

                    <!-- 遍历子菜单 -->
                    <template v-for="row2 in row.children">

                        <!-- 有子菜单的 -->
                        <a-sub-menu v-if="row2.children" :key="row2.route" :title="row2.name">
                            <a-menu-item
                                v-for="row3 in row2.children"
                                :key="row3.route"
                                @click="handleDirect(row3.route, row3.id)">
                                {{ row3.name }}
                            </a-menu-item>
                        </a-sub-menu>

                        <!-- 没有子菜单 -->
                        <a-menu-item
                            v-else
                            :key="`/${row2.route}`"
                            @click="handleDirect(row2.route, row2.id)">
                            <span>{{ row2.name | formate_name }}</span>
                        </a-menu-item>
                        
                    </template>
                </a-sub-menu>

                <!-- 单个菜单 -->
                <a-menu-item
                    v-else
                    :key="`/${row.route}`.split('?')[0]"
                    @click="handleDirect(row.route, row.id)">
                    <i class="iconfont geshop-icon" :class="row.icon_class || 'geshop-icon-setting'"></i>
                    <span>{{row.name | formate_name}}</span>
                </a-menu-item>
            </template>
        </a-menu>

    </a-layout-sider>
</template>

<script>
import { getCookie } from '../../plugin/mUtils'
export default {
	props: ['dataLists', 'collapsed'],
	data () {
		return {
            // 当前环境，可选值 prelrease
            env: '',
            // 初始选中的菜单项 key 数组
            openKeys: [],
            selectedKeys: [],
		};
	},
	filters: {
		location (data) {
			return window.location.origin + '/' + data
        },
        formate_name (name) {
            return name.replace('管理', '');
        }
	},
	methods: {
        /**
         * 跳转路由，判断是否有 vue 路由，没有的话之行 href 跳转
         */
        handleDirect (path, id) {
            sessionStorage.setItem('menuIndex', id);
            /**
             * 判断是否新路由
             */
            if (path[0] == '#') {
                const url = path.replace('#', '');
                this.$router.push(url);
            } else {
                const url = '/' + path;
                window.location.href = url;
            }
        },
    },

    created () {
        // 高亮选中的菜单
        this.$route.matched.map(x => {
            const path = '/#' + x.path;
            this.openKeys.push(path);
            this.selectedKeys.push(path);
            if (x.parent) {
                this.selectedKeys.push(x.parent.path + '/index');
            }
        });

        // 获取当前环境，是否已发布环境
        this.env = eval(getCookie('staging') || 'false') ? 'prerelease' : '';
	},
}
</script>

<style lang="less">

// 默认字体颜色
@normalFontColor: #86C2FF;
// 选中字体颜色
@activeFonrtColor: #438CD7;
// 背景颜色
@backgroundColor: #438CD7;
// 选中背景颜色
@activeBackgroundColor: #fff;


/* 侧边栏 */
.ant-layout-sider {
    background: @backgroundColor;

    .ant-menu-dark, .ant-menu-dark .ant-menu-sub {
        background: @backgroundColor;
    }

    .ant-menu-inline-collapsed {
        width: 100px;
    }

    .ant-menu-item,
    .ant-menu-submenu {
        padding: 10px 0px !important;
        line-height: 1em;
        text-align: center;
        height: 64px;
        color: @normalFontColor;
    }

    .ant-menu-submenu {
        .ant-menu-submenu-title {
            color: @normalFontColor !important;
            height: 100%;
            margin: 0px !important;
            padding: 0px !important;
            text-align: center;
            > span {
                display: block;
                height: 100%;
                line-height: 1em;
            }
        }
    }


    // 图标
    .geshop-icon {
        display: block;
        margin: 0 auto;
        width: 24px;
        height: 24px;
        line-height: 24px;
        font-size: 22px;
        background: none;
        margin-bottom: 4px;
    }
    .geshop-icon + span {
        font-size: 13px;
    }


    // 选中
    .ant-menu.ant-menu-dark .ant-menu-item-selected,
    .ant-menu-submenu-popup.ant-menu-dark .ant-menu-item-selected {
        background: @activeBackgroundColor;
        color: @activeFonrtColor;
    }

    .ant-menu-submenu-open.ant-menu-submenu-active {
        span {
            color: #fff;
        }
    }

    .ant-menu-submenu-title {
        color: @normalFontColor !important;
    }
}

// 图标
.geshop-side-logo {
    margin-top: 16px;
    margin-bottom: 24px;
    text-align: center;
    height: 38px;
    line-height: 38px;
    i {
        font-size: 38px;
    }
    a {
        color: #fff;
    }
}

// 菜单弹层
.ant-menu-submenu.ant-menu-submenu-popup {
    .ant-menu.ant-menu-vertical.ant-menu-sub.ant-menu-submenu-content {
        background: @backgroundColor;
    }
}
</style>
<style lang="less">
    .ant-tooltip.ant-menu-inline-collapsed-tooltip.ant-tooltip-placement-right {
        display: none;
    }
</style>

