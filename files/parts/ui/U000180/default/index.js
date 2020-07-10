$(function () {

    // var siteDomain = {
    //     'rw-pc': 'https://www.rosewholesale.com',
    //     'rw-wap': 'https://m.rosewholesale.com',
    //     'rg-pc': 'https://www.rosegal.com',
    //     'rg-wap': 'https://m.rosegal.com',
    //     'zf-pc': 'https://www.zaful.com',
    //     'zf-wap': 'https://m.zaful.com',
    //     "test-pc": 'http://www.pc-rosegal.com.geshopsign.php5.egomsl.com',
    //     "test-wap": 'http://m.wap-rosegal.com.geshopsign.php5.egomsl.com',
    // };
 
    // var siteDomainUrl = siteDomain[GESHOP_SITECODE];

    // var siteCode = GESHOP_SITECODE ? GESHOP_SITECODE.split('-')[1] : '';
	// var testSite = 'test-' + siteCode;
	// var siteDomainUrl = siteDomain[testSite];
	// console.log(siteDomainUrl);

    $('body').on('click', '.closeBtn', function () {
        $.unblockUI()
    });

    var activity_sign_info_api = GESHOP_INTERFACE ? GESHOP_INTERFACE.activitysigninfo.url : '';
    var activity_do_sign_api = GESHOP_INTERFACE ? GESHOP_INTERFACE.activitydosign.url : '';

    $.post(activity_sign_info_api, 'content=[]', function (data) {

        // 未登陆 按钮显示Sign In
        if (data.code == 1) {
            $('.U000180Module').find('.check-status').html(setStyle.signIn);
        }

        if (data.code != 0) {
            return false;
        }
        var html = '';
        var isToday = false;
        var isChecked = false;

        data.data.SignInfo.forEach(function(v, i) {
            // 当天
            if (v.isToday) {
                isToday = true;
                // 已签到
                if(v.state === 1){
                    isChecked = true;
                    $('.U000180Module').find('.check-status').html(setStyle.signSucceedTitle).addClass('check-checked');
                } 
            }
            // 积分累计天数
            var num = i + 1;
            // 积分数
            var pointNum = '<span class="item-point">+' + v.points + '</span>';
            // 是否签到状态
            var liStatus = v.state ? 'item-checked' : 'item-nocheck';
            var bac = v.state ? setStyle.SignIntegralStateImg : setStyle.UnSignIntegralStateImg
            html += '<li class='+liStatus+' style="width: ' + setStyle.IntegralBlockItemWidth / setStyle.remUnit + 'rem;  text-align: center; margin-right: ' + setStyle.IntegralBlockItemMarginRight / setStyle.remUnit + 'rem; float: left; ">\
                <div style="   height: ' + setStyle.IntegralBlockItemHeight / setStyle.remUnit + 'rem; background: url(' + bac + ') ;  background-size: contain;background-position: center;background-repeat: no-repeat;  color: ' + setStyle.UnSignIntegralNumColor + ';  ">\
                <p style="padding-top: ' + setStyle.IntegralBlockItemTxtPaddingTop / setStyle.remUnit + 'rem;line-height: 100%;">' + pointNum + '</p>\
                </div>\
                <div class="item-day" style=" margin-top: ' + 5 / setStyle.remUnit + 'rem">' + setStyle['d' + num] + '</div>\
            </li>';
        })
        var $component = $('.U000180Module');
        $component.find('.ulcont').html(html);
        $component.find('.total').addClass('has-loginIn').text(data.data.myPoints);
        // $component.find('.check-status').html(setStyle.SignButtonTxt).addClass('check-logined');
        var hasCheckedDays = $component.find('.item-checked').length;
        
        // test
        // var hasCheckedDays = 0;
        
        // 已签到
        if (hasCheckedDays) {
            $component.find('.check-status').html(setStyle.signSucceedTitle.replace(1, hasCheckedDays)).addClass('check-logined');
        }
        // 未签到
        else {
            $component.find('.check-status').addClass('uncheck-checked');
        }

    }, 'jsonp');

    var closeBtnHTML = '<p class="closeBtn"><span>' + setStyle.popConfirmButtonTxt + '</span></p></p>';

    // 签到
    $('.geshop-component-box').on('click', '.sign', function () {
        
        // 已签到不可再次点击
        if ($(this).find('.check-status').hasClass('check-checked')) {
            return false;
        }
        
        $.ajax({
            url: activity_do_sign_api,
            type: 'POST',
            dataType: 'jsonp',
            data: 'content=[]',
            timeout: 2000,
            success: function (data, status, xhr) {
                var options = {
                    content: '<div class="content" ><p class="txt center">Failed check in please try again later</p>' + closeBtnHTML + '</div>',
                    modalWidth: 270,
                    modalHeight: 160,
                    className:'blockUi-U000180',
                    cssoptions: {
                        backgroundColor: '#fff',
                        '-webkit-border-radius': '10px',
                        '-moz-border-radius': '10px',
                        color: '#333'
                    }
                };

                // test
                // data.code = 0;
                // data.data.myPoints = 20
                
                switch (data.code) {
                    case 0://成功
                        var checkedDays = data.data.continuousDays;
                        var continuousDays = data.data.continuousDays - 1;
                        // 积分数文字
                        var totalPoint = '<b class="pointNumber">'+data.data.todayPoints+'</b>';
                        options.content = '<div class="content pd0"  style=" width:270px; height:284px;background: url(' + setStyle.popConfirmBackgroundImg + '); background-repeat: no-repeat;background-position: center;background-size: contain;"><p class="txt center">' + setStyle.signSucceedPop.replace('10', totalPoint) + '</p>' + closeBtnHTML + '</div>';
                        options.modalHeight = 284;
                        options.cssoptions.backgroundColor = 'transparent';
                        $('.U000180Module').find('.total').addClass('has-loginIn').text(data.data.myPoints)
                        $('.U000180Module').find('.ulcont li').eq(continuousDays).find('div').eq(0).css('background-image', 'url(' + setStyle.SignIntegralStateImg + ')')
                        $('.U000180Module').find('.check-status').html(setStyle.signSucceedTitle.replace(1,checkedDays)).addClass('check-checked');
                        options.unblockCallback = function () {
                            window.location.reload();
                        }
                        break;
                    case 1://未登陆
                        var url = location.href
                        location.href = data.data.loginUrl + url;
                        return false;
                    case 4: 
                        $('.U000180Module').find('.sign>span').addClass('check-checked');
                        break;
                    default://其他
                        options.content = '<div class="content" ><p class="txt center">' + data.message + '</p>' + closeBtnHTML + '</div>';
                        break;
                }
                GEShopSiteCommon.dialog.custom(options)
            },
            // 网络异常
            error: function (err) {

                var options = {
                    content: '<div class="content" ><p class="txt center">' + setStyle.signFail + '</p>' + closeBtnHTML + '</div>',
                    modalWidth: 270,
                    modalHeight: 160,
                    className:'blockUi-U000180',
                    cssoptions: {
                        backgroundColor: '#fff',
                        '-webkit-border-radius': '10px',
                        '-moz-border-radius': '10px',
                        color: '#333'
                    }
                };
                GEShopSiteCommon.dialog.custom(options)

            }
        });
    })

    // 规则按钮
    $('.geshop-component-box').on('click', '.rules', function () {
    		var ruleText = setStyle.popRuleTxt.replace(/;/g,'<span style="margin-bottom:15px;display:block;"></span>');
        var options = {
            content: '<div class="content" ><p class="tit">Rules</p>\
                        <p class="txt left">'+ ruleText +'</p>' + closeBtnHTML + '</div>',
            modalWidth: 270,
            className:'blockUi-U000180',
            cssoptions: {
                backgroundColor: '#fff',
                '-webkit-border-radius': '10px',
                '-moz-border-radius': '10px',
                color: '#333',
                transform: 'translate(-50%, -50%)'
            },
        }


        GEShopSiteCommon.dialog.custom(options)
    })

});
