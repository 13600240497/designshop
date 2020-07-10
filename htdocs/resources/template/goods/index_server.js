var pageId = sessionStorage.currentComponentId
$('#gs_getList,#goods_tab .class-manage').on('click', function () {
	var $layerWrap = $('#goods_layer_wrap'),
		$manageBtn = $(this)

	/* sku 输入框
	* goodsType 是否是分类数据
	*/
	var $form = $('#component_form'),
		$targetInput,
		goodsType = $form.find('.layui-tab-content').attr('data-goodsType')
	if (goodsType !== 'tab') {
		$targetInput = $form.find('textarea[name=goodsSKU]')
	} else {
		$targetInput = $manageBtn.parent().prev().find('textarea')
	}

	var skus = $targetInput.val()

	if (!skus) {
		layer.msg('请输入SKU')
		return false
	}

	Design.enableLayuiLoading()

	// $trIndex
	if (!$layerWrap.length) {
		$('body').append('<div id="goods_layer_wrap"><input type="hidden" name="tempGoodsArr"></input><table id="goods_layer_table" lay-filter="gs_goodstable"></table>' +
			'<div id="goods_toolbar"><a class="layui-btn layui-btn-xs" lay-event="class-edit" data-type="class-edit">替换</a>' +
			'<a class="layui-btn layui-btn-xs" lay-event="class-add" data-type="class-add">增加</a><a class="layui-btn layui-btn-xs" lay-event="class-del" data-type="class-del">删除</a>' +
			'<a class="layui-btn layui-btn-xs" lay-event="class-up" data-type="class-up" title="上移">上移</a><a class="layui-btn layui-btn-xs" lay-event="class-down" data-type="class-down">下移</a></div></div>')
	}

	/* 分类SKU */
	// if ($(this).hasClass('class-manage')) {
	// 	var $tr = $(this).parents('tr:eq(0)'),
	// 		$trIndex = $('#design_form .goods-form-table').index($tr)
	// }


	var url = '/activity/goods/tpl-goods-list'
	var Table = layui.table
	Table.render({
		elem: '#goods_layer_table'
		, height: 400
		// , width: 1180
		, url: url
		, where: { lang: 'en', skus: skus, pageId: pageId }
		// , page: true
		, limit: 20
		, id: 'gs_goodstable'
		, cols: [[
			{ type: 'checkbox', fixed: 'left' }
			, {
				field: 'numbers', title: '序号', width: 80, templet: function (d) {
					var value = d.LAY_INDEX
					return '<input type="number" name="goods_item_orders" value="' + value + '" data-order="' + value + '" style="width: 100%;"> '
				}
			}
			, { field: 'goods_id', title: '商品ID', width: 100 }
			, { field: 'goods_sn', title: '商品SKU', width: 140 }
			, { field: 'goods_title', title: '商品标题', width: 100 }
			, {
				field: 'goods_img', title: '商品图片', width: 100, height: 100
				, templet: function (d) {
					return '<img src="' + d.goods_img + '">'
				}
			}
			, {
				field: 'is_on_sale', title: '上架', width: 80, templet: function (d) {
					var text = d.is_on_sale == '1' ? '上架' : '下架'
					return text
				}
			}
			, { field: 'goods_number', title: '库存', width: 80 }
			, { field: 'promte_percent', title: '促销利润比', width: 100 }
			, {
				field: 'left_time', title: '促销时间', width: 200, templet: function (d) {
					return d.promote_start_date ? d.promote_start_date + ' 至 ' + d.promote_end_date : ''
				}
			}
			, { field: 'activity_number', title: '活动库存', width: 100 }
			, { field: 'activity_volume_number', title: '活动库存销量', width: 120 }
			, { fixed: 'right', width: 250, align: 'center', toolbar: '#goods_toolbar' }
			// , {
			// 	field: 'options', 'title': '操作', width: 200
			// 	, templet: function (d) {
			// 		return '<button class="layui-btn layui-btn-xs" data-type="class-edit">替换</button><button class="layui-btn layui-btn-xs" data-type="class-add">增加</button><button class="layui-btn layui-btn-xs" data-type="class-del">删除</button>'
			// 		// return '<i class="layui-icon layui-icon-prev class-top" data-type="class-top"  title="置顶"></input><i class="layui-icon layui-icon-next class-bottom" data-type="class-bottom" title="置底"></i>' +
			// 		// '<i class="layui-icon class-up" data-type="class-up" title="上移">&#xe619;</i><i class="layui-icon class-down" data-type="class-down" title="下移">&#xe61a;</i>' +
			// 		// '<i class="layui-icon class-close" data-type="class-close" title="删除">&#x1006;</i>'
			// 	}
			// }
		]]
		, done: function (res, curr, count) {
			Design.disableLayuiLoading()
			$('input[name=tempGoodsArr]').val(skus);
			if (res.code !== 0 && res.message) {
				/* 清除wrap */
				$('#goods_layer_wrap').next('.layui-form').remove()
				$('#goods_layer_wrap').remove()
				layer.msg(res.message)
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
				layer.open({
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
					yes: function (index, layero) {
						var goodsOrder = $('input[name=tempGoodsArr]', '#goods_layer_wrap').val()
						// if (goodsType !== 'tab') {
						// 	layer.close(index)
						// }
						layer.close(index)
						$targetInput.val(goodsOrder)
					},
					btn2: function (index, layero) {
					},
					btn3: function (index, layero) {
						layer.confirm('确定批量删除商品吗？删除后，商品将不再列表中展示！', { icon: 3, title: '提示' }, function (index) {
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
	})

})

var $layer = $('#goods_layer')
var $tobody = $('tbody', $layer)

var GsManager = (function (my) {
	var $layerWrap, tempGoodsArr, Table = layui.table
	var pageId = sessionStorage.currentComponentId
	my.init = function () {
		$layer = $('#goods_layer')
		$layerWrap = $('#goods_layer_wrap')
		tempGoodsArr = $('input[name=tempGoodsArr]', $layerWrap)
		var _this = this
		_this.countNum()
		/* 监听序号 */
		$('#goods_layer').on('change', 'input[name=goods_item_orders]', function () {
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
		layer.confirm('确定删除此商品吗？删除后，商品将不再列表中展示！', { icon: 3, title: '提示' }, function (index) {
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
							lang: 'en',
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
							lang: 'en',
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
				$('input[name=replaceSKU]', addWrapId).on('change', function () {
					var sku = $(this).val(),
						url = '/activity/goods/tpl-goods-list'
					$.ajax({
						type: 'GET',
						url: url,
						data: { lang: 'en', skus: sku, pageId: pageId },
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

	my.goods_exists = function (sku, callback) {
		var url = '/activity/goods/tpl-goods-exists'
		return $.ajax({
			type: 'GET',
			url: url,
			data: { lang: 'en', skus: sku, pageId: pageId, },
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


