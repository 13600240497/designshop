<template>
    <el-dialog
        title="访问链接"
        class="geshop-view-link-page geshop-activity-preview-address"
        :visible.sync="visible">

        <el-row>

            <el-button
                class="view-link-button--primary"
                v-for="item in list" type="primary"
                :key="item.code"
                @click="redirect(item.page_url)">
                {{ item.name }}
                <template v-if="item.is_default == 1">
                    <span>(默认)</span>
                </template>
            </el-button>

            <p v-if="tips">{{ tips }}重新发布</p>

            <el-form>
                <el-form-item class="geshop-new-activities-btn">
                    <el-button
                        size="small"
                        @click="handle_close">
                        取消
                    </el-button>

                    <el-button
                        type="primary"
                        size="small"
                        @click="handle_close">
                        确定
                    </el-button>
                </el-form-item>
            </el-form>

        </el-row>
    </el-dialog>
</template>
<script>

import {
    ZF_viewPipelineNewestUrl
} from '../../plugin/api'

/**
 * 查看访问链接 弹窗
 */
export default {
    data () {
        return {
            visible: false,
            list: [],
            tips: '',
            urlID: ''
        }
    },
    methods: {
        /**
         * 查看访问链接
         * @param {} group_id
         * @param {} activity_id
         */
        async show (group_id, activity_id) {
            // 获取数据
            try {
                let res = await ZF_viewPipelineNewestUrl({
                    group_id,
                    activity_id
                });
                // 拆分渠道语言数据
                this.list = this.formate_pipelines_list(res.data.pipeline_list || []);
                // 提示文案
                this.tips = res.data && res.data.tips ? res.data.tips : '';
                // ???
                this.urlID = activity_id
                // 展示弹层
                this.visible = true;
            } catch (e) {
                console.warn(e);
                return false;
            }
        },

        /**
         * 关闭弹窗
         */
        handle_close () {
            this.visible = false
        },

        /**
         * 格式化接口数据，拆分渠道和语言信息
         * @param {array} arr 渠道列表
         * @returns {array}
         */
        formate_pipelines_list (arr) {
            const list = [];
            arr.map(item => {
                item.lang_list.map(langItem => {
                    list.push({
                        pipeCode: item.code,
                        name: item.name + '——' + langItem.lang_name,
                        page_url: langItem.page_url,
                        is_default: langItem.is_default, // 是否默认语言
                    });
                });
            });
            return list;
        },

        /**
         * 跳转页面
         */
        redirect (url) {
            window.open(url);
        }
    }
}
</script>