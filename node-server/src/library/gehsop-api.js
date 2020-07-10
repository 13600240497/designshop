/**
 * GESHOP-API 模块
 * @description
 * 测试服务域名：http://test.geshop-api.com.release_sop.php7.egomsl.com
 * 生产域名：xxxxxxx
 * @date 2019-11-10
 * @author Cullen
 */
import axios from 'axios';

/**
 * axios 实例
 */
const fetch = axios.create({
    baseURL: 'http://test.geshop-api.com.release_sop.php7.egomsl.com',
    timeout: 300000 // 请求超时
});

/**
 * 获取页面所有组件的数据
 * @param {} page_id 11745
 * @param {} site_code zf-wap
 * @param {} pipeline ZF
 * @param {} lang en
 */
const get_page_goods_info = (params) => {
	return fetch({
		url: '/geshop/design/asyncInfo',
		method: 'GET',
		params,
	})
}

export default {
    get_page_goods_info
}