<template>
	<el-row class="geshop-head">
		<el-col class="header-left geshop-head-left">
			<a href="javascript:;" class="site-logo" @click="goHome"><img src="/resources/images/geshop-logo-2020.gif" alt="GEShop"></a>
		</el-col>
		<el-col class="text-left geshop-head-right">
			<el-row>
				<el-col style="float:left;">
					<div class="geshop-header-site">
						<span class="icon-geshop-pc" v-if="siteCodeCookie.search('-pc') != -1"></span>
						<span class="icon-geshop-mobile" v-if="siteCodeCookie.search('-wap') != -1 || siteCodeCookie.search('-app') != -1"></span>
						<el-select v-model="siteSelect" placeholder="请选择" class="site-change-site geshop-header-site-switch" @change="siteChange"
						  popper-class="el-icon-caret-bottom">
							<template v-for="item in siteLists">
								<el-option v-if="!item.children" :key="item.code" :label="item.name" :value="item.code" :class="item.style">
								</el-option>
							</template>
						</el-select>
					</div>
					<el-button type="text" class="site-signout"  style="float:right;">
						<span class="icon_user">{{log_name_first}}</span>
						<span class="user-name">
							<span class="geshop-username">{{siteInfo.userName}}</span>
						</span>
						<div class="geshop-user-exit">
							<el-button @click.native="handleLogOut">退出</el-button>
						</div>
					</el-button>
				</el-col>
			</el-row>
		</el-col>
	</el-row>
</template>

<script>
import { getCookie, setCookie, delCookie } from '../../plugin/mUtils'
import bus from '../../store/bus-index.js'
import '../../../resources/fonts/svg-fonts/style.css'

export default {
	name: 'siteHeader',
	data () {
		return {
			log_name_first: '',
			site_icon: [],
			siteCodeCookie: '',
			siteSelect: ''
		}
	},
	props: ['siteLists', 'siteInfo'],
	// computed: {
	// 	siteSelects () {
	// 		let siteInfo = this.siteInfo
	//
	// 		let log_name = siteInfo.userName
	// 		let log_name_first = log_name.substr(0, 1).toUpperCase()
	// 		this.log_name_first = log_name_first
	//
	// 		if (this.siteLists.length === 0) {
	// 			return ''
	// 		}
	// 		return siteInfo.site ? siteInfo.site : siteCodeCookie
	// 	},
	// },
	mounted: function () {
		this.siteCodeCookie = getCookie('site_group_code') || JSON.parse(localStorage.getItem('supportSites')).data[0].name;
		let siteInfo = this.siteInfo

		let log_name = siteInfo.userName
		let log_name_first = log_name.substr(0, 1).toUpperCase()
		this.log_name_first = log_name_first
		this.siteSelect = siteInfo.site ? siteInfo.site : this.siteCodeCookie.toUpperCase();
	},
	methods: {
		siteChange (val) {
			if (val != getCookie('site_group_code')) {
				this.$confirm('请确保切换站点前已保存其他站点的数据,否则会出现数据丢失， 是否继续切换?', '警告', {
					confirmButtonText: '确定',
					cancelButtonText: '取消',
					type: 'warning',
					closeOnClickModal:false,
					center: true
				}).then(() => {
					setCookie('site_group_code', val)
					window.sessionStorage.clear()
					window.location.href = window.location.origin + '?site_group_code=' + getCookie('site_group_code');
				}).catch(() => {
					this.siteSelect = getCookie('site_group_code');
				});
			}
		},
		getQueryString(name) {
			var reg = new RegExp("(^|&)" + name + "=([^&]*)(&|$)", "i");
			var r = window.location.search.substr(1).match(reg);
			if (r != null) return unescape(r[2]); return null;
		},
		handleLogOut () {
			window.sessionStorage.clear()
			delCookie('site_group_code')
			window.location.href = '/base/login/logout'
		},
		goHome () {
			if (window.sessionStorage) {
				sessionStorage.setItem('menuIndex', '')
				sessionStorage.setItem('routeOpeneds', '')
			}
			let site_group_code = this.getQueryString('site_group_code') || getCookie('site_group_code');
			window.location.href = '/?site_group_code=' + site_group_code;
		},
		getData () {
			bus.$emit('giveData', this.siteInfo)
		}
	},
	created () {
		this.getData();
		// this.siteSelect = this.siteSelects;
	}
}
</script>

<style>
.site-header {
  width: 100%;
  line-height: 64px !important;
  background-color: #ffffff;
  color: #ffffff;
  border-bottom: 1px solid rgba(228, 228, 228, 1);
}

.site-logo {
  display: block;
  text-decoration: none;
  text-align: center;
}

.site-logo img {
  display: inline-block;
  vertical-align: middle;
}

.site-change-site {
  width: 256px;
  height: 64px;
  display: inline-block;
}
.user-name {
  font-size: 16px;
  color: #333333;
  margin-left: 4px;
}
.icon_user {
  width: 32px;
  line-height: 32px;
  border-radius: 16px;
  display: inline-block;
  background-color: #1e9fff;
  font-size: 14px;
  color: #ffffff;
}
.selectLabel {
  padding: 0 15px 0 10px;
  font-size: 14px;
}
.el-header {
  padding: 0px 0px;
}
.header-center {
  width: 256px;
  height: 64px;
  border: none;
}
.geshop-header-site {
    float: left;
    display: inline-block;
    width: 256px;
    height: 64px;
    border-left: 1px solid #e6e6e6;
    border-right: 1px solid #e6e6e6;
}
.geshop-header-site .icon-geshop-mobile {
  line-height: 64px;
}
.geshop-header-site .icon-geshop-pc,
.geshop-header-site .icon-geshop-mobile {
  color: #333333;
  font-size: 24px;
  margin-left: 16px;
}
.geshop-header-site-switch {
  width: 200px;
  height: 64px;
  margin-left: 10px;
}
.geshop-header-site-switch .el-input--suffix .el-input__inner {
  padding: 0px 0px;
  border: none;
}
.geshop-aslide .el-submenu__icon-arrow {
  font-size: 0px;
}
.geshop-head {
  display: flex;
}
.geshop-head-left {
  flex-basis: 240px;
}
.geshop-head-right {
  flex: 1;
}
.geshop-header-site .icon-geshop-pc {
  line-height: 64px;
}
.geshop-header-site .el-select {
  position: absolute;
}
.geshop-header-site .el-icon-arrow-up {
  width: 0;
  height: 0;
  border-left: 4px solid transparent;
  border-right: 4px solid transparent;
  border-bottom: 5px solid #333333;
  margin-top: 30px;
  color: #ffffff !important;
  margin-right: 2px;
}
.el-icon-caret-bottom:before {
  content: none;
}

.site-signout {
  width: 158px;
	height: auto;
	min-height: 64px;
  color: #ffffff;
  padding-right: 158px;
	z-index: 100;
	position: relative;
}

.site-signout:hover .geshop-user-exit {
	height: 45px;
	transition: height .3s linear;
}

.geshop-user-exit {
	display: block;
	width: 120px;
	height: 0;
	border: none;
	position: absolute;
	right: 36px;
	top: 63px;
	background-color: #ffffff;
	overflow: hidden;
	box-shadow: 0px 0px 10px #ccc;
	z-index: 200;
}
.geshop-user-exit .el-button {
	margin-top: 5px;
	display: block;
	width: 100%;
	height: 32px;
	line-height: 32px;
	color:#333333;
	padding:0px 0px !important;
	text-align: left;
	padding-left: 19px !important;
	border: none;
}
.geshop-user-exit:hover .el-button {
	color:#1E9FFF;
	background-color: #F7FAFF;
}
</style>
