<template>
    <div class="sub-page-item">
        <template v-if="info.id">
            <div class="sub-page-tag green" v-if="info.status == 2">正在使用</div>
            <div class="sub-page-tag green" v-if="info.status == 3">已发布</div>
            <div class="sub-page-tag grey" v-if="info.status == 1">草稿</div>
            <div class="sub-page-tag warn" v-if="info.status == 4">已下线</div>

            <div class="activity-tag green" v-if="info.status == 2 && info.home_type == 0">A首页</div>
            <div class="activity-tag pink" v-if="info.status == 2 && info.home_type == 1">B首页</div>

            <div class="sub-page-native-tag" v-if="info.is_native == 1">
                <a-icon type="mobile" />
            </div>

            <img class="sub-page-image" src="/resources/images/default/banner_default.png" />

            <!-- 页面信息 -->
            <div class="sub-page-info">
                <div class="sub-page-id">
                    ID: {{ info.id }} 
                    <a
                        v-if="info.is_native == 1"
                        class="sub-page-view-ids"
                        href="#"
                        @click="handle_show_native_pages">
                        [查看所有ID]
                    </a>
                </div>
                <div class="sub-page-title">{{ info.page_languages[0] ? info.page_languages[0].title : '' }}</div>
                <div>
                    <span class="sub-page-create-time">
                        <a-icon type="file-add" />
                        {{ parseInt(info.create_time) | moment('YYYY-MM-DD HH:mm:ss') }}
                    </span>
                    <span class="sub-page-creator">{{ info.create_name }}</span>
                </div>
                <div>
                    <span class="sub-page-update-time">
                        <a-icon type="form" />
                        {{ parseInt(info.update_time) | moment('YYYY-MM-DD HH:mm:ss') }}
                    </span>
                    <span class="sub-page-updator">{{ info.update_user }}</span>
                </div>

                <a
                    href="javascript:;"
                    class="sub-page-view"
                    @click="handle_visit">
                    <template v-if="info.status == 2">
                        查看访问地址
                    </template>
                </a>

                <div class="sub-page-more">
                    <a-dropdown>
                        <a class="ant-dropdown-link" @click="e => e.preventDefault()">
                            <a-icon type="ellipsis" />
                        </a>
                        <a-menu slot="overlay">
                            <a-menu-item :disabled="disabled" @click="handle_edit">编辑</a-menu-item>
                            <a-menu-item :disabled="disabled" @click="handle_delete">删除</a-menu-item>
                            <a-menu-item :disabled="disabled" @click="handle_lock">
                                <template v-if="info.is_lock == 1">锁定</template>
                                <template v-if="info.is_lock == 2">解锁</template>
                            </a-menu-item>
                            <a-menu-item :disabled="disabled" @click="handle_show_qrcode">二维码</a-menu-item>
                        </a-menu>
                    </a-dropdown>
                </div>
            </div>

            <!-- 锁定层 -->
            <div v-if="disabled" class="sub-page-item-lock">
                <a-icon type="lock" />
                <label>已锁定</label>
            </div>

            <!-- 操作层 -->
            <controller
                v-if="!disabled"
                :info="info"
                :platform="platform"
                :is_native="info.is_native"
                :group_status="info.group_status"
                @onOnline="handle_show_online"
                @onOffline="handle_show_offline"
                @onDesign="handle_design"
                @onPreview="handle_preview">
            </controller>
        </template>
    </div>
</template>

<script>

import controller from './list-item-controller.vue';

export default {
    props: {
        info: {
            type: Object,
            default: function () {
                return {
                    id: 0,
                    is_lock: 0,
                    create_time: 1583720725,
                    update_time: 1583720725,
                    create_name: '格雷福斯',
                    update_user: '格雷福斯'
                }
            }
        },
        // 当前站点 code
        site: {
            type: String,
            required: true,
        },
        // 当前应用端
        platform: {
            type: String
        },
        // 是否可用
        disabled: {
            type: Boolean,
            default: false
        }
    },

    components: {
        controller,
        // modalVisit,
    },

    data () {
        return {

        };
    },

    methods: {
        /**
         * 打开弹层
         */
        handle_show_dialog (name) {
            this.$refs[name] && this.$refs[name].show({
                group_id: this.info.group_id,
                activity_id: this.info.activity_id
            });
        },

        /**
         * 编辑
         */
        handle_edit () {
            this.$emit('onEdit', this.info);
        },

        /**
         * 删除主页面的弹窗
         */
        handle_delete () {
            const self = this;
            this.$confirm({
                title: '提示',
                content: '确定删除首页列表信息吗？删除后，不可恢复，请谨慎操作！',
                okText: '删除',
                okType: 'danger',
                cancelText: '取消',
                onOk () {
                    self.$emit('onDelete', self.info.group_id);
                },
                onCancel () {},
            });
        },

        /**
         * 打开装修页
         */
        handle_design () {
            window.open(this.info.design_url);
        },

        /**
         * 打开预览
         */
        handle_preview () {
            this.$emit('onPreview', {
                id: this.info.id,
                preview: 1
            });
        },

        /**
         * 访问链接
         */
        handle_visit () {
            this.$emit('onPreview', {
                id: this.info.id,
                preview: 0
            });
        },

        /**
         * 打开上线
         */
        handle_show_online (data) {
            this.$emit('show_dialog', {
                name: 'sub_page_online',
                data
            })
        },

        /**
         * 打开下线
         */
        handle_show_offline (data) {
            this.$emit('show_dialog', {
                name: 'sub_page_offline',
                data
            })
        },

        /**
         * 锁定/解锁
         * @param {number} lock 0=解锁,1=锁定
         */
        handle_lock () {
            this.$emit('onLock', this.info.group_id);
        },

        /**
         * 打开二维码
         */
        handle_show_qrcode () {
            this.$emit('onQrcode', this.info.id);
        }
    }
}
</script>

<style lang="less" scoped>

.sub-page-item {
    position: relative;
}

// 新增按钮图标
.anticon-plus {
    display: block;
    text-align: center;
    width: 100%;
    font-size: 56px;
    color: #949AA1;
    margin-top: 94px;
    &+label {
        display: block;
        text-align: center;
        margin-top: 20px;
        color: #949AA1;
    }
}

.sub-page-image {
    display: grid;
    width:290px;
    height:108px;
    border-radius: 10px 10px 0px 0px;
}

.sub-page-tag {
    position: absolute;
    left: 0px;
    top: 0px;
    padding-left: 8px;
    padding-right: 8px;
    height:24px;
    line-height: 24px;
    border-radius:10px 0px 10px 0px;
    color: #fff;
    font-size: 12px;
    &.warn {
        background:rgba(255,161,29,1);
    }
    &.green {
        background:#00CB93;
    }
    &.grey {
        background:#949AA1;
    }
}
.sub-page-native-tag {
    position: absolute;
    right: 10px;
    top: 10px;
    color: #1e9fff;
    font-size: 24px;
}

// AB 首页的标识
.activity-tag {
    position: absolute;
    right: 0px;
    width:50px;
    height:24px;
    border-radius:12px 0px 0px 12px;
    top: 142px;
    text-align: center;
    font-size: 12px;
    line-height: 24px;
    &.green {
        color: #02C5BC;
        background-color: rgba(2, 197, 188, 0.15);
    }
    &.pink {
        color: #A476FF;
        background-color: rgba(164, 118, 255, 0.15);
    }
}

.sub-page-info {
    padding: 16px;
    padding-bottom: 0px;
}

.sub-page-id {
    color: #409EFF;
    margin-bottom: 4px;
}

.sub-page-title {
    font-size: 16px;
    color: #3F4245;
    height: 22px;
    line-height: 22px;
    margin-bottom: 12px;
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap;
}

.sub-page-creator,
.sub-page-create-time,
.sub-page-update-time,
.sub-page-updator {
    color: #949AA1;
    font-size: 12px;
}

.sub-page-creator,
.sub-page-updator {
    float: right;
}

// 查看访问地址
.sub-page-view {
    display: inline-block;
    clear: both;
    margin-top: 8px;
    min-height: 21px;
    color: #6B7075;
    &:hover {
        color: #409EFF;
    }
}

// 查看所有页面ID
.sub-page-view-ids {
    color: #6B7075;
}

// 功能操作层交互
.sub-page-item:hover .sub-page-controller {
    opacity: 1;
}

// 锁定 
.sub-page-item-lock {
    position: absolute;
    left: 0px;
    top: 0px;
    right: 0px;
    height: 108px;
    background-color: rgba(255, 255, 255, 0.88);
    text-align: center;
    border-radius: 10px 10px 0 0;
    i {
        font-size: 30px;
        margin-top: 25px;
    }
    label {
        display: block;
        line-height: 2em;
    }
}

// 更多选项
.sub-page-more {
    position: absolute;
    right: 10px;
    bottom: 22px;
}
.ant-dropdown-link {
    position: absolute;
    top: 0px;
    right: 0px;
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