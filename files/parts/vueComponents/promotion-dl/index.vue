<template>
    <div>
        <p
            class="geshop-goods-promotions has-more"
            @click="handleShowMoreClick"
            @mouseenter="handleShowMore(true)"
            @mouseleave="handleShowMore(false)">
            <template v-if="list && list.length > 0">
                <span
                    v-show="media_platform == 'pc'"
                    class="promotions-text"
                    :data-promotions-length="list.length"
                    v-html="list[0] + (list.length > 1 ? ' ···' : '')">
                </span>

                <span
                    v-show="media_platform != 'pc'"
                    class="promotions-text"
                    :data-promotions-length="list.length">
                    <span v-html="list[0]"></span>
                    <img
                        v-if="list.length > 1"
                        :class="{ 'up': showMore }"
                        src="https://geshoptest.s3.amazonaws.com/uploads/WaiETHlZ8ShDeFRXMB97Yjc3uQ4InAJr.png">
                </span>
            </template>
        </p>
        <div
            v-if="list.length > 1"
            class="geshop-goods-promotions-more"
            :class="{ 'none': !showMore }">
            <template v-for="(label, index) in list">
                <p v-html="label" :key="index"></p>
            </template>
        </div>
    </div>
</template>

<script>
export default {
    name: 'geshop-promotion-dl',
    props: {
        media_platform: {
            type: String
        },
        list: {
            type: Array,
            default: () => []
        }
    },

    data () {
        return {
            showMore: false
        };
    },

    methods: {
        handleShowMoreClick () {
            this.showMore = !this.showMore;
        },
        /**
         * 展开/隐藏更多
         * @param {Boolean} value [true/false]
         */
        handleShowMore (value) {
            // 非PC不执行，避免重复执行
            if (this.media_platform != 'pc') return false;
            this.showMore = value;
        }
    }
};
</script>

<style lang="less" scoped>
.geshop-goods-promotions {
    margin-top: 5px;
    cursor: pointer;
    height: 17px;
    line-height: 17px;
    overflow: hidden;
    font-size: 14px;
    padding-left: 12px;
    padding-bottom: 16px;

    span {
        font-size: 14px;
        word-break: break-all;
    }

    img {
        width: 10px;
        margin-left: 8px;
        transition: all 0.3s;

        &.up {
            transform: rotate(180deg);
            transition: all 0.3s;
        }
    }

    .promotions-text em {
        font-style: normal;
    }
}

.geshop-goods-promotions-more {
    position: absolute;
    z-index: 999;
    bottom: 41px;
    left: 0px;
    padding: 10px 20px;
    border: 1px solid #121212;
    border-radius: 4px;
    font-size: 14px;
    color: #121212;
    background-color: #fff;
    line-height: 20px;

    &:before {
        content: '';
        position: absolute;
        left: 15px;
        border-left: 5px solid transparent;
        border-right: 5px solid transparent;
        bottom: -8px;
        border-top: 8px solid #121212;
    }

    &:after {
        content: '';
        position: absolute;
        left: 15px;
        border-left: 5px solid transparent;
        border-right: 5px solid transparent;
        bottom: -7px;
        border-top: 8px solid #fff;
    }
}
.geshop-goods-promotions-more.none {
    display: none;
}

@media screen and (max-width: 1024px) and (min-width: 768px) {
    .geshop-goods-promotions {
        font-size: 14px;
        text-overflow: ellipsis;
        overflow: hidden;
        width: 90%;
        white-space: nowrap;
    }

    .geshop-goods-promotions-more {
        padding: 10px 16px !important;
    }
}

@media screen and (max-width: 767px) and (min-width: 374px) {
    .geshop-goods-promotions {
        padding-bottom: 10px !important;
        font-size: 13px;
        text-overflow: ellipsis;
        overflow: hidden;
        width: 90%;
        white-space: nowrap;
    }
}
</style>
