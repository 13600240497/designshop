<template>
    <div>
        <el-checkbox
            style="margin-right:10px;display:inline-block; vertical-align: top;"
            v-model="data.check_all"
            :indeterminate="data.indeterminate"
            @change="handle_select_all">
            全选
        </el-checkbox>

        <el-checkbox-group
            class="lang-checkbox-group"
            v-model="data.value"
            @change="handle_select_lang">
            <span
                v-for="(item, index) in list.langs"
                :key="index"
                class="checkbox-item">
                <el-checkbox
                    :label="item.lang || item.code"
                    :key="item.lang || item.code"
                    :disabled="disabled.indexOf(item.code) !== -1">
                    {{ item.name }}
                    <template v-if="item.is_default == 1">
                        <span class="default">(默认)</span>
                    </template>
                </el-checkbox>
            </span>
        </el-checkbox-group>
    </div>
</template>

<script>

/**
 * 获取当前渠道的默认语言编码
 * @param {Array} langs
 * @returns {String} 返回语言编码
 */
const get_default_lang_code = (langs) => {
    const res = langs.filter(x => x.is_default == 1);
    return res.length > 0 ? res[0].code : '';
}

/**
 * 选择前，和选择后的数据交叉对比，获取点击的那个 checkbox 的 code
 * @param {Array} a 选择前的数据
 * @param {Array} b 选择后的数据 
 */
const get_handle_select_lang_code = (a, b) => {
    let c, d;
    // 判断[勾选/去除], c.len > d.len
    if (a.length > b.length) {
        c = a;
        d = b;
    } else {
        c = b;
        d = a;
    }
    // 过滤
    const res = [];
    c.map(x => {
        if (!d.includes(x)) {
            res.push(x);
        }
    });
    if (res.length > 0 && res.length === 1) {
        return res[0];
    } else {
        return false;
    }
}

/**
 * 对象转换为数组
 * @description 
 * 1. 因为传入的 lang_list 是 object 类型（无序），需要转换为有序列表（ARRAY）
 * 2. 进行排序，默认语言在最前面
 * @param {object} origin 数据源
 */
const convert_object_to_array = (origin) => {
    const arr = [];
    Object.keys(origin).map(key => {
        arr.push(origin[key]);
    });
    return arr.sort((a, b) => b.is_default - a.is_default);
}

/**
 * 渠道语言选择组件
 * @callback onCheck 语言选择的回调
 * @callback onCheckAll 语言全选的回调
 */
export default {
    props: {
        // 当前端
        platform: {
            required: true,
            type: String,
        },
        // 当前渠道编码
        pipeline: {
            required: true
        },
        // 全选状态
        check_all: {
            required: true,
            type: Boolean
        },
        // 半选状态
        indeterminate: {
            required: true,
            type: Boolean
        },
        // 选中的值
        value: {
            required: true,
        },
        // 可选择的语言列表
        lang_list: {
            required: true,
            type: Object,
        },
        // 禁止选中的值，例如：['en', 'fr']
        disabled: {
            type: Array,
            default () {
                return []
            }
        }
    },

    data () {
        return {
            data: {
                check_all: false, // 全选
                indeterminate: false, // 半选
                value: [], // 选中的语言code值, 如：['en', 'fr']
            },
            list: {
                langs: convert_object_to_array(this.lang_list), // 语言列表，需要转换成数组类型
            }
        }
    },

    watch: {
        // 更新 value 值
        value (newVal) {
            this.udpate_value(newVal);
        }
    },

    methods: {
        /**
         * 数据初始化
         */
        udpate_value (selected) {
            this.data.value = selected, // 选中的语言code值, ['en', 'fr']
            this.data.check_all = selected.length === this.list.langs.length; // 全选
            this.data.indeterminate = selected.length > 0 && selected.length < this.list.langs.length; // 半选
        },

        /**
         * 全选当前渠道下的所有语言
         * @param {Boolean} checked 全选状态
         */
        handle_select_all (checked) {
            this.data.check_all = checked;
            this.data.indeterminate = false;
            checked === true ? this.data.value = this.list.langs.map(x => x.code) : this.data.value = [];
            // update 数据
            this.$emit('update:value', this.data.value);
            this.$emit('update:check_all', this.data.check_all);
            this.$emit('update:indeterminate', this.data.indeterminate);
            this.$emit('onCheckAll', checked);
        },

        /**
         * 勾选语言
         * @param {Array} selected 选中的值
         */
        handle_select_lang (selected) {
            // 判断是[勾选/去除], 1=勾选, 0=去除
            const type = selected.length > this.value.length ? 1 : 0;
            // 获取点击的 code 编码
            const target_code = get_handle_select_lang_code(this.value, selected);
            // 获取默认的 code 编码
            const default_code = get_default_lang_code(this.list.langs);

            // 如选择非默认语言, 则默认语言也自动勾选上
            if (type === 1) {
                if (default_code != '' && !selected.includes(default_code)) {
                    selected.push(default_code);
                }
            }
            // 默认语言取消掉，所有语言的需要去除
            if (type === 0 && target_code === default_code) {
                selected = [];
            }

            // 判断当前是否全选，还是半选
            const all_languages_len = this.list.langs.length;
            this.data.check_all = selected.length == all_languages_len;
            this.data.indeterminate = selected.length > 0 && selected.length < all_languages_len;
            this.data.value = selected;

            // update props data
            this.$emit('update:value', this.data.value);
            this.$emit('update:check_all', this.data.check_all);
            this.$emit('update:indeterminate', this.data.indeterminate);
            this.$emit('onCheck', selected);
        }
    },

    // 初始化
    created () {
        this.udpate_value(this.value);
    }
}
</script>

<style lang="less" scoped>
    // 选项组
    .lang-checkbox-group {
        display: inline-block;
        width: 520px;
        vertical-align: top;
        line-height: 12px;
        padding-top: 16px;
    }

    // 选项
    .checkbox-item {
        display: inline-block;
        height: 12px;
        line-height: 12px;
        margin-right: 10px;
        margin-bottom: 10px;

        .default {
            color: #999;
        }
    }
</style>
