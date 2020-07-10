<template>
	<div>
		<site-layout-for-design></site-layout-for-design>
		<!-- 多端数据处理 -->
		<el-dialog class="sync-page-dialog geshop-new-activities" :visible.sync="dialogGroup.visible" title="多渠道多端数据绑定配置"
							 :close-on-click-modal="dialogGroup.closeModal" :lock-scroll="dialogGroup.lockScroll" @close="handleCloseDialog" top="10vh">
			<el-row class="top-tip-wrapper">
				<el-col><span class="top-tip">请先按步骤选择端口/渠道/组件，选择完毕后添加数据！</span></el-col>
			</el-row>
			<el-form :disabled="dialogGroup.disabled" class="geshop-dialog-body">
				<el-form-item label="应用端口" class="sync-form-item-block">
					<el-checkbox-group v-model="pipeForm.platModel">
						<el-checkbox v-for="(item,index) in pipeForm.platList" :label="item.key" :key="index">{{item.label}}
						</el-checkbox>
					</el-checkbox-group>
				</el-form-item>
				<el-form-item label="应用渠道" class="sync-form-item-block pd-rt-20">
					<el-checkbox-group v-model="pipeForm.channelModel">
						<el-checkbox v-for="(item,index) of pipeForm.channelList" :label="index" :key="index" class="geshop-checkbox-item">{{item}}</el-checkbox>
					</el-checkbox-group>
				</el-form-item>
				<el-form-item label="是否覆盖原有组件内数据" class="sync-form-item-block">
					<el-radio-group v-model="pipeForm.is_cover">
						<el-radio label="1">是</el-radio>
						<el-radio label="0">否</el-radio>
					</el-radio-group>
				</el-form-item>

				<div class="component_list" v-if="dialogGroup.componentReady">
					<!--<el-tabs type="card" editable  v-model="component_current" @edit="handleTabsEdit">-->
					<!--<el-tab-pane v-for="(item,index) in componentDataLists" :key="index" :label="'aaaa'+index+5" :name="'bbb'+index">-->
					<!--<component :is="plugs[item]" :componentLists="componentLists" :componentIndex="index"   :ref="'component_ui_'+ index"></component>-->
					<!--</el-tab-pane>-->
					<!--</el-tabs>-->
					<!--<component :is="plugs[item]" :componentIndex="index" v-for="(item,index) in componentDataLists" :ref="'component_ui_'+ index"></component>-->
					<component 
                        :is="plugs[item.component_key+'_'+item.name_en]" 
                        :componentIndex="index" 
                        :component_id="item.component_key+'_'+item.name_en"
                        v-for="(item, index) in componentDataLists"
                        :key="'component_ui_'+ index"
                        :ref="'component_ui_'+ index"
                        :tab_count="item.sub_tab_count"
                        :formDataCurrent="pageBase.pageType === 'edit' && form_data_obj[item.single_ui_id] ? form_data_obj[item.single_ui_id].public_data:''"
					></component>
				</div>

				<el-form-item class="geshop-new-activities-btn">
					<el-button size="small" @click="handleCloseDialog">取消</el-button>
					<el-button type="primary"
										 size="small" :loading="pageBase.submitLoading" @click="onSubmit">保存
					</el-button>
					<el-button type="danger" size="small" @click="onBind">绑定</el-button>
				</el-form-item>

			</el-form>
		</el-dialog>

		<!-- 商品管理弹窗 -->
        <goods-source-manager
            ref="goodsSourceManager"
            :visible.sync="goodsSourceManager.visible"
            :value.sync="goodsSourceManager.value"
			:able="[]"
			origin="local" />

	</div>

</template>

<script>
	import siteLayoutForDesign from './layouts/LayoutForDesign.vue';
	import uiLayout from './designZF/common/ui_layout.vue';
	import { ZF_getDesignPlatForm, ZF_designcomponentSelect, ZF_designBatchSave, ZF_designBatchBind, ZF_designBatchFrom } from '../plugin/api';
	import '../../resources/stylesheets/DesignSimulate.less';


	// 商品管理模块
	import goodsSourceManager from '../views/design/more/goods-source-manager/index';

	export default {

		components: {
			siteLayoutForDesign,
			uiLayout,
			goodsSourceManager
		},

		data () {
			return {
				dialogGroup: {
					visible: false,
					closeModal: false,
					disabled: false,
					componentReady: false,
					lockScroll: true,
					submitReady: true,
					submitStart: false
				},
				pageBase: {
					submitLoading: false,
					pageType: 'add'
				},
				pipeForm: {
					//强制替换渠道
					enforceChange: false,
					//是否已经初始化
					placeInit: false,
					places: {},
					platList: [{ "key": 'pc', "label": 'PC' }, { "key": 'wap', "label": 'M' }, { "key": 'app', "label": 'APP' }],
					platModel: [],
					channelList: [],
					channelModel: [],
					is_cover: '1'
				},
				//所有端渠道语言
				allSupportChannelLang_res: {},
				//组件列表
				componentLists: [
					// { 'ui_id': 11, 'tpl_id': 11, 'component_key': 'U000001', 'c_name': '普通商品组件1', 't_name': '默认模版' },
					// { 'ui_id': 22, 'tpl_id': 11, 'component_key': 'U000002', 'c_name': '普通商品组件2', 't_name': '默认模版' },
				],
				componentLeft: [],
				component_current: '',
				plugFirstValue: '',
				plugs: {},
				//已开发组件
				plugsCompolete: ['default', 'U000031', 'U000081', 'U000110', 'U000113', 'U000128', 'U000151', 'U000153', 'U000163'],
				//已添加组件
				componentDataLists: [
					// {tpl_id: "16", t_name: "默认模版", ui_id: "31", component_key: "U000031", c_name: "商品列表"}
					// 'U000031'
				],
				componentDataSelected: [],
				//form all data
				form_data: [],
				form_data_obj: {},

				// 商品管理弹窗
				goodsSourceManager: {
					visible: false,
					value: 0,
				}
			};
		},

		watch: {
			"pipeForm.platModel": function (newVal, oldVal) {
				let type = newVal.length > oldVal.length ? 'add' : 'cancel';
				let _this = this;
				if (this.pipeForm.placeInit) {
					//更新comfirm提示
					this.handlePipeChange(type, newVal, oldVal, 'plat', function () {
						_this.form_data = [];
						_this.form_data_obj = [];
						_this.platChangeCallback();
					});
				}
				//编辑更新
				if (this.pipeForm.enforceChange) {
					this.editPlatChangeCallback();
				}

			},
			"pipeForm.channelModel": function (newVal, oldVal) {
				let type = newVal.length > oldVal.length ? 'add' : 'cancel';
				let _this = this;
				if (this.pipeForm.placeInit) {
					this.handlePipeChange(type, newVal, oldVal, 'channel', function () {
						_this.form_data = [];
						_this.form_data_obj = [];
						_this.handleChannelChange();
					});
				}
			},
			/*			"dialogGroup.visible": function (newVal) {
              if (newVal) {
                this.createdMirror();
              }
            }*/
		},

		methods: {
			// handleTabsEdit (targetName, action) {
			// },
			//提交顺序问题解决
			async onSubmit (type, callback) {
				setTimeout(async () => {
					if (!this.dialogGroup.submitReady) return false;
					this.dialogGroup.submitStart = true;
					let $refs = this.$refs, $ref_arr = Object.keys($refs), formAll = [];
					$ref_arr.sort(function(a,b){
						let a_value = a.split('_')[2],
							b_value = b.split('_')[2];
						return a_value - b_value
					});
					$ref_arr.forEach(item => {
						if ($refs[item][0]) {
							let $vmCurrent = $refs[item][0];
							if ($vmCurrent.$children[0]) {
								$vmCurrent.$children[0].handleValidComponent();
							}
						}
					});
					let errorLength = document.querySelectorAll('.sync-page-dialog .design-error-show').length;
					if (errorLength > 0) {
						this.$message({
							message: '请先处理错误字段！',
							type: 'warning'
						});
						return false;
					}

					$ref_arr.forEach((item, index) => {
						if ($refs[item][0]) {
							let currentComponentData = this.componentDataLists[index];
							if(currentComponentData){
								let ui_id = currentComponentData.ui_id,
									pc_tpl = currentComponentData.pc_tpl || '',
									tpl_id = currentComponentData.tpl_id,
									public_data = $refs[item][0].formData,
									single_ui_id = currentComponentData.single_ui_id;
								if (ui_id) {
									formAll.push({ "single_ui_id": single_ui_id, "ui_id": ui_id, "public_data": public_data, "tpl_id": tpl_id, "pc_tpl": pc_tpl, "component_key": currentComponentData.component_key });
								}
							}

						}

					});
					let params = {
						"page_id": document.getElementById('pageId').value,
						"pipeline": this.pipeForm.channelModel.toString(),
						"platform": this.pipeForm.platModel.toString(),
						"is_cover": this.pipeForm.is_cover,
						"form_data": JSON.stringify(formAll)
					};
					let fn = type && type === 'bind' ? ZF_designBatchBind : ZF_designBatchSave;
					let res;
					if (type && type === 'bind') {
						res = await fn(params, { messageOff: true });
						if (res.code === 2 && res.data.length>0) {
							this.$message({
								type: 'error',
								dangerouslyUseHTMLString: true,
								message: res.data.join('<br/>'),
								customClass:'gesohp-bind-error-lists'
							});
						} else if (res.code !== 0 && res.message) {
							this.$message.error(res.message);
						}
					} else {
						res = await fn(params);
					}
					this.dialogGroup.submitStart = false;

					if (callback) callback(res);
				}, 500);
			},

			onBind () {
				this.onSubmit('bind', function (res) {
					if (res.code === 0) {
						setTimeout(function () {
							window.location.reload();
						}, 500);
					}
				});
			},

			createdMirror () {
				this.handlePlatList();
			},

			//page already created
			async MountedAfter () {
				let res = await ZF_designBatchFrom({
					'page_id': document.getElementById('pageId').value
				}, { 'successOff': true });
				this.pageBase.pageType = 'edit';
				let data = res.data;
				if (data && data.form_data && data.form_data.length > 0) {
					this.pipeForm.placeInit = false;
					this.pipeForm.enforceChange = true;
					this.pipeForm.is_cover = data.is_cover;
					this.pipeForm.channelModel = data.pipeline;
					this.pipeForm.platModel = data.platform.split(',');
					this.form_data = data.form_data;

					data.form_data.forEach(item => {
						this.form_data_obj[item.single_ui_id] = item;
					});

					setTimeout(() => {
						this.pipeForm.placeInit = true;
						this.pipeForm.enforceChange = false;
					}, 800);

					this.handleChannelChange('edit');
				} else {
					
				}


			},

			editPlatChangeCallback () {
				let allSupportChannelLang_res = JSON.parse(JSON.stringify(this.allSupportChannelLang_res));
				this.pipeForm.channelList = this.handleMapPipeLineMix(allSupportChannelLang_res, this.pipeForm.platModel);
			},

			//确认新增删除后回调
			platChangeCallback () {
				let _this = this;
				_this.form_data = [];
				_this.form_data_obj = [];
				this.pipeForm.placeInit = false;
				let allSupportChannelLang_res = JSON.parse(JSON.stringify(_this.allSupportChannelLang_res));
				_this.pipeForm.channelList = _this.handleMapPipeLineMix(allSupportChannelLang_res, _this.pipeForm.platModel);
				this.pipeForm.channelModel = [];
				setTimeout(() => {
					this.pipeForm.placeInit = true;
				}, 500);
			},

			handlePipeChange (...rest) {
				let _this = this;
				let typeName = rest[0] === 'add' ? '新增端口/渠道后' : '取消端口/渠道后';
				let newVal = rest[1] || '', oldVal = rest[2] || '', platType = rest[3] || 'plat', catchFn = rest[4] || null;
				this.confirm(`${typeName}，已添加的组件数据会清空呦！`, (vm) => {
					_this.componentDataLists = [];
					_this.componentDataSelected = [];
					if (typeof catchFn === 'function') {
						catchFn();
					}
				}, (vm) => {
					if (platType === 'plat') {
						this.pipeForm.placeInit = false;
						this.pipeForm.platModel = oldVal;
						setTimeout(() => {
							this.pipeForm.placeInit = true;
						}, 500);
					} else {
						this.pipeForm.placeInit = false;
						this.pipeForm.channelModel = oldVal;
						setTimeout(() => {
							this.pipeForm.placeInit = true;
						}, 500);
					}

				});
			},

			/*
			* 渠道变处理
			* type 是否编辑
			* */
			handleChannelChange (type) {
				this.handleChannelSelect(type);
			},

			async handleChannelSelect (type) {
				let pipeline = this.pipeForm.channelModel.toString(),
					platform = this.pipeForm.platModel.toString();
				if (!pipeline || !platform) {
					return false;
				}
				let params = {
					"page_id": document.getElementById('pageId').value,
					"pipeline": pipeline,
					"platform": platform
				};
				let res = await ZF_designcomponentSelect(params),
					dataInitial = res.data;
				//过滤已开发组件
				let data = dataInitial.filter(item => {
					return this.plugsCompolete.includes(item.component_key);
				});
				data.forEach(item => {
					if (item.ui_id) {
						let ui_id_obj = JSON.parse(item.ui_id),
							ui_id_channel = ui_id_obj[Object.keys(ui_id_obj)[0]],
							first_ui_id = ui_id_channel instanceof Array ? ui_id_channel[0] : ui_id_channel[Object.keys(ui_id_channel)[0]];
						item.single_ui_id = first_ui_id || '';
					}

				});

				this.componentLists = data;
				this.componentLeft = JSON.parse(JSON.stringify(data));
				if (type !== 'edit' && data.length > 0) {
					this.componentDataLists.push(data[0]);
					this.componentDataSelected.push(data[0]);
					this.componentLeft.splice(0, 1);
				} else {
					//form_data_keys 已被选
					let form_data_component = [], form_data_arr = this.form_data, form_data_keys = [], component_keys = [];

					form_data_arr.forEach(formItem => {
						form_data_keys.push(formItem.single_ui_id);
						data.forEach(item => {
							component_keys.push(item.single_ui_id);
							if (item.single_ui_id === formItem.single_ui_id) {
								form_data_component.push(item);
							}
						});

					});

					let hash = {};
					form_data_component = form_data_component.reduce((preVal, curVal) => {
						hash[curVal.single_ui_id] ? '' : hash[curVal.single_ui_id] = true && preVal.push(curVal);
						return preVal;
					}, []);

					this.componentDataLists = form_data_component;
					this.componentDataSelected = form_data_component;

					//filter has selected
					let leftSelected = JSON.parse(JSON.stringify(this.componentLists)),
						hasSelected = form_data_keys;

					if (hasSelected.length > 0) {

						let a = leftSelected;
						let b = hasSelected;
						for (var i = 0; i < b.length; i++) {
							for (var j = 0; j < a.length; j++) {
								if (a[j].single_ui_id === b[i]) {
									a.splice(j, 1);
									j = j - 1;
								}
							}
						}
						this.componentLeft = a;
					}
				}


				if (Object.keys(this.componentLists).length > 0) {
					this.componentInit();
				}
			},

			async handlePlatList () {
				let params = {
					"page_id": document.getElementById('pageId').value,
					"site_code": document.getElementById('siteCode').value
				};
				let res = await ZF_getDesignPlatForm(params);
				if (res.code === 0) {
					this.dialogGroup.visible = true;
				} else {
					return false;
				}
				this.allSupportChannelLang_res = res.data;
				this.pageInitPlat(Object.keys(res.data));
				//init channelList
				if (Object.keys(res.data).length > 0) {
					this.pipeForm.channelList = this.handleMapPipeLineMix(this.allSupportChannelLang_res, this.pipeForm.platModel);
				}

				//默认不勾选
				// let channelArr = Object.keys(this.pipeForm.channelList), let channelModel = [];
				// channelArr.forEach(item => {
				// 	channelModel.push(item);
				// });
				// this.pipeForm.channelModel = channelModel;

				setTimeout(() => {
					this.pipeForm.placeInit = true;
				}, 800);

				this.handleChannelChange();

				this.MountedAfter();
			},

			/**
			 *  filter pipeline
			 * @param data 多端数据
			 * @param platform
			 */
			handleMapPipeLine (data, platform) {
				let pipeLineAll = {};
				platform.forEach((item) => {
					let pipeline = item === 'wap' ? data['m'] : data[item],
						pipelineEntry = Object.entries(pipeline);
					pipelineEntry.forEach((channelItem) => {
						const channelKey = channelItem[0],
							channelName = channelItem[1];
						if (!pipeLineAll[channelKey]) {
							pipeLineAll[channelKey] = pipeline[channelKey];
						}
					});
				});

				return pipeLineAll;
			},

			handleMapPipeLineMix (data, platform) {
				let pipeLineAll = {};
				platform.forEach((item) => {
					let pipeline = item === 'wap' ? data['m'] : data[item] ? data[item] : {},
						pipelineEntry = Object.entries(pipeline);
					pipelineEntry.forEach((channelItem) => {
						const channelKey = channelItem[0],
							channelName = channelItem[1];
						if (!pipeLineAll[channelKey]) {
							pipeLineAll[channelKey] = pipeline[channelKey];
						}
					});
				});
				platform.forEach(item => {
					let pipeline = item === 'wap' ? data['m'] : data[item],
						pipelineKeyArr = Object.keys(pipeline);
					Object.keys(pipeLineAll).forEach(item => {
						if (!pipelineKeyArr.includes(item)) {
							delete pipeLineAll[item];
						}
					});

				});

				return pipeLineAll;
			},

			pageInit () {
				let places = JSON.parse(localStorage.currentSites).sites,
					placeArr = Object.keys(places),
					placeList = [],
					platModel = [];
				placeArr.forEach((item) => {
					placeList.push({
						"key": item,
						"label": places[item].platform_name
					});
					platModel.push(item);
				});
				this.pipeForm.places = places;
				this.pipeForm.platList = placeList;
				this.pipeForm.platModel = platModel;
			},

			pageInitPlat (placeArr) {
				let platModel = [],
					placeList = [];
				placeArr.forEach(item => {
					let key = item === 'm' ? 'wap' : item;
					placeList.push({
						'key': key,
						'label': item.toUpperCase()
					});
					platModel.push(key);
				});
				this.pipeForm.platList = placeList;
				this.pipeForm.platModel = platModel;
			},

			componentInit () {
				this.dialogGroup.componentReady = false;
				let componentListsArr = [{ 'id': '0000', 'tpl_id': '0000', 'component_key': 'default', 'name_en': 'default', 'c_name': '默认组件', 't_name': '默认模版' }].concat(this.componentLists);
				componentListsArr.forEach(item => {
					var name = './designZF/' + item.component_key + '/' + item.name_en + '.vue';
					this.plugs[item.component_key + '_' + item.name_en] = () => import(`${name}`);
				});

				let plugsObj = Object.keys(this.plugs);
				this.plugFirstValue = plugsObj[0] ? plugsObj[0] : '';
				this.dialogGroup.componentReady = true;

			},

			handleCloseDialog () {
				this._data = Object.assign(this.$data, this.$options.data());
				this.dialogGroup.visible = false;
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
		},

		created () {
			// 商品管理弹窗暴露函数
			window.goodsSourceManager = (() => {
				/**
				 * 打开商品管理弹窗
				 * @param {String} params.type 数据类型[SKU=1 / 商品运营平台=2]
				 * @param {String} params.sku 数据SKU，逗号分割 "255989505,212363402"
				 * @param {String} params.sop_rule_id 商品运营平台规则ID
				 * @param {Function} params.confirm 确认回调
				 */
				const open = (params) => {
					if (params) {
						this.goodsSourceManager.visible = true;
						this.$refs.goodsSourceManager.init(params);
					}
				};
				return {
					open
				}
			})();
		}
	};
</script>
<style lang="less">
	.sync-page-dialog {
		.sync-form-item-block {
			.el-form-item__label {
				float: none;
			}
		}
		.gs-form-label, .sync-form-item-block .el-form-item__label {
			position: relative;
			&:before {
				content: '*';
				color: #f56c6c;
				margin-right: 4px;
			}
		}
	}

	.component_list {
		padding-right: 20px;
		padding-top: 10px;
		max-height: 300px;
		overflow-y: scroll;
	}

	.pd-rt-20 {
		padding-right: 20px;
	}

	.top-tip-wrapper {
		display: block;
		height: 40px;
		line-height: 40px;
		width: 100%;
		background: rgba(253, 246, 235, 1);
		color: #E7AA4E;
		padding-left: 40px;
		margin-bottom: 0;
	}
	.gesohp-bind-error-lists .el-message__content{
		line-height:1.4;
	}
</style>
