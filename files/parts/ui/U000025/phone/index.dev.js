$(function(){
    var $root = $('.geshop-U000025-phone');
    var $dialog = $root.find('.dialog-btn'),
        $js_verifycode = $rootPhone.find('.js_verifycode'),
        $js_contry = $rootPhone.find('.js_country'),
        $js_phone = $rootPhone.find('.js_phone'),
        $js_areaCode = $rootPhone.find('.js_areaCode'),
        $js_selectCountry = $rootPhone.find('.js_selectCountry');
    // console.log($rootPhone);

    $dialog.on('click', function () {
        $rootPhone.find('.js_country_error').text('').hide();
        $rootPhone.find('.js_phone_error').text('').hide();
        $rootPhone.find('.js_code_error').text('').hide();
        $rootPhone.find('.js_showCode').show();
        $rootPhone.find('.js_sendMes').hide();
        $rootPhone.find('.js_oper').hide();

        $js_areaCode.html('+86');
        $js_selectCountry.data('cid', '');
        $js_selectCountry.html($js_selectCountry.attr('data-lang'));

        $js_verifycode.hide();
        $js_verifycode.find('input.code-input').val('');
        $js_phone.val('');

        if ($js_contry.hasClass('active')) {
            $js_contry.removeClass('active');
        }
        $rootPhone.find('.geshop-U000025-dialog-phone').show();
    });

    $root.on('click', '.js-close-dialog', function () {
        $rootPhone.find('.geshop-U000025-dialog-phone').hide()
    });

    function scrollFn() {
        const $width = $root.find('.gs-bg-img').width();
        if (document.body.scrollHeight > (window.innerHeight || document.documentElement.clientHeight)) {
            if ($width >= 1900 && $width <= 1920 && window.innerWidth >= 1920) {
                $root.addClass('overflow');
            } else {
                $root.removeClass('overflow');
            }
        }
    };
    scrollFn();

    $(window).on('resize', scrollFn);


    var countryNumber = {"41":"1","11":"61","13":"1","40":"44","251":"971","21":"91","283":"63","36":"66","25":"60"};

    var countryWar = {
        countryArr : countryNumber,
        $country : $('#js_contry'),
        $sendAnother : $rootPhone.find('.js_sendAnother'),
        $countryList : $rootPhone.find('.country-list'),
        $js_areaCode : $rootPhone.find('.js_areaCode'),
        $selCountry :  $rootPhone.find('.js_selectCountry'),
        $js_verifycode : $rootPhone.find('.js_verifycode'),
        $js_showCode : $('.js_showCode'),
        $js_sendMes : $('.js_sendMes'),
        successDom : $('.js_successBox'),
        infoBoxDom : $rootPhone.find('.js_infoBox'),
        phoneInput : $rootPhone.find('.js_phone'),
        sendPhone : function(phone,code,cid,callback){
            var self = this;
            var content = {
                phone : phone,
                code : code,
                cid : cid
            };
            var ext = $root.attr('data-id');
            $.ajax({
                url: GESHOP_INTERFACE.activity_sendsms.url,
                type: 'POST',
                dataType: 'jsonp',
                jsonpCallback: `geshop_callback_${ext}`,
                data: content,
                success: function success(data) {
                    if(data.code == 0){
                        typeof callback == 'function' && callback();
                    }else{
                        GLOBAL.PopObj.alert({
                            msg: data.message
                        });
                        self.freshVerifyCode($('.img-verifycode'));
                    }
                }
            });
        },
        checkInfo : function(obj,flag){
            $('.form .error').hide();
            var self = this;
            var phoneNum = $.trim(this.phoneInput.val());
            var fullphoneNum = $rootPhone.find('.js_oper').val()+phoneNum;
            var country = this.$selCountry.data('cid');
            var code = $.trim($rootPhone.find('.code-input').val());
            var min = $rootPhone.find('.js_tel_min').val();
            var max = $rootPhone.find('.js_tel_max').val();
            var pattern = /^[0-9]{1,}$/;
            if(!country)
            {
                //GLOBAL.PopObj.alert({msg:'Please select a country.'});
                $('.js_country_error').text(jsLg.formMsg.country_msg);
                $('.js_country_error').show();
                this.phoneInput.focus();
            }
            else if (phoneNum=='')
            {
                $('.js_phone_error').text(jsLg.formMsg.tel_msg);
                $('.js_phone_error').show();
                this.phoneInput.focus();
            }
            else if (!pattern.test(phoneNum))
            {
                $('.js_phone_error').text(jsLg.formMsg.digits);
                $('.js_phone_error').show();
                this.phoneInput.focus();
            }
            else if (phoneNum.length<min)
            {
                $('.js_phone_error').text(jsLg.formMsg.tel_minlength_msg.replace('{0}',min));
                $('.js_phone_error').show();
                this.phoneInput.focus();
            }
            else if (phoneNum.length>max)
            {
                $('.js_phone_error').text(jsLg.formMsg.tel_maxlength_msg.replace('{0}',max));
                $('.js_phone_error').show();
                this.phoneInput.focus();
            }
            else
            {
                this.$js_verifycode.slideDown();
                $('.js_showCode').hide();
                $('.js_sendMes').show();
            }
            if(flag == 2){
                if(!!code){
                    self.sendPhone(fullphoneNum,code,country,function(){
                        $.layer({
                            shade: [0.5, '#000', true],
                            offset: ["30%", ""],
                            area: ['auto', 'auto'],
                            title: jsLg.message,
                            border: [1, 1, '#ddd', true],
                            dialog: {
                                msg: GESHOP_LANGUAGES.send_success,
                                btns: 1,
                                type: 1,
                                btn: [jsLg.ok],
                                yes: function (index) {
                                    layer.close(index);
                                    $root.find('.geshop-U000025-dialog-phone').hide()
                                },
                                close: function (index) {
                                    layer.close(index)
                                }
                            }
                        });
                    });
                }else{
                    $('.js_code_error').text(jsLg.formMsg.please_enter_verification_code);
                    $('.js_code_error').show();
                }
            }
        },
        freshVerifyCode : function(obj){
            var date = (new Date()).getTime();
            var _url = GESHOP_INTERFACE.base_verify.url;
            obj.attr("src",  _url + "&" + date);
        },
        bindEvent : function(){
            var self = this;
            var _date = (new Date()).getTime();
            $('.img-verifycode').attr('src', GESHOP_INTERFACE.base_verify.url + '&' + _date);

            $rootPhone.on('click', '.js_country', function(){
                $(this).toggleClass('active');
            });

            $rootPhone.find('.country-list').on('click', 'p', function(){
                var cid = $(this).data('cid');
                var cName = $(this).html();
                self.$selCountry.html(cName).data('cid',cid);
                self.$js_areaCode.html('+' + self.countryArr[cid]);

                //\u5224\u65ad\u662f\u5426\u5c06\u7535\u8bdd\u62c6\u5206\u51fa\u8fd0\u8425\u5546\u53f7\u4e0b\u62c9\u6846
                var country=$(this).data('cid');

                var json = $('#supplier_number_json').val();
                json=eval('('+json+')');
                $.each(json,function(i,v){
                    var id=v.country_id;
                    if (country==id) {
                        var oper=v.supplier_number;
                        if (oper!='') {
                            oper=oper.split(',');
                            var len=v.scut_number_length;
                            $rootPhone.find('.js_oper').show().html('');
                            var html='';
                            $(oper).each(function(i,v){
                                html+='<option value="'+v+'">'+v+'</option>';
                            });
                            $rootPhone.find('.js_oper').html(html);
                            $rootPhone.find('.js_tel_min').val(len);
                            $rootPhone.find('.js_tel_min').val(len);
                            return false;
                        }
                        else
                        {
                            $('#js_oper').hide().html('<option value=""></option>');
                            var len=v.number;
                            $('#js_tel_min').val(len);
                            $('#js_tel_max').val(len);
                            return false;
                        }
                    }
                    $rootPhone.find('.js_oper').hide().html('<option value=""></option>');
                    $rootPhone.find('.js_tel_min').val('6');
                    $rootPhone.find('.js_tel_max').val('20');
                });
            });

            $('body').on('click','.js_sendMes',function(){
                self.checkInfo($(this),'2');
            });

            this.$js_showCode.on('click',function(){
                self.checkInfo($(this),'1');
            });

            this.$sendAnother.on('click',function(){
                self.successDom.hide();
                self.infoBoxDom.show();
            });

            $('.img-verifycode').on('click',function(){
                self.freshVerifyCode($(this));
            })

        },
        init : function(){
            this.bindEvent();
        }


    };
    countryWar.init();
});


