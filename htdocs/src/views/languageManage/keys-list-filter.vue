<template>
    <!-- 控制栏 -->
    <div class="controller">
        <a-row type="flex" justify="space-between">
            <a-col>

                <a-form :form="form" @submit="handleSearch" layout="inline">
                    <a-form-item label='健值'>
                        <a-input v-decorator="['key']" />
                    </a-form-item>

                    <a-form-item label='中文文案'>
                        <a-input v-decorator="['cn']" />
                    </a-form-item>

                    <a-form-item>
                        <a-button type="primary" style="margin-right: 8px;" html-type="submit">搜索</a-button>
                        <a-button @click="handleReset">重置</a-button>
                    </a-form-item>
                </a-form>
                
            </a-col>
            <a-col class="controller-right">
                <a-button @click="dialogVisible.dialogKeys = true" type="primary">新增健值</a-button>
                <a-button @click="dialogVisible.dialogExport = true">导出语言列表</a-button>
                <a-button @click="dialogVisible.dialogImport = true">导入语言列表</a-button>
            </a-col>
        </a-row>

        <!-- 可选的语种 -->
        <a-row type="flex" justify="start" class="select-code-layer">
            <a-col class="select-code-layer-head">显示语言种类:</a-col>
            <a-col>
                <ul>
                    <li :class="{ 'active': isSelectedAll }" @click="handleSelectCodeALL()">全选</li>
                    <li
                        v-for="item in langList"
                        v-if="item.lang != 'zh'"
                        :class="{ 'active': selectedCode.includes(item.lang) || isSelectedAll }"
                        :key="item.lang"
                        @click="handleSelectCode(item.lang)">
                        {{item.lang_name}}
                    </li>
                </ul>
            </a-col>
        </a-row>

        <!-- 新建键值弹窗 -->
        <dialog-add-keys
            :visible.sync="dialogVisible.dialogKeys"
            :langList="langList"
            @success="handleSearch">
        </dialog-add-keys>

        <!-- 导入文件弹窗 -->
        <dialog-import-translate
            :visible.sync="dialogVisible.dialogImport"
            :langList="langList"
            @success="handleSearch">
        </dialog-import-translate>

        <!-- 导出文件 -->
        <dialog-export
            ref="dialogExport"
            :visible.sync="dialogVisible.dialogExport"
            :langList="langList">
        </dialog-export>

    </div>
</template>

<script>
// 导出文件弹窗
import dialogExport from './dialog-export-keys.vue';
// 导入文件弹窗
import dialogImportTranslate from './dialog-import-translate.vue';
// 新建键值弹窗
import dialogAddKeys from './dialog-add-keys.vue';

export default {
    components: {
        dialogAddKeys,
        dialogImportTranslate,
        dialogExport,
    },
    data () {
        return {
            // 语种列表
            langList: [],
            // 默认全选
            isSelectedAll: true,
            // 选中的语种
            selectedCode: [],
            // 弹窗是否展示
            dialogVisible: {
                dialogKeys: false, // 新建键值弹窗
                dialogImport: false,  // 导入文件的弹窗
                dialogExport: false, // 导出文件弹窗
            },
        };
    },

    beforeCreate () {
        this.form = this.$form.createForm(this);
    },
    
    methods: {

        /**
         * 初始化 select 勾选
         * @param {array} list 语种列表
         */
        initSelect (list) {
            this.langList = list;
            this.selectedCode = this.langList.map(x => x.lang);
            
            // 导出弹窗，够炫所有
            this.$refs.dialogExport.checkAll = true;
            this.$refs.dialogExport.selected = this.langList.map(x => x.lang);
        },

        /**
         * 搜索事件
         */
        handleSearch (e) {
            e && e.preventDefault();
            this.form.validateFields((error, values) => {
                this.$emit('search', values);
            });
        },

        /**
         * 重置搜索条件事件
         */
        handleReset () {
            this.form.resetFields();
            this.$emit('search', { key: '', cn: '' });
        },

        /**
         * 全选事件
         */
        handleSelectCodeALL () {
            this.isSelectedAll = !this.isSelectedAll;
            // 如果取消全选，则清空所有选择项
            this.selectedCode = this.isSelectedAll === false ? [] : this.langList.map(x => x.lang);
            // 回传父组件
            this.$emit('select', this.selectedCode);
        },

        /**
         * 勾选语种事件
         * @param {string} code 语种简码，不传 code 等于选择所有的
         */
        handleSelectCode (lang) {
            if (!lang) return;
            // 判断是否已经勾选了
            if (this.selectedCode.includes(lang) !== true) {
                this.selectedCode.push(lang);
            } else {
                this.selectedCode = this.selectedCode.filter(x => x != lang);
            }
           
            // 判断是否全选状态
            this.isSelectedAll = this.selectedCode.length === this.langList.length;

            // 回传父组件
            this.$emit('select', this.selectedCode);
        }
    }
}
</script>

<style lang="less" scope>

.controller-right {
    line-height: 39px;
    .ant-btn {
        margin-left: 4px;
    }
}

// 语言选择列表
.select-code-layer {
    padding-top: 24px;
    flex-wrap: nowrap;
    &-head {
        flex-shrink: 0;
        width: 90px;
        padding-top: 3px;
    }

    ul {
        padding-left: 0px;
        list-style: none;
        display: flex;
        flex-wrap: wrap;
        li {
            padding: 3px 8px;
            margin: 0px 8px;
            margin-bottom: 10px;
            border-radius: 4px;
            &.active {
                background: #1E9FFF;
                color: #fff;
                &:hover {
                    color: #fff;
                }
            }
            &:hover {
                color: #1E9FFF;
                cursor: pointer;
            }
        }
    }
}

</style>
