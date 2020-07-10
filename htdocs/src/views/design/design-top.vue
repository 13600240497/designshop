<template>
    <div class="design-layout-top">
        <a-row type="flex" justify="space-between" align="middle" class="header">
            <a-col :span="7" class="left">
                <!--logo-->
                <div class="geshop-logo">
                    <a href="/"><i class="iconfont logo"></i></a>
                </div>

                <!--渠道-->
                <div class="channel">
                    <span class="tip">渠道：</span>
                    <a-select
                        :dropdownStyle="selectStyle"
                        @change="handlepipelineChange"
                        :dropdownMatchSelectWidth="false"
                        @dropdownVisibleChange="dropdownChange"
                        :value="pipeline"
                        class="select-channel">
                        <a-icon slot="suffixIcon" :class="[ { 'drop': dropChannel } ]" type=" iconfont geshop-icon design-arrow-down"></a-icon>

                        <a-select-option
                            v-for="(item, index) in pipelinelList"
                            :key="index"
                            :value="item.pipeline">
                            {{ item.pipeline_name }}
                        </a-select-option>
                    </a-select>
                </div>

                <!--语言-->
                <div class="language">
                    <a-select :value="langValue"
                              :dropdownStyle="selectStyle"
                              :dropdownMatchSelectWidth="false"
                              @dropdownVisibleChange="dropdownLangChange"
                              @change="handleLangChange"
                              class="select-channel">

                        <a-icon slot="suffixIcon" :class="[ { 'drop': dropDownLang } ]" type=" iconfont geshop-icon design-arrow-down"></a-icon>

                        <a-select-option
                            v-for="item in langData"
                            :key="item.key"
                            :value="item.key">
                            {{ item.name }}
                            <template v-if="item.is_default === 1">
                                <span class="default" style="color: #999;">(默认)</span>
                            </template>
                        </a-select-option>

                    </a-select>
                </div>

                <!--端口-->
                <div class="client">
                    <a-select :value="platform" style="width: 70px;" :dropdownMatchSelectWidth="false" dropdownClassName="platformCls" class="select-channel" @change="handleClientChange" @dropdownVisibleChange="dropdownVisibleChange">
                        <a-icon slot="suffixIcon" :class="[ { 'drop': dropDown } ]" type=" iconfont geshop-icon design-arrow-down"></a-icon>

                        <a-select-option v-for="(item, index) in relations" :key="index" :value="item.code">
                            <i class="iconfont geshop-icon design-platform-pc" v-if="item.code == 'pc'"></i>
                            <i class="iconfont geshop-icon design-platform-wap" v-else></i>
                            <span class="title">{{ item.code == 'pc' ?  item.name : '移动端' }}</span>
                        </a-select-option>

                    </a-select>
                </div>
            </a-col>

            <a-col :span="7" class="middle">{{ activity_title }}</a-col>

            <a-col :span="6" class="right">
                <a-select placeholder="更多操作"
                          :dropdownStyle="selectStyle"
                          :dropdownMatchSelectWidth="false"
                          @dropdownVisibleChange="dropdownMoreChange"
                          :value="moreItems"
                          class="select-channel"
                          @change="handleMoreChange">
                    <a-icon slot="suffixIcon" :class="[ { 'drop': dropMore } ]" type=" iconfont geshop-icon design-arrow-down"></a-icon>

                    <a-select-option v-for="(item, index) in moreData" :key="index" :value="item.value">
                        {{ item.name }}
                    </a-select-option>
                </a-select>

                <a href="javascript:void(0);" class="preview" @click="$store.dispatch('design/page_preview')">预览</a>
                <a href="javascript:void(0);" class="save" @click="handle_page_save">保存并继续</a>
                <a href="javascript:void(0);" class="release" @click="releaseModal.visible=true">发布</a>
            </a-col>

        </a-row>

        <!--访问-->
        <component-visit
            :visitData="visitData"
            @handleVisitOk="handleOk"
            @handleVisitCancel="handleCancel" />

        <!--生成页面模板-->
        <page-template
            :pageTemplateData="pageTemplateData"
            ref="pageTemplateData" />

        <!--选择页面模板-->
        <select-page-template :visible.sync="tplInfo.visible"></select-page-template>

        <!--同步渠道信息-->
        <page-sync-channel ref="syncChannelInfo"></page-sync-channel>

        <!-- 发布弹窗 -->
        <release-modal :visible.sync="releaseModal.visible"></release-modal>
    </div>
</template>

<script>
import componentVisit from './component-visit.vue';
import pageTemplate from './component-pageTemplate.vue';
import selectPageTemplate from './component-select-template.vue';
import pageSyncChannel from './component-sync-channel.vue';
import releaseModal from './modal-release.vue'; // 发布弹窗

export default {
    name: 'design-top',
    components: {
        componentVisit,
        pageTemplate,
        selectPageTemplate,
        pageSyncChannel,
        releaseModal,
    },
    data () {
        return {
            activity_title: '', // 活动标题
            pipeline: '',  // 当前选择渠道
            pipelinelList: [], // 渠道
            langData: [], // 语言
            langValue: '', // 当前选择语言
            relations: [], // 端口
            platform: '', // 选中端口
            moreItems: '更多操作', // 更多操作默认值
            itemVal: '', // 选择下拉当前值
            dropDown: false,
            dropDownLang: false,
            dropChannel: false,
            dropMore: false,
            moreData: [
                {
                    name: '访问',
                    value: 0
                },
                {
                    name: '生成页面模板',
                    value: 1
                },
                {
                    name: '选择页面模板',
                    value: 2
                },
                {
                    name: '同步渠道信息',
                    value: 3
                }
            ],
            selectStyle: {
                'text-align': 'center'
            },
            // 访问数据
            visitData: {
                width: 960,
                title: '访问链接',
                visible: false, // 是否显示弹窗
                isShow: false, // 是否显示访问组件
                list: [],
                tips: ''
            },
            // 生成页面模板
            pageTemplateData: {
                width: 960,
                title: '新增页面模板',
                visible: false,
                isShow: false
            },
            // 选择页面模板
            tplInfo: {
                visible: false
            },

            // 发布弹窗
            releaseModal: {
                visible: false
            }
        }
    },
    mounted () {
        const info = this.$store.state.page.info; // 当前装修页数据
        const pipelines = this.$store.state.page.pipelines; // 渠道
        const relations = this.$store.state.page.relations; // 端口

        this.group_id = info.group_id;
        this.pipelinelList = [...pipelines];
        this.activity_title = info.title;
        this.pipeline = info.pipeline;
        this.langValue = info.lang;
        this.platform = info.platform;
        const langArr = this.pipelinelList.filter((item) => {
            return item.pipeline == this.pipeline;
        });
        this.langData = [...langArr[0].langList]; // 语言

        // 端口
        this.relations = Object.keys(relations).map(key => {
            const item = relations[key];
            return {
                name: item.name,
                code: key,
                url: item.url
            }
        });
    },
    methods: {
        /**
         * 渠道改变
         * **/
        handlepipelineChange (val) {
            this.pipeline = val;
            const langArr = this.pipelinelList.filter((item) => {
                return item.pipeline == val;
            });
            this.langData = [...langArr[0].langList];
            this.langValue = this.langData[0].key;
            this.handleRouterPush();
        },

        /**
         * 语言改变
         * **/
        handleLangChange (val) {
            this.langValue = val;
            this.handleRouterPush();
        },

        /**
         * 端口改变
         * **/
        handleClientChange (val) {
            const self = this;
            self.platform = val;
            self.relations.map(item => {
                if (item.code == val) {
                    self.relationUrl = item.url;
                }
            });
            location.href = self.relationUrl;
        },

        // 端口展开下拉
        dropdownVisibleChange (open) {
            if (open) {
                this.dropDown = true;
            } else {
                this.dropDown = false;
            }
        },

        // 语言展开下拉
        dropdownLangChange (open) {
            if (open) {
                this.dropDownLang = true;
            } else {
                this.dropDownLang = false;
            }
        },

        // 渠道下拉
        dropdownChange (open) {
            if (open) {
                this.dropChannel = true;
            } else {
                this.dropChannel = false;
            }
        },

        dropdownMoreChange (open) {
            if (open) {
                this.dropMore = true;
            } else {
                this.dropMore = false;
            }
        },

        /**
         * 路由跳转
         *
         * **/
        handleRouterPush () {
            this.$router.push({
                path: '/design/native',
                query: {
                    group_id: this.group_id,
                    pipeline: this.pipeline,
                    lang: this.langValue,
                    platform: this.platform
                }
            });
        },

        /**
         * 更多操作
         * */
        handleMoreChange (val) {
            this.itemVal = val;
            switch (Number(val)) {
                // 访问
                case 0:
                    this.visitData.isShow = true;
                    break;
                // 生成页面模板
                case 1:
                    this.pageTemplateData.isShow = true;
                    this.pageTemplateData.visible = true;
                    break;
                // 选择页面模板
                case 2:
                    this.tplInfo.visible = true;
                    break;
                // 同步其他渠道信息
                case 3:
                    this.$refs.syncChannelInfo.getVisible();
                    break;
            }
        },

        /**
         * 弹窗确认
         * **/
        handleOk () {
            this.dialogInit();
        },

        /**
         * 弹窗取消
         * **/
        handleCancel () {
            this.dialogInit();
        },

        /**
         * 弹窗初始化
         * **/
        dialogInit () {
            switch (Number(this.itemVal)) {
                // 访问
                case 0:
                    this.visitData.isShow = false;
                    this.visitData.visible = false;
                    break;
            }
        },

        /**
         * 页面保存
         */
        handle_page_save () {
            this.$store.dispatch('design/page_save');
        }
    }
}
</script>

<style lang="less">

    .design-layout-top {

        // 图标
        .design-arrow-down {
            font-size: 12px !important;
            transform: scale(0.5);
            transition: 0.5s;

            &:before {
                display: block;
            }

            &.drop {
                transform: scale(0.5) rotate(180deg);
            }
        }

        // 左侧
        .left {
            .select-channel .ant-select-selection{
                border: none;
                box-shadow: none;
                height: 42px;
            }
            .select-channel .ant-select-selection__rendered{
                line-height: 38px;
            }
        }

        // 右侧
        .right {
            .select-channel .ant-select-selection{
                border: none;
                box-shadow: none;
                border-radius: 16px;
                background-color: #F0F2F5;
            }
            .ant-select-selection__placeholder, .ant-select-selection-selected-value {
                color: #3F4245;
            }
            .ant-select-selection__rendered{
                margin-left: 22px;
            }
            a {
                text-decoration: none;
            }
        }

        .design-platform-pc, .design-platform-wap {
            font-size: 24px !important;
            vertical-align: middle;
        }
        .select-channel .title{
            vertical-align: middle;
        }

        // 渠道选择
        .channel {
            .ant-select-selection-selected-value {
                color: #409EFF !important;
            }
        }
    }
    
    // 端口选择
    .platformCls {
        .design-platform-pc, .design-platform-wap{
            font-size: 24px !important;
            vertical-align: middle;
        }
        .title {
            vertical-align: middle;
        }
    }

     // 下拉菜单选中项去除字体加粗
    .ant-select-dropdown-menu-item-selected,
    .ant-select-dropdown-menu-item-selected:hover {
        font-weight: 400 !important;
    }
</style>

<style lang="less" scoped>
.design-layout-top {
    position: fixed;
    left: 0px;
    top: 0px;
    right: 0px;
    width: 100%;
    height: 50px;
    background: rgba(255,255,255,1);
    box-shadow: 2px 0px 8px 0px rgba(188,195,206,1);
    z-index: 3;

    .header {
        height: 50px;
        flex-flow: row nowrap;
    }

    .left {
        display: flex;
        flex-flow: row nowrap;
        align-items: center;
        min-width: 450px;

        .geshop-logo {
            width: 52px;
            border-right: 1px solid #E8EAEC;
            a {
                padding-left: 8px;
            }
            i {
                font-size: 32px;
            }
        }

        .channel,
        .language,
        .client{
            height: 50px;
            line-height: 50px;
            border-right: 1px solid #E8EAEC;
        }

        // 渠道选择
        .channel {
            padding-left: 16px;
            min-width: 190px;
            .select-channel {
                width: 122px;
            }
        }

        // 语言选择
        .language {
            .select-channel {
                width: 120px;
            }
            // 屏蔽默认语言的 tag
            .default {
                display: none;
            }
        }
        .client .title{
            display: none;
        }
    }

    .middle {
        display: flex;
        justify-content: center;
        align-items: center;
        color: #3F4245;
        font-size: 18px;
        font-weight:600;
    }

    .right {
        display: flex;
        flex-flow: row nowrap;
        justify-content: flex-end;
        line-height: 50px;
        align-items: center;
        min-width: 470px;

        .preview,
        .save,
        .release {
            width: 96px;
            text-align: center;
            color: #3F4245;
        }
        .preview, .save {
            border-left: 1px solid #E8EAEC;
            &:hover {
                background: #F0F2F5;
            }
        }

        .release {
            background-color: #409EFF;
            color: #ffffff;
            &:hover {
                background: #228FFF;
            }
        }

        .select-channel {
            width: 144px;
            margin-right: 25px;
        }
    }


}
</style>
