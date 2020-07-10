<template>
    <div class="inner share-wrap" ref="shareWrap">
        <p class="msg-body" v-html="userData.message"></p>
        <div class="share-icon-list">
            <span class="btn btn_ok" v-if="plate == 'app'" @click="shareStart()">{{ $lang('lott_share') }}</span>
            <ul class="btn-list" v-else>
                <template>
                    <li v-for="item in data.share_data" :key="item" class="btn-share" :title="item" :class="item.toLowerCase()" @click="shareStart(item.toLowerCase())"></li>
                </template>
            </ul>
            <span class="btn btn_ok" @click="closeDiv">{{ $lang('btn_ok') }} </span>
        </div>
    </div>
</template>

<script>
export default {
    name: 'share',
    props: ['userData', 'data'],
    data () {
        return {
            lang: window.GESHOP_LANG,
            copySucc: false,
            plate: window.GESHOP_PLATFORM
        };
    },
    components: {

    },
    methods: {
        closeDiv () {
            this.$emit('hideDia', false);
        },
        shareStart (type) {
            let shareParam = {
                name: $('[property="og:description"]').attr('content'),
                link: $('[property="og:url"]').attr('content'),
                picture: $('[property="og:image"]').attr('content'),
                title: $('[property="og:title"]').attr('content'),
                description: $('[property="og:description"]').attr('content')
            };
            if (window.GESHOP_PLATFORM == 'app') {
                this.shareSuc();
                window.location.href = 'webAction://sharing?shareUrl=' + shareParam.link + '&shareTitle=' + shareParam.title + '&shareContent=' + shareParam.description + '&imageUrl=' + shareParam.picture + '&callback=U000247_app_shareSuccess()';
            } else {
                switch (type) {
                case 'tumblr':
                    window.share.tumblr.start(shareParam, window.U000247_app_shareSuccess(1, type, this.shareSuc));
                    break;
                case 'twitter':
                    window.share.twitter.start(shareParam, window.U000247_app_shareSuccess(1, type, this.shareSuc));
                    break;
                case 'pinterest':
                    window.share.pinterest.start(shareParam, window.U000247_app_shareSuccess(1, type, this.shareSuc));
                    break;
                }
            }
        },
        shareSuc (data) {
            this.closeDiv();
            // 607 今日本渠道的分享机会已超出限制 "This channel have already given!"
        }
    }
};
</script>

<style lang="less">
    .share-wrap {
        .msg-body {
            max-width: 390px;
            margin: 0 auto;
            font-size: 18px;
            line-height: 25px;
        }
        .btn-list {
            font-size: 0;
            margin-top: 23px;
            .btn-share {
                cursor: pointer;
                display: inline-block;
                width: 44px;
                height: 44px;
                margin: 0 18px;
            }
        }
    }
    @media (max-width: 767px) {
        .share-wrap {
            .msg-body {
                font-size: 12px;
                line-height: 18px;
            }
            .btn-list {
                font-size: 0;
                margin-top: 20px;
                .btn-share {
                    cursor: pointer;
                    display: inline-block;
                    width: 38px;
                    height: 38px;
                    vertical-align: middle;
                    margin: 0 15px;
                }
            }
        }
    }
</style>
<style scoped lang="less">

</style>
