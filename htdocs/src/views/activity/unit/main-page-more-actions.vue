<template>
    <a-dropdown>
        <a class="ant-dropdown-link" @click="e => e.preventDefault()">
            <a-icon type="ellipsis" />
        </a>
        <a-menu slot="overlay">
            <a-menu-item :disabled="disabled" @click="handle_edit"> 编辑 </a-menu-item>
            <a-menu-item :disabled="disabled" @click="show_delete_confirm"> 删除 </a-menu-item>
            <a-menu-item :disabled="disabled" @click="handle_lock">
                {{ ['锁定', '解锁'][is_lock] }}
            </a-menu-item>
            <a-menu-item :disabled="disabled" @click="handle_frequently">
                <template v-if="is_frequently == 1">移除常用</template>
                <template v-else>设为常用</template>
            </a-menu-item>
        </a-menu>
    </a-dropdown>
</template>
<script>

import {
    ZF_deleteActivity,
    ZF_getFrequently,
    ZF_lockingActivity
} from '../../../plugin/api.js';

export default {
    name: 'main-page-more-actions',
    props: {
        // 活动页ID
        id: {
            type: Number
        },
        // 是否锁定功能
        disabled: {
            type: Boolean,
            default: false
        },
        // 是否常用
        is_frequently: {
            type: Number
        },
        // 是否锁定
        is_lock: {
            type: Number,
            default: 0
        }
    },

    data () {
        return {}
    },

    methods: {

        /**
         * 打开编辑主页面的弹层
         */
        handle_edit () {
            this.$emit('onEdit', this.id);
        },
        
        /**
         * 删除主页面的弹窗
         */
        show_delete_confirm () {
            const self = this;
            this.$confirm({
                title: '删除操作',
                content: '活动删除后，其所有的子页面也将被删除，确认删除？',
                okText: '删除',
                okType: 'danger',
                cancelText: '取消',
                onOk () {
                    self.handle_delete();
                },
                onCancel () {},
            });
        },

        /**
         * 删除主页面
         * @param {int} id 页面ID
         */
        handle_delete () {
            const id = this.id;
            ZF_deleteActivity({id}).then(res => {
                if (res.code == 0) {
                    this.$message.success(res.message);
                    this.$emit('deleteCallback');
                } else {
                    this.$message.error(res.message);
                }
            });
        },

        /**
         * 锁定/解锁
         */
        handle_lock () {
            this.$emit('onLock', this.id);
        },

        /**
         * 设置/取消常用
         */
        handle_frequently () {
            const id = this.id;
            ZF_getFrequently({id}).then(res => {
                if (res.code == 1) {
                    this.$message.error(res.message);
                } else {
                    this.$message.success(res.message);
                    this.$emit('deleteCallback');
                }
            });
        }
    }
}
</script>

<style lang="less" scoped>
    .ant-dropdown-link {
        position: absolute;
        top: 14px;
        right: 20px;
        font-weight: bold;
        height: 28px;
        line-height: 28px;

        .anticon {
            &:hover {
                background-color: #409EFF;
                border-radius: 100%;
                svg {
                    color: #fff;
                }
            }
        }
        svg {
            font-size: 28px;
        }
    }
</style>