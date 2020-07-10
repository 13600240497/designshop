<template>
    <div class="geshop-u000141-default-v2-body" :style="style_body" v-if="get_status || $root.is_edit_env">
        <div class="geshop-m-timer">
            <div class="geshop-m-timer-title">
                {{ $root.languages.title }}
            </div>
            <div class="geshop-m-timer-right">
                <span class="timer-spiner">{{ spiners[0] }}</span>
                :
                <span class="timer-spiner">{{ spiners[1] }}</span>
                :
                <span class="timer-spiner">{{ spiners[2] }}</span>
                :
                <span class="timer-spiner">{{ spiners[3] }}</span>
            </div>
        </div>
        <!--  -->
        <ul class="u000141-goods-list">
            <li v-for="(item, index) in list" :key="item.goods_sn" :style="style_item">
                <div class="item-image">
                    <geshop-analytics-href
                            :href="item.url_title"
                            :sku="item.goods_sn"
                            :goods_id="item.goods_id"
                            :cate="item.cateid"
                            :warehouse="item.warehousecode"
                            :index="index">
                        <geshop-image-goods :src="item.goods_img" :lazyload="false"></geshop-image-goods>
                        <geshop-discount :value="typeof item.discount != 'undefined' ? item.discount : 50"></geshop-discount>

                    </geshop-analytics-href>
                    <geshop-soldout :visible="(item.activity_number - item.activity_volume_number) <= 0 || item.goods_number == 0"></geshop-soldout>
                </div>
                <div class="item-info">
                    <div class="item-price">
                        <geshop-shop-price :value="item.shop_price"></geshop-shop-price>
                    </div>
                    <div class="item-claimed" :style="{'color': $root.data.goodsLimitTextC || '#333' }">
                        <span v-if="$root.sitecode.indexOf('zf') >= 0">{{ (lang.left || '').replace('XX', item.activity_number_left ) }}</span>
                        <span v-else>{{ (lang.left || '').replace('XX', item.activity_number > 0 ? Math.floor(((item.activity_number - item.activity_volume_number)/item.activity_number).toFixed(2) * 100) + '%' : '0%') }}</span>
                    </div>
                    <div class="item-progress" v-if="$root.sitecode.indexOf('zf') >= 0">
                        <geshop-progress-bar :color1="$root.data.progress_left_color" :color2="$root.data.progress_total_color" :current="item.activity_number - item.activity_number_left" :total="item.activity_number"></geshop-progress-bar>
                    </div>
                    <div class="item-progress" v-else>
                        <geshop-progress-bar :color1="$root.data.progress_left_color" :color2="$root.data.progress_total_color" :current="item.activity_number_left" :total="item.activity_number"></geshop-progress-bar>
                    </div>

                    <div class="item-button" v-if="$root.data.buynow_show >= 1">
                        <geshop-analytics-href
                                :href="item.url_title"
                                :sku="item.goods_sn"
                                :goods_id="item.goods_id"
                                :cate="item.cateid"
                                :warehouse="item.warehousecode"
                                :index="index">
                            <geshop-buynow></geshop-buynow>
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
            starttime: null,
            endtime: null,
            list: [{}, {}, {}, {}],
            localtime: new Date().getTime(),
            lang: GESHOP_LANGUAGES,
            spiners: ['00', '00', '00', '00']
        };
    },
    computed: {
        style_body () {
            return {
                'margin-bottom': this.$px2rem(this.$root.data.box_margin_bottom || 40),
                'background-color': this.$root.data.box_bg_color || '#F2F2F2'
            };
        },
        style_item () {
            return {
                'border-radius': this.$px2rem(this.$root.data.goods_item_radius || 12)
            };
        },
        // 折扣方式, 0/1
        clamid_type () {
            return this.$root.data.goodsLimitsTextType != null ? parseInt(this.$root.data.goodsLimitsTextType) : 1;
        },
        // 是否展示已关闭的
        show_ended () {
            return this.$root.data.tab_endShowActive != null ? this.$root.data.tab_endShowActive : true;
        },
        // 获取时间状态,
        get_status () {
            return (this.localtime >= this.starttime && this.localtime <= this.endtime);
        }
    },

    methods: {
        // 获取数据
        async get_data () {
            const params = {
                goodsSn: this.$root.data.goodsSKU,
                v: '2'
            };
            const res = await this.$jsonp(GESHOP_INTERFACE.timeseckilldetail.url, params);
            const res_goods_info = res.data.goodsInfo.filter(x => x.hasOwnProperty('goods_sn'));
            // 数据处理，增加自定义字段
            if (GESHOP_SITECODE.indexOf('zf') >= 0) {
                this.list = [...res_goods_info];
            } else {
                this.list = res_goods_info.map(item => {
                    item['activity_number_left'] = (parseInt(item.activity_number) - parseInt(item.activity_volume_number)) || 0;
                    return item;
                }) || [];
            }

            // 页面元素初始化
            this.$store.dispatch('global/async_goods_init', this);
        },
        // 获取 second 秒数
        get_second (timestamp) {
            if (timestamp - this.localtime <= 0) {
                return 0;
            } else {
                return parseInt(((timestamp - this.localtime) / 1000));
            };
        },

        // return array
        second_to_date (s) {
            let t = ['00', '00', '00', '00'];
            if (s > -1) {
                t = [];
                const day = Math.floor(s / 3600 / 24);
                const hour = Math.floor(s / 3600) % 24;
                const min = Math.floor(s / 60) % 60;
                const sec = s % 60;
                if (day < 10) {
                    t.push('0' + day);
                } else {
                    t.push(day);
                }
                if (hour < 10) {
                    t.push('0' + hour);
                } else {
                    t.push(hour);
                }
                if (min < 10) {
                    t.push('0' + min);
                } else {
                    t.push(min);
                }
                if (sec < 10) {
                    t.push('0' + sec);
                } else {
                    t.push(sec);
                }
            };
            return t;
        }
    },

    mounted () {
        // 初始化时间
        if (this.$root.data.dataRange) {
            const starttime = this.$root.data.dataRange.split(' - ')[0].replace(/-/g, '/');
            const endtime = this.$root.data.dataRange.split(' - ')[1].replace(/-/g, '/');
            this.starttime = new Date(starttime).getTime();
            this.endtime = new Date(endtime).getTime();
        }
        // 初始化 TABS 数据，安时间排序
        if (this.$root.data.goodsSKU) {
            this.get_data();
        };

        setInterval(() => {
            this.localtime = new Date().getTime();
            if (this.get_status) {
                const second = this.get_second(this.endtime);
                const str = this.second_to_date(second);
                this.spiners = str;
            };
        }, 1000);
    }
};
</script>

<style lang="less" scoped>

    .geshop-u000141-default-v2-body {
        overflow: hidden;
        width: 100%;
    }

    .u000141-goods-list {
        padding-left: 24 / 75rem;
        padding-bottom: 24 / 75rem;
        margin-right: 24 / 75rem;
        display: flex;
        flex-wrap: nowrap;
        overflow-y: hidden;
        -webkit-overflow-scrolling: scroll;
        &::-webkit-scrollbar{
            width: 0px;
            height: 0px;
        }

        .item-claimed {
            text-transform: capitalize;
        }
        // 公共的样式
        li {
            position: relative;
            flex-shrink: 0;
            background: #fff;
            width: 282 / 75rem;
            margin-right: 18 / 75rem;
            padding-bottom: 24 / 75rem;
            border-radius: 12 / 75rem;
            overflow: hidden;
        }

        .item-image {
            display: block;
            position: relative;
            height: 376 / 75rem;
            margin-bottom: 18 / 75rem;
        }

        .item-info {
            padding: 0 24 / 75rem;
        }

        .item-title {
            margin-bottom: 16 / 75rem;
            height: 30 / 75rem;
            line-height: 30 / 75rem;
            overflow: hidden;
        }

        .item-price {
            // margin-bottom: 12 / 75rem;
        }

        .item-button {
            margin-top: 24 / 75rem;
        }

        .item-progress {
            margin-top: 4 / 75rem;
        }
    }

    // 倒计时的
    .geshop-m-timer {
        padding: 0 24 / 75rem;
        height: 96 / 75rem;
        line-height: 96 / 75rem;
        color: #333;
        .geshop-m-timer-title {
            float: left;
            font-size: 36 / 75rem;
            margin-right: 20 / 75rem;
        }
        .geshop-m-timer-right {
            span.timer-spiner {
                display: inline-block;
                width: 36 / 75rem;
                height: 36 / 75rem;
                line-height: 36 / 75rem;
                background: #333;
                color: #fff;
                text-align: center;
                font-size: 24 / 75rem;
                border-radius: 6 / 75rem;
            }
        }
    }

</style>
