/**
 * 组件过滤显示
 * @author wu xingtgao
 * @date 2020-03-10
 * @Description:
 */

class ComponentFilter {
    constructor () {
        // this.$searchBtn = $('.component-plus-search');
        this.$input = $('input[name=componentName],input[name=templateName]'); // 组件搜索值
        this.className = '.sidebar-component-container .design-model-item'; // 绑定的组件class
        this.templateClass = '.sidebar-component-container .template-list-item'; // 模板列表calss
        this.hideClass = 'layui-hide'; // 隐藏class
    }

    /**
     *
     * @param val 匹配值
     * @param name 被匹配
     * @returns {boolean}
     */
    // regName = (val, name) => {
    //     return new RegExp(val, 'ig').test(name);
    // };
    regName (val, name) {
        return new RegExp(val, 'ig').test(name);
    }

    find (target) {
        const val = target.value;
        const classCurrent = target.name === 'componentName' ? this.className : target.name === 'templateName' ? this.templateClass : '';
        const hideClass = this.hideClass;
        if (!val) {
            $(classCurrent).removeClass(hideClass);
            return false;
        }
        $(classCurrent).each((index, item) => {
            let { name } = $(item).data();
            if (!this.regName(val, name)) {
                $(item).addClass(hideClass);
            } else {
                $(item).removeClass(hideClass);
            }
        });
    }

    /**
     * 绑定input监听事件
     */
    bindEvent () {
        this.$input.on('input', (event) => {
            this.find(event.target);
        });
    }
}

export default ComponentFilter;
