<template>
    <div class="geshop-component-box U000247_lottery_wrap" :class="boxWrapMedia" ref="lottWrap">
        <div class="cont">
            <span @click="showDetail('Detail')" class="btn-rule">{{ $lang('details') }}</span>
            <!--容器-->
            <div class="lottery-box">
                <!--转盘背景-->
                <div class="lottery-content" :style="drawStyle" ref="lottBg">
                </div>
                <!--中间指针 -->
                <div class="btn-start" @click="drawStart"></div>
                <p class="merber" v-if="view_platform !== 'm' && coupon_list.is_login"> {{ $lang('change_desc').split('XX')[0] }} <span class="btn-changes">{{ app_times }}</span> {{ $lang('change_desc').split('XX')[1] }}</p>
            </div>
            <p class="merber" v-if="view_platform === 'm' && coupon_list.is_login"> {{ $lang('change_desc').split('XX')[0] }} <span class="btn-changes">{{ app_times }}</span> {{ $lang('change_desc').split('XX')[1] }}</p>

            <!--中奖名单-->
            <div class="lottery-user-list" ref="userList" v-show="coupon_list.start_in">
                <template v-if="userList.length">
                    <p class="titel">{{ $lang('congrats') }}</p>
                    <div class="user-list-wrap">
                        <div class="list-wrap">
                            <ul class="user-list" :style="marqueeStyle">
                                <template v-for="(item, index) in userList" >
                                    <li :key="index">
                                        <p class="info" :title="item.email"><span>{{ item.email }}</span></p>
                                        <p class="desc" :title="reSetText(item)"><span>{{ reSetText(item) }}</span></p>
                                    </li>
                                </template>
                            </ul>
                        </div>
                    </div>
                </template>
            </div>
            <div v-if="!coupon_list.start_in" class="lottery-user-list">
                <div class="act-comming">
                    <p>{{ $lang('lott_start') }}:</p>
                    <p class="down-time"><geshop-cutdown :times="userData.times" :type="userData.timeType"></geshop-cutdown></p>
                </div>
            </div>

        </div>
        <!-- 弹窗浮层 -->
        <div class="dialog-cont" v-if="dialogShow" >
            <div class="dia-wrap">
                <component :is="compName" :data="data" :userData="userData" @hideDia="hideDialog"></component>
                <span class="btn-close" @click="hideDialog(false)"></span>
            </div>
        </div>
    </div>
</template>
<script>
import './share';
// import TestData from './test';
import Coupons from './dialog/coupons';
import Detail from './dialog/detail';
import Share from './dialog/share';
import Message from './dialog/message';
export default {
    props: ['data', 'pid'],
    data () {
        return {
            lang: window.GESHOP_LANG || 'en', // 当前语言
            website: (window.GESHOP_SITECODE ? window.GESHOP_SITECODE.split('-')[0] : 'DL').toUpperCase(), // 站点
            pipeline_code: (window.GESHOP_SITECODE ? window.GESHOP_SITECODE.split('-')[0] : 'DL').toUpperCase() + (GESHOP_LANG == 'fr' ? GESHOP_LANG : '').toUpperCase(), // 当前语言渠道
            dialogShow: false,
            compName: '',
            user_token: '',
            timer: null, // 当前计时器
            marqueeH: 0, // 滚动内容高度
            marqueeTop: 0, // 当前滚动上边距离
            marqueeStyle: {}, // 当前滚动transform
            box: null, // 组件容器
            coupon_list: {
                start_in: 1,
                left_times: 'XXX',
                is_login: 1 // 转盘活动是否进行中
            }, //  初始化中奖列表和次数的数据
            userData: {
                params: {},
                timeType: 2,
                times: 86400,
                message: '123' // 提示消息
            }, // 点击抽奖数据
            userList: [
            ], // 中奖名单
            moveBox: null, // 转盘奖品容器
            lotteryRadius: 8, // 奖品个数
            marqueeFlag: false, // 奖品列表是否可以滚动
            enableDraw: true, // 抽奖按钮是否可以点击
            view_platform: 'pc',
            boxWrapMedia: 'geshop_dl_pc', // pc pad m 类名
            drawStyle: null // 转动角度样式
        };
    },
    components: {
        Coupons,
        Detail,
        Share,
        Message
    },
    created () {
        if (GESHOP_PLATFORM == 'app') { // app
            window.GEShopSiteCommon && window.GEShopSiteCommon.appLogin();
        }
    },
    computed: {
        app_times: {
            get () {
                return this.$store.state.dresslily.left_times;
            },
            set (val) {
                this.$store.commit('dresslily/set_left_time', val);
            }
        }
    },
    async mounted () {
        // 去处loading
        this.$store.dispatch('global/loaded', this);
        // 追加函数到队列
        this.$store.commit('dresslily/update_onresize_marque', this.resizeChange);
        this.user_token = $.cookie ? $.cookie('user-token') : '';
        await this.infoList();
        this.moveBox = this.$refs.lottBg; // 背景图片容器
        this.box = this.$refs.lottWrap; // 组件容器
        this.$nextTick(() => {
            // 滚动初始化
            window.GESHOP_PAGE_TYPE != 1 && this.marqueeStart();
            this.resizeChange();
            // 其他事件绑定
            this.bindEvent();
        });
    },
    beforeDestroy () {
        this.moveBox.removeEventListener('transitionend');
    },
    methods: {
        async infoList () {
            if (this.data.act_code) {
                this.lotteryRadius = this.data.prize_amount;
                try {
                    // =====================================>>>>>>>>>> step 1 <<<<<< ======================================
                    let res = await this.getInfo(this.data.act_code); // 获取抽奖活动数据
                    // let res = TestData.coupon_list;
                    if (res.status === 500) { // 活动出错
                        // this.enableDraw = false;
                        this.coupon_list.is_login = 0;
                        window.GEShopSiteCommon && GEShopSiteCommon.dialog.message(`error! ${res.msg}`);
                    } else if (res.status === 403) { // 未登录、登录失效
                        this.coupon_list.is_login = 0;
                    } else {
                        this.coupon_list = Object.assign({}, this.coupon_list, res.data); // 存放活动信息
                        this.app_times = this.coupon_list.left_times;
                        if (this.coupon_list.start_timestamp > parseInt(Date.now() / 1000)) {
                            this.coupon_list.start_in = 0;
                            this.setCutDowm();
                        }
                        // =====================================>>>>>>>>>> step 2 <<<<<< ======================================
                        this.initLottery(this.coupon_list); // 初始化样式和名单
                    }
                } catch (e) {
                    console.log('err', e);
                }
            } else {
                // 默认没有填写活动ID的时候
                this.userList = new Array(20).fill({
                    'email': 'dai***@globalegrow.com',
                    'created_time': '1551251980',
                    'prize_type': 2,
                    'points': '20',
                    'win_time': '2019-02-27 14:19:40'
                });
                // this.enableDraw = false;
            }
        },
        setCutDowm () {
            this.$set(this.userData, 'times', this.coupon_list.start_timestamp - parseInt(Date.now() / 1000));
            this.$set(this.userData, 'timeType', this.userData.times - 86400 > 0 ? 2 : 1);
        },
        // 宽度变化
        resizeChange () {
            // this.setPlatform();
            $(this.box).find('.cont, .dia-wrap').css('zoom', 1);
            const newValue = document.body.clientWidth || document.documentElement.clientWidth;
            let boxWrapMedia = '';
            if (newValue >= 1025) {
                // pc
                this.view_platform = 'pc';
                boxWrapMedia = 'geshop_dl_pc';
                $(this.box).css('height', this.data.box_bg_h || 530);
            } else if (newValue <= 1024 && newValue >= 768) {
                // pad
                this.view_platform = 'pad';
                boxWrapMedia = 'geshop_dl_pad';
                $(this.box).find('.cont, .dia-wrap').css('zoom', newValue / 1075);
                $(this.box).css('height', this.data.box_bg_h * (newValue / 1075));
            } else if (newValue <= 767) {
                // m
                this.view_platform = 'm';
                boxWrapMedia = 'geshop_dl_wap geshop_dl_m';
                $(this.box).find('.cont, .dia-wrap').css('zoom', newValue / 375);
                $(this.box).css('height', this.data.box_bg_h_m * (newValue / 375) || 562);
            }
            this.boxWrapMedia = boxWrapMedia;
        },
        // 显示那个弹窗
        showDetail (compName) {
            this.renderComp(compName);
            this.showDialog();
        },
        // 弹窗组件名称
        renderComp (compName) {
            this.compName = compName;
        },
        // 显示弹窗
        showDialog () {
            this.dialogShow = true;
        },
        // 关闭弹窗
        hideDialog (flag) {
            this.dialogShow = flag;

            if (this.enableDraw) {
                this.lottReset();
            }
            // this.lottReset();
        },
        // 奖品多语言处理
        reSetText (data) {
            let text = '';
            switch (data.prize_type) {
            case 1: // 1优惠券
                // nomail 1免邮 0非免邮
                if (data.coupon_json.no_mail == 1) {
                    text = this.$lang('free_coupon');
                } else {
                    let yh = data.coupon_json.youhuilv ? data.coupon_json.youhuilv.split(',') : ['0-0'];
                    let youhuilv = yh.length > 0 ? yh[yh.length - 1].split('-') : yh[0];
                    if (data.coupon_json.coupon_type == 1) {
                        // 1百分比
                        if (youhuilv.length === 1) {
                            text = this.$lang('coupon_text_percent').replace('XX', youhuilv[0]).replace('YYQQ', '').replace('QQYY', '');
                        } else {
                            text = this.$lang('coupon_text_percent').replace('XX', youhuilv[1]).replace('YY', youhuilv[0]).replace(/QQ/g, data.coupon_json.sign || '$');
                        }
                    } else {
                        // 2直减
                        if (youhuilv.length === 1) {
                            text = this.$lang('coupon_text_decrease').replace('YYQQ', '').replace('QQYY', '').replace('XX', youhuilv[0]).replace('QQ', data.coupon_json.sign || '$');
                        } else {
                            text = this.$lang('coupon_text_decrease').replace('XX', youhuilv[1]).replace('YY', youhuilv[0]).replace(/QQ/g, data.coupon_json.sign || '$');
                        }
                    }
                }
                break;
            case 2: // 2积分
                text = this.$lang('lott_point').replace(/XX/g, data.points);
                break;
            case 3: // 3商品
                text = this.$lang('free_item');
                break;
            default:
                text = '';
            }
            return text;
        },
        // 中奖名单初始化
        initLottery (data) {
            this.userList = [...data.win_list]; // 中奖名单初始化
        },
        isContain (aa, bb) {
            if (!(aa instanceof Array) || !(bb instanceof Array) || ((aa.length < bb.length))) {
                return false;
            }
            let aaStr = aa.toString().toLowerCase();
            for (let i = 0; i < bb.length; i++) {
                if (aaStr.indexOf(bb[i].toLowerCase()) < 0) {
                    return false;
                }
            }
            return true;
        },
        // =====================================>>>>>>>>>> step 3 <<<<<< ======================================
        // 点击抽奖
        async drawStart () {
            if (this.enableDraw) {
                this.enableDraw = false;
                try {
                    let url = window.GESHOP_INTERFACE.user_info.url;
                    let userInfo = await this.$jsonp(url, {
                        lang: GESHOP_LANG,
                        pipeline: GESHOP_SITECODE
                    });
                    // 未开始
                    if (this.coupon_list.start_in == 0) {
                        this.setCutDowm();
                        this.showNone('not_start');
                        this.enableDraw = true;
                    } else {
                        // =====================================>>>>>>>>>> step 4 <<<<<< ======================================
                        if (userInfo.data.elf_user_token) {
                            let res = await this.starRoll(this.data.act_code, userInfo.data.elf_user_token); // 获取抽奖活动数据
                            this.enableDraw = true;
                            // let res = TestData.lott_list;
                            switch (res.status) {
                            case 200: // 抽奖成功
                                // this.userData = Object.assign({}, this.coupon_list, res.data);
                                this.userData = Object.assign({}, this.userData, res.data);
                                this.app_times = this.userData.left_times;
                                this.$set(this.coupon_list, 'left_times', this.userData.left_times);
                                // 转动转盘
                                // =====================================>>>>>>>>>> step 5 <<<<<< ======================================
                                this.lottStart(this.userData);
                                break;
                            case 403: // 用户未登录
                                this.loginAction();
                                break;
                            case 601: // 用户没有抽奖机会了 I'm sorry, you don't have a lucky draw today
                                let len = GESHOP_PLATFORM == 'app' ? 1 : this.data.share_data.length;
                                if (this.coupon_list.is_share_award == 1 && len) {
                                    this.userData = Object.assign({}, this.coupon_list, res.data);
                                    // 分享可以增加次数
                                    let flag = GESHOP_PLATFORM == 'app' ? this.isContain(this.userData.share_channel, ['facebook', 'messenger']) : this.isContain(this.userData.share_channel, this.data.share_data);
                                    if (flag) {
                                        // 分享次数已用完
                                        this.showNone(this.$lang('no_chances_two'));
                                    } else {
                                        // 弹出分享框
                                        let params = {
                                            act_code: this.data.act_code,
                                            user_token: userInfo.data.elf_user_token,
                                            share_channel: '',
                                            website: this.website,
                                            pipeline_code: this.pipeline_code,
                                            language: this.lang,
                                            id: this.pid
                                        };
                                        this.$store.commit('dresslily/update_lott_params', params);
                                        this.$set(this.userData, 'message', this.$lang('no_chances_one'));
                                        this.$set(this.userData, 'params', params);
                                        this.showDetail('Share');
                                    }
                                } else {
                                    // 分享不可以增加次数
                                    this.showNone(this.$lang('no_chances_two'));
                                }
                                break;
                            case 602: // 请耐心等待上一个抽奖进程结束 The draw failed, please try again later
                                this.showNone(this.$lang('lott_fail'));
                                break;
                            case 603: // 活动未在有效期内，已结束不能抽奖
                                this.showNone(this.$lang('lott_end'));
                                break;
                            case 604: // 活动无效 "Invalid Activity!"
                                // this.$set(this.userData, 'message', this.$lang(lott_fail);
                                this.showNone(this.$lang('lott_fail'));
                                break;
                            case 605: // 用户信息无效，用户不存在
                                this.showNone(this.$lang('lott_fail'));
                                break;
                            case 606: // 无奖品 Did not win, we can try again next time oh！
                                this.showNone(this.$lang('lott_fail'));
                                break;
                            case 500: // 其他
                                window.GEShopSiteCommon && GEShopSiteCommon.dialog.message(`error! ${res.msg}`);
                                break;
                            }
                        } else {
                            this.loginAction();
                            setTimeout(() => {
                                this.enableDraw = true;
                            }, 70);
                            // GEShopSiteCommon.dialog.message(this.$lang(lott_fail);
                        }
                    }
                } catch (e) {
                    console.log('err');
                }
            }
        },
        // 登陆
        loginAction () { // 登陆逻辑
            if (GESHOP_PLATFORM == 'app') { // app
                window.GEShopSiteCommon && GEShopSiteCommon.appLogin(1);
            } else {
                const url = window.location.href;
                window.location.href = window.HTTPS_LOGIN_DOMAIN + '/' + window.JS_LANG + 'm-users-a-sign.htm?ref=' + url;
            }
        },
        // 抽奖转盘开始转动
        // =====================================>>>>>>>>>> step 6 <<<<<< ======================================
        lottStart (data) {
            let id = data.prize_id; // 下标从1 开始
            this.enableDraw = false; // 设置不可点击
            const rote = 360 / this.lotteryRadius / 2 + (1 - id / this.lotteryRadius + 10) * 360;
            this.drawStyle = {
                '-ms-transform': `rotateZ(${rote}deg)`,
                'transform': `rotateZ(${rote}deg)`,
                'transition': `transform 5s cubic-bezier(0.35, -0.005, 0.565, 1.005) 0s`,
                '-ms-transition': `transform 5s cubic-bezier(0.35, -0.005, 0.565, 1.005) 0s`
            };
            // 抽奖结束
            let animateEndBool = false; // 防止reset时 transitionend 再次触发
            this.moveBox.addEventListener('transitionend', () => {
                if (!animateEndBool) {
                    this.drawStyle = {
                        '-ms-transform': `rotateZ(${rote}deg)`,
                        'transform': `rotateZ(${rote}deg)`
                    };
                    this.enableDraw = true;
                    animateEndBool = true;
                    // =====================================>>>>>>>>>> step 7 <<<<<< ======================================
                    this.completeRollEvent(data);
                }
            });
        },
        // 抽奖结束
        // =====================================>>>>>>>>>> step 7<<<<<< ======================================
        completeRollEvent (data) {
            if (data.has_win) {
                // 中奖
                this.showSuccess(data);
                this.infoList();
            } else {
                // 未中奖
                this.showNone(this.$lang('sorry'));
            }
        },
        // 通用消息弹窗
        // =====================================>>>>>>>>>> step 8 <<<<<< ======================================
        showNone (text) {
            this.$set(this.userData, 'message', text);
            this.showDetail('Message');
        },
        // 中奖消息弹窗
        // =====================================>>>>>>>>>> step 8 <<<<<< ======================================
        showSuccess (data) {
            let text = '';
            let name = 'Coupons';
            switch (data.prize_type) {
            case 1: // 1优惠券
                // nomail 1免邮 0非免邮
                if (data.coupon_json.no_mail == 1) {
                    text = this.$lang('lott_free_coupon');
                } else {
                    let yh = data.coupon_json.youhuilv ? data.coupon_json.youhuilv.split(',') : ['0-0'];
                    let youhuilv = yh.length > 0 ? yh[yh.length - 1].split('-') : yh[0];
                    if (data.coupon_json.coupon_type == 1) {
                        // 1百分比
                        if (youhuilv.length === 1) {
                            text = this.$lang('win_pri_percent').replace('XX', youhuilv[0]).replace('YYQQ', '').replace('QQYY', '');
                        } else {
                            text = this.$lang('win_pri_percent').replace('XX', youhuilv[1]).replace('YY', youhuilv[0]).replace(/QQ/g, data.coupon_json.sign || '$');
                        }
                    } else {
                        // 2直减
                        if (youhuilv.length === 1) {
                            text = this.$lang('win_pri_decrease').replace('YYQQ', '').replace('QQYY', '').replace('XX', youhuilv[0]).replace('QQ', data.coupon_json.sign || '$');
                        } else {
                            text = this.$lang('win_pri_decrease').replace('XX', youhuilv[1]).replace('YY', youhuilv[0]).replace(/QQ/g, data.coupon_json.sign || '$');
                        }
                    }
                }
                break;
            case 2: // 2积分
                text = this.$lang('win_point').replace(/XX/g, data.points);
                break;
            case 3: // 3商品
                text = this.$lang('wind_free');
                break;
            default:
                text = '';
            }
            this.$set(this.userData, 'message', text);
            if (data.prize_type === 3) {
                let img = new Image();
                img.src = this.userData.goods_img;
                img.onload = () => {
                    this.showDetail(name);
                };
            } else {
                this.showDetail(name);
            }
        },
        // =====================================>>>>>>>>>> 重置转盘样式 <<<<<< ======================================
        lottReset () {
            this.drawStyle = {};
        },
        // 获取信息列表等数据
        // =====================================>>>>>>>>>> step 1 <<<<<< ======================================
        getInfo (act_code) {
            let url = GESHOP_INTERFACE.elf_webgame_info.url;
            let data = {
                act_code: act_code,
                user_token: this.user_token,
                website: this.website,
                pipeline_code: this.pipeline_code,
                language: this.lang
            };
            return new Promise((resolve, reject) => {
                $.ajax({
                    url: url,
                    type: 'GET',
                    cache: true,
                    dataType: 'jsonp',
                    data: data,
                    timeout: 3000,
                    jsonpCallback: `geshop_callback_${this.$root.pageInstanceId}`,
                    success: function (res) {
                        resolve(res);
                    },
                    error: function (res) {
                        reject(res);
                    }
                });
            });
        },
        // 中奖信息
        // =====================================>>>>>>>>>> step 3 <<<<<< ======================================
        starRoll (act_code, user_token = this.user_token) {
            let url = GESHOP_INTERFACE.elf_webgame_do_lottery.url;
            let data = {
                act_code: act_code,
                user_token: user_token,
                website: this.website,
                share_code: this.coupon_list.share_code || '',
                pipeline_code: this.pipeline_code,
                language: this.lang
            };
            return new Promise((resolve, reject) => {
                $.ajax({
                    url: url,
                    type: 'GET',
                    cache: true,
                    dataType: 'jsonp',
                    data: data,
                    timeout: 3000,
                    jsonpCallback: `geshop_callback_${this.$root.pageInstanceId}`,
                    success: function (res) {
                        resolve(res);
                    },
                    error: function (res) {
                        reject(res);
                    }
                });
            });
        },
        // 列表滚动初始化
        marqueeStart () {
            const listH = $(this.box).find('.list-wrap').height(); // 获取滚动容器高度
            this.marqueeH = $(this.box).find('.user-list').height(); // 获取最大滚动高度
            // 列表高度超过容器才可以滚动
            if (this.marqueeH > listH) {
                // 设置可以滚动
                this.marqueeFlag = true;
                // 复制一份当前所有li
                const li = $(this.box).find('.user-list li').clone();
                $(this.box).find('.user-list').append(li);
                // 滚动
                this.marqueeIng();
            }
        },
        // 列表滚动
        marqueeIng () {
            // 如果不允许滚动
            if (!this.marqueeFlag) {
                return;
            }
            // 如果允许滚动
            this.timer = window.requestAnimationFrame(this.marqueeIng);
            if (this.view_platform === 'm') {
                this.marqueeTop += 0.5;
            } else {
                this.marqueeTop += 1;
            }

            this.marqueeStyle = {
                '-ms-transform': `translateY(${-this.marqueeTop}px)`,
                'transform': `translateY(${-this.marqueeTop}px)`
            };
            // 滚动到原始容器高度时 初始化顶部高度为0
            if (this.marqueeTop >= this.marqueeH) {
                this.marqueeTop = 0;
            }
        },
        bindEvent () {
            if (this.view_platform === 'pc' && GESHOP_PLATFORM != 'app') {
                const _self = this;
                this.$refs.userList.addEventListener('mouseenter', function () {
                    window.cancelAnimationFrame(_self.timer);
                }, false);
                this.$refs.userList.addEventListener('mouseleave', function () {
                    _self.marqueeIng();
                }, false);
            }
        }
    }
};
</script>
<style lang="less">
    /*body {
        -webkit-transform: translate3d(0, 0, 0);
        transform: translate3d(0, 0, 0);
    }*/
    .U000247_lottery_wrap {
        .btn {
            background-color: #DF4D34;
            height:41px;
            line-height: 41px;
            text-align: center;
            color: #ffffff;
            display: inline-block;
            cursor: pointer;
            width: 171px;
            margin: 0 10px;
            &.btn_ok {
                margin: 30px auto 0;
            }
        }
        .dia-wrap {
            h2 {
                text-align: center;
                color: #333333;
                font-size: 28px;
                font-weight: 900;
            }
        }
        .btn-share {
            &.pinterest {

                background: url("https://uidesign.drlcdn.com/DL/image/2019/20191029_13494/p.png") 0 0 no-repeat;
                background-size: 100% 100%;
            }
            &.twitter {

                background: url("https://uidesign.drlcdn.com/DL/image/2019/20191029_13494/tw.png") 0 0 no-repeat;
                background-size: 100% 100%;
            }
            &.tumblr {

                background: url("https://uidesign.drlcdn.com/DL/image/2019/20191029_13494/tu.png") 0 0 no-repeat;
                background-size: 100% 100%;
            }
            &.app-share {
                background: url("https://geshopimg.logsss.com/uploads/R64E1K9GoDtCZ2yQVi0srqIUHjYbmTvh.png") 0 0 no-repeat;
                background-size: 100% 100%;
            }
        }
    }
    @media (max-width: 767px) {
        .U000247_lottery_wrap {
            .btn {
                height:30px;
                line-height: 30px;
                width: 120px;
                font-size: 12px;
                margin: 0 11px;
                &.btn_ok {
                    margin: 19px 5px 0;
                }
            }
            .dia-wrap {
                h2 {
                    text-align: center;
                    color: #333333;
                    font-size: 14px;
                    font-weight: 900;
                }
            }
        }
    }
</style>
<style scoped lang="less">
@import "index_vue";
</style>
