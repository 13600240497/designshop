import Vuex from 'vuex';
import Vue from 'vue';
import page from './modules/page'; // 页面模块
import global from '../../../node-server/src/store/modules/global'; // 页面全局功能
import growingio from '../../../node-server/src/store/modules/growing-io'; // 埋点

Vue.use(Vuex);

const store = new Vuex.Store({
    modules: {
        global,
        page,
        growingio
    },
    mutations: {

    },
    actions: {

    },
    state: {}
});

export default store;
