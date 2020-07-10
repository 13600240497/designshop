<template>
    <div class="geshop-component-body geshop-U000207-template1_v1-body" :class="commonData.boxWrapMedia"
         v-if="commonData.tabs.length > 0 || $root.is_edit_env == true" ref="myTabWrap">
        <template v-if="commonData.view_platform === 'm'">
            <div></div>
        </template>
        <template v-else>
            <!--            <listWeb :commonData="commonData" :list="list"></listWeb>-->
            <div class="gs-goods-bd">
                <!-- 商品轮播列表-->
<!--                <div class="swiper-wrapper">-->
<!--                    <swiper :options="swiperOption" class="gs-tab-label"-->
<!--                            :class="commonData && commonData.tabs.length > 4 ? 'has_navigation' : 'has_navigation&#45;&#45;no'"-->
<!--                            ref="mySwiper">-->
<!--                        <swiper-slide-->
<!--                            v-for="(item, index) in ( (commonData && commonData.tabs.length > 0 ) ? commonData.tabs : 3 )"-->
<!--                            :class="['gs-tab-list', {on :commonData.curTabox == index}]"-->
<!--                            :key="index"-->
<!--                        >-->
<!--                            <div class="tab-desc"-->
<!--                                 @click.prevent="handleChangeTab(index)">-->
<!--                                <div class="item-time">{{ convert_time_date(item.start) }}</div>-->
<!--                                <div class="item-status">-->
<!--                                    <span class="item-status-text">{{ getTimeStatus(item.start,item.end) }}</span>-->
<!--                                    &lt;!&ndash; 倒计时时间 &ndash;&gt;-->
<!--                                    <geshop-timer :start="item.start" :end="item.end"-->
<!--                                                  :format="'hms'"></geshop-timer>-->
<!--                                </div>-->

<!--                            </div>-->
<!--                        </swiper-slide>-->
<!--                    </swiper>-->
<!--                </div>-->
                <div class="swiper-container gs-tab-label"
                     :class="commonData && commonData.tabs.length > 4 ? 'has_navigation' : 'has_navigation--no'"
                     ref="mySwiper">
                    <div class="swiper-wrapper">
                        <div class="swiper-slide"
                             v-for="(item, index) in ( (commonData && commonData.tabs.length > 0 ) ? commonData.tabs : 3 )"
                             :class="['gs-tab-list', {on :commonData.curTabox == index}]"
                             :key="index">
                            <div class="tab-desc"
                                 @click.prevent="handleChangeTab(index)">
                                <div class="item-time">{{ convert_time_date(item.start) }}</div>
                                <div class="item-status">
                                    <span class="item-status-text">{{ getTimeStatus(item.start,item.end) }}</span>
                                    <!-- 倒计时时间 -->
                                    <geshop-timer :start="item.start" :end="item.end"
                                                  :format="'hms'"></geshop-timer>
                                </div>

                            </div>
                        </div>
                    </div>
                </div>
                <div v-show="commonData.tabs && commonData.tabs.length > 4">
                    <div class="swiper-button-prev" slot="button-prev"></div>
                    <div class="swiper-button-next" slot="button-next"></div>
                </div>
            </div>
            <div class="gs-tab-content">
                <div class="gs-tab-item">
                    <ul class="clearfix">
                        <!--列表-->
                        <li class="list-item" v-for="(item, index) in list" :key="index">
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
                                    :visible="item.goods_number <= 0">
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
                                    <!-- 库存描述 -->
                                    <geshop-progress-text
                                        :current="item.activity_number_left"
                                        :total="item.activity_number"
                                        :type="show_limitType"
                                    ></geshop-progress-text>
                                </div>
                                <!--bottom_buy-->
                                <div class="item_button_buy" v-if="show_buynow === 1">
                                    <geshop-analytics-href
                                        :href="item.url_title"
                                        :sku="item.goods_sn"
                                        :cate="item.cateid"
                                        :warehouse="item.warehousecode"
                                        :goods_id="item.goods_id">
                                        <geshop-buynow></geshop-buynow>
                                    </geshop-analytics-href>
                                </div>
                            </div>
                        </li>
                    </ul>
                </div>
            </div>
        </template>
    </div>
</template>

<script>
import 'swiper/dist/css/swiper.css';
import { swiper, swiperSlide } from 'vue-awesome-swiper';

export default {
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
            swiper: null,
            $component: null, // 当前组件ref
            $mySwiper: null, // swiper ref
            swiperOption: {
                // swiper 配置项
                initialSlide :0,
                slidesPerView: 4,
                slideToClickedSlide: true,
                observer: true, // 修改swiper自己或子元素时，自动初始化swiper
                observeParents: true // 修改swiper的父元素时，自动初始化swiper
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
        }
    },
    mounted () {
        // 加载swiper
        loadCss(GESHOP_STATIC + '/resources/javascripts/library/swiper/swiper.4.5.min.css');
        $LAB.script(GESHOP_STATIC + '/resources/javascripts/library/swiper/swiper.4.5.min.js').wait(() => {
            this.$nextTick(() => {
                // 追加函数到队列
                this.$store.commit('dresslily/update_onresize_marque', this.resizeChange);
                this.$component = $(this.$refs.myTabWrap);
                // 去处loading
                this.$store.dispatch('global/loaded', this);
                this.mountedInit();
            });
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
            this.commonData.curTabox = index;
            this.get_data();
        },
        mountedInit () {
            this.resizeChange();
            // 追加函数到队列
            this.$store.commit('dresslily/update_onresize_marque', this.resizeChange);
            this.initSwiper();
            this.dataInit();
            this.get_data();
        },
        initSwiper () {
            const options = Object.assign({},this.swiperOption,{
                init: function (swiper) {
                    swiper.wrapper[0].style.transform = 'none';
                }
            })
            this.swiper = new Swiper(this.$component.find('.swiper-container'), options);
        },
        dataInit () {
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
                this.commonData.tabs.map(item => {
                    if (nowTime >= parseInt(item.start) && this.localtime <= parseInt(item.end)) {
                        if (!!!this.commonData.current.index) {
                            this.commonData.current = item;
                        }
                    }
                });
                // 没有则默认取第一个
                if (!!!this.commonData.current.index) this.commonData.current = this.commonData.tabs[0] || {};

                // 获取数据
                this.get_data();
                // 更改 dom scrollLeft 值
                // this.$nextTick(() => {
                //     const current_left = $(this.$el).find('.u000153-swiper li.is-active').position().left || 0;
                //     const current_width = $(this.$el).find('.u000153-swiper li.is-active').width() || 0;
                //     const container_width = $(this.$el).find('.u000153-swiper').width();
                //     if (current_left + current_width >= container_width) {
                //         $(this.$el).find('.u000153-swiper ul').scrollLeft(current_left);
                //     }
                // });
            }
            // 开始倒计时
            setInterval(() => {
                this.localtime = new Date().getTime();
            }, 1000);
        },
        join_left (val) {
            return val > 9 ? val : `0${val}`;
        },
        /**
         * 获取pc活动的开始时间，格式  15:00 05/15
         * @param timestamp 时间戳
         */
        convert_time_date (timestamp) {
            const time = new Date(parseInt(timestamp));
            const hour = this.join_left(time.getHours());
            const minute = this.join_left(time.getMinutes());
            const month = this.join_left(time.getMonth() + 1);
            const day = this.join_left(time.getDate());
            // return hour + ':' + minute;
            return `${month}/${day} ${hour}:${minute}`;
        },
        /**
         * 获取当前活动状态
         * [coming_soon,on_going,state_ended]
         * @param starttime
         * @param endtime
         * @returns {*}
         */
        getTimeStatus (starttime, endtime) {
            const timeStatus = [this.$lang('coming_soon'), this.$lang('on_going'), this.$lang('state_ended')];
            const startTime = parseInt(starttime);
            const endTime = parseInt(endtime);
            const localtime = this.localtime;
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
                        item['activity_number_left'] = (parseInt(item.activity_number) - parseInt(item.activity_volume_number)) || 0;
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
    }
</style>
