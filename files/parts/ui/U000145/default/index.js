$(function(){
    $('[data-key="U000145"][data-theme="default"]').map(function(i, el) {
        var target = $(el);
        var child = target.find('.text-container');
        var sourceData = [];
        // 获取数据
        child.map(function(i, el) {
            sourceData.push({
                startTimeStamp: $(this).attr('data-starttime'),
                endTimeStamp: $(this).attr('data-endtime'),
            });
        });
        var showItemIndex = 0;
        //
        function showItem() {
            var nowTime = new Date().getTime();
            sourceData.map(function(row, index) {
                var _s = row.startTimeStamp;
                var _e = row.endTimeStamp;
                if (_s != null && _e != null) {
                    if (_s * 1000 <= nowTime && nowTime < _e * 1000) {
                        showItemIndex = index;
                    }
                }
            });
            child.hide().eq(showItemIndex).show();
        }
    
        // 执行一次，解决定时器延时问题
        showItem();
    
        // 定时器开启
        setTimeout(function() {
            showItem()
        }, 1000);
    });

});