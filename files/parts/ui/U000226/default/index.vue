<template>
    <div class="geshop-u000226-default-body" :style="style_body">
        <div class="wrap">
            <div class="swiper-container">
                <div class="swiper-wrapper">
                    <div v-for="(item,index) in list" :key="index" class="swiper-slide">
                        <a :target="openType" :href="item.imgLink || 'javascript:void(0)'" :class="{'cursorDefault': !item.imgLink}">
                            <img :src="item.imgUrl" class="swiper-lazy" alt="">
                        </a>
                    </div>
                </div>
                <div class="swiper-pagination" v-show="mNavShow"></div>
                <div class="swiper-button swiper-button-prev" v-show="pcNavShow"></div>
                <div class="swiper-button swiper-button-next" v-show="pcNavShow"></div>
            </div>
        </div>
    </div>
</template>

<script>
export default {
    props: ['data'],
    data () {
        return {
            mySwiper: Object,
            mNavShow: false,
            pcNavShow: false,
            style_body: '',
            list: [],
            defaultList: [
                { imgUrl: 'https://geshopimg.logsss.com/uploads/uoWqpAkcCeRB0rz4FTImJOg3SnHadXtw.png', imgLink: '' },
                { imgUrl: 'https://geshopimg.logsss.com/uploads/uoWqpAkcCeRB0rz4FTImJOg3SnHadXtw.png', imgLink: '' },
                { imgUrl: 'https://geshopimg.logsss.com/uploads/uoWqpAkcCeRB0rz4FTImJOg3SnHadXtw.png', imgLink: '' },
                { imgUrl: 'https://geshopimg.logsss.com/uploads/uoWqpAkcCeRB0rz4FTImJOg3SnHadXtw.png', imgLink: '' },
                { imgUrl: 'https://geshopimg.logsss.com/uploads/uoWqpAkcCeRB0rz4FTImJOg3SnHadXtw.png', imgLink: '' },
                { imgUrl: 'https://geshopimg.logsss.com/uploads/uoWqpAkcCeRB0rz4FTImJOg3SnHadXtw.png', imgLink: '' }
            ]
        };
    },
    computed: {
        isEditEnv () {
            return this.data.isEditEnv;
        },
        // 获取当前端 pc/pad/wap
        media_platform () {
            return this.$store.state.dresslily.media_platform;
        },
        openType () {
            return this.media_platform === 'pc' || this.media_platform === 'pad' ? this.$root.data.is_open_new : this.$root.data.is_open_new_m;
        }
    },
    methods: {
        initSwiper (slideNum, platfrom) {
            this.$nextTick(() => {
                let pagination = null;
                let nextButton = null;
                let prevButton = null;
                let loop = false;

                if (platfrom === 'pc') {
                    this.list = this.$root.data.pc_datas || this.defaultList;
                    if (this.list.length > 6) {
                        loop = true;
                    } else {
                        this.pcNavShow = false;
                    }
                    nextButton = this.$el.querySelector('.swiper-button-next');
                    prevButton = this.$el.querySelector('.swiper-button-prev');

                    this.mySwiper = new Swiper3(this.$el.querySelector('.swiper-container'), {
                        slidesPerView: slideNum,
                        spaceBetween: 20,
                        slidesPerGroup: slideNum,
                        loop: loop,
                        lazyLoading: true,
                        watchSlidesVisibility: true,
                        loadPrevNextAmount: 2,
                        nextButton: nextButton,
                        prevButton: prevButton
                    });
                } else {
                    pagination = this.$el.querySelector('.swiper-pagination');
                    if (platfrom === 'pad') {
                        this.list = this.$root.data.pad_datas || this.defaultList;
                        if (this.list.length > 4) {
                            loop = true;
                        } else {
                            this.mNavShow = false;
                        }
                        // if (this.data.goodsInfo && Object.keys(this.data.goodsInfo).length <= 4 ) {
                        //     this.mNavShow = false
                        // }
                    } else if (platfrom === 'm') {
                        this.list = this.$root.data.m_datas || this.defaultList;
                        if (this.list.length > 2) {
                            loop = true;
                        } else {
                            this.mNavShow = false;
                        }
                    }
                    this.mySwiper = new Swiper3(this.$el.querySelector('.swiper-container'), {
                        slidesPerView: slideNum,
                        spaceBetween: 15,
                        slidesPerGroup: slideNum,
                        watchSlidesVisibility: true,
                        lazyLoading: true,
                        pagination: pagination,
                        loop: loop,
                        lazyLoadingInPrevNext: true,
                        loadPrevNextAmount: 2
                    });
                }
            });
        },
        reInitSwiper () {
            if (sessionStorage.getItem('gs_platform') == 'pc') {
                // pc
                this.list = this.$root.data.pc_datas || this.defaultList;
                this.initSwiper(6, 'pc');
                this.pcNavShow = true;
                this.mNavShow = false;
            } else if (sessionStorage.getItem('gs_platform') == 'pad') {
                // m
                this.list = this.$root.data.pad_datas || this.defaultList;
                this.initSwiper(4, 'pad');
                this.mNavShow = true;
                this.pcNavShow = false;
            } else if (sessionStorage.getItem('gs_platform') == 'wap') {
                // m
                this.list = this.$root.data.m_datas || this.defaultList;
                this.initSwiper(2, 'm');
                this.mNavShow = true;
                this.pcNavShow = false;
            }
        },
        setPlatform () {
            let platform = GLOBAL.util.getPlatform();
            if (platform === 1) {
                sessionStorage.setItem('gs_platform', 'wap');
            } else if (platform === 2) {
                sessionStorage.setItem('gs_platform', 'pc');
            } else if (platform === 3) {
                sessionStorage.setItem('gs_platform', 'pad');
            }
        }
    },
    async mounted () {
        const _this = this;

        if (_this.isEditEnv == 1) {
            // _this.currentPlatform = sessionStorage.getItem('gs_platform') || 'pc'
            sessionStorage.setItem('gs_platform', 'pc');
            // 监听当前选择的平台
            window.addEventListener('storage', () => {
                _this.reInitSwiper();
            }, false);
        } else {
            _this.setPlatform();
            // 监听窗口拖放
            window.addEventListener('resize', () => {
                setTimeout(() => {
                    _this.setPlatform();
                    _this.reInitSwiper();
                }, 300);
            }, false);
        }

        const staticDomain = GESHOP_STATIC;
        loadCss(staticDomain + '/resources/javascripts/library/swiper/swiper.min.css');
        $LAB.script(staticDomain + '/resources/javascripts/library/swiper/swiper.3.4.spec.min.js').wait(function () {
            _this.reInitSwiper();
        });
    }
};
</script>

<style lang="less" scoped>
    .geshop-u000226-default-body {
        .wrap{
            .swiper-container{
                .swiper-slide{
                    img{
                        width: 100%;
                    }
                    .cursorDefault{
                        cursor: default;
                    }
                }
                .swiper-button{
                    width: 40px;
                    height: 65px;
                    opacity: 0.7;
                    margin-top: -30px!important;
                    background-size: 100%;
                    transition: all 0.3s;

                    &:hover{
                        opacity: 1;
                        transition: all 0.3s;
                    }
                    &.swiper-button-prev{
                        background-image: url(https://geshopimg.logsss.com/uploads/c2Ipt87XRzgOy3KavesirQCbWMmnVZGF.png);
                        left: 0;
                    }
                    &.swiper-button-next{
                        right: 0;
                        background-image: url(https://geshopimg.logsss.com/uploads/xhOnwqUI85BJSlVfHFbykMcmip2u3YD7.png)
                    }
                }
            }
        }
    }

    @media (max-width: 767px) {
        .geshop-u000226-default-body {
            .wrap {
                padding:12px 20px 0 20px
            }
        }
    }

    @media (min-width: 768px) and (max-width:1024px) {
        .geshop-u000226-default-body {
            .wrap {
                padding:20px 30px 0 30px
            }
        }
    }

    @media (min-width: 1025px) {
        .geshop-u000226-default-body {
            .wrap {
                padding: 0px 100px;
            }
        }
    }
</style>
<style lang="less">
.geshop-u000226-default-body {
    .wrap {
        position: relative;
        .swiper-pagination-bullet{
            background: #c9c6c6;
            &.swiper-pagination-bullet-active{
                background:#000;
            }
        }
    }
}
</style>
