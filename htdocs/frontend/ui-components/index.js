import Vue from 'vue'
window.Vue = Vue;


import U000212 from './src/U000212/main.vue'


const components = [
    U000212,
]


// 全局注册
components.forEach(component => {
    Vue.component(component.name, component);
});
