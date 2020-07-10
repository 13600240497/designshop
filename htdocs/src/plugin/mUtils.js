/**
 * defines Utils tools
 *
 * @version 0.0
 * @author wuxingtao@globalegrow.com
 */

import Vue from 'vue';

export const DataTransfer = data => {
	if (!(this instanceof DataTransfer)) {
		return new DataTransfer(data, null, null)
	}
}

//部门数据
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
	Array.from(data).forEach(function (record) {
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
			if (item.parent_id === record.id) {
				item._parent = record
				record.children.push(item)
			}
		})
		tmp.push(record)
	})
	return tmp
}

/* vue-resource common fetch */
export const fetchUrl = (target, url, param, option, callback) => {
	var defaultOp = {
		type: 'get'
	}
	var options = option || defaultOp
	var params = param || {}
	if (options.type === 'get') {
		target.$http.get(url).then(function (res) {
			if (res.body.code === 0) {
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
			if (res.body.code === 0) {
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

/* 设置cookie */
export const setCookie = (name, value, expiredays) => {
	var exdate = new Date()
	exdate.setDate(exdate.getDate() + expiredays)
	document.cookie = name + '=' + escape(value) + (expiredays == null ? ';path=/;' : ';path=/;expires=' + exdate.toGMTString())
}
/* 获取cookie */
export const getCookie = name => {
	var arr
	var reg = new RegExp('(^| )' + name + '=([^;]*)(;|$)')
	if ((arr = document.cookie.match(reg))) return arr[2]
	else return null
}
/* 删除cookie */
export const delCookie = name => {
	var exp = new Date()
	exp.setTime(exp.getTime() - 1)
	var cval = getCookie(name)
	if (cval != null) document.cookie = name + '=' + cval + ';path=/;expires=' + exp.toGMTString()
}


export const extendDeep = (parent, child) => {
	var i,
		toStr = Object.prototype.toString,
		astr = "[object Array]";

	child = child || {};

	for (i in parent) {
		if (parent.hasOwnProperty(i)) {
			if (typeof parent[i] === 'object') {
				child[i] = (toStr.call(parent[i]) === astr) ? [] : {};
				my.extendDeep(parent[i], child[i]);
			} else {
				child[i] = parent[i];
			}
		}
	}
	return child;
}

export const getRequest = () => {
	var url = location.search; //获取url中"?"符后的字串
	var theRequest = new Object();
	if (url.indexOf("?") != -1) {
		var str = url.substr(1),
			strs = str.split("&");
		for (var i = 0; i < strs.length; i++) {
			theRequest[strs[i].split("=")[0]] = unescape(strs[i].split("=")[1]);
		}
	}
	return theRequest;
}

/**
 * @description 根据字段去重
 * @param { Array } originalArr 原始数组
 * @param { String } key 去重字段
 * @returns { Array } 去重后的数组
 */
export const uniqueArray = (originalArr, key) => {
	var n = [originalArr[0]]
	for (var i = 1; i < originalArr.length; i++) {
		if (key === undefined) {
			if (n.indexOf(originalArr[i]) == -1) n.push(originalArr[i])
		} else {
			inner: {
				var has = false
				for (var j = 0; j < n.length; j++) {
					if (originalArr[i][key] == n[j][key]) {
						has = true
						break inner
					}
				}
			}
			if (!has) {
				n.push(originalArr[i])
			}
		}
	}
	return n
}

/**
 * @description 合并端下渠道与语言
 * @param data from pipeline-list res.data
 * pipeline 当前端下渠道
 * @param platArr
 */

export const mapPipeLine = (data,platArr)=>{
    let platform = platArr ? platArr : data.platform;
    let pipeLineAll = {}
    platform.forEach((item)=>{
        let pipeline = data[item].pipeline,
            pipelineEntry = Object.entries(pipeline);
        pipelineEntry.forEach((channelItem)=>{
            const channelName = channelItem[0]
            const channelObj = channelItem[1]
            const channelLanguage = channelObj['language']
            if(!pipeLineAll[channelName]){
                pipeLineAll[channelName] = pipeline[channelName]
                // pipeLineAll[channelItem].language = {}
            }else{
                let currentLanguage = pipeLineAll[channelName]['language']
                pipeLineAll[channelName]['language'] = Object.assign({},currentLanguage,channelLanguage)
            }
        })
    })
    return pipeLineAll
}

/**
 * @description 合并端下渠道与语言
 * @param data from pipeline-list res.data
 * pipeline 当前端下渠道
 * @param platArr
 * @param langlistName 语言列表name名 默认'language'
 */

export const mapPipeLineArr = (data, platArr, langlistName = 'language') => {
	let platform = platArr ? platArr : data.platform;
	let pipeLineAll = {};
	let pipeArr = [];
	platform.forEach((item)=>{
        let pipeline = data[item].pipeline ? data[item].pipeline : data[item],
			pipelineEntry = Object.entries(pipeline);
		pipelineEntry.forEach((channelItem)=>{
			const channelName = channelItem[0]
			const channelObj = channelItem[1]
            const channelLanguage = channelObj[langlistName];
			if(!pipeLineAll[channelName]){
				pipeLineAll[channelName] = pipeline[channelName]
				// pipeLineAll[channelItem].language = {}
			}else{
                let currentLanguage = pipeLineAll[channelName][langlistName];
                pipeLineAll[channelName][langlistName] = Object.assign({}, currentLanguage, channelLanguage);
			}
		})
	})
    Object.keys(pipeLineAll).forEach((item) =>{
        pipeArr.push(pipeLineAll[item])
    })
	return pipeArr
}

/* 获取端下选中的渠道 */
export const getPlatformPageLang = (source) =>{
	let pipeLineArr = []
	let sourceEntry = Object.entries(source)
	sourceEntry.forEach((item) =>{
		// const channelName = item[0]
		const channelObj =  item[1]
		let channelItem = {
			key:channelObj.key,
			name:channelObj.name,
			langList:[]
		}
		Object.entries(channelObj.language).forEach((item,index)=>{
			const langKey = item[0],
				langName = item[1].langName
			channelItem.langList.push({
				"key":langKey,
				"name":langName
			})
		})


		pipeLineArr.push(channelItem)
	})
	return pipeLineArr
}
/**
 * 获取zf有权限渠道,单语言单渠道
 */
export const getHasPermissionChannel = (channel,hasChannel,platArr)=>{
	let pipeLineArr = {},
		channelArr = Object.keys(channel),
		hasChannelArr = Object.keys(hasChannel);
	channelArr.forEach((item,index)=>{
		hasChannelArr.forEach((hasItem,hasIndex)=>{
			if(item === hasItem){
				pipeLineArr[item] = (channel[item])
			}
		})
	})

	return pipeLineArr
}

/**
 *
 * @param obj 来源对象
 * @returns {any}
 * @private
 */
const _cloneParse = (obj)=>{
	return JSON.parse(JSON.stringify(obj))
}
export const clone_simple = _cloneParse;
/**
 * 三端多条空白数据写入
 * @param target
 * @param count
 * @returns {*}
 */
export const tabCountGenerate = (target,count)=> {
	if(!(target instanceof Array)){return target;}
	let result = [target[0]],
		target_item = target[0];
	for(let i=1;i<count;i++){
		result.push(_cloneParse(target_item))
	}
	return result
}

export const deepCopy = (target) => {
	let copyed_objs = [];//此数组解决了循环引用和相同引用的问题，它存放已经递归到的目标对象
	function _deepCopy(target){
		if((typeof target !== 'object')||!target){return target;}
		for(let i= 0 ;i<copyed_objs.length;i++){
			if(copyed_objs[i].target === target){
				return copyed_objs[i].copyTarget;
			}
		}
		let obj = {};
		if(Array.isArray(target)){
			obj = [];//处理target是数组的情况
		}
		copyed_objs.push({target:target,copyTarget:obj})
		Object.keys(target).forEach(key=>{
			if(obj[key]){ return;}
			obj[key] = _deepCopy(target[key]);
		});
		return obj;
	}
	return _deepCopy(target);
}
