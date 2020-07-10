/**
 * Common Class of Design
 * 
 * @author 创新研发部前端组
 */
var DesignCommon = {}

/**
 * timestamp
 */
DesignCommon.timestamp = {}

/**
 * validate timestamp
 * 
 * @param {Number} timestamp The timestamp
 * 
 * @returns {Boolean} Validates the param is 'number' or not
 */
DesignCommon.timestamp.validate = function (timestamp) {
	var returns = {}
  
	if (typeof timestamp == 'number') {
		returns.code = 0
		returns.message = 'The value is right.'
	} else {
		returns.code = 1
		returns.message = 'The value is wrong.'
	}

	return returns
}

/**
 * convert the timestamp of Beijing to the timestamp of service (USA)
 * 
 * @param {Number} bjTimestamp The timestamp of Beijing
 * 
 * @returns {Object} The timestamp of service
 */
DesignCommon.timestamp.convertToService = function (bjTimestamp) {
	var validate = DesignCommon.timestamp.validate(bjTimestamp)

	if (validate.code == 0) {
		validate.data = bjTimestamp + 3600 * 13
	} else {
		validate.data = bjTimestamp
	}
  
	return validate
}

/**
 * convert the timestamp of service (USA) to the timestamp of Beijing
 * 
 * @param {Number} serviceTimestamp The timestamp of service
 * 
 * @returns {Object} The timestamp of Beijing
 */
DesignCommon.timestamp.convertToBeijing = function (serviceTimestamp) {
	var validate = DesignCommon.timestamp.validate(serviceTimestamp)

	if (validate.code == 0) {
		validate.data = serviceTimestamp - 3600 * 13
	} else {
		validate.data = serviceTimestamp
	}
  
	return validate
}

/**
 * convert the date string likes '2018-01-01 00:00:00' to '2018/01/01 00:00:00'
 * 
 * @param {String} dateStr The date string
 */
DesignCommon.timestamp.toAppleFormat = function (dateStr) {
	return dateStr.replace(/-/g, '/')
}

/**
 * picture
 */
DesignCommon.color = {}

/**
 * render color picker plugin
 */
DesignCommon.color.renderColorPicker = function () {
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
