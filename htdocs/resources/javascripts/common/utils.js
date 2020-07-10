/**
 * defines Utils tools
 *
 * @version 0.0
 * @author wuxingtao@globalegrow.com
 */



function DataTransfer (data) {
	if (!(this instanceof DataTransfer)) {
		return new DataTransfer(data, null, null)
	}
}

DataTransfer.treeToArray = function (data, parent, level, expandedAll) {
	var tmp = []
	Array.from(data).forEach(function (record) {
		if (record._expanded === undefined) {
			Vue.set(record, '_expanded', expandedAll)
		}
		if (parent) {
			Vue.set(record, '_parent', parent)
		}
		var _level = 0
		if (level !== undefined && level !== null) {
			_level = level + 1
		}
		Vue.set(record, '_level', _level)
		tmp.push(record)
		if (record.children && record.children.length > 0) {
			var children = DataTransfer.treeToArray(record.children, record, _level, expandedAll)
			tmp = tmp.concat(children)
		}
	})
	return tmp
}

DataTransfer.treeToArray1 = function (data, parent, level, expandedAll) {
	var tmp = []
	Array.from(data).forEach(function (record, index) {
		if (record._expanded === undefined) {
			Vue.set(record, '_expanded', expandedAll)
		}
		if (parent) {
			Vue.set(record, '_parent', parent)
		}
		// var _level = 0;
		// if (level !== undefined && level !== null) {
		//     _level = level + 1
		// }
		Vue.set(record, '_level', record.treeInfo.level - 1)
		Vue.set(record, 'children', [])
		data.forEach(function (item) {
			if (item.parent_id == record.id) {
				item._parent = record
				record.children.push(item)

			}
		})
		tmp.push(record)

	})
	return tmp
}


/* vue-resource common fetch */
function fetchUrl (target, url, params, options, callback) {
	// var defaultOp = {
	// 	type: 'get'
	// }
	// var options = options || defaultOp
	// var params = params || {}
	if (options.type == 'get') {
		target.$http.get(url).then(function (res) {
			if (res.body.code == 0) {
				target.$message(res.body.message)
				if (callback) {
					callback(res)
				}
			} else {
				target.$message(res.body.message)
			}
		})
	} else {
		target.$http.post(url, params).then(function (res) {
			if (res.body.code == 0) {
				target.$message(res.body.message)
				if (callback) {
					callback(res)
				}
			} else {
				target.$message(res.body.message)
			}
		})
	}

}
