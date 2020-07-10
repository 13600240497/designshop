/***
 * 组件公共配置项
 */
var gburl_prefix = document.getElementById('gb_page_type').value === 'gbad' ? '/gbad' : '/activity/gb';
if (typeof GS_common_style === 'undefined') {
	var GS_common_style = (function (my) {
		my.init = function () {
			/*保存样式*/
			Design.enableLoading();
			var url = gburl_prefix + '/design/get-goods-component-style-setting';
			var data = {
				page_id: $('#pageId').val(),
				lang: $('#pageLang').val(),
			};
			Design.getAjax(url, data).done(function (res) {
				my.openLayer(res);
			});
		};
		my.openLayer = function (res) {
			var publicData = res.data.public || {},
				privateData = res.data.private || {};
			var data = Object.assign(publicData, privateData);
			if (res.code === 0) {
				var getTpl = $('#tpl_geshop_component_stylesheet').html();
				if (!getTpl) {
					return false;
				}
				layui.laytpl(getTpl).render(data, function (html) {
					// layero.find('.layui-layer-content').html(html)
					layer.open({
						title: '商品类组件样式设置',
						type: 1,
						sku: 'layui-goods-list',
						id: 'geshop_style_setting',
						closeBtn: 1,
						anim: 5,
						area: '720px',
						shade: 0.3,
						shadeClose: false,
						resize: false,
						content: html,
						btn: ['取消', '确定'],
						btn1: function (index, layero) {
							layer.close(index);
						},
						btn2: function (index, layero) {
							/*保存样式*/
							Design.enableLoading();
							var url = gburl_prefix + '/design/goods-component-style-setting';
							var data = {
								page_id: $('#pageId').val(),
								lang: $('#pageLang').val(),
								list: {}
							};
							var formData = getComponentFormData('.gb-stylesheet-form');
							var listObj = {
								'private': formData.private,
								'public': formData.public
							};
							data.style_list = JSON.stringify(listObj);

							var successCallBack = function (res) {
								Design.disableLoading();
								if (res.code === 0) {
									layui.layer.msg('样式保存成功！');
									setTimeout(function () {
										window.location.reload();
									}, 400);
								} else {
									layui.layer.msg(res.message);
								}
							};

							var errorCallBack = function (res) {
								Design.disableLoading();
								Design.disableLayuiLoading();
								layui.layer.msg(res.message);
							};

							Design.postAjax(url, data, successCallBack, errorCallBack);
							/* 保存样式end */
							layer.close(index);
						},
						success: function (index, layero) {
							layui.form.render();
						},
						end: function (index, layero) {
						}
					});
				});
			}
			renderColorPicker();

		};
		my.pageForm = function () {
			var baseObj = {
				base_bg_width: 1200,
				base_bg_color: '#FFFFFF',
				base_box_bgImage: '',
				base_box_radius: 0,
				base_box_border_active: 0,
				base_box_border_width: 0,
				base_box_border_color: ''
			};
			var discount = {
				discount_bgc: '#FF8A00',
				discount_ftc: '#FFFFFF',
				discount_height: '50',
				discount_width: '50',
				discount_bgImage: '',
				discount_fts: '16',
				discount_off_fts: '12',
				discount_right: '0',
				discount_top: '0',
			};

			var buyBtn = {
				buy_bgc: '#FF8A00',
				buy_hover_bgc: '#FCC556',
				buy_ftc: '#FFFFFF',
				buy_hover_ftc: '#FFFFFF',
				buy_bg_image: '',
			};

			var buyCart = {
				buy_cart_bgc: '#CC0000',
				buy_cart_hover_bgc: '#E00101',
				buy_cart_color: '#FFFFFF',
				buy_cart_hover_color: '#FFFFFF',
			};

			var shadow = {
				base_bg_shadow: '1',
				base_bg_shadow_color: '#E9E9E9',
				goods_shadow_active: '0',
				goods_shadow_color: '',
			};

			var viewMore = {
				view_more_ftc: '#333333',
				view_more_hover_ftc: '#333333',
				view_more_fts: '20',
			};

			var price = {
				price_color: '#CC0000',
				price_fts: '22',
				price_ft_weight: '0',
			};

			var pageForm = Object.assign(baseObj, discount, buyBtn, buyCart, shadow, viewMore, price);
			return pageForm;
		};
		return my;
	}({}));
	/*背景配置*/
	$('body').on('click', '#edit-page-stylesheet', function () {
		GS_common_style.init();
	});

}


/**
 * 组件背景图设置
 */

var layer = layui.layer;
if (typeof GS_background === 'undefined') {
	/*背景配置*/
	$('body').on('click', '.class-background', function () {
		var $form = $('#component_form'),
			$this = $(this),
			$component_bg_value,
			$component_bg_repeat,
			$component_bg_position,
			$targetInput;
		$targetInput = $(this).parent().find('.component_bg');
		$component_bg_value = $(this).parent().find('[name=component_bg_value]');
		$component_bg_repeat = $(this).parent().find('[name=component_bg_repeat]');
		$component_bg_position = $(this).parent().find('[name=component_bg_position]');
		var imgUrl = $targetInput.val(),
			bg_repeat = $component_bg_repeat.val(),
			bg_position = $component_bg_position.val();
		if (!imgUrl) {
			layer.msg('请补充背景图片');
			return false;
		}
		//统一弹窗
		var $layerWrap = $('#gs_background_wrap');
		if (!$layerWrap.length) {
			var layerContent = "";
			layerContent += "<div id=\"gs_background_wrap\">\n";
			layerContent += "  <form class=\"layui-form\">\n";
			layerContent += "    <div class=\"layui-form-item page-background-repeat\" style=\"margin-top: 8px;\">\n";
			layerContent += "      <div class=\"layui-input-block layui-form-title\">平铺方式<\/div>\n";
			layerContent += "      <div class=\"layui-input-block\">\n";
			layerContent += '        <input type="radio" name="page_background_repeat" value="no-repeat" title="不平铺">';
			layerContent += "        <input type=\"radio\" name=\"page_background_repeat\" value=\"repeat\" title=\"平铺\" checked>\n";
			layerContent += "        <input type=\"radio\" name=\"page_background_repeat\" value=\"repeat-x\" title=\"横向平铺\">\n";
			layerContent += "        <input type=\"radio\" name=\"page_background_repeat\" value=\"repeat-y\" title=\"纵向平铺\">\n";
			layerContent += "      <\/div>\n";
			layerContent += "    <\/div>\n";
			layerContent += "    <div class=\"layui-form-item page-position-repeat\" style=\"margin-top: -4px;\">\n";
			layerContent += "      <div class=\"layui-input-block layui-form-title\">对齐方式<\/div>\n";
			layerContent += "      <div class=\"layui-input-block\">\n";
			layerContent += "        <!-- <input type=\"radio\" name=\"page_background_position\" value=\"top\" title=\"向上\">\n";
			layerContent += "        <input type=\"radio\" name=\"page_background_position\" value=\"right\" title=\"向右\">\n";
			layerContent += "        <input type=\"radio\" name=\"page_background_position\" value=\"bottom\" title=\"向下\">\n";
			layerContent += "        <input type=\"radio\" name=\"page_background_position\" value=\"left\" title=\"向左\">\n";
			layerContent += "        <input type=\"radio\" name=\"page_background_position\" value=\"center\" title=\"居中\" checked>\n";
			layerContent += "        <input type=\"radio\" name=\"page_background_position\" value=\"top left\" title=\"上左\">\n";
			layerContent += "        <input type=\"radio\" name=\"page_background_position\" value=\"top right\" title=\"上右\">\n";
			layerContent += "        <input type=\"radio\" name=\"page_background_position\" value=\"bottom left\" title=\"下左\">\n";
			layerContent += "        <input type=\"radio\" name=\"page_background_position\" value=\"bottom right\" title=\"下右\"> -->\n";
			layerContent += "        <a href=\"javascript:;\" class=\"page-background-position\" data-value=\"top left\"><\/a>\n";
			layerContent += "        <a href=\"javascript:;\" class=\"page-background-position\" data-value=\"top\"><\/a>\n";
			layerContent += "        <a href=\"javascript:;\" class=\"page-background-position\" data-value=\"top right\"><\/a>\n";
			layerContent += "        <a href=\"javascript:;\" class=\"page-background-position\" data-value=\"left\"><\/a>\n";
			layerContent += "        <a href=\"javascript:;\" class=\"page-background-position current\" data-value=\"center\"><\/a>\n";
			layerContent += "        <a href=\"javascript:;\" class=\"page-background-position\" data-value=\"right\"><\/a>\n";
			layerContent += "        <a href=\"javascript:;\" class=\"page-background-position\" data-value=\"bottom left\"><\/a>\n";
			layerContent += "        <a href=\"javascript:;\" class=\"page-background-position\" data-value=\"bottom\"><\/a>\n";
			layerContent += "        <a href=\"javascript:;\" class=\"page-background-position\" data-value=\"bottom right\"><\/a>\n";
			layerContent += "      <\/div>\n";
			layerContent += "    <\/div>\n";
			layerContent += "  <\/form>\n";
			layerContent += "<\/div>\n";
			$('body').append(layerContent);
		}

		if ($('#gs_bg_layer').length > 0) {

		} else {
			layer.open({
				title: '背景图片效果设置',
				type: 1,
				sku: 'layui-goods-list',
				id: 'gs_bg_layer',
				closeBtn: 1,
				anim: 5,
				area: '1000px',
				shade: 0.3,
				shadeClose: true,
				content: $('#gs_background_wrap').find('.layui-form'),
				btn: ['取消', '保存'],
				btn1: function (index, layero) {
					layer.close(index);
				},
				btn2: function (index, layero) {
					$layerWrap = $('#gs_background_wrap');
					var background_repeat = $('[name=page_background_repeat]:checked', $layerWrap).val();
					var background_position = $('a.page-background-position.current', $layerWrap).data('value');
					var background_img = imgUrl;
					$component_bg_repeat.val(background_repeat);
					$component_bg_position.val(background_position);
					$component_bg_value.val("background-repeat:" + background_repeat + ";background-position:" + background_position + ";");
					layer.close(index);
				},
				success: function (index, layero) {
					$layerWrap = $('#gs_background_wrap');
					if (bg_repeat) {
						$layerWrap.find('[name=page_background_repeat][value="' + bg_repeat + '"]').prop('checked', true);
					}
					if (bg_position) {
						$layerWrap.find('a.page-background-position[data-value="' + bg_position + '"]').addClass('current').siblings().removeClass('current');
					}

					layui.form.render();
				},
				end: function (index, layero) {
					/* 清除wrap */
					$('#gs_background_wrap').find('.layui-form').remove();
					$('#gs_background_wrap').remove();
				}
			});
		}
	});

	var GS_background = (function (my) {
		my.init = function () {

		};
		return my;
	}({}));
}
