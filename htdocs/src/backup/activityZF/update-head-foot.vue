<template>
    <el-dialog
        class="geshop-new-activities"
        title="一键刷新头尾部"
        :visible.sync="visible">
        
        <div v-loading="loading">
            <!-- 全选 -->
            <el-checkbox
                :indeterminate="isIndeterminate"
                v-model="checkAll"
                @change="handleCheckAllChange">
                所有渠道
            </el-checkbox>
            <div style="margin: 15px 0;"></div>

            <!-- 所有选项 -->
            <el-checkbox-group
                v-model="checkedPipelines"
                @change="handleCheckedPipeline">
                <el-checkbox
                    v-for="item in pipelines"
                    :label="item.code"
                    :key="item.code">
                    {{ item.name }}
                </el-checkbox>
            </el-checkbox-group>
        </div>

        <!-- 弹窗底部 -->
        <div slot="footer" class="dialog-footer">
            <el-button @click="handleCancel">取消</el-button>
            <el-button type="primary" @click="handleConfirm" :loading="submiting">确定</el-button>
        </div>
        
    </el-dialog>
</template>

<script>
import {
    ZF_getCountrySiteList,
    ZF_refreshSite
} from '../../plugin/api'

import { getCookie } from '../../plugin/mUtils'

export default {
    data () {
        return {
            visible: false,
            loading: true, // 是否正在加载数据
            submiting: false, // 是否正在提交
            // 是否半选
            isIndeterminate: false,
            // 是否全选
            checkAll: true,
            // 选中记录
            checkedPipelines: [],
            // 所有渠道的选项
            pipelines: [],
            // 平台 pc/wap/app
            platform: '',
            // 站点编码
            site_code: '',
        }
    },
    methods: {
        /**
         * 打开弹窗
         * @param {string} platform 设备终端，pc/wap/app
         */
        async open (platform = 'pc') {
            // 更新数据
            this.platform = platform;
            this.site_code = getCookie('site_group_code');

            // 获取数据
            await this.getSupportCountrySites(platform);

            // 默认全选
            this.isIndeterminate = false;
            this.checkAll = true;
            const all = this.pipelines.map(x => x.code);
            this.checkedPipelines = all;
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
                        code: item.code,
                        name: item.name
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
        handleCheckAllChange(value) {
            const all = this.pipelines.map(x => x.code);
            this.checkedPipelines = value ? [...all] : [];
            this.isIndeterminate = false;
        },

        // 单选渠道
        handleCheckedPipeline (value) {
            let checkedCount = value.length;
            this.checkAll = checkedCount === this.pipelines.length;
            this.isIndeterminate = checkedCount > 0 && checkedCount < this.pipelines.length;
        },

        // 确定
        async handleConfirm () {
            // 还在请求数据的时候，则不还行
            if (this.loading === true || this.submiting === true) {
                return false;
            }

            // 提交状态
            this.submiting = true;

            // 发起请求
            try {
                let res = await ZF_refreshSite({
                    site_code: `${this.site_code}-${this.platform}`,
                    pipeline: this.checkedPipelines.join(',')
                });
                // 跳转
                if (res.code === 0) {
                    // 确认弹窗
                    this.$confirm(res.message, '提示', {
                        confirmButtonText: '确定',
                        cancelButtonText: '取消',
                        type: 'warning'
                    }).then(() => {
                        window.location.href = '/base/task-log/index'
                    });
                    this.visible = false;
                }
            } catch (err) {}

            // 取消提交中的状态
            this.submiting = false;
        },

        // 取消
        handleCancel () {
            this.visible = false;
        }
    }
}
</script>

<style lang="less" scoped>
    .el-checkbox {
        margin-left: 0px;
        margin-right: 30px;
        min-width: 90px;
    }
    .dialog-footer {
        text-align: center;
    }
</style>
