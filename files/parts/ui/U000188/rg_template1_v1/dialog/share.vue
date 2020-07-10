<template>
    <div class="share-wrap">
        <!--  userData.diaType   1:登录之后刷新页面弹窗  2：点击之后弹窗-->
        <h2 class="site-font-bold" v-if="userData.diaType == 1">{{ language.share_title1 }}</h2>
        <h2 class="site-font-bold" v-else>{{ language.share_title2 }}</h2>
        <cutDown :userData="userData" :language="language" v-if="userData.diaType == 1"></cutDown>

        <!--刷新新用户-->
        <div class="" v-if="userData.is_new_user == true && userData.diaType == 1">
            <p class="text-desc">{{ language.new_user_desc }}</p>
        </div>
        <!-- 刷新老用户-->
        <div class="" v-else-if="userData.is_new_user == false && userData.diaType == 1">
            <p class="text-desc">{{ language.user_desc1 }}</p>
        </div>
        <!--登录新用户-->
        <div class="" v-else-if="userData.is_new_user == true && userData.diaType == 2">
            <p class="text-desc">{{ language.new_user_desc }}</p>
        </div>
        <!--登录老用户-->
        <div class="" v-else-if="userData.is_new_user == false && userData.diaType == 2">
            <p class="text-desc">{{ language.user_desc2 }}</p>
        </div>
        <div class="share-icon-list">
            <ul class="btn-list" v-if="platform === 'wap'">
                <li class="btn-share facebook" @click="shareStart('facebook')"></li>
                <li class="btn-share twitter" @click="shareStart('twitter')"></li>
                <li class="btn-share pinterest" @click="shareStart('pinterest')"></li>
            </ul>
            <span v-if="platform === 'app'" class="btn-list btn-share btn-shares" @click="shareStart('app')"></span>
            <span class="btn-share copy" @click="doCopy">
                <p v-if="copySucc"> {{ language.copyText }}</p>
            </span>
        </div>
    </div>
</template>

<script>
import cutDown from './cutdown';
export default {
    name: 'share',
    props: ['userData', 'language'],
    data () {
        return {
            platform: window.GESHOP_PLATFORM,
            lang: window.GESHOP_LANG,
            copySucc: false
        };
    },
    components: {
        cutDown
    },
    methods: {
        shareStart (type) {
            let shareParam = {
                name: $('[property="og:description"]').attr('content'),
                link: $('[property="og:url"]').attr('content') || window.location.href,
                picture: $('[property="og:image"]').attr('content'),
                title: $('[property="og:title"]').attr('content'),
                description: $('[property="og:description"]').attr('content')
            };
            switch (type) {
            case 'facebook':
                window.share.fackbook.start(shareParam, window.shareSuccess(type));
                break;
            case 'twitter':
                window.share.twitter.start(shareParam, window.shareSuccess(type));
                break;
            case 'pinterest':
                window.share.pinterest.start(shareParam, window.shareSuccess(type));
                break;
            case 'app':
                window.location.href = `webAction://sharing?shareUrl=${shareParam.link}&shareContent=${shareParam.description}&imageUrl=${shareParam.picture}&callback=${window.shareSuccess(type)}`;
                break;
            }
        },
        doCopy () {
            if (!this.copySucc) {
                this.$copyText($('[property="og:url"]').attr('content') || this.userData.share_link).then((e) => {
                    this.copySucc = true;
                    setTimeout(() => {
                        this.copySucc = false;
                    }, 1500);
                }, (e) => {
                    this.copySucc = false;
                    GEShopSiteCommon.dialog.message(e);
                });
            }
        }
    }
};
</script>

<style scoped lang="less">
    /*----- 宽度 -----*/
    .w(@px) {
        width: unit(@px / 37.5, rem);
    }

    /*----- 高度 -----*/
    .h(@px) {
        height: unit(@px / 37.5, rem);
    }

    /*----- 行高 -----*/
    .lh(@px) {
        line-height: unit(@px / 37.5, rem);
    }

    .fs(@px) {
        font-size: unit(@px / 37.5, rem);
    }
    .share-icon-list {
        display: block;
        font-size: 0;
        text-align: center;

    }
    .share-wrap {
        -webkit-box-sizing: border-box;
        -moz-box-sizing: border-box;
        box-sizing: border-box;
        .w(280);
        height:auto;
        background:rgba(255,255,255,1);
        border-radius:2/37.5rem;
        padding: 24/37.5rem  22/37.5rem  30/37.5rem;

        .text-desc {
            .fs(14);
            margin-top: 24/37.5rem;
            text-align: center;
            margin-bottom: 22/37.5rem;
            line-height: 14/37.5rem;
        }

        h2 {
            .fs(16);
            color:#F53565;
            text-align: center;
            margin-bottom: 14/37.5rem;
        }
    }
    .btn-list {
        display: inline-block;
        font-size: 0;
        text-align: center;
        vertical-align: middle;
    }
    .btn-share {
        display: inline-block;
        .w(30);
        .h(30);
        cursor: pointer;
        position: relative;
        vertical-align: middle;
        margin: 0 13/37.5rem;
        &.facebook {
            background: url("../images/facebook.png") 0 0 no-repeat;
            background-size: 100% 100%;
        }
        &.twitter {
            background: url("../images/twitter.png") 0 0 no-repeat;
            background-size: 100% 100%;
        }
        &.pinterest {
            background: url("../images/pinterest.png") 0 0 no-repeat;
            background-size: 100% 100%;
        }
        &.btn-shares {
            background: url("../images/share.png") 0 0 no-repeat;
            background-size: 100% 100%;
        }
        &.copy {
            background: url("../images/btn-copy.png") 0 0 no-repeat;
            background-size: 100% 100%;

            > p {
                .fs(12);
                color: #333;
                border: 2/75rem solid #e6bfa9;
                white-space: normal;
                padding: 10/75rem;
                text-align: left;
                background: #fff;
                position: absolute;
                width: 400/75rem;
                box-sizing: border-box;
                z-index: 2;
                right: -8/37.5rem;
                border-radius: 10/75rem;
                box-shadow: 0 0 20/75rem -10/75rem #000;
                opacity: 1;
                visibility: visible;
                bottom: 80/75rem;
                &:before {
                    content: '';
                    border-top: 8/37.5rem solid #e6bfa9;
                    border-right: 8/37.5rem solid transparent;
                    border-left: 8/37.5rem solid transparent;
                    position: absolute;
                    right: 16/37.5rem;
                    bottom: -8/37.5rem;
                }
                &:after {
                    content: '';
                    border-top: 8/37.5rem solid #fff;
                    border-right: 8/37.5rem solid transparent;
                    border-left: 8/37.5rem solid transparent;
                    right: 16/37.5rem;
                    bottom: -7/37.5rem;
                    z-index: 2;
                    position: absolute;
                }
            }
        }
    }
</style>
