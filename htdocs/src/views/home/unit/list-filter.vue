<template>
    <div class="list-filter">
        <a-input-group compact size="large">
            <a-select v-model="current_search_key" style="width:140px">
                <template v-for="option in search_key_optinos">
                    <a-select-option :value="option.key" :key="option.key">{{ option.label }}</a-select-option>
                </template>
            </a-select>
            <a-input v-model="search_value" @pressEnter="handle_search" :placeholder="current_search_placeholder" />
        </a-input-group>

        <a-button size="large" type="primary" @click="handle_new">+新增首页</a-button>
        <!-- <a-button size="large" @click="handle_show_dialog('updateHead')">一键刷新头尾</a-button> -->
    </div>
</template>

<script>

import {
    ZF_indexList,
} from '../../../plugin/api'


export default {
    props: ['pageNo', 'pageSize', 'site', 'reflesh'],
    data () {
        return {
            // 当前搜索的字段名称
            current_search_key: 'keywords',
            search_value: '',
            search_key_optinos: [
                { key: 'keywords', label: '首页名称', },
            ]
        };
    },

    computed: {
        platform () {
            return this.$store.state.home.current_platform;
        },
        current_search_placeholder () {
            const item = this.search_key_optinos.filter(x => x.key == this.current_search_key)[0];
            return '请输入需要搜索的' + item.label;
        }
    },

    watch: {
        // 监听页码的变更
        pageNo () {
            this.handle_search();
        },
        platform () {
            this.handle_search();
        },
        reflesh () {
            this.handle_search();
        }
    },

    methods: {

        /**
         * 打开弹窗
         * @param {string} name 弹窗名字
         */
        handle_show_dialog (name) {
            this.$emit('showDialog', {
                name
            });
        },

        /**
         * 新增首页
         */
        handle_new () {
            this.$emit('onCreate');
        },

        /**
         * 执行查询
         */
        handle_search () {
            if (this.platform == '' || this.site == '') {
                return false;
            }

            this.$emit('beforeResponse', true);
            const request = {
                pageNo: this.pageNo,
                pageSize: this.pageSize,
                site_code: `${this.site}-${this.platform}`
            }
            request[this.current_search_key] = this.search_value;
            ZF_indexList(request).then(res => {
                if (res.code == 0) {
                    this.$emit('response', res.data);
                    this.$emit('beforeResponse', false);
                }
            });
        },

        /**
         * 一键更新
         */
        handle_release () {

        }
    },

    mounted () {
        this.handle_search();
    }
}
</script>

<style lang="less">

.list-filter {
    position: absolute;
    right: 0px;
    top: -50px;
    display: flex;
    flex-wrap: nowrap;

    .ant-input-group {
        width: 700px;
        display: flex;
        margin-right: 16px;
        .ant-select-selection {
            border-radius: 20px 0 0px 20px !important;
        }
        .ant-select-selection-selected-value {
            line-height: 40px;
        }
        .ant-input {
            border-radius: 0 20px 20px 0px;
        }
    }

    > .ant-btn {
        margin-left: 8px;
        border-radius: 20px;
        font-size: 14px;
    }

    .ant-select-selection-selected-value {
        width: 100%;
        text-align: center;
    }
}

</style>