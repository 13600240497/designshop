<template>
    <!-- zf 站点增加埋点 -->
    <a href="javascript:;" :class="['quick_view',className]" :data-logsss-event-value="analytics"
       @click="boxQuickView">
        <slot></slot>
    </a>
</template>

<script>
export default {
    name: 'geshop-button-quick-view',
    props: {
        url_quick: {
            type: String,
            default: ''
        },
        width: {
            type: [String, Number],
            default: 1080
        },
        height: {
            type: [String, Number],
            default: 597
        },
        // 埋点：商品详情
        item: {
            type: [Object, Number],
            default: {}
        },
        // 商品index 埋点-坑位顺序
        index: {
            type: [Number, String],
            default: 0
        },
        // SKU状态, 1/2/3
        zt: {
            default: 0
        },
        // 埋点-页面模块 mp商品位
        pm: {
            default: 'mp'
        },
        // 埋点事件类型
        x: {
            type: String,
            default: 'addtobag'
        }
    },
    data () {
        return {
            className: 'logsss_event js_logsss_click_delegate'
        };
    },
    computed: {
        // 返回GeshopComponent组件pid
        pid () {
            return this.$root.$children.length > 0 && this.$root.$children[0].pid;
        },
        glb_p () {
            return `gs-${this.$root.pageId}-${this.pid}`;
        },
        isZF () {
            return window.GESHOP_SITECODE.indexOf('zf') > -1;
        },
        // 统计代码
        analytics () {
            const params = {
                pm: this.pm,
                p: this.glb_p,
                x: this.x,
                ubcta: {
                    cpID: `${this.$root.pageInstanceId}`,
                    cpnum: `${this.$root.compKey}`,
                    cplocation: `${this.$root.uiIndex}`,
                    cporder: `${this.$root.layoutIndex}`,
                    sku: `${this.item.goods_sn}`,
                    rank: `${this.index}`,
                    price: `${this.item.shop_price}`
                },
                skuinfo: {
                    sku: this.item.goods_sn,
                    pam: 1,
                    pc: this.item.cateid,
                    k: this.item.warehouse,
                    zt: this.zt
                }
            };
            const str = JSON.stringify(params).replace(/"/g, '\'');
            return str;
        }
    },
    methods: {
        boxQuickView () {
            if (GEShopSiteCommon) {
                window.sessionStorage.setItem('logsss-categoryid', this.glb_p);
                GEShopSiteCommon.dialog.iframe(this.url_quick, this.width, this.height, true);
            }
        }
    }
};
</script>
