<template>
    <div class="geshop-u000233-model1" :class="boxWrapMedia" ref="goodsList">
        <div class="u000233-model1-goods-wrap" v-if="paltform == 'web'" >
            <div class="u000233-model1-list" v-if="combData.length > 0">
                <div class="main-box" >
                    <ul class="comb-lists">
                        <li v-for="(item, index) in combData" :key="item[0].goods_id" class="comb-goods-list">
                            <div class="u000233-model1-goods-img">
                                <a :href="item[0].url_title" target="_blank">
                                    <geshop-image-goods
                                        :src="item[0].goods_img"
                                        :sku="item[0].goods_sn"
                                        :index="index"
                                        :lazyload="false">
                                    </geshop-image-goods>
                                </a>
                                <geshop-discount :value="item[0].discount" :percent="item[0].discount"></geshop-discount>
                            </div>
                            <div class="goods-list-title">
                                {{ item[0].goods_title }}
                            </div>
                            <div class="con-wrap">
                                <div class="select-wrap cr-l">
                                <span class="js-size-text target-text gs-icon-down"
                                      v-if="item[0].size != null"
                                      :title="item[0].size"
                                      @click="searchData(index, 'size', $event)">{{ item[0].size }}
                                </span>
                                    <span class="target-text gs-icon-down off" v-else>{{ item[0].size }}
                                </span>

                                    <ul class="js-size-wrap sleWrap">
                                        <li class="attr-li" v-for="sitem in sizeArr"
                                            :key="sitem.goods_id"
                                            :title="sitem.size"
                                            @click="setSelectItem(index, 'size', sitem)"><span>{{ sitem.size }}</span>
                                        </li>
                                    </ul>
                                </div>
                                <div class="select-wrap cr-r">
                                <span class="js-color-text target-text gs-icon-down"
                                      :title="item[0].color"
                                      v-if="item[0].color != null"
                                      @click="searchData(index, 'color', $event)">{{ item[0].color }}
                                </span>
                                    <span class="target-text gs-icon-down off" v-else>{{ item[0].color }}</span>
                                    <ul class="js-color-wrap sleWrap">
                                        <li class="attr-li" v-for="citem in colorArr"
                                            :key="citem.goods_id"
                                            :title="citem.color"
                                            @click="setSelectItem(index, 'color', citem)"><span>{{ citem.color }}</span>
                                        </li>
                                    </ul>
                                </div>
                            </div>
                            <div class="goods-price">
                                <!-- <geshop-shop-price :value="item[0].shop_price"></geshop-shop-price>-->
                                <div class="geshop-shop-price">
                                    <span class="my-shop-price"
                                          :data-orgp="item[0].shop_price">
                                        ${{ item[0].shop_price }}
                                    </span>
                                </div>
                                <span class="join-comb" :data-id="item[0].goods_id" :data-curIndex="index"
                                      :data-list="JSON.stringify(item[0])">
                                    <i class="icon-ck"></i>
                                    <span class="icon-desc">{{ languages.join_comb[lang] || 'Join the combo' }}</span>
                                </span>
                            </div>
                        </li>
                    </ul>
                    <div class="buy-box">
                        <div class="price-group">
                            <span class="my-shop-price shop-total-price" data-orgp="0">$0</span>
                            <del class="my-shop-price market-total-price" data-orgp="0"><span>$0</span></del>
                        </div>
                        <span class="btn-car" @click="submitCat"><img
                                :src="data.box_bg_image || 'https://geshopimg.logsss.com/uploads/MPoZtbNVsTgAD4a08ykSJix6rmH3wncK.png'"
                                alt="icon-buy" width="24" height="24" class="icon-buycat"><span>{{ languages.get_car[lang] || 'GET THE SET'}}</span></span>
                    </div>
                </div>
            </div>
        </div>
        <div v-else>
            <p style="text-align: center;padding: 20px">APP暂时不支持搭配购组件的使用</p>
        </div>
    </div>
</template>

<script>
export default {
    props: ['data'],
    data () {
        return {
            paltform: GESHOP_PLATFORM,
            lang: GESHOP_LANG, // 语言
            $goodsList: null, // 当前refs 的JQ对象
            sizeArr: [], // 当前可用尺码
            colorArr: [], // 当前可用颜色
            combData: [], // 当前搭配列表数据
            comboInfo: {}, // 当前搭配列表活动 存放类型和优惠段
            goodsIds: [], // 当前选中的goodsID
            marketPrice: 0, // 当前选中的价格总和
            piceCount: 0, // 当前优惠段件数X
            priceArr: [], // 存放选中的价格
            boxWrapMedia: 'geshop_dl_pc', // 屏幕大小的类名
            languages: {
                join_comb: {
                    'en': 'Join the combo',
                    'fr': 'Faites votre combo'
                },
                get_car: {
                    'en': 'GET THE SET',
                    'fr': 'JE FONCE'
                }

            }
        };
    },
    computed: {},
    created () {
        this.$nextTick(() => {
            this.$goodsList = $(this.$refs.goodsList);
            this.bindEvent();
            this.onChange();
        });
    },
    mounted () {
        this.getData();
        // 追加函数到队列
        this.$store.commit('dresslily/update_onresize_marque', this.onChange);
        // $(`#U000233-model1-placeholder-${this.$root.pageInstanceId}`).hide();
    },
    updated () {
    },
    methods: {
        bindEvent () {
            const that = this;
            // 选中事件
            this.$goodsList.find('.main-box').on('click', '.join-comb', function (e) {
                $(this).toggleClass('on');
                that.addDiscount();
            });
            // 点击其他区域 收起下拉
            $('body,html').on('click', function (e) {
                if (!$(e.target).hasClass('target-text')) {
                    $('.sleWrap').removeClass('on').css('height', '0');
                    that.$goodsList.find('.target-text').removeClass('on');
                }
            });
        },
        // 底部购物属性和价格计算
        addDiscount () {
            const $box = this.$goodsList.find('.buy-box');
            const $marketBox = $box.find('.market-total-price');
            const $shoptBox = $box.find('.shop-total-price');
            let shopPrice = 0;
            // 拆分 此处为计算方法
            this.computeData();
            //  在活动中，需要进行折扣计算
            if (this.comboInfo.is_activity_time) {
                // 优惠段
                const rule = JSON.parse(JSON.stringify(this.comboInfo.rule));

                // 优惠段转成对象
                let ruleObj = {};
                rule.forEach((item) => {
                    ruleObj[Object.keys(item)[0]] = Object.values(item)[0];
                });

                if (this.comboInfo.type <= 5) {
                    // 勾选个数
                    let len = this.goodsIds.length;
                    // 优惠段具体信息 -1 表示没有优惠
                    let range = -1;
                    // 存在优惠段
                    if (ruleObj[len]) {
                        range = +ruleObj[len];
                        this.piceCount = len;
                    } else {
                        // 把优惠段键值先按大小排序
                        let ruleCopy = Object.keys(ruleObj).sort(function (x, y) {
                            return x - y;
                        });
                        // 优惠段长度
                        let rlen = ruleCopy.length;
                        // 优惠段在区间内
                        if (len > ruleCopy[0] && len < ruleCopy[rlen - 1]) {
                            let index = ruleCopy.findIndex((item) => {
                                return item > len;
                            });
                            this.piceCount = ruleCopy[index - 1];
                            range = +ruleObj[ruleCopy[index - 1]];
                        } else if (len > ruleCopy[rlen - 1]) {
                            range = +ruleObj[ruleCopy[rlen - 1]];
                            this.piceCount = ruleCopy[rlen - 1];
                        } else {
                            range = -1;
                            this.piceCount = 0;
                        }
                    }

                    if (range < 0) {
                        // 无优惠信息
                        shopPrice = this.marketPrice;
                    } else {
                        switch (parseInt(this.comboInfo.type, 10)) {
                        case 1:
                            // X件享Y折
                            shopPrice = this.marketPrice * (1 - range / 100);
                            break;
                        case 2:
                            // X件免Y件
                            shopPrice = this.priceArr.slice(range).reduce((prev, next) => {
                                return prev + next;
                            });
                            break;
                        case 3:
                            // 第X件享Y折
                            shopPrice = this.priceArr[0] * (1 - range / 100) + this.priceArr.slice(1).reduce((prev, next) => {
                                return prev + next;
                            });
                            break;
                        case 4:
                            // X件减Y元
                            shopPrice = this.marketPrice - range;
                            break;
                        case 5:
                            // X件Y元
                            if (len - this.piceCount > 0) {
                                let sum = this.priceArr.slice(this.piceCount).reduce((prev, next) => {
                                    return prev + next;
                                });
                                shopPrice = +sum + (range - 0);
                            } else {
                                shopPrice = range;
                            }
                            break;
                        }
                    }
                } else {
                    let price = -1;
                    // 把键值先按大小排序
                    let priceCopy = Object.keys(ruleObj).sort(function (x, y) {
                        return x - y;
                    });
                    // 存在优惠段
                    // 优惠段在区间内
                    if (this.marketPrice >= priceCopy[0] && this.marketPrice <= priceCopy[priceCopy.length - 1]) {
                        let index = priceCopy.findIndex((item) => {
                            return item >= this.marketPrice;
                        });
                        price = +ruleObj[priceCopy[index - 1]];
                    } else if (this.marketPrice > priceCopy[priceCopy.length - 1]) {
                        price = +ruleObj[priceCopy[priceCopy.length - 1]];
                    } else if (this.marketPrice < priceCopy[0]) {
                        price = -1;
                    }
                    if (price < 0) {
                        // 无优惠信息
                        shopPrice = this.marketPrice;
                    } else {
                        switch (parseInt(this.comboInfo.type, 10)) {
                        case 6:
                            // X元减Y元
                            shopPrice = this.marketPrice - price;
                            break;
                        case 7:
                            // X元享Y折
                            shopPrice = this.marketPrice * (1 - price / 100);
                            break;
                        }
                    }
                }
            } else {
                shopPrice = this.marketPrice;
            }
            $marketBox.replaceWith(`<del class="my-shop-price market-total-price" data-orgp="${this.marketPrice}"> {this.marketPrice} </del>`);
            $shoptBox.replaceWith(`<span class="my-shop-price shop-total-price" data-orgp="${shopPrice}"> {shopPrice} </span>`);
            if (window.GEShopSiteCommon) {
                window.GEShopSiteCommon.renderCurrency();
            }
        },
        // 底部购物属性和价格计算
        computeData () {
            const ids = [];
            this.priceArr = [];
            this.$goodsList.find('.join-comb.on').each(function (index, item) {
                ids.push(JSON.parse($(this).attr('data-list')));
            });
            this.goodsIds = [];
            let marketPrice = [];
            ids.forEach((item) => {
                this.goodsIds.push(item.goods_id);
                marketPrice.push((item.shop_price - 0));
                // marketPrice += item.shop_price - 0;
            });
            if (marketPrice.length > 0) {
                this.priceArr = marketPrice.sort(function (x, y) {
                    return x - y;
                });
                this.marketPrice = this.priceArr.reduce((prev, next) => {
                    return prev + next;
                });
            } else {
                this.marketPrice = 0;
            }
        },
        // 过滤 size 或者 color
        searchData (index, type, e) {
            const $curSelect = $(e.target);
            const $listWrap = this.$goodsList.find('li.comb-goods-list').eq(index);
            $listWrap.siblings().find('.target-text').removeClass('on');
            $curSelect.toggleClass('on');
            $listWrap.siblings().find('.sleWrap').removeClass('on').css('height', '0');

            if (type == 'size') {
                $listWrap.find('.js-color-wrap').css('height', '0').removeClass('on');
                $listWrap.find('.js-size-wrap').css('height', '0').toggleClass('on');
                const filterColor = $.trim($listWrap.find('.js-color-text').text());
                if (filterColor == '') {
                    this.sizeArr = this.combData[index];
                } else {
                    this.sizeArr = this.combData[index].filter((item) => {
                        return item.color == filterColor;
                    });
                }
                $listWrap.find('.sleWrap.on').css('height', this.sizeArr.length * 30 + 'px');
            } else {
                $listWrap.find('.js-size-wrap').css('height', '0').removeClass('on');
                $listWrap.find('.js-color-wrap').css('height', '0').toggleClass('on');
                const filterSize = $.trim($listWrap.find('.js-size-text').text());
                if (filterSize == '') {
                    this.colorArr = this.combData[index];
                } else {
                    this.colorArr = this.combData[index].filter((item) => {
                        return item.size == filterSize;
                    });
                }
                $listWrap.find('.sleWrap.on').css('height', this.colorArr.length * 30 + 'px');
            }
        },
        // 选中 下拉的属性
        setSelectItem (index, type, item) {
            const $selCont = this.$goodsList.find('.sleWrap');
            const $listWrap = this.$goodsList.find('li.comb-goods-list').eq(index);
            $selCont.removeClass('on').css('height', '0');
            if (type == 'size') {
                $listWrap.find('.js-size-text').text(item.size);
            } else {
                $listWrap.find('.js-color-text').text(item.color);
            }
            // 操作data属性 标记有用信息
            $listWrap.find('.sleWrap').removeClass('on');
            $listWrap.find('.join-comb').attr('data-id', item.goods_id).attr('data-list', JSON.stringify(item));
            $listWrap.find('.goods-list-title').text(item.goods_title);
            $listWrap.find('.js_gbexp_lazy').attr('src', item.goods_img);
            $listWrap.find('.u000233-model1-goods-img a').attr('href', item.url_title);
            $listWrap.find('.my-shop-price').attr('data-orgp', item.shop_price);

            const html = `<span class="my-shop-price" data-orgp="${item.shop_price}"> ${item.shop_price} </span>`;
            $listWrap.find('.geshop-shop-price').html(html);
            if (window.GEShopSiteCommon) {
                window.GEShopSiteCommon.renderCurrency();
            }
            this.addDiscount();
        },
        // 点击购物车按钮
        submitCat () {
            if (this.goodsIds.length < 1) {
                let msgtip_cc;
                if (JS_LANG == 'fr/') {
                    msgtip_cc = 'Merci de sélectionner les produits';
                } else {
                    msgtip_cc = 'please select products';
                }
                layer.alert(msgtip_cc, {
                    title: '',
                    btn: 'ok',
                    skin: 'dresslily-layer-style',
                    shade: [0.89, '#bcbcbc', true],
                    area: ['auto', 'auto']
                });
                return false;
            } else {
                if (GESHOP_PLATFORM == 'web') {
                    const index = layer.load(1);
                    const url = DOMAIN + '/fun/';
                    const data = {
                        act: 'bat_add_to_cart',
                        goods_ids: this.goodsIds.join(',')
                    };
                    $.ajax({
                        url: url,
                        dataType: 'jsonp',
                        data: data,
                        jsonp: 'jsoncallback',
                        success: function (res) {
                            layer.close(index);
                            if (res.status == 0) {
                                // window.location.href = DOMAIN + '/m-flow-a-cart.html';
                                window.open(DOMAIN + '/m-flow-a-cart.html');
                            } else {
                                layer.alert(res.ms, {
                                    title: ' ',
                                    btn: 'ok',
                                    skin: 'dresslily-layer-style',
                                    shade: [0.89, '#bcbcbc', true],
                                    area: ['auto', 'auto']
                                });
                            }
                        }
                    });
                } else {
                    //    APP z暂时没有此功能 需要后期补上
                }
            }
        },
        // 初始化数据
        getData () {
            const id = this.data.act_id || '';
            const goodsSn = this.data.goodsSKU || '';
            if (typeof goodsSn == 'undefined' || goodsSn == '' || goodsSn == null) {
                this.combData = [
                    [
                        {
                            active_num: '3319',
                            color: 'WHITE',
                            discount: '50',
                            goods_id: '1wewqrqfq',
                            goods_img: '',
                            goods_sn: '',
                            goods_title: 'Stylish Round Neck Long Sleeve Hollow Out Spliced Women\'s Blouse',
                            is_on_sale: '1',
                            market_price: '12.00',
                            shop_price: '10.00',
                            url_title: '',
                            size: 'S'
                        },
                        {
                            active_num: '3319',
                            color: 'WHITE',
                            discount: '50',
                            goods_id: 'wewrewr1',
                            goods_img: '',
                            goods_sn: '',
                            goods_title: 'Stylish Round Neck Long Sleeve Hollow Out Spliced Women\'s Blouse',
                            is_on_sale: '1',
                            market_price: '12.00',
                            shop_price: '10.00',
                            url_title: '',
                            size: 'S'
                        }
                    ],
                    [{
                        active_num: '3319',
                        color: 'WHITE',
                        discount: '50',
                        goods_id: '13435432',
                        goods_img: '',
                        goods_sn: '',
                        goods_title: 'Stylish Round Neck Long Sleeve Hollow Out Spliced Women\'s Blouse',
                        is_on_sale: '1',
                        market_price: '12.00',
                        shop_price: '10.00',
                        url_title: '',
                        size: 'S'
                    }],
                    [{
                        active_num: '3319',
                        color: 'WHITE',
                        discount: '50',
                        goods_id: '11223',
                        goods_img: '',
                        goods_sn: '',
                        goods_title: 'Stylish Round Neck Long Sleeve Hollow Out Spliced Women\'s Blouse',
                        is_on_sale: '1',
                        market_price: '12.00',
                        shop_price: '10.00',
                        url_title: '',
                        size: 'S'
                    }],
                    [{
                        active_num: '3319',
                        color: 'WHITE',
                        discount: '50',
                        goods_id: '1222',
                        goods_img: '',
                        goods_sn: '',
                        goods_title: 'Stylish Round Neck Long Sleeve Hollow Out Spliced Women\'s Blouse',
                        is_on_sale: '1',
                        market_price: '12.00',
                        shop_price: '10.00',
                        url_title: '',
                        size: 'S'
                    }]
                ];
                // 页面元素初始化
                this.$store.dispatch('global/async_goods_init', this);
            } else {
                // const url = 'http://www.pc-dresslily.com.v0426.php5.egomsl.com/geshop/goods/comblist';
                const url = GESHOP_INTERFACE.goods_comblist.url;
                const data = {
                    lang: GESHOP_LANG,
                    id: id,
                    client: GESHOP_PLATFORM,
                    goodsSn: goodsSn
                };
                this.$jsonp(url, data).then(res => {
                    this.combData = res.data.goodsInfo;
                    this.comboInfo = res.data.comboInfo;
                    this.$nextTick(() => {
                        this.$goodsList = $(this.$refs.goodsList);
                        this.onChange();
                        this.bindEvent();
                        // 页面元素初始化
                        this.$store.dispatch('global/async_goods_init', this);
                    });
                });
            }
        },
        onChange () {
            let w = this.$goodsList.width();
            if (w > 1200) {
                this.boxWrapMedia = 'geshop_dl_pc';
            } else if (w <= 1200 && w >= 768) {
                this.boxWrapMedia = 'geshop_dl_pad';
            } else if (w < 768 && w >= 375) {
                this.boxWrapMedia = 'geshop_dl_wap';
            } else {
                this.boxWrapMedia = 'geshop_dl_wap geshop_dl_m';
            }
        }
    },
    watch: {}
};
</script>
<style lang="less">
    .geshop-u000233-model1 {
        .buy-box {
            width: 100%;
            text-align: right;
            padding: 18px 0;

            .price-group {
                display: inline-block;
                vertical-align: middle;

                .market-total-price, .shop-total-price {
                    display: block;
                    -webkit-text-stroke: 0px rgba(51, 51, 51, 1);
                    text-stroke: 0px rgba(51, 51, 51, 1);
                    font-family: LatoBold;
                    margin: 0 6px;
                    @media (max-width: 768px){
                        margin: 0;
                    }
                }

                .shop-total-price {
                }
            }

            .btn-car {
                display: inline-block;
                vertical-align: middle;
            }

            .market-total-price {
                font-size: 20px;
                font-family: Lato!important;
            }

            .btn-car {
                padding: 0 20px;
                height: 48px;
                border-radius: 2px;
                border: none;
                font-size: 20px;
                cursor: pointer;
                margin-left: 20px;
                margin-right: 30px;
                text-align: center;
                line-height: 48px;

                img {
                    vertical-align: middle;
                    margin-top: -4px;
                    margin-right: 4px;
                }
            }
        }

        @media (max-width: 768px) {
            .buy-box {
                height: auto;
                padding: 14px 12px;
                text-align: left;
                box-sizing: border-box;
                position: relative;

                .market-total-price, .shop-total-price, .btn-car {
                    display: block;
                }

                .market-total-price, .shop-total-price {
                    line-height: 24px;
                    -webkit-text-stroke: 0px rgba(51, 51, 51, 1);
                    text-stroke: 0px rgba(51, 51, 51, 1);
                    font-family: LatoBold;
                    margin: 0;
                }

                .market-total-price {

                    font-size: 14px;
                    color: rgba(255, 255, 255, 1);

                }

                .price-group {
                    .shop-total-price {
                        color: rgba(255, 255, 255, 1);
                    }

                }

                .btn-car {
                    position: absolute;
                    width: auto;
                    padding: 0 30px;
                    height: 34px;
                    border-radius: 2px;
                    border: none;
                    font-size: 16px;
                    cursor: pointer;
                    margin-left: 0;
                    margin-right: 0;
                    text-align: center;
                    line-height: 34px;
                    right: 12px;
                    top: 50%;
                    margin-top: -17px;

                    > img {
                        display: none;
                    }
                }
            }
        }

    }
</style>

<style lang="less" scoped>
    @import "indexs";
</style>
