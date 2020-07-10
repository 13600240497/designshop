<template>
	<el-container>
		<el-header class="site-header" style="height:64px;">
			<site-header :siteLists="siteLists" :siteInfo="siteInfo"></site-header>
		</el-header>
		<el-container :style="{ minHeight: containerHeight + 'px' }">
			<el-aside class="navigation" style="width:240px;">
				<site-aside :dataLists="tableData" :asideObj="asideObj" @asideExtend="handleSelect"></site-aside>
			</el-aside>
			<el-main class="geshop-el-main">
				<slot></slot>
			</el-main>
		</el-container>
	</el-container>
</template>
<script>
import siteHeader from './Header.vue'
import siteAside from './Aside.vue'
import { getPublicMenus } from '../../plugin/api.js'
import { setCookie } from '../../plugin/mUtils'

export default {
	name: 'layout',
	components: { siteHeader, siteAside },
	data () {
		return {
			containerHeight: 0,
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
			asideObj: {
				routeActive: '',
				routeOpeneds: []
			},
			siteLists: [],
			siteInfo: {
				site: '',
				userName: '',
			}

		}
	},
	created () {
		this.handleLists()
		this.historyEvent()
	},
	beforeDestroy () {
		window.removeEventListener('popstate', this.handlePopstate)
	},
	mounted () {
		this.containerHeight = window.innerHeight - 60
	},
	methods: {
		async handleLists () {
			let res = await getPublicMenus({ 'activity_type': 3 })
			let data = res.data

			this.tableData = data.permissions
			this.pathArr = JSON.parse(JSON.stringify(this.tableData))
			// this.siteLists = data.sites
			this.siteLists = data.siteGroups
			// this.siteInfo.site = data.currentSiteCode
			this.siteInfo.site = data.currentSiteGroupCode
			this.siteInfo.userName = data.userName
			this.siteInfo.isSuper = data.isSuper
			//保存用户信息
			localStorage.setItem('userName', data.userName)
			localStorage.setItem('isSuper', data.isSuper)
			localStorage.setItem('departmentId', data.departmentId)
			localStorage.setItem('isLeader', data.isLeader)
			localStorage.setItem('actionPermissions', JSON.stringify({ data: data.actionPermissions }))
			// localStorage.setItem('supportSites', JSON.stringify({ data: data.sites }))
			localStorage.setItem('supportSites', JSON.stringify({ data: data.siteGroups }))

			// 存储当前站点信息
			data.siteGroups.forEach((item) => {
				if (item.code == this.siteInfo.site) {
					localStorage.setItem('currentSites', JSON.stringify(item))
				}
			})

			if (window.sessionStorage) {
				/* 默认选中第一个菜单 */
				let routeActive = sessionStorage.getItem('menuIndex')
				// routeOpeneds = sessionStorage.getItem('routeOpeneds')
				if (!routeActive) {
					let permissionsKeys = Object.keys(data.permissions),
						permissionsFirst = data.permissions[permissionsKeys[0]]
					if (!(permissionsFirst.children && permissionsFirst.children.length > 0)) {
						sessionStorage.setItem('menuIndex', permissionsFirst.id)
						sessionStorage.setItem('routeOpeneds', permissionsFirst.id)
						sessionStorage.setItem('menuIndexOld', permissionsFirst.id)
						sessionStorage.setItem('routeOpenedsOld', permissionsFirst.id)
					} else {
						let childrenFirst = permissionsFirst.children[0]
						sessionStorage.setItem('menuIndex', childrenFirst.id)
						sessionStorage.setItem('routeOpeneds', [permissionsFirst.id, childrenFirst.id].toString())
						sessionStorage.setItem('menuIndexOld', childrenFirst.id)
						sessionStorage.setItem('routeOpenedsOld', [permissionsFirst.id, childrenFirst.id].toString())
					}
				}

				/* 写入菜单 */
				let routeOpeneds = sessionStorage.getItem('routeOpeneds')
				this.asideObj.routeActive = sessionStorage.getItem('menuIndex')
				this.asideObj.routeOpeneds = routeOpeneds ? routeOpeneds.split(',') : []
			}
			// if (data.currentSiteCode) {
			// 	setCookie('SITECODE', data.currentSiteCode)
			// }
			if (data.currentSiteGroupCode) {
				setCookie('site_group_code', data.currentSiteGroupCode)
			}

			this.$emit('publicReady')
		},
		handleSelect (key, keyPath) {
			let routeActive = sessionStorage.getItem('menuIndex'),
				routeOpeneds = sessionStorage.getItem('routeOpeneds')
			if (window.sessionStorage) {
				sessionStorage.setItem('menuIndexOld', routeActive)
				sessionStorage.setItem('routeOpenedsOld', routeOpeneds)
				sessionStorage.setItem('menuIndex', key)
				sessionStorage.setItem('routeOpeneds', keyPath)
			}
		},
		historyEvent () {
			window.addEventListener('popstate', this.handlePopState)
			this.handlePushState()
		},
		handlePushState () {
			let key = sessionStorage.getItem('menuIndex'),
				keyPath = sessionStorage.getItem('routeOpeneds')
			window.history.pushState({ 'menuIndex': key, 'routeOpeneds': keyPath }, '', '')
		},
		handlePopState () {
			let menuIndexOld = sessionStorage.getItem('menuIndexOld'),
				routeOpenedsOld = sessionStorage.getItem('routeOpenedsOld')

			if (menuIndexOld) {
				sessionStorage.setItem('menuIndex', menuIndexOld)
				sessionStorage.setItem('routeOpeneds', routeOpenedsOld)
			}
			history.back()
		}
	}
}
</script>

<style>
.geshop-el-main {
  padding: 12px 40px 25px 40px;
  background-color: #f9f9f9;
}
</style>
