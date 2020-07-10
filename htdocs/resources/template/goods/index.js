/**
 * gbad GB 广告落地页
 * 商品数据管理 GsManager
 */

var uiId = sessionStorage.currentComponentId;
var activityId = document.getElementById('activityId').value;
var GsManagApiPrefix = activityId > 0 ? '/activity/' : '/home/';
var GESHOP_SITE_GROUP_CODE = Cookies.get('site_group_code');
if (activityId > 0) {
	GsManagApiPrefix = (GESHOP_SITE_GROUP_CODE == 'gb' || GESHOP_SITE_GROUP_CODE == 'zf' || GESHOP_SITE_GROUP_CODE == 'dl' || GESHOP_SITE_GROUP_CODE == 'suk') ? '/activity/' + GESHOP_SITE_GROUP_CODE + '/' : '/activity/';
} else {
	GsManagApiPrefix = GESHOP_SITE_GROUP_CODE == 'zf' ? '/home/' + GESHOP_SITE_GROUP_CODE + '/' : '/home/';
}
var GbAdPrefix = Cookies.get('site_group_code') == 'gb' ? '/gbad/' : '/activity/';
var isGbSite = GESHOP_SITE_GROUP_CODE == 'gb' ? true : false;
/**
 * gbad 落地页判断
 */
if (typeof GESHOP_ACTIVITYTYPE != 'undefined' && GESHOP_ACTIVITYTYPE && GESHOP_ACTIVITYTYPE === 3) {
	GsManagApiPrefix = GbAdPrefix;
}

var layer = layui.layer;

function timestampToTime (timestamp) {
	var date = new Date(timestamp * 1000);//时间戳为10位需*1000，时间戳为13位的话不需乘1000
	Y = date.getFullYear() + '-';
	M = (date.getMonth() + 1 < 10 ? '0' + (date.getMonth() + 1) : date.getMonth() + 1) + '-';
	D = date.getDate() + ' ';
	h = date.getHours() + ':';
	m = date.getMinutes() + ':';
	s = date.getSeconds();
	return Y + M + D + h + m + s;
}

if (!GsManager) {
	$('body').on('click', '#gs_getList,.class-manage', function () {
		var $layerWrap = $('#goods_layer_wrap'),
			$manageBtn = $(this);
		/**sku 输入框
		 * goodsType 是否是分类数据,tab,tab_new
		 * dataFrom 商品数据来源 ips,obs
		 */
		var $form = $('#component_form'),
			$targetInput,
			goodsType = $form.find('.layui-tab-content').attr('data-goodsType'),
			dataFrom = $manageBtn.attr('data-from');
		if (!dataFrom) {
			if (goodsType == 'tab') {
				$targetInput = $manageBtn.parent().prev().find('[name=goodsSKU]');
			} else if (goodsType == 'tab_new') {
				$targetInput = $manageBtn.parents('.goods-tab-item:eq(0)').find('[name=goodsSKU]');
			} else {
				$targetInput = $form.find('[name=goodsSKU]');
			}
		} else {
			if (goodsType == 'tab') {
				$targetInput = $manageBtn.parent().prev().find('[name=' + dataFrom + 'GoodsSKU][data-from=' + dataFrom + ']');
			} else if (goodsType == 'tab_new') {
				$targetInput = $manageBtn.parents('.goods-tab-item:eq(0)').find('[name=' + dataFrom + 'GoodsSKU][data-from=' + dataFrom + ']');
			} else if (goodsType == 'tab_new2') {
				$targetInput = $form.find('[name=goodsSKU]');
			} else {
				$targetInput = $manageBtn.prev('[name=' + dataFrom + 'GoodsSKU][data-from=' + dataFrom + ']');
			}

			if (dataFrom == 'tabs') {
				$targetInput = $manageBtn.prev('.rest-input').find('textarea[name=goodsSKU]');
			}
		}
		var skus = $targetInput.val();

		if (!skus) {
			layer.msg('请输入SKU');
			return false;
		}
		var $tempGoodsTarget = $('input[name=tempGoodsArr]');
		Design.enableLayuiLoading();
		$tempGoodsTarget.val(skus);
		// $trIndex
		if (!$layerWrap.length) {
			var tableWrapper = '';
			if (dataFrom == 'ips') {
				tableWrapper = '<div id="goods_layer_wrap"><input type="hidden" name="tempGoodsArr"></input><table id="goods_layer_table" lay-filter="gs_goodstable"></table>' +
					'<div id="goods_toolbar">' +
					'<a class="layui-btn layui-btn-xs" lay-event="class-up" data-type="class-up" title="上移">上移</a><a class="layui-btn layui-btn-xs" lay-event="class-down" data-type="class-down">下移</a></div></div>';
			} else {
				tableWrapper = '<div id="goods_layer_wrap"><input type="hidden" name="tempGoodsArr"></input><table id="goods_layer_table" lay-filter="gs_goodstable"></table>' +
					'<div id="goods_toolbar"><a class="layui-btn layui-btn-xs" lay-event="class-edit" data-type="class-edit">替换</a>' +
					'<a class="layui-btn layui-btn-xs" lay-event="class-add" data-type="class-add">增加</a><a class="layui-btn layui-btn-xs" lay-event="class-del" data-type="class-del">删除</a>' +
					'<a class="layui-btn layui-btn-xs" lay-event="class-up" data-type="class-up" title="上移">上移</a><a class="layui-btn layui-btn-xs" lay-event="class-down" data-type="class-down">下移</a></div></div>';
			}
			$('body').append(tableWrapper);
		}
		/* 分类SKU */
		// if ($(this).hasClass('class-manage')) {
		// 	var $tr = $(this).parents('tr:eq(0)'),
		// 		$trIndex = $('#design_form .goods-form-table').index($tr)
		// }

		var url = GsManagApiPrefix + 'goods/tpl-goods-list';
		var Table = layui.table;
		var data = { lang: GESHOP_LANG || 'en', skus: skus, uiId: uiId };
		if (GESHOP_SITECODE.indexOf('gb') != -1) {
			data.pipeline = sessionStorage.getItem('gb_channel');
		}
		var site_group_code = typeof GESHOP_SITECODE != undefined ? GESHOP_SITECODE.split('-')[0] : '' || Cookies.get('site_group_code');
		var clothingSiteCol = [
			{ type: 'checkbox', fixed: 'left' }
			, {
				field: 'numbers', title: '序号', width: 80, templet: function (d) {
					var value = d.LAY_INDEX;
					return '<input type="number" name="goods_item_orders" value="' + value + '" data-order="' + value + '" style="width: 100%;"> ';
				}
			}
			, { field: 'goods_id', title: '商品ID', width: 100 }
			, { field: 'goods_sn', title: '商品SKU', width: 140 }
			, { field: 'goods_title', title: '商品标题', width: 100 }
			, {
				field: 'goods_img', title: '商品图片', width: 100, height: 100
				, templet: function (d) {
					return '<img src="' + d.goods_img + '">';
				}
			}
			, {
				field: 'is_on_sale', title: '上架', width: 80, templet: function (d) {
					var text = '';
					if (site_group_code == 'gb') {
						text = d.is_on_sale == '2' ? '上架' : (d.is_on_sale == '1') ? '下架' : '';
					} else {
						text = d.is_on_sale == '1' ? '上架' : (d.is_on_sale == '2') ? '下架' : '';
					}
					return text;
				}
			}
			, {
				field: 'goods_number', title: '库存', width: 80, templet: function (d) {
					var text = '';
					if (site_group_code == 'gb') {
						text = d.stock_num ? d.stock_num : 0;
					} else {
						text = d.goods_number ? d.goods_number : 0;
					}
					return text;
				}
			}
			, { field: 'promte_percent', title: '促销利润比', width: 100 }
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
			, { field: 'activity_number', title: '活动库存', width: 100 }
			, { field: 'activity_volume_number', title: '活动库存销量', width: 120 }
			, {
				fixed: 'right',
				width: dataFrom && dataFrom == 'ips' ? 150 : 250,
				align: 'center',
				toolbar: '#goods_toolbar'
			}
			// , {
			// 	field: 'options', 'title': '操作', width: 200
			// 	, templet: function (d) {
			// 		return '<button class="layui-btn layui-btn-xs" data-type="class-edit">替换</button><button class="layui-btn layui-btn-xs" data-type="class-add">增加</button><button class="layui-btn layui-btn-xs" data-type="class-del">删除</button>'
			// 		// return '<i class="layui-icon layui-icon-prev class-top" data-type="class-top"  title="置顶"></input><i class="layui-icon layui-icon-next class-bottom" data-type="class-bottom" title="置底"></i>' +
			// 		// '<i class="layui-icon class-up" data-type="class-up" title="上移">&#xe619;</i><i class="layui-icon class-down" data-type="class-down" title="下移">&#xe61a;</i>' +
			// 		// '<i class="layui-icon class-close" data-type="class-close" title="删除">&#x1006;</i>'
			// 	}
			// }
		];
		var gbSiteCol = [
			{ type: 'checkbox', fixed: 'left' }
			, {
				field: 'numbers', title: '序号', width: 80, templet: function (d) {
					var value = d.LAY_INDEX;
					return '<input type="number" name="goods_item_orders" value="' + value + '" data-order="' + value + '" style="width: 100%;"> ';
				}
			}
			// , { field: 'goods_id', title: '商品ID', width: 100 }
			, { field: 'goods_sn', title: '商品SKU', width: 140 }
			, { field: 'couponCode', title: 'Coupon码', width: 100 }
			, { field: 'goods_title', title: '商品标题', width: 200 }
			, {
				field: 'goods_img', title: '商品图片', width: 100, height: 100
				, templet: function (d) {
					return '<img src="' + d.goods_img + '">';
				}
			}
			, { field: 'shop_price', title: '本店售价', width: 100 }
			, { field: 'stock_num', title: 'OMS库存', width: 100 }
			, { field: 'limitCount', title: 'Coupon剩余数量', width: 130 }
/*			, {
				field: 'is_on_sale', title: '上下架状态', width: 120, templet: function (d) {
					var text = '';
					if (site_group_code == 'gb') {
						text = d.is_on_sale == '2' ? '上架' : (d.is_on_sale == '1') ? '下架' : '';
					} else {
						text = d.is_on_sale == '1' ? '上架' : (d.is_on_sale == '2') ? '下架' : '';
					}
					return text;
				}
			}*/
			, {
				fixed: 'right',
				title: '操作',
				width: dataFrom && dataFrom == 'ips' ? 150 : 250,
				align: 'center',
				toolbar: '#goods_toolbar'
			}
			// , {
			// 	field: 'options', 'title': '操作', width: 200
			// 	, templet: function (d) {
			// 		return '<button class="layui-btn layui-btn-xs" data-type="class-edit">替换</button><button class="layui-btn layui-btn-xs" data-type="class-add">增加</button><button class="layui-btn layui-btn-xs" data-type="class-del">删除</button>'
			// 		// return '<i class="layui-icon layui-icon-prev class-top" data-type="class-top"  title="置顶"></input><i class="layui-icon layui-icon-next class-bottom" data-type="class-bottom" title="置底"></i>' +
			// 		// '<i class="layui-icon class-up" data-type="class-up" title="上移">&#xe619;</i><i class="layui-icon class-down" data-type="class-down" title="下移">&#xe61a;</i>' +
			// 		// '<i class="layui-icon class-close" data-type="class-close" title="删除">&#x1006;</i>'
			// 	}
			// }
		];
		var tableCol = isGbSite?gbSiteCol:clothingSiteCol;
		Table.render({
			elem: '#goods_layer_table'
			, height: 400
			// , width: 1180
			, url: url
			, where: data
			// , page: true
			, limit: 20
			, id: 'gs_goodstable'
			, cols: [tableCol]
			, done: function (res, curr, count) {
				Design.disableLayuiLoading();
				if (res.code !== 0 && res.message) {
					if ($('#goods_layer').length > 0) {
						var $tempGoodsTarget = $('input[name=tempGoodsArr]');
						var tempSkus = $tempGoodsTarget.val();
						gsSKUConfirm($tempGoodsTarget, tempSkus, res.message,null,function(){
							Table.reload('gs_goodstable', {
								where: {
									lang: GESHOP_LANG || 'en',
									skus: $tempGoodsTarget.val()
								}
							});
						})
					}else{
						/* 清除wrap */
						$('#goods_layer_wrap').next('.layui-form').remove();
						$('#goods_layer_wrap').remove();
						/* 清除错误sku */
						gsSKUConfirm($targetInput, skus, res.message);
					}
					return false;
				}

				// /* goods_layer存在 */
				if ($('#goods_layer').length > 0) {
					// var $copyTable = $('#goods_layer_table').next('.layui-form').find('.layui-table-box')
					// $('#goods_layer').find('.layui-form .layui-table-box').html($copyTable.html())
					var $newForm = $('#goods_layer_table').next('.layui-form'),
						$newFixBody = $newForm.find('.layui-table-main tbody'),
						$newFixLt = $newForm.find('.layui-table-fixed-l .layui-table-body tbody'),
						$newFixRt = $newForm.find('.layui-table-fixed-r .layui-table-body tbody'),
						$newHeader = $newForm.find('.layui-table-header:eq(0) thead');

					$('#goods_layer').find('.layui-table-main tbody').html($newFixBody.html());
					$('#goods_layer').find('.layui-table-fixed-l .layui-table-body tbody').html($newFixLt.html());
					$('#goods_layer').find('.layui-table-fixed-r .layui-table-body tbody').html($newFixRt.html());
					$('#goods_layer').find('.layui-table-header:eq(0) thead').html($newHeader.html());
					$('.layui-table-fixed-r .layui-table-body,.layui-table-fixed-l .layui-table-body', '#goods_layer').css('max-height', '392px');
					layui.form.render('checkbox');
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
						// btn: ['保存', '取消', '批量删除'],
						btn: ['取消', '确定', '批量删除'],
						btn1: function (index, layero) {
							layer.close(index);
						},
						btn2: function (index, layero) {
							var goodsOrder = $('input[name=tempGoodsArr]', '#goods_layer_wrap').val();
							// if (goodsType !== 'tab') {
							// 	layer.close(index)
							// }
							layer.close(index);
							$targetInput.val(goodsOrder);
						},
						btn3: function (index, layero) {
							var checkedLength = $('#goods_layer').find('.layui-table-fixed-l tbody .layui-form-checked').length;
							if(checkedLength < 1){
								layer.msg('请先选择商品',{time:500})
								return false;
							}
							layer.confirm('确定批量删除商品吗？删除后，商品将不再列表中展示！', { icon: 3, title: '提示',btn:['取消','确定'] },function(index){
								layer.close(index);
							}, function (index) {
									layer.msg('删除成功',{time:500},function(){
										$('#goods_layer').find('.layui-table-fixed-l tbody .layui-form-checked').each(function () {
											var $realBody = $('#goods_layer .layui-table-main tbody'),
												$fixTr = $(this).parents('tr:eq(0)'),
												$index = $fixTr.attr('data-index'),
												$tr = $realBody.find('tr[data-index=' + $index + ']');
											$fixTr.remove();
											$tr.remove();
											$('#goods_layer .layui-table-fixed-r tbody ').find('tr[data-index=' + $index + ']').remove();
										});
										layer.close(index);
										GsManager.countNum();
									})
							});

							return false;
						},
						success: function (layero, index) {
							GsManager.init();
						},
						end: function (index, layero) {
							/* 清除wrap */
							$('#goods_layer_wrap').next('.layui-form').remove();
							$('#goods_layer_wrap').remove();
						}
					});

				}

			}
		});

	});

	var $layer = $('#goods_layer');
	var $tobody = $('tbody', $layer);

	var GsManager = (function (my) {
		var $layerWrap, tempGoodsArr, Table = layui.table;
		my.init = function () {
			$layer = $('#goods_layer');
			$layerWrap = $('#goods_layer_wrap');
			tempGoodsArr = $('input[name=tempGoodsArr]', $layerWrap);
			var _this = this;
			_this.countNum();
			/* 监听序号 */
			$('#goods_layer').on('change', 'input[name=goods_item_orders]', function () {
				var $that = $(this), value = parseInt($(this).val()),
					orderValue = parseInt($(this).attr('data-order')),
					lastValue = parseInt($('#goods_layer').find('input[name=goods_item_orders]').length);
				if (value == 0 || !value || value < 0) {
					layer.msg('请输入正确的序号',{time:500})
					$that.val(orderValue);
					return false;
				}

				value = value > lastValue ? lastValue : value == 0 ? 1 : value;
				if (value == orderValue) {
					$that.val(orderValue);
					return false;
				}
				var $thatTr = $that.parents('tr:eq(0)'),
					$dataIndex = $thatTr.attr('data-index'),
					valueIndex = value - 1,
					$clone = $thatTr.clone(),
					$target = $('input[name=goods_item_orders]:eq(' + valueIndex + ')', $layer),
					$targetTr = $target.parents('tr:eq(0)');
				if (value > orderValue) {
					$targetTr.after($clone);
				} else {
					$targetTr.before($clone);
				}
				$thatTr.remove();

				/* fixed联动 checkbox及工具栏*/
				var $fixThatTr = $('#goods_layer .layui-table-fixed-r tbody').find('tr[data-index=' + $dataIndex + ']'),
					$fixTargetTr = $('#goods_layer .layui-table-fixed-r tbody').find('tr:eq(' + valueIndex + ')'),
					$fixClone = $fixThatTr.clone();

				var $fixLtTr = $('#goods_layer .layui-table-fixed-l tbody').find('tr[data-index=' + $dataIndex + ']'),
					$fixLtTargetTr = $('#goods_layer .layui-table-fixed-l tbody').find('tr:eq(' + valueIndex + ')'),
					$fixLtClone = $fixLtTr.clone();


				$fixThatTr.remove();
				$fixLtTr.remove();
				if (value > orderValue) {
					$fixTargetTr.after($fixClone);
					$fixLtTargetTr.after($fixLtClone);
				} else {
					$fixTargetTr.before($fixClone);
					$fixLtTargetTr.before($fixLtClone);
				}
				/* 存储顺序 */
				_this.countNum(function (index, element) {
					$(element).val(index).attr('data-order', index);
				});
				// _this.countNum()
			});

			/* 置顶置底上移下移删除 */
			layui.table.on('tool(gs_goodstable)', function (obj) {
				// var data = obj.data
				// var tr = obj.tr
				var layEvent = obj.event;
				if (layEvent == 'class-del') {
					layer.confirm('确定删除此商品吗？删除后，商品将不再列表中展示！', { icon: 3, title: '提示',btn:['取消','确定'] }, function(index){
						layer.close(index);
					},function (index) {
						layer.msg('删除成功',{time:500},function(){
							obj.del();
							_this.countNum();
							layer.close(index);
						})

					});
				}
			});

			/* tr移动,对应fix也移动 */
			$('#goods_layer').on('click', 'tbody tr td:last-child .layui-btn', function () {
				var $this = $(this),
					$fixTarget = $(this).parents('tr:eq(0)'),
					dataType = $this.attr('data-type'),
					$fixIndex = parseInt($fixTarget.attr('data-index')),

					$fixLtTarget = $('#goods_layer').find('.layui-table-fixed-l tr[data-index=' + $fixIndex + ']'),
					$target = $('#goods_layer').find('.layui-table-main tr[data-index=' + $fixIndex + ']'),
					$index = $fixIndex;
				// $target = $(this).parents('tr:eq(0)'),
				// dataType = $this.attr('data-type'),
				// $index = $('#goods_layer tbody tr').index($target),

				if (dataType) {
					switch (dataType) {
						case 'class-edit':
							GsManager.editOne($target, $index);
							break;
						case 'class-add':
							GsManager.addOne($target, $index);
							break;
						case 'class-up':
							GsManager.moveUp($target, $fixTarget, $fixLtTarget);
							break;
						case 'class-down':
							GsManager.moveDown($target, $fixTarget, $fixLtTarget);
							break;
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

			});

		};
		my.countNum = function (itemFn) {
			var temp = [];
			$('#goods_layer input[name=goods_item_orders]').each(function (index, element) {
				var order = index + 1;
				$(this).val(order).attr('data-order', order);
				var sku = $(this).parents('tr:eq(0)').find('td[data-field="goods_sn"] div').text();
				temp.push(sku);
				if (itemFn) itemFn(order, element);
			});
			tempGoodsArr.val(temp.toString());
			/* 重载checkbox */
			layui.form.render('checkbox');
		};

		my.delOne = function ($target) {
			var _this = this;
			layer.confirm('确定删除此商品吗？删除后，商品将不再列表中展示！', { icon: 3, title: '提示' }, function (index) {
				$target.remove();
				_this.countNum();
				layer.close(index);
			});

		};

		my.addOne = function ($target, $index) {
			var _this = this;
			var addTemp = '';
			addTemp += '<div id="gs_goods_add" class="layui-form">\n';
			addTemp += '	<div class="layui-form-item">\n';
			addTemp += '		<label class="layui-form-label">商品SKU<\/label>\n';
			addTemp += '		<div class="layui-input-block">\n';
			addTemp += '			<textarea type="text" name="addSKU" class="layui-textarea Unwanted" data-skuvalid="true" data-confirmsku="true" placeholder="请输入商品的SKU"><\/textarea>\n';
			addTemp += '         <span class="goodsTip" style="margin: 10px 0;"><i class="layui-icon" style="margin-right: 5px;color: #e0b571;"></i>注意:请输入商品编号（SKU ID），编号与编号间用英文逗号隔开</span>';
			addTemp += '		<\/div>\n';
			addTemp += '	<\/div>\n';
			addTemp += '<\/div>\n';

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
				btn: ['取消', '保存'],
				btn1: function (index, layero) {
					layer.close(index)
				},
				btn2: function (index, layero) {
					var $tempArr = $('#goods_layer_wrap input[name=tempGoodsArr]'),
						$skuNew = $('textarea[name=addSKU]'),
						skuNew = $skuNew.val(),
						tempGoodsArr = $tempArr.val().split(','),
						tempGoodsNew = '';
/*					var skuStatu = _this.validate(skuNew);
					if (!skuStatu) {
						return false;
					}*/
					// 新增 sku验证
					var is_goods_exists = false
					//重复sku处理
					if (!skuNew) {
						layer.msg('SKU不能为空');
						return false;
					}
					_this.clearExistsSKU($skuNew,skuNew)

					//新增sku
					skuNew = $skuNew.val();
					if(skuNew){
						_this.goods_exists(skuNew).done(function (res) {
							if (res.code == 0) {
								layer.msg('新增成功');
								is_goods_exists = true;
							} else {
								gsSkuBatchConfirm(res.message);
								// layer.msg(res.message);
								return false;
							}

							tempGoodsArr.splice($index + 1, 0, skuNew);
							tempGoodsNew = tempGoodsArr.toString();
							$tempArr.val(tempGoodsNew);


							Table.reload('gs_goodstable', {
								where: {
									lang: GESHOP_LANG || 'en',
									skus: tempGoodsNew
								}
							});
							layer.close(index);
							// $('#goods_layer_wrap').find('.layui-goods-list').remove().end().next('.layui-layer-shade').remove()
						});
					}


					return is_goods_exists

				},
				success: function (layero, index) {
					if(isGbSite){
						gsSKUValid(null,true);
					}

				},
				end: function (index, layero) {
				}
			});
		};

		my.editOne = function ($target, $index) {
			var _this = this,
				// $layer = $('#goods_layer'),
				$layerWrap = $('#goods_layer_wrap'),
				imgUrl = $target.find('td[data-field="goods_img"] img').attr('src'),
				addWrapId = '#gs_goods_add';
			var addTemp = '';
			addTemp += '<div id="gs_goods_add" class="layui-form">\n';
			addTemp += '	<div class="layui-form-item">\n';
			addTemp += '		<label class="layui-form-label">商品SKU<\/label>\n';
			addTemp += '		<div class="layui-input-block">\n';
			addTemp += '			<input type="text" name="replaceSKU" class="layui-input Unwanted" placeholder="请输入单个SKU">\n';
			addTemp += '		<\/div>\n';
			addTemp += '	<\/div>\n';
			addTemp += '	<div class="layui-form-item">\n';
			addTemp += '		<label class="layui-form-label">商品图片<\/label>\n';
			addTemp += '		<div class="layui-input-block">\n';
			addTemp += '			<img src="' + imgUrl + '" class="gs-good-preview" style="max-width: 100%;">\n';
			addTemp += '		<\/div>\n';
			addTemp += '	<\/div>\n';
			addTemp += '<\/div>\n';

			var replaceSKULength = 1;
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
				btn: ['取消', '保存'],
				btn1: function (index, layero) {
					layer.close(index);
				},
				btn2: function (index, layero) {
					if(replaceSKULength > 1){
						return false;
					}else if(replaceSKULength<1){
						layer.msg('SKU不能为空');
						return false;
					}
					var $tempArr = $('input[name=tempGoodsArr]', $layerWrap),
						skuNew = $('input[name=replaceSKU]', addWrapId).val(),
						tempGoodsArr = $tempArr.val() ? $tempArr.val().split(',') : [],
						tempGoodsNew = '';
					var skuStatu = _this.validate(skuNew,function (errorMessage) {
						gsSkuBatchConfirm('商品 '+errorMessage+' 重复',function(){
							$('input[name=replaceSKU]', addWrapId).val('')
						});
					});
					if (!skuStatu) {
						return false;
					}
					// var skuExists = false;
					var is_goods_exists = false
					_this.goods_exists(skuNew).done(function (res) {
						if (res.code == 0) {
							layer.msg('替换成功');
							is_goods_exists = true
						} else {
							gsSkuBatchConfirm(res.message,function(){
								$('input[name=replaceSKU]', addWrapId).val('')
							});
							return false;
						}

						tempGoodsArr.splice($index, 1, skuNew);
						tempGoodsNew = tempGoodsArr.toString();
						$tempArr.val(tempGoodsNew);
						Table.reload('gs_goodstable', {
							where: {
								lang: GESHOP_LANG || 'en',
								skus: tempGoodsNew
							}
						});
						layer.close(index);
						// $('#goods_layer_wrap').find('.layui-goods-list').remove().end().next('.layui-layer-shade').remove()
					});
					return is_goods_exists

				},
				success: function (layero, index) {
					var $replaceInput = $('input[name=replaceSKU]', addWrapId)
					$replaceInput.on('change', function () {
						var sku = $(this).val(),
							url = GsManagApiPrefix + 'goods/tpl-goods-list';
						var skuLength = sku.split(',').length;
						replaceSKULength = skuLength;
						if(skuLength>1){
							layer.msg('请输入单个SKU',{time:500},function(){
								$replaceInput.val('');
							});
							return false;
						}
						//获取即将替换的SKU图片
						var data = { lang: GESHOP_LANG || 'en', skus: sku, uiId: uiId };
						if (GESHOP_SITECODE.indexOf('gb') != -1) {
							data.pipeline = sessionStorage.getItem('gb_channel');
						}
						$.ajax({
							type: 'GET',
							url: url,
							data: data,
							dataType: 'json',
							success: function (res) {
								if (res.code == 0 && res.data.length > 0) {
									$('.gs-good-preview', addWrapId).attr('src', res.data[0].goods_img);
								} else {
									$('.gs-good-preview', addWrapId).attr('src', '');
									// layer.msg(res.message)
								}
							},
							error: function (res) {
								layer.msg(res.message || '连接错误');
							}
						});
					});

				},
				end: function (index, layero) {
				}
			});
		};

		my.moveUp = function ($target, $fixTarget, $fixLtTarge) {
			var $trPrev = $target.prev('tr');
			if (0 != $trPrev.length) {
				var $clone = $target.clone();
				$target.remove();
				$trPrev.before($clone);

				if ($fixTarget) {
					[$fixTarget, $fixLtTarge].forEach(function (value, index) {
						var $fixTrPrev = value.prev('tr');
						if (0 != $fixTrPrev.length) {
							var $fixClone = value.clone();
							value.remove();
							$fixTrPrev.before($fixClone);
						}
					});
				}

				this.countNum();
			}
		};

		my.moveDown = function ($target, $fixTarget, $fixLtTarge) {
			var $trNext = $target.next('tr');
			if (0 != $trNext.length) {
				var $clone = $target.clone();
				$target.remove();
				$trNext.after($clone);

				if ($fixTarget) {
					[$fixTarget, $fixLtTarge].forEach(function (value, index) {
						var $fixTrNext = value.next('tr');
						if (0 != $fixTrNext.length) {
							var $fixClone = value.clone();
							value.remove();
							$fixTrNext.after($fixClone);
						}
					});
				}

				this.countNum();
			}

		};

		/* SKU本地校验 */
		my.validate = function (skuNew,tipCall) {
			if (!skuNew) {
				layer.msg('SKU不能为空');
				return false;
			}
			var $tempArr = $('#goods_layer_wrap input[name=tempGoodsArr]'),
				skuArr = skuNew.split(','),
				skuStatu = true,
				skuError = [];
			skuArr.forEach(function (element, index) {
				if ($tempArr.val().indexOf(element) !== -1) {
					skuError.push(element);
					skuStatu = false;
					return false;
				}
			});
			if (!skuStatu) {
				if(!tipCall){
					layer.msg('商品 ' + skuError.toString() + ' 已存在');
				}else if(tipCall && typeof tipCall === 'function'){
					tipCall(skuError.toString())
				}


				return false;
			} else {
				return true;
			}
		};
		/* 新增替换判断返回已存在的SKU */
		my.getExistsSKU = function(skuNew){
			var $tempArr = $('#goods_layer_wrap input[name=tempGoodsArr]'),
				tempVal = $tempArr.val(),
				skuArr = skuNew.split(','),
				skuError = [],
				errorString = '';
			skuArr.forEach(function (element, index) {
				if (tempVal.indexOf(element) !== -1) {
					skuError.push(element);
				}
			});
			errorString = skuError.toString()
			return {
				skuError:errorString,
				errorMessage:"商品 "+errorString+" 格式错误(sku_仓库)"
			}
		}
		/* 新增替换判断清除已存在的SKU */
		my.clearExistsSKU = function(target,skuNew){
			var $tempArr = $('#goods_layer_wrap input[name=tempGoodsArr]'),
				delSkuArr = $tempArr.val().split(','),
				skuListArr = skuNew.split(','),
				newSku = '';
			delSkuArr.forEach(function (delItem) {
				skuListArr.forEach(function (skuItem, skuIndex) {
					if (delItem == skuItem) {
						skuListArr.splice(skuIndex, 1);
					}
				});
			});
			$(target).val(skuListArr.toString());
		}

		my.goods_exists = function (sku, callback) {
			var url = GsManagApiPrefix + 'goods/tpl-goods-exists';
			var data = { lang: GESHOP_LANG || 'en', skus: sku, uiId: uiId, pipeline: '' };
			if (GESHOP_SITECODE.indexOf('gb') != -1) {
				data.pipeline = sessionStorage.getItem('gb_channel');
			}
			return $.ajax({
				type: 'GET',
				url: url,
				data: data,
				dataType: 'json',
				fail: function () {
					layer.msg('连接服务器失败');
				}
			});
		};

		my.goTop = function ($target) {
			var $index = $tbody.find('tr').index($target);
			if (0 != $index) {
				var clone = $target.clone();
				$tobody.prepend(clone);
				$target.remove();
			}
		};

		my.goBottom = function ($target) {
			var $index = $tbody.find('tr').index($target),
				$length = $tbody.find('tr').length;
			if ($length != $index) {
				var clone = $target.clone();
				$tobody.append(clone);
				$target.remove();
			}
		};

		return my;
	}({}));


}


/**
 * 选品系统
 */
var site_code = typeof GESHOP_SITECODE != undefined ? GESHOP_SITECODE : '';

function matchCustom (params, data) {
	// If there are no search terms, return all of the data
	if ($.trim(params.term) === '') {
		return data;
	}

	// Do not display the item if there is no 'text' property
	if (typeof data.text === 'undefined') {
		return null;
	}

	// `params.term` should be the term that is used for searching
	// `data.text` is the text that is displayed for the data object
	if (data.text.indexOf(params.term) > -1) {
		var modifiedData = $.extend({}, data, true);
		// modifiedData.text += ' (matched)';

		// You can return modified objects from here
		// This includes matching the `children` how you want in nested data sets
		return modifiedData;
	}

	// Return `null` if the term should not be displayed
	return null;
}


if (!GsSelect) {
	var GsSelect = (function (my) {
		var level0, level1, level2;
		my.initSelect = function (target) {
			var $target = target || '.radio-tab-true';
			/* 初始select */
			$('.gs-select-box', $target).select2({
				width: '115',
				language: {
					noResults: function (params) {
						return '无匹配数据';
					}
				},
				placeholder: '请输入关键字'
			});

			level0 = $('input[name=gsSelectLevel0]', $target).val();
			level1 = $('input[name=gsSelectLevel1]', $target).val();
			level2 = $('input[name=gsSelectLevel2]', $target).val();


			my.initSelectChange($target);
		};
		/* 变更监听 */
		my.initSelectChange = function ($target) {
			var $selectBox = $target || $('.design-right');
			/* 一级变更 */
			$('.gs-select-box.gs-select-level0', $selectBox).on('change.ips', function () {
				var activity_id = $(this).val();
				var $radioGroup = $(this).closest('.radio-tab-group');
				var level1Value = $('input[name=gsSelectLevel1]', $radioGroup).val();
				var levelVal = level1Value ? level1Value : level1;
				$('.gs-select-box:eq(1)', $radioGroup).html('<option value="00">请选择活动</option>');
				$('.gs-select-box:eq(2)', $radioGroup).html('<option value="00">请选择活动</option>');

				if (activity_id == '00' || !activity_id) {
					return false;
				} else {
					$('.gs-select-box:eq(1)', $radioGroup).parent().removeClass('layui-hide');
				}
				my.getAjax('/soa/ips/activity-group-list', { activity_id: activity_id }).done(function (res) {

					my.initRequestCallback($radioGroup, res, 1, levelVal);
					level1 = null;
				}).fail(function () {
					layer.msg('选品连接失败');
				});
			});

			$('.gs-select-box.gs-select-level1', $selectBox).on('change.ips', function () {
				var activity_id = $(this).val();
				var $radioGroup = $(this).closest('.radio-tab-group');
				var level2Value = $('input[name=gsSelectLevel2]', $radioGroup).val();
				var levelVal = level2Value ? level2Value : level2;
				if (activity_id == '00') {
					$('.gs-select-box:eq(2)', $radioGroup).html('<option value="00">请选择活动</option>');
					return false;
				} else {
					$('.gs-select-box:eq(2)', $radioGroup).parent().removeClass('layui-hide');
				}
				if (!activity_id) {
					return false;
				}

				my.getAjax('/soa/ips/activity-child-list', { activity_child_group_id: activity_id }).done(function (res) {

					my.initRequestCallback($radioGroup, res, 2, levelVal);
					level2 = null;
				}).fail(function () {
					layer.msg('选品连接失败');
				});
			});

			$('.gs-select-box.gs-select-level2', $selectBox).on('change.ips', function () {
				var activity_child_id = $(this).val();
				var $radioGroup = $(this).closest('.radio-tab-group');
				var level2Value = $('input[name=gsSelectLevel2]', $radioGroup).val();

				if (!activity_child_id || activity_child_id == '00') {
					return false;
				}
				var params = {
					lang: $('#pageLang').val(),
					page_id: $('#pageId').val(),
					id: sessionStorage.getItem('currentComponentId'),
					tpl_id: sessionStorage.getItem('currentTemplateId'),
					activity_child_id: activity_child_id
				};
				my.getAjax('/soa/ips/get-activity-goods-sku', params).done(function (res) {
					if (res.code == 0 && res.data.sku) {
						$radioGroup.find('[name=ipsGoodsSKU]').val(res.data.sku);
					}

				}).fail(function () {
					layer.msg('选品SKU获取失败');
				});
			});

		};
		my.initSelectFirst = function ($target) {
			var $radioGroup = $target || null;
			var level0 = $('input[name=gsSelectLevel0]', $radioGroup).val();
			my.getAjax('/soa/ips/activity-list', { site_code: site_code }).done(function (res) {
				if (level0) {
					my.initRequestCallback($radioGroup, res, 0, level0);
					level0 = null;
				} else {
					my.initRequestCallback($radioGroup, res, 0, null);
				}

			}).fail(function () {
				layer.msg('选品连接失败');
			});
		};

		my.initSelectFirstGroup = function () {
			my.getAjax('/soa/ips/activity-list', { site_code: site_code }).done(function (res) {

				$('.radio-tab-true').find('.radio-tab-group').each(function () {
					var $radioGroup = $(this);
					var level0 = $('input[name=gsSelectLevel0]', $radioGroup).val();
					if (level0) {
						my.initRequestCallback($radioGroup, res, 0, level0);
						level0 = null;
					} else {
						my.initRequestCallback($radioGroup, res, 0, null);
					}

				});
			}).fail(function () {
				layer.msg('选品连接失败');
			});
		};

		/**
		 * 初始前回调渲染
		 * @param {*} res
		 * @param {*} targetIndex 第几级上标
		 * @param {*} targetValue 默认选中值
		 */
		my.initRequestCallback = function ($item, res, targetIndex, targetValue) {
			var targetIndex = targetIndex || 0;
			var $box = $item || $('.radio-tab-true .radio-tab-group');
			if (res.code == '0') {
				var data = my.transformData(res.data.list);
				if (targetIndex == 0) {

				}

				$('.gs-select-box:eq(' + targetIndex + ')', $box).html('<option value="00">请选择活动</option>');
				if (data.length === 0) {
					return false;
				}
				var optionLists = '<option value="00">请选择活动</option>';
				for (var i = 0; i < data.length; i++) {
					optionLists += '<option value=' + data[i].id + '>' + data[i].text + '</option>';
				}
				$('.gs-select-box:eq(' + targetIndex + ')', $box).html(optionLists);


				$('.gs-select-box:eq(' + targetIndex + ')', $box).select2({
					width: '115',
					language: {
						noResults: function (params) {
							return '无匹配数据';
						}
					},
					matcher: matchCustom
				});
				if (targetValue) {
					$('.gs-select-box:eq(' + targetIndex + ')', $box).val(targetValue).trigger('change');
				}
			} else {
				layer.msg(res.message);
			}
		};

		//格式化select data
		my.transformData = function (data) {
			return $.map(data, function (obj) {
				obj.id = obj.activity_id || obj.activity_child_group_id || obj.activity_child_id;
				obj.text = obj.activity_title || obj.activity_child_group_title || obj.activity_child_title;
				return obj;
			});
		};

		my.getAjax = function (url, params) {
			var testDomain = 'https://dsn.apizza.net/mock/6a61c62f7a6af49c20eeedc8ba615f88';
			Design.enableLoading();
			return $.ajax({
				type: 'GET',
				url: url,
				data: params,
				dataType: 'json',
				error: function (err) {
					layer.msg('接口异常,请稍后重试');
				},
				complete: function (xhr) {
					Design.disableLoading();
					var res = xhr.responseJSON;
					if (res.code != 0) {
						layui.layer.msg(res.message || '请求错误');
					}
				}
			});

		};
		my.postAjax = function (url, params) {
			return $.ajax({
				type: 'POST',
				url: url,
				data: params,
				dataType: 'json',
				error: function (err) {
					layer.msg('接口异常,请稍后重试');
				}
			});
		};


		return my;
	}({}));
}

/**
 * obs商品
 */

if (!GS_OBS) {
	var GS_OBS = (function (my) {
		/* 初始化select */
		my.initSelect = function (target) {
			var $target = target || '.radio-tab-true';
			/* 初始化select */
			$('.gs-obs-item', $target).select2({
				width: '115',
				language: {
					noResults: function (params) {
						return '无匹配数据';
					}
				},
				placeholder: '请输入关键字'
			});
			my.initSelectChange();
		};
		/* 获取数据 */
		my.initSelectData = function ($target) {
			var $radioGroup = $target || $('.design-right');
			var targetValue = $radioGroup.find('[name=obsId]').val();	//板块id
			var page_id = $radioGroup.find('input[name=page_id]').val();
			if (!page_id) {
				// layui.layer.msg('OBS页面未选中');
				return;
			}
			var url = '/soa/obs/section-list';
			var params = {
				'page_id': page_id,
				uiId: sessionStorage.currentComponentId,
				platform: typeof GESHOP_PLATFORM ? GESHOP_PLATFORM : ''
			};
			GsSelect.getAjax(url, params).done(function (res) {
				if (res.code == 0) {
					my.initRequestCallback($radioGroup, res, targetValue);
				}
			});
		};

		/* 数据填充回调 */
		my.initRequestCallback = function (item, res, targetValue) {
			var $box = item || $('.goods-data-wrapper');
			var data = res.data;
			var $select = $('.gs-select-level2.gs-obs-item', $box);
			$select.html('<option value="00">请选择板块</option>');
			var optionLists = '<option value="00">请选择板块</option>';
			for (var i = 0; i < data.length; i++) {
				optionLists += '<option value=' + data[i].id + '>' + data[i].name + '</option>';
			}
			$select.html(optionLists);

			$select.select2({
				width: '115',
				language: {
					noResults: function (params) {
						return '无匹配数据';
					}
				},
				matcher: matchCustom
			});
			/* 回填第三级 */
			var $obsOldSKU = $box.find('[name=obs_skus]').val();
			if (targetValue) {
				$select.val(targetValue).trigger('change', $obsOldSKU);
			}
			// if ($obsOldSKU) {
			// 	$('[data-target="goodsDataFrom-3"]', $box).find('[name=goodsSKU]').val($obsOldSKU);
			// 	return false;
			// }
		};

		/* 变更监听 */
		my.initSelectChange = function ($target) {
			var $box = $target || $('.design-right');
			var $select = $('.gs-select-level2.gs-obs-item', $box);
			var url = '/soa/obs/product-list';
			var params = {};
			var section_id = '';
			var section_name = '';
			var $box_item = $select.closest('.goods-box-item');
			$select.on('change.obs', function (event, obsOldSKU) {
				section_id = $(this).find('option:checked').val() || $box_item.find('[name=obsId]').val();
				section_name = $(this).find('option:checked').text() || $box_item.find('[name=obsName]').val();
				// var $skuInput = $box_item.find('[name=goodsSKU],[name=ipsGoodsSKU]');

				/* 填充obs板块内容  start*/
				$box_item.find('[name=obsId]').val(section_id);
				$box_item.find('[name=obsName]').val(section_name);

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

			});
		};

		//obs提交校验
		my.submitValid = function () {
			var valid = true;
			var goodsDataFrom = $('[name=goodsDataFrom]:checked').val();
			var obs_section_id = $('[data-target="goodsDataFrom-3"]').find('.gs-obs-item.gs-select-level2').val();
			if (goodsDataFrom == '3' && !(obs_section_id && obs_section_id != '00')) {
				layui.layer.msg('请选择OBS板块');
				valid = false;
			}
			return valid;
		};
		return my;
	}({}));
}

layui.form.on('radio(goodsDataFrom)', function (data) {
	var value = Number(data.value);
	var $radioTabSelect = $('.design-form').find('.radio-tab-group [data-target=goodsDataFrom-' + value + '],.goods-data-wrapper [data-target=goodsDataFrom-' + value + ']');
	$radioTabSelect.addClass('goods-visible').siblings().removeClass('goods-visible');
	/* 处理单个商品管理输入框 */
	$radioTabSelect.find('[name=goodsSKU],[name=ipsGoodsSKU]').removeClass('Unwanted').end().siblings().find('[name=goodsSKU],[name=ipsGoodsSKU]').addClass('Unwanted');
});

if ($('.design-form .radio-tab-group').length > 0) {
	GsSelect.initSelect();
	GsSelect.initSelectFirst();
}

if ($('.design-form .goods-data-wrapper').length > 0) {
	var dataFrom = $('.design-form .goods-data-wrapper').attr('data-from');
	if (dataFrom == 'obs') {
		GS_OBS.initSelect();
		GS_OBS.initSelectData();
	}

}


/**
 * 商品SKU校验
 * close_valid Booleans|是否验证sku
 */

function gsSKUValid (callback,close_valid) {
	$('[name=goodsSKU][data-skuvalid=true],[data-skuvalid=true]').on('change', function () {
		var $this = $(this);
		var res = /(\s{5,1000})/g;
		var reg = /\n/g;
		var skuList = $(this).val().replace(res, '').replace(reg, ',');
		var skuArr = skuList.split(',');
		if (!skuList) {
			return false;
		}
		if (skuArr[skuArr.length - 1] === '') {
			skuArr.pop();
		}
		/*去重*/
		var newArr = [];
		for (var i = 0; i < skuArr.length; i++) {
			if (newArr.indexOf(skuArr[i]) == -1) {
				newArr.push(skuArr[i]);
			}
		}
		skuArr = newArr;
		skuList = newArr.toString();
		$(this).val(skuList);

		if(close_valid){
			return false;
		}

		skuList = $(this).val();

		Design.enableLoading();
		GsManager.goods_exists(skuList).done(function (res) {
			Design.disableLoading();
			if (res.code !== 0) {
				gsSKUConfirm($this, skuList, res.message, callback);
			}
		}).fail(function () {
			Design.disableLoading();
		});
	});

}


/**
 * sku 清除错误sku
 * @param {*} $this  input对象
 * @param {*} skuList  sku列表
 * @param {*} message  sku错误提示
 * @param {*} callback 回调
 * @param {*} endCall 结束回调
 */
function gsSKUConfirm ($this, skuList, message, callback,endCall) {
	layer.confirm('' + message + ',是否清空', {
		title: '提示',
		btn: ['否', '是'],
		area: '420px',
		icon: 3,
		skin: 'element-ui-dialog-class'
	}, function (index) { layui.layer.close(index); }, function (index) {
		if (callback) {
			callback();
		} else {
			var delSkuArr = message.split(' ')[1].split(','),
				skuListArr = skuList.split(','),
				newSku = '';
			delSkuArr.forEach(function (delItem) {
				skuListArr.forEach(function (skuItem, skuIndex) {
					if (delItem == skuItem) {
						delCouponCode(skuIndex);
						skuListArr.splice(skuIndex, 1);
					}
				});
			});
			newSku = skuListArr.toString();
			$this.val(newSku);
			layer.close(index);
		}
		if(endCall){
			endCall()
		}

	});
}

/**
 * sku 批量清除错误sku
 * @param {*} $this  input对象
 * @param {*} skuList  sku列表
 * @param {*} message  sku错误提示
 * @param {*} callback 回调
 * @param {*} addFn 追加成功后回调
 */
function gsSkuBatchConfirm (message, callback,addFn) {
	layer.confirm('' + message + ',是否清空', {
		title: '提示',
		btn: ['否', '是'],
		area: '420px',
		icon: 3,
		skin: 'element-ui-dialog-class'
	}, function (index) { layui.layer.close(index); }, function (index) {
		if (callback) {
			callback();
		} else {
			$('[name=goodsSKU],[data-confirmsku=true]').each(function (index, item) {
				var $this = $(item),
					skuArry = [],
					skuList = $this.val(),
					delSkuArr = message.split(' ')[1].split(','),
					skuListArr = skuList.split(','),
					strArry = '',
					newSku = '';

				if (message.indexOf(',') >= 0 ) {
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

				for (var i = 0; i<strArry.length; i++) {
					if (strArry[i] != null && strArry[i].length > 0) {
						skuArry.push(strArry[i]);
					}
				}

				var skuListArrt = [],
					skuDelList = skuArry;

				for (var i = 0; i < skuDelList.length; i++) {
					try {
						if (skuDelList[i].indexOf('_') >= 0) {
							skuListArrt.push(skuDelList[i]);
						}
					} catch (error) {
						console.log('数据异常!');
					}
				}

				skuListArrt.forEach(function (delItem) {
					skuListArr.forEach(function (skuItem, skuIndex) {
						if (delItem == skuItem) {
							delCouponCode(skuIndex);
							skuListArr.splice(skuIndex, 1);
						}
					});
				});
				newSku = skuListArr.toString();
				$this.val(newSku);
			});

			layer.close(index);
			if(addFn){
				addFn();
			}
		}
	});
}


// Excel Coupon码删除 GMT+8 2019-01-09 14:50:10
function gsCouponValid () {
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
	$('[name=coupons],[data-confirm-coupon=true]').each(function(index, item) {
		var $this = $(item),
			couponList = $this.val(),
			couponListArr = couponList.split(','),
			newCoupon = '';
		couponListArr.forEach(function (couponItem, itemIndex) {
			if (couponIndex == itemIndex) {
				couponListArr.splice(couponIndex, 1);
			}
		});
		newCoupon = couponListArr.toString();
		$this.val(newCoupon);
	})
}

// 批量清除错误Coupon码 GMT+8 2019-01-09 14:50:10
function gsCouponBatchConfirm (message, callback) {
	layer.confirm('' + message + ',是否清空', {
		title: '提示',
		btn: ['否', '是'],
		area: '420px',
		icon: 3,
		skin: 'element-ui-dialog-class'
	}, function (index) { layui.layer.close(index) }, function (index) {
		if (callback) {
			callback();
		} else {
			$('[name=coupons],[data-confirm-coupon=true]').each(function(index, item) {
				var $this = $(item),
					couponList = $this.val();
				var delCouponArr = message.split(' ')[0].split(','),
					couponListArr = couponList.split(','),
					newCoupon = '';
				delCouponArr.forEach(function (delItem) {
					couponListArr.forEach(function (couponItem, couponIndex) {
						if (delItem == couponItem) {
							couponListArr.splice(couponIndex, 1)
						}
					})
				});
				newCoupon = couponListArr.toString();
				$this.val(newCoupon);
			})
			layer.close(index);
		}
	})
}




$(function () {
	gsSKUValid();
	gsCouponValid();
})

/**
 * 表单必填input:text
 */
if (!GS_valid) {
	var GS_valid = function () {
		var obj = { booleans: true, message: '请输入必填', errorNum: 0 };
		var $targetArr = $('.design-form-component').find('[data-formvalid=true]');
		$targetArr.each(function (index, item) {
			var val = $(item).val();
			if (!val) {
				obj.errorNum += 1;
				obj = Object.assign(obj, {
					booleans: false,
					message: $(item).attr('data-message')
				});
				return false;
			}
		});
		return obj;
	};
}
