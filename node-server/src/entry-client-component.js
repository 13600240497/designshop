module.exports = {
    /**
     * 输出浏览器渲染接手的JS
     * @param {obejct} context 上下文
     */
    create(context) {
        // copy object data
        let data = JSON.parse(JSON.stringify(context))
            data.styles = '';
            data._styles = '';
        // remove default data, unuseed
            data.data.default = {}
        let dataStr = JSON.stringify(data.data)
            data
        return `
        $(function() {
            new Vue({
                data: ${JSON.stringify(data)},
                render: function(h) {
                    return h('geshop-component', {
                        props: {
                            pid: '${context.pageInstanceId}',
                            uikey: '${context.key}',
                            theme: '${context.theme}',
                            data: ${dataStr}
                        }
                    })
                }
            }).$mount('#${context.key}_${context.pageInstanceId}_prerender')
        });`
    }
}
