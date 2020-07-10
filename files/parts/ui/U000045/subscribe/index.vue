<template>
    <div class="geshop-U000045-subscribe geshop-col-12">
        <a
            href="javascript:;"
            :data-logsss-event-value="logsss"
            class="logsss_event js_logsss_click_delegate">
            <button :class="activityClass" @click="handle_click">{{ text }}</button>
        </a>

        <!-- 引导APP下载 only for wap -->
        <div class="subscribe-dialog download" v-if="dialog.download.visible">
            <div class="subscribe-dialog-main">
                <div class="subscribe-dialog-head">
                    <img src="https://uidesign.zafcdn.com/ZF/image/2414/download.png" alt="">
                </div>
                <div class="subscribe-dialog-body">
                    <div v-html="$lang('subscribe_success')"></div>
                    <span class="subscribe-dialog-close bold" @click="open_dialog('download', false)">{{ $lang('go_shop') }}</span>
                    <span class="subscribe-dialog-close bold" @click="handle_download_app">{{ label_download_app_site }}</span>
                </div>
            </div>
        </div>

        <!-- 没有权限的弹窗 only for app -->
        <div class="subscribe-dialog auth" v-if="dialog.auth.visible">
            <div class="subscribe-dialog-main">
                <div class="subscribe-dialog-head">
                    <img src="https://uidesign.zafcdn.com/ZF/image/2414/message.png" alt="">
                </div>
                <div class="subscribe-dialog-body">
                    <div v-html="label_subscribe_success"></div>
                    <span class="subscribe-dialog-close bold" @click="open_dialog('auth', false)">{{ $lang('rule_close') }}</span>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
import core from '../../U000044/subscribe/core.js';
export default {
    extends: core
};
</script>

<style lang="less" scoped>
    .geshop-U000045-subscribe {
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
        width:7.23rem;
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
        padding: 0.5333333rem;
        color: #333333;
        text-align: left;
        font-size: 0.4rem;
        line-height: 1.5em;
    }
    .subscribe-dialog-close {
        display: block;
        margin-top: 0.26rem;
        width: 100%;
        height:1.07rem;
        line-height: 1.07rem;
        background:rgba(45,45,45,1);
        border-radius:5px;
        color: #fff;
        font-size: 0.4rem;
        text-align: center;
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
