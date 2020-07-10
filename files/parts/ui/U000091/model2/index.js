
;$(function () {
	var $target = $('.ui-U000091_model2');
	$target.each(function(index,item){
		var $adItem = $('.gs-list-item-ad',item);
		var liHeight = $adItem.parent().find('.gs-list-item').not('.gs-list-item-ad').eq(0).height();
		$adItem.height(liHeight);
	})

});
