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
                let currentUrl = window.location.href;
                window.location.href = window.HTTPS_LOGIN_DOMAIN + JS_LANG + 'm-users-a-sign.htm?ref=' + currentUrl;
            }
            this.$emit('hideDia', false);
        }
    }
};
</script>

<style scoped lang="less">
    .dia-coupons {
        width: 570px;
        height: 409px;
        background: #FFFFFF;
        border-radius: 2px;
        text-align: center;
        overflow: hidden;
    }

    .box-title {
        color: #222222;
        font-size: 28px;
        height: 33px;
        line-height: 33px;
        margin: 50px auto 29px;
    }
    .use-now {
        display: inline-block;
        width:370px;
        height:50px;
        background:#F8386A;
        border-radius:2px;
        line-height: 50px;
        font-size: 22px;
        margin-top: 48px;
        color: #FFFFFF;
        cursor: pointer;
    }

</style>
