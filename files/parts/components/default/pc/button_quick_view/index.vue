<template>
    <a href="javascript:;" class="quick_view" @click="boxQuickView">
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
            }
        },
        data() {
            return {
            }
        },
        methods: {
            boxQuickView() {
                const param = {
                    url_quick: this.url_quick
                }
                this.$emit('quickView', param);
            }
        }
    }
</script>