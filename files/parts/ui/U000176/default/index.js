
;$(function(){
    $('[data-gid=U000176_default]').each(function(i,element){
        // 移除sold out状态商品
        $(element).find('li.good_soldOut').remove();
        $(element).find(".view-more-btn").on("click",function(){
            var $this = $(this);
            var $ul = $this.parent().siblings('.gb-list-default').eq(0);
            var shownum = $ul.data('shownum');
            $ul.children("li").each(function(index,item){
                if(index>shownum-1){
                    if( index ==$ul.children().length-1){
                        if(!$(item).hasClass('isHide')){ // 收缩 
                            $('html').scrollTop($ul.data('scrolltop'))
                            $this.children('span').text($this.data('moretext'))
                            $this.children('i').removeClass('gs-iconfont gs-icon-up').addClass('gs-iconfont gs-icon-down1')
                        }else{ // 展开
                            $ul.data('scrolltop',$('html').scrollTop())
                            $this.children('span').text($this.data('lesstext'))
                            $this.children('i').removeClass('gs-iconfont gs-icon-down1').addClass('gs-iconfont gs-icon-up')
                        } 
                    }
                    $(item).toggleClass('isHide');
                }
            })
        })
    });
});