<template>
    <a :href="urlToApp" :target="target" :class="className" :data-logsss-event-value="analytics">
        <slot></slot>
    </a>
</template>

<script>
/**
 * D网埋点参数文档
 * https://docs.google.com/spreadsheets/d/19DKk4LFcdCPjRSNZWLYPGRdx9NujeEdOy2pMiGLattI/edit#gid=418695093
 */
export default {
    name: 'geshop-analytics-href',
    props: {
        // 是否可用
        disabled: {
            type: Boolean,
            default: false
        },
        // 超链接
        href: {
            type: String,
            default: ''
        },
        // 打开页面方式
        target: {
            type: String,
            default: '_self'
        },
        // 商品的sku, 例如： 32323123123
        sku: {
            type: String,
            default: ''
        },
        // 埋点-分类
        cate: {
            type: String,
            default: ''
        },
        // 埋点-仓库
        warehouse: {
            type: String,
            default: ''
        },
        // 埋点-商品ID
        goods_id: {
            type: [String, Number],
            default: ''
        },
        // 埋点-坑位顺序
        index: {
            type: Number,
            default: 0
        },
        // 埋点：商品详情
        item: {
            type: [Object, Number],
            default: function () {
                return {};
            }
        },
        // 埋点-页面模块
        pm: {
            default: 'mp'
        },
        // DL埋点，推荐位，例如：T_1
        mrlc: {
            default: ''
        },
        // SKU状态, 1/2/3
        zt: {
            default: 0
        },
        // 关闭埋点
        close_deeplink: {
            type: Boolean,
            default: false
        },
        // 埋点事件类型
        x: {
            type: String,
            default: 'sku'
        }
    },
    data () {
        return {
            className: 'logsss_event js_logsss_click_delegate',
            // 埋点用商品参数
            itemObj: {}
        };
    },
    mounted () {
        this.initData();
    },
    methods: {
        /**
         * 兼容对象传参埋点用商品参数
         */
        initData () {
            this.itemObj = Object.assign({}, {
                href: this.href || this.item.url_title,
                sku: this.item.goods_sn || this.sku,
                cate: this.item.cateid || this.cate,
                warehouse: this.item.warehousecode || this.warehouse,
                goods_id: this.item.goods_id || this.goods_id
            });
        }
    },
    computed: {
        // 统计代码
        analytics () {
            const params = {
                pm: this.pm,
                p: this.pid ? `gs-${this.$root.pageId}-${this.pid}` : `gs-${this.$root.pageId}`,
                x: this.x,
                ubcta: {
                    cpID: `${this.$root.pageInstanceId}`,
                    cpnum: `${this.$root.compKey}`,
                    cplocation: `${this.$root.uiIndex}`,
                    sku: `${this.itemObj.sku}`,
                    cporder: `${this.$root.layoutIndex}`,
                    rank: `${this.index}`,
                    mrlc: this.mrlc
                },
                skuinfo: {
                    sku: this.itemObj.sku,
                    pam: 1,
                    pc: this.itemObj.cate,
                    k: this.itemObj.warehouse,
                    zt: this.zt
                }
            };
            return JSON.stringify(params).replace(/"/g, '\'');
        },
        urlToApp () {
            if (this.disabled === true) {
                return 'javascript:;';
            } else {
                if (window.GESHOP_PLATFORM === 'pc' || window.GESHOP_SITECODE === 'dl-web' || window.GESHOP_SITECODE === 'dl-app' || this.close_deeplink) {
                    return this.itemObj.href || this.href;
                } else {
                    return geshopUrlToApp(this.itemObj.href || this.href, this.itemObj.goods_id);
                }
            }
        },
        // 返回GeshopComponent组件pid
        pid () {
            return this.$root.$children.length > 0 && this.$root.$children[0].pid;
        }
    },
    watch: {
        item () {
            this.initData();
        }
    }
};
</script>
