$(function () {
	/* 日期上标 */
	$('.U000179Module').find('.item-day').each(function (index, item) {
		var setDay = $(this).text();
		var subRegexp = /(ｅ|e|°)/g;
		if(subRegexp.test(setDay)){
			var setFormat = "";
			setDay.replace(subRegexp, function () {
				var current = arguments[1];
				setFormat = setDay.replace(current, '<sup>' + current + '</sup>');
			});

			$(this).html(setFormat);
		}

	});
	// var siteDomain = {
	// 	'rw-pc': 'https://www.rosewholesale.com',
	// 	'rw-wap': 'https://m.rosewholesale.com',
	// 	'rg-pc': 'https://www.rosegal.com',
	// 	'rg-wap': 'https://m.rosegal.com',
	// 	'zf-pc': 'https://www.zaful.com',
	// 	'zf-wap': 'https://m.zaful.com',
	// 	"test-pc": 'http://www.pc-rosegal.com.geshopsign.php5.egomsl.com',
	// 	"test-wap": 'http://m.wap-rosegal.com.geshopsign.php5.egomsl.com',
	// };

	// var siteCode = GESHOP_SITECODE ? GESHOP_SITECODE.split('-')[1] : '';
	// var testSite = 'test-' + siteCode;
	// var siteDomainUrl = siteDomain[testSite];
	// console.log(siteDomainUrl);

	// var siteDomainUrl = siteDomain[GESHOP_SITECODE];

	var activity_sign_info_api = GESHOP_INTERFACE ? GESHOP_INTERFACE.activitysigninfo.url : '';
  var activity_do_sign_api = GESHOP_INTERFACE ? GESHOP_INTERFACE.activitydosign.url : '';

	var staticDomain = $('[data-static-domain]:eq(0)').attr('data-static-domain') ? $('[data-static-domain]:eq(0)').attr('data-static-domain') : "";

	function callback () {
		/*弹窗初始化*/
		if(typeof GEShopSiteCommon !== 'undefined'){
			GEShopSiteCommon.dialog.init();
		}


		$('.U000179Module').on('click', '.rules', function () {
			var $this = this;
			var ruleText = setStyle.popRuleTxt.replace(/;/g, '<span style="margin-bottom:15px;display:block;"></span>');
			var html = '<div class="content gs-blockUi-pc pd0">' +
				'<p class="block-rule-title" style="font-size: ' + setStyle.popRuleTitleSize + 'px;color: ' + setStyle.popRuleTitleColor + '">' + setStyle.popRuleTitle + ' :</p>' +
				'<p class="block-rule-content" style="font-size: ' + setStyle.RuleDescribeSize + 'px;color: ' + setStyle.RuleDescribeColor + '"> ' + ruleText + ' </p></div>';
			// html = html.replace(/;/g, '<span style="margin-bottom:15px;display:block;"></span>');
			var $rule = $('.U000179Module .rules'),
				$ruleBtnOffset = $rule.offset();
			var ruleOptions = {
				modalWidth: 600,
				className: 'blockUi-U000179 blockUi-rules pointer-top',
				content: html,
				cssoptions: {
					top: $ruleBtnOffset.top + $rule.height() + 15 - $(window).scrollTop(),
					left: $ruleBtnOffset.left - 600 + ($rule.width())
				},
				overlayCSS: {
					cursor: 'default'
				}
			};

			GEShopSiteCommon.dialog.custom(ruleOptions);
			$('body').addClass('blockUi-U000179-overflow');

		});
		$.post(activity_sign_info_api, 'content=[]', function (data) {
			if (data.code != 0) {
				return false;
			}
			var html = '';
			var isToday = false;
			var isChecked = false;
			var $component = $('.U000179Module');
			$component.find('.check-status').addClass('check-logined').html(setStyle.UnSignButtonTxt);
			data.data.SignInfo.forEach(function (v, i) {
				if (v.isToday) {
					isToday = true;
					if (v.state === 1) {
						isChecked = true;
						$component.find('.check-status').addClass('check-checked').html(setStyle.SignButtonTxt);
					}
				}
				var num = i + 1;
				var bac = v.state ? setStyle.SignIntegralStateImg : setStyle.UnSignIntegralStateImg;
				var liStatus = v.state ? 'item-checked site-font-bold' : 'item-nocheck site-font-bold';
				/*日期上标*/
				var setDay = setStyle['d' + num];
				var subRegexp = /(ｅ|e|°)/g;
				var setFormat = setDay;
				if(subRegexp.test(setDay)){
					setDay.replace(subRegexp, function () {
						var current = arguments[1];
						setFormat = setDay.replace(current, '<sup>' + current + '</sup>');
					});
				}


				html += '<li class=' + liStatus + ' style="width: ' + setStyle.IntegralBlockItemWidth + 'px;  float: left; margin-right: ' + setStyle.IntegralBlockItemMarginRight + 'px; text-align: center;  font-weight: 600">\
                    <p class="item-img-box" style=" background: url(' + bac + ')  no-repeat center;height: 108px; font-size: ' + setStyle.IntegralBlockItemtxtSize + 'px; ">\
                    <span class="item-point" style="margin-top:' + setStyle.IntegralBlockItemTxtPaddingTop + 'px; display:inline-block;color: ' + setStyle.UnSignIntegralNumColor + '">+' + v.points + '</span>\
                    </p>\
                    <p class="item-day" style="line-height: 50px;font-size: 14px; color: ' + setStyle.UnSignIntegralDayColor + '">' + setFormat + '</p>\
                </li>';
			});
			$component.find('.ulcont').html(html);
			$component.find('.total').text(data.data.myPoints);

			var checkedDays = $component.find('.item-checked').length;
			if (checkedDays > 0) {
				$component.find('.point-title-text').html(setStyle.signSucceedTitle.replace(1, checkedDays));
			}
		}, 'jsonp');

		/* content 内容*/
		var closeBtnHTML = '<p class="closeBtn" >' +
			'<span style="background:' + setStyle.popConfirmButtonBackgroundColor + ' url(' + setStyle.popConfirmButtonBackgroundImg + ');">' + setStyle.popConfirmButtonTxt + '</span></p>';

		var options = {
			content: '<div class="content gs-blockUi-pc" ><p class="txt center site-bold-strict">Failed check in please try again later</p>' + closeBtnHTML + '</div>',
			modalWidth: 415,
			modalHeight: 264,
			className: 'blockUi-U000179',
			cssoptions: {
				backgroundColor: '#fff',
				'-webkit-border-radius': '10px',
				'-moz-border-radius': '10px',
				color: '#333'
			},
			overlayCSS: {
				cursor: 'default'
			}
		};

		$('.U000179Module').on('click', '.sign', function () {
			if ($(this).hasClass('check-checked')) {
				return false;
			}
            
			$.ajax({
				url: activity_do_sign_api,
				type: 'POST',
				dataType: 'jsonp',
				data: 'content=[]',
				timeout: 8000,
				success: function (data, status, xhr) {
					var html = '', area = '', skin = '';
					/*虚拟数据*/
					/*										data.code = 0;
                                        data.data = {
                                            myPoints: 250
                                        };*/
					switch (data.code) {
						case 0://成功
							var checkedDays = data.data.continuousDays;
							var continuousDays = data.data.continuousDays - 1;
							html = '<div style="background-position: center; color: ' + setStyle.popConfirmTxtColor + ';background-size: cover ;background-repeat: no-repeat;background-image: url(' + setStyle.popConfirmBackgroundImg + '); width: 600px; height: 500px;">\
                            <div class="success"> \
                            <p class="tex">' + setStyle.signSucceedPop.replace('10', data.data.myPoints) + ' </p>' + closeBtnHTML + '</div>\
                            </div>';
							options.modalWidth = 600;
							options.modalHeight = 500;
							options.className = 'blockUi-U000179 blockUi-status_success';
							// options.timeOut = 3000;

							var signSucceedPop = setStyle.signSucceedPop ? setStyle.signSucceedPop : "You have checked in successfully and earned " + data.data.todayPoints + " Points today！",
								matchPoint = signSucceedPop.match(/10 points/ig)[0],
								matchValue = matchPoint.split(" ")[1];

							options.content = '<div class="content gs-blockUi-pc pd0" style="background-position: center; color: ' + setStyle.popConfirmTxtColor + ';background-size: cover ;background-repeat: no-repeat;background-image: url(' + setStyle.popConfirmBackgroundImg + '); width: 600px; height: 500px;">' +
								'<p class="txt center success site-bold-strict">' + setStyle.signSucceedPop.replace(matchPoint, '<span class="success-point">' + data.data.todayPoints + ' ' + matchValue + '</span>') + '</p>' + closeBtnHTML + '</div>';
							var $component = $('.U000179Module');

							$component.find('.ulcont li').eq(continuousDays).addClass('item-checked');
							$component.find('.ulcont li').eq(continuousDays).find('p').eq(0).css('background-image', 'url(' + setStyle.SignIntegralStateImg + ')');
							$component.find('.total').text(data.data.myPoints);
							$component.find('.check-status').addClass('check-checked').html(setStyle.SignButtonTxt);
							$component.find('.point-title-text').html(setStyle.signSucceedTitle.replace(1, checkedDays));
							break;
						case 1://未登陆
							var url = location.href;
							location.href = data.data.loginUrl + url;
							return false;
						case 4:
							$('.U000179Module').find('.sign').addClass('check-checked').html(setStyle.SignButtonTxt);
							break;
						default://其他
							skin = '';
							area = '300px';
							options.content = '<div class="content gs-blockUi-pc" style="color:' + setStyle.popConfirmTxtColor + ';"><p class="txt center fail site-bold-strict">' + data.message + '</p>' + closeBtnHTML + '</div>';
							break;
					}

					GEShopSiteCommon.dialog.custom(options);

				},
				error: function (err) {
					options.modelHeight = 264;
					options.content = '<div class="content gs-blockUi-pc" style="color:' + setStyle.popConfirmTxtColor + ';">' +
						'<p class="txt center fail site-bold-strict">' + setStyle.signFail + '</p>' + closeBtnHTML + '</div>';
					GEShopSiteCommon.dialog.custom(options);
				}
			});

		});
		$('body').on('click', '.closeBtn,.blockOverlay', function () {
			$('body').removeClass('blockUi-U000179-overflow');
			$.unblockUI();
		});
		$(window).on('resize', function () {
			var $rule = $('.U000179Module .rules'),
				$ruleBtnOffset;
			if ($rule.length > 0) {
				resizeTimeout && clearTimeout(resizeTimeout);

				var resizeTimeout = setTimeout(function () {
					$ruleBtnOffset = $rule.offset();
					$('.blockUi-rules').css({
						top: $ruleBtnOffset.top + $rule.height() + 15 - $(window).scrollTop(),
						left: $ruleBtnOffset.left - 600 + ($rule.width()),
					});
				}, 100);
			}

		});
	}

	callback();


});
