
;(function() {
	turnGoods()
	setInterval( function() { turnGoods() }, 1000)
	

	/* 每隔四个小时切换一次商品 */
	function turnGoods() {
		$('[data-gid=U000001_bigPromote]').each(function(index, element) {

			if (window.localStorage) {
				var nowTime = new Date().getTime();
				var $key = $(element).attr('data-key');
				var $id = $(element).attr('data-id');
				var $fixed_goods_count = parseInt($(element).find('.gs-list-product').attr('data-fixed-g-count'));
				var $default_goods_count = parseInt($(element).find('.gs-list-product').attr('data-default-g-count'));
				var default_g = JSON.parse(localStorage.getItem("geshop_goods_" + $key + "_" + $id));
				if ( default_g ) {
					var default_time = default_g.time;
					var default_skus = default_g.skus;
					var default_count = default_g.default_count;
					var fixed_count = default_g.fixed_count;
					var time_range = Math.floor((nowTime - default_time))/(1000*60*60);
					if ( $default_goods_count == default_count && $fixed_goods_count == fixed_count ) {
						if ( time_range >= 4 ) {
							showGoodsRandom(element);
						}else {
							$(element).find('.goods-item-bigPromote').each(function(index, ele){
								if ( index > $fixed_goods_count - 1 ) {
									$(ele).css("display", "none");
								}
							})
							for (var i = 0; i < default_skus.length; i++) {
								$(element).find('.goods-item-bigPromote').eq(default_skus[i]).css("display", "block");				
							}
						}
						
					}else {
						default_time_sku(element)
					}
				}else{
					 default_time_sku(element)
				}
			} else {
				setInterval( function() {
					showGoodsRandom(element)
				}, 14400000)
			}
		})
	}


/* 获取不重复的任意值 */ 
function createRandom(num, from, to){
    var arr=[];
    if ( from <= to && num >= 0 ) {
	    for(var i=from;i<=to;i++)
	        arr.push(i);
	    arr.sort(function(){
	        return 0.5-Math.random();
	    });
	    if (num > to + 1 - from) {
	    	arr.length=to + 1 - from;
	    }else {
	    	arr.length=num;
	    }
    }
    return arr;
}

/* 随机显示商品 */
function showGoodsRandom(e) {
	var $default_goods_count = parseInt($(e).find('.gs-list-product').attr('data-default-g-count'));
	var $fixed_goods_count = parseInt($(e).find('.gs-list-product').attr('data-fixed-g-count'));
	var $key = $(e).attr('data-key');
	var $id = $(e).attr('data-id');
	var $total = parseInt($(e).find('.gs-list-product').attr('data-total'));
	var goodsArray = createRandom($default_goods_count - $fixed_goods_count, $fixed_goods_count, $total -1);
	var nowTime = new Date().getTime();
	var default_goods = {
		time: nowTime,
		skus: goodsArray,
		default_count: $default_goods_count,
		fixed_count: $fixed_goods_count,
	};
	localStorage.setItem("geshop_goods_" + $key + "_" + $id, JSON.stringify(default_goods));
	if ($default_goods_count < $fixed_goods_count) {
		$(e).find('.goods-item-bigPromote').css("display", "none");
		for (var i = 0; i < $default_goods_count; i++) {
			$(e).find('.goods-item-bigPromote').eq(i).css("display", "block");
		}
		return false;
	}
	$(e).find('.goods-item-bigPromote').each(function(index, ele){
		if ( index > $fixed_goods_count - 1 ) {
			$(ele).css("display", "none");
		}
	})
	for (var i = 0; i < goodsArray.length; i++) {
		$(e).find('.goods-item-bigPromote').eq(goodsArray[i]).css("display", "block");
	}	
} 

/* 首次加载存储时间和默认sku */
function  default_time_sku(e) {
	var $default_goods_count = parseInt($(e).find('.gs-list-product').attr('data-default-g-count'));
	var $fixed_goods_count = parseInt($(e).find('.gs-list-product').attr('data-fixed-g-count'));
	var $key = $(e).attr('data-key');
	var $id = $(e).attr('data-id');
	var $total = parseInt($(e).find('.gs-list-product').attr('data-total'));
	var init_time = parseInt($(e).find('.gs-list-product').attr('data-init-time'));
	var default_goods, goodsArray = [];
	if ( $default_goods_count ) {
	    for(var i=$fixed_goods_count;i<=$total - 1;i++) {
	        goodsArray.push(i)
	    }

	    goodsArray.length=$default_goods_count - $fixed_goods_count;
		default_goods = {
			time: init_time,
			skus: goodsArray,
			default_count: $default_goods_count,
			fixed_count: $fixed_goods_count,
		};
		localStorage.setItem("geshop_goods_" + $key + "_" + $id, JSON.stringify(default_goods));
		$(e).find('.goods-item-bigPromote').each(function(index, ele){
			if ( index > $fixed_goods_count - 1 ) {
				$(ele).css("display", "none");
			}
		})
		for (var i = 0; i < goodsArray.length; i++) {
			$(e).find('.goods-item-bigPromote').eq(goodsArray[i]).css("display", "block");				
		}
	}

}

})();