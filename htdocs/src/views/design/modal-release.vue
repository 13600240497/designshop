<template>
    <design-dialog
        :width="800"
        title="请选择要发布的渠道页面"
        @isOk="handle_confirm"
        @isCancel="handle_cancel"
        :visible="local_visible"
        :confirmLoading="loading">
        <!-- 选择组 -->
        <a-checkbox-group v-model="selected" style="width:100%;">
            <a-row class="pipelines-list">
                <a-col v-for="(item, index) in pipelines" :key="index" :span="4">
                    <a-checkbox
                        :value="item.pipeline"
                        @change="handle_checkbox_change">
                        {{ item.pipeline_name }}
                    </a-checkbox>
                </a-col>
            </a-row>
        </a-checkbox-group>
    </design-dialog>
</template>

<script>
export default {
    props: ['visible'],

    data () {
        return {
            local_visible: this.visible || false,
            selected: [], // 选中记录
            loading: false,
        }
    },

    watch: {
        visible (val) {
            this.local_visible = val;
        }
    },

    computed: {
        // 获取可用的渠道列表
        pipelines () {
            return this.$store.state.page.pipelines || [];
        },
        // 当前页面信息
        page_info () {
            return this.$store.state.page.info;
        },
    },

    methods: {

        /**
         * 勾选渠道，推送信息到后台，后端服务先不做，后期再补上
         */
        handle_checkbox_change (e) {
            if (e.target.checked === true) {
                this.$api.design_reload_release({
                    page_id: this.page_info.page_id,
                    group_id: this.page_info.group_id,
                    pipeline: e.target.value,
                });
            }
        },

        /**
         * 确定发布
         * @description
         * 1. 先执行页面保存操作
         * 2. 请求发布接口
         * @date 2019-12-02
         * @author Cullen
         */
        async handle_confirm () {
            // 遍历可用渠道
            const batchData = [];
            this.selected.map(pipeline => {
                // 当前渠道的所有语言
                const langList = this.pipelines.filter(x => x.pipeline === pipeline)[0].langList;
                langList.map(lang => {
                    batchData.push({
                        pipeline: pipeline,
                        lang: lang.key
                    })
                });
            });
            
            // 如果没勾选则退出
            if (batchData.length <= 0) return false;

            // 保存页面
            this.loading = true;
            this.$store.dispatch('design/page_save').then(res => {
                // 发布页面AJAX
                this.$api.design_release({
                    page_id: this.page_info.page_id,
                    batch_data: JSON.stringify(batchData),
                }).then(res => {
                    this.loading = false;
                    this.selected = [];
                    this.$emit('update:visible', false);
                }, (err) => {
                    this.loading = false;
                });
            }).catch(() => {
                this.loading = true;
            });
        },

        /**
         * 取消发布
         */
        handle_cancel () {
            this.$emit('update:visible', false);
        },

        
    },
}
</script>

<style lang="less" scoped>

.pipelines-list {
    list-style: none;
    margin: 0px;
    padding: 0px;
    .ant-col-4 {
        margin-bottom: 4px;
    }
}

</style>