var $ = window.$
var layui = window.layui
var resourceTree = []
var resourceList = ''
var Cookies = window.Cookies
// var url_prefix = getCookie('site_group_code') === 'gb' ? '/activity/gb' : '/activity'
var url_prefix = '/activity/gb'

/* getCookie */
function getCookie (name) {
	var arr,
		reg = new RegExp('(^| )' + name + '=([^;]*)(;|$)')

	if (arr = document.cookie.match(reg)) {
		return arr[2]
	} else {
		return null
	}
}
/**
 * getComponentFormData
 */
function getComponentFormData (target) {
	var publicData = {}, privateData = {}, tpl_id
	var $target = target?$(target):$('.design-form');
	$target.find('input,select,textarea').each(function (index, element) {
		var nodeType = element.type.toLowerCase()
		var name = element.name
		var val = element.value
		var isPublic = Boolean(element.getAttribute('data-public-tag'))

		if (!$(this).hasClass('Unwanted')) {
			if (name == 'tpl_id') {
				if (element.checked) {
					tpl_id = val
				}
			} else if (nodeType === 'checkbox') {
				if (name.length > 0) {
					if (isPublic) {
						if (!Array.isArray(publicData[name])) {
							publicData[name] = []
						}
						if (element.checked) {
							publicData[name].push(val)
						}
					} else {
						if (!Array.isArray(privateData[name])) {
							privateData[name] = []
						}
						if (element.checked) {
							privateData[name].push(val)
						}
					}
				}
			} else if (nodeType == 'radio') {
				if (element.checked) {
					if (isPublic) {
						publicData[name] = val
					} else {
						privateData[name] = val
					}
				}
			} else {
				if (isPublic) {
					publicData[name] = val
				} else {
					privateData[name] = val
				}
			}
		}
	})

	return {
		tpl_id: tpl_id,
		public: publicData,
		private: privateData
	}
}

/**
 * renderCustomLayout
 */
function renderCustomLayout () {
	var width = $('[type="radio"][name="width"]:checked').val()
	var column = Number($('[type="radio"][name="column"]:checked').val())
	var customColumn = 0

	if (Number(width) === 0) {
		width = $('[name="customWidth"]').val()
	}

	if (width !== '100%' && !Number(width)) {
		layui.layer.msg('请设置布局宽度值(范围960-1920px)')
	}

	if (Number(column) === 0) {
		customColumn = $('[name=customColumn]').val()
	} else {
		customColumn = column
	}

	if (!Number(customColumn)) {
		layui.layer.msg('请设置列的数量(范围1-9列)')
	}

	if (column === 0 &&
		Number(customColumn) &&
		Number($('[type="radio"][name="type"]:checked').val()) === 0) {
		var html = ''

		for (var index = 0; index < customColumn; index++) {
			html += '<input type="text" name="customColumnWidth' + (index) + '" autocomplete="off" class="layui-input" style="display: inline; width: 64px;">'

			if (index < customColumn - 1) {
				if (width === '100%') {
					html += '% + '
				} else {
					html += 'px + '
				}
			}
		}

		if (width === '100%') {
			html += '% = ' + width
		} else {
			html += 'px = ' + String(width) + 'px'
		}

		$('[data-type="type"] .layui-input-block').html(html)
		$('[data-type="type"]').show()
		layui.form.render()
	} else {
		$('[data-type="type"]').hide()
		$('[type=radio][name=type][value=1]').prop('checked', true)
		$('[type=radio][name=type][value=0]').prop('checked', false)
		layui.form.render()
	}
}

/**
 * 生成表单配置文件
 * @param {Number} is_public
 * @param {Number} is_m 是否转m
 * @param {Number} is_app 是否转app
 */
function generageFormConfigFile (option) {
	if (!option) return false
	var fields = {}
	var field_item = { is_public: option.is_public || 0, is_m: option.is_m || 0, is_app: option.is_app || 0 }
	$('.design-form.design-form-component').find('input:not(.Unwanted),textarea').each(function () {
		var $this = $(this)
		var name = $this.attr('name')
		fields[name] = field_item
	})
	return JSON.stringify({ fields: fields })
}

window.generageFormConfigFile = generageFormConfigFile


/**
 * openCustomLayoutDialog
 *
 * @param {Object} target The dropped target
 * @param {Number} prevId The prev id
 * @param {Number} nextId The next id
 * @param {Boolean} isDroppedInParent The flag that is dropped in parent
 */
function openCustomLayoutDialog (target, prevId, isDroppedInParent) {
	layui.layer.open({
		type: 1,
		title: '添加布局',
		btn: ['取消', '确认'],
		btnAlign: 'c',
		area: '640px',
		content: $('#custom_layout_template').html(),
		yes: function (index) {
			layui.layer.close(index)
			Design.removeTip()
		},
		btn2: function (index) {
			if ($('[type="radio"][name="width"][value="0"]').prop('checked') &&
				!Number($('[name="customWidth"]').val())) {
				layui.layer.msg('请设置布局宽度值(范围960-1920px)')

				return false
			}

			if ($('[type="radio"][name="column"][value="0"]').prop('checked') &&
				!Number($('[name="customColumn"]').val())) {
				layui.layer.msg('请设置列的数量(范围1-9列)')

				return false
			}

			var flag = false

			$('[name^="customColumn"]').each(function () {
				if (!Number($(this).val())) {
					flag = true
				}
			})

			if ($('[type="radio"][name="type"][value="0"]').prop('checked') && flag) {
				layui.layer.msg('请设置列的宽度值(px)')

				return false
			}

			// var url_prefix = getCookie('site_group_code') === 'gb' ? '/activity/gb' : '/activity'
			var url = url_prefix + '/layout-design/add-custom-layout'
			var data = {
				prev_id: prevId,
				page_id: $('#pageId').val(),
				component_key: $('#customLayoutKey').val(),
				width: '',
				columns: '',
				lang: $('#pageLang').val()
			}
			var successCallBack = function (res) {
				Design.disableLoading()
				if (res.code === 0) {
					var tipLen = $('.layout-drop').find('#component_layout_add_position_tips').length
					if (tipLen) {
						$('#component_layout_add_position_tips').before(res.data.component_html)
					} else {
						if (isDroppedInParent) {
							target.prepend(res.data.component_html)
						} else {
							target.before(res.data.component_html)
						}
					}

					layui.layer.close(index)
				} else {
					layui.layer.msg(res.message)
				}
				Design.removeTip()
				renderLayoutTitle()
			}

			var errorCallBack = function (res) {
				Design.disableLoading()
				Design.disableLayuiLoading()
				layui.layer.msg(res.message)
			}

			var widthValue = $('[type="radio"][name="width"]:checked').val()

			if (Number(widthValue) === 0) {
				data.width = Number($('[name="customWidth"]').val())
			} else {
				if (widthValue === '100%') {
					data.width = widthValue
				} else {
					data.width = String(widthValue)
				}
			}

			var i
			var column = 0
			var columnsValue = []

			if (Number($('[type="radio"][name="column"]:checked').val()) === 0) {
				if (Number($('[type="radio"][name="type"]:checked').val()) === 0) {
					$('[name^="customColumnWidth"]').each(function () {
						if (data.width === '100%') {
							columnsValue.push(String($(this).val()) + '%')
						} else {
							columnsValue.push(String($(this).val()) + 'px')
						}
					})
				} else {
					column = Number($('[name="customColumn"]').val())

					if (data.width === '100%') {
						for (i = 0; i < column; i++) {
							columnsValue.push(String(Number(100 / column).toFixed(2)) + '%')
						}
					} else {
						for (i = 0; i < column; i++) {
							columnsValue.push(String(Number(data.width / column).toFixed(2)) + 'px')
						}
					}
				}
			} else {
				column = Number($('[type="radio"][name="column"]:checked').val())

				if (data.width === '100%') {
					for (i = 0; i < column; i++) {
						columnsValue.push(String(Number(100 / column).toFixed(2)) + '%')
					}
				} else {
					for (i = 0; i < column; i++) {
						columnsValue.push(String(Number(data.width / column).toFixed(2)) + 'px')
					}
				}
			}

			if (data.width !== '100%') {
				data.width += 'px'
			}
			data.columns = columnsValue.join(',')

			Design.postAjax(url, data, successCallBack, errorCallBack)
		},
		cancel: function (index) {
			layui.layer.close(index)
			Design.removeTip()
		}
	})

	layui.form.render()

	layui.form.on('radio(width)', function (data) {
		if (Number(data.value) === 0) {
			$('[data-type="width"]').show()
		} else {
			$('[data-type="width"]').hide()
		}
		renderCustomLayout()
	})

	layui.form.on('radio(column)', function (data) {
		if (Number(data.value) === 0) {
			$('[data-type="column"]').show()
		} else {
			$('[data-type="column"]').hide()
		}
		renderCustomLayout()
	})

	layui.form.on('radio(type)', function () {
		renderCustomLayout()
	})

	$(document)
		.on('change', '[name="customWidth"]', function () {
			renderCustomLayout()
		})
		.on('change', '[name="customColumn"]', function () {
			renderCustomLayout()
		})
		.on('change', '[name^="customColumnWidth"]', function () {
			var count = 0
			var position = 0
			var countWidth = 0
			var targets = $('[name^="customColumnWidth"]')
			var length = targets.length
			var width = $('[type="radio"][name="width"]:checked').val()
			var totalWidth = $('[name="customWidth"]').val()

			if (Number(width) !== 0) {
				totalWidth = width
			}

			targets.each(function (index) {
				if (!Number($(this).val())) {
					count++
					position = index
				} else {
					countWidth += Number($(this).val())
				}
			})

			if (count === 1) {
				if (totalWidth !== '100%' && !Number(totalWidth)) {
					layui.layer.msg('请设置布局宽度值(范围960-1920px)')
				} else {
					if (totalWidth === '100%') {
						targets.get(position).value = 100 - countWidth
					} else {
						targets.get(position).value = totalWidth - countWidth
					}
				}
			} else if (count === 0) {
				countWidth = 0

				targets.each(function (index) {
					if (index < length - 1) {
						countWidth += Number($(this).val())
					}

					if (totalWidth === '100%') {
						targets.get(length - 1).value = 100 - countWidth
					} else {
						targets.get(length - 1).value = totalWidth - countWidth
					}
				})
			}
		})
}

/**
 * renderViewPage
 */
function renderViewPage () {
	if ($('#design_to_view').hasClass('layui-btn-normal')) {
		$('.geshop-component-box').each(function () {
			$(this).addClass('design-preview-box')
			$(this).find('> *').addClass('design-preview-hidden')

			var key = $(this).data('key')

			$(this).prepend('<p class="design-preview-content">' + $('#design_drag [data-key=' + key + ']').data('name') + '</p>')
		})
	}
}

/**
 * 添加布局后加上title提示
 */
function renderLayoutTitle () {
	$('.geshop-layout-box').each(function () {
		var key = $(this).data('key')
		$(this).find('.component-drop').each(function () {
			if ($(this).find('.geshop-component-box').length > 0) {
				$(this).find('.design-preview-layout-title').remove()
			} else {
				if ($(this).find('.design-preview-layout-title').length == 0) {
					$(this).prepend('<p class="design-preview-layout-title">' + $('#design_drag [data-key=' + key + ']').data('name') + '</p>')
				}
			}
		})
	})
}

// 页面初始化调用
renderLayoutTitle()

/**
 * defines Drag object
 *
 * @version 1.0
 * @author libinjie1@globalegrow.com
 *
 * @description
 *  gTarget: dragged target
 *  pTarget: dropged target
 *  lDrag: dragged layout class name
 *  lDrop: dropped layout class name
 *  cDrag: dragged component class name
 *  cDrop: dropped component class name
 * 	loadingIndex: loading index
 */
var Design = {
	gTarget: '',
	pTarget: '',
	lDrag: 'layout-drag',
	lDrop: 'layout-drop',
	cDrag: 'component-drag',
	cDrop: 'component-drop',
	loadingIndex: ''
}

/**
 * getting
 *
 * @param {string} key The key
 */
Design.get = function (key) {
	return this[key]
}

/**
 * setting
 *
 * @param {string} key The key
 * @param {string, object} key The value
 */
Design.set = function (key, value) {
	this[key] = value
}

/**
 * format class name
 *
 * @param {string} key The key
 */
Design.toClassName = function (key) {
	return '.' + this[key]
}

/**
 * free drag target
 */
Design.freeDragTarget = function () {
	this.set('gTarget', '')
}

/**
 * free drop target
 */
Design.freeDropTarget = function () {
	this.set('pTarget', '')
}

/**
 * free target including of drag target and drop target
 */
Design.freeAllTargets = function () {
	this.freeDragTarget()
	this.freeDropTarget()
}

/**
 * enable loading
 */
Design.enableLoading = function (bgColor) {
	$('#design_view_loading').css({
		backgroundColor: bgColor ? bgColor : 'rgba(0, 0, 0, .3)'
	}).show()
}

/**
 * disable loading
 */
Design.disableLoading = function () {
	$('#design_view_loading').hide()
}

/**
 * component form dialog show or hide
 */
Design.componentFormShow = function () {
	$('#component_form_dialog').show()
}

Design.componentFormHide = function () {
	$('#component_form_dialog').hide()
}

Design.removeTip = function () {
	// 删除tip
	$('#component_layout_add_position_tips').remove()
}

Design.addBackGroundColor = function () {
	$('.geshop-drop-tip-center-right').css({ backgroundColor: 'rgba(30,159,255,0.2)' })
}

Design.removeBackGroundColor = function () {
	$('.geshop-drop-tip-center-right').css({ backgroundColor: '#ffffff' })
}

Design.layoutTitleOpacity = function () {
	$('.design-preview-layout-title').css({ opacity: 1 })
}

/**
 * enable layui loading
 */
Design.enableLayuiLoading = function () {
	this.loadingIndex = layui.layer.load(1, {
		shade: [0.2, '#000000']
	})
}

/**
 * disable layui loading
 */
Design.disableLayuiLoading = function () {
	layui.layer.close(this.loadingIndex)
}

/**
 * get html string with ajax
 *
 * @param {string} url The post url
 * @param {object} data The post data
 * @param {function} successCallBack The success callback function
 * @param {function} errorCallBack The error callback function
 */
Design.postAjax = function (url, data, successCallBack, errorCallBack) {
	if (typeof successCallBack !== 'function') {
		return false
	}

	this.enableLoading()

	$.ajax({
		type: 'POST',
		url: url,
		data: data,
		success: successCallBack,
		error: errorCallBack,
		dataType: 'json'
	})
}

/**
 * remove target in DOM
 */
Design.removeDragTarget = function () {
	this.get('gTarget').parentNode.removeChild(this.get('gTarget'))
}

/**
 * drop target in parent node
 *
 * @param {string} type The dragged type
 */
Design.dropInParent = function (type) {
	var url, data, successCallBack, errorCallBack
	// var url_prefix = getCookie('site_group_code') === 'gb' ? '/activity/gb' : '/activity'

	if ($(this.get('gTarget')).closest('.design-view').length > 0) {
		if (type === 'layout') {
			url = url_prefix + '/layout-design/move-layout'
			data = {
				page_id: $('#pageId').val(),
				id: $(this.get('gTarget')).attr('id'),
				lang: $('#pageLang').val()
			}

			var layout_prev_id = $('#component_layout_add_position_tips').prevAll('.geshop-layout-box').attr('id')
			data.prev_id = (typeof layout_prev_id === 'undefined') ? 0 : layout_prev_id

			if (data.id === data.prev_id) {
				return false
			}
		} else if (type === 'component') {
			url = url_prefix + '/ui-design/move-ui'
			data = {
				page_id: $('#pageId').val(),
				id: $(this.get('gTarget')).data('id'),
				layout_id: $(this.get('pTarget')).closest(this.toClassName('lDrag')).attr('id'),
				position: $(this.get('pTarget')).closest('[data-position]').data('position'),
				lang: $('#pageLang').val()
			}

			var cmp_prev_id = $('#component_layout_add_position_tips').prevAll('.geshop-component-box').attr('data-id')
			data.prev_id = (typeof cmp_prev_id === 'undefined') ? 0 : cmp_prev_id
		}

		successCallBack = function (res) {
			Design.disableLoading()
			if (res.code === 0) {
				Design.removeDragTarget()
				if ($('#component_layout_add_position_tips').length > 0) {
					$(Design.get('gTarget')).insertBefore($('#component_layout_add_position_tips'))
				} else {
					$(Design.get('pTarget')).append(Design.get('gTarget'))
				}
			} else {
				layui.layer.msg(res.message)
			}
			Design.removeTip()
			renderLayoutTitle()
		}
	} else {
		if (type === 'layout') {
			url = url_prefix + '/layout-design/add-layout'
			data = {
				page_id: $('#pageId').val(),
				component_key: $(this.get('gTarget')).data('key'),
				lang: $('#pageLang').val()
			}
			//GB增加渠道判断
			// if (GESHOP_SITECODE.indexOf('gb') != -1) {
			// 	data.pipeline = sessionStorage.getItem('gb_channel')
			// }
			successCallBack = function (res) {
				Design.disableLoading()
				if (res.code === 0) {
					if (sessionStorage.layout_position === '0' || sessionStorage.layout_position === '1') {
						$('#component_layout_add_position_tips').before(res.data.component_html)
					} else {
						$(Design.get('pTarget')).append(res.data.component_html)
					}

					renderViewPage()
				} else {
					layui.layer.msg(res.message)
				}
				Design.removeTip()
				renderLayoutTitle()
			}

			var layout_prev_id = $('#component_layout_add_position_tips').prevAll('.geshop-layout-box').attr('id')
			data.prev_id = (typeof layout_prev_id === 'undefined') ? 0 : layout_prev_id

			if (Number($(this.get('gTarget')).data('custom')) === 1) {
				openCustomLayoutDialog($(this.get('pTarget')), data.prev_id, true)

				return false
			}
		} else if (type === 'component') {
			var isUnique = Boolean($(this.get('gTarget')).data('unique'))
			var isExist = $('#design_view [data-key=' + $(this.get('gTarget')).data('key') + '][data-unique=true]').length

			if (isUnique && isExist > 0) {
				layui.layer.msg('此组件只能创建一次！')
				Design.removeTip()
				return false
			}

			var uniqueCount = $(this.get('pTarget')).closest(this.toClassName('lDrag')).find('[data-unique="true"]').length

			if (!isUnique && uniqueCount > 0) {
				layui.layer.msg('请将组件放置到其他布局！')
				Design.removeTip()
				return false
			}

			var componentCount = $(this.get('pTarget')).closest(this.toClassName('lDrag')).find('.geshop-component-box').length

			if (isUnique && componentCount > 0) {
				layui.layer.msg('当前布局已有组件，不能够再放置，请将组件放置到空布局！')
				Design.removeTip()
				return false
			}

			url = url_prefix + '/ui-design/add-ui'
			data = {
				page_id: $('#pageId').val(),
				layout_id: $(this.get('pTarget')).closest(this.toClassName('lDrag')).attr('id'),
				component_key: $(this.get('gTarget')).data('key'),
				position: $(this.get('pTarget')).parent().data('position'),
				lang: $('#pageLang').val()
			}
			successCallBack = function (res) {
				Design.disableLoading()
				if (res.code === 0) {
					if (sessionStorage.position === '0' || sessionStorage.position === '1') {
						$('#component_layout_add_position_tips').before(res.data.component_html)
					} else {
						$(Design.get('pTarget')).append(res.data.component_html)
					}

					renderViewPage()
				} else {
					layui.layer.msg(res.message)
				}
				Design.removeTip()
				renderLayoutTitle()
			}

			var cmp_prev_id = $('#component_layout_add_position_tips').prevAll('.geshop-component-box').attr('data-id')
			data.prev_id = (typeof cmp_prev_id === 'undefined') ? 0 : cmp_prev_id
		}
	}

	errorCallBack = function (res) {
		Design.disableLoading()
		Design.disableLayuiLoading()
		layui.layer.msg(res.message)
	}

	this.postAjax(url, data, successCallBack, errorCallBack)
}

/**
 * drop target in child node
 *
 * @param {string} type The dragged type
 */
Design.dropInChild = function (type) {
	var url, data, successCallBack, errorCallBack
	// var url_prefix = getCookie('site_group_code') === 'gb' ? '/activity/gb' : '/activity'
	if ($(this.get('gTarget')).closest('.design-view').length > 0) {
		if (type === 'layout') {
			url = url_prefix + '/layout-design/move-layout'
			data = {
				page_id: $('#pageId').val(),
				id: $(this.get('gTarget')).attr('id'),
				lang: $('#pageLang').val()
			}

			var layout_prev_id = $('#component_layout_add_position_tips').prevAll('.geshop-layout-box').attr('id')
			data.prev_id = (typeof layout_prev_id === 'undefined') ? 0 : layout_prev_id

		} else if (type === 'component') {
			url = url_prefix + '/ui-design/move-ui'
			data = {
				page_id: $('#pageId').val(),
				id: $(this.get('gTarget')).data('id'),
				// prev_id: $(this.get('pTarget')).prevAll(Design.toClassName('cDrag')).length > 0 ? $(this.get('pTarget')).prevAll(Design.toClassName('cDrag')).get(0).getAttribute('data-id') : 0,
				layout_id: $(this.get('pTarget')).closest(this.toClassName('lDrag')).attr('id'),
				position: $(this.get('pTarget')).closest('[data-position]').data('position'),
				lang: $('#pageLang').val()
			}

			var cmp_prev_id = $('#component_layout_add_position_tips').prevAll('.geshop-component-box').attr('data-id')
			data.prev_id = (typeof cmp_prev_id === 'undefined') ? 0 : cmp_prev_id
		}

		if (data && (data.id === data.next_id || data.id === data.prev_id)) {
			return false
		}

		successCallBack = function (res) {
			Design.disableLoading()
			if (res.code === 0) {
				Design.removeDragTarget()
				$(Design.get('gTarget')).insertBefore($('#component_layout_add_position_tips'))
			} else {
				layui.layer.msg(res.message)
			}
			Design.removeTip()
			renderLayoutTitle()
		}
	} else {
		if (type === 'layout') {
			url = url_prefix + '/layout-design/add-layout'
			data = {
				page_id: $('#pageId').val(),
				component_key: $(this.get('gTarget')).data('key'),
				lang: $('#pageLang').val()
			}
			//GB增加渠道判断
			// if (GESHOP_SITECODE.indexOf('gb') != -1) {
			// 	data.pipeline = sessionStorage.getItem('gb_channel')
			// }
			successCallBack = function (res) {
				Design.disableLoading()
				if (res.code === 0) {

					if (sessionStorage.layout_position === '0') {
						$(Design.get('pTarget')).before(res.data.component_html)
					} else if (sessionStorage.layout_position === '1') {
						$(Design.get('pTarget')).after(res.data.component_html)
					}

					renderViewPage()

				} else {
					layui.layer.msg(res.message)
				}
				Design.removeTip()
				renderLayoutTitle()
			}

			var layout_prev_id = $('#component_layout_add_position_tips').prevAll('.geshop-layout-box').attr('id')
			data.prev_id = (typeof layout_prev_id === 'undefined') ? 0 : layout_prev_id

			if (Number($(this.get('gTarget')).data('custom')) === 1) {
				openCustomLayoutDialog($(this.get('pTarget')), data.prev_id, false)

				return false
			}
		} else if (type === 'component') {
			var isUnique = Boolean($(this.get('gTarget')).data('unique'))
			var isExist = $('#design_view [data-key=' + $(this.get('gTarget')).data('key') + '][data-unique=true]').length

			if (isUnique && isExist > 0) {
				layui.layer.msg('此组件只能创建一次！')
				Design.removeTip()
				return false
			}

			var uniqueCount = $(this.get('pTarget')).closest(this.toClassName('lDrag')).find('[data-unique="true"]').length

			if (!isUnique && uniqueCount > 0) {
				layui.layer.msg('请将组件放置到其他布局！')
				Design.removeTip()
				return false
			}

			var componentCount = $(this.get('pTarget')).closest(this.toClassName('lDrag')).find('.geshop-component-box').length

			if (isUnique && componentCount > 0) {
				layui.layer.msg('请将组件放置到空布局！')
				Design.removeTip()
				return false
			}

			url = url_prefix + '/ui-design/add-ui'
			data = {
				page_id: $('#pageId').val(),
				layout_id: $(this.get('pTarget')).closest(this.toClassName('lDrag')).attr('id'),
				component_key: $(this.get('gTarget')).data('key'),
				position: $(this.get('pTarget')).closest('[data-position]').data('position'),
				lang: $('#pageLang').val()
			}
			successCallBack = function (res) {
				Design.disableLoading()
				if (res.code === 0) {

					if (sessionStorage.position === '0') {
						$(Design.get('pTarget')).before(res.data.component_html)
					} else if (sessionStorage.position === '1') {
						$(Design.get('pTarget')).after(res.data.component_html)
					}

					renderViewPage()
				} else {
					layui.layer.msg(res.message)
				}
				Design.removeTip()
				renderLayoutTitle()
			}

			var cmp_prev_id = $('#component_layout_add_position_tips').prevAll('.geshop-component-box').attr('data-id')
			data.prev_id = (typeof cmp_prev_id === 'undefined') ? 0 : cmp_prev_id
		}
	}

	errorCallBack = function (res) {
		Design.disableLoading()
		Design.disableLayuiLoading()
		layui.layer.msg(res.message)
	}

	this.postAjax(url, data, successCallBack, errorCallBack)
}

/**
 * drop layout
 */
Design.dropLayout = function () {
	if (this.get('pTarget').className.indexOf(this.get('lDrag')) !== -1) {
		this.dropInChild('layout')
	} else if (this.get('pTarget').className.indexOf(this.get('lDrop')) !== -1) {
		this.dropInParent('layout')
	}
}

/**
 * drop component
 */
Design.dropComponent = function () {
	if (this.get('pTarget').className.indexOf(this.get('cDrag')) !== -1) {
		this.dropInChild('component')
	} else if (this.get('pTarget').className.indexOf(this.get('cDrop')) !== -1) {
		this.dropInParent('component')
	}
}

/**
 * dropping event including of dropping layout and dropping component
 */
Design.drop = function () {
	if (!this.get('pTarget')) {
		return false
	}

	if ($(this.get('pTarget')).closest('.design-view').length <= 0) {
		return false
	}

	if (this.get('gTarget').className.indexOf(this.get('lDrag')) !== -1) {
		this.dropLayout()
	}

	if (this.get('gTarget').className.indexOf(this.get('cDrag')) !== -1) {
		this.dropComponent()
	}
}

/**
 * show drop tip
 *
 * @param {string} message The message of tip
 * @param {number} top The value of top position of tip
 * @param {string} type The type of tip
 */
Design.showDropTip = function (message, target, type) {
	var top = target.offset().top
	var left = target.offset().left
	var width = target.outerWidth()
	var height = target.outerHeight()

	// 拖放到布局操作栏小图标时清除文字提示
	if (target.outerWidth() < 340) {
		message = ''
	}


	$('.geshop-dragenter-tip-right').removeClass('geshop-dragenter-tip-right')
	$('.geshop-dragenter-tip-wrong').removeClass('geshop-dragenter-tip-wrong')

	target.addClass('geshop-dragenter-tip-' + type)

	$('#geshop_drop_tip_center p').text(message)
	$('#geshop_drop_tip_center').removeClass('geshop-drop-tip-center-right geshop-drop-tip-center-wrong')
	$('#geshop_drop_tip_center').css({
		top: top,
		left: left,
		width: width,
		height: height,
		fontSize: '14px'
	}).addClass('geshop-drop-tip-center-' + type).show()

}

Design.hideDropTip = function () {
	$('.geshop-dragenter-tip-right').removeClass('geshop-dragenter-tip-right')
	$('.geshop-dragenter-tip-wrong').removeClass('geshop-dragenter-tip-wrong')

	$('#geshop_drop_tip_center p').text('')
	$('#geshop_drop_tip_center').css({
		top: 0,
		left: 0,
		width: 0,
		height: 0
	}).removeClass('geshop-drop-tip-center-right geshop-drop-tip-center-wrong').hide()
}

/**
 * series of events about drag and drop
 */

Design.enableLoading('rgba(255, 255, 255, .3)')

$(function () {
	Design.disableLoading()
	$('#design_drag, #design_view').click(function (event) {
		if ($(event.target).closest('.design-form').length <= 0 &&
			!$(event.target).hasClass('component-drag-mask')) {
			$('.design-form').removeClass('design-form-visible')
		}
	})

	$('#js_toggleLeft,#js_toggleLeft_2').click(function () {
		$('.design-left-box').css('width', '80px')
		$('.design-control').css('left', '80px')
		$('.sidebar-btn').removeClass('current')
		$('.template-item-select-container').hide()
	})

	$('.js_tmpCancelSelect').click(function () {
		$('#js_toggleLeft_2').trigger('click')
	})

	// 新改版左侧边栏按钮绑定事件
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
		if (index == 1 || index == 2) {
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
				var url = '/home/page-tpl/confirm-tpl'
				if (getCookie('site_group_code') === 'gb') {
					url = '/activity/gb/page-tpl/confirm-tpl'
				}
				$.post(url, {
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

	$('.sidebar-list-title i').on('click', function () {
		var $ele = $(this).parent()
		var is_visibile = $ele.next().is(':visible')
		if (is_visibile) {
			$ele.addClass('open')
			$ele.next().hide()
		} else {
			$ele.removeClass('open')
			$ele.next().show()
		}
	})

	$('#sync_goodsList').click(function () {
		// var url_prefix = getCookie('site_group_code') === 'gb' ? '/activity/gb' : '/activity'
		layui.layer.confirm('是否同步英文版商品sku？', {
			btn: ['否', '是'],
			area: '420px',
			icon: 3,
			skin: 'element-ui-dialog-class'
		}, function (index) {
			layui.layer.close(index)
		}, function (index) {
			$.ajax({
				url: url_prefix + '/design/copy-sku',
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
	/* gb同步默认语言SKU */
	$('#gb_sync_goodsList').click(function () {
		// var url_prefix = getCookie('site_group_code') === 'gb' ? '/activity/gb' : '/activity'
		var defaultLangName = $(this).data('lang') || '语言'
		layui.layer.confirm('是否同步' + defaultLangName + '版商品sku？', {
			btn: ['否', '是'],
			area: '420px',
			icon: 3,
			skin: 'element-ui-dialog-class'
		}, function (index) {
			layui.layer.close(index)
		}, function (index) {

			var page_id = $('#pageId').val()
			var source = {
				page_id: page_id,
				lang: $('#defaultLang').val()
			}
			var copy = { pipeList: {} }
			copy.pipeList[page_id] = []
			copy.pipeList[page_id].push($('#pageLang').val())
			var data = {
				data: JSON.stringify({
					source: source,
					copy: copy
				})
			}

			$.ajax({
				url: url_prefix + '/design/copy-sku',
				type: 'POST',
				data: data,
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
		// var url_prefix = getCookie('site_group_code') === 'gb' ? '/activity/gb' : '/activity'
		layui.layer.confirm('是否同步英文版数据配置？', {
			btn: ['否', '是'],
			area: '420px',
			icon: 3,
			skin: 'element-ui-dialog-class'
		}, function (index) {
			layui.layer.close(index)
		}, function (index) {
			$.get(url_prefix + '/design/copy-page', {
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
	/* gb 同步默认语言配置 */
	$('#gb_sync_config').click(function () {
		// var url_prefix = getCookie('site_group_code') === 'gb' ? '/activity/gb' : '/activity'
		var defaultLangName = $(this).data('lang') || '语言'
		layui.layer.confirm('是否同步' + defaultLangName + '版配置？', {
			btn: ['否', '是'],
			area: '420px',
			icon: 3,
			skin: 'element-ui-dialog-class'
		}, function (index) {
			layui.layer.close(index)
		}, function (index) {
			var page_id = $('#pageId').val()
			var source = {
				page_id: page_id,
				lang: $('#defaultLang').val()
			}
			var copy = { pipeList: {} }
			copy.pipeList[page_id] = []
			copy.pipeList[page_id].push($('#pageLang').val())
			/**
			 *
			 * @type {{data: string}}
			 * source:被同步的源渠道
			 * copy:同步的渠道
			 * copyData gb类型
			 */
			var data = {
				data: JSON.stringify({
					source: source,
					copy: copy,
					"copyData": 1
				})
			}
			$.post(url_prefix + '/design/copy-page', data, function (res) {
				if (res.code == 0) {
					window.location.reload()
				} else {
					layui.layer.msg(res.message)
					layui.layer.close(index)
				}
			})
		})
	})

	/**
	 * 时间戳转日期
	 * @param { int } time - 时间戳
	 */
	function dateConverse (time) {
		var t = new Date(time),
			year = t.getFullYear(),
			month = t.getMonth() + 1,
			date = t.getDate(),
			hour = t.getHours(),
			minutes = t.getMinutes(),
			second = t.getSeconds()
		return year + '-' + month + '-' + date + ' ' + hour + ':' + minutes + ':' + second
	}

	// 装修页添加页面样式表单渲染
	var addStyleFormRender = {
		renderDatetime: function () {
			$('.js_start_time').each(function () {
				var _this = this
				layui.laydate.render({
					elem: _this
					, type: 'datetime'
					, done: function (value) {
						var start_time = Date.parse(new Date(value))
						var end_time = Date.parse(new Date($(_this).nextAll('.js_end_time').val()))
						if (start_time >= end_time) {
							layer.msg('开始时间不能大于结束时间', { time: 5000 })
							return false
						}
					}
				})
			})
			$('.js_end_time').each(function () {
				var _this = this
				layui.laydate.render({
					elem: _this
					, type: 'datetime'
					, done: function (value) {
						var start_time = Date.parse(new Date($(_this).prevAll('.js_start_time').val()))
						var end_time = Date.parse(new Date(value))
						if (start_time >= end_time) {
							layer.msg('开始时间不能大于结束时间', { time: 5000 })
							return false
						}
					}
				})
			})
		}
	}

	// 添加商品服务标
	$('#add_goods_serviceStandard').click(function () {
		// var url_prefix = getCookie('site_group_code') === 'gb' ? '/activity/gb' : '/activity'
		layui.layer.open({
			title: '添加商品服务标',
			btn: ['取消', '确认'],
			type: '1',
			area: ['1100px', '800px'],
			btnAlign: 'c',
			content: $('#goods-service-tag-template').html(),
			success: function (layero, index) {
				var url = url_prefix + '/design/get-service-tag'
				var data = {
					page_id: $('#pageId').val(),
					lang: $('#pageLang').val(),
				}

				$.ajax({
					url: url,
					method: 'GET',
					type: 'json',
					data: data,
					success: function (res) {
						if (res.code == 0) {
							var data = res.data.tag_config ? JSON.parse(res.data.tag_config) : []
							if (data.length) {
								var getTpl = $('#goods-service-tag-lay-template').html()
								layui.laytpl(getTpl).render(data, function (html) {
									layero.find('.layui-layer-content').html(html)
								})
							}
							renderColorPicker()
							initServiceStandardIndex()
						} else {
							layui.layer.msg(res.message)
						}
					}
				})
			},
			yes: function (index) {
				layui.layer.close(index)
			},
			btn2: function (index) {
				var url = url_prefix + '/design/save-service-tag'
				var data = {
					page_id: $('#pageId').val(),
					lang: $('#pageLang').val(),
				}

				var tag_config_arr = []

				var sku_status = true

				// 遍历服务标组合
				$('.goods-service-tag-container .goods-service-tag-card').each(function (i, element) {
					var tag_config_obj = {}
					var data_config_arr = []
					var $ele = $(element)

					// 过滤重复sku
					var sku_string = $ele.find('textarea.goodsSKU').val().trim()
					var sku_list = sku_string.split(',')
					if (sku_string.length == 0) {
						sku_status = false
					}
					var sku_list_res
					sku_list_res = sku_list.filter(function (item, index, array) {
						return array.indexOf(item) == index
					})
					if (sku_list_res.length != sku_list.length) {
						layui.layer.msg('已过滤重复sku！')
					}
					tag_config_obj['sku_list'] = sku_list_res

					// 遍历服务标配置项
					$ele.find('li').each(function (i, ele) {
						var tag_li_config_obj = {}
						var $element = $(ele)
						tag_li_config_obj['text'] = $element.find('input.text').val()
						tag_li_config_obj['bg_color'] = $element.find('input.bg_color').val()
						tag_li_config_obj['text_color'] = $element.find('input.text_color').val()
						tag_li_config_obj['pic_url'] = $element.find('input.pic_url').val()
						tag_li_config_obj['link_url'] = $element.find('input.link_url').val()
						data_config_arr.push(tag_li_config_obj)
					})
					tag_config_obj['data_config'] = data_config_arr
					tag_config_arr.push(tag_config_obj)
				})

				data['tag_config'] = JSON.stringify(tag_config_arr)

				if (!sku_status) {
					layui.layer.msg('请输入服务标商品SKU')
					return false
				}

				var successCallBack = function (res) {
					Design.disableLoading()
					if (res.code == 0) {
						layui.layer.msg('商品服务标添加成功')
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
				return false
			},
			cancel: function (index) {
				layui.layer.close(index)
			}
		})
	})

	// 增加商品服务标组合
	$('body').on('click', '.add_service_standard_btn', function () {
		var $this = $(this)
		var element = $this.parent('.goods-service-tag-container').find('.goods-service-tag-card:eq(0)').clone()
		element.find('input,textarea').val('')
		element.find('.color-picker-selector[data-hidden-name="service_standard_bgC"]>div').css({ backgroundColor: '#451ca9' })
		element.find('input.bg_color').val('#451ca9')
		element.find('.color-picker-selector[data-hidden-name="service_standard_color"]>div').css({ backgroundColor: '#FFFFFF' })
		element.find('input.text_color').val('#FFFFFF')
		$this.before(element)
		renderColorPicker()
		initServiceStandardIndex()
	})

	// 服务标组合序号
	function initServiceStandardIndex () {
		$('.goods-service-tag-container .goods-service-tag-card').each(function () {
			var $this = $(this)
			var index = parseInt($this.index())
			$this.find('li').each(function (i, ele) {
				var liIndex = $(ele).index() + 1
				$(ele).find('.image-input').attr('name', 'service_standard_bgImg_' + index + '_' + liIndex)
			})
			$this.find('.layui-card-header .service-index').text(index)
		})
	}

	// tab列表项新增序号
	var addPageStyleIndex = 0

	$('#add_page_stylesheet').click(function () {
		// var url_prefix = getCookie('site_group_code') === 'gb' ? '/activity/gb' : '/activity'
		$.get(url_prefix + '/design/get-setting', {
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
							self.find('.page-background-attachment').show()
						} else {
							self.find('.page-background-repeat').hide()
							self.find('.page-position-repeat').hide()
							self.find('.page-background-attachment').hide()
						}

						if (res.data.general.background_repeat && res.data.general.background_repeat.length > 0) {
							self.find('[name=page_background_repeat][value="' + res.data.general.background_repeat + '"]').prop('checked', true)
						}
						if (res.data.general.background_position && res.data.general.background_position.length > 0) {
							self.find('a.page-background-position[data-value="' + res.data.general.background_position + '"]').addClass('current').siblings().removeClass('current')
						}
						if (res.data.general.background_attachment && res.data.general.background_attachment.length > 0) {
							self.find('[name=page_background_attachment][value="' + res.data.general.background_attachment + '"]').prop('checked', true)
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
									'<span class="time-icon"></span><input type="text" class="layui-input start-time js_start_time" style="display:inline;width:203px;border-right:none" name="" autocomplete="off" value="' + dateConverse(item.start_time) + '"><span class="time-split">至</span>' +
									'<input type="text" class="layui-input end-time js_end_time" style="display:inline;width:203px;border-left:none;margin-left:-1px" name="" autocomplete="off" value="' + dateConverse(item.end_time) + '"><span class="time-settings-tips">（注:时间段不要与其他页面样式出现交集哦）</span>' +
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
									contentHtml += '<div class="layui-form-item page-background-attachment" style="display: block; margin-top: 8px;">'
								} else {
									contentHtml += '<div class="layui-form-item page-background-attachment" style="display: none; margin-top: 8px;">'
								}

								contentHtml += '<div class="layui-input-block layui-form-title">背景是否固定</div>' +
									'<div class="layui-input-block">'
								if (item.style_type == 1) {
									if (item.style.background_attachment == 'fixed') {
										contentHtml += '<input type="radio" name="page_background_attachment_' + (index + 1) + '" value="fixed" title="是" checked>'
										contentHtml += '<input type="radio" name="page_background_attachment_' + (index + 1) + '" value="" title="否">'
									} else {
										contentHtml += '<input type="radio" name="page_background_attachment_' + (index + 1) + '" value="fixed" title="是">'
										contentHtml += '<input type="radio" name="page_background_attachment_' + (index + 1) + '" value="" title="否" checked>'
									}
								} else if (item.style_type == 2) {
									contentHtml += '<input type="radio" name="page_background_attachment_' + (index + 1) + '" value="fixed" title="是">'
									contentHtml += '<input type="radio" name="page_background_attachment_' + (index + 1) + '" value="" title="否" checked>'
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
										// contentHtml+='<input type="radio" name="page_background_position_'+(index+1)+'" value="top" title="向上" checked>'
										contentHtml += '<a href="javascript:;" class="page-background-position current" data-value="top"></a>'
									} else {
										// contentHtml+='<input type="radio" name="page_background_position_'+(index+1)+'" value="top" title="向上">'
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
									if (item.style.background_position == 'bottom left') {
										contentHtml += '<a href="javascript:;" class="page-background-position current" data-value="bottom left"></a>'
									} else {
										contentHtml += '<a href="javascript:;" class="page-background-position" data-value="bottom left"></a>'
									}
									if (item.style.background_position == 'bottom right') {
										contentHtml += '<a href="javascript:;" class="page-background-position current" data-value="bottom right"></a>'
									} else {
										contentHtml += '<a href="javascript:;" class="page-background-position" data-value="bottom right"></a>'
									}
								} else if (item.style_type == 2) {
									// contentHtml+='<input type="radio" name="page_background_position_'+(index+1)+'" value="top" title="向上">'
									// contentHtml+='<input type="radio" name="page_background_position_'+(index+1)+'" value="right" title="向右">'
									// contentHtml+='<input type="radio" name="page_background_position_'+(index+1)+'"  value="bottom" title="向下">'
									// contentHtml+='<input type="radio" name="page_background_position_'+(index+1)+'"  value="left" title="向左">'
									// contentHtml+='<input type="radio" name="page_background_position_'+(index+1)+'"  value="center" title="居中" checked>'
									// contentHtml+='<input type="radio" name="page_background_position_'+(index+1)+'"  value="top left" title="上左">'
									// contentHtml+='<input type="radio" name="page_background_position_'+(index+1)+'"  value="top right" title="上右">'
									// contentHtml+='<input type="radio" name="page_background_position_'+(index+1)+'"  value="bottom left" title="下左">'
									// contentHtml+='<input type="radio" name="page_background_position_'+(index+1)+'"  value="bottom right" title="下右">'
									contentHtml += '<a href="javascript:;" class="page-background-position" data-value="top"></a>'
									contentHtml += '<a href="javascript:;" class="page-background-position" data-value="right"></a>'
									contentHtml += '<a href="javascript:;" class="page-background-position" data-value="bottom"></a>'
									contentHtml += '<a href="javascript:;" class="page-background-position" data-value="left"></a>'
									contentHtml += '<a href="javascript:;" class="page-background-position current" data-value="center"></a>'
									contentHtml += '<a href="javascript:;" class="page-background-position" data-value="top left"></a>'
									contentHtml += '<a href="javascript:;" class="page-background-position" data-value="top right"></a>'
									contentHtml += '<a href="javascript:;" class="page-background-position" data-value="bottom left"></a>'
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
						addStyleFormRender.renderDatetime()
						// 初始化tab
						layui.element.render('tab')
						// 初始化tab添加序列号
						addPageStyleIndex = $('.page_style_option_bar .layui-tab-title li').length

						layui.form.render()
						renderColorPicker()
					},
					// yes: function (index) {
					// 	layui.layer.close(index)
					// },
					btn2: function () {
						var url = url_prefix + '/design/setting'
						// var data = {
						// 	page_id: $('#pageId').val(),
						// 	lang: $('#pageLang').val(),
						// 	custom_css: $('[name=page_custom_css]').val(),
						// 	background_color: $('[name=page_background_color]').val(),
						// 	background_image: $('[name=page_background_image]').val(),
						// 	background_repeat: $('[name=page_background_repeat]:checked').val(),
						// 	background_position: $('[name=page_background_position]:checked').val()
						// }
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
						var general_background_attachment = self.find('.page-background-attachment input[type=radio]:checked').val()

						data.general = {
							style_type: Number(general_type)
						}

						if (general_type == 1) {
							data.general.background_color = general_background_color
							data.general.background_image = general_background_image
							data.general.background_repeat = general_background_repeat
							data.general.background_position = general_background_position
							data.general.background_attachment = general_background_attachment
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
									background_position: $(this).find('a.page-background-position.current').data('value'),
									background_attachment: $(this).find('.page-background-attachment input[type=radio]:checked').val()
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
								layui.layer.msg('页面样式保存成功！');
								window.location.reload();
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

	// 专题与活动管理 - 生成模板
	$('#generate_page_model').click(function () {
		// var url_prefix = getCookie('site_group_code') === 'gb' ? '/activity/gb' : '/activity'
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
					url: url_prefix + '/page-tpl/upload-pic',
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
				var url = url_prefix + '/page-tpl/add'
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
		/* 获取模板快照 */
		var pageLanguageId = $('#pageLanguageId').val()
		var siteCode = $('#siteCode').val()
		Design.postAjax('/activity/page-tpl/get-snapshot', {
			site_code: siteCode,
			action: 'activity',
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

	// 活动推广管理 - 生成模板
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

	// GB活动推广管理 - 生成模板
	$('#gbad_generate_page_model').click(function () {
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
					url: '/gbad/page-tpl/upload-pic',
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
				var url = '/gbad/page-tpl/add'
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

	$('#preview_page').click(function () {
		window.open($(this).data('href'))
		// 按钮失去焦点
		$(this).blur()
	})

	/**
	 * 三端合并后，PC转M端，M转APP不再需要传site code
	 */
	$('#view_convert_page').change(function () {
		// 三端合并后，PC转M，M转APP，不需要再传SITECODE
		// var siteCode = Cookies.get('SITECODE')
		// if (siteCode.search('-wap') != -1) {
		// 	Cookies.set('SITECODE', siteCode.substring(0, siteCode.indexOf('-')) + '-app', {
		// 		expires: 1
		// 	})
		// } else if (siteCode.search('-app') != -1) {
		// 	Cookies.set('SITECODE', siteCode.substring(0, siteCode.indexOf('-')) + '-wap', {
		// 		expires: 1
		// 	})
		// }

		// location.href = $(this).attr('data-href')
		location.href = $(this).val()
	})

	// 专题与活动管理 - 重新发布 GMT+8 2019-01-03 18:20:10
	$('body').on('click', '.redistribution', function (index) {
		// var url_prefix = getCookie('site_group_code') === 'gb' ? '/activity/gb' : '/activity',
		var	sessionChannelVue = window.sessionStorage.getItem('gb_channelID') || ''
		$.post(url_prefix + '/design/batch-release', {
			page_id: $('#pageId').val(),
			ids: sessionChannelVue
		}, function (res) {
			layui.layer.msg(res.message)
			layui.layer.close(index)
		})
	})

	// 活动推广管理 - 重新发布
	$('body').on('click', '.adRedistribution', function (index) {
		$.post('/advertisement/design/release', {
			page_id: $('#pageId').val(),
			lang: $('#pageLang').val()
		}, function (res) {
			layui.layer.msg(res.message)
			layui.layer.close(index)
		})
	})

	// GB活动推广管理 - 重新发布
	$('body').on('click', '.gbAdRedistribution', function (index) {
		$.post('/gbad/design/release', {
			page_id: $('#pageId').val(),
			lang: $('#pageLang').val()
		}, function (res) {
			layui.layer.msg(res.message)
			layui.layer.close(index)
		})
	})

	// 专题与活动管理 - 查看访问链接 GMT+8 2019-01-03 18:20:10
	$('#view_page').click(function () {
		var url_prefix =  '/activity/gb/page/get-page-newest-url-list'
		$.get(url_prefix , {
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
					var redistribution_btn = '<button class=\'layui-btn layui-btn-normal redistribution\'>重新发布</button>'
					$.each(html, function () {
						htmlLang += '<a href="' + this.url + '"style="margin-bottom: 10px;margin-left: 5px; width:145px; overflow: hidden; text-overflow: ellipsis;" class="layui-btn layui-btn-normal" target="_blank">' + this.name + '</a>'
					})
					if (res.data.tips && res.data.tips != '') {
						var tips = res.data.tips
						layui.layer.open({
							title: ['发布过程中', 'margin-bottom: 20px;'],
							btn: ['取消', '确认'],
							btnAlign: 'c',
							area: '1000px',
							content: htmlLang + '<br><p style="margin-top: 5px; margin-left: 14px;">其它页面发布进行中，请稍后，关闭此弹窗，将自动在后台执行刷新！！！</p>'
							// content: htmlLang + '<p style="margin-top:20px;"> ' + tips + ' &nbsp;&nbsp; ' + redistribution_btn + '</p>'
						})
					} else {
						layui.layer.open({
							title: ['查看访问链接', 'margin-bottom: 20px;'],
							// btn: ['取消', '确认'],
							btn: [],
							// btnAlign: 'c',
							area: '1000px',
							content: htmlLang
						})
					}
				} else {
					layui.layer.open({
						title: '访问链接',
						btn: ['取消', '确认'],
						btnAlign: 'c',
						area: '1000px',
						content: res.data.tips + '&nbsp;&nbsp;<button class=\'layui-btn layui-btn-normal redistribution\'>重新发布</button>'
					})
				}
			} else {
				layui.layer.open({
					title: '访问链接',
					btn: ['取消', '确认'],
					btnAlign: 'c',
					area: '1000px',
					content: '页面未发布成功，请&nbsp;&nbsp;<button class=\'layui-btn layui-btn-normal redistribution\'>重新发布</button>'
				})
			}
		})
	})

	// 活动推广管理 - 查看访问链接
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
						htmlLang += '<a href="' + this.url + '" class="layui-btn layui-btn-normal" target="_blank" style="border: 1px solid #e6e6e6 !important;">' + this.name + '</a>'
					})
					if (res.data.tips != '') {
						var tips = res.data.tips
						layui.layer.open({
							title: '访问链接',
							btn: ['取消', '确认'],
							btnAlign: 'c',
							area: '640px',
							skin: 'layui-layer-set-view',
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

	// 活动推广管理 - 查看访问链接
	$('#gbad_view_page').click(function () {
		$.get('/gbad/page/get-page-newest-urls', {
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
					var adRedistribution_btn = '<button class=\'layui-btn layui-btn-normal gbAdRedistribution\'>重新发布</button>'
					$.each(html, function () {
						htmlLang += '<a href="' + this.url + '"style="margin-bottom: 10px;margin-left: 5px;width:145px" class="layui-btn layui-btn-normal" target="_blank">' + this.name + '</a>'
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
							// btn: ['取消', '确认'],
							btn: [],
							// btnAlign: 'c',
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
						content: res.data.tips + '&nbsp;&nbsp;<button class=\'layui-btn layui-btn-normal gbAdRedistribution\'>重新发布</button>'
					})
				}
			} else {
				layui.layer.open({
					title: '访问链接',
					btn: ['取消', '确认'],
					btnAlign: 'c',
					area: '640px',
					content: '页面未发布成功，请&nbsp;&nbsp;<button class=\'layui-btn layui-btn-normal gbAdRedistribution\'>重新发布</button>'
				})
			}
		})
	})

	function select_all() {
		layui.use('form', function() {
			var form = layui.form
			form.on('checkbox(check_all)', function (data) {
				$("input[name='langName']").each(function () {
					this.checked = data.elem.checked
				})
				form.render('checkbox')
			})
		})
	}

	// layui复选框全选/反选 GMT+8 2019-01-02 15:30:10
	function checkbox_all() {
		layui.use('form', function () {
			var form = layui.form
			// 全选|反选
			form.on('checkbox(check_all)', function(data) {
				var child = data.elem.checked, item = $("input[name='langName']")

				if (child == true) {
					item.prop("checked", true)
					form.render('checkbox')
				} else {
					item.prop("checked", false)
					form.render('checkbox')
				}
			})

			// 有一个未选中全选取消选中
			form.on('checkbox(checkName)', function (data) {
				var item = $("input[name='langName']"), checkBox_all = $("#check_lang_all")
				for (var i = 0; i < item.length; i++) {
					if (item[i].checked == false) {
						checkBox_all.prop("checked", false)
						form.render('checkbox')
						break
					}
				}

				// 如果都勾选了则自动勾上全选
				var  all = item.length
				for (var i = 0; i < item.length; i++) {
					if (item[i].checked == true) {
						all--
					}
				}
				if (all == 0) {
					checkBox_all.prop("checked", true)
					form.render('checkbox')
				}
			})
		})
	}


    // 批量发布多语言渠道 GMT+8 2019-01-02 14:20:10
	function release_sync () {
		// var url_prefix = getCookie('site_group_code') === 'gb' ? '/activity/gb' : '/activity'
		var childArr = [], childGroupObj = {}

		var group_id = $('#group_id').val()
		Design.getAjax('/activity/gb/page/pipeline-list', { group_id: group_id }).done(function (res) {
			if (res.code == 0 && res.data) {
				var data = res.data
				childGroupObj = data
				childArr = Object.keys(childGroupObj)

				layui.layer.open({
					title: '发布',
					btn: ['取消', '确定'],
					shade: .6,
					skin: 'gb_sync_lang',
					type: '1',
					area: '720px',
					btnAlign: 'c',
					content: $('#page_release_template').html(),
					success: function () {
						layui.form.render()
						var $target = $('.gb_sync_lang'), sessionChannel = window.sessionStorage.getItem('gb_channel')

						var render_init = function () {
							var channelLabel = '', langItemHtml = '',  currentThis = 'layui-form-items'
							channelLabel += '<input type="checkbox" id="check_lang_all" lay-filter="check_all" data-name="" lay-skin="primary" title="全选">'

							Object.keys(childGroupObj).forEach(function (item, index) {
								// 默认勾选当前渠道站点
								if (sessionChannel && sessionChannel.length > 0) {
									if (childGroupObj[item].key === sessionChannel) {
										langItemHtml += '<input type="checkbox" name="langName" lay-filter="checkName" title="' + childGroupObj[item].name + '" data-key="' + childGroupObj[item].key + '" data-value="' + childGroupObj[item].page_id + '" lay-skin="primary" checked>'
									} else {
										langItemHtml += '<input type="checkbox" name="langName" lay-filter="checkName" title="' + childGroupObj[item].name + '" data-key="' + childGroupObj[item].key + '" data-value="' + childGroupObj[item].page_id + '" lay-skin="primary">'
									}
								} else {
									langItemHtml += '<input type="checkbox" name="langName" lay-filter="checkName" title="' + childGroupObj[item].name + '" data-key="' + childGroupObj[item].key + '" data-value="' + childGroupObj[item].page_id + '" lay-skin="primary">'
								}

								$('.layui-all-check', $target).html(channelLabel)
								$('.layui-lang-item', $target).html(langItemHtml)
							})
						}

						render_init()
						checkbox_all()
						layui.form.render()
					},
					btn1: function (index) {
						layui.layer.close(index)
					},
					btn2: function () {
						var pipeList = {}, itemsValue = [], $checkBox = $('.gb_sync_lang').find('.layui-lang-item')

						if ($("input[name='langName']:checked").length > 0) {
							$("input[name='langName']:checked").each(function (item, index) {
								var taht = $(index);
								itemsValue.push(taht.data('value'));
							})

							// 缓存站点渠道ID 重新发布需要这个Value
							window.sessionStorage.setItem('gb_channelID', itemsValue.join(','))

							$.post(url_prefix + '/design/batch-release', {
								page_id: $('#pageId').val(),
								ids: itemsValue.join(','),
                                site_code:$('#siteCode').val()
							}, function (res) {
								// PS: 线上发布语言渠道较多，发布较慢， 本应是根据Code状态码进行提示，但接口没有对应的状态码，因此先弹个框提示一下先！！！
								layui.layer.open({
									title: false,
									shade: .6,
									skin: 'gb_release_class',
									type: '1',
									time: 5000,
									area: ['460px', '130px'],
									closeBtn: 0,
									content: $('#page_release_tips_template').html()
								})
								if (res.code === 0) {
									setTimeout(function () {
										layui.layer.open({
											title: false,
											shade: .6,
											skin: 'gb_release_class',
											type: '1',
											time: 5000,
											closeBtn: 0,
											area: ['460px', '104px'],
											content: $('#page_release_succ_template').html()
										})
										// layui.layer.msg(res.message)
									}, 5000)
								} else {
									layui.layer.msg(res.message)
								}
							})
						} else {
							layui.layer.msg('最少选中一个需发布的渠道!')
							return false;
						}
					},
				})
			}
		})
	}

	// 专题与活动管理 - 装修页发布 PS:仅限于GB专题活动页 GMT+8 2019-01-02 18:20:10
	$('#generate_page').click(function () {
		release_sync()
	})


	// 专题与活动管理 - 装修页发布 PS:已弃用!!!
	$('#generate_page-back').click(function () {
		// var url_prefix = getCookie('site_group_code') === 'gb' ? '/activity/gb' : '/activity'
		layui.layer.confirm('确认生成该页面？', {
			btn: ['取消', '确定'],
			area: '420px',
			skin: 'element-ui-dialog-class',
			icon: 3,
			title: '提示'
		}, function (index) {
			layui.layer.close(index)
		}, function (index) {
			$.post(url_prefix + '/design/release', {
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

	// 活动推广管理 - 装修页发布
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

	// 专题与活动管理 - 装修页发布
	$('#gbad_generate_page').click(function () {
		layui.layer.confirm('确认生成该页面？', {
			btn: ['取消', '确定'],
			area: '420px',
			skin: 'element-ui-dialog-class',
			icon: 3,
			title: '提示'
		}, function (index) {
			layui.layer.close(index)
		}, function (index) {
			$.post('/gbad/design/release', {
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
		renderViewPage()
	})


	/** */
	Design.getAjax = function (url, params) {
		Design.enableLoading()
		return $.ajax({
			type: 'GET',
			url: url,
			data: params,
			dataType: 'json',
			error: function (err) {
				layer.msg('接口异常,请稍后重试')
			},
			complete: function (xhr) {
				Design.disableLoading()
				var res = xhr.responseJSON;
				if (res.code != 0) {
					layui.layer.msg(res.message || '请求错误')
				}
			}
		})

	}

	/**
	 * 同步其他渠道所有数据
	 */

	$('#gb_sync_channel').click(function () {
		channel_sync('sync_data')
	})

	$('#gb_sync_channel_goods').click(function () {
		channel_sync('sync_goods')
	})
	/**
	 * 同步其他渠道数据
	 * @param {* sync_data,sync_goods} type
	 */
	function channel_sync (type) {
		// var channelGroupObj = { "GB": { "key": "GB", "name": "全球站", "langList": { "en": { "key": "en", "name": "英语" }, "ep": { "key": "ep", "name": "西班牙语" }, "fr": { "key": "fr", "name": "法语" } } }, "GBES": { "key": "GBES", "name": "西语站", "langList": { "ep": { "key": "ep", "name": "西班牙语" } } }, "GBFR": { "key": "GBFR", "name": "法语站", "langList": { "fr": { "key": "fr", "name": "法语" } } } }
		// var channelArr = Object.keys(channelGroupObj)
		// var url_prefix = getCookie('site_group_code') === 'gb' ? '/activity/gb' : '/activity'
		var channelArr = [], channelGroupObj = {}

		var group_id = $('#group_id').val()
		Design.getAjax('/activity/gb/page/pipeline-list', { group_id: group_id }).done(function (res) {
			if (res.code == 0 && res.data) {
				var data = res.data
				channelGroupObj = data
				channelArr = Object.keys(channelGroupObj)

				var title = type == 'sync_data' ? '同步其他渠道所有数据' : '同步其他渠道商品数据信息'
				layui.layer.open({
					title: title,
					btn: ['取消', '确定'],
					skin: 'gb_sync_class',
					type: '1',
					area: '720px',
					btnAlign: 'c',
					content: $('#page_pipeline_sync_template').html(),
					success: function () {
						layui.form.render()
						var $target = $('.gb_sync_class')
						var $channel = $('.gb_sync_class').find('[name=channel_list]')

						var channel_init = function () {
							var channelList = '', channelTab = '', channelContent = '', langListHtml = ''
							Object.keys(channelGroupObj).forEach(function (item, index) {
								var currentThis = '', currentClass = 'layui-tab-item'
								if (index === 0) {
									currentThis = 'layui-this'
									currentClass = 'layui-tab-item layui-show'
								}
								channelList += '<option data-value="' + channelGroupObj[item].page_id + '" value="' + channelGroupObj[item].key + '">' + channelGroupObj[item].name + '</option>'
								channelTab += '<li class="swiper-slide" data-key="' + channelGroupObj[item].key + '" class=' + currentThis + '>' + channelGroupObj[item].name + '</li>'
								//语言列表
								var channelContent = '<div class="' + currentClass + '" data-channel1=' + item + ' data-channel=' + channelGroupObj[item].page_id + '>'
								Object.keys(channelGroupObj[item].langList).forEach(function (childItem) {
									channelContent += '<input type="checkbox" name="lang_' + childItem + '_' + item + '" title="' + channelGroupObj[item].langList[childItem].name + '" data-value="' + childItem + '" lay-skin="primary">'
								})
								channelContent += '</div>'
								langListHtml += channelContent
							})
							$channel.html(channelList)
							$('.layui-tab-title', $target).html(channelTab)
							$('.layui-tab-content', $target).html(langListHtml)

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
								nextButton:'.swiper-button-next',
								prevButton:'.swiper-button-prev',
							});
							if ($('.layui-tab-content .layui-tab-item', $target).length <= 6) {
								channelSwiper.navigation.$nextEl.addClass('layui-hide');
								channelSwiper.navigation.$prevEl.addClass('layui-hide');
							}

						}

						var renderLangList = function (channelKey) {
							var channelListObj = channelKey ? channelGroupObj[channelKey].langList : channelGroupObj[channelArr[0]].langList
							var langList = ''
							Object.keys(channelListObj).forEach(function (item) {
								langList += '<option value="' + channelListObj[item].key + '">' + channelListObj[item].name + '</option>'
							})
							$('.gb_sync_class').find('[name=lang_list]').html(langList)
							layui.element.render()

							/* swiper slide */
							$('.channel-container .swiper-wrapper li').on('click', function () {
								// var index = $(this).attr('data-swiper-slide-index')
								var index = $(this).index()
								$(this).addClass('layui-this').siblings().removeClass('layui-this')
								$('.channel-container').find('.layui-tab-item:eq(' + index + ')').addClass('layui-show').siblings().removeClass('layui-show')
							})
						}
						channel_init()
						renderLangList()

						layui.form.render()
						layui.form.on('select(gb_channel)', function (data) {
							var channelKey = data.value
							renderLangList(channelKey)
							layui.form.render()
						})

					},
					yes: function (index) {
						layui.layer.close(index)
					},
					btn2: function () {
						var pipeList = {}
						var $tabCont = $('.gb_sync_class').find('.layui-tab-item')
						$tabCont.each(function (item, index) {
							var channelValue = $(this).attr('data-channel')
							var $tab = $(this)
							if ($tab.find('.layui-form-checked').length > 0) {
								pipeList[channelValue] = []
								$tab.find('.layui-form-checked').each(function (lang) {
									var langValue = $(this).prev('input[type=checkbox]').attr('data-value')
									pipeList[channelValue].push(langValue)
								})
							}

						})
						if (Object.keys(pipeList).length <= 0) {
							layui.layer.msg('最少选中一个需同步的渠道语言')
							return false;
						}

						var copy = {
							// page_id: $('#pageId').val(),
							pipeList: pipeList
						}
						var source = {
							// pipeline: $('.gb_sync_class select[name=channel_list]').val(),
							lang: $('.gb_sync_class select[name=lang_list]').val(),
							page_id: $('.gb_sync_class select[name=channel_list] option:checked').data('value')
						}
						//copyData 同步其他渠道所有数据 1
						var url = type == 'sync_data' ? url_prefix + '/design/copy-page' : url_prefix + '/design/copy-sku'
						var param = { source: source, copy: copy }
						if (type == 'sync_data') {
							param.copyData = 1
						}
						var data = JSON.stringify(param)
						$.post(url, { data: data }, function (res) {
							if (res.code == 0) {
								layui.layer.msg('已同步渠道数据')
								setTimeout(function () {
									window.location.reload()
								}, 500)

							} else {
								layui.layer.msg(res.message)
							}
						})

					},
					cancel: function (index) {
						layui.layer.close(index)
					}
				})
			}

		})

	}

	/**
	 * design page layui form
	 */
	layui.use('form', function () {
		var form = layui.form
		// 装修页布局切换
		form.on('switch(switchInput)', function (data) {
			if (data.elem.checked) {
				$('#design_to_view').trigger('click')
			} else {
				$('#view_to_design').trigger('click')
			}
		})
		// 装修页多语言切换（超过4种变成下拉框）
		form.on('select(selectLang)', function (data) {
			window.location.href = data.value
		})

		// GB装修页多语言切换（超过4种变成下拉框）
		sessionStorage.setItem('gb_channel', $('.gb_selectLang').next().find('.layui-anim dd.layui-this').attr('lay-value'))
		form.on('select(gb_selectLang)', function (data) {
			var channelIndex = $('.gb_selectLang').next().find('.layui-anim dd.layui-this').index()
			var gb_channel = data.value
			sessionStorage.setItem('gb_channel', gb_channel)
			$('.langList-content').find('li:eq(' + channelIndex + ')').addClass('current').siblings().removeClass('current')
			var langFirst, langHref
			if ($('.langList-content').find('li:eq(' + channelIndex + ')').find('a:eq(0)').length > 0) {
				langFirst = $('.langList-content').find('li:eq(' + channelIndex + ')').find('a:eq(0)')
				langHref = langFirst.attr('href')
			} else {
				langFirst = $('.langList-content').find('li:eq(' + channelIndex + ')').find('dd:eq(0)')
				langHref = langFirst.attr('lay-value')

			}
			window.location.href = langHref
		})

		form.on('select(gb_selectChildLang)', function (data) {
			window.location.href = data.value
		})

		layui.element.on('tab(tab_channel_lang)', function (data) {
			var $elem = $(data.elem)
			$elem.find('.layui-tab-title').addClass('layui-tab-more_gb')

			setTimeout(function () {
				$elem.find('.layui-tab-title').addClass('layui-tab-more').removeClass('layui-tab-more_gb')
				$elem.find('.layui-tab-bar').attr('title', '收缩')

			}, 100)
		});

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
			'<div class="layui-form-item page-background-attachment" style="margin-top: 12px;">' +
			'<div class="layui-input-block layui-form-title">页面背景是否固定</div>' +
			'<div class="layui-form-item" style="margin-top: 8px;">' +
			'<div class="layui-input-block">' +
			'<input type="radio" name="page_background_attachment_' + addPageStyleIndex + '" value="fixed" title="是">' +
			'<input type="radio" name="page_background_attachment_' + addPageStyleIndex + '" value="" title="否" checked>' +
			'</div>' +
			'</div></div>' +
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
			'<a href="javascript:;" class="page-background-position" data-value="bottom left"></a>' +
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
		addStyleFormRender.renderDatetime()
		// 初始化颜色选择器
		renderColorPicker()

	})

	/**
	 * openComponentForm
	 *
	 * @param {Number} componentId The component id
	 * @param {Boolean} isNewTemplate Flag that is new template
	 * @param {Boolean} isNewForm Flag that is new form
	 */
	function openComponentForm (componentId, isNewTemplate, isNewForm) {
		// var url_prefix = getCookie('site_group_code') === 'gb' ? '/activity/gb' : '/activity'
		if (!isNewForm && !isNewTemplate) {
			return false
		}

		Design.enableLoading()
		Design.componentFormShow()
		$.get(url_prefix + '/ui-design/get-form', {
			page_id: $('#pageId').val(),
			id: componentId,
			lang: $('#pageLang').val(),
			tpl_id: sessionStorage.getItem('currentTemplateId')
		}, function (res) {
			Design.disableLoading()
			if (res.code === 0) {
				if ($('.design-form-component').find('.geshop-form-content').length > 0) {
					$('.design-form-component').find('.geshop-form-content').remove()
				}
				var formHtml = []
				var time = 0

				formHtml.push('<div class="geshop-form-content">')
				formHtml.push(res.data.component_html)
				formHtml.push('</div>')

				if (isNewTemplate && $('.design-form-component').hasClass('design-form-visible')) {
					$('.design-form-component').removeClass('design-form-visible')
					time = 300
				}

				setTimeout(function () {
					$('#component_form').prepend(formHtml.join(''))
					$('.design-form-component').addClass('design-form-visible')
					var form = layui.form
					var element = layui.element
					var carousel = layui.carousel

					form.render()
					form.on('checkbox(check_all)', function (data) {
						$('[name=' + data.elem.getAttribute('data-name') + ']').prop('checked', data.elem.checked)
						form.render('checkbox')
					})

					form.on('radio(templateId)', function (event) {
						var url = url_prefix + '/ui-design/save-form'
						var data = {
							page_id: $('#pageId').val(),
							id: sessionStorage.getItem('currentComponentId'),
							lang: $('#pageLang').val(),
							tpl_id: sessionStorage.getItem('currentTemplateId')
						}
						//GB增加渠道判断
						if (GESHOP_SITECODE.indexOf('gb') != -1) {
							data.pipeline = sessionStorage.getItem('gb_channel')
						}

						var successCallBack = function (res) {
							Design.disableLoading()
							if (res.code === 0) {
								sessionStorage.setItem('currentTemplateId', event.elem.value)

								var beenReplaced = $(Design.toClassName('cDrag') + '[data-id=' + sessionStorage.getItem('currentComponentId') + ']')

								beenReplaced.after(res.data.select_component_html)
								beenReplaced.remove()
								openComponentForm(sessionStorage.getItem('currentComponentId'), true, true)
							}
							// 1100005 => Coupon校验
							if (res.message) {
								var $length = $('[name=goodsSKU]').length || $('[data-confirmsku=true]').length;
								if ((res.code === 1100001 || res.code === 1100002 || res.code === 1100003 || res.code === 1100004) && typeof gsSkuBatchConfirm != 'undefined' && $length > 0){
									gsSkuBatchConfirm(res.message);
								} else if ((res.code === 1100005) && typeof gsCouponBatchConfirm != 'undefined' && $length > 0) {
									gsCouponBatchConfirm(res.message);
								} else {
									layui.layer.msg(res.message);
								}
							}
						}

						var errorCallBack = function (res) {
							Design.disableLoading()
							Design.disableLayuiLoading()
							layui.layer.msg(res.message)
						}

						var formData = getComponentFormData()

						data.select_tpl_id = formData.tpl_id

						if (data.select_tpl_id != sessionStorage.getItem('currentTemplateId')) {
							data.private_data = '{}'
						} else {
							data.private_data = JSON.stringify(formData.private)
						}
						data.public_data = JSON.stringify(formData.public)
						Design.postAjax(url, data, successCallBack, errorCallBack)
					})
					element.render()

					var obj = carousel.render({
						elem: '#form_carousel',
						width: '100%',
						height: '100%',
						arrow: 'none',
						autoplay: false,
						indicator: 'none',
						index: 0
					})
					$('body')
						.on('click', '#js_moreConfig', function () {
							$('#form_carousel>[carousel-item]>div').removeClass('layui-this')
							obj.reload({
								index: 1
							})
						})
						.on('click', '#js_baseConfig', function () {
							$('#form_carousel>[carousel-item]>div').removeClass('layui-this')
							obj.reload({
								index: 0
							})
						})
					renderColorPicker()
					sessionStorage.setItem('currentComponentId', componentId)
					sessionStorage.setItem('currentTemplateId', res.data.tpl_id)
				}, time)
			} else {
				layui.layer.msg(res.message)
			}
		})
	}

	$('#design_view')
		.on('mouseenter', Design.toClassName('lDrag'), function () {
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
		.on('mouseleave', Design.toClassName('lDrag'), function () {
			$(this).find('.design-operation-panel').remove()
			$(Design.toClassName('cDrop') + ', ' + Design.toClassName('cDrag')).css({
				overflow: 'hidden'
			})
		})
		.on('mouseenter', Design.toClassName('cDrag'), function () {
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
		.on('mouseleave', Design.toClassName('cDrag'), function () {
			$(this).find('.design-operation-panel').remove()
			$(Design.toClassName('cDrop') + ', ' + Design.toClassName('cDrag')).css({
				overflow: 'hidden'
			})
		})
		.on('click', '.js_openLayoutForm', function () {
			var id = $(this).closest(Design.toClassName('lDrag')).attr('id')

			Design.enableLoading()
			Design.componentFormShow()
			// var url_prefix = getCookie('site_group_code') === 'gb' ? '/activity/gb' : '/activity'
			$.get(url_prefix + '/layout-design/get-layout-form', {
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
						$('#layout_form').prepend(formHtml.join(''))
						$('.design-form-layout').addClass('design-form-visible')

						var form = layui.form
						var element = layui.element

						form.render()
						element.render()
						renderColorPicker()
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
			openComponentForm(id, true, true)
			sessionStorage.setItem('currentComponentId', id)
		})
		.on('click', '.js_addLayout', function () {
			var layoutTarget = $(this).closest(Design.toClassName('lDrag'))
			var prevId = layoutTarget.prev(Design.toClassName('lDrag')).length > 0 ? layoutTarget.prev(Design.toClassName('lDrag')).attr('id') : 0

			openCustomLayoutDialog(layoutTarget, prevId, false)
			renderLayoutTitle()
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
				// var url_prefix = getCookie('site_group_code') === 'gb' ? '/activity/gb' : '/activity'
				var url = url_prefix + '/layout-design/delete-layout'
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
			// var url_prefix = getCookie('site_group_code') === 'gb' ? '/activity/gb' : '/activity'
			var url = url_prefix + '/layout-design/move-layout'
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
			// var url_prefix = getCookie('site_group_code') === 'gb' ? '/activity/gb' : '/activity'
			var url = url_prefix + '/layout-design/move-layout'
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
				// var url_prefix = getCookie('site_group_code') === 'gb' ? '/activity/gb' : '/activity'
				var url = url_prefix + '/ui-design/delete-ui'
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
			// var url_prefix = getCookie('site_group_code') === 'gb' ? '/activity/gb' : '/activity'
			var url = url_prefix + '/ui-design/copy-ui'
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
					renderViewPage()
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
				// var url_prefix = getCookie('site_group_code') === 'gb' ? '/activity/gb' : '/activity'
				var url = url_prefix + '/layout-design/copy-layout'
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
						renderViewPage()
						renderLayoutTitle()
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
				// var url_prefix = getCookie('site_group_code') === 'gb' ? '/activity/gb' : '/activity'
				var url = url_prefix + '/ui-design/copy-ui'
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
						renderViewPage()
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

			// var url_prefix = getCookie('site_group_code') === 'gb' ? '/activity/gb' : '/activity'
			var url = url_prefix + '/ui-design/move-ui'
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

			// var url_prefix = getCookie('site_group_code') === 'gb' ? '/activity/gb' : '/activity'
			var url = url_prefix + '/ui-design/move-ui'
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
				openComponentForm(id, true)
				sessionStorage.setItem('currentComponentId', id)
			}
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

			// var url_prefix = getCookie('site_group_code') === 'gb' ? '/activity/gb' : '/activity'
			var url = url_prefix + '/layout-design/save-layout-form'
			var data = {
				page_id: $('#pageId').val(),
				id: sessionStorage.getItem('currentLayoutId'),
				data:{}
			}
			var successCallBack = function (res) {
				Design.disableLoading()
				if (res.code === 0) {
					var LayoutTarget = $(Design.toClassName('lDrag') + '[id=' + sessionStorage.getItem('currentLayoutId') + ']')

					LayoutTarget.after(res.data.component_html)
					LayoutTarget.remove()
					// 保存成功后关闭蒙层
					$('.js_closeDesignForm').trigger('click')
				}

				layui.layer.msg(res.message)
			}
			// 获取表单数据,不需要公有数据
			var formData = getComponentFormData();
			data.data = JSON.stringify(formData.private);
			/*			$('.design-form').find('input').each(function (index, element) {
                      data['data'][element.name] = element.value
                  })*/

			var errorCallBack = function (res) {
				Design.disableLoading()
				Design.disableLayuiLoading()
				layui.layer.msg(res.message)
			}

			Design.postAjax(url, data, successCallBack, errorCallBack)
		})
		.on('click', '#component_form .js_submitDesignForm', function () {

			// var url_prefix = getCookie('site_group_code') === 'gb' ? '/activity/gb' : '/activity'
			var url = url_prefix + '/ui-design/save-form'
			var data = {
				page_id: $('#pageId').val(),
				id: sessionStorage.getItem('currentComponentId'),
				lang: $('#pageLang').val()
			}
			//GB增加渠道判断
			if (GESHOP_SITECODE.indexOf('gb') != -1) {
				data.pipeline = sessionStorage.getItem('gb_channel')
			}
			var successCallBack = function (res) {
				Design.disableLoading()
				if (res.code === 0) {
					var componentTarget = $(Design.toClassName('cDrag') + '[data-id=' + sessionStorage.getItem('currentComponentId') + ']')
					var flag = false

					if (typeof res.data.component_html !== typeof undefined && res.data.component_html !== false) {
						componentTarget.after(res.data.component_html)
						componentTarget.remove()
					}

					if (res.data.tpl_id != sessionStorage.getItem('currentTemplateId')) {
						flag = true
					}
					openComponentForm(sessionStorage.getItem('currentComponentId'), flag, false)
					// 保存成功后关闭蒙层
					$('.js_closeDesignForm').trigger('click')
				}
				if (res.message) {
					var $length = $('[name=goodsSKU]').length || $('[data-confirmsku=true]').length;
					if ((res.code === 1100001 || res.code === 1100002 || res.code === 1100003 || res.code === 1100004) && typeof gsSkuBatchConfirm != 'undefined' && $length > 0){
						gsSkuBatchConfirm(res.message);
					} else if ((res.code === 1100005) && typeof gsCouponBatchConfirm != 'undefined' && $length > 0) {
						gsCouponBatchConfirm(res.message);
					} else {
						layui.layer.msg(res.message)
					}
				}
			}

			var errorCallBack = function (res) {
				Design.disableLoading()
				Design.disableLayuiLoading()
				layui.layer.msg(res.message)
			}

			var formData = getComponentFormData()

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
				initializeResourceDialog(index)
			},
			cancel: function () {
				sessionStorage.setItem('currentResourceId', 0)
			}
		})
	})

	/**
	 * render resource exploere
	 *
	 * @param {Number} id The tree node id
	 * @param {String} url The url of getting resource data
	 */
	function renderResourceExploere (id, url) {
		Design.enableLayuiLoading()
		$.get(url, {
			id: id
		}, function (res) {
			Design.disableLayuiLoading()
			if (res.code === 0) {
				var html = []

				if (res.data.list.length > 0) {
					res.data.list.forEach(function (element) {
						if (Number(element.type) === 1) {
							html.push('<div class="layui-col-xs2 geshop-resource-explorer-col" style="width: 118px; text-align: center;">')
							html.push('<p style="position: relative;">')
							html.push('<img src="' + element.thumbnail_url + '" width="100" style="max-height: 100px;">')
							html.push('<span style="display: block; position: absolute; left: 8px; right: 8px; bottom: -20px; background-color: #f4f4f4; height: 20px; line-height: 20px; font-size: 12px; color: #333333;">' + element.width + 'X' + element.height + '</span>')
							html.push('</p>')
							html.push('<p class="geshop-resource-explorer-title">' + element.name + '</p>')

							html.push('<div class="geshop-resource-operate">')
							html.push('<a href="javascript:;" class="js_useResource" data-url="' + element.url + '" data-width="' + element.width + '" data-height="' + element.height + '">使用</a>')

							html.push('</div>')

							html.push('</div>')
						}
					})
				}

				if (html.length == 0) {
					html.push('<div class="layui-col-xs12">')
					html.push('<p>暂无数据</p>')
					html.push('</div>')
				}

				resourceList = html.join('')

				if (Number(id) === 0 && $('.geshop-resource-explorer').length == 0) {
					layui.layer.open({
						maxmin: true,
						type: 1,
						title: '素材管理器',
						area: ['920px', '600px'],
						content: $('#resource_view_template').html(),
						success: function (layero, index) {
							initializeResourceDialog(index)
						},
						cancel: function () {
							sessionStorage.setItem('currentResourceId', 0)
						}
					})
				} else {
					$('#geshop_resoure_list').html(resourceList)
				}
			}
		})
	}

	/**
	 * initialize resource dialog when opened
	 *
	 * @param {Number} index The index number of layer dialog
	 */
	function initializeResourceDialog (index) {
		$('#geshop_resoure_list').html(resourceList)

		if (index !== false) {
			$('#layui-layer' + index + ' .layui-layer-content').css('height', 'calc(100% - 63px)')
		}

		if (resourceTree.length > 0) {
			renderResourceTree()
		} else {
			$.get('/admin/resource/folder-tree', function (res) {
				resourceTree = res.data
				renderResourceTree()
			})
		}

		initializeResourceUpload()
	}

	//上传图片的条件限制
	function imageLimit (w, h, eve) {
		var $width = w
		var $height = h
		sessionStorage.setItem('imgHeight', $height)
		sessionStorage.setItem('imgWidth', $width)
		// if ($width && $height) {
		// 	sessionStorage.setItem('currentInputTargetName', $(eve).next('input').attr('name'))
		// 	if (resourceList.length > 0) {
		// 		layui.layer.open({
		// 			maxmin: true,
		// 			type: 1,
		// 			title: '素材管理器',
		// 			area: ['960px', '600px'],
		// 			content: $('#resource_view_template').html(),
		// 			success: function () {
		// 				initializeResourceDialog()
		// 			}
		// 		})
		// 	} else {
				renderResourceExploere(0, '/admin/resource/list')
			// }
		// } else {
		// 	layui.layer.msg('请填写好图片的高度与宽度再添加图片')
		// }
	}

	/**
	 * render resource tree
	 */
	function renderResourceTree () {
		$.jstree.destroy()
		$('#geshop_resource_tree')
			.on('changed.jstree', function (e, data) {
				if (data.node) {
					sessionStorage.setItem('currentResourceId', data.node.id)
				}
				$('#empty_tip').hide()
				renderResourceExploere(data.node.id, '/admin/resource/list')
			})
			.jstree({
				core: {
					data: resourceTree
				}
			})
	}

	/**
	 * initialize resource upload function
	 */
	function initializeResourceUpload () {
		layui.upload.render({
			elem: '#upload_resource',
			multiple: true,
			data: {
				parent_id: function () {
					return sessionStorage.getItem('currentResourceId')
				}
			},
			field: 'files',
			url: '/admin/resource/multi-file-upload',
			before: function () {
				if (!sessionStorage.getItem('currentResourceId') ||
					Number(sessionStorage.getItem('currentResourceId')) == 0) {
					$('#empty_tip').show()
				} else {
					Design.enableLayuiLoading()
				}
			},
			done: function (res) {
				Design.disableLayuiLoading()
				if (res.code === 0) {
					renderResourceExploere(sessionStorage.getItem('currentResourceId'), '/admin/resource/list')
				} else {
					layui.layer.msg(res.message)
				}
			},
			error: function () {
				Design.disableLayuiLoading()
				layui.layer.msg('接口错误！')
			}
		})
	}

	$('body')
		.on('click', '.js_useResource', function () {
			var currentTabContentType = sessionStorage.getItem('currentTabContentType')
			var currentTabContentIndex = sessionStorage.getItem('currentTabContentIndex')

			var targetHeight = sessionStorage.getItem('imgHeight'),
				targetWidth = sessionStorage.getItem('imgWidth')
			var currentHeight = $(this).data('height'),
				currentWidth = $(this).data('width');
			var isSelect = targetHeight && targetWidth ? true : false
			var isRight = parseInt(targetHeight) == currentHeight && parseInt(targetWidth) == currentWidth
			var isContrast = isSelect && isContrast
			var target, index;

			if (currentTabContentType == 'tabBgImg') {
				target = $('.page-stylesheet-list-container .layui-tab-item:eq(' + currentTabContentIndex + ')').find('input[name=' + sessionStorage.getItem('currentInputTargetName') + ']').val($(this).data('url'))
				sessionStorage.removeItem('currentTabContentType')
				sessionStorage.removeItem('currentTabContentIndex')
				// 添加页面样式 - 选择背景图片后显示对齐方式
				$('.page-stylesheet-list-container .layui-tab-item:eq(' + currentTabContentIndex + ')').find('.page-background-repeat').show()
				$('.page-stylesheet-list-container .layui-tab-item:eq(' + currentTabContentIndex + ')').find('.page-position-repeat').show()
				$('.page-stylesheet-list-container .layui-tab-item:eq(' + currentTabContentIndex + ')').find('.page-background-attachment').show()
			} else {
				target = $('[name=' + sessionStorage.getItem('currentInputTargetName') + ']')
				if (isSelect && !isRight) {
					layui.layer.msg('请选择合适大小的图片！')

					return false
				} else {
					target = $('[name=' + sessionStorage.getItem('currentInputTargetName') + ']')
					index = $(this).closest('.layui-layer-page').attr('times')

					// 注释该行，解决图片限制后仍将图片url赋值到文本框
					target.val($(this).data('url'))

					target.prev().find('img').attr('src', $(this).data('url'))
					target.prev().find('img').css({
						'width': '80px',
						'height': '35px'
					})
					if ($('[name=' + target.data('width') + ']').length > 0) {
						$('[name=' + target.data('width') + ']').val($(this).data('width'))
					}
					if ($('[name=' + target.data('height') + ']').length > 0) {
						$('[name=' + target.data('height') + ']').val($(this).data('height'))
					}

					if ($('[name = "nowPicWidth"]').length > 0) {
						$('[name = "nowPicWidth"]').val($(this).data('width'))
					}
					if ($('[name= "nowPicHeight"]').length > 0) {
						$('[name= "nowPicHeight"]').val($(this).data('height'))
					}
					sessionStorage.removeItem('imgHeight')
					sessionStorage.removeItem('imgWidth')
					layui.layer.close(index)
				}
			}

			var index = $(this).closest('.layui-layer-page').attr('times')

			if ((target.data('validWidth') && $('[name=' + target.data('validWidth') + ']').val() != $(this).data('width')) ||
				(target.data('validHeight') && $('[name=' + target.data('validHeight') + ']').val() != $(this).data('height'))) {
				layui.layer.msg('请选择合适大小的图片！')

				return false
			}

			target.val($(this).data('url'))
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
					renderResourceTree()
					renderResourceExploere(sessionStorage.getItem('currentResourceId') || 0, '/admin/resource/list')
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




	//APP分享上传的条件限制
	$(document).on('click', '.app-vip-item .img_openResource', function () {
		imageLimit(244, 417, this)

	})
	// GB-v1.2.0 动态图片商品列表组件 U000159,U000160 图片大小限制
	$(document).on('click', '.U000159-img .img_openResource', function () {
		imageLimit(225, 225, this)

	})
	// GB-v1.2.0 动态图片商品列表组件 U000159,U000160 图片大小限制
	$(document).on('click', '.U000160-img .img_openResource', function () {
		imageLimit(312, 312, this)

	})
	//加价购上传的条件限制
	$(document).on('click', '.jia-pc-item .img_openResource', function () {
		imageLimit(244, 346, this)

	})
	//加价购上传的条件限制
	$(document).on('click', '.jia-m-item .img_openResource', function () {
		imageLimit(344, 496, this)

	})


	document.addEventListener('dragstart', function (event) {
		event.dataTransfer.setData('text/plain', 'for firefox')
		if (typeof event.target.getAttribute('data-key') === 'string') {
			var crt = event.target.cloneNode(true)
			crt.style.backgroundColor = '#FFFFFF'
			crt.style.boxShadow = '0px 0px 10px 0px rgba(155,155,155,0.5)'
			crt.firstElementChild.style.color = '#000'
			crt.style.width = '128px'
			crt.style.height = '80px'
			crt.style.padding = '10px'
			crt.style.boxSizing = 'border-box'
			crt.style.position = 'absolute'
			crt.style.top = '-200px'
			crt.style.zIndex = '-100'
			document.body.appendChild(crt)
			event.dataTransfer.setDragImage(crt, 64, 40)
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

	document.addEventListener('dragend', function () {

	}, false)

	document.addEventListener('dragenter', function () {

	}, false)

	// 组件或布局添加位置提示
	var $tips = $('#component_layout_add_position_tips').clone()

	// 当前鼠标位置
	var pos = {}

	document.addEventListener('dragover', function (event) {
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
	}, false)

	document.addEventListener('dragleave', function (event) {
		// display block layout title
		Design.layoutTitleOpacity()
	}, false)

	document.addEventListener('drop', function (event) {
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

/**
 * render color picker plugin
 */
function renderColorPicker () {
	$('.color-picker-selector + input').each(function () {
		if ($(this).val() == '') {
			$(this).prevAll('.color-picker-selector').find('div').addClass('transparent')
		} else {
			$(this).prevAll('.color-picker-selector').find('div').removeClass('transparent')
		}
	})

	$('.color-picker-selector + input').change(function () {
		var val = $.trim($(this).val())

		if (val.length == 0) {
			$(this).val('transparent')
			$(this).prevAll('.color-picker-selector').find('div').addClass('transparent')
		} else {
			$(this).prevAll('.color-picker-selector').find('div').css('backgroundColor', val).removeClass('transparent')
			$(this).prevAll('.color-picker-selector').ColorPickerSetColor(val)
		}
	})

	$('.color-picker-selector').ColorPicker({
		onBeforeShow: function () {
			$(this).ColorPickerSetColor($(this).next('[name=' + $(this).data('hiddenName') + ']').val())
		},
		onShow: function (colpkr) {
			$(colpkr).fadeIn(500)
			return false
		},
		onHide: function (colpkr) {
			$(colpkr).fadeOut(500)
			return false
		},
		onSubmit: function (hsb, hex, rgb, el) {
			$(el).find('div').css('backgroundColor', '#' + hex).removeClass('transparent')
			$(el).next('[name=' + $(el).data('hiddenName') + ']').val('#' + hex)
			$(el).parents('.layui-form-item:eq(0)').next('.color-picker-rgb').val(rgb.r+','+rgb.g+','+rgb.b)
			$(el).ColorPickerHide()
		}
	})
}

function geshopUrlToApp (goodsUrl, goods_id) {
	var urlconfig = {
		'zf-app': 'zaful://action?actiontype=3&url=' + goods_id + '&name=scoop-neck-loose-knit-sweater&source=deeplink',
		'rw-app': 'rosewholesale://product?goods_id=' + goods_id + '&source=',
		'rg-app': 'rosegal://product?goods_id=' + goods_id + '&source='
	}
	if (GESHOP_PLATFORM || GESHOP_SITECODE) {
		if (GESHOP_PLATFORM != 'app') {
			return goodsUrl;
		} else {
			return urlconfig[GESHOP_SITECODE]
		}
	} else {
		return goodsUrl
	}
}
