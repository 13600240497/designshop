/* 自定义布局窗口缩放监听 */
var public_layout_pc = $('.geshop-layout-pc'),
    public_layout_wap = $('.geshop-layout-wap');
function layoutInit() {
    if (sessionStorage.getItem('gs_platform') == 'pc') {
        if (public_layout_pc && public_layout_wap) {
            public_layout_wap.parent().addClass('hidden_row');
            public_layout_pc.parent().removeClass('hidden_row')  
        }
    } else if (sessionStorage.getItem('gs_platform') == 'pad') {
        if (public_layout_pc && public_layout_wap) {
            public_layout_wap.parent().addClass('hidden_row');
            public_layout_pc.parent().removeClass('hidden_row')
        }
    } else if (sessionStorage.getItem('gs_platform') == 'wap') {
        if (public_layout_pc && public_layout_wap) {
            public_layout_pc.parent().addClass('hidden_row')
            public_layout_wap.parent().removeClass('hidden_row'); 
        }
    }
}

function beforeunload() {
    // sessionStorage.setItem('gs_platform', 'pc');
    window.onbeforeunload = function(event) { 
        sessionStorage.setItem('gs_platform', 'pc');
    };
}

function onBeforeunload () {
    window.addEventListener('onbeforeunload', function() {
        beforeunload()
    }, false);
}

function setPlatform () {
    var cw = document.body.clientWidth;
    if (cw >= 1025){
        // pc
        sessionStorage.setItem('gs_platform', 'pc')
    } else if (cw <= 1024 && cw >= 768) {
        // pad
        sessionStorage.setItem('gs_platform', 'pad')
    } else if(cw <= 767) {
        // wap
        sessionStorage.setItem('gs_platform', 'wap')
    }
}

function handlPlatform() {
    var isEditEnv = $('.geshop-layout-L000057-box').data('editenv');
    if (isEditEnv == 1) {
        var platform = sessionStorage.getItem('gs_platform') || 'pc';
        sessionStorage.setItem('gs_platform', platform);

        // 监听当前选择的平台
        window.addEventListener('storage', function() {
            layoutInit()
        }, false);
    } else {
        setPlatform();
        // 监听窗口缩放
        window.addEventListener('resize', function() {
            setTimeout(function() {
                setPlatform();
                layoutInit();
            }, 300);
        }, false);
    }
}

/* 初始化 */
$(function() {
    // handlPlatform()
});



