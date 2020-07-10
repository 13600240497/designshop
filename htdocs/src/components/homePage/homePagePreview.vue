<template>
    <el-dialog title="首页预览地址" :visible.sync="oHomePagePreview.dialogPreviewLinksVisible" class="geshop-index-preview-address">
        <el-row>
            <el-button v-for="item in oHomePagePreview.previewLinks"
                       type="primary"
                       :key="item.lang"
                       @click="redirect(item.page_url)">{{ item.lang_name }}</el-button>
        </el-row>
    </el-dialog>
</template>

<script>
    export default {
        name: 'homePagePreview',
        props: {
            oHomePagePreview: {
                type: Object
            }
        },
        methods: {
            redirect (url) {
                window.open(url)
            }
        }
    }
</script>

<style scoped>

</style>