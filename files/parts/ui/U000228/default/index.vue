<template>
    <div
        class="geshop-u000228-default-body"
        :class="isEditEnv ? 'is_edit' :'is_prod'">
        <div class="wrap">

            <!-- swiper for pc -->
            <swiper ref="swiperPC" v-show="platform === 'pc' && list.pc.length > 0" :options="swiperOption.pc">
                <!-- 图片 -->
                <swiper-slide v-for="item in list.pc" :key="item.imgUrl">
                    <a :target="openType" :href="item.imgLink || 'javascript:;'">
                        <img :src="defaultImg.loading" :data-src="item.imgUrl" class="swiper-lazy">
                    </a>
                </swiper-slide>

                <!-- 分页 -->
                <div
                    v-show="list.pc.length > 1"
                    slot="pagination"
                    class="swiper-pagination swiper-pagination-pc"
                    :class="classIco ? 'swiper-default-dot' : 'swiper-default-line' ">
                </div>

                <!-- 上一页 -->
                <div
                    v-if="classIco && list.pc.length > 1"
                    slot="button-prev"
                    class="swiper-button-box"
                    :class="'swiper-button-' + list.pc.length">
                    <div class="swiper-button swiper-button-prev"></div>
                </div>

                <!-- 下一页 -->
                <div
                    v-if="classIco && list.pc.length > 1"
                    slot="button-next"
                    class="swiper-button-box"
                    :class="'swiper-button-' + list.pc.length">
                    <div class="swiper-button swiper-button-next"></div>
                </div>
             
            </swiper>

            <!-- swiper for pad -->
            <swiper ref="swiperPAD" v-show="platform === 'pad' && list.pad.length > 0" :options="swiperOption.pad">

                <!-- 图片 -->
                <swiper-slide v-for="item in list.pad" :key="item.imgUrl">
                    <a :target="openType" :href="item.imgLink || 'javascript:;'">
                        <img :src="defaultImg.loading" :data-src="item.imgUrl" class="swiper-lazy">
                    </a>
                </swiper-slide>
                
                <!-- 分页 -->
                <div
                    v-show="list.pad.length > 1"
                    slot="pagination"
                    class="swiper-pagination swiper-pagination-pad"
                    :class="classIco ? 'swiper-default-dot' : 'swiper-default-line' ">
                </div>
            </swiper>

            <!-- swiper for wap  -->
            <swiper ref="swiperWAP" v-show="platform === 'wap' && list.wap.length > 0" :options="swiperOption.wap">
                <!-- 图片 -->
                <swiper-slide v-for="item in list.wap" :key="item.imgUrl">
                    <a :target="openType" :href="item.imgLink || 'javascript:;'">
                        <img :src="defaultImg.loading" :data-src="item.imgUrl" class="swiper-lazy">
                    </a>
                </swiper-slide>

                <!-- 分页 -->
                <div
                    v-show="list.wap.length > 1"
                    slot="pagination"
                    class="swiper-pagination swiper-pagination-m"
                    :class="classIco ? 'swiper-default-dot' : 'swiper-default-line' ">
                </div>
            </swiper>
        </div>
    </div>
</template>

<script type="text/javascript">
import { swiper, swiperSlide } from 'vue-awesome-swiper';
import 'swiper/dist/css/swiper.css';

// 通用 swiper 配置项
const swiperCommonConfig = {
    autoplay: {
        // 用户操作swiper之后，是否禁止autoplay
        disableOnInteraction: false
    },
    autoHeight: true,
    effect: 'fade',
    loop: true,
    init: false,
    lazy: {
        lazy: true,
        loadOnTransitionStart: true,
        loadPrevNext: true
    }
};

export default {
    name: 'geshop-u000228-default-body',
    components: { swiper, swiperSlide },
    props: ['theme', 'pid', 'data'],
    data () {
        return {
            // swiper 合并各终端的配置项
            swiperOption: {
                pc: Object.assign({
                    pagination: {
                        el: '.swiper-pagination-pc',
                        clickable: true
                    },
                    navigation: {
                        nextEl: '.swiper-button-next',
                        prevEl: '.swiper-button-prev'
                    }
                }, swiperCommonConfig),

                pad: Object.assign({
                    pagination: { el: '.swiper-pagination-pad' }
                }, swiperCommonConfig),

                wap: Object.assign({
                    pagination: { el: '.swiper-pagination-m' }
                }, swiperCommonConfig)
            },
            // 原点，还是线条
            classIco: this.data.box_banner_ico == '0',
            // 默认图
            defaultImg: {
                loading: 'https://geshopimg.logsss.com/uploads/0vpxHOz9lsCGnERZVThBfdcyF7Kwk1me.png',
                pc: 'https://geshopimg.logsss.com/uploads/hFqsv8kDEVM9GLAgyw21lX5WfYuiB6Sb.png',
                pad: 'https://geshopimg.logsss.com/uploads/esZuGah348NAH09F6jpKRwcoyMbDmzXT.png',
                wap: 'https://geshopimg.logsss.com/uploads/HsvKLCn5tj7pQEPJzXgcrSuM3NhUBkFl.png'
            },
            // 列表
            list: {
                pc: [],
                pad: [],
                wap: []
            }
        };
    },
    computed: {
        // 平台
        platform () {
            return this.$store.state.dresslily.media_platform;
        },
        // 获取当前端 pc/pad/wap
        media_platform () {
            return this.$store.state.dresslily.media_platform;
        },
        openType () {
            return this.media_platform === 'pc' || this.media_platform === 'pad' ? this.$root.data.is_open_new : this.$root.data.is_open_new_m;
        },
        isEditEnv () {
            return this.data.isEditEnv === 1;
        }
    },

    created () {
        const pc = [...this.$root.data.pc_datas || []];
        const pad = [...this.$root.data.pad_datas || []];
        const wap = [...this.$root.data.m_datas || []];

        // 如果是装修页，空数据则添加预览图
        if (this.isEditEnv) {
            pc.length <= 0 && pc.push({ imgUrl: this.defaultImg['pc'] });
            pad.length <= 0 && pad.push({ imgUrl: this.defaultImg['pad'] });
            wap.length <= 0 && wap.push({ imgUrl: this.defaultImg['wap'] });
        }

        this.list.pc = [...pc];
        this.list.pad = [...pad];
        this.list.wap = [...wap];
    },

    mounted () {
        try {
            this.$nextTick(() => {
                setTimeout(() => {
                    // 如果图片只有1张的话，停止 autoplay（因为会重复的闪）
                    this.list.pc.length <= 1 && this.$refs.swiperPC.swiper.autoplay.stop();
                    this.list.pad.length <= 1 && this.$refs.swiperPAD.swiper.autoplay.stop();
                    this.list.wap.length <= 1 && this.$refs.swiperWAP.swiper.autoplay.stop();
                }, 0);
            });
        } catch (err) {}

        // 去处骨架图
        this.$store.dispatch('global/loaded', this);
        this.$refs.swiperPC.swiper.init();
        this.$refs.swiperPAD.swiper.init();
        this.$refs.swiperWAP.swiper.init();
        setInterval(() => {
            this.$refs.swiperPC.swiper.updateAutoHeight();
            this.$refs.swiperPAD.swiper.updateAutoHeight();
            this.$refs.swiperWAP.swiper.updateAutoHeight();
        }, 500);
    }
};
</script>

<style lang="less" scoped>
    .geshop-u000228-default-body {
        position: relative;

        .wrap {
            width: 100%;
            height: 100%;
            position: relative;
            /*background: url(https://geshopimg.logsss.com/uploads/hFqsv8kDEVM9GLAgyw21lX5WfYuiB6Sb.png);*/

            .swiper-wrapper {
                -webkit-align-items: center;
                box-align: center;
                -moz-box-align: center;
                -webkit-box-align: center;
            }

            .swiper-container {
                width: 100%;
                height: 100%;

                .swiper-slide {
                    -webkit-align-items: center;
                    box-align: center;
                    -moz-box-align: center;
                    -webkit-box-align: center;
                    text-align: center;
                    background-color: #fff;
                    display: table;

                    a {
                        /*                        display: block;
                                                height: 600px;*/
                        display: table-cell;
                        vertical-align: middle;
                        max-width: 100%;
                        max-height: 100%;
                        height: auto;
                        font-size: 0;

                        img {
                            max-width: 100%;
                            max-height: 100%;
                            border: 0;
                        }
                    }
                }

                .swiper-pagination {
                    height: 12px;
                    line-height: 12px;
                    bottom: 24px;
                }

                .swiper-button-box {
                    width: 100%;
                    height: 24px;
                    position: absolute;
                    bottom: 20px;
                }

                .swiper-button {
                    width: 24px;
                    height: 24px;
                    background-size: 100%;
                    transition: all .3s;

                    &:hover {
                        opacity: 1;
                        transition: all .3s;
                    }

                    &.swiper-button-prev {
                        top: 0;
                        left: 50%;
                        margin: 2px 0 0 -80px;
                        background-image: url(https://geshopimg.logsss.com/uploads/9vCcXS0Zy1AdYathLmp7rgPJxnzq56HD.png);
                    }

                    &.swiper-button-next {
                        top: 0;
                        right: 50%;
                        margin: 2px -80px 0 0;
                        background-image: url(https://geshopimg.logsss.com/uploads/cWV4b6C5jv9aoAFMDRLiOwuKsGQyY2mU.png)
                    }
                }

                .swiper-button-2 {
                    .swiper-button {
                        &.swiper-button-prev {
                            margin: 2px 0 0 -76px;
                        }

                        &.swiper-button-next {
                            margin: 2px -76px 0 0;
                        }
                    }
                }

                .swiper-button-3 {
                    .swiper-button {
                        &.swiper-button-prev {
                            margin: 2px 0 0 -90px;
                        }

                        &.swiper-button-next {
                            margin: 2px -90px 0 0;
                        }
                    }
                }

                .swiper-button-4 {
                    .swiper-button {
                        &.swiper-button-prev {
                            margin: 2px 0 0 -104px;
                        }

                        &.swiper-button-next {
                            margin: 2px -104px 0 0;
                        }
                    }
                }

                .swiper-button-5 {
                    .swiper-button {
                        &.swiper-button-prev {
                            margin: 2px 0 0 -117px;
                        }

                        &.swiper-button-next {
                            margin: 2px -117px 0 0;
                        }
                    }
                }

                .swiper-button-6 {
                    .swiper-button {
                        &.swiper-button-prev {
                            margin: 2px 0 0 -134px;
                        }

                        &.swiper-button-next {
                            margin: 2px -134px 0 0;
                        }
                    }
                }

                .swiper-button-7 {
                    .swiper-button {
                        &.swiper-button-prev {
                            margin: 2px 0 0 -148px;
                        }

                        &.swiper-button-next {
                            margin: 2px -148px 0 0;
                        }
                    }
                }

                .swiper-button-8 {
                    .swiper-button {
                        &.swiper-button-prev {
                            margin: 2px 0 0 -164px;
                        }

                        &.swiper-button-next {
                            margin: 2px -164px 0 0;
                        }
                    }
                }

                .swiper-button-9 {
                    .swiper-button {
                        &.swiper-button-prev {
                            margin: 2px 0 0 -178px;
                        }

                        &.swiper-button-next {
                            margin: 2px -178px 0 0;
                        }
                    }
                }

                .swiper-button-10 {
                    .swiper-button {
                        &.swiper-button-prev {
                            margin: 2px 0 0 -192px;
                        }

                        &.swiper-button-next {
                            margin: 2px -192px 0 0;
                        }
                    }
                }
            }

            .swiper-default-img {
                width: 100%;
            }

            //处理slide不同高度问题
            .swiper-container-fade {
                .swiper-slide {
                    opacity: 0 !important;
                }

                .swiper-slide-active {
                    opacity: 1 !important;
                }
            }
        }
    }

    @media screen and (min-width: 1025px) {
        .geshop-u000228-default-body {
            .wrap {
                .item-for-pad, .item-for-m {

                }
            }
        }
    }

    @media screen and (max-width: 1024px) and (min-width: 768px) {
        .geshop-u000228-default-body {
            .wrap {
                .item-for-pc, .item-for-m {

                }
            }
        }
    }

    @media screen and (max-width: 767px) and (min-width: 0px) {
        .geshop-u000228-default-body {
            .wrap {
                .item-for-pc, .item-for-pad {

                }
            }
        }
    }
</style>

<style lang="less">
    .geshop-u000228-default-body {
        .wrap {
            .swiper-default-dot {
                .swiper-pagination-bullet {
                    width: 8px;
                    height: 8px;
                    opacity: .9;
                    margin: 0 8px !important;
                    background: none;
                    border: 2px solid #ffffff;

                    &.swiper-pagination-bullet-active {
                        background: #000000;
                    }
                }
            }

            .swiper-default-line {
                .swiper-pagination-bullet {
                    width: 12px;
                    height: 8px;
                    opacity: .9;
                    border-radius: 6px;
                    margin: 0 8px !important;
                    background: #ffffff;
                    border: 0;
                    position: relative;

                    &.swiper-pagination-bullet-active {
                        background: #000000;
                    }
                }
            }
        }
    }
</style>
