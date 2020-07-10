import Vue from 'vue';
import Router from 'vue-router';
import store from '../store/design-index.js';
import { message } from 'ant-design-vue';

Vue.use(Router);

// 基础布局
import base from '../components/layouts/antd-layout.vue';

export const constantRouterMap = [
    {
        path: '/',
        redirect: '/activity',
    },
    {
        path: '/base/language-package',
        component: base,
        meta: { name: '多语言管理', },
        redirect: '/base/language-package/index',
        children: [
            {
                path: 'index',
                meta: { name: '多语言管理', show_bread: false, },
                component: () => import('../views/languageManage/index.vue')
            },
            {
                path: 'keys',
                meta: { name: '语言包管理', show_bread: true, },
                component: () => import('../views/languageManage/keys-list.vue')
            }
        ]
    },
    {
        path: '/activity',
        component: base,
        meta: { name: '专题活动页' },
        redirect: '/activity/index',
        children: [
            {
                path: 'index',
                meta: { name: '专题活动管理', show_bread: false, },
                component: () => import('../views/activity/list.vue')
            }
        ]
    },
    {
        path: '/home',
        component: base,
        redirect: '/home/index',
        meta: { name: '首页管理' },
        children: [
            {
                path: 'index',
                meta: { name: '首页管理', show_bread: false, },
                component: () => import('../views/home/list.vue')
            }
        ]
    },
    {
        path: '/design/native',
        meta: { name: '装修页' },
        component: () => import('../views/design/index.vue')
    }
    // { path: '*', redirect: '/404', hidden: true }
]

// 路由实例
const router = new Router({
    routes: constantRouterMap
});

// 路由守卫
router.beforeEach((to, from, next) => {

    // 如果是装修页
    if (to.path === '/activity/zf/native-design/native222') {

        // 获取URL参数
        const { group_id = '', pipeline = '', lang = '', platform = 'm' } = to.query;

         // 拦截非法请求
         if (group_id === '' || pipeline === '' || lang === '' || platform === '') {
            message.error('非法请求');
            next(false);
            return false;
        }
   
        // 请求页面数据
        store.dispatch('design/page_load', {
            group_id,
            pipeline,
            lang,
            platform,
            callback (res) {
                next();
                // if (res.code === 0) {
                //     next();
                // } else {
                //     window.location.href = '/';
                // }
            }
        });
    } else {
        next();
    }
});

export default router;

