// 模块加载函数，过滤了异常情况
const loader = require('./library/loader')

// 一级路由
const routers = {
    home: loader('/library/home/index', __dirname),
    component: loader('/library/component/index', __dirname),
    test: loader('/library/test/index', __dirname),
}

// 执行一级路由
module.exports = async (basePath, ctx, next) => {
    const route = ctx.params.route || 'home'
    const action = ctx.params.action || 'index' 
    const params = { basePath, action, ctx, next }
    // 根据一级路由执行
    routers[route] && await routers[route](params)
}
