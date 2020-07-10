/**
 * @author wu xingtgao
 * @date 2019/6/26
 * @Description: 普通商品列表 sku自动刷新
 */

/**
 * 默认的假数据，在装修页会用到
 */
const default_lists = [
    { goods_title: 'Tartan Panel Long Sle…', promotions: [] },
    { goods_title: 'Tartan Panel Long Sle…', promotions: [] },
    { goods_title: 'Tartan Panel Long Sle…', promotions: [] },
    { goods_title: 'Tartan Panel Long Sle…', promotions: [] }
];

export default {
    data () {
        return {
            noPreview: false // 是否隐藏组件
        };
    },
    computed: {
        /**
         * store 判断数据是否请求完毕
         * @returns {*}
         */
        isDateRes: function () {
            return this.$store.state.global.isDateRes;
        },

        /**
         * store 中的所有商品数据
         * @returns {*}
         */
        goodsInfo: function () {
            return this.$store.state.global.goodsInfo;
        }
    },

    watch: {
        /**
         * 监听远端数据是否请求完毕
         * @param val
         */
        isDateRes: function (val) {
            this.updateComponent();
        }
    },

    methods: {
        updateComponent () {
            // 获取商品数据
            this.updateComponentGoodsInfo();

            // 是否隐藏组件
            this.noPreview = this.list.length <= 0;

            // 渲染完毕
            this.$nextTick(() => {
                this.$store.dispatch('global/loaded', this);
                this.$store.dispatch('global/async_goods_init_v2', this);
            });
        },

        /**
         * 更新商品列表数据
         */
        updateComponentGoodsInfo () {
            // 获取远端商品数据
            let lists = this.getSyncGoodsInfo();
            if (lists.length > 0 || window.GESHOP_PAGE_TYPE != '1') {
                // 商品列表赋值
                this.list = lists;
            } else {
                this.list = default_lists;
            }
        },
    
        /**
         * 获取自动刷新的商品数据
         * @returns {Array}
         */
        getSyncGoodsInfo () {
            // 组件唯一ID
            const pid = this.pid;
            // 
            let lists = [];
            let asyncInfo = this.goodsInfo;
            if (asyncInfo && asyncInfo[pid] && asyncInfo[pid].length > 0 && asyncInfo[pid][0].goodsInfo.length > 0) {
                lists = asyncInfo[pid][0].goodsInfo;
            }
            return lists;
        }
    },

    mounted () {
        // 初始化数据
        this.isDateRes && this.updateComponent();
    }
};
