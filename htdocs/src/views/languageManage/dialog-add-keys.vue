<template>
    <!-- 新增键值对 -->
    <div class="dialog-add-keys">
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

            <div class="langList">
                <label for=""><span>*</span>应用语言：</label>
                <ul>
                    <li v-for="(item, index) in langList" :key="index">
                        {{ item.lang_name }}
                    </li>
                </ul>
            </div>

            <!-- 表单 -->
            <table class='add-lang-keys-table'>
                <thead>
                    <tr>
                        <th><span>*</span>键值</th>
                        <th><span>*</span>键值对应中文文案</th>
                        <th width="40px"></th>
                    </tr>
                </thead>
                <tbody>
                    <tr v-for="(row, index) in tableDatas" :key="index">
                        <td>
                            <a-input
                                :id="`key_${index}`"
                                v-model="row.key"
                                :class="{ 'in-error': row.key_message }"
                                @change="check_key_formate">
                            </a-input>
                            <p class="error-message">{{ row.key_message }}</p>
                        </td>
                        <td>
                            <a-input
                                :id="`value_${index}`"
                                v-model="row.lang_zh"
                                :class="{ 'in-error': row.zh_message }"
                                @change="check_zh_formate">
                            </a-input>
                            <p class="error-message">{{ row.zh_message }}</p>
                        </td>
                        <td style="vertical-align: middle;">
                            <a-icon v-if="tableDatas.length > 1" type="delete" @click="handleDeleteRow(index)" />
                        </td>
                    </tr>
                    <tr>
                        <td colspan="2" class="table-td-add">
                            <a-icon type="plus" @click="handleAddRow"/>
                        </td>
                        <td></td>
                    </tr>
                </tbody>
            </table>

            <template slot="footer">
                <a-button key="reset" @click="handleReset">重置</a-button>
                <a-button key="back" @click="handleCancel">取消</a-button>
                <a-button key="submit" type="primary" :loading="loading" @click="handleOk">确定</a-button>
            </template>
            
        </a-modal>
    </div>
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
            // 窗口模式，0=新增, 1=编辑
            mode: 0,
            // 请求状态
            loading: false,
            // 表格数据，默认留5行
            tableDatas: [
                { key: '', lang_zh: '', key_message: '', zh_message: '' },
            ]
        };
    },
    computed: {
        // 标题
        dialogTitle () {
            return this.mode === 0 ? '新增键值' : '编辑键值';
        }
    },
    methods: {

        // 检查KEY值对不对，返回提示信息
        check_key_formate (val) {
            const index = val.target.id.split('_')[1];
            const data = this.tableDatas[index].key;
            let message = '';
            // 空的不校验
            if (!data) {
                message = '请输入必填内容项';
            } else {
                if (/^([a-z]|\_)*$/g.test(data) === false) {
                    message = '请输入小写字符和下划线';
                } else if (/^\S{1,20}$/g.test(data) === false) {
                    message = '单个键值请不要超过20个英文字符';
                }
            }
            this.tableDatas[index]['key_message'] = message;
        },

        /**
         * 中文校验，返回提示信息
         * @param {string} param.data 文本框的值
         */
        check_zh_formate (val) {
            // 去除头空格
            const trimStr = (str) => str.replace(/(^\s*)/g, "");
            const index = val.target.id.split('_')[1];
            const data = this.tableDatas[index].lang_zh;
            let message = '';
            if (!data) {
                message = '请输入必填内容项';
            } else {
                if (data.length > 50) {
                    message = '字符长度不超过50个字符';
                }
            };
            this.tableDatas[index]['zh_message'] = message;
            // 去除头空格
            this.tableDatas[index].lang_zh = trimStr(data);;
        },

        /**
         * 重置弹窗
         * @param {Boolean} val true/false
         */
        handleReset () {
            // 重置表单
            this.form.resetFields();
            // 展示弹窗
            this.loading = false;
            // 默认数据
            this.tableDatas = [
                { key: '', lang_zh: '', key_message: '', zh_message: '' },
            ];
        },

        /**
         * 增加行
         */
        handleAddRow () {
            this.tableDatas.push({
                key: '',
                lang_zh: ''
            });
        },

        /**
         * 删除行
         * @param {string} key 健名
         */
        handleDeleteRow (index) {
            this.tableDatas.splice(index, 1);
        },

        /**
         * 确认按钮 新增/编辑
         */
        async handleOk () {
            // 首先校验有没有错误提示信息， 有则不触发提交函数
            const invalid = this.tableDatas.filter((x, index) => {
                this.check_key_formate({ data: x.key, target: { id: `key_${index}` }});
                this.check_zh_formate({ data: x.lang_zh, target: { id: `value_${index}` }});
                return (x.key_message || x.zh_message);
            });
            if (invalid.length > 0) return false;

            // 检查重复键值的
            const replyKeys = this.tableDatas.filter(row => {
                const key = row.key;
                const count = this.tableDatas.filter(x => x.key == key);
                return count.length >= 2;
            });
            if (replyKeys.length >= 1) {
                return this.$message.error('键值不能重复');
            }
            // 检查重复中文的
            const replyValue = this.tableDatas.filter(row => {
                const value = row.lang_zh;
                const count = this.tableDatas.filter(x => x.lang_zh == value);
                return count.length >= 2;
            });
            if (replyValue.length >= 1) {
                return this.$message.error('键值对应的中文文案不能重复');
            }


            // 所有的 key, [array]
            const keys = this.tableDatas.map(x => x.key);
            // 所有的中文翻译 [array]
            const lang_zh = this.tableDatas.map(x => x.lang_zh);

            // 发请求
            this.loading = true;
            const res = await this.$api.addLangKeys({
                key: keys.join(','),
                lang_zh: lang_zh.join(',')
            });
            this.loading = false;
            if (res.code === 0) {
                this.$emit('success'); 
                this.$emit('update:visible', false);
                this.handleReset();
            }
        },

        /**
         * 取消按钮
         */
        handleCancel () {
            this.$emit('update:visible', false);
        }
    },

    beforeCreate () {
        this.form = this.$form.createForm(this);
    }
}
</script>

<style lang="less" scoped>

// 应用语言：
.langList {
    display: flex;
    label {
        width: 80px;
        flex-shrink: 0;
        span  {
            color: red;
        }
    }
    ul {
        list-style: none;
        padding: 0px;
        margin: 0px;
        display: flex;
        flex-wrap: wrap;
        li {
            height: 18px;
            line-height: 18px;
            margin-bottom: 14px;
            margin-right: 24px; 
        }
    }
}

// 键值表格
.add-lang-keys-table {
    width: 100%;
    border-collapse: collapse;
    th, td {
        border: solid 1px rgba(237,240,242,1);
        padding: 11px 30px;
    }
    thead {
        th {
            background:rgba(243,245,247,1);
            span {
                color: red;
            }
        }
    }
    tbody {
        td {
            vertical-align: top;
        }
    }
    .table-td-add {
        text-align: center;
    }
}

.ant-input.in-error {
    border-color: red;
    outline: none !important;
    box-shadow: none !important;
}

// 错误信息
.error-message {
    color: red;
    margin-bottom: 0px;
    margin-top: 5px;
}
</style>
