/**
 * Growing IO 埋点
 * 埋点触发事件：
 *  1. 模块点击 js_growingio_click
 *  2. 模块曝光 js_growingio_xxxxx
 *  3. ...
 */
const config = {
    projectId: '88bb4e0c99399b41'
};

/**
 * 根据组件ID获取埋点数据
 * @param {string} component_id 组件唯一的ID
 */
const get_growing_value = (component_id) => {
    if (window.GESHOP_GROWINGIO && window.GESHOP_GROWINGIO[component_id]) {
        const params = JSON.stringify(window.GESHOP_GROWINGIO[component_id]);
        return JSON.parse(params);
    } else {
        return {};
    }
};

/**
 * Main fucntions
 */
const growingio = {
    namespaced: true,
    actions: {
        /**
         * 初始化流程
         */
        init ({ dispatch }) {
            (function (e, t, n, g, i) {
                e[i] = e[i] || function () {
                    (e[i].q = e[i].q || []).push(arguments);
                };
                n = t.createElement('script');
                let tag = t.getElementsByTagName('script')[0];
                n.async = 1;
                n.src = (document.location.protocol == 'https:' ? 'https://' : 'http://') + g;
                tag.parentNode.insertBefore(n, tag);
            })(window, document, 'script', 'assets.giocdn.com/2.1/gio.js', 'gio');

            // 初始化gio
            window.gio('init', config.projectId, {});
            // 语言和站点
            window.GESHOP_PIPELINE && window.gio('visitor.set', 'national_code', window.GESHOP_PIPELINE || '');
            window.gio('send');

            // 绑定模块点击事件
            dispatch('bind_click_events');
            // 绑定模块曝光事件
            dispatch('bind_browser_event');
        },

        /**
         * 绑定点击事件
         */
        bind_click_events ({ dispatch }) {
            // 测试发送数据
            $(document).on('click', '.js-growingio', function () {
                // 获取参数
                const id = $(this).attr('data-growingio-id');
                // 发送埋点
                dispatch('send_click', id);
            });
        },

        /**
         * 绑定模块曝光
         * @param {jQuery DOM} targets 需要绑定的DOMS节点
         */
        bind_browser_event ({ dispatch }, targets) {
            /**
             * IntersectionObserver 构造函数
             */
            const observer = new window.IntersectionObserver((changes) => {
                const match = changes.filter(x => x.isIntersecting === true);
                match.map(x => {
                    // 获取对应的埋点参数
                    const id = $(x.target).attr('data-growingio-id');
                    // 发送埋点
                    dispatch('send_browser', id);
                    // 移除监控
                    observer.unobserve(x.target);
                });
            });

            /**
             * 遍历DOM，绑定事件
             */
            if (targets) {
                observer.observe(targets);
            } else {
                $('.js-growingio').each((x, item) => {
                    observer.observe(item);
                });
            }
        },

        /**
         * 发送点击埋点
         * @param {string} id 组件ID
         * @param {object} params 埋点参数
         */
        send_click ({ state }, id) {
            const params = get_growing_value(id);
            window.gio('track', 'activityClickH5', params);
            window.gio('evar.set', params);
        },

        /**
         * 发送曝光埋点
         * @param {string} id 组件ID
         * @param {object} params 埋点对象
         */
        send_browser ({ state }, id) {
            const params = get_growing_value(id);
            window.gio('track', 'activityImpH5', params);
        }
    }
};

export default growingio;
