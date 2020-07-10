
$(function() {
    var components = $('.geshop-U000196-default');
    components.map(function(index, elem){
        // 初始化组件
        ge_amount_list_init(elem);
    })
})

var GE_amount_list_fn = function(elem) {

    this.init = {
        site_code: GESHOP_SITECODE ? GESHOP_SITECODE.split('-')[0] : '',
        languages: {}, // 语言包

        // 初始化语言包
        init_languages: function() {
            Object.keys(GESHOP_HIGH_AMOUNT_LIST_LANGUAGES).map(function(key) {
                var current_language = (GESHOP_LANG == 'en' || GESHOP_LANG == 'fr') ? GESHOP_LANG : 'en';
                this.languages[key] = GESHOP_HIGH_AMOUNT_LIST_LANGUAGES[key][current_language];
            }.bind(this));
        },
        
        init_events: function () {
            var _this = this;
            
            // 查看我的排名
            $(elem).find('.js_check-my-ranking').on('click', function () {
                var className = $(this).attr('data-tag');
                _this.check_my_ranking(className);
            });

            // 查看规则
            $(elem).find('.js_view-rule-btn').on('click', function () {
                var className = $(this).attr('data-tag');
                _this.show_dialog(className);
            });

            // 关闭弹窗
            $(elem).find('.js_close-dialog').on('click', function () {
                var className = $(this).attr('data-tag');
                _this.hide_dialog(className);
            });
        },

        show_dialog: function (className) {
            $(elem).find('.' + className).show();
        },

        hide_dialog: function (className) {
            $(elem).find('.' + className).hide();
        },

        animateList: function() {
            var animate_wraper = $(elem).find('.amount-list-outer-wraper');
            var animate_target = animate_wraper.find('.amount-list-container');
            var amount_list_lineheight = $(elem).attr('data-amount-list-lineheight');
            var top = 0;
            function up() {
                animate_target.animate({
                    top: (top - amount_list_lineheight) + 'px'
                }, 1000, function () {
                    animate_target.css({ top: '0px' }).find('li:first').appendTo(animate_target);
                    up();
                })
            }
            if (animate_wraper.height() <= animate_target.height()) {
                up();
            }
        },

        // 获取数据
        get_ajax_data: function (url, params, callback_name) {
            var url = url;
            var content = { content: JSON.stringify(params) };
            // 组件ID
            var component_id = $(elem).attr('data-id');
            return $.ajax({
                url: url,
                type: 'get',
                dataType: 'jsonp',
                data: content,
                cache: true,
                jsonpCallback: `geshop_callback_${component_id}_${callback_name}`
            });
        },

        // 渲染榜单列表
        render_amount_list: function () {
            var url = GESHOP_INTERFACE.user_gettotalconsumptionlist.url;
            var params = {
                starttime: $(elem).attr('data-start-time'),
                endtime: $(elem).attr('data-end-time'),
                amount: $(elem).attr('data-amount')
            }
            var _this = this;
            this.get_ajax_data(url, params, 'get_list').done(function (res) {
                if (res.code == 0) {
                    var html = '';
                    res.data.forEach(function (item, index, array) {
                        html += '<li class="amount-list-item">'+
                            '<span class="serial-number site-bold-strict">TOP.'+ (index + 1) +'</span>'+
                            '<span class="email">' + item.mail + '</span>'+
                            '<span class="payment-text">' + _this.languages.rankingListPayment + ': </span>'+
                            '<span class="payment-amount"><span class="my_shop_price" data-orgp="' + item.amount + '">$' + item.amount + '</span></span>'+
                            '</li>';
                    });
                    $(elem).find('.amount-list-container').html(html);
                    _this.animateList();
                    typeof GEShopSiteCommon !== 'undefined' && GEShopSiteCommon.renderCurrency();
                } 
                // 接口返回错误状态码都是100，前端无法判断
                else if (res.code == 100) {
                    // if ($(elem).attr('data-iseditenv') == 1) {
                    //     layer.msg(res.message);
                    // }
                }
            });
        },

        // 获取排名详情
        check_my_ranking: function (className) {
            var url = GESHOP_INTERFACE.user_gettotalconsumptiondetail.url;
            var params = {
                starttime: $(elem).attr('data-start-time')
            }
            var _this = this;
            this.get_ajax_data(url, params, 'check').done(function (res) {
                
                if (res.code == 0) {
                    var today_shop_price_html = '<p class="amount my_shop_price" data-orgp="' + res.data.todayTotal + '">' + res.data.todayTotal + '</p>';
                    var total_shop_price_html = '<p class="amount my_shop_price" data-orgp="' + res.data.areaTotal + '">' + res.data.areaTotal + '</p>';
                    var rank = res.data.rank >= 100 ? '100+' : res.data.rank;
                    var rank_html = '<p class="amount">' + _this.languages.myRankingNum.replace('xxx', rank) + '</p>';
                    var html = '<div class="ranking-item">' + _this.languages.todayAmount.replace('xxx.xx', today_shop_price_html) + '</div>'+
                        '<div class="ranking-item">' + _this.languages.totalAmount.replace('xxx.xx', total_shop_price_html) + '</div>'+
                        '<div class="ranking-item">' + _this.languages.myRanking.replace('xxx', rank_html) + '</div>';

                    $(elem).find('.ge-196d-dialog-my-ranking .content').html(html);
                    typeof GEShopSiteCommon !== 'undefined' && GEShopSiteCommon.renderCurrency();
                    _this.show_dialog(className);
                } 
                // code 100
                // 接口返回错误状态码都是100，前端无法判断，先根据message判断
                else if (res.message == 'Please log in first') {
                    var loginUrl = (typeof HTTPS_LOGIN_DOMAIN !== 'undefined' ? HTTPS_LOGIN_DOMAIN : (DOMAIN_LOGIN + '/')) + 'm-users-a-sign.htm';
                    var ref = window.location.href;
                    window.location.href = loginUrl + '?ref=' + ref;
                } else {
                    typeof GEShopSiteCommon !== 'undefined' && GEShopSiteCommon.dialog.message(res.message);
                }
            });
        }
    }

    return this.init;
}

function ge_amount_list_init(elem) {
    var f = new GE_amount_list_fn(elem);

    // 初始化语言包
    f.init_languages();  

    // 绑定事件
    f.init_events();

    // 渲染榜单列表
    f.render_amount_list();
    
}

