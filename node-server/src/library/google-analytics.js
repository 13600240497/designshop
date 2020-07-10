/**
 * Google Analaytics
 * @description 目前用于ZF站点
 * @reqiured [jQuery]
 * @Docs  http://wiki.hqygou.com:8090/pages/viewpage.action?pageId=209651727
 * @author Cullen
 * @date 2020-03-05
 */
class GA {
    constructor () {
        // GA 核心库
        this.core = null;

        // GA 自定义事件名
        this.trigger_event = {
            click: 'geshop-component-click', // 点击
            view: 'geshop-component-view' // 曝光
        };
        
        // 设置触发事件的class钩子
        this.trigger_class = {
            click: 'js-geshop-ga-click',
            view: 'js-geshop-ga-view'
        };

        // 设置上报参数的钩子
        this.trigger_data_key = {
            name: 'geshop-component-name'
        };
    }

    /**
     * 事件初始化
     */
    init () {
        // 检查依赖包
        if (!window.jQuery) {
            throw new Error('jquery is required');
        }
        if (!window.dataLayer) {
            console.warn('GA is required');
            this.core = [];
        } else {
            this.core = window.dataLayer;
        }

        // 绑定事件
        this._bind_event_view();
        this._bind_event_click();
    }

    /**
     * 绑定点击事件
     */
    _bind_event_click (target) {
        const self = this;
        const element = target ? $(target) : $(`.${this.trigger_class.click}`);
        element.off('click').on('click', function () {
            if ($(this).hasClass(self.trigger_class.click) == true) {
                const value = $(this).attr(self.trigger_data_key.name) || '';
                self.core.push({ 'event': self.trigger_event.click, 'name': value });
            }
        });
    }

    /**
     * 绑定曝光事件
     */
    _bind_event_view (target) {
        /**
         * IntersectionObserver 构造函数
         */
        const observer = new window.IntersectionObserver((changes) => {
            const match = changes.filter(x => x.isIntersecting === true);
            match.map(x => {
                // 获取对应的埋点参数
                const name = x.target.getAttribute(this.trigger_data_key.name) || '';
                // 发送埋点
                // dispatch('send_browser', id);
                this.core.push({ 'event': this.trigger_event.view, 'name': name });
                // 移除监控
                observer.unobserve(x.target);
            });
        });

        /**
         * 遍历DOM，绑定事件
         */
        if (target) {
            observer.observe(target);
        } else {
            const match_node_list = document.querySelectorAll(`.${this.trigger_class.view}`);
            for (let i = 0; i < match_node_list.length; i++) {
                const target = match_node_list[i];
                observer.observe(target);
            }
        }
    }

    /**
     * 根据DOM容器，找到需要绑定的曝光元素
     * @param {Node} element
     */
    bind_event_by_dom (element) {
        $(element).find(`.${this.trigger_class.view}`).each((i, item) => {
            this._bind_event_view(item);
        });
        $(element).find(`.${this.trigger_class.click}`).each((i, item) => {
            this._bind_event_click(item);
        });
    }
}

export default GA;
