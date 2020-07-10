<template>
    <div class="geshop-U000232-model1-body" :style="style_body">
        <div class="wrap geshop-U000232-model1-wrapper">
            <div class="gs-btn-area text-center">
                <a :href="data.btn_link||'javascript:;'" :target="data.target_blank == '1' ? '_blank':'_self' " class="gs-btn btn-primary">{{data.btn_text || 'Button' }}</a>
            </div>
        </div>
    </div>
</template>

<script type="text/javascript">
export default {
    name: 'geshop-U000232-model1-body',
    props: ['theme', 'pid', 'data'],
    data () {
        return {
            bodyWidth: 0,
            platform: '',
            style_body: '',
            thumbnail_img: {
                pc: '',
                pad: '',
                m: ''
            },
            classIco: this.data.box_banner_ico == '0',
            defaultImg: 'https://geshopimg.logsss.com/uploads/HsvKLCn5tj7pQEPJzXgcrSuM3NhUBkFl.png'
        };
    },
    watch: {
        bodyWidth (newValue, oldValue) {
            if (newValue >= 1025) {
                // pc
                this.platform = 'pc';
            } else if (newValue <= 1024 && newValue >= 768) {
                // pad
                this.platform = 'pad';
            } else if (newValue <= 767) {
                // m
                this.platform = 'm';
            }
        }
    },
    computed: {
        style_body_pc () {
            return {
                'margin-top': `${this.data.pc_marginTop || 0}px`,
                'margin-bottom': `${this.data.pc_marginBottom || 32}px`
            };
        },
        style_body_pad () {
            return {
                'margin-top': `${this.data.pad_marginTop || 0}px`,
                'margin-bottom': `${this.data.pad_marginBottom || 32}px`
            };
        },
        style_body_m () {
            return {
                'margin-top': `${this.data.m_marginTop || 0}px`,
                'margin-bottom': `${this.data.m_marginBottom || 20}px`
            };
        }
    },

    created () {
        // 默认图片
        this.thumbnail_img.pc = 'https://geshopimg.logsss.com/uploads/HsvKLCn5tj7pQEPJzXgcrSuM3NhUBkFl.png';
    },

    mounted () {
        const that = this;

        // 获取组件宽度
        window.onresize = () => {
            return (() => {
                that.bodyWidth = document.body.clientWidth;
            })();
        };

        // 初始化页面宽度
        that.$nextTick(() => {
            // 预览页 m = 1 pc =2 ipad = 3
            let cw = document.body.clientWidth;
            if (that.isEditEnv == 0) {
                try {
                    const platformt = typeof GLOBAL.util.getPlatform() != 'undefined' ? GLOBAL.util.getPlatform() : 2;
                    if (platformt == 1) {
                        // m
                        that.platform = 'm';
                    } else if (platformt == 2) {
                        // pc
                        that.platform = 'pc';
                    } else if (platformt == 3) {
                        // pad
                        that.platform = 'pad';
                    }
                } catch (e) {}
            } else {
                // 编辑模式
                if (cw >= 1025) {
                    // pc
                    that.platform = 'pc';
                } else if (cw <= 1024 && cw >= 768) {
                    // pad
                    that.platform = 'pad';
                } else if (cw <= 767) {
                    // m
                    that.platform = 'm';
                }
            }
        });
    }
};
</script>

<style lang="less" scoped>
    .geshop-U000232-model1-body {
        .wrap {
            width: 100%;
            height: 100%;
            position: relative;
            .item-list-box {
                width: 100%;
                position: relative;

                a {
                    margin-right: 20px;
                    display: inline-block;

                    img {
                        border: 0;
                        display: block;
                    }
                }
            }
        }
    }

    @media screen and (min-width: 1025px) {
        .geshop-U000232-model1-body {
            .wrap {
                color: #333;
            }
        }
    }

    @media screen and (max-width: 1024px) and (min-width: 768px) {
        .geshop-U000232-model1-body {
            .wrap {
                color: #333;
            }
        }
    }

    @media screen and (max-width: 767px) and (min-width: 0px) {
        .geshop-U000232-model1-body {
            .wrap {
                color: #333;
            }
        }
    }
</style>
