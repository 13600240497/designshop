<template>
    <el-dialog
        title="页面模板"
        class="geshop-template-model"
        :visible.sync="visible"
        :width="sideWidth"
        :modal="false"
        :modal-append-to-body="false"
        @close="handleClose">
        <el-row v-loading="loading">
            <el-col class="imgPreview text-center" style="height:100%;">
                <iframe frameborder="0" :src="src" class="iframePreview" style="width:100%;height:100%;"></iframe>
            </el-col>
        </el-row>
    </el-dialog>
</template>

<script>

import {
    ZF_getCountrySiteList,
    ZF_getPageTemplateList,
} from '../../plugin/api.js';

import { getCookie } from '../../plugin/mUtils'

export default {
    data  () {
        return {
            loading: false,
            visible: false,
            html: '',
            sideType: 'pc',
            sideWidth: '100%',
            src: ''
        }
    },

    methods: {
        /**
         * 页面模版预览
         * @param {Stirng} pid 页面group_id
         * @param {String} lang 语言
         * @param {String} tpl_id 模版ID
         * @param {String} site_code 站点
         */
        show ({pid, lang = 'en', tpl_id, site_code, platform}) {
            if (!pid) {
                this.$message.error('活动pid不存在');
                return false;
            }
            this.visible = true
            this.loading = true
            this.src = '/activity/zf/page-tpl/preview?pid=' + pid + '&lang=' + lang + '&id=' + tpl_id + '&site_code=' + site_code + '-' + platform;
            this.sideType = platform;
            this.sideWidth = (platform == 'pc' || platform == 'web') ? '100%' : '400px';
            this.loading = false;
        },

        /**
         * 关闭页面模版
         */
        handleClose () {
            this.visible = false;
            this.html = '';
            this.src = '';
        }
    }
}
</script>

<style lang="less">

/* 查看模板大图开始 */
.geshop-template-model .el-dialog {
  margin-top: 0px !important;
  height: 100%;
}
.geshop-template-model .el-dialog .el-dialog__header {
	height: 64px;
	padding: 20px 0px 19px 40px;
	/* width: 100%; */
	font-size: 18px;
	background-color: #1A233B;
}
.geshop-template-model .el-dialog .el-dialog__header .el-dialog__title {
  color: #FFFFFF;
}
.geshop-template-model .el-dialog .el-dialog__body {
  height: 100%;
  /* width: 100%; */
}
.geshop-template-model .el-row {
  /* height: 100% !important; */
  height: 90% !important;
}
.geshop-template-model .el-dialog__header .el-dialog__close,
.geshop-template-model .el-dialog__header .el-dialog__close:hover {
  font-size: 24px;
  color: #FFFFFF;
}
/* 查看模板大图结束 */
</style>