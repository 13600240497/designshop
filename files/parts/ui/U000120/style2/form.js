/**
 * 自定义的前置提交函数
 * @param {object} progress 公共提交函数对象
 */
function beforeSubmit(progress) {
    /** 校验 可展示数量的值 是否数字 */ 
    var count = $.trim($('[name="show_count"]').val());
    if (isNaN(parseInt(count)) || parseInt(count) <= 0) {
        layer.msg('请输入为大于1的整数');
        return false;
    };

    var ids = $.trim($('[name="promotion_ids"]').val());
    ids = ids.split(' ').join('');
    $('[name="promotion_ids"]').val(ids);

    if (ids == '') {
        progress.next();
        return false;
    };
    
    var test_url = 'http://www.pc-zaful.com.v0402_geshop.php5.egomsl.com/geshop/goods/fullgiftverify';
    var data = {
        activityids: ids,
        lang: GESHOP_LANG,
        pipeline: (typeof GESHOP_PIPELINE != 'undefined' ? GESHOP_PIPELINE : '')
    };
    var requestData = { content: JSON.stringify(data) };

    $.ajax({
        url: GESHOP_INTERFACE.fullgiftlistverify.url || test_url,
        type: 'GET',
        dataType: 'jsonp',
        data: requestData,
        success: function (res) {
            if (res.code == 0) {
                if (res.data.is_not_exists != '') {
                    var label = '赠品活动ID ' + res.data.is_not_exists + ' 不存在，是否清空？';
                    showConfirm(label, function() {
                        $('[name="promotion_ids"]').val(cleanPromotionID(res.data.is_not_exists));
                    });
                    return;
                }
                if (res.data.is_closed != '') {
                    var label = '赠品活动ID ' + res.data.is_closed + ' 已在后台关闭，是否继续保存？';
                    showConfirm(label, progress.next);
                    return;
                }
                if (res.data.is_ended != '') {
                    var label = '赠品活动ID ' + res.data.is_ended + ' 已结束，是否继续保存？';
                    showConfirm(label, progress.next);
                    return;
                }
                if (res.data.is_ready != '') {
                    var label = '赠品活动ID ' + res.data.is_ready + ' 未开始，是否继续保存？';
                    showConfirm(label, progress.next);
                    return;
                }
                progress.next();
            } else {
                layer.msg(res.message || '校验接口失败');
            }
        },
        error: function () {
            layer.msg('校验接口失败');
        }
    });
}

/** 清空无效的 */
function cleanPromotionID(invalid) {
    var arr1 = invalid.split(',');
    var arr2 = $('[name="promotion_ids"]').val().split(',');
    var reduce = arr2.filter(function(x) {
        return arr1.indexOf(x) < 0;
    });
    return reduce.join(',');
}

/** 展示弹窗 */
function showConfirm(content, next) {
    layer.confirm(content, {
        title: '提示',
        btn: ['否', '是'],
        area: '420px',
        icon: 3,
        skin: 'element-ui-dialog-class'
    }, function (index) {
        layer.close(index);
    }, function () {
        next();
    });
}
