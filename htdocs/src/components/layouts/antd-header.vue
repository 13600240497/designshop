<template>
	<header class="geshop-head" :style="{ 'left': collapsed ? '100px' : '220px' }">

        <!-- 下拉选择渠道 -->
        <a-select
            class="geshop-header-site-switch"
            placeholder="请选择"
            :value="selected_site"
            :dropdownMatchSelectWidth="false"
            @change="handle_siteChange">
            <template v-for="item in siteLists">
                <a-select-option
                    v-if="!item.children"
                    :key="item.code"
                    :label="item.name"
                    :value="item.code"
                    :class="item.style">{{ item.name | site_full_name }}
                </a-select-option>
            </template>
        </a-select>

        <!-- ???? -->
        <!-- <div class="geshop-header-site">
            <span class="icon-geshop-pc" v-if="siteCodeCookie.search('-pc') != -1"></span>
            <span class="icon-geshop-mobile" v-if="siteCodeCookie.search('-wap') != -1 || siteCodeCookie.search('-app') != -1"></span>
        </div> -->

        <div class="site-signout">
            <span class="icon_user">
                {{ full_name | name_first_code }}
            </span>
            <span class="user-name">
                <span class="geshop-username">
                    {{ full_name }}
                </span>
            </span>
            <div class="geshop-user-exit">
                <p @click="handleLogOut">退出</p>
            </div>
        </div>
	</header>
</template>

<script>
import { getCookie, setCookie, delCookie } from '../../plugin/mUtils'
import '../../../resources/fonts/svg-fonts/style.css'

// 站点全称
const site_full_name_map = {
    rg: 'Rosegal',
    zf: 'Zaful',
    dl: 'Dresslily',
    gb: 'Gearbest'
};

export default {
    name: 'siteHeader',
    // siteInfo 用户登录信息
    props: ['collapsed', 'siteLists', 'siteInfo'],
	data () {
		return {
            selected_site: this.$store.state.siteCode
		}
    },
	computed: {
        // 用户名
        full_name () {
            return this.siteInfo.userName;
        },
    },
    filters: {
        // 名字首字母
        name_first_code (val = 'C') {
			return val.substr(0, 1).toUpperCase()
        },
        
        // 站点 CODE 转换为全称, RG => Rosegal
        site_full_name (code = 'test') {
            const str = code.toLowerCase();
            const full_name = site_full_name_map[str] || str.toUpperCase();
            return full_name;
        }
    },
	methods: {
        load (params) {
            this.selected_site = this.$store.state.siteCode;
        },
        
        /**
         * 展开／收起 侧边栏
         */
        handleCollapsed () {
            this.$emit('handleCollapse', !this.collapsed);
        },

        /**
         * 切换站点
         * @param {string} val 站点的 code
         */
		handle_siteChange (val) {
            if (val != getCookie('site_group_code')) {
                this.$confirm({
                    title: '警告',
                    content: '请确保切换站点前已保存其他站点的数据,否则会出现数据丢失， 是否继续切换?',
                    okText: '确定',
                    okType: 'danger',
                    cancelText: '取消',
                    onOk () {
                        setCookie('site_group_code', val)
                        window.sessionStorage.clear()
                        window.location.href = window.location.origin + '?site_group_code=' + getCookie('site_group_code');
                    },
                    onCancel () {
                        self.siteSelect = getCookie('site_group_code');
                    },
                });
            }
        },
        
        // 退出登录
		handleLogOut () {
			window.sessionStorage.clear()
			delCookie('site_group_code')
			window.location.href = '/base/login/logout'
        },

        // 返回首页
		goHome () {
			if (window.sessionStorage) {
				sessionStorage.setItem('menuIndex', '')
				sessionStorage.setItem('routeOpeneds', '')
			}
			window.location.href = '/'
		}
	}
}
</script>

<style lang="less">

header.geshop-head {
    position: fixed;
    left: 220px;
    right: 0px;
    height: 50px;
    background:rgba(255,255,255,1);
    box-shadow: 2px 0px 6px 2px rgba(0,0,0,0.09);
    z-index: 2;
}

// 下来渠道
.geshop-header-site-switch {
    width:160px;
    height:32px;
    display: inline-block;
    outline: none;
    margin-top: 9px;
    margin-left: 24px;

    .ant-select-selection {
        width: 100%;
        height:32px;
        border-radius: 0px;
        border-left: none;
        border-top: none;
        border-bottom: none;
        border-right: none;
        outline: none;
        background:rgba(243,245,247,1);
        border-radius:16px;
        box-shadow: none !important;
        .ant-select-selection__rendered {
            line-height: 32px;
            outline: none;
        }
    }

    .ant-select-selection-selected-value {
        padding-left: 16px;
        color: #1e9fff;
    }
}


// 右侧退出按钮
.site-signout {
    float: right;
    width: 158px;
    height: 50px;
    line-height: 50px;
    color: #ffffff;
    z-index: 100;
    position: relative;
    &:hover .geshop-user-exit {
        height: 45px;
	    transition: height .3s linear;
    }

    .user-name {
        font-size: 16px;
        color: #333333;
        margin-left: 4px;
        display: inline-block;
    }
    .icon_user {
        text-align: center;
        width: 30px;
        line-height: 30px;
        border-radius: 30px;
        display: inline-block;
        background-color: #1e9fff;
        font-size: 14px;
        color: #ffffff;
    }

    .geshop-user-exit {
        display: block;
        width: 120px;
        height: 0px;
        border: none;
        position: absolute;
        right: 36px;
        top: 50px;
        background-color: #ffffff;
        overflow: hidden;
        box-shadow: 0px 0px 10px #ccc;
        z-index: 200;
        p {
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
    }
}
</style>
