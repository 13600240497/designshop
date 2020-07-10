<template>
	<ui-layout :componentIndex="componentIndex" :component_id="component_id">
		<el-form class="geshop-design-component-form">
			<div v-for="(item,index) in formDataOriginal.skuTab" :key="index">
				<el-row>
					<el-col :span="12">
						<p>秒杀模块{{index + 1}}设置</p>
						<el-form-item label="秒杀时间段（请选择北京时间）">
							<el-date-picker
								v-model="item.value"
								type="datetimerange"
								value-format="yyyy-MM-dd HH:MM:SS"
								range-separator="至"
								start-placeholder="开始日期"
								end-placeholder="结束日期"
								@change="handleTimePicker($event,index)"
								@blur="blurTime($event)"
								v-valid:skuTab.lists="{'message':'请输入秒杀时间段',type:'datePicker','dataFrom':'formDataOriginal','structure':{'firstKey':'skuTab','index':index,'secondKey':'lists'}}"
							></el-date-picker>
						</el-form-item>
					</el-col>
				</el-row>
				<el-form-item label="SKU">
					<goods-sku :elObj="{type:'textarea',
					structure:{dataName:'formDataOriginal','target':'skuTab','index':index,'name':'lists'},
					model:item.lists,
					api:'isseckill',
					content:{'lang':'en','goodsSn':item.lists}}"></goods-sku>
					<!--<el-input type="textarea" v-model="formData.goodsSKU"></el-input>-->
				</el-form-item>
			</div>
			<button class="geshop-design-ui_hide" v-checkSubmit ref="submit"></button>
		</el-form>
	</ui-layout>
</template>

<script>
	import uiLayout from '../common/ui_layout.vue';
	import goodsSku from '../common/goods_sku.vue';
	import {tabCountGenerate} from '../../../plugin/mUtils'

	export default {
		components: {uiLayout, goodsSku},
		props: ['componentIndex', 'formDataCurrent', 'component_id', 'tab_count'],
		data() {
			return {
				formData: {
					'goodsSKUTab': '', 'goodsSKUSort': ''
				},
				formDataOriginal: {
					'skuTab': [{
						'timeRange': '',
						'lists': '',
						'dataStartTime': '',
						'dataEndTime': '',
						'value': []
					}]
				},
				pageInfo: {
					submitLoading: false
				},
				validShow: false,
				//validStart 是否开始校验证
				validStart: false,
				//是否完成校验 '0','1'
				validStatus: '0',
			};
		},
		created() {

		},
		mounted() {
			let formDataCurrent = this.formDataCurrent;
			if (formDataCurrent) {
				this.formData = formDataCurrent;
				let formDataJson = formDataCurrent['goodsSKUTab'] && JSON.parse(formDataCurrent['goodsSKUTab']);
				formDataJson && formDataJson.forEach(item => {
					if (!item.value && item.timeRange) {
						item.value = [item.timeRange.split(' - ')[0], item.timeRange.split(' - ')[1]];
					}
				});
				if (typeof formDataCurrent['goodsSKUTab'] === 'string' && formDataJson.length > 0) {
					this.formDataOriginal.skuTab = formDataJson;
				}
			} else if (this.tab_count && this.tab_count > 1) {
				this.formDataOriginal.skuTab = tabCountGenerate(this.formDataOriginal.skuTab, this.tab_count)
			}

		},
		watch: {},
		methods: {
			//添加组件触发表单提交方法
			submit() {
				let use_goodsSKUTab = this.formDataOriginal.skuTab.filter(item => {
					return item.dataStartTime && item.dataEndTime;
				});
				this.formData.goodsSKUTab = JSON.stringify(use_goodsSKUTab);
				let goodsSKUSort = use_goodsSKUTab.sort(function () {
					return (function (a, b) {
						return a['dataStartTime'] - b['dataStartTime'];
					});
				});
				this.formData.goodsSKUSort = JSON.stringify(goodsSKUSort);
			},
			handleTimePicker(value, index) {
				let skuTab = this.formDataOriginal.skuTab, valueEable = true;
				try {
					skuTab.forEach((item, itemIndex) => {
						if (!item.value || !value) return false;
						if (index !== itemIndex && item.value[0] === value[0]) {
							this.$message('该开始时间已有秒杀');
							this.formDataOriginal.skuTab[index].value = [];
							this.formDataOriginal.skuTab[index].dataStartTime = '';
							this.formDataOriginal.skuTab[index].dataEndTime = '';
							valueEable = false;
							throw new Error("EndIterative");
						}
					});
				} catch (e) {
					if (e.message !== "EndIterative") throw e;
				}

				if (!valueEable) {
					return false;
				}
				const goodsCurrent = skuTab[index];
				if (value) {
					skuTab[index] = Object.assign(goodsCurrent, {
						'dataStartTime': new Date(value[0]).getTime(),
						'dataEndTime': new Date(value[1]).getTime(),
						'timeRange': value[0] + ' - ' + value[1]
					});
				} else {
					skuTab[index] = Object.assign(goodsCurrent, {
						'dataStartTime': '',
						'dataEndTime': '',
						'timeRange': ''
					});
				}

			},
			blurTime($event) {
				let $input = $event.$el.childNodes[1];
				let evObj = document.createEvent('Event');
				evObj.initEvent('keyup', true, true);
				setTimeout(function () {
					$input.dispatchEvent(evObj);
				}, 200);
			}
		}
	};
</script>

<style lang="less" scoped>
</style>

