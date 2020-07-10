<template>
    <el-dialog title="页面模板" class="geshop-template-pic" :visible.sync="viewModel.visible" @close="viewModelClose" width="100%" :fullscreen="true">
        <el-row v-loading="oHomeData.pageLoading">
            <el-col class="imgPreview text-center" style="height:100%;">
                <iframe frameborder="0" :src="viewModel.src" class="iframePreview" style="width:100%;height:100%;"></iframe>
            </el-col>
        </el-row>
    </el-dialog>
</template>

<script>
    export default {
        name: 'viewModel',
        props: {
            viewModel: {
                type: Object
            },
            oHomeData: {
                type: Object
            }
        },
        methods: {
            viewModelClose () {
                this.viewModel.visible = false
                this.viewModel.html = ''
                this.viewModel.src = ''
            }
        }
    }
</script>

<style scoped>

</style>