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
		btn: ['确认', '取消'],
		area: '640px',
		content: $('#custom_layout_template').html(),
		yes: function (index) {
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
					if (isDroppedInParent) {
						target.append(res.data.component_html)
					} else {
						target.before(res.data.component_html)
					}

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
		btn2: function (index) {
			layui.layer.close(index)
		},
		cancel: function (index) {
			layui.layer.close(index)
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
		top: $('#design_view').offset().top,
		left: $('#design_view').offset().left,
		width: $('#design_view').outerWidth(),
		height: $('#design_view').outerHeight(),
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

			if ($(this.get('pTarget')).find(this.toClassName('lDrag') + ':last').length > 0) {
				data.prev_id = $(this.get('pTarget')).find(this.toClassName('lDrag') + ':last').attr('id')
			} else {
				data.prev_id = 0
			}

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

			if ($(this.get('pTarget')).find(this.toClassName('cDrag') + ':last').length > 0) {
				data.prev_id = $(this.get('pTarget')).find(this.toClassName('cDrag') + ':last').data('id')
			} else {
				data.prev_id = 0
			}
		}

		successCallBack = function (res) {
			Design.disableLoading()
			if (res.code === 0) {
				Design.removeDragTarget()
				Design.get('pTarget').appendChild(Design.get('gTarget'))
			} else {
				layui.layer.msg(res.message)
			}
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
					$(Design.get('pTarget')).append(res.data.component_html)
					renderViewPage()
				} else {
					layui.layer.msg(res.message)
				}
			}

			if ($(this.get('pTarget')).find(this.toClassName('lDrag') + ':last').length > 0) {
				data.prev_id = $(this.get('pTarget')).find('.' + this.get('lDrag') + ':last').attr('id')
			} else {
				data.prev_id = 0
			}

			if (Number($(this.get('gTarget')).data('custom')) === 1) {
				openCustomLayoutDialog($(this.get('pTarget')), data.prev_id, true)

				return false
			}
		} else if (type === 'component') {
			var isUnique = Boolean($(this.get('gTarget')).data('unique'))
			var isExist = $('#design_view [data-unique="true"]').length

			if (isUnique && isExist > 0) {
				layui.layer.msg('此组件只能创建一次！')

				return false
			}

			var uniqueCount = $(this.get('pTarget')).closest(this.toClassName('lDrag')).find('[data-unique="true"]').length

			if (!isUnique && uniqueCount > 0) {
				layui.layer.msg('请将组件放置到其他布局！')

				return false
			}

			var componentCount = $(this.get('pTarget')).closest(this.toClassName('lDrag')).find('.geshop-component-box').length

			if (isUnique && componentCount > 0) {
				layui.layer.msg('当前布局已有组件，不能够再放置，请将组件放置到空布局！')

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
			successCallBack = function (res) {
				Design.disableLoading()
				if (res.code === 0) {
					$(Design.get('pTarget')).append(res.data.component_html)
					renderViewPage()
				} else {
					layui.layer.msg(res.message)
				}
			}

			if ($(this.get('pTarget')).find(this.toClassName('cDrag') + ':last').length > 0) {
				data.prev_id = $(this.get('pTarget')).find(this.toClassName('cDrag') + ':last').data('id')
			} else {
				data.prev_id = 0
			}
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
				prev_id: $(this.get('pTarget')).prevAll(this.toClassName('lDrag')).length > 0 ? $(this.get('pTarget')).prevAll(this.toClassName('lDrag')).get(0).id : 0,
				lang: $('#pageLang').val()
			}
		} else if (type === 'component') {
			url = '/activity/ui-design/move-ui'
			data = {
				page_id: $('#pageId').val(),
				id: $(this.get('gTarget')).data('id'),
				prev_id: $(this.get('pTarget')).prevAll(Design.toClassName('cDrag')).length > 0 ? $(this.get('pTarget')).prevAll(Design.toClassName('cDrag')).get(0).getAttribute('data-id') : 0,
				layout_id: $(this.get('pTarget')).closest(this.toClassName('lDrag')).attr('id'),
				position: $(this.get('pTarget')).closest('[data-position]').data('position'),
				lang: $('#pageLang').val()
			}
		}

		if (data && (data.id === data.next_id || data.id === data.prev_id)) {
			return false
		}

		successCallBack = function (res) {
			Design.disableLoading()
			if (res.code === 0) {
				Design.removeDragTarget()
				Design.get('pTarget').parentNode.insertBefore(Design.get('gTarget'), Design.get('pTarget'))
			} else {
				layui.layer.msg(res.message)
			}
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
					$(Design.get('pTarget')).before(res.data.component_html)
					renderViewPage()
				} else {
					layui.layer.msg(res.message)
				}
			}

			if ($(this.get('pTarget')).prevAll(this.toClassName('lDrag')).length > 0) {
				data.prev_id = $(this.get('pTarget')).prevAll(this.toClassName('lDrag')).get(0).id
			} else {
				data.prev_id = 0
			}

			if (Number($(this.get('gTarget')).data('custom')) === 1) {
				openCustomLayoutDialog($(this.get('pTarget')), data.prev_id, false)

				return false
			}
		} else if (type === 'component') {
			var isUnique = Boolean($(this.get('gTarget')).data('unique'))
			var isExist = $('#design_view [data-unique="true"]').length

			if (isUnique && isExist > 0) {
				layui.layer.msg('此组件只能创建一次！')

				return false
			}

			var uniqueCount = $(this.get('pTarget')).closest(this.toClassName('lDrag')).find('[data-unique="true"]').length

			if (!isUnique && uniqueCount > 0) {
				layui.layer.msg('请将组件放置到其他布局！')

				return false
			}

			var componentCount = $(this.get('pTarget')).closest(this.toClassName('lDrag')).find('.geshop-component-box').length

			if (isUnique && componentCount > 0) {
				layui.layer.msg('请将组件放置到空布局！')

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
			successCallBack = function (res) {
				Design.disableLoading()
				if (res.code === 0) {
					$(Design.get('pTarget')).before(res.data.component_html)
					renderViewPage()
				} else {
					layui.layer.msg(res.message)
				}
			}

			if ($(this.get('pTarget')).prevAll(this.toClassName('cDrag')).length > 0) {
				data.prev_id = $(this.get('pTarget')).prevAll(this.toClassName('cDrag')).get(0).getAttribute('data-id')
			} else {
				data.prev_id = 0
			}
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
	if (width < 200) {
		width = 200
		left = left - 100
	}

	$('.geshop-dragenter-tip-right').removeClass('geshop-dragenter-tip-right')
	$('.geshop-dragenter-tip-wrong').removeClass('geshop-dragenter-tip-wrong')

	target.addClass('geshop-dragenter-tip-' + type)

	$('#geshop_drop_tip_top span').text(message)
	$('#geshop_drop_tip_top').removeClass('geshop-drop-tip-top-right geshop-drop-tip-top-wrong')
	$('#geshop_drop_tip_top').css({
		top: top - 20,
		left: left,
		width: width
	}).addClass('geshop-drop-tip-top-' + type).show()

	$('#geshop_drop_tip_bottom span').text(message)
	$('#geshop_drop_tip_bottom').removeClass('geshop-drop-tip-bottom-right geshop-drop-tip-bottom-wrong')
	$('#geshop_drop_tip_bottom').css({
		top: top + height,
		left: left,
		width: width
	}).addClass('geshop-drop-tip-bottom-' + type).show()
}

Design.hideDropTip = function () {
	$('.geshop-dragenter-tip-right').removeClass('geshop-dragenter-tip-right')
	$('.geshop-dragenter-tip-wrong').removeClass('geshop-dragenter-tip-wrong')
	$('#geshop_drop_tip_top span').text('')
	$('#geshop_drop_tip_top').css({
		top: 0,
		left: 0,
		width: 0
	}).removeClass('geshop-drop-tip-top-right geshop-drop-tip-top-wrong').hide()

	$('#geshop_drop_tip_bottom span').text('')
	$('#geshop_drop_tip_bottom').css({
		top: 0,
		left: 0,
		width: 0
	}).removeClass('geshop-drop-tip-bottom-right geshop-drop-tip-bottom-wrong').hide()
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

	$('#js_toggleLeft').click(function () {
		$('#design_drag').toggleClass('design-left-box-visible')
		$('.design-control').toggleClass('design-control-full')
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
			btn: ['是', '否'],
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

	$('#add_page_stylesheet').click(function () {
		$.get('/activity/design/get-setting', {
			page_id: $('#pageId').val(),
			lang: $('#pageLang').val()
		}, function (res) {
			if (res.code == 0) {
				layui.layer.open({
					title: '编辑页面样式代码',
					btn: ['确认', '取消'],
					area: '640px',
					content: $('#page_stylesheet_template').html(),
					success: function () {
						$('[name=page_custom_css]').val(res.data.custom_css)
						$('[name=page_background_color]').val(res.data.background_color)
						$('[name=page_background_color]').prev().find('div').css('backgroundColor', res.data.background_color)
						$('[name=page_background_image]').val(res.data.background_image)
						if (res.data.background_repeat.length > 0) {
							$('[name=page_background_repeat][value=' + res.data.background_repeat + ']').prop('checked', true)
						}
						if (res.data.background_position.length > 0) {
							$('[name=page_background_position][value=' + res.data.background_position + ']').prop('checked', true)
						}

						layui.form.render()
						renderColorPicker()
					},
					yes: function () {
						var url = '/activity/design/setting'
						var data = {
							page_id: $('#pageId').val(),
							lang: $('#pageLang').val(),
							custom_css: $('[name=page_custom_css]').val(),
							background_color: $('[name=page_background_color]').val(),
							background_image: $('[name=page_background_image]').val(),
							background_repeat: $('[name=page_background_repeat]:checked').val(),
							background_position: $('[name=page_background_position]:checked').val()
						}
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

						Design.postAjax(url, data, successCallBack, errorCallBack)
					},
					btn2: function (index) {
						layui.layer.close(index)
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

	$('#generate_page_model').click(function () {
		layui.layer.open({
			title: '新增页面模板',
			btn: ['确认', '取消'],
			type: '1',
			area: '640px',
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
			btn2: function (index) {
				layui.layer.close(index)
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

	$('#preview_page').click(function () {
		window.open($(this).data('href'))
	})

	$('#view_convert_page').on('change', function () {
		var siteCode = Cookies.get('SITECODE')
		var prefix = siteCode.substring(0, siteCode.indexOf('-'))
		var aimSiteCode = ''
		var suffix = ''
		var target = $(this)
		var link = target.val()

		target.find('option').each(function () {
			if ($(this).attr('value') == link) {
				aimSiteCode = $(this).attr('data-site')
			}
		})

		suffix = aimSiteCode.substring(aimSiteCode.indexOf('-'))

		Cookies.set('SITECODE', prefix + suffix, { expires: 1 })
		location.href = link
	})

	// 重新发布
	$('body').on('click', '.redistribution', function (index) {
		$.post('/activity/design/release', {
			page_id: $('#pageId').val(),
			lang: $('#pageLang').val()
		}, function (res) {
			layui.layer.msg(res.message)
			layui.layer.close(index)
		})
	})

	// 查看访问链接
	$('#view_page').click(function () {
		$.get('/activity/page/get-page-newest-urls', {
			id: $('#pageId').val()
		}, function (res) {
			// Design.disableLoading()
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
							area: '640px',
							content: htmlLang + '<p style="margin-top:20px;"> ' + tips + ' &nbsp;&nbsp; ' + redistribution_btn + '</p>'
						})
					} else {
						layui.layer.open({
							title: '访问链接',
							area: '640px',
							content: htmlLang
						})
					}
				} else {
					layui.layer.open({
						title: '访问链接',
						area: '640px',
						content: res.data.tips + '&nbsp;&nbsp;<button class=\'layui-btn layui-btn-normal redistribution\'>重新发布</button>'
					})
				}
			} else {
				layui.layer.open({
					title: '访问链接',
					area: '640px',
					content: '页面未发布成功，请&nbsp;&nbsp;<button class=\'layui-btn layui-btn-normal redistribution\'>重新发布</button>'
				})
			}
		})
	})

	$('#generate_page').click(function () {
		layui.layer.confirm('确认生成该页面？', {
			btn: ['确认', '取消']
		}, function (index) {
			$.post('/activity/design/release', {
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

								var beenReplaced = $('[data-id=' + sessionStorage.getItem('currentComponentId') + ']')

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
		})
		.on('mouseenter', Design.toClassName('cDrag'), function () {
			if ($('#switch_preview').prop('checked')) {
				return false
			}

			var isUnique = Boolean($(this).data('unique'))

			if ($(this).find('.design-operation-panel').length === 0) {
				$(this).append($('#component_operation_template').html())

				if (isUnique) {
					$(this).find('.geshop-draggable, .js_sortUpComponent, .js_sortDownComponent, .js_copyComponent, .js_pasteInComponent').remove()
				}

				if (!sessionStorage.getItem('copiedComponentId')) {
					$('.js_pasteInComponent').hide()
				} else {
					$('.js_pasteInComponent').show()
				}

				$('#js_componentName').text($('#design_drag [data-key=' + $(this).data('key') + ']').data('name'))
			}

			var layoutDragTarget = $(this).closest(Design.toClassName('lDrag'))

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
		})
		.on('click', '.js_openLayoutForm', function () {
			var id = $(this).closest(Design.toClassName('lDrag')).attr('id')

			Design.enableLoading()
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
		})
		.on('click', '.js_removeLayout', function () {
			var layout = $(this).closest(Design.toClassName('lDrag'))

			layui.layer.confirm('确定删除该布局？', function (index) {
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

			layui.layer.confirm('确认删除该组件？', function (index) {
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
				btn: ['确定', '取消']
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
				btn: ['确定', '取消']
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

	$('.design-form')
		.on('click', '.js_closeDesignForm', function () {
			$(this).closest('.design-form').removeClass('design-form-visible')
			$('.design-form').find('.geshop-form-content').remove()
		})
		.on('click', '#layout_form .js_submitDesignForm', function () {
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
				lang: $('#pageLang').val()
			}
			var successCallBack = function (res) {
				Design.disableLoading()
				if (res.code === 0) {
					var componentTarget = $(Design.toClassName('cDrag') + '[data-id=' + sessionStorage.getItem('currentComponentId') + ']')
					var flag = false

					componentTarget.after(res.data.component_html)
					componentTarget.remove()

					if (res.data.tpl_id != sessionStorage.getItem('currentTemplateId')) {
						flag = true
					}
					openComponentForm(sessionStorage.getItem('currentComponentId'), flag, false)
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

	$(document).on('click', '.js_openResource', function () {
		if (!sessionStorage.getItem('currentResourceId')) {
			sessionStorage.setItem('currentResourceId', 0)
		}

		sessionStorage.setItem('currentInputTargetName', $(this).next('input').attr('name'))

		if (resourceTree.length > 0 && resourceList.length > 0) {
			layui.layer.open({
				maxmin: true,
				type: 1,
				title: '资源管理器',
				area: ['960px', '640px'],
				content: $('#resource_view_template').html(),
				success: function (layero, index) {
					initializeResourceDialog(index, '/admin/resource/list')
				}
			})
		} else {
			renderResourceExploere(0, '/admin/resource/list', true)
		}

		$('#current_resource_folder').text(sessionStorage.getItem('currentResourceName'))
	})

	/**
		 * render resource exploere
		 *
		 * @param {Number} id The tree node id
		 * @param {String} url The url of getting resource data
		 * @param {Boolean} isNeedUpdateTree Flags if needs to update tree
		 */
	function renderResourceExploere (id, url, isNeedUpdateTree) {
		Design.enableLayuiLoading()
		$.get(url, {
			id: id
		}, function (res) {
			Design.disableLayuiLoading()
			if (res.code === 0) {
				var html = []
				var tree = []

				if (res.data.list.length > 0) {
					res.data.list.forEach(function (element) {
						if (Number(element.type) === 0) {
							tree.push({
								id: element.id,
								name: element.name,
								spread: false,
								loaded: false,
								children: [{

								}]
							})
						} else {
							html.push('<div class="layui-col-xs2 geshop-resource-explorer-col" style="text-align: center;">')
							html.push('<p style="position: relative;">')
							html.push('<img src="' + element.thumbnail_url + '" width="100" height="100">')
							html.push('<span style="display: block; position: absolute; left: 6px; right: 6px; bottom: 0; background-color: rgba(0, 0, 0, .5); height: 18px; line-height: 18px; font-size: 12px; color: #ffffff;">' + element.width + 'X' + element.height + '</span>')
							html.push('</p>')
							html.push('<p class="geshop-resource-explorer-title">' + element.name + '</p>')

							html.push('<div class="geshop-resource-operate">')
							html.push('<a href="javascript:;" class="js_useResource" data-url="' + element.url + '" data-width="' + element.width + '" data-height="' + element.height + '">使用</a>')

							html.push('</div>')

							html.push('</div>')
						}
					})
				} else {
					html.push('<div class="layui-col-xs12">')
					html.push('<p>暂无数据</p>')
					html.push('</div>')
				}

				if (resourceTree.length > 0 && Number(sessionStorage.getItem('currentResourceId')) != 0) {
					updateResourceTree(resourceTree, id, tree)
				} else {
					resourceTree = tree
				}

				resourceList = html.join('')

				if (Number(id) === 0 && $('.geshop-resource-explorer').length == 0) {
					layui.layer.open({
						maxmin: true,
						type: 1,
						title: '资源管理器',
						area: ['960px', '640px'],
						content: $('#resource_view_template').html(),
						success: function (layero, index) {
							initializeResourceDialog(index, url)
						}
					})
				} else {
					if (isNeedUpdateTree) {
						$('#geshop_resource_tree').html('')
						layui.tree({
							elem: '#geshop_resource_tree',
							nodes: resourceTree,
							click: function (node) {
								sessionStorage.setItem('currentResourceId', node.id)
								sessionStorage.setItem('currentResourceName', node.name)
								$('#current_resource_folder').text(node.name)

								if (node.spread === false && node.loaded === false) {
									renderResourceExploere(node.id, url, true)
								} else {
									renderResourceExploere(node.id, url, false)
								}
							}
						})
					}

					$('#geshop_resoure_list').html(resourceList)
				}
			}
		})
	}

	/**
		 * update resource tree
		 *
		 * @param {Array} tree The resource tree
		 * @param {Number} id The parent id
		 * @param {Array} children The children tree
		 */
	function updateResourceTree (tree, id, children) {
		var length = tree ? tree.length : 0

		for (var index = 0; index < length; index++) {
			if (tree[index].id == id) {
				if (length > 0) {
					tree[index].children = children
					if (tree[index].spread) {
						tree[index].spread = false
					} else {
						tree[index].spread = true
					}
				}
				tree[index].loaded = true
				break
			} else {
				updateResourceTree(tree[index].children, id, children)
			}
		}
	}

	/**
		 * initialize resource dialog when opened
		 *
		 * @param {Number} index The index number of layer dialog
		 * @param {String} url The url to get resources data
		 */
	function initializeResourceDialog (index, url) {
		$('#geshop_resoure_list').html(resourceList)
		$('#layui-layer' + index + ' .layui-layer-content').css('height', 'calc(100% - 43px)')

		layui.tree({
			elem: '#geshop_resource_tree',
			nodes: resourceTree,
			click: function (node) {
				sessionStorage.setItem('currentResourceId', node.id)
				sessionStorage.setItem('currentResourceName', node.name)
				$('#current_resource_folder').text(node.name)

				renderResourceExploere(node.id, url, true)
			}
		})

		initializeResourceUpload()
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
				Design.enableLayuiLoading()
			},
			done: function (res) {
				if (!sessionStorage.getItem('currentResourceId')) {
					layui.layer.msg('请选择文件夹！')
				}

				Design.disableLayuiLoading()
				if (res.code === 0) {
					renderResourceExploere(sessionStorage.getItem('currentResourceId'), '/admin/resource/list', false)
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
			var target = $('[name=' + sessionStorage.getItem('currentInputTargetName') + ']')
			var index = $(this).closest('.layui-layer-page').attr('times')

			target.val($(this).data('url'))
			if ($('[name=' + target.data('width') + ']').length > 0) {
				$('[name=' + target.data('width') + ']').val($(this).data('width'))
			}
			if ($('[name=' + target.data('height') + ']').length > 0) {
				$('[name=' + target.data('height') + ']').val($(this).data('height'))
			}
			layui.layer.close(index)
		})
		.on('click', '.layui-tree-spread', function () {
			$(this).next('a').trigger('click')
		})
		.on('click', '#create_resource_folder', function () {
			layui.layer.open({
				title: '新增文件夹',
				btn: ['确定', '取消'],
				area: '640px',
				content: $('#resource_folder_creation_template').html(),
				yes: function (index) {
					if ($.trim($('[name=resourceFolderName]').val()).length <= 0) {
						layui.layer.msg('请输入文件夹名称！')

						return false
					}

					Design.enableLoading()

					var url = '/admin/resource/add'
					var data = {
						name: $('[name=resourceFolderName]').val(),
						type: 0,
						url: '',
						parent_id: sessionStorage.getItem('currentResourceId') || 0
					}
					var successCallBack = function (res) {
						Design.disableLoading()
						if (res.code === 0) {
							renderResourceExploere(sessionStorage.getItem('currentResourceId') || 0, '/admin/resource/list', true)
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
				}
			})
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
				$(el).ColorPickerHide()
			}
		})
	}

	document.addEventListener('drag', function () {

	}, false)

	document.addEventListener('dragstart', function (event) {
		event.dataTransfer.setData('text/plain', null)

		var image = new Image(120, 90)

		image.src = '/resources/images/drag-image.png'
		event.dataTransfer.setDragImage(image, 60, 45)

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

	document.addEventListener('dragover', function (event) {
		event.preventDefault()
	}, false)

	document.addEventListener('dragenter', function (event) {
		if ($(event.target).closest('.design-view').length <= 0) {
			return false
		}

		var message, type, target

		if (Design.get('gTarget').className.indexOf(Design.get('lDrag')) !== -1) {
			if ($(event.target).closest(Design.toClassName('lDrag')).length > 0) {
				target = $(event.target).closest(Design.toClassName('lDrag'))

				message = '释放之后，新布局将位于此布局之前'
				type = 'right'
			} else if ($(event.target).closest(Design.toClassName('lDrop')).length > 0) {
				target = $(event.target).closest(Design.toClassName('lDrop'))
				if (target.find(Design.toClassName('lDrag')).length > 0) {
					message = '释放之后，新布局将位于所有布局之后'
				} else {
					message = '释放之后，新布局将添加到此页面'
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
				message = '释放之后，新组件将位于此组件之前'
				type = 'right'
			} else if ($(event.target).closest(Design.toClassName('cDrop')).length > 0) {
				target = $(event.target).closest(Design.toClassName('cDrop'))
				if (target.find(Design.toClassName('cDrag')).length > 0) {
					message = '释放之后，新组件将位于所有组件之后'
				} else {
					message = '释放之后，新组件将添加到此布局'
				}
				type = 'right'
			} else {
				target = $(event.target)
				message = '此位置不能存放新组件'
				type = 'wrong'
			}

			Design.showDropTip(message, target, type)
		}
	}, false)

	document.addEventListener('dragleave', function () {

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
	}, false)
})
