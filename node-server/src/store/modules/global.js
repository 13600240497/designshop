import PageGoods from '../../library/global-page-goods';

const global = {
    namespaced: true,
    state: {
        // APP 是否已经登录 0 非app、 1 已经登录 2 未登录
        isAppLogin: 0,
        // 是否发布页请求之后返回数据
        isDateRes: false,
        // 商品组件的商品详情
        goodsInfo: {}
    },
    mutations: {
        /**
         * 更新商品组件的商品详情 存放store session 和 window
         * @param state
         * @param data
         * @constructor
         */
        UPDATE_GOODS_INFO (state, data) {
            // 分别存储数据用于调试或者取值
            window.GESHOP_ASYNC_DATA_INFO = data;
            state.goodsInfo = data;
            // sessionStorage.goodsInfo = JSON.stringify(data);
            // 页面请求数据返回之后改变状态
            state.isDateRes = true;
        }
    },
    actions: {
        /**
         * 页面初始化 async_goods_init
         * @version 1.0
         * 1. 页面图片懒加载激活
         * 2. 币种切换激活
         * */
        async_goods_init ({ commit }, vm) {
            try {
                // 判断当前vue对象挂在在对象上还是对象属性上
                const vue = typeof vm._that !== 'undefined' ? vm._that : vm;
                vue.$nextTick(() => {
                    // const images = $(vm.$el).find('img.js_gdexp_lazy');
                    // // imgFilter 过滤已加载数据
                    // if (vm.imgFilter && vm.imgFilter === true) {
                    //     let filterImages = images.filter((index, element) => {
                    //         return $(element).attr('src') !== $(element).attr('data-original');
                    //     });
                    //     window.GS_GOODS_LAZY_FN && window.GS_GOODS_LAZY_FN(filterImages);
                    // } else {
                    //     // display 为 none的元素不进行懒加载
                    //     if (vm.type && vm.type === 2) {
                    //         window.GEShopCommonFn_Vue.lazyload(images);
                    //     } else {
                    //         // display 为 none的元素也进行懒加载
                    //         // 渲染图片
                    //         window.GS_GOODS_LAZY_FN && window.GS_GOODS_LAZY_FN(images);
                    //     }
                    // }
                    window.GS_GOODS_LAZY_FN && window.GS_GOODS_LAZY_FN();

                    // 渲染货币
                    // 首页兼容
                    window.GEShopSiteCommon && (typeof window.GEShopSiteCommon.renderCurrency_v2 != 'undefined' ? window.GEShopSiteCommon.renderCurrency_v2() : window.GEShopSiteCommon.renderCurrency());
                });
            } catch (e) {
                console.log(e);
            }
        },

        /**
         * 页面初始化 async_goods_init
         * @version 2
         * @description
         * 1. 页面图片懒加载激活优化，只针对还没有切换的图片，只针对单个组件容器的图片元素
         * 2. 币种切换激活，只针对单个组件容器的价格元素
         * */
        async_goods_init_v2 ({ commit }, vm) {
            vm.$nextTick(() => {
                // 图片懒加载
                /* const images_element = $(vm.$el).find('img.js_gdexp_lazy').filter((i, x) => {
                    return $(x).attr('data-original') != $(x).attr('src');
                });

                window.GS_GOODS_LAZY_FN && window.GS_GOODS_LAZY_FN(images_element); */
                window.GS_GOODS_LAZY_FN && window.GS_GOODS_LAZY_FN();

                if (window.GEShopSiteCommon) {
                    // 货币切换
                    if (!GEShopSiteCommon.getSearch('is_app')) {
                        if (window.hasOwnProperty('GLOBAL')) {
                            window.GLOBAL.currency.change_html('', $(vm.$el));
                        }
                        if (window.hasOwnProperty('FUN')) {
                            window.FUN.currency.change_html('', $(vm.$el));
                        }
                    } else {
                        window.hasOwnProperty('getCurrencyInfoInGEShop') && window.getCurrencyInfoInGEShop();
                    }
                }
            });
        },

        /**
         * 原生APP版本的商品组件初始化
         * @param {*} param0
         * @param {*} vm
         */
        async_goods_init_2 ({ commit }, vm) {
            const vue = typeof vm._that !== 'undefined' ? vm._that : vm;
            vue.$nextTick(() => {
                // 图片懒加载
                const images = $(vm.$el).find('img.js_gdexp_lazy').filter((i, x) => {
                    return $(x).attr('data-original') != $(x).attr('src');
                });
                try {
                    if ($.fn.lazyload) {
                        $(images).lazyload({
                            threshold: 100,
                            effect: 'fadeIn',
                            failure_limit: 20
                        });
                    } else {
                        window.GS_GOODS_LAZY_FN();
                    }
                } catch (err) {
                    images.map((i, x) => {
                        $(x).attr('src', $(x).attr('data-original'));
                    });
                }
                // 渲染货币
                window.GLOBAL.currency.change_html(null, vm.$el);
            });
        },

        /**
         * 图片懒加载功能抽离
         * v2.2.2 新增：版本秒杀组件水平滚动无法触发 jqery.lazyload 插件，所以单独抽出来实现
         * 将会在：v2.2.3 版本删除，因为站点升级了懒加载模块
         * @param {Object} jQueryDom JQ的DOM元素
         */
        lazyload_img_by_dom ({ commit }, jQueryDom) {
            // 过滤相同的路径
            const images = $(jQueryDom).filter((i, x) => {
                return $(x).attr('data-original') != $(x).attr('src');
            });
            try {
                $(images).lazyload({
                    threshold: 100,
                    effect: 'fadeIn',
                    failure_limit: 20
                });
            } catch (err) {
                images.map((i, x) => {
                    $(x).attr('src', $(x).attr('data-original'));
                });
            }
        },

        /**
         * 页面加载成功调用，去除 loading 的遮罩层
         * @param {Object} commit
         * @param {Object} vm vue 实例
         */
        loaded ({ commit }, vm) {
            if (vm === undefined) {
                return false;
            }
            const pid = vm.$root.pageInstanceId;
            const key = vm.$root.compKey;
            $(`#${key}_${pid}_container`).removeClass('is-preloading');
        },

        // 存放当前组件信息
        saveCurrentGoodsInfo ({ commit }, data) {
            try {
                sessionStorage['currentGoodsInfo'] = JSON.stringify(data);
            } catch (e) {

            }
        },

        // 删除当前组件信息
        deleteCurrentGoodsInfo ({ commit }, data) {
            try {
                sessionStorage.removeItem('currentGoodsInfo');
            } catch (e) {

            }
        },

        /**
         * 请求页面数据
         * @param {Function} commit
         */
        updateNowInfo ({ commit }) {
            // 实例化对象
            const pageData = new PageGoods({
                site_code: window.GESHOP_SITECODE,
                lang: window.GESHOP_LANG,
                pipeline: window.GESHOP_PIPELINE,
                page_id: window.GESHOP_PID,
                platform: window.GESHOP_PLATFORM,
                interfaces: window.GESHOP_INTERFACE,
                server_timestamp: window.GESHOP_PUBLISHED_TIME || ''
            });

            // 获取站点编码
            const siteCode = window.GESHOP_SITECODE ? window.GESHOP_SITECODE.split('-')[0] : '';

            // 请求数据
            pageData.getRemoteData({
                type: (siteCode === 'zf' || siteCode == 'dl') ? 'api' : 'json'
            }).then(data => {
                commit('UPDATE_GOODS_INFO', Object.assign({}, data));
            }).catch(() => {
                commit('UPDATE_GOODS_INFO', Object.assign({}, window.GESHOP_ASYNC_DATA_INFO));
            });
        },

        /**
         * 用户人群分组
         * @param commit
         * @param vm
         */
        userGroupHandle ({ commit }, vm) {
            vm.$nextTick(() => {
                setTimeout(() => {
                    typeof GESHOP_UTIL.userGroupHandle === 'function' && GESHOP_UTIL.userGroupHandle(vm);
                }, 500);
            });
        }
    }
};

export default global;
