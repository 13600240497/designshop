; $(function () {
	$('[data-gid="U000139_default"]').each(function (i, element) {
	// /* 添加购物车 */
	var addCard = $(element).find(".js_addSale");
	addCard.on('click', function () {
        var $this = $(this);
        var activeType = $this.attr('data-activeType');
		var goodsSn = $(element).find(".gb_goodsSn");
		var stockNum = $(element).find(".gb_stockNum");
        var qty = $this.attr('data-qty');
        var warehousecode = $(element).find(".gb_warehousecode");
        
        var goodsType = activeType ? 2 : 1;   //主商品 0 //配件 1 赠品2
        var source = $this.attr('data-suorce'); 
        var reqDataArr = [];
        for(var index = 0 ; index < goodsSn.length; index++){
			if(stockNum.eq(index).val() > 0){
				var reqItem = {
					goodsSn:goodsSn.eq(index).val(),
					qty:qty,
					warehouseCode:warehousecode.eq(index).val(),
					goodsType: !index ?  0 : goodsType,
					activityId:0,                                       //营销id,默认0
					mainGoodsSn:index ? goodsSn.eq(0).val() : "",  //配件商品传,默认为空;
					ciphertext:"",                                      //邮箱加密,默认空;
					source:source,
				};
				reqDataArr.push(reqItem);
			}
        }
        var imgSrc = $this.attr('data-img');	
		GESHOP.addToCart({
			reqData: reqDataArr,
			animation: {
            imgSrc: imgSrc,
			origin: $this,
			}
		});
        });

	
	});
});
