/**
 *@author: 任贸华
 *@date: 2018/11/21 08:54:18
 *@desc:
 * @param {string} action 触发行为
 * @param {string} type 事件类型(名称)
 *  * @param {function} name 描述名称
 * @param {function} handler 绑定事件
 * @param {string} rest 实参
 */
function listenEvent(action = 'edit') {
    this.events = {};
    this.action = action;
}

listenEvent.prototype = {
    constructor: listenEvent,
    on: function (type, name, handler) {
        if (typeof handler != 'function') {
            throw new Error('请传入回调函数!');
        }
        if (typeof type != 'string') {
            throw new Error('请传入type类型!');
        }
        if (typeof name != 'string') {
            throw new Error('请传入name名称!');
        }
        var events = this.events[type];
        switch (this.action) {
            case 'add':
                if (!events || events.length === 0) {
                    this.events[type] = [];
                }
                this.events[type].push({[name]: handler});
                break;
            case 'edit':
                if (!events || events.length === 0) {
                    this.events[type] = [];
                    this.events[type].push({[name]: handler});
                } else {
                    for (var i = 0; i < events.length; i++) {
                        if (events[i][name]) {
                            events[i][name] = handler
                        }
                    }
                }
            case 'ignore':
                if (!events || events.length === 0) {
                    this.events[type] = [];
                    this.events[type].push({[name]: handler});
                } else {
                    var isName = false
                    for (var i = 0; i < events.length; i++) {
                        if (events[i][name]) {
                            isName = true;
                            break;
                        }
                    }
                    if (!isName) {
                        this.events[type].push({[name]: handler});
                    }
                }

        }
    },
    emit: function (type, name, ...rest) {
        if (typeof type != 'string') {
            throw new Error('请传入type类型!');
        }
        var events = this.events[type];
        if (!events || events.length === 0) {
            return false;
        }
        for (var i = 0; i < events.length; i++) {
            if (name) {
                events[i][name].apply(this, rest);
            } else {
                for (var j in events[i]) {
                    events[i][j].apply(this, rest);
                }

            }

        }
    },
    off: function (type, name) {
        if (typeof type != 'string') {
            throw new Error('请传入type类型!');
        }
        var events = this.events[type];
        if (!events || events.length === 0) {
            return false;
        }
        if (!name) {
            delete this.events[type]
        } else {
            for (var i = events.length - 1; i >= 0; i--) {
                if (events[i][name]) {
                    events.splice(i, 1);
                }
            }
        }

    }
};
var listenEvents = new listenEvent('edit')

setTimeout(function () {
    listenEvents.emit('ready')
    console.log(listenEvents)
}, 0)

/**
 *@author: 任贸华
 *@date: 2018/11/20 15:38:07
 *@desc:
 * @param {string} type 类型
 * @param {string} msg 打印文本
 */

function consoleMsg({type = 'log', msg = ''}) {
    console && console[type](msg)
}

/**
 *@author: 任贸华
 *@date: 2018/11/20 15:38:07
 *@desc:
 * @param {string} type 类型
 * @param {object} delegate Element对象
 * @param {string} selector 选择器对象
 * @param {function} callback 回调函数
 * @param {function} action 触发类型
 */
function isRepBind({type = 'click', delegate, selector, callback, action = 'ignore'}) {
    if (typeof callback != 'function') {
        throw new Error('请传入回调函数!');
    }
    if (!/^HTML/ig.test(delegate.constructor.name)) {
        throw new Error('请传入非jquery的Element对象!');
    }
    const events = $._data(delegate, 'events');
    const originalType = type.split('.')[0];
    if (!events || !events[originalType]) {
        callback()
    } else if (events[originalType]) {
        let num = false;
        for (let i = events[originalType].length - 1; i >= 0; i--) {
            const eventobj = events[originalType][i];
            const typeNamespace = eventobj.type + eventobj.namespace;
            if (eventobj.selector == selector && typeNamespace == type) {
                num = true;
                const str = '重复事件'
                const selectortStr = selector ? `${selector}-` : '';
                const delegateStr = delegate.getAttribute('id') ? `#${delegate.getAttribute('id')}` : `.${delegate.getAttribute('class')}`
                const msg = action + '-' + delegateStr + '-' + type + '-' + selectortStr + str
                switch (action) {
                    case 'ignore':
                        consoleMsg({type: 'warn', msg})
                        break;
                    case 'add':
                        consoleMsg({type: 'warn', msg})
                        callback();
                        break;
                    case 'edit':
                        consoleMsg({type: 'warn', msg})
                        events[originalType].splice(i, 1);
                        callback();
                        break;
                }
                break;
            }
        }
        if (!num) {
            callback()
        }
        consoleMsg({type: 'log', msg: events[originalType]})
    }
}