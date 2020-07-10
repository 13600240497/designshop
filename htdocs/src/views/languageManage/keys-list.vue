<template>
    <div class="geshop-page-language">

        <!-- 筛选表单 -->
        <filter-form
            ref="filterForm"
            @select="handleChangeColumns"
            @search="handleSearch">
        </filter-form>
        
        <!-- 列表 -->
        <a-table
            class="geshop-table"
            rowKey="row_id"
            :columns="tableColumns"
            :dataSource="dataSet.keysList"
            :pagination="pagination"
            :loading="loading"
            :scroll="{ x: true }"
            @change="handleTableChange">

            <!-- 编号 -->
            <template slot="row_id" slot-scope="text">
                <div style="width: 100px">{{ text }}</div>
            </template>

            <template slot="key" slot-scope="text">
                <div style="width: 245px">{{ text }}</div>
            </template>

            <!-- 自定义列 -->
            <template
                v-for="(code, index) in tableColumnsCustom.map(x => x.dataIndex)"
                :slot="code"
                slot-scope="text, record">
                <div :key="index" style="min-width: 200px;">
                    <edit-cell
                        :edit="onEditing.key == record.key && onEditing.lang == code"
                        :lang="code"
                        :text="text"
                        :row="record"
                        @onEdit="handleEditing"
                        @onSuccess="handleSearch">
                    </edit-cell>
                </div>
            </template>

            <template slot="update_user" slot-scope="text">
                <div style="min-width: 200px">{{ text || '-' }}</div>
            </template>

            <!-- 操作 -->
            <template slot="action" slot-scope="text, record">
                <div style="width: 100px">
                    <a @click="handleCheckComponentUsed(record.key)">查看详情</a>
                </div>
            </template>

        </a-table>
        
        <!-- KEY和组件模版的绑定关系弹窗 -->
        <dialog-key-component ref="dialogKeyComponent"> </dialog-key-component>
    </div>
</template>

<script>
// 筛选表单
import filterForm from './keys-list-filter.vue';
// 编辑单元格
import editCell from './keys-list-editCell.vue';
// KEY和组件模版的绑定关系
import dialogKeyComponent from './dialog-key-component.vue';


export default {
    components: {
        filterForm,
        editCell,
        dialogKeyComponent,
    },
    data () {
        return {
            // 表格加载状态
            loading: true,
            // 数据集
            dataSet: {
                // 语种列表
                langList: [],
                // 键值对列表，表格用到
                keysList: [],
            },
            // 基础的表格列配置，中间可以动态插入列，在坐标[3]之间插入
            tableColumnsBase: [
                {
                    title: '编号',
                    key: 'row_id',
                    dataIndex: 'row_id',
                    align: 'center',
                    width: '120px',
                    fixed: 'left'
                },
                {
                    title: '键值',
                    key: 'key',
                    dataIndex: 'key',
                    width: '275px',
                    fixed: 'left',
                    scopedSlots: { customRender: 'key' }
                },
                {
                    title: '中文文案',
                    key: 'zh',
                    dataIndex: 'zh',
                },
                {
                    title: '最后操作人',
                    key: 'update_user',
                    dataIndex: 'update_user',
                    scopedSlots: { customRender: 'update_user' }
                },
                {
                    title: '最后操作时间',
                    key: 'update_time',
                    dataIndex: 'update_time',
                },
                {
                    title: '操作',
                    key: 'action',
                    dataIndex: 'action',
                    fixed: 'right',
                    width: '120px',
                    scopedSlots: { customRender: 'action' }
                },
            ],
            // 自定义表格列配置，通过 <filter-form> 组件配置
            tableColumnsCustom: [],
            // 表格的分页配置器
            pagination: {
                current: 1,
                pageSize: 10,
                total: 1
            },
            // 搜索条件
            search: {
                key: '',
                lang_zh: ''
            },
            // 纪录当前正在编辑的 key 和 lang
            onEditing: {
                key: '',
                lang: '',
            }
        };
    },

    computed: {
        // 站点编码
        siteCode () {
            return this.$store.state.siteCode;
        },

        // 合并表格的基础列，以及自定义列
        tableColumns () {
            const concatList = [...this.tableColumnsBase];
            [...this.tableColumnsCustom].map((x, index) => {
                // 过滤 简体中文， 因为已经在固定列 配置里面了。
                if (x.dataIndex != 'zh') {
                    concatList.splice(3 + index, 0, x);
                }
            });
            return concatList;
        }
    },

    methods: {

        /**
         * 获取语种列表
         */
        async getLangList () {
            const request = {
                site_code: this.siteCode
            }
            const res = await this.$api.getLangCodeList(request);
            if (res.code === 0) {
                return res.data.list;
            } else {
                return []
            }
        },

        /**
         * 搜索列表
         * @param {object} values 搜索字段，默认 null
         */
        async handleSearch (values = null) {
            if (values != null) {
                this.search.key = values.key || '',
                this.search.lang_zh = values.cn || ''
            }
            this.loading = true;
            const res = await this.$api.getLangKeysList({
                site_code: this.siteCode,
                pageNo: this.pagination.current,
                pageSize: this.pagination.pageSize,
                search_key: this.search.key,
                search_value: this.search.lang_zh
            });
            this.loading = false;
            if (res.code === 0) {
                this.dataSet.keysList = res.data.list.map(row => {
                    // 拼装每一行的数据
                    const newRow = {
                        row_id: row.id,
                        key: row.key,
                        update_time: row.update_time,
                        update_user: row.update_user,
                    }
                    Object.keys(row.lang_value).map(key => {
                        newRow[key] = row.lang_value[key] || '';
                    });
                    return newRow;
                });
                this.pagination.total = res.data.pagination.totalCount;
            }
        },

        /**
         * 更改表格展示的列数
         * @param {Array} selectedCode 选中的语种列表
         * @example ['en', 'cn', 'es']
         */
        handleChangeColumns (selectedCode) {
            // 过滤中文
            const codes = selectedCode.filter(x => x != 'zh');
            // 拼装合适的column的格式
            this.tableColumnsCustom = codes.map(lang => {
                const matchItem = this.dataSet.langList.filter(x => x.lang === lang);
                const name = matchItem[0].lang_name;
                return {
                    title: name,
                    dataIndex: lang,
                    width: '100px',
                    scopedSlots: { customRender: lang }
                };
            });
        },

        // 更改页码
        handleTableChange (pagination, filters, sorter) {
            this.pagination.current = pagination.current;
            this.handleSearch();
        },

        /**
         * 查看 KEY 在哪个组件用到
         */
        handleCheckComponentUsed (key) {
            this.$refs.dialogKeyComponent.show(key);
        },

        /**
         * 编辑翻译语言
         */
        handleEditing (key, lang) {
            this.onEditing.key = key;
            this.onEditing.lang = lang;
        },
    },

    async mounted () {
        // 获取语种列表，初始化 <filter-form> 组件
        this.dataSet.langList = await this.getLangList();
        // 过滤中文，不需要勾选，因为默认已经加到固定列
        this.dataSet.langList = this.dataSet.langList.filter(x => x.lang != 'zh');
        this.$refs.filterForm.initSelect(this.dataSet.langList);

        // 默认显示所有的列
        this.handleChangeColumns(this.dataSet.langList.map(x => x.lang));

        // 搜索列表
        this.handleSearch();
    }
}
</script>

<style lang="less">
.geshop-page-language {
    padding: 24px;
    .ant-table td {
        min-width: 120px;
        white-space: nowrap;
        padding-top: 8px;
        padding-bottom: 8px;
        line-height: 32px;
    }
}
</style>
