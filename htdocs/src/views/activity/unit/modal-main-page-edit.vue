<template>

    <design-dialog
        :width="800"
        :title="title"
        :confirmLoading="loading"
        :visible="visible"
        @isOk="handle_confirm"
        @isCancel="handle_cancel">

        <div class="dialog-main-page-edit">
            <a-form layout="vertical" class="main-page-edit-form">
                <a-form-item label="活动名称" :hasFeedback="true" :required="true">
                    <a-input v-model="form.title"></a-input>
                </a-form-item>

                <a-form-item label="活动简介">
                    <a-textarea v-model="form.description" placeholder="请简单描述一下这个活动..." :rows="4" />
                </a-form-item>

                应用端口
                <!-- 端切换 -->
                <a-tabs
                    class="device-tab"
                    v-model="current_selected_device"
                    defaultActiveKey="pc">

                    <!-- 遍历所有应用端 pc/wap/app/web -->
                    <a-tab-pane
                        v-for="device in support_pipelines"
                        :tab="device.label"
                        :key="device.code"
                        :disabled="device.disabled">

                        <a-checkbox
                            :indeterminate="device.indeterminate"
                            :checked="device.checkAll"
                            @change="handle_device_checkall(device)">
                            全选
                        </a-checkbox>

                        <!-- 渠道切换 -->
                        <a-tabs v-model="current_selected_pipeline">
                            <!-- 遍历所有渠道 -->
                            <a-tab-pane
                                v-for="pipeline in device.pipelines"
                                :tab="pipeline.name"
                                :key="pipeline.code">

                                <a-checkbox
                                    :checked="pipeline.checkAll"
                                    @change="handle_pipeline_lang_checkall(pipeline)">
                                    全选
                                </a-checkbox>
                                
                                <!-- 遍历这个却道下的所有语言 -->
                                <a-checkbox-group
                                    v-model="pipeline.lang_list_checked"
                                    :options="pipeline.lang_list"
                                    @change="handle_lang_check(pipeline)" />

                            </a-tab-pane>
                        </a-tabs>

                        <!-- 编辑增加语言 显示同步 -->
                        <div v-if="show_sync[device.code].show" style="margin-top: 16px">
                            <a-form-item label="默认同步信息" prop="syncChannelValue">
                                <a-radio-group
                                    v-model="show_sync[device.code].selected"
                                    :options="show_sync[device.code].options" />
                            </a-form-item>
                            <p style="margin:0;color:#b7b7b7;">勾选后会同步该渠道下的子活动信息，请尽快去“编辑子页面”进行信息更新</p>
                        </div>
                        
                    </a-tab-pane>
                </a-tabs>
            </a-form>
        </div>

    </design-dialog>
</template>

<script>

import {
    getZFActivityList,
    ZF_addActivity,
    ZF_updateActivity
} from '../../../plugin/api.js';

/**
 * 初始化 “默认同步信息”
 * @param {Object} source_data 当前活动页已经创建了页面的渠道信息
 * @returns {Object}
 */
const get_sync_pipelines = (source_data) => {
    const obj = {
        pc: {
            show: false,
            selected: '', 
            options: []
        },
        wap: {
            show: false,
            selected: '',
            options: []
        },
        app: {
            show: false,
            selected: '',
            options: []
        },
        web: {
            show: false,
            selected: '',
            options: []
        }
    }
    Object.keys(obj).map(platform => {
        if (source_data && source_data[platform]) {
            Object.keys(source_data[platform]).map(pipeline => {
                Object.keys(source_data[platform][pipeline].lang_list).map(lang => {
                    const pipeline_name = source_data[platform][pipeline].name;
                    const lang_name = source_data[platform][pipeline].lang_list[lang].name;
                    obj[platform].options.push({
                        label: `${pipeline_name}-${lang_name}`,
                        value: `${pipeline}-${lang}`
                    });
                })
            });
            // 默认选中第一个
            obj[platform].selected = obj[platform].options[0].value;
        }
    });
    return obj;
}

export default {

    props: {
        // 当前站点编码
        site: {
            type: String,
        }
    },

    data () {
        return {
            title: '',
            visible: false,
            loading: false,
            form: {
                id: 0,
                title: '',
                description: ''
            },

            // 当前选中的应用端
            current_selected_device: 'pc',
            // 当权选中的渠道
            current_selected_pipeline: 'zf',

            // 存储当前系统的所有渠道信息
            support_pipelines: {},

            // 当前活动页已经设置的渠道
            activity_setting_pipelines: {},

            // 是否展示同步渠道信息的 DIV
            show_sync: {
                pc: {
                    show: false,
                    selected: '', // 选择的值（渠道+语言）
                    options: []
                },
                wap: {
                    show: false,
                    selected: '',
                    options: []
                },
                app: {
                    show: false,
                    selected: '',
                    options: []
                },
                web: {
                    show: false,
                    selected: '',
                    options: []
                }
            }
        }
    },
    
    methods: {

        /**
         * 初始化，获取所有的渠道信息和端信息，外部调用
         */
        init_site_support_pipelines (resource) {
            this.support_pipelines = Object.keys(resource).map(device => {
                return {
                    code: device,
                    label: device == 'wap' ? 'm' : device,
                    checkAll: false,
                    indeterminate: false,
                    disabled: false,
                    pipelines: Object.keys(resource[device]).map(pipeline => {
                        return {
                            code: pipeline,
                            name: resource[device][pipeline].name,
                            lang_list: Object.keys(resource[device][pipeline].lang_list).map(lang => {
                                return {
                                    label: resource[device][pipeline].lang_list[lang].name,
                                    value: lang,
                                    disabled: false
                                };
                            }),
                            checkAll: false,
                            indeterminate: false,
                            lang_list_checked: [], // 新勾选的
                            lang_list_checked_original: [], // 记录编辑模式下，已保存的数据
                        };
                    })
                }
            });
        },

        /**
         * 打开弹窗
         * @description 根据 data 判断是[新增/编辑]
         */
        show (data = {}) {
            this.visible = true;
            this.title = data.id ? '编辑活动' : '新增活动';
           
            // 如果是编辑的情况下
            if (data.id) {
                this.form.id = data.id;
                this.form.title = data.name;
                this.form.description = data.description;
                
                // 获取已经勾选的渠道
                const source_data = JSON.parse(JSON.stringify(data.group_info.support_list));
                this.activity_setting_pipelines = source_data;

                // tab选中第一个可用的应用端
                const firstPlatformCode = Object.keys(source_data)[0];
                const firstPipelinesCode = Object.keys(source_data[firstPlatformCode])[0];
                this.current_selected_device = firstPlatformCode;
                this.current_selected_pipeline = firstPipelinesCode;

                // 根据已有的数据结构，给复选框赋值，并且已选的都设置成禁用（不能取消）
                // 1. 遍历应用端
                this.support_pipelines.map((platform, platformIndex) => {
                    // 先重置
                    platform.checkAll = false;
                    platform.indeterminate = false;
                    platform.disabled = false;

                    // 2. 遍历渠道
                    platform.pipelines.map(pipeline => {
                        // 3. 先重置
                        pipeline.lang_list_checked = [];
                        pipeline.checkAll = false;
                        pipeline.indeterminate = false;

                        // 4. 获取 source_data 下的语言
                        if (source_data[platform.code]) {
                            if (source_data[platform.code][pipeline.code]) {
                                // 获取已经勾选的
                                const selected = Object.keys(source_data[platform.code][pipeline.code].lang_list);
                                pipeline.lang_list_checked = [...selected];
                                pipeline.lang_list_checked_original = [...selected];
                                // 已经勾选的设置成禁用
                                pipeline.lang_list.map(lang => {
                                    if (selected.includes(lang.value)) {
                                        lang.disabled = true;
                                    } else {
                                        lang.disabled = false;
                                    }
                                });
                            } else {
                                // 6. 如果 source_data 里面没有这个应用端，重置报告的语言列表的勾选状态
                                pipeline.lang_list_checked = [];
                                pipeline.lang_list_checked_original = [];
                                pipeline.lang_list.map(lang => {
                                    lang.disabled = false;
                                });
                            }
                        } else {
                            // 5. 如果 source_data 里面没有这个应用端，则界面上不能再选择
                            platform.disabled = true;
                        }
                    });
                });

                // 更新应用端全选按钮状态
                this.update_checkbox_sattus();

                // 给 “默认同步信息“ 赋值
                this.show_sync = get_sync_pipelines(source_data);
            }
 
            // 如果是新增活动
            else {
                // 选中第一个可用的应用端
                this.current_selected_device = this.support_pipelines[0].code;
                this.current_selected_pipeline = this.support_pipelines[0].pipelines[0].code;
                
                this.form.id = -1;
                this.form.title = '';
                this.form.description = '';
                // 全选所有
                this.support_pipelines.map(device => {
                    device.checkAll = true;
                    device.disabled = false;
                    device.pipelines.map(pipeline => {
                        pipeline.checkAll = true;
                        pipeline.indeterminate = true;
                        pipeline.lang_list_checked = pipeline.lang_list.map(lang => lang.value);
                        pipeline.lang_list_checked_original = [];
                        pipeline.lang_list.map(lang => {
                            lang.disabled = false;
                        })
                    });
                });
                // 给 “默认同步信息“ 赋值
                this.show_sync = get_sync_pipelines();
            }
        },

        /**
         * 选中当前端下的所有渠道和所有的语言
         */
        handle_device_checkall (device) {
            device.checkAll = !device.checkAll;
            device.pipelines.map(pipeline => {
                // 全选的状态
                if (device.checkAll == true) {
                    pipeline.checkAll = device.checkAll;
                    pipeline.lang_list_checked = device.checkAll ? pipeline.lang_list.map(x => x.value) : [];
                } else {
                    // 取消全选的状态，需要判断那些是已经保存到数据库里面的，这些不能取消勾选
                    pipeline.lang_list_checked = [...pipeline.lang_list_checked_original];
                    pipeline.checkAll = pipeline.lang_list.length == pipeline.lang_list_checked.length;
                }
            });
            // 更新全选状态
            this.update_checkbox_sattus();
            // 更新“默认同步信息”
            this.udpate_sync_show();
        },

        /**
         * 选中当前渠道下的所有语言
         * @param {object} pipeline
         */
        handle_pipeline_lang_checkall (pipeline) {
            pipeline.checkAll = !pipeline.checkAll;
            // 全选的状态
            if (pipeline.checkAll == true) {
                pipeline.lang_list_checked = pipeline.checkAll ? pipeline.lang_list.map(x => x.value) : [];
            } else {
                // 取消全选的状态，需要判断那些是已经保存到数据库里面的，这些不能取消勾选
                pipeline.lang_list_checked = [...pipeline.lang_list_checked_original];
            }
            this.update_checkbox_sattus();
            // 更新“默认同步信息”
            this.udpate_sync_show();
        },

        /**
         * 单个语言选择，判断是否已经全选了
         */
        handle_lang_check (pipeline) {
            pipeline.checkAll = pipeline.lang_list_checked.length == pipeline.lang_list.length;
            this.update_checkbox_sattus();
            // 更新“默认同步信息”
            this.udpate_sync_show();
        },

        /**
         * 检查每个端下面的checkbox是否已经所有都选中了
         */
        update_checkbox_sattus () {
            // 遍历端
            this.support_pipelines.map(device => {

                // 存放应用端下所有渠道的选择个数
                const lang_list = [];
                const lang_list_checked = [];

                // 遍历渠道
                device.pipelines.map(pipeline => {
                    // 临时存储单个渠道下的选择的个数
                    const in_pipeline_lang_len1 = [];
                    const in_pipeline_lang_len2 = [];

                    // 得出所有可选项
                    pipeline.lang_list.map(lang => {
                        lang_list.push(lang.value);
                        in_pipeline_lang_len1.push(lang.value);
                    });
                    // 得出所有已选的
                    pipeline.lang_list_checked.map(x => {
                        lang_list_checked.push(x);
                        in_pipeline_lang_len2.push(x);
                    });
                    // 得出渠道是否全选
                    pipeline.checkAll = in_pipeline_lang_len1.length == in_pipeline_lang_len2.length;
                });
                // 判断2个数组长度是否一样
                device.checkAll = lang_list.length == lang_list_checked.length;
            });
        },

        /**
         * 更新 “默认同步信息”
         */
        udpate_sync_show () {
            // 编辑模式下才有效
            if (this.form.id > 0) {
                this.support_pipelines.map(device => {
                    let current_arr = [];
                    let original_arr = [];
                    device.pipelines.map(pipeline => {
                        const current = [...pipeline.lang_list_checked];
                        const original = [...pipeline.lang_list_checked_original];
                        current_arr = current_arr.concat(current);
                        original_arr = original_arr.concat(original);
                    });
                    // 如果渠道少于之前的不展示
                    if (current_arr.length < original_arr.length) {
                        return false;
                    }
                    const a1 = current_arr.sort().join('');
                    const a2 = original_arr.sort().join('');
                    this.show_sync[device.code].show = a1 != a2;
                });
            }
        },

        /**
         * 保存
         */
        handle_confirm () {
            // 构造请求参数
            const request = {};
            this.support_pipelines.map(device => {
                const device_data = {
                    type: '',
                    name: this.form.title,
                    description: this.form.description,
                    start_time: 0,
                    end_time: 0,
                    site_code: `${this.site}-${device.code}`,
                    pipeline_list: {},
                    sync_pipeline: this.show_sync[device.code].selected.split('-')[0],
                    sync_lang: this.show_sync[device.code].selected.split('-')[1]
                };
                // 计算有没有选择渠道，如果当前应用端下的选择语言数量=0则不提交ajax
                let count_selected = 0;
                device.pipelines.map(pipeline => {
                    device_data.pipeline_list[pipeline.code] = pipeline.lang_list_checked;
                    // 计算有没有选择渠道，如果当前应用端下的选择语言数量=0则不提交ajax
                    if (pipeline.lang_list_checked.length > 0) {
                        count_selected++;
                    }
                });
                // 计算有没有选择渠道，如果当前应用端下的选择语言数量=0则不提交ajax
                if (count_selected > 0) {
                    request[device.code] = device_data;
                }
            });

            // 区分更新新增
            const ajax_function = this.form.id > 0 ?  ZF_updateActivity : ZF_addActivity;

            this.loading = true;
            // 异步请求
            ajax_function({
                id: this.form.id,
                platform_list: JSON.stringify(request)
            }).then(res => {
                this.loading = false;
                if (res.code == 0) {
                    this.$message.success(res.message);
                    this.$emit('success');
                    this.handle_cancel();
                    this.handle_reset();
                } else {
                    this.$message.error(res.message);
                }
            });
        },

        /**
         * 取消
         */
        handle_cancel () {
            this.visible = false;
        },

        /**
         * 重制表单
         */
        handle_reset () {
            this.form.title = '';
            this.form.description = '';
        }
    }
}
</script>

<style lang="less" scoped>

.main-page-edit-form {
    background-color: #fff;
    box-sizing: border-box;
}

</style>

<style lang="less">
.dialog-main-page-edit {
    // 端的tab
    .device-tab {
        .ant-tabs-bar {
            height: 45px;
        }
    }
}
</style>

