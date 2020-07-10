<template>
    <a :href="urlToApp" :target="target" class="logsss_event" :data-logsss-event-value="analytics">
        <slot></slot>
    </a>
</template>

<script>
export default {
    name: 'geshop-analytics-href',
    props: {
        disabled: {
            type: Boolean,
            default: false,
        },
        href: {
            type: String,
            default: ''
        },
        target: {
            type: String,
            default: '_self'
        },
        sku: {
            type: String,
        },
        cate: {
            type: String,
        },
        warehouse: {
            type: String,
        },
        goods_id: {
            type: String,
        },
        index: {
            type: Number,
        },   
    },
    data() {
      return {
      }
    },
    computed: {
        // 统计代码
        analytics() {
            const params = {
                pm: `mp`,
                p: `p-${this.$root.pageId}`,
                ubcta: {
                    cpID: `${this.$root.pageInstanceId}`,
                    cpnum: `${this.$root.compKey}`,
                    cplocation: `${this.$root.uiIndex}`,
                    sku: `${this.sku}`,
                    cporder: `${this.$root.layoutIndex}`,
                    rank: `${this.index}`,
                },
                skuinfo: {
                    sku: `${this.sku}`,
                    pam: '0',
                    pc: `${this.cate}`,
                    k: `${this.warehouse}`,
                }
            }
            return JSON.stringify(params);
        },
        urlToApp() {
            if (this.disabled == true) {
                return 'javascript:;'
            } else {
                return  geshopUrlToApp(this.href, this.goods_id)
            }
        }
    },
}
</script>
