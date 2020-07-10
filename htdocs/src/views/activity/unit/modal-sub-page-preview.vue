<template>

    <design-dialog
        :width="900"
        title="预览"
        :confirmLoading="loading"
        :visible="visible"
        @isOk="handle_confirm"
        @isCancel="handle_cancel">

        <div class="dialog-sub-page-preview">
            
            <!-- 所有选项 -->
            <template v-for="item in page_list">
                <a :href="item.preview_url" :key="item.label" target="_BLANK">{{ item.label }}</a>
            </template>

        </div>
    </design-dialog>
</template>

<script>

export default {
    data () {
        return {
            visible: false,
            loading: true, // 是否正在加载数据
            // 所有渠道+语言的列表
            page_list: [],
        }
    },

    methods: {
        /**
         * 打开弹窗
         */
        async show (dataList) {
            // 更新数据
            this.visible = true;

            // 获取数据
            this.getSupportCountrySites(dataList);
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
                Object.keys(paltform_pipelines).map(key => {
                    const item = paltform_pipelines[key];
                    Object.keys(item.lang_list).map(langCode => {
                        const lang_name = item.lang_list[langCode].name;
                        const lang_url = item.lang_list[langCode].name;
                        const preview_url = item.lang_list[langCode].preview_url;
                        if (preview_url) {
                            this.page_list.push({
                                label: `${item.name}-${lang_name}`,
                                url: lang_url,
                                preview_url,
                            })
                        }
                        
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
