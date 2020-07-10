<template>
    <div class="dia-coupons">
        <p class="box-title site-font-bold">{{ language.congrats }} !</p>
        <cutDown :userData="userData" :language="language"></cutDown>
        <span class="use-now site-font-bold" @click="userN">{{ language.shop_now }} >></span>
    </div>
</template>

<script>
import cutDown from './cutdown';
export default {
    name: 'coupons',
    props: ['userData', 'language'],
    data () {
        return {
        };
    },
    mounted () {
    },
    components: {
        cutDown
    },
    methods: {
        userN () {
            if (window.USER_ID == 0) {
                if (window.GESHOP_PLATFORM === 'app') {
                    GEShopSiteCommon.appLogin(1);
                } else {
                    let currentUrl = window.location.href;
                    window.location.href = DOMAIN_LOGIN + '/m-users-a-sign.html?ref=' + currentUrl;
                }
            }
            this.$emit('hideDia', false);
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

    .dia-coupons {
        .w(280);
        .h(195);
        background: #FFFFFF;
        border-radius: 2/37.5rem;
        text-align: center;
        overflow: hidden;
    }

    .box-title {
        color: #222222;
        .fs(18);
        .h(22);
        .lh(22);
        margin: 23/37.5rem auto 12/37.5rem;
    }
    .use-now {
        display: inline-block;
        .w(150);
        .h(30);
        background:#F8386A;
        border-radius:2/37.5rem;
        .lh(30);
        font-size: 16/37.5rem;
        margin-top: 16/37.5rem;
        color: #FFFFFF;
        cursor: pointer;
    }

</style>
