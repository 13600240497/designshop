<template>
    <div class="sub-page-controller">
        <button @click="handle_design">
            <a-icon theme="filled" type="edit" />
            <label>装修</label>
        </button>
        
        <button @click="handle_preview">
            <a-icon theme="filled" type="eye" />
            <label>预览</label>
        </button>

        <!-- 上线按钮 -->
        <button
            v-if="show_online_button"
            @click="handle_show_dialog('onOnline')">
            <a-icon theme="filled" type="caret-up" />
            <label>上线</label>
        </button>

        <!-- 下线按钮 -->
        <button
            v-if="show_offline_button"
            @click="handle_show_dialog('onOffline')">
            <a-icon theme="filled" type="caret-down" />
            <label>下线</label>
        </button>

        
    </div>
</template>

<script>



export default {
    props: {
        info: {
            type: Object,
        },
        platform: {
            type: String
        },
        // 是否原生页面 0=否，1=是
        is_native: {
            type: Number,
            default: 0
        },
        /**
         * 渠道上下线的状态
         * 1=所有渠道都没上线
         * 4=已经下线
         * 10=部分渠道上线
         */
        group_status: {
            type: Number,
            required: true,
            default: 4
        }
    },

    data () {
        return {};
    },

    computed: {
        /**
         * 是否展示上线按钮
         * @description
         * 1. 非原生页面 
         * 2. 渠道状态=[1/4/10]
         * @param {number} group_status 渠道状态
         * @param {numer} is_native 是否原生 0=否，1=是
         * @returns {Boolean}
         */
        show_online_button () {
            const { is_native, group_status } = this;
            if (is_native == 1) {
                return false;
            } else {
                return [1,4,10].includes(group_status);
            }
        },

        /**
         * 是否展示下线按钮
         * @description
         * 1. 非原生页面 
         * 2. 渠道状态=[2/10]
         * @param {number} group_status 渠道状态
         * @param {numer} is_native 是否原生 0=否，1=是
         * @returns {Boolean}
         */
        show_offline_button () {
            const { is_native, group_status } = this;
            if (is_native == 1) {
                return false;
            } else {
                return [2,10].includes(group_status);
            }
        }
    },

    methods: {
        
        /**
         * 打开装修页
         */
        handle_design () {
            this.$emit('onDesign', this.info.id);
        },

        /**
         * 打开预览
         */
        handle_preview () {
            this.$emit('onPreview');
        },

        /**
         * 打开各自对应的模块
         */
        handle_show_dialog (event) {
            this.$emit(event, {
                page_id: this.info.id,
                group_id: this.info.group_id,
                platform: this.platform
            })
        },
    }
    
}
</script>

<style lang="less" scoped>

.sub-page-controller {
    position: absolute;
    left: 0px;
    top: 0px;
    right: 0px;
    line-height: 108px;
    background-color: rgba(255, 255, 255, 0.88);
    text-align: center;
    border-radius: 10px 10px 0 0;
    opacity: 0;
}

// 按钮
.sub-page-controller {
    > button {
        display: inline-block;
        width:48px;
        height:32px;
        line-height: 30px;
        margin: 0 6px;
        border-radius:16px;
        vertical-align: middle;
        border:1px solid rgba(64,158,255,1);
        background-color: #fff;
        cursor: pointer;
        outline: none;
        i {
            display: block;
            color: #409EFF;
            font-size: 22px;
        }
        label {
            display: none;

        }
    }
    > button:hover {
        background:rgba(64,158,255,1);
        label {
            display: block;
            color: #fff;
            font-size: 12px;
            cursor: pointer;
        }
        i {
            display: none;
        }
    }
}
</style>