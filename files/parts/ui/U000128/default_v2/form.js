/**
 * 初始化每个ITEM的数字下标，从1开始
 */
function initItemIndex () {
    $('.goods-tab-lists').find('.goods-tab-item').each(function (index) {
        var $this = $(this);
        $this.find('.title-index').text(index + 1);
    });
}

/**
 * 自定义的前置提交函数
 * @param {object} progress 公共提交函数对象
 */
function beforeSubmit(progress) {
    var ids = [];
    $('#dataIDs .goodsIDitem').each(function(index, item) {
        var id = $.trim($(item).val());
        $(item).val(id);
        id != '' && ids.push(id);
    });

    if (ids.length <= 0) {
        progress.next();
        return false;
    };

    var test_url = 'http://www.pc-zaful.com.v0402_geshop.php5.egomsl.com/geshop/goods/fullgiftverify';
    var data = {
        activityids: ids.join(','),
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
                        cleanPromotionID(res.data.is_not_exists);
                    });
                    return;
                }
                if (res.data.is_closed != '') {
                    var label = '赠品活动ID ' + res.data.is_closed + ' 已在后台关闭，是否继续保存？';
                    showConfirm(label, function() {
                        var ids = get_promotion_ids();
                        $('#goodsIDs input[name="goodsID"]').val(JSON.stringify(ids));
                        $('#goodsIDs input[name="goodsIDnum"]').val(ids.length);
                        progress.next();
                    });
                    return;
                }
                if (res.data.is_ended != '') {
                    var label = '赠品活动ID ' + res.data.is_ended + ' 已结束，是否继续保存？';
                    showConfirm(label, function() {
                        var ids = get_promotion_ids();
                        $('#goodsIDs input[name="goodsID"]').val(JSON.stringify(ids));
                        $('#goodsIDs input[name="goodsIDnum"]').val(ids.length);
                        progress.next();
                    });
                    return;
                }
                if (res.data.is_ready != '') {
                    var label = '赠品活动ID ' + res.data.is_ready + ' 未开始，是否继续保存？';
                    showConfirm(label, function() {
                        var ids = get_promotion_ids();
                        $('#goodsIDs input[name="goodsID"]').val(JSON.stringify(ids));
                        $('#goodsIDs input[name="goodsIDnum"]').val(ids.length);
                        progress.next();
                    });
                    return;
                }
                var ids = get_promotion_ids();
                $('#goodsIDs input[name="goodsID"]').val(JSON.stringify(ids));
                $('#goodsIDs input[name="goodsIDnum"]').val(ids.length);
                progress.next();
            } else {
                layer.msg(res.message || '校验接口失败');
            }
        },
        error: function (err) {
            layer.msg('校验接口失败');
        }
    });
}

/* 获取所有的ID */
function get_promotion_ids() {
	var ids = [];
	$('#dataIDs .goodsIDitem').each(function(index, item) {
        var x = $(item).val();
        ids.push(x);
    });
	return ids;
}

/** 清空无效的 */
function cleanPromotionID(invalid) {
    var arr1 = invalid.split(',');
    $('#dataIDs .goodsIDitem').each(function(index, item) {
        var x = $(item).val();
        if (arr1.indexOf(x) >= 0) {
            $(item).val('');
        }
    });
}

/**
 * 展示弹窗函数
 * @param {string} content 展示的文案
 * @param {function} next 确认的回调函数
 */
function showConfirm(content, next) {
    layer.confirm(content, {
        title: '提示',
        btn: ['否', '是'],
        area: '420px',
        icon: 3,
        skin: 'element-ui-dialog-class'
    }, function (index) {
        layer.close(index);
    }, function (index) {
        next();
    });
}

$(function () {
	/* 回填RG站点金额字段 */
	var RG_GoodsPriceArrs = $('#rg_goodsPriceArrs').find('li');
	if (RG_GoodsPriceArrs.length) {
		RG_GoodsPriceArrs.each(function(i, element) {
			$('.goods-tab-lists.radio-tab-true').find('input.goodsPriceitem:eq('+i+')').val($(element).text());
		});
	};

	var idIndex = $(".goods-tab-item").length;

	/*新增分类 */
	$("#dataIDs").on('click', '.goods-tab-item .class-add', function (e) {
		e.stopPropagation();
		idIndex++;
		$("#dataIDs").append(`<div class="goods-tab-item goods-tab-item${idIndex}">
			<div class="layui-form-item">
				<label class="layui-form-label">满赠数据<span class="title-index">${idIndex}</span> ID</label>
				<div class="layui-input-block">
						<input class="layui-input goodsIDitem" data-ids="false"  autocomplete="off"  value="">
				</div>				
			</div>
			<div class="geshop-third-value mr-10">
				<span class="img-btn class-up">
					<i class='icon-up'></i>
					<b class="tips">上移</b>
				</span>
				<span class="img-btn class-down">
					<i class='icon-down'></i>
					<b class="tips">下移</b>
				</span>
				<span class="img-btn class-close">
					<i class='icon-delete' data='${idIndex}'></i>
					<b class="tips">删除</b>
				</span>
				<span class="img-btn class-add">
					<i class='icon-add'></i>
					<b class="tips">新增</b>
				</span>
				 
			</div>
		</div>`);
		initItemIndex();
	});

	/*删除 */
	$("#dataIDs").on('click','.goods-tab-item .class-close',function(e){
		var idnum = $(this).find('.icon-delete').attr('data');

		layer.confirm('是否删除该满赠数据?',{icon:3,title:'提示'},function(index){

			$('.goods-tab-item'+idnum).remove();

			layer.close(index);
		});

	});

	/*下移 */
	$("#dataIDs").on('click','.goods-tab-item .class-down',function(e){
		 var $this = $('this'),
		$tr = $(this).parents('.goods-tab-item:eq(0)'),
		$trNext = $tr.next();
        if (0 != $trNext.length) {
            $trNext.after($tr);
        }
		initItemIndex();
	});
	/*上移 */
	$("#dataIDs").on('click','.goods-tab-item .class-up',function(e){
		 var $this = $('this'),
		$tr = $(this).parents('.goods-tab-item:eq(0)'),
		$trPrev = $tr.prev();
		if(0 != $trPrev.length){
			$trPrev.before($tr);
		}
		initItemIndex();
	});
});
