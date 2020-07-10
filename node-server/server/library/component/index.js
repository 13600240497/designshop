// 注册二级路由
const routes = {
    render: require('./render')
}

module.exports = async function ({ basePath, action, ctx, next }) {
    routes[action] && await routes[action]({ basePath, action, ctx, next })
}
