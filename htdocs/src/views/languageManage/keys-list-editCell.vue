<template>
    <div class="geshop-edit-cell" @dblclick="handleDbClick" style="">
        <!-- 编辑状态 -->
        <template v-if="is_edit">

            <a-input-group compact :style="{ 'width': width }">
                <a-input v-model="value" />
                <a-button type="primary" @click="handleSave">
                    <a-icon type="check" />
                </a-button>
                <a-button type="danger" @click="handleCancel">
                    <a-icon type="close" />
                </a-button>
            </a-input-group>
                
        </template>

        <!-- 预览状态 -->
        <template v-else>
            <div style="min-width: 100px">{{ value || '-' }}</div>
        </template>
    </div>
</template>

<script>
export default {
    props: {
        // 当前行
        row: {
            tpye: Object,
            required: true,
        },
        // 当前文案
        text: {
            type: String,
            default: ''
        },
        // 当前语种
        lang: {
            type: String,
        },
        // 是否编辑状态
        edit: {
            type: Boolean,
            default: false,
        }
    },
    data () {
        return {
            value: this.text || '',
            width: '100%', // 单元格宽度
        };
    },
    computed: {
        // 告诉父组件，当前 KEY 和 lang 在编辑中
        is_edit: {
            get () {
                return this.edit;
            },
            set (val) {
                if (val) {
                    this.$emit('onEdit', this.row.key, this.lang);
                } else {
                    this.$emit('onEdit', '', '');
                }
            }
        }
    },
    watch: {
        text (val) {
            this.value = val;
        }
    },
    methods: {
        // 开启编辑状态
        handleDbClick (e) {
            if (this.is_edit === true) return false;
            // 切换编辑状态
            const width = e.target.clientWidth + 'px';
            this.width = width
            this.is_edit = true;
        },

        /**
         * 编辑保存
         */
        async handleSave () {
            // 去除头尾空格
            const trimStr = (str) => str.replace(/(^\s*)|(\s*$)/g, "");
            const data = trimStr(this.value);

            // 默认有值，后面人为清空了。变更为默认值
            if (data === '' && this.text !== '') {
                this.value = this.text;
                this.is_edit = false;
                return;
            }
            // 2个值相等的
            if (data === this.text) {
                this.is_edit = false;
                return false;
            }
            this.loading = true;
            const res = await this.$api.editLangKeys({
                key: this.row.key,
                lang: this.lang,
                value: this.value,
                site_code: this.$store.state.siteCode
            });
            this.loading = false;
            // 保存成功回调
            if (res.code === 0) {
                // this.row[this.code] = this.value;
                this.is_edit = false;
                this.$emit('onSuccess');
            }
        },

        /**
         * 取消
         */
        handleCancel () {
            this.is_edit = false;
            this.value = this.text;
        }
    }
}
</script>

<style lang="less">
.geshop-edit-cell {
    height: 32px;

    .ant-input-group {
        display: flex;
        input {
            width: 100%;
            flex-shrink: 1;
        }
        button {
            flex-shrink: 0;
            padding: 0 10px;
        }
    }
}
</style>
