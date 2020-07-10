/**
 * defines valid for multiple component
 *
 * @version 1.0
 * @author wuxingtao@globalegrow.com
 */


export default {
	install: function (Vue, options) {

		/**
		 * 校验 满足sku前置情况下的必填
		 * arg 验证属性
		 * modifiers 必填条件
		 */
		Vue.directive('valid', {
				inserted: function (el, binding, vNod) {
					let context = vNod.context,
						data = context._data,
						arg = binding.arg,
						conditionObj = binding.value,
						modifiers = binding.modifiers,
						modifiersArr = Object.keys(modifiers),
						validArr = Object.keys(conditionObj);
					let $el = conditionObj.type && conditionObj.type === 'datePicker' ? el.children[1] : el.children[0],
						$parent = $el.parentElement;
					$el.addEventListener('keyup', function () {
						if (!context.validStart) {
							return false;
						}
						try {
							let length = 0;
							modifiersArr.forEach(item => {
								//满足参数特殊条件
								let $formData = conditionObj.dataFrom ? data[conditionObj.dataFrom] : data.formData;
								let structure_obj = conditionObj.structure;
								let conditionStatus = structure_obj ? $formData[structure_obj.firstKey][structure_obj.index][structure_obj.secondKey] : $formData[item];
								if ($el.value === '' && conditionStatus) {
									if (!$parent.nextElementSibling || $parent.nextElementSibling.className.indexOf('design-error-tip') === -1) {
										let small = document.createElement('small');
										small.className = 'design-error-tip design-error-show';
										small.textContent = conditionObj.message || '错误提示';
										$parent.parentElement.appendChild(small);
									} else {
										if ($parent.nextElementSibling.className.indexOf('design-error-tip') !== -1) {
											$parent.nextElementSibling.className = 'design-error-tip design-error-show';
										}
									}
									throw new Error('EndIterative');
								} else {
									length += 1;
								}
							});
							if (length === modifiersArr.length) {
								if ($parent.nextElementSibling && $parent.nextElementSibling.className.indexOf('design-error-tip') !== -1) {
									$parent.nextElementSibling.className = 'design-error-tip design-error-hide';
								}

								let nodeLists = $parent.parentElement.querySelector('.design-error-show');
								if (nodeLists) {
									nodeLists.className = 'design-error-tip design-error-hide';
								}
							}
						} catch (e) {
							if (e.message !== 'EndIterative') throw e;
						}
					});
				},
				bind: function (el, binding, vnode) {

				}
			}
		);

		Vue.directive('checkSubmit', {
			inserted: function (el, binding, vNode) {
				el.addEventListener('click', function (event) {
					event.preventDefault();
					let evObj = document.createEvent('Event');
					evObj.initEvent('keyup', true, true);
					Array.from(el.form).forEach(element => {
						element.dispatchEvent(evObj);
					});
					let context = vNode.context, $root = vNode.context.$root.$children[0];
					let errorInputs = context.$el.querySelectorAll('.design-error-show');
					if (errorInputs.length === 0 && !$root.dialogGroup.submitStart) {
						context.$children[0].handleAddSuccess();
						// vNode.context.submit();
					}
				});
			}
		});
	}

};
