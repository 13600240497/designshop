;$(function () {
	// good ad
	var $target = $('.ui-U000092_model2');
	$target.each(function (index, item) {
		var $adItem = $('.gs-list-item-ad', item);
		var liHeight = $adItem.parent().find('.gs-list-item--m').not('.gs-list-item-ad').eq(0).height();
		$adItem.height(liHeight);
	});

	//view more
	if (GBViewMore) {
		$target.each(function (i, element) {
			GBViewMore(element);
		});
	}
});
