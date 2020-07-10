const auth = {
    namespaced: true,
    
    state: {
        type: 'home', // home/activity 页面类型
        platforms: [], // 当前所有端 Array
    },

    mutations: {
        /**
         * 更新站点端
         * @param {*} state 
         * @param {String} json
         */
        update_platforms (state, json) {
            const data = JSON.parse(json);
            Object.keys(data.sites).map(code => {
                state.platforms.push({
                    code: code,
                    name: data.sites[code].platform_name
                });
            });
        }
    }
};

export default auth;
