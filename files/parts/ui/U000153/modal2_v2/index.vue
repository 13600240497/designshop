<template>
    <div class="geshop-u000153-default-v2-body geshop-u000153-modal2-v2-body" :style="style_body"
         v-if="tabs.length > 0 || $root.is_edit_env == true">
        <div class="u000153-swiper">
            <ul>
                <li v-for="item in tabs" :key="item.index" :class="{ 'is-active': current.index == item.index}"
                    @click="handleChnageTab(item.index)">
                    <div class="item-time">{{ convert_time_label(item.start) }}</div>
                    <div class="item-date">{{ convert_time_status(item.start, item.end) }}</div>
                </li>
            </ul>
        </div>
        <!-- 倒计时时间 -->
        <geshop-timer :label="$root.data.timer_label" :start="current.start" :end="current.end"
                      :format="'hms'"></geshop-timer>
        <!-- 商品列表  -->
        <ul class="u000153-goods-list" :class="class_body">
            <li v-for="(item, index) in list" :key="item.goods_sn" :style="style_item">
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
                        <geshop-image-goods :src="item.goods_img || defaultGoodImg" />
                        <!-- 商品折扣 -->
                        <geshop-discount :value="item.discount" :visible="item.discount != 0"></geshop-discount>
                        <!-- 商品售空 -->
                        <geshop-soldout :visible="item.activity_number_left <= 0"></geshop-soldout>
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
                    <div class="item-claimed" :style="{'color': $root.data.goodsLimitsTextColor || '#333' }">
                        <!-- 库存显示方式 1 百分比-->
                        <template v-if="clamid_type == 1">
                            {{ item.activity_volume_percent }} {{ lang['claimed'] || '' }}
                        </template>
                        <template v-else>
                            {{ (lang.left || '').replace('XX', item.activity_number_left) }}
                        </template>
                    </div>
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
                            <geshop-buynow v-if="current.start > localtime" :value="$lang('btn_coming_soon')"></geshop-buynow>
                            <geshop-buynow v-else :value="$lang('btn_buy_now')"></geshop-buynow>
                        </geshop-analytics-href>
                    </div>
                </div>
            </li>
        </ul>
    </div>
</template>

<script>
export default {
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
            defaultGoodImg: 'https://geshopimg.logsss.com/uploads/nxQshzC1wXTYy8BmHD6GE39vLWjciKaR.png'
        };
    },
    computed: {
        /*  u000153-goods-list 样式   */
        class_body () {
            if (!!this.$root.data.box_multi_column === false || this.$root.data.box_multi_column == 1) {
                return 'is-multi';
            } else {
                return 'is-single';
            }
            ;
        },
        /*  geshop-u000153-modal2-v2-body 样式    */
        style_body () {
            return {
                'margin-bottom': this.$px2rem(this.$root.data.box_margin_bottom || 32),
                'background-color': this.$root.data.box_bg_color || '#F8F8F8'
            };
        },
        /*  商品样式    */
        style_item () {
            return {
                'border-radius': this.$px2rem(this.$root.data.goods_item_radius || 12)
            };
        },
        /*  折扣方式, 0/1   */
        clamid_type () {
            return this.$root.data.goodsLimitsTextType != null ? parseInt(this.$root.data.goodsLimitsTextType) : 1;
        },
        /*  是否展示已关闭的    */
        show_ended () {
            return this.$root.data.tab_endShowActive != null ? this.$root.data.tab_endShowActive : true;
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
                        item['activity_number_left'] = this.convert_activity_number_left(item.activity_number_left);
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
         * 获取活动的状态文案
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
                return this.$root.languages.Ongoing || 'Ongoing';
            }
            if (this.localtime > parseInt(endtime)) {
                return this.$root.languages.Ended || 'Ended';
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

        /**
         * 处理剩余多少件的数据
         * @date 2020-05-29
         * @author Cullen
         * @param {Number} val
         * @returns {Number}
         */
        convert_activity_number_left (val = 0) {
            const left = parseInt(val);
            if (isNaN(left)) return 0;
            return left >= 0 ? left : 0;
        }
    },
    mounted () {
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

            // 更改 dom scrollLeft 值
            this.$nextTick(() => {
                const current_left = $(this.$el).find('.u000153-swiper li.is-active').position().left || 0;
                const current_width = $(this.$el).find('.u000153-swiper li.is-active').width() || 0;
                const container_width = $(this.$el).find('.u000153-swiper').width();
                if (current_left + current_width >= container_width) {
                    $(this.$el).find('.u000153-swiper ul').scrollLeft(current_left);
                }
            });
        }
        // 开始倒计时
        setInterval(() => {
            this.localtime = new Date().getTime();
        }, 1000);
    }
};
</script>

<style lang="less" scoped>

    .u000153-swiper {
        height: 112 / 75rem;
        background: #333;
        width: 100%;
        overflow: hidden;

        ul {
            display: flex;
            flex-wrap: nowrap;
            overflow-y: hidden;
            -webkit-overflow-scrolling: scroll;
        }

        li {
            flex-shrink: 0;
            padding: 16 / 75rem 48 / 75rem;
            text-align: center;
            color: #999999;
            font-size: 0px;

            .item-time {
                height: 48 / 75rem;
                line-height: 48 / 75rem;
                font-size: 36 / 75rem;
                font-family: OpenSans-Semibold;
            }

            .item-date {
                font-size: 24 / 75rem;
                line-height: 32 / 75rem;
                display: inline-block;
                word-break: keep-all;
                white-space: nowrap;
            }
        }

        li.is-active {
            color: #fff;
        }
    }

    // 商品列表，区分单列，和 2 列
    .u000153-goods-list {
        padding-top: 24 / 75rem;
        padding-left: 24 / 75rem;
        padding-bottom: 6 / 75rem;

        // 公共的样式
        li {
            position: relative;
            flex-shrink: 0;
            background: #fff;
            line-height: 1;
        }

        .item-title {
            margin-bottom: 16 / 75rem;
            height: 30 / 75rem;
            line-height: 30 / 75rem;
            overflow: hidden;
            text-overflow: ellipsis;
            word-break: keep-all;
            white-space: nowrap;
        }

        .item-price {
            margin-bottom: 16 / 75rem;
        }

        .item-button {
            margin-top: 24 / 75rem;
        }

        .item-progress {
            margin-top: 4 / 75rem;
        }

        // 多列
        &.is-multi {
            display: flex;
            flex-wrap: wrap;

            li {
                width: 342 / 75rem;
                margin-right: 18 / 75rem;
                margin-bottom: 18 / 75rem;
                padding-bottom: 24 / 75rem;
                border-radius: 12 / 75rem;
                overflow: hidden;
            }

            .item-image {
                display: block;
                position: relative;
                height: 456 / 75rem;
                margin-bottom: 18 / 75rem;
            }

            .item-info {
                padding: 0 24 / 75rem;
            }

            .geshop-shop-price {
                margin-right: 16 / 75rem;
            }
        }

        // 单列
        &.is-single {
            overflow: hidden;
            display: block;

            li {
                display: flex;
                width: 702 / 75rem;
                margin-bottom: 16 / 75rem;
                overflow: hidden;
            }

            .item-image {
                position: relative;
                width: 270 / 75rem;
                height: 360 / 75rem;
                flex-shrink: 0;
            }

            .item-info {
                width: 430 / 75rem;
                padding: 30 / 75rem  30 / 75rem;
                box-sizing: border-box;
                position: relative;
            }

            .item-price {
                font-size: 0px;
                line-height: 0px;
                margin-bottom: 24 / 75rem;

                .geshop-shop-price {
                    display: block;
                    height: 48 / 75rem;
                    line-height: 48 / 75rem;
                }

                .geshop-market-price {
                    display: block;
                    height: 32 / 75rem;
                    line-height: 32 / 75rem;
                }
            }

            .item-button {
                position: absolute;
                left: 30 / 75rem;
                right: 30 / 75rem;
                bottom: 30 / 75rem;
                width: 60%;
            }

            .item-title {
                display: -webkit-box;
                -webkit-line-clamp: 2;
                -webkit-box-orient: vertical;
                height: 60 / 75rem;
                white-space: normal;
            }

            .item-progress {
                width: 85%;
            }

            .geshop-zaful-image-goods {
                font-size: 0;
                height: 100%;

                img {
                    height: 100%;
                }
            }
        }
    }
</style>

<style lang="less">
    .geshop-u000153-modal2-v2-body {
        .geshop-m-timer {
            font-family: OpenSans-Semibold;

            .geshop-m-timer-right {
                font-size: 24 /75rem;
            }
        }

        .item-info {
            .geshop-button-buynow {
                font-size: 28 / 75rem;
            }
        }

        .u000153-goods-list {
            .geshop-m-soldout-layer > span {
                font-family: OpenSans-Semibold, serif;
            }

            &.is-single {
                .geshop-components-discount {
                    left: 12 / 75rem;
                    right: initial;
                }
            }
        }

        .item-title {
            font-size: 24 / 75rem;
        }

        .item-claimed {
            font-size: 22 /75rem;
            text-transform: capitalize;
        }

        .geshop-market-price {
            font-size: 24 / 75rem;
        }

        .geshop-shop-price {
            font-family: OpenSans-Semibold;
        }

        .geshop-zaful-image-goods img {
            font-size: 0;
            height: 100%;
        }

        .geshop-zaful-discount {
            span {
                label {
                    font-weight: normal;
                }
            }
        }

        .geshop-m-soldout-layer {
            span {
                font-family: OpenSans-Semibold !important;
            }
        }
    }
    /*
        自定义代码 填充到组件 避免每次手动写入
    */
    .geshop-U000153-modal2_v2 .geshop-m-timer .geshop-m-timer-title,.geshop-U000153-modal2_v2 .geshop-m-timer .geshop-m-timer-right{
        width: auto !important;
    }
    .geshop-u000153-modal2-v2-body .geshop-m-timer{
        justify-content: space-between;
    }
</style>
