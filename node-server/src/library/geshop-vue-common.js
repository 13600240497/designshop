/**
 * @Description: 定义公共方法文件
 * @author lifeng
 * @date 2019/6/18
 */
class GEShopCommonFn {
    /**
     * H5 页面与APP 建立连接通讯
     * @param {string} methodName
     * @param {function} methodCallback
     */
    invokingAppMethod (methodName, methodCallback) {
        if (/(iPhone|iPad|iPod|iOS)/i.test(navigator.userAgent)) {
            this.setupWebViewJavascriptBridge((bridge) => {
                try {
                    bridge.callHandler(methodName, {}, function (response) {
                        methodCallback(response);
                    });
                } catch (e) {
                }
            });
        } else if (/(Android)/i.test(navigator.userAgent)) {
            try {
                let response = window.AndroidNativeMethod[methodName]();
                methodCallback(response);
            } catch (e) {
            }
        }
    }

    /**
     * 设备验证
     * @param callback
     * @returns {*}
     */
    setupWebViewJavascriptBridge (callback) {
        if (window.WebViewJavascriptBridge) {
            return callback(window.WebViewJavascriptBridge);
        }
        if (window.WVJBCallbacks) {
            return window.WVJBCallbacks.push(callback);
        }
        window.WVJBCallbacks = [callback];
        let WVJBIframe = document.createElement('iframe');
        WVJBIframe.style.display = 'none';
        WVJBIframe.src = 'https://__bridge_loaded__';
        document.documentElement.appendChild(WVJBIframe);
        setTimeout(() => {
            document.documentElement.removeChild(WVJBIframe);
        }, 0);
    }

    // 写入cookie 并跳转
    cookieAction () {
        document.cookie = 'staging=true;path=/;domain=zaful.com';
        if (!sessionStorage['is_refresh']) {
            // 标记页面是否是刷新页面
            sessionStorage['is_refresh'] = 1;
            window.location.replace(`https://uidesign.zafcdn.com/ZF/email/20180522_3450/localtion.html?return=${window.location.href}`);
        }
    }

    initAppCookieAction () {
        this.invokingAppMethod('fetchReleaseStatus', (response) => {
            if (response) {
                // 只有预发布才会返回true
                this.cookieAction();
            }
        });
    }

    // 图片懒加载 依赖于JQ 和 JQ 插件， 该方法用于display 为none 时 不进行懒加载
    lazyload (element) {
        if ($.fn.lazyload) {
            $(element).lazyload({
                threshold: 100,
                effect: 'fadeIn',
                failure_limit: 20,
                skip_invisible: true // true表示display 为none 时 不进行懒加载， false 反之
            });
        }
    }

    /**
     * JSONP 回调函数
     * @param url
     * @param params
     * @param pid
     * @param target  组件元素
     * @param jsonpCallback 回调参数
     * @param {Boolean} cache 是否缓存
     * @returns {jQuery}
     */
    $jsonp (url, params, { pid = '', target = '', jsonpCallback = '', cache = true } = {}) {
        const data = Object.assign(params, {
            lang: GESHOP_LANG,
            pipeline: (typeof GESHOP_PIPELINE !== 'undefined' ? GESHOP_PIPELINE : '')
        });
        // jsonp 回调名字处理
        if (jsonpCallback === '') {
            // 获取组件ID作为后缀，如果找不到则用时间戳代替
            const ext = pid || (target && $(target).attr('data-id')) || new Date().getTime();
            jsonpCallback = `geshop_callback_${ext}`;
        }
        // 传参封装
        const requestData = {
            content: JSON.stringify(data)
        };
        let result = $.ajax({
            url: url,
            type: 'GET',
            cache: true,
            dataType: 'jsonp',
            data: requestData,
            jsonpCallback: jsonpCallback,
            error: function (res) {
            }
        });
        // 重置done回调
        if ('done' in result) {
            let done = result.done;
            result.done = function (fn) {
                function _done (res) {
                    if (res.code != 0) {
                        return;
                    }
                    fn(res);
                }

                done.call(result, _done);
                return this;
            };
        }
        return result;
    }
}

window.GEShopCommonFn_Vue = new GEShopCommonFn();
