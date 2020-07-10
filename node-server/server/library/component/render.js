// 公共包
const path = require('path')
const fs = require('fs')
const UglifyJS = require('uglify-js')
// sass 预编译
const sass = require('node-sass')
// 日至模块
const { logger } = require('../../library/logger');


/**
 * 替换 Sass 文件中的 [php] 变量
 * @param {object} data php传过来的对象值，建值对
 * @param {*} content sass 文件内容
 */
const replace_sass_vriables = (data, content) => {
    if (content.length <= 0) return ''
    let _content = content
    const reg = /\${1}\{{1}[^\}]*\}{1}/g
    const var_list = content.match(reg)
    var_list.map(item => {
        const s = item.replace('${', '').replace('}', '')
        try {
            const value = eval(s);
            _content = _content.replace(item, value)
        } catch (err) {
            logger.error(`SASS渲染错误，${err}, key=${this.context.key}, theme=${this.context.theme}`)
        }
    })
    return _content
}


class Component {
    constructor (basePath) {
        this.basePath = basePath
        this.path = {
            clientManifest: process.env.NODE_ENV == 'development' ? '/develop' : '/resource',
        }
        this.context = {}
        this.updateContext = this.updateContext.bind(this)
        this.render = this.render.bind(this)
        this.renderSass = this.renderSass.bind(this)
        this.renderComponent = this.renderComponent.bind(this)
        this.renderJavascript = this.renderJavascript.bind(this)
        this.get_languages_by_code = this.get_languages_by_code.bind(this)
    }


    /**
     * 更新 contex 公共变量
     * @param {object} ctx 
     */
    updateContext (ctx) {
        this.context = {
            pageId: ctx.pageId || '',
            pageInstanceId: ctx.pageInstanceId || '1',
            key: ctx.key || '',
            theme: ctx.theme || '',
            lang: ctx.lang || 'en',
            platform: ctx.platform || 'pc',
            sitecode: ctx.sitecode || 'zf-pc',
            data: ctx.data || {},
            languages: this.get_languages_by_code(ctx.languages || {}, ctx.lang),
        };
        return this
    }


    /**
     * 暴露出去的渲染函数
     */
    async render () {
        try {
            const html = await this.renderComponent()
            const js = await this.renderJavascript()
            const css = await this.renderSass({})
            return {
                code: 0,
                data: html + js + css
            }
        } catch (err) {
            return {
                code: 1,
                msg: err
            }
        }
    }

    /**
     * 渲染VUE组件，输出HTMl
     */
    async renderComponent () {
        const serverBundle = require(this.basePath + '/node-server/dist/vue-ssr-server-bundle.json');
        const clientManifest = require(this.basePath + '/htdocs' + this.path.clientManifest + '/vue-ssr-client-manifest.json');
        const vueRenderer = require('vue-server-renderer').createBundleRenderer(serverBundle, {
            runInNewContext: false,
            clientManifest: clientManifest,
            inject: false,
        });

        let context = this.context

        // 开始渲染
        return new Promise(async (resolve, reject) => {
            vueRenderer.renderToString(context, async (err, html) => {
                let renderHtml, renderCss, renderJS, renderState
                if (err) {
                    logger.error(`组件渲染错误，${err}, key=${this.context.key}, theme=${this.context.theme}`)
                    reject({
                        code: 1,
                        msg: err['code']
                    })
                } else {
                    renderHtml = `<div id="${this.context.key}_${this.context.pageInstanceId}" class="geshop-component-box component-drag" data-key="${this.context.key}" data-id="${this.context.pageInstanceId}"><div id="${this.context.key}_${this.context.pageInstanceId}_prerender">${html}</div></div>`
                    renderCss = context.renderStyles().replace(/\n/g, '\n ')
                    renderJS = context.renderScripts()
                    renderState = context.state
                    resolve(renderCss + renderJS + renderHtml)
                }
            });
        })
    }

    /**
     * 渲染 css 样式
    */
    async renderSass () {
        let content
        // 替换 data 值
        try {
            content = fs.readFileSync(`${this.basePath}/files/parts/ui/${this.context.key}/${this.context.theme}/index.scss`, 'utf-8');
            content = replace_sass_vriables(this.context.data, content)
        } catch {
            return content = ''
        }
        // 最外层包裹1层组件的ID
        content = `#${this.context.key}_${this.context.pageInstanceId} { ${content} }`;
        // 开始渲染 sass
        try {
            const result = sass.renderSync({
                data: content,
                outputStyle: 'compact',
                sourceMap: false,
            });
            return `<style>${result.css.toString()}</style>`.replace(/\n/g, '')
        } catch (err) {
            logger.error(`SASS渲染错误，${err}, key=${this.context.key}, theme=${this.context.theme}`)
            return `<style>/* css 编译错误 */</style>`
        }

    }

    
    /**
     * 渲染 Javascript 内容
     */
    async renderJavascript () {
        const compoenntBundle = require(`${this.basePath}/node-server/src/entry-client-component`)
        const code = compoenntBundle.create(this.context)
        const result = UglifyJS.minify(code)
        const template = `<script>${result.code}</script>`
        return template
    }


    /**
     * 根据 langCode 获取对应的语言包 
     */
    get_languages_by_code (langPackage, code) {
        var newLangPackage = {}
        Object.keys(langPackage).map(key => {
            if (langPackage[key] && langPackage[key][code]) {
                newLangPackage[key] = langPackage[key][code]
    
            } else {
                newLangPackage[key] = ''
            }
        })
        return newLangPackage
    }
}


module.exports = async function ({ basePath, ctx }) {
    const post = ctx.request.body;

    // 校验组件名字
    if (/^U{1}\d{6}$/.test(post.key) == false) {
        logger.error(`组件ID错误，key=${post.key}`)
        ctx.body = { code: 1, msg: "组件ID错误，字段 [key]，参考 U000001" }
        return;
    }

    // 校验模版名字
    if (post.theme == '') {
        ctx.body = { code: 1, msg: "组件ID错误，字段 [key]，参考 U000001" }
        return;
    }

    // 开始校验
    let component = new Component(basePath).updateContext(post)
    ctx.body = await component.render()
}

