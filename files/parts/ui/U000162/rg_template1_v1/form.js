function onSubmit (progress) {
	var res = /(\s{5,1000})/g;
	var reg = /\n/g;
	var sku = $('[name=goodsSKU]').val().trim().replace(res, ',').replace(reg, ',');
    var skuArr = sku.split(',');
	if (!skuArr) {
		return false
	}
	if (skuArr[skuArr.length - 1] === '') {
		skuArr.pop();
	}
	if (skuArr.length>100) {
		layui.layer.msg('商品sku数量不得多于100');
		return false;
	} else {
        progress.next();
	}
};
