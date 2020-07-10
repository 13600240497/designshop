import Design from './modules/design.dl.common'

window.Design = Design

var $ = window.$
var layui = window.layui
window.resourceTree = []
window.resourceList = ''
var iframeContentWindowDocument

// 页面初始化调用
Design.renderLayoutTitle()

Design.enableLoading('rgba(255, 255, 255, .3)')

$(function () {
    /**
     * 工具集
     * @type {{debounce: (function(*=, *=, *=): debounced)}}
     */
    var utils = {
        debounce: function(fn, wait, options) {
            wait = wait || 0;
            var timerId;

            function debounced() {
                if (timerId) {
                    clearTimeout(timerId);
                    timerId = null;
                }

                var args = Array.prototype.slice.call(arguments);
                timerId = setTimeout(function() { fn.apply(options.context, args.concat(options)); }, wait);
            }
            return debounced;
        }
    }
    /**
     * 记录drag,drop mouseevent
     * lastMousePageY 上次Y轴坐标
     * @type {{lastMousePageY: number}}
     */
    var dragInfo = {
        lastMousePageY: 0,
        dragStatus: 0 // 1 dragover
    }

	Design.disableLoading()

	$('#design_drag, #design_view').click(function (event) {
		if ($(event.target).closest('.design-form').length <= 0 &&
			!$(event.target).hasClass('component-drag-mask')) {
			$('.design-form').removeClass('design-form-visible')
		}
	});

	$('#js_toggleLeft,#js_toggleLeft_2').click(function () {
		$('.design-left-box').css('width', '80px')
		$('.design-control').css('left', '80px')
		$('.sidebar-btn').removeClass('current')
		$('.template-item-select-container').hide()
	})

	$('.js_tmpCancelSelect').click(function () {
		$('#js_toggleLeft_2').trigger('click')
	})

	// 左侧边栏按钮绑定事件
	$('.sidebar-btn').click(function () {
		var index = $(this).index()
		$(this).addClass('current').siblings().removeClass('current')
		$('.sidebar-container .sidebar-content').eq(index).show().siblings('.sidebar-content').hide()
		$('#design_drag').addClass('design-left-box-visible')
		if (index == 2) {
			$('.template-item-select-container').show()
		} else {
			$('.template-item-select-container').hide()
		}
		if (index == 1 || index == 2 || index == 3) {
			$('.design-left-box-visible').css('width', '408px')
			$('.design-control').css('left', '400px')
		} else {
			$('.design-left-box-visible').css('width', '240px')
			$('.design-control').css('left', '240px')
		}
	})

	// 模板切换
	$('.sidebar-template-list-container').on('click', '.template-list-item', function () {
		var type = $(this).parent().data('type')
		if (type == 1) {
			$('.sidebar-component-container[data-type=2]').children('.template-list-item').removeClass('checked')
		} else {
			$('.sidebar-component-container[data-type=1]').children('.template-list-item').removeClass('checked')
		}
		$(this).addClass('checked').siblings().removeClass('checked')
	})

	$('.template-item-select-container').on('click', '.js_tmpConfirmSelect', function () {
		var item = $('.sidebar-template-list-container .template-list-item.checked')
		if (item.length) {
			var id = item.data('id')
			layui.layer.confirm('切换所需模板后，参数会被替换。', {
				btn: ['取消', '确定'],
				area: '420px',
				icon: 3,
				skin: 'element-ui-dialog-class'
			}, function (index) {
				layui.layer.close(index)
			}, function () {
				$.post('/home/'+ Design.site_code +'/page-tpl/confirm-tpl', {
					tpl_id: id,
					page_id: $('#pageId').val(),
					lang: $('#pageLang').val()
				}, function (res) {
					layui.layer.msg(res.message)

					if (res.code === 0) {
						window.location.reload()
					}
				})
			})

		} else {
			layui.layer.msg('请先选择模板！')
		}
	})

	// 模板切换按钮事件
	$('.sidebar-list-title i').on('click', function () {
		var $ele = $(this).parent()
		var is_visibile = $ele.next().is(':visible')
		if (is_visibile) {
			$ele.addClass('open')
			$ele.next().hide()
		} else {
			$ele.removeClass('open')
			$ele.next().show()
			$ele.parent('.sidebar-list-container').siblings('.sidebar-list-container').find('.sidebar-list-title').addClass('open').next().hide()
		}
	})

	// 模板列表切换按钮事件
	$('.sidebar-child-list-title i').on('click', function () {
		var $ele = $(this).parent()
		var is_visibile = $ele.next().is(':visible')
		if (is_visibile) {
			$ele.addClass('open')
			$ele.next().hide()

		} else {
			$ele.removeClass('open')
			$ele.next().show()
			$ele.siblings('.sidebar-child-list-title').addClass('open').next().hide()
		}
	})

	$('#sync_goodsList').click(function () {
		layui.layer.confirm('是否同步英文版商品sku？', {
			btn: ['否', '是'],
			area: '420px',
			icon: 3,
			skin: 'element-ui-dialog-class'
		}, function (index) {
			layui.layer.close(index)
		}, function (index) {
			$.ajax({
				url: '/home/'+ Design.site_code +'/design/copy-sku',
				type: 'POST',
				data: { lang: $('#pageLang').val(), page_id: $('#pageId').val() },
				success: function (res) {
					if (res.message) {
						layui.layer.msg(res.message)
					}
					layui.layer.close(index)
					if (res.code == 0) {
						window.location.reload()
					}
				},
				error: function (error) {
					if (error && error.responseJSON) {
						layui.layer.msg(error.responseJSON.message)
					}
					layui.layer.close(index)
				}
			})
		})
	})

	$('#sync_config').click(function () {
		layui.layer.confirm('是否同步英文版数据配置？', {
			btn: ['否', '是'],
			area: '420px',
			icon: 3,
			skin: 'element-ui-dialog-class'
		}, function (index) {
			layui.layer.close(index)
		}, function (index) {
			$.get('/home/'+ Design.site_code +'/design/copy-page', {
				lang: $('#pageLang').val(),
				page_id: $('#pageId').val()
			}, function (res) {
				if (res.code == 0) {
					window.location.reload()
				} else {
					layui.layer.msg(res.message)
					layui.layer.close(index)
				}
			})
		})
	})

	// tab列表项新增序号
	var addPageStyleIndex = 0

	$('#add_page_stylesheet').click(function () {
		$.get('/home/'+ Design.site_code +'/design/get-setting', {
			page_id: $('#pageId').val(),
			lang: $('#pageLang').val()
		}, function (res) {
			if (res.code == 0) {
				layui.layer.open({
					type: 1,
					title: '添加页面样式',
					btn: ['取消', '确认'],
					btnAlign: 'c',
					offset: '180px',
					area: '880px',
					content: $('#page_stylesheet_template').html(),
					success: function () {

						// 初始化系统设置、自定义设置显示隐藏
						var type = res.data.general.style_type
						if (type == 1) {
							$('.page-system-settings-container').show()
							$('.page-custom-settings-container').hide()
						} else if (type == 2) {
							$('.page-custom-settings-container').show()
							$('.page-system-settings-container').hide()
						}

						// 选取通用样式配置项
						var self = $('.page-stylesheet-list-container .layui-tab-item:eq(0)')
						self.find('.page-style-select-container input[value=' + res.data.general.style_type + ']').prop('checked', true)
						self.find('[name=page_custom_css]').val(res.data.general.custom_css)
						self.find('[name=page_background_color]').val(res.data.general.background_color)
						self.find('[name=page_background_color]').prev().find('div').css('backgroundColor', res.data.general.background_color)
						self.find('[name=page_background_image]').val(res.data.general.background_image)
						// 有背景图片才显示平铺方式和对齐方式
						if (res.data.general.background_image) {
							self.find('.page-background-repeat').show()
							self.find('.page-position-repeat').show()
						} else {
							self.find('.page-background-repeat').hide()
							self.find('.page-position-repeat').hide()
						}

						if (res.data.general.background_repeat.length > 0) {
							self.find('[name=page_background_repeat][value="' + res.data.general.background_repeat + '"]').prop('checked', true)
						}
						if (res.data.general.background_position.length > 0) {
							self.find('a.page-background-position[data-value="' + res.data.general.background_position + '"]').addClass('current').siblings().removeClass('current')
						}

						// 初始化自定义tab
						// 看到这段代码的人不要打我
						var tabHTML = ''
						var contentHtml = ''
						var list = res.data.list
						if (list != null) {
							list.forEach(function (item, index) {
								tabHTML += '<li lay-id="' + (index + 1) + '">' + item.class_name + '</li>'
								contentHtml += '<div class="layui-tab-item"><div class="layui-form-item">' +
									'<div class="layui-input-block layui-form-title">样式名称</div>' +
									'<div class="layui-input-block">' +
									'<input type="text" class="layui-input class-name" style="width:406px" name="" autocomplete="off" value="' + item.class_name + '">' +
									'</div>' +
									'</div>' +
									'<div class="layui-form-item" style="margin-top: 8px;">' +
									'<div class="layui-input-block layui-form-title">时间设置</div>' +
									'<div class="layui-input-block" style="position:relative;width:406px">' +
									'<span class="time-icon"></span><input type="text" class="layui-input start-time js_start_time" style="display:inline;width:203px;border-right:none" name="" autocomplete="off" value="' + Design.dateConverse(item.start_time) + '"><span class="time-split">至</span>' +
									'<input type="text" class="layui-input end-time js_end_time" style="display:inline;width:203px;border-left:none;margin-left:-1px" name="" autocomplete="off" value="' + Design.dateConverse(item.end_time) + '"><span class="time-settings-tips">（注:时间段不要与其他页面样式出现交集哦）</span>' +
									'</div>' +
									'</div>' +
									'<div class="layui-form-item page-style-select-container" style="margin-top: 8px;">' +
									'<div class="layui-input-block layui-form-title">页面样式选择<span style="margin-left:10px;color:#9E9E9E;font-size:12px;">（注：当无任何切换的时间段时，页面样式按照通用样式设置进行展示）</span></div>' +
									'<div class="layui-input-block">'
								if (item.style_type == 1) {
									contentHtml += '<input type="radio" name="page_style_select_' + (index + 1) + '" lay-filter="system-custom-settings" value="1" title="系统设置" checked><input type="radio" name="page_style_select_' + (index + 1) + '" lay-filter="system-custom-settings" value="2" title="自定义设置">'
								} else if (item.style_type == 2) {
									contentHtml += '<input type="radio" name="page_style_select_' + (index + 1) + '" lay-filter="system-custom-settings" value="1" title="系统设置"><input type="radio" name="page_style_select_' + (index + 1) + '" lay-filter="system-custom-settings" value="2" title="自定义设置" checked>'
								}
								contentHtml += '</div>' +
									'</div>'
								if (item.style_type == 1) {
									contentHtml += '<div class="page-system-settings-container" style="margin-top: -4px;">'
								} else {
									contentHtml += '<div class="page-system-settings-container" style="display:none;margin-top: -4px;">'
								}
								contentHtml += '<div class="layui-form-item">' +
									'<div class="layui-input-block layui-form-title">整体页面背景颜色</div>' +
									'<div class="layui-input-block">' +
									'<div class="color-picker-selector" data-hidden-name="page_background_color">' +
									'<div style="background-color:' + (item.style.background_color ? item.style.background_color : 'transparent') + '"></div>' +
									'</div>' +
									'<input type="text" class="layui-input background-color" style="width:406px" name="page_background_color" autocomplete="off" value="' + (item.style.background_color ? item.style.background_color : '') + '">' +
									'</div>' +
									'</div>' +
									'<div class="layui-form-item" style="margin-top: 4px;">' +
									'<div class="layui-input-block layui-form-title">整体页面背景图片</div>' +
									'<div class="layui-input-block">' +
									'<a href="javascript:;" class="js_openResource design-open-resource" data-type="tabBgImg">' +
									'<i class="layui-icon">&#xe64a;</i>' +
									'</a>' +
									'<input type="text" name="page_background_image" style="width:406px" autocomplete="off" class="layui-input background-image" value="' + (item.style.background_image ? item.style.background_image : '') + '">' +
									'</div>' +
									'</div>'
								if (item.style.background_image) {
									contentHtml += '<div class="layui-form-item page-background-repeat" style="display:block;margin-top: 8px;">'
								} else {
									contentHtml += '<div class="layui-form-item page-background-repeat" style="display:none;margin-top: 8px;">'
								}
								contentHtml += '<div class="layui-input-block layui-form-title">平铺方式</div>' +
									'<div class="layui-input-block">'
								if (item.style_type == 1) {
									if (item.style.background_repeat == 'no-repeat') {
										contentHtml += '<input type="radio" name="page_background_repeat_' + (index + 1) + '" value="no-repeat" title="不平铺" checked>'
									} else {
										contentHtml += '<input type="radio" name="page_background_repeat_' + (index + 1) + '" value="no-repeat" title="不平铺">'
									}
									if (item.style.background_repeat == 'repeat') {
										contentHtml += '<input type="radio" name="page_background_repeat_' + (index + 1) + '" value="repeat" title="平铺" checked>'
									} else {
										contentHtml += '<input type="radio" name="page_background_repeat_' + (index + 1) + '" value="repeat" title="平铺">'
									}
									if (item.style.background_repeat == 'repeat-x') {
										contentHtml += '<input type="radio" name="page_background_repeat_' + (index + 1) + '" value="repeat-x" title="横向平铺" checked>'
									} else {
										contentHtml += '<input type="radio" name="page_background_repeat_' + (index + 1) + '" value="repeat-x" title="横向平铺">'
									}
									if (item.style.background_repeat == 'repeat-y') {
										contentHtml += '<input type="radio" name="page_background_repeat_' + (index + 1) + '" value="repeat-y" title="纵向平铺" checked>'
									} else {
										contentHtml += '<input type="radio" name="page_background_repeat_' + (index + 1) + '" value="repeat-y" title="纵向平铺">'
									}
								} else if (item.style_type == 2) {
									contentHtml += '<input type="radio" name="page_background_repeat_' + (index + 1) + '" value="no-repeat" title="不平铺"><input type="radio" name="page_background_repeat_' + (index + 1) + '" value="repeat" title="平铺" checked><input type="radio" name="page_background_repeat_' + (index + 1) + '" value="repeat-x" title="横向平铺"><input type="radio" name="page_background_repeat_' + (index + 1) + '" value="repeat-y" title="纵向平铺">'
								}

								contentHtml += '</div>' +
									'</div>'
								if (item.style.background_image) {
									contentHtml += '<div class="layui-form-item page-position-repeat" style="display:block;margin-top: -4px;">'
								} else {
									contentHtml += '<div class="layui-form-item page-position-repeat" style="display:none;margin-top: -4px;">'
								}

								contentHtml += '<div class="layui-input-block layui-form-title">对齐方式</div>' +
									'<div class="layui-input-block">'
								if (item.style_type == 1) {
									if (item.style.background_position == 'top') {
										contentHtml += '<a href="javascript:;" class="page-background-position current" data-value="top"></a>'
									} else {
										contentHtml += '<a href="javascript:;" class="page-background-position" data-value="top"></a>'
									}
									if (item.style.background_position == 'right') {
										contentHtml += '<a href="javascript:;" class="page-background-position current" data-value="right"></a>'
									} else {
										contentHtml += '<a href="javascript:;" class="page-background-position" data-value="right"></a>'
									}
									if (item.style.background_position == 'bottom') {
										contentHtml += '<a href="javascript:;" class="page-background-position current" data-value="bottom"></a>'
									} else {
										contentHtml += '<a href="javascript:;" class="page-background-position" data-value="bottom"></a>'
									}
									if (item.style.background_position == 'left') {
										contentHtml += '<a href="javascript:;" class="page-background-position current" data-value="left"></a>'
									} else {
										contentHtml += '<a href="javascript:;" class="page-background-position" data-value="left"></a>'
									}
									if (item.style.background_position == 'center') {
										contentHtml += '<a href="javascript:;" class="page-background-position current" data-value="center"></a>'
									} else {
										contentHtml += '<a href="javascript:;" class="page-background-position" data-value="center"></a>'
									}
									if (item.style.background_position == 'top left') {
										contentHtml += '<a href="javascript:;" class="page-background-position current" data-value="top left"></a>'
									} else {
										contentHtml += '<a href="javascript:;" class="page-background-position" data-value="top left"></a>'
									}
									if (item.style.background_position == 'top right') {
										contentHtml += '<a href="javascript:;" class="page-background-position current" data-value="top right"></a>'
									} else {
										contentHtml += '<a href="javascript:;" class="page-background-position" data-value="top right"></a>'
									}
									if (item.style.background_position == 'buttom left') {
										contentHtml += '<a href="javascript:;" class="page-background-position current" data-value="buttom left"></a>'
									} else {
										contentHtml += '<a href="javascript:;" class="page-background-position" data-value="buttom left"></a>'
									}
									if (item.style.background_position == 'bottom right') {
										contentHtml += '<a href="javascript:;" class="page-background-position current" data-value="bottom right"></a>'
									} else {
										contentHtml += '<a href="javascript:;" class="page-background-position" data-value="bottom right"></a>'
									}
								} else if (item.style_type == 2) {
									contentHtml += '<a href="javascript:;" class="page-background-position" data-value="top"></a>'
									contentHtml += '<a href="javascript:;" class="page-background-position" data-value="right"></a>'
									contentHtml += '<a href="javascript:;" class="page-background-position" data-value="bottom"></a>'
									contentHtml += '<a href="javascript:;" class="page-background-position" data-value="left"></a>'
									contentHtml += '<a href="javascript:;" class="page-background-position current" data-value="center"></a>'
									contentHtml += '<a href="javascript:;" class="page-background-position" data-value="top left"></a>'
									contentHtml += '<a href="javascript:;" class="page-background-position" data-value="top right"></a>'
									contentHtml += '<a href="javascript:;" class="page-background-position" data-value="buttom left"></a>'
									contentHtml += '<a href="javascript:;" class="page-background-position" data-value="bottom right"></a>'
								}

								contentHtml += '</div>' +
									'</div>' +
									'</div>'
								if (item.style_type == 2) {
									contentHtml += '<div class="page-custom-settings-container" style="display:block">'
								} else {
									contentHtml += '<div class="page-custom-settings-container" style="display:none">'
								}
								contentHtml += '<div class="layui-form-item custom-settings-stylesheet">' +
									'<div class="layui-input-block layui-form-title">页面样式</div>' +
									'<div class="layui-input-block">'
								if (item.style_type == 2) {
									contentHtml += '<textarea name="page_custom_css" style="width:800px" placeholder="请输入样式代码" class="layui-textarea">' + item.style.custom_css + '</textarea>'
								} else {
									contentHtml += '<textarea name="page_custom_css" style="width:800px" placeholder="请输入样式代码" class="layui-textarea"></textarea>'
								}

								contentHtml += '</div>' +
									'</div>' +
									'</div>' +
									'</div>'
							})
							$('.page_style_option_bar .layui-tab-title li').after(tabHTML)
							$('.page-stylesheet-list-container .layui-tab-item').after(contentHtml)
						}

						// 初始化日期选择器
						Design.renderDatetime()
						// 初始化tab
						layui.element.render('tab')
						// 初始化tab添加序列号
						addPageStyleIndex = $('.page_style_option_bar .layui-tab-title li').length

						layui.form.render()
						Design.renderColorPicker()
					},
					btn2: function () {
						var url = '/home/'+ Design.site_code +'/design/setting'
						var data = {
							page_id: $('#pageId').val(),
							lang: $('#pageLang').val(),
						}
						var list = []
						var time_status = false
						var start_end_status = false
						var time_cross = false

						var self = $('.page-stylesheet-list-container .layui-tab-item:eq(0)')
						var general_type = self.find('.page-style-select-container input:checked').val()
						var general_custom_css = self.find('textarea[name=page_custom_css]').val()
						var general_background_color = self.find('[name=page_background_color]').val()
						var general_background_image = self.find('[name=page_background_image]').val()
						var general_background_repeat = self.find('.page-background-repeat input[type=radio]:checked').val()
						var general_background_position = self.find('a.page-background-position.current').data('value')

						data.general = {
							style_type: Number(general_type)
						}

						if (general_type == 1) {
							data.general.background_color = general_background_color
							data.general.background_image = general_background_image
							data.general.background_repeat = general_background_repeat
							data.general.background_position = general_background_position
						} else if (general_type == 2) {
							data.general.custom_css = general_custom_css
						}

						data.custom = {}

						// 开始和结束时间数组
						var start_array = []
						var end_array = []

						var compareDate = function (begin, over) {
							begin = begin.sort()
							over = over.sort()
							for (var i = 1; i < begin.length; i++) {
								if (begin[i] < over[i - 1]) {
									layui.layer.msg('时间段不能与其他页面样式出现交集', { time: 5000 })
									return false
								}
							}
							return true
						}

						// 遍历样式tab
						$('.page-stylesheet-list-container .layui-tab-item:gt(0)').each(function () {
							var index = $(this).index()
							var obj = {}
							var type = $(this).find('.page-style-select-container input:checked').val()
							var start_time_value = $(this).find('input.start-time').val()
							var end_time_value = $(this).find('input.end-time').val()
							var start_time = Date.parse(new Date(start_time_value))
							var end_time = Date.parse(new Date(end_time_value))
							obj.style_type = Number(type)
							obj.class_name = $(this).find('input.class-name').val()
							// 日期为必须项
							if (start_time_value == '' || end_time_value == '') {
								time_status = false
								layui.layer.msg('请设置时间，不设置请先删除对应tab页签')
								return false
							} else {
								time_status = true
							}

							// 结束时间必须大于开始时间
							if (start_time <= end_time) {
								start_array.push(start_time)
								end_array.push(end_time)
								obj.start_time = start_time
								obj.end_time = end_time
								start_end_status = true
							}
							else {
								start_end_status = false
								layui.layer.msg('开始时间必须小于结束时间！')
								return false
							}

							if (type == 1) {
								obj.style = {
									background_color: $(this).find('[name=page_background_color]').val(),
									background_image: $(this).find('[name=page_background_image]').val(),
									background_repeat: $(this).find('.page-background-repeat input[type=radio]:checked').val(),
									background_position: $(this).find('a.page-background-position.current').data('value')
								}
							} else if (type == 2) {
								obj.style = {
									custom_css: $(this).find('[name=page_custom_css]').val()
								}
							}
							list.push(obj)
						})

						data.custom.list = list

						var successCallBack = function (res) {
							Design.disableLoading()
							if (res.code == 0) {
								layui.layer.msg('页面样式保存成功，请到预览页面查看效果！')
							} else {
								layui.layer.msg(res.data)
							}
						}

						var errorCallBack = function (res) {
							Design.disableLoading()
							Design.disableLayuiLoading()
							layui.layer.msg(res.message)
						}


						data.general = JSON.stringify(data.general)
						data.custom = JSON.stringify(data.custom)

						// 校验时间选择是否有交集
						time_cross = compareDate(start_array, end_array)

						// 只有通用设置，无自定义样式
						if ($('.page-stylesheet-list-container .layui-tab-item').length == 1) {
							Design.postAjax(url, data, successCallBack, errorCallBack)
							return false
						} else {
							// 日期为必选项
							// 日期开始时间不能大于结束时间
							// 日期不能有交集
							if (!time_status || !start_end_status || !time_cross) {
								return false
							} else {
								Design.postAjax(url, data, successCallBack, errorCallBack)
								return false
							}
						}
					},
					cancel: function (index) {
						layui.layer.close(index)
					}
				})
			} else {
				layui.layer.msg(res.message)
			}
		})
	})

	// 首页管理 - 生成模板
	$('#generate_page_model').click(function () {
		layui.layer.open({
			title: '新增页面模板',
			btn: ['取消', '确认'],
			type: '1',
			area: '720px',
			btnAlign: 'c',
			content: $('#page_model_template').html(),
			success: function () {
				layui.form.render()
				layui.upload.render({
					elem: '#upload_model_picture',
					field: 'files',
					url: '/home/zf/page-tpl/upload-pic',
					accept: 'images',
					exts: 'jpg|png|gif|bmp|jpeg|wepb',
					size: 3 * 1024,
					before: function () {
						Design.enableLayuiLoading()
					},
					done: function (res) {
						Design.disableLayuiLoading()
						if (res.code === 0) {
							$('[name=model_pic').val(res.data.url)
							var preImg = res.data.url
							$('#upload_model_picture').css('background-image', 'url(' + preImg + ')')
							$('#upload_model_picture>i').css('opacity', '0')
							$('#upload_model_picture>p').css('opacity', '0')
						} else {
							layui.layer.msg(res.message)
						}
					},
					error: function () {
						Design.disableLayuiLoading()
						layui.layer.msg('接口错误！')
					}
				})
			},
			yes: function (index) {
				layui.layer.close(index)
			},
			btn2: function (index) {
				var url = '/home/zf/page-tpl/add'
				var data = {
					pageId: $('#pageId').val(),
					lang: $('#pageLang').val(),
					name: $('[name=model_name]').val(),
					pic: $('[name=model_pic]').val(),
					site_code: $('#siteCode').val(),
					pid: window.location.search.substr(5, window.location.search.length),
					type: $('select[name=model_type]').val() //1 共有,2私有
				}

				if (data.name == '') {
					layui.layer.msg('名称不能为空')
					return false
				} else if (data.name.length > 50) {
					layui.layer.msg('名称不能超过50个字符')
					return false
				}

				var successCallBack = function (res) {
					Design.disableLoading()
					if (res.code == 0) {
						layui.layer.msg('页面模板生成成功')
						layui.layer.close(index)
					} else {
						layui.layer.msg(res.data, {
							time: 10000
						})
					}
				}

				var errorCallBack = function (res) {
					Design.disableLoading()
					Design.disableLayuiLoading()
					layui.layer.msg(res.message)
				}

				Design.postAjax(url, data, successCallBack, errorCallBack)
			},
			cancel: function (index) {
				layui.layer.close(index)
			}
		})

		/* 获取模板快照 */
		var pageLanguageId = $('#pageLanguageId').val()
		var siteCode = $('#siteCode').val()
		Design.postAjax('/activity/page-tpl/get-snapshot', {
			site_code: siteCode,
			action: 'home',
			id: pageLanguageId
		}, function (res) {
			Design.disableLoading()
			Design.disableLayuiLoading()
			if(res.code === 0){
				$('.preview-img img').attr('src',res.data).css({'width': '180px',	'height': 'auto'})
				$('input[name=model_pic]').val(res.data)
			}
		}, function (res) {
			Design.disableLoading()
			Design.disableLayuiLoading()
			layui.layer.msg(res.message)
			Design.disableLoading()
		})
	})

    // 将首页发布状态存储到sessionStorage GESHOP_HOME_PAGE_STATUS
    var geshop_home_page_status = typeof GESHOP_HOME_PAGE_STATUS !== 'undefined' ? GESHOP_HOME_PAGE_STATUS : 0
    sessionStorage.setItem('GESHOP_HOME_PAGE_STATUS', geshop_home_page_status)

	// 首页管理 - 站点装修页发布并设置为首页
	$('#generate_page').click(function () {
		layui.layer.open({
			title: '请选择要发布的页面',
			btn: ['取消', '确定'],
			skin: 'gb_sync_class',
			type: '1',
			area: '720px',
			btnAlign: 'c',
			content: $('#page_language_generate_template').html(),
			success: function () {
				layui.form.render()
                var status = sessionStorage.getItem('GESHOP_HOME_PAGE_STATUS')
                if (status && status != 1 && status != 3) {
                    $('#page_lang_wraper').show()
                } else {
                    $('#page_lang_wraper').hide()
                }
			},
			btn2: function (index) {
                var indexType = parseInt($('input[name="indexType"]:checked').val())
                var url = indexType === 1 ? '/home/'+ Design.site_code +'/design/online' : '/home/'+ Design.site_code + '/design/online-b'
                var pageStatus = parseInt($('#pageStatus').val())

                if (pageStatus === 5) {
                    layui.layer.msg('当前页面推送中，请等待推送完成后操作')
                    return false
                }
                if(pageStatus === 2 && indexType === 2 || pageStatus === 7 && indexType === 2){
                    layui.layer.msg('当前页已经是A首页，不可设置为B首页')
                    return false
                }

                var langArr = []
                $('#page_lang_wraper').find('.layui-form-checked').each(function() {
                    var $this = $(this)
                    var lang = $this.prev('input[type="checkbox"]').attr('name')
                    langArr.push(lang)
                })

                var status = sessionStorage.getItem('GESHOP_HOME_PAGE_STATUS')
                if (status != 1 && status != 3 && langArr.length == 0) {
                    layui.layer.msg('请选择语言')
                    return false
                }

                var params = {
                    page_id: $('#pageId').val()
                }

                if (status != 1 && status != 3) {
                    params.langList = langArr.join(',')
                }

				Design.postAjax(url, params, function (res) {
					layui.layer.msg(res.message)
					Design.disableLoading()
                    if (res.code === 0) {
                        $('#page_link_template').html(
                            '<a href="' + res.data.current.url + '" target="_blank" class="layui-btn layui-btn-normal">' + res.data.current.name + '</a>'
                        )
                    }
				}, function (res) {
					layui.layer.msg(res.message)
					Design.disableLoading()
				})
			}
		})
	})

	// 首页 - 生成模板
	$('#advertisement_generate_page_model').click(function () {
		layui.layer.open({
			title: '新增页面模板',
			btn: ['取消', '确认'],
			type: '1',
			area: '720px',
			btnAlign: 'c',
			content: $('#page_model_template').html(),
			success: function () {
				layui.form.render()
				layui.upload.render({
					elem: '#upload_model_picture',
					field: 'files',
					url: '/advertisement/page-tpl/upload-pic',
					accept: 'images',
					exts: 'jpg|png|gif|bmp|jpeg|wepb',
					size: 3 * 1024,
					before: function () {
						Design.enableLayuiLoading()
					},
					done: function (res) {
						Design.disableLayuiLoading()
						if (res.code === 0) {
							$('[name=model_pic]').val(res.data.url)
							var preImg = res.data.url
							$('#upload_model_picture').css('background-image', 'url(' + preImg + ')')
							$('#upload_model_picture>i').css('opacity', '0')
							$('#upload_model_picture>p').css('opacity', '0')
						} else {
							layui.layer.msg(res.message)
						}
					},
					error: function () {
						Design.disableLayuiLoading()
						layui.layer.msg('接口错误！')
					}
				})
			},
			yes: function (index) {
				layui.layer.close(index)
			},
			btn2: function (index) {
				var url = '/advertisement/page-tpl/add'
				var data = {
					pageId: $('#pageId').val(),
					lang: $('#pageLang').val(),
					name: $('[name=model_name]').val(),
					pic: $('[name=model_pic]').val(),
					site_code: $('#siteCode').val(),
					pid: window.location.search.substr(5, window.location.search.length),
					type: $('select[name=model_type]').val()//1 共有,2私有
				}

				if (data.name == '') {
					layui.layer.msg('名称不能为空')
					return false
				} else if (data.name.length > 50) {
					layui.layer.msg('名称不能超过50个字符')
					return false
				}

				var successCallBack = function (res) {
					Design.disableLoading()
					if (res.code == 0) {
						layui.layer.msg('页面模板生成成功')
						layui.layer.close(index)
					} else {
						layui.layer.msg(res.data, {
							time: 10000
						})
					}
				}

				var errorCallBack = function (res) {
					Design.disableLoading()
					Design.disableLayuiLoading()
					layui.layer.msg(res.message)
				}

				Design.postAjax(url, data, successCallBack, errorCallBack)
			},
			cancel: function (index) {
				layui.layer.close(index)
			}
		})
	})

	$('#preview_page,#preview_pageUrl').click(function () {
		window.open($(this).data('href'))
		// 按钮失去焦点
		$(this).blur()
	})

	/**
	 * 三端合并后，PC转M端，M转APP不再需要传site code
	 */
	$('#view_convert_page').change(function () {
		location.href = $(this).val()
	})


	/**
	 * lay 全选
	 * @param all_filter all_check input lay-filter name
	 * @param one_filter one_check input lay-filter name
	 */
	function checkbox_all(all_filter,one_filter,selector){
		// 全选
		layui.form.on('checkbox('+ all_filter +')', function (data) {
			var checked = data.elem.checked || false;
			$(selector).not(':disabled').prop('checked',checked);
			layui.form.render('checkbox');
		});

		layui.form.on('checkbox('+ one_filter +')', function (data) {
			var item = $(selector);
			// 存在未选
			for(var i=0;i<item.length;i++){
				if(item[i].checked == false){
					$('input[name='+ all_filter +']').prop('checked',false);
					layui.form.render('checkbox');
					break;
				}
			}
			// 全选
			var allLength = item.length;
			for (var i = 0; i <item.length ; i++) {
				if(item[i].checked == true){
					allLength--;
				}
			}
			if(allLength == 0){
				$('input[name='+ all_filter +']').prop('checked',true);
			}
			layui.form.render('checkbox');
		});
	}


	/**
	 *  同步渠道数据
	 */
	$('#sync_channel').click(function () {
		layui.layer.open({
			title: '同步其他渠道信息',
			btn: ['取消', '确定'],
			skin: 'gb_sync_class',
			type: '1',
			area: '720px',
			btnAlign: 'c',
			content: $('#page_pipeline_sync_template').html(),
			success: function () {
				var $target = $('.gb_sync_class');
				Design.getAjax('/home/zf/design/get-copy-pipeline', {
					page_id: $('#pageId').val(),
					lang: $('#pageLang').val()
				}).done( function (res) {
					if (res.code == 0) {
						var html = '';
						var channelArr = res.data.fromPipeline;
						var channelGroupObj = {};
						if (channelArr.length > 0) {
							channelArr.forEach(function (element) {
								html += '<option value="' + element.pipeline + '">' + element.pipeline_name + '</option>'
								channelGroupObj[element.pipeline] = element;
							})

							$('[name="pipeline_selected"]').html(html)
						} else {
							/*layui.layer.msg('渠道数据不存在');*/
						}

						var channel_init = function () {
							if (res.data.toPipeline.length > 0) {
								var channelTab = '', channelContent = '', langListHtml = '';
								res.data.toPipeline.forEach(function (item, index) {
									var currentThis = '', currentClass = 'layui-tab-item';
									if (index === 0) {
										currentThis = 'layui-this';
										currentClass = 'layui-tab-item layui-show';
									}
									channelTab += '<li class="swiper-slide" data-key="' + item.pipeline + '" class=' + currentThis + '>' + item.pipeline_name + '</li>';
									//语言列表
									var channelContent = '<div class="' + currentClass + '" data-channel1=' + item.pipeline + ' data-channel=' + item.page_id + '>' +
										'<input type="checkbox" name="check_all_'+ item.pipeline+'" class="input_check_all" lay-filter="check_all_'+ item.pipeline+'" title="全选" lay-skin="primary">';
									item.langList.forEach(function (childItem) {
										channelContent += '<input type="checkbox" class="input_item" name="lang_' + childItem.key + '_' + item.pipeline + '" title="' + childItem.name + '" ' +
											'data-value="' + childItem.key + '" value="' + item.pipeline + '" lay-skin="primary" lay-filter="sync_checkone_'+ item.pipeline +'">';
									});
									channelContent += '</div>';
									langListHtml += channelContent;
								});

								$('.layui-tab-title', $target).html(channelTab);
								$('.layui-tab-content', $target).html(langListHtml);

								/* 全选语言 */
								$('.layui-tab-item',$target).each(function(index,element){
									var check_all_filter = $(element).find('.input_check_all').attr('lay-filter');
									var check_one_filter = $(element).find('.input_item').attr('lay-filter');
									checkbox_all(check_all_filter,check_one_filter,$('.input_item',$(element)))
								})

								/* swiper slide */
								$('.swiper-wrapper', $target).html(channelTab);
								var channelSwiper = new Swiper($('.channel-container', $target), {
									// centeredSlides: true,
									slidesPerView: 5,
									spaceBetween: 10,
									slidesPerGroup: 5,
									// freeMode: true,
									// loop: true,
									loopFillGroupWithBlank: true,
									navigation: {
										nextEl: '.swiper-button-next',
										prevEl: '.swiper-button-prev',
									},
									nextButton: '.swiper-button-next',
									prevButton: '.swiper-button-prev',
								});
								if ($('.layui-tab-content .layui-tab-item', $target).length <= 5) {
									if(channelSwiper.navigation){
										channelSwiper.navigation.$nextEl.addClass('layui-hide');
										channelSwiper.navigation.$prevEl.addClass('layui-hide');
									}else{
										channelSwiper.nextButton.addClass('layui-hide');
										channelSwiper.prevButton.addClass('layui-hide');
									}

								}

							}
						};

						/**
						 * 渲染被同步语言
						 * @param channelKey 语言简码
						 */
						var renderLangList = function (channelKey) {
							var channelListArr = channelKey ? channelGroupObj[channelKey].langList : channelArr.length>0 ? channelArr[0].langList : [];
							var langList = '';
							channelListArr.forEach(function (item) {
								langList += '<option value="' + item.key + '">' + item.name + '</option>';
							});
							$target.find('[name=lang_list]').html(langList);
							layui.form.render('select');

							/* swiper slide */
							$('.channel-container .swiper-wrapper li').on('click', function () {
								// var index = $(this).attr('data-swiper-slide-index')
								var index = $(this).index();
								$(this).addClass('layui-this').siblings().removeClass('layui-this');
								$('.channel-container').find('.layui-tab-item:eq(' + index + ')').addClass('layui-show').siblings().removeClass('layui-show');
							});
						};
						/**
						 * 禁用选中的渠道被同步
						 * @param channelKey 语言简码
						 */
						var disableLang = function (channelKey) {
							var current_pipeline_selected = channelKey ? channelKey : $('[name="pipeline_selected"]').val();
							var current_lang_selected = $('[name="lang_list"]').val();
							$('.layui-tab-item[data-channel1='+ current_pipeline_selected +'] .input_check_all', $target).prop('checked',false);
							$('.layui-tab-item input', $target).each(function () {
								var target = $(this);
								if (target.val() == current_pipeline_selected && target.attr('data-value') == current_lang_selected) {
									target.prop('checked', false);
									target.attr('disabled', 'disabled');
								} else {
									target.removeAttr('disabled');
								}
							});
							check_all_reset();
						};
						var check_all_reset = function(){
							$('.layui-tab-item', $target).each(function(index,item){
								var checked = false;
								if($(item).find('.input_item').length > $(item).find('.input_item:checked').length){
									checked = false
								}else{
									checked = true;
								}
								$(item).find('.input_check_all').prop('checked',checked);
							})
						}

						channel_init();
						renderLangList();
						disableLang();

						layui.form.render()

						// 监听渠道radio
						layui.form.on('select(pipeline_selected)', function (data) {
							var channelKey = data.value;
							renderLangList(channelKey);
							disableLang(channelKey);
							layui.form.render('checkbox');
						})
						// 监听语言radio
						layui.form.on('select(lang_list)', function (data) {
							disableLang();
							layui.form.render('checkbox');
						})
					}
				})
			},
			btn2: function (index) {
				var $target = $('.gb_sync_class');
				if ($('.layui-tab-item input.input_item:checked').length > 0 && $('[name=pipeline_selected]',$target).val() && $('[name=lang_list]',$target).val() ) {
					var toVal = []

					$('.layui-tab-item input.input_item:checked').each(function () {
						toVal.push($(this).val())
					})

					var from = {'pipeline': $('[name="pipeline_selected"]',$target).val(), 'lang': $('[name="lang_list"]',$target).val()};
					var to = [];
					$('.layui-tab-item',$target).each(function(index,item){
						var pipeLangArr = [];
						$(item).find('.input_item:checked').each(function(langIndex,langItem){
							pipeLangArr.push($(langItem).attr('data-value'));
						})
						if(pipeLangArr.length > 0){
							to.push({
								'pipeline': $(item).attr('data-channel1'),
								'lang': pipeLangArr.toString()
							})
						}

					})

					Design.postAjax('/home/zf/design/copy-pipeline', {
						page_id: $('#pageId').val(),
						type: $('[name="pipeline_type"]:checked').val(),
						from: JSON.stringify(from),
						to: JSON.stringify(to)
					}, function (res) {
						if (res.code == 0) {
							layui.layer.msg('数据同步成功')
							window.location.reload()
						} else {
							layui.layer.msg(res.message)
						}

						layui.layer.close(index)
						Design.disableLoading()
					}, function (res) {
						layui.layer.msg(res.message)
						layui.layer.close(index)
						Design.disableLoading()
					})
				} else {
					layui.layer.msg('请选择必选内容')

					return false
				}
			}
		})
	})

	// 首页 - 重新发布
	// $('body').on('click', '.redistribution', function (index) {
	// 	$.post('/home/' + Design.site_code + '/design/release', {
	// 		page_id: $('#pageId').val(),
	// 		lang: $('#pageLang').val()
	// 	}, function (res) {
	// 		layui.layer.msg(res.message)
	// 		layui.layer.close(index)
	// 	})
	// })

	// 首页 - 重新发布
	$('body').on('click', '.adRedistribution', function (index) {
		$.post('/advertisement/design/release', {
			page_id: $('#pageId').val(),
			lang: $('#pageLang').val()
		}, function (res) {
			layui.layer.msg(res.message)
			layui.layer.close(index)
		})
	})

	// 重新发布
	$('body').on('click', '.redistribution', function (index) {
		layui.layer.closeAll();
		$('#release_page_AB').trigger('click');
		/*		$.post('/home/zf/design/release', {
                    page_id: $('#pageId').val(),
                    lang: $('#pageLang').val()
                }, function (res) {
                    layui.layer.msg(res.message)
                    layui.layer.close(index)
                })*/
	})

	$('#generate_page').click(function () {
		layui.layer.confirm('确认生成该页面？', {
			btn: ['取消', '确定'],
			area: '420px',
			skin: 'element-ui-dialog-class',
			icon: 3,
			title: '提示'
		}, function (index) {
			layui.layer.close(index)
		}, function (index) {
			$.post('/home/zf/design/release', {
				page_id: $('#pageId').val(),
				lang: $('#pageLang').val()
			}, function (res) {
				layui.layer.msg(res.message)
				layui.layer.close(index)

				if (res.code === 0) {
					$('#page_link_template').html(
						'<a href="' + res.data.current.url + '" target="_blank" class="layui-btn layui-btn-normal">' + res.data.current.name + '</a>'
					)
				}
			})
		})
	})

	// 将首页发布状态存储到sessionStorage
	var geshop_home_page_status = typeof GESHOP_HOME_PAGE_STATUS !== 'undefined' ? GESHOP_HOME_PAGE_STATUS : 0
	sessionStorage.setItem('GESHOP_HOME_PAGE_STATUS', geshop_home_page_status)

	// 发布AB页面
	$('#release_page_AB').click(function(){
		// page_status [1,2,3] > [草稿，正在使用，已发布]
		var page_status = $('#pageStatus').val();
		var all_home_permissions = typeof GESHOP_HAS_ALL_HOME_PERMISSIONS !== 'undefined' ? GESHOP_HAS_ALL_HOME_PERMISSIONS : null;
		var isNewPipeline = $('#isNewPipeline').val();  // 0 否 1 是 新渠道
		if(isNewPipeline != '1' && page_status == '1' && all_home_permissions != '1'){
			layer.msg('首次发布需有全部渠道权限')
			return false;
		}
		layui.layer.open({
			type: 1,
			title: '发布页面',
			content: $('#ab_test').html(),
			btn: ['取消', '确定'],
			btnAlign: 'c',
			offset:'200px',
			area: '500px',
			success: function () {
				layui.form.render();
				release_AB_select_all();
				var status = sessionStorage.getItem('GESHOP_HOME_PAGE_STATUS')
				// B首页， A首页处理逻辑
				release_AB_home_type_control();
			},
			yes: function(index, layDom){
				layui.layer.close(index)
			},
			btn2: function(index, layDom){
				var indexType = parseInt($('input[name="indexType"]:checked').val());
				// home_type 0 A首页 1 B首页
				var home_type = indexType === 1 ? 0 : 1;
				var url = '/home/zf/design/release';
				// var url = indexType === 1 ? '/home/zf/design/release' : '/home/zf/design/release_b'
				var pageStatus = parseInt($('#pageStatus').val());
				if (pageStatus === 5) {
					layui.layer.msg('当前页面推送中，请等待推送完成后操作')
					return false
				}

				var pipelineArr = []
				$('#ab_test_pipeline_wraper').find('.pipeline_list .layui-form-checked').each(function() {
					var $this = $(this)
					var pipeline = $this.prev('input[type="checkbox"]').attr('name')
					pipelineArr.push(pipeline)
				})

				// 渠道一直可选
				if(pipelineArr.length == 0){
					layui.layer.msg('请先选择渠道信息');
					return false;
				}

				if(isNewPipeline != '1' && page_status == '1' && pipelineArr.length < $('#ab_test_pipeline_wraper .pipeClass').length){
					layui.layer.msg('首次发布，需要发布所有渠道');
					return false;
				}

				var params = {
					page_id: $('#pageId').val(),
					pipeline: pipelineArr.join(','),
					home_type: home_type
				}
				// 发布进行中
				if($('#isRollbackLock').val() != '1'){
					release_loading_show();
				}
				$.post(url, params, function(res){
					// layui.layer.close(index);
					layui.layer.closeAll();
					if (res.code === 0) {
						// 草稿处理
						if(pageStatus == '1'){
							window.GESHOP_IS_HOME_B = home_type == '0' ? '0' : '1';
							// 已发布
							$('#pageStatus').val(3);
						}
						release_loading_show(res.msg||'页面发布完成，请在【<span style="color:red;">线上预览</span>】中查看页面发布详情！');

						// 原逻辑
						if(res.data && res.data.current){
							$('#page_link_template').html(
								'<a href="' + res.data.current.url + '" target="_blank" class="layui-btn layui-btn-normal">' + res.data.current.name + '</a>'
							)
						}
					}else{
						layui.layer.msg(res.message);
					}
				}).fail(function(){
					layui.layer.closeAll();
					layui.layer.msg('发布失败,请稍后再试');
				})
			}
		})
	})

	/**
	 * 发布进行中
	 */
	function release_loading_show(msg){
		var html;
		if(msg){
			html = '<div style="padding:40px;text-align:center;">' +
				'<p>'+msg+'</p></div>';
		}else{
			html = '<div style="padding:40px;text-align:center;">' +
				'<p><i class="layui-icon layui-icon-upload" style="color:#32a0ff;font-size:50px;">&#xe67c;</i></p>' +
				'<p>发布进行中，稍后在“<span style="color:red;">线上预览</span>”查看线上页面效果！！！</p></div>';
		}
		layer.open({
			title:false,
			closeBtn:false,
			type: 1,
			content: html,
			offset:'200px',
			area: '500px',
			skin:'release_loading_content',
			success:function(layero,index){
				setTimeout(function(){
					layui.layer.close(index)
				},5000);
			}
		})
	}

	/**
	 * 发布，设为首页全选
	 */
	function release_AB_select_all(){
		// 全选
		layui.form.on('checkbox(release_check_all)', function (data) {
			var checked = data.elem.checked || false;
			$('.pipeClass').prop('checked',checked);
			layui.form.render('checkbox');
		});

		layui.form.on('checkbox(release_check_one)', function (data) {
			var item = $('.pipeClass');
			// 存在未选
			for(var i=0;i<item.length;i++){
				if(item[i].checked == false){
					$('input[name=release_check_all]').prop('checked',false);
					layui.form.render('checkbox');
					break;
				}
			}
			// 全选
			var allLength = item.length;
			for (var i = 0; i <item.length ; i++) {
				if(item[i].checked == true){
					allLength--;
				}
			}
			if(allLength == 0){
				$('input[name=release_check_all]').prop('checked',true);
			}
			layui.form.render('checkbox');
		});
	}

	/**
	 * 发布，设为首页全选，首页类型控制
	 */
	function release_AB_home_type_control(){
		var page_status = $('#pageStatus').val();
		var isNewPipeline = $('#isNewPipeline').val();
		if(page_status != '1' || isNewPipeline == '1'){
			if (typeof GESHOP_IS_HOME_B !== 'undefined' && GESHOP_IS_HOME_B == '1') {
				$('#ab_test_temp').find('input[value=1]').attr('disabled', true).parent().addClass('layui-hide');
				$('#ab_test_temp').find('input[value=2]').prop({'checked':true,'disabled':true});
			}else{
				$('#ab_test_temp').find('input[value=2]').attr('disabled', true).parent().addClass('layui-hide');
				$('#ab_test_temp').find('input[value=1]').prop({'checked':true,'disabled':true});
			}
		}
	}
	// 设为首页
	$('#generate_page_AB').click(function () {
		var page_status = $('#pageStatus').val();
		var all_home_permissions =  typeof GESHOP_HAS_ALL_HOME_PERMISSIONS !== 'undefined' ? GESHOP_HAS_ALL_HOME_PERMISSIONS : null;
		var isNewPipeline = $('#isNewPipeline').val();
		if(isNewPipeline != '1' && page_status == '1' && all_home_permissions != '1'){
			layer.msg('首次设为首页,需有全部渠道权限')
			return false;
		}
		layui.layer.open({
			type: 1,
			title: '设为首页类型',
			content: $('#ab_test').html(),
			btn: ['取消', '确定'],
			btnAlign: 'c',
			offset:'200px',
			area: '500px',
			success: function () {
				layui.form.render();
				release_AB_select_all();
				var status = sessionStorage.getItem('GESHOP_HOME_PAGE_STATUS');
				// B首页， A首页处理逻辑
				release_AB_home_type_control();
			},
			yes: function(index, layDom){
				layui.layer.close(index)
			},
			btn2: function(index, layDom){
				var indexType = parseInt($('input[name="indexType"]:checked').val());
				var home_type = indexType === 1 ? 0 : 1;
				/*var url = indexType === 1 ? '/home/zf/design/online' : '/home/zf/design/online-b'*/
				var url = '/home/zf/design/online';
				var pageStatus = parseInt($('#pageStatus').val())
				if (pageStatus === 5) {
					layui.layer.msg('当前页面推送中，请等待推送完成后操作')
					return false
				}

				var pipelineArr = []
				$('#ab_test_pipeline_wraper').find('.pipeline_list .layui-form-checked').each(function() {
					var $this = $(this)
					var pipeline = $this.prev('input[type="checkbox"]').attr('name')
					pipelineArr.push(pipeline)
				})

				var status = sessionStorage.getItem('GESHOP_HOME_PAGE_STATUS')
				if (status != 1 && status != 3 && pipelineArr.length == 0) {
					layui.layer.msg('请选择渠道')
					return false
				}

				// 渠道一直可选
				if(pipelineArr.length == 0){
					layui.layer.msg('请先选择渠道信息');
					return false;
				}

				var params = {
					page_id: $('#pageId').val(),
					pipeline:pipelineArr.join(','),
					home_type:home_type
				}

				/*                if (status != 1 && status != 3) {
                                    params.pipeline = pipelineArr.join(',')
                                }*/

				if(isNewPipeline != '1' && page_status == '1' && pipelineArr.length < $('#ab_test_pipeline_wraper .pipeClass').length){
					layui.layer.msg('首次设为首页，需要将所有渠道全部设为首页');
					return false;
				}

				$.post(url, params, function(res){
					layui.layer.msg(res.message)
					layui.layer.close(index)
					if (res.code === 0) {
						// 草稿处理
						if(pageStatus == '1'){
							window.GESHOP_IS_HOME_B = home_type == '0' ? '0' : '1';
							// 设为首页
							$('#pageStatus').val(2);
						}

						// 原逻辑
						if(res.data && res.data.current){
							$('#page_link_template').html(
								'<a href="' + res.data.current.url + '" target="_blank" class="layui-btn layui-btn-normal">' + res.data.current.name + '</a>'
							)
						}
					}
				}).fail(function(){
					layui.layer.closeAll();
					layui.layer.msg('设为首页失败,请稍后再试');
				})
			}
		})
	})


	// 首页管理 - 查看访问链接
	$('#view_page').click(function () {
		var status = $('#pageStatus').val()
		var url = ''


		// if (Number(status) == 8) {
		// 	url = '/home/zf/page/get-homeb-newest-urls'
		// } else {
		// 	url = '/home/zf/page/get-page-newest-urls'
		// }
		function layer_view_page(content){
			layui.layer.open({
				title: '访问链接',
				btn: ['取消', '确认'],
				skin: 'view-page-dialog-wrap',
				btnAlign: 'c',
				area: '800px',
				content: content
			})
		}

		$.get('/home/zf/page/pipeline-newest-urls', {
			id: $('#pageId').val()
			// id: 1065
		}, function (res) {
			Design.disableLoading()
			if (res.code == 0) {
				var html = []
				if (typeof res.data.pipeline_list != 'undefined' && res.data.pipeline_list.length > 0) {
					res.data.pipeline_list.forEach(function (element) {
						element.lang_list.forEach(function(langItem){
							html.push({
								name: element.name + '——' + langItem.lang_name,
								url: langItem.page_url
							})
						})

					})
				}
				if (html != '') {
					var htmlLang = ''
					var redistribution_btn = '<button class=\'layui-btn layui-btn-normal redistribution\'>重新发布</button>'

					$.each(html, function () {
						htmlLang += '<a href="' + this.url + '" class="layui-btn layui-btn-normal view-link" target="_blank">' + this.name + '</a>'
					})
					if (typeof res.data.tips != 'undefined') {
						var tips = res.data.tips
						layer_view_page(htmlLang + '<p style="margin-top:20px;"> ' + tips + ' &nbsp;&nbsp; ' + redistribution_btn + '</p>')
					} else {
						layer_view_page(htmlLang)
					}
				} else {
					layer_view_page(res.data.tips + '&nbsp;&nbsp;<button class=\'layui-btn layui-btn-normal redistribution\'>重新发布</button>')
				}
			} else {
				layer_view_page('页面未发布成功，请&nbsp;&nbsp;<button class=\'layui-btn layui-btn-normal redistribution\'>重新发布</button>')
			}
		})
	})

	// 首页 - 查看访问链接
	$('#advertisement_view_page').click(function () {
		$.get('/advertisement/page/get-page-newest-urls', {
			id: $('#pageId').val()
		}, function (res) {
			Design.disableLoading()
			if (res.code == 0) {
				var html = []
				if (res.data.list.length > 0) {
					res.data.list.forEach(function (element) {
						html.push({
							name: element.lang_name,
							url: element.page_url
						})
					})
				}
				if (html != '') {
					var htmlLang = ''
					var adRedistribution_btn = '<button class=\'layui-btn layui-btn-normal adRedistribution\'>重新发布</button>'
					$.each(html, function () {
						htmlLang += '<a href="' + this.url + '" class="layui-btn layui-btn-normal" target="_blank">' + this.name + '</a>'
					})
					if (res.data.tips != '') {
						var tips = res.data.tips
						layui.layer.open({
							title: '访问链接',
							btn: ['取消', '确认'],
							btnAlign: 'c',
							area: '640px',
							content: htmlLang + '<p style="margin-top:20px;"> ' + tips + ' &nbsp;&nbsp; ' + adRedistribution_btn + '</p>'
						})
					} else {
						layui.layer.open({
							title: '访问链接',
							btn: ['取消', '确认'],
							btnAlign: 'c',
							area: '640px',
							content: htmlLang
						})
					}
				} else {
					layui.layer.open({
						title: '访问链接',
						btn: ['取消', '确认'],
						btnAlign: 'c',
						area: '640px',
						content: res.data.tips + '&nbsp;&nbsp;<button class=\'layui-btn layui-btn-normal adRedistribution\'>重新发布</button>'
					})
				}
			} else {
				layui.layer.open({
					title: '访问链接',
					btn: ['取消', '确认'],
					btnAlign: 'c',
					area: '640px',
					content: '页面未发布成功，请&nbsp;&nbsp;<button class=\'layui-btn layui-btn-normal adRedistribution\'>重新发布</button>'
				})
			}
		})
	})

	// 首页 - 装修页发布
	$('#advertisement_generate_page').click(function () {
		layui.layer.confirm('确认生成该页面？', {
			btn: ['取消', '确定'],
			area: '420px',
			skin: 'element-ui-dialog-class',
			icon: 3,
			title: '提示'
		}, function (index) {
			layui.layer.close(index)
		}, function (index) {
			$.post('/advertisement/design/release', {
				page_id: $('#pageId').val(),
				lang: $('#pageLang').val()
			}, function (res) {
				layui.layer.msg(res.message)
				layui.layer.close(index)

				if (res.code === 0) {
					$('#page_link_template').html(
						'<a href="' + res.data.current.url + '" target="_blank" class="layui-btn layui-btn-normal">' + res.data.current.name + '</a>'
					)
				}
			})
		})
	})

	$('#view_to_design').click(function () {
		$(this).addClass('layui-btn-normal').removeClass('layui-btn-primary')
		$('#design_to_view').removeClass('layui-btn-normal').addClass('layui-btn-primary')
		$('.design-preview-hidden').removeClass('design-preview-hidden')
		$('.geshop-component-box').each(function () {
			$(this).removeClass('design-preview-box')
			$(this).find('.design-preview-content').remove()
		})
	})

	$('#design_to_view').click(function () {
		$(this).addClass('layui-btn-normal').removeClass('layui-btn-primary')
		$('#view_to_design').removeClass('layui-btn-normal').addClass('layui-btn-primary')
		Design.renderViewPage()
	})

	/**
	 * design page layui form
	 */
	layui.use('form', function () {
		var form = layui.form
		// 装修页布局切换
		form.on('switch(switchInput)', function (data) {
			if (data.elem.checked) {
				$('#design_to_view', iframeContentWindowDocument).trigger('click')
			} else {
				$('#view_to_design', iframeContentWindowDocument).trigger('click')
			}
		})
		// 装修页多语言切换（超过4种变成下拉框）
		form.on('select(selectLang)', function (data) {
			window.location.href = data.value
		})

		layui.element.on('tab(tab_channel_lang)', function (data) {
			var $elem = $(data.elem)
			$elem.find('.layui-tab-title').addClass('layui-tab-more_gb')

			setTimeout(function () {
				$elem.find('.layui-tab-title').addClass('layui-tab-more').removeClass('layui-tab-more_gb')
				$elem.find('.layui-tab-bar').attr('title', '收缩')

			}, 100)
		})

		// 添加页面样式 - 系统设置，自定义设置切换
		layui.form.on('radio(system-custom-settings)', function (data) {
			if (data.value == 1) {
				$(this).parents('.page-style-select-container').nextAll('.page-system-settings-container').show()
				$(this).parents('.page-style-select-container').nextAll('.page-custom-settings-container').hide()
			} else if (data.value == 2) {
				$(this).parents('.page-style-select-container').nextAll('.page-custom-settings-container').show()
				$(this).parents('.page-style-select-container').nextAll('.page-system-settings-container').hide()
			}
		})

	})

	var element = layui.element

	// 样式名称输入框失去焦点更改对应tab名称
	$('body').on('blur', '.page-stylesheet-list-container .class-name', function () {
		var value = $(this).val().trim()
		if (value) {
			var index = $(this).parents('.layui-tab-item').index()
			$('.page_style_option_bar .layui-tab-title li:eq(' + index + ')').text($(this).val())
			// 初始化tab
			layui.element.render('tab')
		}
	})

	// 添加页面样式 - 对齐方式切换
	$('body').on('click', '.page-position-repeat .page-background-position', function () {
		$(this).addClass('current').siblings().removeClass('current')
	})

	// 添加tab项
	$('body').on('click', '#js_addTab', function () {

		var tabLenght = $('.page_style_option_bar .layui-tab-title li').length
		if (tabLenght >= 7) {
			layui.layer.msg('最多只能添加6个自定义样式，别贪心哟', { time: 5000 })
			return false
		}

		addPageStyleIndex++

		var contentHtml = '<div class="layui-form-item">' +
			'<div class="layui-input-block layui-form-title">样式名称</div>' +
			'<div class="layui-input-block">' +
			'<input type="text" class="layui-input class-name" style="width:406px" name="" autocomplete="off" value="页面样式' + addPageStyleIndex + '">' +
			'</div>' +
			'</div>' +
			'<div class="layui-form-item" style="margin-top: 8px;">' +
			'<div class="layui-input-block layui-form-title">时间设置</div>' +
			'<div class="layui-input-block" style="position:relative;width:406px">' +
			'<span class="time-icon"></span><input type="text" class="layui-input start-time js_start_time" style="display:inline;width:203px;border-right:none;" name="" placeholder="开始时间" autocomplete="off" value=""><span class="time-split">至</span>' +
			'<input type="text" class="layui-input end-time js_end_time" style="display:inline;width:203px;border-left:none;margin-left:-1px" placeholder="结束时间" name="" autocomplete="off" value=""><span class="time-settings-tips">（注:时间段不要与其他页面样式出现交集哦）</span>' +
			'</div>' +
			'</div>' +
			'<div class="layui-form-item page-style-select-container" style="margin-top: 8px;">' +
			'<div class="layui-input-block layui-form-title">页面样式选择<span style="margin-left:10px;color:#9E9E9E;font-size:12px;">（注：当无任何切换的时间段时，页面样式按照通用样式设置进行展示）</span></div>' +
			'<div class="layui-input-block">' +
			'<input type="radio" name="page_style_select_' + addPageStyleIndex + '" lay-filter="system-custom-settings" value="1" title="系统设置" checked>' +
			'<input type="radio" name="page_style_select_' + addPageStyleIndex + '" lay-filter="system-custom-settings" value="2" title="自定义设置">' +
			'</div>' +
			'</div>' +
			'<div class="page-system-settings-container" style="margin-top: -4px;">' +
			'<div class="layui-form-item">' +
			'<div class="layui-input-block layui-form-title">整体页面背景颜色</div>' +
			'<div class="layui-input-block">' +
			'<div class="color-picker-selector" data-hidden-name="page_background_color">' +
			'<div></div>' +
			'</div>' +
			'<input type="text" class="layui-input background-color" style="width:406px" name="page_background_color" autocomplete="off" value="">' +
			'</div>' +
			'</div>' +
			'<div class="layui-form-item" style="margin-top: 4px;">' +
			'<div class="layui-input-block layui-form-title">整体页面背景图片</div>' +
			'<div class="layui-input-block">' +
			'<a href="javascript:;" class="js_openResource design-open-resource" data-type="tabBgImg">' +
			'<i class="layui-icon">&#xe64a;</i>' +
			'</a>' +
			'<input type="text" name="page_background_image" style="width:406px" autocomplete="off" class="layui-input background-image" value="">' +
			'</div>' +
			'</div>' +
			'<div class="layui-form-item page-background-repeat" style="margin-top: 8px;">' +
			'<div class="layui-input-block layui-form-title">平铺方式</div>' +
			'<div class="layui-input-block">' +
			'<input type="radio" name="page_background_repeat_' + addPageStyleIndex + '" value="no-repeat" title="不平铺">' +
			'<input type="radio" name="page_background_repeat_' + addPageStyleIndex + '" value="repeat" title="平铺" checked>' +
			'<input type="radio" name="page_background_repeat_' + addPageStyleIndex + '" value="repeat-x" title="横向平铺">' +
			'<input type="radio" name="page_background_repeat_' + addPageStyleIndex + '" value="repeat-y" title="纵向平铺">' +
			'</div>' +
			'</div>' +
			'<div class="layui-form-item page-position-repeat" style="margin-top: -4px;">' +
			'<div class="layui-input-block layui-form-title">对齐方式</div>' +
			'<div class="layui-input-block">' +
			'<a href="javascript:;" class="page-background-position" data-value="top"></a>' +
			'<a href="javascript:;" class="page-background-position" data-value="right"></a>' +
			'<a href="javascript:;" class="page-background-position" data-value="bottom"></a>' +
			'<a href="javascript:;" class="page-background-position" data-value="left"></a>' +
			'<a href="javascript:;" class="page-background-position current" data-value="center"></a>' +
			'<a href="javascript:;" class="page-background-position" data-value="top left"></a>' +
			'<a href="javascript:;" class="page-background-position" data-value="top right"></a>' +
			'<a href="javascript:;" class="page-background-position" data-value="buttom left"></a>' +
			'<a href="javascript:;" class="page-background-position" data-value="bottom right"></a>' +
			'</div>' +
			'</div>' +
			'</div>' +
			'<div class="page-custom-settings-container">' +
			'<div class="layui-form-item custom-settings-stylesheet">' +
			'<!-- <label class="layui-form-label">页面样式</label> -->' +
			'<div class="layui-input-block layui-form-title">页面样式</div>' +
			'<div class="layui-input-block">' +
			'<textarea name="page_custom_css" style="width:800px" placeholder="请输入样式代码" class="layui-textarea"></textarea>' +
			'</div>' +
			'</div>' +
			'</div>'

		//新增一个Tab项
		element.tabAdd('page-stylesheet-settings-bar', {
			title: '页面样式' + addPageStyleIndex
			, content: contentHtml
			, id: addPageStyleIndex
		})
		$(this).insertAfter($('.page_style_option_bar .layui-tab-title li:last-child'))
		layui.form.render('radio')

		// 初始化日期选择器
		Design.renderDatetime()
		// 初始化颜色选择器
		Design.renderColorPicker()

	})
	$('#design_view').on('mouseenter', Design.toClassName('lDrag'), function () {
		if ($('#switch_preview').prop('checked')) {
			return false
		}

		if ($(this).find('.design-operation-panel').length === 0) {
			$(this).append($('#layout_operation_template').html())

			if (!sessionStorage.getItem('copiedLayoutId')) {
				$('.js_pasteInLayout').hide()
			} else {
				$('.js_pasteInLayout').show()
			}

			if ($(this).find(Design.toClassName('cDrag')).length > 0 ||
					!sessionStorage.getItem('copiedComponentId')) {
				$('.js_pasteInEmptyLayout').hide()
			} else {
				$('.js_pasteInEmptyLayout').show()
			}

			$('#js_layoutName').text($('#design_drag [data-key=' + $(this).data('key') + ']').data('name'))
		}
	})
	$('#design_view').on('mouseleave', Design.toClassName('lDrag'), function () {
		$(this).find('.design-operation-panel').remove()
		$(Design.toClassName('cDrop') + ', ' + Design.toClassName('cDrag')).css({
			overflow: 'hidden'
		})
	})
	$('#design_view').on('mouseenter', Design.toClassName('cDrag'), function () {
		if ($('#switch_preview').prop('checked')) {
			return false
		}

		var target = $(this)
		var isUnique = Boolean(target.data('unique'))

		if (target.find('.design-operation-panel').length === 0) {
			target.append($('#component_operation_template').html())

			target.css({
				overflow: 'auto'
			})

			target.parent(Design.toClassName('cDrop')).css({
				overflow: 'auto'
			})

			if (isUnique) {
				target.find('.geshop-draggable, .js_sortUpComponent, .js_sortDownComponent, .js_copyComponent, .js_pasteInComponent').remove()
			}

			if (!sessionStorage.getItem('copiedComponentId')) {
				$('.js_pasteInComponent').hide()
			} else {
				$('.js_pasteInComponent').show()
			}

			$('#js_componentName').text($('#design_drag [data-key=' + target.data('key') + ']').data('name'))
		}

		var layoutDragTarget = target.closest(Design.toClassName('lDrag'))

		if (layoutDragTarget.find('> .design-operation-panel').length === 0) {
			layoutDragTarget.append($('#layout_operation_template').html())

			if (isUnique) {
				layoutDragTarget.find('.js_copyLayout, .js_pasteInEmptyLayout, .js_pasteInLayout').remove()
			}

			if (!sessionStorage.getItem('copiedLayoutId')) {
				$('.js_pasteInLayout').hide()
			} else {
				$('.js_pasteInLayout').show()
			}

			$('.js_pasteInEmptyLayout').hide()

			$('#js_layoutName').text($('#design_drag [data-key=' + layoutDragTarget.data('key') + ']').data('name'))
		}
	})
	$('#design_view').on('mouseleave', Design.toClassName('cDrag'), function () {
		$(this).find('.design-operation-panel').remove()
		$(Design.toClassName('cDrop') + ', ' + Design.toClassName('cDrag')).css({
			overflow: 'hidden'
		})
	})
	$('#design_view')
		.on('click', '.js_openLayoutForm', function () {
			var id = $(this).closest(Design.toClassName('lDrag')).attr('id')

			Design.enableLoading()
			Design.componentFormShow()
			$.get('/home/'+ Design.site_code +'/layout-design/get-layout-form', {
				page_id: $('#pageId').val(),
				id: id
			}, function (res) {
				Design.disableLoading()
				if (res.code === 0) {
					if ($('.design-form-layout').find('.geshop-form-content').length > 0) {
						$('.design-form-layout').find('.geshop-form-content').remove()
					}
					var formHtml = []
					var time = 0

					formHtml.push('<div class="geshop-form-content">')
					formHtml.push(res.data.component_html)
					formHtml.push('</div>')

					if ($('.design-form-layout').hasClass('design-form-visible')) {
						$('.design-form-layout').removeClass('design-form-visible')
						time = 300
					}
					setTimeout(function () {
						$(window.parent.document).find('#layout_form').prepend(formHtml.join(''))
						$(window.parent.document).find('.design-form-layout').addClass('design-form-visible')

						var form = layui.form
						var element = layui.element

						form.render()
						element.render()
						window.parent.window.Design.renderColorPicker()
						sessionStorage.setItem('currentLayoutId', id)
					}, time)
				} else {
					layui.layer.msg(res.message)
				}
			})
		})
		.on('click', '.js_openComponentForm', function () {
			var id = $(this).closest(Design.toClassName('cDrag')).data('id')

			sessionStorage.setItem('currentTemplateId', '')
			window.parent.window.Design.openComponentForm(id, true, true)
			sessionStorage.setItem('currentComponentId', id)
		})
		.on('click', '.js_addLayout', function () {
			var layoutTarget = $(this).closest(Design.toClassName('lDrag'))
			var prevId = layoutTarget.prev(Design.toClassName('lDrag')).length > 0 ? layoutTarget.prev(Design.toClassName('lDrag')).attr('id') : 0

			Design.openCustomLayoutDialog(layoutTarget, prevId, false)
			Design.renderLayoutTitle()
		})
		.on('click', '.js_removeLayout', function () {
			var layout = $(this).closest(Design.toClassName('lDrag'))

			layui.layer.confirm('确定删除该布局？', {
				btn: ['取消', '确认'],
				area: '420px',
				icon: 3,
				skin: 'element-ui-dialog-class'
			}, function (index) {
				layui.layer.close(index)
			}, function (index) {
				var url = '/home/'+ Design.site_code +'/layout-design/delete-layout'
				var id = layout.attr('id')
				var data = {
					page_id: $('#pageId').val(),
					id: id,
					lang: $('#pageLang').val()
				}
				var successCallBack = function (res) {
					Design.disableLoading()
					if (res.code === 0) {
						layout.remove()

						if (id == sessionStorage.getItem('currentLayoutId')) {
							$('#js_closeDesignForm').trigger('click')
						}

						if (id == sessionStorage.getItem('copiedLayoutId')) {
							sessionStorage.setItem('copiedLayoutId', '')
						}

						var components = layout.find(Design.toClassName('cDrag'))

						if (components.length > 0) {
							components.each(function () {
								if ($(this).data('id') == sessionStorage.getItem('copiedComponentId')) {
									sessionStorage.setItem('copiedComponentId', '')
								}
							})
						}

						layui.layer.close(index)
					}

					layui.layer.msg(res.message)
				}

				var errorCallBack = function (res) {
					Design.disableLoading()
					Design.disableLayuiLoading()
					layui.layer.msg(res.message)
				}

				Design.postAjax(url, data, successCallBack, errorCallBack)
			})
		})
		.on('click', '.js_sortUpLayout', function () {
			var target = $(this).closest(Design.toClassName('lDrag'))

			if (target.prevAll(Design.toClassName('lDrag')).length <= 0) {
				layui.layer.msg('布局已在最前面，无需移动！')

				return false
			}

			var url = '/home/'+ Design.site_code +'/layout-design/move-layout'
			var data = {
				page_id: $('#pageId').val(),
				id: target.attr('id'),
				prev_id: target.prevAll(Design.toClassName('lDrag')).length > 1 ? target.prevAll(Design.toClassName('lDrag')).get(1).id : 0,
				lang: $('#pageLang').val()
			}
			var successCallBack = function (res) {
				Design.disableLoading()
				if (res.code === 0) {
					$(target.prevAll(Design.toClassName('lDrag')).get(0)).before(target.get(0).outerHTML)
					target.remove()
				} else {
					layui.layer.msg(res.message)
				}
			}

			var errorCallBack = function (res) {
				Design.disableLoading()
				Design.disableLayuiLoading()
				layui.layer.msg(res.message)
			}

			Design.postAjax(url, data, successCallBack, errorCallBack)
		})
		.on('click', '.js_sortDownLayout', function () {
			var target = $(this).closest(Design.toClassName('lDrag'))

			if (target.nextAll(Design.toClassName('lDrag')).length <= 0) {
				layui.layer.msg('布局已在最后面，无需移动！')

				return false
			}

			var url = '/home/'+ Design.site_code +'/layout-design/move-layout'
			var data = {
				page_id: $('#pageId').val(),
				id: target.attr('id'),
				prev_id: target.nextAll(Design.toClassName('lDrag')).length > 0 ? target.nextAll(Design.toClassName('lDrag')).get(0).id : 0,
				lang: $('#pageLang').val()
			}
			var successCallBack = function (res) {
				Design.disableLoading()
				if (res.code === 0) {
					$(target.nextAll(Design.toClassName('lDrag')).get(0)).after(target.get(0).outerHTML)
					target.remove()
				} else {
					layui.layer.msg(res.message)
				}
			}

			var errorCallBack = function (res) {
				Design.disableLoading()
				Design.disableLayuiLoading()
				layui.layer.msg(res.message)
			}

			Design.postAjax(url, data, successCallBack, errorCallBack)
		})
		.on('click', '.js_removeComponent', function () {
			var component = $(this).closest(Design.toClassName('cDrag'))

			layui.layer.confirm('确认删除该组件？', {
				btn: ['取消', '确认'],
				area: '420px',
				icon: 3,
				skin: 'element-ui-dialog-class'
			}, function (index) {
				layui.layer.close(index)
			}, function (index) {
				var url = '/home/'+ Design.site_code +'/ui-design/delete-ui'
				var id = component.data('id')
				var data = {
					page_id: $('#pageId').val(),
					id: id,
					lang: $('#pageLang').val()
				}
				var successCallBack = function (res) {
					Design.disableLoading()
					if (res.code === 0) {
						component.remove()

						if (id == sessionStorage.getItem('copiedComponentId')) {
							sessionStorage.setItem('copiedComponentId', '')
						}

						if (id == sessionStorage.getItem('currentComponentId')) {
							$('#js_closeDesignForm').trigger('click')
						}

						layui.layer.close(index)
					}
					layui.layer.msg(res.message)
				}

				var errorCallBack = function (res) {
					Design.disableLoading()
					Design.disableLayuiLoading()
					layui.layer.msg(res.message)
				}

				Design.postAjax(url, data, successCallBack, errorCallBack)
			})
		})
		// 复制组件
		.on('click', '.js_copyComponent', function () {
			var id = $(this).closest(Design.toClassName('cDrag')).data('id')

			sessionStorage.setItem('copiedComponentId', id)
			layui.layer.msg('组件已复制！')
		})
		// 复制布局
		.on('click', '.js_copyLayout', function () {
			var id = $(this).closest(Design.toClassName('lDrag')).attr('id')

			sessionStorage.setItem('copiedLayoutId', id)
			layui.layer.msg('布局已复制！')
		})
		// 粘贴内容到空布局
		.on('click', '.js_pasteInEmptyLayout', function () {
			var componentTarget = $(this).closest(Design.toClassName('lDrag'))
			var url = '/home/'+ Design.site_code +'/ui-design/copy-ui'
			var data = {
				page_id: $('#pageId').val(),
				layout_id: $(this).closest(Design.toClassName('lDrag')).attr('id'),
				position: 1,
				lang: $('#pageLang').val(),
				copy_id: sessionStorage.getItem('copiedComponentId'),
				prev_id: 0,
				num: 1
			}

			var successCallBack = function (res) {
				Design.disableLoading()
				if (res.code === 0) {
					componentTarget.find('[data-position=1] ' + Design.toClassName('cDrop')).html(res.data.component_html)
					Design.renderViewPage()
					layui.layer.msg('组件已粘贴！')
				} else {
					layui.layer.msg(res.message)
				}
			}

			var errorCallBack = function (res) {
				Design.disableLoading()
				Design.disableLayuiLoading()
				layui.layer.msg(res.message)
			}

			Design.postAjax(url, data, successCallBack, errorCallBack)
		})
		// 布局粘贴
		.on('click', '.js_pasteInLayout', function () {
			var layoutTarget = $(this).closest(Design.toClassName('lDrag'))

			layui.layer.confirm('请输入复制份数', {
				content: $('#copy_layout_template').html(),
				btn: ['取消', '确定']
			}, function (index) {
				layui.layer.close(index)
			}, function () {
				var copyNum = $('#copyNum').val()
				var url = '/home/'+ Design.site_code +'/layout-design/copy-layout'
				var data = {
					page_id: $('#pageId').val(),
					lang: $('#pageLang').val(),
					copy_id: sessionStorage.getItem('copiedLayoutId'),
					prev_id: layoutTarget.attr('id'),
					num: copyNum
				}

				var successCallBack = function (res) {
					Design.disableLoading()
					if (res.code === 0) {
						layoutTarget.after(res.data.component_html)
						Design.renderViewPage()
						Design.renderLayoutTitle()
						layui.layer.msg('布局已粘贴！')
					} else {
						layui.layer.msg(res.message)
					}
				}
				var errorCallBack = function (res) {
					Design.disableLoading()
					Design.disableLayuiLoading()
					layui.layer.msg(res.message)
				}
				Design.postAjax(url, data, successCallBack, errorCallBack)
			})
		})
		// 组件粘贴
		.on('click', '.js_pasteInComponent', function () {
			var componentTarget = $(this).closest(Design.toClassName('cDrag'))
			var layout_id = $(this).closest(Design.toClassName('lDrag')).attr('id')
			var position = $(this).closest('[data-position]').data('position')

			layui.layer.confirm('请输入复制份数', {
				content: $('#copy_layout_template').html(),
				btn: ['取消', '确定']
			}, function (index) {
				layui.layer.close(index)
			}, function () {
				var copyNum = $('#copyNum').val()
				var url = '/home/'+ Design.site_code +'/ui-design/copy-ui'
				var data = {
					page_id: $('#pageId').val(),
					layout_id: layout_id,
					position: position,
					lang: $('#pageLang').val(),
					copy_id: sessionStorage.getItem('copiedComponentId'),
					num: copyNum,
					prev_id: componentTarget.data('id')
				}
				var successCallBack = function (res) {
					Design.disableLoading()
					if (res.code === 0) {
						componentTarget.after(res.data.component_html)
						Design.renderViewPage()
						layui.layer.msg('组件已粘贴！')
					} else {
						layui.layer.msg(res.message)
					}
				}

				var errorCallBack = function (res) {
					Design.disableLoading()
					Design.disableLayuiLoading()
					layui.layer.msg(res.message)
				}

				Design.postAjax(url, data, successCallBack, errorCallBack)
			})
		})
		.on('click', '.js_sortUpComponent', function () {
			var target = $(this).closest(Design.toClassName('cDrag'))

			if (target.prevAll(Design.toClassName('cDrag')).length <= 0) {
				layui.layer.msg('组件已在最前面，无需移动！')

				return false
			}

			var url = '/home/'+ Design.site_code +'/ui-design/move-ui'
			var data = {
				page_id: $('#pageId').val(),
				id: target.data('id'),
				prev_id: target.prevAll(Design.toClassName('cDrag')).length > 1 ? target.prevAll(Design.toClassName('cDrag')).get(1).getAttribute('data-id') : 0,
				layout_id: target.closest(Design.toClassName('lDrag')).attr('id'),
				position: target.closest('[data-position]').data('position'),
				lang: $('#pageLang').val()
			}

			var successCallBack = function (res) {
				Design.disableLoading()
				if (res.code === 0) {
					$(target.prevAll(Design.toClassName('cDrag')).get(0)).before(target.get(0).outerHTML)
					target.remove()
				} else {
					layui.layer.msg(res.message)
				}
			}

			var errorCallBack = function (res) {
				Design.disableLoading()
				Design.disableLayuiLoading()
				layui.layer.msg(res.message)
			}

			Design.postAjax(url, data, successCallBack, errorCallBack)
		})
		.on('click', '.js_sortDownComponent', function () {
			var target = $(this).closest(Design.toClassName('cDrag'))

			if (target.nextAll(Design.toClassName('cDrag')).length <= 0) {
				layui.layer.msg('组件已在最后面，无需移动！')

				return false
			}

			var url = '/home/'+ Design.site_code +'/ui-design/move-ui'
			var data = {
				page_id: $('#pageId').val(),
				id: target.data('id'),
				prev_id: target.nextAll(Design.toClassName('cDrag')).length > 0 ? target.nextAll(Design.toClassName('cDrag')).get(0).getAttribute('data-id') : 0,
				layout_id: target.closest(Design.toClassName('lDrag')).attr('id'),
				position: target.closest('[data-position]').data('position'),
				lang: $('#pageLang').val()
			}

			var successCallBack = function (res) {
				Design.disableLoading()
				if (res.code === 0) {
					$(target.nextAll(Design.toClassName('cDrag')).get(0)).after(target.get(0).outerHTML)
					target.remove()
				} else {
					layui.layer.msg(res.message)
				}
			}

			var errorCallBack = function (res) {
				Design.disableLoading()
				Design.disableLayuiLoading()
				layui.layer.msg(res.message)
			}

			Design.postAjax(url, data, successCallBack, errorCallBack)
		})
		.on('mouseenter', '.component-drag', function () {
			$(this).append('<a href="javascript:;" class="component-drag-mask js_maskOpenComponentForm"></a>')
		})
		.on('mouseleave', '.component-drag-mask', function () {
			$('.component-drag-mask').remove()
		})
		.on('click', '.js_maskOpenComponentForm', function () {
			var id = $(this).closest(Design.toClassName('cDrag')).data('id'),
				currentId = sessionStorage.getItem('currentComponentId')

			if (id == currentId && $('.design-form-component').hasClass('design-form-visible')) {
				$('.design-form-component').removeClass('design-form-visible')
			} else {
				sessionStorage.setItem('currentTemplateId', '')
				window.parent.window.Design.openComponentForm(id, true)
				sessionStorage.setItem('currentComponentId', id)
			}
		})
		// 保存组件模板
		.on('click', '.js_saveComponentTemp', function () {
			var ui_id = $(this).closest(Design.toClassName('cDrag')).data('id')
			var isMobile = $('#design_view').width() === 385 || $('#design_view').width() === 375
			layui.layer.open({
				title: '新增组件模板',
				btn: ['取消', '确认'],
				type: '1',
				// area: ['720px', '550px'],
				area: isMobile ? '320px' : '720px',
				btnAlign: 'c',
				content: $('#component_model_template').html(),
				success: function () {
					layui.form.render()
					layui.upload.render({
						elem: '#upload_model_picture',
						field: 'files',
						url: '/home/'+ Design.site_code +'/page-tpl/upload-pic',
						accept: 'images',
						exts: 'jpg|png|gif|bmp|jpeg|wepb',
						size: 3 * 1024,
						before: function () {
							Design.enableLayuiLoading()
						},
						done: function (res) {
							Design.disableLayuiLoading()
							if (res.code === 0) {
								var preImg = res.data.url
								$('#upload_model_resource').html('上传成功，' + preImg);
								$('[name=model_pic]').val(preImg)
								// $('#upload_model_picture').css('background-image', 'url(' + preImg + ')')
								// $('#upload_model_picture>i').css('opacity', '0')
								// $('#upload_model_picture>p').css('opacity', '0')
							} else {
								layui.layer.msg(res.message)
							}
						},
						error: function () {
							Design.disableLayuiLoading()
							layui.layer.msg('接口错误！')
						}
					})
				},
				yes: function (index) {
					layui.layer.close(index)
				},
				btn2: function (index) {
					var url = '/home/'+ Design.site_code +'/page-ui-tpl/add'
					var data = {
						page_id: $('#pageId').val(),
						ui_id: ui_id,
						name: $('[name=cmp_model_name]').val(),
						pic_url: $('[name=model_pic]').val(),
						view_type: $('select[name=model_type]').val()// 1 共有 2私有
					}

					if (data.name == '') {
						layui.layer.msg('模板名称不能为空')
						return false
					} else if (data.name.length > 50) {
						layui.layer.msg('名称不能超过50个字符')
						return false
					}

					var successCallBack = function (res) {
						Design.disableLoading()
						if (res.code == 0) {
							layui.layer.msg('组件模板生成成功')
							layui.layer.close(index)
						} else {
							layui.layer.msg(res.message)
						}
					}

					var errorCallBack = function (res) {
						Design.disableLoading()
						Design.disableLayuiLoading()
						layui.layer.msg(res.message)
					}

					Design.postAjax(url, data, successCallBack, errorCallBack)

					// 禁用默认关闭弹窗 使用layer.close关闭
					return false
				},
				cancel: function (index) {
					layui.layer.close(index)
				}
			})

			/*
				获取模板快照
				修改日志：
				2019-03-14: 不再请求这个快照接口，By Cullen
			*/
			return false;
			Design.postAjax('/home/'+ Design.site_code +'/page-ui-tpl/get-snapshot', {
				ui_id: ui_id,
				page_id: $('#pageId').val()
			}, function (res) {
				Design.disableLoading()
				Design.disableLayuiLoading()
				if(res.code === 0){
					$('.preview-img img').attr('src',res.data).css({'width': '180px',	'height': 'auto'})
					$('input[name=model_pic]').val(res.data)
				}
			}, function (res) {
				Design.disableLoading()
				Design.disableLayuiLoading()
				layui.layer.msg(res.message)
				Design.disableLoading()
			})
		})

	$('.design-form')
		.on('submit', '#layout_form', function (event) {
			event.preventDefault()
		})
		.on('submit', '#component_form', function (event) {
			event.preventDefault()
		})
		.on('click', '.js_closeDesignForm', function () {
			$('.design-form').removeClass('design-form-visible')
			$('.design-form').find('.geshop-form-content').remove()
			sessionStorage.removeItem('imgHeight')
			sessionStorage.removeItem('imgWidth')
			Design.componentFormHide()
		})
		.on('click', '#layout_form .js_submitDesignForm', function () {
			sessionStorage.removeItem('imgHeight')
			sessionStorage.removeItem('imgWidth')
			var url = '/home/'+ Design.site_code +'/layout-design/save-layout-form'
			var data = {
				page_id: $('#pageId').val(),
				id: sessionStorage.getItem('currentLayoutId')
			}
			var successCallBack = function (res) {
				Design.disableLoading()
				if (res.code === 0) {
					var LayoutTarget = $(Design.toClassName('lDrag') + '[id=' + sessionStorage.getItem('currentLayoutId') + ']', iframeContentWindowDocument)

					LayoutTarget.after(res.data.component_html)
					LayoutTarget.remove()
					// 保存成功后关闭蒙层
					$('.js_closeDesignForm').trigger('click')
				}

				layui.layer.msg(res.message)
			}

			$('.design-form').find('input').each(function (index, element) {
				data[element.name] = element.value
			})

			var errorCallBack = function (res) {
				Design.disableLoading()
				Design.disableLayuiLoading()
				layui.layer.msg(res.message)
			}

			Design.postAjax(url, data, successCallBack, errorCallBack)
		})
		.on('click', '#component_form .js_submitDesignForm', function () {
			var url = '/home/'+ Design.site_code +'/ui-design/save-form'
			var data = {
				page_id: $('#pageId').val(),
				id: sessionStorage.getItem('currentComponentId'),
                lang: $('#pageLang').val(),
                is_vue_ssr: $('.design-form [name="tpl_id"]:checked').attr('data-is-ssr')
			}
			var successCallBack = function (res) {
				Design.disableLoading()
				if (res.code === 0) {
					var componentTarget = $(Design.toClassName('cDrag') + '[data-id=' + sessionStorage.getItem('currentComponentId') + ']', iframeContentWindowDocument)
					var flag = false

					componentTarget.after(res.data.component_html)
					componentTarget.remove()

					if (res.data.tpl_id != sessionStorage.getItem('currentTemplateId')) {
						flag = true
					}
					Design.openComponentForm(sessionStorage.getItem('currentComponentId'), flag, false)
					// 保存成功后关闭蒙层
					$('.js_closeDesignForm').trigger('click')
				}
				layui.layer.msg(res.message)
			}

			var errorCallBack = function (res) {
				Design.disableLoading()
				Design.disableLayuiLoading()
				layui.layer.msg(res.message)
			}

			var formData = Design.getComponentFormData()

			data.tpl_id = formData.tpl_id

			if (data.tpl_id != sessionStorage.getItem('currentTemplateId')) {
				data.private_data = '{}'
			} else {
				data.private_data = JSON.stringify(formData.private)
			}

			data.public_data = JSON.stringify(formData.public)
			Design.postAjax(url, data, successCallBack, errorCallBack)
		})

	// 组件表单编辑蒙层点击关闭
	$('#component_form_dialog').click(function () {
		$('.js_closeDesignForm').trigger('click')
	})

	// 组件布局操作栏按钮
	$('.design-view').on('mouseenter', '.design-operation-panel>a', function () {
		$(this).children('span').show()
	})

	$('.design-view').on('mouseleave', '.design-operation-panel>a', function () {
		$(this).children('span').hide()
	})

	$(document).on('click', '.js_openResource', function () {
		// 装修页添加页面样式，每个tab都可设置背景图片，currentTabContentIndex为当前是第几个tab项
		if ($(this).data('type') === 'tabBgImg') {
			sessionStorage.setItem('currentTabContentIndex', $(this).parents('.layui-tab-item').index())
			sessionStorage.setItem('currentTabContentType', 'tabBgImg')
		}

		sessionStorage.setItem('currentResourceId', 0)
		resourceTree = []
		sessionStorage.setItem('currentInputTargetName', $(this).next('input').attr('name'))

		layui.layer.open({
			maxmin: true,
			type: 1,
			title: '素材管理器',
			area: ['920px', '600px'],
			content: $('#resource_view_template').html(),
			success: function (layero, index) {
				Design.initializeResourceDialog(index)
			},
			cancel: function () {
				sessionStorage.setItem('currentResourceId', 0)
			}
		})
	})

	$('body')
		.on('click', '.js_useResource', function () {
			var currentTabContentType = sessionStorage.getItem('currentTabContentType')
			var currentTabContentIndex = sessionStorage.getItem('currentTabContentIndex')

			var targetHeight = sessionStorage.getItem('imgHeight'),
				targetWidth = sessionStorage.getItem('imgWidth')
			var currentHeight = $(this).data('height'),
				currentWidth = $(this).data('width')
			var isSelect = targetHeight && targetWidth ? true : false
			var isRight = parseInt(targetHeight) == currentHeight && parseInt(targetWidth) == currentWidth
			var isContrast = isSelect && isContrast
			var target, index

			if (currentTabContentType == 'tabBgImg') {
				target = $('.page-stylesheet-list-container .layui-tab-item:eq(' + currentTabContentIndex + ')').find('input[name=' + sessionStorage.getItem('currentInputTargetName') + ']').val($(this).data('url'))
				sessionStorage.removeItem('currentTabContentType')
				sessionStorage.removeItem('currentTabContentIndex')
				// 添加页面样式 - 选择背景图片后显示对齐方式
				$('.page-stylesheet-list-container .layui-tab-item:eq(' + currentTabContentIndex + ')').find('.page-background-repeat').show()
				$('.page-stylesheet-list-container .layui-tab-item:eq(' + currentTabContentIndex + ')').find('.page-position-repeat').show()
			} else {
				if (isSelect && !isRight) {
					layui.layer.msg('请选择合适大小的图片！')

					return false
				} else {
					target = $('[name=' + sessionStorage.getItem('currentInputTargetName') + ']')
					index = $(this).closest('.layui-layer-page').attr('times')

					if ($('[name=' + target.data('width') + ']').length > 0) {
						$('[name=' + target.data('width') + ']').val($(this).data('width'))
					}
					if ($('[name=' + target.data('height') + ']').length > 0) {
						$('[name=' + target.data('height') + ']').val($(this).data('height'))
					}

					sessionStorage.removeItem('imgHeight')
					sessionStorage.removeItem('imgWidth')
					layui.layer.close(index)
				}
			}

			if ((target.data('validWidth') && $('[name=' + target.data('validWidth') + ']').val() != $(this).data('width')) ||
				(target.data('validHeight') && $('[name=' + target.data('validHeight') + ']').val() != $(this).data('height'))) {
				layui.layer.msg('请选择合适大小的图片！')

				return false
			}

			target.val($(this).data('url')).attr({'img-width':$(this).data('width'),'img-height':$(this).data('height')})
			if ($('[name=' + target.data('width') + ']').length > 0) {
				$('[name=' + target.data('width') + ']').val($(this).data('width'))
			}
			if ($('[name=' + target.data('height') + ']').length > 0) {
				$('[name=' + target.data('height') + ']').val($(this).data('height'))
			}
			layui.layer.close(index)
		})
		.on('click', '#create_resource_folder', function () {
			$('#resource_folder_box').show()
		})
		.on('change', '#resource_folder_input', function () {
			var input = $(this)

			if ($.trim(input.val()).length <= 0) {
				layui.layer.msg('请输入文件夹名称！')

				return false
			}

			Design.enableLoading()

			var url = '/admin/resource/add'
			var data = {
				name: input.val(),
				type: 0,
				url: '',
				parent_id: sessionStorage.getItem('currentResourceId') || 0
			}
			var successCallBack = function (res) {
				Design.disableLoading()
				if (res.code === 0) {
					$('#resource_folder_input').val('')
					$('#resource_folder_box').hide()
					resourceTree.push({
						id: res.data.id,
						parent: res.data.parent_id == 0 ? '#' : res.data.parent_id,
						text: res.data.name
					})
					Design.renderResourceTree()
					Design.renderResourceExploere(sessionStorage.getItem('currentResourceId') || 0, '/admin/resource/list')
				} else {
					layui.layer.msg(res.message)
				}
			}

			var errorCallBack = function (res) {
				Design.disableLoading()
				Design.disableLayuiLoading()
				layui.layer.msg(res.message)
			}

			Design.postAjax(url, data, successCallBack, errorCallBack)
		})
		.on('click', '#upload_resource_replace', function () {
			if (!sessionStorage.getItem('currentResourceId') ||
				Number(sessionStorage.getItem('currentResourceId')) == 0) {
				$('#empty_tip').show()
			} else {
				$('#upload_resource').trigger('click')
			}
		})
		.on('click', '#switch_to_tabletop', function () {
			$('#design_view').stop().animate({
				width: 1378
			}, 500)
			window.sessionStorage.setItem('gs_platform', 'pc')
			$(this).addClass('layui-btn-normal').removeClass('layui-btn-primary')
			$(this).siblings().removeClass('layui-btn-normal').addClass('layui-btn-primary')
		})
		.on('click', '#switch_to_tablet', function () {
			$('#design_view').stop().animate({
				width: 972
			}, 500)
			window.sessionStorage.setItem('gs_platform', 'pad')
			$(this).addClass('layui-btn-normal').removeClass('layui-btn-primary')
			$(this).siblings().removeClass('layui-btn-normal').addClass('layui-btn-primary')
		})
		.on('click', '#switch_to_mobile', function () {
			$('#design_view').stop().animate({
				width: 387
			}, 500)
			window.sessionStorage.setItem('gs_platform', 'wap')
			$(this).addClass('layui-btn-normal').removeClass('layui-btn-primary')
			$(this).siblings().removeClass('layui-btn-normal').addClass('layui-btn-primary')
		})

	// data-ctype - cTmp：组件模板
	document.addEventListener('dragstart', function (event) {
		event.dataTransfer.setData('text/plain', 'for firefox')
		if (typeof event.target.getAttribute('data-key') === 'string') {
			var crt = event.target.cloneNode(true)
			crt.style.backgroundColor = '#FFFFFF'
			crt.style.boxShadow = '0px 0px 10px 0px rgba(155,155,155,0.5)'
			crt.firstElementChild.style.color = '#000'
			crt.style.width = '128px'
			var height = event.target.getAttribute('data-ctype') === 'cTmp' ? '148px' : '80px'
			crt.style.height = height
			crt.style.padding = '10px'
			crt.style.boxSizing = 'border-box'
			crt.style.position = 'absolute'
			crt.style.top = '-200px'
			crt.style.zIndex = '-100'
			document.body.appendChild(crt)
			var top = event.target.getAttribute('data-ctype') === 'cTmp' ? 74 : 40
			event.dataTransfer.setDragImage(crt, 64, top)
		} else {
			var img = new Image()
			img.src = '/resources/images/drag-image.png'
			event.dataTransfer.setDragImage(img, 60, 45)
		}

		if (event.target.hasAttribute('data-type')) {
			var type = event.target.getAttribute('data-type')

			if (type === 'layout') {
				Design.set('gTarget', $(event.target).closest(Design.toClassName('lDrag')).get(0))
			} else if (type === 'component') {
				Design.set('gTarget', $(event.target).closest(Design.toClassName('cDrag')).get(0))
			}
		} else {
			Design.set('gTarget', event.target)
		}
	}, false)

	document.addEventListener('drag', function () {

	}, false)

	document.addEventListener('dragend', function (event) {
		if (event.dataTransfer.dropEffect == 'none') {
			Design.removeTip()
			Design.hideDropTip()
		}
	}, false)

	document.addEventListener('dragenter', function () {

	}, false)

	// 组件或布局添加位置提示
	var $tips = $('#component_layout_add_position_tips').clone()

	// 当前鼠标位置
	var pos = {}

	$('#design_view_iframe').on('load', function() {
		$('#design_view_iframe').css('height', $('#design_view').height())
		iframeContentWindowDocument = $('#design_view_iframe').get(0).contentWindow.document
		Design.documentObject = iframeContentWindowDocument

		iframeContentWindowDocument.addEventListener('dragover', handleDragoverEvent, false)

		iframeContentWindowDocument.addEventListener('dragleave', function () {
			// display block layout title
			Design.layoutTitleOpacity()
		}, false)

		iframeContentWindowDocument.addEventListener('drop', function (event) {
			event.preventDefault()
			var target = null

			if (Design.get('gTarget').className.indexOf(Design.get('lDrag')) !== -1) {
				if ($(event.target).closest(Design.toClassName('lDrag')).length > 0) {
					target = $(event.target).closest(Design.toClassName('lDrag')).get(0)
				} else {
					target = $(event.target).closest(Design.toClassName('lDrop')).get(0)
				}
			} else if (Design.get('gTarget').className.indexOf(Design.get('cDrag')) !== -1) {
				if ($(event.target).closest(Design.toClassName('cDrag')).length > 0) {
					target = $(event.target).closest(Design.toClassName('cDrag')).get(0)
				} else {
					target = $(event.target).closest(Design.toClassName('cDrop')).get(0)
				}
			}

			if (!target) {
				target = null
			}

			Design.set('pTarget', target)
			Design.drop()
			Design.hideDropTip()
			Design.layoutTitleOpacity()
		}, false)
	})

    function handleDragoverEvent(event){
        event.preventDefault()
        event.stopPropagation()

        /* 处理多余的dom */
        var lastMousePageY = dragInfo.lastMousePageY;
        dragInfo.lastMousePageY = parseInt(event.pageY);
        var designTips = $('#component_layout_add_position_tips',Design.documentObject);
        if((designTips && lastMousePageY === event.pageY) || !!dragInfo.dragStatus || !!!lastMousePageY && Math.abs(lastMousePageY-event.pageY)<=200){
            return false;
        }else{
            dragInfo.dragStatus = 1;
            setTimeout(function(){
                dragInfo.dragStatus = 0;
            },50)
        }

        if ($(event.target).attr('id') === 'component_layout_add_position_tips') {
            $('.geshop-drop-tip-center-right').hide()
        }

        pos.y = event.pageY
        if ($(event.target).closest('.design-view').length <= 0) {
            Design.removeTip()
            Design.hideDropTip()
            return false
        }
        var message, type, target

        if (Design.get('gTarget').className.indexOf(Design.get('lDrag')) !== -1) {
            if ($(event.target).closest(Design.toClassName('lDrag')).length > 0) {
                target = $(event.target).closest(Design.toClassName('lDrag'))

                // 计算是在上半区域还是下半区域
                var layoutCenterY = target.offset().top + target.outerHeight() * 0.5

                if (pos.y <= layoutCenterY) {
                    $tips.text('释放之后，此布局将增加在此').show().insertBefore(target)
                    sessionStorage.layout_position = 0	// 前置
                } else {
                    $tips.text('释放之后，此布局将增加在此').show().insertAfter(target)
                    sessionStorage.layout_position = 1	// 后置
                }

                message = ''
                type = 'right'
                Design.removeBackGroundColor()

            } else if ($(event.target).closest(Design.toClassName('lDrop')).length > 0) {
                target = $(event.target).closest(Design.toClassName('lDrop'))
                if (target.find(Design.toClassName('lDrag')).length > 0) {
                    // 判断当前tip所在位置的后面是否有布局，没有布局才会将当前tip移到所有布局的后面
                    var $tips_len = $tips.next('.geshop-layout-box').length
                    if ($tips_len == 0) {
                        $tips.text('释放之后，此布局将增加在此').show().insertAfter(target.find('.geshop-layout-box').last())
                    }
                    message = ''
                    Design.removeBackGroundColor()
                } else {
                    sessionStorage.layout_position = 2 // 当前无布局
                    message = '释放之后，新布局将添加到此页面'
                    Design.addBackGroundColor()
                }
                type = 'right'
            } else {
                target = $(event.target)
                message = '此位置不能存放新布局'
                type = 'wrong'
            }

            Design.showDropTip(message, target, type)

        } else if (Design.get('gTarget').className.indexOf(Design.get('cDrag')) !== -1) {

            if ($(event.target).closest(Design.toClassName('cDrag')).length > 0) {
                target = $(event.target).closest(Design.toClassName('cDrag'))

                // 计算是在上半区域还是下半区域
                var componentCenterY = target.offset().top + target.outerHeight() * 0.5

                if (pos.y <= componentCenterY) {
                    $tips.text('释放之后，此组件将增加在此').show().insertBefore(target)
                    sessionStorage.position = 0	// 前置
                } else {
                    $tips.text('释放之后，此组件将增加在此').show().insertAfter(target)
                    sessionStorage.position = 1	// 后置
                }
                message = ''
                type = 'right'
                Design.removeBackGroundColor()

            } else if ($(event.target).closest(Design.toClassName('cDrop')).length > 0) {

                target = $(event.target).closest(Design.toClassName('cDrop'))
                if (target.find(Design.toClassName('cDrag')).length > 0) {

                    message = ''
                    Design.removeBackGroundColor()

                } else {
                    sessionStorage.position = 2 // 当前布局无组件
                    message = '释放之后，新组件将添加到此布局'

                    Design.addBackGroundColor()
                    Design.removeTip()
                    // display none layout title
                    $(target).children('.design-preview-layout-title').css({ opacity: 0 })
                }
                type = 'right'
            } else {
                target = $(event.target)
                message = '此位置不能存放新组件'
                type = 'wrong'

                $(target).find('.design-preview-layout-title').css({ opacity: 0 })
                Design.removeTip()
            }

            Design.showDropTip(message, target, type)

        }
        event.preventDefault()
    }
})
