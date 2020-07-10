;$(function () {
	// good ad
	var $target = $('.ui-U000092_model3');
	$target.each(function (index, item) {
		var $adItem = $('.gs-list-item-ad', item);
		var liHeight = $adItem.parent().find('.gs-list-item--m').not('.gs-list-item-ad').eq(0).height();
		$adItem.height(liHeight);
	});

	//view more
	if (GBViewMore) {
		$('.ui-U000092_model3 .view-more-btn').on('click',function(){
			var $this = $(this);
			var $ul = $(this).parent().siblings('.gb-list-default').eq(0);
			var shownum = $ul.data('shownum');
			var Lis = $ul.children().each(function(index,item){
				if(index>shownum-1){
					if( index ==$ul.children().length-1){
						if(!$(item).hasClass('isHide')){ // 收缩 
							$('html').scrollTop($ul.data('scrolltop'))
							$this.children('span').text($this.data('moretext'))
							$this.children('i').removeClass('gs-iconfont gs-icon-up icon-arrow_up').addClass('gs-iconfont gs-icon-down1 icon-arrow_down')
						}else{ // 展开
							$ul.data('scrolltop',$('html').scrollTop())
							$this.children('span').text($this.data('lesstext'))
							$this.children('i').removeClass('gs-iconfont gs-icon-down1 icon-arrow_down').addClass('gs-iconfont gs-icon-up icon-arrow_up')
						} 
					}
					$(item).toggleClass('isHide')
				}
			})
		})
	}
});
