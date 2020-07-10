<template>
    <div v-show="visitData.isShow">
        <design-dialog
            :width="visitData.width"
            :title="visitData.title"
            @isOk="handleOk"
            @isCancel="handleCancel"
            :visible="visitData.visible">
            <div class="visit-list">
                <a
                    v-for="(item, index) in visitData.list"
                    :href="item.url"
                    target="_blank"
                    :key="index">
                    {{ item.name }}
                    <span v-if="item.is_default == 1">(默认)</span>
                </a>
            </div>
            <div class="visit-tips" v-if="visitData.tips != ''">
                <span>{{ visitData.tips }}</span>
                <a-button @click="handleRelease" type="primary">重新发布</a-button>
            </div>
        </design-dialog>
    </div>
</template>

<script>
import { ZF_getNewestUrls, ZF_designRelease } from '../../plugin/api'

export default {
    name: 'visit',
    props: {
        visitData: {
            type: Object
        }
    },
    watch: {
        'visitData.isShow': function (val) {
            if (val) {
                this.init();
            }
        }
    },
    methods: {
        async init () {
            const self = this;
            const info = this.$store.state.page.info;

            const params = {
                activity_id: info.activity_id,
                group_id: info.group_id
            };
            self.visitData.list = [];
            const res = await ZF_getNewestUrls(params);

            if (res.code == 0) {
                let $data = res.data;
                let $pipeline_list = $data.pipeline_list;
                self.visitData.visible = true;

                if ($pipeline_list && $pipeline_list.length > 0) {
                    for (let item of $pipeline_list) {
                        for (let lang of item.lang_list) {
                            self.visitData.list.push({
                                name: item.name + '-' + lang.lang_name,
                                url: lang.page_url,
                                is_default: lang.is_default
                            })
                        }
                    }
                }
                // 推送中，提示
                if ($data.tips && $data.tips != '') {
                    self.visitData.tips = $data.tips;
                }
            } else {
                self.visitData.isShow = false;
            }
        },
        handleOk () {
            this.$emit('handleVisitOk');
        },
        handleCancel () {
            this.$emit('handleVisitCancel');
        },

        // 重新发布
        async handleRelease () {
            const { page_id, lang } = this.$store.state.page.info;
            const request = { page_id, lang };
            this.$api.design_release_again(request).then((res) => {
                if (res.code == 0) {
                    this.$message.success('发布成功');
                    this.$emit('handleVisitCancel');
                }
            });
        }
    }
}
</script>

<style lang="less" scoped>
.geshop-dialog {
    .visit-list {
        display: flex;
        flex-flow: row wrap;
        a {
            padding: 9px 16px;
            border-radius: 4px;
            border: 1px solid #E8EAEC;
            margin-bottom: 16px;
            margin-right: 16px;
            color: #3F4245;
            span {
                color: #999;
                transition: color .3s;
            }
            &:hover {
                color: #ffffff !important;
                background-color: #409EFF;
                span {
                    color: #fff;
                }
            }
        }
    }
    .visit-tips{
        margin-top: 29px;
        font-size: 16px;
        color: #6B7075;
        padding-right: 32px;
        span {
            line-height: 22px;
            margin-right: 6px;
        }
    }
}
</style>
