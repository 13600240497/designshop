<template>
    <div v-if="pageTemplateData.isShow">
        <design-dialog
            :width="pageTemplateData.width"
            :title="pageTemplateData.title"
            :confirmLoading="loading"
            @isOk="handleOk"
            @isCancel="handleCancel"
            :visible="pageTemplateData.visible">

            <!-- 表单 -->
            <a-form :layout="formLayout" :form="form" class="pageForm">
                <a-form-item
                    label='模板分类'>
                    <a-select name='model_type'
                              :value="itemsType"
                              @change="handleChange">
                        <a-select-option v-for="(item, index) in modelTypeList" :key="index" :value="item.type">
                            {{ item.name }}
                        </a-select-option>
                    </a-select>
                    <span class="tips">注:  私有模板仅自己可见</span>
                </a-form-item>

                <a-form-item
                    label='模板名称'>
                    <a-input
                        v-decorator="[
                        'name',
                            {
                                rules: [
                                    { required: true, message: '请输入模板名称' }
                                ]
                            }
                        ]"
                    />
                </a-form-item>

                <a-form-item
                    label='图片链接'>
                    <a-input :disabled="true" name="model_pic" placeholder="不可编辑，请上传图片！" />
                </a-form-item>

            </a-form>


        </design-dialog>
    </div>
</template>

<script>

export default {
    name: 'component-pageTemplate',
    props: {
        pageTemplateData: {
            type: Object
        }
    },
    data () {
        return {
            formLayout: 'vertical',
            modelTypeList: [
                {
                    name: '公有模板',
                    type: 1
                },
                {
                    name: '私有模板',
                    type: 2
                },
            ],
            loading: false,
            spinning: true, // 预览图加载中
            preview_img: '', // 预览图
            itemsType: 1 // 模板类型, 默认为公有模板
        }
    },
    beforeCreate () {
        this.form = this.$form.createForm(this);
    },
    mounted () {

    },
    methods: {
        handleOk () {
            this.form.validateFields(async (err, val) => {
                if (err) {
                    return;
                }
                this.loading = true;
                const info = this.$store.state.page.info;

                const request = {
                    pageId: info.page_id,
                    lang: info.lang,
                    name: val.name,
                    pic: this.preview_img,
                    site_code: info.site_code,
                    type: this.itemsType
                };
                const res = await this.$api.ZF_getPageTplAdd(request);
                this.loading = false;

                if (res.code === 0) {
                    this.$message.success('页面模板生成成功!');
                    this.pageTemplateData.isShow = false;
                    this.pageTemplateData.visible = false;
                    this.handleResetForm();
                }
            });
        },
        handleCancel () {
            this.pageTemplateData.isShow = false;
            this.pageTemplateData.visible = false;
            this.handleResetForm();
        },

        handleResetForm () {
            this.loading = false;
            this.itemsType = 1;
            // 重置表单
            this.form.resetFields();
        },

        handleChange (val) {
            this.itemsType = val;
        }
    }
}
</script>

<style lang="less">
.pageForm {
    .ant-input {
        height: 40px;
    }
    .ant-form-item {
        margin-bottom: 8px !important;
    }
    .ant-select {
        width: 320px;
        vertical-align: bottom;
    }
    .ant-select-selection--single{
        height: 40px;
    }
    .ant-select-selection__rendered {
        line-height: 40px;
    }
    .tips {
        color: #AEB1B3;
        margin-left: 16px;
    }
    .image {
        width: 400px;
        height: 400px;
        img {
            width: auto;
            height: auto;
            max-width: 100%;
            max-height: 100%;
        }
    }
}
</style>
