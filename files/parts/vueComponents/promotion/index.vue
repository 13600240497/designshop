<template>
    <div :class="`item_promotion is-${$root.platform} is-${site_code}`"
         :style="style_promotions"
         @click="site_code !== 'pc' && handleToggle()">
        <div class="title"
             v-if="value && value.length == 1"
             v-html="promotionsFormat(value[0])"></div>
        <div class="title"
             v-if="value && value.length > 1"
             v-html="promotionsFormat(value[0], '1')"></div>

        <ul v-if="value && value.length > 1" class="promotion" :class="[{'is-hide':hide_list}]">
            <li v-for="(val, idx) in value" :key="idx" v-html="promotionsFormat(val)"></li>
        </ul>
    </div>
</template>

<script>
/**
 * RG,ZF营销信息
 */
export default {
    name: 'geshop-promotion',
    props: {
        value: {
            type: Array,
            default: () => []
        },
        color: {
            default: '#333333'
        }
    },
    data () {
        return {
            hide_list: true
        };
    },

    computed: {
        site_code () {
            return GESHOP_SITECODE;
        },
        /**
         * 营销信息样式
         */
        style_promotions () {
            let _color = this.color;
            const style = {
                'color': _color
            };
            return style;
        }
    },
    methods: {
        /**
         *  @Description 营销信息转化
         *
         */
        promotionsFormat (val, type) {
            let $val = val.replace(/&quot;/g, '"').replace(/&lt;/g, '<').replace(/&gt;/g, '>').replace(/&#39;/g, '\'');
            return type && type == 1 ? $val + '...' : $val;
        },
        handleToggle () {
            this.hide_list = !this.hide_list;
        }
    }
};
</script>

<style lang="less" scoped>
    .item_promotion {
        &.is-pc {
            position: relative;
            font-size: 12px;
            height: 20px;

            .promotion {
                position: absolute;
                top: 22px;
                left: 0;
                width: auto;
                display: none;
                padding: 12px;
                font-size: 12px;
                background-color: #FFFFFF;
                border: 1px solid rgba(238, 238, 238, 1);
                z-index: 99;

                &:before {
                    content: " ";
                    height: 0;
                    width: 0;
                    position: absolute;
                    pointer-events: none;
                    border: solid rgba(255, 255, 255, 0);
                    border-color: rgba(255, 255, 255, 0);
                    border-width: 5px;
                    top: -10px;
                    border-bottom-color: #eeeeee;
                    left: 50%;
                    margin-left: -56px;
                }

                &:after {
                    content: " ";
                    height: 0;
                    width: 0;
                    position: absolute;
                    pointer-events: none;
                    border: solid rgba(255, 255, 255, 0);
                    border-color: rgba(255, 255, 255, 0);
                    border-width: 4px;
                    top: -8px;
                    border-bottom-color: #ffffff;
                    left: 50%;
                    margin-left: -55px;
                }
            }

            .title {
                line-height: 20px;
                height: 20px;
                cursor: pointer;
            }
        }

        &.is-wap, &.is-app {
            position: relative;
            font-size: 12/37.5rem;
            height: 20/37.5rem;

            .promotion {
                position: absolute;
                width: 70%;
                top: 24/37.5rem;
                left: 0;
                display: none;
                padding: 12/37.5rem;
                background-color: #FFFFFF;
                border: 1px solid rgba(238, 238, 238, 1);
                z-index: 99;

                &.is-hide {
                    display: none;
                }

                &:before {
                    content: " ";
                    height: 0;
                    width: 0;
                    position: absolute;
                    pointer-events: none;
                    border: solid rgba(255, 255, 255, 0);
                    border-color: rgba(255, 255, 255, 0);
                    border-width: 5px;
                    top: -10px;
                    border-bottom-color: #eeeeee;
                    left: 50%;
                    margin-left: -56px;
                }

                &:after {
                    content: " ";
                    height: 0;
                    width: 0;
                    position: absolute;
                    pointer-events: none;
                    border: solid rgba(255, 255, 255, 0);
                    border-color: rgba(255, 255, 255, 0);
                    border-width: 4px;
                    top: -8px;
                    border-bottom-color: #ffffff;
                    left: 50%;
                    margin-left: -55px;
                }
            }

            .title {
                line-height: 20/37.5rem;
                height: 20/37.5rem;
                cursor: pointer;
            }
        }

        &:hover {
            .promotion {
                display: block;
            }
        }

        li {
            text-overflow: ellipsis;
            white-space: nowrap;
            word-wrap: break-word;
            overflow: hidden;
            color: #333333;
        }
    }

</style>
<style lang="less">
    .geshop-component-body .list-li {
        &:last-child,&:nth-last-child(2){
            .is-wap, .is-app {
                .promotion{
                    top: -78/37.5rem;
                    &:before{
                        top: initial;
                        bottom: -10px;
                        border-top-color: #eeeeee;
                        border-bottom-color:transparent;;
                    }
                    &:after{
                        top: initial;
                        bottom: -8px;
                        border-top-color: #ffffff;
                        border-bottom-color:transparent;;
                    }
                }
            }
        }
    }
</style>
