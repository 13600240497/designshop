<template>
    <div class="sub-page-item">
        <template v-if="info.id">
            <div class="sub-page-tag green" v-if="info.pipeline_info.group_status == 2">上线</div>
            <div class="sub-page-tag grey" v-if="info.pipeline_info.group_status == 4">下线</div>
            <div class="sub-page-tag warn" v-if="info.pipeline_info.group_status == 10">部分渠道上线</div>

            <div class="sub-page-native-tag" v-if="info.is_native == 1">
                <a-icon type="mobile" />
            </div>

            <img class="sub-page-image" :src="info.preview_pic_url" />

            <!-- 页面信息 -->
            <div class="sub-page-info">
                <div class="sub-page-id">
                    ID: {{ info.id }} 
                    <a
                        class="sub-page-view-ids"
                        href="#"
                        @click="handle_show_page_id(info.is_native == 1 ? 'native' : 'normal')">
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
                    @click="handle_show_dialog('sub_page_visit')">
                    查看访问地址
                </a>

                <div class="sub-page-more">
                    <a-dropdown>
                        <a class="ant-dropdown-link" @click="e => e.preventDefault()">
                            <a-icon type="ellipsis" />
                        </a>
                        <a-menu slot="overlay">
                            <a-menu-item :disabled="disabled" @click="handle_edit">编辑</a-menu-item>
                            <a-menu-item :disabled="disabled" @click="handle_delete">删除</a-menu-item>
                            <a-menu-item
                                v-if="platform == 'pc' && info.hasToWap == true"
                                :disabled="disabled"
                                @click="handle_convert('pc')">
                                转M端
                            </a-menu-item>
                            <a-menu-item
                                v-if="(platform == 'wap' && info.is_native == 0) || platform == 'web'"
                                :disabled="disabled"
                                @click="handle_convert('wap')">
                                转APP端
                            </a-menu-item>
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
                :group_status="info.pipeline_info.group_status"
                @onOnline="handle_show_online"
                @onOffline="handle_show_offline"
                @onDesign="handle_design"
                @onPreview="handle_preview">
            </controller>

            <!-- 查看访问地址 -->
            <modal-visit ref="sub_page_visit"></modal-visit>

        
        </template>

        <!-- 新增的按钮 -->
        <template v-else>
            <div @click="handle_new">
                <a-icon type="plus" />
                <label>添加子页面</label>
            </div>
        </template>
    </div>
</template>

<script>

import {
    ZF_deletePage,
} from '../../../plugin/api.js';

import controller from './sub-page-item-controller.vue';
import modalVisit from'./modal-sub-page-visit.vue';

/**
 * 检查是否原生装修页面
 * @param {string} url
 */
const is_native = (url) => {
    return url.includes('native');
};

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
        modalVisit,
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

        // [新增/编辑]
        handle_edit () {
            this.$emit('edit', this.info);
        },

        handle_new () {
            this.$emit('edit');
        },

        /**
         * 删除主页面的弹窗
         */
        handle_delete () {
            const self = this;
            this.$confirm({
                title: '删除操作',
                content: '活动删除后，其所有的子页面也将被删除，确认删除？',
                okText: '删除',
                okType: 'danger',
                cancelText: '取消',
                onOk () {
                    ZF_deletePage({
                        group_id: self.info.group_id,
                        activity_id: self.info.activity_id
                    }).then(res => {
                        self.$emit('update');
                    });
                },
                onCancel () {
                    console.log('Cancel');
                },
            });
        },

        /**
         * 打开装修页
         */
        handle_design () {
            // 存储当前子活动字段，activity_id
            localStorage.setItem('ZF_activityData', JSON.stringify({
                activity_id: this.info.activity_id,
                group_id: this.info.group_id,
                page_id: this.info.id,
                site_code: this.info.site_code
            }));

            // 判断是否原生专题
            let url = '';
            if (is_native(this.info.design_url) == true) {
                url = '#' + this.info.design_url.split('#')[1];
            } else {
                url = this.info.design_url;
            }
            window.open(url);
        },

        /**
         * 打开预览
         */
        handle_preview () {
            this.$emit('onPreview', this.info.pipeline_info.page_list);
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
         * 打开(PC转M)/(M转APP)的弹窗
         */
        handle_convert (type = 'pc') {
            if (type == 'pc') {
                this.$emit('convertPC', this.info);                
            }
            if (type == 'wap') {
                this.$emit('convertWAP', this.info);
            }
        },

        /**
         * 查看页面ID
         * @param {String} type normal/native
         */
        handle_show_page_id (type = '') {
            if (type == 'native') {
                this.$emit('show_dialog', {
                    name: 'sub_page_native_id',
                    data: {
                        page_id: this.info.id,
                        group_id: this.info.group_id,
                        site_code: `${this.site}-${this.platform}`
                    }
                })
            }
            if (type == 'normal') {
                this.$emit('show_dialog', {
                    name: 'sub_page_id',
                    data: {
                        page_id: this.info.id,
                        group_id: this.info.group_id,
                        site_code: `${this.site}-${this.platform}`
                    }
                })
            }
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
        background:green;
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