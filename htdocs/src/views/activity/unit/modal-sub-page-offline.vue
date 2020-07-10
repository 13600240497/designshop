<template>

    <design-dialog
        :width="800"
        title="请选择要下线的渠道页面"
        :confirmLoading="loading"
        :visible="visible"
        @isOk="handle_confirm"
        @isCancel="handle_cancel">

        <div class="dialog-update-head">
            <a-checkbox
                :checked="checkAll"
                @change="handleCheckAllChange">
                全选所有渠道
            </a-checkbox>
                    
            <div style="margin: 15px 0;"></div>

            <!-- 所有选项 -->
            <a-checkbox-group
                v-model="checkedPipelines"
                :options="pipelines"
                @change="handleCheckedPipeline">
            </a-checkbox-group>

        </div>
    </design-dialog>
</template>

<script>
import {
    ZF_getChannelPageInfo,
    ZF_verifyPage
} from '../../../plugin/api'

export default {
    props: ['platform'],
    data () {
        return {
            visible: false,
            loading: true, // 是否正在加载数据
            submiting: false, // 是否正在提交
            // 是否全选
            checkAll: false,
            // 选中记录
            checkedPipelines: [],
            // 所有渠道的选项
            pipelines: [],
            // 站点编码
            site_code: '',
            group_id: '',
            page_id: ''
        }
    },

    methods: {
        /**
         * 打开弹窗
         */
        async show ({page_id, group_id, status}) {
            // 更新数据
            this.page_id = page_id;
            this.group_id = group_id;

            // 获取数据
            await this.getSupportCountrySites();

            // 默认全选
            this.checkAll = false;
            this.checkedPipelines = [];

            this.visible = true;
        },
        
        /**
         * 获取所有渠道
         */
        async getSupportCountrySites () {
            this.loading = true;
            this.pipelines = [];
            try {
                const res = await ZF_getChannelPageInfo({ group_id: this.group_id });
                // 所有端支持的渠道，根据当前的端获取
                const paltform_pipelines = res.data.page_list;

                // 对象转数组
                Object.keys(paltform_pipelines).map(key => {
                    const item = paltform_pipelines[key];
                    // 只添加已经上线的数据
                    if (item.status == 2) {
                        this.pipelines.push({
                            value: item.code,
                            label: item.name,
                            lang: Object.keys(item.lang_list)
                        });
                    }
                });
                // 展示弹窗
                this.visible = true;
                this.loading = false;
            } catch (err) {
                this.visible = false;
            }
        },
        
        /**
         * 全选渠道
         * @param {boolean} value 是否全选
         *  */
        handleCheckAllChange () {
            this.checkAll = !this.checkAll;
            this.checkedPipelines = this.checkAll ? this.pipelines.map(x => x.value) : [];
        },

        // 单选渠道
        handleCheckedPipeline (value) {
            let checkedCount = value.length;
            this.checkAll = checkedCount === this.pipelines.length;
        },

        // 确定
        async handle_confirm () {
            // 还在请求数据的时候，则不还行
            if (this.loading === true || this.submiting === true) {
                return false;
            }

            // 提交状态
            this.loading = true;

            // 遍历选中的数据，组装
            const batch_data = this.checkedPipelines.map(pipeline_code => {
                const info = this.pipelines.filter(item => item.value == pipeline_code)[0];
                return {
                    pipeline: pipeline_code,
                    lang: info.lang.join(',')
                }
            });
            const request = {
                id: this.page_id,
                status: 4,
                batch_data: JSON.stringify(batch_data)
            }
            // 发起请求
            let res = ZF_verifyPage(request).then(res => {
                // 跳转
                if (res.code === 0) {
                    this.$message.success(res.message);
                    this.visible = false;
                    this.$emit('onSuccess');
                } else {
                    this.$message.error(res.message);
                }
                // 取消提交中的状态
                this.loading = false;
                this.$emit('onSuccess');
            }).catch(() => {
                // 取消提交中的状态
                this.loading = false;
            });
        },

        // 取消
        handle_cancel () {
            this.visible = false;
        }
    }
}
</script>

<style lang="less">
.dialog-update-head {
    .ant-checkbox-wrapper {
        margin-left: 0px;
        margin-right: 30px;
        min-width: 90px;
    }
}
</style>
