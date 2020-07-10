<template>
  <el-container>
    <el-header class="site-header">
      <site-header :siteLists="siteLists" :siteInfo="siteInfo"></site-header>
    </el-header>
  </el-container>
</template>

<script>
import siteHeader from './Header.vue'
import { getPublicMenus } from '../../plugin/api.js'

export default {
	name: 'layoutFroDesign',
	components: { siteHeader },
	data() {
		return {
			tableData: [{
				name: '',
				children: [{
					name: '',
					children: []
				}]
			}],
			siteLists: [],
			siteInfo: {
				site: '',
				userName: ''
			}
		}
	},
	created() {
		this.handleLists()
	},
	methods: {
		async handleLists() {
			let res = await getPublicMenus()
			let data = res.data
			this.tableData = data.permissions
			this.pathArr = JSON.parse(JSON.stringify(this.tableData))
			// this.siteLists = data.sites
			this.siteLists = data.siteGroups
			// this.siteInfo.site = data.currentSiteCode
			this.siteInfo.site = data.currentSiteGroupCode
			this.siteInfo.userName = data.userName
			localStorage.setItem('actionPermissions', JSON.stringify({'data': data.actionPermissions}))
		}
	}
}
</script>
