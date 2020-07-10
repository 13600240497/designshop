<template>
    <a-modal
        class="geshop-modal"
        width="900px"
        :title="dialogTitle"
        :visible="visible"
        :confirmLoading="loading"
        okText="确定"
        cancelText="取消"
        @ok="handleOk"
        @cancel="handleCancel">

        <!-- 表单 -->
        <a-form layout='horizontal' :form="form">

            <a-form-item
                label='语言名称'
                :label-col="labelCol"
                :wrapper-col="wrapperCol">
                <a-input
                    v-decorator="[
                        'name',
                        {
                            rules: [
                                { required: true, message: '请输入必填内容项' },
                                { pattern: /^[\u4e00-\u9fa5]*$/, message: '请输入中文字符' },
                                { max: 10, message: '单个语言名称请不要超过10个中文字符' }
                            ]
                        }
                    ]"
                />
            </a-form-item>

            <a-form-item
                label='语言简码'
                :label-col="labelCol"
                :wrapper-col="wrapperCol">
                <a-input
                    v-decorator="[
                        'code',
                            {
                                rules: [
                                    { required: true, message: '请输入必填内容项' },
                                    { pattern: /^([a-z]|-)*$/g, message: '请输入小写英文字符或中划线' },
                                    { max: 20, message: '单个语言简码请不要超过20个小写英文字符或中划线' }
                                ]
                            }
                        ]"
                    />
            </a-form-item>
        </a-form>
    </a-modal>
</template>

<script>
export default {
    data () {
        return {
            // 窗口模式，0=新增, 1=编辑
            mode: 0,
            // 是否展示弹窗
            visible: false,
            // 请求状态
            loading: false,
            // 表单
            // form: this.$form.createForm(this),
            labelCol: { span: 3 },
            wrapperCol: { span: 21 },
        };
    },
    beforeCreate () {
        this.form = this.$form.createForm(this);
    },
    computed: {
        // 标题
        dialogTitle () {
            return this.mode === 0 ? '新增语言' : '编辑语言';
        }
    },
    methods: {
        /**
         * 展示或者关闭弹窗
         * @param {Boolean} val true/false
         */
        show (val = true) {
            // 重置表单
            this.form.resetFields();
            // 展示弹窗
            this.loading = false;
            this.visible = val;
        },

        /**
         * 确认按钮 新增/编辑
         */
        handleOk () {
            this.form.validateFields(async (err, values) => {
                if (err) return;
                this.loading = true;
                const request = {
                    site_code: this.$store.state.siteCode,
                    name: values.name,
                    code: values.code,
                };
                const res = await this.$api.postAddLang(request);
                this.loading = false;
                if (res.code === 0) {
                    this.visible = false;
                    this.$emit('success');
                }
            });
        },

        /**
         * 取消按钮
         */
        handleCancel () {
            this.visible = false;
        }
    }
}
</script>

<style lang="less">

</style>
