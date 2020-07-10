/**
 * Jetlore 配置项
 * @param {string} cid 项目ID
 */
const config = {
    dl: {
        cid: '3d1feedf377bf27d14946e684f8513f0'
    },
    rg: {},
    zf: {}
};

/**
 * 获取用户信息
 */
function getUserInfo () {
    let e = {};
    let t = $.cookie('user_info');
    if (t) {
        try {
            e = JSON.parse(t);
        } catch (i) {
            console.error(i);
        }
    };
    return e;
};

/**
 * Main Fcuntions
 */
const jetlore = {
    namespaced: true,

    state: {
        isReady: !1, // 是否已加载脚本
        $LABInstance: null // $LAB 实例
    },

    actions: {
        // 异步加载脚本
        loadScript ({ state }) {
            state.$LABInstance = $LAB.setOptions({
                AllowDuplicates: !1
            }).script('//assets.jetlore.com/js/jlranker.js');
        },
        // function ready
        ready ({ state, dispatch }, e) {
            return state.isReady ? void e() : void state.$LABInstance.wait(() => {
                dispatch('_initJetloreRanking', e);
            });
        },
        // 初始化 jetlore ranking
        _initJetloreRanking ({ state }, e) {
            let i = function (i, s) {
                JL_RANKER.init({
                    cid: config.dl.cid, // 上面的配置数据
                    id: i || 'undefined',
                    div: s ? s.toLocaleLowerCase() : '',
                    lang: window.GESHOP_LANG ? 'fr' : 'en'
                });
                state.isReady = true;
                e();
            };
            if (window.info_check) {
                window.info_check.deferred.done(function (e) {
                    let s = null;
                    let n = null;
                    e.firstname && (s = getUserInfo().u);
                    e.CountryCode && (n = e.CountryCode);
                    i(s, n);
                }).fail(() => {
                    i();
                });
            } else {
                i();
            }
        }
    }
};

export default jetlore;
