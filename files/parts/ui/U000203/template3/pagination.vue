<template>
    <div class="GESHOP_pagination" v-show="showPage" v-if="oPage.pageCount > 1" :style="pageColor">
        <!-- prev -->
        <div class="btn_prev" @click="handlePrev"></div>

        <ul @click="onPagerClick" class="pager">
            <li
                :data-id="oPage.currentPage"
                :class="{ active: oPage.currentPage === 1}"
                v-if="oPage.pageCount > 0"
                class="number">1</li>
            <li
                class="more btn_quickprev"
                :class="[quickprevIconClass]"
                v-if="showPrevMore"
                @mouseenter="onMouseenter('left')">
                ...
            </li>
            <li
                v-for="pager in pagers"
                :key="pager"
                :class="{ active: oPage.currentPage === pager }"
                class="number">{{ pager }}</li>
            <li
                class="more btn_quicknext"
                :class="[quicknextIconClass]"
                v-if="showNextMore"
                @mouseenter="onMouseenter('right')">
                ...
            </li>

            <!--总页数-->
            <li
                :class="{ active: oPage.currentPage === pageCount }"
                class="number"
                v-if="oPage.pageCount > 1">{{ pageCount }}</li>
        </ul>

        <!--next-->
        <div class="btn_next" @click="handleNext"></div>

    </div>
</template>

<script>
export default {
    name: 'geshop-pagination',
    props: {
        showPage: Boolean,
        currentPage: Number, // 当前页
        pageSize: Number, // 每页显示数量
        // 商品总数
        total: {
            type: Number,
            default: 10
        },
        // 总页数
        pageCount: Number,
        // 默认显示数量，超过总页数会折叠
        pagerCount: {
            type: [String, Number]
        },
        // 样式
        pageStyle: {
            type: Object,
            default: {}
        }
    },
    watch: {
        total () {
            this.oPage.currentPage = Number(this.currentPage);
            this.oPage.pageSize = Number(this.pageSize);
            this.oPage.total = this.total;
            this.oPage.pageCount = this.pageCount;
            this.oPage.pagerCount = this.pagerCount;
        },
        currentPage () {
            this.oPage.currentPage = Number(this.currentPage);
            this.oPage.pageSize = Number(this.pageSize);
            this.oPage.total = this.total;
            this.oPage.pageCount = this.pageCount;
            this.oPage.pagerCount = this.pagerCount;
        },
        showPrevMore (val) {
            if (!val) {
                this.quickprevIconClass = 'icon_more';
            }
        },
        showNextMore (val) {
            if (!val) {
                this.quicknextIconClass = 'icon_more';
            }
        }
    },
    methods: {
        /**
         *  @Description page click
         *
         */

        onPagerClick (event) {
            const target = event.target;
            if (target.tagName === 'UL') {
                return;
            }
            let newPage = Number(event.target.textContent);
            const pageCount = this.oPage.pageCount;
            const currentPage = this.oPage.currentPage;
            const pagerCountOffset = this.oPage.pagerCount - 2;

            // prev next
            if (target.className.indexOf('more') !== -1) {
                if (target.className.indexOf('quickprev') !== -1) {
                    newPage = currentPage - pagerCountOffset;
                } else if (target.className.indexOf('quicknext') !== -1) {
                    newPage = currentPage + pagerCountOffset;
                }
            }

            if (!isNaN(newPage)) {
                if (newPage < 1) {
                    newPage = 1;
                }
                if (newPage > pageCount) {
                    newPage = pageCount;
                }
            }
            if (newPage !== currentPage) {
                this.$emit('change', newPage);
            }
        },

        /**
         *  @Description 省略
         *
         */
        onMouseenter (direction) {
            if (direction === 'left') {
                this.quickprevIconClass = 'arrow_left';
            } else {
                this.quicknextIconClass = 'arrow_right';
            }
        },

        /**
         *  @Description prev
         *
         */
        handlePrev () {
            const newVal = this.oPage.currentPage - 1;
            if (newVal <= 0) {
                return false;
            }
            this.$emit('change', newVal);
        },

        /**
         *  @Description next
         *
         */
        handleNext () {
            const newVal = this.oPage.currentPage + 1;
            if (newVal > this.oPage.pageCount) {
                return false;
            }
            this.$emit('change', newVal);
        }
    },
    data () {
        return {
            current: null,
            showPrevMore: false, // 显示prev
            showNextMore: false, // 显示next
            quicknextIconClass: 'icon_more',
            quickprevIconClass: 'icon_more',
            oPage: {
                currentPage: 1,
                pageSize: 20,
                total: '',
                pageCount: null,
                pagerCount: null
            }
        };
    },
    computed: {
        /**
         *  @Description 遍历页数
         *
         */
        pagers () {
            if (this.oPage.total != '') {
                const pagerCount = Number(this.oPage.pagerCount); // 默认显示页数
                const halfPagerCount = (pagerCount - 1) / 2;
                const currentPage = Number(this.oPage.currentPage); // 当前页
                const pageCount = Number(this.oPage.pageCount); // 总页数

                let showPrevMore = false;
                let showNextMore = false;

                // 总页数大于默认显示页数
                if (pageCount > pagerCount && pagerCount != 0) {
                    if (currentPage > pagerCount - halfPagerCount) {
                        showPrevMore = true;
                    }
                    if (currentPage < pageCount - halfPagerCount) {
                        showNextMore = true;
                    }
                }

                const array = [];
                if (showPrevMore && !showNextMore) { // 显示prev
                    const startPage = pageCount - (pagerCount - 2);
                    for (let i = startPage; i < pageCount; i++) {
                        array.push(i);
                    }
                } else if (!showPrevMore && showNextMore) { // 显示 next
                    for (let i = 2; i < pagerCount; i++) {
                        array.push(i);
                    }
                } else if (showPrevMore && showNextMore) {
                    const offset = Math.floor(pagerCount / 2) - 1;
                    let _i = currentPage - offset;
                    if (String(halfPagerCount).indexOf('.') != -1 && Math.ceil(halfPagerCount) > 1) {
                        _i = currentPage - offset + 1;
                    }
                    for (let i = _i; i <= currentPage + offset; i++) {
                        array.push(i);
                    }
                } else {
                    for (let i = 2; i < pageCount; i++) {
                        array.push(i);
                    }
                }
                this.showPrevMore = showPrevMore;
                this.showNextMore = showNextMore;
                return array;
            }
        },
        pageColor () {
            return {
                color: this.pageStyle.color
            };
        }
    }
};
</script>

<style lang="less" scoped>
    .GESHOP_pagination{
        clear: both;
        font-size: 14px;
        text-align: left;
        margin-left: 30px;
        ul{
            display: inline-block;
            line-height: 22px;
            vertical-align: middle;
            li {
                float: left;
                padding: 0px 15px;
                line-height: 38px;
                height: 38px;
                color: #666666;
                cursor: pointer;
            }
        }
        .btn_prev, .btn_next {
            display: inline-block;
            cursor: pointer;
        }

        .btn_prev:before{
            content: '<';
            margin-right: 5px;
        }

        .btn_next:before{
            content: '>';
        }
    }
</style>
