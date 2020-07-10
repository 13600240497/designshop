(function () {
    window.share = {
        fackbook: {
            init: function (callback) {
            },
            start: function (cfg, callback) {
                // let _this = this;
                cfg = cfg || {};

                let iWidth = 650;
                let iHeight = 430;
                let iTop = (window.screen.availHeight - 30 - iHeight) / 2;
                let iLeft = (window.screen.availWidth - 10 - iWidth) / 2;
                let url = 'https://www.facebook.com/dialog/share?app_id=100924140245002&display=popup&href=' + encodeURIComponent(cfg.link);
                window.open(url, '', 'width=' + iWidth + ',height=' + iHeight + ',top=' + iTop + ',left=' + iLeft);
            }
        },
        twitter: {
            init: function (callback) {
                // let _this = this;
                $('body').append('<div id="twitter-root"></div>');
                window.twttr = (function (d, s, id) {
                    let t;
                    let js;
                    let fjs = d.getElementsByTagName(s)[0];
                    if (d.getElementById(id)) return;
                    js = d.createElement(s);
                    js.id = id;
                    js.src = 'https://platform.twitter.com/widgets.js';
                    fjs.parentNode.insertBefore(js, fjs);
                    return window.twttr || (t = {
                        _e: [],
                        ready: function (f) {
                            t._e.push(f);
                        }
                    });
                }(document, 'script', 'twitter-wjs'));

                if (typeof callback == 'function') {
                    callback();
                }
            },
            start: function (cfg, callback) {
                // let _this = this;
                cfg = cfg || {};

                let iWidth = 650;
                let iHeight = 430;
                let iTop = (window.screen.availHeight - 30 - iHeight) / 2;
                let iLeft = (window.screen.availWidth - 10 - iWidth) / 2;
                let url = 'https://twitter.com/intent/tweet?text=' + encodeURIComponent(cfg.title) + '&url=' + encodeURIComponent(cfg.link);
                window.open(url, '', 'width=' + iWidth + ',height=' + iHeight + ',top=' + iTop + ',left=' + iLeft);

                if (typeof callback == 'function') {
                    callback();
                }
            }
        },
        pinterest: {
            start: function (cfg, callback) {
                // let _this = this;
                cfg = cfg || {};
                let iWidth = 800;
                let iHeight = 650;
                let iTop = (window.screen.availHeight - 30 - iHeight) / 2;
                let iLeft = (window.screen.availWidth - 10 - iWidth) / 2;
                let url = 'https://www.pinterest.com/pin/create/button/?url=' + encodeURIComponent(cfg.link) + '&media=' + encodeURIComponent(cfg.picture) + '&description=' + encodeURIComponent(cfg.description);
                window.open(url, '', 'width=' + iWidth + ',height=' + iHeight + ',top=' + iTop + ',left=' + iLeft);
                if (typeof callback == 'function') {
                    callback();
                }
            }
        },
        google: {
            start: function (cfg, callback) {
                let shareUrl = 'https://plus.google.com/share?url=' + encodeURIComponent(cfg.link);
                window.open(shareUrl, '', 'width=500,height=500,top=' + (window.innerHeight / 2 - 250) + ',left=' + (window.innerWidth / 2 - 250));
                if (typeof callback == 'function') {
                    callback();
                }
            }
        },
        tumblr: {
            start: function (cfg, callback) {
                let shareUrl = 'http://tumblr.com/widgets/share/tool?canonicalUrl=' + cfg.link + '&caption=' + cfg.description;
                window.open(shareUrl, '', 'width=500,height=500,top=' + (window.innerHeight / 2 - 250) + ',left=' + (window.innerWidth / 2 - 250));
                if (typeof callback == 'function') {
                    callback();
                }
            }
        }
    };
    window.share.fackbook.init();
    window.share.twitter.init();

    let lastTime = 0;
    let vendors = ['webkit', 'moz'];
    for (let x = 0; x < vendors.length && !window.requestAnimationFrame; ++x) {
        window.requestAnimationFrame = window[vendors[x] + 'RequestAnimationFrame'];
        window.cancelAnimationFrame = window[vendors[x] + 'CancelAnimationFrame'] || // Webkit中此取消方法的名字变了
            window[vendors[x] + 'CancelRequestAnimationFrame'];
    }

    if (!window.requestAnimationFrame) {
        window.requestAnimationFrame = function (callbackName, element) {
            let currTime = new Date().getTime();
            let timeToCall = Math.max(0, 16.7 - (currTime - lastTime));
            let id = window.setTimeout(function () {
                callbackName(currTime + timeToCall);
            }, timeToCall);
            lastTime = currTime + timeToCall;
            return id;
        };
    }
    if (!window.cancelAnimationFrame) {
        window.cancelAnimationFrame = function (id) {
            clearTimeout(id);
        };
    }

    window.shareSuccess = function (type) {
        $.ajax({
            url: window.DOMAIN + 'index.php?m=share_story&a=ajax_share_count&t=' + new Date().getTime(),
            type: 'GET',
            dataType: 'json',
            success: function (data) {
                if (typeof cb == 'function') {
                }
            },
            error: function () {
                if (typeof err == 'function') {
                }
            }
        });
    };
})();
