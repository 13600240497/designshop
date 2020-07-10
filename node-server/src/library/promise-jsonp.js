const { Vue } = window;

/**
 * 封装全局 jsonp 函数，支持 promoise 特性
 * @description
 * 自带2个全局参数:
 * lang 语种，默认 en
 * pipeline 当前渠道
 * @param {String} url 请求地址
 * @param {Object} params 请求参数
 * @param {Object} cache 是否缓存
 * @param {number} timeout 超时时间，默认 true
 * @param {string} jsonpCallback jsonp回调函数名 
 */
Vue.prototype.$jsonp = function (url, params, { cache = true, jsonpCallback = '', timeout = false } = {}) {
    // 全局封装 "语言"，"渠道" 字段
    const data = Object.assign(params, {
        lang: GESHOP_LANG,
        pipeline: (typeof GESHOP_PIPELINE !== 'undefined' ? GESHOP_PIPELINE : '')
    });

    // 传参封装
    const requestData = {
        content: JSON.stringify(data)
    };

    // jsonp 回调名字处理
    if (jsonpCallback === '') {
        // 获取组件ID作为后缀，如果找不到则用时间戳代替
        const ext = this.pid ? this.pid : this.$root.pageInstanceId ? this.$root.pageInstanceId : new Date().getTime();
        jsonpCallback = `geshop_callback_${ext}`;
    }
    
    return new Promise((resolve, reject) => {
        $.ajax({
            url: url,
            type: 'GET',
            cache: cache,
            dataType: 'jsonp',
            data: requestData,
            timeout: timeout,
            jsonpCallback: jsonpCallback,
            success: function (res) {
                if (res.code === 0 || res.code === '0') {
                    resolve(res);
                } else {
                    reject(res);
                }
            },
            error: function (res) {
                reject(res);
            }
        });
    });
};

/**
 * 获取页面兜底数据
 * @param {String} page_id 页面ID
 * @param {string} pid 组件ID
 */
Vue.prototype.$backupData = function (page_id, pid) {
    const url = `fallback/${page_id}/${pid}.json`;
    return new Promise((resolve, reject) => {
        $.ajax({
            url: url,
            type: 'GET',
            success: function (res) {
                resolve(res);
            },
            error: function () {
                resolve({});
            }
        });
    });
};
