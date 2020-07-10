/**
 * @author wu xingtgao
 * @date 2019/6/26
 * @Description: tab商品列表 sku自动刷新
 */
export default {
    data () {
        return {
            noPreview: false,
            hasSyncData: false, // 存在异步数据
            goodsArray: [] // tab商品数据列
        };
    },
    computed: {
        /**
         * 商品 pid
         * @returns {*}
         */
        pidValue: function () {
            return this.pid || this.$attrs.pid;
        },
        /**
         * store 是否发布页请求之后返回数据
         * @returns {*}
         */
        isDateRes: function () {
            return this.$store.state.global.isDateRes;
        },
        /**
         * store 中商品数据
         * @returns {*}
         */
        goodsInfo: function () {
            return this.$store.state.global.goodsInfo;
        },
        /**
         * 本地商品数据 []
         */
        asyncInfo: function () {
            return window.GESHOP_ASYNC_DATA_INFO;
        }
    },
    watch: {
        /**
         * 更新自动刷新sku
         * @param val
         */
        isDateRes: function (val) {
            this.updateComponent(val);
        }
    },
    mounted () {
        this.getGoodsInfo();
        this.isDateRes && this.updateComponent();
    },
    methods: {
        updateComponent (val) {
            let asyncInfo = window.GESHOP_ASYNC_DATA_INFO;
            if (asyncInfo && asyncInfo[this.pidValue]) {
                this.getGoodsInfo();
                // 初始化mounted
                if (this.initMounted) {
                    this.initMounted(val);
                } else {
                    // 渲染完毕
                    this.$nextTick(() => {
                        // 去除骨架图
                        this.$store.dispatch('global/loaded', this);
                        // 页面元素初始化
                        this.$store.dispatch('global/async_goods_init', this);
                    });
                }
                // 去除骨架图
                this.$store.dispatch('global/loaded', this);
            }
        },
        /**
         * 更新商品列表数据
         * @param val
         */
        getGoodsInfo (val) {
            let goodsArray = this.getSyncGoodsInfo();
            if (goodsArray.length > 0) {
                this.goodsArray = goodsArray;
                this.hasSyncData = true;
            } else {
                this.hasSyncData = false;
            }

            // this.handleDataException();
        },
        /**
         * 数据异常处理 ToDo: tab 数据异常处理,tab暂无需处理
         */
        handleDataException () {
            // 是否隐藏组件
            let data = this.data;
            if (data.isEditEnv == 1) {
                this.noPreview = false;
                return false;
            }
            let goodsInfo = this.getSyncGoodsInfo();
            if ((data.ipsGoodsSKU || data.goodsSKU) && (!goodsInfo || (goodsInfo && goodsInfo.length <= 0))) {
                this.noPreview = true;
            } else {
                this.noPreview = false;
            }
        },
        /**
         * 获取自动刷新sku
         * @returns {Array}
         */
        getSyncGoodsInfo () {
            let goodsArray = [];
            let asyncInfo = window.GESHOP_ASYNC_DATA_INFO;
            if (asyncInfo && asyncInfo[this.pidValue] && asyncInfo[this.pidValue].length > 0 && asyncInfo[this.pidValue][0].goodsInfo.length > 0) {
                goodsArray = asyncInfo[this.pidValue];
            }
            return goodsArray;
        }
    }
};
