<!-- 选择预览页面 -->
<template>
    <el-dialog title="预览" custom-class="geshop-preview-dialog-container" class="geshop-view-link-page geshop-activity-preview-address"
               :visible.sync="data.dialogVisible">
        <el-row>
            <el-button v-for="(item,index) in data.list" type="primary" :key="index" @click="redirect(item.preview_url)">{{
                item.name }}
            </el-button>
        </el-row>
        <el-form>
            <el-form-item class="geshop-new-activities-btn">
                <el-button @click="handleClose" size="small">取消</el-button>
                <el-button type="primary" @click="handleClose" size="small">确定</el-button>
            </el-form-item>
        </el-form>
    </el-dialog>
</template>

<script>
export default {
    props: {
        data: {
            type: Object,
            required: true
        }
    },
    data () {
        return {};
    },
    created () {

    },
    methods: {
        /**
         * 链接跳转
         * @param { String } preview_url - 预览链接
         */
        redirect (preview_url) {
            window.open(preview_url);
        },
        handleClose(){
            this.$emit('handleClose')
        }
    }
};
</script>

