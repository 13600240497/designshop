<template>
    <div class="geshop-component-box U0000187_lottery_wrap" :class="'lang-' + lang" ref="lottWrap">
        <div class="cont">
            <div class="member-wrap">
                <p> {{ language.change_desc.split('XX')[0] }} <span class="site-font-bold">{{ coupon_list.less || 0 }}</span> {{ language.change_desc.split('XX')[1] }}</p>
                <span @click="showDetail('Detail')">{{ language.details }}</span>
            </div>
            <!--容器-->
            <div class="lottery-box">
                <!--顶部指针-->
                <span class="lot-top"></span>
                <!--转盘背景-->
                <div class="lottery-content" :style="drawStyle" ref="lottBg">
                </div>
                <!--中间指针 -->
                <div class="btn-start" @click.self.stop="drawStart"></div>
            </div>
            <!--中奖名单-->
            <div class="lottery-user-list" ref="userList">
                <p class="titel">{{ language.congrats }}</p>
                <div class="user-list-wrap">
                    <div class="list-wrap">
                        <ul class="user-list" :style="marqueeStyle">
                            <template v-for="(item, index) in userList" >
                                <li :key="index">
                                    <p class="info"><span>{{item.email}}</span> <span>{{item.add_date}}</span></p>
                                    <p class="desc">{{item.tip}}</p>
                                </li>
                            </template>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
        <!-- 弹窗浮层 -->
        <div class="dialog-cont" :class="{ 'on':dialogShow }">
            <div class="dia-wrap">
                <component :is="compName" :userData="userData" @hideDia="hideDialog" :language="language"></component>
                <span class="btn-close" @click="hideDialog(false)"></span>
            </div>
        </div>
    </div>
</template>
<script>
import language from './lang';
import './share';
import Coupons from './dialog/coupons';
import Detail from './dialog/detail';
import Share from './dialog/share';
export default {
    data () {
        return {
            lang: window.GESHOP_LANG || 'en', // 当前语言
            userData: {}, // 分享时 coupon_info 数据
            dialogShow: false,
            language: language[window.GESHOP_LANG || 'en'], // 多语言
            compName: '',
            timer: null, // 当前计时器
            marqueeH: 0, // 滚动内容高度
            marqueeTop: 0, // 当前滚动上边距离
            marqueeStyle: {}, // 当前滚动transform
            box: null, // 组件容器
            coupon_list: {}, // 初始化数据
            userList: [
                {
                    'user_id': '19267308',
                    'prize_id': '13',
                    'prize_val': '0.01',
                    'email': 'thev**',
                    'add_time': '1568873577',
                    'add_date': '19/09/2019 01:12',
                    'tip': 'FREE SHIPPING '
                },
                {
                    'user_id': '19267308',
                    'prize_id': '13',
                    'prize_val': '0.01',
                    'email': 'thev**',
                    'add_time': '1568873577',
                    'add_date': '19/09/2019 01:12',
                    'tip': 'FREE SHIPPING3 '
                }
            ], // 中奖名单
            moveBox: null, // 转盘奖品容器
            lotteryRadius: 6, // 奖品个数
            marqueeFlag: false, // 奖品列表是否可以滚动
            enableDraw: true, // 抽奖按钮是否可以点击
            drawStyle: null // 转动角度样式
        };
    },
    components: {
        Coupons,
        Detail,
        Share
    },
    async mounted () {
        this.$store.dispatch('global/loaded', this);
        try {
            this.coupon_list = await this.getUser(); // 获取list数据
            this.initLottery(this.coupon_list);
        } catch (e) {
            console.log('err', e);
        }
        this.moveBox = this.$refs.lottBg; // 背景图片容器
        this.box = this.$refs.lottWrap; // 组件容器
        this.$nextTick(() => {
            // 滚动初始化
            this.marqueeStart();
            // 其他事件绑定
            this.bindEvent();
        });
    },
    beforeDestroy () {
        this.moveBox.removeEventListener('transitionend');
    },
    methods: {
        showDetail (compName) {
            this.renderComp(compName);
            this.showDialog();
        },
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
            this.lottReset();
        },
        // 页面弹窗和中奖名单初始化
        initLottery (data) {
            this.userList = [...this.coupon_list.list];
            if (data.is_get_coupon == 1) { // 登录刷新之后显示奖品
                this.userData = data.coupon_info;
                this.showDetail('Coupons');
            } else if (data.is_get_coupon == 2) { // 登录刷新抽奖机会不足
                this.userData = Object.assign(data.coupon_info, { diaType: 1 });
                const url = window.location.href.split('?')[0];
                $('[property="og:url"]').attr('content', `${url}?share_id=${data.coupon_info.share_id || ''}`);
                if (data.is_new_user == true) { // 新用户提示
                    this.userData = Object.assign(this.userData, { is_new_user: true });
                } else { // 老用户提示
                    this.userData = Object.assign(this.userData, { is_new_user: false });
                }
                this.showDetail('Share');
            }
        },
        // 点击抽奖
        async drawStart () {
            if (this.enableDraw) {
                try {
                    let data = await this.starRoll();
                    // this.userData = data;
                    if (data.user_id == 0 && data.login_to_get == 1) { // 需要登录
                        let currentUrl = window.location.href;
                        window.location.href = window.HTTPS_LOGIN_DOMAIN + window.JS_LANG + 'm-users-a-sign.htm?ref=' + currentUrl;
                    } else if (data.status == 1) { // 抽奖成功
                        this.$set(this.coupon_list, 'less', data.less);
                        // 转动转盘
                        this.lottStart(data);
                        this.userData = data;
                    } else if (data.status == 2) { // 次数不够了
                        // $('[property="og:url"]').attr('content', data.share_link);
                        const url = window.location.href.split('?')[0];
                        $('[property="og:url"]').attr('content', `${url}?share_id=${data.share_id || ''}`);
                        this.userData = Object.assign(data, { diaType: 2 });
                        this.showDetail('Share');
                    } else { // 其他情况 输出 msg
                        GEShopSiteCommon && GEShopSiteCommon.dialog.message(this.language.sorry);
                    }
                } catch (e) {
                    GEShopSiteCommon.dialog.message(e);
                }
            }
        },
        lottStart (data) {
            let id = data.point; // 下标从1 开始
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
                    this.completeRollEvent();
                }
            });
        },
        // 抽奖结束
        completeRollEvent () {
            this.showDetail('Coupons');
        },
        lottReset () {
            this.drawStyle = {};
        },
        // 获取信息列表等数据
        getUser () {
            return new Promise((resolve, reject) => {
                $.ajax({
                    url: window.DOMAIN + '/index.php?m=user_exclusive&a=ajax_coupon_list&t=' + new Date().getTime(),
                    // url: 'http://m.wap-rosegal.com.v1024.php5.egomsl.com/index.php?m=user_exclusive&a=ajax_coupon_list&t=' + new Date().getTime(),
                    type: 'GET',
                    dataType: 'json',
                    success: function (data) {
                        resolve(data);
                    },
                    error: function (err) {
                        reject(err);
                    }
                });
            });
        },
        // 请求中奖信息
        starRoll () {
            return new Promise((resolve, reject) => {
                $.ajax({
                    url: window.DOMAIN + '/index.php?m=user_exclusive&a=ajax_lucky_wheel&t=&' + new Date().getTime(),
                    // url: 'http://m.wap-rosegal.com.v1024.php5.egomsl.com/index.php?m=user_exclusive&a=ajax_lucky_wheel&t=&' + new Date().getTime(),
                    type: 'POST',
                    dataType: 'json',
                    data: {},
                    success: function (data) {
                        resolve(data);
                    },
                    error: function (err) {
                        reject(err);
                    }
                });
            });
        },
        // 列表滚动
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
        marqueeIng () {
            // 如果不允许滚动
            if (!this.marqueeFlag) {
                return;
            }
            // 如果允许滚动
            this.timer = window.requestAnimationFrame(this.marqueeIng);
            this.marqueeTop += 1;
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
            const _self = this;
            this.$refs.userList.addEventListener('mouseenter', function () {
                window.cancelAnimationFrame(_self.timer);
            }, false);
            this.$refs.userList.addEventListener('mouseleave', function () {
                _self.marqueeIng();
            }, false);
        }
    }
};
</script>
<style scoped lang="less">
@import "index_vue";
</style>
