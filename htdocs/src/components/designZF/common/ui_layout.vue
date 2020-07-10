<template>
	<el-form class="design-ui-layout">
		<el-form-item label="应用组件数据" class="sync-form-item-block">
			<el-row :gutter="8">
				<el-col :span="6">
					<!--<el-select v-model="dataModel" placeholder="请选择" @change="handleChange" value-key="c_name">-->
					<!--<el-option v-for="(item,index) in vm.componentLeft" :key="item.c_name" :label="item.c_name+'&#45;&#45;'+item.t_name" :value="item"></el-option>-->
					<!--</el-select>-->
					<el-select v-model="dataModel" placeholder="请选择" @change="handleChange">
						<el-option v-for="(item) in vm.componentLeft" :key="item.ui_id" :label="item.c_name ? item.c_name+'--'+item.t_name:''" :value="item.ui_id"></el-option>
					</el-select>
				</el-col>
				<el-col :span="8">
					<el-button icon="el-icon-delete" @click="handleRemoveComponent"
										 :class="vm.componentDataLists.length === 1 ? 'geshop-design-ui_hide':''"
										 class="design-ui-btn-delete" style="padding: 12px 12px;"></el-button>
					<el-button icon="el-icon-plus" @click="handleAddComponent"
										 :class="this.componentIndex>0 && Number(this.componentIndex) === vm.componentLists.length - 1?'geshop-design-ui_hide':''"
										 class="design-ui-btn-add"
										 style="margin-left: 5px;">添加组件
					</el-button>
				</el-col>

			</el-row>
		</el-form-item>
		<el-form-item>
			<slot :formDataFrom="formDataFrom"></slot>
		</el-form-item>
	</el-form>
</template>

<script>
	import { ZF_syncPlatformDelBind } from '../../../plugin/api';

	export default {
		components: {},
		props: ['componentIndex'],
		data () {
			return {
				dataLists: [],
				dataModel: {},
				dataModelId: '',
				pageInfo: {
					submitLoading: false,
					pageInit: false,
					ui_show: true
				},
				vm: '',
				componentSelect: '',
				formDataFrom: {}
			};
		},
		created () {
			this.vm = this.$root.$children[0];
			let vm = this.$root.$children[0];
			let componentSelected = vm.componentDataLists[this.componentIndex];
			this.dataModel = componentSelected.c_name ? componentSelected.c_name + '--' + componentSelected.t_name : '';
			this.dataModelId = componentSelected.ui_id;
		},
		mounted () {},
		methods: {
			createdMirror: function () {
				this.vm = this.$root.$children[0];
				let vm = this.$root.$children[0];
				let componentSelected = vm.componentDataLists[this.componentIndex];
				this.dataModel = componentSelected.c_name ? componentSelected.c_name + '--' + componentSelected.t_name : '';
				this.dataModelId = componentSelected.ui_id;
			},
			handleChange: function (newVal) {
				let _this = this;
				let $refs_parent = this.vm.$refs, $ref_arr = Object.keys($refs_parent), hasSelected = [];
				let leftSelected = JSON.parse(JSON.stringify(this.vm.componentLists));
				//selectComponent is selected component
				let selectComponent = {};
				leftSelected.forEach(item => {
					if (item.ui_id === newVal) {
						selectComponent = item;
					}
				});
				this.dataModelId = newVal;

				/* filter id */
				$ref_arr.forEach(item => {
					let $ui = $refs_parent[item][0] ? $refs_parent[item][0].$children[0] : '';
					// if ($ui.dataModel && $ui.dataModel.ui_id) {
					// 	hasSelected.push($ui.dataModel.ui_id);
					// }
					if ($ui && $ui.dataModel && $ui.dataModelId) {
						hasSelected.push($ui.dataModelId);
					}
				});
				if (hasSelected.length > 0) {

					let a = leftSelected;
					let b = hasSelected;
					for (var i = 0; i < b.length; i++) {
						for (var j = 0; j < a.length; j++) {
							if (a[j].ui_id === b[i]) {
								a.splice(j, 1);
								j = j - 1;
							}
						}
					}

					this.vm.componentLeft = a;

				}
				// this.vm.componentDataLists[this.componentIndex] = this.dataModel.component_key
				this.vm.componentDataLists[this.componentIndex] = selectComponent;
				this.vm.$forceUpdate();
			},

			handleAddComponent () {
				if (!this.$parent.$refs.submit) {
					this.handleAddSuccess();
				}
				this.handleValidComponent();
			},
			handleValidComponent () {
				if (this.$parent.$refs.submit) {
					this.$parent.validStart = true;
					this.$parent.$refs.submit.click();
				}
				if (typeof this.$parent.submit === 'function') {
					this.$parent.submit();
				}
			},
			handleAddSuccess () {
				let componentDataLength = Object.keys(this.vm.componentDataLists).length;
				if (componentDataLength < this.vm.componentLists.length) {
					this.vm.componentDataLists.push({ 'id': '0000', 'tpl_id': '0000', 'component_key': 'default', 'name_en': 'default', 'c_name': '', 't_name': '' });
				} else {
					this.$message('没有更多的组件');
				}
			},
			handleRemoveComponent () {
				this.confirm('是否删除该组件？', async () => {
					let vm = this.vm,
						componentIndex = this.componentIndex;
					let componentCurrent = vm.componentDataLists[componentIndex];

					if (componentCurrent.tpl_id === '0000') {
						vm.componentDataLists.splice(componentIndex, 1);
					} else {
						let params = {
							page_id: document.getElementById('pageId').value,
							tpl_id: componentCurrent.tpl_id,
							pc_tpl: componentCurrent.pc_tpl,
							ui_id: componentCurrent.single_ui_id
						};
						let res = await ZF_syncPlatformDelBind(params);

						if (res.code === 0) {
							// this.pageInfo.ui_show = false
							if (componentCurrent && componentCurrent.component_key !== 'default') {
								let status = false;
								vm.componentLeft.forEach(item => {
									if (item.ui_id === componentCurrent.ui_id) {
										status = true;
									}
								});
								if (!status) {
									vm.componentLeft.push(vm.componentDataLists[componentIndex]);
								}

							}
							// match next component if same component befor others
							let $comp = this.$vnode.context;
							const $siblings = $comp.$parent.$children;
							let nextAllComponent = this.getSameComponentId($comp);
							if (nextAllComponent.length > 1) {
								nextAllComponent.forEach((item, index) => {
									if (index < nextAllComponent.length - 1) {
										let oldComponent = $siblings[item],
											nextComponent = $siblings[item + 1];
										// oldComponent.formData = nextComponent.formData;
										let old_data = oldComponent._data,
											next_data = nextComponent._data
										for (let i in old_data) {
											if (next_data && next_data[i])
												old_data[i] = next_data[i];
										}
										oldComponent.$children[0].dataModelId = nextComponent.$children[0].dataModelId;
									}
								});
							}


							// this.$vnode.context.$destroy()
							vm.componentDataLists.splice(componentIndex, 1);
							// this.pageInfo.ui_show = true
						}


					}

				});

			},
			getSameComponentId (vm) {
				let $comp = vm,
					$siblings = $comp.$parent.$children,
					current_uid = $comp._uid,
					current_index = 0,
					current_component_id = $comp.component_id,
					sameComponent = [],
					sameComponentIndex = [];
				if (!current_component_id) {
					return false;
				}
				if ($siblings.length > 1) {
					$siblings.forEach((item, index) => {
						if (item._uid === current_uid) {
							current_index = index;
						}
						if (item.component_id === current_component_id) {
							sameComponent.push(item);
							sameComponentIndex.push(index);
						}
					});
				}
				sameComponentIndex.sort();
				let nextSameComponent = sameComponentIndex.filter((item) => {
					return item >= current_index;
				});

				let connectComponent = this.getSameSibling(nextSameComponent)[0];

				// connectComponent.forEach(item=>{
				// 	
				// })
				return connectComponent;
			},
			getSameSibling (arr) {
				var result = [],
					i = 0;
				result[i] = [arr[0]];
				arr.reduce(function (prev, cur) {
					cur - prev === 1 ? result[i].push(cur) : result[++i] = [cur];
					return cur;
				});
				return result;
			},
			getNextSameComponentId (vm) {
				let $comp = vm,
					$siblings = $comp.$parent.$children,
					current_uid = $comp._uid,
					current_index = 0,
					nextComponent = '';
				if ($siblings.length > 1) {
					try {
						$siblings.forEach((item, index) => {
							if (item._uid === current_uid) {
								current_index = index;
								throw new Error("EndIterative");
							}
						});
					} catch (e) {
						if (e.message !== "EndIterative") throw e;
					}

				}
				if ($siblings[current_index + 1]) {
					nextComponent = $siblings[current_index + 1];
				}
				return nextComponent;
			},
			confirm (message, callback, catchFn) {
				this.$confirm(message, '提示', {
					confirmButtonText: '确定',
					cancelButtonText: '取消',
					type: 'warning'
				}).then(() => {
					if (typeof callback === 'function') {
						callback(this);
					}
				}).catch((err) => {
					if (typeof catchFn === 'function') {
						catchFn(this);
					}
					this.$message({
						type: 'info',
						message: '已取消操作!'
					});
				});
			}
		}
	};
</script>

<style lang="less" scoped>
</style>

