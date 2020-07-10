<template>
    <div :class="classBody" class="U000025-model1_v1-body">
        <div class="geshop-image-wrapper">
            <a :href="this.data.imgURL||'javascript:;'" :target="this.data.imgURL ? '_blank' : '_self'" :style="!this.data.imgURL && {'cursor':'initial'}">
                <!--                <geshop-image-goods :src="this.data.imgSrc || this.defaultBanner" :lazyload.number="false"></geshop-image-goods>-->
                <div class="geshop-components-default-image-goods">
                    <span>
                        <!-- 区分装修页和预览发布页 -->
                        <template v-if="$root.is_edit_env == 1">
                            <img :src="this.data.imgSrc || this.defaultBanner"/>
                        </template>
                        <!-- 预览发布页开启懒加载，埋点 -->
                        <template v-else>
                            <img
                                :src="this.data.imgSrc || this.defaultBanner"/>
                        </template>
                    </span>
                </div>
            </a>
        </div>

    </div>

</template>

<script>
export default {
    name: 'U000025-model1_v1',
    props: ['data', 'pid'],
    data () {
        return {
            isEditEnv: 0,
            defaultBanner: 'https://geshopcss.logsss.com/imagecache/geshop-test/resources/images/default/banner.png'
        };
    },
    created () {
        if (this.data) {

        }
    },
    mounted () {
        this.$store.dispatch('global/async_goods_init', this);
    },

    computed: {
        classBody () {
            return 'geshop-U000025-model1_v1-body-' + this.pid;
        }
    },
    methods: {}
};
</script>
<style lang="less" scoped>

</style>>
<style lang="less">
    .U000025-model1_v1-body {
        .geshop-image-wrapper {
            text-align: center;
            a{
                display:block;width:100%;height:100%
            }
            .geshop-components-default-image-goods {
                display: block;
                background: none;
                width: 100%;
                height: 100%;
                font-size:0;
                line-height:1;
                span {
                    display: inline-block;
                    vertical-align: middle;
                    text-align: center;
                }

                img {
                    max-width: 100%;
                    width: auto;
                    height: auto;
                    margin-left: auto;
                    margin-right: auto;
                }
            }

        }
    }

</style>
