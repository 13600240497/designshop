<template>
    <div :class="classBody">
        <template v-if="img_jump_url != ''">
            <a :href="img_jump_url" target="_blank">
                <div class="rule-bg-img">
                    <img :src="img_url" class="img-url" />
                    <a href="javascript:;" @click="btnDialog" class="rule-btn" v-if="rule_btn_type == 1">{{ btn_title }}</a>
                </div>
            </a>
        </template>

        <template v-else>
            <div class="rule-bg-img">
                <img :src="img_url" class="img-url" />
                <a href="javascript:;" @click="btnDialog" class="rule-btn" v-if="rule_btn_type == 1">{{ btn_title }}</a>
            </div>
        </template>

        <!--弹窗-->
        <div class="dialog-rule" v-show="dialogRuleShow">
            <div class="dialog-mask"></div>

            <div class="dialog-container">
                <div class="dialog-close" @click="btnClose">
                    <img :src="closeImg" alt="" />
                </div>
                <div class="dialog-body">
                    <h4>{{ btn_title }}</h4>
                    <div class="content" v-html="ruleContent"></div>
                </div>
            </div>
        </div>
    </div>

</template>

<script>
export default {
    name: 'u000184-rule',
    props: ['data', 'pid'],
    data () {
        return {
            btn_title: 'RULE',
            rule_btn_type: 1,
            img_jump_url: '',
            isEditEnv: 0,
            dialogRuleShow: false,
            closeImg: 'https://geshopimg.logsss.com/uploads/xXV4Lf8FKyTEIRBOrPWvGwk3b0Q1qamg.png',
            pc_img_url: 'https://geshopimg.logsss.com/uploads/GbQJA0SDdhjUvMsEu9KL1ktNoYn3qXIP.png',
            m_img_url: 'https://geshopimg.logsss.com/uploads/J47pELy5jsPiux3OzfnNXoFhZKtH8wMg.png',
            img_url: ''
        };
    },
    created () {
        if (this.data) {
            this.isEditEnv = this.data.isEditEnv;
            this.btn_title = this.data.btn_title;
            this.rule_btn_type = this.data.rule_btn_type;
            this.img_jump_url = this.data.img_jump_url;

            if (this.data.pc_img_url !== '') {
                this.pc_img_url = this.data.pc_img_url;
            };

            if (this.data.m_img_url !== '') {
                this.m_img_url = this.data.m_img_url;
            };
        }
    },
    mounted () {
        const self = this;
        this.resizeEvent = self.debounce(this.getPlatForm, 300, { context: this });
        // 监听窗口拖放
        window.addEventListener('resize', this.resizeEvent, false);
        self.getPlatForm();
        this.$nextTick(() => {
            // img加载失败, 隐藏按钮
            let $parent = $(`.geshop-u000184-rule-body-${self.pid}`);
            $parent.find('.img-url').on('error', function () {
                self.rule_btn_type = 0;
            });
        });
    },
    beforeDestroy () {
        const self = this;
        window.removeEventListener('resize', self.resizeEvent, false);
    },
    computed: {
        classBody () {
            return 'geshop-u000184-rule-body-' + this.pid;
        },
        ruleContent () {
            if (this.data.rule_title) {
                return this.data.rule_title.replace(/\n|\r\n/g, '<br/>');
            };
        }
    },
    methods: {
        debounce (fn, wait, options) {
            wait = wait || 0;
            let timerId;

            function debounced () {
                if (timerId) {
                    clearTimeout(timerId);
                    timerId = null;
                };
                let args = Array.prototype.slice.call(arguments);
                timerId = setTimeout(() => {
                    fn.apply(options.context, args.concat(options));
                }, wait);
            }
            return debounced;
        },
        btnDialog () {
            if (this.isEditEnv == 0) {
                this.dialogRuleShow = true;
            };
        },

        btnClose () {
            this.dialogRuleShow = false;
        },

        // 针对不同端
        getPlatForm () {
            if (this.isEditEnv == 0) {
                if (typeof GLOBAL !== undefined) {
                    let type = GLOBAL.util.getPlatform();
                    switch (type) {
                    case 1:
                        // M端
                        this.img_url = this.m_img_url;
                        break;
                    case 2:
                        // PC
                        this.img_url = this.pc_img_url;
                        break;
                    case 3:
                        // pad
                        this.img_url = this.pc_img_url;
                        break;
                    };
                };
            } else {
                let type = sessionStorage.getItem('gs_platform') || 'pc';
                switch (type) {
                case 'pc':
                    // PC
                    this.img_url = this.pc_img_url;
                    break;
                case 'pad':
                    // pad
                    this.img_url = this.pc_img_url;
                    break;
                case 'wap':
                    // M端
                    this.img_url = this.m_img_url;
                    break;
                };
            };
        }
    }
};
</script>

<style lang="less" scoped>
    .dialog-rule {
        position: fixed;
        left: 0px;
        top: 0px;
        right: 0px;
        bottom: 0px;
        z-index: 1000;

        .dialog-container {
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            background:rgba(255,255,255,1);
            z-index: 1003;
            border: 2px solid #000000;

            .dialog-body {
                h4 {
                    margin-top: 20px;
                    margin-bottom: 20px;
                    font-weight:bold;
                    color:rgba(0,0,0,1);
                    text-align: center;
                }
                .content {
                    text-align: left;
                    font-weight:400;
                    color:rgba(0,0,0,1);
                    overflow-x: hidden;
                    overflow-y: auto;
                    white-space: normal;
                    word-break: keep-all;
                    word-wrap: break-word;
                }

                .content::-webkit-scrollbar {
                    width:8px;
                    height:8px;
                    background-color:#FFFFFF;
                    border-radius:10px;
                }

                .content::-webkit-scrollbar-track {
                    background-color:#FFFFFF;
                }

                .content::-webkit-scrollbar-thumb {
                    border-radius:10px;
                    -webkit-box-shadow:inset 0 0 6px rgba(0,0,0,.3);
                    background-color:#797979;
                }

            }

        }

        @media screen and (min-width: 1025px) {
            .dialog-container {
                width: 640px;
            }
            .dialog-body {
                h4 {
                    line-height:36px;
                    height: 36px;
                    font-size:30px;
                }
                .content {
                    margin: 0 auto 40px;
                    width: 580px;
                    font-size:14px;
                    line-height:28px;
                    max-height: 547px;
                    min-height: 225px;
                    padding-left: 20px;
                }

                .content::-webkit-scrollbar {
                    width:8px;
                    height:8px;
                    background-color:#FFFFFF;
                    border-radius:10px;
                }

                .content::-webkit-scrollbar-track {
                    background-color:#FFFFFF;
                }

                .content::-webkit-scrollbar-thumb {
                    border-radius:10px;
                    -webkit-box-shadow:inset 0 0 6px rgba(0,0,0,.3);
                    background-color:#797979;
                }
            }

            .dialog-close {
                position: absolute;
                top: 12px;
                right: 12px;
                cursor: pointer;
                width: 20px;
                height: 20px;
            }
        }

        @media screen and (max-width: 1024px) and (min-width: 768px) {
            .dialog-container {
                width: 640px;
            }

            .dialog-body {
                h4 {
                    line-height:36px;
                    height: 36px;
                    font-size:30px;
                }
                .content {
                    margin: 0 auto 40px;
                    width: 580px;
                    font-size:14px;
                    line-height:28px;
                    max-height: 547px;
                    min-height: 225px;
                    padding-left: 20px;
                }

                .content::-webkit-scrollbar {
                    width:8px;
                    height:8px;
                    background-color:#FFFFFF;
                    border-radius:10px;
                }

                .content::-webkit-scrollbar-track {
                    background-color:#FFFFFF;
                }

                .content::-webkit-scrollbar-thumb {
                    border-radius:10px;
                    -webkit-box-shadow:inset 0 0 6px rgba(0,0,0,.3);
                    background-color:#797979;
                }
            }

            .dialog-close {
                position: absolute;
                top: 12px;
                right: 12px;
                cursor: pointer;
                width: 20px;
                height: 20px;
            }
        }

        @media screen and (max-width: 767px) and (min-width: 0px) {
            .dialog-container {
                width: 340px;
            }

            .dialog-body {
                h4 {
                    line-height:24px;
                    height: 24px;
                    font-size:20px;
                }
                .content {
                    margin: 0 auto 20px;
                    width: 310px;
                    font-size:12px;
                    line-height:24px;
                    max-height: 313px;
                    min-height: 150px;
                    padding-left: 10px;
                }

                .content::-webkit-scrollbar {
                    width:8px;
                    height:8px;
                    background-color:#FFFFFF;
                    border-radius:10px;
                }

                .content::-webkit-scrollbar-track {
                    background-color:#FFFFFF;
                }

                .content::-webkit-scrollbar-thumb {
                    border-radius:10px;
                    -webkit-box-shadow:inset 0 0 6px rgba(0,0,0,.3);
                    background-color:#797979;
                }

            }

            .dialog-close {
                position: absolute;
                top: 10px;
                right: 10px;
                cursor: pointer;
                width: 20px;
                height: 20px;
            }
        }
    }

    .dialog-mask {
        position: absolute;
        left: 0px;
        top: 0px;
        right: 0px;
        bottom: 0px;
        background: #000;
        opacity: .2;
        z-index: 1002;
    }
</style>
