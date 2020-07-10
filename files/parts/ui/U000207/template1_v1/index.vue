<template>
    <div class="geshop-component-body geshop-U000207-template1_v1-body" :class="commonData.boxWrapMedia"
         v-if="commonData.tabs.length > 0 || $root.is_edit_env == true" ref="myTabWrap">
        <template>
            <div class="gs-goods-bd">
                <!-- 商品轮播列表-->
                <div class="swiper-wrapper"
                     :class="commonData && commonData.tabs.length > 4 ? 'has_navigation' : 'has_navigation--no'">
                    <swiper :options="swiperOption" class="gs-tab-label"
                            ref="mySwiper">
                        <swiper-slide
                            v-for="(item, index) in ( (commonData && commonData.tabs.length > 0 ) ? commonData.tabs : 3 )"
                            :class="['gs-tab-list', {on :commonData.current.index == index},{'swiper-no-swiping' : commonData.tabs.length <=4}]"
                            :key="index"
                        >
                            <div class="tab-desc"
                                 @click.prevent="handleChangeTab(index)">
                                <template v-if="commonData.view_platform !== 'm'">
                                    <div class="item-time" v-html="convert_time_date(item.start)"></div>
                                    <div class="item-status">
                                        <span class="item-status-text">{{ getTimeStatus(item.start,item.end) }}</span>
                                        <!-- 倒计时时间 -->
                                        <geshop-timer :start="item.start" :end="item.end"
                                                      :format="'hms'"></geshop-timer>
                                    </div>
                                </template>
                                <template v-else>
                                    <div class="item-time">{{ convert_time_label(item.start) }}</div>
                                    <div class="item-date">{{ convert_time_status(item.start, item.end) }}</div>
                                </template>

                            </div>
                        </swiper-slide>
                    </swiper>
                    <div v-show="commonData.tabs && commonData.tabs.length > 4 && commonData.view_platform !== 'm'">
                        <div class="swiper-button-prev" slot="button-prev"></div>
                        <div class="swiper-button-next" slot="button-next"></div>
                    </div>

                </div>
            </div>
        </template>
        <div class="gs-tab-content">
            <div class="gs-tab-item">
                <ul class="clearfix" :class="class_body">
                    <!--列表-->
                    <li class="list-item" v-for="(item, index) in list" :key="item.goods_sn + '_' + index">
                        <div class="list-item-img">
                            <geshop-analytics-href
                                :href="item.url_title"
                                :sku="item.goods_sn"
                                :cate="item.cateid"
                                :warehouse="item.warehousecode"
                                :goods_id="item.goods_id"
                                target="_blank"
                                class="item-img">
                                <!--图片-->
                                <geshop-image-goods :src="item.goods_img"
                                                    :sku="item.goods_sn"
                                                    :lazyload="true"
                                                    :index="index">

                                </geshop-image-goods>
                                <!--折扣-->
                                <geshop-discount :value="item.discount"
                                                 :percent="item.discount"
                                                 :visible="parseInt(item.discount) > 0"></geshop-discount>
                                <!--售罄-->
                            </geshop-analytics-href>
                            <geshop-soldout
                                :visible="item.activity_number_left <= 0">
                            </geshop-soldout>
                        </div>
                        <!--商品标题-->
                        <div class="item-info-box">
                            <geshop-analytics-href
                                :href="item.url_title"
                                target="_blank"
                                :sku="item.goods_sn"
                                :cate="item.cateid"
                                :warehouse="item.warehousecode"
                                :goods_id="item.goods_id" class="item-title">
                                {{ item.goods_title || defaultText.goods_title}}
                            </geshop-analytics-href>
                            <!--售价信息-->
                            <p class="item-shop-price">
                                <geshop-shop-price :value="item.shop_price"></geshop-shop-price>
                                <geshop-market-price :value="item.market_price"
                                                     v-show="item.shop_price  - item.market_price > 0 ? false : true"></geshop-market-price>
                            </p>
                            <div class="item-progress">
                                <!--库存进度-->
                                <geshop-progress-bar
                                    :color1="$root.data.progress_left_color"
                                    :color2="$root.data.progress_total_color"
                                    :current="item.activity_number_left"
                                    :total="item.activity_number"
                                    :type="'full-100'"
                                    :limitType="$root.data.goodsLimitsTextType">
                                </geshop-progress-bar>
                                <!-- 库存描述百分比 -->
                                <geshop-progress-text
                                    :current="item.activity_number_left"
                                    :total="item.activity_number"
                                    :type="show_limitType"
                                ></geshop-progress-text>
                            </div>
                            <!--bottom_buy-->
                            <div class="item_button_buy" v-if="show_buynow === 1">
                                <geshop-analytics-href
                                    :sku="item.goods_sn"
                                    :cate="item.cateid"
                                    :warehouse="item.warehousecode"
                                    :goods_id="item.goods_id">
                                    <geshop-buynow :href="item.url_title"></geshop-buynow>
                                </geshop-analytics-href>
                            </div>
                        </div>
                    </li>
                </ul>
            </div>
        </div>
    </div>
</template>

<script>
import 'swiper/dist/css/swiper.css';
import { swiper, swiperSlide } from 'vue-awesome-swiper';

export default {
    props: ['data', 'pid'],
    components: {
        swiper,
        swiperSlide
    },
    data () {
        return {
            commonData: {
                boxWrapMedia: '', // 当前端class
                view_platform: 'pc', // viewport窗口类型 pc,pad m
                current: {},
                tabs: [{
                    index: 1,
                    start: new Date().getTime(),
                    end: new Date().getTime(),
                    ids: '', // skus:string
                    timeRange: ''// 时间范围：string
                }],
                list: [{}, {}, {}, {}], // 商品列表
                localtime: new Date().getTime(), // 时间戳实例
                lang: GESHOP_LANGUAGES, // 语言列表
                defaultGoodImg: 'https://geshopimg.logsss.com/uploads/nxQshzC1wXTYy8BmHD6GE39vLWjciKaR.png',
                curTabox: 0 // 当前的选中tab
            },
            defaultText: {
                goods_title: 'Spaghetti Strap Criss Cross Bikini'
            },
            localtime: new Date().getTime(), // 时间戳实例
            list: [{}, {}, {}, {}], // 商品列表
            $mySwiper: null, // swiper ref
            swiperOption: {
                // swiper 配置项
                noSwiping: true,
                init: true,
                initialSlide: 0,
                slidesPerView: 4,
                slideToClickedSlide: true,
                observer: true, // 修改swiper自己或子元素时，自动初始化swiper
                observeParents: true, // 修改swiper的父元素时，自动初始化swiper
                navigation: {
                    nextEl: `#U000207_${this.pid} .geshop-U000207-template1_v1-body .swiper-button-next`,
                    prevEl: `#U000207_${this.pid} .geshop-U000207-template1_v1-body .swiper-button-prev`
                }

            }
        };
    },
    computed: {
        /*  是否展示已关闭的    */
        show_ended () {
            return this.$root.data.tab_endShowActive != null ? this.$root.data.tab_endShowActive : true;
        },
        /* 1 only xx% left 2 only xx left */
        show_limitType () {
            return this.$root.data.goodsLimitsTextType != null ? parseInt(this.$root.data.goodsLimitsTextType) : 1;
        },
        /* 1 是 2 否 */
        show_buynow () {
            return this.$root.data.buy_btn_active != null ? parseInt(this.$root.data.buy_btn_active) : 1;
        },
        /* m 一行多列,一行一列 */
        class_body () {
            if (!!this.$root.data.box_multi_column === false || this.$root.data.box_multi_column == 1) {
                return 'is-multi';
            } else {
                return 'is-single';
            }
            ;
        }
    },
    mounted () {
        this.resizeChange();
        // 追加函数到队列
        this.$store.commit('dresslily/update_onresize_marque', this.resizeChange);
        // 去处loading
        this.$store.dispatch('global/loaded', this);
        this.initMounted();
        this.$nextTick(() => {
            if (this.$refs.mySwiper) {
                // this.$refs.mySwiper.swiper.init();
                this.$refs.mySwiper.swiper.slideTo(this.commonData.current.index);
                this.$refs.mySwiper.swiper.navigation && this.$refs.mySwiper.swiper.navigation.update();
            }
            /* if (this.commonData.current.index > 0) {
                $(this.$refs.myTabWrap).find('.swiper-button-prev').addClass('next-first-time');
            }
            if (this.commonData.current.index === this.commonData.tabs.length - 1) {
                $(this.$refs.myTabWrap).find('.swiper-button-next').removeClass('next-first-time');
            }

            $(this.$refs.myTabWrap).find('.next-first-time').on('click', function () {
                $(this).removeClass('next-first-time');
            }); */
        });
    },
    methods: {
        resizeChange () {
            // this.setPlatform();
            const newValue = document.body.clientWidth || document.documentElement.clientWidth;
            let boxWrapMedia = '';
            let view_platform = '';
            if (newValue >= 1025) {
                // pc
                view_platform = 'pc';
                boxWrapMedia = 'geshop_dl_pc';
            } else if (newValue <= 1024 && newValue >= 768) {
                // pad
                view_platform = 'pad';
                boxWrapMedia = 'geshop_dl_pad';
            } else if (newValue <= 767) {
                // m
                view_platform = 'm';
                boxWrapMedia = 'geshop_dl_wap geshop_dl_m';
            }
            this.commonData.view_platform = view_platform;
            this.commonData.boxWrapMedia = boxWrapMedia;
        },
        handleChangeTab (index) {
            this.commonData.current = this.commonData.tabs[index];
            this.curTabox = index;
            this.get_data();
        },
        initMounted () {
            const nowTime = new Date().getTime();
            // 初始化 TABS 数据，安时间排序
            if (this.$root.data.goodsSKUTab) {
                this.commonData.tabs = [];
                this.$root.data.goodsSKUTab.sort((a, b) => a.dataStartTime - b.dataStartTime);
                this.$root.data.goodsSKUTab.map((item, index) => {
                    // 是否装修页，是否时间正确，是否展示已过期。只要1个对了都添加到数据展示 （2019/5/25装修页启用）
                    if (this.show_ended == true || nowTime < item.dataEndTime) {
                        this.commonData.tabs.push({
                            index: index,
                            start: item.dataStartTime,
                            end: item.dataEndTime,
                            ids: item.lists,
                            timeRange: item.timeRange
                        });
                    }
                    ;
                });
                // 取时间对应上的第一个
                let tab_soonIndexArr = [];
                this.commonData.tabs.map((item, index) => {
                    // 存在已开始活动
                    if (nowTime >= parseInt(item.start) && this.localtime <= parseInt(item.end)) {
                        if (!!!this.commonData.current.index && this.commonData.current.index !== 0) {
                            this.commonData.current = item;
                        }
                    }
                    // comming soon
                    if (parseInt(item.start) > nowTime) {
                        tab_soonIndexArr.push(item);
                    }
                    item.index = index;
                    return item;
                });
                // 没有则默认取第一个
                if (!!!this.commonData.current.index && this.commonData.current.index !== 0) this.commonData.current = tab_soonIndexArr[0] || this.commonData.tabs[0] || {};

                // 获取数据
                this.get_data();
                // 开始倒计时
                setInterval(() => {
                    this.localtime = new Date().getTime();
                }, 1000);
            }
        },
        join_left (val) {
            return val > 9 ? val : `0${val}`;
        },
        /**
         * 获取pc活动的开始时间，格式  15:00 05/15
         * @param timestamp 时间戳
         */
        convert_time_date (timestamp) {
            if (!timestamp) {
                return `<span class="font-weight">00:00</span> 00/00`;
            }
            const time = new Date(parseInt(timestamp));
            const hour = this.join_left(time.getHours());
            const minute = this.join_left(time.getMinutes());
            const month = this.join_left(time.getMonth() + 1);
            const day = this.join_left(time.getDate());
            // return hour + ':' + minute;
            return `<span class="font-weight">${hour}:${minute}</span> ${month}/${day}`;
        },
        /**
         * 获取当前活动状态
         * [coming_soon,on_going,state_ended]
         * @param starttime
         * @param endtime
         * @returns {*}
         */
        getTimeStatus (starttime, endtime) {
            const timeStatus = [this.$lang('coming_soon'), this.$lang('on_going'), this.$lang('already_ended')];
            const startTime = parseInt(starttime);
            const endTime = parseInt(endtime);
            const localtime = new Date().getTime();
            let status = 0;
            if (localtime < startTime) {
                status = 0;
            }
            if (localtime >= startTime && localtime <= endTime) {
                status = 1;
            }
            if (localtime > endTime) {
                status = 2;
            }
            return timeStatus[status];
        },
        /**
         * m 端获取活动的开始时间，格式  15:00
         * @param timestamp 时间戳
         */
        convert_time_label (timestamp) {
            if (!timestamp) {
                return '00:00';
            }
            const join_left = (val) => {
                return val > 9 ? val : `0${val}`;
            };
            const time = new Date(parseInt(timestamp));
            const hour = join_left(time.getHours());
            const minute = join_left(time.getMinutes());
            return hour + ':' + minute;
        },
        /**
         * m 端获取活动的状态文案
         * @param starttime
         * @param endtime
         */
        convert_time_status (starttime, endtime) {
            if (!starttime || !endtime) {
                return this.$lang('state_ended');
            }
            if (this.localtime < parseInt(starttime)) {
                const join_left = (val) => {
                    return val > 9 ? val : `0${val}`;
                };
                const date = new Date(parseInt(starttime));
                const month = join_left(date.getMonth() + 1);
                const day = join_left(date.getDate());
                return month + '/' + day;
            }
            if (this.localtime >= parseInt(starttime) && this.localtime <= parseInt(endtime)) {
                return this.$lang('on_going');
            }
            if (this.localtime > parseInt(endtime)) {
                return this.$lang('state_ended');
            }
        },
        /*  获取数据    */
        async get_data () {
            if (this.commonData.current && this.commonData.current.ids) {
                const params = {
                    goodsSn: this.commonData.current.ids,
                    v: '2'
                };
                const res = await this.$jsonp(GESHOP_INTERFACE.timeseckilldetail.url, params);
                // 数据处理，增加自定义字段
                let arrayList = [];
                let item;
                for (let i = 0; i < res.data.goodsInfo.length; i++) {
                    item = res.data.goodsInfo[i];
                    // 去除空白数据
                    if (JSON.stringify(item) !== '{}') {
                        const activity_number_left = parseInt(item.activity_number) - parseInt(item.activity_volume_number);
                        item['activity_number_left'] = item.goods_number > 0 && activity_number_left > 0 ? activity_number_left : 0;
                        item['activity_volume_percent'] = this.get_percent(Number(item.activity_volume_number), Number(item.activity_number));
                        arrayList.push(item);
                    }
                }
                this.list = arrayList;
                // 页面元素初始化
                this.$store.dispatch('global/async_goods_init', this);
            }
        },
        /**
         * 获取销售百分比
         * @param volumn 已售
         * @param total 库存
         */
        get_percent (volumn, total) {
            if (total <= 0 || volumn >= total || isNaN(volumn) || isNaN(total)) {
                return '100%';
            } else {
                return Math.ceil(parseInt((volumn / total) * 100)) + '%';
            }
            ;
        }
    }
};
</script>

<style lang="less" scoped>
    @import 'component.less';
</style>

<style lang="less">
    .geshop-U000207-template1_v1-body {
        .font-weight {
            font-family: LatoBold;
        }

        .item_button_buy .logsss_event {
            font-size: 0;
            display: block;
        }

        .has_navigation--no {
            &.swiper-container {
                width: 100%;

                .swiper-wrapper {
                    -webkit-box-pack: center;
                    -ms-flex-pack: center;
                    justify-content: center;
                }
            }
        }

        .geshop-dl-soldout > p {
            overflow: hidden;
        }

        &.geshop_dl_pad {
            .geshop-market-price {
                display: block;
            }
        }

        &.geshop_dl_m {
            .swiper-container {
                width: 100%;
            }

            .geshop-shop-price {
                font-size: 18px;
            }

            .geshop-market-price {
                font-size: 13px;
            }

            .geshop-progress-bar {
                margin-top: 8px;
                margin-bottom: 2px;
                height: 4px;
                line-height: 4px;

                span {
                    height: 100%;
                    line-height: 1;
                }
            }

            .geshop-progress-text {
                font-size: 12px;
            }

            .geshop-button-buynow {
                margin-top: 10px;
                height: 30px;
                line-height: 30px;
            }

            .geshop-dl-soldout > p {
                width: 80px;
                height: 77.5px;
                line-height: 77.5px;
                margin-left: -40px;
                margin-top: -38.75px;
                font-size: 14px;
            }

            .is-multi {
                .geshop-market-price {
                    display: block;
                    line-height: 18px;
                }
            }

            .is-single {
                .geshop-progress-bar {
                    margin-top: 12px;
                }

                .geshop-button-buynow {
                    margin-top: 12px;
                }
            }
        }
    }
</style>
