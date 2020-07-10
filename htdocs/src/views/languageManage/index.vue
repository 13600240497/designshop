<template>
    <div style="padding: 24px;">
        <div class="contorller-layer">
            <a-button type="primary" @click="handleAdd">新增语言</a-button>
            <a-button @click="handleManage">
                管理语言包
                <a-icon type="arrow-right" />
            </a-button>
        </div>

        <!-- 列表 -->
        <div class="list">
            <a-table
                class="geshop-table"
                rowKey="id"
                :columns="tableColumns"
                :dataSource="tableDatas"
                :pagination="pagination"
                :loading="loading"
                @change="handleTableChange">
            </a-table>
        </div>

        <!-- 弹窗新增/编辑语种 -->
        <dialog-add ref="dialogAdd" @success="get_list"></dialog-add>

    </div>
</template>

<script>

// 弹窗新增/编辑语种
import dialogAdd from './dialog-add-lang.vue';

export default {
    data () {
        return {
            loading: false,
            // 表格原数据
            tableDatas: [],
            // 表格展示的列
            tableColumns: [
                {
                    title: '编号',
                    key: 'id',
                    dataIndex: 'id',
                    width: '20%',
                    align: 'center'
                },
                {
                    title: '语言名称',
                    key: 'lang_name',
                    dataIndex: 'lang_name',
                    width: '20%'
                },
                {
                    title: '语言简码',
                    key: 'lang',
                    dataIndex: 'lang',
                    width: '20%'
                },
                {
                    title: '操作人',
                    key: 'update_user',
                    dataIndex: 'update_user',
                    width: '20%'
                },
                {
                    title: '操作时间',
                    key: 'update_time',
                    dataIndex: 'update_time',
                    width: '20%'
                }
            ],
            // 分页配置选项
            pagination: {
                current: 1,
                total: 1,
                pageSize: 20, 
                showQuickJumper: true
            }
        };
    },
    components: {
        dialogAdd
    },
    methods: {
        /**
         * 获取语种列表
         */
        async get_list () {
            this.loading = true;
            const request = {
                pageNo: this.pagination.current,
                pageSize: this.pagination.pageSize,
                site_code: this.$store.state.siteCode
            }
            try {
                const res = await this.$api.getLangCodeList(request);
                this.loading = false;
                this.tableDatas = res.data.list;
                this.pagination.total = res.data.pagination.totalCount;
            } catch (err) {}
            this.loading = false;
        },

        /**
         * 切换页码
         */
        handleTableChange (pagination, filters, sorter) {
            this.pagination.current = pagination.current;
            this.get_list();
        },

        /**
         * 是否展示新增/编辑弹窗口
         */
        handleAdd () {
            this.$refs.dialogAdd.show(true);
        },

        /**
         * 跳转页面，管理语言包
         */
        handleManage () {
            this.$router.push('/base/language-package/keys')
        }
    },
    watch: {
    },
    async created () {
        await this.get_list();
    }
}
</script>

<style lang="less" scoped>
    .contorller-layer {
        text-align: right;
        margin-bottom: 24px;
        button {
            margin-left: 4px;
        }
    }
</style>
