import Vue from 'vue'
import ElementUI from 'element-ui'
import 'element-ui/lib/theme-chalk/index.css'
import Moment from 'vue-moment'
import App from './components/HomePageZF.vue'

Vue.use(ElementUI)
Vue.use(Moment)

new Vue({
	el: '#app',
	components: { App }
})