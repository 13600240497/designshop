<template>
    <el-dialog :visible.sync="oViewQrCode.isDetailActive" width="360px" @close='detailClose'>
        <el-card>
            <div slot="header" class="text-center">{{ oViewQrCode.currentActivityRow.pageLanguages[0].title }}</div>
            <div>
                <el-row v-if="Boolean(oViewQrCode.currentActivityRow.qrcode) || Boolean(oViewQrCode.currentActivityRow.preview)">

                    <el-col :span="24">
                        <h3>活动预览地址：</h3>
                    </el-col>

                    <el-col :span="24" class="text-center" v-if="Boolean(oViewQrCode.currentActivityRow.qrcode)">
                        <img alt="二维码" :src="oViewQrCode.currentActivityRow.qrcode" width="120">
                    </el-col>

                    <el-col :span="24" class="text-center" v-if="Boolean(oViewQrCode.currentActivityRow.preview)">
                        <a :href="oViewQrCode.currentActivityRow.preview" class="index-detail-link">{{ oViewQrCode.currentActivityRow.title }}</a>
                    </el-col>

                </el-row>
            </div>
        </el-card>
    </el-dialog>
</template>

<script>
    export default {
        name: 'viewQrCode',
        props: {
            oViewQrCode: {
                type: Object
            }
        },
        methods: {
            detailClose () {
                this.oViewQrCode.isDetailActive = false
            }
        }
    }
</script>

<style scoped>

</style>