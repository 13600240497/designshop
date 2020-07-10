<template>
    <design-dialog
        wrapClassName="dialog-page-template"
        :visible="tplInfo.visible"
        :width="tplInfo.width"
        :title="tplInfo.title"
        :confirmLoading="tplInfo.loading"
        @isOk="handleOk"
        @isCancel="handleCancel">

        <div class="tab-container">
            <a-tabs :animated="false" :activeKey="activeKey" @change="activeCallback">
                <a-tab-pane tab="我的模板" key="0">
                    <a-spin :spinning="tplInfo.tabLoading">
                        <page-template-list @getPageId="getPageId"
                                            :tplType="tplType"
                                            ref="pageTemplate"
                                            :list="pageTemplateList"></page-template-list>
                    </a-spin>
                </a-tab-pane>

                <a-tab-pane tab="公用模板" key="1">
                    <a-spin :spinning="tplInfo.tabLoading">
                        <page-template-list @getPageId="getPageId"
                                            ref="pageTemplate"
                                            :tplType="tplType"
                                            :list="pageTemplateList"></page-template-list>
                    </a-spin>
                </a-tab-pane>
            </a-tabs>
        </div>

    </design-dialog>
</template>

<script>
import { ZF_getPageTemplateListNative, ZF_nativeConfirmTpl } from '../../plugin/api'
import pageTemplateList from './more/page-template-list.vue'

export default {
    name: 'component-select-template',
    props: ['visible'],
    data () {
        return {
            tplInfo: {
                width: 960,
                title: '选择页面模板',
                loading: false,
                visible: this.visible || false,
                tabLoading: true,
                pageNo: 1, // 页数
                pageSize: 100, // 数量
                totalNumber: 0 // 总数量
            },
            tpl_id: '',
            activeKey: '0',
            tplType: 0, // 模板类型
            scrollType: '', // 向下加载数据
            pageTemplateList: [] // 页面模板数据
        }
    },
    watch: {
        visible (val) {
            this.tplInfo.visible = val;
            if (val) {
                this.activeKey = '0';
                this.getPageTemplates();
            }
        }
    },
    components: {
        pageTemplateList
    },

    computed: {
        info () {
            return this.$store.state.page.info;
        }
    },
    methods: {
        /**
         * 获取页面模板
         * **/
        async getPageTemplates(val) {
            const type = val || 0;

            let params = {
                place: 1,
                type: type,
                pageNo: this.tplInfo.pageNo,
                pageSize: this.tplInfo.pageSize,
                is_native_theme: 1,
                site_code: this.info.site_code
            };
            let res = await ZF_getPageTemplateListNative(params);
            if (res.code == 0) {
                let data = res.data.list;

                this.tplType = type;
                this.tplInfo.tabLoading = false;
                this.tplInfo.totalNumber = Math.ceil(res.data.totalCount / this.tplInfo.pageSize);

                if (this.scrollType == 'scroll' && this.tplInfo.pageNo > 1) {
                    this.pageTemplateList = this.pageTemplateList.concat([...data]);
                } else {
                    this.pageTemplateList = data;
                }
            }
            this.$nextTick(() => {
                this.handleScroll();
            })
        },

        handleScroll () {
            this.timer = null;
            if (this.$refs.pageTemplate) {
                this.targetNode = this.$refs.pageTemplate.$el;
                this.targetNode.addEventListener('scroll', this.scrollCallBack)
            }
        },

        scrollCallBack () {
            let self = this;
            let $targetNode = self.targetNode;
            if (this.timer) {
                clearTimeout(this.timer);
            }
            this.timer = setTimeout(function () {
                if ($targetNode.clientHeight + $targetNode.scrollTop == $targetNode.scrollHeight) {
                    let tempNum = self.tplInfo.pageNo + 1;
                    self.tplInfo.pageNo++;
                    if (tempNum <= self.tplInfo.totalNumber) {
                        self.scrollType = 'scroll';
                        self.getPageTemplates(self.tplType);
                    }
                }
            }, 600);
        },

        // 确认
        handleOk () {
            const self = this;
            console.log(this.info, 2222);

            if (this.tpl_id == '') {
                this.$emit('update:visible', false);
                return false;
            }

            const params = {
                tpl_id: this.tpl_id,
                page_id: this.info.page_id,
                lang: this.info.lang
            };

            self.confirm = this.$confirm({
                content: '切换所需模板后，参数会被替换!',
                onOk () {
                    ZF_nativeConfirmTpl(params).then((res) => {
                        if (res.code == 0) {
                            self.$message.success(res.message, 2, () => {
                                self.removeSrcoll();
                                window.location.reload();
                            });
                        }
                    });
                },
                onCancel () {
                    self.removeSrcoll();
                    self.confirm.destroy();
                }
            });


        },

        // 取消
        handleCancel () {
            this.$emit('update:visible', false);
            this.removeSrcoll();
        },

        // tab 切换
        activeCallback (val) {
            this.activeKey = val;
            this.tplInfo.tabLoading = true;
            this.tplType = val; // 模板类型
            this.tplInfo.pageNo = 1;
            this.getPageTemplates(val)
        },

        getPageId (val) {
            this.tpl_id = val;
        },

        destroyed () {
            this.removeSrcoll();
        },
        // 销毁scroll事件
        removeSrcoll () {
            this.targetNode && this.targetNode.removeEventListener('scroll', this.scrollCallBack);
        }
    }
}
</script>

<style lang="less">

.dialog-page-template {
    .ant-modal-body {
        padding-right: 0px;
    }

    .tab-container {
        .ant-tabs-bar {
            border-bottom: none;
        }
        .ant-tabs-nav-wrap {
            margin-bottom: 0px;
        }

        .ant-tabs-nav {
            border: 1px solid #E8EAEC;
            border-radius: 4px;
            overflow: hidden;
        }

        .ant-tabs-nav .ant-tabs-tab {
            width:156px;
            height:40px;
            line-height: 40px;
            margin: 0px;
            padding: 0px 16px;
            text-align: center;
        }
        .ant-tabs-nav .ant-tabs-tab-active {
            background:#409EFF;
            color: #fff;
        }

        .ant-tabs-ink-bar {
            height: 0px;
        }
    }
}

</style>
