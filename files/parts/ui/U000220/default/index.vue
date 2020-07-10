<template>
    <div class="geshop-u000220-default">
        <div class="geshop-u000220-default-wrapper">
            <div class="item-logo"></div>
            <div class="item-list item-for-pc" @mouseenter="handleMounseEnter" @mouseleave="handleMounseLeave">
                <ul>
                    <template v-for="(item, key) in list.pc">
                        <li :key="key" :class="get_status_class(item)">
                            <div class="li-item-table">
                                <div class="li-item-td">
                                    <div class="item-date"><span>{{ item.timeRange | date_label }}</span></div>
                                    <div class="item-title" v-if="item.activityName">{{ item.activityName }}</div>
                                    <div class="item-desc" v-if="item.activityDesc">{{ item.activityDesc }}</div>
                                </div>
                            </div>
                        </li>
                        <li class="item-arrow"><img :src="icon_arrow.pc"></li>
                    </template>
                </ul>
            </div>
            <div class="item-list item-for-pad">
                <ul>
                    <template v-for="(item, key) in list.pc">
                        <li :key="key" :class="get_status_class(item)">
                            <div class="li-item-table">
                                <div class="li-item-td">
                                    <div class="item-date"><span>{{ item.timeRange | date_label }}</span></div>
                                    <div class="item-title" v-if="item.activityName">{{ item.activityName }}</div>
                                    <div class="item-desc" v-if="item.activityDesc">{{ item.activityDesc }}</div>
                                </div>
                            </div>
                        </li>
                        <li class="item-arrow"><img :src="icon_arrow.pc"></li>
                    </template>
                </ul>
            </div>
            <div class="item-list item-for-m">
                <ul>
                    <template v-for="(item, key) in list.pc">
                        <li :key="key" :class="get_status_class(item)">
                            <div class="li-item-table">
                                <div class="li-item-td">
                                    <div class="item-date"><span>{{ item.timeRange | date_label }}</span></div>
                                    <div class="item-title" v-if="item.activityName">{{ item.activityName }}</div>
                                    <div class="item-desc" v-if="item.activityDesc">{{ item.activityDesc }}</div>
                                </div>
                            </div>
                        </li>
                        <li class="item-arrow"><img :src="icon_arrow.pc"></li>
                    </template>
                </ul>
            </div>
            <!-- 日历 -->
            <div
                class="item-calendar"
                @click.stop="handleClickCalendar"
                @mouseenter="handleMouseEnterCalendar"
                @mouseleave="handleMouseLeaveCalendar">
                <!-- 日历弹窗1 -->
                <div class="geshop-u000220-default-dialog" v-if="dialog1_show">
                    <div class="geshop-u000220-default-dialog-wrapper">
                        <div class="geshop-u000220-default-dialog-title"></div>
                    </div>
                </div>
            </div>
            <!-- 攻略 -->
            <div class="item-raiders" @click="handleClickRaiders"></div>
        </div>
        <!-- 日历弹窗2 -->
        <div class="geshop-u000220-default-dialog" v-if="dialog2_show">
            <div class="geshop-u000220-default-dialog-wrapper">
                <div class="geshop-u000220-default-dialog-title"></div>
                <a href="#" class="geshop-dialog-close" @click="dialog2_show = false"></a>
            </div>
            <div class="geshop-u000220-default-dialog-background"></div>
        </div>
    </div>
</template>

<script>
export default {
    data () {
        return {
            default_list: [
                { timeRange: '2018-06-20 00:00:00 - 2018-06-30 00:00:00', activityName: 'Laces Tops', activityDesc: '$7 OFF $49' },
                { timeRange: '2018-06-20 00:00:00 - 2018-06-30 00:00:00', activityName: 'Laces Tops', activityDesc: '$7 OFF $49' },
                { timeRange: '2018-06-20 00:00:00 - 2018-06-30 00:00:00', activityName: 'Laces Tops', activityDesc: '$7 OFF $49' },
                { timeRange: '2018-06-20 00:00:00 - 2018-06-30 00:00:00', activityName: 'Laces Tops', activityDesc: '$7 OFF $49' },
                { timeRange: '2018-06-20 00:00:00 - 2018-06-30 00:00:00', activityName: 'Laces Tops', activityDesc: '$7 OFF $49' },
                { timeRange: '2018-06-20 00:00:00 - 2018-06-30 00:00:00', activityName: 'Laces Tops', activityDesc: '$7 OFF $49' }
            ],
            list: {
                pc: [],
                pad: [],
                m: []
            },
            dialog1_show: false,
            dialog2_show: false,
            icon_arrow: {
                pc: '',
                pad: '',
                m: ''
            },
            tiemr: null,
            source_scroll_left: 0
        };
    },

    filters: {
        date_label (date) {
            if (date) {
                const leftjoin = (val) => { return val > 9 ? val : `0${val}`; };
                const start_date = date.split(' - ')[0].replace(/-/g, '/');
                const end_date = date.split(' - ')[1].replace(/-/g, '/');
                const starttime = new Date(start_date);
                const endtime = new Date(end_date);
                const start_month = leftjoin(starttime.getMonth() + 1);
                const start_day = leftjoin(starttime.getDate());
                const end_month = leftjoin(endtime.getMonth() + 1);
                const end_day = leftjoin(endtime.getDate());
                return `${start_month}.${start_day}-${end_month}.${end_day}`;
            } else {
                return '';
            }
        }
    },

    methods: {
        /**
         * 根据宽度获取平台, pc, pad, m
         */
        get_platform () {
            const w = $('body').width();
            if (w > 1024) return 'pc';
            if (w <= 1024 && w >= 768) return 'pad';
            if (w <= 767) return 'm';
            return 'pc';
        },
        /**
         * 获取时间状态的 class name
         * 未开始 ＝ is-ready
         * 已开始 = is-start
         * 结束 = is-ended
         */
        get_status_class (item) {
            const range = item.timeRange || '';
            if (!range) {
                return 'is-ended';
            }
            const start_date = range.split(' - ')[0].replace(/-/g, '/');
            const end_date = range.split(' - ')[1].replace(/-/g, '/');
            const starttime = new Date(start_date).getTime();
            const endtime = new Date(end_date).getTime();
            const local = new Date().getTime();
            if (local < starttime) {
                return 'is-ready';
            }
            if (local >= starttime && local < endtime) {
                return 'is-start';
            }
            if (local >= endtime) {
                return 'is-end';
            }
        },

        // 点击攻略页，根据平台跳转
        handleClickRaiders () {
            const platform = this.get_platform();
            const link = {
                pc: this.$root.data.pc_raiders_link || '',
                pad: this.$root.data.pad_raiders_link || '',
                m: this.$root.data.m_raiders_link || ''
            };
            link[platform] && window.open(link[platform]);
        },

        // 鼠标 [hover] 时间轴
        handleMounseEnter () {
            clearInterval(this.timer);

            // 非 [PC] 端的取消
            if (this.get_platform() !== 'pc') return;

            // 获取最后一个NODE节点的左偏移，判断是否有超出区域，没超出则不执行滚动效果
            const width1 = $(this.$el).find('.item-list.item-for-pc').width();
            let width2 = this.source_scroll_left + $(this.$el).find('.item-list.item-for-pc ul').width();
            if (width1 >= width2) return false;

            const container = $(this.$el).find('.item-list.item-for-pc');
            // 记录原来宽度，追加 40px 的宽度（因为最后1个LI会隐藏）
            const source_ul_width = width2 + 28;

            // 开始复制数据
            const all = $(this.$el).find('.item-list.item-for-pc ul').children().clone();
            all.each((index, item) => {
                $(item).addClass('is-copy');
            });
            // 复制 dom
            $(this.$el).find('.item-list.item-for-pc ul').append(all);

            // 定时器动画
            this.timer = setInterval(() => {
                const sl = container.find('ul').scrollLeft();
                if (sl >= source_ul_width) {
                    container.find('ul').scrollLeft(0);
                } else {
                    container.find('ul').scrollLeft(sl + 2);
                }
            }, 30);
        },
        // 鼠标 [离开] 时间轴
        handleMounseLeave () {
            clearInterval(this.timer);
            $(this.$el).find('.item-list.item-for-pc ul').scrollLeft(this.source_scroll_left || 0);
            $(this.$el).find('.item-list.item-for-pc ul li.is-copy').remove();
        },
        // 鼠标 [点击] 日历
        handleClickCalendar () {
            if (this.get_platform() != 'pc') {
                this.dialog2_show = true;
            }
        },
        // 鼠标 [hover] 日历
        handleMouseEnterCalendar () {
            if (this.get_platform() == 'pc') {
                this.dialog1_show = true;
            }
        },
        // 鼠标 [离开] 日历
        handleMouseLeaveCalendar () {
            if (this.get_platform() == 'pc') {
                this.dialog1_show = false;
            }
        }
    },
    created () {
        this.icon_arrow.pc = this.$root.data.pc_timezone_image || 'https://geshopimg.logsss.com/uploads/ILjGMaYnmql84cfRQgCwpTObstZ0kS9E.png';
        this.icon_arrow.pad = this.$root.data.pad_timezone_image || 'https://geshopimg.logsss.com/uploads/ILjGMaYnmql84cfRQgCwpTObstZ0kS9E.png';
        this.icon_arrow.m = this.$root.data.m_timezone_image || 'https://geshopimg.logsss.com/uploads/ILjGMaYnmql84cfRQgCwpTObstZ0kS9E.png';
        // 获取数组
        this.list.pc = this.$root.data.pc_dateTimeArrs || [];
        this.list.pad = this.$root.data.pad_dateTimeArrs || [];
        this.list.m = this.$root.data.m_dateTimeArrs || [];
    },

    mounted () {
        // 装修页处理默认数据
        if (this.$root.is_edit_env) {
            if (this.list.pc.length <= 0 || this.list.pc == '[]') {
                this.list.pc = this.default_list;
            }
            if (this.list.pad.length <= 0 || this.list.pad == '[]') {
                this.list.pad = this.default_list;
            }
            if (this.list.m.length <= 0 || this.list.m == '[]') {
                this.list.m = this.default_list;
            }
        }

        // 计算正在开始的偏移值
        let index = -1;
        const platform = this.get_platform();
        // 根据平台取不同的数据
        const calc_arr = this.list[platform] || [];

        // 定位到正在进行的索引
        calc_arr.map((item, key) => {
            if (this.get_status_class(item) == 'is-start') {
                if (index < 0) index = key;
            }
        });

        // 没有找到坐标，则不执行
        if (index == -1) return false;
        // 执行左偏移
        this.$nextTick(() => {
            let left = $(this.$el).find(`.item-list.item-for-${this.get_platform()} ul li.is-start`).eq(0).position().left;
            switch (platform) {
            case 'pc':
                $(this.$el).find(`.item-list.item-for-${this.get_platform()} ul`).scrollLeft(left);
                break;
            case 'pad':
                $(this.$el).find(`.item-list.item-for-${this.get_platform()} ul`).scrollLeft(left);
                break;
            case 'm':
                $(this.$el).find(`.item-list.item-for-${this.get_platform()} ul`).scrollLeft(left);
                break;
            }
            this.source_scroll_left = $(this.$el).find(`.item-list.item-for-${this.get_platform()} ul`).scrollLeft();
        });
    }
};
</script>

<style lang="less" scoped>

.geshop-u000220-default {
    width: 100%;
    height: 100px;
    margin: 0 auto;
    background-size: auto 100%;
    background-position: center;
    background-repeat: no-repeat;

    .geshop-u000220-default-wrapper {
        position: relative;
        height: 100px;
        width: 100%;
        max-width: 1200px;
        margin: 0 auto;
        display: table;
        table-layout: fixed;
    }

    .item-list,
    .item-logo,
    .item-calendar,
    .item-raiders {
        display: table-cell;
        vertical-align: top;
    }

    .item-logo,
    .item-calendar,
    .item-raiders {
        position: relative;
        width: 100px;
        height: 100px;
        background-size: 100% 100%;
        background-repeat: no-repeat;
        background-position: center;
        img {
            display: block;
            width: 100px;
            height: 100px;
        }
    }

    .item-list {
        font-size: 0px;
        line-height: 0px;
        overflow: hidden;
        ul {
            position: relative;
            display: inline-block;
            height: 100px;
            word-break: keep-all;
            white-space: nowrap;
            overflow-y: hidden;
            overflow-x: scroll;
        }
        ul::-webkit-scrollbar {
            display: none;
        }
        li {
            display: inline-block;
            padding:  0 16px;
            height: 100px;
            vertical-align: middle;
        }
        li.item-arrow {
            margin: 0 10px;
            width: 20px;
            padding: 0px;
            img {
                width: 20px;
                height: 20px;
                margin-top: 40px;
            }
        }
        li:last-child {
            display: none;
        }
    }
    .li-item-table {
        display: table;
        height: 100%;
    }
    .li-item-td {
        display: table-cell;
        vertical-align: middle;
    }
    .item-date {
        font-size: 0px;
        span {
            font-size: 14px;
            height: 21px;
            line-height: 21px;
            padding: 0px 6px;
            background:rgba(204,204,204,1);
            border-radius:11px;
        }
    }
    .item-title {
        margin-top: 5px;
        height: 19px;
        font-size:16px;
        font-weight:bold;
        color:rgba(51,51,51,1);
        line-height:19px;
    }
    .item-desc {
        margin-top: 6px;
        height: 19px;
        font-size: 16px;
        font-weight: 400;
        color:rgba(51,51,51,1);
        line-height:19px;
    }
}

@media screen and (min-width: 1025px) {
    .geshop-u000220-default {
        .item-list {
            ul {
                width: 100%;
                overflow: hidden;
            }
        }
        .item-for-pad,
        .item-for-m {
            display: none;
        }
    }

    // 日历弹窗
    .geshop-u000220-default-dialog {
        position: absolute;
        right: 0px;
        top: 100px;
        z-index: 100;
        width: 600px;
        height: 500px;
        &-wrapper {
            height: 100%;
            background: #ededed;
            z-index: 1;
            background-size: 100% 100%;
            background-position: center;
            background-repeat: no-repeat;
        }
        &-title {
            text-align: center;
            padding-top: 30px;
            &:hover {
                font-weight: bold;
            }
        }
    }
}

@media screen and (max-width: 1024px) and (min-width: 768px) {
    .geshop-u000220-default {

        .item-for-pc,
        .item-for-m {
            display: none;
        }

        // 时间轴列表
        .item-list {
            -webkit-overflow-scrolling: touch;
            ul {
                width: 100%;
                height: 100%;
                -webkit-overflow-scrolling: touch;
            }
        }

    }

    // 日历弹窗
    .geshop-u000220-default-dialog {
        position: fixed;
        left: 0px;
        right: 0px;
        top: 0px;
        bottom: 0px;
        z-index: 100;
        &-background {
            position: relative;
            background: #000;
            opacity: 0.5;
            width: 100%;
            height: 100%;
        }
        &-wrapper {
            position: absolute;
            left: 50%;
            top: 50%;
            width: 600px;
            height: 500px;
            margin-left: -300px;
            margin-top: -250px;
            background: #ededed;
            z-index: 1;
            background-size: 100% 100%;
            background-position: center;
            background-repeat: no-repeat;
        }
        &-title {
            text-align: center;
            padding-top: 30px;
        }
        .geshop-dialog-close {
            position: absolute;
            width: 30px;
            height: 30px;
            right: -15px;
            top: -15px;
            background-image: url(data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAB4AAAAeCAYAAAA7MK6iAAAAGXRFWHRTb2Z0d2FyZQBBZG9iZSBJbWFnZVJlYWR5ccllPAAAAyJpVFh0WE1MOmNvbS5hZG9iZS54bXAAAAAAADw/eHBhY2tldCBiZWdpbj0i77u/IiBpZD0iVzVNME1wQ2VoaUh6cmVTek5UY3prYzlkIj8+IDx4OnhtcG1ldGEgeG1sbnM6eD0iYWRvYmU6bnM6bWV0YS8iIHg6eG1wdGs9IkFkb2JlIFhNUCBDb3JlIDUuMy1jMDExIDY2LjE0NTY2MSwgMjAxMi8wMi8wNi0xNDo1NjoyNyAgICAgICAgIj4gPHJkZjpSREYgeG1sbnM6cmRmPSJodHRwOi8vd3d3LnczLm9yZy8xOTk5LzAyLzIyLXJkZi1zeW50YXgtbnMjIj4gPHJkZjpEZXNjcmlwdGlvbiByZGY6YWJvdXQ9IiIgeG1sbnM6eG1wPSJodHRwOi8vbnMuYWRvYmUuY29tL3hhcC8xLjAvIiB4bWxuczp4bXBNTT0iaHR0cDovL25zLmFkb2JlLmNvbS94YXAvMS4wL21tLyIgeG1sbnM6c3RSZWY9Imh0dHA6Ly9ucy5hZG9iZS5jb20veGFwLzEuMC9zVHlwZS9SZXNvdXJjZVJlZiMiIHhtcDpDcmVhdG9yVG9vbD0iQWRvYmUgUGhvdG9zaG9wIENTNiAoV2luZG93cykiIHhtcE1NOkluc3RhbmNlSUQ9InhtcC5paWQ6NDg1QjM5RUREQjcxMTFFODlBNDZCRjdFMjlGNkExN0EiIHhtcE1NOkRvY3VtZW50SUQ9InhtcC5kaWQ6NDg1QjM5RUVEQjcxMTFFODlBNDZCRjdFMjlGNkExN0EiPiA8eG1wTU06RGVyaXZlZEZyb20gc3RSZWY6aW5zdGFuY2VJRD0ieG1wLmlpZDo0ODVCMzlFQkRCNzExMUU4OUE0NkJGN0UyOUY2QTE3QSIgc3RSZWY6ZG9jdW1lbnRJRD0ieG1wLmRpZDo0ODVCMzlFQ0RCNzExMUU4OUE0NkJGN0UyOUY2QTE3QSIvPiA8L3JkZjpEZXNjcmlwdGlvbj4gPC9yZGY6UkRGPiA8L3g6eG1wbWV0YT4gPD94cGFja2V0IGVuZD0iciI/PmkvRE0AAAPXSURBVHjarJdNSBtBFIDfLir4ExPUSFBIrVYUBRsoFATBS2NLD72U0oiHCPEHL1IoufZWPJceSrRiDXpJeym9tBgVSqhQQayC+IPWamNDQiT+EKii6XvbzHay2c2apA8ek5ndfd+bmffeTATIQi4vLzux6UC1olpQL1AjgiB8xzaAbQD+lyQSCRsCPajhhI7gOz9RX6K25AO0ooGRRI6C3z5DNWvZFzSgd1Df4tKZ+PFIJAKrq6sQi8Xg9PQU8DmUlpZCRUUFtLW1Sa3Czi9sHKIoftYFo5dONPiGH1tZWQG/3w/hcDjjKtXU1EBXVxc0NzcrHXiA8A+aYITeRehH1j85OYHp6WnY3d3NapsaGxuhu7sbSkpKeHgnP3OBg17H5huCDdQPBoMwOTkJx8fHOcVIZWUluFwuefkR/ANt30Q9or7IvTvEoATLB0oSjUZhfHwc4vH43xkKwjVisOdi0ptb+MDNBqempvKC8nCfz8cPjeDK3uDBsifLy8uwt7enGjiZpKioCEwmU9r4+vo6bG5u8kNDMhhn62Kjs7OzaR/bbDYYHh6G/v5+KCwsTHtusVjA7XZLSg4oZWZm5l80J1kiTt3OBil6KVeVsr29LS19Q0MD9Pb2psCrq6slhwwGA+zs7MDZ2Vna9/v7+xAKhVjXiMzbYrL2SrK1taW6jJRWHo8nDU7QwcFBqYgQ1Ov1am6FwnYHgZv4ypQpUHg4pQoPnZiYgPPzc83vFbabRFxzC+tRGdSL0rGxMQleV1d3ZaiKbYuYbYpQfSaV81EUc0o1EVNJ3vWysrKML5vNZjmQKBBp72nmtOxq0c6LwnaI3N3gDWeCDgwMyNFLVWl0dDQFrpZKGrY3CBzgi7uaUGHgoWxPKWB4uNPp1Fx6he0ABdcc61mt1rQzlaS2tlaCUj4rA4nBWcAVFBSofk9FJilH6NxXIXkyfUEH2un30tKSsr7KhSLTeUx7XFxcrFrjKe/ZGY0x9QLBT9i6POXLI3moFL1LAK2CGrS+vl55MXgl12r0YAE9WWDp4XA4Ug7xXKW8vFyyxV0GnqP9jbTzGB/8ZhFIUUr7mqtQQPb19UnwJDTKZqt29XmIe/2O9elSR/X34OAgKygFWU9PT4rjCL6Hs/2U1WVvcXER5ufn4fDwULfA2O126capuOw9RqhP93qL8PvYvEcHUnKD7mFra2tS3lKwUTxUVVWB0WiE1tZWPmUYMIY2HqH6s/m70kL/CvK40NOfAWvOQYIG2lG9qPEr8GL43mu6w+keNlk6YU9eHGhNW3AJLxBC6RGUyqAozl3V1h8BBgAskqDnc4fYywAAAABJRU5ErkJggg==);
            background-repeat: no-repeat;
            background-position: center;
        }
    }
}

@media screen and (max-width: 767px) and (min-width: 0px) {

    .geshop-u000220-default {
        height: 70px;

        .geshop-u000220-default-wrapper {
            height: 70px;
        }

        .item-for-pc,
        .item-for-pad {
            display: none;
        }

        // LOGO和日历
        .item-logo,
        .item-calendar {
            width: 70px;
            height: 70px;
        }

        // 攻略
        .item-raiders {
            position: absolute;
            right: 0px;
            top: 70 + 14px;
            width: 91px;
            height: 40px;
            z-index: 2;
        }

        // 时间轴列表
        .item-list {
            width: 100%;
            -webkit-overflow-scrolling: touch;
            ul {
                width: 100%;
                height: 100%;
                -webkit-overflow-scrolling: touch;
            }
            li {
                height: 70px;
                padding: 0 11px;
            }
            li.item-arrow {
                margin: 0px;
                img {
                    margin-top: 27px;
                }
            }
        }

        // 日期
        .item-date {
            margin-top: 6px;
            span {
                width: 55px;
                height: 17px;
                line-height: 17px;
                border-radius: 17px;
                font-size: 11px;
            }
        }
        .item-title {
            margin-top: 5px;
            height: 15px;
            line-height: 15px;
            font-size: 12px;
        }
        .item-desc {
            margin-top: 5px;
            font-size: 13px;
            line-height: 15px;
            height: 15px;
        }
    }

    // 日历弹窗
    .geshop-u000220-default-dialog {
        position: fixed;
        left: 0px;
        right: 0px;
        top: 0px;
        bottom: 0px;
        z-index: 100;
        &-background {
            position: relative;
            background: #000;
            opacity: 0.5;
            width: 100%;
            height: 100%;
        }
        &-wrapper {
            position: absolute;
            left: 50%;
            top: 50%;
            width: 300px;
            height: 380px;
            margin-left: -150px;
            margin-top: -190px;
            background: #ededed;
            z-index: 1;
            background-size: 100% 100%;
            background-position: center;
            background-repeat: no-repeat;
        }
        &-title {
            text-align: center;
            padding-top: 30px;
        }
        .geshop-dialog-close {
            position: absolute;
            width: 30px;
            height: 30px;
            right: -15px;
            top: -15px;
            background-image: url(data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAB4AAAAeCAYAAAA7MK6iAAAAGXRFWHRTb2Z0d2FyZQBBZG9iZSBJbWFnZVJlYWR5ccllPAAAAyJpVFh0WE1MOmNvbS5hZG9iZS54bXAAAAAAADw/eHBhY2tldCBiZWdpbj0i77u/IiBpZD0iVzVNME1wQ2VoaUh6cmVTek5UY3prYzlkIj8+IDx4OnhtcG1ldGEgeG1sbnM6eD0iYWRvYmU6bnM6bWV0YS8iIHg6eG1wdGs9IkFkb2JlIFhNUCBDb3JlIDUuMy1jMDExIDY2LjE0NTY2MSwgMjAxMi8wMi8wNi0xNDo1NjoyNyAgICAgICAgIj4gPHJkZjpSREYgeG1sbnM6cmRmPSJodHRwOi8vd3d3LnczLm9yZy8xOTk5LzAyLzIyLXJkZi1zeW50YXgtbnMjIj4gPHJkZjpEZXNjcmlwdGlvbiByZGY6YWJvdXQ9IiIgeG1sbnM6eG1wPSJodHRwOi8vbnMuYWRvYmUuY29tL3hhcC8xLjAvIiB4bWxuczp4bXBNTT0iaHR0cDovL25zLmFkb2JlLmNvbS94YXAvMS4wL21tLyIgeG1sbnM6c3RSZWY9Imh0dHA6Ly9ucy5hZG9iZS5jb20veGFwLzEuMC9zVHlwZS9SZXNvdXJjZVJlZiMiIHhtcDpDcmVhdG9yVG9vbD0iQWRvYmUgUGhvdG9zaG9wIENTNiAoV2luZG93cykiIHhtcE1NOkluc3RhbmNlSUQ9InhtcC5paWQ6NDg1QjM5RUREQjcxMTFFODlBNDZCRjdFMjlGNkExN0EiIHhtcE1NOkRvY3VtZW50SUQ9InhtcC5kaWQ6NDg1QjM5RUVEQjcxMTFFODlBNDZCRjdFMjlGNkExN0EiPiA8eG1wTU06RGVyaXZlZEZyb20gc3RSZWY6aW5zdGFuY2VJRD0ieG1wLmlpZDo0ODVCMzlFQkRCNzExMUU4OUE0NkJGN0UyOUY2QTE3QSIgc3RSZWY6ZG9jdW1lbnRJRD0ieG1wLmRpZDo0ODVCMzlFQ0RCNzExMUU4OUE0NkJGN0UyOUY2QTE3QSIvPiA8L3JkZjpEZXNjcmlwdGlvbj4gPC9yZGY6UkRGPiA8L3g6eG1wbWV0YT4gPD94cGFja2V0IGVuZD0iciI/PmkvRE0AAAPXSURBVHjarJdNSBtBFIDfLir4ExPUSFBIrVYUBRsoFATBS2NLD72U0oiHCPEHL1IoufZWPJceSrRiDXpJeym9tBgVSqhQQayC+IPWamNDQiT+EKii6XvbzHay2c2apA8ek5ndfd+bmffeTATIQi4vLzux6UC1olpQL1AjgiB8xzaAbQD+lyQSCRsCPajhhI7gOz9RX6K25AO0ooGRRI6C3z5DNWvZFzSgd1Df4tKZ+PFIJAKrq6sQi8Xg9PQU8DmUlpZCRUUFtLW1Sa3Czi9sHKIoftYFo5dONPiGH1tZWQG/3w/hcDjjKtXU1EBXVxc0NzcrHXiA8A+aYITeRehH1j85OYHp6WnY3d3NapsaGxuhu7sbSkpKeHgnP3OBg17H5huCDdQPBoMwOTkJx8fHOcVIZWUluFwuefkR/ANt30Q9or7IvTvEoATLB0oSjUZhfHwc4vH43xkKwjVisOdi0ptb+MDNBqempvKC8nCfz8cPjeDK3uDBsifLy8uwt7enGjiZpKioCEwmU9r4+vo6bG5u8kNDMhhn62Kjs7OzaR/bbDYYHh6G/v5+KCwsTHtusVjA7XZLSg4oZWZm5l80J1kiTt3OBil6KVeVsr29LS19Q0MD9Pb2psCrq6slhwwGA+zs7MDZ2Vna9/v7+xAKhVjXiMzbYrL2SrK1taW6jJRWHo8nDU7QwcFBqYgQ1Ov1am6FwnYHgZv4ypQpUHg4pQoPnZiYgPPzc83vFbabRFxzC+tRGdSL0rGxMQleV1d3ZaiKbYuYbYpQfSaV81EUc0o1EVNJ3vWysrKML5vNZjmQKBBp72nmtOxq0c6LwnaI3N3gDWeCDgwMyNFLVWl0dDQFrpZKGrY3CBzgi7uaUGHgoWxPKWB4uNPp1Fx6he0ABdcc61mt1rQzlaS2tlaCUj4rA4nBWcAVFBSofk9FJilH6NxXIXkyfUEH2un30tKSsr7KhSLTeUx7XFxcrFrjKe/ZGY0x9QLBT9i6POXLI3moFL1LAK2CGrS+vl55MXgl12r0YAE9WWDp4XA4Ug7xXKW8vFyyxV0GnqP9jbTzGB/8ZhFIUUr7mqtQQPb19UnwJDTKZqt29XmIe/2O9elSR/X34OAgKygFWU9PT4rjCL6Hs/2U1WVvcXER5ufn4fDwULfA2O126capuOw9RqhP93qL8PvYvEcHUnKD7mFra2tS3lKwUTxUVVWB0WiE1tZWPmUYMIY2HqH6s/m70kL/CvK40NOfAWvOQYIG2lG9qPEr8GL43mu6w+keNlk6YU9eHGhNW3AJLxBC6RGUyqAozl3V1h8BBgAskqDnc4fYywAAAABJRU5ErkJggg==);
            background-repeat: no-repeat;
            background-position: center;
        }
    }
}
</style>
