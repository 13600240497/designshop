const { Vue } = window;

/**
 *
 * @returns {Function}
 * @private
 */
function _$jsonp () {
    /**
     *
     * @returns {Function}
     * @private
     * @param {String} url 请求地址
     * @param {Object} params 请求参数
     * @param cache 是否缓存
     * @param jsonpCallback jsonp回调函数名
     * @param timeout 超时时间，默认 true
     */
    return (url, params, { cache = true, jsonpCallback = '', timeout = false } = {}) => {
        // jsonp 回调名字处理
        if (jsonpCallback === '') {
            // 获取组件ID作为后缀，如果找不到则用时间戳代替
            const ext = this.pid ? this.pid : this.$root.pageInstanceId ? this.$root.pageInstanceId : new Date().getTime();
            jsonpCallback = `geshop_callback_${ext}`;
        }
        try {
            return new Promise((resolve, reject) => {
                $.ajax({
                    url: url,
                    type: 'GET',
                    cache: cache,
                    dataType: 'jsonp',
                    data: params,
                    timeout: timeout,
                    jsonpCallback: jsonpCallback,
                    success: function (res) {
                        if ((res.code === 0 || res.code === '0') && res.data && res.data.goods_list) {
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
        } catch (err) {
        }
    };
}

/**
 * 通过API获取远端商品数据
 * @param vm vue组件实例
 * @returns {boolean | Promise<any | never>}
 * @param options 传入配置参数 page_no
 */
Vue.prototype.$GESHOP_DATA_FN = (vm, options) => {
    if (!vm) {
        return false;
    }
    // page_size组件分页信息,sort_id商品排序id
    const { is_pagination = 0, page_size = 20, sort_id = '' } = vm.data;
    // 组件商品运营信息
    const { goods_source_type, goods_source_info } = vm.data;
    // 默认配置
    const defaultOption = {
        page_size: is_pagination === 1 ? 20 : page_size,
        page_no: is_pagination === 1 ? options.page_no : 1,
        rule_id: goods_source_type === 'single' ? goods_source_info.sop_rule_id : '',
        sort_id: sort_id
    };
    const config = Object.assign({}, defaultOption, options);
    if (!config.rule_id) {
        return false;
    }

    // 站点信息 country_code 国家简码, od （cookie_id,用户id）
    const {
        country_code = '',
        od = '',
        platform = 'web',
        bts_unique_id = ''
    } = vm.$store.state.zaful.siteInfo;

    const url_group = {
        'pc': GESHOP_INTERFACE.gesApi_pc_goods_getSopGoodsDetail.url,
        'wap': GESHOP_INTERFACE.gesApi_m_goods_getSopGoodsDetail.url
    };
    const url = window.GESHOP_PLATFORM === 'wap' || window.GESHOP_PLATFORM === 'app' ? url_group.wap : url_group.pc;
    let params = {
        'site_code': window.GESHOP_SITECODE || '', // 站点编码，ZF/RG
        'lang': window.GESHOP_LANG || 'en', // 当前页面选中的语种，默认英语
        'pipeline': window.GESHOP_PIPELINE || '', // 当前页面渠道
        'rule_id': config.rule_id, // 商品运营平台规则ID
        'sort_id': config.sort_id, // 商品排序规则
        'agent': platform, // 来源平台
        'country_code': country_code, // 访问用户国家简码
        'cookie_id': od, // 用户cookie ID
        'bts_unique_id': bts_unique_id, // 实验分流
        'page_size': config.page_size, // 分页大小
        'page_no': config.page_no, // 页码
        'env': window.GESHOP_PAGE_TYPE || '1' // 页面环境
    };

    return _$jsonp.call(vm)(url, params).then(function (res) {
        if (res && res.data && res.data.goods_list) {
            return res;
        }
    });
};
