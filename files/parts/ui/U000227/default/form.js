/**
 * 更新数字索引
 */
var loopfield = $('.lamp-form-table');

function initItemIndex() {
    $('.lamp-elem-box').find('.lamp-elem-item').each(function(index, item){
        var $this = $(item);
        var listNum = index + 1;
        $this.find('.loop-index').text(listNum);

        if ($this.find('.loop-index').text() == 1) {
            $this.addClass('layui-loop').siblings('.lamp-elem-item').removeClass('layui-loop');
        } 
    })
};

/* 新增模块 */
function addOne() {
    var clone = $('.lamp-tab-template', loopfield).clone(true);
    var len = $('.lamp-form-table').find('.lamp-elem-item').length;
    clone.removeClass('lamp-tab-template layui-hide');
    $('.lamp-elem-box', loopfield).append(clone);
};


/* 提交表单函数 */
function beforeSubmit(progress) {
    var dateTemplateArr = [];
    
    $('.lamp-elem-box').find('.lamp-elem-item').each(function(index, item) {
        var $flag = true,
            $this = $(item),
            base_lampText = $this.find('.base_lampText').val(),
            base_lampLink = $this.find('.base_lampLink').val();

        if (base_lampText == '' && base_lampLink == '') {
            $flag = false
        }
        if ($flag) {
            dateTemplateArr.push({
                'base_lampText': base_lampText,
                'base_lampLink': base_lampLink
            });
        }
    });
    if (dateTemplateArr.length > 0) {
        $('.lamp-form-table input[name=dateTemplateArr]').val(JSON.stringify(dateTemplateArr));
    } else {
        $('.lamp-form-table input[name=dateTemplateArr]').val('');
    }
    progress.next();
}

/* 初始化 */
$(function() {

    /* 绑定事件 */
    $('.lamp-form-table')
        /* 新增模块 */
        .on('click','.lamp-elem-box .class-add', function(e) {
            e.stopPropagation();
            addOne();
            initItemIndex();
        })

        /* 删除模块 */
        .on('click','.lamp-elem-box .class-close', function() {
            var $tr = $(this).parents('.lamp-elem-item:eq(0)');
            layer.confirm('是否删除该分类?', { icon: 3, title: '提示' }, function(index) {
                $tr.remove();
                initItemIndex();
                layer.close(index);
            });
        })

        /* 向上移动 */
        .on('click', '.lamp-elem-box .class-up', function(e) {
            var $tr = $(this).parents('.lamp-elem-item:eq(0)');
            $trPrev = $tr.prev();
            if (0 != $trPrev.length) {
                $trPrev.before($tr);
            }
            initItemIndex();
        })

        /* 向下移动 */
        .on('click','.lamp-elem-box .class-down',function(e){
            var $tr = $(this).parents('.lamp-elem-item:eq(0)');
            $trNext = $tr.next();
            if (0 != $trNext.length) {
                $trNext.after($tr);
            }
            initItemIndex();
        })
});



