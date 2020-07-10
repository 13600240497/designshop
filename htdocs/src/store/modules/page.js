// import { Modal } from 'ant-design-vue'
const page = {
    namespaced: true,
    state: {
        // 环境变量，1=装修，2=预览, 3=发布
        env: 1,
        // 当前页面信息
        info: {
            page_id: 0,         // 页面ID
            group_id: 0,        // 当前页面的唯一ID
            title: '',          // 页面标题
            pipeline: '',       // 当前页面渠道
            lang: '',           // 当前页面选中的语种，默认英语,
            site_code: '',      // 站点编码，ZF/RG
            platform: '',       // 设备终端，[pc/m],
            activity_id: '',    // 活动ID
            od: '',  // 千人千面需求，大数据生成的用户ID
            country_code: '',    // 用户访问的国家,
            bts_unique_id: ''   // 实验分流标示
        },
        relations: [], // 可用设备端口
        pipelines: [], // 可用渠道

        // 布局和组件基础数据
        components: {}, // 组件信息
        layouts: [], // 布局信息

        // 存放API层的数据
        goodsSKU: [], // 当前页面所有组件的商品类型数据源

        // 页面数据
        remote_data_loaded: false, // 页面远端数据是否加载完毕
        remote_data: [], // 页面远端数据， { 1: {}, 2: {} }

        languages: [], // 页面语言包
        preview_url: '', // 预览链接
        isNewGuys: true, // 是否新老用户，1=新用户, 2=老用户, 通过 cookie['WEBF-dan_num'] 订单量来判断
    },

    mutations: {
        /**
         * 更新页面布局信息
         * @param {Object} state
         * @param {Array} layouts 布局信息数组
         * [1,2,3,4,5,{ id: 111, groups: [222,333] }]
         */
        update_layout (state, layouts) {
            state.layouts = layouts.map(x => x.id);
        },

        /**
         * 增加组件
         * @param {Object} state 
         * @param {Object} data 组件数据
         */
        component_add (state, data) {
            state.components.push(data);
        },
        
        /**
         * 删除组件
         * @param {object} state 
         * @param {number} id 组件ID
         */
        component_delete (state, id) {
            // 删除组件数据
            state.components = state.components.filter(x => x.id !== id);
            // 删除组件布局
            state.layouts = state.layouts.filter(xid => xid !== id);
        },

        /**
         * 更新组件数据
         * @param {object} state 
         * @param {object} data 组件数据
         */
        component_update (state, data) {
            state.components = state.components.map(x => {
                if (x.id === data.id) {
                    return data;
                } else {
                    return x;
                }
            });
        }
    },

    actions: {
        /**
         * 加载页面数据
         * @param {String} group_id 页面分组ID
         * @param {String} pipeline 渠道
         * @param {String} lang 语言
         * @param {String} platform 设备终端
         * @param {String} title 页面标题
         * @param {Array} relations 可支持设备终端数据
         * @param {Array} pipelines 可用的渠道列表
         * @param {Array} layouts 页面布局信息
         * @param {Object} components 页面组件信息
         * @param {Object} languages 组件的语言包信息
         * @param {Number} env 页面环境变量
         * @param {Array} goodsSKU 页面的商品数据
         */
        load ({ state }, data) {
            const {
                page_id,
                group_id,
                pipeline,
                lang,
                site_code,
                platform,
                title,
                relations,
                layouts,
                components,
                pipelines,
                languages,
                preview_url,
                env,
                goodsSKU,
                activity_id
            } = data;
            // update store value
            state.env = env; // 页面环境变量
            state.info.page_id = page_id;
            state.info.group_id = group_id;
            state.info.title = title;
            state.info.pipeline = pipeline;
            state.info.lang = lang;
            state.info.site_code = site_code;
            state.info.platform = platform;
            state.info.activity_id = activity_id; // 活动ID
            state.relations = relations; // 设备终端
            state.layouts = layouts; // 布局
            state.components = components; // 组件
            state.pipelines = pipelines; // 可用渠道
            state.languages = languages; // 语言包
            state.preview_url = preview_url; // 预览链接
            state.goodsSKU = goodsSKU; // 页面的商品数据
        },

        /**
         * 通过API获取远端商品数据
         * @param {Number} params.is_first 是否首次加载, 1=是
         * @param {object} params.data 额外参数
         * @param {function} params.callback 回调函数
         */
        load_remote_goods_data ({ state, dispatch }, params = {}) {

            // 装修预览和发布页的接口区分
            let url = state.env == 3 ? window.GESHOP_INTERFACE.geshopApi_page_asyncInfo.url : window.GESHOP_INTERFACE.geshopApi_design_asyncInfo.url;
            // 解决跨域问题，用的JSONP
            const dataType = 'jsonp';
            
            // 基础的请求参数
            const basic_request = {
                page_id: state.info.page_id,
                site_code: state.info.site_code,
                pipeline: state.info.pipeline,
                lang: state.info.lang,
                cookie_id: state.info.od,
                bts_unique_id: state.info.bts_unique_id,
                country_code: state.info.country_code,
                user_group: state.isNewGuys ? 0 : 1,
                env: state.env,
                agent: 'wap'
            };

            // 如果是第一次请求
            if (params.is_first) {
                basic_request.is_first = params.is_first;
            }

            // 合并请求的参数
            const merge_request = Object.assign(basic_request, params.data || {});

            // 获取远端数据
            $.ajax({
                url: url,
                type: 'POST',
                data: merge_request,
                dataType: dataType,
                success: (res) => {
                    // 如果是有回调函数的则执行
                    if (params.callback) {
                        return params.callback(res);
                    }
                    // 如果没回调函数则执行下面的数据
                    if (res.code != 0) {
                        return dispatch('load_local_goods_data');
                    }

                    // 遍历
                    Object.keys(res.data).map(component_id => {
                        // 如果是商品数据类型的话
                        if (res.data[component_id].hasOwnProperty('skuInfo') == true) {
                            // ajax数据源列表
                            const source_list = res.data[component_id].skuInfo;
                            // 遍历ajax数据源
                            Array.isArray(source_list) && source_list.map(item => {
                                // 如果有的则覆盖, 没有则追加
                                state.goodsSKU = state.goodsSKU.filter(x => parseInt(x.id) != parseInt(item.id));
                                state.goodsSKU.push(Object.assign({}, item));
                            });
                        } else {
                            state.remote_data.push({
                                component_id,
                                data: res.data[component_id]
                            });
                        }
                    });
                    // 远端数据加载成功标记
                    state.remote_data_loaded = true;
                },
                error: () => {
                    dispatch('load_local_goods_data');
                }
            });
        },

        /**
         * 当页面数据API调取失败的时候，再去页面同步数据
         */
        load_local_goods_data ({ state }) {
            // 兜底同步商品数据到store
            [...state.components].map(cmpt => {
                Array.isArray(cmpt.goodsSKU) && cmpt.goodsSKU.map(item => {
                    state.goodsSKU.push(Object.assign({}, item));
                });
            });
            state.remote_data_loaded = true;
        },
    }
};

export default page;
