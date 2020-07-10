/**
 * 更新数字索引
 */
function initIndex() {
    $('.datatime-lists-wrap').each(function() {
        var $section = $(this);
        $section.find('li').each(function() {
            var index = parseInt($(this).index()) + 1;
            $(this).find('.loop-index').text(index);
        })
    })
};


/* 排序 */
function timeSort (a, b) {
    var t1_start = new Date(a.timeRange.split(' - ')[0]).getTime();
    var t1_end = new Date(a.timeRange.split(' - ')[1]).getTime();
    var t2_start= new Date(b.timeRange.split(' - ')[0]).getTime();
    var t2_end = new Date(b.timeRange.split(' - ')[1]).getTime();
    if (t2_start > t1_start) {
        return -1;
    }
    if (t2_start < t1_start) {
        return 1;
    }
    if (t1_start == t2_start) {
        return t1_end - t2_end
    }
};

/* 提交表单函数 */
function beforeSubmit(progress) {
    $('.data_time_setting').each(function() {
        var $container = $(this);
        var code = $container.attr('data-for');
        /** 根据code=pc, m, pad 找到隐藏域 */
        var hiddenInput = $container.find('[name="'+code+'_dateTimeArrs"]');
        /** 便利数据 */
        var dateTimeArrs = [];
        $container.find('.datatime-lists-wrap li').each(function(i, item) {
            var dateStartTime = $(item).find('.down-timestamp').attr('data-start'),
                dateEndTime = $(item).find('.down-timestamp').attr('data-end'),
                timeRange = $(item).find('.down-timestamp').val(),
                activityName = $(item).find('.activity-name').val(),
                activityDesc = $(item).find('.activity-desc').val(),
                dateObject = {};
            /*设置日期为必选项*/
            if (timeRange) {
                dateObject = {
                    'timeRange': timeRange,
                    'dateStartTime': dateStartTime,
                    'dateEndTime': dateEndTime,
                    'activityName': activityName,
                    'activityDesc': activityDesc,
                };
                dateTimeArrs.push(dateObject);
            }
        });
        /** 排序，扶植 */
        dateTimeArrs.sort(timeSort);
        hiddenInput.val(JSON.stringify(dateTimeArrs)); 
    });
    progress.next();
};



/** 初始化 */
$(function() {

    initIndex();

    /* 移除绑定事件 */
    $('.up-datetime-item').unbind('click');
    $('.down-datetime-item').unbind('click');
    $('.add-datetime-item').unbind('click');
    $('.delete-datetime-item').unbind('click');

    /** 绑定事件 */
    $('.data_time_setting')
        /* 新增活动模块 */
        .on('click', '.add-datetime-item', function() {
            var $container = $(this).parents('.data_time_setting');
            var clone = $container.find('.tab-template li').clone();
                clone.find('.down-timestamp2').removeClass('down-timestamp2').addClass('down-timestamp');
            $container.find('.datatime-lists-wrap').append(clone);
            initIndex();
            /** 初始化时间控件 */
            downDateInit();
        })
        /* 删除活动模块 */
        .on('click', '.delete-datetime-item', function() {
            var child = $(this).closest('ul').find('li');
            /** 少于0则不删除 */
            if (child.length <= 1) {
                return false
            }
            var target = $(this).closest('li'),
                timeRange = target.find('.down-timestamp').val(),
                activityName = target.find('.activity-name').val(), 
                activityDesc = target.find('.activity-desc').val();

            if (timeRange || activityName || activityDesc) {
                layui.layer.confirm('删除当前数据不可恢复，确定是否删除？', {
                    btn: ['否', '是'],
                    area: '420px',
                    icon: 3,
                    skin: 'element-ui-dialog-class'
                }, function (index) {
                    layui.layer.close(index)
                }, function (index) {	
                    target.remove();
                    initIndex();
                });
            } else {
                target.remove();
                initIndex();
            }
        })
        /** 排序上 */
        .on('click', '.up-datetime-item', function() {

        })
        /** 排序下 */
        .on('click', '.down-datetime-item', function() {

        });
});



