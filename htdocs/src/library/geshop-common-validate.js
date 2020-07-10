import { message, Modal } from 'ant-design-vue'
import { get_siteVerificationVerify } from '../plugin/api.js';

/**
 * 字符串内容格式化
 * create by Cullen 2019/12/17
 * @description 
 * 1. 去除空格
 * 2. 去除回车，替换成逗号
 * 3. 去除连续2个逗号
 * @param {String} content 传入内容，字符串
 * @returns {String}
 */
const trim = (content = '') => {
    return content.replace(/(^\s*)|(\s*$)/g, '').split(' ').join('').replace(/\n/g, ',').replace(',,', ',');
}

/**
 * 数组去重
 * create by Cullen 2019/12/17
 * @param {Array} list
 * @returns {Array}
 */
const unique = (list = []) => {
    if (Array.isArray(list)) {
        return [...new Set(list)];
    } else {
        return [];
    }
}

/**
 * 获取数组重复的数据
 * create by Cullen 2019/12/17
 * @param {Array} list 
 * @returns {Array}
 */
const duplicate = (list = []) => {
    if (Array.isArray(list)) {
        const repeat = list.filter(x => {
            const res = list.filter(y => x === y);
            return res.length >= 2;
        });
        return unique(repeat);
    } else {
        return [];
    }
}

/**
 * 根据规则获取错误提示
 * create by Cullen 2019/12/17
 * @param {Array} invalid 错误数据
 * @param {String} rule 规则，默认为 default
 */
const get_tips_by_rule = (invalid = [], rule = 'default') => {
    const str = invalid.join(', ');
    // 配置中心
    const map = {
        default: '',
        GOODS_VALIDATE_EXITS: `商品SKU ${str} 不存在，是否清空？`,
        GOODS_VALIDATE_ON_SALE: `商品SKU ${str} 已下架，是否清空？`,
        GOODS_VALIDATE_STOCK: `商品SKU ${str} 库存(包含虚拟和真实库存), 库存小于等于0为无效SKU, 是否清空？`,
        GOODS_VALIDATE_REAL_STOCK: `商品SKU ${str} 真实库存小于等于0为无效SKU, 是否清空？`,
        GOODS_VALIDATE_SECKILL: `商品SKU ${str} 不是秒杀商品，是否清空？`,
        GOODS_VALIDATE_NEW_USER_PRICE: `商品SKU ${str} 没有新人专享价，是否清空？`,
        GOODS_VALIDATE_APP_PRICE: `商品SKU ${str} 没有APP专享价，是否清空？`,
        GOODS_VALIDATE_GIFT: `商品SKU ${str} 不是赠品，是否清空？`,
        GOODS_VALIDATE_PRE_PROMOTION: `${str} 不是预促销SKU，是否清空？`,
        GOODS_VALIDATE_REDEEM: `${str} 不是积分兑换商品SKU，是否清空？`,
        GOODS_VALIDATE_SAME_SPU: `${str} 为同款商品SKU，是否清空？`,
        CATEGORY_VALIDATE_EXITS: `商品分类id ${str} 不存在，是否清空？`,
        COUPON_VALIDATE_EXITS: `优惠码 ${str} 不存在，是否清空？`,
        COUPON_VALIDATE_EXPIRED: `优惠码 ${str} 已过期，是否清空？`,
        COUPON_VALIDATE_FINISHED: `优惠码 ${str} 已领完，是否清空？`,
        COUPON_VALIDATE_TYPE: `优惠码 ${str} 为不是指定类型的优惠码，是否清空？`,
        COUPON_VALIDATE_ID_EXITS: `优惠券ID ${str} 不存在，是否清空？`,
        PRICE_VALIDATE_ID_EXITS: `秒杀活动ID ${str} 不存在，是否清空？`,
        PRICE_VALIDATE_TIME_CONSISTENT: `秒杀活动 ${str} 活动时间不一致, 请检查ID活动时间！`
    };
    return map[rule] || '';
};

/**
 * 原生APP专题统一检验模块
 * create by wubinbin on 2019/9/2
 * 参看API文档：http://yapi.geshop.php7.egomsl.com/project/22/interface/api/49
 */
class GEShopCommonValidFn {

    /**
     * 构造器
     * @param {String} params.site_code 站点编码
     * @param {String} params.pipeline 渠道
     * @param {String} params.lang 语言
     * @param {String} params.client 设备终端
     */
    constructor ({ site_code, pipeline, lang, client, store = null }) {
        this.store = store;
        this.site_code = site_code || '';
        this.pipeline = pipeline || '';
        this.lang = lang || '';
        this.client = client || '';
        this.sku = ''; // 商品sku
        this.errorRule = [] // 错误规则
        this.price_sys_ids = '';
    }

    /**
     * 更新全局参数
     */
    update_global_fields () {
        if (this.store) {
            const info = this.store.state.page.info;
            this.site_code = info.site_code;
            this.pipeline = info.pipeline;
            this.lang = info.lang;
            this.client = info.client;
        }
    }

    /**
     * @param {string} SKU [商品sku]
     * @param {string} check_type [goods: 商品sku] [flashsale_id: 秒杀ID]
     * @param {function} success 成功
     * @param {function} filter{sku} 过滤不必要sku
     * @param {function} update(sku) 更新Sku (string)
     */
    designCommonValid (params) {
        
        // 校验传参
        this.params = params;
        this.sku = params.sku || '';
        this.check_type = params.check_type || 'goods';
        this.check_rules = params.check_rules || 'GOODS_VALIDATE_EXITS,GOODS_VALIDATE_ON_SALE';

        // 基础请求参数
        this.update_global_fields();
        // 页面全局参数 
        const request = {
            site_code: this.site_code,
            lang: this.lang,
            client: this.platform,
            pipeline: this.pipeline,
            check_type: this.check_type,
            check_rules: this.check_rules
        };

        // 判断校验类型
        switch (this.check_type) {
            case 'goods':
                /* 当SKU为空，则直接判定成功 */
                if (this.sku == '') {
                    params.success && params.success();
                    return true;
                }

                // SKU 格式调整
                this.sku = trim(this.sku);
                // 获取已去重的数据
                const unique_sku = unique(this.sku.split(','));
                // 获取重复的
                const duplicate_sku = duplicate(this.sku.split(','));
                
                // 提示重复的数据
                if (duplicate_sku.length > 0) {
                    // 更新已去重的的SKU;
                    this.sku = unique_sku.join(',');
                    message.error(duplicate_sku.join(',') + ' sku重复，已自动去重!');
                    this.params.update && this.params.update(this.sku); // 更新SKU
                    this.params.error && this.params.error(); // 错误回调
                    return false;
                };

                // 校验SKU最多可输入100个
                if (this.sku.split(',').length > 100) {
                    message.error('商品SKU最多可输入100个!');
                    this.sku = this.sku.split(',').slice(0, 100).join(',');
                    this.params.update && this.params.update(this.sku); // 更新SKU
                    this.params.error && this.params.error(); // 错误回调
                    return false;
                };

                request.goods_sku = this.sku;
                break;
            
            // 秒杀类型
            case 'price_sys':
                // 转数组
                let array_id = trim(params.price_sys_ids).split(',');
                // 去重
                array_id = unique(array_id);
                // 封装请求参数
                this.price_sys_ids = array_id.join(',');
                request.price_sys_ids = this.price_sys_ids;
                break;

            default:
                break;
        }

        // 请求统一接口
        get_siteVerificationVerify(request).then((res) => {
            if (res.code != 0) {
                const data = res.data;
                if (data) {
                    this.errorRule.push({
                        check_type: data.check_type,
                        fail_rule: data.fail_rule,
                        invalid: [...data.invalid_data]
                    });
                    this.errorRuleFn();
                }
                params.error && params.error();
            } else {
                params.success && params.success();
            }
        });

        return false;
    }

    // 校验失败
    errorRuleFn () {
        const self = this;
        // 获取错误的数据
        const invalid = this.errorRule[0].invalid;
        // 获取错误的规则
        const rule = this.errorRule[0].fail_rule;
        // 获取错误的提示
        const tips = get_tips_by_rule(invalid, rule);

        Modal.confirm({
            title: '提示',
            content: tips,
            onOk () {
                switch (self.check_type) {
                    case 'goods':
                        const sku = self.sku.split(',');
                        const _sku = sku.filter(item => {
                            return invalid.indexOf(item) < 0;
                        });
                        self.params.filter && self.params.filter(_sku.join(',')); // 过滤Sku
                        break;
                    case 'price_sys':
                        // 过滤回调
                        const res = self.price_sys_ids.split(',').filter(x => !invalid.includes(x));
                        self.params.filter && self.params.filter(res.join(','));
                        break;
                    default:
                        break;
                }
                self.errorRule = [];
            },
            onCancel () {
                self.errorRule = [];
            }
        });
    }
}

export default GEShopCommonValidFn;
