<template>
    <div class="inner message">
        <template v-if="userData.message == 'not_start'">
            <div class="act-comming">
                <p>{{ $lang('lott_start') }}:</p>
                <p class="down-time"><geshop-cutdown :times="userData.times" :type="userData.timeType"></geshop-cutdown></p>
            </div>
        </template>
        <template v-else>
            <p class="msg_cont" v-html="userData.message || 'Test Message'"></p>
        </template>
        <span class="btn btn_ok" @click="closeDiv">{{ $lang('btn_ok') }} </span>
    </div>
</template>

<script>
export default {
    name: 'message',
    props: ['userData'],
    data () {
        return {

        };
    },
    mounted () {

    },
    components: {},
    methods: {
        closeDiv () {
            this.$emit('hideDia', false);
        }
    }
};
</script>

<style scoped lang="less">
    .inner.message {
        text-align: center;
        .msg_cont {
            font-size: 18px;
            margin: 0 auto;
            line-height: 23px;
            @media (max-width: 767px) {
                max-width: 212px;
                font-size: 12px;
                line-height: 18px;
            }
        }
    }
</style>
