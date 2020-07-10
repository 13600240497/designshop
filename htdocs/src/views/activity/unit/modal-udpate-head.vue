<template>

    <design-dialog
        :width="800"
        title="一键刷新头尾部"
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
    ZF_getCountrySiteList,
    ZF_refreshSite
} from '../../../plugin/api'

import { getCookie } from '../../../plugin/mUtils'

export default {
    props: ['platform'],
    data () {
        return {
            visible: false,
            loading: true, // 是否正在加载数据
            submiting: false, // 是否正在提交
            // 是否全选
            checkAll: true,
            // 选中记录
            checkedPipelines: [],
            // 所有渠道的选项
            pipelines: [],
            // 站点编码
            site_code: '',
        }
    },

    methods: {
        /**
         * 打开弹窗
         */
        async show () {
            // 更新数据
            this.site_code = getCookie('site_group_code');
            this.visible = true;

            // 获取数据
            await this.getSupportCountrySites(this.platform);

            // 默认全选
            this.checkAll = true;
            this.checkedPipelines = this.pipelines.map(x => x.value);
        },
        
        /**
         * 获取所有渠道
         * @param {string} platform 设备终端，pc/wap/app
         */
        async getSupportCountrySites (platform) {
            this.loading = true;
            try {
                const res = await ZF_getCountrySiteList({ activity_type: 1 });
                // 所有端支持的渠道，根据当前的端获取
                const paltform_pipelines = res.data.support_pipelines[platform];
                // 对象转数组
                this.pipelines = Object.keys(paltform_pipelines).map(key => {
                    const item = paltform_pipelines[key];
                    return {
                        value: item.code,
                        label: item.name
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

            // 发起请求
            try {
                let res = await ZF_refreshSite({
                    site_code: `${this.site_code}-${this.platform}`,
                    pipeline: this.checkedPipelines.join(',')
                });
                // 跳转
                if (res.code === 0) {
                    // 确认弹窗
                    this.$confirm({
                        title: '提示', 
                        content: res.message,
                        onOk() {
                            window.location.href = '/base/task-log/index'
                        }
                    });
                    this.visible = false;
                }
            } catch (err) {}

            // 取消提交中的状态
            this.loading = false;
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
