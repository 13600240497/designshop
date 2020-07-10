<template>

    <design-dialog
        class="geshop-new-child-page"
        title="查看页面ID"
        width="900px"
        :visible.sync="visible"
        @isOk="handleCancel"
        @isCancel="handleCancel">
        <div class="list" v-if="list.length > 0">
            <div class="list-item" v-for="(item, index) in list" :key="index">
                <div class="item-title">
                    <span class="name">
                        {{ item.pipeline_lang }}
                        <span v-if="item.is_default == 1" class="default">(默认)</span>
                    </span>
                    <span>
                        <strong>ID:</strong>
                        {{ item.id }}
                    </span>
                </div>
                <div class="item-footer">
                    <a href="javascript:;" class="deep-link" :data-clipboard-text="item.id">复制ID</a>
                </div>
            </div>
        </div>

        <div v-else>
            暂无已发布渠道
        </div>

    </design-dialog>
</template>

<script>
import { ZF_get_page_id } from '../../../plugin/api'
import Clipboard from 'clipboard'

export default {
    name: 'pageId',
    data () {
        return {
            visible: false,
            clipboard: null,
            list: []
        }
    },
    mounted () {
        const self = this;
        this.clipboard = new Clipboard('.deep-link');

        this.clipboard.on('success', function(e) {
            self.$message.success('复制成功!');
            e.clearSelection();
        });

        this.clipboard.on('error', function() {
            self.$message.error('复制失败!');
        });
    },
    destroyed () {
        this.clipboard.destroy();
    },
    methods: {
        /**
         * 打开弹层
         * @param {string} page_id 页面ID
         * @param {string} group_id 分组ID
         * @param {string} site_code 渠道编码
         */
        show (params) {
            this.visible = true;
            // 获取数据
            this.list = [];
            ZF_get_page_id(params).then((res) => {
                if (res.code == 0) {
                    if (res.data.length) {
                        this.list = [...res.data];
                    }
                }
            });
        },

        /**
         * 关闭弹层
         */
        handleCancel () {
            this.visible = false;
        }
    }
}
</script>

<style lang="less" scoped>
    .list {
        display: flex;
        flex-flow: row wrap;
    }

    .list-item {
        position: relative;
        width:256px;
        border-radius: 4px;
        border:1px solid #DBDBDB;
        margin-bottom: 24px;
        margin-right: 16px;
        height: 110px;

        .item-title {
            display: flex;
            flex-flow: column nowrap;
            padding: 19px 16px 0px;
            overflow: hidden;
            span {
                line-height: 14px;
                color: #666666;
                word-break: break-all;
            }
        }
        .item-title {
            .name {
                font-weight:600;
                margin-bottom: 10px;
                color: #333333;
            }
            .default {
                color: #999;
                font-weight: 400;
            }
        }

        .item-footer {
            position: absolute;
            bottom: 0;
            left: 0;
            width: 100%;
            line-height: 32px;
            height: 32px;
            display: flex;
            justify-content: space-between;
        }
        .deep-link {
            text-decoration: none;
            width: 100%;
            background-color: #409EFF;
            color: #ffffff;
            text-align: center;
        }

    }
</style>
