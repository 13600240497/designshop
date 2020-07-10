<template>
    <div class="geshop-dialog-add-bag" v-if="visible">
        <div class="geshop-dialog-body">
            <div style="text-align:left;">
                <a href="javascript:;" class="geshop-dialog-close" @click="handleClose"></a>
                <div class="model-main">
                    <div class="geshop-dialog-image-layer">
                        <geshop-image-goods :src="product.goods_img"></geshop-image-goods>
                    </div>
                    <div class="model-right">
                        <div class="goods-title"> {{ product.goods_title }}</div>
                        <div class="item-price">
                            <span class="shop-price">
                                <strong class="my_shop_price" data-orgp="0.00">$0.00</strong>
                            </span>
                            <span class="market-price">
                                <del class="my_shop_price" :data-orgp="product.market_price">${{ product.market_price }}</del>
                            </span>
                        </div>
                        <div class="select-content select-size" v-if="product.size_list.length > 0">
                            <div class="size-title">{{ languages.size }}</div>
                            <div class="size-box clearfix">
                                <div
                                    class="size-item"
                                    :class="{ 'selected-size': selected_size == item.title }"
                                    v-for="item in product.size_list"
                                    :key="item.title"
                                    @click="handleUpdateSKU(item)">
                                    {{ item.title }}
                                </div>
                            </div>
                        </div>
                        <div class="select-content select-color" v-if="product.color_list.length > 0">
                            <div class="color-title">{{ languages.color }}</div>
                            <div class="color-box clearfix">
                                <div
                                    class="color-item"
                                    :class="{ 'selected-color': selected_color == item.color_value }"
                                    v-for="item in product.color_list"
                                    :key="item.title"
                                    :data-goodsku="item.goods_sn"
                                    @click="handleUpdateSKU(item)">
                                    <template v-if="item.color_img">
                                        <img :src="item.color_img" width="100%" height="100%">
                                    </template>
                                    <template v-else>
                                        <span :style="{ 'background-color': item.color_value }"></span>
                                    </template>
                                </div>
                            </div>
                        </div>
                        <div class="add-bag" @click="handleSubmit">{{ languages.add_to_bag }}</div>
                    </div>
                </div>
            </div>
        </div>
        <!--  -->
        <div class="geshop-dialog-overlay"></div>
    </div>
</template>

<script>

export default {
    name: 'geshop-dialog-add-bag',
    props: [],
    data() {
        return {
            visible: false,
            product: {
                goods_title: 'This is the commodity related name and it’s information',
                shop_price: '0.00',
                market_price: '0.00',
                color_list: [],
                size_list: [],
            },
            // 当前SKU
            goods_sn: '',
            // 当前活动ID
            activity_id: '',
            // 当前选中的尺寸
            selected_size: '',
            // 当前选中的颜色
            selected_color: '',
            // 
            languages: window.GESHOP_LANGUAGES,
            langCode: window.GESHOP_LANG,
        }
    },
    methods: {
        // 展示弹层
        show({ goods_sn, activity_id }) {
            this.activity_id = activity_id;
            this.goods_sn = goods_sn;
            this.get_product_info();
        },

        async get_product_info () {
            //添加购物车参数
            const params = {
                goodsSn: this.goods_sn,
                manzeng_id: this.activity_id,
            };
            this.$jsonp(GESHOP_INTERFACE.getlistinspu.url, params).then(res => {
                // 有数据就弹层，否则报错
                if (res.data.goodsInfo.goods_id) {
                    this.product = JSON.parse(JSON.stringify(res.data.goodsInfo));
                    this.goods_sn = this.product.goods_sn;
                    this.selected_size = this.product.size;
                    this.selected_color = this.product.color;
                    this.visible = true;
                    this.update_currency()
                } else {
                    GEShopSiteCommon.dialog.message(`<div class="add-fail">${err.message || GESHOP_LANGUAGES.gift_fail_add}</div>`)
                }
            }, (err) => {
                GEShopSiteCommon.dialog.message(`<div class="add-fail">${err.message || GESHOP_LANGUAGES.gift_fail_add}</div>`)
            })
        },

        // 更改size
        async handleUpdateSKU(item) {
            this.goods_sn = item.goods_sn
            await this.get_product_info()
        },

        // 关闭弹层
        handleClose() {
            this.visible = false;
        },

        // 加入购物车
        handleSubmit() {
            const data = {
                goodsSn: this.goods_sn,
                manzeng_id: this.activity_id,
            };
            this.$jsonp(GESHOP_INTERFACE.addgifttocart.url, data).then(res => {
                GEShopSiteCommon.dialog.message( GESHOP_LANGUAGES.gift_add_cart);
            }, err => {
                GEShopSiteCommon.dialog.message(err.message);
            })
            this.visible = false;
        },
        update_currency() {
            this.$nextTick(() => {
                if (window.GEShopSiteCommon) {
                    window.GEShopSiteCommon.renderCurrency()
                }
            })
        }
    },
    mounted() {
        this.update_currency()
    }
}
</script>

<style lang="less" scoped>

    .geshop-dialog-overlay {
        z-index: 1000;
        border: none;
        margin: 0px;
        padding: 0px;
        width: 100%;
        height: 100%;
        top: 0px;
        left: 0px;
        background-color: rgb(0, 0, 0);
        opacity: 0.6;
        cursor: wait;
        position: fixed;
    }

    .geshop-dialog-body {
        z-index: 1011;
        position: fixed;
        padding: 50px;
        margin: -217px auto auto -376px;
        width: 752px;
        top: 50%;
        left: 50%;
        text-align: center;
        color: rgb(0, 0, 0);
        border: none;
        background-color: rgb(255, 255, 255);
        cursor: default;
        height: 394px;
        box-sizing: border-box;
    }

    .geshop-dialog-image-layer {
        width: 221px;
        height: 294px;
        position: absolute;
        left: 40px;
        top: 50px;
        img {
            height: auto !important;
        }
    }

    .geshop-dialog-close {
        position: absolute; right: -15px; top: -15px; z-index: 10002; width: 30px; height: 30px; margin-left: 0; background-image: url(data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAB4AAAAeCAYAAAA7MK6iAAAAGXRFWHRTb2Z0d2FyZQBBZG9iZSBJbWFnZVJlYWR5ccllPAAAAyJpVFh0WE1MOmNvbS5hZG9iZS54bXAAAAAAADw/eHBhY2tldCBiZWdpbj0i77u/IiBpZD0iVzVNME1wQ2VoaUh6cmVTek5UY3prYzlkIj8+IDx4OnhtcG1ldGEgeG1sbnM6eD0iYWRvYmU6bnM6bWV0YS8iIHg6eG1wdGs9IkFkb2JlIFhNUCBDb3JlIDUuMy1jMDExIDY2LjE0NTY2MSwgMjAxMi8wMi8wNi0xNDo1NjoyNyAgICAgICAgIj4gPHJkZjpSREYgeG1sbnM6cmRmPSJodHRwOi8vd3d3LnczLm9yZy8xOTk5LzAyLzIyLXJkZi1zeW50YXgtbnMjIj4gPHJkZjpEZXNjcmlwdGlvbiByZGY6YWJvdXQ9IiIgeG1sbnM6eG1wPSJodHRwOi8vbnMuYWRvYmUuY29tL3hhcC8xLjAvIiB4bWxuczp4bXBNTT0iaHR0cDovL25zLmFkb2JlLmNvbS94YXAvMS4wL21tLyIgeG1sbnM6c3RSZWY9Imh0dHA6Ly9ucy5hZG9iZS5jb20veGFwLzEuMC9zVHlwZS9SZXNvdXJjZVJlZiMiIHhtcDpDcmVhdG9yVG9vbD0iQWRvYmUgUGhvdG9zaG9wIENTNiAoV2luZG93cykiIHhtcE1NOkluc3RhbmNlSUQ9InhtcC5paWQ6NDg1QjM5RUREQjcxMTFFODlBNDZCRjdFMjlGNkExN0EiIHhtcE1NOkRvY3VtZW50SUQ9InhtcC5kaWQ6NDg1QjM5RUVEQjcxMTFFODlBNDZCRjdFMjlGNkExN0EiPiA8eG1wTU06RGVyaXZlZEZyb20gc3RSZWY6aW5zdGFuY2VJRD0ieG1wLmlpZDo0ODVCMzlFQkRCNzExMUU4OUE0NkJGN0UyOUY2QTE3QSIgc3RSZWY6ZG9jdW1lbnRJRD0ieG1wLmRpZDo0ODVCMzlFQ0RCNzExMUU4OUE0NkJGN0UyOUY2QTE3QSIvPiA8L3JkZjpEZXNjcmlwdGlvbj4gPC9yZGY6UkRGPiA8L3g6eG1wbWV0YT4gPD94cGFja2V0IGVuZD0iciI/PmkvRE0AAAPXSURBVHjarJdNSBtBFIDfLir4ExPUSFBIrVYUBRsoFATBS2NLD72U0oiHCPEHL1IoufZWPJceSrRiDXpJeym9tBgVSqhQQayC+IPWamNDQiT+EKii6XvbzHay2c2apA8ek5ndfd+bmffeTATIQi4vLzux6UC1olpQL1AjgiB8xzaAbQD+lyQSCRsCPajhhI7gOz9RX6K25AO0ooGRRI6C3z5DNWvZFzSgd1Df4tKZ+PFIJAKrq6sQi8Xg9PQU8DmUlpZCRUUFtLW1Sa3Czi9sHKIoftYFo5dONPiGH1tZWQG/3w/hcDjjKtXU1EBXVxc0NzcrHXiA8A+aYITeRehH1j85OYHp6WnY3d3NapsaGxuhu7sbSkpKeHgnP3OBg17H5huCDdQPBoMwOTkJx8fHOcVIZWUluFwuefkR/ANt30Q9or7IvTvEoATLB0oSjUZhfHwc4vH43xkKwjVisOdi0ptb+MDNBqempvKC8nCfz8cPjeDK3uDBsifLy8uwt7enGjiZpKioCEwmU9r4+vo6bG5u8kNDMhhn62Kjs7OzaR/bbDYYHh6G/v5+KCwsTHtusVjA7XZLSg4oZWZm5l80J1kiTt3OBil6KVeVsr29LS19Q0MD9Pb2psCrq6slhwwGA+zs7MDZ2Vna9/v7+xAKhVjXiMzbYrL2SrK1taW6jJRWHo8nDU7QwcFBqYgQ1Ov1am6FwnYHgZv4ypQpUHg4pQoPnZiYgPPzc83vFbabRFxzC+tRGdSL0rGxMQleV1d3ZaiKbYuYbYpQfSaV81EUc0o1EVNJ3vWysrKML5vNZjmQKBBp72nmtOxq0c6LwnaI3N3gDWeCDgwMyNFLVWl0dDQFrpZKGrY3CBzgi7uaUGHgoWxPKWB4uNPp1Fx6he0ABdcc61mt1rQzlaS2tlaCUj4rA4nBWcAVFBSofk9FJilH6NxXIXkyfUEH2un30tKSsr7KhSLTeUx7XFxcrFrjKe/ZGY0x9QLBT9i6POXLI3moFL1LAK2CGrS+vl55MXgl12r0YAE9WWDp4XA4Ug7xXKW8vFyyxV0GnqP9jbTzGB/8ZhFIUUr7mqtQQPb19UnwJDTKZqt29XmIe/2O9elSR/X34OAgKygFWU9PT4rjCL6Hs/2U1WVvcXER5ufn4fDwULfA2O126capuOw9RqhP93qL8PvYvEcHUnKD7mFra2tS3lKwUTxUVVWB0WiE1tZWPmUYMIY2HqH6s/m70kL/CvK40NOfAWvOQYIG2lG9qPEr8GL43mu6w+keNlk6YU9eHGhNW3AJLxBC6RGUyqAozl3V1h8BBgAskqDnc4fYywAAAABJRU5ErkJggg==); background-repeat: no-repeat; background-position: center;
    }

    .model-right {
        float: none;
        position: relative;
        margin-left: 260px;
    }

    .goods-title {
        font-size: 18px;
        font-family: TrebuchetMS;
        color: #333;
        margin-bottom: 6px;
        height: 48px;
        overflow: hidden;
        text-overflow: ellipsis;
        display: -webkit-box;
        -webkit-box-orient: vertical;
        -webkit-line-clamp: 2;
    }

    .shop-price .my_shop_price {
        color: #333;
        height: 40px;
        line-height: 40px;
        font-size: 28px;
    }
    .market-price del {
        height: 40px;
        line-height: 40px;
        font-size: 14px;
        color: #999;
        margin-left: 16px;
    }

    .select-content {
        height: 40px;
    }

    .select-size {
        margin: 28px 0 6px 0;
    }

    .select-color {
        margin-bottom: 28px;
    }

    .size-title, .color-title {
        height: 30px;
        line-height: 30px;
        float: left;
        width: 80px;
    }

    .size-box, .gs-model-120 .color-box {
        float: left;
    }

    .size-item, .color-item {
        float: left;
        background: #fff;
        border: 1px solid #ddd;
        color: #666;
        text-align: center;
        cursor: pointer;
        box-sizing: border-box;
        width: 30px;
        height: 30px;
        line-height: 30px;
        margin-left: 10px;
        text-align: center;
    }

    .color-item {
        padding: 2px;
        > span {
            display: block;
            width: 100%;
            height: 100%;
        }
    }

    .selected-size {
        min-width: 30px;
        width: auto;
        border: 2px solid #333333;
    }

    .selected-color {
        color: #FFFFFF;
        background-color: #333333;
        border: 0;
    }


    .add-bag {
        display: inline-block;
        margin-top: 12px;
        min-width: 160px;
        max-width: 100%;
        padding: 0 20px;
        height: 50px;
        line-height: 50px;
        text-align: center;
        font-size: 16px;
        font-family: TrebuchetMS-Bold;
        font-weight: bold;
        color: #fff;
        cursor: pointer;
        background-color: #333;
        box-sizing: border-box;
    }

    .free {
        position: absolute;
        top: 0;
        left: 0;
        width: 100px;
        height: 100px;
        color: #FFFFFF;
        font-size: 16px;
        overflow: hidden;
        text-align: center;
    }

    .free-text {
        background: #FF8A00;
        height: 30px;
        line-height: 30px;
        transform: rotateZ(-45deg);
        -ms-transform: rotate(-45deg);
        -moz-transform: rotate(-45deg);
        -webkit-transform: rotate(-45deg);
        -o-transform: rotate(-45deg);
        margin-left: -60px;
    }


</style>
