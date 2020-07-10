<template>
    <div class="geshop-u000164-coupon2" v-show="layOutShow">
        <div class="u000164-coupon2-box">
            <div class="coupon2-info-group">
                <!--库存条-->
                <div class="good-range-box" v-if="data.coupon_range == 0">
                    <span v-if="data.coupon_range_show == 0">{{ goodsPercent }}% {{ languages.pcs_left[lang] }}</span>
                    <span v-else>{{ couponInfo.leftCount }} {{ this.languages.pcs_left[this.lang] }}</span>
                    <div class="good-range-bar">
                        <span class="good-range-left" :style="{width: goodsPercent + '%'}"></span>
                    </div>
                </div>
                <!--未开始-->
                <ul v-if="couponInfo.coupon_active == 0" class="btn-group-state group-state-comming">
                    <li>{{ this.languages.upcoming[this.lang] }}</li>
                </ul>
                <!--进行中-->
                <ul v-else-if="couponInfo.coupon_active == 1" class="btn-group-state group-state-start">
                    <!--优惠券张数已领完-->
                    <li v-if="couponInfo.received_states == 0" class="state-all-claimed">{{ this.languages.coupons_none[this.lang] }}</li>
                    <!--优惠券可以领-->
                    <li class="btn-cursor" v-else-if="couponInfo.received_states == 1" @click="handleShowGetDia(true)">{{ this.languages.underway[this.lang] }}</li>
                    <!--去使用-->
                    <li class="btn-cursor" v-else-if="couponInfo.received_states == 2"><a :href="data.active_url || couponInfo.site_url" target="_blank">{{ this.languages.use_now[this.lang] }}</a></li>
                    <!--已使用-->
                    <li v-else>{{ this.languages.user_none[this.lang] }}</li>
                </ul>
                <!--已结束-->
                <ul v-else class="btn-group-state group-state-end" >
                    <li>{{ this.languages.ended[this.lang] }}</li>
                </ul>
            </div>
        </div>
        <!--积分兑换弹窗-->
        <div class="dia-coupon-getting" v-show="gettingShow">
            <div class="getting-main">
                <div class="getting-info">
                    <p v-html="score"></p>
                    <span class="btn-sure" @click="getCoupon()">{{ this.languages.yes[this.lang] }}</span>
                </div>
                <span class="btn-close" @click="handleShowGetDia(false)">X</span>
            </div>
        </div>
        <!--积分不够提示弹窗-->
        <div class="dia-coupon-noEnough" v-show="tipsShow">
            <div class="noEnough-main">
                <div class="noEnough-info">
                    <h3>{{ this.languages.oops[this.lang] }}!</h3>
                    <img v-if="data.model_icon && data.model_icon.length > 1" :src="data.model_icon" alt="coupon-gif" class="coupon-gif">
                    <p>{{ this.languages.oops_tips[this.lang] }}</p>
                    <p><a :href="couponInfo.checkin_url" target="_blank">{{ this.languages.get_more_score[this.lang] }}{{ this.languages.get_more_score_href[this.lang] }}</a></p>
                    <span class="btn-sure" @click="handleShowTipsDia(false)">{{ this.languages.yes[this.lang] }}</span>
                </div>
                <span class="btn-close" @click="handleShowTipsDia(false)">X</span>
            </div>
        </div>
    </div>
</template>

<script>
import languages from './language';
export default {
    props: ['pid', 'data'],
    data () {
        return {
            lang: GESHOP_LANG || 'en', // 当前语言
            couponInfo: {
                id: '',
                coupon_active: 1, // 优惠券状态: 0将要开始 1 进行中 2已结束
                received_states: 1, // 优惠券进行中状态: 0 优惠券张数已领完，1优惠券可以领 ，2  用户已领use now,  3 单人优惠券使用完
                integral: 10, // 领取该优惠券需要的积分
                leftCount: 2, // 优惠券剩余个数，不能展示负数
                maxCount: 10, // 优惠券总个数
                site_url: '//www.pc-rosegal.com.master.php5.egomsl.com/', // 站点首页
                coupon_url: '//user.pc-rosegal.com.master.php5.egomsl.com/m-ucoupon-a-couponlist.htm' // 个人优惠券中心
            },
            languages, // 多语言翻译
            score: '', //  您确定使用{score}积分兑换优惠券吗？ score 替换
            goodsPercent: 0, // 库存百分比
            gettingShow: false, // 领取提示 默认false关闭
            tipsShow: false, // 积分不够提示 默认false关闭
            layOutShow: true // 组件数据出错预览页隐藏，装修页显示默认
        };
    },
    computed: {
        couArr () {
            return this.$store.state.rosegal.coupon2Arr;
        }
    },
    mounted () {
        if (typeof this.data.active_id != 'undefined' && this.data.active_id != null && this.data.active_id != '') {
            // 填写了ID
            this.initCoupon();
        } else {
            if (this.$root.is_edit_env == 0) {
                // 预览页
                this.layOutShow = false;
            }
        }
    },
    methods: {
        // 初始化信息
        initCoupon () {
            this.layOutShow = true;
            try {
                const dataInfo = this.couArr;
                // 根据组件ID找到对应的数据
                const coupData = dataInfo.find((item) => {
                    return this.pid == item.component_id;
                });
                if (!coupData) {
                    // 数据异常
                    // data 数据容错处理，数据发生错误没有返回时隐藏
                    if (this.$root.is_edit_env == 0) {
                        // 预览页
                        this.layOutShow = false;
                    }
                } else {
                    // 正常流程
                    this.couponInfo = Object.assign({}, coupData);
                    // 优惠券剩余数量不能展示负数，展示为0
                    if (parseInt(this.couponInfo.leftCount) < 0) {
                        this.couponInfo.leftCount = 0;
                    }
                    // 计算剩余百分比
                    this.goodsPercent = Math.ceil(this.couponInfo.leftCount / this.couponInfo.maxCount * 100);
                    this.score = this.languages.use_score[this.lang].replace('{score}', '<span>' + this.couponInfo.integral + '</span>');
                }
            } catch (e) {}
        },
        /**
         * 兑换弹窗
         * @param {boolean} bool : bool 为 true 显示， false 关闭
         *
         */
        handleShowGetDia (bool) {
            if (!this.data.active_id || !this.couponInfo.id) { // 没有填写id 返回
                return false;
            }
            if (this.couponInfo.integral == 0) {
                // 消耗积分为0是直接领取
                this.getCoupon();
            } else {
                // 正常流程
                this.gettingShow = bool;
            }
        },
        /**
         * 积分不够提示弹窗
         * @param {boolean} bool : bool 为 true 显示， false 关闭
         *
         */
        handleShowTipsDia (bool) {
            this.tipsShow = bool;
        },
        // 获取优惠券
        getCoupon () {
            this.gettingShow = false; // 关闭弹窗
            const _url = GESHOP_INTERFACE.getcoupon.url;
            const content = {
                lang: this.lang,
                couponid: this.couponInfo.id,
                pipeline: (typeof GESHOP_PIPELINE != 'undefined' ? GESHOP_PIPELINE : ''),
                platform: (typeof GESHOP_PLATFORM != 'undefined' ? GESHOP_PLATFORM : '')
            };
            try {
                this.$jsonp(_url, content).then(res => {
                    // res.code 为0 领取成功
                    this.couponInfo.received_states = 2; // 更改状态为已领
                    this.couponInfo.leftCount = this.couponInfo.leftCount - 1; // 数量减1
                    this.goodsPercent = Math.ceil(this.couponInfo.leftCount / this.couponInfo.maxCount * 100); // 剩余百分比
                    GEShopSiteCommon.dialog.message(this.languages.exchange_succcess[this.lang]); // 弹出领取成功提示
                }, state => {
                    // state.code 不为 0
                    /**
                         * {"code":1,"data":{“loginurl”:"http://xxxxx?redirecturl="},"message":"请先登录"}
                         * "code":2,"data":{},"message":"您已领取过该优惠券"} /
                         {"code":3,"data":{},"message":"该优惠券已被领取完毕"}  /
                         {"code":4,"data":{},"message":"还未到优惠券可领取时间"} /
                         {"code": 5, "data": {}, "message": "积分不够"} /
                         */
                    switch (state.code) {
                    case 1: // 请先登录
                        window.location.href = state.data.loginurl + window.location.href;
                        break;
                    case 5: // 积分不够
                        this.tipsShow = true;
                        break;
                    }
                });
            } catch (e) {
            }
        }
    },
    watch: {
        couArr () {
            // 监听存放所有优惠券信息的数组 变化就重新执行初始化方法 更新信息
            this.initCoupon();
        }
    }
};
</script>
<style lang="less">
    .getting-info {
        p {
            span {
                font-size: 24px;
            }
        }
    }
</style>
<style lang="less" scoped>
    @import "coupon";
</style>
