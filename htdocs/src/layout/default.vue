<template>
     <a-layout id="components-layout-demo-custom-trigger">

        <!-- 侧边栏 -->
        <a-layout-sider
            width="220"
            :style="{
                overflow: 'auto',
                height: '100vh',
                position: 'fixed',
                left: 0
            }"
            theme="light"
            :trigger="null"
            collapsible
            v-model="collapsed">
            <div class="logo" />
            <slide-menu></slide-menu>
        </a-layout-sider>

        <site-header></site-header>
    
        <!-- 路由内容 -->
        <a-layout :style="{ margin: collapsed ? '0px 0px 0px 80px' : '0px 0px 0px 220px' }">
            <a-layout-content
                :style="{ margin: '24px 16px', padding: '24px', background: '#fff', minHeight: '280px' }">
                <router-view></router-view>
            </a-layout-content>
        </a-layout>

    </a-layout>
</template>

<script>
import header from '../components/layouts/antd-header.vue';
import menu from '../components/antd-menu/index.vue'

export default {
    data () {
        return {
            collapsed: false,
        }
    },
    components: {
        'slide-menu': menu,
        'site-header': header
    }
}
</script>

<style>
#components-layout-demo-custom-trigger .trigger {
    font-size: 18px;
    line-height: 64px;
    padding: 0 24px;
    cursor: pointer;
    transition: color .3s;
}

#components-layout-demo-custom-trigger .trigger:hover {
    color: #1890ff;
}

#components-layout-demo-custom-trigger .logo {
    height: 32px;
    background: rgba(255,255,255,.2);
    margin: 16px;
}
</style>

