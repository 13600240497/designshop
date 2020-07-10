<template>
    <a-layout>

        <!-- 侧边栏 -->
        <site-menu
            :dataLists="menuLists"
            :collapsed="collapsed">
        </site-menu>

        <!-- 顶部 -->
        <site-header
            ref="siteHeader"
            :collapsed="collapsed"
            :siteLists="siteLists"
            :siteInfo="siteInfo"
            @handleCollapse="handleCollapse">
        </site-header>

        <!-- 中心内容 -->
        <a-layout :style="{ margin: collapsed ? '50px 0px 0px 0px' : '50px 0px 0px 220px', padding: '32px' }">

            <!-- 路由面包屑 -->
            <site-breadcrumb></site-breadcrumb>
        
            <!-- 路由 -->
            <a-layout-content
                :style="{ margin: '8px 0px 0px 0px', minHeight: containerHeight+'px', background: '#fff' }">
                <router-view></router-view>
            </a-layout-content>

            <!-- 帮助文案？ -->
            <template v-if="footLink">
                <div style="text-align: center;">
                    <we-link></we-link>
                </div>
            </template>

        </a-layout>
    </a-layout>
</template>

<script>
import siteHeader from './antd-header.vue'
import siteMenu from '../antd-menu/index.vue'
import siteBreadcrumb from './antd-breadcrumb.vue'
import siteAside from './Aside.vue'
import weLink from './Welink.vue'
import { getPublicMenus } from '../../plugin/api.js'
import { setCookie } from '../../plugin/mUtils'

export default {
	name: 'layout',
	components: { siteHeader, siteMenu, siteBreadcrumb, siteAside, weLink },
	props:{
		'footLink':Number
	},
	data () {
		return {
            // 是否折叠
            collapsed: true,
            // 窗口高度
            containerHeight: 0,
            // 左侧有权限的菜单的列表数据
            // 结构: [{ name: '', children: [ { name: '', children: [] } ] }]
            menuLists: [],
            // 顶部，可选站点的列表数据
            siteLists: [],
            // 顶部，用户信息
			siteInfo: {
				site: '',
				userName: '',
			}

		}
	},
	methods: {
		async get_info () {
            // 获取当前用户信息
            let res = await getPublicMenus();
			let data = res.data;
            // 菜单数据
            this.menuLists = data.permissions;
            // 站点数据
            this.siteLists = data.siteGroups;
            // 用户信息数据
			this.siteInfo.site = data.currentSiteGroupCode;
			this.siteInfo.userName = data.userName;
            this.siteInfo.isSuper = data.isSuper;
            
			//保存用户信息
			localStorage.setItem('userName', data.userName)
			localStorage.setItem('isSuper', data.isSuper)
			localStorage.setItem('departmentId', data.departmentId)
			localStorage.setItem('isLeader', data.isLeader)
			localStorage.setItem('actionPermissions', JSON.stringify({ data: data.actionPermissions }))
			localStorage.setItem('supportSites', JSON.stringify({ data: data.siteGroups }))

			// 存储当前站点信息
			data.siteGroups.forEach((item) => {
				if (item.code == this.siteInfo.site) {
                    localStorage.setItem('currentSites', JSON.stringify(item));
                    // 存入 store
                    this.$store.commit('site/update_platforms', JSON.stringify(item));
				}
			});

            // ????????
			if (data.currentSiteGroupCode) {
                setCookie('site_group_code', data.currentSiteGroupCode)
                this.$store.state.siteCode = data.currentSiteGroupCode;
			}

            // ????????
            this.$emit('publicReady');
            this.$refs.siteHeader.load(this.siteInfo);

            // 更新账户信息到 store
            this.$store.commit('auth/update_logInfo', {
                username: res.data.userName,
                realName: res.data.realName,
                isSuper: res.data.isSuper
            });
        },
        
        /**
         * 伸缩菜单
         * @param {Boolean} val 是否收起菜单
         */
        handleCollapse (val) {
            this.collapsed = val;
        }
    },
    async created () {
        // 获取信息
		await this.get_info();
	},
    mounted () {
        // 计算高度
		this.containerHeight = window.innerHeight - 130
	}
}
</script>

<style lang="less" scoped>
.ant-layout-content {
    background: transparent !important;
}
</style>
