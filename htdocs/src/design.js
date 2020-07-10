import Vue from 'vue'
import ElementUI from 'element-ui'
import 'element-ui/lib/theme-chalk/index.css'
import App from './components/Design.vue'

Vue.use(ElementUI)

new Vue({
	el: '#app',
	components: { App }
})
