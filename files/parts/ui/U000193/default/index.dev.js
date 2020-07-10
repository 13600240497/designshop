$(function () {
    $('.geshop-U000193-default').each(function () {

        var $component = $(this);
        var editEnv = $component.find('input[name=editEnv]').val();
        if (editEnv == 0 && !$.cookie("first_access")) {
            $(this).find('.mask').show();
            var height = $component.find('input[name=cHeight]').val() == 0 ? '-240' : $component.find('input[name=cHeight]').val();
            $component.find('.email-wrap').css({ 'display': 'block', 'marginTop': height + 'px', 'position': 'fixed', 'zIndex': 10001 });
            $component.find('.success-wrap').css({ 'marginTop': height + 'px', 'position': 'fixed', 'zIndex': 10001 });
            $.cookie('first_access', 'yes', { expires: 90, path: '/' });
            // 发送埋点
            gbLogsss.getsku($($(this).find('.email-wrap img')[0]));
            gbLogsss.sendsku();
        }

        $(document).on('click', '.geshop-popup-close', function () {
            $component.find('.mask').hide();
            $component.find('.email-wrap').hide();
            $component.find('.success-wrap').hide();
        })

        // 提交
        $(document).on('click', '.submit-btn', function () {
            var email = $component.find('.input-wrap input').val();
            // 是否展示弹窗
            var show_success = $component.find('[name="show_success"]').val();

            if (!email) {
                $component.find('.msg').show();
                $component.find('.msg-text').text(GESHOP_LANGUAGES.email_require);
                return;
            }
            // 校验格式
            if (/^[a-z0-9]+([._\\-]*[a-z0-9])*@([a-z0-9]+[-a-z0-9]*[a-z0-9]+.){1,63}[a-z0-9]+$/.test(email) == false) {
                $component.find('.msg').show();
                $component.find('.msg-text').text(GESHOP_LANGUAGES.email_require);
                return;
            }

            // 判断是否打开订阅弹窗
            if (show_success == 1) {
                getData(0, email, function (result) {
                    if (result == 0) {
                        $component.find('.email-wrap').hide();
                        if (show_success == 1) {
                            $component.find('.success-wrap').show();
                        } else {
                            $component.find('.mask').hide();
                            window.location.href = 'https://login.rosegal.com/m-users-a-sign.htm';
                        }
                    } else if (result == 1) {
                        $component.find('.msg').show();
                        $component.find('.msg-text').text(GESHOP_LANGUAGES.email_error);
                    } else if (result == 2) {
                        $component.find('.msg').show();
                        $component.find('.msg-text').text(GESHOP_LANGUAGES.email_alreay_join);
                    }
                });
            } else {
                // 隐藏
                $component.find('.mask').hide();
                $component.find('.email-wrap').hide();
                $component.find('.success-wrap').hide();
                // 打开RG的登录弹窗
                open_rg_dialog(email);
            }
        });

        var isEU;
        getData(1, '', function (result) {
            isEU = result;
            if (isEU) {
                $(document).on('focus', 'input[name=email]', function () {
                    $component.find('.msg').show();
                    var eu_agreement = '';
                    switch (GESHOP_LANG) {
                        case 'en':
                            eu_agreement = 'By pressing subscribe,I agree to receive marketing information about Rosegal products and services and to the processing of my personal data for such purposes as described in the <a class="privacy-link" href="https://baidu.com" target="_blank">Rosegal Privacy Policy.</a> I can withdraw my consent at any time.';
                            break;
                        case 'de':
                            eu_agreement = 'Durch Drücken von Join erkläre ich mich einverstanden, Marketinginformationen über Rosegal-Produkte und -Dienste zu erhalten und dass meine persönlichen Daten zu den in der <a class="privacy-link" href="https://www.rosegal.com/privacy-policy/" target="_blank"> Rosegal-Datenschutzrichtlinie</a> beschriebenen Zwecken verarbeitet werden. Ich kann meine Einwilligung jederzeit widerrufen.';
                            break;
                        case 'es':
                            eu_agreement = 'Al hacer click en "unirme", acepto recibir información de marketing sobre los productos y servicios de Rosegal y sobre el procesamiento de mis datos personales para los fines descritos en la <a class="privacy-link" href="https://www.rosegal.com/privacy-policy/" target="_blank"> Política de privacidad de Rosegal.</a> Puedo retirar mi consentimiento en cualquier momento.';
                            break;
                        case 'fr':
                            eu_agreement = 'En appuyant sur rejoindre, j’accepte de recevoir des informations commerciales sur les produits et services Rosegal et de traiter mes données personnelles aux fins décrites dans <a class="privacy-link" href="https://fr.rosegal.com/privacy-policy/" target="_blank"> la politique de confidentialité de Rosegal.</a> Je peux retirer mon consentement à tout moment.';
                            break;
                        case 'it':
                            eu_agreement = 'Premendo scriversi, accetto di ricevere informazioni di marketing sui prodotti e servizi Rosegal e sul trattamento dei miei dati personali per gli scopi descritti <a class="privacy-link" href="https://www.rosegal.com/privacy-policy/" target="_blank"> nell’informativa sulla privacy di Rosegal.</a> Posso ritirare il mio consenso in qualsiasi momento.';
                            break;
                        case 'pt':
                            eu_agreement = 'Ao pressionar cadastrar, Eu concordo em receber informações de marketing sobre produtos e serviços Rosegal e processar minhas informações pessoais para tais propósitos descritos na <a class="privacy-link" href="https://www.rosegal.com/privacy-policy/" target="_blank">Política de Privacidade Rosegal.</a> Eu posso retirar meu consentimento a qualquer momento.';
                            break;
                        case 'ru':
                            eu_agreement = 'Нажимая «присоединиться », вы соглашаетесь получать рекламную информацию о продуктах и услугах Rosegal, а также на обработку своих персональных данных в целях, описаных в <a class="privacy-link" href="https://ru.rosegal.com/privacy-policy/" target="_blank"> Политике конфиденциальности Rosegal.</a> Вы можете отменить свое согласие в любое время.';
                            break;
                        case 'ar':
                            eu_agreement = 'بالضغط علي "انضمام" اوافق علي ان استقبل معلومات تسويقية عن خدمات و منتجات Rosegal<a class="privacy-link" href="https://ar.rosegal.com/privacy-policy/" target="_blank"> و ان تتم استخدام معلوماتي الشخصية لهذا الغرض كما هوا موضح في </a>سياسية الخصوصيه الخاصة بRosegal. يمكنني سحب موافقتي هذه فأي وقت.  ';
                            break;

                    }
                    $component.find('.msg-text').html(eu_agreement);
                });
            }
        })

    });

    /**
     * 读取邮件是否已经订阅
     * @param {*} type 
     * @param {string} email 邮件地址
     * @param {function} callback 回调
     */
    function getData (type, email, callback) {
        var url = GESHOP_INTERFACE.user_userSubscribe.url;
        var params = 'email=' + email + '&type=' + type + '&content={}&lang=' + GESHOP_LANG;
        $.ajax({
            url: url,
            type: 'get',
            dataType: "jsonp",
            data: params,
            success: function (res) {
                if (type == 1) {
                    callback(res.data.oumengCountry);
                } else {
                    callback(res.code);
                }
            }
        });
    }

    /**
     * 打开RG弹窗
     * @param {string} email 电子邮件地址
     */
    function open_rg_dialog (email) {
        GLOBAL.geshop && GLOBAL.geshop.openSignInAndRegisterDialog({
            fromType: 'geshopIndexDialog',
            email: email,
            checkEmail: true,
        });
    }
});

