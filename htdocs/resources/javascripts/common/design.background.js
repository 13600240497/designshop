/*
 * @Author: wuxingtao
 * @Date: 2018/12/4 17:26
 * @Last Modified by: wuxingtao
 * @Last Modified time: 2018/12/4 17:26
 */
'use strict';

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
			layerContent += "        <input type=\"radio\" name=\"page_background_repeat\" value=\"no-repeat\" title=\"不平铺\">\n";
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
				btn: ['取消','保存'],
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
						$layerWrap .find('[name=page_background_repeat][value="' + bg_repeat + '"]').prop('checked', true)
					}
					if (bg_position) {
						$layerWrap .find('a.page-background-position[data-value="' + bg_position + '"]').addClass('current').siblings().removeClass('current')
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
	}({}));
}
