/* 保存前的方法 */
function beforeSubmit(progress) {
    var tabs = $('#U0000226-default .layui-tab-content .layui-tab-item');
    tabs.each(function(){
        var type = $(this).data('type');
        
        var itemData = [];
        $(this).find('.slider-item').each(function(){
            var bannerObj = $(this);
            if(bannerObj.find('input.slider-img').val()){
                itemData.push({
                    imgUrl: bannerObj.find('input.slider-img').val(),
                    imgLink: bannerObj.find('input[name=slider_link]').val()
                });
            }
        });
        if(itemData.length > 0){
            $(tabs).find('input.hidden_data[data-type='+ type +']').val(JSON.stringify(itemData));
        }
        

    });
    progress.next();
}

$(function() {

    
    
    $('body').off('click','.add-class').on('click','.add-class' ,function(){
        
        var dataType = $(this).parent().data('type');
        var currentTab = $('#U0000226-default .layui-tab-content .layui-tab-item[data-type='+ dataType +']');
        var curretnSliders = currentTab.find('.slider-item');
        var flagIndex = curretnSliders.length;
        var clone = currentTab.find('.slider-item').eq(0).clone();
        flagIndex++;
        clone.find('.add-class').attr('type',dataType);
        clone.find('input[name=nav_text]').val('');
        clone.find('input.slider-img').val('');
        clone.find('input.slider-img').attr('name',dataType+'_slider_img'+ flagIndex);
        clone.find('input[name=slider_link]').val('');
        currentTab.append(clone);
        initItemIndex(dataType);
        return false;
    });

    $('body').on('click','.icon-up',function () {
        
        var dataType = $(this).parents('.geshop-third-value').data('type');
        var target = $(this).closest('.slider-item');
        if (target.prev('.slider-item').length > 0) {
        target.prev('.slider-item').before(target.clone());
        target.remove();
        initItemIndex(dataType);
        }
    });

    $('body').on('click', '.icon-down', function () {
        
        var target = $(this).closest('.slider-item');
        var dataType = $(this).parents('.geshop-third-value').data('type');
        if (target.next('.slider-item').length > 0) {
        target.next('.slider-item').after(target.clone());
        target.remove();
        initItemIndex(dataType);
        }
    });

    $('body').on('click', '.icon-delete', function () {
        var dataType = $(this).parents('.geshop-third-value').data('type');
        var imgLen = $('.nav-list-setting .slider-item').length;
        if (imgLen == 1){
            layui.layer.msg('最后一项不能删除');
        }else{
            var target = $(this).closest('.slider-item');
            layui.layer.confirm('确认删除吗？', {
                btn: ['取消', '确定'],
                area: '420px',
                skin: 'element-ui-dialog-class',
                icon: 3,
                title: '提示'
            }, function (index) {
                layui.layer.close(index);
            }, function (index) {
                target.remove();
                initItemIndex(dataType);
            });
        }
    });


    function initItemIndex(type){
        
        var slides = $('#U0000226-default .layui-tab-content .layui-tab-item[data-type='+ type +']').find('.slider-item');
        slides.each(function(index){
            var $this = $(this);
            var listNum = index + 1;
            $this.find('.nav-title').html('图片'+listNum);
        });
    }

  
});