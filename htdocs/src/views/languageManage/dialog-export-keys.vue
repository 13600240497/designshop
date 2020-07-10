<template>
    <a-modal
        class="geshop-modal"
        width="900px"
        title="导出语言列表"
        :visible="visible"
        :confirmLoading="loading"
        okText="确定"
        cancelText="取消"
        @ok="handleOk"
        @cancel="handleCancel">

        <div class="langList">
            <div class="langList-head">
                需要导出的语言
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

        <!-- 模拟表单 -->
        <form
            ref="simForm"
            style="display:none;"
            method="POST"
            action="/base/language-data/export-package">
            <input name="lang" :value="selected.join(',')">
            <input type="hidden" name="site_code" :value="$store.state.siteCode">
        </form>

    </a-modal>
</template>

<script>
export default {
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
            loading: false, // 请求状态
            selected: [], // 当前选中纪录
            indeterminate: false, // 是否半选
            checkAll: false, // 是否全选
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
         * 确认按钮 新增/编辑
         */
        async handleOk () {
            if (this.selected.length <= 0) {
                this.$message.error('请选择语言');
                return false;
            }
            this.$refs.simForm.submit();
        },

        /**
         * 取消按钮
         */
        handleCancel () {
            this.$emit('update:visible', false);
        }
    }
}
</script>

<style lang="less" scoped>

// 语言列表
.langList {
    display: flex;
    
    .langList-head {
        flex-shrink: 0;
        width: 86 + 91px;
        .ant-checkbox-wrapper {
            margin-right: 0px;
            margin-left: 8px;
            margin-bottom: 0px;
        }
    }

    .ant-checkbox-wrapper {
        margin-bottom: 12px;
    }
}

</style>
