const auth = {
    namespaced: true,
    
    state: {
        username: '', // 当前账户名
        realName: '', // 真是姓名
        isSuper: 0, // 是否超级管理员
    },

    mutations: {
        update_logInfo (state, data) {
            state.username = data.username;
            state.realName = data.realName;
            state.isSuper = parseInt(data.isSuper);
        }
    }
};

export default auth;
