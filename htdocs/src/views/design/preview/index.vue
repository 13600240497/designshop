<template>
    <div
        :class="`design-layout-preview page-site-zf ${text_direction}`"
        @click.self="handle_release_selected">

        <!-- 主要布局，中间，最小宽度 375x667 -->
        <div class="main-layout is-app" :class="{ 'is-empty': layouts.length <= 0}">

            <!-- 拖拽区域 -->
            <draggable
                class="dragArea list-group"
                v-bind="dragOptions"
                v-model="layouts"
                id="canvas"
                group="people"
                @start="handle_drag_start"
                @end="handle_drag_end"
                @change="handle_drag_change">

                <!-- 组件对象 -->
                <div
                    class="list-group-item"
                    v-for="component in layouts"
                    :key="component.id">

                    <!-- 引入组件 -->
                    <div class="component-box">
                        <controller :id="component.id" />
                    </div>
                </div>

            </draggable>

            <!-- 测试信息 -->
            <!-- <div class="debuger-info">
                <label for="">page_layouts</label>
                <p>{{ page_layouts }}</p>
                <label for="">layouts:</label>
                <p>{{ layouts }}</p>
            </div> -->

            <!-- 空信息 -->
            <div class="is-empty" v-if="layouts.length <= 0">
                <img src="https://geshopimg.logsss.com/uploads/HguJsXhUjdbMSBARmtv1KPz9cLEq85OI.png" alt="">
                哎哟，您还没有放置组件哦~
            </div>
            
        </div>

    </div>
</template>

<script>
import draggable from 'vuedraggable'
import controller  from './controller.vue';

export default {
    components: { draggable, controller },

    data () {
        return {
            // 拖拽组件参数
            dragOptions: {
                // animation: 200, // 动画时间
                group: "description",
                disabled: false,
                // delay: 0,
                touchStartThreshold: 5,
                ghostClass: "sortable-ghost",
                dragClass: "sortable-drag",
                filter: ".controller-title, .controller-aside", // 不允许此元素拖拽
            },
            layouts: [], // 当前页面布局信息
        };
    },

    computed: {
        // 页面布局
        page_layouts () {
            return this.$store.state.page.layouts || [];
        },
        // 页面组件数据
        page_components () {
            return this.$store.state.page.components || {};
        },
        // 文案方向
        text_direction () {
            const map = ['he'];
            const lang = this.$store.state.page.info.lang || 'en';
            return map.includes(lang) ? 'rtl' : 'ltr';
        }
    },

    watch: {
        // 监听到布局顺序变更
        page_layouts (newVal) {
            this.update_layouts(newVal);
        }
    },

    methods: {

        // 更新布局数据
        update_layouts (layouts) {
            this.layouts = layouts.map(id => {
                return { id };
            });
        },

        /**
         * 开始拖拽
         */
        handle_drag_start () {
            this.$store.state.design.preview_in_drag = true;
        },

        /**
         * 结束拖拽
         */
        handle_drag_end () {
            this.$store.state.design.preview_in_drag = false;
        },

        /**
         * 拖拽结束事件变更
         */
        async handle_drag_change (data) {
            // 新增组件模式
            if (data.hasOwnProperty('added')) {
                // 判断是否允许存在多个同类型组件
                const can_added = await this.$store.dispatch('design/component_is_multiple', data.added.element.component_key);
                if (can_added) {
                    // 追加组件数据到 store
                    await this.$store.dispatch('design/component_add', Object.assign({}, data.added.element));
                    this.$store.dispatch('design/page_update_layout', this.layouts);
                    // 新增后默认打开表单项
                    this.$store.dispatch('design/form_open', data.added.element.id);
                } else {
                    // 回滚布局数据
                    this.layouts = this.layouts.filter(x => x.id != data.added.element.id);
                }
            }

            // 排序组件
            if (data.hasOwnProperty('moved')) {
                this.$store.dispatch('design/page_update_layout', this.layouts);
            }
        },

        /**
         * 释放组件选中效果
         */
        handle_release_selected () {
            this.$store.dispatch('design/form_close');
        }
    },

    created () {
        // 初次数据
        this.update_layouts(this.page_layouts);
    }
}
</script>

<style lang="less">
    @import '../../../less/zaful-font.less';
    @import '../../../less/zaful.less';
</style>

<style lang="less" scoped>

.design-layout-preview {
    position: relative;
    width: 100%;
    height: 100%;
    min-height: 100%;
    padding-top: 114px;
    padding-bottom: 100px;
    overflow-y: scroll;

    // 主要预览布局
    .main-layout {
        position: relative;
        margin: 0 auto;
        width: 375px;
        min-height: 667px;
        background: #fff;
        box-shadow:-10px 20px 30px 0px rgba(192,197,205,0.8);
        // 为空
        &.is-empty {
            .is-empty {
                display: block;
            }
        }
        // 空数据的样式
        .is-empty {
            position: absolute;
            left: 0px;
            top: 270px;
            right: 0px;
            display: none;
            text-align: center;
            color: #C7DCFF;
            font-size: 14px;
            img {
                margin: 0 auto;
                display: block;
            }
        }
    }

    // 拖拽区域
    .dragArea {
        min-height: 667px;
        > span {
            display: block;
            min-height: 667px;
        }
    }
}

// 拖拽效果
.flip-list-move {
    transition: transform 0.5s;
}

// 拖拽组件容器
.list-group-item {
    position: relative;
    &.is-hover {
        &:before {
            position: absolute;
            content: " ";
            left: 0px;
            bottom: 0px;
            top: 0px;
            right: 0px;
            border: dashed 2px #409EFF;
        }
        .component-controller {
            display: block;
        }
    }

    // 选中打开表单中
    &.is-active {
        &::before {
            position: absolute;
            content: " ";
            left: 0px;
            bottom: 0px;
            top: 0px;
            right: 0px;
            border: solid 3px #409EFF;
        }
    }
}
</style>

<style lang="less">

// 拖拽时的样式
.design-layout-preview {
    .main-layout {
        .dragArea {
            > .sortable-ghost {
                position: relative;
                list-style: none;
                width: 100%;
                height: 100px;
                line-height: 100px;
                text-align: center;
                background:rgba(64,158,255,0.3);
                cursor: move;

                i,
                p,
                .component-box {
                    display: none;
                }

                &:before {
                    position: absolute;
                    content: " ";
                    left: 0px;
                    bottom: 0px;
                    top: 0px;
                    right: 0px;
                    border: 3px solid rgba(64,158,255,1);
                }
                &:after {
                    content: "我要在这里";
                }
            }
        }
    }
}

// 测试信息
.debuger-info {
    background: #333;
    color: #fff;
    padding: 10px;
    label {
        font-weight: bold;
    }
    p {
        margin: 0px;
        margin-bottom: 10px;
    }
}
</style>
