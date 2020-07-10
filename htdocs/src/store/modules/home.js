const home = {
    namespaced: true,
    
    state: {
        // 当前选中的端
        current_platform: '', 
        // 当前选中的站点
        current_site: '',
    },

    mutations: {
        /**
         * 更新站点端
         * @param {*} state 
         * @param {String} json
         */
        update_platform (state, value) {
            state.current_platform = value;
        },

        /**
         * 更新站点
         * @param {*} state 
         * @param {*} value 
         */
        update_site (state, value) {
            state.current_site = value;
        }
    }
};

export default home;
