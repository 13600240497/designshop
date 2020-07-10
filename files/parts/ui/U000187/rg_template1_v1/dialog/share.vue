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
            <ul class="btn-list">
                <li class="btn-share facebook" @click="shareStart('facebook')"></li>
                <li class="btn-share twitter" @click="shareStart('twitter')"></li>
                <li class="btn-share pinterest" @click="shareStart('pinterest')"></li>
            </ul>
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
                link: $('[property="og:url"]').attr('content'),
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
    .share-icon-list {
        display: block;
        font-size: 0;
        text-align: center;

    }
    .share-wrap {
        -webkit-box-sizing: border-box;
        -moz-box-sizing: border-box;
        box-sizing: border-box;
        width:570px;
        height:auto;
        background:rgba(255,255,255,1);
        border-radius:2px;
        padding: 50px 64px 60px;

        .text-desc {
            margin-top: 32px;
            font-size: 16px;
            line-height: 19px;
            text-align: center;
            margin-bottom: 23px;
        }

        h2 {
            font-size:28px;
            color:#F53565;
            text-align: center;
            margin-bottom: 32px;
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
        width: 48px;
        height: 48px;
        cursor: pointer;
        position: relative;
        vertical-align: middle;
        margin: 0 16px;
        &:hover {
            opacity: 0.85;
        }
        &.facebook {
            background: url("../images/facebook.png") 0 0 no-repeat;
        }
        &.twitter {
            background: url("../images/twitter.png") 0 0 no-repeat;
        }
        &.pinterest {
            background: url("../images/pinterest.png") 0 0 no-repeat;
        }
        &.copy {
            background: url("../images/btn-copy.png") 0 0 no-repeat;

            > p {
                font-size: 14px;
                color: #333;
                border: 1px solid #e6bfa9;
                white-space: normal;
                padding: 5px 8px;
                background: #fff;
                position: absolute;
                width: 240px;
                box-sizing: border-box;
                z-index: 2;
                left: 50%;
                margin-left: -120px;
                border-radius: 5px;
                box-shadow: 0 0 10px -5px #000;
                opacity: 1;
                visibility: visible;
                bottom: 60px;
                &:before {
                    content: '';
                    border-top: 8px solid #e6bfa9;
                    border-right: 8px solid transparent;
                    border-left: 8px solid transparent;
                    position: absolute;
                    left: 50%;
                    margin-left: -8px;
                    bottom: -9px;
                }
                &:after {
                    content: '';
                    border-top: 8px solid #fff;
                    border-right: 8px solid transparent;
                    border-left: 8px solid transparent;
                    left: 50%;
                    margin-left: -8px;
                    bottom: -8px;
                    z-index: 2;
                    position: absolute;
                }
            }
        }
    }
</style>
