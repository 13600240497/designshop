<template>
    <design-dialog
        :width="900"
        :title="title"
        :confirmLoading="loading"
        :visible="visible"
        @isOk="handle_confirm"
        @isCancel="handle_cancel">

        <div class="dialog-sub-page-preview">
            
            <!-- 所有选项 -->
            <template v-for="item in page_list">
                <a :href="item.page_url" :key="item.label" target="_BLANK">{{ item.label }}</a>
            </template>

        </div>
    </design-dialog>
</template>

<script>

import {
    ZF_getHomeLink
} from '../../../plugin/api.js'

export default {
    data () {
        return {
            visible: false,
            loading: true, // 是否正在加载数据
            is_preview: 0, // 是否预览链接
            page_list: [], // 所有渠道+语言的列表
        }
    },

    computed: {
        title () {
            const map = ['首页访问地址', '首页线上预览地址'];
            return map[this.is_preview];
        }
    },

    methods: {
        /**
         * 打开弹窗
         * @param {number} id Mysql自增ID
         * @param {Numbe} is_preview 是否预览链接
         */
        async show (id, preview = 0) {
            this.is_preview = preview;
            const res = await ZF_getHomeLink({id, preview});
            if (res.code == 0) {
                this.visible = true;
                // 获取数据
                this.getSupportCountrySites(res.data.pipeline_list);
            }
        },
        
        /**
         * 获取所有渠道
         * @param {Object} paltform_pipelines 列表
         */
        async getSupportCountrySites (paltform_pipelines) {
            this.page_list = [];
            this.loading = true;
            try {
                // 所有端支持的渠道，根据当前的端获取
                // 对象转数组
                paltform_pipelines.map(item => {
                    item.lang_list.map(lang => {
                        lang.page_url && this.page_list.push({
                            label: `${item.name}-${lang.lang_name}`,
                            page_url: lang.page_url,
                        });
                    });
                });
                // 展示弹窗
                this.visible = true;
                this.loading = false;
            } catch (err) {
                console.log(err);
            }
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
.dialog-sub-page-preview {
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
        width: 200px;
    }
}
</style>
