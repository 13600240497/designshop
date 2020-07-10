<template>
    <design-dialog
        :width="channelInfo.width"
        :title="channelInfo.title"
        :confirmLoading="channelInfo.loading"
        :visible="channelInfo.visible"
        @isOk="handleOk"
        @isCancel="handleCancel">

        <div class="dialog-sync-channel">

            <a-form :form="form">
                <a-form-item class="form-label" label="被同步的渠道信息">
                    <div class="channel-info">
                        <a-row type="flex" justify="start">
                            <a-col class="info-item">
                                <div class="channel">
                                    <a-form-item label="被同步的渠道">
                                        <a-select
                                            v-decorator="[
                                              'pipeline',
                                              {
                                                rules: [{ required: true, message: '请选择被同步渠道' }],
                                                initialValue: pipeline ? pipeline : undefined,
                                              }
                                            ]"
                                            placeholder="请选择"
                                            :notFoundContent="notFoundContent"
                                            @change="handlepipelineChange">
                                            <a-select-option v-for="(item, index) in fromPipelineList" :key="index" :value="item.pipeline">
                                                {{ item.pipeline_name }}
                                            </a-select-option>
                                        </a-select>
                                    </a-form-item>
                                </div>
                            </a-col>

                            <a-col class="info-item">
                                <div class="lang">
                                    <a-form-item label="被同步的语言页面">
                                        <a-select placeholder="请选择"
                                                  v-decorator="[
                                                      'lang',
                                                      {
                                                        rules: [{ required: true, message: '请选择被语言' }],
                                                        initialValue: lang ? lang : undefined,
                                                      }
                                                  ]"
                                                  :notFoundContent="notFoundContent"
                                                  @change="handleLangChange">
                                            <a-select-option v-for="(item, index) in langList" :key="index" :value="item.key">
                                                {{ item.name }}
                                            </a-select-option>
                                        </a-select>
                                    </a-form-item>
                                </div>
                            </a-col>

                            <a-col class="info-item">
                                <div class="content">
                                    <div class="tip">同步的内容</div>
                                    <a-select :value="pipeline_type"
                                              @change="handlePageChange"
                                    >
                                        <a-select-option v-for="(item, index) in syncData" :key="index" :value="item.value">
                                            {{ item.name }}
                                        </a-select-option>
                                    </a-select>
                                </div>
                            </a-col>
                        </a-row>
                    </div>
                </a-form-item>

                <!--页面信息-->
                <a-form-item class="form-label" label="同步到的页面信息">
                    <div class="page-info">
                        <div class="tip">渠道</div>
                        <a-tabs @change="handleTabChange" v-model="activeKey">
                            <a-tab-pane :tab="item.pipeline_name" :key="item.pipeline" v-for="(item, index) in toPipelineData">
                                <a-form-item class="page-item" label="语言">

                                    <a-checkbox
                                        :checked="checkAll"
                                        @change="onCheckAllChange"
                                        :indeterminate="indeterminate">
                                        全选
                                    </a-checkbox>

                                    <a-checkbox-group
                                        v-for="(lang, idx) in item.langList"
                                        v-model="checkedList"
                                        :disabled="lang.disabled"
                                        :key="lang.key"
                                        @change="onCheckChange">
                                        <a-checkbox :value="lang.key">
                                            {{ lang.name }}
                                            <span v-if="lang.is_default == 1" style="color: #999">(默认)</span>
                                        </a-checkbox>
                                    </a-checkbox-group>

                                </a-form-item>
                            </a-tab-pane>
                        </a-tabs>
                    </div>
                </a-form-item>

            </a-form>

            <div class="warn">温馨提示：勾选后会覆盖掉所选渠道已有数据信息且不可恢复，请谨慎勾选！</div>
        </div>
    </design-dialog>
</template>

<script>
import { ZF_getCopyPipeline, ZF_CopyPipeline } from '../../plugin/api'
export default {
    name: 'component-sync-channel',
    data () {
        return {
            channelInfo: {
                width: 960,
                title: '同步渠道信息',
                loading: false,
                visible: false
            },
            pipeline: undefined,
            fromPipelineList: [], // 被渠道数据
            lang: undefined,
            langList: [], // 语言
            pipeline_type: 1, // 同步内容 1: 页面， 3 商品数据
            syncData: [
                {
                    name: '页面',
                    value: 1
                },
                {
                    name: '商品数据',
                    value: 3
                }
            ],
            activeKey: '', // tab key
            notFoundContent: '无数据',
            toPipelineData: [], // 同步渠道
            checkAll: false, // 全选是否选中
            indeterminate: false,
            plainCheckedList: [], // 当前tab所有渠道语言
            checkedList: [], // 选择语言
            toData: []
        }
    },

    computed: {
        // 获取当前装修页面的渠道
        current_pipeline () {
            return this.$store.state.page.info.pipeline || 'ZF';
        }
    },

    methods: {
        // 获取同步渠道数据
        async getVisible () {
            const self = this;
            self.channelInfo.visible = true;

            const info = this.$store.state.page.info;
            this.page_id = info.page_id;

            const params = {
                page_id: info.page_id,
                lang: info.lang
            };
            const res = await ZF_getCopyPipeline(params);

            if (res.code === 0) {
                let pipelines = res.data.fromPipeline;
                let toPipeline = res.data.toPipeline;

                if (pipelines.length) {
                    self.pipeline = pipelines[0].pipeline;

                    this.fromPipelineList = [...pipelines]; // from
                    this.getFromLangList();
                }

                if (toPipeline.length) {
                    self.activeKey = info.pipeline;
                    this.toPipelineData = [...toPipeline]; // to
                    this.getToLangList();
                }

            }
        },

        // 确认
        handleOk () {
            this.form.validateFields(async (err, val) => {
                if (err) {
                    return;
                }
                const from = {
                    pipeline: this.pipeline,
                    lang: this.lang
                };

                const toData = this.toPipelineData.filter(item => {
                    if (item.checkedList.length) {
                        return item;
                    }
                });

                if (toData.length == 0) {
                    this.$message.error('请选择同步渠道语言!');
                    return false;
                }

                this.toData = [];
                for (let item of toData) {
                    let oData = {
                        pipeline: item.pipeline,
                        lang: item.checkedList.join(',')
                    }
                    this.toData.push(oData);
                }
                // 开始请求ajax
                this.channelInfo.loading = true;
                const params = {
                    page_id: this.page_id,
                    type: this.pipeline_type,
                    from: JSON.stringify(from),
                    to: JSON.stringify(this.toData)
                };
                const res = await ZF_CopyPipeline(params);
                if (res.code === 0) {
                    this.$message.success('数据同步成功', 2, function () {
                        window.location.reload();
                    });
                } else {
                    this.channelInfo.loading = false;
                }
            });
        },

        // 取消
        handleCancel () {
            this.channelInfo.visible = false;
            this.handleResetForm();
        },

        // 重置表单
        handleResetForm () {
            this.form.resetFields();
        },

        // 渠道改变
        handlepipelineChange (val) {
            this.pipeline = val;
            this.getFromLangList();

        },

        // 被同步获取对应语言
        getFromLangList () {
            const self = this;
            // 渠道对应语言
            const langArr = self.fromPipelineList.filter((item) => {
                return item.pipeline == self.pipeline;
            });

            if (langArr.length) {
                this.langList = [...langArr[0].langList]; // 语言
                this.lang = this.langList[0].key;
            }
            this.pipelineDisabled();
        },

        // 当前渠道下语言禁用
        pipelineDisabled () {
            const self = this;
            this.toPipelineData.map(item => {
                item.langList.map(lang => {
                    lang.disabled = false;
                    if (lang.key == self.lang && self.pipeline == item.pipeline) {
                        item.checkedList = [];
                        item.checkAll = false;
                        lang.disabled = true;
                        this.checkAll = false;
                        this.checkedList = [];
                    }
                });
            });
        },

        // 同步获取对应语言
        getToLangList (val) {
            const _val = val ? val : 'ZF'; // 默认为全球站
            const self = this;

            // 渠道添加属性, disabled 是否禁用，checkedList: 选中的语言, checkAll: 全选, 默认不全选
            this.toPipelineData.map(item => {
                item.checkedList = [];
                item.checkAll = false;
                item.langList.map(lang => {
                    lang.disabled = false;
                    if (lang.key == this.lang && self.pipeline == item.pipeline) {
                        lang.disabled = true;
                    }
                });
            });

            // 找到对应的渠道数据
            const toLangArr = [];
            this.toPipelineData.map(x => {
                if (x.pipeline == this.current_pipeline) {
                    toLangArr.push(JSON.parse(JSON.stringify(x)));
                }
            });

            // 过滤找到对应语言
            if (toLangArr.length) {
                let langList = toLangArr[0].langList;
                this.langItem = JSON.parse(JSON.stringify(langList)); // 当前tab语言，用来过滤禁用
                
                let list = langList.map((item) => {
                    let key = item.key;
                    return key;
                });
                this.plainCheckedList = [...list];
            }
        },

        // 被同步语言改变
        handleLangChange (val) {
            this.lang = val;
            this.pipelineDisabled();
        },

        // 同步内容改变
        handlePageChange (val) {
            this.pipeline_type = val;
        },

        // 同步tab改变
        handleTabChange (val) {
            this.activeKey = val; // 当前key

            // 重置 当前渠道
            const channel = this.toPipelineData.filter((item) => {
                return item.pipeline == val;
            });

            try {
                let oData = channel[0];
                let checkedList = oData.checkedList;

                let langList = oData.langList;
                this.langItem = langList;

                let list = langList.map((item) => {
                    let key = item.key;
                    return key;
                });

                // 当前默认语言
                this.plainCheckedList = [...list];

                // 选中语言, 全选
                Object.assign(this, {
                    checkedList: checkedList ? checkedList : [],
                    indeterminate: false,
                    checkAll: oData.checkAll
                });

            } catch (e) {}
        },

        // 全选
        onCheckAllChange (e) {
            const self = this;
            try {
                if (e.target.checked) {
                    this.plainCheckedList = this.langItem.filter(item => {
                        if (!item.disabled) {
                            return item;
                        }
                    }).map(item => {
                        let key = item.key;
                        return key;
                    });
                }

                Object.assign(this, {
                    checkedList: e.target.checked ? this.plainCheckedList : [],
                    indeterminate: false,
                    checkAll: e.target.checked,
                });

                this.toPipelineData.map((item) => {
                    if (self.activeKey == item.pipeline) {
                        item.checkAll = e.target.checked;
                        item.checkedList = this.checkedList;
                    }
                });
            } catch (e) {}
        },

        // 选择语言
        onCheckChange (checkedList) {
            const self = this;
            this.checkedList = checkedList; // 选中语言
            this.indeterminate = !!checkedList.length && (checkedList.length < this.plainCheckedList.length);
            this.checkAll = checkedList.length === this.plainCheckedList.length;

            this.toPipelineData.map((item) => {
                if (self.activeKey == item.pipeline) {
                    item.checkAll = this.checkAll;
                    item.checkedList = checkedList;
                }
            });
        }
    },
    beforeCreate () {
        this.form = this.$form.createForm(this);
    }
}
</script>

<style lang="less">
    .dialog-sync-channel {

        .form-label {
            margin-bottom: 0px;
        }

        .ant-form-item-label{
            line-height: 22px;
            color: #3F4245;
            font-size:16px;
            font-weight: 600;
            margin-bottom: 14px;
        }

        .info-item .ant-form-item-label {
            font-weight: 400;
            margin-bottom: 0px;
        }

        .info-item {
            display: flex;
            flex-flow: column wrap;
            margin-right: 16px;

            .tip {
                font-size:14px;
                color: #3F4245;
                line-height: 22px;
            }

            .ant-select {
                width: 288px;
            }
        }

        .page-info {
            padding-right: 32px;
            .tip {
                font-size:14px;
                color: #3F4245;
                line-height: 22px;
            }

            .page-item .ant-form-item-label{
                font-size: 14px;
                color: #3F4245;
                margin-bottom: 0px;
                font-weight: 400;
            }
        }

        .warn {
            color: red;
        }

        .ant-checkbox-disabled + span {
            span {
                color: rgba(0, 0, 0, 0.25) !important;
            }
        }
    }
</style>
