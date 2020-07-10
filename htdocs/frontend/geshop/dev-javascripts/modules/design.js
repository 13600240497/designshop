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
 *  documentObject: the object of window.document
 */
var Design = {
	gTarget: '',
	pTarget: '',
	lDrag: 'layout-drag',
	lDrop: 'layout-drop',
	cDrag: 'component-drag',
	cDrop: 'component-drop',
	loadingIndex: '',
	documentObject: window.document,
    site_code: getCookie('site_group_code')
}

var url_code = 'zf' // 三站统一 默认统一成zf站点下的接口

/**
 * getCookie
 * **/
function getCookie(name) {
    var arr
    var reg = new RegExp('(^| )' + name + '=([^;]*)(;|$)')
    if ((arr = document.cookie.match(reg))) return arr[2]
    else return null
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
	$('#component_form_dialog', Design.documentObject).hide()
	$('#component_form_dialog').hide()
}

/**
 * remove tip
 */
Design.removeTip = function () {
	$('#component_layout_add_position_tips', Design.documentObject).remove()
}

Design.addBackGroundColor = function () {
	$('.geshop-drop-tip-center-right').css({ backgroundColor: 'rgba(30,159,255,0.2)' })
}

Design.removeBackGroundColor = function () {
	$('.geshop-drop-tip-center-right').css({ backgroundColor: '#ffffff' })
}

Design.layoutTitleOpacity = function () {
	$('.design-preview-layout-title', Design.documentObject).css({ opacity: 1 })
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
			url = '/activity/'+ url_code +'/layout-design/move-layout'
			data = {
				page_id: $('#pageId').val(),
				id: $(this.get('gTarget')).attr('id'),
				lang: $('#pageLang').val()
			}

			var layout_prev_id = $('#component_layout_add_position_tips', Design.documentObject).prevAll('.geshop-layout-box').attr('id')
			data.prev_id = (typeof layout_prev_id === 'undefined') ? 0 : layout_prev_id

			if (data.id === data.prev_id) {
				return false
			}
		} else if (type === 'component') {
			url = '/activity/'+ url_code +'/ui-design/move-ui'
			data = {
				page_id: $('#pageId').val(),
				id: $(this.get('gTarget')).data('id'),
				layout_id: $(this.get('pTarget')).closest(this.toClassName('lDrag')).attr('id'),
				position: $(this.get('pTarget')).closest('[data-position]').data('position'),
				lang: $('#pageLang').val()
			}

			var cmp_prev_id = $('#component_layout_add_position_tips', Design.documentObject).prevAll('.geshop-component-box').attr('data-id')
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
			Design.renderLayoutTitle()
		}
	} else {
		if (type === 'layout') {
			url = '/activity/'+ url_code +'/layout-design/add-layout'
			data = {
				page_id: $('#pageId').val(),
				component_key: $(this.get('gTarget')).data('key'),
				lang: $('#pageLang').val()
			}
			successCallBack = function (res) {
				Design.disableLoading()
				if (res.code === 0) {
					if (sessionStorage.layout_position === '0' || sessionStorage.layout_position === '1') {
						$('#component_layout_add_position_tips', Design.documentObject).before(res.data.component_html)
					} else {
						$(Design.get('pTarget')).append(res.data.component_html)
					}

					Design.renderViewPage()
				} else {
					layui.layer.msg(res.message)
				}
				Design.removeTip()
				Design.renderLayoutTitle()
			}

			layout_prev_id = $('#component_layout_add_position_tips', Design.documentObject).prevAll('.geshop-layout-box').attr('id')
			data.prev_id = (typeof layout_prev_id === 'undefined') ? 0 : layout_prev_id

			if (Number($(this.get('gTarget')).data('custom')) === 1) {
				Design.openCustomLayoutDialog($(this.get('pTarget')), data.prev_id, true)

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

			url = '/activity/'+ url_code +'/ui-design/add-ui'
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
                    // 因为d网装修页是 iframe ，所以要通过 postMessage 消息通知传输数据
                    window.postMessage && window.postMessage({
                        data: window.GESHOP_ASYNC_DATA_INFO,
                        message_type: 'dresslily_update_goods_info'
                    },window.location.origin);

					if (sessionStorage.position === '0' || sessionStorage.position === '1') {
						$('#component_layout_add_position_tips', Design.documentObject).before(res.data.component_html)
					} else {
						$(Design.get('pTarget')).append(res.data.component_html)
					}

					Design.renderViewPage()
				} else {
					layui.layer.msg(res.message)
				}
				Design.removeTip()
				Design.renderLayoutTitle()
			}

			cmp_prev_id = $('#component_layout_add_position_tips', Design.documentObject).prevAll('.geshop-component-box').attr('data-id')
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
			url = '/activity/'+ url_code +'/layout-design/move-layout'
			data = {
				page_id: $('#pageId').val(),
				id: $(this.get('gTarget')).attr('id'),
				lang: $('#pageLang').val()
			}

			var layout_prev_id = $('#component_layout_add_position_tips', Design.documentObject).prevAll('.geshop-layout-box').attr('id')
			data.prev_id = (typeof layout_prev_id === 'undefined') ? 0 : layout_prev_id

		} else if (type === 'component') {
			url = '/activity/'+ url_code +'/ui-design/move-ui'
			data = {
				page_id: $('#pageId').val(),
				id: $(this.get('gTarget')).data('id'),
				layout_id: $(this.get('pTarget')).closest(this.toClassName('lDrag')).attr('id'),
				position: $(this.get('pTarget')).closest('[data-position]').data('position'),
				lang: $('#pageLang').val()
			}

			var cmp_prev_id = $('#component_layout_add_position_tips', Design.documentObject).prevAll('.geshop-component-box').attr('data-id')
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
			Design.renderLayoutTitle()
		}
	} else {
		if (type === 'layout') {
			url = '/activity/'+ url_code +'/layout-design/add-layout'
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

					Design.renderViewPage()

				} else {
					layui.layer.msg(res.message)
				}
				Design.removeTip()
				Design.renderLayoutTitle()
			}

			layout_prev_id = $('#component_layout_add_position_tips', Design.documentObject).prev('.geshop-layout-box').attr('id')
			data.prev_id = (typeof layout_prev_id === 'undefined') ? 0 : layout_prev_id

			if (Number($(this.get('gTarget')).data('custom')) === 1) {
				Design.openCustomLayoutDialog($(this.get('pTarget')), data.prev_id, false)

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

			url = '/activity/'+ url_code +'/ui-design/add-ui'
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

					Design.renderViewPage()
				} else {
					layui.layer.msg(res.message)
				}
				Design.removeTip()
				Design.renderLayoutTitle()
			}

			var cmp_prev_id = $('#component_layout_add_position_tips', Design.documentObject).prevAll('.geshop-component-box').attr('data-id')
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

	$('.geshop-dragenter-tip-right', Design.documentObject).removeClass('geshop-dragenter-tip-right')
	$('.geshop-dragenter-tip-wrong', Design.documentObject).removeClass('geshop-dragenter-tip-wrong')

	target.addClass('geshop-dragenter-tip-' + type)

	$('#geshop_drop_tip_center p', Design.documentObject).text(message)
	$('#geshop_drop_tip_center', Design.documentObject).removeClass('geshop-drop-tip-center-right geshop-drop-tip-center-wrong')
	$('#geshop_drop_tip_center', Design.documentObject).css({
		top: top,
		left: left,
		width: width,
		height: height,
		fontSize: '14px'
	}).addClass('geshop-drop-tip-center-' + type).show()

}

Design.hideDropTip = function () {
	$('.geshop-dragenter-tip-right', Design.documentObject).removeClass('geshop-dragenter-tip-right')
	$('.geshop-dragenter-tip-wrong', Design.documentObject).removeClass('geshop-dragenter-tip-wrong')

	$('#geshop_drop_tip_center p', Design.documentObject).text('')
	$('#geshop_drop_tip_center', Design.documentObject).css({
		top: 0,
		left: 0,
		width: 0,
		height: 0
	}).removeClass('geshop-drop-tip-center-right geshop-drop-tip-center-wrong').hide()
}

Design.getAjax = function (url, params) {
	Design.enableLoading()
	return $.ajax({
		type: 'GET',
		url: url,
		data: params,
		dataType: 'json',
		error: function () {
			layui.layer.msg('接口异常,请稍后重试')
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
 * 时间戳转日期
 * @param { int } time - 时间戳
 */
Design.dateConverse = function (time) {
	var t = new Date(time),
		year = t.getFullYear(),
		month = t.getMonth() + 1,
		date = t.getDate(),
		hour = t.getHours(),
		minutes = t.getMinutes(),
		second = t.getSeconds()

	return year + '-' + month + '-' + date + ' ' + hour + ':' + minutes + ':' + second
}

/**
 * 生成表单配置文件
 * @param {Number} is_public
 * @param {Number} is_m 是否转m
 * @param {Number} is_app 是否转app
 */
Design.generageFormConfigFile = function (option) {
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

/**
 * @description getComponentFormData
 */
Design.getComponentFormData = function () {
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
 * openComponentForm
 *
 * @param {Number} componentId The component id
 * @param {Boolean} isNewTemplate Flag that is new template
 * @param {Boolean} isNewForm Flag that is new form
 */
Design.openComponentForm = function (componentId, isNewTemplate, isNewForm) {
	if (!isNewForm && !isNewTemplate) {
		return false
	}

	Design.enableLoading()
    Design.componentFormShow();
    $.ajax({
        url: '/activity/'+ url_code +'/ui-design/get-form',
        type: 'GET',
        data: {
            page_id: $('#pageId').val(),
            id: componentId,
            lang: $('#pageLang').val(),
            tpl_id: sessionStorage.getItem('currentTemplateId')
        },
        success: function (res) {
            Design.disableLoading();
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
                        var url = '/activity/'+ url_code +'/ui-design/save-form'
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
                                Design.openComponentForm(sessionStorage.getItem('currentComponentId'), true, true)
                            }

                            layui.layer.msg(res.message)
                        }

                        var errorCallBack = function (res) {
                            Design.disableLoading()
                            Design.disableLayuiLoading()
                            layui.layer.msg(res.message)
                        }

                        var formData = Design.getComponentFormData()

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
                    Design.renderColorPicker()
                    sessionStorage.setItem('currentComponentId', componentId)
                    sessionStorage.setItem('currentTemplateId', res.data.tpl_id)
                }, time)
            } else {
                layui.layer.msg(res.message)
            }
        },
        error: function (err) {
            // 关闭弹层
            Design.disableLoading();
            Design.componentFormHide();
            layui.layer.msg('Error Code: ' + err.status);
        }
    });
}

/**
 * openCustomLayoutDialog
 *
 * @param {Object} target The dropped target
 * @param {Number} prevId The prev id
 * @param {Number} nextId The next id
 * @param {Boolean} isDroppedInParent The flag that is dropped in parent
 */
Design.openCustomLayoutDialog = function (target, prevId, isDroppedInParent) {
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

			var url = '/activity/'+ url_code +'/layout-design/add-custom-layout'
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
				Design.renderLayoutTitle()
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
		Design.renderCustomLayout()
	})

	layui.form.on('radio(column)', function (data) {
		if (Number(data.value) === 0) {
			$('[data-type="column"]').show()
		} else {
			$('[data-type="column"]').hide()
		}
		Design.renderCustomLayout()
	})

	layui.form.on('radio(type)', function () {
		Design.renderCustomLayout()
	})

	$(document)
		.on('change', '[name="customWidth"]', function () {
			Design.renderCustomLayout()
		})
		.on('change', '[name="customColumn"]', function () {
			Design.renderCustomLayout()
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
 * render color picker plugin
 */
Design.renderColorPicker = function () {
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

/**
 * renderCustomLayout
 */
Design.renderCustomLayout = function () {
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

Design.renderDatetime = function () {
	$('.js_start_time').each(function () {
		var _this = this
		layui.laydate.render({
			elem: _this
			, type: 'datetime'
			, done: function (value) {
				var start_time = Date.parse(new Date(value))
				var end_time = Date.parse(new Date($(_this).nextAll('.js_end_time').val()))
				if (start_time >= end_time) {
					layui.layer.msg('开始时间不能大于结束时间', { time: 5000 })
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
					layui.layer.msg('开始时间不能大于结束时间', { time: 5000 })
					return false
				}
			}
		})
	})
}

/**
 * 添加布局后加上title提示
 */
Design.renderLayoutTitle = function () {
	$('.geshop-layout-box', Design.documentObject).each(function () {
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

/**
 * renderViewPage
 */
Design.renderViewPage = function () {
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
 * initialize resource dialog when opened
 *
 * @param {Number} index The index number of layer dialog
 */
Design.initializeResourceDialog = function (index) {
	$('#geshop_resoure_list').html(resourceList)

	if (index !== false) {
		$('#layui-layer' + index + ' .layui-layer-content').css('height', 'calc(100% - 63px)')
	}

	if (resourceTree.length > 0) {
		Design.renderResourceTree()
	} else {
		$.get('/admin/resource/folder-tree', function (res) {
			resourceTree = res.data
			Design.renderResourceTree()
		})
	}

	Design.initializeResourceUpload()
}

/**
 * initialize resource upload function
 */
Design.initializeResourceUpload = function () {
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
				Design.renderResourceExploere(sessionStorage.getItem('currentResourceId'), '/admin/resource/list')
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

/**
 * render resource exploere
 *
 * @param {Number} id The tree node id
 * @param {String} url The url of getting resource data
 */
Design.renderResourceExploere = function (id, url) {
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
						Design.initializeResourceDialog(index)
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
 * render resource tree
 */
Design.renderResourceTree = function () {
	$.jstree.destroy()
	$('#geshop_resource_tree')
		.on('changed.jstree', function (e, data) {
			if (data.node) {
				sessionStorage.setItem('currentResourceId', data.node.id)
			}
			$('#empty_tip').hide()
			Design.renderResourceExploere(data.node.id, '/admin/resource/list')
		})
		.jstree({
			core: {
				data: resourceTree
			}
		})
}

export default Design
