$(function () {
    var $form = $('.U000030-form-dataset'),
        $allSelect = $form.find('.all-item').find('.layui-unselect');

    function eachCheck() {
        var isAllCheck = true;
        $form.find('.layui-form-item').each(function(index, item) {
            var checkbox = $(item).find('input[name="nav_menu"]');
            if (checkbox.prop('checked') === false) {
                isAllCheck = false;
                return isAllCheck;
            };
        });
        if (!isAllCheck) {
            $form.find('.check_all').prop('checked', false);
            $allSelect.removeClass('layui-form-checked');
        } else {
            $form.find('.check_all').prop('checked', true);
            $allSelect.addClass('layui-form-checked');
        }
    }
    eachCheck();


    /*复选框 */
    $form.on('click', '.layui-unselect, .layui-unselect span', function(){
        var checkbox = $(this).parents('.layui-form-item').find('input[name="nav_menu"]');
        if (checkbox.prop('checked') === false) {
            if ($allSelect.hasClass('layui-form-checked')) {
                $allSelect.removeClass('layui-form-checked');
            };
            $form.find('.check_all').prop('checked', false);
            return;
        };
        eachCheck();
    });

    /*全选 */
    $form.find('.all-item').on('click', '.layui-unselect, .layui-unselect span', function(){
        var checkbox = $(this).parents('.all-item').find('.check_all');
        var isCheck = checkbox.prop('checked');
        eachAll(isCheck)
    });
    
    function eachAll(isCheck) {
        $form.find('.layui-form-item').each(function(index, item) {
            var checkbox = $(item).find('input[name="nav_menu"]');
            var $unSelect = $(item).find('.layui-unselect');

            if (!isCheck) {
                checkbox.prop('checked', false);
                $unSelect.removeClass('layui-form-checked');
            } else {
                checkbox.prop('checked', true);
                $unSelect.addClass('layui-form-checked');
            }
        });
    };
});