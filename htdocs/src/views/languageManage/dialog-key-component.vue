<template>
    <a-modal
        class="geshop-modal"
        width="900px"
        title="查看详情"
        :visible="visible"
        @ok="handleOk"
        @cancel="handleCancel">

        <a-table
            class="geshop-table"
            :columns="tableColumns"
            :dataSource="tableDatas"
            :pagination="false"
            :scroll="{ y: 400 }"
            :loading="loading">
            <!-- 终端 -->
            <template slot="range" slot-scope="text">
                {{ ['', 'PC', 'M', '响应式'][text] }}
            </template>
        </a-table>
        
    </a-modal>
</template>

<script>
export default {
    data () {
        return {
            loading: false, // 请求状态
            visible: false,
            tableDatas: [
                {},
                {},
                {},
                {},
                {},
                {},
            ],
            tableColumns: [
                {
                    title: '组件编码',
                    key: 'component_key',
                    dataIndex: 'component_key',
                    align: 'center',
                    width: '25%'
                },
                {
                    title: '端口',
                    key: 'range',
                    dataIndex: 'range',
                    scopedSlots: { customRender: 'range' },
                    width: '25%'
                },
                {
                    title: '组件名称',
                    key: 'component_name',
                    dataIndex: 'component_name',
                    width: '25%'
                },
                {
                    title: '模版名称',
                    key: 'tpl_name',
                    dataIndex: 'tpl_name',
                    width: '25%'
                }
            ]
        };
    },
    methods: {
        /**
         * 获取列表
         */
        async show (key) {
            this.visible = true;
            this.loading = true;
            const res = await this.$api.getLangKey4Component({
                key: key
            });
            this.loading = false;
            this.tableDatas = res.data || [];
        },

        /**
         * 确认按钮 新增/编辑
         */
        handleOk () {
            this.visible = false;
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

<style lang="less" scoped>

</style>
