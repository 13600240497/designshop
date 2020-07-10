<template>
    <div>
        <a-modal
            class="geshop-modal geshop-modal-import-languages"
            width="900px"
            title="导入语言列表"
            :visible="visible"
            :confirmLoading="uploading"
            okText="确定"
            cancelText="取消"
            @ok="handleOk"
            @cancel="handleCancel">


            <!-- 是否覆盖 -->
            <a-row class="upload-layer" type="flex">
                <a-col style="margin-bottom: 14px; width: 104px; margin-right: 16px;">重复内容是否覆盖</a-col>
                <a-col>
                    <a-radio-group v-model="is_cover" name="is_cover" :defaultValue="1">
                        <a-radio :value="1">是</a-radio>
                        <a-radio :value="0">否</a-radio>
                    </a-radio-group>
                </a-col>
            </a-row>

            <!-- 语言列表 -->
            <div class="langList">
                <div class="langList-head">
                    <span>*</span>
                    需要导入的语言
                    <a-checkbox
                        :indeterminate="indeterminate"
                        @change="onCheckAllChange"
                        :checked="checkAll" >
                        全选
                    </a-checkbox>
                </div>

                <a-checkbox-group :value="selected" @change="onChange">
                    <a-checkbox
                        v-for="item in langList"
                        :value="item.lang"
                        :key="item.lang">
                        {{item.lang_name}}
                    </a-checkbox>
                </a-checkbox-group>
            </div>

            <!-- 上传文件 -->
            <a-row class="upload-layer" type="flex">
                <a-col style="width: 70px; line-height: 32px;"><span style="color: red;">*</span>上传文件</a-col>
                <a-col style="width: 782px;">
                    <a-input v-model="fileName" :disabled="true">
                        <a-upload
                            slot="addonAfter"
                            style="width: 80px"
                            accept=".xlsx, .csv, .xls"
                            :fileList="fileList"
                            :showUploadList="false"
                            :remove="handleRemove"
                            :beforeUpload="beforeUpload">
                            <a-button type="primary">选取文件</a-button>
                        </a-upload>
                    </a-input>
                    <p>注：只支持excel格式的文件，如无文件格式，请先下载导入文件模板：<a href="/resources/excelTemplate/多语言导入模板.xlsx" target="_BLANK">多语言导入模板.xlsx</a></p>
                </a-col>
            </a-row>

        </a-modal>

        <!-- 导入成功 -->
        <dialog-import-detail
            ref="dialogDetail"
            @reset="handleReset">
        </dialog-import-detail>
    </div>

</template>

<script>
import dialogImportDetail from './dialog-import-detail.vue';

export default {
    components: {
        dialogImportDetail,
    },
    props: {
        // 是否展示弹窗
        visible: {
            type: Boolean,
            default: false
        },
        // 所有语种列表
        langList: {
            type: Array,
            default: []
        }
    },
    data () {
        return {
            selected: [], // 当前选中纪录
            indeterminate: false, // 是否半选
            checkAll: false, // 是否全选
            fileList: [],
            fileName: '', // 文件名
            uploading: false, // 上传文件中,
            is_cover: 1, // 是否覆盖重复的
        };
    },
    methods: {
        /**
         * checkbox 变更
         */
        onChange (checkedList) {
            this.selected = [...checkedList];
            this.indeterminate = !!checkedList.length && (checkedList.length < this.langList.length)
            this.checkAll = checkedList.length === this.langList.length
        },

        /**
         * 全选
         */
        onCheckAllChange (e) {
            Object.assign(this, {
                checkedList: e.target.checked ? this.langList.map(x => x.lang) : [],
                indeterminate: false,
                checkAll: e.target.checked,
            });
            this.selected = e.target.checked ? this.langList.map(x => x.lang) : [];
        },

        /**
         * 删除文件
         */
        handleRemove () {

        },

        // 选择文件
        beforeUpload (file) {
            this.fileList = [];
            this.fileList.push(file);
            this.fileName = this.fileList[0].name;
            return false;
        },

        /**
         * 确认按钮 上传文件
         */
        async handleOk () {
            if (this.selected.length === 0) {
                return this.$message.error('请选择需要导入的语言');
            }
            const { fileList } = this;
            if (this.fileList.length === 0) {
                return this.$message.error('请选择需要导入的文件');
            }
            // 拼装数据
            this.uploading = true;
            const formData = new FormData();
            formData.append('files', fileList[0]);
            formData.append('lang', this.selected.join(','));
            formData.append('site_code', this.$store.state.siteCode);
            formData.append('is_cover', this.is_cover);
            this.$api.importLangKeys(formData).then(res => {
                this.uploading = false;
                if (res.code === 0) {
                    // 展示结果弹窗
                    this.$refs.dialogDetail.show(res);
                    // 重置表单
                    this.handleReset();
                    this.handleCancel();
                }
            }).catch(res => {
                this.uploading = false;
            });
        },

        /**
         * 取消按钮
         */
        handleCancel () {
            this.$emit('update:visible', false);
        },

        /**
         * 重置表单，继续上传
         */
        handleReset () {
            this.$emit('update:visible', true);
            this.fileName = '';
            this.fileList = [];
            this.selected = [];
            this.indeterminate = false;
            this.checkAll = false;
        }
    }
}
</script>

<style lang="less">
.geshop-modal-import-languages {

    // 语言列表
    .langList {
        display: flex;
        
        .langList-head {
            flex-shrink: 0;
            width: 195px;
            padding-right: 24px;
            > span {
                color: red;
            }
            .ant-checkbox-wrapper {
                flex-shrink: 1;
                margin-right: 0px;
                margin-left: 14px;
                margin-bottom: 0px;
            }
        }
        .ant-checkbox-group {
            width: 100%;
            flex-shrink: 1;
        }
        .ant-checkbox-wrapper {
            margin-bottom: 12px;
        }
    }

    // 上传区域
    .upload-layer {
        p {
            font-size: 12px;
            margin-bottom: 0px;
            margin-top: 8px;
            color: #6B7075;
            a {
                color: #1E9FFF;
            }
        }
        .ant-input-group-addon {
            padding: 0px;
            border: none;
            button {
                border-radius: 0px 4px 4px 0px;
                border-left: none;
                font-size: 13px;
            }
        }
    }

}


</style>
