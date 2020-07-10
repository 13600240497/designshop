/**
 * 全局获取页面商品数据
 * @class PageGoods
 * @constructor
 * @param {String} site_code - 站点编码
 * @param {String} lang - 语言编码
 * @param {String} pipeline - 渠道编码
 * @param {String} page_id - 页面ID
 * @param {String} platform - 设备端[pc/wap/app/web]
 * @param {timestamp} server_timestamp - 页面发布时间
 * @param {Object} interfaces - 页面接口集合
 * @description
 *  1. 目前适用站点 ZF, DL
 *  2. 目前拥有2个数据源（JSON文件和API接口）
 * @author Cullen
 * @date 2020-05-30
 */
class PageGoods {
    constructor ({
        site_code = '',
        lang = '',
        pipeline = '',
        page_id = '',
        platform = 'pc',
        server_timestamp = '',
        interfaces = {}
    }) {
        // 基础参数
        this.state = {
            site_code,
            lang,
            pipeline,
            page_id,
            platform,
            server_timestamp
        };
        // 接口集合
        this.interfaces = interfaces;
    }

    /**
     * 获取远端数据
     * @param {String} type - 根据类型获取不同的数据源，可选值[api/json]
     * @returns {Promise}
     */
    getRemoteData ({ type = 'api' }) {
        return type == 'api' ? this._getApiData() : this._getJsonData();
    }

    /**
     * 获取API的数据
     * @returns {Promise}
     */
    _getApiData () {
        const url = this._getInterfaceURL(this.state.platform);
        // 请求参数
        const requestData = {
            site_code: this.state.site_code,
            lang: this.state.lang,
            pipeline: this.state.pipeline,
            page_id: this.state.page_id
        };
        return new Promise((resolve, reject) => {
            // 请求商品数据接口
            $.ajax({
                url: url,
                type: 'GET',
                dataType: 'jsonp',
                data: requestData,
                timeout: false
            }).done((res) => {
                if (res.code === 0) {
                    resolve(res.data);
                } else {
                    reject(res);
                }
            }).fail((err) => {
                reject(err);
            });
        });
    }

    /**
     * 获取JSON文件的数据
     * @returns {Promise}
     */
    _getJsonData () {
        // 页面参数
        const page_id = this.state.page_id;
        const server_timestamp = this.state.server_timestamp;
        const platform = this.state.platform;
        // 时间
        const now = new Date();
        const year = now.getFullYear();
        const month = now.getMonth() + 1;
        const day = now.getDate();
        const hour = now.getHours();
        const timestamp = `${year}${month}${day}${hour}`;
        // 获取后台发布页面的时间戳
        return new Promise((resolve, reject) => {
            // 请求JSON文件
            let url = `async-data-${page_id}.json?v=${server_timestamp}&timestamp=${timestamp}`;
            if (platform === 'app') {
                url = url + `&is_app=1`;
            }
            $.ajax(url).done((res) => {
                resolve(res);
            }).fail((err) => {
                reject(err);
            });
        });
    }

    /**
     * 根据设备端获取接口的URL链接
     * @param {String} platform [pc/web/app/wap]
     * @returns {URL}
     */
    _getInterfaceURL (platform) {
        if (platform == 'pc' || platform == 'web') {
            return this.interfaces.gesApi_pc_goods_getAutoRefreshUiGoodsList.url;
        }
        if (platform == 'wap' || platform == 'app') {
            return this.interfaces.gesApi_m_goods_getAutoRefreshUiGoodsList.url;
        }
    }
}

export default PageGoods;
