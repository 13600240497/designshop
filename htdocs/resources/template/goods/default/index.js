/**
 * ZAFUL, DL, RG
 * 商品数据管理 GsManager
 */

var uiId = sessionStorage.currentComponentId
var activityId = document.getElementById('activityId').value
var GsManagApiPrefix = activityId > 0 ? '/activity/' : '/home/'
var GESHOP_SITE_GROUP_CODE = Cookies.get('site_group_code')
if (activityId > 0) {
    GsManagApiPrefix = (GESHOP_SITE_GROUP_CODE == 'gb' || GESHOP_SITE_GROUP_CODE == 'dl') ? '/activity/' + GESHOP_SITE_GROUP_CODE + '/' : '/activity/zf';
    switch (GESHOP_SITE_GROUP_CODE){
        case 'zf': case 'rg': case 'suk': case 'dl':
        GsManagApiPrefix = '/activity/zf/'
        break;
    case 'gb':
        GsManagApiPrefix = '/activity/' + GESHOP_SITE_GROUP_CODE + '/'
        break;
    default:
        GsManagApiPrefix = '/activity/'
        break
    }
} else {
    switch (GESHOP_SITE_GROUP_CODE) {
    case 'zf': case 'rg': case 'suk': case 'dl':
        GsManagApiPrefix = '/home/zf/'
        break;
    case 'gb':
        GsManagApiPrefix = '/home/' + GESHOP_SITE_GROUP_CODE + '/'
        break;
    default:
        GsManagApiPrefix = '/home/'
        break
    }
}
var GbAdPrefix = Cookies.get('site_group_code') == 'gb' ? '/gbad/' : '/activity/'

// 存储商品列表tab三级活动信息
var tabItemObj = {}

/**
 * gbad 落地页判断
 */
if (typeof GESHOP_ACTIVITYTYPE != 'undefined' && GESHOP_ACTIVITYTYPE && GESHOP_ACTIVITYTYPE === 3) {
    GsManagApiPrefix = GbAdPrefix
}
// GsManagApiPrefix = '/activity/zf/'
var layer = layui.layer

function timestampToTime(timestamp) {
    var date = new Date(timestamp * 1000)//时间戳为10位需*1000，时间戳为13位的话不需乘1000
    Y = date.getFullYear() + '-'
    M = (date.getMonth() + 1 < 10 ? '0' + (date.getMonth() + 1) : date.getMonth() + 1) + '-'
    D = date.getDate() + ' '
    h = date.getHours() + ':'
    m = date.getMinutes() + ':'
    s = date.getSeconds()
    return Y + M + D + h + m + s
}


if (!GsManager) {
    $('body').on('click', '#gs_getList,.class-manage', function () {
        var $layerWrap = $('#goods_layer_wrap'),
            $manageBtn = $(this)
        /**sku 输入框
         * goodsType 是否是分类数据,tab,tab_new
         * dataFrom 商品数据来源 ips,obs
         */
        var $form = $('#component_form'),
            $targetInput,
            goodsType = $manageBtn.attr('data-goodsType') ? $manageBtn.attr('data-goodsType') : $form.find('.layui-tab-content').attr('data-goodsType'),
            dataFrom = $manageBtn.attr('data-from')
        if (!dataFrom) {
            if (goodsType == 'tab') {
                $targetInput = $manageBtn.parent().prev().find('[name=goodsSKU]')
            } else if (goodsType == 'tab_new') {
                $targetInput = $manageBtn.parents('.goods-tab-item:eq(0)').find('[name=goodsSKU]')
            } else {
                $targetInput = $form.find('[name=goodsSKU]')
            }
        } else {
            if (goodsType == 'tab') {
                $targetInput = $manageBtn.parent().prev().find('[name=' + dataFrom + 'GoodsSKU][data-from=' + dataFrom + ']')
            } else if (goodsType == 'tab_new') {
                $targetInput = $manageBtn.parents('.goods-tab-item:eq(0)').find('[name=' + dataFrom + 'GoodsSKU][data-from=' + dataFrom + ']')
            } else if (goodsType == 'tab_new2') {
                if (dataFrom == 'tabs') {
                    $targetInput = $manageBtn.prev('.rest-input').find('textarea[name=goodsSKU]')
                } else {
                    $targetInput = $form.find('[name=goodsSKU]')
                }


            } else {
                $targetInput = $manageBtn.prev('[name=' + dataFrom + 'GoodsSKU][data-from=' + dataFrom + ']')
            }

            if (dataFrom == 'tabs') {
                $targetInput = $manageBtn.prev('.rest-input').find('textarea[name=goodsSKU]')
            }
        }

        var $skuParent = $form.find('input[name=sku_selector_parent]').val();
        if($skuParent){
            $targetInput = $manageBtn.parents($skuParent).eq(0).find('[name=goodsSKU]');
        }

        var skus = $targetInput.val()

        if (!skus) {
            layer.msg('请输入SKU')
            return false
        }

        // 去重toast
        GS_GOODS_UTILS.repeatCheck($targetInput);

        // 新版本，统一校验规则
        var is_new_form = $('#design_form_is_new').val() == '1'
        if (is_new_form && window.design_form_submit_valid && skus) {
            design_form_submit_valid({
                skus: skus.split(','),
                success: function() {
                    GsManager.render($targetInput,skus)
                    // next();
                },
                confirm: function() {
                    Design.disableLayuiLoading();
                },
                cancel: function() {
                    Design.disableLayuiLoading();
                },
                fail: function() {
                    Design.disableLayuiLoading();
                }
            });
        } else {
            GsManager.render($targetInput,skus)
            // next();
        }
    });

    /**
     * ips 系统通信
     * component_type 组件是否为tab组件
     */
    $('body').on('click', '.ips-rule-manage', function () {
        if (!Geshop_message) {
            return false;
        }
        var dataObj = JSON.parse($(this).parent().attr('data-filter'));
        var ips_type = dataObj.ips_type,
            component_type = dataObj.type,
            $btn = $(this),
            $targetInput,
            $parentItem = $btn.parents('.gs-tab-select-item:eq(0)');

        $targetInput = $parentItem.find('.ips-message-input');
        if (component_type && component_type === 'tab') {
            Geshop_message.init($targetInput,ips_type)
        } else {
            Geshop_message.init($targetInput,ips_type)

            if (ips_type === 'rule') {

            } else if (ips_type === 'sku') {

            }
        }

    })

    $('body').on('click','.ips-good-manage',function(){
        var $btn = $(this),
            $form = $('#component_form'),
            $targetInput,
            $parentItem = $btn.parents('.gs-tab-select-item:eq(0)'),
            ipsMethods,
            $ipsMethodsWrap;
        var	dataObj = JSON.parse($btn.parent().attr('data-filter'));
        var component_type = dataObj && dataObj.type ? dataObj.type : 'single';

        $targetInput = $parentItem.find('.ips-message-input');
        if(component_type === 'single'){
            $ipsMethodsWrap = $('#component_form');
            ipsMethods = $ipsMethodsWrap.find('input[name=ipsMethods]:checked').val();
        }else{
            $ipsMethodsWrap = $btn.parents('.goods-tab-item:eq(0)')
            ipsMethods = $ipsMethodsWrap.find('.ipsItemRadio:checked').val();
        }

        var skus = $targetInput.val();
        var message = '';
        if (!skus) {
            message = ipsMethods == '3' ? '请添加规则' : '请输入商品'
            layer.msg(message)
            return false
        }

        // 新版本，统一校验规则
        var is_new_form = $('#design_form_is_new').val() == '1'
        if (is_new_form && window.design_form_submit_valid && skus) {
            design_form_submit_valid({
                skus: skus.split(','),
                success: function() {
                    GsManager.render($targetInput,skus)
                    // next();
                },
                confirm: function() {
                    Design.disableLayuiLoading();
                },
                cancel: function() {
                    Design.disableLayuiLoading();
                },
                fail: function() {
                    Design.disableLayuiLoading();
                }
            });
        } else {
            GsManager.render($targetInput,skus)
            // next();
        }

    })

    var $layer = $('#goods_layer')
    var $tobody = $('tbody', $layer)

    var GsManager = (function (my) {
        var $layerWrap, tempGoodsArr, Table = layui.table
        my.init = function () {
            $layer = $('#goods_layer')
            $layerWrap = $('#goods_layer_wrap')
            tempGoodsArr = $('input[name=tempGoodsArr]', $layerWrap)
            var _this = this
            _this.countNum()
            /* 监听序号 */
            $('#goods_layer').off('change').on('change', 'input[name=goods_item_orders]', function () {
                var $that = $(this), value = parseInt($(this).val()),
                    orderValue = parseInt($(this).attr('data-order')),
                    lastValue = parseInt($('#goods_layer').find('input[name=goods_item_orders]').length)
                if (value !== 0 && !value || value < 0) {
                    $that.val(orderValue)
                    return false
                }

                value = value > lastValue ? lastValue : value == 0 ? 1 : value
                if (value == orderValue) {
                    $that.val(orderValue)
                    return false
                }
                var $thatTr = $that.parents('tr:eq(0)'),
                    $dataIndex = $thatTr.attr('data-index'),
                    valueIndex = value - 1,
                    $clone = $thatTr.clone(),
                    $target = $('input[name=goods_item_orders]:eq(' + valueIndex + ')', $layer),
                    $targetTr = $target.parents('tr:eq(0)')
                if (value > orderValue) {
                    $targetTr.after($clone)
                } else {
                    $targetTr.before($clone)
                }
                $thatTr.remove()

                /* fixed联动 checkbox及工具栏*/
                var $fixThatTr = $('#goods_layer .layui-table-fixed-r tbody').find('tr[data-index=' + $dataIndex + ']'),
                    $fixTargetTr = $('#goods_layer .layui-table-fixed-r tbody').find('tr:eq(' + valueIndex + ')'),
                    $fixClone = $fixThatTr.clone()

                var $fixLtTr = $('#goods_layer .layui-table-fixed-l tbody').find('tr[data-index=' + $dataIndex + ']'),
                    $fixLtTargetTr = $('#goods_layer .layui-table-fixed-l tbody').find('tr:eq(' + valueIndex + ')'),
                    $fixLtClone = $fixLtTr.clone()


                $fixThatTr.remove()
                $fixLtTr.remove()
                if (value > orderValue) {
                    $fixTargetTr.after($fixClone)
                    $fixLtTargetTr.after($fixLtClone)
                } else {
                    $fixTargetTr.before($fixClone)
                    $fixLtTargetTr.before($fixLtClone)
                }
                /* 存储顺序 */
                _this.countNum(function (index, element) {
                    $(element).val(index).attr('data-order', index)
                })
                // _this.countNum()
            })

            /* 置顶置底上移下移删除 */
            layui.table.on('tool(gs_goodstable)', function (obj) {
                // var data = obj.data
                // var tr = obj.tr
                var layEvent = obj.event
                if (layEvent == 'class-del') {
                    layer.confirm('确定删除此商品吗？删除后，商品将不再列表中展示！', { icon: 3, title: '提示' }, function (index) {
                        obj.del()
                        _this.countNum()
                        layer.close(index)
                    })
                }
            })

            /* tr移动,对应fix也移动 */
            $('#goods_layer').on('click', 'tbody tr td:last-child .layui-btn', function () {
                var $this = $(this),
                    $fixTarget = $(this).parents('tr:eq(0)'),
                    dataType = $this.attr('data-type'),
                    $fixIndex = parseInt($fixTarget.attr('data-index')),

                    $fixLtTarget = $('#goods_layer').find('.layui-table-fixed-l tr[data-index=' + $fixIndex + ']'),
                    $target = $('#goods_layer').find('.layui-table-main tr[data-index=' + $fixIndex + ']'),
                    $index = $fixIndex
                // $target = $(this).parents('tr:eq(0)'),
                // dataType = $this.attr('data-type'),
                // $index = $('#goods_layer tbody tr').index($target),

                if (dataType) {
                    switch (dataType) {
                        case 'class-edit':
                            GsManager.editOne($target, $index)
                            break
                        case 'class-add':
                            GsManager.addOne($target, $index)
                            break
                        case 'class-up':
                            GsManager.moveUp($target, $fixTarget, $fixLtTarget)
                            break
                        case 'class-down':
                            GsManager.moveDown($target, $fixTarget, $fixLtTarget)
                            break
                        // case 'class-del':
                        // 	GsManager.delOne($target)
                        // 	break
                        // }
                        // case 'class-top':
                        // 	GsManager.goTop($target)
                        // 	break
                        // case 'class-bottom':
                        // 	GsManager.goBottom($target)
                        // 	break
                        // case 'class-close':
                        // 	GsManager.removeThis($target)
                        // 	break
                    }
                }

            })
        }
        my.render = function($targetInput,skus){
            Design.enableLayuiLoading()
            $('input[name=tempGoodsArr]').val(skus);
            var $layerWrap = $('#goods_layer_wrap');
            // $trIndex
            if (!$layerWrap.length) {
                var tableWrapper = ''
                if (dataFrom == 'ips') {
                    tableWrapper = '<div id="goods_layer_wrap"><input type="hidden" name="tempGoodsArr"></input><table id="goods_layer_table" lay-filter="gs_goodstable"></table>' +
                        '<div id="goods_toolbar">' +
                        '<a class="layui-btn layui-btn-xs" lay-event="class-up" data-type="class-up" title="上移">上移</a><a class="layui-btn layui-btn-xs" lay-event="class-down" data-type="class-down">下移</a></div></div>'
                } else {
                    tableWrapper = '<div id="goods_layer_wrap"><input type="hidden" name="tempGoodsArr"></input><table id="goods_layer_table" lay-filter="gs_goodstable"></table>' +
                        '<div id="goods_toolbar"><a class="layui-btn layui-btn-xs" lay-event="class-edit" data-type="class-edit">替换</a>' +
                        '<a class="layui-btn layui-btn-xs" lay-event="class-add" data-type="class-add">增加</a><a class="layui-btn layui-btn-xs" lay-event="class-del" data-type="class-del">删除</a>' +
                        '<a class="layui-btn layui-btn-xs" lay-event="class-up" data-type="class-up" title="上移">上移</a><a class="layui-btn layui-btn-xs" lay-event="class-down" data-type="class-down">下移</a></div></div>'
                }
                $('body').append(tableWrapper)
            }
            /* 分类SKU */
            // if ($(this).hasClass('class-manage')) {
            // 	var $tr = $(this).parents('tr:eq(0)'),
            // 		$trIndex = $('#design_form .goods-form-table').index($tr)
            // }

            var url = GsManagApiPrefix + 'goods/tpl-goods-list'
            var Table = layui.table
            var data = {lang: GESHOP_LANG || 'en', skus: skus, uiId: uiId}
            if (GESHOP_SITECODE.indexOf('gb') != -1) {
                data.pipeline = sessionStorage.getItem('gb_channel')
            }
            if (GESHOP_SITECODE.indexOf('zf') != -1 || GESHOP_SITECODE.indexOf('rg') != -1) {
                data.pipeline = GESHOP_PIPELINE
            }
            var site_group_code = typeof GESHOP_SITECODE != undefined ? GESHOP_SITECODE.split('-')[0] : '' || Cookies.get('site_group_code')
            // commone_site_col 服装cols, rg_site_col rg站点cols
            var commone_site_col = [
                {type: 'checkbox', fixed: 'left'}
                , {
                    field: 'numbers', title: '序号', width: 80, templet: function (d) {
                        var value = d.LAY_INDEX
                        return '<input type="number" name="goods_item_orders" value="' + value + '" data-order="' + value + '" style="width: 100%;"> '
                    }
                }
                , {field: 'goods_id', title: '商品ID', width: 100}
                , {field: 'goods_sn', title: '商品SKU', width: 140}
                , {field: 'goods_title', title: '商品标题', width: 100}
                , {
                    field: 'goods_img', title: '商品图片', width: 100, height: 100
                    , templet: function (d) {
                        return '<img src="' + d.goods_img + '">'
                    }
                }
                , {
                    field: 'is_on_sale', title: '上架', width: 80, templet: function (d) {
                        var text = ''
                        if (site_group_code == 'gb') {
                            text = d.is_on_sale == '2' ? '上架' : (d.is_on_sale == '1') ? '下架' : ''
                        } else {
                            text = d.is_on_sale == '1' ? '上架' : (d.is_on_sale == '2') ? '下架' : ''
                        }
                        return text
                    }
                }
                , {
                    field: 'goods_number', title: '库存', width: 80, templet: function (d) {
                        var text = ''
                        if (site_group_code == 'gb') {
                            text = d.stock_num ? d.stock_num : 0
                        } else {
                            text = d.goods_number ? d.goods_number : 0
                        }
                        return text
                    }
                }
                , {field: 'promte_percent', title: '促销利润比', width: 100}
                /*				, {
                  field: 'left_time', title: '促销时间', width: 200, templet: function (d) {
                      var text = ''
                      if (site_group_code == 'gb') {
                          text = d.promote_start_date ? timestampToTime(d.promote_start_date) + ' 至 ' + timestampToTime(d.promote_end_date) : ''
                      } else {
                          text = d.promote_start_date ? d.promote_start_date + ' 至 ' + d.promote_end_date : ''
                      }

                      return text
                  }
              }*/
                , {field: 'activity_number', title: '活动库存', width: 100}
                , {field: 'activity_volume_number', title: '活动库存销量', width: 120}
                , {fixed: 'right', width: dataFrom && dataFrom == 'ips' ? 150 : 250, align: 'center', toolbar: '#goods_toolbar'}
                // , {
                // 	field: 'options', 'title': '操作', width: 200
                // 	, templet: function (d) {
                // 		return '<button class="layui-btn layui-btn-xs" data-type="class-edit">替换</button><button class="layui-btn layui-btn-xs" data-type="class-add">增加</button><button class="layui-btn layui-btn-xs" data-type="class-del">删除</button>'
                // 		// return '<i class="layui-icon layui-icon-prev class-top" data-type="class-top"  title="置顶"></input><i class="layui-icon layui-icon-next class-bottom" data-type="class-bottom" title="置底"></i>' +
                // 		// '<i class="layui-icon class-up" data-type="class-up" title="上移">&#xe619;</i><i class="layui-icon class-down" data-type="class-down" title="下移">&#xe61a;</i>' +
                // 		// '<i class="layui-icon class-close" data-type="class-close" title="删除">&#x1006;</i>'
                // 	}
                // }
            ]
            var rg_site_col = [
                {type: 'checkbox', fixed: 'left'}
                , {
                    field: 'numbers', title: '序号', width: 80, templet: function (d) {
                        var value = d.LAY_INDEX
                        return '<input type="number" name="goods_item_orders" value="' + value + '" data-order="' + value + '" style="width: 100%;"> '
                    }
                }
                , {field: 'goods_id', title: '商品ID', width: 100}
                , {field: 'goods_sn', title: '商品SKU', width: 140}
                , {field: 'goods_title', title: '商品标题', width: 100}
                , {
                    field: 'goods_img', title: '商品图片', width: 100, height: 100
                    , templet: function (d) {
                        return '<img src="' + d.goods_img + '">'
                    }
                }
                , {
                    field: 'is_on_sale', title: '上架', width: 80, templet: function (d) {
                        var text = ''
                        if (site_group_code == 'gb') {
                            text = d.is_on_sale == '2' ? '上架' : (d.is_on_sale == '1') ? '下架' : ''
                        } else {
                            text = d.is_on_sale == '1' ? '上架' : (d.is_on_sale == '2') ? '下架' : ''
                        }
                        return text
                    }
                }
                , {
                    field: 'goods_number', title: '库存', width: 80, templet: function (d) {
                        var text = ''
                        if (site_group_code == 'gb') {
                            text = d.stock_num ? d.stock_num : 0
                        } else {
                            text = d.goods_number ? d.goods_number : 0
                        }
                        return text
                    }
                }
                , {field: 'promte_percent', title: '促销利润比', width: 100}
                , {field: 'activity_number', title: '活动库存', width: 100}
                ,{
                    field: 'shop_price', title: '当前售价', width: 120, hide: site_group_code !== 'rg', templet: function (d) {
                        return text = d.shop_price ? d.shop_price : d.market_price ? d.market_price : 0
                    }
                }
                , {field: 'activity_volume_number', title: '活动库存销量', width: 120}
                , {fixed: 'right', width: dataFrom && dataFrom == 'ips' ? 150 : 250, align: 'center', toolbar: '#goods_toolbar'}
            ]
            var tableCol = site_group_code == 'rg' ? rg_site_col : commone_site_col;

            Table.render({
                elem: '#goods_layer_table'
                , height: 400
                // , width: 1180
                , url: url
                , where: data
                // , page: true
                , limit: 100
                , id: 'gs_goodstable'
                , cols: [tableCol]
                , done: function (res, curr, count) {
                    Design.disableLayuiLoading()
                    if (res.code !== 0 && res.message) {
                        /* 清除wrap */
                        $('#goods_layer_wrap').next('.layui-form').remove()
                        $('#goods_layer_wrap').remove()
                        /* 清除错误sku */
                        gsSKUConfirm($targetInput, skus, res.message)
                        // layer.msg(res.message)
                        return false
                    }

                    // /* goods_layer存在 */
                    if ($('#goods_layer').length > 0) {
                        // var $copyTable = $('#goods_layer_table').next('.layui-form').find('.layui-table-box')
                        // $('#goods_layer').find('.layui-form .layui-table-box').html($copyTable.html())
                        var $newForm = $('#goods_layer_table').next('.layui-form'),
                            $newFixBody = $newForm.find('.layui-table-main tbody'),
                            $newFixLt = $newForm.find('.layui-table-fixed-l .layui-table-body tbody'),
                            $newFixRt = $newForm.find('.layui-table-fixed-r .layui-table-body tbody'),
                            $newHeader = $newForm.find('.layui-table-header:eq(0) thead')

                        $('#goods_layer').find('.layui-table-main tbody').html($newFixBody.html())
                        $('#goods_layer').find('.layui-table-fixed-l .layui-table-body tbody').html($newFixLt.html())
                        $('#goods_layer').find('.layui-table-fixed-r .layui-table-body tbody').html($newFixRt.html())
                        $('#goods_layer').find('.layui-table-header:eq(0) thead').html($newHeader.html())
                        $('.layui-table-fixed-r .layui-table-body,.layui-table-fixed-l .layui-table-body', '#goods_layer').css('max-height', '383px')
                        layui.form.render('checkbox')
                        // GsManager.init()
                    } else {
                        layui.layer.open({
                            title: '商品管理',
                            type: 1,
                            skin: 'layui-goods-list',
                            id: 'goods_layer',
                            closeBtn: 1,
                            anim: 5,
                            area: '1200px',
                            shade: 0.3,
                            shadeClose: true,
                            content: $('#goods_layer_table').next('.layui-form'),
                            btn: ['取消', '保存', '批量删除'],
                            btn2: function (index, layero) {
                                var goodsOrder = $('input[name=tempGoodsArr]', '#goods_layer_wrap').val()
                                // if (goodsType !== 'tab') {
                                // 	layer.close(index)
                                // }
                                layer.close(index)
                                $targetInput.val(goodsOrder)
                            },
                            yes: function (index, layero) {
                                layer.close(index);
                            },
                            btn3: function (index, layero) {
                                layer.confirm('确定批量删除商品吗？删除后，商品将不再列表中展示！', {icon: 3, title: '提示'}, function (index) {
                                    $('#goods_layer').find('.layui-table-fixed-l tbody .layui-form-checked').each(function () {
                                        var $realBody = $('#goods_layer .layui-table-main tbody'),
                                            $fixTr = $(this).parents('tr:eq(0)'),
                                            $index = $fixTr.attr('data-index'),
                                            $tr = $realBody.find('tr[data-index=' + $index + ']')
                                        $fixTr.remove()
                                        $tr.remove()
                                        $('#goods_layer .layui-table-fixed-r tbody ').find('tr[data-index=' + $index + ']').remove()
                                    })
                                    layer.close(index)
                                    GsManager.countNum()
                                })

                                return false
                            },
                            success: function (layero, index) {
                                GsManager.init()
                            },
                            end: function (index, layero) {
                                /* 清除wrap */
                                $('#goods_layer_wrap').next('.layui-form').remove()
                                $('#goods_layer_wrap').remove()
                            }
                        })

                    }

                }
            });
        }

        my.countNum = function (itemFn) {
            var temp = []
            $('#goods_layer input[name=goods_item_orders]').each(function (index, element) {
                var order = index + 1
                $(this).val(order).attr('data-order', order)
                var sku = $(this).parents('tr:eq(0)').find('td[data-field="goods_sn"] div').text()
                temp.push(sku)
                if (itemFn) itemFn(order, element)
            })
            tempGoodsArr.val(temp.toString())
            /* 重载checkbox */
            layui.form.render('checkbox')
        }

        my.delOne = function ($target) {
            var _this = this
            layer.confirm('确定删除此商品吗？删除后，商品将不再列表中展示！', {icon: 3, title: '提示'}, function (index) {
                $target.remove()
                _this.countNum()
                layer.close(index)
            })

        }

        my.addOne = function ($target, $index) {
            var _this = this
            var addTemp = ''
            addTemp += '<div id="gs_goods_add" class="layui-form">\n'
            addTemp += '	<div class="layui-form-item">\n'
            addTemp += '		<label class="layui-form-label">商品SKU<\/label>\n'
            addTemp += '		<div class="layui-input-block">\n'
            addTemp += '			<textarea type="text" name="addSKU" class="layui-textarea Unwanted" placeholder="请输入商品的SKU"><\/textarea>\n'
            addTemp += '         <span class="goodsTip" style="margin: 10px 0;"><i class="layui-icon" style="margin-right: 5px;color: #e0b571;"></i>注意:请输入商品编号（SKU ID），编号与编号间用英文逗号隔开</span>'
            addTemp += '		<\/div>\n'
            addTemp += '	<\/div>\n'
            addTemp += '<\/div>\n'

            layer.open({
                title: '商品新增',
                type: 1,
                skin: 'layui-goods-edit',
                closeBtn: 1,
                anim: 5,
                area: '600px',
                shade: 0.3,
                shadeClose: true,
                content: addTemp,
                btn: ['保存', '取消'],
                yes: function (index, layero) {
                    var $tempArr = $('#goods_layer_wrap input[name=tempGoodsArr]'),
                        skuNew = $('textarea[name=addSKU]').val(),
                        tempGoodsArr = $tempArr.val().split(','),
                        tempGoodsNew = ''
                    var skuStatu = _this.validate(skuNew)

                    if (!skuStatu) {
                        return false
                    }

                    _this.goods_exists(skuNew).done(function (res) {
                        if (res.code == 0) {
                            layer.msg('新增成功')
                        } else {
                            layer.msg(res.message)
                            return false
                        }

                        tempGoodsArr.splice($index + 1, 0, skuNew)
                        tempGoodsNew = tempGoodsArr.toString()
                        $tempArr.val(tempGoodsNew)


                        Table.reload('gs_goodstable', {
                            where: {
                                lang: GESHOP_LANG || 'en',
                                skus: tempGoodsNew
                            }
                        })
                        layer.close(index)
                        // $('#goods_layer_wrap').find('.layui-goods-list').remove().end().next('.layui-layer-shade').remove()
                    })

                },
                btn2: function (index, layero) {
                },
                success: function (layero, index) {
                },
                end: function (index, layero) {
                }
            })
        }

        my.editOne = function ($target, $index) {
            var _this = this,
                // $layer = $('#goods_layer'),
                $layerWrap = $('#goods_layer_wrap'),
                imgUrl = $target.find('td[data-field="goods_img"] img').attr('src'),
                addWrapId = '#gs_goods_add'
            var addTemp = ''
            addTemp += '<div id="gs_goods_add" class="layui-form">\n'
            addTemp += '	<div class="layui-form-item">\n'
            addTemp += '		<label class="layui-form-label">商品SKU<\/label>\n'
            addTemp += '		<div class="layui-input-block">\n'
            addTemp += '			<input type="text" name="replaceSKU" class="layui-input Unwanted" placeholder="请输入商品的SKU">\n'
            addTemp += '		<\/div>\n'
            addTemp += '	<\/div>\n'
            addTemp += '	<div class="layui-form-item">\n'
            addTemp += '		<label class="layui-form-label">商品图片<\/label>\n'
            addTemp += '		<div class="layui-input-block">\n'
            addTemp += '			<img src="' + imgUrl + '" class="gs-good-preview" style="max-width: 100%;">\n'
            addTemp += '		<\/div>\n'
            addTemp += '	<\/div>\n'
            addTemp += '<\/div>\n'

            layer.open({
                title: '商品替换',
                type: 1,
                skin: 'layui-goods-edit',
                closeBtn: 1,
                anim: 5,
                area: '600px',
                shade: 0.3,
                shadeClose: true,
                content: addTemp,
                btn: ['保存', '取消'],
                yes: function (index, layero) {
                    var $tempArr = $('input[name=tempGoodsArr]', $layerWrap),
                        skuNew = $('input[name=replaceSKU]', addWrapId).val(),
                        tempGoodsArr = $tempArr.val() ? $tempArr.val().split(',') : [],
                        tempGoodsNew = ''
                    var skuStatu = _this.validate(skuNew)
                    if (!skuStatu) {
                        return false
                    }
                    // var skuExists = false;
                    _this.goods_exists(skuNew).done(function (res) {
                        if (res.code == 0) {
                            layer.msg('替换成功')
                        } else {
                            layer.msg(res.message)
                            return false
                        }

                        tempGoodsArr.splice($index, 1, skuNew)
                        tempGoodsNew = tempGoodsArr.toString()
                        $tempArr.val(tempGoodsNew)
                        Table.reload('gs_goodstable', {
                            where: {
                                lang: GESHOP_LANG || 'en',
                                skus: tempGoodsNew
                            }
                        })
                        layer.close(index)
                        // $('#goods_layer_wrap').find('.layui-goods-list').remove().end().next('.layui-layer-shade').remove()
                    })

                },
                btn2: function (index, layero) {
                },
                success: function (layero, index) {
                    $('input[name=replaceSKU]', addWrapId).off('change').on('change', function () {
                        var sku = $(this).val(),
                            url = GsManagApiPrefix + 'goods/tpl-goods-list',
                            param = GESHOP_SITECODE.indexOf('zf') != -1 ? {lang: GESHOP_LANG || 'en', skus: sku, uiId: uiId, pipeline: GESHOP_PIPELINE} : {lang: GESHOP_LANG || 'en', skus: sku, uiId: uiId}
                        $.ajax({
                            type: 'GET',
                            url: url,
                            data: param,
                            dataType: 'json',
                            success: function (res) {
                                if (res.code == 0 && res.data.length > 0) {
                                    $('.gs-good-preview', addWrapId).attr('src', res.data[0].goods_img)
                                } else {
                                    $('.gs-good-preview', addWrapId).attr('src', '')
                                    // layer.msg(res.message)
                                }
                            },
                            error: function () {
                                layer.msg('替换失败')
                            }
                        })
                    })

                },
                end: function (index, layero) {
                }
            })
        }

        my.moveUp = function ($target, $fixTarget, $fixLtTarge) {
            var $trPrev = $target.prev('tr')
            if (0 != $trPrev.length) {
                var $clone = $target.clone()
                $target.remove()
                $trPrev.before($clone)

                if ($fixTarget) {
                    [$fixTarget, $fixLtTarge].forEach(function (value, index) {
                        var $fixTrPrev = value.prev('tr')
                        if (0 != $fixTrPrev.length) {
                            var $fixClone = value.clone()
                            value.remove()
                            $fixTrPrev.before($fixClone)
                        }
                    })
                }

                this.countNum()
            }
        }

        my.moveDown = function ($target, $fixTarget, $fixLtTarge) {
            var $trNext = $target.next('tr')
            if (0 != $trNext.length) {
                var $clone = $target.clone()
                $target.remove()
                $trNext.after($clone)

                if ($fixTarget) {
                    [$fixTarget, $fixLtTarge].forEach(function (value, index) {
                        var $fixTrNext = value.next('tr')
                        if (0 != $fixTrNext.length) {
                            var $fixClone = value.clone()
                            value.remove()
                            $fixTrNext.after($fixClone)
                        }
                    })
                }

                this.countNum()
            }

        }

        /* SKU本地校验 */
        my.validate = function (skuNew) {
            if (!skuNew) {
                layer.msg('SKU不能为空')
                return false
            }
            var $tempArr = $('#goods_layer_wrap input[name=tempGoodsArr]'),
                skuArr = skuNew.split(','),
                skuStatu = true,
                skuError = []
            skuArr.forEach(function (element, index) {
                if ($tempArr.val().indexOf(element) !== -1) {
                    skuError.push(element)
                    skuStatu = false
                    return false
                }
            })
            if (!skuStatu) {
                layer.msg(skuError.toString() + '已存在')
                return false
            } else {
                return true
            }
        }

        my.goods_exists = function (sku, callback, addParams) {
            var url = GsManagApiPrefix + 'goods/tpl-goods-exists'
            var data = {lang: GESHOP_LANG || 'en', skus: sku, uiId: uiId, pipeline: ''}
            if (GESHOP_SITECODE.indexOf('gb') != -1) {
                data.pipeline = sessionStorage.getItem('gb_channel')
            }
            if (addParams && Object.getOwnPropertyNames(addParams).length > 0) {
                data = Object.assign(data, addParams)
            }

            return $.ajax({
                type: 'GET',
                url: url,
                data: data,
                dataType: 'json',
                fail: function () {
                    layer.msg('连接服务器失败')
                }
            })
        }

        my.goTop = function ($target) {
            var $index = $tbody.find('tr').index($target)
            if (0 != $index) {
                var clone = $target.clone()
                $tobody.prepend(clone)
                $target.remove()
            }
        }

        my.goBottom = function ($target) {
            var $index = $tbody.find('tr').index($target),
                $length = $tbody.find('tr').length
            if ($length != $index) {
                var clone = $target.clone()
                $tobody.append(clone)
                $target.remove()
            }
        }

        return my
    }({}))
}


/**
 * 选品系统
 */
var site_code = typeof GESHOP_SITECODE != undefined ? GESHOP_SITECODE : ''

function matchCustom(params, data) {
    // If there are no search terms, return all of the data
    if ($.trim(params.term) === '') {
        return data
    }

    // Do not display the item if there is no 'text' property
    if (typeof data.text === 'undefined') {
        return null
    }

    // `params.term` should be the term that is used for searching
    // `data.text` is the text that is displayed for the data object
    if (data.text.indexOf(params.term) > -1) {
        var modifiedData = $.extend({}, data, true)
        // modifiedData.text += ' (matched)';

        // You can return modified objects from here
        // This includes matching the `children` how you want in nested data sets
        return modifiedData
    }

    // Return `null` if the term should not be displayed
    return null
}

layui.form.on('radio(ipsMethods)', function (data) {
    var value = Number(data.value)
    var $wraper = $('.gs-tab-select-wraper');
    var currentIndex;
    switch (value) {
        case 2:
            currentIndex = 0
            break;
        case 1:
            currentIndex = 1
            break;
        default:
            currentIndex = value - 1 || 0
            break;
    }
    var $target = $('.gs-tab-select-wraper').find('.gs-tab-select-item:eq(' + currentIndex + ')')
    $target.addClass('goods-visible');
    $target.siblings().removeClass('goods-visible');
})


if (!GsSelect) {
    var GsSelect = (function (my) {
        var level0, level1, level2, level3, level4, level5

        my.initSelect = function (target) {
            // var $target = target || '.radio-tab-true'
            var $target = target
            /* 初始select */
            if( $.fn.select2 ){
                $('.gs-select-box', $target).not('.hide .gs-select-box').select2({
                    width: '115',
                    language: {
                        noResults: function (params) {
                            return '无匹配数据';
                        }
                    },
                    ajax: {
                        url: function (params) {
                            return ajaxUrl($(this));

                        },
                        data: function (params) {
                            return ajaxParamsData($(this));

                        },
                        processResults: function (data) {
                            return processResults(data, $(this.$element));//这里的this指向select2的实例

                        }
                    },
                    placeholder: '请输入关键字'
                }).on("change", function (e) {


                    selectChange($(this));
                });
            }


            /**
             * 不同级活动的数据接口不一样
             * @param {select的Jq对象} $curTarget
             */
            function ajaxUrl($curTarget) {
                var level = $curTarget.data('level');
                var ajaxUrl = '';
                //为兼容手动选品，延续之前的区分，
                //level=0手动选品一级活动，level=1手动选品二级活动，level=2手动选品三级活动
                //level=3自动选品一级活动，level=4自动选品二级活动，level=5自动选品三级活动
                if (level == 0 || level == 3) {
                    ajaxUrl = '/soa/ips/activity-list'
                } else if (level == 1 || level == 4) {
                    ajaxUrl = '/soa/ips/activity-group-list'
                } else if (level == 2 || level == 5) {
                    ajaxUrl = '/soa/ips/activity-child-list'
                }
                return ajaxUrl;
            }

            /**
             * 获取活动数据
             * @param {select的Jq对象} $curTarget
             */
            function ajaxParamsData($curTarget) {
                var level = $curTarget.data('level');
                var site_code = typeof GESHOP_SITECODE != undefined ? GESHOP_SITECODE : '';
                var query = {};
                // 为兼容手动选品，延续之前的区分，
                //level=0手动选品一级活动，level=1手动选品二级活动，level=2手动选品三级活动
                //level=3自动选品一级活动，level=4自动选品二级活动，level=5自动选品三级活动
                if (level == 0 || level == 3) {
                    query = {site_code: site_code}
                } else if (level == 1 || level == 4) {
                    var paren_activity_id = $curTarget.data('parent-activity-id');//传一级活动id
                    query = {activity_id: paren_activity_id}
                } else if (level == 2 || level == 5) {
                    var paren_activity_id = $curTarget.data('parent-activity-id');//传二级活动id
                    query = {activity_child_group_id: paren_activity_id}
                }
                return query;
            }

            /**
             * 封装返回的数据
             * @param {ajax获取的数据} data
             * @param {select的Jq对象} $curTarget
             */
            function processResults(data, $curTarget) {
                var istabselect = $curTarget.data('istabselect');
                var ipsMethods = istabselect == 1 ? $curTarget.closest('.gs-tab-select-wraper').find('input.ipsItemRadio:checked').val() : $('input[name="ipsMethods"]:checked').val();//确定是手动选品还是自动选品,ipsMethods == 1是自动选品 ipsMethods==2手动选品
                var returnOptionsData = [];
                var level = $curTarget.data('level');
                var activity_id = $curTarget.data('activity-id');//获取选择的id进行数据回填
                // 为兼容手动选品，延续之前的区分，
                //level=0手动选品一级活动，level=1手动选品二级活动，level=2手动选品三级活动
                //level=3自动选品一级活动，level=4自动选品二级活动，level=5自动选品三级活动
                data && data.data && data.data.list.length > 0 && data.data.list.forEach(function (v) {
                    if (level == 0 || level == 3) {

                        if (ipsMethods == 1 && v.is_auto_activity == 2) {//只要自动选品数据
                            returnOptionsData.push({
                                "id": v.activity_id,
                                "text": v.activity_id + ' ' + v.activity_title,
                                "selected": v.activity_id == activity_id
                            })

                        } else if (ipsMethods == 2 && v.is_auto_activity == 1) {//只要手动选品数据
                            returnOptionsData.push({
                                "id": v.activity_id,
                                "text": v.activity_id + ' ' + v.activity_title,
                                "selected": v.activity_id == activity_id
                            })
                        }
                    } else if (level == 1 || level == 4) {
                        returnOptionsData.push({
                            "id": v.activity_child_group_id,
                            "text": v.activity_child_group_id + ' ' + v.activity_child_group_title,
                            "selected": v.activity_child_group_id == activity_id
                        })
                    } else if (level == 2 || level == 5) {
                        returnOptionsData.push({
                            "id": v.activity_child_id,
                            "text": v.activity_child_id + ' ' + v.activity_child_title,
                            "selected": v.activity_child_group_id == activity_id
                        })
                    }

                })

                return {
                    "results": returnOptionsData

                }
            }

            /**
             * 下拉框值改变
             * @param {select的Jq对象} $curTarget
             */
            function selectChange($curTarget) {
                var level = $curTarget.data('level');
                var val = $curTarget.val();
                var istabselect = $curTarget.data('istabselect');

                var $curWarp = istabselect == 1 ? $curTarget.closest('.gs-tab-select-item') : $curTarget.closest('.gs-tab-select-item');

                // 为兼容手动选品，延续之前的区分，
                //level=0手动选品一级活动，level=1手动选品二级活动，level=2手动选品三级活动
                //level=3自动选品一级活动，level=4自动选品二级活动，level=5自动选品三级活动


                if (level == 0 || level == 3) { //清空二级和三级的数据
                    $curTarget.data('activity-id', val);
                    var $subSelect = $curWarp.find('.gs-select-box-second');
                    var $subSubSelect = $curWarp.find('.gs-select-box-third');
                    $subSelect.data('parent-activity-id', val).data('activity-id', '0').select2("val", "0");
                    $subSubSelect.data('parent-activity-id', val).data('activity-id', '0').select2("val", "0");

                } else if (level == 1 || level == 4) { //清空三级的数据
                    var $subSubSelect = $curWarp.find('.gs-select-box-third');
                    $curTarget.data('activity-id', val);
                    $subSubSelect.data('parent-activity-id', val).data('activity-id', '0').select2("val", "0");

                } else if (level == 2 || level == 5) {
                    $curTarget.data('activity-id', val);
                    var ipsMethods = $('input[name="ipsMethods"]:checked').val();//确定是手动选品还是自动选品,ipsMethods == 1是自动选品 ipsMethods==2手动选品

                    if (ipsMethods == 2) {//收到选品可以排序商品
                        var activity_child_id = val

                        if (!activity_child_id || activity_child_id == 0) {
                            return false
                        }
                        var params = {
                            lang: $('#pageLang').val(),
                            page_id: $('#pageId').val(),
                            id: sessionStorage.getItem('currentComponentId'),
                            tpl_id: sessionStorage.getItem('currentTemplateId'),
                            activity_child_id: activity_child_id
                        }
                        my.getAjax('/soa/ips/get-activity-goods-sku', params).done(function (res) {
                            if (res.code == 0 && res.data.sku) {
                                // if ($curWarp.find('[name=ipsGoodsSKU]').val() == '') {
                                // 	$radioGroup.find('[name=ipsGoodsSKU]').val(res.data.sku)
                                // }
                                $curWarp.find('[name=ipsGoodsSKU]').val(res.data.sku)
                            }

                        }).fail(function () {
                            layer.msg('选品SKU获取失败')
                        })
                    }

                }
            }

            // level0 = $('input[name=gsSelectLevel0]', $target).val()
            // level1 = $('input[name=gsSelectLevel1]', $target).val()
            // level2 = $('input[name=gsSelectLevel2]', $target).val()

            // level3 = $('input[name=gsSelectLevel3]', $target).val()
            // level4 = $('input[name=gsSelectLevel4]', $target).val()
            // level5 = $('input[name=gsSelectLevel5]', $target).val()


            //my.initSelectChange($target)
        }
        /* 变更监听 */
        my.initSelectChange = function ($target) {
            // var $selectBox = $target || $('.design-right')
            // /* 一级变更 */
            // $('.gs-select-box.gs-select-level0', $selectBox).on('change.ips', function () {
            // 	var activity_id = $(this).val()
            // 	var $radioGroup = $(this).closest('.radio-tab-group')
            // 	var level1Value = $('input[name=gsSelectLevel1]', $radioGroup).val()
            // 	var levelVal = level1Value ? level1Value : level1
            // 	$('.gs-select-box:eq(1)', $radioGroup).html('<option value="00">请选择活动</option>')
            // 	$('.gs-select-box:eq(2)', $radioGroup).html('<option value="00">请选择活动</option>')
            // 	if (activity_id == '00' || !activity_id) {
            // 		return false
            // 	} else {
            // 		$('.gs-select-box:eq(1)', $radioGroup).parent().removeClass('layui-hide')
            // 	}

            // 	my.getAjax('/soa/ips/activity-group-list', { activity_id: activity_id }).done(function (res) {

            // 		my.initRequestCallback($radioGroup, res, 1, levelVal)
            // 		level1 = null
            // 	}).fail(function () {
            // 		layer.msg('选品连接失败')
            // 	})
            // })

            // // 选品系统手动方式 - 二级活动信息选择框
            // $('.gs-select-box.gs-select-level1', $selectBox).on('change.ips', function () {

            // 	var activity_id = $(this).val()
            // 	var $radioGroup = $(this).closest('.radio-tab-group')
            // 	var level2Value = $('input[name=gsSelectLevel2]', $radioGroup).val()
            // 	var levelVal = level2Value ? level2Value : level2
            // 	if (activity_id == '00') {
            // 		$('.gs-select-box:eq(2)', $radioGroup).html('<option value="00">请选择活动</option>')
            // 		return false
            // 	} else {
            // 		$('.gs-select-box:eq(2)', $radioGroup).parent().removeClass('layui-hide')
            // 	}
            // 	if (!activity_id) {
            // 		return false
            // 	}

            // 	my.getAjax('/soa/ips/activity-child-list', { activity_child_group_id: activity_id }).done(function (res) {

            // 		my.initRequestCallback($radioGroup, res, 2, levelVal)
            // 		level2 = null
            // 	}).fail(function () {
            // 		layer.msg('选品连接失败')
            // 	})
            // })

            // // 选品系统自动方式 - 一级活动信息选择框
            // $('.gs-select-box.gs-select-level3', $selectBox).on('change.ips', function () {
            // 	var activity_id = $(this).val()
            // 	var $radioGroup = $(this).closest('.radio-tab-group')
            // 	var level3Value = $('input[name=gsSelectLevel3]', $radioGroup).val()
            // 	var levelVal = level3Value ? level3Value : level3
            // 	$('.gs-select-box:eq(4)', $radioGroup).html('<option value="00">请选择活动</option>')
            // 	// $('.gs-select-box:eq(5)', $radioGroup).html('<option value="00">请选择活动</option>')
            // 	if (activity_id == '00' || !activity_id) {
            // 		return false
            // 	} else {
            // 		$('.gs-select-box:eq(4)', $radioGroup).parent().removeClass('layui-hide')
            // 	}

            // 	my.getAjax('/soa/ips/activity-group-list', { activity_id: activity_id }).done(function (res) {

            // 		my.initRequestCallback($radioGroup, res, 4, levelVal)
            // 		level4 = null
            // 		my.resetAutomaticSelect(true)
            // 	}).fail(function () {
            // 		layer.msg('选品连接失败')
            // 	})

            // })

            // // 商品列表tab组件：选品系统自动方式 - 一级活动信息选择框
            // $('.gs-select-box.gs-select-level-ips-auto', $selectBox).on('change.ips', function () {
            // 	var activity_id = $(this).val()
            // 	var elem = $(this).parents('.select-item').nextAll('.select-item')
            // 	if (activity_id == '00' || !activity_id) {
            // 		return false
            // 	} else {
            // 		elem.removeClass('layui-hide')
            // 	}

            // 	my.getAjax('/soa/ips/activity-group-list', { activity_id: activity_id }).done(function (res) {

            // 		my.initIpsAutoSecTrdSelect(res, elem)
            // 	}).fail(function () {
            // 		layer.msg('选品连接失败')
            // 	})

            // })

            // // 选品系统自动方式 - 二级活动信息选择框
            // $('.gs-select-box.gs-select-level4', $selectBox).on('change.ips', function () {
            // 	var activity_id = $(this).val()
            // 	var $radioGroup = $(this).closest('.radio-tab-group')
            // 	var level4Value = $('input[name=gsSelectLevel4]', $radioGroup).val()
            // 	var levelVal = level4Value ? level4Value : level4
            // 	if (activity_id == '00') {
            // 		$('.gs-select-box:eq(5)', $radioGroup).html('<option value="00">请选择活动</option>')
            // 		return false
            // 	} else {
            // 		$('.gs-select-box:eq(5)', $radioGroup).parents('.gs-select-item-wraper').removeClass('layui-hide')
            // 	}
            // 	if (!activity_id) {
            // 		return false
            // 	}

            // 	my.getAjax('/soa/ips/activity-child-list', { activity_child_group_id: activity_id }).done(function (res) {
            // 		// 存储第三级数据作为添加第三级活动初始化数据
            // 		sessionStorage.gs_select_list = JSON.stringify(res.data.list)
            // 		$('input[name=thirdAutoSelectInfo]').val(JSON.stringify(res.data.list))

            // 		my.initRequestCallback($radioGroup, res, 5, levelVal)
            // 		level5 = null
            // 		my.resetAutomaticSelect()
            // 	}).fail(function () {
            // 		layer.msg('选品连接失败')
            // 	})

            // })


            // // 商品列表tab组件：选品系统自动方式 - 二级活动信息选择框
            // $('.gs-select-box.gs-select-level2-ips-auto', $selectBox).on('change.ips', function () {
            // 	var activity_id = $(this).val()
            // 	var elem = $(this).parents('.select-item').nextAll('.gs-select-item-wraper')
            // 	var tabItemIndex = $(this).parents('.goods-tab-item').attr('data-tab-index')

            // 	if (activity_id == '00') {
            // 		// $('.gs-select-box:eq(5)').html('<option value="00">请选择活动</option>')
            // 		return false
            // 	} else {
            // 		elem.removeClass('layui-hide')
            // 	}
            // 	if (!activity_id) {
            // 		return false
            // 	}

            // 	my.getAjax('/soa/ips/activity-child-list', { activity_child_group_id: activity_id }).done(function (res) {
            // 		// 存储第三级数据作为添加第三级活动初始化数据
            // 		tabItemObj[tabItemIndex] = res.data.list
            // 		sessionStorage.gs_tab_select_list = JSON.stringify(tabItemObj)
            // 		$('input[name=thirdAutoSelectInfo]').val(JSON.stringify(tabItemObj))

            // 		my.initIpsAutoSecTrdSelect(res, elem)
            // 	}).fail(function () {
            // 		layer.msg('选品连接失败')
            // 	})

            // })

            // // 选品系统手动 - 选择三级信息后请求sku接口
            // $('.gs-select-box.gs-select-level2', $selectBox).on('change.ips', function () {
            // 	var activity_child_id = $(this).val()
            // 	var $radioGroup = $(this).closest('.radio-tab-group')
            // 	var level2Value = $('input[name=gsSelectLevel2]', $radioGroup).val()

            // 	if (!activity_child_id || activity_child_id == '00') {
            // 		return false
            // 	}
            // 	var params = {
            // 		lang: $('#pageLang').val(),
            // 		page_id: $('#pageId').val(),
            // 		id: sessionStorage.getItem('currentComponentId'),
            // 		tpl_id: sessionStorage.getItem('currentTemplateId'),
            // 		activity_child_id: activity_child_id
            // 	}
            // 	my.getAjax('/soa/ips/get-activity-goods-sku', params).done(function (res) {
            // 		if (res.code == 0 && res.data.sku) {
            // 			// if ($radioGroup.find('[name=ipsGoodsSKU]').val() == '') {
            // 			// 	$radioGroup.find('[name=ipsGoodsSKU]').val(res.data.sku)
            // 			// }
            // 			$radioGroup.find('[name=ipsGoodsSKU]').val(res.data.sku)
            // 		}

            // 	}).fail(function () {
            // 		layer.msg('选品SKU获取失败')
            // 	})
            // })

        }

        /**
         * @description 选品系统自动方式重置三级活动信息 默认保留一个三级活动信息
         * @param { Boolean } isLevel3 - 是否一级选择框（是则隐藏三级选择框）
         */
        my.resetAutomaticSelect = function (isLevel3) {
            var $target = $('.gs-select-item-wraper'), ipsAutoInfo = JSON.parse($('input[name=ipsAutoInfo]').val())
            if (!ipsAutoInfo) {
                $target.find('.gs-select-item:gt(0)').remove()
            }
            if (isLevel3) {
                $('.gs-select-item-wraper').addClass('layui-hide')
            }
        }

        my.initSelectFirst = function ($target) {
            // var $radioGroup = $target || null
            // var level0 = $('input[name=gsSelectLevel0]', $radioGroup).val()
            // var level3 = $('input[name=gsSelectLevel3]', $radioGroup).val()
            // var ipsMethods = $('input[name=ipsMethods]:checked').val()

            // my.getAjax('/soa/ips/activity-list', { site_code: site_code }).done(function (res) {
            // 	// 过滤非自动和自动选品数据
            // 	// is_auto_activity 1 - 非自动
            // 	// is_auto_activity 2 - 自动
            // 	var activity_list = res.data.list, is_auto_activity_list = [], not_auto_activity_list = []
            // 	activity_list.forEach(function(item) {
            // 		if (item.is_auto_activity == 1) {
            // 			not_auto_activity_list.push(item)
            // 		} else if (item.is_auto_activity == 2) {
            // 			is_auto_activity_list.push(item)
            // 		}
            // 	})

            // 	var is_auto_results = { code: res.code, data: {} }, not_auto_results = { code: res.code, data: {} }
            // 	is_auto_results.data.list = is_auto_activity_list
            // 	not_auto_results.data.list = not_auto_activity_list
            // 	// 非自动
            // 	if (level0 && ipsMethods == 2) {
            // 		my.initRequestCallback($radioGroup, not_auto_results, 0, level0)
            // 		level0 = null
            // 		my.initRequestCallback($radioGroup, is_auto_results, 3, null)
            // 	}
            // 	// 自动
            // 	else if (level3  && ipsMethods == 1) {
            // 		my.initRequestCallback($radioGroup, is_auto_results, 3, level3)
            // 		level3 = null
            // 		my.initRequestCallback($radioGroup, not_auto_results, 0, null)
            // 	} else {
            // 		my.initRequestCallback($radioGroup, not_auto_results, 0, null)
            // 		my.initRequestCallback($radioGroup, is_auto_results, 3, null)
            // 	}

            // }).fail(function () {
            // 	layer.msg('选品连接失败')
            // })

        }

        my.initSelectFirstGroup = function () {
            // var gsSelectAutoLevel = $('input[name=gsSelectAutoLevel]').val()
            // my.getAjax('/soa/ips/activity-list', { site_code: site_code }).done(function (res) {
            // 	// 过滤非自动和自动选品数据
            // 	// is_auto_activity 1 - 非自动
            // 	// is_auto_activity 2 - 自动
            // 	var activity_list = res.data.list, is_auto_activity_list = [], not_auto_activity_list = []
            // 	activity_list.forEach(function(item) {
            // 		if (item.is_auto_activity == 1) {
            // 			not_auto_activity_list.push(item)
            // 		} else if (item.is_auto_activity == 2) {
            // 			is_auto_activity_list.push(item)
            // 		}
            // 	})

            // 	var is_auto_results = { code: res.code, data: {} }, not_auto_results = { code: res.code, data: {} }
            // 	is_auto_results.data.list = is_auto_activity_list
            // 	not_auto_results.data.list = not_auto_activity_list

            // 	$('.radio-tab-true').find('.radio-tab-group').each(function () {
            // 		var $radioGroup = $(this)
            // 		var level0 = $('input[name=gsSelectLevel0]', $radioGroup).val()
            // 		if (level0) {
            // 			my.initRequestCallback($radioGroup, not_auto_results, 0, level0)
            // 			level0 = null
            // 		} else if (gsSelectAutoLevel == 1) {
            // 			my.initIpsAutoFirstSelect(is_auto_results)
            // 		} else {
            // 			my.initRequestCallback($radioGroup, not_auto_results, 0, null)
            // 			my.initIpsAutoFirstSelect(is_auto_results)
            // 		}

            // 	})


            // }).fail(function () {
            // 	layer.msg('选品连接失败')
            // })

        }

        /**
         * 初始前回调渲染
         * @param {*} res
         * @param {*} targetIndex 第几级上标
         * @param {*} targetValue 默认选中值
         */
        my.initRequestCallback = function ($item, res, targetIndex, targetValue) {
            // var targetIndex = targetIndex || 0
            // var $box = $item || $('.radio-tab-true .radio-tab-group')
            // if (res.code == '0') {
            // 	var data = my.transformData(res.data.list)
            // 	if (targetIndex == 0) {

            // 	}

            // 	$('.gs-select-box:eq(' + targetIndex + ')', $box).html('<option value="00">请选择活动</option>')
            // 	if (data.length === 0) {
            // 		return false
            // 	}
            // 	var optionLists = '<option value="00">请选择活动</option>'
            // 	for (var i = 0; i < data.length; i++) {
            // 		optionLists += '<option value=' + data[i].id + '>' + data[i].id + ' ' + data[i].text + '</option>'
            // 	}
            // 	$('.gs-select-box:eq(' + targetIndex + ')', $box).html(optionLists)

            // 	// 如果是选品自动方式 对所有三级活动信息初始化
            // 	if ($('.gs-tab-select-wraper').attr('data-ipsMethods') == 1) {
            // 		$('.gs-select-box:eq(' + targetIndex + ')', $box).parents('.gs-select-item').nextAll('.gs-select-item').find('.gs-select-box').html(optionLists);
            // 	}

            // 	$('.gs-select-box:eq(' + targetIndex + ')', $box).select2({
            // 		width: '115',
            // 		language: {
            // 			noResults: function (params) {
            // 				return '无匹配数据'
            // 			}
            // 		},
            // 		matcher: matchCustom
            // 	})


            // 	if (targetValue) {
            // 		$('.gs-select-box:eq(' + targetIndex + ')', $box).val(targetValue).trigger('change')
            // 	}

            // } else {
            // 	layer.msg(res.message)
            // }
        }

        /**
         * @desc 商品列表组件 - 选品自动方式第三级数据渲染
         */
        my.initIpsAutoThirdSelect = function ($item, res, targetIndex, targetValue) {
            if (res.code == '0') {
                var data = my.transformData(res.data.list)

                $('.gs-select-box.gs-select-ips-third-item').html('<option value="00">请选择活动</option>')
                if (data.length === 0) {
                    return false
                }
                var optionLists = '<option value="00">请选择活动</option>'
                for (var i = 0; i < data.length; i++) {
                    optionLists += '<option value=' + data[i].id + '>' + data[i].id + ' ' + data[i].text + '</option>'
                }

                $('.gs-select-box.gs-select-ips-third-item').html(optionLists)

                // 如果是选品自动方式 对所有三级活动信息初始化
                if ($('.gs-tab-select-wraper').attr('data-ipsMethods') == 1) {
                    $('.gs-select-box.gs-select-ips-third-item').parents('.gs-select-item').nextAll('.gs-select-item').find('.gs-select-box').html(optionLists)
                }

                typeof $('.gs-select-box.gs-select-ips-third-item').data('select2') != 'undefined' && $('.gs-select-box.gs-select-ips-third-item').data('select2').destroy()
                $('.gs-select-box.gs-select-ips-third-item').select2({
                    width: '115',
                    language: {
                        noResults: function (params) {
                            return '无匹配数据'
                        }
                    },
                    matcher: matchCustom
                })

            } else {
                layer.msg(res.message)
            }
        }

        //格式化select data
        my.transformData = function (data) {
            // return $.map(data, function (obj) {
            // 	obj.id = obj.activity_id || obj.activity_child_group_id || obj.activity_child_id
            // 	obj.text = obj.activity_title || obj.activity_child_group_title || obj.activity_child_title
            // 	return obj
            // })
        }

        /**
         * 存储自动更新商品数据
         * @param id selector id
         */
        my.saveSyncDataInfo = function(id, param){
            var $id = id;
            /*存放所有信息*/
            var data = [];
            /*skuFrom 1 手动 2 选品*/
            var skuFrom = $id.find('input[name="goodsDataFrom"]:checked').val(),
                /*psMethods 1 自动 2 手动 3 规则添加（自动） 4 筛选器添加（手动)*/
                ipsMethods = skuFrom == 2 ? $id.find('input[name="ipsMethods"]:checked').val() : "",
                /*商品sku*/
                goodsSku = skuFrom == 1 ? $id.find('textarea[name="goodsSKU"]').val() : "",
                /*skuFrom === 2 时 存放的选品ID 和显示数量*/
                ipsInfo = [];

            /* 过滤非法空格[space]和回车[enter] */
            if (goodsSku != '') {
                goodsSku = $.trim(goodsSku).split(' ').join('').replace(/\\n/g, '').replace(/&#13;/g, '');
            }

            switch (parseInt(ipsMethods)) {
                /*自动*/
                case 1:
                    $id.find('.gs-select-level3-wraper').each(function () {
                        ipsInfo.push({
                            id: $(this).find('.gs-select-box-third').val(),
                            sku_num: $(this).find('.sku-select-input').val()
                        });
                    });
                    break;
                /*手动*/
                case 2:
                    $id.find('.gs-select-automatic-content').each(function () {
                        ipsInfo.push({
                            id: $(this).find('.gs-select-box-third').val(),
                            sku_num: ""
                        });
                    });
                    break;
                /*规则添加（自动）*/
                case 3:
                    // 筛选自动
                    $id.find('.gs-select-automatic-content').each(function () {
                        ipsInfo.push({
                            id: $(this).find('[name=ips_activity_child_id]').val(),
                            goodsSku: $id.find('textarea[name="ips_auto_sku"]').val()
                        });
                    });
                    break;
                //  筛选手动
                case 4:
                    $id.find('.gs-select-automatic-content').each(function () {
                        ipsInfo.push({
                            id: $(this).find('[name=ips_activity_child_id]').val(),
                            goodsSku: $id.find('textarea[name="ips_manual_sku"]').val()
                        });
                    });
                    break;
            };
            if (+skuFrom < 3){
                var nowData =  {
                    "skuFrom": skuFrom,
                    "ipsMethods": ipsMethods,
                    "ipsInfo": ipsInfo,
                    "goodsSku": goodsSku,
                    "goodsInfo": []
                }
                if (param) {
                    var arr = Object.keys(param);
                    if (arr.indexOf('isSync') > -1) {
                        nowData['isSync'] = param['isSync'];
                        delete param.isSync;
                    }
                    if (Object.keys(param) > 0) {
                        nowData['apiParams'] = param;
                    }
                }
                data.push(
                    nowData
                );
            }
            if(typeof GESHOP_STORE !== 'undefined'){
                GESHOP_STORE.dispatch('global/saveCurrentGoodsInfo', data);
            }
        }

        /**
         *  商品列表tab 组件自动刷新
         * */
        my.saveSyncTabInfo = function ($id, param) {
            let navData = [];
            let goodsData = [];
            let saveData = [];
            let $wrap = $id.find('.nav-form-wrap');
            $wrap.find('.wrap-config').each(function (index, item) {
                let obj = {};
                /** tab 分类名 */
                obj.navName = $(this).find('input[name="navName"]').val();
                obj.more_href = $(this).find('input[name="more_href"]').val() || '';

                $(this).find('.list-group-item').each(function (childIndex, item) {
                    let obj2 = {};
                    let goodsSku = $(this).find('textarea[name="goodsSKU"]').val();
                    /* 过滤非法空格[space]和回车[enter] */
                    if (goodsSku != '') {
                        goodsSku = $.trim(goodsSku).split(' ').join('').replace(/\\n/g, '').replace(/&#13;/g, '');
                    }
                    obj2.lists = goodsSku;
                    obj2.catIds = $(this).find('textarea[name="catIds"]').val();
                    /* 是否异步数据 */
                    /* obj2.isAsync = 1; */
                    obj2.key = index + '-' + childIndex;
                    /* ipsJson 获取IPS数据 */
                    let ipsJson = window.GESHOP_IPS.getIPSItemJson($(this));
                    obj2 = Object.assign({}, obj2, ipsJson);
                    obj = Object.assign({}, obj, obj2, ipsJson);

                    goodsData.push(obj2);
                });
                navData.push(obj);
                let ipsInfo = [];
                let goodsSKU = obj.skuFrom == 1 ? obj.lists : '';
                switch (parseInt(obj.ipsMethods)) {
                    // {# 自动 #}
                    case 1:
                        ipsInfo = obj.ips.level3;
                        break;
                    // {# 手动 #}
                    case 2:
                        ipsInfo.push({
                            id: obj.ips.gsSelectLevel2,
                            sku_num: ''
                        });
                        break;
                    // {# 规则添加（自动）暂无 #}
                    case 3:
                        ipsInfo.push({
                            goodsSku: obj.ips.ipsGoodsSKU
                        });
                        break;
                    // {# 筛选器添加（手动） #}
                    case 4:
                        ipsInfo.push({
                            id: obj.ips.ipsFilterInfo.ips_activity_child_id,
                            goodsSku: obj.ips.ipsFilterInfo.ips_manual_sku
                        });
                        /* goodsSku = obj.ips.ipsGoodsSKU; */
                        break;
                }
                var nowData = {};
                if (obj.skuFrom - 0 < 3){
                    nowData = {
                        'tab_index': index,
                        'skuFrom': obj.skuFrom || '1',
                        'ipsMethods': obj.ipsMethods || '',
                        'ipsInfo': ipsInfo,
                        'goodsSku': goodsSKU,
                        'goodsInfo': []
                    }
                    if (param) {
                        var arr = Object.keys(param);
                        if (arr.indexOf('isSync') > -1) {
                            nowData['isSync'] = param['isSync'];
                            // delete param.isSync;
                        }
                        if (Object.keys(param) > 0) {
                            nowData['apiParams'] = param;
                        }
                    }
                    saveData.push(
                        nowData
                    )
                }
            });
            GESHOP_STORE.dispatch('global/saveCurrentGoodsInfo', saveData);
            $wrap.parents().find('.nav-list-arr').val(JSON.stringify(navData));
        }

        my.getAjax = function (url, params) {
            var testDomain = 'https://dsn.apizza.net/mock/6a61c62f7a6af49c20eeedc8ba615f88'
            Design.enableLoading()
            return $.ajax({
                type: 'GET',
                url: url,
                data: params,
                dataType: 'json',
                error: function (err) {
                    layer.msg('接口异常,请稍后重试')
                },
                complete: function (xhr) {
                    Design.disableLoading()
                    var res = xhr.responseJSON
                    if (res.code != 0) {
                        layui.layer.msg(res.message || '请求错误')
                    }
                }
            })

        }
        my.postAjax = function (url, params) {
            return $.ajax({
                type: 'POST',
                url: url,
                data: params,
                dataType: 'json',
                success:function(res){
                    if(res.code !== 0){
                        layui.layer.msg(res.message || '请求错误')
                    }
                },
                error: function (err) {
                    layer.msg('接口异常,请稍后重试')
                }
            })
        }
        return my
    }({}))
}

/**
 * obs商品
 */

if (!GS_OBS) {
    var GS_OBS = (function (my) {
        /* 初始化select */
        my.initSelect = function (target) {
            var $target = target || '.radio-tab-true'
            /* 初始化select */
            $('.gs-obs-item', $target).select2({
                width: '115',
                language: {
                    noResults: function (params) {
                        return '无匹配数据'
                    }
                },
                placeholder: '请输入关键字'
            })
            my.initSelectChange()
        }
        /* 获取数据 */
        my.initSelectData = function ($target) {
            var $radioGroup = $target || $('.design-right')
            var targetValue = $radioGroup.find('[name=obsId]').val()	//板块id
            var page_id = $radioGroup.find('input[name=page_id]').val()
            if (!page_id) {
                // layui.layer.msg('OBS页面未选中');
                return
            }
            var url = '/soa/obs/section-list'
            var params = {
                'page_id': page_id,
                uiId: sessionStorage.currentComponentId,
                platform: typeof GESHOP_PLATFORM ? GESHOP_PLATFORM : ''
            }
            GsSelect.getAjax(url, params).done(function (res) {
                if (res.code == 0) {
                    my.initRequestCallback($radioGroup, res, targetValue)
                }
            })
        }

        /* 数据填充回调 */
        my.initRequestCallback = function (item, res, targetValue) {
            var $box = item || $('.goods-data-wrapper')
            var data = res.data
            var $select = $('.gs-select-level2.gs-obs-item', $box)
            $select.html('<option value="00">请选择板块</option>')
            var optionLists = '<option value="00">请选择板块</option>'
            for (var i = 0; i < data.length; i++) {
                optionLists += '<option value=' + data[i].id + '>' + data[i].name + '</option>'
            }
            $select.html(optionLists)

            $select.select2({
                width: '115',
                language: {
                    noResults: function (params) {
                        return '无匹配数据'
                    }
                },
                matcher: matchCustom
            })
            /* 回填第三级 */
            var $obsOldSKU = $box.find('[name=obs_skus]').val()
            if (targetValue) {
                $select.val(targetValue).trigger('change', $obsOldSKU)
            }
            // if ($obsOldSKU) {
            // 	$('[data-target="goodsDataFrom-3"]', $box).find('[name=goodsSKU]').val($obsOldSKU);
            // 	return false;
            // }
        }

        /* 变更监听 */
        my.initSelectChange = function ($target) {
            var $box = $target || $('.design-right')
            var $select = $('.gs-select-level2.gs-obs-item', $box)
            var url = '/soa/obs/product-list'
            var params = {}
            var section_id = ''
            var section_name = ''
            var $box_item = $select.closest('.goods-box-item')
            $select.off('change.obs').on('change.obs', function (event, obsOldSKU) {
                section_id = $(this).find('option:checked').val() || $box_item.find('[name=obsId]').val()
                section_name = $(this).find('option:checked').text() || $box_item.find('[name=obsName]').val()
                // var $skuInput = $box_item.find('[name=goodsSKU],[name=ipsGoodsSKU]');

                /* 填充obs板块内容  start*/
                $box_item.find('[name=obsId]').val(section_id)
                $box_item.find('[name=obsName]').val(section_name)

                // /* 使用以处理的SKU */
                // if (obsOldSKU) {
                // 	$skuInput.val(obsOldSKU);
                // 	return false;
                // }

                // if (section_id == '00') {
                // 	$skuInput.val('');
                // 	return false;
                // }
                // params = {
                // 	section_id: section_id
                // };

                // /* 获取obs section下 SKU*/
                // var goodsDataFrom = $('.radio-tab-true').find('[name=goodsDataFrom]:checked').val();
                // GsSelect.getAjax(url, params).done(function (res) {
                // 	if (res.code == 0 && res.data) {
                // 		$skuInput.val(res.data);
                // 		/* 校验 */
                // 		if (goodsDataFrom == '3') {
                // 			$skuInput.trigger('change');
                // 		}

                // 	}
                // })

            })
        }

        //obs提交校验
        my.submitValid = function () {
            var valid = true
            var goodsDataFrom = $('[name=goodsDataFrom]:checked').val()
            var obs_section_id = $('[data-target="goodsDataFrom-3"]').find('.gs-obs-item.gs-select-level2').val()
            if (goodsDataFrom == '3' && !(obs_section_id && obs_section_id != '00')) {
                layui.layer.msg('请选择OBS板块')
                valid = false
            }
            return valid
        }
        return my
    }({}))
}

layui.form.on('radio(goodsDataFrom)', function (data) {
    var value = Number(data.value)
    var $radioTabSelect = $('.design-form').find('.radio-tab-group [data-target=goodsDataFrom-' + value + '],.goods-data-wrapper [data-target=goodsDataFrom-' + value + ']')
    $radioTabSelect.addClass('goods-visible').siblings().removeClass('goods-visible')
    /* 处理单个商品管理输入框 */
    $radioTabSelect.find('[name=goodsSKU],[name=ipsGoodsSKU]').removeClass('Unwanted').end().siblings().find('[name=goodsSKU],[name=ipsGoodsSKU]').addClass('Unwanted')
})

if ($('.design-form .radio-tab-group').length > 0) {
    GsSelect.initSelect()
    GsSelect.initSelectFirst()
}

if ($('.design-form .goods-data-wrapper').length > 0) {
    var dataFrom = $('.design-form .goods-data-wrapper').attr('data-from')
    if (dataFrom == 'obs') {
        GS_OBS.initSelect()
        GS_OBS.initSelectData()
    }

}

/**
 * 获取组件校验接口传参
 * target jq selector
 * skuList 商品skus
 */
function getApiParams(target, skuList) {
    var $component = target.parents(".design-form:eq(0)")
    var api = $('input[name=param_api]', $component).val()
    if (!api) {
        return {}
    }
    var param_content = $('input[name=param_content]', $component).val();
    var filterObj = JSON.parse(param_content);
    var content = Object.assign({}, {
        "pipeline": GESHOP_PIPELINE || 'ZF',
        "lang": GESHOP_LANG || "en",
        "goodsSn": skuList
    }, filterObj)
    return {
        content: JSON.stringify(content),
        api: api,
        site_code: GESHOP_SITECODE
    }
}


/**
 *  商品类工具
 */
if( !GS_GOODS_UTILS ){
    var GS_GOODS_UTILS = (function(my){

        /**
         * sku字符串去重
         * @param value
         * @param callback
         * @returns {string}
         */
        my.getUnique = function(param){
            var value = param.value;
            var callback = param.callback;
            if( !value ){
                return ''
            }
            var res = /(\s{5,1000})/g;
            var reg = /\n/g;
            var skuList = value.replace(res, '').replace(reg, ',');
            var skuArr = skuList.split(',');
            if (skuArr[skuArr.length - 1] === '') {
                skuArr.pop()
            }
            /*去重*/
            var newArr = [];
            var repeatList = [];
            for (var i = 0; i < skuArr.length; i++) {
                var skuItem = skuArr[i];
                if (newArr.indexOf(skuItem) === -1) {
                    newArr.push(skuItem);
                }else{
                    if(repeatList.indexOf(skuItem) === -1){
                        repeatList.push(skuItem);
                    }
                }
            }

            if( callback && typeof callback === 'function'){
                callback({
                    value: newArr.toString(),
                    repeatList: repeatList.toString()
                });
            }
            return newArr.toString();
        }
        my.repeatCheck = function(target){
            var skus = $(target).val();
            var status = 1;
            this.getUnique({value:skus,callback:function(res){
                if(res.value){
                    $(target).val(res.value);
                }
                if(res.repeatList){
                    status = 0;
                    my.repeatToast(res.repeatList);
                }
            }});
            return status;
        }
        my.repeatToast = function(repeatList){
            var text = repeatList + ' sku重复，已自动去重，保留位置靠前的一个';
            layui.layer.open({
                title: false,
                btn: false,
                content: '<div style="padding-top:15px;text-align:center;">'+ text +'</div>',
                area: ['420px'],
                skin: 'element-ui-dialog-class dialog-repeat-toast',
                success:function(layero, index){
                    setTimeout(function(){
                        layui.layer.close(index)
                    },2000)
                }
            })
        }
        /**
         * 商品SKU校验
         */

        my.gsSKUValid = function (callback) {
            $('[name=goodsSKU][data-skuvalid=true],[data-skuvalid=true]').off('change').on('change', function (event, arg1) {
                var $this = $(this)
                var res = /(\s{5,1000})/g
                var reg = /\n/g
                var skuList = $(this).val().replace(res, '').replace(reg, ',')
                var skuArr = skuList.split(',')
                if (!skuList) {
                    return false
                }
                if (skuArr[skuArr.length - 1] === '') {
                    skuArr.pop()
                }
                /*去重*/
                var newArr = []
                for (var i = 0; i < skuArr.length; i++) {
                    if (newArr.indexOf(skuArr[i]) == -1) {
                        newArr.push(skuArr[i])
                    }
                }
                skuArr = newArr
                skuList = newArr.toString()
                $(this).val(skuList)

                var skuList = $(this).val();
                var addParams = getApiParams($this, skuList)
                Design.enableLoading();
                GsManager.goods_exists(skuList, null, addParams).done(function (res) {
                    Design.disableLoading()
                    if (res.code !== 0) {
                        gsSKUConfirm($this, skuList, res.message, callback)
                    } else {
                        if (arg1) {
                            arg1();
                        }
                    }
                }).fail(function () {
                    Design.disableLoading()
                })
            })

        }
        /* 展示弹窗内容 content */
        my.showConfirm = function(content,next){
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
        /**
         * 效验value 为大于等于1的正整数
         * false 默认msg,并清空
         * @param $id
         * @param $input
         * @param msg
         */
        my.numValid1 = function(obj){
            if(!obj.id || !obj.name){
                return false;
            }
            var $id = obj.id;
            var name = obj.name;
            var msg = obj.msg || '';
            $id.on('blur','[name='+name+']', function (e) {
                var $this = $(this);
                var val = $this.val();
                // val = val.length === 1 && val == '0' ? parseInt(val) : val;
                if (val && (!/^([1-9][0-9]*)$/.test(val) || val < 1)) {
                    layui.layer.msg(msg);
                    $this.val('');
                }
            });
        }
        /**
         *
         * @param hex
         * @param opacity
         * @returns {string}
         */
        my.hexToRgba = function(hex,opacity){
            return "rgba(" + parseInt("0x" + hex.slice(1, 3)) + "," + parseInt("0x" + hex.slice(3, 5)) + "," + parseInt("0x" + hex.slice(5, 7)) + "," + opacity + ")";
        }
        return my;
    })({})
}

/**
 * sku 清除错误sku
 * @param {*} $this  input对象
 * @param {*} skuList  sku列表
 * @param {*} message  sku错误提示
 * @param {*} callback 回调
 */
function gsSKUConfirm($this, skuList, message, callback) {
    layer.confirm('' + message + ',是否清空', {
        title: '提示',
        btn: ['否', '是'],
        area: '420px',
        icon: 3,
        skin: 'element-ui-dialog-class'
    }, function (index) {
        layui.layer.close(index)
    }, function (index) {
        if (callback) {
            callback()
        } else {
            var delSkuArr = message.split(' ')[1].split(','),
                skuListArr = skuList.split(','),
                newSku = ''
            delSkuArr.forEach(function (delItem) {
                skuListArr.forEach(function (skuItem, skuIndex) {
                    if (delItem == skuItem) {
                        delCouponCode(skuIndex)
                        skuListArr.splice(skuIndex, 1)
                    }
                })
            })
            newSku = skuListArr.toString()
            $this.val(newSku)
            layer.close(index)
        }

    })
}

/**
 * sku 批量清除错误sku
 * @param {*} $this  input对象
 * @param {*} skuList  sku列表
 * @param {*} message  sku错误提示
 * @param {*} callback 回调
 */
function gsSkuBatchConfirm(message, callback) {
    layer.confirm('' + message + ',是否清空', {
        title: '提示',
        btn: ['否', '是'],
        area: '420px',
        icon: 3,
        skin: 'element-ui-dialog-class'
    }, function (index) {
        layui.layer.close(index)
    }, function (index) {
        if (callback) {
            callback()
        } else {
            $('[name=goodsSKU],[data-confirmsku=true]').each(function (index, item) {
                var $this = $(item),
                    skuArry = [],
                    skuList = $this.val(),
                    // strArry = message.split(' '),
                    // strArry = message.indexOf(',') >= 0 ? message.split(',') : message.split(' '),

                    // PS: 接口返回数据杂乱无序，因此只能先这样临时处理一下
                    delSkuArr = message.split(' ')[1].split(','),
                    skuListArr = skuList.split(','),
                    strArry = '',
                    newSku = ''

                if (message.indexOf(',') >= 0) {
                    if (message.split(' ')[0].split(',') == '商品') {
                        strArry = message.split(' ')[1].split(',')
                    } else {
                        // emmm... 针对于SKU数量正确，对应的Coupon数量不正确状态
                        strArry = message.split(' ')[0].split(',')
                    }
                } else {
                    // emmm... 针对于多个或批量SKU错误和Coupon错误状态
                    strArry = message.split(' ')
                }

                for (var i = 0; i < strArry.length; i++) {
                    if (strArry[i] != null && strArry[i].length > 0) {
                        skuArry.push(strArry[i])
                    }
                }

                var skuListArrt = [],
                    skuDelList = skuArry

                for (var i = 0; i < skuDelList.length; i++) {
                    if (skuDelList[i].indexOf('_') > 0) {
                        skuListArrt.push(skuDelList[i])
                    }
                }

                skuListArrt.forEach(function (delItem) {
                    skuListArr.forEach(function (skuItem, skuIndex) {
                        if (delItem == skuItem) {
                            delCouponCode(skuIndex)
                            skuListArr.splice(skuIndex, 1)
                        }
                    })
                })
                newSku = skuListArr.toString()
                $this.val(newSku)
            })
            layer.close(index)
        }
    })
}


// Excel Coupon码删除 GMT+8 2019-01-09 14:50:10
function gsCouponValid() {
    $('[name=coupons][data-coupon-valid=true]').on('blur', function () {
        var $this = $(this),
            res = /(\s{5,1000})/g,
            reg = /\n/g,
            couponList = $(this).val().replace(res, '').replace(reg, ','),
            couponArr = couponList.split(',');
        if (!couponList) {
            return false;
        }
        if (couponArr[couponArr.length - 1] === '') {
            couponArr.pop();
        }

        couponList.toString();
        $(this).val(couponList);
        couponList = $(this).val();
    });
}

// SKU/Coupon关联数据批量删除 GMT+8 2019-01-09 14:50:10
function delCouponCode(couponIndex) {
    $('[name=coupons],[data-confirm-coupon=true]').each(function (index, item) {
        var $this = $(item),
            couponList = $this.val(),
            couponListArr = couponList.split(','),
            newCoupon = ''
        couponListArr.forEach(function (couponItem, itemIndex) {
            if (couponIndex == itemIndex) {
                couponListArr.splice(couponIndex, 1)
            }
        })
        newCoupon = couponListArr.toString()
        $this.val(newCoupon)
    })
}

// 批量清除错误Coupon码 GMT+8 2019-01-09 14:50:10
function gsCouponBatchConfirm(message, callback) {
    layer.confirm('' + message + ',是否清空', {
        title: '提示',
        btn: ['否', '是'],
        area: '420px',
        icon: 3,
        skin: 'element-ui-dialog-class'
    }, function (index) {
        layui.layer.close(index)
    }, function (index) {
        if (callback) {
            callback()
        } else {
            $('[name=coupons],[data-confirm-coupon=true]').each(function (index, item) {
                var $this = $(item),
                    couponList = $this.val()
                var delCouponArr = message.split(' ')[0].split(','),
                    couponListArr = couponList.split(','),
                    newCoupon = ''
                delCouponArr.forEach(function (delItem) {
                    couponListArr.forEach(function (couponItem, couponIndex) {
                        if (delItem == couponItem) {
                            couponListArr.splice(couponIndex, 1)
                        }
                    })
                })
                newCoupon = couponListArr.toString()
                $this.val(newCoupon)
            })
            layer.close(index)
        }
    })
}


$(function () {
    GS_GOODS_UTILS.gsSKUValid();
    gsCouponValid();
    Geshop_message.dataWatch();
})

/**
 * 表单必填input:text
 */
if (!GS_valid) {
    var GS_valid = function () {
        var obj = {booleans: true, message: '请输入必填', errorNum: 0}
        var $targetArr = $('.design-form-component').find('[data-formvalid=true]')
        $targetArr.each(function (index, item) {
            var val = $(item).val()
            if (!val) {
                obj.errorNum += 1
                obj = Object.assign(obj, {
                    booleans: false,
                    message: $(item).attr('data-message')
                })
                return false
            }
        })
        return obj
    }
}

/**
 * ips 筛选器
 * @type {{}}
 */
if (!Geshop_message) {
    var Geshop_message = (function (my) {
        var _this = my;
        my.$options = {
            initMessage: false,
            inputTarget:'',
            ipsWindow:'',
            maximum:400
        }
        my.init = function ($target, ips_type) {
            this.authentication(ips_type, function () {
                _this._init($target, ips_type);
            })
        }
        my._init = function ($target, ips_type) {
            this.$options.inputTarget = $target
            this.getIpsWindowInfo(ips_type,function(data){
                _this.openWindow(data.url);
                var $wrapper = $($target).parents('.gs-tab-select-wraper:eq(0)');
                $wrapper.find('input[name=ips_activity_child_id]').val(data.ips_activity_child_id);
            });
        }
        my.initMessage = function () {
            if (this.$options.initMessage) {
                return false;
            }
            /*
            * data.type    GESHOP-NOTIFY-2 自动|GESHOP-NOTIFY-1 筛选器添加
            * skus 商品SKU数组
            * */
            window.addEventListener('message', function (e) {
                var $currentInput = _this.$options.inputTarget,
                    $currentVal = $currentInput.val();
                var currentLengthValid = _this.limitNumTip($currentVal.split(',').length,true);
                if(!currentLengthValid){ return false;}
                if ($currentInput && e.data ) {
                    var type = e.data.type,
                        skus = e.data.skus,
                        composeVal
                    switch (type){
                        case 'GESHOP-NOTIFY-2':
                            composeVal = skus.toString()
                            break;
                        case 'GESHOP-NOTIFY-1':
                            composeVal = $currentVal !== '' && $currentVal[$currentVal.length - 1] !== ','  ? $currentVal + ',' + skus.toString() : $currentVal + skus.toString();
                            break;
                        default:
                            composeVal = skus.toString()
                            break;
                    }
                    if(composeVal){
                        var composeArr = composeVal.split(','),
                            maxVal = composeArr.slice(0,_this.$options.maximum).toString();
                        $currentInput.val(_this.dataInit(maxVal))

                        _this.limitNumTip(composeArr.length)
                    }
                    // $currentInput.trigger('change')
                }
            })
            //关闭ips窗口
            $('.design-form').on('click','.js_closeDesignForm',function(){
                _this.$options.ipsWindow.close();
            })
            this.$options.initMessage = true;
        }
        //商品数据最大限制
        my.limitNumTip = function(num,openStatus){
            var status = true
            if(num > _this.$options.maximum){
                status = false
                layui.layer.msg('商品数据已满，不支持推送更多');
            }
            if(openStatus){
                return status
            }

        }
        my.dataWatch = function(){
            $('#component_form').off('change').on('change','.ips-message-input',function(event,arg1){
                var skuVal = $(this).val();
                $(this).val(_this.dataInit(skuVal));
            })
        }
        my.dataInit = function(skuVal){
            var res = /(\s{5,1000})/g
            var reg = /\n/g
            var skuList = skuVal.replace(res, '').replace(reg, ',')
            var skuArr = skuList.split(',')

            var newArr = []
            for (var i = 0; i < skuArr.length; i++) {
                if (newArr.indexOf(skuArr[i]) == -1) {
                    newArr.push(skuArr[i])
                }
            }
            skuArr = newArr
            skuList = newArr.toString()
            return skuList
        },

        my.openWindow = function (url, name) {
            var w_name = name || 'ips_window';
            this.initMessage();
            var ipsWindow = window.open(url, w_name);
            this.$options.ipsWindow = ipsWindow;

            var activityInfo = {
                'activityId':'00'
            }
            ipsWindow.postMessage(activityInfo,"*")
        }

        my.authentication = function (ips_type,callback) {
            var url = '/common/activity-to-ips/get-ips-page-auth';
            var params = {is_auto_activity: ips_type && ips_type === 'rule' ? 2 : 1};
            GsSelect.getAjax(url, params).done(function (res) {
                if(res.code === 0 && res.data.is_has_auth){
                    typeof callback === 'function' && callback();
                }else{
                    layui.layer.msg(res.message || '无ips权限，请走we流程申请');
                }
            })
        }

        my.getIpsWindowInfo = function(ips_type,callback){
            var params = {
                page_id: $('#pageId').val(),
                id: sessionStorage.getItem('currentComponentId'),
                lang: $('#pageLang').val(),
                tpl_id: sessionStorage.getItem('currentTemplateId'),
                is_auto_activity: ips_type && ips_type === 'rule' ? 2 : 1
            }
            GsSelect.postAjax('/common/activity-to-ips/create-activity-to-ips',params).done(function(res){
                if(res.code == 0 ){
                    callback && typeof callback === 'function' && callback(res.data)
                }
            })
        }
        return my
    }({}))

}

if(!GS_formvalid){
    /**
     * 常用的组件form效验
     * rule：
     * var validRule = [{ name: 'goodsSKU', type: 'sku', max: '2',message:'SKU最多输入2个'}];
     * 流程：遍历rule> 对比通过效验条数，（true>执行next,false>报错）
     * @type {{}}
     */
    var GS_formvalid = (function(my){
        my.addFormValid = function(next){
            var rule = validRule;
            if(!rule){return false;}
            var $id = $('#component_form');
            var result = {
                total: rule.length,
                pass:0
            };
            rule.forEach(function(item){
                var status = true;
                // 使用item 指定容器$id
                if(item.selector){
                    $id = item.selector
                }
                if(item.type === 'sku'){
                    if(item.max){
                        $('[name='+item.name+']',$id).each(function(aindex,aitem){
                            var num = $(aitem).val().split(',');
                            if(num.length > item.max){
                                layui.layer.msg(item.message);
                                status = false;
                                return false;
                            }
                        });
                    };
                };
                if(item.type === 'value'){
                    // 必填
                    if(item.required){
                        $('[name='+item.name+']',$id).each(function(aindex,aChild){
                            var val = $(aChild).val();
                            if(!!!$(aChild).val()){
                                layui.layer.msg(item.message);
                                status = false;
                                return false;
                            }
                        });
                    }
                }

                if(status){
                    result.pass +=1;
                }else{
                    return false;
                };
            });
            if(result.total === result.pass){
                next && next();
            };
        }
        return my;
    }({}))
}
