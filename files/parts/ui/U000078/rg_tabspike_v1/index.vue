<template>
    <div class="geshop-u000078-rg_tabspike_v1"
         v-if="tabs.length > 0 || $root.is_edit_env == true">
        <div class="u000078-swiper" v-if="tab_timearea_Show">
            <div class="swiper-wrapper" :class="tabs && tabs.length > 4 ? 'has_navigation' : 'has_navigation--no'">
                <swiper :options="swiperOption" class="gs-tab-label"
                        ref="mySwiper">
                    <swiper-slide
                            v-for="(item, index) in ( (tabs && tabs.length > 0 ) ? tabs : 3 )"
                            :class="['gs-tab-list', {on : current.index == item.index || tabs.length == 1 },{'swiper-no-swiping' : tabs.length <=4}]"
                            :style="{width: tabs && tabs.length > 4 ? '280px' : 100/(tabs.length || 3) + '%'}"
                            :key="index"
                    >
                        <div class="tab-desc" @click.prevent="handleChnageTab(index)" >
                            <div class="item-time">{{ convert_time_label(item.start) }} {{convert_time_day(item.start, item.end)}}</div>
                            <!-- 倒计时时间 -->
                            <div v-if="item.start <= localtime && item.end >= localtime" class="timesLabel">
                                {{$lang('down_ends')}}<geshop-timer style="margin-left: 6px"  :start="item.start" :end="item.end" :format="'hms'"></geshop-timer>
                            </div>
                            <div v-else class="item-date">{{ convert_time_status(item.start, item.end) }}</div>
                        </div>
                    </swiper-slide>
                </swiper>
                <div class="btn-page">
                    <div class="swiper-button-prev" slot="button-prev"></div>
                    <div class="swiper-button-next" slot="button-next"></div>
                </div>

            </div>
        </div>
        <!-- 商品列表  -->
        <ul class="u000078-goods-list">
            <li v-for="(item, index) in list" :key="item.goods_sn" class="goods-list-item">
                <div class="item-image" :sku="item.goods_sn">
                    <!-- 商品带埋点链接 -->
                    <geshop-analytics-href
                        :href="item.url_title"
                        :sku="item.goods_sn"
                        :goods_id="item.goods_id"
                        :cate="item.cateid"
                        :warehouse="item.warehousecode"
                        :index="index">
                        <!-- 商品图片 -->
                        <geshop-image-goods :src="item.goods_img || defaultGoodImg"
                                            :lazyload="false"></geshop-image-goods>
                        <!-- 商品折扣 -->
                        <geshop-discount :value="item.discount" :visible="item.discount != 0"></geshop-discount>
                        <!-- 商品售空 -->
                        <geshop-soldout
                            :visible="item.activity_number - item.activity_volume_number <= 0 || item.goods_number - 0 <= 0"></geshop-soldout>
                    </geshop-analytics-href>
                </div>
                <div class="item-info">
                    <div class="item-title">
                        {{ item.goods_title || 'ZAFUL Asymmetric Stripe …' }}
                    </div>
                    <div class="item-price">
                        <!-- 商品价 -->
                        <geshop-shop-price :value="item.shop_price"></geshop-shop-price>
                        <!-- 市场价 -->
                        <geshop-market-price :value="item.market_price"></geshop-market-price>
                    </div>

                    <div class="item-progress">
                        <!--库存进度-->
                        <geshop-progress-bar
                            :color1="$root.data.progress_left_color"
                            :color2="$root.data.progress_total_color"
                            :current="item.activity_number_left"
                            :total="item.activity_number"
                            :limitType="$root.data.goodsLimitsTextType">
                        </geshop-progress-bar>
                    </div>
                    <div class="item-claimed" :style="{'color': $root.data.goodsLimitsTextColor || '#333' }">
                        <!-- 库存显示方式 1 百分比-->
                        {{ $lang('only_left').replace('xx', item.activity_number > 0 ? Math.floor((item.activity_number
                        - item.activity_volume_number )/ item.activity_number * 100) : 0) }}
                    </div>
                    <div class="item-button">
                        <geshop-analytics-href
                            :href="item.url_title"
                            :sku="item.goods_sn"
                            :goods_id="item.goods_id"
                            :cate="item.cateid"
                            :warehouse="item.warehousecode"
                            :index="index">
                            <!-- 购买按钮 -->
                            <div class="geshop-button-buynow site-font-bold"> {{ $lang('btn_buy_now') }}</div>
                        </geshop-analytics-href>
                    </div>
                </div>
            </li>
        </ul>
    </div>
</template>

<script>
import 'swiper/dist/css/swiper.css';
import { swiper, swiperSlide } from 'vue-awesome-swiper';
export default {
    props: ['data', 'pid'],
    data () {
        return {
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
            swiperOption: {
                // swiper 配置项
                noSwiping: true,
                init: true,
                initialSlide: 0,
                slidesPerView: 'auto',
                slideToClickedSlide: true,
                observer: true, // 修改swiper自己或子元素时，自动初始化swiper
                observeParents: true, // 修改swiper的父元素时，自动初始化swiper
                navigation: {
                    nextEl: `#U000078_${this.pid} .geshop-u000078-rg_tabspike_v1 .swiper-button-next`,
                    prevEl: `#U000078_${this.pid} .geshop-u000078-rg_tabspike_v1 .swiper-button-prev`
                }

            }
        };
    },
    components: {
        swiper,
        swiperSlide
    },
    computed: {
        /*  折扣方式, 0/1   */
        clamid_type () {
            return this.$root.data.goodsLimitsTextType != null ? parseInt(this.$root.data.goodsLimitsTextType) : 1;
        },
        /*  是否展示已关闭的    */
        show_ended () {
            return this.$root.data.tab_endShowActive != null ? this.$root.data.tab_endShowActive : true;
        },
        /* 是否显示秒杀时间段 */
        tab_timearea_Show () {
            return this.$root.data.tab_timearea_Show != null ? parseInt(this.$root.data.tab_timearea_Show) : true;
        }
    },
    methods: {
        /**
         * 获取销售百分比
         * @param volumn 已售
         * @param total 库存
         */
        get_percent (volumn, total) {
            if (total <= 0 || volumn >= total || isNaN(volumn) || isNaN(total)) {
                return '100%';
            } else {
                return parseInt((volumn / total) * 100) + '%';
            }
            ;
        },

        /*  获取数据    */
        async get_data () {
            if (this.current && this.current.ids) {
                const params = {
                    goodsSn: this.current.ids,
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
                        item['activity_number_left'] = Number(item.activity_number) - (parseInt(item.activity_volume_number));
                        // item['activity_volume_percent'] = this.get_percent(Number(item.activity_volume_number), Number(item.activity_number));
                        arrayList.push(item);
                    }
                }
                this.list = arrayList;
                // 页面元素初始化
                this.$store.dispatch('global/async_goods_init', this);
            }
        },
        /**
         * 获取活动的开始时间，格式  15:00
         * @param timestamp 时间戳
         */
        convert_time_label (timestamp) {
            const join_left = (val) => {
                return val > 9 ? val : `0${val}`;
            };
            const time = new Date(parseInt(timestamp));
            const hour = join_left(time.getHours());
            const minute = join_left(time.getMinutes());
            return hour + ':' + minute;
        },
        /**
         * 获取活动的日期
         * @param starttime
         * @param endtime
         */
        convert_time_day (starttime, endtime) {
            const join_left = (val) => {
                return val > 9 ? val : `0${val}`;
            };
            const date = new Date(parseInt(starttime));
            const month = join_left(date.getMonth() + 1);
            const day = join_left(date.getDate());
            return month + '/' + day;
        },
        /**
         * 获取活动的状态文案
         * @param starttime
         * @param endtime
         */
        convert_time_status (starttime, endtime) {
            if (this.localtime >= parseInt(starttime) && this.localtime <= parseInt(endtime)) {
                return this.$root.languages.Ongoing || 'Ongoing';
            }
            if (this.localtime > parseInt(endtime)) {
                return this.$root.languages.Ended || 'Ended';
            }
            if (this.localtime < parseInt(starttime)) {
                return this.$lang('coming_soon') || 'Coming Soon';
            }
        },
        /**
         * 点击切换
         * @param index 下标
         */
        handleChnageTab (index) {
            this.current = this.tabs[index];
            this.get_data();
        },
        initMounted () {
            const nowTime = new Date().getTime();
            // 初始化 TABS 数据，安时间排序
            if (this.$root.data.goodsSKUTab) {
                this.tabs = [];
                this.$root.data.goodsSKUTab.sort((a, b) => a.dataStartTime - b.dataStartTime);
                this.$root.data.goodsSKUTab.map((item, index) => {
                    // 是否装修页，是否时间正确，是否展示已过期。只要1个对了都添加到数据展示 （2019/5/25装修页启用）
                    if (this.show_ended == true || nowTime < item.dataEndTime) {
                        this.tabs.push({
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
                this.tabs.map(item => {
                    if (nowTime >= parseInt(item.start) && this.localtime <= parseInt(item.end)) {
                        if (!!!this.current.index) {
                            this.current = item;
                        }
                    }
                });
                // 没有则默认取第一个
                if (!!!this.current.index) this.current = this.tabs[0] || {};

                // 获取数据
                this.get_data();
            }
            // 开始倒计时
            setInterval(() => {
                // this.localtime = new Date().getTime();
            }, 1000);
        }
    },
    mounted () {
        this.$store.commit('dresslily/update_onresize_marque', this.resizeChange);
        // 去处loading
        this.$store.dispatch('global/loaded', this);
        this.initMounted();
        this.$nextTick(() => {
            if (this.$refs.mySwiper) {
                // this.$refs.mySwiper.swiper.init();
                this.$refs.mySwiper.swiper.slideTo(this.current.index);
                this.$refs.mySwiper.swiper.navigation && this.$refs.mySwiper.swiper.navigation.update();
            }
        });
    }
};
</script>

<style lang="less" scoped>
    @height: 60px;

    .geshop-u000078-rg_tabspike_v1 {
        width: 1200px;
        margin: 0 auto;

        .u000078-goods-list {
            width: 100%;
            -webkit-box-sizing: border-box;
            -moz-box-sizing: border-box;
            box-sizing: border-box;
            margin: 0 auto;
            padding: 16px 8px;
            font-size: 0;
            overflow: hidden;
            .goods-list-item {
                display: inline-block;
                vertical-align: top;
                font-size: 14px;
                width:280px;
                margin: 8px;
                position: relative;
                -webkit-box-sizing: border-box;
                -moz-box-sizing: border-box;
                box-sizing: border-box;
                background-color: #ffffff;
                overflow: hidden;

                .item-image {
                    width: 278px;
                    height: 371px;
                    position: relative;
                    overflow: hidden;
                }

                .item-info {
                    -webkit-box-sizing: border-box;
                    -moz-box-sizing: border-box;
                    box-sizing: border-box;
                    padding: 0 12px;

                    .item-title {
                        text-overflow: ellipsis;
                        overflow: hidden;
                        white-space: nowrap;
                        margin-top: 12px;
                        margin-bottom: 4px;
                    }

                    .geshop-shop-price {
                        margin-right: 4px;
                    }

                    .geshop-market-price {
                        vertical-align: middle;
                    }
                    .item-claimed {
                        margin-bottom: 12px;
                    }

                    .geshop-button-buynow {
                        width: 254px;
                        height: 34px;
                        line-height: 34px;
                        -webkit-box-sizing: border-box;
                        -moz-box-sizing: border-box;
                        box-sizing: border-box;
                        text-align: center;
                        margin-top: 12px;
                        margin-bottom: 24px;
                        background-color: #ffffff;
                    }

                }
            }
        }

        .u000078-swiper {
            height: @height;
            background: #333;
            width: 100%;
            overflow: hidden;

            .swiper-wrapper {
                height: 100%;
                display: block;
                overflow-y: hidden;
            }

            .gs-tab-label {
                height: 100%;
            }

            .gs-tab-list {
                width: 280px;
                padding: 9px 0;
                text-align: center;
                font-size: 0px;
                height: 100%;
                box-sizing: border-box;
                display: inline-block;
                cursor: pointer;
                .tab-desc {
                    height: 100%;
                    .item-time {
                        font-size: 20px;
                    }

                    .item-date {
                        font-size: 14px;
                        display: inline-block;
                        word-break: keep-all;
                        white-space: nowrap;
                    }
                    .timesLabel {
                        font-size: 14px;
                    }
                }
            }

        }

        .has_navigation--no {
            .btn-page {
                display: none;
            }
        }

        .has_navigation {
            .gs-tab-label {
                width: 1120px;
            }
        }

        .swiper-button-prev, .swiper-button-next {
            background-image: none;
            height: 60px;
            width: 40px;
            top: 0;
            margin-top: 0;
            background-color: #222222;
            opacity: .7;

            &:hover {
                opacity: 1;
                //background-color: #000000;
            }

            &.swiper-button-disabled {
                background-color: #222222;
                opacity: .1;
            }

            &.next-first-time {
                cursor: pointer;
                pointer-events: initial;
                background-color: #000000;
            }

            &:hover {
                opacity: .9;
                /*background-color: #D8D8D8;*/
            }

            &:focus {
                outline: none
            }

            &:after {
                content: "";
                border-top: 2px solid #fff;
                border-right: 2px solid #fff;
                width: 14px;
                height: 14px;
                line-height: 0;
                font-size: 0;
                position: absolute;
                left: 50%;
                top: 50%;
                margin-top: -7px;
            }
        }

        .swiper-button-prev {
            &:after {
                content: "";
                -webkit-transform: rotate(-135deg);
                transform: rotate(-135deg);
                margin-left: -3px;
            }

            left: 0;
        }

        .swiper-button-next {
            &:after {
                content: "";
                margin-left: -12px;
                -webkit-transform: rotate(45deg);
                transform: rotate(45deg);
            }

            right: 0;
        }
    }

</style>
