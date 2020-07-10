import Vue from 'vue'
import ElementUI from 'element-ui'
import 'element-ui/lib/theme-chalk/index.css'
import '../resources/font-awesome/css/font-awesome.min.css'
import Moment from 'vue-moment'
import App from './components/ActivityGB.vue'

Vue.use(ElementUI)
Vue.use(Moment)

new Vue({
	el: '#app',
	components: { App }
})
