<template>
    <div class="inner">
        <h2>{{ $lang('details') }}</h2>
        <div class="rule_box" v-html="data.rule_content" ></div>
        <p class="box-sure"><span class="btn btn_ok" @click="closeDiv">{{ $lang('btn_ok')}} </span></p>
    </div>
</template>

<script>

export default {
    name: 'detail',
    props: ['data'],
    data () {
        return {
            lang: window.GESHOP_LANG
        };
    },
    components: {},
    methods: {
        closeDiv () {
            this.$emit('hideDia', false);
        }
    }
};
</script>
<style>
    .U000247_lottery_wrap .rule_box p {
        line-height: 30px;
        /*margin-bottom: 10px;*/
        font-size: 16px;
    }
    @media (max-width: 767px) {
        .U000247_lottery_wrap .rule_box p {
            font-size: 12px;
            line-height: 15px;
            margin-bottom: 4px;
        }
    }

</style>
<style scoped lang="less">
    .inner {
        h2 {
            margin-bottom: 35px;
            @media (max-width: 767px) {
                margin-bottom: 15px;
            }
        }
        .rule_box {
            max-height: 296px;
            overflow-y: auto;
            text-align: left;
            @media (max-width: 767px) {
                width: 80%;
                margin: 0 auto;
                &::-webkit-scrollbar {
                    width: 5px
                }
                &::-webkit-scrollbar-track {
                    border-radius: 6px;
                    background-color: transparent
                }
                &::-webkit-scrollbar-thumb {
                    border-radius: 6px;
                    background: #616161
                }
            }
        }
        .box-sure {
            text-align: center;
        }
    }

</style>
