
(function(){
	$('.js_freeTips').each(function (i,v) {
		var $freeTips = $(this).find(".free_tipsList");
		var $freeList = $freeTips.find('p');
		$freeTips.height($($freeList [0]).height()*$freeList.length);
		setInterval(function() {
			$freeTips.animate({
				"top": "-1rem"
			}, 500,'linear', function() {
				$(this).css({
						"top":0
				}).find("p:first").appendTo(this)
			});
		}, 5000);
	})
})();