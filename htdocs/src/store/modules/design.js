import { Modal } from 'ant-design-vue'
import { message } from 'ant-design-vue';
import $ from "jquery";

import {
    design_get_page_info,
    design_save_page_info,
    design_auth_udpate_status
} from '../../plugin/api.js';

/**
 * 获取组件配置项方法
 * @param {string} key 组件KEY, 对应数据库字段 component_key
 * @param {string} template 组件模版，默认 template1
 * @returns promise
 */
const load_component_config = (key, template = 'template1') => {
    return new Promise((resolve, reject) => {
        // 读取 config 配置文件
        require([`../../../../files/parts/vue-ui-components/zaful/${key}/m/form/index.js`], (module) => {
            // deep clone object
            const data = JSON.parse(JSON.stringify(module.config));
            resolve(data);
        });
    });
}


/**
 * 创建组件唯一的ID，规则：时间戳
 * @return {String}
 */
const component_create_id = () => {
    const timestamp = new Date().getTime();
    return timestamp;
};
window.component_create_id = component_create_id;

// 装修页模块
const design = {
    namespaced: true,
    state: {
        first_loaded: false, // 首次加载是否成功
        loading: false, // 页面是否加载中
        preview_in_drag: false, // 预览区域是否在拖拽中
        selected_id: 0, // 选中的组件ID，打开form表单
        show_component_form: false, // 是否展示组件配置项,
        avavible_components: [], // 当前页面可用的组件信息
        // 装修权限相关
        auth: {
            in_keep_locking: null // 是否定期发送AJAX续命请求
        }
    },

    mutations: {
        /**
         * 更新页面可用组件和模版
         * @param {object} state 
         * @param {array} list 可用的组件列表
         */
        update_avavible_components (state, list) {
            state.avavible_components = [...list];
        }
    },

    actions: {
        /**
         * 打开form表单
         * @param {number} id 表单ID
         */
        async form_open ({ state, rootState, commit }, id) {
            // 避免多次点击
            if (id === state.selected_id) return false;

            // 获取组件数据对象
            const component = rootState.page.components.filter(x => x.id === id)[0];
            // 拷贝组件基础数据
            const new_component_data = JSON.parse(JSON.stringify(component));

            // 如果组件没有读取配置项，则读取 config.js 文件
            if (component.hasOwnProperty('_LoadedConfig') == false) {
                const config = await load_component_config(component.component_key, component.template_name);

                // 将配置项数据打到 组件数据对象里面
                new_component_data.config = JSON.parse(JSON.stringify(config));

                // 更新远端 [数据] 到配置项里面
                if (new_component_data.hasOwnProperty('data')) {
                    Object.keys(new_component_data.data).map(key => {
                        try {
                            new_component_data.config.datas[key].value = JSON.parse(JSON.stringify(new_component_data.data[key]));
                        } catch (err) {
                            console.warn(`key '${key}' is dosen't exit`);
                        }
                    });
                }

                // 更新远端 [样式数据] 到配置项里面
                if (new_component_data.hasOwnProperty('style')) {
                    Object.keys(new_component_data.style).map(key => {
                        try {
                            // 普通赋值
                            new_component_data.config.styles[key].value = JSON.parse(JSON.stringify(new_component_data.style[key]));
                            // 判断是否有绑定其他字段的关联关系
                            if (new_component_data.config.styles[key].hasOwnProperty('bind') === true) {
                                const bindStr = new_component_data.config.styles[key].bind;
                                const value = new_component_data.config.styles[key].value;
                                eval('new_component_data.config.styles.' + bindStr + '=' + value);
                            }
                        } catch (err) {
                            console.warn(`key '${key}' is dosen't exit`);
                        }
                    });
                }

                // 标记已经读取 config
                new_component_data['_LoadedConfig'] = true;

                // 更新最后修改时间
                new_component_data.lastmodify = new Date().getTime();

                // 存到全局变量里面
                commit('page/component_update', new_component_data, { root: true });
            }

            // 展示 form
            state.selected_id = id;
            state.show_component_form = true;
        },

        /**
         * 关闭表单
         */
        form_close ({ state }) {
            state.selected_id = '';
            state.show_component_form = false;
        },

        /**
         * 装修页增加组件
         * @param {int} data.id 组件ID
         * @param {string} data.component_key 组件KEY
         * @param {string} data.component_title 组件标题
         * @param {string} data.icon 组件图标
         * @param {int} data.lastmodify 最后修改时间
         * @date 2019-11-07
         * @author Cullen
         */
        async component_add ({ commit }, data) {

            // 读取文件关键路经
            const component_key = data.component_key;
            const template_name = data.template_name || 'template1';

            // 配置项
            const config = await load_component_config(component_key, template_name);

            // 拷贝组件基础数据
            const new_component_data = JSON.parse(JSON.stringify(data));

            // 将配置项数据打到 组件数据对象里面
            new_component_data.config = JSON.parse(JSON.stringify(config));;
            
            // 更新最后修改时间
            new_component_data.lastmodify = new Date().getTime();

            // 存到全局变量里面
            commit('page/component_add', new_component_data, { root: true });
        },

        /**
         * 删除组件
         * @description
         *  1. 触发 confrim 弹窗
         *  2. 请求 AJAX
         *  3. 自定义回调
         * @param {Number} id 当前页面组件的唯一ID
         */
        component_delete ({ state, commit }, id) {
            // 弹层
            Modal.confirm({
                title: '确认删除该组件？',
                onOk () {
                    commit('page/component_delete', id, { root: true });
                    // 如果删除的组件，是当前打开表单的组件，则关闭表单区域，并且清空选择值
                    state.selected_id = null;
                    state.show_component_form = false;
                }
            });
        },

        /**
         * 更换组件模版
         * @param {object} state
         * @param {string} template_name 模版文件夹名字
         */
        component_template_change ({ state }, template_name) {
            message.destroy();
            message.success('更换模版成功');
        },

        /**
         * 检查组件能否多次拖入，检查页面是否有存在相同的组件
         * @param {object} component_key 组件编码 
         * @returns {Boolean}
         */
        component_is_multiple ({ rootState }, component_key) {
            if (component_key === 'U000248') {
                // 获取同类型的组件列表
                const same_type_components = [...rootState.page.components].filter(x => x.component_key === component_key);
                if (same_type_components.length >= 1) {
                    message.error('此组件同页面只允许添加1个');
                    return false;
                }
            }
            return true;
        },

        /**
         * 中间预览区域，定位到对应的组件楼层
         * @param {*} floor_index 楼层索引
         */
        component_locate_by_floor ({ rootState }, floor_index) {
            const component_top = $('#canvas').find('.list-group-item').eq(floor_index).offset().top;
            const body_top = $('.design-layout-preview').scrollTop();
            const body_padding = floor_index == 0 ? 114 : 50;
            $('.design-layout-preview').animate({
                scrollTop: parseInt(component_top + body_top - body_padding)
            });
        },

        /**
         * 页面加载
         * @param {object} request  页面参数
         */
        page_load ({ state, dispatch }, request) {
            state.loading = true;

            // 页面参数，渠道，语言
            const { group_id, pipeline, lang, platform } = request;
            // 回调函数
            const { callback } = request;

            // 装修页获取页面数据
            design_get_page_info({
                group_id,
                pipeline,
                lang
            }).then(resource => {
                // 3秒后跳回首页
                if (resource.code == 1) {
                    setTimeout(() => {
                        window.location.href = '/';
                    }, 3000);
                    return false;
                }

                // 拼装页面数据
                const res = resource.data;
                const data = {
                    page_id: res.pageId || '',
                    group_id: res.groupId || '',
                    pipeline: res.pipeline || 'zf',
                    lang: res.lang || 'en',
                    site_code: res.siteCode || '',
                    platform: res.platform || 'm',
                    title: res.pageTitle || '',
                    relations: res.relations.list || [],
                    layouts: [],
                    components: [],
                    pipelines: res.activityInfo.allLangList || [],
                    activity_id: res.activityInfo.id || '',
                    languages: res.languages || [],
                    preview_url: res.preview_url || '',
                    env: 1,
                    goodsSKU: [],
                };
                
                /**
                 * 个别特殊处理(容错处理)
                 * 0. layouts 数据和 components 数据做校验，是否对应
                 * 1. 每个组件增加 lastmodify 字段
                 * 2. ID 强制转为 Number 类型
                 * 3. 将组件包含的数据源抽出来，单独存放（11-10版本删除此条，改为通过 GESHOP-API 获取）
                 */
                try {
                    // 1.1 校验 layouts 数据
                    data.layouts = res.pageData.layouts.map(id => Number(id));
                    data.layouts = data.layouts.filter(id => {
                        const all_components_id = res.pageData.list.map(item => Number(item.id));
                        return all_components_id.includes(id);
                    }) || [];

                    // 1.2 校验组件数据
                    data.components = res.pageData.list.map(item => {
                        // 克隆1个对象出来
                        const item2 = Object.assign({}, item);

                        // 2. ID强制转为 Number
                        item2.id = Number(item.id);
                        item2['lastmodify'] = Number(item.id);
                        item2['template_name'] = 'template1';

                        // 3. 商品数据抽取到 page.state.goodsSKU 里面。
                        if (Array.isArray(item2.goodsSKU)) {
                            item2.goodsSKU.map(x => {
                                // 重新绑定组件ID，解决同步渠道带来的组件ID不对应的问题
                                x.component_id = item2.id;
                                // 追加到 store
                                data.goodsSKU.push(Object.assign({}, x));
                            });
                            // goods 字段默认取 [0] 个数据源ID
                            try {
                                item2.data.goods = item2.goodsSKU[0].id;
                            } catch (err) {
                                console.warn(err);
                            }
                        };
                        return item2;
                    });
                } catch (err) {
                    data.layouts = [];
                    data.components = [];
                    data.goodsSKU = [];
                    console.error('layouts & components error');
                }
                // console.log('路由的数据', res.pageData);
                // console.log('遍历保存的数据====', data);

                // 存储页面数据
                dispatch('page/load', data, { root: true });  

                // 11-10 通过 API 获取当前页面组件的商品数据 - Cullen
                dispatch('page/load_remote_goods_data', {
                    is_first: 1
                }, { root: true }); 

                // 更新状态
                state.loading = false;
                state.first_loaded = true;
            }, (err) => {
                err.message && message.error(err.message);
                setTimeout(() => {
                    window.location.href = '/';
                }, 3000);
            });
        },

        /**
         * 页面保存函数
         * @description 1. 保存到 store, 2. 数据通过接口保存到数据库
         * @author Cullen
         * @date 2019-11-26
         */
        page_save ({ state, rootState, commit }) {
            // 开启 loading 状态
            state.loading = true;

            // 清除垃圾组件
            rootState.page.components.map(item => {
                if (rootState.page.layouts.includes(item.id) == false) {
                    commit('page/component_delete', item.id, { root: true });
                }
            });

            // 构造请求参数
            const request = {
                page_id: rootState.page.info.page_id,
                group_id: rootState.page.info.group_id,
                pipeline: rootState.page.info.pipeline,
                lang: rootState.page.info.lang,
                layouts: [...rootState.page.layouts],
                components: [...rootState.page.components] || [],
            }
            /**
             * 格式化组件数据，删除无用的数据，减少AJAX传送体积
             * 1. 去除config里面的无效数据
             * 2. 增加3个数据纬度，数据配置+样式配置+商品SKU数据
             */
            request.components = request.components.map(cmpt_clone => {
                const cmpt = Object.assign({}, cmpt_clone);
                delete cmpt.icon;
                delete cmpt.lastmodify;

                // 如果当前组件有开启表单，则组装数据
                if (cmpt.hasOwnProperty('config')) {
                    cmpt.data = {};
                    cmpt.style = {};
    
                    // 数据配置
                    Object.keys(cmpt.config.datas).map(key => {
                        const value = cmpt.config.datas[key].value;
                        cmpt.data[key] = value;
                    });
    
                    // 样式配置
                    Object.keys(cmpt.config.styles).map(key => {
                        const value = cmpt.config.styles[key].value;
                        cmpt.style[key] = value;
                    });
                    delete cmpt.config;
                }
                cmpt.goodsSKU = [];

                // 找到当前的组件的商品SKU数据
                const current_component_goodsSKU = rootState.page.goodsSKU.filter(x => Number(x.component_id) === Number(cmpt.id));
                
                // 过滤里面的不需要的字段
                cmpt.goodsSKU = current_component_goodsSKU.map(item => {
                    const clone = JSON.parse(JSON.stringify(item));
                    clone.goodsInfo = [];
                    clone.pagination = {};
                    clone.relation_component_id = [];
                    clone.filters = {};
                    return clone;
                });

                return cmpt; 
            });

            // 发送保存请求
            return new Promise((resolve, reject) => {
                design_save_page_info(request).then(res => {
                    state.loading = false;
                    resolve(res);
                }, (err) => {
                    // 保存失败
                    state.loading = false;
                    reject(err);
                });
            });
        },

        /**
         * 页面预览
         */
        page_preview ({ rootState }) {
            window.open(rootState.page.preview_url);
        },

        /**
         * 清除页面组件
         */
        page_reset ({ rootState }) {
            // 弹层
            Modal.confirm({
                title: '确认删除所有组件？',
                onOk () {
                    rootState.page.components = [];
                    rootState.page.layouts = [];
                    rootState.page.goodsSKU = [];
                }
            });
        },

        /**
         * 页面轮训保持锁定状态，定期每 30 秒定时发送请求到后台（续命）
         * @author Cullen
         * @date 2019-11-26
         */
        page_keep_locking ({ state, rootState }) {
            // 轮训秒数， 30秒
            const second = 1000 * 10 * 1;
            state.in_keep_locking = setInterval(() => {
                // 如果还在可编辑状态下，发送请求
                design_auth_udpate_status({
                    page_id: rootState.page.info.page_id
                });
            }, second);
        },

        /**
         * 装修页更新页面布局排序
         * @param {Object} state
         * @param {Array} layouts 布局排序数组
         * [1,2,3,4,5,{ id: 111, groups: [222,333] }]
         */
        page_update_layout ({ commit, dispatch }, layouts) {
            // 更新 page modules
            commit('page/update_layout', layouts, { root: true });
            // 更新所有导航组件的 list 数据
            dispatch('component_update_navigator');
        },

        /**
         * 复制组件
         * @param {Number} id 复制的组件ID
         */
        component_copy ({ rootState }, { id, index }) {
            let copy_component = null;
            // 复制组件数据
            rootState.page.components.map(x => {
                if (Number(x.id) === id) {
                    copy_component = JSON.parse(JSON.stringify(x));
                    // 创建新的组件ID
                    copy_component.id = window.component_create_id();
                };
            });
            
            if (copy_component != null) {
                // 复制商品数据
                let goods_id = null;
                if (copy_component.hasOwnProperty('data') && copy_component.data.hasOwnProperty('goods')) {
                    goods_id = copy_component.data.goods;
                }
                if (copy_component.hasOwnProperty('config') && copy_component.config.datas.hasOwnProperty('goods')) {
                    goods_id = copy_component.config.datas.goods.value;
                }
                if (goods_id != null && goods_id != '') {
                    const target = rootState.page.goodsSKU.filter(x => Number(x.id) === Number(goods_id))[0];
                    const goods_copy = JSON.parse(JSON.stringify(target));
                    goods_copy.id = new Date().getTime();
                    goods_copy.component_id = copy_component.id;
                    try {
                        copy_component.data.goods = goods_copy.id;
                    } catch (err) {}
                    try {
                        copy_component.config.datas.goods.value = goods_copy.id;
                    } catch (err) {}
                    rootState.page.goodsSKU.push(goods_copy);
                }
                // 更新页面组件数据
                rootState.page.components.push(copy_component);

                // 更新页面布局信息
                rootState.page.layouts.splice(index + 1, 0, copy_component.id);

                // 提示
                message.success('组件复制成功');
            } else {
                message.error('组件复制失败');
            }
        },

        /**
         * 更新水平导航组件的list字段的数据
         * @param {*} param0 
         */
        component_update_navigator ({ state, rootState }) {
            // 找到所有的带导航功能的组件
            const title_components = rootState.page.components.filter(x => x.component_key === 'U000242' || x.need_navigate === 1);
            const title_components_sort = [];
            // 文本组件排序
            rootState.page.layouts.map(id => {
                const component = title_components.filter(x => Number(x.id) === Number(id))[0];
                if (component) {
                    title_components_sort.push(component);
                }
            });

            // 找到所有的水平导航组件
            const target_component = rootState.page.components.filter(x => x.component_key === 'U000244');
            target_component.map(component => {
                // 获取data字段(区分form的data和数据库的data)
                const list = component.config ? component.config.datas.list.value : component.data ? component.data.list : [];
                if (Array.isArray(list)) {
                    // 获取已经配置的文本组件的标题
                    const ids = list.map(x => Number(x.component_id));
                    // 存储新的数据
                    const new_list = [];
                    title_components_sort.map(title_component => {
                        const id = Number(title_component.id);
                        let title = '';
                        if (title_component.config) {
                            if (title_component.config.datas.title) {
                                title = title_component.config.datas.title.value;
                            }
                            if (title_component.config.datas.navigator_title) {
                                title = title_component.config.datas.navigator_title.value;
                            }
                        } else {
                            if (title_component.data.title) {
                                title = title_component.data.title;
                            }
                            if (title_component.data.navigator_title) {
                                title = title_component.data.navigator_title;
                            }
                        }
                        // 如果是选中的，追加到数据里面
                        if (ids.includes(id)) {
                            new_list.push({
                                component_id: id,
                                component_title: title
                            });
                        }
                    });
                    if (component.config) {
                        component.config.datas.list.value = new_list;
                    }
                    if (component.data) {
                        component.data['list'] = new_list;
                    }
                }
            });
        }
    }
};

export default design;
