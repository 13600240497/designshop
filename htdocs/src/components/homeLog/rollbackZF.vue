<template>
    <el-dialog title="回退首页" :visible.sync="visible" class="geshop-new-index rollback-index" @close="closeRollBack">
        <el-form class="rollback-zf">
            <el-form-item label="回退到的版本" :rules="{required:true}">
                <el-select @change="handleVersionChange" v-model="dialogInfo.version_select" value-key="log_id">
                    <el-option v-for="(item,index) in dataLists" :key="index" :label="item.version"
                               :value="item"></el-option>
                </el-select>
                <el-button style="margin-left:20px;" @click="handlePreview">预览</el-button>
            </el-form-item>
            <el-form-item>
                <div>注：执行此功能后，此渠道、此语言页面的线上首页将回至此条信息对应的”线上页面“，是否确认执行？</div>
            </el-form-item>
            <el-form-item class="new-index-btns">
                <el-button @click="closeRollBack" size="small">取消</el-button>
                <el-button type="primary" @click="submitForm($event,dialogInfo.version_select)" size="small"
                           :loading="loading">确定
                </el-button>
            </el-form-item>
        </el-form>
    </el-dialog>
</template>

<script>
export default {
    data () {
        return {
            dialogInfo: {
                title: '',
                version_select: [{ name: '0', value: '请选择' }],
                visible: false
            },
            submitLoading: false
        };
    },
    props: {
        data: {
            type: Array,
            default: [{ log_id: '0000', page_url: '请选择', version: '0000' }]
        },
        visible: {
            type: Boolean,
            default: false
        },
        loading:{
            type: Boolean,
            default: false
        }
    },
    computed: {
        dataLists () {
            return this.data.length > 0 ? this.data : [{ name: '0', value: '请选择' }];
        },

    },
    watch: {
      visible:function(val){
          if(val){
              this.dialogInfo.version_select = this.dataLists[0];
          }
      }
    },
    created () {

    },
    mounted () {
        this.dialogInfo.version_select = this.dataLists[0];
    },
    methods: {

        /**
         * 触发关闭弹窗
         * @param event
         */
        closeRollBack (event) {
            this.dialogInfo.version_select = { name: '0', value: '请选择' };
            this.$emit('closeRollZF', event);
        },


        /**
         * 触发版本选中
         * @param val 选择的版本key
         */
        handleVersionChange (val) {

        },

        /**
         * 触发版本预览
         */
        handlePreview () {
            let url = this.dialogInfo.version_select.page_url;
            if (!url) {
                this.$message.error('预览页不存在');
                return false;
            }
            window.open(this.dialogInfo.version_select.page_url);
        },

        /**
         * 触发版本选择提交
         * @param event
         */
        submitForm (event,params) {
            this.$emit('rollZFCall', event, params);
        }
    }
};
</script>
<style lang="less" scoped>
    .rollback-zf {
        margin-top: 20px;
    }
</style>
<style lang="less">
    .rollback-index {
        .el-dialog {
            min-height: auto;
        }
    }
</style>

