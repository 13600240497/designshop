<template>
    <div class="GESHOP_pagination" v-if="visible && pageInfo.pageCount > 0">
        <div class="page_box" :style="defaultColor">
            <ul>
                <!-- prev -->
                <li class="btn_prev" :class="{'disabled':pageInfo.currentPage === 1}" @click="handlePrev">
                    <i class="gs-icon gs-icon-arrow-left"></i>
                </li>
                <li
                    :data-id="pageInfo.currentPage"
                    :class="{ active: pageInfo.currentPage === 1}"
                    v-if="pageInfo.pageCount > 0"
                    class="number"
                    @click="goPage(1)">1
                </li>
                <li
                    class="more btn_quickprev"
                    v-if="showPrevMore">
                    ...
                </li>
                <li
                    v-for="aPage in pagers"
                    :key="aPage"
                    :class="{ active: pageInfo.currentPage === aPage }"
                    class="number"
                    @click="goPage(aPage)">{{ aPage }}
                </li>
                <li
                    class="more btn_quicknext"
                    v-if="showNextMore">
                    ...
                </li>
                <!--总页数-->
                <li
                    :class="{ active: pageInfo.currentPage === pageInfo.pageCount }"
                    class="number"
                    v-if="pageInfo.pageCount > 1"
                    @click="goPage(pageInfo.pageCount)">{{ pageInfo.pageCount }}
                </li>
                <!--next-->
                <li class="btn_next" :class="{'disabled':pageInfo.currentPage === pageInfo.pageCount}" @click="handleNext">
                    <i class="gs-icon gs-icon-arrow-right"></i>
                </li>
            </ul>
        </div>
    </div>
</template>

<script>
export default {
    name: 'geshop-pagination',
    props: {
        visible: Boolean,
        // 商品总数
        totalCount: {
            type: Number,
            default: ''
        },
        // 当前页数
        currentPage: {
            type: Number,
            default: 1
        },
        // 总页数
        pageCount: Number,
        pageSize: {
            type: Number,
            default: 20
        }
    },
    data () {
        return {
            showPrevMore: false, // 显示prev ...
            showNextMore: false, // 显示next ...
            pageInfo: {},
            data: '', // form 组件数据data
            pagers: [] // 页码数组
        };
    },
    watch: {

        /**
         * 监听商品总数
         */
        totalCount () {
            this.initPage();
        },
        /**
         * 监听当前页数
         */
        currentPage () {
            this.initPage();
        },
        /**
         * 监听页面信息
         */
        pageInfo: {
            handler () {
                this.pagers = this.getPagers();
            },
            deep: true
        }
    },
    mounted () {
        this.data = this.$root.data;
    },
    computed: {
        defaultColor () {
            return {
                'color': `${this.data.page_text_color || '#333333'}`,
                'border-color': `${this.data.page_border_color || '#DDDDDD'}`
            };
        }
    },
    methods: {
        /**
         * 初始化prop数据
         */
        initPage () {
            this.pageInfo = {
                totalCount: Number(this.totalCount),
                currentPage: Number(this.currentPage),
                pageCount: Number(this.pageCount),
                pageSize: Number(this.pageSize)
            };
        },
        handleNext () {
            let currentPage = this.pageInfo.currentPage;
            let pageCount = this.pageInfo.pageCount;
            if (currentPage === pageCount) {
                return false;
            }
            this.goPage(this.pageInfo.currentPage + 1);
        },
        handlePrev () {
            let currentPage = this.pageInfo.currentPage;
            if (currentPage === 1) {
                return false;
            }
            this.goPage(this.pageInfo.currentPage - 1);
        },
        /**
         * 前往页码
         * @param value
         */
        goPage (value) {
            if (value !== this.pageInfo.currentPage) {
                this.$emit('change', Number(value));
            }
        },
        /**
         * 生成当前pagination页码
         * @returns {Array}
         */
        getPagers () {
            let { totalCount, currentPage, pageCount } = this.pageInfo;
            const result = [];
            if (Number(totalCount) > 0) {
                let pageSpace = 4;
                let showPrevMore = false;
                let showNextMore = false;

                // 显示next页码省略号
                if (pageCount > pageSpace + 2 && currentPage <= pageCount - 2) {
                    showNextMore = true;
                }

                // 显示prev页码省略号
                if (pageCount > pageSpace + 2 && currentPage > pageSpace) {
                    showPrevMore = true;
                }
                // 不存在showNextMore...间隔 并且currentPage 与 pageCount 相邻
                if (showPrevMore && !showNextMore && (currentPage === pageCount - 1 || currentPage === pageCount)) {
                    for (let i = pageCount - 3; i < pageCount; i++) {
                        result.push(i);
                    }
                }
                // 不存在showNextMore...间隔
                if (!showNextMore && !showPrevMore) {
                    for (let i = 2; i < pageCount; i++) {
                        result.push(i);
                    }
                }
                // 存在showNextMore... 并且页数少于4
                if (showNextMore && currentPage < 4) {
                    for (let i = 2; i <= 4; i++) {
                        result.push(i);
                    }
                }
                // 存在showNextMore... 页数大于等于4
                if (showNextMore && currentPage >= 4) {
                    for (let i = currentPage - 2; i <= currentPage + 1; i++) {
                        result.push(i);
                    }
                }
                this.showNextMore = showNextMore;
                this.showPrevMore = showPrevMore;
            }

            return result;
        }
    }
};
</script>

<style lang="less" scoped>
    .page_box {
        position: relative;
        display: inline-block;

        li {
            display: inline-block;
            width: 40px;
            height: 40px;
            -moz-box-sizing: border-box;
            box-sizing: border-box;
            border: 1px solid #ddd;
            font-size: 14px;
            line-height: 38px;
            margin-right: 8px;
            color: inherit;
            padding: 0 6px;
            float: left;
            border-color: inherit;
            &:hover {
                border-color: #333;
            }
        }
    }

    .GESHOP_pagination {
        font-size: 14px;
        text-align: center;
        padding-bottom: 24px;
        height: 40px;
        .page_box{
            height: 100%;
        }
        ul {
            display: inline-block;
            line-height: 22px;
            height: 100%;
            border-color: inherit;
            li {
                float: left;
                color: inherit;
                cursor: pointer;
                border-color: inherit;
            }
        }

        .btn_prev, .btn_next {
            display: inline-block;
            font-size: 16px;
            font-weight: bold;
            color: inherit;
        }

        .btn_prev {
            margin-right: 12px;
        }

        .btn_next {
            margin-right: 12px;
        }

        .active {
            background: #333;
            color: #fff;
            border: none;
        }

        .disabled {
            cursor: default;
            color: #e3e3e3 !important;
            border-color: #ddd !important;
        }
    }
</style>
