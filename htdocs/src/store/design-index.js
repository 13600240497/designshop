import Vuex from 'vuex';
import Vue from 'vue';
import design from './modules/design'; // 装修页面模块
import page from './modules/page'; // 前端预览页面模块
import auth from './modules/auth'; // 权限模块
import site from './modules/site'; // 站点模块相关
import home from './modules/home'; // 站点模块相关

Vue.use(Vuex);

/* 设置cookie */
const setCookie = (name, value, expiredays) => {
	var exdate = new Date()
	exdate.setDate(exdate.getDate() + expiredays)
	document.cookie = name + '=' + escape(value) + (expiredays == null ? ';path=/;' : ';path=/;expires=' + exdate.toGMTString())
}
/* 获取cookie */
const getCookie = name => {
	var arr
	var reg = new RegExp('(^| )' + name + '=([^;]*)(;|$)')
	if ((arr = document.cookie.match(reg))) return arr[2]
	else return null
}
/* 删除cookie */
const delCookie = name => {
	var exp = new Date()
	exp.setTime(exp.getTime() - 1)
	var cval = getCookie(name)
	if (cval != null) document.cookie = name + '=' + cval + ';path=/;expires=' + exp.toGMTString()
}

const store = new Vuex.Store({
	modules: {
		design,
		page,
		auth,
		site,
		home
    },
    state: {
        siteCode: getCookie('site_group_code') || '',
    }
});

export default store;
