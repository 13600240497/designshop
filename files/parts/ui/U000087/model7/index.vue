<template>
    <div class="geshop-U000087-model7"  ref="coupon_model7">
        <div class="U000087-model7-wrap" >
            <!--<div class="debug" style="font-size: 16px">
                 === {{this.debug}} ====
                <br>
                {{this.couponData}}
            </div>-->
            <div class="coupon7_box">
                <ul class="coupon7_body">
                    <!--============= 优惠券左侧 =============-->
                    <li class="coupon_left">
                        <!--== 不显示数量 ==-->
                        <div class="coupon_dis_num">
                            <p class="dis_hd">
                                <span v-if="couponData.type == 2" class="currency_icon" :class="{'c_icon_right' : couponData.offerCurrency == 'EUR' || couponData.offerCurrency == 'VND'}">{{this.currency}}</span>
                                {{ this.couponData.offerAmount }}
                                <span v-if="couponData.type == 1" class="currency_icon c_icon_right">%</span>
                            </p>
                        </div>
                    </li>
                    <!--============= 优惠券左侧 end=============-->
                    <!--============= 优惠券中间信息 start====================-->
                    <li class="coupon_center">
                        <p class="center_tit_hd">{{ this.data.coupon_tit_main }}</p>
                        <p class="center_tit_bd">{{ this.data.coupon_tit_side }}</p>
                        <ul class="time_pla_info">
                            <!--==日期显示方式 英式 ==-->
                            <li v-if="data.time_display == 1" class="list_time">
                                {{ this.languages.validity }}: {{ this.validDate.start.day }} {{ this.validDate.start.month }} - {{ this.validDate.end.day }} {{ this.validDate.end.month }}
                            </li>
                            <!--==日期显示方式 美式 ==-->
                            <li v-else class="list_time">
                                {{ this.languages.validity }}: {{ this.validDate.start.month }} {{ this.validDate.start.day }} - {{ this.validDate.end.month }} {{ this.validDate.end.day }}
                            </li>
                            <!--== 不显示 ==-->
                            <li class="list_plat" v-if="data.platform_show == 0">
                                {{ this.languages[data.platform_type] || 'PC only' }}
                            </li>
                        </ul>
                    </li>
                    <!--============= 优惠券中间信息 end====================-->
                    <!--============= 优惠券右侧信息 start=============-->
                    <!--限量-->
                    <li class="coupon_right" v-if="couponData.maxCount > 0">
                        <canvas  ref="canvas" class="percent-range" v-if="couponData.received_states != 2"></canvas>
                        <div class="use_now_box" v-else>
                            <p class="coupon_code">
                                {{ couponData.coupon_code }}
                            </p>
                            <p class="coupon_code_desc">
                                {{ this.languages.coupons_received }}
                            </p>
                        </div>
                        <ul>
                            <!--未开始-->
                            <li v-if="couponData.coupon_active == 0">
                                <span class="btn-group btn-soon btn-icon">{{ this.languages.coming_soon }}</span>
                            </li>
                            <!--进行中-->
                            <li v-if="couponData.coupon_active == 1">
                                <!--优惠券已用完总数为0-->
                                <span v-show="couponData.received_states == 0" class="btn-group btn_coupons_none">{{ this.languages.coupons_none }}</span>
                                <!--用户可领取 Claim-->
                                <span v-show="couponData.received_states == 1" class="btn-group btn-claim btn-icon" @click="getCoupon()">{{ this.languages.claim }}</span>
                                <!--用户已领  Use Now-->
                                <a :href="data.site_url || couponData.site_url" v-show="couponData.received_states == 2" class="btn-group btn-useNow btn-icon">{{ this.languages.use_now }}</a>
                                <!--用户已使用完-->
                                <span v-show="couponData.received_states == 3" class="btn-group btn_user_none">{{ this.languages.user_none }}</span>
                            </li>
                            <!--已结束-->
                            <li v-if="couponData.coupon_active == 2">
                                <span class="btn-group btn-end btn-icon">{{ this.languages.ended }}</span>
                            </li>
                        </ul>
                    </li>
                    <!--不限量-->
                    <li class="coupon_right max" v-else :class="{'off': couponData.received_states == 2}">
                        <div class="use_now_box" v-if="couponData.received_states == 2">
                            <p class="coupon_code">
                                {{ couponData.coupon_code }}
                            </p>
                            <p class="coupon_code_desc">
                                {{ this.languages.coupons_received }}
                            </p>
                        </div>
                        <ul>
                            <!--未开始-->
                            <li v-if="couponData.coupon_active == 0">
                                <span class="btn-group btn-soon btn-icon">{{ this.languages.coming_soon }}</span>
                            </li>
                            <!--进行中-->
                            <li v-if="couponData.coupon_active == 1">
                                <!--优惠券已用完总数为0-->
                                <span v-show="couponData.received_states == 0" class="btn-group btn_coupons_none">{{ this.languages.coupons_none }}</span>
                                <!--用户可领取 Claim-->
                                <span v-show="couponData.received_states == 1" class="btn-group btn-claim btn-icon" @click="getCoupon()">{{ this.languages.claim }}</span>
                                <!--用户已领  Use Now-->
                                <a :href="data.site_url || couponData.site_url" v-show="couponData.received_states == 2" class="btn-group btn-useNow btn-icon">{{ this.languages.use_now }}</a>
                                <!--用户已使用完-->
                                <span v-show="couponData.received_states == 3" class="btn-group btn_user_none">{{ this.languages.user_none }}</span>
                            </li>
                            <!--已结束-->
                            <li v-if="couponData.coupon_active == 2">
                                <span class="btn-group btn-end btn-icon">{{ this.languages.ended }}</span>
                            </li>
                        </ul>
                    </li>
                    <!--============= 优惠券右侧信息 end=============-->
                </ul>
            </div>
        </div>
        <div class="dialog-msg" v-if="showDia">
            <div class="dia-box">
                <h3>{{this.languages.congrats}}</h3>
                <p v-html="diaText" class="dia-body-desc"></p>
                <p class="user_coupon_info">
                    <a
                        :href="couponData.coupon_url"
                        target="_blank"
                        v-html="languages.check_left">
                    </a>
                </p>
                <span class="close_dia" @click.prevent="closeDia"></span>
            </div>
        </div>
    </div>
</template>

<script>
/**
 * @Description: 优惠券
 * @author lifeng
 * @date 2019/5/25
*/

// 多语言翻译
// import languages from '../../U000086/model7/languages';
export default {
    props: ['data'],
    data () {
        return {
            paltform: 'wap',
            lang: GESHOP_LANG || 'en', // 语言
            $couponWrap: null, // 当前refs 的JQ对象
            canvas: null, // canvas 对象
            context: null, // canvas2d
            canvas_width: 120, // canvas 宽
            canvas_height: 120, // canvas 高
            requestId: null,
            speed: 0, // 进度初始值
            currency: '$',
            debug: {},
            boxRem: 0, // 容器宽度和设计稿 750 的倍数
            diaText: '', // 弹窗内的优惠力度文案
            showDia: false, // 是否显示弹窗
            // coupon_active: 1, // 优惠券状态（0,1,2） 0 未开始，1 进行中，2 已结束
            couponData: { // 优惠券初始化信息
                id: '1111', // 优惠券ID
                enableStartTime: '1558602939', // 优惠券可使用开始时间戳
                enableEndTime: '1561219200', // 优惠券可使用结束时间戳
                coupon_active: 1, // 优惠券状态（0,1,2） 0 未开始，1 进行中，2 已结束
                type: 2, // 1-百分比折扣，2-满减
                offerAmount: '0', // 优惠段
                offerCurrency: 'USD', // 货币
                flagCurrency: '$', // 货币符号
                maxCount: '999', // 优惠券总个数
                leftCount: 500, // 优惠券剩余个数
                coupon_code: 'ABCDEFGHIJ', // 用户领取的优惠码
                coupon_url: 'http://m.wap-zaful.com.v0528.php5.egomsl.com/m-users-a-coupon_points.htm',
                site_url: 'http://m.wap-zaful.com.v0528.php5.egomsl.com/',
                receivedCount: 0, // 当前用户领取次数
                received_states: 1// 优惠券进行中状态：（0, 1，2，3） 0 优惠券已领完，1优惠券可以领 ，2  用户已领,  3 优惠券使用完
            },
            validDate: {
                start: {
                    day: '',
                    month: '' // 转换过的月
                },
                end: {
                    day: '',
                    month: '' // 转换过的月
                }
            }
        };
    },
    computed: {
        coupStr () {
            return this.$store.state.zaful.couponModel7Arr;
        },
        languages () {
            return this.$root.languages;
        }
    },
    mounted () {
        this.updateCoupon();
    },
    updated () {
    },
    methods: {
        updateCoupon () {
            const langArr = ['en', 'de', 'fr', 'es', 'pt', 'th', 'zh-tw', 'ar', 'ru', 'it', 'tr', 'vi'];
            if (langArr.indexOf(this.lang) < 0) {
                this.lang = 'en';
            }
            this.$couponWrap = $(this.$refs.coupon_model7);
            /* if (this.data.get_time) {
                console.log('xxxx', this.data.get_time);
            } */

            if (typeof this.data.coupon_id != 'undefined' && this.data.coupon_id != null && this.data.coupon_id != '') {
                // 优惠券数据初始化，请求数据赋值
                // const url = 'http://m.wap-zaful.com.v0528.php5.egomsl.com/geshop/goods/couponlist_new';
                /* const url = GESHOP_INTERFACE.goods_couponlist_new.url;
                const data = {
                    lang: this.lang,
                    couponid: this.data.coupon_id,
                    pipeline: (typeof GESHOP_PIPELINE != 'undefined' ? GESHOP_PIPELINE : ''),
                    platform: this.paltform
                };
                const res = await this.$jsonp(url, data); */
                // 改成vuex 读取数据 合并请求
                const data = this.$store.state.zaful.couponModel7Arr.find((item) => {
                    return item.id == this.data.coupon_id;
                });
                this.couponData = Object.assign({}, data);
            }
            // $存储
            // $存储
            this.currency = this.couponData.flagCurrency;
            if (this.couponData.type == 1) { // 百分比
                this.diaText = this.languages.desc_tit.replace(/XX/g, this.couponData.offerAmount + '%');
            } else { // 直减
                // 弹窗文案 和 币种前后显示处理
                if (this.couponData.offerCurrency == 'EUR' || this.couponData.offerCurrency == 'VND') {
                    // 欧元和越南盾
                    this.diaText = this.languages.desc_tit.replace(/XX/g, this.couponData.offerAmount + this.currency);
                } else {
                    this.diaText = this.languages.desc_tit.replace(/XX/g, this.currency + this.couponData.offerAmount);
                }
            }
            // 不是不限量和不是已领的才有进度条
            if (+this.couponData.maxCount > 0 && this.couponData.received_states != 2) {
                this.draw();
                $(window).on('resize', () => {
                    this.draw();
                });
            }
            // 时间格式化 Validity: 23,May. - 5,Jun.
            this.mothTrans();
        },
        draw () {
            this.$nextTick(() => {
                // rem 和px换算
                this.boxRem = this.$couponWrap.width() / 750;
                // canvas 初始化
                this.canvas = $(this.$refs.canvas)[0];
                this.context = this.canvas.getContext('2d');
                window.devicePixelRatio ? this.setScale(window.devicePixelRatio) : this.setScale(4);
                // this.setScale(1); // 1 表示不处理锯齿
                this.drawFrame();
            });
        },
        // 处理锯齿
        setScale (d) {
            this.canvas_width = 120 * this.boxRem;
            this.canvas_height = 120 * this.boxRem;
            this.canvas.style.width = this.canvas_width + 'px';
            this.canvas.style.height = this.canvas_height + 'px';
            this.canvas.width = this.canvas_width * d;
            this.canvas.height = this.canvas_height * d;
            this.context.scale(d, d);
        },
        // 时间转换
        mothTrans () {
            let start_day = new Date(this.couponData.enableStartTime * 1000).getDate();
            let start_month = new Date(this.couponData.enableStartTime * 1000).getMonth();
            let end_day = new Date(this.couponData.enableEndTime * 1000).getDate();
            let end_month = new Date(this.couponData.enableEndTime * 1000).getMonth();
            // 获取月份的语言包
            const language_month_array = eval(this.languages.month);
            this.validDate.start.day = start_day;
            this.validDate.start.month = language_month_array[start_month];
            this.validDate.end.day = end_day;
            this.validDate.end.month = language_month_array[end_month];
        },
        // GET  获取优惠券
        getCoupon () {
            const url = GESHOP_INTERFACE.getcoupon.url;
            if (typeof this.data.coupon_id == 'undefined' || this.data.coupon_id == '' || this.data.coupon_id == null) {
                return false;
            }
            // const url = 'http://m.wap-zaful.com.v0528.php5.egomsl.com/geshop/goods/getcoupon';
            const data = {
                lang: this.lang,
                couponid: this.data.coupon_id,
                platform: this.paltform,
                pipeline: (typeof GESHOP_PIPELINE != 'undefined' ? GESHOP_PIPELINE : '')
            };
            this.$jsonp(url, data).then(res => {
                this.dealCoupon(res);
            }, err => {
                this.dealCoupon(err);
            });
        },
        /**
         * 点击GET后处理函数
         * @data // 获取的data
         */
        dealCoupon (data) {
            this.debug = data;
            switch (data.code) {
            case 0: // 领取成功
                this.couponData.received_states = 2;
                this.couponData.coupon_code = data.data.coupon_code;
                this.showDia = true;
                break;
            case 1: // 未登录
                // {# 获取优惠券未登陆状态 跳转登录页面 #}
                const login_url = GESHOP_PLATFORM === 'app' ? 'webAction://login?callback=appUserInfo()&isAlert=1' : data.data.loginurl + window.location.href;
                window.location.href = login_url;
                break;
            default:
                break;
            }
        },
        // canvas
        drawFrame () {
            const rad = Math.PI * 2 / 100; // 将360度分成100份，那么每一份就是rad度
            this.context.clearRect(0, 0, this.canvas.width, this.canvas.height); // 清除画布
            this.draCircleOut(this.canvas, this.context); // 画外圆
            let percent; // 圆环占比%
            percent = Math.floor(this.couponData.leftCount / this.couponData.maxCount * 100);
            this.speed += 2;
            if (this.speed <= percent) { // 小于百分占比 绘图动画
                this.requestId = window.requestAnimationFrame(this.drawFrame);
            } else { // 清除动画
                this.speed = percent;
                window.cancelAnimationFrame(this.requestId);
            }
            this.draCircle(this.canvas, this.context, rad, this.speed); // 画內圆 剩余数
        },
        /**
         * 绘制外圆
         * @param canvas // canvas 容器
         * @param context // canvas 2d 对象
         */
        draCircleOut (canvas, context) {
            context.save();
            context.strokeStyle = this.data.canvas_out_color || '#414141'; // 设置描边样式
            // context.lineCap = 'round';
            context.radius = 0;
            context.lineWidth = 4 * this.boxRem; // 设置线宽
            context.beginPath(); // 路径开始
            context.arc(this.canvas_width / 2, this.canvas_height / 2, 56 * this.boxRem, 0, 2 * Math.PI, false); // 用于绘制圆弧context.arc(x坐标，y坐标，半径，起始角度，终止角度，顺时针/逆时针)
            context.stroke(); // 绘制
            context.closePath(); // 路径结束
            context.restore();
        },
        /**
         * 绘制內圆
         * @param canvas // canvas 容器
         * @param context // canvas 2d 对象
         * @param rad // 弧形角度
         * @param n // 速度
         */
        draCircle (canvas, context, rad, n) {
            context.save();
            context.strokeStyle = this.data.canvas_in_color || '#FFFFFF'; // 设置描边样式
            context.lineCap = 'round';
            context.lineWidth = 4 * this.boxRem; // 设置线宽
            context.beginPath(); // 路径开始
            context.arc(this.canvas_width / 2, this.canvas_height / 2, 56 * this.boxRem, 0, n * rad, false); // 用于绘制圆弧context.arc(x坐标，y坐标，半径，起始角度，终止角度，顺时针/逆时针)
            context.stroke(); // 绘制
            context.closePath(); // 路径结束
            context.restore();

            // 绘制百分比文字
            context.fillStyle = this.data.canvas_text_color || '#FFFFFF';
            context.textAlign = 'center';

            context.font = Math.floor(28 * this.boxRem) + 'px OpenSans-Regular';
            if (this.data.text_type == 1) {
                context.fillText(this.couponData.leftCount, this.canvas_width / 2, this.canvas_height / 2);
            } else {
                context.fillText(n + '%', this.canvas_width / 2, this.canvas_height / 2);
            }
            context.font = Math.floor(22 * this.boxRem) + 'px OpenSans-Regular';
            context.fillText(this.languages.pcs_left || 'Left', this.canvas_width / 2, this.canvas_height / 2 + 22 * this.boxRem);
        },
        /**
         * 关闭弹窗
         */
        closeDia () {
            this.showDia = false;
        }
    },
    watch: {
        coupStr (val) {
            this.updateCoupon();
        }
    }
};
</script>
<style lang="less">
    .geshop-U000087-model7 {
        .dia-body-desc {
            span.cop{
                display: inline-block;
                padding: 12 / 75rem 40 / 75rem;
                font-size: 36 / 75rem;
                border-radius: 34/ 75rem;
                margin-top: 16 / 75rem;
            }
        }
    }
</style>
<style lang="less" scoped>
    @import "coupon";
</style>
