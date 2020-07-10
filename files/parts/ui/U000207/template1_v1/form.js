var $id = $('#U0000207-template1_v1');
var helper = {

    /** 时间排序 */
    compareTime: function (property) {
        return function(a,b){
            var value1 = a[property],
            value2 = b[property];
            return value1 - value2;
        }
    },

    /** 更新 dom 的排序 */
    initIndex: function () {
        $('.tab-lists-wrap li',$id).each(function() {
            var index = parseInt($(this).index()) + 1;
            $(this).find('.loop-index').text(index);
            $(this).find('.down-timestamp').removeAttr('lay-key');
        });
    },
    
    /**
     * 获取每个秒杀模块的DOM
     * @return {array} 模块对应的 jQuery DOM 对象
     */
    get_block_element: function () {
        var list = [];
        $('#form_carousel .tab-lists-wrap li').each(function() {
            var item = $(this);
            list.push(item);
        });
        return list;
    },
    
    /** 检查时间是否为空 */
    check_time_empty: function () {
        /** 校验是否通过 */
        var pass = true;
        /** DOM列表 */
        var dom_list = this.get_block_element();
        dom_list.map(function (element) {
            var time = $(element).find('.down-timestamp').val();
            /** 只要有1个为空，也不通过 */
            if (!time) pass = false;
        });
        return pass;
    },
    
    /** 检查 SKU 是否为空 */
    check_skus_empty: function () {
        /** 校验是否通过 */
        var pass = true;
        /** DOM列表 */
        var dom_list = this.get_block_element();
        dom_list.map(function (element) {
            var skus = $(element).find('textarea[name=goodsSKU]').val();
            /** 只要有1个为空，也不通过 */
            if (!skus) pass = false;
        });
        return pass;
    }
};

/** 提交函数 */
function beforeSubmit (progress) {
    /* 检查单个tabsku个数据 */
    var skuLimit = true;
    $('.tab-lists-wrap .goods-tab-item',$id).each(function(index,item){
        var skuArr = $(item).find('textarea[name=goodsSKU]').val().split(',');
        if(skuArr.length >100){
            layui.layer.msg('SKU最多输入100个');
            skuLimit = false;
            return false;
        }
    });
    if(!!!skuLimit){
        return false;
    }
    
    /** 检查时间格式 */
    if (helper.check_time_empty() === false || helper.check_skus_empty() === false) {
            layui.layer.msg('秒杀时间及商品sku为必填');
        return false;
    }

    /** 遍历组装数据 */
    var dom_list = helper.get_block_element();
	var goodsArr = [];
	var goodsSortArr = [];
    /** 是否展示已经结束的秒杀内容 */
    var tab_endShowActive = $(".design-form  [name=tab_endShowActive]:checked").val();
    
    /** 遍历组装数据 */
	dom_list.map(function(item) {
		var skus = $(item).find('textarea').val();
		var $time = $(item).find('.down-timestamp');
        var timeRange = $time.val();
        var startTime = $time.attr('data-start');
        var endTime = $time.attr('data-end');

        /** 数据格式 */
        var data = {
            'timeRange': timeRange,
            "lists": skus,
            'dataStartTime': startTime,
            'dataEndTime': endTime
        };

        /** 所有的数据 */
        goodsArr.push(data);
        
        /** 过滤删除已结束的数据 */
        tab_endShowActive == '0' && goodsSortArr.push(JSON.parse(JSON.stringify(data)));
	});

    /** 兼容空数据的问题 */
    goodsArr.length < 1 && goodsArr.push({});

    /** 保存表单 */
    $('#form_carousel input[name=goodsSKUTab]:eq(0)').val(JSON.stringify(goodsArr));

	/** 排序显示 */
    if (tab_endShowActive == '1') {
		goodsSortArr = goodsArr;
    }


	var goodsSkuSort = goodsSortArr.sort(helper.compareTime('dataStartTime'));
    $('#form_carousel input[name=goodsSKUSort]:eq(0)').val(JSON.stringify(goodsSkuSort));

    /** 下个流程  */
    progress.next();
};


$(function () {

    /**初始化时间选择 */
    downDateInit();
    var $goodsTable = $('#form_carousel');
    /*限制tab个数*/
    function limitTab(num){
        var result = true;
        var tabLength = $('.tab-lists-wrap .goods-tab-item',$id).length;
        if(tabLength === parseInt(num)){
            result = false;
        }
        return result;
    }

    /** 新增秒杀模块 */
    $('#gs_tab_add',$id).on('click', function() {
        if(!!!limitTab(20)){
            layui.layer.msg('最多可增加二十个秒杀时间段');
            return false;
        }
        var clone = $('.tab-template li', $goodsTable).clone();
        $('.tab-lists-wrap', $goodsTable).append(clone);
        helper.initIndex();
        downDateInit();
    });

    /* 删除活动模块 */
    $('body').on('click', '#U0000207-template1_v1 .delete-datetime-item', function () {
        var target = $(this).closest('li'),
            timeRange = target.find('.down-timestamp').val(),
            goodsSku = target.find('.goods-sku').val();

        if (timeRange || goodsSku) {
            layui.layer.confirm('删除当前数据不可恢复，确定是否删除？', {
                btn: ['否', '是'],
                area: '420px',
                icon: 3,
                skin: 'element-ui-dialog-class'
            }, function (index) {
                layui.layer.close(index)
            }, function (index) {
                target.remove();
                helper.initIndex();
            });
        } else {
            target.remove();
            helper.initIndex();
        }
    });

});




