<template>

    <design-dialog
        :width="900"
        title="访问链接"
        :confirmLoading="loading"
        :visible="visible"
        @isOk="handle_confirm"
        @isCancel="handle_cancel">

        <div class="dialog-sub-page-visit">
            
            <!-- 遍历所有链接 -->
            <template v-for="pipeline in page_list">
                <template v-for="lang in pipeline.lang_list">
                    <a :href="lang.page_url" :key="lang.lang_name" target="_BLANK">
                        {{ pipeline.name }}——{{ lang.lang_name }}{{ lang.is_default == 1 ? '（默认）' : '' }}
                    </a>
                </template>
            </template>

            <div>{{ tips }}</div>

        </div>
    </design-dialog>
</template>

<script>
import {
    ZF_getNewestUrls
} from '../../../plugin/api'

export default {
    data () {
        return {
            visible: false,
            loading: true, // 是否正在加载数据
            submiting: false, // 是否正在提交
            // 是否全选
            checkAll: true,
            // 选中记录
            checkedPipelines: [],
            // 所有渠道+语言的列表
            page_list: [],
            // 查询需要的页面字段
            group_id: '',
            activity_id: '',
            // 错误提示文案
            tips: '',
        }
    },

    methods: {
        /**
         * 打开弹窗
         */
        async show ({ group_id, activity_id }) {

            // 更新数据
            this.group_id = group_id;
            this.activity_id = activity_id;

            // 获取数据
            await this.getSupportCountrySites();
        },
        
        /**
         * 获取所有渠道
         * @param {string} platform 设备终端，pc/wap/app
         */
        async getSupportCountrySites () {
            this.page_list = [];
            this.loading = true;
            // 请求数据
            ZF_getNewestUrls({ group_id: this.group_id, activity_id: this.activity_id }).then(res => {
                if (res.code == 0) {
                    this.visible = true;
                    this.tips = res.data.tips || '';
                    this.page_list = res.data.pipeline_list || [];
                } else {
                    this.visible = false;
                }
                this.loading = false;
            });
        },
        
        // 确定
        async handle_confirm () {
            this.visible = false;
        },

        // 取消
        handle_cancel () {
            this.visible = false;
        }
    }
}
</script>

<style lang="less">
.dialog-sub-page-visit {
    a {
        display: inline-block;
        background-color: #409EFF;
        border-color: #409EFF;
        height: 40px;
        line-height: 40px;
        padding-left: 20px;
        padding-right: 20px;
        margin-right: 10px;
        margin-bottom: 10px;
        text-align: center;
        color: #fff;
        border-radius: 4px;
        vertical-align: middle;
        overflow: hidden;
        width: 200px;
    }
}
</style>
