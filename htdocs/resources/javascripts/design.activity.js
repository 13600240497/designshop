var $ = window.$
var layui = window.layui
var resourceTree = []
var resourceList = ''
var Cookies = window.Cookies

/**
 * getComponentFormData
 */
function getComponentFormData () {
	var publicData = {}, privateData = {}, tpl_id

	$('.design-form').find('input,select,textarea').each(function (index, element) {
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
	$('.design-form.design-form-component').find('input:not(.Unwanted),textarea').each(function() {
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

			var url = '/activity/layout-design/add-custom-layout'
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

var templateData = {
    currentInputTarget: null
}

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

	if ($(this.get('gTarget')).closest('.design-view').length > 0) {
		if (type === 'layout') {
			url = '/activity/layout-design/move-layout'
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
			url = '/activity/ui-design/move-ui'
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
			url = '/activity/layout-design/add-layout'
			data = {
				page_id: $('#pageId').val(),
				component_key: $(this.get('gTarget')).data('key'),
				lang: $('#pageLang').val()
			}
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

			var findString = '[data-unique="true"][data-key="U000055"], [data-unique="true"][data-key="U000056"]'
			var uniqueCount = $(this.get('pTarget')).closest(this.toClassName('lDrag')).find(findString).length

			if (!isUnique && uniqueCount > 0) {
				layui.layer.msg('请将组件放置到其他布局！')
				Design.removeTip()
				return false
			}

			var gTargetKey = $(this.get('gTarget')).data('key')
			var combineComponentKeys = ['U000055', 'U000056']
			var componentCount = $(this.get('pTarget')).closest(this.toClassName('lDrag')).find('.geshop-component-box').length

			if (isUnique && $.inArray(gTargetKey, combineComponentKeys) != -1 && componentCount > 0) {
				layui.layer.msg('当前布局已有组件，不能够再放置，请将组件放置到空布局！')
				Design.removeTip()
				return false
			}

			url = '/activity/ui-design/add-ui'
			data = {
				page_id: $('#pageId').val(),
				layout_id: $(this.get('pTarget')).closest(this.toClassName('lDrag')).attr('id'),
				component_key: $(this.get('gTarget')).data('key'),
				position: $(this.get('pTarget')).parent().data('position'),
				lang: $('#pageLang').val()
			}

			var tpl_id = $(this.get('gTarget')).data('tplid')
			if (tpl_id) {
				data.ui_tpl_id = tpl_id
			}

			successCallBack = function (res) {
				Design.disableLoading()
				if (res.code === 0) {
					var getData = res.data.async_data_info ? res.data.async_data_info : []
					// 保存返回的商品详情
					window.GESHOP_ASYNC_DATA_INFO = Object.assign({}, window.GESHOP_ASYNC_DATA_INFO)
					window.GESHOP_ASYNC_DATA_INFO[res.data.ui_id] = getData
					if (window.GESHOP_STORE) {
						GESHOP_STORE.commit('global/UPDATE_GOODS_INFO', window.GESHOP_ASYNC_DATA_INFO)
					}

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
	if ($(this.get('gTarget')).closest('.design-view').length > 0) {
		if (type === 'layout') {
			url = '/activity/layout-design/move-layout'
			data = {
				page_id: $('#pageId').val(),
				id: $(this.get('gTarget')).attr('id'),
				lang: $('#pageLang').val()
			}

			var layout_prev_id = $('#component_layout_add_position_tips').prevAll('.geshop-layout-box').attr('id')
			data.prev_id = (typeof layout_prev_id === 'undefined') ? 0 : layout_prev_id

		} else if (type === 'component') {
			url = '/activity/ui-design/move-ui'
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
			url = '/activity/layout-design/add-layout'
			data = {
				page_id: $('#pageId').val(),
				component_key: $(this.get('gTarget')).data('key'),
				lang: $('#pageLang').val()
			}
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

			var findString = '[data-unique="true"][data-key="U000055"], [data-unique="true"][data-key="U000056"]'
			var uniqueCount = $(this.get('pTarget')).closest(this.toClassName('lDrag')).find(findString).length

			if (!isUnique && uniqueCount > 0) {
				layui.layer.msg('请将组件放置到其他布局！')
				Design.removeTip()
				return false
			}

			var gTargetKey = $(this.get('gTarget')).data('key')
			var combineComponentKeys = ['U000055', 'U000056']
			var componentCount = $(this.get('pTarget')).closest(this.toClassName('lDrag')).find('.geshop-component-box').length

			if (isUnique && componentCount > 0 && $.inArray(gTargetKey, combineComponentKeys) != -1) {
				layui.layer.msg('请将组件放置到空布局！')
				Design.removeTip()
				return false
			}

			url = '/activity/ui-design/add-ui'
			data = {
				page_id: $('#pageId').val(),
				layout_id: $(this.get('pTarget')).closest(this.toClassName('lDrag')).attr('id'),
				component_key: $(this.get('gTarget')).data('key'),
				position: $(this.get('pTarget')).closest('[data-position]').data('position'),
				lang: $('#pageLang').val()
			}

			var tpl_id = $(this.get('gTarget')).data('tplid')
			if (tpl_id) {
				data.ui_tpl_id = tpl_id
			}

			successCallBack = function (res) {
				Design.disableLoading()
				if (res.code === 0) {
					var getData = res.data.async_data_info ? res.data.async_data_info : []
					// 保存返回的商品详情
					window.GESHOP_ASYNC_DATA_INFO = Object.assign({}, window.GESHOP_ASYNC_DATA_INFO)
					window.GESHOP_ASYNC_DATA_INFO[res.data.ui_id] = getData
					if (window.GESHOP_STORE) {
						GESHOP_STORE.commit('global/UPDATE_GOODS_INFO', window.GESHOP_ASYNC_DATA_INFO)
					}
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

	if (this.get('gTarget').className.indexOf(this.get('lDrag')) !== -1 && ComponentInfo.componentMoveAble($(this.get('gTarget')))) {
		this.dropLayout()
	}

	if (this.get('gTarget').className.indexOf(this.get('cDrag')) !== -1 && ComponentInfo.componentMoveAble($(this.get('gTarget')))) {
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
				$.post('/home/page-tpl/confirm-tpl', {
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
				url: '/activity/design/copy-sku',
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
			$.get('/activity/design/copy-page', {
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

	// tab列表项新增序号
	var addPageStyleIndex = 0

	$('#add_page_stylesheet').click(function () {
		$.get('/activity/design/get-setting', {
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
						addStyleFormRender.renderDatetime()
						// 初始化tab
						layui.element.render('tab')
						// 初始化tab添加序列号
						addPageStyleIndex = $('.page_style_option_bar .layui-tab-title li').length

						layui.form.render()
						renderColorPicker()
					},
					btn2: function () {
						var url = '/activity/design/setting'
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

	// 专题与活动管理 - 生成模板
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
					url: '/activity/page-tpl/upload-pic',
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
				var url = '/activity/page-tpl/add'
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

	// 专题与活动管理 - 站点装修页发布
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
			},
			btn2: function (index) {
				var langList = []
				var pageId = $('#pageId').val()

				if ($('[name="generate_release_language"]:checked').length == 0) {
					layui.layer.msg('请选择需要发布的语言')

					return false
				}

				$('[name="generate_release_language"]:checked').each(function () {
					langList.push($(this).val())
				})

				Design.postAjax('/activity/design/release', {
					page_id: pageId,
					langList: langList.join(',')
				}, function (res) {
					layui.layer.msg(res.message)
					Design.disableLoading()
				}, function (res) {
					layui.layer.msg(res.message)
					Design.disableLoading()
				})
			}
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

	$('#preview_page').click(function () {
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

	// 专题与活动管理 - 重新发布
	$('body').on('click', '.redistribution', function (index) {
		$.post('/activity/design/release', {
			page_id: $('#pageId').val(),
			lang: $('#pageLang').val()
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

	// 专题与活动管理 - 查看访问链接
	$('#view_page').click(function () {
		$.get('/activity/page/get-page-newest-urls', {
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
						htmlLang += '<a href="' + this.url + '" class="layui-btn layui-btn-normal" target="_blank">' + this.name + '</a>'
					})
					if (res.data.tips != '') {
						var tips = res.data.tips
						layui.layer.open({
							title: '访问链接',
							btn: ['取消', '确认'],
							btnAlign: 'c',
							area: '640px',
							content: htmlLang + '<p style="margin-top:20px;"> ' + tips + ' &nbsp;&nbsp; ' + redistribution_btn + '</p>'
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
						content: res.data.tips + '&nbsp;&nbsp;<button class=\'layui-btn layui-btn-normal redistribution\'>重新发布</button>'
					})
				}
			} else {
				layui.layer.open({
					title: '访问链接',
					btn: ['取消', '确认'],
					btnAlign: 'c',
					area: '640px',
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
				var res = xhr.responseJSON
				if (res.code != 0) {
					layui.layer.msg(res.message || '请求错误')
				}
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
		if (!isNewForm && !isNewTemplate) {
			return false
		}

		Design.enableLoading()
		Design.componentFormShow()
		$.get('/activity/ui-design/get-form', {
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



                    /** 组件模版切换，保存表单 */
					form.on('radio(templateId)', function (event) {
						var url = '/activity/ui-design/save-form'
						var data = {
							page_id: $('#pageId').val(),
							id: sessionStorage.getItem('currentComponentId'),
							lang: $('#pageLang').val(),
							tpl_id: sessionStorage.getItem('currentTemplateId')
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

							layui.layer.msg(res.message)
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
			$.get('/activity/layout-design/get-layout-form', {
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
				var url = '/activity/layout-design/delete-layout'
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

			var url = '/activity/layout-design/move-layout'
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

			var url = '/activity/layout-design/move-layout'
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
				var url = '/activity/ui-design/delete-ui'
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
			var url = '/activity/ui-design/copy-ui'
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
				var url = '/activity/layout-design/copy-layout'
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
				var url = '/activity/ui-design/copy-ui'
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
						try {
							var copy_id = sessionStorage.getItem('copiedComponentId')
							res.data.uiIds.forEach(function (item) {
								window.GESHOP_ASYNC_DATA_INFO[item] = JSON.parse(JSON.stringify(window.GESHOP_ASYNC_DATA_INFO[copy_id] || []))
								if (window.GESHOP_STORE) {
									GESHOP_STORE.commit('global/UPDATE_GOODS_INFO', window.GESHOP_ASYNC_DATA_INFO)
								}
							})
						} catch (e) {
							console.log(e)
						}
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

			var url = '/activity/ui-design/move-ui'
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

			var url = '/activity/ui-design/move-ui'
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
		// 保存组件模板
		.on('click', '.js_saveComponentTemp', function () {
			var ui_id = $(this).closest(Design.toClassName('cDrag')).data('id')
			layui.layer.open({
				title: '新增组件模板',
				btn: ['取消', '确认'],
				type: '1',
				// area: ['720px', '550px'],
				area: '720px',
				btnAlign: 'c',
				content: $('#component_model_template').html(),
				success: function () {
					layui.form.render()
					layui.upload.render({
						elem: '#upload_model_picture',
						field: 'files',
						url: '/activity/page-tpl/upload-pic',
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
					var url = '/activity/page-ui-tpl/add'
					var data = {
						page_id: $('#pageId').val(),
						ui_id: ui_id,
						name: $('[name=cmp_model_name]').val(),
						pic_url: $('[name=model_pic]').val(),
						view_type: $('select[name=model_type]').val() // 1 共有 2私有
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
			Design.postAjax('/activity/page-ui-tpl/get-snapshot', {
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
			var url = '/activity/layout-design/save-layout-form'
			var data = {
				page_id: $('#pageId').val(),
				id: sessionStorage.getItem('currentLayoutId')
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
			var url = '/activity/ui-design/save-form'
			var data = {
				page_id: $('#pageId').val(),
				id: sessionStorage.getItem('currentComponentId'),
				lang: $('#pageLang').val(),
                goodsInfoArr: sessionStorage.currentGoodsInfo
			}
			var successCallBack = function (res) {
				Design.disableLoading()
				if (res.code === 0) {
                    // 请求之后删除 session 避免影响其他组件提交该属性
                    GESHOP_STORE.dispatch('global/deleteCurrentGoodsInfo')
                    var getData = res.data.async_data_info ? res.data.async_data_info : []
                    // 保存返回的商品详情
					window.GESHOP_ASYNC_DATA_INFO = Object.assign({}, window.GESHOP_ASYNC_DATA_INFO)
                    window.GESHOP_ASYNC_DATA_INFO[sessionStorage.getItem('currentComponentId')] = getData
                    if (window.GESHOP_STORE) {
                        GESHOP_STORE.commit('global/UPDATE_GOODS_INFO', window.GESHOP_ASYNC_DATA_INFO);
                    }

					var componentTarget = $(Design.toClassName('cDrag') + '[data-id=' + sessionStorage.getItem('currentComponentId') + ']')
					var flag = false

					componentTarget.after(res.data.component_html)
					componentTarget.remove()

					if (res.data.tpl_id != sessionStorage.getItem('currentTemplateId')) {
						flag = true
					}
					openComponentForm(sessionStorage.getItem('currentComponentId'), flag, false)
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
		sessionStorage.setItem('currentInputTargetName', $(this).next('input').attr('name'));
		// tab图片组件开启
		if($(this).parents('.list-group-item').length > 0){
            templateData.currentInputTarget = $(this).next('input');
        }else{
            templateData.currentInputTarget = null;
        }
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
				// target = $('[name=' + sessionStorage.getItem('currentInputTargetName') + ']')
				if (isSelect && !isRight) {
					layui.layer.msg('请选择合适大小的图片！')
					return false
				} else {
					target = templateData.currentInputTarget ? templateData.currentInputTarget : $('[name=' + sessionStorage.getItem('currentInputTargetName') + ']')
					index = $(this).closest('.layui-layer-page').attr('times')

					// 注释该行，解决图片限制后仍将图片url赋值到文本框
					// target.val($(this).data('url'))

					// target.prev().find('img').attr('src', $(this).data('url'))
					// target.prev().find('img').css({
					// 	'width': '80px',
					// 	'height': '35px'
					// })
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

			// var index = $(this).closest('.layui-layer-page').attr('times')

			if ((target.data('validWidth') && $('[name=' + target.data('validWidth') + ']').val() != $(this).data('width')) ||
				(target.data('validHeight') && $('[name=' + target.data('validHeight') + ']').val() != $(this).data('height'))) {
				layui.layer.msg('请选择合适大小的图片！')

				return false
			}

			if ($('[name=' + target.data('width') + ']').length > 0) {
				$('[name=' + target.data('width') + ']').val($(this).data('width'))
			}
			if ($('[name=' + target.data('height') + ']').length > 0) {
				$('[name=' + target.data('height') + ']').val($(this).data('height'))
			}
			// 给input框加上data-width和data-heigh属性
			target.attr('data-width', currentWidth);
			target.attr('data-height', currentHeight);
			// 改变URL值
			target.val($(this).data('url'));
			target.change();
			// 关闭弹层
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

	//上传图片的条件限制
	function imageLimit (w, h, eve) {
		var $width = w
		var $height = h
		sessionStorage.setItem('imgHeight', $height)
		sessionStorage.setItem('imgWidth', $width)
		if ($width && $height) {
			sessionStorage.setItem('currentInputTargetName', $(eve).next('input').attr('name'))
			if (resourceList.length > 0) {
				layui.layer.open({
					maxmin: true,
					type: 1,
					title: '素材管理器',
					area: ['960px', '600px'],
					content: $('#resource_view_template').html(),
					success: function () {
						initializeResourceDialog()
					}
				})
			} else {
				renderResourceExploere(0, '/admin/resource/list')
			}
		} else {
			layui.layer.msg('请填写好图片的高度与宽度再添加图片')
		}
	}


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
				$(el).ColorPickerHide()
			}
		})
	}

	// data-ctype - cTmp：组件模板
	window.renderColorPicker = renderColorPicker

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

	document.addEventListener('dragend', function () {

	}, false)

	document.addEventListener('dragenter', function () {

	}, false)

	// 组件或布局添加位置提示
	var $tips = $('#component_layout_add_position_tips').clone()

	// 当前鼠标位置
	var pos = {}

	document.addEventListener('dragover', handleDragoverEvent, false)

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

	/* 生成模板快照 */
    /**
     * drag悬浮事件
     * dragover event callback
     * @param event
     * @returns {boolean}
     */
    function handleDragoverEvent(event) {
        /* 处理多余的dom */
        event.preventDefault();
        event.stopPropagation();
        var lastMousePageY = dragInfo.lastMousePageY;
        dragInfo.lastMousePageY = parseInt(event.pageY);
        var designTips = $('#component_layout_add_position_tips');
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
function geshopUrlToApp (goodsUrl, goods_id) {
	var urlconfig = {
		'zf-app': 'zaful://action?actiontype=3&url=' + goods_id + '&name=scoop-neck-loose-knit-sweater&source=deeplink',
		'rw-app': 'rosewholesale://product?goods_id=' + goods_id + '&source=',
		'rg-app': 'rosegal://product?goods_id=' + goods_id + '&source='
	}
	if (GESHOP_PLATFORM || GESHOP_SITECODE) {
		if (GESHOP_PLATFORM != 'app') {
			return goodsUrl
		} else {
			return urlconfig[GESHOP_SITECODE]
		}
	} else {
		return goodsUrl
	}
}


var ComponentInfo = (function(my){
    my.componentType = function(target){
        if(!target){return ''};
        return target.hasClass('geshop-component-box') ? 'ui-component' : target.hasClass('geshop-layout-box') ? 'layout' : '';
    }
    /**
     * 是否是ui组件
     * @param target
     * @returns {boolean}
     */
    my.isComponent = function(target){
        return this.componentType(target) === 'ui-component'
    }
    /**
     * 是否在跳过验证可拖动白名单
     * 布局列表，组件列表，组件模板
     * @param target
     * @returns {boolean}
     */
    my.isWhitelist = function(target){
        var whitelistClass = ['design-model-item','design-layout-item','component-list-item'];
        var result = false;
        for(var i=0;i<whitelistClass.length;i++){
            if( target.hasClass(whitelistClass[i]) ){
                result = true
                break;
            }
        }
        return result;
    }
    /**
     * 组件位置是否移动判断
     * componentClass 判断是ui组件/layout组件
     * prevCompId 上一个组件data-id ，nextCompId 下一个组件data-id
     * success removeTip('释放之后，此组件将增加在此')
     * @param target 组件target
     * @return {boolean}
     */
    my.componentMoveAble = function (target){
        var status = true;
        var componentClass = target.hasClass('geshop-component-box') ? '.geshop-component-box' : target.hasClass('geshop-layout-box') ? '.geshop-layout-box' : '';
        var $component_tips = $('#component_layout_add_position_tips');
        var $component_prev = $component_tips.prevAll(componentClass);
        var $component_next = $component_tips.nextAll(componentClass);

        var is_component = this.isComponent(target);
        var prevCompId = is_component ? $component_prev.attr('data-id') : $component_prev.attr('id');
        var nextCompId = is_component ? $component_next.attr('data-id') : $component_next.attr('id');
        var targetId = is_component ? target.attr('data-id') : target.attr('id');
        // var isWhitelist = this.isWhitelist(target);
        if ((!!targetId ) && (prevCompId === targetId || nextCompId === targetId)) {
            // layui.layer.msg('组件未移动！')
            Design.removeTip()
            renderLayoutTitle()
            status = false
        }
        return status
    }

    return my
}({}))


