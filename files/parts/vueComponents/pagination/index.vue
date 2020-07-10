<!-- 分页组件 -->
<template>
    <component :is="components[site_code]" :visible="visible" :totalCount="totalCount"
               :currentPage="currentPage"
               :pageCount="pageCount"
               :pageSize="pageSize"
               @change="handlePageChange"></component>
</template>

<script>
import zfpc from './zf-pc.vue';

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
            components: {
                'zf-pc': zfpc,
                'rg-pc': zfpc,
                'dl-web': zfpc,
                'dl-app': zfpc
            }
        };
    },
    computed: {
        site_code () {
            return window.GESHOP_SITECODE;
        }
    },
    methods: {
        handlePageChange (value) {
            this.$emit('change', value);
        }
    }
};
</script>
