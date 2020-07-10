<template>
    <div class="geshop-U000044-subscribe geshop-col-12">
        <a
            href="javascript:;"
            :data-logsss-event-value="logsss"
            class="logsss_event js_logsss_click_delegate">
            <button :class="activityClass" @click="handle_click">{{ text }}</button>
        </a>

        <!-- 引导APP下载 -->
        <div class="subscribe-dialog download" v-if="dialog.download.visible">
            <div class="subscribe-dialog-main">
                <div class="subscribe-dialog-head">
                    <img src="https://uidesign.zafcdn.com/ZF/image/2414/downlead_pca.jpg" alt="">
                </div>
                <div class="subscribe-dialog-body">
                    <div v-html="$lang('subscribe_success')"></div>
                    <span class="subscribe-dialog-close bold" @click="open_dialog('download', false)">{{ $lang('go_shop') }}</span>
                    <span class="subscribe-dialog-close bold" @click="handle_download_app">{{ label_download_app_site }}</span>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
import core from './core';
export default {
    extends: core
};
</script>

<style lang="less" scoped>
    .geshop-U000044-subscribe {
        text-align: center;
    }

    button {
        font-size: 16px;
        font-weight: 100;
        border-radius: 20px;
        width: 240px;
        height: 40px;
        line-height: 38px;
        border: solid 1px;
        &.unclick.animate {
            -webkit-animation: subscribe_btn_scale .3s infinite alternate;
            -moz-animation: subscribe_btn_scale .3s infinite alternate;
            animation: subscribe_btn_scale .3s infinite alternate
        }
    }

    // 弹窗
    .subscribe-dialog {
        position: fixed;
        left: 0px;
        top: 0px;
        right: 0px;
        bottom: 0px;
        background: rgba(0,0,0,0.5);
        z-index: 999;
        display: flex;
        align-items: center;
        justify-content: center;
    }
    .subscribe-dialog-main {
        width: 642px;
        border-radius: 10px;
        overflow: hidden;
    }
    .subscribe-dialog-head {
        img {
            display: block;
            width: 100%;
        }
    }
    .subscribe-dialog-body {
        background-color: #fff;
        padding-top: 30px;
        padding-left: 30px;
        padding-right: 30px;
        padding-bottom: 30px;
        font-size: 18px;
        line-height: 1.5em;
        text-align: left;
    }
    .subscribe-dialog-close {
        display: block;
        margin-top: 20px;
        width: 100%;
        height: 60px;
        line-height:  60px;
        background:rgba(45,45,45,1);
        border-radius:5px;
        color: #fff;
        font-size: 24px;
        text-align: center;
    }
    .subscribe-dialog-close + .subscribe-dialog-close {
        margin-top: 20px;
    }

    @-webkit-keyframes subscribe_btn_scale {
        from {
            -webkit-transform: scale(1);
            transform: scale(1)
        }

        to {
            -webkit-transform: scale(.95);
            transform: scale(.95)
        }
    }

    @-moz-keyframes subscribe_btn_scale {
        from {
            -moz-transform: scale(1);
            transform: scale(1)
        }

        to {
            -moz-transform: scale(.95);
            transform: scale(.95)
        }
    }

    @keyframes subscribe_btn_scale {
        from {
            -webkit-transform: scale(1);
            -moz-transform: scale(1);
            transform: scale(1)
        }

        to {
            -webkit-transform: scale(.95);
            -moz-transform: scale(.95);
            transform: scale(.95)
        }
    }
</style>
