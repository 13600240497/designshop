// 引入axios
import axios from 'axios'
import qs from 'qs'
import { Message } from 'element-ui'
import 'element-ui/lib/theme-chalk/index.css'

let cancel
let promiseArr = {}
const CancelToken = axios.CancelToken
// 请求拦截器
axios.interceptors.request.use(config => {

	config.baseURL = location.hostname == 'www.geshop.com.wujianeng.dev.local.com' && location.port=='3001' ? '/proxy/' : '';

	// 发起请求时，取消掉当前正在进行的相同请求
	if (promiseArr[config.url]) {
		if (!config.addOp || !config.addOp.parallel) {
			promiseArr[config.url]('操作取消')
			promiseArr[config.url] = cancel
		}

	} else {
		promiseArr[config.url] = cancel
	}
	return config
}, error => {
	return Promise.reject(error)
})

// 响应拦截器即异常处理
axios.interceptors.response.use(response => {
	return response
}, error => {
	if (error && error.message == '操作取消') {
		return Promise.reject(error)
	}
	if (error && error.response) {
		switch (error.response.status) {
		case 400:
			error.message = '错误请求'
			break
		case 401:
			error.message = '未授权，请重新登录'
			break
		case 403:
			error.message = '拒绝访问'
			break
		case 404:
			error.message = '请求错误,未找到该资源'
			break
		case 405:
			error.message = '请求方法未允许'
			break
		case 408:
			error.message = '请求超时'
			break
		case 500:
			error.message = '服务器端出错'
			break
		case 501:
			error.message = '网络未实现'
			break
		case 502:
			error.message = '网络错误'
			break
		case 503:
			error.message = '服务不可用'
			break
		case 504:
			error.message = '网络超时'
			break
		case 505:
			error.message = 'http版本不支持该请求'
			break
		default:
			error.message = `连接错误${error.response.status}`
		}
	} else {
		error.message = '连接到服务器失败'
	}
	Message.error(error.message)
	return Promise.reject(error.response)
})

axios.defaults.baseURL = ''
// 设置默认请求头
axios.defaults.headers = {
	'X-Requested-With': 'XMLHttpRequest',
	'Content-Type': 'application/x-www-form-urlencoded'
}
axios.defaults.timeout = 30000

export default {
	// get请求
	get (url, param, addOp) {
		return new Promise((resolve, reject) => {
			axios({
				method: 'get',
				url,
				params: param,
				addOp: addOp,
				cancelToken: new CancelToken(c => {
					cancel = c
				})
			}).then(res => {
				if (res.data.code !== 0) {
					Message.error(res.data.message)
				}

				resolve(res.data)
			}).catch(res => {
				reject(res)
			})
		})
	},
	// post请求
	post (url, param, options) {
		return new Promise((resolve, reject) => {
			const data = options && options.type === 'file' ? param : qs.stringify(param)
			const timeout = options && options.timeout ? options.timeout : '30000'
			axios({
				method: 'post',
				url,
				data: data,
				timeout: timeout,
				cancelToken: new CancelToken(c => {
					cancel = c
				}),
				onUploadProgress: (progressEvent) => {
					if (options && options.progressFn) {
						let complete = (progressEvent.loaded / progressEvent.total * 100 | 0) + '%'
						options.progressFn(complete)
					}
				}
			}).then(res => {
				if (res.data.code !== 0 && !(options && options.messageOff)) {
					Message.error(res.data.message)
				} else {
					if (!(options && options.successOff)) {
						Message.success(res.data.message)
					}
				}

				resolve(res.data)
			}).catch(res => {
				if (window.GESHOP_FULL_LOADING) {
                    window.GESHOP_FULL_LOADING.close();
				}
				reject(res)
			})
		})
    },
    // 新窗口 POST 文件
    postDownLoad (url, param, options) {
        return new Promise((resolve, reject) => {
			const data = options && options.type === 'file' ? param : qs.stringify(param)
			const timeout = options && options.timeout ? options.timeout : '30000'
			axios({
				method: 'post',
				url,
				data: data,
				timeout: timeout,
				cancelToken: new CancelToken(c => {
					cancel = c
				}),
				onUploadProgress: (progressEvent) => {
					if (options && options.progressFn) {
						let complete = (progressEvent.loaded / progressEvent.total * 100 | 0) + '%'
						options.progressFn(complete)
					}
				}
			}).then(res => {
				resolve(res)
			}).catch(res => {
				reject({ code: 1 })
			})
		})
    }
}
